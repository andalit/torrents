<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$db->expect_slow_query(600);

//
// Make tracker snapshot
//
define('NEW_BT_TRACKER_SNAP_TABLE', 'new_tracker_snap');
define('OLD_BT_TRACKER_SNAP_TABLE', 'old_tracker_snap');

$db->query("DROP TABLE IF EXISTS ". NEW_BT_TRACKER_SNAP_TABLE .", ". OLD_BT_TRACKER_SNAP_TABLE);

$db->query("CREATE TABLE ". NEW_BT_TRACKER_SNAP_TABLE ." LIKE ". BT_TRACKER_SNAP_TABLE);

$per_cycle = 50000;
$row = $db->fetch_row("SELECT MIN(topic_id) AS start_id, MAX(topic_id) AS finish_id FROM ". BT_TRACKER_TABLE);
$start_id  = (int) $row['start_id'];
$finish_id = (int) $row['finish_id'];

while (true)
{
	set_time_limit(600);
	$end_id = $start_id + $per_cycle - 1;

	$val = array();
	$sql = "
		SELECT
			topic_id, SUM(seeder) AS seeders, (COUNT(*) - SUM(seeder)) AS leechers,
			SUM(speed_up) AS speed_up, SUM(speed_down) AS speed_down
		FROM ". BT_TRACKER_TABLE ."
		WHERE topic_id BETWEEN $start_id AND $end_id
		GROUP BY topic_id
	";
	foreach ($db->fetch_rowset($sql) as $row)
	{
		$val[] = join(',', $row);
	}
	if ($val)
	{
		$db->query("
			REPLACE INTO ". NEW_BT_TRACKER_SNAP_TABLE ."
			(topic_id, seeders, leechers, speed_up, speed_down)
			VALUES(". join('),(', $val) .")
		");
	}
	if ($end_id > $finish_id)
	{
		break;
	}
	if (!($start_id % ($per_cycle*10)))
	{
		sleep(1);
	}
	$start_id += $per_cycle;
}

$db->query("
	RENAME TABLE
	". BT_TRACKER_SNAP_TABLE     ." TO ". OLD_BT_TRACKER_SNAP_TABLE .",
	". NEW_BT_TRACKER_SNAP_TABLE ." TO ". BT_TRACKER_SNAP_TABLE ."
");

$db->query("DROP TABLE IF EXISTS ". NEW_BT_TRACKER_SNAP_TABLE .", ". OLD_BT_TRACKER_SNAP_TABLE);

//
// Make dl-list snapshot
//
define('NEW_BT_DLSTATUS_SNAP_TABLE', 'new_dlstatus_snap');
define('OLD_BT_DLSTATUS_SNAP_TABLE', 'old_dlstatus_snap');

$db->query("DROP TABLE IF EXISTS ". NEW_BT_DLSTATUS_SNAP_TABLE .", ". OLD_BT_DLSTATUS_SNAP_TABLE);

$db->query("CREATE TABLE ". NEW_BT_DLSTATUS_SNAP_TABLE ." LIKE ". BT_DLSTATUS_SNAP_TABLE);

if ($bb_cfg['bt_show_dl_list'] && $bb_cfg['bt_dl_list_only_count'])
{
	$db->query("
		INSERT INTO ". NEW_BT_DLSTATUS_SNAP_TABLE ."
			(topic_id, dl_status, users_count)
		SELECT
			topic_id, user_status, COUNT(*)
		FROM ". BT_DLSTATUS_TABLE ."
		WHERE user_status != ". DL_STATUS_RELEASER ."
		GROUP BY topic_id, user_status
	");
}

$db->query("
	RENAME TABLE
	". BT_DLSTATUS_SNAP_TABLE     ." TO ". OLD_BT_DLSTATUS_SNAP_TABLE .",
	". NEW_BT_DLSTATUS_SNAP_TABLE ." TO ". BT_DLSTATUS_SNAP_TABLE ."
");

$db->query("DROP TABLE IF EXISTS ". NEW_BT_DLSTATUS_SNAP_TABLE .", ". OLD_BT_DLSTATUS_SNAP_TABLE);

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
