# buffers
SET SESSION myisam_sort_buffer_size = 16*1024*1024;
SET SESSION bulk_insert_buffer_size =  8*1024*1024;

# bb_config
REPLACE INTO `bb_config` VALUES ('tp_version', '1.0.1.4');
REPLACE INTO `bb_config` VALUES ('tp_release_date', '2009-03-18');

# gold fix
ALTER TABLE `bb_bt_tracker` ADD `tor_type` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `releaser`;

# ipv6 implementation
ALTER TABLE `bb_bt_tracker` ADD `ipv6` CHAR( 32 ) NOT NULL AFTER `ip`;
ALTER TABLE `bb_banlist` CHANGE `ban_ip` `ban_ip` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `bb_log` CHANGE `log_user_ip` `log_user_ip` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `bb_posts` CHANGE `poster_ip` `poster_ip` CHAR( 32 ) NOT NULL;
ALTER TABLE `bb_privmsgs` CHANGE `privmsgs_ip` `privmsgs_ip` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `bb_sessions` CHANGE `session_ip` `session_ip` CHAR( 32 ) NOT NULL;
ALTER TABLE `bb_users` CHANGE `user_last_ip` `user_last_ip` CHAR( 32 ) NOT NULL;
ALTER TABLE `bb_users` CHANGE `user_reg_ip` `user_reg_ip` CHAR( 32 ) NOT NULL;
ALTER TABLE `bb_vote_voters` CHANGE `vote_user_ip` `vote_user_ip` CHAR( 32 ) NOT NULL;