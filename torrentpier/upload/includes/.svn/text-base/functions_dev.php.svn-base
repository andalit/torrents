<?php

function get_sql_log ()
{
	global $db, $datastore, $bb_cache, $session_cache;

	$log = '';
	$log .= !empty($db) ? get_sql_log_html($db, '$db [MySQL]') : '';
	$log .= !empty($datastore->db) ? get_sql_log_html($datastore->db, '$datastore [SQLite]') : '';
	$log .= !empty($bb_cache->db) ? get_sql_log_html($bb_cache->db, '$bb_cache [SQLite]') : '';
	$log .= !empty($session_cache->db) ? get_sql_log_html($session_cache->db, '$session_cache [SQLite]') : '';

	return $log;
}

function get_sql_log_html ($db, $log_name)
{
	if (empty($db->dbg)) return '';

	$log = '';

	foreach ($db->dbg as $i => $dbg)
	{
		$id   = "sql_{$i}_". make_rand_str(6);
		$sql  = sql_log_query_short($dbg['sql']);
		$time = sprintf('%.4f', $dbg['time']);
		$perc = sprintf('[%2d]', $dbg['time']*100/$db->sql_timetotal);
		$info = !empty($dbg['info']) ? $dbg['info'] .' ['. $dbg['src'] .']' : $dbg['src'];
		$file = addslashes($dbg['file']);
		$line = $dbg['line'];
		$edit = (DEBUG === true) ? "OpenInEditor('$file', $line);" : '';

		$log .= ''
		. '<div class="sqlLogRow" title="'. $info .'" ondblclick="'. $edit .'">'
		.  '<span style="letter-spacing: -1px;">'. $time .' </span>'
		.  '<span title="Copy to clipboard" onclick="if (ie_copyTextToClipboard($p(\''. $id .'\'))) alert(\'SQL copied to clipboard\');" style="color: gray; letter-spacing: -1px;">'. $perc .'</span>'
		.  ' '
		.  '<span style="letter-spacing: 0px;" id="'. $id .'">'. $sql .'</span>'
		.  '<span style="color: gray"> # '. $info .' </span>'
		. '</div>'
		. "\n";
	}
	return '
		<div class="sqlLogTitle">'. $log_name .'</div>
		'. $log .'
	';
}
