# buffers
SET SESSION myisam_sort_buffer_size = 16*1024*1024;
SET SESSION bulk_insert_buffer_size =  8*1024*1024;

# bb_sessions
DELETE FROM `bb_sessions`;

ALTER TABLE `bb_sessions` DROP session_page;
ALTER TABLE `bb_sessions` CHANGE session_id session_id CHAR(20) binary NOT NULL default '';
ALTER TABLE `bb_sessions` CHANGE session_ip session_ip CHAR(8) binary NOT NULL default '';
ALTER TABLE `bb_sessions` DROP INDEX session_user_id;
ALTER TABLE `bb_sessions` DROP INDEX session_id_ip_user_id;

# bb_confirm
DELETE FROM `bb_confirm`;
ALTER TABLE `bb_confirm` CHANGE confirm_id confirm_id CHAR(12) binary NOT NULL default '';
ALTER TABLE `bb_confirm` CHANGE session_id session_id CHAR(20) binary NOT NULL default '';

# bb_attachments_config
REPLACE INTO `bb_attachments_config` (config_name, config_value) VALUES ('img_create_thumbnail', '0');
DELETE FROM `bb_attachments_config` WHERE config_name = 'attachment_topic_review';
DELETE FROM `bb_attachments_config` WHERE config_name = 'show_apcp';

# bb_config
ALTER TABLE `bb_config` CHANGE config_value config_value TEXT NOT NULL default '';

DELETE FROM `bb_config` WHERE config_name = 'allow_html';
DELETE FROM `bb_config` WHERE config_name = 'allow_html_tags';
DELETE FROM `bb_config` WHERE config_name = 'allow_theme_create';
DELETE FROM `bb_config` WHERE config_name = 'bt_dl_list_expire';
DELETE FROM `bb_config` WHERE config_name = 'bt_force_passkey';
DELETE FROM `bb_config` WHERE config_name = 'bt_search_tbl_last_clean';
DELETE FROM `bb_config` WHERE config_name = 'cookie_domain';
DELETE FROM `bb_config` WHERE config_name = 'cookie_name';
DELETE FROM `bb_config` WHERE config_name = 'cookie_path';
DELETE FROM `bb_config` WHERE config_name = 'cookie_secure';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_disallow_postcounter';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_disallow_rebuild';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_rebuild_end';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_rebuild_pos';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_rebuildcfg_maxmemory';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_rebuildcfg_minposts';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_rebuildcfg_php3only';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_rebuildcfg_php3pps';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_rebuildcfg_php4pps';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_rebuildcfg_timelimit';
DELETE FROM `bb_config` WHERE config_name = 'dbmtnc_rebuildcfg_timeoverwrite';
DELETE FROM `bb_config` WHERE config_name = 'default_style';
DELETE FROM `bb_config` WHERE config_name = 'gzip_compress';
DELETE FROM `bb_config` WHERE config_name = 'override_user_style';
DELETE FROM `bb_config` WHERE config_name = 'script_path';
DELETE FROM `bb_config` WHERE config_name = 'server_name';
DELETE FROM `bb_config` WHERE config_name = 'server_port';
DELETE FROM `bb_config` WHERE config_name = 'session_length';
DELETE FROM `bb_config` WHERE config_name = 'sessions_next_clean';
DELETE FROM `bb_config` WHERE config_name = 'xs_check_switches';
DELETE FROM `bb_config` WHERE config_name = 'xs_def_template';
DELETE FROM `bb_config` WHERE config_name = 'xs_downloads_count';
DELETE FROM `bb_config` WHERE config_name = 'xs_downloads_default';
DELETE FROM `bb_config` WHERE config_name = 'xs_ftp_host';
DELETE FROM `bb_config` WHERE config_name = 'xs_ftp_login';
DELETE FROM `bb_config` WHERE config_name = 'xs_ftp_path';
DELETE FROM `bb_config` WHERE config_name = 'xs_warn_includes';

REPLACE INTO `bb_config` (config_name, config_value) VALUES ('allow_autologin','1');
REPLACE INTO `bb_config` (config_name, config_value) VALUES ('cron_last_check', '0');
REPLACE INTO `bb_config` (config_name, config_value) VALUES ('login_reset_time', '30');
REPLACE INTO `bb_config` (config_name, config_value) VALUES ('max_autologin_time','10');
REPLACE INTO `bb_config` (config_name, config_value) VALUES ('max_login_attempts', '5');

REPLACE INTO `bb_config` (config_name, config_value) VALUES ('bt_search_bool_mode', '1');
REPLACE INTO `bb_config` (config_name, config_value) VALUES ('version', '.0.22');
REPLACE INTO `bb_config` (config_name, config_value) VALUES ('xs_version', '8');

# bb_users
UPDATE `bb_users` SET user_from_flag = '' WHERE user_from_flag = 'blank.gif';
UPDATE `bb_users` SET user_lang = '';
UPDATE `bb_users` SET user_dateformat = '';

ALTER TABLE `bb_users` CHANGE user_id user_id MEDIUMINT(8) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bb_users` ADD ignore_srv_load TINYINT(1) default '0' NOT NULL;
ALTER TABLE `bb_users` ADD autologin_id VARCHAR(12) binary default '' NOT NULL;
ALTER TABLE `bb_users` ADD user_newest_pm_id MEDIUMINT(8) default '0' NOT NULL;

ALTER TABLE `bb_users` ADD user_opt INT DEFAULT '0' NOT NULL AFTER user_last_privmsg;
UPDATE `bb_users` SET user_opt =
CONV(
  CONCAT(
    user_allow_passkey,
    user_notify_pm,
    user_notify,
    user_allow_viewonline,
    user_allow_pm,
    user_allowavatar,
    user_attachsig,
    user_viewemail
  )
, 2, 10);

ALTER TABLE `bb_users` DROP user_session_page;
ALTER TABLE `bb_users` DROP user_emailtime;
ALTER TABLE `bb_users` DROP bt_tor_browse_set;
ALTER TABLE `bb_users` DROP user_style;
ALTER TABLE `bb_users` DROP user_allowhtml;
ALTER TABLE `bb_users` DROP user_allowbbcode;
ALTER TABLE `bb_users` DROP user_popup_pm;
ALTER TABLE `bb_users` DROP user_allowsmile;
ALTER TABLE `bb_users` DROP user_viewemail;
ALTER TABLE `bb_users` DROP user_attachsig;

ALTER TABLE `bb_users` DROP INDEX user_session_time;
ALTER TABLE `bb_users` DROP INDEX username;
ALTER TABLE `bb_users`  ADD INDEX username (username(10));
ALTER TABLE `bb_users` DROP INDEX user_email;
ALTER TABLE `bb_users`  ADD INDEX user_email (user_email(10));
ALTER TABLE `bb_users` DROP INDEX user_level;
ALTER TABLE `bb_users`  ADD INDEX user_level (user_level);

ALTER TABLE `bb_users` CHANGE user_password user_password varchar(32) binary NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_dateformat user_dateformat varchar(14) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_active user_active TINYINT(1) NOT NULL default '1';
ALTER TABLE `bb_users` CHANGE user_actkey user_actkey VARCHAR(32) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_aim user_aim VARCHAR(255) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_avatar user_avatar VARCHAR(100) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_email user_email VARCHAR(255) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_from user_from VARCHAR(100) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_from_flag user_from_flag VARCHAR(25) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_icq user_icq VARCHAR(15) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_interests user_interests VARCHAR(255) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_lang user_lang VARCHAR(255) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_level user_level TINYINT(4) NOT NULL default '0';
ALTER TABLE `bb_users` CHANGE user_msnm user_msnm VARCHAR(255) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_newpasswd user_newpasswd VARCHAR(32) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_occ user_occ VARCHAR(100) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_rank user_rank INT(11) NOT NULL default '0';
ALTER TABLE `bb_users` CHANGE user_sig user_sig TEXT NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_sig_bbcode_uid user_sig_bbcode_uid VARCHAR(10) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_website user_website VARCHAR(100) NOT NULL default '';
ALTER TABLE `bb_users` CHANGE user_yim user_yim VARCHAR(255) NOT NULL default '';

# bb_search_results
DROP TABLE IF EXISTS `bb_search_results`;
CREATE TABLE `bb_search_results` (
  `session_id` char(20) binary NOT NULL default '',
  `search_type` tinyint(4) NOT NULL default '0',
  `search_id` varchar(12) binary NOT NULL default '',
  `search_time` int(11) NOT NULL default '0',
  `search_settings` text NOT NULL default '',
  `search_array` text NOT NULL default '',
  PRIMARY KEY  (`session_id`,`search_type`)
) TYPE=MyISAM;

# bb_cron
CREATE TABLE `bb_cron` (
  `cron_id` smallint(5) unsigned NOT NULL auto_increment,
  `cron_active` tinyint(1) NOT NULL default '1',
  `cron_title` char(120) NOT NULL default '',
  `cron_script` char(120) NOT NULL default '',
  `schedule` enum('hourly','daily','weekly','monthly','interval') NOT NULL default 'daily',
  `run_day` enum('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28') default NULL,
  `run_time` time default '04:00:00',
  `run_order` tinyint(4) unsigned NOT NULL,
  `last_run` datetime NOT NULL default '0000-00-00 00:00:00',
  `next_run` datetime NOT NULL default '0000-00-00 00:00:00',
  `run_interval` time default NULL,
  `log_enabled` tinyint(1) NOT NULL default '0',
  `log_file` char(120) NOT NULL default '',
  `log_sql_queries` tinyint(4) NOT NULL default '0',
  `disable_board` tinyint(1) NOT NULL default '0',
  `run_counter` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cron_id`),
  UNIQUE KEY `title` (`cron_title`),
  UNIQUE KEY `script` (`cron_script`)
) TYPE=MyISAM;

#
# Dumping data for table bb_cron
#

REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('0', 'Site backup',                   'site_backup.php',                'daily',   '1',   '05:00:00', 10,  NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('0', 'DB backup',                     'db_backup.php',                  'daily',   '1',   '05:00:00', 20,  NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Avatars cleanup',               'avatars_cleanup.php',            'weekly',   1,    '05:00:00', 30,  NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Board maintenance',             'bb_maintenance.php',             'daily',    NULL, '05:00:00', 40,  NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Prune forums',                  'prune_forums.php',               'daily',    NULL, '05:00:00', 50,  NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Prune topic moved stubs',       'prune_topic_moved.php',          'daily',    NULL, '05:00:00', 60,  NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Logs cleanup',                  'clean_log.php',                  'daily',    NULL, '05:00:00', 70,  NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Tracker maintenance',           'tr_maintenance.php',             'daily',    NULL, '05:00:00', 90,  NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Clean dlstat',                  'clean_dlstat.php',               'daily',    NULL, '05:00:00', 100, NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Prune inactive users',          'prune_inactive_users.php',       'daily',    NULL, '05:00:00', 110, NULL,       1, 1);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Sessions cleanup',              'sessions_cleanup.php',           'interval', NULL, NULL,       255, '00:03:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'DS update \'cat_forums\'',      'ds_update_cat_forums.php',       'interval', NULL, NULL,       255, '00:05:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'DS update \'stats\'',           'ds_update_stats.php',            'interval', NULL, NULL,       255, '00:10:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Flash topic view',              'flash_topic_view.php',           'interval', NULL, NULL,       255, '00:10:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Clean search results',          'clean_search_results.php',       'interval', NULL, NULL,       255, '00:10:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Tracker cleanup and dlstat',    'tr_cleanup_and_dlstat.php',      'interval', NULL, NULL,       10,  '00:15:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Make tracker snapshot',         'tr_make_snapshot.php',           'interval', NULL, NULL,       20,  '00:10:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Seeder last seen',              'tr_update_seeder_last_seen.php', 'interval', NULL, NULL,       255, '01:00:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Clean torrents search options', 'clean_tor_search_options.php',   'interval', NULL, NULL,       255, '06:00:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Tracker dl-complete count',     'tr_complete_count.php',          'interval', NULL, NULL,       255, '06:00:00', 0, 0);
REPLACE INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Cache garbage collector',       'cache_gc.php',                   'interval', NULL, NULL,       255, '00:05:00', 0, 0);

# bb_bt_user_settings
CREATE TABLE `bb_bt_user_settings` (
  `user_id` mediumint(9) NOT NULL default '0',
  `tor_search_set` text NOT NULL default '',
  `last_modified` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;

# bb_bt_torstat (part 1)
# !!! table structure altered later !!!
CREATE TABLE `bb_bt_torstat` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` mediumint(9) NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `t_up_total` bigint(20) unsigned NOT NULL default '0',
  `t_down_total` bigint(20) unsigned NOT NULL default '0',
  `last_modified_torstat` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `completed` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`topic_id`,`user_id`)
) TYPE=MyISAM;

INSERT IGNORE INTO `bb_bt_torstat`
  (topic_id,    user_id,   user_status,   t_up_total,   t_down_total, last_modified_torstat)
SELECT
  u.topic_id, u.user_id, u.user_status, t.t_up_total, t.t_down_total, FROM_UNIXTIME(u.update_time) AS last_modified_torstat
FROM
          `bb_bt_users_dl_status` u
LEFT JOIN `bb_bt_torrents`        tor ON tor.topic_id = u.topic_id
LEFT JOIN `bb_bt_tor_dl_stat`     t   ON t.torrent_id = tor.torrent_id AND t.user_id = u.user_id;

UPDATE `bb_bt_torstat` SET user_status = 4 WHERE user_status = 0;
UPDATE `bb_bt_torstat` SET user_status = 0 WHERE user_status = 1;
UPDATE `bb_bt_torstat` SET user_status = 1 WHERE user_status = 2;
UPDATE `bb_bt_torstat` SET completed   = 1 WHERE user_status = 1;

# bb_bt_dlstatus_main
CREATE TABLE `bb_bt_dlstatus_main` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `last_modified_dlstatus` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) TYPE=MyISAM;

# bb_bt_dlstatus_new
CREATE TABLE `bb_bt_dlstatus_new` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `last_modified_dlstatus` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) TYPE=MyISAM;

# bb_bt_dlstatus_mrg
CREATE TABLE `bb_bt_dlstatus_mrg` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `last_modified_dlstatus` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  KEY `user_topic` (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) TYPE=MRG_MyISAM UNION=(`bb_bt_dlstatus_main`,`bb_bt_dlstatus_new`);

REPLACE INTO `bb_bt_dlstatus_main`
	(user_id, topic_id, user_status, last_modified_dlstatus)
SELECT
	user_id, topic_id, user_status, last_modified_torstat
FROM
	`bb_bt_torstat`;

# bb_bt_torstat (part 2)
DELETE FROM `bb_bt_torstat` WHERE user_status != 1;
ALTER TABLE `bb_bt_torstat` DROP user_status, DROP t_up_total, DROP t_down_total;

# bb_bt_dlstatus_snap
CREATE TABLE `bb_bt_dlstatus_snap` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `dl_status` tinyint(4) NOT NULL default '0',
  `users_count` smallint(5) unsigned NOT NULL default '0',
  KEY `topic_id` (`topic_id`)
) TYPE=MyISAM;

# bb_bt_tracker
DROP TABLE IF EXISTS `bb_bt_tracker`;
CREATE TABLE `bb_bt_tracker` (
  `peer_hash` char(32) binary NOT NULL default '',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_id` mediumint(9) NOT NULL default '0',
  `ip` char(8) binary NOT NULL default '0',
  `port` smallint(5) unsigned NOT NULL default '0',
  `seeder` tinyint(1) NOT NULL default '0',
  `releaser` tinyint(1) NOT NULL default '0',
  `uploaded` bigint(20) unsigned NOT NULL default '0',
  `downloaded` bigint(20) unsigned NOT NULL default '0',
  `remain` bigint(20) unsigned NOT NULL default '0',
  `speed_up` mediumint(8) unsigned NOT NULL default '0',
  `speed_down` mediumint(8) unsigned NOT NULL default '0',
  `up_add` bigint(20) unsigned NOT NULL default '0',
  `down_add` bigint(20) unsigned NOT NULL default '0',
  `update_time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`peer_hash`),
  KEY `topic_id` (`topic_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

# bb_bt_tracker_snap
CREATE TABLE `bb_bt_tracker_snap` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `seeders` mediumint(8) unsigned NOT NULL default '0',
  `leechers` mediumint(8) unsigned NOT NULL default '0',
  `speed_up` int(10) unsigned NOT NULL default '0',
  `speed_down` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topic_id`)
) TYPE=MyISAM;

# bb_bt_torrents
ALTER TABLE `bb_bt_torrents` DROP torrent_id;
ALTER TABLE `bb_bt_torrents` DROP piece_length;
ALTER TABLE `bb_bt_torrents` DROP last_seeder_uid;
ALTER TABLE `bb_bt_torrents` CHANGE info_hash info_hash CHAR(20) binary NOT NULL default '';
ALTER TABLE `bb_bt_torrents` DROP INDEX info_hash;
ALTER TABLE `bb_bt_torrents` ADD PRIMARY KEY (info_hash);
ALTER TABLE `bb_bt_torrents` ADD tor_status TINYINT NOT NULL DEFAULT '0';
ALTER TABLE `bb_bt_torrents` ADD checked_user_id mediumint(8) NOT NULL default '0';
ALTER TABLE `bb_bt_torrents` ADD checked_time int(11) NOT NULL default '0';

# bb_bt_last_torstat
CREATE TABLE `bb_bt_last_torstat` (
  `topic_id`    mediumint(8) unsigned NOT NULL default '0',
  `user_id`     mediumint(9)          NOT NULL default '0',
  `dl_status`   tinyint(1)            NOT NULL default '0',
  `up_add`      bigint(20)   unsigned NOT NULL default '0',
  `down_add`    bigint(20)   unsigned NOT NULL default '0',
  `release_add` bigint(20)   unsigned NOT NULL default '0',
  `bonus_add`   bigint(20)   unsigned NOT NULL default '0',
  `speed_up`    bigint(20)   unsigned NOT NULL default '0',
  `speed_down`  bigint(20)   unsigned NOT NULL default '0',
  PRIMARY KEY  USING BTREE (`topic_id`,`user_id`)
) TYPE=MyISAM;

# bb_bt_last_userstat
CREATE TABLE `bb_bt_last_userstat` (
  `user_id`     mediumint(9)          NOT NULL default '0',
  `up_add`      bigint(20)   unsigned NOT NULL default '0',
  `down_add`    bigint(20)   unsigned NOT NULL default '0',
  `release_add` bigint(20)   unsigned NOT NULL default '0',
  `bonus_add`   bigint(20)   unsigned NOT NULL default '0',
  `speed_up`    bigint(20)   unsigned NOT NULL default '0',
  `speed_down`  bigint(20)   unsigned NOT NULL default '0',
  PRIMARY KEY (`user_id`)
) TYPE=MyISAM;

# bb_bt_torhelp
CREATE TABLE `bb_bt_torhelp` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id_csv` text NOT NULL,
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;

# buf_topic_view
CREATE TABLE `buf_topic_view` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `topic_views` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topic_id`)
) TYPE=MyISAM;

# buf_last_seeder
CREATE TABLE `buf_last_seeder` (
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `seeder_last_seen` int(11) NOT NULL default '0',
  PRIMARY KEY  (`topic_id`)
) TYPE=MyISAM;

# bb_bt_users
DELETE FROM `bb_bt_users` WHERE user_id = -1;
ALTER TABLE `bb_bt_users` CHANGE auth_key auth_key CHAR(10) binary NOT NULL default '';
ALTER TABLE `bb_bt_users` ADD u_up_release BIGINT UNSIGNED default '0' NOT NULL;
ALTER TABLE `bb_bt_users` ADD u_up_bonus BIGINT UNSIGNED default '0' NOT NULL;

# bb_attachments
ALTER TABLE `bb_attachments` DROP `user_id_2`;
ALTER TABLE `bb_attachments` DROP INDEX privmsgs_id;
ALTER TABLE `bb_attachments` DROP INDEX attach_id_privmsgs_id;
ALTER TABLE `bb_attachments` DROP privmsgs_id;
ALTER TABLE `bb_attachments` DROP INDEX attach_id_post_id;
ALTER TABLE `bb_attachments` DROP INDEX post_id;
ALTER TABLE `bb_attachments` ADD INDEX attach_id (attach_id);
ALTER TABLE `bb_attachments` ADD INDEX post_id (post_id);

# bb_attachments_desc
ALTER TABLE `bb_attachments_desc` DROP INDEX physical_filename;
ALTER TABLE `bb_attachments_desc`  ADD INDEX physical_filename (physical_filename(10));
ALTER TABLE `bb_attachments_desc` CHANGE `comment` `comment` VARCHAR(255) NOT NULL default '';
ALTER TABLE `bb_attachments_desc` CHANGE extension extension VARCHAR(100) NOT NULL default '';
ALTER TABLE `bb_attachments_desc` CHANGE mimetype mimetype VARCHAR(100) NOT NULL default '';

# bb_forums
ALTER TABLE `bb_forums` CHANGE forum_name forum_name VARCHAR(150) NOT NULL default '';
ALTER TABLE `bb_forums` CHANGE forum_desc forum_desc TEXT NOT NULL default '';
ALTER TABLE `bb_forums` CHANGE prune_next prune_next INT(11) NOT NULL default '0';
ALTER TABLE `bb_forums` CHANGE cat_id cat_id SMALLINT UNSIGNED NOT NULL default '0';
ALTER TABLE `bb_forums` CHANGE forum_order forum_order SMALLINT UNSIGNED NOT NULL default '1';
ALTER TABLE `bb_forums` CHANGE forum_parent forum_parent SMALLINT UNSIGNED NOT NULL default '0';
ALTER TABLE `bb_forums` CHANGE forum_id forum_id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `bb_forums` ADD topic_tpl_id SMALLINT NOT NULL default '0';
ALTER TABLE `bb_forums` ADD prune_days SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER prune_enable;

UPDATE `bb_forums` SET show_on_index = 1 WHERE forum_parent = 0;
UPDATE `bb_forums` SET forum_display_sort = 0  WHERE forum_display_sort > 2;
UPDATE `bb_forums` f, `bb_forum_prune` pr SET
  f.prune_days = pr.prune_days
WHERE f.forum_id = pr.forum_id
  AND f.prune_enable != 0;

ALTER TABLE `bb_forums` DROP dl_type_default;
ALTER TABLE `bb_forums` DROP show_dl_buttons;
ALTER TABLE `bb_forums` DROP last_dl_topics_synch;
ALTER TABLE `bb_forums` DROP prune_next;
ALTER TABLE `bb_forums` DROP prune_enable;

ALTER TABLE bb_forums DROP INDEX forum_parent;
ALTER TABLE bb_forums  ADD INDEX forum_parent (forum_parent);

# bb_topics
ALTER TABLE `bb_topics` CHANGE topic_title topic_title VARCHAR(250) NOT NULL DEFAULT '';
ALTER TABLE `bb_topics` ADD topic_last_post_time INT default '0' NOT NULL;
ALTER TABLE `bb_topics` DROP topic_dl_status;
ALTER TABLE `bb_topics` DROP INDEX topic_moved_id;
ALTER TABLE `bb_topics` DROP INDEX topic_status;
ALTER TABLE `bb_topics` DROP INDEX topic_type;
ALTER TABLE `bb_topics` DROP INDEX forum_id;
ALTER TABLE `bb_topics`  ADD INDEX forum_id (forum_id);
ALTER TABLE `bb_topics` DROP INDEX topic_last_post_id;
ALTER TABLE `bb_topics`  ADD INDEX topic_last_post_id (topic_last_post_id);
ALTER TABLE `bb_topics` DROP INDEX topic_last_post_time;
ALTER TABLE `bb_topics`  ADD INDEX topic_last_post_time (topic_last_post_time);

UPDATE `bb_topics` t, `bb_posts` p SET
  t.topic_last_post_time = p.post_time
WHERE p.topic_id = t.topic_id
  AND t.topic_moved_id = 0
  AND t.topic_last_post_id = p.post_id;

# bb_posts
ALTER TABLE `bb_posts` CHANGE post_username post_username VARCHAR(25) NOT NULL default '';
ALTER TABLE `bb_posts` CHANGE post_edit_time post_edit_time INT(11) NOT NULL default '0';
ALTER TABLE `bb_posts` CHANGE poster_ip poster_ip CHAR(8) binary NOT NULL default '';
ALTER TABLE `bb_posts` DROP INDEX forum_id;
ALTER TABLE `bb_posts` DROP INDEX forum_id_post_time;
ALTER TABLE `bb_posts` ADD INDEX forum_id_post_time (forum_id, post_time);

ALTER TABLE `bb_posts` DROP enable_html;

# bb_posts_text
ALTER TABLE `bb_posts_text` CHANGE post_subject post_subject ENUM('','kFpILr5') NOT NULL default '';
ALTER TABLE `bb_posts_text` CHANGE post_text post_text TEXT NOT NULL default '';

# bb_posts_html
CREATE TABLE `bb_posts_html` (
  `post_id` mediumint(9) NOT NULL default '0',
  `post_html_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `post_html` mediumtext NOT NULL default '',
  PRIMARY KEY  (`post_id`)
) TYPE=MyISAM;

# bb_categories
ALTER TABLE `bb_categories` CHANGE cat_id cat_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `bb_categories` CHANGE cat_order cat_order SMALLINT UNSIGNED NOT NULL default '0';
ALTER TABLE `bb_categories` CHANGE cat_title cat_title VARCHAR(100) NOT NULL default '';

# -- GROUPS --
# admin AUTH_ACCESS
DELETE aa
FROM `bb_auth_access` aa, `bb_groups` g, `bb_user_group` ug, `bb_users` u
WHERE g.group_id = aa.group_id
  AND ug.group_id = g.group_id
  AND g.group_single_user = 1
  AND u.user_id = ug.user_id
  AND u.user_level = 1;

# orphan FORUM_ID in AUTH_ACCESS
DELETE aa
FROM `bb_auth_access` aa
LEFT JOIN `bb_forums` f USING(forum_id)
WHERE f.forum_id IS NULL;

# GROUP_SINGLE_USER without AUTH_ACCESS
DELETE g
FROM `bb_groups` g
LEFT JOIN `bb_auth_access` aa USING(group_id)
WHERE g.group_single_user = 1
  AND aa.group_id IS NULL;

# orphan USER_GROUP (against GROUP table)
DELETE ug
FROM `bb_user_group` ug
LEFT JOIN `bb_groups` g USING(group_id)
WHERE g.group_id IS NULL;

# orphan USER_GROUP (against USERS table)
DELETE ug
FROM `bb_user_group` ug
LEFT JOIN `bb_users` u USING(user_id)
WHERE u.user_id IS NULL;

# orphan GROUP_SINGLE_USER (against USER_GROUP table)
DELETE g
FROM `bb_groups` g
LEFT JOIN `bb_user_group` ug USING(group_id)
WHERE g.group_single_user = 1
  AND ug.group_id IS NULL;

# orphan AUTH_ACCESS (against GROUP table)
DELETE aa
FROM `bb_auth_access` aa
LEFT JOIN `bb_groups` g USING(group_id)
WHERE g.group_id IS NULL;

# bb_user_group
ALTER TABLE `bb_user_group` CHANGE user_pending user_pending TINYINT(1) NOT NULL default '0';
ALTER TABLE `bb_user_group` DROP INDEX group_id;
ALTER TABLE `bb_user_group` ADD PRIMARY KEY (group_id,user_id);

# bb_auth_access
ALTER TABLE `bb_auth_access` DROP INDEX group_id;
ALTER TABLE `bb_auth_access` ADD PRIMARY KEY (group_id,forum_id);

# bb_topic_templates
CREATE TABLE `bb_topic_templates` (
  `tpl_id` smallint(6) NOT NULL auto_increment,
  `tpl_name` varchar(20) NOT NULL default '',
  `tpl_script` varchar(30) NOT NULL default '',
  `tpl_template` varchar(30) NOT NULL default '',
  `tpl_desc` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`tpl_id`),
  UNIQUE KEY `tpl_name` (`tpl_name`)
) TYPE=MyISAM;

REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('video',        'video',      'video',        'Video (basic)');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('video_home',   'video',      'video_home',   'Video (home)');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('video_simple', 'video',      'video_simple', 'Video (simple)');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('video_lesson', 'video',      'video_lesson', 'Video (lesson)');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('games',        'games',      'games',        'Games');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('games_ps',     'games',      'games_ps',     'Games PS/PS2');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('games_psp',    'games',      'games_psp',    'Games PSP');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('games_xbox',   'games',      'games_xbox',   'Games XBOX');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('progs',        'progs',      'progs',        'Programs');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('progs_mac',    'progs',      'progs_mac',    'Programs Mac OS');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('music',        'music',      'music',        'Music');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('books',        'books',      'books',        'Books');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('audiobooks',   'audiobooks', 'audiobooks',   'Audiobooks');
REPLACE INTO `bb_topic_templates` (tpl_name, tpl_script, tpl_template, tpl_desc) VALUES ('sport',        'sport',      'sport',        'Sport');

# bb_banlist
ALTER TABLE `bb_banlist` CHANGE ban_email ban_email VARCHAR(255) NOT NULL default '';

# bb_extensions
ALTER TABLE `bb_extensions` CHANGE `comment` `comment` VARCHAR(100) NOT NULL default '';

# bb_extension_groups
ALTER TABLE `bb_extension_groups` CHANGE upload_icon upload_icon VARCHAR(100) NOT NULL default '';
ALTER TABLE `bb_extension_groups` CHANGE forum_permissions forum_permissions TEXT NOT NULL default '';

# bb_flags
ALTER TABLE `bb_flags` CHANGE flag_name flag_name VARCHAR(25) NOT NULL default '';
ALTER TABLE `bb_flags` CHANGE flag_image flag_image VARCHAR(25) NOT NULL default '';

# bb_privmsgs
ALTER TABLE `bb_privmsgs` CHANGE privmsgs_ip privmsgs_ip VARCHAR(8) binary NOT NULL default '';
ALTER TABLE `bb_privmsgs` DROP privmsgs_attachment;
ALTER TABLE `bb_privmsgs` DROP privmsgs_enable_html;
ALTER TABLE `bb_privmsgs` DROP privmsgs_attach_sig;

# bb_privmsgs_text
ALTER TABLE `bb_privmsgs_text` CHANGE privmsgs_text privmsgs_text TEXT NOT NULL default '';

# bb_ranks
DELETE FROM `bb_ranks` WHERE rank_special != 1;
ALTER TABLE `bb_ranks` CHANGE rank_special rank_special TINYINT(1) NOT NULL default '1';
ALTER TABLE `bb_ranks` CHANGE rank_image rank_image VARCHAR(255) NOT NULL default '';

# bb_smilies
ALTER TABLE `bb_smilies` CHANGE code code VARCHAR(50) NOT NULL default '';
ALTER TABLE `bb_smilies` CHANGE smile_url smile_url VARCHAR(100) NOT NULL default '';
ALTER TABLE `bb_smilies` CHANGE emoticon emoticon VARCHAR(75) NOT NULL default '';

CREATE TABLE `bb_datastore` (
  `ds_title` varchar(255) NOT NULL default '',
  `ds_data` longtext NOT NULL default '',
  PRIMARY KEY  (`ds_title`)
) TYPE=MyISAM;

ALTER TABLE `bb_auth_access` ADD forum_perm INT NOT NULL DEFAULT '0';

UPDATE `bb_auth_access` SET forum_perm =
CONV(
  CONCAT(
    auth_download,
    auth_attachments,
    auth_pollcreate,
    auth_vote,
    auth_announce,
    auth_sticky,
    auth_delete,
    auth_edit,
    auth_reply,
    auth_post,
    auth_mod,
    auth_read,
    auth_view,
    '0'          # for backward compatibility with ambiguous AUTH_ALL usage
  )
, 2, 10);

ALTER TABLE `bb_auth_access`
  DROP auth_view,
  DROP auth_read,
  DROP auth_post,
  DROP auth_reply,
  DROP auth_edit,
  DROP auth_delete,
  DROP auth_sticky,
  DROP auth_announce,
  DROP auth_vote,
  DROP auth_pollcreate,
  DROP auth_attachments,
  DROP auth_mod,
  DROP auth_download;

CREATE TABLE `bb_auth_access_snap` (
  `user_id` mediumint(9) NOT NULL default '0',
  `forum_id` smallint(6) NOT NULL default '0',
  `forum_perm` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`forum_id`)
) TYPE=MyISAM;

REPLACE INTO `bb_auth_access_snap` (user_id, forum_id, forum_perm)
SELECT ug.user_id, aa.forum_id, BIT_OR(forum_perm)
FROM `bb_user_group` ug, `bb_groups` g, `bb_auth_access` aa
WHERE ug.user_pending = 0
  AND ug.group_id = g.group_id
  AND g.group_id = aa.group_id
GROUP BY ug.user_id, aa.forum_id;

# Fix (possible) missing topic_attachment markers
UPDATE `bb_topics` t, `bb_posts` p SET
  t.topic_attachment = 1
WHERE p.post_attachment = 1
  AND p.topic_id = t.topic_id;

ALTER TABLE `bb_bt_torrents` ADD forum_id SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER topic_id;
UPDATE `bb_bt_torrents` tor, `bb_topics` t SET
  tor.forum_id = t.forum_id
WHERE tor.topic_id = t.topic_id;
ALTER TABLE `bb_bt_torrents` DROP INDEX forum_id;
ALTER TABLE `bb_bt_torrents`  ADD INDEX forum_id (forum_id);
ALTER TABLE `bb_bt_torrents` DROP INDEX poster_id;
ALTER TABLE `bb_bt_torrents`  ADD INDEX poster_id (poster_id);

# Action logs
CREATE TABLE `bb_log` (
  `log_type_id` mediumint(8) unsigned NOT NULL default '0',
  `log_user_id` mediumint(9) NOT NULL default '0',
  `log_username` varchar(25) NOT NULL default '',
  `log_user_ip` varchar(8) binary NOT NULL default '',
  `log_forum_id` smallint(5) unsigned NOT NULL default '0',
  `log_forum_id_new` smallint(5) unsigned NOT NULL default '0',
  `log_topic_id` mediumint(8) unsigned NOT NULL default '0',
  `log_topic_id_new` mediumint(8) unsigned NOT NULL default '0',
  `log_topic_title` varchar(250) NOT NULL default '',
  `log_topic_title_new` varchar(250) NOT NULL default '',
  `log_time` int(11) NOT NULL default '0',
  `log_msg` text NOT NULL default '',
  KEY `log_time` (`log_time`),
  FULLTEXT KEY `log_topic_title` (`log_topic_title`)
) TYPE=MyISAM;

# Post search index
CREATE TABLE `bb_posts_search` (
  `post_id` mediumint(8) unsigned NOT NULL default '0',
  `search_words` text NOT NULL default '',
  PRIMARY KEY  (`post_id`),
  FULLTEXT KEY `search_words` (`search_words`)
) TYPE=MyISAM;

# bb_search_rebuild
CREATE TABLE `bb_search_rebuild` (
  `rebuild_session_id` mediumint(8) unsigned NOT NULL auto_increment,
  `start_post_id` mediumint(8) unsigned NOT NULL default '0',
  `end_post_id` mediumint(8) unsigned NOT NULL default '0',
  `start_time` int(11) NOT NULL default '0',
  `end_time` int(11) NOT NULL default '0',
  `last_cycle_time` int(11) NOT NULL default '0',
  `session_time` int(11) NOT NULL default '0',
  `session_posts` mediumint(8) unsigned NOT NULL default '0',
  `session_cycles` mediumint(8) unsigned NOT NULL default '0',
  `search_size` int(10) unsigned NOT NULL default '0',
  `rebuild_session_status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`rebuild_session_id`)
) TYPE=MyISAM;

# bb_ads
CREATE TABLE `bb_ads` (
  `ad_id` mediumint(8) unsigned NOT NULL auto_increment,
  `ad_block_ids` varchar(255) NOT NULL default '',
  `ad_start_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ad_active_days` smallint(6) NOT NULL default '0',
  `ad_status` tinyint(4) NOT NULL default '1',
  `ad_desc` varchar(255) NOT NULL default '',
  `ad_html` text NOT NULL default '',
  PRIMARY KEY (`ad_id`)
) TYPE=MyISAM;

######################### LAST BLOCK START #########################
# Drop old or unused tables
DROP TABLE IF EXISTS `bb_bt_config`;
DROP TABLE IF EXISTS `bb_bt_search_results`;
DROP TABLE IF EXISTS `bb_bt_tor_dl_stat`;
DROP TABLE IF EXISTS `bb_bt_users_dl_status`;
DROP TABLE IF EXISTS `bb_forbidden_extensions`;
DROP TABLE IF EXISTS `bb_forum_prune`;
DROP TABLE IF EXISTS `bb_search_wordlist`;
DROP TABLE IF EXISTS `bb_search_wordmatch`;
DROP TABLE IF EXISTS `bb_themes_name`;
DROP TABLE IF EXISTS `bb_themes`;
########################## LAST BLOCK END ##########################

