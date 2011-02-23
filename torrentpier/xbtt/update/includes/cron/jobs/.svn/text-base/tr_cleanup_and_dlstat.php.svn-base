<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));


$releaser = DL_STATUS_RELEASER;
global $tr_cfg;

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

// Update PER TORRENT DL-Status (for "completed" counter)
	$db->query("
		INSERT IGNORE INTO ". BT_TORSTAT_TABLE ."
			(topic_id, user_id)
		SELECT
			topic_id, user_id
		FROM ". BT_TRACKER_TABLE ."
		WHERE IF(releaser, $releaser, seeder) = ". DL_STATUS_COMPLETE ." AND (up_add != 0 OR down_add != 0)
	");
	// Reset up/down additions in tracker
	$db->query("UPDATE ". BT_TRACKER_TABLE ." SET up_add = 0, down_add = 0");

// Delete not registered torrents from tracker
/*
$db->query("
	DELETE tr
	FROM ". BT_TRACKER_TABLE ." tr
	LEFT JOIN ". BT_TORRENTS_TABLE ." tor USING(topic_id)
	WHERE tor.topic_id IS NULL
");
*/