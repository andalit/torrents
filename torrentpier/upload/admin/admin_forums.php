<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['Forums']['Manage'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

require(INC_DIR .'functions_group.php');

$s = '';

$default_forum_auth = array(
	'auth_view'        => AUTH_ALL,
	'auth_read'        => AUTH_ALL,
	'auth_post'        => AUTH_REG,
	'auth_reply'       => AUTH_REG,
	'auth_edit'        => AUTH_REG,
	'auth_delete'      => AUTH_REG,
	'auth_sticky'      => AUTH_MOD,
	'auth_announce'    => AUTH_MOD,
	'auth_vote'        => AUTH_REG,
	'auth_pollcreate'  => AUTH_REG,
	'auth_attachments' => AUTH_REG,
	'auth_download'    => AUTH_REG,
);

$mode = (@$_REQUEST['mode']) ? (string) $_REQUEST['mode'] : '';

$cat_forums = get_cat_forums();

if ($orphan_sf_sql = get_orphan_sf())
{
	fix_orphan_sf($orphan_sf_sql, TRUE);
}
$forum_parent = $cat_id = 0;
$forumname = '';

if (isset($_REQUEST['addforum']) || isset($_REQUEST['addcategory']))
{
	$mode = (isset($_REQUEST['addforum'])) ? "addforum" : "addcat";

	if ($mode == 'addforum' && isset($_POST['addforum']) && isset($_POST['forumname']) && is_array($_POST['addforum']))
	{
		$req_cat_id = array_keys($_POST['addforum']);
		$cat_id = $req_cat_id[0];
		$forumname = stripslashes($_POST['forumname'][$cat_id]);
	}
}

$show_main_page = false;

if ($mode)
{
	switch ($mode)
	{
		case 'addforum':
		case 'editforum':
			//
			// Show form to create/modify a forum
			//
			if ($mode == 'editforum')
			{
				// $newmode determines if we are going to INSERT or UPDATE after posting?

				$l_title = $lang['EDIT_FORUM'];
				$newmode = 'modforum';
				$buttonvalue = $lang['UPDATE'];

				$forum_id = intval($_GET[POST_FORUM_URL]);

				$row = get_info('forum', $forum_id);

				$cat_id = $row['cat_id'];
				$forumname = $row['forum_name'];
				$forumdesc = $row['forum_desc'];
				$forumstatus = $row['forum_status'];
				$forum_display_sort = $row['forum_display_sort'];
				$forum_display_order = $row['forum_display_order'];
				$forum_parent = $row['forum_parent'];
				$show_on_index = $row['show_on_index'];
				$prune_enabled = ($row['prune_days']) ? HTML_CHECKED : '';
				$prune_days = $row['prune_days'];
			}
			else
			{
				$l_title = $lang['CREATE_FORUM'];
				$newmode = 'createforum';
				$buttonvalue = $lang['CREATE_FORUM'];

				$forumdesc = '';
				$forumstatus = FORUM_UNLOCKED;
				$forum_display_sort = 0;
				$forum_display_order = 0;
				$forum_id = '';
				$show_on_index = 1;
				$prune_enabled = '';
				$prune_days = 0;
			}

			if (isset($_REQUEST['forum_parent']))
			{
				$forum_parent = intval($_REQUEST['forum_parent']);

				if ($parent = get_forum_data($forum_parent))
				{
					$cat_id = $parent['cat_id'];
				}
			}
			else if (isset($_REQUEST['c']))
			{
				$cat_id = (int) $_REQUEST['c'];
			}

			$catlist = get_list('category', $cat_id, TRUE);
			$forumlocked = $forumunlocked = '';

			$forumstatus == ( FORUM_LOCKED ) ? $forumlocked = "selected=\"selected\"" : $forumunlocked = "selected=\"selected\"";

			// These two options ($lang['STATUS_UNLOCKED'] and $lang['STATUS_LOCKED']) seem to be missing from
			// the language files.
			$lang['STATUS_UNLOCKED'] = isset($lang['STATUS_UNLOCKED']) ? $lang['STATUS_UNLOCKED'] : 'Unlocked';
			$lang['STATUS_LOCKED'] = isset($lang['STATUS_LOCKED']) ? $lang['STATUS_LOCKED'] : 'Locked';

			$statuslist = "<option value=\"" . FORUM_UNLOCKED . "\" $forumunlocked>" . $lang['STATUS_UNLOCKED'] . "</option>\n";
			$statuslist .= "<option value=\"" . FORUM_LOCKED . "\" $forumlocked>" . $lang['STATUS_LOCKED'] . "</option>\n";

			$forum_display_sort_list = get_forum_display_sort_option($forum_display_sort, 'list', 'sort');
			$forum_display_order_list = get_forum_display_sort_option($forum_display_order, 'list', 'order');

			$s_hidden_fields = '<input type="hidden" name="mode" value="' . $newmode .'" /><input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '" />';

			$s_parent = '<option value="-1">&nbsp;'. $lang['SF_NO_PARENT'] ."</option>\n";
			$sel_forum = ($forum_parent && !isset($_REQUEST['forum_parent'])) ? $forum_id : $forum_parent;
			$s_parent .= sf_get_list('forum', $forum_id, $sel_forum);

			$template->assign_vars(array(
				'TPL_EDIT_FORUM' => true,

				'S_FORUM_DISPLAY_SORT_LIST'		=> $forum_display_sort_list,
				'S_FORUM_DISPLAY_ORDER_LIST'	=> $forum_display_order_list,
				'S_FORUM_ACTION' => append_sid("admin_forums.php"),
				'S_HIDDEN_FIELDS' => $s_hidden_fields,
				'S_SUBMIT_VALUE' => $buttonvalue,
				'S_CAT_LIST' => $catlist,
				'S_STATUS_LIST' => $statuslist,
				'S_PRUNE_ENABLED' => $prune_enabled,

				'SHOW_ON_INDEX' => $show_on_index,
				'L_SHOW_ON_INDEX' => $lang['SF_SHOW_ON_INDEX'],
				'L_PARENT_FORUM' => $lang['SF_PARENT_FORUM'],
				'S_PARENT_FORUM' => $s_parent,
				'CAT_LIST_CLASS' => ($forum_parent) ? 'hidden' : '',
				'SHOW_ON_INDEX_CLASS' => (!$forum_parent) ? 'hidden' : '',

				'L_FORUM_TITLE' => $l_title,
				'L_FORUM_EXPLAIN' => $lang['FORUM_EDIT_DELETE_EXPLAIN'],
				'L_FORUM_DESCRIPTION' => $lang['FORUM_DESC'],
				'L_AUTO_PRUNE' => $lang['FORUM_PRUNING'],

				'PRUNE_DAYS' => $prune_days,
				'FORUM_NAME' => htmlCHR($forumname),
				'DESCRIPTION' => htmlCHR($forumdesc),
			));
			break;

		case 'createforum':
			//
			// Create a forum in the DB
			//
			$cat_id = intval($_POST[POST_CAT_URL]);
			$forum_name = str_replace("\'", "''", trim($_POST['forumname']));
			$forum_desc = str_replace("\'", "''", trim($_POST['forumdesc']));
			$forum_status = intval($_POST['forumstatus']);

			$prune_enable = isset($_POST['prune_enable']);
			$prune_days = ($prune_enable) ? intval($_POST['prune_days']) : 0;

			$forum_parent = ($_POST['forum_parent'] != -1) ? intval($_POST['forum_parent']) : 0;
			$show_on_index = ($forum_parent) ? intval($_POST['show_on_index']) : 1;

			$forum_display_sort = intval($_POST['forum_display_sort']);
			$forum_display_order = intval($_POST['forum_display_order']);

			if (!$forum_name)
			{
				message_die(GENERAL_ERROR, "Can't create a forum without a name");
			}

			if ($forum_parent)
			{
				if (!$parent = get_forum_data($forum_parent))
				{
					message_die(GENERAL_ERROR, "Parent forum with <b>id=$forum_parent</b> not found");
				}

				$cat_id = $parent['cat_id'];
				$forum_parent = ($parent['forum_parent']) ? $parent['forum_parent'] : $parent['forum_id'];
				$forum_order = $parent['forum_order'] + 5;
			}
			else
			{
				$max_order = get_max_forum_order($cat_id);
				$forum_order = $max_order + 5;
			}

			if ($prune_enable && !$prune_days)
			{
				message_die(GENERAL_MESSAGE, $lang['SET_PRUNE_DATA']);
			}

			// Default permissions of public forum
			$field_sql = $value_sql = '';

			foreach ($default_forum_auth as $field => $value)
			{
				$field_sql .= ", $field";
				$value_sql .= ", $value";
			}

			$columns = ' forum_name,   cat_id,   forum_desc,   forum_order,  forum_status,  prune_days,  forum_parent,  show_on_index,  forum_display_sort,  forum_display_order'. $field_sql;
			$values = "'$forum_name', $cat_id, '$forum_desc', $forum_order, $forum_status, $prune_days, $forum_parent, $show_on_index, $forum_display_sort, $forum_display_order". $value_sql;

			$db->query("INSERT INTO ". FORUMS_TABLE ." ($columns) VALUES ($values)");

			renumber_order('forum', $cat_id);
			$datastore->update('cat_forums');

			$message = $lang['FORUMS_UPDATED'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_FORUMADMIN'], "<a href=\"" . "admin_forums.php?c=$cat_id" . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");
			message_die(GENERAL_MESSAGE, $message);

			break;

		case 'modforum':
			//
			// Modify a forum in the DB
			//
			$cat_id = intval($_POST[POST_CAT_URL]);
			$forum_id = intval($_POST[POST_FORUM_URL]);
			$forum_name = str_replace("\'", "''", trim($_POST['forumname']));
			$forum_desc = str_replace("\'", "''", trim($_POST['forumdesc']));
			$forum_status = intval($_POST['forumstatus']);

			$prune_enable = isset($_POST['prune_enable']);
			$prune_days = ($prune_enable) ? intval($_POST['prune_days']) : 0;

			$forum_parent = ($_POST['forum_parent'] != -1) ? intval($_POST['forum_parent']) : 0;
			$show_on_index = ($forum_parent) ? intval($_POST['show_on_index']) : 1;

			$forum_display_order = intval($_POST['forum_display_order']);
			$forum_display_sort = intval($_POST['forum_display_sort']);

			$forum_data = get_forum_data($forum_id);
			$old_cat_id = $forum_data['cat_id'];
			$forum_order = $forum_data['forum_order'];

			if (!$forum_name)
			{
				message_die(GENERAL_ERROR, "Can't modify a forum without a name");
			}

			if ($forum_parent)
			{
				if (!$parent = get_forum_data($forum_parent))
				{
					message_die(GENERAL_ERROR, "Parent forum with <b>id=$forum_parent</b> not found");
				}

				$cat_id = $parent['cat_id'];
				$forum_parent = ($parent['forum_parent']) ? $parent['forum_parent'] : $parent['forum_id'];
				$forum_order = $parent['forum_order'] + 5;

				if ($forum_id == $forum_parent)
				{
					message_die(GENERAL_ERROR, "Ambiguous forum ID's. Please select other parent forum", '', __LINE__, __FILE__);
				}
			}
			else if ($cat_id != $old_cat_id)
			{
				$max_order = get_max_forum_order($cat_id);
				$forum_order = $max_order + 5;
			}
			else if ($forum_data['forum_parent'])
			{
				$old_parent = $forum_data['forum_parent'];
				$forum_order = $cat_forums[$old_cat_id]['f'][$old_parent]['forum_order'] - 5;
			}

			if ($prune_enable && !$prune_days)
			{
				message_die(GENERAL_MESSAGE, $lang['SET_PRUNE_DATA']);
			}

			$db->query("
				UPDATE ". FORUMS_TABLE ." SET
					forum_name    = '$forum_name',
					cat_id        = $cat_id,
					forum_desc    = '$forum_desc',
					forum_order   = $forum_order,
					forum_status  = $forum_status,
					prune_days    = $prune_days,
					forum_parent  = $forum_parent,
					show_on_index = $show_on_index,
					forum_display_order = $forum_display_order,
					forum_display_sort  = $forum_display_sort
				WHERE forum_id = $forum_id
			");

			if ($cat_id != $old_cat_id)
			{
				change_sf_cat($forum_id, $cat_id, $forum_order);
				renumber_order('forum', $cat_id);
			}

			renumber_order('forum', $old_cat_id);

			$cat_forums = get_cat_forums();
			$fix = fix_orphan_sf();
			$datastore->update('cat_forums');

			$message = $lang['FORUMS_UPDATED'] . "<br /><br />";
			$message .= ($fix) ? "$fix<br /><br />" : '';
			$message .= sprintf($lang['CLICK_RETURN_FORUMADMIN'], "<a href=\"" . "admin_forums.php?c=$cat_id" . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");
			message_die(GENERAL_MESSAGE, $message);

			break;

		case 'addcat':
			//
			// Create a category in the DB
			//
			verify_sid();

			if (!$new_cat_title = trim($_POST['categoryname']))
			{
				bb_die('Category name is empty');
			}

			check_name_dup('cat', $new_cat_title);

			$order = $db->fetch_row("SELECT MAX(cat_order) AS max_order FROM ". CATEGORIES_TABLE);

			$args = $db->build_array('INSERT', array(
				'cat_title' => (string) $new_cat_title,
				'cat_order' => (int) $order['max_order'] + 10,
			));

			$db->query("INSERT INTO ". CATEGORIES_TABLE . $args);

			$datastore->update('cat_forums');

			$message = $lang['FORUMS_UPDATED'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_FORUMADMIN'], "<a href=\"" . append_sid("admin_forums.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");
			message_die(GENERAL_MESSAGE, $message);

			break;

		case 'editcat':
			//
			// Show form to edit a category
			//
			$cat_id   = (int) $_GET['c'];
			$cat_info = get_info('category', $cat_id);

			$hidden_fields = array(
				'mode' => 'modcat',
				'c'    => $cat_id,
			);

			$template->assign_vars(array(
				'TPL_EDIT_CATEGORY' => true,

				'CAT_TITLE'       => htmlCHR($cat_info['cat_title']),
				'L_EDIT_CAT'      => $lang['EDIT_CATEGORY'],
				'L_EDIT_CAT_EXPL' => $lang['EDIT_CATEGORY_EXPLAIN'],
				'S_HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
				'S_SUBMIT_VALUE'  => $lang['UPDATE'],
				'S_FORUM_ACTION'  => "admin_forums.php",
			));

			break;

		case 'modcat':
			//
			// Modify a category in the DB
			//
			verify_sid();

			if (!$new_cat_title = trim($_POST['cat_title']))
			{
				bb_die('Category name is empty');
			}

			$cat_id = (int) $_POST['c'];

			$row = get_info('category', $cat_id);
			$cur_cat_title = $row['cat_title'];

			if ($cur_cat_title && $cur_cat_title !== $new_cat_title)
			{
				check_name_dup('cat', $new_cat_title);

				$new_cat_title_sql = $db->escape($new_cat_title);

				$db->query("
					UPDATE ". CATEGORIES_TABLE ." SET
						cat_title = '$new_cat_title_sql'
					WHERE cat_id = $cat_id
				");
			}

			$datastore->update('cat_forums');

			$message = $lang['FORUMS_UPDATED'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_FORUMADMIN'], "<a href=\"" . append_sid("admin_forums.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");
			message_die(GENERAL_MESSAGE, $message);

			break;

		case 'deleteforum':
			//
			// Show form to delete a forum
			//
			$forum_id = (int) $_GET['f'];

			$move_to_options = '<option value="-1">'. $lang['DELETE_ALL_POSTS'] .'</option>';
			$move_to_options .= sf_get_list('forum', $forum_id, 0);

			$foruminfo = get_info('forum', $forum_id);

			$hidden_fields = array(
				'mode'    => 'movedelforum',
				'from_id' => $forum_id,
			);

			$template->assign_vars(array(
				'TPL_DELETE_FORUM' => true,

				'WHAT_TO_DELETE'   => htmlCHR($foruminfo['forum_name']),
				'DELETE_TITLE'     => $lang['FORUM_DELETE'],
				'L_DELETE_EXPL'    => $lang['FORUM_DELETE_EXPLAIN'],
				'L_MOVE_CONTENTS'  => $lang['MOVE_CONTENTS'],
				'CAT_FORUM_NAME'   => $lang['FORUM_NAME'],

				'S_HIDDEN_FIELDS'  => build_hidden_fields($hidden_fields),
				'S_FORUM_ACTION'   => "admin_forums.php",
				'MOVE_TO_OPTIONS'  => $move_to_options,
				'S_SUBMIT_VALUE'   => $lang['MOVE_AND_DELETE'],
			));

			break;

		case 'movedelforum':
			//
			// Move or delete a forum in the DB
			//
			verify_sid();

			$from_id = (int) $_POST['from_id'];
			$to_id = (int) $_POST['to_id'];

			if ($to_id == -1)
			{
				// Delete everything from forum
				topic_delete('prune', $from_id, 0, true);
			}
			else
			{
				// Move all posts
				$sql = "SELECT * FROM ". FORUMS_TABLE ." WHERE forum_id IN($from_id, $to_id)";
				$result = $db->query($sql);

				if ($db->sql_numrows($result) != 2)
				{
					message_die(GENERAL_ERROR, "Ambiguous forum ID's", "", __LINE__, __FILE__);
				}

				// Update topics
				$db->query("
					UPDATE ". TOPICS_TABLE ." SET
						forum_id = $to_id
					WHERE forum_id = $from_id
				");

				// Update posts
				$db->query("
					UPDATE ". POSTS_TABLE ." SET
						forum_id = $to_id
					WHERE forum_id = $from_id
				");

				// Update torrents
				$db->query("
					UPDATE ". BT_TORRENTS_TABLE ." SET
						forum_id = $to_id
					WHERE forum_id = $from_id
				");

				sync('forum', $to_id);
			}

			$db->query("DELETE FROM ". FORUMS_TABLE           ." WHERE forum_id = $from_id");
			$db->query("DELETE FROM ". AUTH_ACCESS_TABLE      ." WHERE forum_id = $from_id");
			$db->query("DELETE FROM ". AUTH_ACCESS_SNAP_TABLE ." WHERE forum_id = $from_id");

			$cat_forums = get_cat_forums();
			fix_orphan_sf();
			update_user_level('all');
			$datastore->update('cat_forums');

			$message = $lang['FORUMS_UPDATED'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_FORUMADMIN'], "<a href=\"" . append_sid("admin_forums.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");
			message_die(GENERAL_MESSAGE, $message);

			break;

		case 'deletecat':
			//
			// Show form to delete a category
			//
			$cat_id  = (int) $_GET['c'];
			$catinfo = get_info('category', $cat_id);
			$categories_count = $catinfo['number'];

			if ($categories_count == 1)
			{
				$row = $db->fetch_row("SELECT COUNT(*) AS forums_count FROM ". FORUMS_TABLE);

				if ($row['forums_count'] > 0)
				{
					message_die(GENERAL_ERROR, $lang['MUST_DELETE_FORUMS']);
				}
				else
				{
					$template->assign_var('NOWHERE_TO_MOVE', $lang['NOWHERE_TO_MOVE']);
				}
			}

			$hidden_fields = array(
				'mode'    => 'movedelcat',
				'from_id' => $cat_id,
			);

			$template->assign_vars(array(
				'TPL_DELETE_FORUM' => true,

				'WHAT_TO_DELETE'   => htmlCHR($catinfo['cat_title']),
				'DELETE_TITLE'     => $lang['CATEGORY_DELETE'],
				'L_DELETE_EXPL'    => $lang['FORUM_DELETE_EXPLAIN'],
				'CAT_FORUM_NAME'   => $lang['CATEGORY'],

				'S_HIDDEN_FIELDS'  => build_hidden_fields($hidden_fields),
				'S_FORUM_ACTION'   => "admin_forums.php",
				'MOVE_TO_OPTIONS'  => get_list('category', $cat_id, 0),
				'S_SUBMIT_VALUE'   => $lang['MOVE_AND_DELETE'],
			));

			break;

		case 'movedelcat':
			//
			// Move or delete a category in the DB
			//
			verify_sid();

			$from_id = (int) $_POST['from_id'];
			$to_id   = (int) $_POST['to_id'];

			if ($from_id == $to_id || !cat_exists($from_id) || !cat_exists($to_id))
			{
				bb_die('Bad input');
			}

			$order_shear = get_max_forum_order($to_id) + 10;

			$db->query("
				UPDATE ". FORUMS_TABLE ." SET
					cat_id = $to_id,
					forum_order = forum_order + $order_shear
				WHERE cat_id = $from_id
			");

			$db->query("DELETE FROM ". CATEGORIES_TABLE ." WHERE cat_id = $from_id");

			renumber_order('forum', $to_id);
			$cat_forums = get_cat_forums();
			$fix = fix_orphan_sf();
			$datastore->update('cat_forums');

			$message = $lang['FORUMS_UPDATED'] . "<br /><br />";
			$message .= ($fix) ? "$fix<br /><br />" : '';
			$message .= sprintf($lang['CLICK_RETURN_FORUMADMIN'], "<a href=\"" . append_sid("admin_forums.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");
			message_die(GENERAL_MESSAGE, $message);

			break;

		case 'forum_order':
			//
			// Change order of forums
			//
			$move = intval($_GET['move']);
			$forum_id = intval($_GET[POST_FORUM_URL]);

			$forum_info = get_info('forum', $forum_id);
			renumber_order('forum', $forum_info['cat_id']);

			$cat_id = $forum_info['cat_id'];

			$move_down_forum_id = FALSE;
			$forums = $cat_forums[$cat_id]['f_ord'];
			$forum_order = $forum_info['forum_order'];
			$prev_forum = (isset($forums[$forum_order - 10])) ? $forums[$forum_order - 10] : FALSE;
			$next_forum = (isset($forums[$forum_order + 10])) ? $forums[$forum_order + 10] : FALSE;

			// move selected forum ($forum_id) UP
			if ($move < 0 && $prev_forum)
			{
				if ($forum_info['forum_parent'] && $prev_forum['forum_parent'] != $forum_info['forum_parent'])
				{
					break;
				}
				else if ($move_down_forum_id = get_prev_root_forum_id($forums, $forum_order))
				{
					$move_up_forum_id = $forum_id;
					$move_down_ord_val = (get_sf_count($forum_id) + 1) * 10;
					$move_up_ord_val = ((get_sf_count($move_down_forum_id) + 1) * 10) + $move_down_ord_val;
					$move_down_forum_order = $cat_forums[$cat_id]['f'][$move_down_forum_id]['forum_order'];
				}
			}
			// move selected forum ($forum_id) DOWN
			else if ($move > 0 && $next_forum)
			{
				if ($forum_info['forum_parent'] && $next_forum['forum_parent'] != $forum_info['forum_parent'])
				{
					break;
				}
				else if ($move_up_forum_id = get_next_root_forum_id($forums, $forum_order))
				{
					$move_down_forum_id = $forum_id;
					$move_down_forum_order = $forum_order;
					$move_down_ord_val = (get_sf_count($move_up_forum_id) + 1) * 10;
					$move_up_ord_val = ((get_sf_count($move_down_forum_id) + 1) * 10) + $move_down_ord_val;
				}
			}
			else
			{
				$show_main_page = true;
				break;
			}

			if ($forum_info['forum_parent'])
			{
				$sql = 'UPDATE ' . FORUMS_TABLE . " SET
						forum_order = forum_order + $move
					WHERE forum_id = $forum_id";

				if (!$db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, "Couldn't change forum order", '', __LINE__, __FILE__, $sql);
				}
			}
			else if ($move_down_forum_id)
			{
				$sql = 'UPDATE '. FORUMS_TABLE ." SET
						forum_order = forum_order + $move_down_ord_val
					WHERE cat_id = $cat_id
						AND forum_order >= $move_down_forum_order";

				if (!$db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, "Couldn't change forum order", '', __LINE__, __FILE__, $sql);
				}

				$sql = 'UPDATE '. FORUMS_TABLE ." SET
						forum_order = forum_order - $move_up_ord_val
					WHERE forum_id = $move_up_forum_id
						 OR forum_parent = $move_up_forum_id";

				if (!$db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, "Couldn't change forum order", '', __LINE__, __FILE__, $sql);
				}
			}

			renumber_order('forum', $forum_info['cat_id']);
			$datastore->update('cat_forums');

			$show_main_page = true;
			break;

		case 'cat_order':
			$move = (int) $_GET['move'];
			$cat_id = (int) $_GET['c'];

			$db->query("
				UPDATE ". CATEGORIES_TABLE ." SET
					cat_order = cat_order + $move
				WHERE cat_id = $cat_id
			");

			renumber_order('category');
			$datastore->update('cat_forums');

			$show_main_page = true;
			break;

		case 'forum_sync':
			sync('forum', intval($_GET['f']));
			$datastore->update('cat_forums');

			$show_main_page = true;
			break;

		default:
			message_die(GENERAL_MESSAGE, $lang['NO_MODE']);

			break;
	}
}

if (!$mode || $show_main_page)
{
	$template->assign_vars(array(
		'TPL_FORUMS_LIST' => true,

		'S_FORUM_ACTION' => append_sid("admin_forums.php"),
		'L_FORUM_TITLE' => $lang['FORUM_ADMIN_MAIN'],
		'L_FORUM_EXPLAIN' => $lang['FORUM_ADMIN_EXPLAIN'],
		'L_EDIT' => 'edit', //$lang['EDIT'],
		'L_RESYNC' => 'sync', //$lang['RESYNC']
	));

	$sql = "SELECT cat_id, cat_title, cat_order
		FROM " . CATEGORIES_TABLE . "
		ORDER BY cat_order";
	if( !$q_categories = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Could not query categories list", "", __LINE__, __FILE__, $sql);
	}

	if( $total_categories = $db->sql_numrows($q_categories) )
	{
		$category_rows = $db->sql_fetchrowset($q_categories);

		$where_cat_sql = $req_cat_id = '';

		if ($c =& $_REQUEST['c'])
		{
			if ($c !== 'all')
			{
				$req_cat_id = (int) $c;
				$where_cat_sql = "WHERE cat_id = $req_cat_id";
			}
			else
			{
				$req_cat_id = 'all';
			}
		}
		else
		{
			$where_cat_sql = "WHERE cat_id = '-1'";
		}

		$sql = "SELECT *
			FROM ". FORUMS_TABLE ."
				$where_cat_sql
			ORDER BY cat_id, forum_order";
		if(!$q_forums = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not query forums information", "", __LINE__, __FILE__, $sql);
		}

		if( $total_forums = $db->sql_numrows($q_forums) )
		{
			$forum_rows = $db->sql_fetchrowset($q_forums);
		}

		//
		// Okay, let's build the index
		//
		$gen_cat = array();

		$bgr_class_1    = 'prow1';
		$bgr_class_2    = 'prow2';
		$bgr_class_over = 'prow3';

		$template->assign_vars(array(
			'U_ALL_FORUMS' => "admin_forums.php?c=all",
		));

		for($i = 0; $i < $total_categories; $i++)
		{
			$cat_id = $category_rows[$i]['cat_id'];

			$template->assign_block_vars("catrow", array(
				'S_ADD_FORUM_SUBMIT' => "addforum[$cat_id]",
				'S_ADD_FORUM_NAME'   => "forumname[$cat_id]",

				'CAT_ID'   => $cat_id,
				'CAT_DESC' => htmlCHR($category_rows[$i]['cat_title']),

				'U_CAT_EDIT'      => "admin_forums.php?mode=editcat&amp;c=$cat_id",
				'U_CAT_DELETE'    => "admin_forums.php?mode=deletecat&amp;c=$cat_id",
				'U_CAT_MOVE_UP'   => "admin_forums.php?mode=cat_order&amp;move=-15&amp;c=$cat_id",
				'U_CAT_MOVE_DOWN' => "admin_forums.php?mode=cat_order&amp;move=15&amp;c=$cat_id",
				'U_VIEWCAT'       => "admin_forums.php?c=$cat_id",
				'U_CREATE_FORUM'  => "admin_forums.php?mode=addforum&amp;c=$cat_id",
			));

			for($j = 0; $j < $total_forums; $j++)
			{
				$forum_id = $forum_rows[$j]['forum_id'];

				$bgr_class = (!($j % 2)) ? $bgr_class_2 : $bgr_class_1;
				$row_bgr   = " class=\"$bgr_class\" onmouseover=\"this.className='$bgr_class_over';\" onmouseout=\"this.className='$bgr_class';\"";

				if ($forum_rows[$j]['cat_id'] == $cat_id)
				{

					$template->assign_block_vars("catrow.forumrow",	array(
						'FORUM_NAME' => htmlCHR($forum_rows[$j]['forum_name']),
						'FORUM_DESC' => htmlCHR($forum_rows[$j]['forum_desc']),
						'NUM_TOPICS' => $forum_rows[$j]['forum_topics'],
						'NUM_POSTS'  => $forum_rows[$j]['forum_posts'],
						'PRUNE_DAYS' => ($forum_rows[$j]['prune_days']) ? $forum_rows[$j]['prune_days'] : '-',

						'ORDER'    => $forum_rows[$j]['forum_order'],
						'FORUM_ID' => $forum_rows[$j]['forum_id'],
						'ROW_BGR'  => $row_bgr,

						'SHOW_ON_INDEX'     => (bool) $forum_rows[$j]['show_on_index'],
						'FORUM_PARENT'      => $forum_rows[$j]['forum_parent'],
						'SF_PAD'            => ($forum_rows[$j]['forum_parent']) ? ' style="padding-left: 20px;" ' : '',
						'FORUM_NAME_CLASS'  => ($forum_rows[$j]['forum_parent']) ? 'genmed' : 'gen',
						'ADD_SUB_HREF'      => "admin_forums.php?mode=addforum&amp;forum_parent={$forum_rows[$j]['forum_id']}",
						'U_VIEWFORUM'       => BB_ROOT ."viewforum.php?f=$forum_id",
						'U_FORUM_EDIT'      => "admin_forums.php?mode=editforum&amp;f=$forum_id",
						'U_FORUM_DELETE'    => "admin_forums.php?mode=deleteforum&amp;f=$forum_id",
						'U_FORUM_MOVE_UP'   => "admin_forums.php?mode=forum_order&amp;move=-15&amp;f=$forum_id&amp;c=$req_cat_id",
						'U_FORUM_MOVE_DOWN' => "admin_forums.php?mode=forum_order&amp;move=15&amp;f=$forum_id&amp;c=$req_cat_id",
						'U_FORUM_RESYNC'    => "admin_forums.php?mode=forum_sync&amp;f=$forum_id",
					));

				}// if ... forumid == catid
			} // for ... forums
		} // for ... categories
	}// if ... total_categories
}

print_page('admin_forums.tpl', 'admin');

//
// Functions
//
function get_info($mode, $id)
{
	global $db;

	switch($mode)
	{
		case 'category':
			$table = CATEGORIES_TABLE;
			$idfield = 'cat_id';
			$namefield = 'cat_title';
			break;

		case 'forum':
			$table = FORUMS_TABLE;
			$idfield = 'forum_id';
			$namefield = 'forum_name';
			break;

		default:
			message_die(GENERAL_ERROR, "Wrong mode for generating select list", "", __LINE__, __FILE__);
			break;
	}
	$sql = "SELECT count(*) as total
		FROM $table";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get Forum/Category information", "", __LINE__, __FILE__, $sql);
	}
	$count = $db->sql_fetchrow($result);
	$count = $count['total'];

	$sql = "SELECT *
		FROM $table
		WHERE $idfield = $id";

	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get Forum/Category information", "", __LINE__, __FILE__, $sql);
	}

	if( $db->sql_numrows($result) != 1 )
	{
		message_die(GENERAL_ERROR, "Forum/Category doesn't exist or multiple forums/categories with ID $id", "", __LINE__, __FILE__);
	}

	$return = $db->sql_fetchrow($result);
	$return['number'] = $count;
	return $return;
}

function get_list($mode, $id, $select)
{
	global $db;

	switch($mode)
	{
		case 'category':
			$table = CATEGORIES_TABLE;
			$idfield = 'cat_id';
			$namefield = 'cat_title';
			$order = 'cat_order';
			break;

		case 'forum':
			$table = FORUMS_TABLE;
			$idfield = 'forum_id';
			$namefield = 'forum_name';
			$order = 'cat_id, forum_order';
			break;

		default:
			message_die(GENERAL_ERROR, "Wrong mode for generating select list", "", __LINE__, __FILE__);
			break;
	}

	$sql = "SELECT *
		FROM $table";
	if( $select == 0 )
	{
		$sql .= " WHERE $idfield <> $id";
	}
		$sql .= " ORDER BY $order";

	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get list of Categories/Forums", "", __LINE__, __FILE__, $sql);
	}

	$catlist = '';

	while( $row = $db->sql_fetchrow($result) )
	{
		$s = "";
		if ($row[$idfield] == $id)
		{
			$s = " selected=\"selected\"";
		}
		$catlist .= "<option value=\"$row[$idfield]\"$s>&nbsp;" . htmlCHR(str_short($row[$namefield], 60)) . "</option>\n";
	}

	return($catlist);
}

function renumber_order($mode, $cat = 0)
{
	global $db;

	switch($mode)
	{
		case 'category':
			$table = CATEGORIES_TABLE;
			$idfield = 'cat_id';
			$orderfield = 'cat_order';
			$cat = 0;
			break;

		case 'forum':
			$table = FORUMS_TABLE;
			$idfield = 'forum_id';
			$orderfield = 'forum_order';
			$catfield = 'cat_id';
			break;

		default:
			message_die(GENERAL_ERROR, "Wrong mode for generating select list", "", __LINE__, __FILE__);
			break;
	}

	$sql = "SELECT * FROM $table";
	if( $cat != 0)
	{
		$sql .= " WHERE $catfield = $cat";
	}
	$sql .= " ORDER BY $orderfield ASC";


	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get list of Categories", "", __LINE__, __FILE__, $sql);
	}

	$i = 10;
	$inc = 10;

	while( $row = $db->sql_fetchrow($result) )
	{
		$sql = "UPDATE $table
			SET $orderfield = $i
			WHERE $idfield = " . $row[$idfield];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update order fields", "", __LINE__, __FILE__, $sql);
		}
		$i += 10;
	}

	if (!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't get list of Categories", "", __LINE__, __FILE__, $sql);
	}

}

function get_cat_forums ($cat_id = FALSE)
{
	global $db;

	$forums = array();
	$where_sql = '';

	if ($cat_id = intval($cat_id))
	{
		$where_sql = "AND f.cat_id = $cat_id";
	}

	$sql = 'SELECT c.cat_title, f.*
		FROM '. FORUMS_TABLE .' f, '. CATEGORIES_TABLE ." c
		WHERE f.cat_id = c.cat_id
			$where_sql
		ORDER BY c.cat_order, f.cat_id, f.forum_order";

	if (!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't get list of Categories", "", __LINE__, __FILE__, $sql);
	}

	if ($rowset = $db->sql_fetchrowset($result))
	{
		foreach ($rowset as $rid => $row)
		{
			$forums[$row['cat_id']]['cat_title'] = $row['cat_title'];
			$forums[$row['cat_id']]['f'][$row['forum_id']] = $row;
			$forums[$row['cat_id']]['f_ord'][$row['forum_order']] = $row;
		}
	}

	return $forums;
}

function get_sf_count ($forum_id)
{
	global $cat_forums;

	$sf_count = 0;

	foreach ($cat_forums as $cid => $c)
	{
		foreach ($c['f'] as $fid => $f)
		{
			if ($f['forum_parent'] == $forum_id)
			{
				$sf_count++;
			}
		}
	}

	return $sf_count;
}

function get_prev_root_forum_id ($forums, $curr_forum_order)
{
	$i = $curr_forum_order - 10;

	while ($i > 0)
	{
		if (isset($forums[$i]) && !$forums[$i]['forum_parent'])
		{
			return $forums[$i]['forum_id'];
		}
		$i = $i - 10;
	}

  return FALSE;
}

function get_next_root_forum_id ($forums, $curr_forum_order)
{
	$i = $curr_forum_order + 10;
	$limit = (count($forums) * 10) + 10;

	while ($i < $limit)
	{
		if (isset($forums[$i]) && !$forums[$i]['forum_parent'])
		{
			return $forums[$i]['forum_id'];
		}
		$i = $i + 10;
	}

  return FALSE;
}

function get_orphan_sf ()
{
	global $cat_forums;

	$last_root = 0;
	$bad_sf_ary = array();

	foreach ($cat_forums as $cid => $c)
	{
		foreach ($c['f'] as $fid => $f)
		{
			if ($f['forum_parent'])
			{
				if ($f['forum_parent'] != $last_root)
				{
					$bad_sf_ary[] = $f['forum_id'];
				}
			}
			else
			{
				$last_root = $f['forum_id'];
			}
		}
	}

	return implode(',', $bad_sf_ary);
}

function fix_orphan_sf ($orphan_sf_sql = '', $show_mess = FALSE)
{
	global $db, $lang;

	$done_mess = '';

	if (!$orphan_sf_sql)
	{
		$orphan_sf_sql = get_orphan_sf();
	}

	if ($orphan_sf_sql)
	{
		$sql = "UPDATE ". FORUMS_TABLE ." SET
				forum_parent = 0,
				show_on_index = 1
			WHERE forum_id IN($orphan_sf_sql)";

		if (!$db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't change subforums data", '', __LINE__, __FILE__, $sql);
		}

		if ($affectedrows = $db->sql_affectedrows())
		{
			$done_mess = "Subforums data corrected. <b>$affectedrows</b> orphan subforum(s) moved to root level.";
		}

		if ($show_mess)
		{
			$message  = $done_mess .'<br /><br />';
			$message .= sprintf($lang['CLICK_RETURN_FORUMADMIN'], "<a href=\"admin_forums.php\">", '</a>') .'<br /><br />';
			$message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"index.php?pane=right\">", '</a>');
			message_die(GENERAL_MESSAGE, $message);
		}
	}

	return $done_mess;
}

function sf_get_list ($mode, $exclude = 0, $select = 0)
{
	global $cat_forums, $forum_parent;

	$opt = '';

	if ($mode == 'forum')
	{
		foreach ($cat_forums as $cid => $c)
		{
			$opt .= '<optgroup label="&nbsp;'. htmlCHR($c['cat_title']) .'">';

			foreach ($c['f'] as $fid => $f)
			{
				$selected = ($fid == $select) ? HTML_SELECTED : '';
				$disabled = ($fid == $exclude && !$forum_parent) ? HTML_DISABLED : '';
				$style = ($disabled) ? ' style="color: gray" ' : (($fid == $exclude) ? ' style="color: darkred" ' : '');
				$opt .= '<option value="'. $fid .'" '. $selected . $disabled . $style .'>'. (($f['forum_parent']) ? HTML_SF_SPACER : '') . htmlCHR(str_short($f['forum_name'], 60)) ."&nbsp;</option>\n";
			}

			$opt .= '</optgroup>';
		}
	}

	return $opt;
}

function get_forum_data ($forum_id)
{
	global $cat_forums;

	foreach ($cat_forums as $cid => $c)
	{
		foreach ($c['f'] as $fid => $f)
		{
			if ($fid == $forum_id)
			{
				return $f;
			}
		}
	}

	return FALSE;
}

function get_max_forum_order ($cat_id)
{
	global $db;

	$row = $db->fetch_row("
		SELECT MAX(forum_order) AS max_forum_order
		FROM ". FORUMS_TABLE ."
		WHERE cat_id = $cat_id
	");

	return intval($row['max_forum_order']);
}

function check_name_dup ($mode, $name, $die_on_error = true)
{
	global $db;

	$name_sql = $db->escape($name);

	if ($mode == 'cat')
	{
		$what_checked = 'Category';
		$sql = "SELECT cat_id FROM ". CATEGORIES_TABLE ." WHERE cat_title = '$name_sql'";
	}
	else
	{
		$what_checked = 'Forum';
		$sql = "SELECT forum_id FROM ". FORUMS_TABLE ." WHERE forum_name = '$name_sql'";
	}

	$name_is_dup = $db->fetch_row($sql);

	if ($name_is_dup && $die_on_error)
	{
		bb_die("This $what_checked name taken, please choose something else");
	}

	return $name_is_dup;
}

/**
 *  Change subforums cat_id if parent's cat_id was changed
 */
function change_sf_cat ($parent_id, $new_cat_id, $order_shear)
{
	global $db;

	$db->query("
		UPDATE ". FORUMS_TABLE ." SET
			cat_id      = $new_cat_id,
			forum_order = forum_order + $order_shear
		WHERE forum_parent = $parent_id
	");
}
