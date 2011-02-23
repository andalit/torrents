<?php

define('IN_PHPBB', true);
define('BB_ROOT', './../forum/');
require(BB_ROOT .'common.php');

$ary2 = array();

$ary1 = array(
	'f1_int'   => (int) -1,
	'f2_int'   => (int) 1,
	'f3_int'   => (int) "a'aa",
	'f4_bool'  => (bool) false,
	'f5_bool'  => (bool) "1'or",
	'f6_str'   => (string) 777,
	'f7_str'   => (string) 'esc_\_\'_"_'. chr(0x00) .'_',
	'f8_float' => (float) 2147483647*2147483647,
	'f9_null'  => null,
//'error'    => array('1' => 2),
);
$ary2[] = $ary1;
$ary2[] = $ary1;
//$ary2[] = $ary1;

$INSERT        = $db->build_array('INSERT', $ary1);
$INSERT_SELECT = $db->build_array('INSERT_SELECT', $ary1);
$MULTI_INSERT  = $db->build_array('MULTI_INSERT', $ary2);
$UPDATE        = $db->build_array('UPDATE', $ary1);
$SELECT        = $db->build_array('SELECT', $ary1);

?>
<pre>

<b>$ary1</b><br>
<?= print_r($ary1) ?>
<br><br>

<b>$ary2</b><br>
<?= print_r($ary2) ?>
<br><br>

<b>$INSERT</b><br>
<?= $INSERT ?>
<br><br>

<b>$INSERT_SELECT</b><br>
<?= $INSERT_SELECT ?>
<br><br>

<b>$MULTI_INSERT</b><br>
<?= $MULTI_INSERT ?>
<br><br>

<b>$UPDATE</b><br>
<?= $UPDATE ?>
<br><br>

<b>$SELECT</b><br>
<?= $SELECT ?>
<br><br>

</pre>