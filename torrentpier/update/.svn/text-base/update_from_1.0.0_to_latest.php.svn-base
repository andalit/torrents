<?php

define('IN_PHPBB', true);
define('IN_INSTALL', true);
define('BB_ROOT', './../');
require(BB_ROOT .'common.php');

while (@ob_end_flush());
ob_implicit_flush();

error_reporting(E_ALL);
ini_set('display_errors', 1);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title></title>
</head>
<body style="font: 12px Courier, monospace; white-space: nowrap;">

<?php

if (empty($_POST['confirm']))
{
	echo '
		<br />
		<center>
		<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
		<input type="submit" name="confirm" value="Start update" />
		</form>
		</center>
	';

	exit;
}
else
{
	echo '';
}

// Fix possible duplicate records in bb_user_group table
$dup_count = 0;
$delete_dup_sql = array();

$sql = "SELECT group_id, COUNT(user_id) AS dup
	FROM ". USER_GROUP_TABLE ."
	GROUP BY group_id, user_id
	HAVING dup > 1";

if ($rowset = $db->fetch_rowset($sql))
{
	foreach ($rowset as $row)
	{
		$dup_count += $row['dup'];
		$delete_dup_sql[] = $row['group_id'];
	}
	if ($delete_dup_sql = join(',', $delete_dup_sql))
	{
		$db->query("DELETE FROM ". USER_GROUP_TABLE ." WHERE group_id IN($delete_dup_sql)");
	}
}
print_sql_ok("Removing <b>$dup_count</b> duplicate records from ". USER_GROUP_TABLE ." table");

// Fix possible duplicate records in bb_auth_access table
$dup_count = 0;
$delete_dup_sql = array();

$sql = "SELECT group_id, COUNT(forum_id) AS dup
	FROM ". AUTH_ACCESS_TABLE ."
	GROUP BY group_id, forum_id
	HAVING dup > 1";

if ($rowset = $db->fetch_rowset($sql))
{
	foreach ($rowset as $row)
	{
		$dup_count += $row['dup'];
		$delete_dup_sql[] = $row['group_id'];
	}
	if ($delete_dup_sql = join(',', $delete_dup_sql))
	{
		$db->query("DELETE FROM ". AUTH_ACCESS_TABLE ." WHERE group_id IN($delete_dup_sql)");
	}
}
print_sql_ok("Removing <b>$dup_count</b> duplicate records from ". AUTH_ACCESS_TABLE ." table");

$bb_cfg = array_merge(bb_get_config(CONFIG_TABLE), $bb_cfg);
$version = isset($bb_cfg['tp_version']) ? $bb_cfg['tp_version'] : '1.0.0.0';
$version = explode('.', $version);
$base    = (int) $version[0];
$release = (int) $version[1];
$current = (isset($version[2]) ? intval($version[2]) : 0 );
$sub     = (isset($version[3]) ? intval($version[3]) : 0 );
// Run update scripts

if ($base == 1 && $release == 0 && $current == 0 && $sub < 1)
{
	run_sql_file('update_from_1.0.0_to_1.0.1.0.sql');
}
if ($base == 1 && $release == 0 && $current == 1 && $sub < 1)
{
	run_sql_file('update_from_1.0.1.0_to_1.0.1.1.sql');
}
if ($base == 1 && $release == 0 && $current == 1 && $sub < 2)
{
	$db->query("ALTER TABLE ". USERS_TABLE ." ADD `user_last_ip` CHAR( 8 ) NOT NULL AFTER `user_lastvisit`");
	$db->query("ALTER TABLE ". USERS_TABLE ." ADD `user_reg_ip` CHAR( 8 ) NOT NULL AFTER `user_regdate`");
}
if ($base == 1 && $release == 0 && $current == 1 && $sub < 3)
{
	$db->query("ALTER TABLE ". ATTACHMENTS_TABLE ." DROP PRIMARY KEY, ADD PRIMARY KEY (`attach_id`, `post_id`), DROP INDEX `post_id` ");
	$db->query("ALTER TABLE ". BT_TORRENTS_TABLE ." ADD `tor_type` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `checked_time`");
	
	$columns  = $db->fetch_row("SHOW COLUMNS FROM ". TOPICS_TABLE);
	$old_gold = false;
	foreach ($columns as $column)
	{
		if ($column['Field'] == 'topic_gold')
		{
			$old_gold = true;
			break;
		}
	}
	if ($old_gold)
	{
		$db->query("
				UPDATE 
					". BT_TORRENTS_TABLE ." tor, ". TOPICS_TABLE ." t
				SET   tor.tor_type = t.topic_gold
				WHERE tor.topic_id = t.topic_id
		");
		$db->query("ALTER TABLE ". TOPICS_TABLE ." DROP `topic_gold`");
	}
}
if ($base == 1 && $release == 0 && $current == 1 && $sub < 4)
{
	run_sql_file('update_from_1.0.1.3_to_1.0.1.4.sql');
}
if ($base == 1 && $release == 0 && $current == 1 && $sub < 5)
{
	bb_update_config(array(
		'bt_disable_dht'  => 0,
		'tp_version'      => '1.0.1.5',
		'tp_release_date' => '2009-04-20',		
	), CONFIG_TABLE);
}

// Update cache
bb_get_config(CONFIG_TABLE, true, true);

//
// Functions
//
function run_sql_file ($file_name)
{
	$file_path = './sql/'. basename($file_name);
	$file_contents = file_get_contents($file_path);

	$sql_ary = get_sql_ary($file_contents);

	run_sql_query($sql_ary);
}

function get_sql_ary ($sql_str)
{
	$sql_str = preg_replace('/^#.*$/m', '', $sql_str);
	$sql_str = preg_replace('#(TYPE=\w+)#', "\\1 DEFAULT CHARSET='.DBCHARSET.'", $sql_str);
	$sql_str = preg_replace('#(`)(bb_|buf_)(\w+)(`)#', '$1$3$4', $sql_str);

	return explode(';', $sql_str);
}

function print_sql_err ($sql)
{
	global $err;

	$sql_error = $GLOBALS['db']->sql_error();
	$msg = $sql_error['message'];
	$err = $sql_error['code'];

	echo '<div>';
	echo "\n<br /><font color=darkred>$sql\n<br />";
	echo "<b>Error $err: $msg</b>\n<br />". str_repeat(' ', 256) ."</font>";
	echo '</div>';
}

function print_sql_ok ($sql)
{
	global $err;

	echo ($err) ? "\n<br />" : '';
	$err = '';

	echo '<div>';
	echo "<font color=darkgreen><b>OK</b> - $sql</font>". str_repeat(' ', 256) ."\n<br />";
	echo '</div>';
}

function run_sql_query ($sql_ary)
{
	global $db;

	array_deep($sql_ary, 'trim');

	foreach ($sql_ary as $sql)
	{
		if (!$sql) continue;

		set_time_limit(600);

		if (@$db->query($sql))
		{
			print_sql_ok($sql);
		}
		else
		{
			$sql_error = $db->sql_error();

			if ($sql_error['code'] == 1091) // check that column/key exists
			{
				print_sql_ok($sql);
			}
			else
			{
				print_sql_err($sql);
			}
		}
	}
}

?>

</div>
<br />
Update complete.
</body>
</html>
