<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

// Get complete counts
$db->query("
	CREATE TEMPORARY TABLE tmp_complete_count
	SELECT
		topic_id, COUNT(*) AS compl_cnt
	FROM ". BT_TORSTAT_TABLE ."
	WHERE completed = 0
	GROUP BY topic_id
");

// Update USER "completed" counters
$db->query("UPDATE ". BT_TORSTAT_TABLE ." SET completed = 1");

// Update TORRENT "completed" counters
$db->query("
	UPDATE
		". BT_TORRENTS_TABLE ." tor,
		tmp_complete_count      tmp
	SET
		tor.complete_count = tor.complete_count + tmp.compl_cnt
	WHERE
		tor.topic_id = tmp.topic_id
");

// Drop tmp table
$db->query("DROP TEMPORARY TABLE tmp_complete_count");
