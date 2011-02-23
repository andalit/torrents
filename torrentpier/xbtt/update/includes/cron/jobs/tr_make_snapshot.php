<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

global $bb_cfg;

$db->expect_slow_query(600);

//
// Make tracker snapshot
//
define('NEW_BT_TRACKER_SNAP_TABLE', 'new_tracker_snap');
define('OLD_BT_TRACKER_SNAP_TABLE', 'old_tracker_snap');

$db->query("DROP TABLE IF EXISTS ". NEW_BT_TRACKER_SNAP_TABLE .", ". OLD_BT_TRACKER_SNAP_TABLE);

$db->query("CREATE TABLE ". NEW_BT_TRACKER_SNAP_TABLE ." LIKE ". BT_TRACKER_SNAP_TABLE);

$db->query("
	INSERT INTO ". NEW_BT_TRACKER_SNAP_TABLE ."
		(topic_id, seeders, leechers, speed_up, speed_down)
	SELECT
		topic_id, SUM(seeder) AS seeders, (COUNT(*) - SUM(seeder)) AS leechers,
		SUM(speed_up) AS speed_up, SUM(speed_down) AS speed_down
	FROM ". BT_TRACKER_TABLE ."
	GROUP BY topic_id
");

$db->query("
	RENAME TABLE
	". BT_TRACKER_SNAP_TABLE     ." TO ". OLD_BT_TRACKER_SNAP_TABLE .",
	". NEW_BT_TRACKER_SNAP_TABLE ." TO ". BT_TRACKER_SNAP_TABLE ."
");

$db->query("DROP TABLE IF EXISTS ". NEW_BT_TRACKER_SNAP_TABLE .", ". OLD_BT_TRACKER_SNAP_TABLE);


//
// TORHELP
//
if ($bb_cfg['torhelp_enabled'])
{
	$tor_min_seeders         = 0;   // "<="
	$tor_min_leechers        = 2;   // ">="
	$tor_min_completed       = 10;  // ">="
	$tor_seed_last_seen_days = 3;   // "<="
	$tor_downloaded_days_ago = 60;  // ">="
	$user_last_seen_online   = 15;  // minutes
	$users_limit             = 3000;
	$dl_status_ary           = array(DL_STATUS_COMPLETE);

	define('NEW_BT_TORHELP_TABLE', 'new_torhelp');
	define('OLD_BT_TORHELP_TABLE', 'old_torhelp');

	$db->query("DROP TABLE IF EXISTS ". NEW_BT_TORHELP_TABLE .", ". OLD_BT_TORHELP_TABLE);

	$db->query("CREATE TABLE ". NEW_BT_TORHELP_TABLE ." LIKE ". BT_TORHELP_TABLE);

	// Select users
	$sql = "
		SELECT DISTINCT session_user_id AS uid
		FROM ". SESSIONS_TABLE ."
		WHERE session_time > (UNIX_TIMESTAMP() - $user_last_seen_online*60)
		  AND session_user_id != ". ANONYMOUS ."
		ORDER BY session_time DESC
		LIMIT $users_limit
	";
	$online_users_ary = array();

	foreach ($db->fetch_rowset($sql) as $row)
	{
		$online_users_ary[] = $row['uid'];
	}

	if ($online_users_csv = join(',', $online_users_ary))
	{
		$db->query("
			INSERT INTO ". NEW_BT_TORHELP_TABLE ." (user_id, topic_id_csv)
			SELECT
			  dl.user_id, GROUP_CONCAT(dl.topic_id)
			FROM       ". BT_TRACKER_SNAP_TABLE  ." trsn
			INNER JOIN ". BT_TORRENTS_TABLE      ." tor ON (tor.topic_id = trsn.topic_id)
			INNER JOIN ". BT_DLSTATUS_MAIN_TABLE ." dl  ON (dl.topic_id = tor.topic_id)
			WHERE
			      trsn.seeders          <=  $tor_min_seeders
			  AND trsn.leechers         >=  $tor_min_leechers
			  AND tor.forum_id          !=  ". (int) $bb_cfg['trash_forum_id'] ."
			  AND tor.complete_count    >=  $tor_min_completed
			  AND tor.seeder_last_seen  <=  (UNIX_TIMESTAMP() - $tor_seed_last_seen_days*86400)
			  AND dl.user_id            IN($online_users_csv)
			  AND dl.user_status        IN(". get_id_csv($dl_status_ary) .")
			  AND dl.last_modified_dlstatus > DATE_SUB(NOW(), INTERVAL $tor_downloaded_days_ago DAY)
			GROUP BY dl.user_id
			LIMIT 10000
		");
	}

	$db->query("
		RENAME TABLE
		". BT_TORHELP_TABLE     ." TO ". OLD_BT_TORHELP_TABLE .",
		". NEW_BT_TORHELP_TABLE ." TO ". BT_TORHELP_TABLE ."
	");

	$db->query("DROP TABLE IF EXISTS ". NEW_BT_TORHELP_TABLE .", ". OLD_BT_TORHELP_TABLE);
}

$db->expect_slow_query(10);
