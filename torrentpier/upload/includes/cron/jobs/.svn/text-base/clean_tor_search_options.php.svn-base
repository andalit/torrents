<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if ($bb_cfg['tr_settings_days_keep'])
{
	$db->query("
		DELETE FROM ". BT_USER_SETTINGS_TABLE ."
		WHERE last_modified < ". (TIMENOW - 86400*$bb_cfg['tr_settings_days_keep']) ."
	");
}

