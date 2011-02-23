<?php

/*
	This file is part of TorrentPier

	TorrentPier is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	TorrentPier is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	A copy of the GPL 2.0 should have been included with the program.
	If not, see http://www.gnu.org/licenses/

	Official SVN repository and contact information can be found at
	http://code.google.com/p/torrentpier/
 */

define('TIMESTART', utime());
define('TIMENOW',   time());

if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) die();

if (!defined('BB_ROOT')) define('BB_ROOT', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
if (!defined('IN_PHPBB') && !defined('IN_TRACKER')) define('IN_PHPBB', true);

if (!isset($phpEx)) $phpEx = PHP_EXT;
$phpbb_root_path = BB_ROOT;

// Get initial config
require(BB_ROOT .'config.'. PHP_EXT);
require(BB_ROOT .'config_mods.'. PHP_EXT);

if (empty($dbcharset)) $dbcharset = 'utf8';

// Debug options
define('DBG_USER', (isset($_COOKIE[COOKIE_DBG]) || DEBUG === true));

if (DBG_LOG) dbg_log(' ', '__hits__');

// Board/Tracker shared constants and functions
define('BT_TORRENTS_TABLE',      $table_prefix .'bt_torrents');
define('BT_TRACKER_TABLE',       $table_prefix .'bt_tracker');
define('BT_TRACKER_SNAP_TABLE',  $table_prefix .'bt_tracker_snap');
define('BT_USERS_TABLE',         $table_prefix .'bt_users');

define('BT_AUTH_KEY_LENGTH', 10);

define('DL_STATUS_RELEASER', -1);
define('DL_STATUS_DOWN',      0);
define('DL_STATUS_COMPLETE',  1);
define('DL_STATUS_CANCEL',    3);
define('DL_STATUS_WILL',      4);

define('TOR_TYPE_GOLD',       1);
define('TOR_TYPE_SILVER',     2);

define('ANONYMOUS', -1);

// Cache
define('PEER_HASH_PREFIX',  'peer_');
define('PEERS_LIST_PREFIX', 'peers_list_');

define('PEER_HASH_EXPIRE',  round($bb_cfg['announce_interval'] * (0.85*$tr_cfg['expire_factor'])));  // sec
define('PEERS_LIST_EXPIRE', round($bb_cfg['announce_interval'] * 0.7));  // sec

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

class cache_eaccelerator extends cache_common
{
	var $used = true;

	function cache_eaccelerator ()
	{
		if (!$this->is_installed())
		{
			die('Error: eAccelerator extension not installed');
		}
	}

	function get ($name)
	{
		return eaccelerator_get($name);
	}

	function set ($name, $value, $ttl = 0)
	{
		return eaccelerator_put($name, $value, $ttl);
	}

	function rm ($name)
	{
		return eaccelerator_rm($name);
	}

	function is_installed ()
	{
		return function_exists('eaccelerator_get');
	}
}

class cache_apc extends cache_common
{
	var $used = true;

	function cache_apc ()
	{
		if (!$this->is_installed())
		{
			die('Error: APC extension not installed');
		}
	}

	function get ($name)
	{
		return apc_fetch($name);
	}

	function set ($name, $value, $ttl = 0)
	{
		return apc_store($name, $value, $ttl);
	}

	function rm ($name)
	{
		return apc_delete($name);
	}

	function is_installed ()
	{
		return function_exists('apc_fetch');
	}
}

class cache_memcached extends cache_common
{
	var $used      = true;

	var $cfg       = null;
	var $memcache  = null;
	var $connected = false;

	function cache_memcached ($cfg)
	{
		global $bb_cfg;

		if (!$this->is_installed())
		{
			die('Error: Memcached extension not installed');
		}

		$this->cfg = $cfg;
		$this->memcache = new Memcache;
	}

	function connect ()
	{
		$connect_type = ($this->cfg['pconnect']) ? 'pconnect' : 'connect';

		if (@$this->memcache->$connect_type($this->cfg['host'], $this->cfg['port']))
		{
			$this->connected = true;
		}

		if (DBG_LOG) dbg_log(' ', 'CACHE-connect'. ($this->connected ? '' : '-FAIL'));

		if (!$this->connected && $this->cfg['con_required'])
		{
			die('Could not connect to memcached server');
		}
	}

	function get ($name)
	{
		if (!$this->connected) $this->connect();
		return ($this->connected) ? $this->memcache->get($name) : false;
	}

	function set ($name, $value, $ttl = 0)
	{
		if (!$this->connected) $this->connect();
		return ($this->connected) ? $this->memcache->set($name, $value, false, $ttl) : false;
	}

	function rm ($name)
	{
		if (!$this->connected) $this->connect();
		return ($this->connected) ? $this->memcache->delete($name, 0) : false;
	}

	function is_installed ()
	{
		return class_exists('Memcache');
	}
}

class cache_xcache extends cache_common
{
	var $used = true;

	function cache_xcache ()
	{
		if (!$this->is_installed())
		{
			die('Error: XCache extension not installed');
		}
	}

	function get ($name)
	{
		return xcache_get($name);
	}

	function set ($name, $value, $ttl = 0)
	{
		return xcache_set($name, $value, $ttl);
	}

	function rm ($name)
	{
		return xcache_unset($name);
	}

	function is_installed ()
	{
		return function_exists('xcache_get');
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

class cache_file extends cache_common
{
	var $used = true;

	var $dir  = null;

	function cache_file ($dir)
	{
		$this->dir = $dir;
	}

	function get ($name)
	{
		$filename = $this->dir . clean_filename($name) . '.'. PHP_EXT;
		
		if(file_exists($filename)) 
		{
			require($filename);
		}		

		return (!empty($filecache['value'])) ? $filecache['value'] : false;
	}

	function set ($name, $value, $ttl = 86400)
	{
		if (!function_exists('var_export'))
		{
			return false;
		}
		
		$filename   = $this->dir . clean_filename($name) . '.'. PHP_EXT;
		$expire     = TIMENOW + $ttl;
		$cache_data = array(
			'expire'  => $expire,
			'value'   => $value,
		);

		$filecache = "<?php\n";
		$filecache .= "if (!defined('BB_ROOT')) die(basename(__FILE__));\n";
		$filecache .= '$filecache = ' . var_export($cache_data, true) . ";\n";
		$filecache .= '?>';		

		return (bool) file_write($filecache, $filename, false, true, true);
	}

	function rm ($name)
	{
		$filename   = $this->dir . clean_filename($name) . '.' . PHP_EXT;
		if (file_exists($filename))
		{
			return (bool) unlink($filename);
		}
		return false;
	}

	function gc ($expire_time = TIMENOW)
	{
		$dir = $this->dir;
		
		if (is_dir($dir)) 
		{
			if ($dh = opendir($dir)) 
			{
				while (($file = readdir($dh)) !== false) 
				{
					if ($file != "." && $file != "..") 
					{ 
						$filename = $dir . $file;
					
						require($filename);
					
						if(!empty($filecache['expire']) && ($filecache['expire'] < $expire_time))
						{
							unlink($filename);
						}
					}
				}
				closedir($dh);
			}
		}

		return;
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
	var $dbh       = null;
	var $connected = false;

	var $num_queries    = 0;
	var $sql_starttime  = 0;
	var $sql_inittime   = 0;
	var $sql_timetotal  = 0;
	var $cur_query_time = 0;

	var $dbg            = array();
	var $dbg_id         = 0;
	var $dbg_enabled    = false;
	var $cur_query      = null;

	var $table_create_attempts = 0;

	function sqlite_common ($cfg)
	{
		if (!function_exists('sqlite_open')) die('Error: Sqlite extension not installed');
		$this->cfg = array_merge($this->cfg, $cfg);
		$this->dbg_enabled = (SQL_DEBUG && DBG_USER && !empty($_COOKIE['sql_log']));
	}

	function connect ()
	{
		$this->cur_query = 'connect';
		$this->debug('start');

		$connect_type = ($this->cfg['pconnect']) ? 'sqlite_popen' : 'sqlite_open';

		if (@$this->dbh = $connect_type($this->cfg['db_file_path'], 0666, $sqlite_error))
		{
			$this->connected = true;
		}

		if (DBG_LOG) dbg_log(' ', $this->cfg['log_name'] .'-connect'. ($this->connected ? '' : '-FAIL'));

		if (!$this->connected && $this->cfg['con_required'])
		{
			trigger_error($sqlite_error, E_USER_ERROR);
		}

		$this->debug('stop');
		$this->cur_query = null;
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

		$this->cur_query = $query;
		$this->debug('start');

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

		$this->debug('stop');
		$this->cur_query = null;

		$this->num_queries++;

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

	function debug ($mode)
	{
		if (!$this->dbg_enabled) return;

		$id  =& $this->dbg_id;
		$dbg =& $this->dbg[$id];

		if ($mode == 'start')
		{
			$this->sql_starttime = utime();

			$dbg['sql']  = $this->cur_query;
			$dbg['src']  = $this->debug_find_source();
			$dbg['file'] = $this->debug_find_source('file');
			$dbg['line'] = $this->debug_find_source('line');
			$dbg['time'] = '';
		}
		else if ($mode == 'stop')
		{
			$this->cur_query_time = utime() - $this->sql_starttime;
			$this->sql_timetotal += $this->cur_query_time;
			$dbg['time'] = $this->cur_query_time;
			$id++;
		}
	}

	function debug_find_source ($mode = '')
	{
		foreach (debug_backtrace() as $trace)
		{
			if ($trace['file'] !== __FILE__)
			{
				switch ($mode)
				{
					case 'file': return $trace['file'];
					case 'line': return $trace['line'];
					default: return hide_bb_path($trace['file']) .'('. $trace['line'] .')';
				}
			}
		}
		return null;
	}
}

switch ($bb_cfg['tr_cache_type'])
{
	case 'eaccelerator':
		$tr_cache = new cache_eaccelerator();
		break;

	case 'APC':
		$tr_cache = new cache_apc();
		break;

	case 'memcached':
		$tr_cache = new cache_memcached($bb_cfg['tr_cache']['memcached']);
		break;

	case 'xcache':
		$tr_cache = new cache_xcache();
		break;

	case 'sqlite':
		$tr_cache = new cache_sqlite($bb_cfg['tr_cache']['sqlite']);
		break;
		
	case 'filecache':
		$tr_cache = new cache_file(CACHE_DIR . $bb_cfg['tr_cache']['filecache']['path']);
		break;

	default:
		$tr_cache = new cache_common();
}

switch ($bb_cfg['bb_cache_type'])
{
	case 'same_as_tracker':
		$bb_cache =& $tr_cache;
		break;

	case 'eaccelerator':
		$bb_cache = new cache_eaccelerator();
		break;

	case 'APC':
		$bb_cache = new cache_apc();
		break;

	case 'memcached':
		$bb_cache = new cache_memcached($bb_cfg['bb_cache']['memcached']);
		break;

	case 'xcache':
		$bb_cache = new cache_xcache();
		break;

	case 'sqlite':
		$bb_cache = new cache_sqlite($bb_cfg['bb_cache']['sqlite']);
		break;
		
	case 'filecache':
		$bb_cache = new cache_file(CACHE_DIR . $bb_cfg['bb_cache']['filecache']['path']);
		break;

	default:
		$bb_cache = new cache_common();
}

// Functions
function utime ()
{
	return array_sum(explode(' ', microtime()));
}

function bb_log ($msg, $file_name)
{
	if (is_array($msg))
	{
		$msg = join(LOG_LF, $msg);
	}
	$file_name .= (LOG_EXT) ? '.'. LOG_EXT : '';
	return file_write($msg, LOG_DIR . $file_name);
}

function dbg_log ($str, $file)
{
	if (!DBG_LOG) return;

	$dir = LOG_DIR . (defined('IN_PHPBB') ? 'dbg_bb/' : 'dbg_tr/') . date('m-d_H') .'/';
	return file_write($str, $dir . $file, false, false);
}

function file_write ($str, $file, $max_size = LOG_MAX_SIZE, $lock = true, $replace_content = false)
{
	$bytes_written = false;

	if ($max_size && @filesize($file) >= $max_size)
	{
		$old_name = $file; $ext = '';
		if (preg_match('#^(.+)(\.[^\\/]+)$#', $file, $matches))
		{
			$old_name = $matches[1]; $ext = $matches[2];
		}
		$new_name = $old_name .'_[old]_'. date('Y-m-d_H-i-s_') . getmypid() . $ext;
		clearstatcache();
		if (@file_exists($file) && @filesize($file) >= $max_size && !@file_exists($new_name))
		{
			@rename($file, $new_name);
		}
	}
	if (!$fp = @fopen($file, 'ab'))
	{
		if ($dir_created = bb_mkdir(dirname($file)))
		{
			$fp = @fopen($file, 'ab');
		}
	}
	if ($fp)
	{
		if ($lock)
		{
			@flock($fp, LOCK_EX);
		}
		if ($replace_content)
		{
			@ftruncate($fp, 0);
			@fseek($fp, 0, SEEK_SET);
		}
		$bytes_written = @fwrite($fp, $str);
		@fclose($fp);
	}

	return $bytes_written;
}

function bb_mkdir ($path, $mode = 0777)
{
	$old_um = umask(0);
	$dir = mkdir_rec($path, $mode);
	umask($old_um);
	return $dir;
}

function mkdir_rec ($path, $mode)
{
	if (is_dir($path))
	{
		return ($path !== '.' && $path !== '..') ? is_writable($path) : false;
	}
	else
	{
		return (mkdir_rec(dirname($path), $mode)) ? @mkdir($path, $mode) : false;
	}
}

function verify_id ($id, $length)
{
	return (preg_match('#^[a-zA-Z0-9]{'. $length .'}$#', $id) && is_string($id));
}

function clean_filename ($fname)
{
	static $s = array('\\', '/', ':', '*', '?', '"', '<', '>', '|', ' ');
	return str_replace($s, '_', str_compact($fname));
}

function encode_ip ($dotquad_ip)
{
	$ip_sep = explode('.', $dotquad_ip);
	if (count($ip_sep) == 4)
	{
		return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
	}

	$ip_sep = explode(':', preg_replace('/(^:)|(:$)/', '', $dotquad_ip));
	$res = '';
	foreach ($ip_sep as $x)
	{
		$res .= sprintf('%0'. ($x == '' ? (9 - count($ip_sep)) * 4 : 4) .'s', $x);
	}
	return $res;
}

function decode_ip ($int_ip)
{
	$int_ip = trim($int_ip);
	
	if (strlen($int_ip) == 32) 
	{
		$int_ip = substr(chunk_split($int_ip, 4, ':'), 0, 39);
		$int_ip = ':'. implode(':', array_map("hexhex", explode(':',$int_ip))) .':';
		preg_match_all("/(:0)+/", $int_ip, $zeros);
		if (count($zeros[0]) > 0) 
		{
			$match = '';
			foreach($zeros[0] as $zero)
				if (strlen($zero) > strlen($match))
					$match = $zero;
			$int_ip = preg_replace('/'. $match .'/', ':', $int_ip, 1);
		}
		return preg_replace('/(^:([^:]))|(([^:]):$)/', '$2$4', $int_ip);
	}
	if (strlen($int_ip) !== 8) $int_ip = '00000000';
	$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
	return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
}

function hexhex ($value)
{
	return dechex(hexdec($value));
}

function verify_ip ($ip)
{
	return preg_match('#^(\d{1,3}\.){3}\d{1,3}$#', $ip);
}

function str_compact ($str)
{
	return preg_replace('#\s+#', ' ', trim($str));
}

function make_rand_str ($len = 10)
{
	$str = '';
	while (strlen($str) < $len)
	{
		$str .= str_shuffle(preg_replace('#[^0-9a-zA-Z]#', '', crypt(uniqid(mt_rand(), true))));
	}
	return substr($str, 0, $len);
}

// bencode: based on OpenTracker [http://whitsoftdev.com/opentracker]
function bencode ($var)
{
	if (is_string($var))
	{
		return strlen($var) .':'. $var;
	}
	else if (is_int($var))
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
				if (!is_int($key))
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
		trigger_error('bencode error: wrong data type', E_USER_ERROR);
	}
}

function array_deep (&$var, $fn, $one_dimensional = false, $array_only = false)
{
	if (is_array($var))
	{
		foreach ($var as $k => $v)
		{
			if (is_array($v))
			{
				if ($one_dimensional)
				{
					unset($var[$k]);
				}
				else if ($array_only)
				{
					$var[$k] = $fn($v);
				}
				else
				{
					array_deep($var[$k], $fn);
				}
			}
			else if (!$array_only)
			{
				$var[$k] = $fn($v);
			}
		}
	}
	else if (!$array_only)
	{
		$var = $fn($var);
	}
}

function hide_bb_path ($path)
{
	return substr(str_replace(BB_PATH, '', $path), 1);
}

function tr_drop_request ($drop_type)
{
	if (DBG_LOG) dbg_log(' ', "request-dropped-$drop_type");
	dummy_exit(mt_rand(300, 900));
}

function get_loadavg ()
{
	if (is_callable('sys_getloadavg'))
	{
		$loadavg = join(' ', sys_getloadavg());
	}
	else if (strpos(PHP_OS, 'Linux') !== false)
	{
		$loadavg = @file_get_contents('/proc/loadavg');
	}

	return !empty($loadavg) ? $loadavg : 0;
}

function ver_compare ($version1, $operator, $version2)
{
	return version_compare($version1, $version2, $operator);
}

// Board init
if (defined('IN_PHPBB'))
{
	require(INC_DIR .'init_bb.'. PHP_EXT);
}
// Tracker init
else if (defined('IN_TRACKER'))
{
	define('DUMMY_PEER', pack('Nn', ip2long($_SERVER['REMOTE_ADDR']), !empty($_GET['port']) ? intval($_GET['port']) : mt_rand(1000, 65000)));
	
	function dummy_exit ($interval = 1800)
	{
		$output = bencode(array(
			'interval'     => (int)    $interval,
			'min interval' => (int)    $interval,
			'peers'        => (string) DUMMY_PEER,
		));

		die($output);
	}
	
	header('Content-Type: text/plain');
	header('Pragma: no-cache');

	if (STRIP_SLASHES)
	{
		array_deep($_GET, 'stripslashes');
	}

	if (!defined('IN_ADMIN'))
	{
		// Exit if tracker is disabled via ON/OFF trigger
		if (file_exists(BB_DISABLED))
		{
			dummy_exit(mt_rand(1200, 2400));  #  die('d14:failure reason20:temporarily disablede');
		}

		// Limit server load
		if ($bb_cfg['max_srv_load'] || $bb_cfg['tr_working_second'])
		{
			if ((!empty($_GET['uploaded']) || !empty($_GET['downloaded'])) && (!isset($_GET['event']) || $_GET['event'] === 'started'))
			{
				if ($bb_cfg['tr_working_second'] && (TIMENOW % $bb_cfg['tr_working_second']))
				{
					tr_drop_request('wrk_sec');
				}
				else if ($bb_cfg['max_srv_load'] && LOADAVG)
				{
					if (LOADAVG > $bb_cfg['max_srv_load'])
					{
						tr_drop_request('load');
					}
				}
			}
		}
	}
}
