<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

global $bb_cfg, $lang, $userdata, $gen_simple_header, $template, $db;
global $datastore, $bb_cache, $session_cache;

$logged_in = !empty($userdata['session_logged_in']);

if (!empty($template))
{
	$template->assign_vars(array(
		'SIMPLE_FOOTER'    => !empty($gen_simple_header),

		'TRANSLATION_INFO' => isset($lang['TRANSLATION_INFO']) ? $lang['TRANSLATION_INFO'] : '',
		'SHOW_ADMIN_LINK'  => (IS_ADMIN && !defined('IN_ADMIN')),
		'ADMIN_LINK_HREF'  => "admin/index.php",
		'L_GOTO_ADMINCP'   => $lang['ADMIN_PANEL'],

		'SHOW_BANNERS'     => (!DEBUG && (!(IS_AM) || $userdata['user_id'] == 2)),
	));

	$template->set_filenames(array('page_footer' => 'page_footer.tpl'));
	$template->pparse('page_footer');
}

if (IS_ADMIN)
{
	$show_dbg_info = true;
} else {
	$show_dbg_info = false;
}

flush();

if ($show_dbg_info)
{
	$gen_time = utime() - TIMESTART;
	$gen_time_txt = sprintf('%.3f', $gen_time);
	$gzip_text = (UA_GZIP_SUPPORTED) ? 'GZIP' : '<s>GZIP</s>';
	$gzip_text .= ($bb_cfg['gzip_compress']) ? ' ON' : ' OFF';
	$debug_text = (DEBUG) ? 'Debug ON' : 'Debug OFF';

	$stat = '[&nbsp; ';
	$stat .= "Execution time: $gen_time_txt sec ";

	if (!empty($db))
	{
		$sql_time = ($db->sql_timetotal) ? sprintf('%.3f sec (%d%%) in ', $db->sql_timetotal, round($db->sql_timetotal*100/$gen_time)) : '';
		$stat .= "&nbsp;|&nbsp; MySQL: {$sql_time}{$db->num_queries} queries";
	}

	$stat .= "&nbsp;|&nbsp; $gzip_text";

	if (MEM_USAGE)
	{
		$stat .= ' &nbsp;|&nbsp; Mem: ';
		$stat .= humn_size($bb_cfg['mem_on_start'], 2) .' / ';
		$stat .= (PHP_VERSION >= 5.2) ? humn_size(memory_get_peak_usage(), 2) .' / ' : '';
		$stat .= humn_size(memory_get_usage(), 2);
	}

	if (LOADAVG AND $l = explode(' ', LOADAVG))
	{
		for ($i=0; $i < 3; $i++)
		{
			$l[$i] = round($l[$i], 1);
			$l[$i] = (IS_ADMIN && $bb_cfg['max_srv_load'] && $l[$i] > ($bb_cfg['max_srv_load'] + 4)) ? "<span style='color: red'><b>$l[$i]</b></span>" : $l[$i];
		}
		$stat .= " &nbsp;|&nbsp; Load: $l[0] $l[1] $l[2]";
	}

	$stat .= ' &nbsp;]';

	echo '<div style="margin: 6px; font-size:10px; color: #444444; letter-spacing: -1px; text-align: center;">'. $stat .'</div>';
}

echo '
	</div><!--/body_container-->
';

if (DBG_USER && (SQL_DEBUG || PROFILER))
{
	require(INC_DIR . 'page_footer_dev.php');
}

##### LOG #####
global $log_ip_resp;

if (isset($log_ip_resp[USER_IP]) || isset($log_ip_resp[CLIENT_IP]))
{
	$str = date('H:i:s') . LOG_SEPR . preg_replace("#\s+#", ' ', $contents) . LOG_LF;
	$file = 'sessions/'. date('m-d') .'_{'. USER_IP .'}_'. CLIENT_IP .'_resp';
	bb_log($str, $file);
}
### LOG END ###

if (!empty($GLOBALS['timer_markers']) && DBG_USER)
{
	$GLOBALS['timer']->display();
}

echo '
	</body>
	</html>
';

if (defined('REQUESTED_PAGE') && !defined('DISABLE_CACHING_OUTPUT'))
{
	if (IS_GUEST === true)
	{
		caching_output(true, 'store', REQUESTED_PAGE .'_guest');
	}
}

bb_exit();
