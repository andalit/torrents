<?php

define('FORCE_CRON', true);
@ini_set ('memory_limit', "1M");
@set_time_limit(0);
@ini_set('max_execution_time', 0);
@ini_set("output_buffering", "off");
@ob_end_clean();
ob_implicit_flush(true);
error_reporting(1);
extract($_REQUEST, EXTR_SKIP);
require('./common.php');
exit;