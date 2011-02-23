<?
define('COOKIE_DOMAIN', '.'. preg_replace('#^(www\.)?(.*)$#i', '\\2', trim($_SERVER['SERVER_NAME'])));
define('COOKIE_PATH', str_replace(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '', str_replace('\\', '/', realpath(BB_ROOT) .'/')));
define('COOKIE_SECURE', (int) (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off'));
?>

<span style="color: darkred">

COOKIE_DOMAIN<br>
<b><?= COOKIE_DOMAIN ?></b>
<br><br>

COOKIE_PATH<br>
<b><?= COOKIE_PATH ?></b>
<br><br>

COOKIE_SECURE<br>
<b><?= COOKIE_SECURE ?></b>
<br><br>

</span>

<hr><br>

__FILE__<br>
<b><?= __FILE__ ?></b>
<br><br>

basename(__FILE__)<br>
<b><?= basename(__FILE__) ?></b>
<br><br>

dirname(__FILE__)<br>
<b><?= dirname(__FILE__) ?></b>
<br><br>

realpath(__FILE__)<br>
<b><?= realpath(__FILE__) ?></b>
<br><br>

$_SERVER['DOCUMENT_ROOT']<br>
<b><?= $_SERVER['DOCUMENT_ROOT'] ?></b>
<br><br>

$_SERVER['SERVER_NAME']<br>
<b><?= $_SERVER['SERVER_NAME'] ?></b>
<br><br>

realpath(BB_ROOT) [BB_ROOT = <b><?= BB_ROOT ?></b>]<br>
<b><?= realpath(BB_ROOT) ?></b>
<br><br>

$_SERVER['HTTPS']<br>
<b><? echo (isset($_SERVER['HTTPS'])) ? $_SERVER['HTTPS'] : '<i>undefined</i>'; ?></b>
<br><br>

