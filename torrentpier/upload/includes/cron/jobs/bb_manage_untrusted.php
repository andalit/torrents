<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$max_login_attempts = 10;

$db->query("
	UPDATE ". UNTRUSTED_IPS_TABLE ."
	SET untrusted_pending = 0 WHERE untrusted_attempts > $max_login_attempts AND untrusted_pending = 1
");


$db->query("
	DELETE FROM ". UNTRUSTED_IPS_TABLE ."
	WHERE untrusted_pending = 1
");

