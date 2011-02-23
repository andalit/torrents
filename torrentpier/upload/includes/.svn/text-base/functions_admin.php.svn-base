<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

function sync ($type, $id)
{
	global $db;

	switch ($type)
	{
	  case 'forum':

			$all_forums = ($id === 'all');

			if (!$all_forums AND !$forum_csv = get_id_csv($id))
			{
				break;
			}

			$tmp_sync_forums = 'tmp_sync_forums';

			$db->query("
				CREATE TEMPORARY TABLE $tmp_sync_forums (
					forum_id           SMALLINT  UNSIGNED NOT NULL DEFAULT '0',
					forum_last_post_id MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					forum_posts        MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					forum_topics       MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					PRIMARY KEY (forum_id)
				) ENGINE = MyISAM
			");

			$where_sql = (!$all_forums) ? "WHERE f.forum_id IN($forum_csv)" : '';

			$db->query("
				INSERT INTO $tmp_sync_forums
				SELECT
					f.forum_id,
					MAX(p.post_id),
					COUNT(p.post_id),
					COUNT(DISTINCT p.topic_id)
				FROM      ". FORUMS_TABLE ." f
				LEFT JOIN ". POSTS_TABLE  ." p USING(forum_id)
					$where_sql
				GROUP BY f.forum_id
			");

			$db->query("
				UPDATE
					$tmp_sync_forums tmp, ". FORUMS_TABLE ." f
				SET
					f.forum_last_post_id = tmp.forum_last_post_id,
					f.forum_posts        = tmp.forum_posts,
					f.forum_topics       = tmp.forum_topics
				WHERE
					f.forum_id = tmp.forum_id
			");

			$db->query("DROP TEMPORARY TABLE $tmp_sync_forums");

			break;

		case 'topic':

			$all_topics = ($id === 'all');

			if (!$all_topics AND !$topic_csv = get_id_csv($id))
			{
				break;
			}

			$tmp_sync_topics = 'tmp_sync_topics';

			$db->query("
				CREATE TEMPORARY TABLE $tmp_sync_topics (
					topic_id             MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					total_posts          MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					topic_first_post_id  MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					topic_last_post_id   MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					topic_last_post_time INT       UNSIGNED NOT NULL DEFAULT '0',
					topic_attachment     TINYINT   UNSIGNED NOT NULL DEFAULT '0',
					PRIMARY KEY (topic_id)
				) ENGINE = MyISAM
			");

			$where_sql = (!$all_topics) ? "AND t.topic_id IN($topic_csv)" : '';

			$db->query("
				INSERT INTO $tmp_sync_topics
				SELECT
					t.topic_id,
					COUNT(p.post_id) AS total_posts,
					MIN(p.post_id) AS topic_first_post_id,
					MAX(p.post_id) AS topic_last_post_id,
					MAX(p.post_time) AS topic_last_post_time,
					IF(MAX(a.attach_id), 1, 0) AS topic_attachment
				FROM      ". TOPICS_TABLE      ." t
				LEFT JOIN ". POSTS_TABLE       ." p ON(p.topic_id = t.topic_id)
				LEFT JOIN ". ATTACHMENTS_TABLE ." a ON(a.post_id = p.post_id)
				WHERE t.topic_status != ". TOPIC_MOVED ."
					$where_sql
				GROUP BY t.topic_id
			");

			$db->query("
				UPDATE
					$tmp_sync_topics tmp, ". TOPICS_TABLE ." t
				SET
					t.topic_replies        = tmp.total_posts - 1,
					t.topic_first_post_id  = tmp.topic_first_post_id,
					t.topic_last_post_id   = tmp.topic_last_post_id,
					t.topic_last_post_time = tmp.topic_last_post_time,
					t.topic_attachment     = tmp.topic_attachment
				WHERE
					t.topic_id = tmp.topic_id
			");

			$sql = "SELECT topic_id FROM ". $tmp_sync_topics ." WHERE total_posts = 0";

			if ($rowset = $db->fetch_rowset($sql))
			{
				$topics = array();
				foreach ($rowset as $row)
				{
					$topics[] = $row['topic_id'];
				}
				topic_delete($topics);
			}

			$db->query("DROP TEMPORARY TABLE $tmp_sync_topics");

			break;

		case 'user_posts':

			$all_users = ($id === 'all');

			if (!$all_users AND !$user_csv = get_id_csv($id))
			{
				break;
			}

			$tmp_user_posts = 'tmp_sync_user_posts';

			$db->query("
				CREATE TEMPORARY TABLE $tmp_user_posts (
					user_id    MEDIUMINT NOT NULL DEFAULT '0',
					user_posts MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					PRIMARY KEY (user_id)
				) ENGINE = MyISAM
			");

			// Set posts count = 0 and then update to real count
			$where_user_sql = (!$all_users) ? "AND user_id IN($user_csv)" : "AND user_posts != 0";
			$where_post_sql = (!$all_users) ? "AND poster_id IN($user_csv)" : '';

			$db->query("
				REPLACE INTO $tmp_user_posts
					SELECT user_id, 0
					FROM ". USERS_TABLE ."
					WHERE user_id != ". ANONYMOUS ."
						$where_user_sql
				UNION
					SELECT poster_id, COUNT(*)
					FROM ". POSTS_TABLE ."
					WHERE poster_id != ". ANONYMOUS ."
						$where_post_sql
					GROUP BY poster_id
			");

			$db->query("
				UPDATE
					$tmp_user_posts tmp, ". USERS_TABLE ." u
				SET
					u.user_posts = tmp.user_posts
				WHERE
					u.user_id = tmp.user_id
			");

			$db->query("DROP TEMPORARY TABLE $tmp_user_posts");

		break;
	}
}

function topic_delete ($mode_or_topic_id, $forum_id = null, $prune_time = 0, $prune_all = false)
{
	global $db, $lang, $bb_cfg, $log_action;

	$prune = ($mode_or_topic_id === 'prune');

	if (!$prune AND !$topic_csv = get_id_csv($mode_or_topic_id))
	{
		return false;
	}

	$log_topics = $sync_forums = array();

	if ($prune)
	{
		$sync_forums[$forum_id] = true;
	}
	else
	{
		$where_sql = ($forum_csv = get_id_csv($forum_id)) ? "AND forum_id IN($forum_csv)" : '';

		$sql = "
			SELECT topic_id, forum_id, topic_title, topic_status
			FROM ". TOPICS_TABLE ."
			WHERE topic_id IN($topic_csv)
				$where_sql
		";

		$topic_csv = array();

		foreach ($db->fetch_rowset($sql) as $row)
		{
			$topic_csv[] = $row['topic_id'];
			$log_topics[] = $row;
			$sync_forums[$row['forum_id']] = true;
		}

		if (!$topic_csv = get_id_csv($topic_csv))
		{
			return false;
		}
	}

	// Get topics to delete
	$tmp_delete_topics = 'tmp_delete_topics';

	$db->query("
		CREATE TEMPORARY TABLE $tmp_delete_topics (
			topic_id MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (topic_id)
		) ENGINE = MyISAM
	");

	$where_sql  = ($prune) ? "forum_id = $forum_id" : "topic_id IN($topic_csv)";
	$where_sql .= ($prune && $prune_time) ? " AND topic_last_post_time < $prune_time" : '';
	$where_sql .= ($prune && !$prune_all) ? " AND topic_type NOT IN(". POST_ANNOUNCE .",". POST_STICKY .")": '';

	$db->query("
		INSERT INTO $tmp_delete_topics
			SELECT topic_id
			FROM ". TOPICS_TABLE ."
			WHERE $where_sql
	");

	// Get topics count
	$row = $db->fetch_row("SELECT COUNT(*) AS topics_count FROM $tmp_delete_topics");

	if (!$deleted_topics_count = $row['topics_count'])
	{
		$db->query("DROP TEMPORARY TABLE $tmp_delete_topics");
		return 0;
	}

	// Update user posts count
	$tmp_user_posts = 'tmp_user_posts';

	$db->query("
		CREATE TEMPORARY TABLE $tmp_user_posts (
			user_id    MEDIUMINT NOT NULL DEFAULT '0',
			user_posts MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (user_id)
		) ENGINE = MyISAM
	");

	$db->query("
		INSERT INTO $tmp_user_posts
			SELECT p.poster_id, COUNT(p.post_id)
			FROM ". $tmp_delete_topics ." del, ". POSTS_TABLE ." p
			WHERE p.topic_id = del.topic_id
				AND p.poster_id != ". ANONYMOUS ."
			GROUP BY p.poster_id
	");

	$db->query("
		UPDATE
			$tmp_user_posts tmp, ". USERS_TABLE ." u
		SET
			u.user_posts = u.user_posts - tmp.user_posts
		WHERE
			u.user_id = tmp.user_id
	");

	$db->query("DROP TEMPORARY TABLE $tmp_user_posts");

	// Delete votes
	$db->query("
		DELETE vd, vr, vu
		FROM      ". $tmp_delete_topics ." del
		LEFT JOIN ". VOTE_DESC_TABLE    ." vd USING(topic_id)
		LEFT JOIN ". VOTE_RESULTS_TABLE ." vr USING(vote_id)
		LEFT JOIN ". VOTE_USERS_TABLE   ." vu USING(vote_id)
	");

	if ($bb_cfg['auto_delete_posted_pics'])
	{
		$result = $db->sql_query("
			SELECT ph.post_id, ph.post_html
			FROM $tmp_delete_topics tmp
			LEFT JOIN ". POSTS_TABLE ." p USING(topic_id)
			LEFT JOIN ". POSTS_HTML_TABLE ." ph ON(p.post_id = ph.post_id)		
		");

		while ( $post = $db->sql_fetchrow($result) )
		{
			preg_match_all('#<var.*?title="(.*?)"#', $post['post_html'], $matches, PREG_SET_ORDER);

			foreach($matches as $match)
			{
				$have = $db->fetch_row("
					SELECT post_id
					FROM ". POSTS_HTML_TABLE ."
					WHERE post_html LIKE '%". $db->escape($match[1]). "%'
						AND post_id != {$post['post_id']}
				");

				if(empty($have))
				{
					@unlink(BB_ROOT . $bb_cfg['pic_dir'] . end(explode('/', $match[1])));
				}
			}
		}
	}

	// Delete attachments (from disk)
	$attach_dir = get_attachments_dir();

	$result = $db->query("
		SELECT
			d.physical_filename
		FROM
			". $tmp_delete_topics     ." del,
			". POSTS_TABLE            ." p,
			". ATTACHMENTS_TABLE      ." a,
			". ATTACHMENTS_DESC_TABLE ." d
		WHERE
			    p.topic_id = del.topic_id
			AND a.post_id = p.post_id
			AND d.attach_id = a.attach_id
	");

	while ($row = $db->fetch_next($result))
	{
		if ($filename = basename($row['physical_filename']))
		{
			@unlink("$attach_dir/". $filename);
			@unlink("$attach_dir/". THUMB_DIR .'/t_'. $filename);
		}
	}
	unset($row, $result);

	// Delete posts, posts_text, attachments (from DB)
	$db->query("
		DELETE p, pt, ps, a, d
		FROM      ". $tmp_delete_topics     ." del
		LEFT JOIN ". POSTS_TABLE            ." p  ON(p.topic_id = del.topic_id)
		LEFT JOIN ". POSTS_TEXT_TABLE       ." pt ON(pt.post_id = p.post_id)
		LEFT JOIN ". POSTS_SEARCH_TABLE     ." ps ON(ps.post_id = p.post_id)
		LEFT JOIN ". ATTACHMENTS_TABLE      ." a  ON(a.post_id = p.post_id)
		LEFT JOIN ". ATTACHMENTS_DESC_TABLE ." d  ON(d.attach_id = a.attach_id)
	");

	// Delete topics, topics watch
	$db->query("
		DELETE t, tw
		FROM      ". $tmp_delete_topics ." del
		LEFT JOIN ". TOPICS_TABLE       ." t  USING(topic_id)
		LEFT JOIN ". TOPICS_WATCH_TABLE ." tw USING(topic_id)
	");

	// Delete topic moved stubs
	$db->query("
		DELETE t
		FROM ". $tmp_delete_topics ." del, ". TOPICS_TABLE ." t
		WHERE t.topic_moved_id = del.topic_id
	");

	// Delete torrents
	$db->query("
		DELETE tor, tr
		FROM      ". $tmp_delete_topics ." del
		LEFT JOIN ". BT_TORRENTS_TABLE  ." tor USING(topic_id)
		LEFT JOIN ". BT_TRACKER_TABLE   ." tr  USING(topic_id)
	");
/*
	// Delete dlstat
	$db->query("
		DELETE dl
		FROM      ". $tmp_delete_topics ." del
		LEFT JOIN ". BT_DLSTATUS_TABLE  ." dl USING(topic_id)
	");
*/
	// Log action
	if ($prune)
	{
		// TODO
	}
	else
	{
		foreach ($log_topics as $row)
		{
			if ($row['topic_status'] == TOPIC_MOVED)
			{
				$row['topic_title'] = '<i>'. $lang['TOPIC_MOVED'] .'</i> '. $row['topic_title'];
			}

			$log_action->mod('mod_topic_delete', array(
				'forum_id'    => $row['forum_id'],
				'topic_id'    => $row['topic_id'],
				'topic_title' => $row['topic_title'],
			));
		}
	}

	// Sync
	sync('forum', array_keys($sync_forums));

	$db->query("DROP TEMPORARY TABLE $tmp_delete_topics");

	return $deleted_topics_count;
}

function topic_move ($topic_id, $to_forum_id, $from_forum_id = null, $leave_shadow = false, $insert_bot_msg = false)
{
	global $db, $log_action;

	$to_forum_id = (int) $to_forum_id;

	// Verify input params
	if (!$topic_csv = get_id_csv($topic_id))
	{
		return false;
	}
	if (!forum_exists($to_forum_id))
	{
		return false;
	}
	if ($from_forum_id && (!forum_exists($from_forum_id) || $to_forum_id == $from_forum_id))
	{
		return false;
	}

	// Get topics info
	$where_sql = ($forum_csv = get_id_csv($from_forum_id)) ? "AND forum_id IN($forum_csv)" : '';

	$sql = "
		SELECT *
		FROM ". TOPICS_TABLE ."
		WHERE topic_id IN($topic_csv)
			AND topic_status != ". TOPIC_MOVED ."
				$where_sql
	";

	$topics = array();
	$sync_forums = array($to_forum_id => true);

	foreach ($db->fetch_rowset($sql) as $row)
	{
		if ($row['forum_id'] != $to_forum_id)
		{
			$topics[$row['topic_id']] = $row;
			$sync_forums[$row['forum_id']] = true;
		}
	}

	if (!$topics OR !$topic_csv = get_id_csv(array_keys($topics)))
	{
		return false;
	}

	// Insert topic in the old forum that indicates that the topic has moved
	if ($leave_shadow)
	{
		$shadows = array();

		foreach ($topics as $topic_id => $row)
		{
			$shadows[] = array(
				'forum_id'             => $row['forum_id'],
				'topic_title'          => $row['topic_title'],
				'topic_poster'         => $row['topic_poster'],
				'topic_time'           => TIMENOW,
				'topic_status'         => TOPIC_MOVED,
				'topic_type'           => POST_NORMAL,
				'topic_vote'           => $row['topic_vote'],
				'topic_views'          => $row['topic_views'],
				'topic_replies'        => $row['topic_replies'],
				'topic_first_post_id'  => $row['topic_first_post_id'],
				'topic_last_post_id'   => $row['topic_last_post_id'],
				'topic_moved_id'       => $topic_id,
				'topic_last_post_time' => $row['topic_last_post_time'],
			);
		}
		if ($sql_args = $db->build_array('MULTI_INSERT', $shadows))
		{
			$db->query("INSERT INTO ". TOPICS_TABLE . $sql_args);
		}
	}

	// Update topics
	$db->query("
		UPDATE ". TOPICS_TABLE ." SET
			forum_id = $to_forum_id
		WHERE topic_id IN($topic_csv)
	");

	// Update posts
	$db->query("
		UPDATE ". POSTS_TABLE ." SET
			forum_id = $to_forum_id
		WHERE topic_id IN($topic_csv)
	");

	// Update torrents
	$db->query("
		UPDATE ". BT_TORRENTS_TABLE ." SET
			forum_id = $to_forum_id
		WHERE topic_id IN($topic_csv)
	");

	// Bot
	if ($insert_bot_msg)
	{
		foreach ($topics as $topic_id => $row)
		{
			insert_post('after_move', $topic_id, $to_forum_id, $row['forum_id']);
		}
		sync('topic', array_keys($topics));
	}

	// Sync
	sync('forum', array_keys($sync_forums));

	// Log action
	foreach ($topics as $topic_id => $row)
	{
		$log_action->mod('mod_topic_move', array(
			'forum_id'     => $row['forum_id'],
			'forum_id_new' => $to_forum_id,
			'topic_id'     => $topic_id,
			'topic_title'  => $row['topic_title'],
		));
	}

	return true;
}

function post_delete ($mode_or_post_id, $user_id = null)
{
	global $db, $bb_cfg, $log_action;

	$del_user_posts = ($mode_or_post_id === 'user');  // Delete all user posts

	// Get required params
	if ($del_user_posts)
	{
		if (!$user_csv = get_id_csv($user_id)) return false;
	}
	else
	{
		if (!$post_csv = get_id_csv($mode_or_post_id)) return false;
	}

	// Collect data for logs, sync..
	$log_topics = $sync_forums = $sync_topics = $sync_users = array();

	if ($del_user_posts)
	{
		$sql = "SELECT DISTINCT topic_id FROM ". POSTS_TABLE ." WHERE poster_id IN($user_csv)";

		foreach ($db->fetch_rowset($sql) as $row)
		{
			$sync_topics[] = $row['topic_id'];
		}

		if ($topic_csv = get_id_csv($sync_topics))
		{
			$sql = "SELECT DISTINCT forum_id FROM ". TOPICS_TABLE ." WHERE topic_id IN($topic_csv)";

			foreach ($db->fetch_rowset($sql) as $row)
			{
				$sync_forums[$row['forum_id']] = true;
			}
		}

		$sync_users = explode(',', $user_csv);
	}
	else
	{
		$sql = "
			SELECT p.topic_id, p.forum_id, t.topic_title
			FROM ". POSTS_TABLE ." p, ". TOPICS_TABLE ." t
			WHERE p.post_id IN($post_csv)
				AND t.topic_id = p.topic_id
			GROUP BY t.topic_id
		";

		foreach ($db->fetch_rowset($sql) as $row)
		{
			$log_topics[] = $row;
			$sync_topics[] = $row['topic_id'];
			$sync_forums[$row['forum_id']] = true;
		}

		$sql = "SELECT DISTINCT poster_id FROM ". POSTS_TABLE ." WHERE post_id IN($post_csv)";

		foreach ($db->fetch_rowset($sql) as $row)
		{
			$sync_users[] = $row['poster_id'];
		}
	}

	// Get all post_id for deleting
	$tmp_delete_posts = 'tmp_delete_posts';

	$db->query("
		CREATE TEMPORARY TABLE $tmp_delete_posts (
			post_id MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (post_id)
		) ENGINE = MyISAM
	");

	$where_sql = ($del_user_posts) ? "poster_id IN($user_csv)" : "post_id IN($post_csv)";

	$db->query("
		INSERT INTO $tmp_delete_posts
			SELECT post_id
			FROM ". POSTS_TABLE ."
			WHERE $where_sql
	");

	// Deleted posts count
	$row = $db->fetch_row("SELECT COUNT(*) AS posts_count FROM $tmp_delete_posts");

	if (!$deleted_posts_count = $row['posts_count'])
	{
		$db->query("DROP TEMPORARY TABLE $tmp_delete_posts");
		return 0;
	}

	if ($bb_cfg['auto_delete_posted_pics'])
	{
		$result = $db->sql_query("
			SELECT ph.post_id, ph.post_html
			FROM $tmp_delete_posts tmp
			LEFT JOIN ". POSTS_HTML_TABLE ." ph USING(post_id)		
		");

		while ( $post = $db->sql_fetchrow($result) )
		{
			preg_match_all('#<var.*?title="(.*?)"#', $post['post_html'], $matches, PREG_SET_ORDER);

			foreach($matches as $match)
			{
				$have = $db->fetch_row("
					SELECT post_id
					FROM ". POSTS_HTML_TABLE ."
					WHERE post_html LIKE '%". $db->escape($match[1]). "%'
						AND post_id != {$post['post_id']}
				");

				if(empty($have))
				{
					@unlink(BB_ROOT . $bb_cfg['pic_dir']. end(explode('/', $match[1])));
				}
			}
		}
	}

	// Delete attachments (from disk)
	$attach_dir = get_attachments_dir();

	$result = $db->query("
		SELECT
			d.physical_filename
		FROM
			". $tmp_delete_posts      ." del,
			". ATTACHMENTS_TABLE      ." a,
			". ATTACHMENTS_DESC_TABLE ." d
		WHERE
			    a.post_id = del.post_id
			AND d.attach_id = a.attach_id
	");

	while ($row = $db->fetch_next($result))
	{
		if ($filename = basename($row['physical_filename']))
		{
			@unlink("$attach_dir/". $filename);
			@unlink("$attach_dir/". THUMB_DIR .'/t_'. $filename);
		}
	}
	unset($row, $result);

	// Delete posts, posts_text, attachments (from DB)
	$db->query("
		DELETE p, pt, ps, tor, a, d
		FROM      ". $tmp_delete_posts      ." del
		LEFT JOIN ". POSTS_TABLE            ." p   ON(p.post_id   = del.post_id)
		LEFT JOIN ". POSTS_TEXT_TABLE       ." pt  ON(pt.post_id  = del.post_id)
		LEFT JOIN ". POSTS_SEARCH_TABLE     ." ps  ON(ps.post_id  = del.post_id)
		LEFT JOIN ". BT_TORRENTS_TABLE      ." tor ON(tor.post_id = del.post_id)
		LEFT JOIN ". ATTACHMENTS_TABLE      ." a   ON(a.post_id   = del.post_id)
		LEFT JOIN ". ATTACHMENTS_DESC_TABLE ." d   ON(d.attach_id = a.attach_id)
	");

	// Log action
	if ($del_user_posts)
	{
		$log_action->admin('mod_post_delete', array(
			'log_msg' => 'user: '. get_usernames_for_log($user_id) ."<br />posts: $deleted_posts_count",
		));
	}
	else
	{
		foreach ($log_topics as $row)
		{
			$log_action->mod('mod_post_delete', array(
				'forum_id'    => $row['forum_id'],
				'topic_id'    => $row['topic_id'],
				'topic_title' => $row['topic_title'],
			));
		}
	}

	// Sync
	sync('topic', $sync_topics);
	sync('forum', array_keys($sync_forums));
	sync('user_posts', $sync_users);

	$db->query("DROP TEMPORARY TABLE $tmp_delete_posts");

	return $deleted_posts_count;
}

function poll_delete ($topic_id)
{
	global $db;

	if (!$topic_csv = get_id_csv($topic_id))
	{
		return false;
	}

	$db->query("
		DELETE vd, vr, vu
		FROM      ". VOTE_DESC_TABLE    ." vd
		LEFT JOIN ". VOTE_RESULTS_TABLE ." vr USING(vote_id)
		LEFT JOIN ". VOTE_USERS_TABLE   ." vu USING(vote_id)
		WHERE vd.topic_id IN($topic_csv)
	");

	$db->query("
		UPDATE ". TOPICS_TABLE ." SET topic_vote = 0 WHERE topic_id IN($topic_csv)
	");
}

function user_delete ($user_id, $delete_posts = false)
{
	global $db, $bb_cfg, $log_action;

	$default_group_moderator_id = 2;

	if (!$user_csv = get_id_csv($user_id))
	{
		return false;
	}

	// LOG
	$log_action->admin('adm_user_delete', array(
		'log_msg' => get_usernames_for_log($user_id),
	));

	// Avatar
	$result = $db->query("
		SELECT user_avatar
		FROM ". USERS_TABLE ."
		WHERE user_avatar_type = ". USER_AVATAR_UPLOAD ."
			AND user_avatar != ''
			AND user_id IN($user_csv)
	");

	while ($row = $db->fetch_next($result))
	{
		if ($filename = basename($row['user_avatar']))
		{
			@unlink(BB_ROOT . $bb_cfg['avatar_path'] .'/'. $filename);
		}
	}
	unset($row, $result);

	// Group
	$db->query("
		UPDATE ". GROUPS_TABLE ." SET
			group_moderator = $default_group_moderator_id
		WHERE group_single_user = 0
			AND group_moderator IN($user_csv)
	");

	if ($delete_posts)
	{
		post_delete('user', $user_id);
	}
	else
	{
		$db->query("
			UPDATE ". POSTS_TABLE ." p, ". USERS_TABLE ." u SET
				p.post_username = u.username,
				p.poster_id = ". DELETED ."
			WHERE u.user_id IN($user_csv)
				AND p.poster_id = u.user_id
		");
	}

	$db->query("
		UPDATE ". TOPICS_TABLE ." SET
			topic_poster = ". DELETED ."
		WHERE topic_poster IN($user_csv)
	");

	$db->query("
		UPDATE ". VOTE_USERS_TABLE ." SET
			vote_user_id = ". DELETED ."
		WHERE vote_user_id IN($user_csv)
	");

	$db->query("
		UPDATE ". BT_TORRENTS_TABLE ." SET
			poster_id = ". DELETED ."
		WHERE poster_id IN($user_csv)
	");

	$db->query("
		DELETE ug, g, a, qt1, qt2
		FROM ".      USER_GROUP_TABLE       ." ug
		LEFT JOIN ". GROUPS_TABLE           ." g   ON(g.group_id = ug.group_id AND g.group_single_user = 1)
		LEFT JOIN ". AUTH_ACCESS_TABLE      ." a   ON(a.group_id = g.group_id)
		LEFT JOIN ". QUOTA_TABLE            ." qt1 ON(qt1.user_id = ug.user_id)
		LEFT JOIN ". QUOTA_TABLE            ." qt2 ON(qt2.group_id = g.group_id)
		WHERE ug.user_id IN($user_csv)
	");

	$db->query("
		DELETE u, ban, s, tw, asn
		FROM ".      USERS_TABLE            ." u
		LEFT JOIN ". BANLIST_TABLE          ." ban ON(ban.ban_userid = u.user_id)
		LEFT JOIN ". SESSIONS_TABLE         ." s   ON(s.session_user_id = u.user_id)
		LEFT JOIN ". TOPICS_WATCH_TABLE     ." tw  ON(tw.user_id = u.user_id)
		LEFT JOIN ". AUTH_ACCESS_SNAP_TABLE ." asn ON(asn.user_id = u.user_id)
		WHERE u.user_id IN($user_csv)
	");

	$db->query("
		DELETE btu, tr, ust
		FROM ".      BT_USERS_TABLE         ." btu
		LEFT JOIN ". BT_TRACKER_TABLE       ." tr  ON(tr.user_id = btu.user_id)
		LEFT JOIN ". BT_USER_SETTINGS_TABLE ." ust ON(ust.user_id = btu.user_id)
		WHERE btu.user_id IN($user_csv)
	");

	// PM
	$db->query("
		DELETE pm, pmt
		FROM ". PRIVMSGS_TABLE ." pm
		LEFT JOIN ". PRIVMSGS_TEXT_TABLE ." pmt ON(pmt.privmsgs_text_id = pm.privmsgs_id)
		WHERE pm.privmsgs_from_userid IN($user_csv)
			AND pm.privmsgs_type IN(". PRIVMSGS_SENT_MAIL .','. PRIVMSGS_SAVED_OUT_MAIL .")
	");

	$db->query("
		DELETE pm, pmt
		FROM ". PRIVMSGS_TABLE ." pm
		LEFT JOIN ". PRIVMSGS_TEXT_TABLE ." pmt ON(pmt.privmsgs_text_id = pm.privmsgs_id)
		WHERE pm.privmsgs_to_userid IN($user_csv)
			AND pm.privmsgs_type IN(". PRIVMSGS_READ_MAIL .','. PRIVMSGS_SAVED_IN_MAIL .")
	");

	$db->query("
		UPDATE ". PRIVMSGS_TABLE ." SET
			privmsgs_from_userid = ". DELETED ."
		WHERE privmsgs_from_userid IN($user_csv)
	");

	$db->query("
		UPDATE ". PRIVMSGS_TABLE ." SET
			privmsgs_to_userid = ". DELETED ."
		WHERE privmsgs_to_userid IN($user_csv)
	");
}

function get_usernames_for_log ($user_id)
{
	global $db;

	$users_log_msg = array();

	if ($user_csv = get_id_csv($user_id))
	{
		$sql = "SELECT user_id, username FROM ". USERS_TABLE ." WHERE user_id IN($user_csv)";

		foreach ($db->fetch_rowset($sql) as $row)
		{
			$users_log_msg[] = "<b>$row[username]</b> [$row[user_id]]";
		}
	}

	return join(', ', $users_log_msg);
}