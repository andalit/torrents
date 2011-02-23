<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['General']['Smilies'] = basename(__FILE__);
	return;
}

function update_smilies () { $GLOBALS['datastore']->update('smile_replacements'); }
register_shutdown_function('update_smilies');

require('./pagestart.php');
// ACP Header - END

//
// Check to see what mode we should operate in.
//
if( isset($_POST['mode']) || isset($_GET['mode']) )
{
	$mode = ( isset($_POST['mode']) ) ? $_POST['mode'] : $_GET['mode'];
	$mode = htmlspecialchars($mode);
}
else
{
	$mode = "";
}

$delimeter  = '=+:';

//
// Read a listing of uploaded smilies for use in the add or edit smliey code...
//
$dir = @opendir($phpbb_root_path . $board_config['smilies_path']);

while($file = @readdir($dir))
{
	if( !@is_dir(phpbb_realpath($phpbb_root_path . $board_config['smilies_path'] . '/' . $file)) )
	{
		$img_size = @getimagesize($phpbb_root_path . $board_config['smilies_path'] . '/' . $file);

		if( $img_size[0] && $img_size[1] )
		{
			$smiley_images[] = $file;
		}
		else if( preg_match('/.pak$/i', $file) )
		{
			$smiley_paks[] = $file;
		}
	}
}

@closedir($dir);

//
// Select main mode
//
if( isset($_GET['import_pack']) || isset($_POST['import_pack']) )
{
	//
	// Import a list a "Smiley Pack"
	//
	$smile_pak = ( isset($_POST['smile_pak']) ) ? $_POST['smile_pak'] : $_GET['smile_pak'];
	$clear_current = ( isset($_POST['clear_current']) ) ? $_POST['clear_current'] : $_GET['clear_current'];
	$replace_existing = ( isset($_POST['replace']) ) ? $_POST['replace'] : $_GET['replace'];

	if ( !empty($smile_pak) )
	{
		//
		// The user has already selected a smile_pak file.. Import it.
		//
		if( !empty($clear_current)  )
		{
			$sql = "DELETE
				FROM " . SMILIES_TABLE;
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't delete current smilies", "", __LINE__, __FILE__, $sql);
			}
		}
		else
		{
			$sql = "SELECT code
				FROM ". SMILIES_TABLE;
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't get current smilies", "", __LINE__, __FILE__, $sql);
			}

			$cur_smilies = $db->sql_fetchrowset($result);

			for( $i = 0; $i < count($cur_smilies); $i++ )
			{
				$k = $cur_smilies[$i]['code'];
				$smiles[$k] = 1;
			}
		}

		$fcontents = @file($phpbb_root_path . $board_config['smilies_path'] . '/'. $smile_pak);

		if( empty($fcontents) )
		{
			message_die(GENERAL_ERROR, "Couldn't read smiley pak file", "", __LINE__, __FILE__, $sql);
		}

		for( $i = 0; $i < count($fcontents); $i++ )
		{
			$smile_data = explode($delimeter, trim(addslashes($fcontents[$i])));

			for( $j = 2; $j < count($smile_data); $j++)
			{
				//
				// Replace > and < with the proper html_entities for matching.
				//
				$smile_data[$j] = str_replace("<", "&lt;", $smile_data[$j]);
				$smile_data[$j] = str_replace(">", "&gt;", $smile_data[$j]);
				$k = $smile_data[$j];

				if( $smiles[$k] == 1 )
				{
					if( !empty($replace_existing) )
					{
						$sql = "UPDATE " . SMILIES_TABLE . "
							SET smile_url = '" . str_replace("\'", "''", $smile_data[0]) . "', emoticon = '" . str_replace("\'", "''", $smile_data[1]) . "'
							WHERE code = '" . str_replace("\'", "''", $smile_data[$j]) . "'";
					}
					else
					{
						$sql = '';
					}
				}
				else
				{
					$sql = "INSERT INTO " . SMILIES_TABLE . " (code, smile_url, emoticon)
						VALUES('" . str_replace("\'", "''", $smile_data[$j]) . "', '" . str_replace("\'", "''", $smile_data[0]) . "', '" . str_replace("\'", "''", $smile_data[1]) . "')";
				}

				if( $sql != '' )
				{
					$result = $db->sql_query($sql);
					if( !$result )
					{
						message_die(GENERAL_ERROR, "Couldn't update smilies!", "", __LINE__, __FILE__, $sql);
					}
				}
			}
		}

		$message = $lang['SMILEY_IMPORT_SUCCESS'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_SMILEADMIN'], "<a href=\"" . append_sid("admin_smilies.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);

	}
	else
	{
		//
		// Display the script to get the smile_pak cfg file...
		//
		$smile_paks_select = "<select name='smile_pak'><option value=''>" . $lang['SELECT_PAK'] . "</option>";
		while( list($key, $value) = @each($smiley_paks) )
		{
			if ( !empty($value) )
			{
				$smile_paks_select .= "<option>" . $value . "</option>";
			}
		}
		$smile_paks_select .= "</select>";

		$hidden_vars = "<input type='hidden' name='mode' value='import'>";

		$template->assign_vars(array(
			'TPL_SMILE_IMPORT' => true,

			"L_SMILEY_EXPLAIN" => $lang['SMILEY_IMPORT_INST'],
			"L_SELECT_LBL" => $lang['CHOOSE_SMILE_PAK'],
			"L_CONFLICTS" => $lang['SMILE_CONFLICTS'],
			"L_DEL_EXISTING" => $lang['DEL_EXISTING_SMILEYS'],

			"S_SMILEY_ACTION" => append_sid("admin_smilies.php"),
			"S_SMILE_SELECT" => $smile_paks_select,
			"S_HIDDEN_FIELDS" => $hidden_vars)
		);
	}
}
else if( isset($_POST['export_pack']) || isset($_GET['export_pack']) )
{
	//
	// Export our smiley config as a smiley pak...
	//
	if ( $_GET['export_pack'] == "send" )
	{
		$sql = "SELECT *
			FROM " . SMILIES_TABLE;
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Could not get smiley list", "", __LINE__, __FILE__, $sql);
		}

		$resultset = $db->sql_fetchrowset($result);

		$smile_pak = "";
		for($i = 0; $i < count($resultset); $i++ )
		{
			$smile_pak .= $resultset[$i]['smile_url'] . $delimeter;
			$smile_pak .= $resultset[$i]['emoticon'] . $delimeter;
			$smile_pak .= $resultset[$i]['code'] . "\n";
		}

		header("Content-Type: text/x-delimtext; name=\"smiles.pak\"");
		header("Content-disposition: attachment; filename=smiles.pak");

		echo $smile_pak;

		exit;
	}

	$message = sprintf($lang['EXPORT_SMILES'], "<a href=\"" . append_sid("admin_smilies.php?export_pack=send", true) . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_SMILEADMIN'], "<a href=\"" . append_sid("admin_smilies.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

	message_die(GENERAL_MESSAGE, $message);

}
else if( isset($_POST['add']) || isset($_GET['add']) )
{
	//
	// Admin has selected to add a smiley.
	//
	$filename_list = "";
	for( $i = 0; $i < count($smiley_images); $i++ )
	{
		$filename_list .= '<option value="' . $smiley_images[$i] . '">' . $smiley_images[$i] . '</option>';
	}

	$s_hidden_fields = '<input type="hidden" name="mode" value="savenew" />';

	$template->assign_vars(array(
		'TPL_SMILE_EDIT' => true,

		"L_SMILEY_EXPLAIN" => $lang['SMILE_DESC'],
		"L_SMILEY_EMOTION" => $lang['SMILEY_EMOT'],

		"SMILEY_IMG" => $phpbb_root_path . $board_config['smilies_path'] . '/' . $smiley_images[0],

		"S_SMILEY_ACTION" => append_sid("admin_smilies.php"),
		"S_HIDDEN_FIELDS" => $s_hidden_fields,
		"S_FILENAME_OPTIONS" => $filename_list,
		"S_SMILEY_BASEDIR" => $phpbb_root_path . $board_config['smilies_path'])
	);
}
else if ( $mode != "" )
{
	switch( $mode )
	{
		case 'delete':
			//
			// Admin has selected to delete a smiley.
			//

			$smiley_id = ( !empty($_POST['id']) ) ? $_POST['id'] : $_GET['id'];
			$smiley_id = intval($smiley_id);

			$sql = "DELETE FROM " . SMILIES_TABLE . "
				WHERE smilies_id = " . $smiley_id;
			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't delete smiley", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['SMILEY_DEL_SUCCESS'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_SMILEADMIN'], "<a href=\"" . append_sid("admin_smilies.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
			break;

		case 'edit':
			//
			// Admin has selected to edit a smiley.
			//

			$smiley_id = ( !empty($_POST['id']) ) ? $_POST['id'] : $_GET['id'];
			$smiley_id = intval($smiley_id);

			$sql = "SELECT *
				FROM " . SMILIES_TABLE . "
				WHERE smilies_id = " . $smiley_id;
			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, 'Could not obtain emoticon information', "", __LINE__, __FILE__, $sql);
			}
			$smile_data = $db->sql_fetchrow($result);

			$filename_list = "";
			for( $i = 0; $i < count($smiley_images); $i++ )
			{
				if( $smiley_images[$i] == $smile_data['smile_url'] )
				{
					$smiley_selected = "selected=\"selected\"";
					$smiley_edit_img = $smiley_images[$i];
				}
				else
				{
					$smiley_selected = "";
				}

				$filename_list .= '<option value="' . $smiley_images[$i] . '"' . $smiley_selected . '>' . $smiley_images[$i] . '</option>';
			}

			$s_hidden_fields = '<input type="hidden" name="mode" value="save" /><input type="hidden" name="smile_id" value="' . $smile_data['smilies_id'] . '" />';

			$template->assign_vars(array(
				'TPL_SMILE_EDIT' => true,

				"SMILEY_CODE" => $smile_data['code'],
				"SMILEY_EMOTICON" => $smile_data['emoticon'],

				"L_SMILEY_EXPLAIN" => $lang['SMILE_DESC'],
				"L_SMILEY_EMOTION" => $lang['SMILEY_EMOT'],

				"SMILEY_IMG" => $phpbb_root_path . $board_config['smilies_path'] . '/' . $smiley_edit_img,

				"S_SMILEY_ACTION" => append_sid("admin_smilies.php"),
				"S_HIDDEN_FIELDS" => $s_hidden_fields,
				"S_FILENAME_OPTIONS" => $filename_list,
				"S_SMILEY_BASEDIR" => $phpbb_root_path . $board_config['smilies_path'])
			);

			break;

		case "save":
			//
			// Admin has submitted changes while editing a smiley.
			//

			//
			// Get the submitted data, being careful to ensure that we only
			// accept the data we are looking for.
			//
			$smile_code = ( isset($_POST['smile_code']) ) ? trim($_POST['smile_code']) : trim($_GET['smile_code']);
			$smile_url = ( isset($_POST['smile_url']) ) ? trim($_POST['smile_url']) : trim($_GET['smile_url']);
			$smile_url = phpbb_ltrim(basename($smile_url), "'");
			$smile_emotion = ( isset($_POST['smile_emotion']) ) ? trim($_POST['smile_emotion']) : trim($_GET['smile_emotion']);
			$smile_id = ( isset($_POST['smile_id']) ) ? intval($_POST['smile_id']) : intval($_GET['smile_id']);

			// If no code was entered complain ...
			if ($smile_code == '' || $smile_url == '')
			{
				message_die(GENERAL_MESSAGE, $lang['FIELDS_EMPTY']);
			}

			//
			// Convert < and > to proper htmlentities for parsing.
			//
			$smile_code = str_replace('<', '&lt;', $smile_code);
			$smile_code = str_replace('>', '&gt;', $smile_code);

			//
			// Proceed with updating the smiley table.
			//
			$sql = "UPDATE " . SMILIES_TABLE . "
				SET code = '" . str_replace("\'", "''", $smile_code) . "', smile_url = '" . str_replace("\'", "''", $smile_url) . "', emoticon = '" . str_replace("\'", "''", $smile_emotion) . "'
				WHERE smilies_id = $smile_id";
			if( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, "Couldn't update smilies info", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['SMILEY_EDIT_SUCCESS'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_SMILEADMIN'], "<a href=\"" . append_sid("admin_smilies.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
			break;

		case "savenew":
			//
			// Admin has submitted changes while adding a new smiley.
			//

			//
			// Get the submitted data being careful to ensure the the data
			// we recieve and process is only the data we are looking for.
			//
			$smile_code = ( isset($_POST['smile_code']) ) ? $_POST['smile_code'] : $_GET['smile_code'];
			$smile_url = ( isset($_POST['smile_url']) ) ? $_POST['smile_url'] : $_GET['smile_url'];
			$smile_url = phpbb_ltrim(basename($smile_url), "'");
			$smile_emotion = ( isset($_POST['smile_emotion']) ) ? $_POST['smile_emotion'] : $_GET['smile_emotion'];
			$smile_code = trim($smile_code);
			$smile_url = trim($smile_url);
			$smile_emotion = trim($smile_emotion);

			// If no code was entered complain ...
			if ($smile_code == '' || $smile_url == '')
			{
				message_die(GENERAL_MESSAGE, $lang['FIELDS_EMPTY']);
			}

			//
			// Convert < and > to proper htmlentities for parsing.
			//
			$smile_code = str_replace('<', '&lt;', $smile_code);
			$smile_code = str_replace('>', '&gt;', $smile_code);

			//
			// Save the data to the smiley table.
			//
			$sql = "INSERT INTO " . SMILIES_TABLE . " (code, smile_url, emoticon)
				VALUES ('" . str_replace("\'", "''", $smile_code) . "', '" . str_replace("\'", "''", $smile_url) . "', '" . str_replace("\'", "''", $smile_emotion) . "')";
			$result = $db->sql_query($sql);
			if( !$result )
			{
				message_die(GENERAL_ERROR, "Couldn't insert new smiley", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['SMILEY_ADD_SUCCESS'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_SMILEADMIN'], "<a href=\"" . append_sid("admin_smilies.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);
			break;
	}
}
else
{

	//
	// This is the main display of the page before the admin has selected
	// any options.
	//
	$sql = "SELECT *
		FROM " . SMILIES_TABLE;
	$result = $db->sql_query($sql);
	if( !$result )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain smileys from database", "", __LINE__, __FILE__, $sql);
	}

	$smilies = $db->sql_fetchrowset($result);

	$template->assign_vars(array(
		'TPL_SMILE_MAIN' => true,

		"L_SMILEY_TEXT" => $lang['SMILE_DESC'],
		"L_SMILEY_ADD" => $lang['SMILE_ADD'],
		"L_EMOT" => $lang['EMOTION'],
		"L_IMPORT_PACK" => $lang['IMPORT_SMILE_PACK'],
		"L_EXPORT_PACK" => $lang['EXPORT_SMILE_PACK'],

		"S_HIDDEN_FIELDS" => @$s_hidden_fields,
		"S_SMILEY_ACTION" => append_sid("admin_smilies.php"))
	);

	//
	// Loop throuh the rows of smilies setting block vars for the template.
	//
	for($i = 0; $i < count($smilies); $i++)
	{
		//
		// Replace htmlentites for < and > with actual character.
		//
		$smilies[$i]['code'] = str_replace('&lt;', '<', $smilies[$i]['code']);
		$smilies[$i]['code'] = str_replace('&gt;', '>', $smilies[$i]['code']);

		$row_class = !($i % 2) ? 'row1' : 'row2';

		$template->assign_block_vars("smiles", array(
			"ROW_CLASS" => $row_class,

			"SMILEY_IMG" =>  $phpbb_root_path . $board_config['smilies_path'] . '/' . $smilies[$i]['smile_url'],
			"CODE" => $smilies[$i]['code'],
			"EMOT" => $smilies[$i]['emoticon'],

			"U_SMILEY_EDIT" => append_sid("admin_smilies.php?mode=edit&amp;id=" . $smilies[$i]['smilies_id']),
			"U_SMILEY_DELETE" => append_sid("admin_smilies.php?mode=delete&amp;id=" . $smilies[$i]['smilies_id']))
		);
	}
}

print_page('admin_smilies.tpl', 'admin');
