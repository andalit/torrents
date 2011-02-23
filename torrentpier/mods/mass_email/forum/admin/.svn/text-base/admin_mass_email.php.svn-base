<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['General']['Mass_Email'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

@set_time_limit(1200);

$message = '';
$subject = '';

//
// Do the job ...
//
if ( isset($_POST['submit']) )
{
	$subject = stripslashes(trim($_POST['subject']));
	$message = stripslashes(trim($_POST['message']));

	$error = FALSE;
	$error_msg = '';

	if ( empty($subject) )
	{
		$error = true;
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_subject'] : $lang['Empty_subject'];
	}

	if ( empty($message) )
	{
		$error = true;
		$error_msg .= ( !empty($error_msg) ) ? '<br />' . $lang['Empty_message'] : $lang['Empty_message'];
	}

	$group_id = intval($_POST[POST_GROUPS_URL]);

	if ( !$error )
	{
		$subject = $db->escape($subject);
		$message = $db->escape($message);

		$sql = "INSERT INTO ". BULKMAIL_TABLE ." (mail_subject,mail_body,group_id) VALUES ('$subject','$message',$group_id)";

		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not insert message text', '', __LINE__, __FILE__, $sql);
		}

		message_die(GENERAL_MESSAGE, $lang['Email_sent'] . '<br /><br />' . sprintf($lang['Click_return_admin_index'],  '<a href="' . append_sid("index.php?pane=right") . '">', '</a>'));
	}

}

if ( @$error )
{
	$template->assign_vars(array('ERROR_MESSAGE' => $error_msg));
}

//
// Initial selection
//

$sql = "SELECT group_id, group_name FROM ".GROUPS_TABLE . " WHERE group_single_user <> 1";
if ( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not obtain list of groups', '', __LINE__, __FILE__, $sql);
}

$select_list = '<select name = "' . POST_GROUPS_URL . '"><option value = "-1">' . $lang['All_users'] . '</option>';
if ( $row = $db->sql_fetchrow($result) )
{
	do
	{
		$select_list .= '<option value = "' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
	}
	while ( $row = $db->sql_fetchrow($result) );
}
$select_list .= '</select>';

//
// Generate page
//
require(PAGE_HEADER);

$template->assign_vars(array(
	'MESSAGE' => $message,
	'SUBJECT' => $subject,

	'L_EMAIL_EXPLAIN' => $lang['Mass_email_explain'],
	'L_COMPOSE' => $lang['Compose'],
	'L_RECIPIENTS' => $lang['Recipients'],
	'L_EMAIL_SUBJECT' => $lang['Subject'],
	
	'L_NOTICE' => @$notice,

	'S_USER_ACTION' => append_sid('admin_mass_email.php'),
	'S_GROUP_SELECT' => $select_list)
);

$sql = "SELECT bulk_id, bulk_complete, last_user_id, mail_subject FROM bb_bulkmail ORDER BY bulk_id DESC";
if( !($result = $db->sql_query($sql)) )
{
	message_die(GENERAL_ERROR, 'Could not query groups', '', __LINE__, __FILE__, $sql);
}
while ( $row = $db->sql_fetchrow($result) )
{
	$template->assign_block_vars('mailrow', array(
		'ID' => $row['bulk_id'],
		'SUBJECT' => $row['mail_subject'],
		'STATUS' => $row['bulk_complete'] ? 'Завершена' : $row['last_user_id'],
	));
}

print_page('admin_mass_email.tpl', 'admin');
