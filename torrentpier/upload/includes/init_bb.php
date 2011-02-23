<?php

##### LOG #####
#	bb_log(' ', 'hits/'. date('m-d') .'/'. date('H-') .'bb');
### LOG END ###

if (!defined('IN_PHPBB')) die(basename(__FILE__));
# if (PHP_VERSION < '4.3') die('TorrentPier requires PHP version 4.3+. Your PHP version '. PHP_VERSION);
if (!defined('BB_SCRIPT')) define('BB_SCRIPT', 'undefined');

// Exit if board is disabled via ON/OFF trigger
if (!defined('IN_ADMIN') && !defined('IN_INSTALL') && !defined('IN_AJAX') && !defined('IN_SERVICE'))
{
	if (file_exists(BB_DISABLED))
	{
		cron_release_deadlock();  // Если нужна разблокировка в случае залипания крона, отключающего форум
		header('HTTP/1.0 503 Service Unavailable');
		require(TEMPLATES_DIR .'board_disabled_exit.'. PHP_EXT);
	}
}

//
// Cron functions
//
function cron_release_deadlock ()
{
	if (file_exists(CRON_RUNNING))
	{
		if (TIMENOW - filemtime(CRON_RUNNING) > 2400)
		{
			cron_enable_board();
			cron_release_file_lock();
		}
	}
}

function cron_release_file_lock ()
{
	$lock_released = @rename(CRON_RUNNING, CRON_ALLOWED);
	cron_touch_lock_file(CRON_ALLOWED);
}

function cron_touch_lock_file ($lock_file)
{
	file_write(make_rand_str(20), $lock_file, 0, true, true);
}

function cron_enable_board ()
{
	@rename(BB_DISABLED, BB_ENABLED);
#	bb_update_config(array('board_disable' => 0));
}

function cron_disable_board ()
{
	@rename(BB_ENABLED, BB_DISABLED);
#	bb_update_config(array('board_disable' => 1));
}

// Define some basic configuration arrays
unset($stopwords, $synonyms_match, $synonyms_replace);
$userdata = $theme = $images = $lang = $nav_links = $bf = $attach_config = array();
$gen_simple_header = false;
$user = null;

// Obtain and encode user IP
$client_ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
$user_ip = encode_ip($client_ip);
define('CLIENT_IP', $client_ip);
define('USER_IP',   $user_ip);

function send_page ($contents)
{
	return compress_output($contents);
}

define('UA_GZIP_SUPPORTED', (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false));

function compress_output ($contents)
{
	global $bb_cfg;

	if ($bb_cfg['gzip_compress'] && GZIP_OUTPUT_ALLOWED && !defined('NO_GZIP'))
	{
		if ((UA_GZIP_SUPPORTED || $bb_cfg['gzip_force']) && strlen($contents) > 2000)
		{
			header('Content-Encoding: gzip');
			$contents = gzencode($contents, 1);
		}
	}

	return $contents;
}

// Start output buffering
if (!defined('IN_INSTALL') && !defined('IN_AJAX'))
{
	ob_start('send_page');
}

if (DEBUG === true)
{
	require(DEV_DIR .'init_debug.'. PHP_EXT);
#	if ($a);
#	trigger_error("error handler test", E_USER_ERROR);
}

// Config options
define('TPL_LIMIT_LOAD_EXIT', TEMPLATES_DIR .'limit_load_exit.'. PHP_EXT);

// Cookie params
$c = $bb_cfg['cookie_prefix'];
define('COOKIE_DATA',  $c .'data');
define('COOKIE_FORUM', $c .'f');
define('COOKIE_LOAD',  $c .'isl');
define('COOKIE_MARK',  $c .'mark_read');
define('COOKIE_TEST',  $c .'test');
define('COOKIE_TOPIC', $c .'t');
unset($c);

define('COOKIE_SESSION', 0);
define('COOKIE_EXPIRED', TIMENOW - 31536000);
define('COOKIE_PERSIST', TIMENOW + 31536000);

define('COOKIE_MAX_TRACKS', 90);

function bb_setcookie ($name, $val, $lifetime = COOKIE_PERSIST, $httponly = false)
{
	global $bb_cfg;

	$domain = $bb_cfg['cookie_domain'];

	if (PHP_VERSION < 5.2)
	{
	  // HttpOnly hack by Matt Mecham [http://blog.mattmecham.com/archives/2006/09/http_only_cookies_without_php.html]
	  $domain .= ($httponly) ? '; HttpOnly' : '';
	  return setcookie($name, $val, $lifetime, $bb_cfg['cookie_path'], $domain, $bb_cfg['cookie_secure']);
	}
	else
	{
	  return setcookie($name, $val, $lifetime, $bb_cfg['cookie_path'], $domain, $bb_cfg['cookie_secure'], $httponly);
	}
}

// Debug options
if (DBG_USER)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}

define('DELETED', -1);

// User Levels <- Do not change the values of USER or ADMIN
define('USER',         0);
define('ADMIN',        1);
define('MOD',          2);
define('GROUP_MEMBER', 20);

$excluded_users = array(
	ANONYMOUS,
	BOT_UID,
);
define('EXCLUDED_USERS_CSV', implode(',', $excluded_users));

// User related
define('USER_ACTIVATION_NONE',  0);
define('USER_ACTIVATION_SELF',  1);
define('USER_ACTIVATION_ADMIN', 2);

define('USER_AVATAR_NONE',    0);
define('USER_AVATAR_UPLOAD',  1);
define('USER_AVATAR_REMOTE',  2);
define('USER_AVATAR_GALLERY', 3);

// Group settings
define('GROUP_OPEN',   0);
define('GROUP_CLOSED', 1);
define('GROUP_HIDDEN', 2);

// Forum state
define('FORUM_UNLOCKED', 0);
define('FORUM_LOCKED',   1);

// Topic status
define('TOPIC_UNLOCKED',          0);
define('TOPIC_LOCKED',            1);
define('TOPIC_MOVED',             2);

define('TOPIC_WATCH_NOTIFIED',    1);
define('TOPIC_WATCH_UN_NOTIFIED', 0);

// Topic types
define('POST_NORMAL',          0);
define('POST_STICKY',          1);
define('POST_ANNOUNCE',        2);
define('POST_GLOBAL_ANNOUNCE', 3);

// Search types
define('SEARCH_TYPE_POST',     0);
define('SEARCH_TYPE_TRACKER',  1);

// Error codes
define('GENERAL_MESSAGE',      200);
define('GENERAL_ERROR',        202);
define('CRITICAL_MESSAGE',     203);
define('CRITICAL_ERROR',       204);

define('E_AJAX_GENERAL_ERROR', 1000);
define('E_AJAX_NEED_LOGIN',    1001);

// Private messaging
define('PRIVMSGS_READ_MAIL',      0);
define('PRIVMSGS_NEW_MAIL',       1);
define('PRIVMSGS_SENT_MAIL',      2);
define('PRIVMSGS_SAVED_IN_MAIL',  3);
define('PRIVMSGS_SAVED_OUT_MAIL', 4);
define('PRIVMSGS_UNREAD_MAIL',    5);

// URL PARAMETERS (hardcoding allowed)
define('POST_CAT_URL',    'c');
define('POST_FORUM_URL',  'f');
define('POST_GROUPS_URL', 'g');
define('POST_POST_URL',   'p');
define('POST_TOPIC_URL',  't');
define('POST_USERS_URL',  'u');

// Download Modes
define('INLINE_LINK',   1);
define('PHYSICAL_LINK', 2);

// Categories
define('NONE_CAT',   0);
define('IMAGE_CAT',  1);
define('STREAM_CAT', 2);
define('SWF_CAT',    3);

// Misc
define('MEGABYTE',              1024);
define('ADMIN_MAX_ATTACHMENTS', 50);
define('THUMB_DIR',             'thumbs');
define('MODE_THUMBNAIL',        1);

// Forum Extension Group Permissions
define('GPERM_ALL', 0); // ALL FORUMS

// Quota Types
define('QUOTA_UPLOAD_LIMIT', 1);
define('QUOTA_PM_LIMIT',     2);

// Torrents
define('TOR_STATUS_NORMAL', 0);
define('TOR_STATUS_FROZEN', 1);

// Report
// Report status constants
define('REPORT_NEW', 0);
define('REPORT_OPEN', 1);
define('REPORT_IN_PROCESS', 2);
define('REPORT_CLEARED', 3);
define('REPORT_DELETE', 4);
// Report authorisation constants
define('REPORT_AUTH_USER', 0);
define('REPORT_AUTH_MOD', 1);
define('REPORT_AUTH_CONFIRM', 2);
define('REPORT_AUTH_ADMIN', 3);
// Report notification constants
define('REPORT_NOTIFY_NEW', 1);
define('REPORT_NOTIFY_CHANGE', 2);
// Other report constants
define('POST_REPORT_URL', 'r');
define('POST_REPORT_REASON_URL', 'r');
// Report [END]

// Table names
$b = $buffer_prefix;
$t = $table_prefix;

define('BUF_TOPIC_VIEW_TABLE',       $b .'topic_view');
define('BUF_LAST_SEEDER_TABLE',      $b .'last_seeder');

define('ADS_TABLE',                  $t .'ads');
define('ATTACH_CONFIG_TABLE',        $t .'attachments_config');
define('ATTACHMENTS_DESC_TABLE',     $t .'attachments_desc');
define('ATTACHMENTS_TABLE',          $t .'attachments');
define('AUTH_ACCESS_SNAP_TABLE',     $t .'auth_access_snap');
define('AUTH_ACCESS_TABLE',          $t .'auth_access');
define('BANLIST_TABLE',              $t .'banlist');
define('BT_DLSTATUS_MAIN_TABLE',     $t .'bt_dlstatus_main');
define('BT_DLSTATUS_NEW_TABLE',      $t .'bt_dlstatus_new');
define('BT_DLSTATUS_SNAP_TABLE',     $t .'bt_dlstatus_snap');
define('BT_DLSTATUS_TABLE',          $t .'bt_dlstatus_mrg');   // main + new
define('BT_LAST_TORSTAT_TABLE',      $t .'bt_last_torstat');
define('BT_LAST_USERSTAT_TABLE',     $t .'bt_last_userstat');
define('BT_TORHELP_TABLE',           $t .'bt_torhelp');
define('BT_TORSTAT_TABLE',           $t .'bt_torstat');
define('BT_USER_SETTINGS_TABLE',     $t .'bt_user_settings');
define('CATEGORIES_TABLE',           $t .'categories');
define('CONFIG_TABLE',               $t .'config');
define('CONFIRM_TABLE',              $t .'confirm');
define('COUNTRIES_TABLE',            $t .'countries');
define('CRON_TABLE',                 $t .'cron');
define('DATASTORE_TABLE',            $t .'datastore');
define('DISALLOW_TABLE',             $t .'disallow');
define('EXTENSION_GROUPS_TABLE',     $t .'extension_groups');
define('EXTENSIONS_TABLE',           $t .'extensions');
define('FORUMS_TABLE',               $t .'forums');
define('GROUPS_TABLE',               $t .'groups');
define('LOG_TABLE',                  $t .'log');
define('POSTS_SEARCH_TABLE',         $t .'posts_search');
define('POSTS_TABLE',                $t .'posts');
define('POSTS_TEXT_TABLE',           $t .'posts_text');
define('POSTS_HTML_TABLE',           $t .'posts_html');
define('PRIVMSGS_TABLE',             $t .'privmsgs');
define('PRIVMSGS_TEXT_TABLE',        $t .'privmsgs_text');
define('QUOTA_LIMITS_TABLE',         $t .'quota_limits');
define('QUOTA_TABLE',                $t .'attach_quota');
define('RANKS_TABLE',                $t .'ranks');
// Report
define('REPORTS_TABLE',              $t .'reports');
define('REPORTS_CHANGES_TABLE',      $t .'reports_changes');
define('REPORTS_MODULES_TABLE',      $t .'reports_modules');
define('REPORTS_REASONS_TABLE',      $t .'reports_reasons');
// Report [END]
define('SEARCH_REBUILD_TABLE',       $t .'search_rebuild');
define('SEARCH_TABLE',               $t .'search_results');
define('SESSIONS_TABLE',             $t .'sessions');
define('SMILIES_TABLE',              $t .'smilies');
define('TOPIC_TPL_TABLE',            $t .'topic_templates');
define('TOPICS_TABLE',               $t .'topics');
define('TOPICS_WATCH_TABLE',         $t .'topics_watch');
define('USER_GROUP_TABLE',           $t .'user_group');
define('USERS_TABLE',                $t .'users');
define('VOTE_DESC_TABLE',            $t .'vote_desc');
define('VOTE_RESULTS_TABLE',         $t .'vote_results');
define('VOTE_USERS_TABLE',           $t .'vote_voters');
define('WORDS_TABLE',                $t .'words');
// dj_maxx: add visual confirmation to login form
define('UNTRUSTED_IPS_TABLE',        $t.'untrusted_ips');
// end of
unset($t, $b);

define('TORRENT_EXT', 'torrent');

define('TOPIC_DL_TYPE_NORMAL', 0);
define('TOPIC_DL_TYPE_DL',     1);

define('SHOW_PEERS_COUNT', 1);
define('SHOW_PEERS_NAMES', 2);
define('SHOW_PEERS_FULL',  3);

define('SEARCH_ID_LENGTH', 12);
define('SID_LENGTH',       20);
define('LOGIN_KEY_LENGTH', 12);

define('PAGE_HEADER', INC_DIR .'page_header.'. PHP_EXT);
define('PAGE_FOOTER', INC_DIR .'page_footer.'. PHP_EXT);

define('CAT_URL',      "index.$phpEx?"     .'c=');
define('DOWNLOAD_URL', "download.$phpEx?"  .'id=');
define('FORUM_URL',    "viewforum.$phpEx?" .'f=');
define('GROUP_URL',    "groupcp.$phpEx?"   .'g=');
define('LOGIN_URL',    "login.$phpEx?"     .'redirect=');
define('MODCP_URL',    "modcp.$phpEx?"     .'f=');
define('PM_URL',       "privmsg.$phpEx?"   .'mode=post&amp;u=');
define('POST_URL',     "viewtopic.$phpEx?" .'p=');
define('PROFILE_URL',  "profile.$phpEx?"   .'mode=viewprofile&amp;u=');
define('TOPIC_URL',    "viewtopic.$phpEx?" .'t=');

define('USER_AGENT', @strtolower($_SERVER['HTTP_USER_AGENT']));
define('UA_OPERA',   strpos(USER_AGENT, 'pera'));
define('UA_IE',      strpos(USER_AGENT, 'msie'));

define('HTML_SELECT_MAX_LENGTH', 60);
define('HTML_WBR_LENGTH', 12);

define('HTML_CHECKED',  ' checked="checked" ');
define('HTML_DISABLED', ' disabled="disabled" ');
define('HTML_READONLY', ' readonly="readonly" ');
define('HTML_SELECTED', ' selected="selected" ');

define('HTML_SF_SPACER', '&nbsp;|-&nbsp;');

// $GPC
define('KEY_NAME', 0);   // position in $GPC['xxx']
define('DEF_VAL',  1);
define('GPC_TYPE', 2);

define('GET',     1);
define('POST',    2);
define('COOKIE',  3);
define('REQUEST', 4);
define('CHBOX',   5);
define('SELECT',  6);

if (!empty($banned_user_agents))
{
	foreach ($banned_user_agents as $agent)
	{
		if (strstr(USER_AGENT, $agent))
		{
/*##### LOG #####
$file = 'ban/user_agents_'. date('m-d');
$str = array();
$str[] = date('H:i:s');
$str[] = @$_SERVER['HTTP_USER_AGENT'];
$str[] = @$_SERVER['REMOTE_ADDR'];
$str[] = @$_SERVER['REQUEST_URI'];
$str[] = @$_SERVER['HTTP_REFERER'];
bb_log($str, $file);
### LOG END ###*/

			$filename = 'Skachivajte fajly brauzerom (скачивайте файлы браузером)';
			$output = '@';

			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="'. $filename .'"');

			die($output);
		}
	}
}

// Functions
function send_no_cache_headers ()
{
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: '. gmdate('D, d M Y H:i:s'). ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
}

function bb_exit ($output = '')
{
	if ($output)
	{
		echo $output;
	}
	exit;
}

// Exit if server overloaded
if (!(defined('IN_PROFILE') || defined('IN_LOGIN') || defined('IN_ADMIN') || defined('IN_AJAX') || defined('IN_SERVICE')) && BB_ROOT == './')
{
	if ($bb_cfg['max_srv_load'] && empty($_POST['message']) && !empty($_COOKIE[COOKIE_LOAD]) && LOADAVG)
	{
		if (LOADAVG > $bb_cfg['max_srv_load'] && (TIMENOW - $_COOKIE[COOKIE_LOAD]) > $bb_cfg['user_session_duration'])
		{
			require(TPL_LIMIT_LOAD_EXIT);
		}
	}
}

function prn_r ($var, $title = '', $print = true)
{
	$r = '<pre>'. (($title) ? "<b>$title</b>\n\n" : '') . htmlspecialchars(print_r($var, true)) .'</pre>';
	if ($print) echo $r;
	return $r;
}

function prn ()
{
	if (!DBG_USER) return;
	foreach (func_get_args() as $var) prn_r($var);
}

function vdump ($var, $title = '')
{
	echo '<pre>'. (($title) ? "<b>$title</b>\n\n" : '');
	var_dump($var);
	echo '</pre>';
}

function htmlCHR ($txt, $replace_space = false)
{
	return ($replace_space) ? str_replace(' ', '&nbsp;', htmlspecialchars($txt, ENT_QUOTES)) : htmlspecialchars($txt, ENT_QUOTES);
}

function make_url ($path)
{
	global $bb_cfg;

	$server_protocol = ($bb_cfg['cookie_secure']) ? 'https://' : 'http://';
	$server_port = ($bb_cfg['server_port'] != 80) ? ':'. $bb_cfg['server_port'] : '';
	$path = preg_replace('#^\/?(.*?)\/?$#', '\1', $path);

	return $server_protocol . $bb_cfg['server_name'] . $server_port . $bb_cfg['script_path'] . $path;
}

// PHP5 with register_long_arrays off?
if (PHP_VERSION >= 5 && !ini_get('register_long_arrays'))
{
	$HTTP_POST_VARS   = $_POST;
	$HTTP_GET_VARS    = $_GET;
	$HTTP_SERVER_VARS = $_SERVER;
	$HTTP_COOKIE_VARS = $_COOKIE;
	$HTTP_ENV_VARS    = $_ENV;
	$HTTP_POST_FILES  = $_FILES;
}

if (STRIP_SLASHES)
{
	array_deep($_GET,     'stripslashes');
	array_deep($_POST,    'stripslashes');
	array_deep($_COOKIE,  'stripslashes');
	array_deep($_REQUEST, 'stripslashes');
	array_deep($_SERVER,  'stripslashes');
	array_deep($_ENV,     'stripslashes');
	array_deep($_FILES,   'stripslashes');
}
else
{
	array_deep($HTTP_GET_VARS,    'addslashes');
	array_deep($HTTP_POST_VARS,   'addslashes');
	array_deep($HTTP_COOKIE_VARS, 'addslashes');
}

require(INC_DIR .'functions.'. PHP_EXT);
require(INC_DIR .'sessions.'.  PHP_EXT);
require(INC_DIR .'template.'.  PHP_EXT);
require(INC_DIR .'db/mysql.'.  PHP_EXT);

if (DBG_USER) require(INC_DIR .'functions_dev.'. PHP_EXT);

// Make the database connection.
$db = new sql_db(array(
	'dbms'        => $dbms,
	'dbhost'      => $dbhost,
	'dbname'      => $dbname,
	'dbuser'      => $dbuser,
	'dbpasswd'    => $dbpasswd,
	'charset'     => $dbcharset,
	'collation'   => $dbcollation,
	'persist'     => $pconnect,
));
unset($dbpasswd);

// Setup forum wide options
$board_config =& $bb_cfg;

if (defined('IN_INSTALL'))
{
	$bb_cfg['cron_enabled'] = false;
}
else
{
	$bb_cfg = array_merge(bb_get_config(CONFIG_TABLE), $bb_cfg);

	$bb_cfg['cookie_name']      = $bb_cfg['cookie_prefix'];
	$bb_cfg['board_dateformat'] = $bb_cfg['default_dateformat'];
	$bb_cfg['board_lang']       = $bb_cfg['default_lang'];

	if (file_exists("install/install.$phpEx"))
	{
		message_die(GENERAL_MESSAGE, 'Please_remove_install_contrib');
	}
}

$user = new user_common();
$userdata =& $user->data;

$html = new html_common();
$log_action = new log_action();

$ads = new ads_common();

// Initialize Datastore
switch ($bb_cfg['datastore_type'])
{
	case 'sqlite':
		$datastore = new datastore_sqlite($bb_cfg['datastore']['sqlite']);
		break;
		
	case 'eaccelerator':
		$datastore = new datastore_eaccelerator();
		break;
		
	case 'memcached':
		$datastore = new datastore_memcached($bb_cfg['datastore']['memcached']);
		break;
		
	case 'xcache':
		$datastore = new datastore_xcache();
		break;
		
	case 'APC':
		$datastore = new datastore_apc();
		break;
		
	case 'filecache':
		$datastore = new datastore_file(CACHE_DIR . $bb_cfg['datastore']['filecache']['path']);
		break;
		
	default:
		$datastore = new datastore_mysql();
}

// !!! Temporarily (??) 'cat_forums' always enqueued
$datastore->enqueue(array(
	'cat_forums',
));

// Cron
if ((empty($_POST) && !defined('IN_ADMIN') && !defined('IN_AJAX') && !defined('IN_SERVICE') && !file_exists(CRON_RUNNING) && $bb_cfg['cron_enabled']) || defined('FORCE_CRON') /* && !empty($_GET['cron_test_9gndjk']) */)
{
	if (TIMENOW - $bb_cfg['cron_last_check'] > $bb_cfg['cron_check_interval'])
	{
		// Update cron_last_check
		bb_update_config(array('cron_last_check' => (time() + 10)));

		require(CFG_DIR .'cron_cfg.'. PHP_EXT);

		bb_log(date('H:i:s - ') . getmypid() .' -x-- DB-LOCK try'. LOG_LF, CRON_LOG_DIR .'cron_check');

		if ($db->get_lock('cron', 1))
		{
			bb_log(date('H:i:s - ') . getmypid() .' --x- DB-LOCK OBTAINED !!!!!!!!!!!!!!!!!'. LOG_LF, CRON_LOG_DIR .'cron_check');

			sleep(2);
			require(CRON_DIR .'cron_init.'. PHP_EXT);

			$db->release_lock('cron');
		}
	}
}

$dl_link_css = array(
	DL_STATUS_RELEASER => 'genmed',
	DL_STATUS_WILL     => 'dlWill',
	DL_STATUS_DOWN     => 'leechmed',
	DL_STATUS_COMPLETE => 'seedmed',
	DL_STATUS_CANCEL   => 'dlCancel',
);

$dl_status_css = array(
	DL_STATUS_RELEASER => 'genmed',
	DL_STATUS_WILL     => 'dlWill',
	DL_STATUS_DOWN     => 'dlDown',
	DL_STATUS_COMPLETE => 'dlComplete',
	DL_STATUS_CANCEL   => 'dlCancel',
);

if (!defined('IN_INSTALL'))
{
	// Show 'Board is disabled' message if needed.
	if ($bb_cfg['board_disable'] && !defined('IN_ADMIN') && !defined('IN_LOGIN'))
	{
		message_die(GENERAL_MESSAGE, 'Board_disable', 'Information');
	}
}

#	if (@$_GET['sdfhjk45689032'] == 1) require(INC_DIR .'cron/jobs/site_backup.php');

