#include "stdafx.h"
#include "tracker_input.h"

#include <bt_misc.h>
#include <socket.h>

// TorrentPier begin
#ifdef WIN32

#define IN6ADDRSZ 16
#define INADDRSZ 4
#define INT16SZ 2

/* int
 * inet_pton4(src, dst)
 *	like inet_aton() but without all the hexadecimal and shorthand.
 * return:
 *	1 if `src' is a valid dotted quad, else 0.
 * notice:
 *	does not touch `dst' unless it's returning 1.
 * author:
 *	Paul Vixie, 1996.
 */
static int
inet_pton4(const char *src, unsigned char *dst)
{
	static const char digits[] = "0123456789";
	int saw_digit, octets, ch;
	unsigned char tmp[INADDRSZ], *tp;

	saw_digit = 0;
	octets = 0;
	*(tp = tmp) = 0;
	while ((ch = *src++) != '\0') {
		const char *pch;

		if ((pch = strchr(digits, ch)) != NULL) {
			unsigned int newval = (unsigned int) (*tp * 10 + (pch - digits));

			if (newval > 255)
				return (0);
			*tp = newval;
			if (! saw_digit) {
				if (++octets > 4)
					return (0);
				saw_digit = 1;
			}
		} else if (ch == '.' && saw_digit) {
			if (octets == 4)
				return (0);
			*++tp = 0;
			saw_digit = 0;
		} else
			return (0);
	}
	if (octets < 4)
		return (0);

	memcpy(dst, tmp, INADDRSZ);
	return (1);
}

/* int
 * inet_pton6(src, dst)
 *	convert presentation level address to network order binary form.
 * return:
 *	1 if `src' is a valid [RFC1884 2.2] address, else 0.
 * notice:
 *	(1) does not touch `dst' unless it's returning 1.
 *	(2) :: in a full address is silently ignored.
 * credit:
 *	inspired by Mark Andrews.
 * author:
 *	Paul Vixie, 1996.
 */
static int
inet_pton6(const char *src, unsigned char *dst)
{
	static const char xdigits_l[] = "0123456789abcdef",
			  xdigits_u[] = "0123456789ABCDEF";
	unsigned char tmp[IN6ADDRSZ], *tp, *endp, *colonp;
	const char *xdigits, *curtok;
	int ch, saw_xdigit;
	unsigned int val;

	memset((tp = tmp), '\0', IN6ADDRSZ);
	endp = tp + IN6ADDRSZ;
	colonp = NULL;
	/* Leading :: requires some special handling. */
	if (*src == ':')
		if (*++src != ':')
			return (0);
	curtok = src;
	saw_xdigit = 0;
	val = 0;
	while ((ch = *src++) != '\0') {
		const char *pch;

		if ((pch = strchr((xdigits = xdigits_l), ch)) == NULL)
			pch = strchr((xdigits = xdigits_u), ch);
		if (pch != NULL) {
			val <<= 4;
			val |= (pch - xdigits);
			if (val > 0xffff)
				return (0);
			saw_xdigit = 1;
			continue;
		}
		if (ch == ':') {
			curtok = src;
			if (!saw_xdigit) {
				if (colonp)
					return (0);
				colonp = tp;
				continue;
			}
			if (tp + INT16SZ > endp)
				return (0);
			*tp++ = (unsigned char) (val >> 8) & 0xff;
			*tp++ = (unsigned char) val & 0xff;
			saw_xdigit = 0;
			val = 0;
			continue;
		}
		if (ch == '.' && ((tp + INADDRSZ) <= endp) &&
		    inet_pton4(curtok, tp) > 0) {
			tp += INADDRSZ;
			saw_xdigit = 0;
			break;	/* '\0' was seen by inet_pton4(). */
		}
		return (0);
	}
	if (saw_xdigit) {
		if (tp + INT16SZ > endp)
			return (0);
		*tp++ = (unsigned char) (val >> 8) & 0xff;
		*tp++ = (unsigned char) val & 0xff;
	}
	if (colonp != NULL) {
		/*
		 * Since some memmove()'s erroneously fail to handle
		 * overlapping regions, we'll do the shift by hand.
		 */
		const int n = tp - colonp;
		int i;

		for (i = 1; i <= n; i++) {
			endp[- i] = colonp[n - i];
			colonp[n - i] = 0;
		}
		tp = endp;
	}
	if (tp != endp)
		return (0);
	memcpy(dst, tmp, IN6ADDRSZ);
	return (1);
}

int my_inet_pton(int af, const char *src, void *dst)
{
	return (inet_pton6(src, (unsigned char *) dst));
}

#define inet_pton my_inet_pton

#endif

Ctracker_input::Ctracker_input(int family)
// TorrentPier end
{
	m_compact = false;
	m_downloaded = 0;
	m_event = e_none;
	m_ipa = 0;
	m_left = 0;
	m_port = 0;
	m_uploaded = 0;
	m_num_want = -1;

	// TorrentPier begin
	m_ipv6set = false;
	m_family = family;
	m_protocol = 0;
	// TorrentPier end
}

void Ctracker_input::set(const std::string& name, const std::string& value)
{
	if (name.empty())
		return;
	switch (name[0])
	{
	case 'c':
		if (name == "compact")
			m_compact = atoi(value.c_str());
		break;
	case 'd':
		if (name == "downloaded")
			m_downloaded = atoll(value.c_str());
		break;
	case 'e':
		if (name == "event")
		{
			if (value == "completed")
				m_event = e_completed;
			else if (value == "started")
				m_event = e_started;
			else if (value == "stopped")
				m_event = e_stopped;
			else if (value == "paused")
				m_event = e_paused;
			else
				m_event = e_none;
		}
		break;
	case 'i':
		if (name == "info_hash" && value.size() == 20)
		{
			m_info_hash = value;
			m_info_hashes.push_back(value);
		}

		// TorrentPier begin
		else if (name == "ip" || name == "ipv4")
			m_ipa = inet_addr(value.c_str());
		else if (name == "ipv6") {
			m_ipv6set = inet_pton(AF_INET6, value.c_str(), m_ipv6bin);
			if (m_ipv6bin[0] == '\xFE' && m_ipv6bin[1] == '\x80') m_ipv6set = false;
		}
		// TorrentPier end

		break;
	case 'l':
		if (name == "left")
			m_left = atoll(value.c_str());
		break;
	case 'n':
		if (name == "numwant")
			m_num_want = atoi(value.c_str());
		break;
	case 'p':
		if (name == "peer_id" && value.size() == 20)
			m_peer_id = value;
		else if (name == "port")
			m_port = htons(atoi(value.c_str()));

		// TorrentPier begin
		else if (name == "p")
			m_protocol = atoi(value.c_str());
		// TorrentPier end

		break;
	case 'u':
		if (name == "uploaded")
			m_uploaded = atoll(value.c_str());
		// TorrentPier begin
                else if (name == "uk")
                        m_passkey = value;
		// TorrentPier end
		break;
	}
}

bool Ctracker_input::valid() const
{
	return m_downloaded >= 0
		&& (m_event != e_completed || !m_left)
		&& m_info_hash.size() == 20
		&& m_left >= -1
		&& m_peer_id.size() == 20
		&& m_port >= 0
		&& m_uploaded >= 0;
}
