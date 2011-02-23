<?php

define('TR_ROOT', './');

define('TIMESTART', utime());
define('TIMENOW',   time());

require(TR_ROOT .'config.php');

// ----------------------------------------------------------------------------
// Initialization
//
// Cache
switch ($tr_cfg['tr_cache_type'])
{
	case 'sqlite':
		$tr_cache = new cache_sqlite($tr_cfg['tr_cache']['sqlite']);
		break;
	default:
		$tr_cache = new cache_common();
}

// DB
switch ($tr_cfg['tr_db_type'])
{
	case 'sqlite':
		$db = new sqlite_common($tr_cfg['tr_db']['sqlite']);
		$db_random_function = 'random()';
		break;
	default:
		trigger_error('unsupported db type', E_USER_ERROR);
}

// Garbage collector
if (!empty($_GET[$tr_cfg['run_gc_key']]))
{
	$announce_interval = max(intval($tr_cfg['announce_interval']), 600);
	$expire_factor     = max(intval($tr_cfg['peer_expire_factor']), 2);
	$peer_expire_time  = TIMENOW - floor($announce_interval * $expire_factor);

	$db->query("DELETE FROM tracker WHERE update_time < $peer_expire_time");

	if (method_exists($tr_cache, 'gc'))
	{
		$changes = $tr_cache->gc();
	}

	die();
}

// Recover info_hash
if (isset($_GET['?info_hash']) && !isset($_GET['info_hash']))
{
	$_GET['info_hash'] = $_GET['?info_hash'];
}

// Input var names
// String
$input_vars_str = array(
	'info_hash',
	'peer_id',
	'event',
);
// Numeric
$input_vars_num = array(
	'port',
	'uploaded',
	'downloaded',
	'left',
	'numwant',
	'compact',
);

// Init received data
// String
foreach ($input_vars_str as $var_name)
{
	$$var_name = isset($_GET[$var_name]) ? (string) $_GET[$var_name] : null;
}
// Numeric
foreach ($input_vars_num as $var_name)
{
	$$var_name = isset($_GET[$var_name]) ? (float) $_GET[$var_name] : null;
}

// Verify required request params (info_hash, peer_id, port, uploaded, downloaded, left)
if (!isset($info_hash) || strlen($info_hash) != 20)
{
	msg_die('Invalid info_hash');
}
if (!isset($peer_id) || strlen($peer_id) != 20)
{
	msg_die('Invalid peer_id');
}
if (!isset($port) || $port < 0 || $port > 0xFFFF)
{
	msg_die('Invalid port');
}
if (!isset($uploaded) || $uploaded < 0)
{
	msg_die('Invalid uploaded value');
}
if (!isset($downloaded) || $downloaded < 0)
{
	msg_die('Invalid downloaded value');
}
if (!isset($left) || $left < 0)
{
	msg_die('Invalid left value');
}

// IP
$ip = $_SERVER['REMOTE_ADDR'];

if (!$tr_cfg['ignore_reported_ip'] && isset($_GET['ip']) && $ip !== $_GET['ip'])
{
	if (!$tr_cfg['verify_reported_ip'])
	{
		$ip = $_GET['ip'];
	}
	else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))
	{
		foreach ($matches[0] as $x_ip)
		{
			if ($x_ip === $_GET['ip'])
			{
				if (!$tr_cfg['allow_internal_ip'] && preg_match("#^(10|172\.16|192\.168)\.#", $x_ip))
				{
					break;
				}
				$ip = $x_ip;
				break;
			}
		}
	}
}
// Check that IP format is valid
if (!verify_ip($ip))
{
	msg_die("Invalid IP: $ip");
}
// Convert IP to HEX format
$ip_sql = encode_ip($ip);

// ----------------------------------------------------------------------------
// Start announcer
//
$info_hash_sql = rtrim($db->escape($info_hash), ' ');

// Stopped event
if ($event === 'stopped')
{
	$db->query("
		DELETE FROM tracker
		WHERE info_hash = '$info_hash_sql'
		  AND ip = '$ip_sql'
		  AND port = $port
	");

	die();
}

// Update peer info
$db->query("
	REPLACE INTO tracker
		(info_hash, ip, port, update_time)
	VALUES
		('$info_hash_sql', '$ip_sql', $port, ". time() .")
");

// Get cached output
if (!$output = $tr_cache->get(PEERS_LIST_PREFIX . $info_hash))
{
	// Retrieve peers
	$numwant      = (int) $tr_cfg['numwant'];
	$compact_mode = ($tr_cfg['compact_mode'] || !empty($compact));

	$rowset = $db->fetch_rowset("
		SELECT ip, port
		FROM tracker
		WHERE info_hash = '$info_hash_sql'
		ORDER BY $db_random_function
		LIMIT $numwant
	");

	if ($compact_mode)
	{
		$peers = '';

		foreach ($rowset as $peer)
		{
			$peers .= pack('Nn', ip2long(decode_ip($peer['ip'])), $peer['port']);
		}
	}
	else
	{
		$peers = array();

		foreach ($rowset as $peer)
		{
			$peers[] = array(
				'ip'   => decode_ip($peer['ip']),
				'port' => intval($peer['port']),
			);
		}
	}

	$output = array(
		'interval'     => (int) $tr_cfg['announce_interval'],
		'min interval' => (int) $tr_cfg['announce_interval'],
		'peers'        => $peers,
	);

	$peers_list_cached = $tr_cache->set(PEERS_LIST_PREFIX . $info_hash, $output, PEERS_LIST_EXPIRE);
}

// Return data to client
echo bencode($output);

exit;

// ----------------------------------------------------------------------------
// Functions
//
function utime ()
{
	return array_sum(explode(' ', microtime()));
}

function msg_die ($msg)
{
	$output = bencode(array(
		'min interval'    => (int) 1800,
		'failure reason'  => (string) $msg,
	));

	die($output);
}

function dummy_exit ($interval = 1800)
{
	$output = bencode(array(
		'interval'     => (int)    $interval,
		'min interval' => (int)    $interval,
		'peers'        => (string) DUMMY_PEER,
	));

	die($output);
}

function encode_ip ($ip)
{
	$d = explode('.', $ip);

	return sprintf('%02x%02x%02x%02x', $d[0], $d[1], $d[2], $d[3]);
}

function decode_ip ($ip)
{
	return long2ip("0x{$ip}");
}

function verify_ip ($ip)
{
	return preg_match('#^(\d{1,3}\.){3}\d{1,3}$#', $ip);
}

function str_compact ($str)
{
	return preg_replace('#\s+#', ' ', trim($str));
}

// based on OpenTracker [http://whitsoftdev.com/opentracker]
function bencode ($var)
{
	if (is_int($var))
	{
		return 'i'. $var .'e';
	}
	else if (is_float($var))
	{
		return 'i'. sprintf('%.0f', $var) .'e';
	}
	else if (is_array($var))
	{
		if (count($var) == 0)
		{
			return 'de';
		}
		else
		{
			$assoc = false;

			foreach ($var as $key => $val)
			{
				if (!is_int($key) && !is_float($var))
				{
					$assoc = true;
					break;
				}
			}

			if ($assoc)
			{
				ksort($var, SORT_REGULAR);
				$ret = 'd';

				foreach ($var as $key => $val)
				{
					$ret .= bencode($key) . bencode($val);
				}
				return $ret .'e';
			}
			else
			{
				$ret = 'l';

				foreach ($var as $val)
				{
					$ret .= bencode($val);
				}
				return $ret .'e';
			}
		}
	}
	else
	{
		return strlen($var) .':'. $var;
	}
}

// Cache
class cache_common
{
	var $used = false;
	/**
	* Returns value of variable
	*/
	function get ($name)
	{
		return false;
	}
	/**
	* Store value of variable
	*/
	function set ($name, $value, $ttl = 0)
	{
		return false;
	}
	/**
	* Remove variable
	*/
	function rm ($name)
	{
		return false;
	}
}

class cache_sqlite extends cache_common
{
	var $used = true;

	var $cfg  = array();
	var $db   = null;

	function cache_sqlite ($cfg)
	{
		$this->cfg = array_merge($this->cfg, $cfg);
		$this->db = new sqlite_common($cfg);
	}

	function get ($name)
	{
		$result = $this->db->query("
			SELECT cache_value
			FROM ". $this->cfg['table_name'] ."
			WHERE cache_name = '". sqlite_escape_string($name) ."'
				AND cache_expire_time > ". TIMENOW ."
			LIMIT 1
		");

		return ($result AND $cache_value = sqlite_fetch_single($result)) ? unserialize($cache_value) : false;
	}

	function set ($name, $value, $ttl = 86400)
	{
		$name   = sqlite_escape_string($name);
		$expire = TIMENOW + $ttl;
		$value  = sqlite_escape_string(serialize($value));

		$result = $this->db->query("
			REPLACE INTO ". $this->cfg['table_name'] ."
				(cache_name, cache_expire_time, cache_value)
			VALUES
				('$name', '$expire', '$value')
		");

		return (bool) $result;
	}

	function rm ($name)
	{
		$result = $this->db->query("
			DELETE FROM ". $this->cfg['table_name'] ."
			WHERE cache_name = '". sqlite_escape_string($name) ."'
		");

		return (bool) $result;
	}

	function gc ($expire_time = TIMENOW)
	{
		$result = $this->db->query("
			DELETE FROM ". $this->cfg['table_name'] ."
			WHERE cache_expire_time < $expire_time
		");

		return ($result) ? sqlite_changes($this->db->dbh) : 0;
	}
}

class sqlite_common
{
	var $cfg = array(
	             'db_file_path' => 'sqlite.db',
	             'table_name'   => 'table_name',
	             'table_schema' => 'CREATE TABLE table_name (...)',
	             'pconnect'     => true,
	             'con_required' => true,
	             'log_name'     => 'SQLite',
	           );
	var $dbh                    = null;
	var $connected              = false;
	var $table_create_attempts  = 0;

	function sqlite_common ($cfg)
	{
		if (!function_exists('sqlite_open')) die('Error: Sqlite extension not installed');
		$this->cfg = array_merge($this->cfg, $cfg);
	}

	function connect ()
	{
		$connect_type = ($this->cfg['pconnect']) ? 'sqlite_popen' : 'sqlite_open';

		if (@$this->dbh = $connect_type($this->cfg['db_file_path'], 0666, $sqlite_error))
		{
			$this->connected = true;
		}
		if (!$this->connected && $this->cfg['con_required'])
		{
			trigger_error($sqlite_error, E_USER_ERROR);
		}
	}

	function create_table ()
	{
		$this->table_create_attempts++;
		$result = sqlite_query($this->dbh, $this->cfg['table_schema']);
		$msg = ($result) ? "{$this->cfg['table_name']} table created" : $this->get_error_msg();
		trigger_error($msg, E_USER_WARNING);
		return $result;
	}

	function query ($query, $type = 'unbuffered')
	{
		if (!$this->connected) $this->connect();

		$query_function = ($type === 'unbuffered') ? 'sqlite_unbuffered_query' : 'sqlite_query';

		if (!$result = $query_function($this->dbh, $query, SQLITE_ASSOC))
		{
			if (!$this->table_create_attempts && !sqlite_num_rows(sqlite_query($this->dbh, "PRAGMA table_info({$this->cfg['table_name']})")))
			{
				if ($this->create_table())
				{
					$result = $query_function($this->dbh, $query, SQLITE_ASSOC);
				}
			}
			if (!$result)
			{
				$this->trigger_error($this->get_error_msg());
			}
		}

		return $result;
	}

	function fetch_row ($query, $type = 'unbuffered')
	{
		$result = $this->query($query, $type);
		return is_resource($result) ? sqlite_fetch_array($result, SQLITE_ASSOC) : false;
	}

	function fetch_rowset ($query, $type = 'unbuffered')
	{
		$result = $this->query($query, $type);
		return is_resource($result) ? sqlite_fetch_all($result, SQLITE_ASSOC) : array();
	}

	function escape ($str)
	{
		return sqlite_escape_string($str);
	}

	function get_error_msg ()
	{
		return 'SQLite error #'. ($err_code = sqlite_last_error($this->dbh)) .': '. sqlite_error_string($err_code);
	}

	function trigger_error ($msg = 'DB Error')
	{
		if (error_reporting()) trigger_error($msg, E_USER_ERROR);
	}
}

