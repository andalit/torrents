<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if (method_exists($tr_cache, 'gc'))
{
	$changes = $tr_cache->gc();
	$cron_runtime_log .= date('Y-m-d H:i:s') ." -- tr -- $changes rows deleted\n";
}
if (method_exists($bb_cache, 'gc'))
{
	$changes = $bb_cache->gc();
	$cron_runtime_log .= date('Y-m-d H:i:s') ." -- bb -- $changes rows deleted\n";
}
if (method_exists($session_cache, 'gc'))
{
	$changes = $session_cache->gc(TIMENOW + $bb_cfg['session_cache_gc_ttl']);
	$cron_runtime_log .= date('Y-m-d H:i:s') ." -- ss -- $changes rows deleted\n";
}
