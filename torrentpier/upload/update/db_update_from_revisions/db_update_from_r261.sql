# buffers
SET SESSION myisam_sort_buffer_size = 16*1024*1024;
SET SESSION bulk_insert_buffer_size =  8*1024*1024;

# bb_users
ALTER TABLE `bb_users` CHANGE user_id user_id MEDIUMINT(8) NOT NULL AUTO_INCREMENT;

# bb_attachments_config
DELETE FROM `bb_attachments_config` WHERE config_name = 'attachment_topic_review';
DELETE FROM `bb_attachments_config` WHERE config_name = 'show_apcp';

# bb_config
DELETE FROM `bb_config` WHERE config_name = 'bt_dl_list_expire';
DELETE FROM `bb_config` WHERE config_name = 'sessions_next_clean';
DELETE FROM `bb_config` WHERE config_name = 'xs_def_template';
DELETE FROM `bb_config` WHERE config_name = 'xs_warn_includes';

# bb_cron
ALTER TABLE `bb_cron` ADD run_order TINYINT unsigned NOT NULL default '0' AFTER run_time;
ALTER TABLE `bb_cron` ADD log_sql_queries TINYINT NOT NULL default '0' AFTER log_file;

INSERT INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Sessions cleanup', 'sessions_cleanup.php', 'interval', NULL, NULL, 255, '00:03:00', 0, 0);
INSERT INTO `bb_cron` (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, run_interval, log_enabled, disable_board) VALUES ('1', 'Cache garbage collector',       'cache_gc.php',                 'interval', NULL, NULL,       255, '00:05:00', 0, 0);

# bb_bt_dlstatus_main
DROP TABLE IF EXISTS `bb_bt_dlstatus_main`;
CREATE TABLE `bb_bt_dlstatus_main` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `last_modified_dlstatus` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) TYPE=MyISAM;

INSERT INTO `bb_bt_dlstatus_main`
	(user_id, topic_id, user_status, last_modified_dlstatus)
SELECT
	user_id, topic_id, user_status, last_modified_dlstat
FROM
	`bb_bt_dlstat`;

# bb_bt_dlstatus_new
DROP TABLE IF EXISTS `bb_bt_dlstatus_new`;
CREATE TABLE `bb_bt_dlstatus_new` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `last_modified_dlstatus` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) TYPE=MyISAM;

# bb_bt_dlstatus_mrg
DROP TABLE IF EXISTS `bb_bt_dlstatus_mrg`;
CREATE TABLE `bb_bt_dlstatus_mrg` (
  `user_id` mediumint(9) NOT NULL default '0',
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `user_status` tinyint(1) NOT NULL default '0',
  `last_modified_dlstatus` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  KEY `user_topic` (`user_id`,`topic_id`),
  KEY `topic_id` (`topic_id`)
) TYPE=MRG_MyISAM UNION=(`bb_bt_dlstatus_main`,`bb_bt_dlstatus_new`);

# bb_bt_torstat
DROP TABLE IF EXISTS `bb_bt_torstat`;
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

INSERT INTO `bb_bt_torstat`
	(topic_id, user_id, user_status, t_up_total, t_down_total, last_modified_torstat, completed)
SELECT
	topic_id, user_id, user_status, t_up_total, t_down_total, last_modified_dlstat, completed
FROM
	`bb_bt_dlstat`;

# RENAME
RENAME TABLE `bb_bt_dlstat_snap` TO `bb_bt_dlstatus_snap`;
RENAME TABLE `bb_bt_dlstat` TO `bb_bt_dlstat_OLD_UNUSED`;

# DROP
DROP TABLE `buf_tor_dlstat`;
DROP TABLE `buf_user_dlstat`;

