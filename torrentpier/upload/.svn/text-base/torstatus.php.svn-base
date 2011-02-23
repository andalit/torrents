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

define('IN_PHPBB', true);
define('BB_SCRIPT', 'torstatus');
define('BB_ROOT', './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require(BB_ROOT ."common.$phpEx");
require(BB_ROOT . 'attach_mod/attachment_mod.'. PHP_EXT);
require(INC_DIR .'functions_torrent.'. PHP_EXT);

// Start session management
$user->session_start();

// Check if user logged in
if (!$userdata['session_logged_in'])
{
	redirect(append_sid("login.$phpEx?redirect=index.$phpEx", true));
}

$sid = (@$_REQUEST['sid']) ? $_REQUEST['sid'] : '';
$confirm = isset($_POST['status_confirm']);

// Set received variables
// Strings
$input_vars_str  = array(
	'mode' => 'mode'
);

// Numeric
$input_vars_num = array(
	'attach_id' => 'id',
	'req_uid' => 'u'
);

// Strings
foreach ($input_vars_str as $var => $param)
{
	$$var = (isset($_REQUEST[$param])) ? $_REQUEST[$param] : '';
}
// Numeric
foreach ($input_vars_num as $var => $param)
{
	$$var = (isset($_REQUEST[$param])) ? intval($_REQUEST[$param]) : '';
}

if (($mode == 'reg' || $mode == 'unreg' || !empty($_POST['tor_action'])) && !$attach_id)
{
	message_die(GENERAL_ERROR, 'Invalid attach_id');
}

// Show users torrent-profile
if ($mode == 'userprofile')
{
	redirect(append_sid("profile.$phpEx?mode=viewprofile&u=$req_uid"), true);
}

// check SID
if ($sid == '' || $sid !== $userdata['session_id'])
{
//message_die(GENERAL_ERROR, 'Invalid_session');
}

if (!empty($_POST['tor_status']) && $confirm)
{
		$new_tor_status = $_POST['tor_status'];
		change_tor_status($attach_id, $new_tor_status);
		$sql = "update ". BT_TORRENTS_TABLE ." set checked_user_id=". $userdata['user_id'] .", checked_time=". time() ." WHERE attach_id=". $attach_id;
		$db->sql_query($sql);
		redirect("viewtopic.$phpEx?t=$topic_id");	
		

//end torrent status mod


//$userdata = session_pagestart($user_ip, PAGE_INDEX);
//init_userprefs($userdata);


//$attach_id = $_GET['a'];
/*
if( $userdata['user_id'] != ANONYMOUS && is_numeric($attach_id) ) {

  $sql = 'SELECT p.forum_id
	FROM '. ATTACHMENTS_TABLE .' a join '. POSTS_TABLE .' p on a.post_id=p.post_id
	WHERE a.attach_id='. $attach_id;

  if( $result = $db->sql_query($sql) ) {

    $row = $db->sql_fetchrow($result);
    $forum_id = $row['forum_id'];
    $is_auth = array();
    $is_auth = auth(AUTH_ALL, $forum_id, $userdata);
    if( $is_auth['auth_mod'] ) {

	$sql = "update ". BT_TORRENTS_TABLE ." set checked_user_id=". $userdata['user_id'] .", checked_time=". time()
		." WHERE attach_id=". $attach_id;

	if( $db->sql_query($sql) ) {
		echo 'var vb=document.getElementById("VA'. $attach_id .'");vb.innerHTML="Одобрено";';
	} else {
#		echo "alert('SQL Update Error');";
	}

    } else {
	echo "alert('Unauthorized');";
    }

  } else {
#	echo "alert('SQL Forum_ID Error');";
  }
}*/
}
?>