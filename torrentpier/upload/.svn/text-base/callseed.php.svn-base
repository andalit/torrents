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

//variables
define('IN_PHPBB', true);
define('BB_SCRIPT', 'callseed');
define('BB_ROOT', './');
require(BB_ROOT . "common.php");

// Init userdata
$user->session_start();

require(INC_DIR .'bbcode.php');
require(LANG_DIR .'lang_callseed.php');

function topic_info($topic_id) 
{
	global $db;
	
	$sql = "  SELECT tor.poster_id, tor.forum_id, tor.attach_id, t.topic_title, f.forum_name
				FROM ". BT_TORRENTS_TABLE ." tor , ". TOPICS_TABLE ." t, ". FORUMS_TABLE ." f
				WHERE tor.topic_id = $topic_id
					AND t.topic_id = tor.topic_id
					AND f.forum_id = tor.forum_id
				LIMIT 1";
	$row = $db->fetch_row($sql);

	$t = array(
		"topic_title"  => $row['topic_title'],
		"forum_title"  => $row['forum_name'],
		"attach_id"    => $row['attach_id'],
		"topic_poster" => $row['poster_id']
	);

	return $t;
}

function send_pm($topic_id, $t_info, $to_user_id) 
{
	global $db, $userdata, $lang, $msg_error;

	$sql = "UPDATE ". BT_TORRENTS_TABLE ." SET call_seed_time=". TIMENOW ." WHERE topic_id = $topic_id";
	if (!$db->sql_query($sql)) {
		$msg_error = "TIME";
		return;
	}

	$subj = sprintf ($lang['CALLSEED_SUBJ'], $t_info['topic_title']);
	$text = sprintf ($lang['CALLSEED_TEXT'], $topic_id, $t_info['forum_title'], $t_info['topic_title'], $t_info['attach_id']);
	$subj = $db->escape($subj);
	$text = $db->escape($text);

	$sql = "INSERT INTO ". PRIVMSGS_TABLE ." (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip)
	VALUES (". PRIVMSGS_NEW_MAIL .",'$subj',{$userdata['user_id']},$to_user_id,". TIMENOW .",'". USER_IP ."')";
	if (!$db->sql_query($sql)) {
		$msg_error = "MSG";
		return;
	}

	$id = $db->sql_nextid();

	$sql = "INSERT INTO ". PRIVMSGS_TEXT_TABLE ." VALUES($id, '". make_bbcode_uid() ."', '$text')";
	if (!$db->sql_query($sql)) {
		$msg_error = "MSG_TEXT";
		return;
	}

	$sql = "UPDATE ". USERS_TABLE ." SET 
		user_new_privmsg = user_new_privmsg + 1, 
		user_last_privmsg = ". TIMENOW .",
		user_newest_pm_id = $id
		WHERE user_id = $to_user_id";
	if (!$db->sql_query($sql)) {
		$msg_error = "POPUP";
		return;
	}
}

	$u_id = array();
	$topic_id = request_var('t', 0);
	$t_info = topic_info($topic_id);
	$msg_error = "OK";

	$sql = "SELECT call_seed_time FROM ". BT_TORRENTS_TABLE ." WHERE topic_id = $topic_id LIMIT 1";
	if($row = $db->fetch_row($sql))
	{
		$pr_time = $row['call_seed_time'];
		$pause = 86400; //1 day
		$cp = TIMENOW - $pr_time;
		$pcp = $pause - $cp;
		if($cp <= $pause)
		{
			$cur_pause_hour = floor($pcp/3600);
			$cur_pause_min = floor($pcp/60)/*-($cur_pause_hour*60)*/;
			$msg_error = "SPAM";
		}
	} else {
		message_die(GENERAL_ERROR, 'Topic does not callseed time', '', __LINE__, __FILE__);
	}

	// check have_seed
	if ($msg_error == "OK")    
	{
		$sql = "SELECT seeders, leechers FROM ". BT_TRACKER_SNAP_TABLE ." WHERE topic_id = $topic_id LIMIT 1";
        $row = $db->fetch_row($sql);
        if ($row['seeders'] > 2)
		#if ( !in_array($userdata['user_level'], array(ADMIN, MOD)) )
		{
			$seeders = $row['seeders'];
			$leechers = $row['leechers'];
			$msg_error = "HAVE_SEED";
		}
	}

	$sql = "SELECT user_id FROM ". BT_DLSTATUS_TABLE ." WHERE topic_id = $topic_id";
	/*$row = $db->fetch_rowset($sql);*/
	foreach($db->fetch_rowset($sql) as $row)
	{
		$u_id[] = $row['user_id'];
	}
	if (!in_array($t_info['topic_poster'], $u_id))
	{
		$u_id[] = $t_info['topic_poster'];	
	}
	array_unique($u_id);

	foreach($u_id as $i=>$user_id)
	{
		if ($msg_error != "OK") break;

		send_pm($topic_id, $t_info, $user_id);
	}

	$msg = '';
	meta_refresh("viewtopic.php?t=$topic_id", 8);
	$return_to = sprintf ($lang['CALLSEED_RETURN'], $topic_id);

	switch($msg_error) {
		case "OK":
			$msg .= $lang['CALLSEED_MSG_OK'];
			break;
		case "SPAM":
			$msg .= sprintf ($lang['CALLSEED_MSG_SPAM'], $cur_pause_hour, $cur_pause_min);
			break;
		case "MSG":
			$msg .= $lang['CALLSEED_MSG_MSG'];
			break;
		case "MSG_TEXT":
			$msg .= $lang['CALLSEED_MSG_MSG_TEXT'];
			break;
		case "POPUP":
			$msg .= $lang['CALLSEED_MSG_POPUP'];
			break;
		case "TIME":
			$msg .= $lang['CALLSEED_MSG_TIME'];
			break;
		case "HAVE_SEED":
			$msg .= sprintf ($lang['CALLSEED_HAVE_SEED'], $seeders, $leechers);
			break;
	}

$msg .= $return_to;
message_die(GENERAL_MESSAGE, $msg);