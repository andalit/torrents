<?php

/*
	This file is part of TorrentPier

	TorrentPier is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	TorrentPier is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	A copy of the GPL 2.0 should have been included with the program.
	If not, see http://www.gnu.org/licenses/

	Official SVN repository and contact information can be found at
	http://code.google.com/p/torrentpier/
 */

// Settings
$medal_num_recent = 15; // Number of recent articles you wish to display
$start = NULL;

define('IN_PHPBB', true);
define('BB_SCRIPT', 'medal');
define('BB_ROOT', './');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include(BB_ROOT ."common.$phpEx");
$current_time = (isset($_GET['time']) && $_GET['time'] == 'all') ? 0 : time();
//
// Start session management
//

// Session start
$user->session_start(array('req_login' => $bb_cfg['bt_tor_browse_only_reg']));

//
// End session management
//
//
// Generate page
//
$page_title = $lang['MEDAL'];
require(PAGE_HEADER);

$template->set_filenames(array(
        'body' => 'medal.tpl')
);

if( !empty($bb_cache->db) && !$_GET['re'] && ($out = $bb_cache->get('medal-out')) ) {
	echo $out;
} else {
	ob_start();

make_jumpbox('viewforum.'.$phpEx);

//profiler_block( 'ratio' );

$sql = "SELECT u.username, u.user_id, u.user_regdate, tr.user_id, tr.u_up_total, tr.u_down_total, tr.u_up_bonus, tr.u_up_release,
	(tr.u_up_total+tr.u_up_bonus+tr.u_up_release)/tr.u_down_total AS rat
	FROM " . BT_USERS_TABLE . " tr
	LEFT JOIN " . USERS_TABLE . " u ON u.user_id=tr.user_id
	WHERE tr.u_down_total > 2147483648
	AND u.user_level<>1
	ORDER BY rat DESC
	LIMIT 10";

if( !($result = $db->sql_query($sql)) )
{
        message_die(GENERAL_ERROR, "Could not query users", '', __LINE__, __FILE__, $sql);
}
if ( $row = $db->sql_fetchrow($result) )
{
        $i = 0;
        do
        {
                $username = $row['username'];
                $user_id = $row['user_id'];
                $joined = create_date($lang['DATE_FORMAT'], $row['user_regdate'], $board_config['board_timezone']);
                $ratio = $row['rat'];
                $download = $row['u_down_total'];
                $upload = $row['u_up_total'];

                $row_class = ( !($i % 2) ) ? 'row1' : 'row2';
                $template->assign_block_vars('memberrow', array(
                        'ROW_NUMBER' => $i + ( $start + 1 ),
                        'ROW_CLASS' => $row_class,
                        'USERNAME' => $username,
                        'JOINED' => $joined,
                        'UP_DOWN_RATIO' => round($ratio, 1),
                        'UP' => humn_size ($upload),
                        'DOWN' => humn_size ($download),
                        'BONUS' => humn_size( $row['u_up_bonus'] ),
                        'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id"))
                );
                $i++;
        }
        while ( $row = $db->sql_fetchrow($result) );
        $db->sql_freeresult($result);
}

//profiler_block( 'diff' );

$sql = "SELECT u.username, u.user_id, u.user_regdate, tr.user_id, tr.u_up_total, tr.u_down_total, tr.u_up_bonus,
	round(tr.u_up_total-tr.u_down_total) AS rat1
	FROM " . BT_USERS_TABLE . " tr
	LEFT JOIN " . USERS_TABLE . " u ON u.user_id=tr.user_id
	WHERE tr.u_up_total>tr.u_down_total
	AND u.user_level<>1
	ORDER BY rat1 DESC
	LIMIT 10";

if( !($result = $db->sql_query($sql)) )
{
        message_die(GENERAL_ERROR, 'Could not query users', '', __LINE__, __FILE__, $sql);
}
if ( $row = $db->sql_fetchrow($result) )
{
        $i = 0;
        do
        {
                $username = $row['username'];
                $user_id = $row['user_id'];
                $joined = create_date($lang['DATE_FORMAT'], $row['user_regdate'], $board_config['board_timezone']);
                $ratio1 = $row['rat1'];
                $download = $row['u_down_total'];
                $upload = $row['u_up_total'];

                $row_class = ( !($i % 2) ) ? 'row1' : 'row2';
                $template->assign_block_vars('memberrow1', array(
                        'ROW_NUMBER' => $i + ( $start + 1 ),
                        'ROW_CLASS' => $row_class,
                        'USERNAME' => $username,
                        'JOINED' => $joined,
                        'UP_DOWN_RATIO1' => humn_size ($ratio1),
                        'UP' => humn_size ($upload),
                        'DOWN' => humn_size ($download),
                        'BONUS' => humn_size( $row['u_up_bonus'] ),
                        'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id"))
                );
                $i++;
        }
        while ( $row = $db->sql_fetchrow($result) );
        $db->sql_freeresult($result);
}

//profiler_block( 'best' );

$sql = "SELECT tor.*, t.*, u.username, u.user_id, f.forum_id, f.forum_name, cat.cat_id, cat.cat_title, t.topic_title, t.topic_id
	FROM ". BT_TORRENTS_TABLE ." tor, " . TOPICS_TABLE . " t , " . FORUMS_TABLE. " f , " . USERS_TABLE . " u , " . CATEGORIES_TABLE . " cat
	WHERE tor.topic_id = t.topic_id
	AND tor.poster_id = u.user_id
	AND t.forum_id = f.forum_id
	AND f.cat_id = cat.cat_id
	ORDER BY tor.complete_count DESC
	LIMIT $medal_num_recent";
if( !($result = $db->sql_query($sql)) )
{
        message_die(GENERAL_ERROR, "Could not query torrent", '', __LINE__, __FILE__, $sql);
}
if ( $row = $db->sql_fetchrow($result) )
{
        $i = 0;
        do
        {
                $username = $row['username'];
                $user_id = $row['user_id'];
                $category = $row['cat_id'];
                $forum_name = $row ['forum_name'];
                $forum_id = $row['forum_id'];
                $complete = $row['complete_count'];
                $topic_title = $row['topic_title'];
                $topic_id = $row['topic_id'];
                $reg_time = create_date($lang['DATE_FORMAT'], $row['reg_time'], $board_config['board_timezone']);

                $row_class = ( !($i % 2) ) ? 'row1' : 'row2';
                $template->assign_block_vars('torrentsrow', array(
                        'ROW_NUMBER' => $i + ( $start + 1 ),
                        'ROW_CLASS' => $row_class,
                        'USERNAME' => $username,
                        'CATEGORY' => $category,
                        'FORUM_NAME' => $forum_name,
                        'FORUM_HREF' => append_sid("viewforum.$phpEx?f=". $row['forum_id']),
                        'COMPLETE_COUNT' => $complete,
                        'REG_TIME' => $reg_time,
                        'TOPIC_TITLE' => $topic_title,
                        'TOPIC_HREF'   => append_sid("viewtopic.$phpEx?t=". $row['topic_id']),
                        'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id"))
                );
                $i++;
        }
        while ( $row = $db->sql_fetchrow($result) );
        $db->sql_freeresult($result);

}

//profiler_block( 'best30' );

$sql = "SELECT tor.*, t.*, u.username, u.user_id, f.forum_id, f.forum_name, cat.cat_id, cat.cat_title, t.topic_title, t.topic_id
	FROM ". BT_TORRENTS_TABLE ." tor, " . TOPICS_TABLE . " t , " . FORUMS_TABLE. " f , " . USERS_TABLE . " u , " . CATEGORIES_TABLE . " cat
	WHERE tor.topic_id = t.topic_id
	AND tor.poster_id = u.user_id
	AND t.forum_id = f.forum_id
	AND f.cat_id = cat.cat_id
	AND tor.reg_time>".( time() - 30*24*3600 )."
	ORDER BY tor.complete_count DESC
	LIMIT $medal_num_recent";

if( !($result = $db->sql_query($sql)) )
{
        message_die(GENERAL_ERROR, "Could not query torrent", '', __LINE__, __FILE__, $sql);
}
if ( $row = $db->sql_fetchrow($result) )
{
        $i = 0;
        do
        {
                $username = $row['username'];
                $user_id = $row['user_id'];
                $category = $row['cat_id'];
                $forum_name = $row ['forum_name'];
                $forum_id = $row['forum_id'];
                $complete = $row['complete_count'];
                $topic_title = $row['topic_title'];
                $topic_id = $row['topic_id'];
                $reg_time = create_date($lang['DATE_FORMAT'], $row['reg_time'], $board_config['board_timezone']);

                $row_class = ( !($i % 2) ) ? 'row1' : 'row2';
                $template->assign_block_vars('torrent30', array(
                        'ROW_NUMBER' => $i + ( $start + 1 ),
                        'ROW_CLASS' => $row_class,
                        'USERNAME' => $username,
                        'CATEGORY' => $category,
                        'FORUM_NAME' => $forum_name,
                        'FORUM_HREF' => append_sid("viewforum.$phpEx?f=". $row['forum_id']),
                        'COMPLETE_COUNT' => $complete,
                        'REG_TIME' => $reg_time,
                        'TOPIC_TITLE' => $topic_title,
                        'TOPIC_HREF'   => append_sid("viewtopic.$phpEx?t=". $row['topic_id']),
                        'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id"))
                );
                $i++;
        }
        while ( $row = $db->sql_fetchrow($result) );
        $db->sql_freeresult($result);

}

//profiler_block( 'best7' );

$sql = "SELECT tor.*, t.*, u.username, u.user_id, f.forum_id, f.forum_name, cat.cat_id, cat.cat_title, t.topic_title, t.topic_id
	FROM ". BT_TORRENTS_TABLE ." tor, " . TOPICS_TABLE . " t , " . FORUMS_TABLE. " f , " . USERS_TABLE . " u , " . CATEGORIES_TABLE . " cat
	WHERE tor.topic_id = t.topic_id
	AND tor.poster_id = u.user_id
	AND t.forum_id = f.forum_id
	AND f.cat_id = cat.cat_id
	AND tor.reg_time>".( time() - 7*24*3600 )."
	ORDER BY tor.complete_count DESC
	LIMIT $medal_num_recent";
	
if( !($result = $db->sql_query($sql)) )
{
        message_die(GENERAL_ERROR, "Could not query torrent", '', __LINE__, __FILE__, $sql);
}
if ( $row = $db->sql_fetchrow($result) )
{
        $i = 0;
        do
        {
                $username = $row['username'];
                $user_id = $row['user_id'];
                $category = $row['cat_id'];
                $forum_name = $row ['forum_name'];
                $forum_id = $row['forum_id'];
                $complete = $row['complete_count'];
                $topic_title = $row['topic_title'];
                $topic_id = $row['topic_id'];
                $reg_time = create_date($lang['DATE_FORMAT'], $row['reg_time'], $board_config['board_timezone']);

                $row_class = ( !($i % 2) ) ? 'row1' : 'row2';
                $template->assign_block_vars('torrent7', array(
                        'ROW_NUMBER' => $i + ( $start + 1 ),
                        'ROW_CLASS' => $row_class,
                        'USERNAME' => $username,
                        'CATEGORY' => $category,
                        'FORUM_NAME' => $forum_name,
                        'FORUM_HREF' => append_sid("viewforum.$phpEx?f=". $row['forum_id']),
                        'COMPLETE_COUNT' => $complete,
                        'REG_TIME' => $reg_time,
                        'TOPIC_TITLE' => $topic_title,
                        'TOPIC_HREF'   => append_sid("viewtopic.$phpEx?t=". $row['topic_id']),
                        'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$user_id"))
                );
                $i++;
        }
        while ( $row = $db->sql_fetchrow($result) );
        $db->sql_freeresult($result);

}

//profiler_block( 'count' );

$sql = "SELECT u.user_id, u.username, u.user_regdate, SUM(complete_count) AS rc, COUNT(*) AS cc
	FROM ". BT_TORRENTS_TABLE ." t join ". USERS_TABLE ." u on t.poster_id=user_id
	GROUP BY t.poster_id
	ORDER BY rc desc
	LIMIT 20";

if( !($result = $db->sql_query($sql)) )
{
        message_die(GENERAL_ERROR, "Could not query d/l counts", '', __LINE__, __FILE__, $sql);
}
$i = 0;
while ( $row = $db->sql_fetchrow($result) )
{
	$row_class = ( !($i % 2) ) ? 'row1' : 'row2';
	$i++;
	$template->assign_block_vars('countrow', array(
		'ROW_NUMBER' => $i,

		'ROW_CLASS' => $row_class,
		'USERNAME' => $row['username'],
		'JOINED' => create_date($lang['DATE_FORMAT'], $row['user_regdate'], $board_config['board_timezone']),
		'DL_COUNT' => $row['rc'],
		'RELEASES' => $row['cc'],
		'DL_AVG' => round( $row['rc'] / $row['cc'] ),
		'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;". POST_USERS_URL ."=". $row['user_id']),
		'U_RELEASES' => append_sid("tracker.php?pid=". $row['user_id']),
	));
}

//profiler_block( 'count30' );

$sql = "SELECT u.user_id, u.username, u.user_regdate, SUM(complete_count) AS rc, COUNT(*) AS cc
	FROM ". BT_TORRENTS_TABLE ." t
	JOIN ". USERS_TABLE ." u on t.poster_id=user_id
	WHERE t.reg_time>".( time() - 30*24*3600 )."
	GROUP BY t.poster_id
	ORDER BY rc desc
	LIMIT $medal_num_recent";

if( !($result = $db->sql_query($sql)) )
{
        message_die(GENERAL_ERROR, "Could not query d/l counts", '', __LINE__, __FILE__, $sql);
}
$i = 0;
while ( $row = $db->sql_fetchrow($result) )
{
	$row_class = ( !($i % 2) ) ? 'row1' : 'row2';
	$i++;
	$template->assign_block_vars('count30', array(
		'ROW_NUMBER' => $i,

		'ROW_CLASS' => $row_class,
		'USERNAME' => $row['username'],
		'JOINED' => create_date($lang['DATE_FORMAT'], $row['user_regdate'], $board_config['board_timezone']),
		'DL_COUNT' => $row['rc'],
		'RELEASES' => $row['cc'],
		'DL_AVG' => round( $row['rc'] / $row['cc'] ),
		'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;". POST_USERS_URL ."=". $row['user_id']),
		'U_RELEASES' => append_sid("tracker.php?pid=". $row['user_id']),
	));
}

//profiler_block( 'thanks' );
/*
$sql = "SELECT u.user_id, u.username, u.user_regdate, SUM(d.thanks) AS rc, count(*) AS cc,
	SUM(d.rating_sum) AS r_sum, SUM(d.rating_count) AS r_count
	FROM ". BT_TORRENTS_TABLE ." t join ". USERS_TABLE ." u on t.poster_id=user_id
	JOIN ". ATTACHMENTS_DESC_TABLE ." d on d.attach_id=t.attach_id
	GROUP by t.poster_id
	ORDER BY rc desc
	LIMIT 20";


if( !($result = $db->sql_query($sql)) )
{
        message_die(GENERAL_ERROR, "Could not query thanks", '', __LINE__, __FILE__, $sql);
}
$i = 0;
while ( $row = $db->sql_fetchrow($result) )
{
	$row_class = ( !($i % 2) ) ? 'row1' : 'row2';
	$i++;
	$template->assign_block_vars('thankrow', array(
		'ROW_NUMBER' => $i,

		'ROW_CLASS' => $row_class,
		'USERNAME' => $row['username'],
		'JOINED' => create_date($lang['DATE_FORMAT'], $row['user_regdate'], $board_config['board_timezone']),
		'THANKS' => $row['rc'],
		'RELEASES' => $row['cc'],
		'RATE' => $row['r_count'] ? round($row['r_sum']/$row['r_count'],2) .' ('. $row['r_count'] .')' : '',
		'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;". POST_USERS_URL ."=". $row['user_id']),
		'U_RELEASES' => append_sid("tracker.php?pid=". $row['user_id']),
	));
}
*/
//profiler_block( 'thank30' );
/*
$sql = "SELECT u.user_id, u.username, u.user_regdate, SUM(d.thanks) AS rc, COUNT(*) AS cc,
	SUM(d.rating_sum) AS r_sum, SUM(d.rating_count) AS r_count
	FROM ". BT_TORRENTS_TABLE ." t join ". USERS_TABLE ." u on t.poster_id=user_id
	JOIN ". ATTACHMENTS_DESC_TABLE ." d on d.attach_id=t.attach_id
	WHERE t.reg_time>".( time() - 30*24*3600 )."
	GROUP BY t.poster_id
	ORDER BY rc desc
	LIMIT $medal_num_recent";

if( !($result = $db->sql_query($sql)) )
{
        message_die(GENERAL_ERROR, "Could not query thanks", '', __LINE__, __FILE__, $sql);
}
$i = 0;
while ( $row = $db->sql_fetchrow($result) )
{
	$row_class = ( !($i % 2) ) ? 'row1' : 'row2';
	$i++;
	$template->assign_block_vars('thank30', array(
		'ROW_NUMBER' => $i,

		'ROW_CLASS' => $row_class,
		'USERNAME' => $row['username'],
		'JOINED' => create_date($lang['DATE_FORMAT'], $row['user_regdate'], $board_config['board_timezone']),
		'THANKS' => $row['rc'],
		'RELEASES' => $row['cc'],
		'RATE' => $row['r_count'] ? round($row['r_sum']/$row['r_count'],2) .' ('. $row['r_count'] .')' : '',
		'U_VIEWPROFILE' => append_sid("profile.$phpEx?mode=viewprofile&amp;". POST_USERS_URL ."=". $row['user_id']),
		'U_RELEASES' => append_sid("tracker.php?pid=". $row['user_id']),
	));
}
*/
//profiler_block( 'template' );

$template->assign_vars(array(
	'PAGE_TITLE' => $page_title,
));

$template->pparse('body');

	$out = ob_get_contents();
	if (!empty($bb_cache->db)) $bb_cache->set( 'medal-out', $out, 7200 );
}

require(PAGE_FOOTER);

?>