<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

require(DEV_DIR .'dbg_config.'.      PHP_EXT);
require(DEV_DIR .'functions_debug.'. PHP_EXT);

//
// Timer
//
require(DEV_DIR .'benchmark/timer.'. PHP_EXT);
$timer_markers = 0;
$timer = new Benchmark_Timer();
$GLOBALS['timer']->start();
#	$GLOBALS['timer']->setMarker();                // empty setMarker() will point to "source(line)"
#	$GLOBALS['timer']->setMarker('Marker 1');
#	$GLOBALS['timer']->setMarker('Marker 1 End');
#	$GLOBALS['timer']->stop();
#	$GLOBALS['timer']->display(); die;

//
// HackerConsole
//
require(DEV_DIR .'HackerConsole/Main.'. PHP_EXT);
$dbgCons = new Debug_HackerConsole_Main();
#	hc($var, $title);
#	new Debug_HackerConsole_Main();
#	Debug_HackerConsole_Main::out($var);
#	Debug_HackerConsole_Main::out($_SERVER, "Input");
#	$dbgCons->out("message 2", 'group 2');

function hc ($var, $title = '')
{
	Debug_HackerConsole_Main::out($var, $title);
}

//
// Error handler
//
require(DEV_DIR .'error_handler.'. PHP_EXT);

//
// OB conveyer
//
function prepend_debug_info ($contents)
{
	if (method_exists(@$GLOBALS['dbgCons'], 'entries_count') && $GLOBALS['dbgCons']->entries_count())
	{
		$contents = $GLOBALS['dbgCons']->attachToHtml($contents);
#		bb_setcookie('console', 1);
	}
	if ($errors = $GLOBALS['errHandler']->get_clean_errors())
	{
		$contents = file_get_contents(DEV_DIR .'dbg_header.'. PHP_EXT) . $errors . $contents;
	}

	return $contents;
}

ob_start('prepend_debug_info');

//
// Var_Dump
//
require(DEV_DIR .'Var_Dump.'. PHP_EXT);

Var_Dump::displayInit(
	array(
		'display_mode'   => 'HTML4_Text',
	),
	array(
		'mode'           => 'normal',
		'offset'         => 1,
	)
);
function dump ($var, $title = '')
{
	if ($title) echo "<h4>$title</h4>\n";
	Var_Dump::display($var);
}

