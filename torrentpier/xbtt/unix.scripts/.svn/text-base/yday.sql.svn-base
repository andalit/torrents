LOCK TABLES bb_bt_users u write;

UPDATE bb_bt_users u
SET
 u_up_yday = u_up_today, u_up_today = 0,
 u_down_yday = u_down_today, u_down_today = 0,
 u_bonus_yday = u_bonus_today, u_bonus_today = 0
WHERE u_up_yday != 0 or u_up_today != 0 OR
 u_down_yday != 0 or u_down_today != 0 OR
 u_bonus_yday != 0 or u_bonus_today != 0;

UNLOCK TABLES;