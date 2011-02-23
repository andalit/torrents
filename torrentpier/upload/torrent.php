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
define('BB_SCRIPT', 'torrent');
define('BB_ROOT', './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require(BB_ROOT ."common.$phpEx");
require(BB_ROOT .'attach_mod/attachment_mod.'. PHP_EXT);
require(INC_DIR .'functions_torrent.'. PHP_EXT);

// Start session management
$user->session_start();

// Check if user logged in
if (!$userdata['session_logged_in'])
{
	redirect(append_sid("login.$phpEx?redirect=index.$phpEx", true));
}

$sid = request_var('sid', '');
$confirm = isset($_POST['confirm']);

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

// Register torrent on tracker
if ($mode == 'reg')
{
	tracker_register($attach_id, 'request');
	exit;
}

// Unregister torrent from tracker
if ($mode == 'unreg')
{
	tracker_unregister($attach_id, 'request');
	exit;
}

if (!empty($_POST['tor_action']) && $confirm)
{
	// Delete torrent
	if ($_POST['tor_action'] === 'del_torrent')
	{
		delete_torrent($attach_id, 'request');
		redirect("viewtopic.$phpEx?t=$topic_id");
	}
	// Delete torrent and move topic
	if ($_POST['tor_action'] === 'del_torrent_move_topic')
	{
		delete_torrent($attach_id, 'request');
		redirect("modcp.$phpEx?t=$topic_id&mode=move&sid={$userdata['session_id']}");
	}
	// Set/UnSet GOLD & SILVER
	if ( ($_POST['tor_action'] === 'set_silver' || $_POST['tor_action'] === 'set_gold' || $_POST['tor_action'] === 'unset_silver_gold' ) && $bb_cfg['gold_silver_enabled'])
	{
		if ($_POST['tor_action'] === 'set_silver')
		{
			$tor_type = TOR_TYPE_SILVER;
		}
		elseif ($_POST['tor_action'] === 'set_gold')
		{
			$tor_type = TOR_TYPE_GOLD;
		}
		else
		{
			$tor_type = 0;
		}
		change_tor_type($attach_id, $tor_type);
		redirect("viewtopic.$phpEx?t=$topic_id");
	}
}

// Generate passkey
if ($mode == 'gen_passkey')
{
	if (($req_uid == $user->id || IS_ADMIN) && $sid === $userdata['session_id'])
	{
		$force_generate = (IS_ADMIN);

		if (!generate_passkey($req_uid, $force_generate))
		{
			message_die(GENERAL_ERROR, 'Could not insert passkey', '', __LINE__, __FILE__, $sql);
		}
		tracker_rm_user($req_uid);
		message_die(GENERAL_MESSAGE, $lang['BT_GEN_PASSKEY_OK']);
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['NOT_AUTHORISED']);
	}
}

message_die(GENERAL_ERROR, 'Not confirmed or invalid mode');

