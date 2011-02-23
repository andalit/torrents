<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

global $db;

$data = array();

// usercount
$row = $db->fetch_row("SELECT COUNT(*) AS usercount FROM ". USERS_TABLE ." WHERE user_id NOT IN(". EXCLUDED_USERS_CSV .")");
$data['usercount'] = number_format($row['usercount']);

// newestuser
$row = $db->fetch_row("SELECT user_id, username FROM ". USERS_TABLE ." ORDER BY user_id DESC LIMIT 1");
$data['newestuser'] = $row;

// post/topic count
$row = $db->fetch_row("SELECT SUM(forum_topics) AS topiccount, SUM(forum_posts) AS postcount FROM ". FORUMS_TABLE);
$data['postcount'] = number_format($row['postcount']);
$data['topiccount'] = number_format($row['topiccount']);

// torrents stat
$row = $db->fetch_row("SELECT COUNT(topic_id) AS torrentcount, SUM(size) AS size FROM ". BT_TORRENTS_TABLE);
$data['torrentcount'] = number_format($row['torrentcount']);
$data['size'] = $row['size'];

// peers stat
$row = $db->fetch_row("SELECT SUM(seeders) AS seeders, SUM(leechers) AS leechers, ((SUM(speed_up) + SUM(speed_down))/2) AS speed FROM ". BT_TRACKER_SNAP_TABLE);
$data['seeders']  = number_format($row['seeders']);
$data['leechers'] = number_format($row['leechers']);
$data['peers']    = number_format($row['seeders'] + $row['leechers']);
$data['speed']    = $row['speed'];

$this->store('stats', $data);
