<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

global $db;

$ranks = array();

$sql = "SELECT rank_id, rank_title, rank_image FROM ". RANKS_TABLE;

foreach ($db->fetch_rowset($sql) as $row)
{
	$ranks[$row['rank_id']] = $row;
}

$this->store('ranks', $ranks);
