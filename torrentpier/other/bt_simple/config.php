<?php

if (!defined('TR_ROOT')) die(basename(__FILE__));

error_reporting(E_ALL);                            // Set php error reporting mode
set_magic_quotes_runtime(0);                       // Disable magic_quotes_runtime

// Tracker config
$tr_cfg = array();

// Garbage collector (run this script in cron each 5 minutes with '?run_gc=1' e.g. http://yoursite.com/announce.php?run_gc=1)
$tr_cfg['run_gc_key'] = 'run_gc';

// Announce interval
$tr_cfg['announce_interval']  = 1800;              // sec, min = 600

// Consider a peer dead if it has not announced in a number of seconds equal to this many times the calculated announce interval at the time of its last announcement
$tr_cfg['peer_expire_factor'] = 4;                 // min = 2;

$tr_cfg['numwant']            = 50;                // number of peers being sent to client
$tr_cfg['compact_mode']       = true;              // if TRUE - work only in compact mode (this will save huge amount of tracker traffic)

// IP
$tr_cfg['ignore_reported_ip'] = true;              // Ignore IP reported by client
$tr_cfg['verify_reported_ip'] = true;              // Verify IP reported by client against $_SERVER['HTTP_X_FORWARDED_FOR']
$tr_cfg['allow_internal_ip']  = false;             // Allow internal IP (10.xx.. etc.)

// Cache
define('PEERS_LIST_PREFIX', '');
define('PEERS_LIST_EXPIRE', round(0.7 * $tr_cfg['announce_interval']));  // sec

$tr_cfg['tr_cache_type'] = 'sqlite';               // Available cache types: none, sqlite

$tr_cfg['tr_cache']['sqlite'] = array(
	'db_file_path' => '/dev/shm/bt_cache_sqlite.db', // preferable on tmpfs
	'table_name'   => 'cache',
	'table_schema' => 'CREATE TABLE cache (
	                     cache_name        VARCHAR(255),
	                     cache_expire_time INT,
	                     cache_value       TEXT,
	                     PRIMARY KEY (cache_name)
	                   )',
	'pconnect'     => true,
	'con_required' => true,
	'log_name'     => 'CACHE',
);

// DB
$tr_cfg['tr_db_type'] = 'sqlite';                  // Available db types: sqlite

$tr_cfg['tr_db']['sqlite'] = array(
	'db_file_path' => '/dev/shm/bt_tracker_sqlite.db', // preferable on tmpfs
	'table_name'   => 'tracker',
	'table_schema' => 'CREATE TABLE tracker (
	                     info_hash   CHAR(20),
	                     ip          CHAR(8),
	                     port        INT,
	                     update_time INT,
	                     PRIMARY KEY (info_hash, ip, port)
	                   )',
	'pconnect'     => true,
	'con_required' => true,
	'log_name'     => 'TRACKER',
);

// Debug
define('DEBUG',     false);
define('DBG_LOG',   false);
define('SQL_DEBUG', false);

// Misc
define('DUMMY_PEER', pack('Nn', ip2long('10.254.254.247'), 64765));
