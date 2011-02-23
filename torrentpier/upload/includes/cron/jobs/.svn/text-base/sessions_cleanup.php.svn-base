<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$user_session_expire_time  = TIMENOW - intval($bb_cfg['user_session_duration']);
$admin_session_expire_time = TIMENOW - intval($bb_cfg['admin_session_duration']);

$user_session_gc_time  = $user_session_expire_time - intval($bb_cfg['user_session_gc_ttl']);
$admin_session_gc_time = $admin_session_expire_time;

// ############################ Tables LOCKED ################################
$db->lock(array(
	USERS_TABLE    .' u',
	SESSIONS_TABLE .' s',
));

// Update user's session time
$db->query("
	UPDATE
		". USERS_TABLE    ." u,
		". SESSIONS_TABLE ." s
	SET
		u.user_session_time = IF(u.user_session_time < s.session_time, s.session_time, u.user_session_time)
	WHERE
				u.user_id = s.session_user_id
		AND s.session_user_id != ". ANONYMOUS ."
		AND (
			(s.session_time < $user_session_expire_time AND s.session_admin = 0)
			OR
			(s.session_time < $admin_session_expire_time AND s.session_admin != 0)
		)
");

$db->unlock();
// ############################ Tables UNLOCKED ##############################

sleep(5);

// Delete staled sessions
$db->query("
	DELETE s
	FROM ". SESSIONS_TABLE ." s
	WHERE
		(s.session_time < $user_session_gc_time AND s.session_admin = 0)
		OR
		(s.session_time < $admin_session_gc_time AND s.session_admin != 0)
");

