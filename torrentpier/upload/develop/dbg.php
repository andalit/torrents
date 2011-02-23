<?php

define('IN_PHPBB', true);
define('BB_ROOT', './../');
require(BB_ROOT .'common.php');

if (DEBUG !== true) die(basename(__FILE__));

$mode = @$_REQUEST['mode'];