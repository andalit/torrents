<?php

$locale  = 'ru_RU.CP1251';
$charset = 'windows-1251';
$text    = 'ЁАБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';

setlocale(LC_ALL, $locale);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$charset?>">
<meta http-equiv="Content-Style-Type" content="text/css">
</head>
<body>
<pre>
<b>text</b>  : <?= $text ?><br />
<b>lower</b> : <?= strtolower($text) ?><br />
<br />
<b>time</b>  : <?= strftime("%A %B %Z") ?><br />
<br />
<b>locale -a | grep ru</b><br />
<?= system('locale -a | grep ru') ?><br />
<br />
<b>locale -a</b><br />
<?= system('locale -a') ?><br />

</pre>
</body>
</html>