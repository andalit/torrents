#
# выборка всех членов групп
#
SELECT ug.group_id, ug.user_pending, IF(g.group_single_user, 'Personal', g.group_name) AS group_name, u.username, u.user_id, u.user_level, g.group_type, g.group_moderator

FROM bb_user_group ug
LEFT JOIN bb_groups g USING(group_id)
LEFT JOIN bb_users u ON(ug.user_id = u.user_id)

GROUP BY ug.user_id, ug.group_id

ORDER BY g.group_single_user DESC, g.group_name ASC, u.username ASC
LIMIT 10000

#
# выборка списка групп с указанием принадлежности им данного юзера
#
SELECT g.group_name, g.group_description, g.group_id, g.group_type, IF( ug.user_id IS NOT NULL , IF( ug.user_pending =1, 10, 20 ) , 0 ) AS in_group, g.group_moderator, u.username AS moderator_name, IF( g.group_moderator = ug.user_id, 1, 0 ) AS is_group_mod
FROM bb_groups g
LEFT JOIN bb_user_group ug ON ( ug.group_id = g.group_id AND ug.user_id =28282 )
LEFT JOIN bb_users u ON ( g.group_moderator = u.user_id )
WHERE g.group_single_user =0
ORDER BY is_group_mod DESC , in_group DESC , g.group_type ASC , g.group_name ASC