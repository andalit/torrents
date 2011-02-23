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

// Run update script
run_sql_file('update_from_0.3.5_to_1.0.0.sql');

//
// Functions
//
function run_sql_file ($file_name)
{
	$file_path = './'. basename($file_name);
	$file_contents = file_get_contents($file_path);

	$sql_ary = get_sql_ary($file_contents);

	run_sql_query($sql_ary);
}

function get_sql_ary ($sql_str)
{
	global $table_prefix, $buffer_prefix, $dbcharset;

	$sql_str = preg_replace('/^#.*$/m', '', $sql_str);
	$sql_str = preg_replace('#(TYPE=\w+)#', "\\1 DEFAULT CHARSET=$dbcharset", $sql_str);

	if (isset($table_prefix) && $table_prefix != 'bb_')
	{
		$sql_str = preg_replace('#(`)(bb_)(\w+)(`)#', '$1'. $table_prefix .'$3$4', $sql_str);
	}
	if (isset($buffer_prefix) && $buffer_prefix != 'buf_')
	{
		$sql_str = preg_replace('#(`)(buf_)(\w+)(`)#', '$1'. $buffer_prefix .'$3$4', $sql_str);
	}

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
