<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$tables = array(
	BT_DLSTATUS_SNAP_TABLE,
	BT_LAST_TORSTAT_TABLE,
	BT_LAST_USERSTAT_TABLE,
	BT_TRACKER_SNAP_TABLE,
	BT_TRACKER_TABLE,
	BUF_LAST_SEEDER_TABLE,
	BUF_TOPIC_VIEW_TABLE,
	SESSIONS_TABLE,
);

if (!empty($_GET['to']))
{
	$type = ($_GET['to'] === 'HEAP') ? 'HEAP' : 'MyISAM';

	foreach ($tables as $table)
	{
		$db->query("ALTER TABLE `$table` TYPE = $type");
	}
}