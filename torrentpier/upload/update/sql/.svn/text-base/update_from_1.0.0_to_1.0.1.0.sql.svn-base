# buffers
SET SESSION myisam_sort_buffer_size = 16*1024*1024;
SET SESSION bulk_insert_buffer_size =  8*1024*1024;

# bt_torrents
ALTER TABLE `bb_bt_torrents` add (
  checked_user_id mediumint(8) NOT NULL default '0',
  checked_time int(11) NOT NULL default '0'
);
ALTER TABLE `bb_bt_torrents` ADD call_seed_time int(11) NOT NULL default '0' AFTER reg_time;

# bb_attachments
ALTER TABLE `bb_attachments` DROP INDEX `attach_id`;
ALTER TABLE `bb_attachments` ADD PRIMARY KEY (`attach_id`);
# bb_config
REPLACE INTO `bb_config` VALUES ('tp_version', '1.0.1.0');
REPLACE INTO `bb_config` VALUES ('tp_release_date', '2008-12-31');
REPLACE INTO `bb_config` VALUES ('tp_release_state', 'svn beta');

# bb_reports
CREATE TABLE `bb_reports` (
  `report_id` mediumint(8) unsigned NOT NULL auto_increment,
  `user_id` mediumint(8) NOT NULL,
  `report_time` int(11) NOT NULL,
  `report_last_change` mediumint(8) unsigned default NULL,
  `report_module_id` mediumint(8) unsigned NOT NULL,
  `report_status` tinyint(1) NOT NULL,
  `report_reason_id` mediumint(8) unsigned NOT NULL,
  `report_subject` int(11) NOT NULL,
  `report_subject_data` mediumtext,
  `report_title` varchar(255) NOT NULL,
  `report_desc` text NOT NULL,
  PRIMARY KEY  (`report_id`),
  KEY `user_id` (`user_id`),
  KEY `report_time` (`report_time`),
  KEY `report_type_id` (`report_module_id`),
  KEY `report_status` (`report_status`),
  KEY `report_reason_id` (`report_reason_id`),
  KEY `report_subject` (`report_subject`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

REPLACE INTO `bb_config` (`config_name`, `config_value`) VALUES
('report_subject_auth', '1'),
('report_modules_cache', '1'),
('report_hack_count', '0'),
('report_notify', '0'),
('report_list_admin', '0'),
('report_new_window', '0');

# bb_reports_changes
CREATE TABLE `bb_reports_changes` (
  `report_change_id` mediumint(8) unsigned NOT NULL auto_increment,
  `report_id` mediumint(8) unsigned NOT NULL,
  `user_id` mediumint(8) NOT NULL,
  `report_change_time` int(11) NOT NULL,
  `report_status` tinyint(1) NOT NULL,
  `report_change_comment` text NOT NULL,
  PRIMARY KEY  (`report_change_id`),
  KEY `report_id` (`report_id`),
  KEY `user_id` (`user_id`),
  KEY `report_change_time` (`report_change_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# bb_reports_modules
CREATE TABLE `bb_reports_modules` (
  `report_module_id` mediumint(8) unsigned NOT NULL auto_increment,
  `report_module_order` mediumint(8) unsigned NOT NULL,
  `report_module_notify` tinyint(1) NOT NULL,
  `report_module_prune` smallint(6) NOT NULL,
  `report_module_last_prune` int(11) default NULL,
  `report_module_name` varchar(50) NOT NULL,
  `auth_write` tinyint(1) NOT NULL,
  `auth_view` tinyint(1) NOT NULL,
  `auth_notify` tinyint(1) NOT NULL,
  `auth_delete` tinyint(1) NOT NULL,
  PRIMARY KEY  (`report_module_id`),
  KEY `report_module_order` (`report_module_order`),
  KEY `auth_view` (`auth_view`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;
INSERT INTO `bb_reports_modules` (`report_module_id`, `report_module_order`, `report_module_notify`, `report_module_prune`, `report_module_last_prune`, `report_module_name`, `auth_write`, `auth_view`, `auth_notify`, `auth_delete`) VALUES
(1, 1, 0, 0, NULL, 'report_general', 0, 1, 1, 1),
(2, 2, 0, 0, NULL, 'report_post', 0, 1, 1, 1),
(3, 3, 0, 0, NULL, 'report_topic', 0, 1, 1, 1),
(4, 4, 0, 0, NULL, 'report_user', 0, 1, 1, 1),
(5, 5, 0, 0, NULL, 'report_privmsg', 0, 1, 1, 1);

# bb_reports_reasons
CREATE TABLE `bb_reports_reasons` (
  `report_reason_id` mediumint(8) unsigned NOT NULL auto_increment,
  `report_module_id` mediumint(8) unsigned NOT NULL,
  `report_reason_order` mediumint(8) unsigned NOT NULL,
  `report_reason_desc` varchar(255) NOT NULL,
  PRIMARY KEY  (`report_reason_id`),
  KEY `report_type_id` (`report_module_id`),
  KEY `report_reason_order` (`report_reason_order`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# new columns
ALTER TABLE `bb_posts`    ADD `post_reported` tinyint(1) NOT NULL default '0';
ALTER TABLE `bb_topics`   ADD `topic_reported` tinyint(1) NOT NULL default '0';
ALTER TABLE `bb_privmsgs` ADD `privmsgs_reported` tinyint(1) NOT NULL default '0';