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

define('IN_PHPBB',   true);
define('BB_SCRIPT', 'login');
define('IN_LOGIN', true);
define('BB_ROOT', './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require(BB_ROOT ."common.$phpEx");

$redirect_url = "index.$phpEx";
$login_error = $login_err_msg = false;

// Requested redirect
if (!empty($_POST['redirect']))
{
	$redirect_url = str_replace('&amp;', '&', htmlspecialchars($_POST['redirect']));
}
else if (!empty($_SERVER['HTTP_REFERER']) && ($parts = @parse_url($_SERVER['HTTP_REFERER'])))
{
	$redirect_url = (isset($parts['path']) ? $parts['path'] : "index.$phpEx") . (isset($parts['query']) ? '?'. $parts['query'] : '');
}
else if (preg_match('/^redirect=([a-z0-9\.#\/\?&=\+\-_]+)/si', $_SERVER['QUERY_STRING'], $matches))
{
	$redirect_url = $matches[1];

	if (!strstr($redirect_url, '?') && $first_amp = strpos($redirect_url, '&'))
	{
		$redirect_url[$first_amp] = '?';
	}
}

$redirect_url = str_replace('&admin=1', '', $redirect_url);

if (!$redirect_url || strstr(urldecode($redirect_url), "\n") || strstr(urldecode($redirect_url), "\r") || strstr(urldecode($redirect_url), ';url'))
{
	$redirect_url = "index.$phpEx";
}

if (!empty($_POST['login']) && !empty($_POST['cookie_test']))
{
	if (empty($_COOKIE[COOKIE_TEST]) || $_COOKIE[COOKIE_TEST] !== $_POST['cookie_test'])
	{
		$login_error = 'cookie';
	}
}

// Start login
$user->session_start();

$redirect_url = str_replace("&sid={$user->data['session_id']}", '', $redirect_url);
if (isset($_REQUEST['admin']) && !(IS_MOD || IS_ADMIN))
{
	bb_die($lang['NOT_ADMIN']);
}

$mod_admin_login = ((IS_MOD || IS_ADMIN) && !$user->data['session_admin']);
// dj_maxx: add visual confirmation to login form
$sql = "SELECT * FROM ". UNTRUSTED_IPS_TABLE ." WHERE untrusted_ip = '".USER_IP."'";
if ( $row = $db->fetch_row($sql) )  // record in banlist table exists
{
	$login_enable_confirm = ($row['untrusted_attempts'] > 10) ? 1 : 0;
}
else    // no such records
{
	$login_enable_confirm = 0;
}
// end of

if ($login_error)
{
	//!? TODO
}
// login
else if (isset($_POST['login']))
{
	if (!IS_GUEST && !$mod_admin_login)
	{
		redirect("index.$phpEx");
	}
// dj_maxx: add visual confirmation to login form
 
	/* treat all as untrusted, cron will sort who is who in bb_manage_untrusted.php :) */
	$sql = "INSERT INTO ". UNTRUSTED_IPS_TABLE ."
		(untrusted_ip,  untrusted_reason, untrusted_attempts, untrusted_pending) VALUES
		('".USER_IP."', 'bruteforce', 1, 1)
		ON DUPLICATE KEY UPDATE
		untrusted_attempts = untrusted_attempts + 1";
	if ( !$db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Could not update anti-attack table.", '', __LINE__, __FILE__, $sql);
	}


	if ($login_enable_confirm)
	{
		if (empty($HTTP_POST_VARS['confirm_id']))
		{
			$login_error = 'captcha';
			$login_err_msg = $lang['CONFIRM_CODE_WRONG'];
		}
		else
		{
			$sid = (isset($HTTP_POST_VARS['sid'])) ? $HTTP_POST_VARS['sid'] : 0;
			$confirm_id = htmlspecialchars($HTTP_POST_VARS['confirm_id']);
			$confirm_code = trim(htmlspecialchars($HTTP_POST_VARS['cfmcd']));

			if (!preg_match('/^[A-Za-z0-9]+$/', $confirm_id))
			{
				$confirm_id = '';
			}
       	
			$sql = 'SELECT code
				FROM ' . CONFIRM_TABLE . "
				WHERE confirm_id = '$confirm_id'
					AND session_id = '" . $sid . "'";
			if (!($result = $db->sql_query($sql)))
			{
				message_die(GENERAL_ERROR, 'Could not obtain confirmation code', '', __LINE__, __FILE__, $sql);
			}
				if ($row = $db->sql_fetchrow($result))
			{      
				// Only compare one char if the zlib-extension is not loaded
				if (!@extension_loaded('zlib'))
				{
					$row['code'] = substr($row['code'], -1);
				}
	
				if (strtolower($row['code']) != strtolower($confirm_code))
				{
					$login_error = 'captcha';
					$login_err_msg = $lang['CONFIRM_CODE_WRONG'];
				}
				else
				{
					$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
						WHERE confirm_id = '$confirm_id'
						AND session_id = '" . $sid . "'";
					if (!$db->sql_query($sql))
					{
						message_die(GENERAL_ERROR, 'Could not delete confirmation code', '', __LINE__, __FILE__, $sql);
					}
				}
			}
			else
			{
				$login_error = 'captcha';
				$login_err_msg = $lang['CONFIRM_CODE_WRONG'];
			}
			$db->sql_freeresult($result);
		}
	}

	// if ($user->login($HTTP_POST_VARS, $mod_admin_login))
	if (!$login_error && $user->login($HTTP_POST_VARS, $mod_admin_login))
	// end of

	//if ($user->login($HTTP_POST_VARS, $mod_admin_login))
	{
		if ($bb_cfg['board_disable'] && $user->data['user_level'] != ADMIN)
		{
			redirect("index.$phpEx");
		}

		if ($mod_admin_login)
		{
			redirect($redirect_url);
		}
		else
		{
			$redirect_url = (defined('FIRST_LOGON')) ? $bb_cfg['first_logon_redirect_url'] : $redirect_url;
			redirect($redirect_url);
		}
	}

// dj_maxx: add visual confirmation to login form
//	$login_err_msg = $lang['ERROR_LOGIN'];
	if (!$login_err_msg) $login_err_msg = $lang['ERROR_LOGIN'];
// end of
}
// logout
else if (!empty($_GET['logout']))
{
	if (!IS_GUEST)
	{
		$user->session_end();
	}
	redirect("index.$phpEx");
}

// Login page
if (IS_GUEST || $mod_admin_login)
{
// dj_maxx: add visual confirmation to login form
	$confirm_image = '';
	$s_hidden_fields = '';

	if ($login_enable_confirm)
	{
		$db->query("
			DELETE cfm
			FROM ". CONFIRM_TABLE ." cfm
			LEFT JOIN ". SESSIONS_TABLE ." s USING(session_id)
			WHERE s.session_id IS NULL
		");

		$row = $db->fetch_row("
			SELECT COUNT(session_id) AS attempts
			FROM ". CONFIRM_TABLE ."
			WHERE session_id = '{$userdata['session_id']}'
		");

		if (isset($row['attempts']) && $row['attempts'] > 20)
		{
			message_die(GENERAL_MESSAGE, $lang['TOO_MANY_REGISTERS']);
		}

		$confirm_chars = array('1', '2', '3', '4', '5', '6', '7', '8', '9');

		$max_chars = count($confirm_chars) - 1;
		$confirm_code = '';
		for ($i = 0; $i < 3; $i++)
		{
			$confirm_code .= $confirm_chars[mt_rand(0, $max_chars)];
		}

		$confirm_id = make_rand_str(12);

		$db->query("
			INSERT INTO ". CONFIRM_TABLE ." (confirm_id, session_id, code)
			VALUES ('$confirm_id', '{$userdata['session_id']}', '$confirm_code')
		");

		$confirm_image = (extension_loaded('zlib')) ? '
			<img src="'. append_sid("profile.$phpEx?mode=confirm&amp;id=$confirm_id") .'" alt="" title="" />
		' : '
			<img src="'. append_sid("profile.$phpEx?mode=confirm&amp;id=$confirm_id&amp;c=1") .'" alt="" title="" />
			<img src="'. append_sid("profile.$phpEx?mode=confirm&amp;id=$confirm_id&amp;c=2") .'" alt="" title="" />
			<img src="'. append_sid("profile.$phpEx?mode=confirm&amp;id=$confirm_id&amp;c=3") .'" alt="" title="" />
			<img src="'. append_sid("profile.$phpEx?mode=confirm&amp;id=$confirm_id&amp;c=4") .'" alt="" title="" />
		';
		$s_hidden_fields .= '<input type="hidden" name="confirm_id" value="'. $confirm_id .'" />';
		$s_hidden_fields .= '<input type="hidden" name="sid" value="'. $userdata['session_id'] .'" />';

		$template->assign_block_vars('switch_confirm', array());
	}
	// end of
	$cookie_test_val = mt_rand();
	bb_setcookie(COOKIE_TEST, $cookie_test_val, COOKIE_SESSION);

	$template->assign_vars(array(
		'USERNAME'         => ($mod_admin_login) ? $user->data['username'] : '',

		'ERR_MSG'          => $login_err_msg,
		'T_ENTER_PASSWORD' => ($mod_admin_login) ? $lang['ADMIN_REAUTHENTICATE'] : $lang['ENTER_PASSWORD'],

		'U_SEND_PASSWORD'  => "profile.$phpEx?mode=sendpassword",
		'ADMIN_LOGIN'      => $mod_admin_login,
		'COOKIE_TEST_VAL'  => $cookie_test_val,
		'COOKIES_ERROR'    => ($login_error == 'cookie'),
		// dj_maxx: add visual confirmation to login form
		'CONFIRM_IMG' 	   => $confirm_image,
		'S_HIDDEN_FIELDS'  => $s_hidden_fields,
		// end of

		'REDIRECT_URL'     => $redirect_url,
	));

	print_page('login.tpl');
}

redirect("index.$phpEx");

