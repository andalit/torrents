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
**    Folders     **
********************
  +[DIR] other
  |- backup\dumper.php - very convenient program to create backup MySQL server, as well as restore (http://sypex.net/)
  |- test              - the test scripts to work MySQL, server apache.
  |- update            - program to update the script with earlier versions
  |- bench             - Memories ... that in the script but after the site included debug
  |- bt_simple         - not the original and untested version of announce, better use bt
  |- SphinX config     - Configuration file for (SphinX Search Engine)

  +[DIR] upload
  |- bt                - anonser without it, you can not transfer information via tracker
  |- *                 - a forum tracker TorrentPier R775 Modern

  +[DIR] sql           - Dump MySQL database that is absolutely pouring
  +[DIR] mod           - the folder with mod

********************
**  Installation  **
********************
Unpack at the server
 [*] contents of the folder 'upload'
 [*] files favicon.ico (changing its icon if any), robots.txt (admission or ban bot search engines to the server, not all blocks)
 [*] phpmyadmin go to open or create a new database, then dump Import (no MySQL_dump folder upload on the server)!
 [*] Edit config.php change data in the database log at the discretion of the rest.
    -- Remember to adjust it in config.php

// Cookie
$bb_cfg['cookie_domain'] = '.mysite.ru';     # '.yourdomain.com'
$bb_cfg['cookie_path']   = '/';              # '/forum/'

$bb_cfg['script_path'] = '/';

**********************************************
** The right of access to folders and files **
**********************************************

Set the right of access to data folders 777:

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
** Necessary values in php.ini    **
************************************
mbstring.internal_encoding = UTF-8
magic_quotes_gpc = Off

***********************
** Question / Answer **
***********************

_______________________________________________________________
How do I change "SiteName" ... "site slogan, site slogan site slogan site slogan?
The file is in the templates\default\memberlist.tpl

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
How do I change the words to their torrents.ru?
files are where this occurs:

forum\language\lang_russian\lang_main.php
forum\misc\html\advert.html
forum\misc\html\copyright_holders.html
forum\misc\html\user_agreement.html
forum\templates\board_disabled_exit.php
forum\templates\limit_load_exit.php
forum\templates\topic_tpl_overall_header.html
forum\templates\topic_tpl_rules_video.html
_______________________________________________________________
Question: How do I remove the line with debug information "[Execution time: 2.419 sec | MySQL: 9 queries | GZIP ON | Mem: 441.36 KB / 2.43 MB / 1.91 MB]"?
Answer: We change the file includes \ page_footer.php value $ show_dbg_info = true; at $ show_dbg_info = false;
_______________________________________________________________
Question: How do I remove the line on the main page of "Forum ****"?
Answer: File "templates \ default \ index.tpl"
Remove from the following lines

<div id="forums_top_nav">
    <h1 class="pagetitle"> <a href="{U_INDEX}"> (T_INDEX) </ a> </ h1>
</ div ><!--/ forums_top_nav ->