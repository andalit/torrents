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
define('IN_PROFILE', true);
define('BB_SCRIPT', 'profile');
define('BB_ROOT', './');
require(BB_ROOT . "common.php");

// Start session management
$user->session_start();

// session id check
$sid = request_var('sid', '');

//
// Set default email variables
//
$script_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($board_config['script_path']));
$script_name = ( $script_name != '' ) ? $script_name . '/profile.php' : 'profile.php';
$server_name = trim($board_config['server_name']);
$server_protocol = ( $board_config['cookie_secure'] ) ? 'https://' : 'http://';
$server_port = ( $board_config['server_port'] <> 80 ) ? ':' . trim($board_config['server_port']) . '/' : '/';

$server_url = $server_protocol . $server_name . $server_port . $script_name;

// -----------------------
// Page specific functions
//
function gen_rand_string ($hash)
{
	$rand_str = make_rand_str(8);

	return ($hash) ? md5($rand_str) : $rand_str;
}
//
// End page specific functions
// ---------------------------

//
// Start of program proper
//
if ( isset($_GET['mode']) || isset($_POST['mode']) )
{
	$mode = request_var('mode', '');
	$mode = htmlspecialchars($mode);

	if ( $mode == 'viewprofile' )
	{
		require(INC_DIR . 'ucp/usercp_viewprofile.php');
		exit;
	}
	else if ( $mode == 'editprofile' || $mode == 'register' )
	{
		if ( !$userdata['session_logged_in'] && $mode == 'editprofile' )
		{
			login_redirect();
		}

		require(INC_DIR . 'ucp/usercp_register.php');
		exit;
	}
	else if ( $mode == 'confirm' )
	{
		// Visual Confirmation
		if ( $userdata['session_logged_in'] )
		{
			exit;
		}

		require(INC_DIR . 'ucp/usercp_confirm.php');
		exit;
	}
	else if ( $mode == 'sendpassword' )
	{
		require(INC_DIR . 'ucp/usercp_sendpasswd.php');
		exit;
	}
	else if ( $mode == 'activate' )
	{
		require(INC_DIR . 'ucp/usercp_activate.php');
		exit;
	}
	else if ( $mode == 'email' )
	{
		require(INC_DIR . 'ucp/usercp_email.php');
		exit;
	}
	else if ( $mode == 'attachcp' )
	{
		require(INC_DIR . 'ucp/usercp_attachcp.php');
		exit;
	}
}

redirect(append_sid("index.php", true));