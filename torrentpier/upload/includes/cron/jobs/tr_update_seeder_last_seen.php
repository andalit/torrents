<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$db->query("
	UPDATE
		". BUF_LAST_SEEDER_TABLE ." b,
		". BT_TORRENTS_TABLE     ." tor
	SET
		tor.seeder_last_seen = b.seeder_last_seen
	WHERE
		tor.topic_id = b.topic_id
");

$db->query("TRUNCATE TABLE ".  BUF_LAST_SEEDER_TABLE);
