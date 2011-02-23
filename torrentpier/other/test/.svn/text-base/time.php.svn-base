<?php

define('IN_TRACKER', true);
define('BB_ROOT', './../forum/');
require(BB_ROOT .'common.php');

header('Content-Type: text/html');
header('Pragma: no-cache');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!mysql_connect(DBHOST, DBUSER, DBPASSWD))
{
	echo mysql_error();
	die("<br>Не могу подключиться к БД");
}
mysql_select_db(DBNAME);

$mysql_timestamp = mysql_fetch_row(mysql_query("SELECT UNIX_TIMESTAMP()"));
$mysql_curtime   = mysql_fetch_row(mysql_query("SELECT curtime()"));

$date_default_timezone = function_exists('date_default_timezone_get') ? date_default_timezone_get() : 'not supported';

$tmp_fname = LOG_DIR .'test_file--'. date('Y-m-d_H-i-s--pid_') . getmypid();
file_write('', $tmp_fname);
$filemtime = filemtime($tmp_fname);
unlink($tmp_fname);

?>
<html><body>
<pre>

php   date("D M j G:i:s T Y")   - <?php echo date("D M j G:i:s T Y"); ?>


php   time()                    - <b><?php echo time();              ?></b> --| должно
php   filemtime()               - <b><?php echo $filemtime;          ?></b> --| быть
mysql UNIX_TIMESTAMP()          - <b><?php echo $mysql_timestamp[0]; ?></b> --| одинаковым

mysql CURTIME()                 - <?php echo $mysql_curtime[0]; ?>

php   DEFAULT_TIMEZONE          - <?php echo $date_default_timezone; ?>



</pre>
</body></html>