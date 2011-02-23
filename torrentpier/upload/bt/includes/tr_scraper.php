<?php

if (!defined('IN_TRACKER')) die(basename(__FILE__));

db_init();

$info_hash_sql = rtrim($db->escape($info_hash), ' ');

$row = $db->fetch_row("
		SELECT tor.complete_count, snap.seeders, snap.leechers
		FROM ". BT_TORRENTS_TABLE ." tor
		LEFT JOIN ". BT_TRACKER_SNAP_TABLE ." snap ON (snap.topic_id = tor.topic_id)
		WHERE tor.info_hash = '$info_hash_sql'
		LIMIT 1
");

$output['files'][$info_hash] = array(
		'complete'    => (int) $row['seeders'],
		'downloaded'  => (int) $row['complete_count'],
		'incomplete'  => (int) $row['leechers'],
);

echo bencode($output);

tracker_exit();