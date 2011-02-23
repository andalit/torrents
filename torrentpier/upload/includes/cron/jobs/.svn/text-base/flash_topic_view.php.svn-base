<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

// Lock tables
$db->lock(array(
	TOPICS_TABLE         .' t',
	BUF_TOPIC_VIEW_TABLE .' buf',
));

// Flash buffered records
$db->query("
	UPDATE
		". TOPICS_TABLE         ." t,
		". BUF_TOPIC_VIEW_TABLE ." buf
	SET
		t.topic_views = t.topic_views + buf.topic_views
	WHERE
		t.topic_id = buf.topic_id
");

// Delete buffered records
$db->query("DELETE buf FROM ". BUF_TOPIC_VIEW_TABLE ." buf");

// Unlock tables
$db->unlock();

