<?php
/***************************************************************************
 *                             usercp_email.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: usercp_email.php,v 1.7.2.13 2003/06/06 18:02:15 acydburn Exp $
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
	exit;
}

// Is send through board enabled? No, return to index
if (!$board_config['board_email_form'])
{
	redirect(append_sid("index.$phpEx", true));
}

if ( !empty($HTTP_GET_VARS[POST_USERS_URL]) || !empty($HTTP_POST_VARS[POST_USERS_URL]) )
{
	$user_id = ( !empty($HTTP_GET_VARS[POST_USERS_URL]) ) ? intval($HTTP_GET_VARS[POST_USERS_URL]) : intval($HTTP_POST_VARS[POST_USERS_URL]);
}
else
{
	message_die(GENERAL_MESSAGE, $lang['NO_USER_SPECIFIED']);
}

if ( !$userdata['session_logged_in'] )
{
	redirect(append_sid("login.$phpEx?redirect=profile.$phpEx&mode=email&" . POST_USERS_URL . "=$user_id", true));
}

$sql = "SELECT username, user_email, user_lang
	FROM " . USERS_TABLE . "
	WHERE user_id = $user_id";
if ( $row = $db->fetch_row($sql) )
{
	$username = $row['username'];
	$user_email = $row['user_email'];
	$user_lang = $row['user_lang'];

	if ( true || IS_ADMIN )  //  TRUE instead of missing user_opt "prevent_email"
	{
		if ( isset($HTTP_POST_VARS['submit']) )
		{
			$error = FALSE;

			if ( !empty($HTTP_POST_VARS['subject']) )
			{
				$subject = trim(stripslashes($HTTP_POST_VARS['subject']));
			}
			else
			{
				$error = TRUE;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . '<br />' . $lang['EMPTY_SUBJECT_EMAIL'] : $lang['EMPTY_SUBJECT_EMAIL'];
			}

			if ( !empty($HTTP_POST_VARS['message']) )
			{
				$message = trim(stripslashes($HTTP_POST_VARS['message']));
			}
			else
			{
				$error = TRUE;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . '<br />' . $lang['EMPTY_MESSAGE_EMAIL'] : $lang['EMPTY_MESSAGE_EMAIL'];
			}

			if ( !$error )
			{
				require(INC_DIR . 'emailer.'.$phpEx);
				$emailer = new emailer($board_config['smtp_delivery']);

				$emailer->from($userdata['user_email']);
				$emailer->replyto($userdata['user_email']);

				$email_headers = 'X-AntiAbuse: Board servername - ' . $server_name . "\n";
				$email_headers .= 'X-AntiAbuse: User_id - ' . $userdata['user_id'] . "\n";
				$email_headers .= 'X-AntiAbuse: Username - ' . $userdata['username'] . "\n";
				$email_headers .= 'X-AntiAbuse: User IP - ' . CLIENT_IP . "\n";

				$emailer->use_template('profile_send_email', $user_lang);
				$emailer->email_address($user_email);
				$emailer->set_subject($subject);
				$emailer->extra_headers($email_headers);

				$emailer->assign_vars(array(
					'SITENAME' => $board_config['sitename'],
					'BOARD_EMAIL' => $board_config['board_email'],
					'FROM_USERNAME' => $userdata['username'],
					'TO_USERNAME' => $username,
					'MESSAGE' => $message)
				);
				$emailer->send();
				$emailer->reset();

				if ( !empty($HTTP_POST_VARS['cc_email']) )
				{
					$emailer->from($userdata['user_email']);
					$emailer->replyto($userdata['user_email']);
					$emailer->use_template('profile_send_email');
					$emailer->email_address($userdata['user_email']);
					$emailer->set_subject($subject);

					$emailer->assign_vars(array(
						'SITENAME' => $board_config['sitename'],
						'BOARD_EMAIL' => $board_config['board_email'],
						'FROM_USERNAME' => $userdata['username'],
						'TO_USERNAME' => $username,
						'MESSAGE' => $message)
					);
					$emailer->send();
					$emailer->reset();
				}

				sleep(7);
				$message = $lang['EMAIL_SENT'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="' . append_sid("index.$phpEx") . '">', '</a>');
				message_die(GENERAL_MESSAGE, $message);
			}
		}

		if (!empty($error))
		{
			$template->assign_vars(array('ERROR_MESSAGE' => $error_msg));
		}

		$template->assign_vars(array(
			'USERNAME' => $username,

			'S_HIDDEN_FIELDS' => '',
			'S_POST_ACTION' => append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL . "=$user_id"),

			'L_MESSAGE_BODY_DESC' => $lang['EMAIL_MESSAGE_DESC'],
		));

		print_page('usercp_email.tpl');
	}
	else
	{
		message_die(GENERAL_MESSAGE, $lang['USER_PREVENT_EMAIL']);
	}
}
else
{
	message_die(GENERAL_MESSAGE, $lang['USER_NOT_EXIST']);
}

