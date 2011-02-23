#!/usr/bin/perl

$HOST = "site.ru"; # mail host
$NAME_IN_HOST = "webmaster"; # $ADDR+@+$HOST = (webmaster@site.ru)

use DBI;
use MIME::Base64;

$max_count = 1000;
$count = 0;

$dsn = "DBI:mysql:database=forum;host=localhost;port=3306".
#		"mysql_auto_reconnect=1;".
#		"mysql_enable_utf8=1;".
		"mysql_compression=1;";
$dbh = DBI->connect($dsn, 'login', '******');

# Set charset cp1251
$dbh->do("SET character_set_client = cp1251;");
$dbh->do("SET character_set_connection = cp1251;");
$dbh->do("SET character_set_results = cp1251;");

$sth = $dbh->prepare("SELECT bulk_id, last_user_id, mail_subject, mail_body, group_id
	FROM bb_bulkmail where bulk_complete = 0 limit 1");
$sth->execute;
if (my $ref = $sth->fetchrow_hashref())
{
	$bulk_id = $ref->{'bulk_id'};
	$group_id = $ref->{'group_id'};
	$last_user_id = $ref->{'last_user_id'};

	$bid = 'b' . $bulk_id . 's' . $last_user_id;
	$fn = '/root/bulkmail/queue/' . $bid . '.bsmtp';
	open(FH, "> $fn") || die $!;
	print FH "MAIL FROM:<$NAME_IN_HOST\@$HOST>\n";

	$mail_subject = $ref->{'mail_subject'};
	$mail_body = $ref->{'mail_body'};
	$mail_body =~ s/\r//g;
	$mail_body =~ s/^\./../mg;
	$mail_subject = encode_base64($mail_subject);
	$mail_subject =~ s/^\s*(.*?)\s*$/ =?windows-1251?B?$1?=/gm;

	$group_sql = $group_id > 0 ? "JOIN bb_user_group ug ON u.user_id=ug.user_id AND ug.group_id=$group_id" : '';

	$sql2 = "SELECT u.user_id, u.user_email
		FROM bb_users u $group_sql
		WHERE u.user_id > $last_user_id AND u.user_active = 1 AND no_spam = 0
		AND user_email like '%\@%' AND user_email not like '%\@-%'
		ORDER BY u.user_id
		LIMIT $max_count";

	$sth2 = $dbh->prepare($sql2);
	$sth2->execute;
	while (my $ref2 = $sth2->fetchrow_hashref()) {
		$user_id = $ref2->{'user_id'};
		$user_email = $ref2->{'user_email'};
		$count++;
		$last_user_id = $user_id if $user_id > $last_user_id;
		print FH "RCPT TO:<$user_email>\n";
	}
	$sth2->finish;

	print FH "DATA\n";
	print FH "From: $HOST <$NAME_IN_HOST\@$HOST>\n";
	print FH "To: Members <users\@$HOST>\n";
	print FH "Subject:$mail_subject\n";
	print FH "Message-ID: <$bid\@$HOST>\n";
	print FH "MIME-Version: 1.0\n";
	print FH "Content-Type: text/plain; charset=windows-1251\n";
	print FH "Content-Transfer-Encoding: 8bit\n";
	print FH "X-Priority: 3\n";
	print FH "X-MSMail-Priority: Normal\n";
	print FH "X-Mailer: $HOST Mail\n";
	print FH "\n";
	print FH "$mail_body\n";
	print FH ".\n";
	close FH;

	system ("/usr/sbin/exim -bS <$fn >$fn.log") if $count > 0;

	$sql_complete = $count < $max_count ? ", bulk_complete = 1" : '';
	$sql2 = "UPDATE bb_bulkmail
		SET last_user_id = $last_user_id $sql_complete
		WHERE bulk_id = $bulk_id";
	$dbh->do($sql2);
}
$sth->finish;
