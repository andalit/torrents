<?php

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
	exit;
}

if (!$userdata['session_logged_in'])
{
	redirect(append_sid("login.php?redirect={$_SERVER['REQUEST_URI']}", TRUE));
}

$action = isset($_GET['action']) ? (string) $_GET['action'] : null;

$user_id = (int) $userdata['user_id'];

$btu = get_bt_userdata($user_id);
			
$up_total = $btu['u_up_total'] + $btu['u_up_release'] + $btu['u_up_bonus'];
$down_total =$btu['u_down_total'];
$ratio = ($down_total) ? round((($up_total) / $down_total), 2) : '-';
$ratio_nulled = $btu['ratio_nulled'];

if ($down_total < MIN_DL_FOR_RATIO )
{
	$message =  $lang['NULLRATIO1']. '<b>'. humn_size(MIN_DL_FOR_RATIO) . '</b><br /><br /><a href="' . append_sid("index.php?") . '">Go to index</a>';
	message_die(GENERAL_MESSAGE, $message);
}	
else if ($ratio_nulled)
{
	$message =  $lang['NULLRATIO4']. '<br /><br /><a href="' . append_sid("index.php?") . '">Go to index</a>';
	message_die(GENERAL_MESSAGE, $message);
}
else if (!$bb_cfg['rationull_enabled'])
{
	$message =  $lang['NULLRATIO9']. '<br /><br /><a href="' . append_sid("index.php?") . '">Go to index</a>';
	message_die(GENERAL_MESSAGE, $message);
}
else if ($ratio > $bb_cfg['ratio_to_null'])
{
	$message =  $lang['NULLRATIO5']. '<br /><br /><a href="' . append_sid("index.php?") . '">Go to index</a>';
	message_die(GENERAL_MESSAGE, $message);
}
else
{
	$ok = true;
	$template->assign_vars(array(
				'S_ACTION'     => "profile.php?mode=nullratio&amp;action=null", 
				'L_NULLRATIO0' => $lang['NULLRATIO0'],
				'L_NULLRATIO2' => $lang['NULLRATIO2'],
				'L_NULLRATIO3' => $lang['NULLRATIO3']
	));
}
if ($action == 'null' && $ok && isset($_POST['send']))
{
	$sql = "update ". BT_USERS_TABLE ." set u_up_total=0, u_down_total=0, u_up_release=0, u_up_bonus=0, ratio_nulled=1 WHERE user_id=". $user_id;
	$db->sql_query($sql);
	$message =  $lang['NULLRATIO6'] . '<br /><br /><a href="' . append_sid("index.php?") . '">Go to index</a>';
	message_die(GENERAL_MESSAGE, $message);
}

print_page('usercp_nullratio.tpl');

