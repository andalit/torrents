<?php
define('IN_PHPBB', true); define('IN_ADMIN', true); define('DEBUG', true);
define('BB_ROOT', './../forum/'); require(BB_ROOT .'common.php');

$show_source_start = __LINE__;
$cnt = 100;
$timer->setMarker('__begin__');
for ($i=0; $i<$cnt; $i++) $Marker_1 = 'code for "Marker 1" ...';
$timer->setMarker('Marker 1');
for ($i=0; $i<$cnt; $i++) $Marker_2 = 'code for "Marker 2" ...';
$timer->setMarker('Marker 2');
prn($Marker_1, $Marker_2);
$show_source_end = __LINE__;

if (!empty($show_source_start) && !empty($show_source_end)) {
	require(DEV_DIR .'dbg_header.php');
	echo showSource(__FILE__, $show_source_start, 0, $show_source_end-$show_source_start-2, false);
}
$GLOBALS['timer']->stop(); $GLOBALS['timer']->display();

die;