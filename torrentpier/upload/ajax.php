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

define('IN_AJAX', true);
$ajax =& new ajax_common();

require('./common.php');

$ajax->init();

// Handle "board disabled via ON/OFF trigger"
if (file_exists(BB_DISABLED))
{
	$ajax->ajax_die($bb_cfg['board_disabled_msg']);
}

// Load actions required modules
switch ($ajax->action)
{
	case 'view_post':
		require(INC_DIR .'bbcode.'. PHP_EXT);
	break;
}

// position in $ajax->valid_actions['xxx']
define('AJAX_AUTH', 0);  //  'guest', 'user', 'mod', 'admin'

$user->session_start();
$ajax->exec();

//
// Ajax
//
class ajax_common
{
	var $request  = array();
	var $response = array();

	var $valid_actions = array(
	//   ACTION NAME             AJAX_AUTH
		'edit_user_profile' => array('admin'),
		'view_post'         => array('guest'),
	);

	var $action = null;

	/**
	*  Constructor
	*/
	function ajax_common ()
	{
		ob_start(array(&$this, 'ob_handler'));
		header('Content-Type: text/plain');
	}

	/**
	*  Perform action
	*/
	function exec ()
	{
		global $lang;

		// Exit if we already have errors
		if (!empty($this->response['error_code']))
		{
			$this->send();
		}

		// Check that requested action is valid
		$action = $this->action;

		if (!$action || !is_string($action))
		{
			$this->ajax_die('no action specified');
		}
		else if (!$action_params =& $this->valid_actions[$action])
		{
			$this->ajax_die('invalid action: '. $action);
		}

		// Auth check
		switch ($action_params[AJAX_AUTH])
		{
			// GUEST
			case 'guest':
				break;

			// USER
			case 'user':
				if (IS_GUEST)
				{
					$this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
				}
				break;

			// MOD
			case 'mod':
				if (!(IS_MOD || IS_ADMIN))
				{
					$this->ajax_die($lang['ONLY_FOR_MOD']);
				}
				$this->check_admin_session();
				break;

			// ADMIN
			case 'admin':
				if (!IS_ADMIN)
				{
					$this->ajax_die($lang['ONLY_FOR_ADMIN']);
				}
				$this->check_admin_session();
				break;

			default:
				trigger_error("invalid auth type for $action", E_USER_ERROR);
		}

		// Run action
		$this->$action();

		// Send output
		$this->send();
	}

	/**
	*  Exit on error
	*/
	function ajax_die ($error_msg, $error_code = E_AJAX_GENERAL_ERROR)
	{
		$this->response['error_code'] = $error_code;
		$this->response['error_msg'] = $error_msg;

		$this->send();
	}

	/**
	*  Initialization
	*/
	function init ()
	{
		$this->request = $_POST;

		$this->action  =& $this->request['action'];
	}

	/**
	*  Send data
	*/
	function send ()
	{
		$this->response['action'] = $this->action;

		if (DBG_USER && SQL_DEBUG && !empty($_COOKIE['sql_log']))
		{
			$this->response['sql_log'] = get_sql_log();
		}

		// sending output will be handled by $this->ob_handler()
		exit();
	}

	/**
	*  OB Handler
	*/
	function ob_handler ($contents)
	{
		if (DBG_USER)
		{
			if ($contents)
			{
				$this->response['raw_output'] = $contents;
			}
		}


		$response_js = bb_json_encode($this->response);

		if (GZIP_OUTPUT_ALLOWED && !defined('NO_GZIP'))
		{
			if (UA_GZIP_SUPPORTED && strlen($response_js) > 2000)
			{
				header('Content-Encoding: gzip');
				$response_js = gzencode($response_js, 1);
			}
		}

		return $response_js;
	}

	/**
	*  Admin session
	*/
	function check_admin_session ()
	{
		global $user, $HTTP_POST_VARS;

		if (!$user->data['session_admin'])
		{
			if (empty($this->request['user_password']))
			{
				$this->prompt_for_password();
			}
			else
			{
				$login_args = array(
					'login_username' => $user->data['username'],
					'login_password' => $HTTP_POST_VARS['user_password'],  // $HTTP_POST_VARS - for compatibility with phpbb
				);
				if (!$user->login($login_args, true))
				{
					$this->ajax_die('Wrong password');
				}
			}
		}
	}

	/**
	*  Prompt for password
	*/
	function prompt_for_password ()
	{
		$this->response['prompt_password'] = 1;
		$this->send();
	}

	/**
	*  Edit user profile
	*/
	function edit_user_profile ()
	{
		global $db, $bb_cfg, $lang;

		if (!$user_id = intval($this->request['user_id']) OR !$profiledata = get_userdata($user_id))
		{
			$this->ajax_die('invalid user_id');
		}
		if (!$field = (string) $this->request['field'])
		{
			$this->ajax_die('invalid profile field');
		}

		$table = USERS_TABLE;
		$value = (string) $this->request['value'];

		switch ($field)
		{
			case 'user_regdate':
			case 'user_lastvisit':
				$tz = TIMENOW + (3600 * $bb_cfg['board_timezone']);
				if (($value = strtotime($value, $tz)) < $bb_cfg['board_startdate'] OR $value > TIMENOW)
				{
					$this->ajax_die('invalid date: '. $this->request['value']);
				}
				$this->response['new_value'] = bb_date($value);
				break;

			case 'ignore_srv_load':
				$value = ($this->request['value']) ? 0 : 1;
				$this->response['new_value'] = ($profiledata['user_level'] != USER || $value) ? $lang['NO'] : $lang['YES'];
				break;

			case 'u_up_total':
			case 'u_down_total':
			case 'u_up_release':
			case 'u_up_bonus':
				if (!IS_SUPER_ADMIN)
				{
					$this->ajax_die($lang['ONLY_FOR_SUPER_ADMIN']);
				}

				$table = BT_USERS_TABLE;
				$value = (float) $this->request['value'];

				foreach (array('KB'=>1,'MB'=>2,'GB'=>3,'TB'=>4) as $s => $m)
				{
					if (strpos($this->request['value'], $s) !== false)
					{
						$value *= pow(1024, $m);
						break;
					}
				}
				$value = sprintf('%.0f', $value);
				$this->response['new_value'] = humn_size($value, null, null, ' ');

				if (!$btu = get_bt_userdata($user_id))
				{
					require(INC_DIR .'functions_torrent.'. PHP_EXT);
					generate_passkey($user_id, true);
					$btu = get_bt_userdata($user_id);
				}
				$btu[$field] = $value;
				$this->response['update_ids']['u_ratio'] = (string) get_bt_ratio($btu);
				break;

			default:
				$this->ajax_die("invalid profile field: $field");
		}

		$value_sql = $db->escape($value, true);
		$db->query("UPDATE $table SET $field = $value_sql WHERE user_id = $user_id LIMIT 1");

		$this->response['edit_id'] = $this->request['edit_id'];
	}

	/**
	*  View post
	*/
	function view_post ()
	{
		global $user, $db, $lang;

		$post_id = (int) $this->request['post_id'];

		$sql = "
			SELECT
			  p.*,
			  h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text,
			  pt.post_subject, pt.bbcode_uid,
			  f.auth_read
			FROM       ". POSTS_TABLE      ." p
			INNER JOIN ". POSTS_TEXT_TABLE ." pt ON(pt.post_id = p.post_id)
			 LEFT JOIN ". POSTS_HTML_TABLE ." h  ON(h.post_id = pt.post_id)
			INNER JOIN ". FORUMS_TABLE     ." f  ON(f.forum_id = p.forum_id)
			WHERE
			  p.post_id = $post_id
			LIMIT 1
		";

		if (!$post_data = $db->fetch_row($sql))
		{
			$this->ajax_die($lang['TOPIC_POST_NOT_EXIST']);
		}

		// Auth check
		if ($post_data['auth_read'] == AUTH_REG)
		{
			if (IS_GUEST)
			{
				$this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
			}
		}
		else if ($post_data['auth_read'] != AUTH_ALL)
		{
			$is_auth = auth(AUTH_READ, $post_data['forum_id'], $user->data, $post_data);
			if (!$is_auth['auth_read'])
			{
				$this->ajax_die($lang['TOPIC_POST_NOT_EXIST']);
			}
		}

		$this->response['post_id']   = $post_id;
		$this->response['post_html'] = get_parsed_post($post_data);
	}
}
