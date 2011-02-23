<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$releaser = DL_STATUS_RELEASER;

define('NEW_BT_LAST_TORSTAT_TABLE',  'new_bt_last_torstat');
define('OLD_BT_LAST_TORSTAT_TABLE',  'old_bt_last_torstat');
define('NEW_BT_LAST_USERSTAT_TABLE', 'new_bt_last_userstat');
define('OLD_BT_LAST_USERSTAT_TABLE', 'old_bt_last_userstat');

$db->query("DROP TABLE IF EXISTS ". NEW_BT_LAST_TORSTAT_TABLE .", ". NEW_BT_LAST_USERSTAT_TABLE);
$db->query("DROP TABLE IF EXISTS ". OLD_BT_LAST_TORSTAT_TABLE .", ". OLD_BT_LAST_USERSTAT_TABLE);

$db->query("CREATE TABLE ". NEW_BT_LAST_TORSTAT_TABLE  ." LIKE ". BT_LAST_TORSTAT_TABLE);
$db->query("CREATE TABLE ". NEW_BT_LAST_USERSTAT_TABLE ." LIKE ". BT_LAST_USERSTAT_TABLE);

$db->expect_slow_query(600);

// Update dlstat (part 1)
if ($tr_cfg['update_dlstat'])
{
	// ############################ Tables LOCKED ################################
	$db->lock(array(
		BT_TRACKER_TABLE,
		NEW_BT_LAST_TORSTAT_TABLE,
	));

	// Get PER TORRENT user's dlstat from tracker
	$db->query("
		INSERT INTO ". NEW_BT_LAST_TORSTAT_TABLE ."
			(topic_id, user_id, dl_status, up_add, down_add, release_add, speed_up, speed_down)
		SELECT
			topic_id, user_id, IF(releaser, $releaser, seeder), SUM(up_add), SUM(down_add), IF(releaser, SUM(up_add), 0), SUM(speed_up), SUM(speed_down)
		FROM ". BT_TRACKER_TABLE ."
		WHERE (up_add != 0 OR down_add != 0)
		GROUP BY topic_id, user_id
	");

	// Reset up/down additions in tracker
	$db->query("UPDATE ". BT_TRACKER_TABLE ." SET up_add = 0, down_add = 0");

	$db->unlock();
	// ############################ Tables UNLOCKED ##############################
}

// Update last seeder info in BUF
$db->query("
	REPLACE INTO ". BUF_LAST_SEEDER_TABLE ."
		(topic_id, seeder_last_seen)
	SELECT
		topic_id, ". TIMENOW ."
	FROM ". BT_TRACKER_TABLE ."
	WHERE seeder = 1
	GROUP BY topic_id
");

// Clean peers table
if ($tr_cfg['autoclean'])
{
	$announce_interval = max(intval($bb_cfg['announce_interval']), 60);
	$expire_factor     = max(floatval($tr_cfg['expire_factor']), 1);
	$peer_expire_time  = TIMENOW - floor($announce_interval * $expire_factor);

	$db->query("DELETE FROM ". BT_TRACKER_TABLE ." WHERE update_time < $peer_expire_time");
}

// Delete not registered torrents from tracker
/*
$db->query("
	DELETE tr
	FROM ". BT_TRACKER_TABLE ." tr
	LEFT JOIN ". BT_TORRENTS_TABLE ." tor USING(topic_id)
	WHERE tor.topic_id IS NULL
");
*/

// Update dlstat (part 2)
if ($tr_cfg['update_dlstat'])
{
	// Set "only 1 seeder" bonus
	$db->query("
		UPDATE
		  ". NEW_BT_LAST_TORSTAT_TABLE  ." tb,
		  ". BT_TRACKER_SNAP_TABLE      ." sn
		SET
		  tb.bonus_add = tb.up_add
		WHERE
		      tb.topic_id = sn.topic_id
		  AND sn.seeders = 1
		  AND tb.up_add != 0
		  AND tb.dl_status = ". DL_STATUS_COMPLETE ."
	");

	// Get SUMMARIZED user's dlstat
	$db->query("
		INSERT INTO ". NEW_BT_LAST_USERSTAT_TABLE ."
			(user_id, up_add, down_add, release_add, bonus_add, speed_up, speed_down)
		SELECT
			user_id, SUM(up_add), SUM(down_add), SUM(release_add), SUM(bonus_add), SUM(speed_up), SUM(speed_down)
		FROM ". NEW_BT_LAST_TORSTAT_TABLE ."
		GROUP BY user_id
	");

	// Update TOTAL user's dlstat
	$db->query("
		UPDATE
			". BT_USERS_TABLE             ." u,
			". NEW_BT_LAST_USERSTAT_TABLE ." ub
		SET
			u.u_up_total   = u.u_up_total   + ub.up_add,
			u.u_down_total = u.u_down_total + ub.down_add,
			u.u_up_release = u.u_up_release + ub.release_add,
			u.u_up_bonus   = u.u_up_bonus   + ub.bonus_add
		WHERE u.user_id = ub.user_id
	");

	// Delete from MAIN what exists in BUF but not exsits in NEW
	$db->query("
		DELETE main
		FROM ". BT_DLSTATUS_MAIN_TABLE ." main
		INNER JOIN (
			". NEW_BT_LAST_TORSTAT_TABLE ." buf
			LEFT JOIN ". BT_DLSTATUS_NEW_TABLE ." new USING(user_id, topic_id)
		) USING(user_id, topic_id)
		WHERE new.user_id IS NULL
			AND new.topic_id IS NULL
	");

	// Update DL-Status
	$db->query("
		REPLACE INTO ". BT_DLSTATUS_NEW_TABLE ."
			(user_id, topic_id, user_status)
		SELECT
			user_id, topic_id, dl_status
		FROM ". NEW_BT_LAST_TORSTAT_TABLE ."
	");

	// Update PER TORRENT DL-Status (for "completed" counter)
	$db->query("
		INSERT IGNORE INTO ". BT_TORSTAT_TABLE ."
			(topic_id, user_id)
		SELECT
			topic_id, user_id
		FROM ". NEW_BT_LAST_TORSTAT_TABLE ."
		WHERE dl_status = ". DL_STATUS_COMPLETE ."
	");
}

$db->query("
	RENAME TABLE
	". BT_LAST_TORSTAT_TABLE     ." TO ". OLD_BT_LAST_TORSTAT_TABLE .",
	". NEW_BT_LAST_TORSTAT_TABLE ." TO ". BT_LAST_TORSTAT_TABLE ."
");
$db->query("DROP TABLE IF EXISTS ". NEW_BT_LAST_TORSTAT_TABLE .", ". OLD_BT_LAST_TORSTAT_TABLE);

$db->query("
	RENAME TABLE
	". BT_LAST_USERSTAT_TABLE     ." TO ". OLD_BT_LAST_USERSTAT_TABLE .",
	". NEW_BT_LAST_USERSTAT_TABLE ." TO ". BT_LAST_USERSTAT_TABLE ."
");
$db->query("DROP TABLE IF EXISTS ". NEW_BT_LAST_USERSTAT_TABLE .", ". OLD_BT_LAST_USERSTAT_TABLE);

$db->expect_slow_query(10);
