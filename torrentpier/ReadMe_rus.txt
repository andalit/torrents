//////////////////////////////////////
//                                  //
// TorrentPier SVN based for R775   //
// Time/Date: 13:00 14.01.2009      //
// Site: http://torrentpier.info/   //
//                                  //
// Project owners:                  //
//   roadtrain4eg, PandoraBox2007   //
//                                  //
// Project members:                 //
//   segalws, givemex, bulanovk,    //
//   johnny.concent                 //
//                                  //
// Code modification:               //
//   Pandora, RoadTrain             //
//                                  //
//////////////////////////////////////

********************
**     Папки      **
********************
  +[DIR] other
  |- backup\dumper.php -    Очень удобная программа для создания бэкапа MySQL сервера, а также восстановить (http://sypex.net/)
  |- test              -    скрипты для теста работы MySQL, сервера, апатча.
  |- update            -    программа для обновление скрипта с более ранних версий
  |- bench             -    хз... что за скрипт но после него включается дебаг сайта
  |- bt_simple         -    оригинальная версия анонсера не тестил не знаю юзайте лучше bt
  |- SphinX config     -    Файлы конфигурации для (SphinX Search Engine)

  +[DIR] upload
  |- bt                -    анонсер без него нельзя передавать информацию через трекер
  |- *                 -    форум трекера TorrentPier R775 Modern

  +[DIR] SQL           - Дамп БД MySQL который нада залить
  +[DIR] mod           - папка с модами

********************
**   Установка    **
********************
Распаковываем на сервер
 [*] содержимое папки upload
 [*] файлы favicon.ico (меняем на свою иконку если есть), robots.txt(Допуск или запрет ботам поисковиков к серверу, блокирует не все)
 [*] зайти в phpmyadmin открыть или создать новую базу, потом Импортировать дамп (папку MySQL_dump не заливать на сервер)!
 [*] Отредактировать config.php: изменить данные входа в БД остальное по усмотрению.
   - Не забываем это настроить в config.php    

// Cookie
$bb_cfg['cookie_domain'] = '.mysite.ru';     # '.yourdomain.com'
$bb_cfg['cookie_path']   = '/';              # '/forum/'

$bb_cfg['script_path'] = '/';

************************************
** Права доступа на папки и файлы **
************************************

Устанавливаем права доступа на данные папки 777:

- ajax
- ajax/html 
- images
- images/avatars
- images/avatars/gallery
- images/flags
- images/logo
- images/ranks
- images/smiles
- cache
- cache/filecache/*
- files
- files/thumbs
- log
- pictures
- triggers

************************************
** Необходимые значения в php.ini **
************************************
mbstring.internal_encoding = UTF-8
magic_quotes_gpc = Off

******************
** Вопрос/Ответ **
******************
_______________________________________________________________
Вопрос: Как мне изменить "SiteName" ... "site slogan, site slogan site slogan site slogan"?
Ответ: Файл находится в templates\default\memberlist.tpl

<!--logo-->
<div id="logo">
	<h1>{SITENAME}</h1>
	<h6>{SITE_DESCRIPTION}</h6>
<!--
	<a href="{U_INDEX}"><img src="images/logo/logo.jpg" alt="" /></a>
-->
</div>
<!--/logo-->
_______________________________________________________________
Вопрос: Как мне изменить слова torrents.ru на свои? (устарело, больше не требуется)
Ответ: вот файлы где встречается это -

forum\language\lang_russian\lang_main.php
forum\misc\html\advert.html
forum\misc\html\copyright_holders.html
forum\misc\html\user_agreement.html
forum\templates\board_disabled_exit.php
forum\templates\limit_load_exit.php
forum\templates\topic_tpl_overall_header.html
forum\templates\topic_tpl_rules_video.html
_______________________________________________________________
Вопрос: Как убрать строчку отладочной информации "[  Execution time: 2.419 sec  |  MySQL: 9 queries |  GZIP ON  |  Mem: 441.36 KB / 2.43 MB / 1.91 MB  ]"?
Ответ: Меняем в файле includes\page_footer.php значение $show_dbg_info = true; на $show_dbg_info = false;

_______________________________________________________________
Вопрос: Как мне удалить строку на главной странице "Список форумов ****"?
Ответ: Файл "templates\default\index.tpl"
Убрать оттуда следующие строки

<div id="forums_top_nav">
   <h1 class="pagetitle"><a href="{U_INDEX}">{T_INDEX}</a></h1>
</div><!--/forums_top_nav-->