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
define('BB_SCRIPT', 'online');
define('BB_ROOT', './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require(BB_ROOT ."common.$phpEx");

// Start session management
$user->session_start(array('req_login' => true));

//
// Output page header and load viewonline template
//
$template->assign_vars(array(
	'PAGE_TITLE' => $lang['WHOSONLINE'],
	'L_LAST_UPDATE' => $lang['LAST_UPDATED'],
));

//
// Forum info
//
$sql = "SELECT forum_name, forum_id
	FROM " . FORUMS_TABLE;
if ( $result = $db->sql_query($sql) )
{
	while( $row = $db->sql_fetchrow($result) )
	{
		$forum_data[$row['forum_id']] = htmlCHR($row['forum_name']);
	}
}
else
{
	message_die(GENERAL_ERROR, 'Could not obtain user/online forums information', '', __LINE__, __FILE__, $sql);
}

//
// Get auth data
//
$is_auth_ary = array();
$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata);

//
// Get user list
//
$sql = "SELECT u.user_id, u.username, u.user_allow_viewonline, u.user_level, s.session_logged_in, s.session_time, s.session_ip
	FROM ".USERS_TABLE." u, ".SESSIONS_TABLE." s
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ".( time() - 300 ) . "
	ORDER BY u.username ASC, s.session_ip ASC";
if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not obtain regd user/online information', '', __LINE__, __FILE__, $sql);
}

$guest_users = 0;
$registered_users = 0;
$hidden_users = 0;

$reg_counter = 0;
$guest_counter = 0;
$prev_user = 0;
$prev_ip = '';

$user_id = 0;

while ( $row = $db->sql_fetchrow($result) )
{
	$view_online = false;

	if ( $row['session_logged_in'] )
	{
		$user_id = $row['user_id'];

		if ( $user_id != $prev_user )
		{
			$username = $row['username'];

			$style_color = '';
			if ( $row['user_level'] == ADMIN )
			{
				$username = '<b class="colorAdmin">' . $username . '</b>';
			}
			else if ( $row['user_level'] == MOD )
			{
				$username = '<b class="colorMod">' . $username . '</b>';
			}

			if ( !$row['user_allow_viewonline'] )
			{
				$view_online = (IS_ADMIN || IS_MOD);
				$hidden_users++;

				$username = '<i>' . $username . '</i>';
			}
			else
			{
				$view_online = true;
				$registered_users++;
			}

			$which_counter = 'reg_counter';
			$which_row = 'reg_user_row';
			$prev_user = $user_id;
		}
	}
	else
	{
		if ( $row['session_ip'] != $prev_ip )
		{
			$username = $lang['GUEST'];
			$view_online = true;
			$guest_users++;

			$which_counter = 'guest_counter';
			$which_row = 'guest_user_row';
		}
	}

	$prev_ip = $row['session_ip'];

	if ( $view_online )
	{
		$row_class = !($$which_counter % 2) ? 'row1' : 'row2';

		$template->assign_block_vars("$which_row", array(
			'ROW_CLASS' => $row_class,
			'USERNAME' => $username,
			'LASTUPDATE' => bb_date($row['session_time']),

			'U_USER_PROFILE' => ((isset($user_id)) ? append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . '=' . $user_id) : ''),
		));

		$$which_counter++;
	}
}

if( $registered_users == 0 )
{
	$l_r_user_s = $lang['REG_USERS_ZERO_ONLINE'];
}
else if( $registered_users == 1 )
{
	$l_r_user_s = $lang['REG_USER_ONLINE'];
}
else
{
	$l_r_user_s = $lang['REG_USERS_ONLINE'];
}

if( $hidden_users == 0 )
{
	$l_h_user_s = $lang['HIDDEN_USERS_ZERO_ONLINE'];
}
else if( $hidden_users == 1 )
{
	$l_h_user_s = $lang['HIDDEN_USER_ONLINE'];
}
else
{
	$l_h_user_s = $lang['HIDDEN_USERS_ONLINE'];
}

if( $guest_users == 0 )
{
	$l_g_user_s = $lang['GUEST_USERS_ZERO_ONLINE'];
}
else if( $guest_users == 1 )
{
	$l_g_user_s = $lang['GUEST_USER_ONLINE'];
}
else
{
	$l_g_user_s = $lang['GUEST_USERS_ONLINE'];
}

$template->assign_vars(array(
	'TOTAL_REGISTERED_USERS_ONLINE' => sprintf($l_r_user_s, $registered_users) . sprintf($l_h_user_s, $hidden_users),
	'TOTAL_GUEST_USERS_ONLINE' => sprintf($l_g_user_s, $guest_users))
);

if ( $registered_users + $hidden_users == 0 )
{
	$template->assign_vars(array(
		'L_NO_REGISTERED_USERS_BROWSING' => $lang['NO_USERS_BROWSING'])
	);
}

if ( $guest_users == 0 )
{
	$template->assign_vars(array(
		'L_NO_GUESTS_BROWSING' => $lang['NO_USERS_BROWSING'])
	);
}

print_page('viewonline.tpl');
