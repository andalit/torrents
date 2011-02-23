<?php
/**
*
* @package attachment_mod
* @version $Id: functions_delete.php,v 1.1 2005/11/05 12:23:33 acydburn Exp $
* @copyright (c) 2002 Meik Sievertsen
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* All Attachment Functions processing the Deletion Process
*/

/**
* Delete Attachment(s) from post(s) (intern)
*/
function delete_attachment($post_id_array = 0, $attach_id_array = 0, $page = 0, $user_id = 0)
{
	global $db;

	// Generate Array, if it's not an array
	if ($post_id_array === 0 && $attach_id_array === 0 && $page === 0)
	{
		return;
	}

	if ($post_id_array === 0 && $attach_id_array !== 0)
	{
		$post_id_array = array();

		if (!is_array($attach_id_array))
		{
			if (strstr($attach_id_array, ', '))
			{
				$attach_id_array = explode(', ', $attach_id_array);
			}
			else if (strstr($attach_id_array, ','))
			{
				$attach_id_array = explode(',', $attach_id_array);
			}
			else
			{
				$attach_id = intval($attach_id_array);
				$attach_id_array = array();
				$attach_id_array[] = $attach_id;
			}
		}

		// Get the post_ids to fill the array
		$p_id = 'post_id';

		$sql = "SELECT $p_id
			FROM " . ATTACHMENTS_TABLE . '
				WHERE attach_id IN (' . implode(', ', $attach_id_array) . ")
			GROUP BY $p_id";

		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not select ids', '', __LINE__, __FILE__, $sql);
		}

		$num_post_list = $db->sql_numrows($result);

		if ($num_post_list == 0)
		{
			$db->sql_freeresult($result);
			return;
		}

		while ($row = $db->sql_fetchrow($result))
		{
			$post_id_array[] = intval($row[$p_id]);
		}
		$db->sql_freeresult($result);
	}

	if (!is_array($post_id_array))
	{
		if (trim($post_id_array) == '')
		{
			return;
		}

		if (strstr($post_id_array, ', '))
		{
			$post_id_array = explode(', ', $post_id_array);
		}
		else if (strstr($post_id_array, ','))
		{
			$post_id_array = explode(',', $post_id_array);
		}
		else
		{
			$post_id = intval($post_id_array);

			$post_id_array = array();
			$post_id_array[] = $post_id;
		}
	}

	if (!sizeof($post_id_array))
	{
		return;
	}

	// First of all, determine the post id and attach_id
	if ($attach_id_array === 0)
	{
		$attach_id_array = array();

		// Get the attach_ids to fill the array
		$whereclause = 'WHERE post_id IN (' . implode(', ', $post_id_array) . ')';

		$sql = 'SELECT attach_id
			FROM ' . ATTACHMENTS_TABLE . " $whereclause
			GROUP BY attach_id";

		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not select Attachment Ids', '', __LINE__, __FILE__, $sql);
		}

		$num_attach_list = $db->sql_numrows($result);

		if ($num_attach_list == 0)
		{
			$db->sql_freeresult($result);
			return;
		}

		while ($row = $db->sql_fetchrow($result))
		{
			$attach_id_array[] = (int) $row['attach_id'];
		}
		$db->sql_freeresult($result);
	}

	if (!is_array($attach_id_array))
	{
		if (strstr($attach_id_array, ', '))
		{
			$attach_id_array = explode(', ', $attach_id_array);
		}
		else if (strstr($attach_id_array, ','))
		{
			$attach_id_array = explode(',', $attach_id_array);
		}
		else
		{
			$attach_id = intval($attach_id_array);

			$attach_id_array = array();
			$attach_id_array[] = $attach_id;
		}
	}

	if (!sizeof($attach_id_array))
	{
		return;
	}

	$sql_id = 'post_id';

	if (sizeof($post_id_array) && sizeof($attach_id_array))
	{
		$sql = 'DELETE FROM ' . ATTACHMENTS_TABLE . '
			WHERE attach_id IN (' . implode(', ', $attach_id_array) . ")
				AND $sql_id IN (" . implode(', ', $post_id_array) . ')';

		if ( !($db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, $lang['ERROR_DELETED_ATTACHMENTS'], '', __LINE__, __FILE__, $sql);
		}

		//bt
		if ($sql_id == 'post_id')
		{
			$sql = "SELECT topic_id
				FROM ". BT_TORRENTS_TABLE ."
				WHERE attach_id IN(". implode(',', $attach_id_array) .")";

			if (!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, $lang['ERROR_DELETED_ATTACHMENTS'], '', __LINE__, __FILE__, $sql);
			}

			$torrents_sql = array();

			while ($row = $db->sql_fetchrow($result))
			{
				$torrents_sql[] = $row['topic_id'];
			}

			if ($torrents_sql = implode(',', $torrents_sql))
			{
				// Remove peers from tracker
				$sql = "DELETE FROM ". BT_TRACKER_TABLE ."
					WHERE topic_id IN($torrents_sql)";

				if (!$db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Could not delete peers', '', __LINE__, __FILE__, $sql);
				}
			}
			// Delete torrents
			$sql = "DELETE FROM ". BT_TORRENTS_TABLE ."
				WHERE attach_id IN(". implode(',', $attach_id_array) .")";

			if (!$db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, $lang['ERROR_DELETED_ATTACHMENTS'], '', __LINE__, __FILE__, $sql);
			}
		}
		//bt end

		for ($i = 0; $i < sizeof($attach_id_array); $i++)
		{
			$sql = 'SELECT attach_id
				FROM ' . ATTACHMENTS_TABLE . '
						WHERE attach_id = ' . (int) $attach_id_array[$i];

			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not select Attachment Ids', '', __LINE__, __FILE__, $sql);
			}

				$num_rows = $db->sql_numrows($result);
				$db->sql_freeresult($result);

				if ($num_rows == 0)
				{
					$sql = 'SELECT attach_id, physical_filename, thumbnail
						FROM ' . ATTACHMENTS_DESC_TABLE . '
							WHERE attach_id = ' . (int) $attach_id_array[$i];

					if ( !($result = $db->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Couldn\'t query attach description table', '', __LINE__, __FILE__, $sql);
					}
					$num_rows = $db->sql_numrows($result);

					if ($num_rows != 0)
					{
						$num_attach = $num_rows;
						$attachments = $db->sql_fetchrowset($result);
						$db->sql_freeresult($result);

						// delete attachments
						for ($j = 0; $j < $num_attach; $j++)
						{
							unlink_attach($attachments[$j]['physical_filename']);

							if (intval($attachments[$j]['thumbnail']) == 1)
							{
								unlink_attach($attachments[$j]['physical_filename'], MODE_THUMBNAIL);
							}

							$sql = 'DELETE FROM ' . ATTACHMENTS_DESC_TABLE . '
								WHERE attach_id = ' . (int) $attachments[$j]['attach_id'];

							if ( !($db->sql_query($sql)) )
							{
								message_die(GENERAL_ERROR, $lang['ERROR_DELETED_ATTACHMENTS'], '', __LINE__, __FILE__, $sql);
							}
						}
					}
					else
					{
						$db->sql_freeresult($result);
					}
				}
			}
		}

		// Now Sync the Topic/PM
		if (sizeof($post_id_array))
		{
			$sql = 'SELECT topic_id
			FROM ' . POSTS_TABLE . '
			WHERE post_id IN (' . implode(', ', $post_id_array) . ')
			GROUP BY topic_id';

		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Couldn\'t select Topic ID', '', __LINE__, __FILE__, $sql);
		}

		while ($row = $db->sql_fetchrow($result))
		{
			attachment_sync_topic($row['topic_id']);
		}
		$db->sql_freeresult($result);
	}
}