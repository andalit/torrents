<?php
function run_jobs($jobs) {
	global $db, $bb_cfg, $datastore;
	
	define('IN_CRON', true);

	if (!defined('DBCHARSET')) define('DBCHARSET', 'latin1');
	$sql = "SELECT cron_script
			FROM " . CRON_TABLE ."
			WHERE cron_id IN ($jobs)";
	if (!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not obtain cron script', '', __LINE__, __FILE__, $sql);
	}	

	while ($row = $db->sql_fetchrow($result))
	{
		$job = $row['cron_script'];
		$job_script = BB_ROOT . 'includes/cron/jobs/' . $job;
		require($job_script);
	}
	$db->query("
			UPDATE ". CRON_TABLE ." SET
				last_run = NOW(),
				run_counter = run_counter + 1,
				next_run =
			CASE
				WHEN schedule = 'hourly' THEN
					DATE_ADD(NOW(), INTERVAL 1 HOUR)
				WHEN schedule = 'daily' THEN
					DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL TIME_TO_SEC(run_time) SECOND)
				WHEN schedule = 'weekly' THEN
					DATE_ADD(
						DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(NOW()) DAY), INTERVAL 7 DAY),
					INTERVAL CONCAT(ROUND(run_day-1), ' ', run_time) DAY_SECOND)
				WHEN schedule = 'monthly' THEN
					DATE_ADD(
						DATE_ADD(DATE_SUB(CURDATE(), INTERVAL DAYOFMONTH(NOW())-1 DAY), INTERVAL 1 MONTH),
					INTERVAL CONCAT(ROUND(run_day-1), ' ', run_time) DAY_SECOND)
				ELSE
					DATE_ADD(NOW(), INTERVAL TIME_TO_SEC(run_interval) SECOND)
			END
			WHERE cron_id IN ($jobs)			
		");
	sleep(3);
	return;
}

function delete_jobs($jobs) {
	global $db;	
	$db->query("DELETE FROM " . CRON_TABLE . " WHERE cron_id IN ($jobs)");	
	return;
}

function toggle_active($jobs, $cron_action) {
	global $db;	
	$active = ($cron_action == 'disable') ? 0 : 1;
	$db->query("UPDATE " . CRON_TABLE . " SET cron_active = $active WHERE cron_id IN ($jobs)");	
	return;
}

function validate_cron_post($cron_arr) {
	$errors = 'Errors in: ';
	$errnum = 0;
	if (!$cron_arr['cron_title']){
		$errors .= 'cron title (empty value), ';
		$errnum++;
	}	
	if (!$cron_arr['cron_script']){
		$errors .= 'cron script (empty value), ';
		$errnum++;
	}
	if ($errnum > 0){
		$result = $errors . ' total ' . $errnum . ' errors <br/> <a href="javascript:history.back(-1)">Back</a>';
	}
	else {
		$result = 1;
	}	
	return $result;
}

function insert_cron_job($cron_arr) {
	global $db;
	
	$cron_active = $cron_arr['cron_active'];
	$cron_title = $cron_arr['cron_title'];
	$cron_script = $cron_arr['cron_script'];
	$schedule = $cron_arr['schedule'];
	$run_day = $cron_arr['run_day'];
	$run_time = $cron_arr['run_time'];
	$run_order = $cron_arr['run_order'];
	$last_run = $cron_arr['last_run'];
	$next_run = $cron_arr['next_run'];
	$run_interval = $cron_arr['run_interval'];
	$log_enabled = $cron_arr['log_enabled'];
	$log_file = $cron_arr['log_file'];
	$log_sql_queries = $cron_arr['log_sql_queries'];
	$disable_board = $cron_arr['disable_board'];
	$run_counter = $cron_arr['run_counter'];
	$db->query("INSERT INTO ".CRON_TABLE." (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, last_run, next_run, run_interval, log_enabled, log_file, log_sql_queries, disable_board, run_counter) VALUES ( 
	$cron_active, '$cron_title', '$cron_script', '$schedule', '$run_day', '$run_time', '$run_order', '$last_run', '$next_run', '$run_interval', $log_enabled, '$log_file', $log_sql_queries, $disable_board, '$run_counter')");	
}	

function update_cron_job($cron_arr) {
	global $db;
	
	$cron_id = $cron_arr['cron_id'];
	$cron_active = $cron_arr['cron_active'];
	$cron_title = $db->escape($cron_arr['cron_title']);
	$cron_script = $db->escape($cron_arr['cron_script']);
	$schedule = $cron_arr['schedule'];
	$run_day = $cron_arr['run_day'];
	$run_time = $cron_arr['run_time'];
	$run_order = $cron_arr['run_order'];
	$last_run = $cron_arr['last_run'];
	$next_run = $cron_arr['next_run'];
	$run_interval = $cron_arr['run_interval'];
	$log_enabled = $cron_arr['log_enabled'];
	$log_file = $db->escape($cron_arr['log_file']);
	$log_sql_queries = $cron_arr['log_sql_queries'];
	$disable_board = $cron_arr['disable_board'];
	$run_counter = $cron_arr['run_counter'];
	
	$db->query("UPDATE " . CRON_TABLE . " SET
		cron_active = '$cron_active',
		cron_title = '$cron_title',
		cron_script = '$cron_script',
		schedule = '$schedule',
		run_day = '$run_day',
		run_time = '$run_time',
		run_order = '$run_order',
		last_run = '$last_run',
		next_run = '$next_run',
		run_interval = '$run_interval',
		log_enabled = '$log_enabled',
		log_file = '$log_file',
		log_sql_queries = '$log_sql_queries',
		disable_board = '$disable_board',
		run_counter = '$run_counter'
	WHERE cron_id = $cron_id	
	");
}	

function update_config_php($config_option_name, $new_value) {
	$file = file(BB_ROOT.'config.php');
	$i = 0;
	$count = count($file);
	while ($i<$count) {
		if (preg_match("/$config_option_name/i", $file[$i])) { 
			$line = explode(';', $file[$i]); //explode comments
			$line[0] = '$bb_cfg[\''.$config_option_name.'\'] = '.$new_value.''; //assign a new value
			$file[$i] = implode(';', $line); //build a new line
		
			$fp = fopen(BB_ROOT."config.php","w"); 
			fputs($fp,implode("",$file)); 
			fclose($fp);
		}
	$i++;	
	}
	return;
}