<?php

 /*
	Moderator Control Panel
  
    From this 'Control Panel' the moderator of a forum will be able to do
    mass topic operations (locking/unlocking/moving/deleteing), and it will
    provide an interface to do quick locking/unlocking/moving/deleting of
    topics via the moderator operations buttons on all of the viewtopic pages.
 
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
define('BB_SCRIPT', 'modcp');
define('BB_ROOT', './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require(BB_ROOT ."common.$phpEx");
require(INC_DIR .'bbcode.'. PHP_EXT);
require(INC_DIR .'functions_post.'. PHP_EXT);
require_once(INC_DIR .'functions_admin.'. PHP_EXT);

//
// Functions
//
function return_msg_mcp ($status_msg)
{
	global $topic_id, $req_topics, $forum_id, $lang, $mode;

	if (count($req_topics) == 1)
	{
		$topic_id = $req_topics[0];
	}

	$message = $status_msg;
	$message .= '<br /><br />';

	if ($topic_id && $mode != 'delete')
	{
		$message .= sprintf($lang['CLICK_RETURN_TOPIC'], '<a href="'. TOPIC_URL . $topic_id .'">', '</a>');
		$message .= '<br /><br />';
	}
	else if (count($req_topics) != 1)
	{
		$message .= sprintf($lang['CLICK_RETURN_MODCP'], '<a href="'. FORUM_URL ."$forum_id&amp;mod=1".'">', '</a>');
		$message .= '<br /><br />';
	}

	$message .= sprintf($lang['CLICK_RETURN_FORUM'], '<a href="'. FORUM_URL . $forum_id .'">', '</a>');

	return $message;
}

function validate_topics ($forum_id, &$req_topics, &$topic_titles)
{
	$valid_topics = $valid_titles = array();

	if ($topic_csv = get_id_csv($req_topics))
	{
		$sql = "SELECT topic_id, topic_title FROM ". TOPICS_TABLE ." WHERE topic_id IN($topic_csv) AND forum_id = $forum_id";

		foreach ($GLOBALS['db']->fetch_rowset($sql) as $row)
		{
			$valid_topics[] = $row['topic_id'];
			$valid_titles[] = $row['topic_title'];
		}
	}

	$req_topics = $valid_topics;
	$topic_titles = $valid_titles;
}

// Obtain initial vars
$forum_id = (int) @$_REQUEST['f'];
$topic_id = (int) @$_REQUEST['t'];
$post_id  = (int) @$_REQUEST['p'];

$start = isset($_REQUEST['start']) ? abs(intval($_REQUEST['start'])) : 0;
$confirmed = isset($_POST['confirm']);

$mode = $topic_title = '';

if (isset($_REQUEST['mode']))
{
	$mode = (string) $_REQUEST['mode'];
}
else
{
	if (isset($_REQUEST['delete']) || @$_POST['mod_action'] === 'topic_delete')
	{
		$mode = 'delete';
	}
	else if (isset($_REQUEST['move']) || @$_POST['mod_action'] === 'topic_move')
	{
		$mode = 'move';
	}
	else if (isset($_REQUEST['lock']) || @$_POST['mod_action'] === 'topic_lock')
	{
		$mode = 'lock';
	}
	else if (isset($_REQUEST['unlock']) || @$_POST['mod_action'] === 'topic_unlock')
	{
		$mode = 'unlock';
	}
}

// Obtain relevant data
if ($topic_id)
{
	$sql = "
		SELECT
			f.forum_id, f.forum_name, f.forum_topics, f.self_moderated,
			t.topic_first_post_id, t.topic_poster
		FROM ". TOPICS_TABLE ." t, ". FORUMS_TABLE ." f
		WHERE t.topic_id = $topic_id
			AND f.forum_id = t.forum_id
		LIMIT 1
	";

	if (!$topic_row = $db->fetch_row($sql))
	{
		message_die(GENERAL_MESSAGE, 'Topic_post_not_exist');
	}

	$forum_id = $topic_row['forum_id'];
	$forum_name = $topic_row['forum_name'];
	$forum_topics = (!$topic_row['forum_topics']) ? 1 : $topic_row['forum_topics'];
}
else if ($forum_id)
{
	$sql = "SELECT forum_name, forum_topics FROM ". FORUMS_TABLE ." WHERE forum_id = $forum_id LIMIT 1";

	if (!$topic_row = $db->fetch_row($sql))
	{
		message_die(GENERAL_MESSAGE, 'Forum_not_exist');
	}

	$forum_name = $topic_row['forum_name'];
	$forum_topics = (!$topic_row['forum_topics']) ? 1 : $topic_row['forum_topics'];
}
else
{
	message_die(GENERAL_MESSAGE, 'Invalid request');
}

// Start session management
$user->session_start(array('req_login' => true));

// Check if user did or did not confirm. If they did not, forward them to the last page they were on
if (isset($_POST['cancel']) || IS_GUEST)
{
	$redirect = "index.$phpEx";

	if ($topic_id || $forum_id)
	{
		$redirect = ($topic_id) ? TOPIC_URL . $topic_id : FORUM_URL . $forum_id;
	}
	redirect($redirect);
}

// Start auth check
$is_auth = auth(AUTH_ALL, $forum_id, $userdata);
$is_moderator = (IS_MOD || IS_ADMIN);

if ($mode == 'ip')
{
	// Moderator can view IP in all forums
	$is_auth['auth_mod'] = $is_moderator;
}
else if ($mode == 'move' && !$is_auth['auth_mod'])
{
	// User can move his own topic if this forum is "self_moderated"
	if ($topic_id && $topic_row['self_moderated'] && $topic_row['topic_poster'] == $userdata['user_id'])
	{
		$is_auth['auth_mod'] = true;

		$_POST['insert_bot_msg'] = 1;
		unset($_POST['topic_id_list']);
		unset($_POST['move_leave_shadow']);
	}
}

// Exit if user not authorized
if (!$is_auth['auth_mod'])
{
	message_die(GENERAL_MESSAGE, $lang['NOT_MODERATOR'], $lang['NOT_AUTHORISED']);
}

// Redirect to login page if not admin session
if ($is_moderator && !$userdata['session_admin'])
{
	$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : $_SERVER['REQUEST_URI'];
	redirect("login.$phpEx?redirect=$redirect&admin=1");
}

//
// Get required vars
//
$req_topics = $topic_csv = $topic_titles = $hidden_fields = array();

switch ($mode)
{
	case 'delete':
	case 'move':
	case 'lock':
	case 'unlock':
	case 'set_download':
	case 'unset_download':

		if (empty($_POST['topic_id_list']) && empty($topic_id))
		{
			message_die(GENERAL_MESSAGE, $lang['NONE_SELECTED']);
		}

		$req_topics = isset($_POST['topic_id_list']) ? $_POST['topic_id_list'] : $topic_id;
		validate_topics($forum_id, &$req_topics, &$topic_titles);

		if (!$req_topics OR !$topic_csv = get_id_csv($req_topics))
		{
			message_die(GENERAL_MESSAGE, $lang['NONE_SELECTED']);
		}

		$hidden_fields = array(
			'sid'  => $userdata['session_id'],
			'mode' => $mode,
			'f'    => $forum_id,
			't'    => $topic_id,
		);
		foreach ($req_topics as $req_topic_id)
		{
			$hidden_fields['topic_id_list'][] = $req_topic_id;
		}

	break;
}

//
// Perform action or show confirm message
//
switch ($mode)
{
	case 'delete':

		if (!$is_auth['auth_delete'])
		{
			message_die(GENERAL_MESSAGE, sprintf($lang['SORRY_AUTH_DELETE'], $is_auth['auth_delete_type']));
		}

		if ($confirmed)
		{
			$result = topic_delete($req_topics, $forum_id);

			$msg = ($result) ? $lang['TOPICS_REMOVED'] : 'No topics were removed';
			message_die(GENERAL_MESSAGE, return_msg_mcp($msg));
		}
		else
		{
			print_confirmation(array(
				'QUESTION'      => $lang['CONFIRM_DELETE_TOPIC'],
				'ITEMS_LIST'    => join("\n</li>\n<li>\n", $topic_titles),
				'FORM_ACTION'   => "modcp.$phpEx",
				'HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
			));
		}
		break;

	case 'move':

		if ($confirmed)
		{
			$result = topic_move($req_topics, $_POST['new_forum'], $forum_id, isset($_POST['move_leave_shadow']), isset($_POST['insert_bot_msg']));

			$msg = ($result) ? $lang['TOPICS_MOVED'] : $lang['NO_TOPICS_MOVED'];
			message_die(GENERAL_MESSAGE, return_msg_mcp($msg));
		}
		else
		{
			if (IS_ADMIN)
			{
				$forum_select_mode = 'admin';
			}
			else
			{
				$not_auth_forums_csv = $user->get_not_auth_forums(AUTH_VIEW);
				$forum_select_mode = explode(',', $not_auth_forums_csv);
			}

			$forum_select = get_forum_select($forum_select_mode, 'new_forum', $forum_id);

			$template->assign_vars(array(
				'TPL_MODCP_MOVE'   => true,
				'SHOW_LEAVESHADOW' => $is_moderator,
				'SHOW_BOT_OPTIONS' => $is_moderator,
				'L_LEAVE_MSG'      => $lang['BOT_LEAVE_MSG_MOVED'],

				'MESSAGE_TITLE' => $lang['CONFIRM'],
				'MESSAGE_TEXT'  => $lang['CONFIRM_MOVE_TOPIC'],
				'TOPIC_TITLES'  => join("\n</li>\n<li>\n", $topic_titles),

				'L_LEAVESHADOW'   => $lang['LEAVE_SHADOW_TOPIC'],

				'S_FORUM_SELECT'  => $forum_select,
				'S_MODCP_ACTION'  => "modcp.$phpEx",
				'S_HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
			));

			$template->set_filenames(array('body' => 'modcp.tpl'));
		}
	break;

	case 'lock':
	case 'unlock':
		$lock = ($mode == 'lock');
		$new_topic_status = ($lock) ? TOPIC_LOCKED : TOPIC_UNLOCKED;

		$sql = "
			SELECT topic_id, topic_title
			FROM ". TOPICS_TABLE ."
			WHERE topic_id IN($topic_csv)
				AND forum_id = $forum_id
				AND topic_status != ". TOPIC_MOVED ."
				AND topic_status != $new_topic_status
		";

		$topic_csv = array();

		foreach ($db->fetch_rowset($sql) as $row)
		{
			$topic_csv[] = $row['topic_id'];
			$log_topics[$row['topic_id']] = $row['topic_title'];
		}

		if (!$topic_csv = get_id_csv($topic_csv))
		{
			message_die(GENERAL_MESSAGE, $lang['NONE_SELECTED']);
		}

		$db->query("
			UPDATE ". TOPICS_TABLE ." SET
				topic_status = $new_topic_status
			WHERE topic_id IN($topic_csv)
		");

		// Log action
		$type = ($lock) ? 'mod_topic_lock' : 'mod_topic_unlock';

		foreach ($log_topics as $topic_id => $topic_title)
		{
			$log_action->mod($type, array(
				'forum_id'    => $forum_id,
				'topic_id'    => $topic_id,
				'topic_title' => $topic_title,
			));
		}

		$msg = ($lock) ? $lang['TOPICS_LOCKED'] : $lang['TOPICS_UNLOCKED'];
		message_die(GENERAL_MESSAGE, return_msg_mcp($msg));

	break;

	// Set or unset topics DL-type
	case 'set_download':
	case 'unset_download':
		$set_download = ($mode == 'set_download');
		$new_dl_type  = ($set_download) ? TOPIC_DL_TYPE_DL : TOPIC_DL_TYPE_NORMAL;

		$db->query("
			UPDATE ". TOPICS_TABLE ." SET
				topic_dl_type = $new_dl_type
			WHERE topic_id IN($topic_csv)
				AND forum_id = $forum_id
				AND topic_moved_id = 0
		");

		if ($mode == 'unset_download')
		{
			clear_dl_list($topic_csv);
		}

		$msg = ($set_download) ? $lang['TOPICS_DOWN_SETS'] : $lang['TOPICS_DOWN_UNSETS'];
		message_die(GENERAL_MESSAGE, return_msg_mcp($msg));

		break;

	case 'split':
		//mpd
		$delete_posts = isset($_POST['delete_posts']);
		$split = (isset($_POST['split_type_all']) || isset($_POST['split_type_beyond']));
		$posts = (isset($_POST['post_id_list'])) ? $_POST['post_id_list'] : array();
		$start = /* (isset($_POST['start'])) ? intval($_POST['start']) : */ 0;
		$topic_first_post_id = (isset($topic_row['topic_first_post_id'])) ? $topic_row['topic_first_post_id'] : '';

		$post_id_sql = $req_post_id_sql = array();

		if (($split || $delete_posts) && ($posts && $topic_id && $forum_id && $topic_first_post_id) && $confirmed)
		{
			foreach ($posts as $post_id)
			{
				if ($pid = intval($post_id))
				{
					$req_post_id_sql[] = $pid;
				}
			}
			if ($req_post_id_sql = join(',', $req_post_id_sql))
			{
				$sql = "SELECT post_id
					FROM ". POSTS_TABLE ."
					WHERE post_id IN($req_post_id_sql)
						AND post_id != $topic_first_post_id
						AND topic_id = $topic_id
						AND forum_id = $forum_id";

				if (!$result = $db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Could not get post id information', '', __LINE__, __FILE__, $sql);
				}
				if ($rowset = $db->sql_fetchrowset($result))
				{
					foreach ($rowset as $rid => $row)
					{
						$post_id_sql[] = $row['post_id'];
					}
					$post_id_sql = join(',', $post_id_sql);
				}
			}
		}

		if ($post_id_sql && $split)
		//mpd end
		{
			$sql = "SELECT post_id, poster_id, topic_id, post_time
				FROM " . POSTS_TABLE . "
				WHERE post_id IN ($post_id_sql)
				ORDER BY post_time ASC";
			if (!($result = $db->sql_query($sql)))
			{
				message_die(GENERAL_ERROR, 'Could not get post information', '', __LINE__, __FILE__, $sql);
			}

			if ($row = $db->sql_fetchrow($result))
			{
				$first_poster = $row['poster_id'];
				$topic_id = $row['topic_id'];
				$post_time = $row['post_time'];

				$user_id_sql = '';
				$post_id_sql = '';
				do
				{
					$user_id_sql .= (($user_id_sql != '') ? ', ' : '') . intval($row['poster_id']);
					$post_id_sql .= (($post_id_sql != '') ? ', ' : '') . intval($row['post_id']);;
				}
				while ($row = $db->sql_fetchrow($result));

				$post_subject = trim(htmlspecialchars($HTTP_POST_VARS['subject']));
				if (empty($post_subject))
				{
					message_die(GENERAL_MESSAGE, $lang['EMPTY_SUBJECT']);
				}

				$new_forum_id = intval($HTTP_POST_VARS['new_forum_id']);
				$topic_time = time();

				$sql = 'SELECT forum_id FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . $new_forum_id;
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, 'Could not select from forums table', '', __LINE__, __FILE__, $sql);
				}

				if (!$db->sql_fetchrow($result))
				{
					message_die(GENERAL_MESSAGE, 'New forum does not exist');
				}

				$db->sql_freeresult($result);

				$sql  = "INSERT INTO " . TOPICS_TABLE . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type)
					VALUES ('" . str_replace("\'", "''", $post_subject) . "', $first_poster, " . $topic_time . ", $new_forum_id, " . TOPIC_UNLOCKED . ", " . POST_NORMAL . ")";
				if (!($db->sql_query($sql)))
				{
					message_die(GENERAL_ERROR, 'Could not insert new topic', '', __LINE__, __FILE__, $sql);
				}

				$new_topic_id = $db->sql_nextid();

				// Update topic watch table, switch users whose posts
				// have moved, over to watching the new topic
				$sql = "UPDATE " . TOPICS_WATCH_TABLE . "
					SET topic_id = $new_topic_id
					WHERE topic_id = $topic_id
						AND user_id IN ($user_id_sql)";
				if (!$db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Could not update topics watch table', '', __LINE__, __FILE__, $sql);
				}

				$sql_where = (!empty($HTTP_POST_VARS['split_type_beyond'])) ? " post_time >= $post_time AND topic_id = $topic_id" : "post_id IN ($post_id_sql)";

				$sql = 	"UPDATE " . POSTS_TABLE . "
					SET topic_id = $new_topic_id, forum_id = $new_forum_id
					WHERE $sql_where";
				if (!$db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Could not update posts table', '', __LINE__, __FILE__, $sql);
				}

				//bot
				if (isset($_POST['after_split_to_old']))
				{
					insert_post('after_split_to_old', $topic_id, $forum_id, '', $new_topic_id, trim($_POST['subject']));
				}
				if (isset($_POST['after_split_to_new']))
				{
					insert_post('after_split_to_new', $new_topic_id, $new_forum_id, $forum_id, $new_topic_id, '', $topic_id);
				}
				//bot end

				sync('topic', array($topic_id, $new_topic_id));
				sync('forum', array($forum_id, $new_forum_id));

				//bot
				$message = $lang['TOPIC_SPLIT'] .'<br /><br /><a href="' . "viewtopic.$phpEx?". POST_TOPIC_URL ."=$topic_id&amp;sid=". $userdata['session_id'] .'">'. $lang['TOPIC_SPLIT_OLD'] .'</a>';
				$message .= ' &nbsp;::&nbsp; <a href="' . "viewtopic.$phpEx?". POST_TOPIC_URL ."=$new_topic_id&amp;sid=". $userdata['session_id'] .'">'. $lang['TOPIC_SPLIT_NEW'] .'</a>';
				//bot end

				// Log action
				$log_action->mod('mod_topic_split', array(
					'forum_id'        => $forum_id,
					'forum_id_new'    => $new_forum_id,
					'topic_id'        => $topic_id,
					'topic_title'     => get_topic_title($topic_id),
					'topic_id_new'    => $new_topic_id,
					'topic_title_new' => htmlCHR($_POST['subject']),
				));

				message_die(GENERAL_MESSAGE, $message);
			}
		}
		//mpd
		else if ($post_id_sql && $delete_posts)
		{
			if (!$is_auth['auth_delete'])
			{
				message_die(GENERAL_MESSAGE, sprintf($lang['SORRY_AUTH_DELETE'], $is_auth['auth_delete_type']));
			}

			// Delete posts
			$result = post_delete(explode(',', $post_id_sql));

			$msg = ($result) ? $lang['DELETE_POSTS_SUCCESFULLY'] : 'No posts were removed';
			message_die(GENERAL_MESSAGE, return_msg_mcp($msg));
		}
		//mpd end
		else
		{
			$sql = "SELECT u.username, p.*, pt.post_text, pt.bbcode_uid, pt.post_subject, p.post_username
				FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
				WHERE p.topic_id = $topic_id
					AND p.poster_id = u.user_id
					AND p.post_id = pt.post_id
				ORDER BY p.post_time ASC";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not get topic/post information', '', __LINE__, __FILE__, $sql);
			}

			$s_hidden_fields = '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" /><input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '" /><input type="hidden" name="' . POST_TOPIC_URL . '" value="' . $topic_id . '" /><input type="hidden" name="mode" value="split" />';

			if( ( $total_posts = $db->sql_numrows($result) ) > 0 )
			{
				$postrow = $db->sql_fetchrowset($result);

				$template->assign_vars(array(
					'FORUM_NAME' => htmlCHR($forum_name),
					'U_VIEW_FORUM' => FORUM_URL . $forum_id,
					'S_SPLIT_ACTION' => "modcp.$phpEx",
					'S_HIDDEN_FIELDS' => $s_hidden_fields,
					'S_FORUM_SELECT' => get_forum_select('admin', 'new_forum_id', $forum_id),
				));

				for($i = 0; $i < $total_posts; $i++)
				{
					$post_id = $postrow[$i]['post_id'];
					$poster_id = $postrow[$i]['poster_id'];
					$poster = $postrow[$i]['username'];

					$post_date = create_date($board_config['default_dateformat'], $postrow[$i]['post_time'], $board_config['board_timezone']);

					$bbcode_uid = $postrow[$i]['bbcode_uid'];
					$message = $postrow[$i]['post_text'];
					$post_subject = ( isset($postrow[$i]['post_subject']) ) ? $postrow[$i]['post_subject'] : $topic_title;

					//
					// If the board has HTML off but the post has HTML
					// on then we process it, else leave it alone
					//
					if ( $bbcode_uid != '' )
					{
						$message = ( $board_config['allow_bbcode'] ) ? bbencode_second_pass($message, $bbcode_uid) : preg_replace('/\:[0-9a-z\:]+\]/si', ']', $message);
					}

					$message = make_clickable($message);

					if ( $board_config['allow_smilies'] && $postrow[$i]['enable_smilies'] )
					{
						$message = smilies_pass($message);
					}

					$message = str_replace("\n", '<br />', $message);

					$row_class = !($i % 2) ? 'row1' : 'row2';

					$template->assign_block_vars('postrow', array(
						'ROW_CLASS' => $row_class,
						'POSTER_NAME' => wbr($poster),
						'POST_DATE' => $post_date,
						'POST_SUBJECT' => $post_subject,
						'MESSAGE' => $message,

						'CHECKBOX' => (defined('BEGIN_CHECKBOX')) ? TRUE : FALSE,
						'POST_ID'  => $post_id,
						'ROW_ID'   => $i,
						'CB_ID'    => 'cb_'. $i
					));

					if ($post_id == $topic_first_post_id)
					{
						define('BEGIN_CHECKBOX', TRUE);
					}
				}
			}
		}
		$template->set_filenames(array('body' => 'modcp_split.tpl'));
		break;

	case 'ip':
		$anon = ANONYMOUS;

		$rdns_ip_num = ( isset($HTTP_GET_VARS['rdns']) ) ? $HTTP_GET_VARS['rdns'] : "";

		if ( !$post_id )
		{
			message_die(GENERAL_MESSAGE, $lang['NO_SUCH_POST']);
		}

		// Look up relevent data for this post
		$sql = "SELECT *
			FROM " . POSTS_TABLE . "
			WHERE post_id = $post_id
				AND forum_id = $forum_id";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not get poster IP information', '', __LINE__, __FILE__, $sql);
		}

		if ( !($post_row = $db->sql_fetchrow($result)) )
		{
			message_die(GENERAL_MESSAGE, $lang['NO_SUCH_POST']);
		}

		$ip_this_post = decode_ip($post_row['poster_ip']);
		$ip_this_post = ( $rdns_ip_num == $ip_this_post ) ? gethostbyaddr($ip_this_post) : $ip_this_post;

		$poster_id = $post_row['poster_id'];

		$template->assign_vars(array(
			'TPL_MODCP_IP'   => true,
			'L_THIS_POST_IP' => $lang['THIS_POSTS_IP'],
			'L_OTHER_IPS'    => $lang['OTHER_IP_THIS_USER'],
			'L_OTHER_USERS'  => $lang['USERS_THIS_IP'],
			'IP'             => $ip_this_post,
			'U_LOOKUP_IP'    => "modcp.$phpEx?mode=ip&amp;" . POST_POST_URL . "=$post_id&amp;" . POST_TOPIC_URL . "=$topic_id&amp;rdns=$ip_this_post&amp;sid=" . $userdata['session_id'])
		);

		//
		// Get other IP's this user has posted under
		//
		$where_sql = ($poster_id == $anon) ? "post_username = '{$post_row['post_username']}'" : "poster_id = $poster_id";

		$sql = "SELECT poster_ip, COUNT(*) AS postings
			FROM " . POSTS_TABLE . "
			WHERE $where_sql
			GROUP BY poster_ip
			ORDER BY postings DESC
			LIMIT 100";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not get IP information for this user', '', __LINE__, __FILE__, $sql);
		}

		if ( $row = $db->sql_fetchrow($result) )
		{
			$i = 0;
			do
			{
				if ( $row['poster_ip'] == $post_row['poster_ip'] )
				{
					$template->assign_vars(array(
						'POSTS' => $row['postings'],
					));
					continue;
				}

				$ip = decode_ip($row['poster_ip']);
				$ip = ( $rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') ? gethostbyaddr($ip) : $ip;

				$template->assign_block_vars('iprow', array(
					'ROW_CLASS'   => !($i % 2) ? 'row4' : 'row5',
					'IP'          => $ip,
					'POSTS'       => $row['postings'],
					'U_LOOKUP_IP' => "modcp.$phpEx?mode=ip&amp;" . POST_POST_URL . "=$post_id&amp;" . POST_TOPIC_URL . "=$topic_id&amp;rdns=" . $row['poster_ip'] . "&amp;sid=" . $userdata['session_id'],
				));

				$i++;
			}
			while ( $row = $db->sql_fetchrow($result) );
		}

		//
		// Get other users who've posted under this IP
		//
		$sql = "SELECT
				u.user_id,
				IF(u.user_id = $anon, p.post_username, u.username) AS username,
				COUNT(*) as postings
			FROM " . USERS_TABLE ." u, " . POSTS_TABLE . " p
			WHERE p.poster_id = u.user_id
				AND p.poster_ip = '" . $post_row['poster_ip'] . "'
			GROUP BY u.user_id, p.post_username
			ORDER BY postings DESC
			LIMIT 100";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not get posters information based on IP', '', __LINE__, __FILE__, $sql);
		}

		if ( $row = $db->sql_fetchrow($result) )
		{
			$i = 0;
			do
			{
				$id = $row['user_id'];
				$username = (!$row['username']) ? $lang['GUEST'] : $row['username'];

				$template->assign_block_vars('userrow', array(
					'ROW_CLASS'      => !($i % 2) ? 'row4' : 'row5',
					'USERNAME'       => wbr($username),
					'POSTS'          => $row['postings'],
					'L_SEARCH_POSTS' => $lang['SEARCH_USER_POSTS_SHORT'],
					'U_PROFILE'      => ($id == ANONYMOUS) ? "modcp.$phpEx?mode=ip&amp;p=$post_id&amp;t=$topic_id" : PROFILE_URL . $id,
					'U_SEARCHPOSTS'  => "search.$phpEx?search_author=1&amp;uid=$id",
				));

				$i++;
			}
			while ( $row = $db->sql_fetchrow($result) );
		}

		$template->set_filenames(array('body' => 'modcp.tpl'));
		break;

	default:
		bb_die('invalid action');
		break;
}

$template->assign_vars(array('PAGE_TITLE' => $lang['MOD_CP']));

require(PAGE_HEADER);

$template->pparse('body');

require(PAGE_FOOTER);

