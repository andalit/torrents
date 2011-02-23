<?php

return;
// ACP Header - START
if (!empty($setmodules))
{
	$module['Users']['Flags'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

if( isset($HTTP_GET_VARS['mode']) || isset($HTTP_POST_VARS['mode']) )
{
	$mode = ($HTTP_GET_VARS['mode']) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];
}
else
{
	//
	// These could be entered via a form button
	//
	if( isset($HTTP_POST_VARS['add']) )
	{
		$mode = "add";
	}
	else if( isset($HTTP_POST_VARS['save']) )
	{
		$mode = "save";
	}
	else
	{
		$mode = "";
	}
}

// if we are are doing a delete make sure we got confirmation
if ( $mode == 'do_delete')
{
	// user bailed out, return to flag admin
	if ( !$HTTP_POST_VARS['confirm'] )
	{
		$mode = '' ;
	}
}


if( $mode != "" )
{
	if( $mode == "edit" || $mode == "add" )
	{
		//
		// They want to add a new flag, show the form.
		//
		$flag_id = ( isset($HTTP_GET_VARS['id']) ) ? intval($HTTP_GET_VARS['id']) : 0;

		$s_hidden_fields = "";

		if( $mode == "edit" )
		{
			if( empty($flag_id) )
			{
				message_die(GENERAL_MESSAGE, $lang['MUST_SELECT_FLAG']);
			}

			$sql = "SELECT * FROM " . FLAG_TABLE . "
				WHERE flag_id = $flag_id";
			if(!$result = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Couldn't obtain flag data", "", __LINE__, __FILE__, $sql);
			}

			$flag_info = $db->sql_fetchrow($result);
			$s_hidden_fields .= '<input type="hidden" name="id" value="' . $flag_id . '" />';

		}

		$s_hidden_fields .= '<input type="hidden" name="mode" value="save" />';

		$template->assign_vars(array(
			'TPL_FLAGS_EDIT' => true,

			"FLAG" => $flag_info['flag_name'],
			"IMAGE" => ( $flag_info['flag_image'] != "" ) ? $flag_info['flag_image'] : "",
			"IMAGE_DISPLAY" => ( $flag_info['flag_image'] != "" ) ? '<img src="../images/flags/' . $flag_info['flag_image'] . '" />' : "",

			"L_FLAGS_TEXT" => $lang['FLAGS_EXPLAIN'],

			"S_FLAG_ACTION" => append_sid("admin_flags.$phpEx"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);

	}
	else if( $mode == "save" )
	{
		//
		// Ok, they sent us our info, let's update it.
		//

		$flag_id = ( isset($HTTP_POST_VARS['id']) ) ? intval($HTTP_POST_VARS['id']) : 0;
		$flag_name = ( isset($HTTP_POST_VARS['title']) ) ? trim($HTTP_POST_VARS['title']) : "";
		$flag_image = ( (isset($HTTP_POST_VARS['flag_image'])) ) ? trim($HTTP_POST_VARS['flag_image']) : "";

		if( $flag_name == "" )
		{
			message_die(GENERAL_MESSAGE, $lang['MUST_SELECT_FLAG']);
		}

		//
		// The flag image has to be a jpg, gif or png
		//
		if($flag_image != "")
		{
			if ( !preg_match("/(\.gif|\.png|\.jpg)$/is", $flag_image))
			{
				$flag_image = "";
			}
		}

		if ($flag_id)
		{
			$sql = "UPDATE " . FLAG_TABLE . "
				SET flag_name = '" . str_replace("\'", "''", $flag_name) . "', flag_image = '" . str_replace("\'", "''", $flag_image) . "'
				WHERE flag_id = $flag_id";

			$message = $lang['FLAG_UPDATED'];
		}
		else
		{
			$sql = "INSERT INTO " . FLAG_TABLE . " (flag_name, flag_image)
				VALUES ('" . str_replace("\'", "''", $flag_name) . "', '" . str_replace("\'", "''", $flag_image) . "')";

			$message = $lang['FLAG_ADDED'];
		}

		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update/insert into flags table", "", __LINE__, __FILE__, $sql);
		}

		$message .= "<br /><br />" . sprintf($lang['CLICK_RETURN_FLAGADMIN'], "<a href=\"" . append_sid("admin_flags.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);

	}
	else if( $mode == 'delete' )
	{
		if( isset($HTTP_POST_VARS['id']) || isset($HTTP_GET_VARS['id']) )
		{
			$flag_id = ( isset($HTTP_POST_VARS['id']) ) ? intval($HTTP_POST_VARS['id']) : intval($HTTP_GET_VARS['id']);
		}
		else
		{
			$flag_id = 0;
		}
		$hidden_fields = '<input type="hidden" name="id" value="' . $flag_id . '" /><input type="hidden" name="mode" value="do_delete" />';

		print_confirmation(array(
			'QUESTION'      => $lang['CONFIRM_DELETE_FLAG'],
			'FORM_ACTION'   => "admin_flags.$phpEx",
			'HIDDEN_FIELDS' => $hidden_fields,
		));
	}
	else if( $mode == 'do_delete' )
	{

		//
		// Ok, they want to delete their flag
		//

		if( isset($HTTP_POST_VARS['id']) || isset($HTTP_GET_VARS['id']) )
		{
			$flag_id = ( isset($HTTP_POST_VARS['id']) ) ? intval($HTTP_POST_VARS['id']) : intval($HTTP_GET_VARS['id']);
		}
		else
		{
			$flag_id = 0;
		}

		if( $flag_id )
		{
			// get the doomed flag's info
			$sql = "SELECT * FROM " . FLAG_TABLE . "
				WHERE flag_id = $flag_id" ;
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't get flag data", "", __LINE__, __FILE__, $sql);
			}
			$row = $db->sql_fetchrow($result);
			$flag_image = $row['flag_image'] ;


			// delete the flag
			$sql = "DELETE FROM " . FLAG_TABLE . "
				WHERE flag_id = $flag_id";

			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't delete flag data", "", __LINE__, __FILE__, $sql);
			}

			// update the users who where using this flag
			$sql = "UPDATE " . USERS_TABLE . "
				SET user_from_flag = ''
				WHERE user_from_flag = '$flag_image'";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, $lang['NO_UPDATE_FLAGS'], "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['FLAG_REMOVED'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_FLAGADMIN'], "<a href=\"" . append_sid("admin_flags.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['MUST_SELECT_FLAG']);
		}
	}
	else
	{
		//
		// They didn't feel like giving us any information. Oh, too bad, we'll just display the
		// list then...
		//
		$sql = "SELECT * FROM " . FLAG_TABLE . "
			ORDER BY flag_name";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain flags data", "", __LINE__, __FILE__, $sql);
		}

		$flag_rows = $db->sql_fetchrowset($result);
		$flag_count = count($flag_rows);

		$template->assign_vars(array(
			'TPL_FLAGS_LIST' => true,

			"L_FLAGS_TEXT" => $lang['FLAGS_EXPLAIN'],
			"L_FLAG" => $lang['FLAG_NAME'],
			"L_ADD_FLAG" => $lang['ADD_NEW_FLAG'],

			"S_FLAGS_ACTION" => append_sid("admin_flags.$phpEx"))
		);

		for( $i = 0; $i < $flag_count; $i++)
		{
			$flag = $flag_rows[$i]['flag_name'];
			$flag_id = $flag_rows[$i]['flag_id'];

			$row_class = !($i % 2) ? 'row1' : 'row2';

			$template->assign_block_vars("flags", array(
				"ROW_CLASS" => $row_class,

				"FLAG" => $flag,
				"IMAGE_DISPLAY" => ( $flag_rows[$i]['flag_image'] != "" ) ? '<img src="../images/flags/' . $flag_rows[$i]['flag_image'] . '" />' : "",

				"U_FLAG_EDIT" => append_sid("admin_flags.$phpEx?mode=edit&amp;id=$flag_id"),
				"U_FLAG_DELETE" => append_sid("admin_flags.$phpEx?mode=delete&amp;id=$flag_id"))
			);
		}
	}
}
else
{
	//
	// Show the default page
	//
	$sql = "SELECT * FROM " . FLAG_TABLE . "
		ORDER BY flag_name ASC";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain flags data", "", __LINE__, __FILE__, $sql);
	}
	$flag_count = $db->sql_numrows($result);

	$flag_rows = $db->sql_fetchrowset($result);

	$template->assign_vars(array(
		'TPL_FLAGS_LIST' => true,

		"L_FLAGS_TEXT" => $lang['FLAGS_EXPLAIN'],
		"L_FLAG" => $lang['FLAG_NAME'],
		"L_ADD_FLAG" => $lang['ADD_NEW_FLAG'],

		"S_FLAGS_ACTION" => append_sid("admin_flags.$phpEx"))
	);

	for($i = 0; $i < $flag_count; $i++)
	{
		$flag = $flag_rows[$i]['flag_name'];
		$flag_id = $flag_rows[$i]['flag_id'];
		$row_class = !($i % 2) ? 'row1' : 'row2';

		$template->assign_block_vars("flags", array(
			"ROW_CLASS" => $row_class,
			"FLAG" => $flag,
			"IMAGE_DISPLAY" => ( $flag_rows[$i]['flag_image'] != "" ) ? '<img src="../images/flags/' . $flag_rows[$i]['flag_image'] . '" />' : "",

			"U_FLAG_EDIT" => append_sid("admin_flags.$phpEx?mode=edit&amp;id=$flag_id"),
			"U_FLAG_DELETE" => append_sid("admin_flags.$phpEx?mode=delete&amp;id=$flag_id"))
		);
	}
}

print_page('admin_flags.tpl', 'admin');
