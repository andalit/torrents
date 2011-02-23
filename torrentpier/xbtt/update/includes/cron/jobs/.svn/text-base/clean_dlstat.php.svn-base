<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

global $bb_cfg;

// Delete staled dl-status records
$keeping_dlstat = array(
	DL_STATUS_WILL     => (int) $bb_cfg['dl_will_days_keep'],
	DL_STATUS_DOWN     => (int) $bb_cfg['dl_down_days_keep'],
	DL_STATUS_COMPLETE => (int) $bb_cfg['dl_complete_days_keep'],
	DL_STATUS_CANCEL   => (int) $bb_cfg['dl_cancel_days_keep'],
);

$delete_dlstat_sql = array();

foreach ($keeping_dlstat as $dl_status => $days_to_keep)
{
	if ($days_to_keep)
	{
		$delete_dlstat_sql[] = "
			user_status = $dl_status
			AND
			last_modified_dlstatus < DATE_SUB(NOW(), INTERVAL $days_to_keep DAY)
		";
	}
}

if ($delete_dlstat_sql = join(') OR (', $delete_dlstat_sql))
{
	$db->query("DELETE QUICK FROM ". BT_DLSTATUS_TABLE ." WHERE ($delete_dlstat_sql)");
}

// Delete orphans
$db->query("
	DELETE QUICK dl
	FROM ". BT_DLSTATUS_TABLE ." dl
	LEFT JOIN ". USERS_TABLE ." u USING(user_id)
	WHERE u.user_id IS NULL
");

$db->query("
	DELETE QUICK dl
	FROM ". BT_DLSTATUS_TABLE ." dl
	LEFT JOIN ". TOPICS_TABLE ." t USING(topic_id)
	WHERE t.topic_id IS NULL
");

// Tor-Stats cleanup
if ($torstat_days_keep = intval($bb_cfg['torstat_days_keep']))
{
	$db->query("DELETE QUICK FROM ". BT_TORSTAT_TABLE ." WHERE last_modified_torstat < DATE_SUB(NOW(), INTERVAL $torstat_days_keep DAY)");
}

$db->query("
	DELETE QUICK tst
	FROM ". BT_TORSTAT_TABLE ." tst
	LEFT JOIN ". BT_TORRENTS_TABLE ." tor USING(topic_id)
	WHERE tor.topic_id IS NULL
");

