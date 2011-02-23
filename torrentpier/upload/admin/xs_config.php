<?php

/***************************************************************************
 *                               xs_config.php
 *                               -------------
 *   copyright            : (C) 2003 - 2005 CyberAlien
 *   support              : http://www.phpbbstyles.com
 *
 *   version              : 2.3.1
 *
 *   file revision        : 72
 *   project revision     : 78
 *   last modified        : 05 Dec 2005  13:54:54
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
 
require('./pagestart.php');

// check if mod is installed
if(empty($template->xs_version) || $template->xs_version !== 8)
{
	message_die(GENERAL_ERROR, isset($lang['XS_ERROR_NOT_INSTALLED']) ? $lang['XS_ERROR_NOT_INSTALLED'] : 'eXtreme Styles mod is not installed. You forgot to upload includes/template.php');
}

define('IN_XS', true);
include('xs_include.' . $phpEx);

$template->assign_block_vars('nav_left',array('ITEM' => '&raquo; <a href="' . append_sid('xs_config.'.$phpEx) . '">' . $lang['XS_CONFIGURATION'] . '</a>'));

$lang['XS_CONFIG_UPDATED_EXPLAIN'] = str_replace('{URL}', append_sid('xs_config.'.$phpEx), $lang['XS_CONFIG_UPDATED_EXPLAIN']);
$lang['XS_CONFIG_TITLE'] = str_replace('{VERSION}', $template->xs_versiontxt, $lang['XS_CONFIG_TITLE']);
$lang['XS_CONFIG_WARNING_EXPLAIN'] = str_replace('{URL}', append_sid('xs_chmod.'.$phpEx), $lang['XS_CONFIG_WARNING_EXPLAIN']);
$lang['XS_CONFIG_BACK'] = str_replace('{URL}', append_sid('xs_config.'.$phpEx), $lang['XS_CONFIG_BACK']);

//
// Updating configuration
//
if(isset($HTTP_POST_VARS['submit']) && !defined('DEMO_MODE'))
{
	$vars = array('xs_use_cache', 'xs_auto_compile', 'xs_auto_recompile', 'xs_php', 'xs_add_comments', 'xs_shownav');
	// checking navigation config
	$shownav = 0;
	for($i=0; $i<count($lang['XS_CONFIG_SHOWNAV']); $i++)
	{
		$num = pow(2, $i);
		if($i != XS_SHOWNAV_DOWNLOAD && !empty($HTTP_POST_VARS['shownav_' . $i])) // downloads feature is disabled
		{
			$shownav += $num;
		}
	}
	if($shownav !== $board_config['xs_shownav'])
	{
		$template->assign_block_vars('left_refresh', array(
				'ACTION'	=> append_sid('index.' . $phpEx . '?pane=left')
			));
	}
	$HTTP_POST_VARS['xs_shownav'] = $shownav;
	// checking submitted data
	$update_time = false;
	foreach($vars as $var)
	{
		if (!isset($_POST[$var])) continue;

		$new[$var] = trim($_POST[$var]);
		if(($var == 'xs_auto_recompile') && !$new['xs_auto_compile'])
		{
			$new[$var] = 0;
		}
		if($board_config[$var] !== $new[$var])
		{
			bb_update_config(array($var => $new[$var]));
			$board_config[$var] = $new[$var];
		}
	}
	if($update_time)
	{
		$board_config['xs_template_time'] = time() + 10; // set time 10 seconds in future in case if some tpl file would be compiled right now with current settings
		bb_update_config(array('xs_template_time' => $board_config['xs_template_time']));
	}
	$template->assign_block_vars('switch_updated', array());
	$template->load_config($template->root, false);
}

$template->assign_vars(array(
	'XS_USE_CACHE_0'			=> $board_config['xs_use_cache'] ? '' : ' checked="checked"',
	'XS_USE_CACHE_1'			=> $board_config['xs_use_cache'] ? ' checked="checked"' : '',
	'XS_AUTO_COMPILE_0'			=> $board_config['xs_auto_compile'] ? '' : ' checked="checked"',
	'XS_AUTO_COMPILE_1'			=> $board_config['xs_auto_compile'] ? ' checked="checked"' : '',
	'XS_AUTO_RECOMPILE_0'		=> $board_config['xs_auto_recompile'] ? '' : ' checked="checked"',
	'XS_AUTO_RECOMPILE_1'		=> $board_config['xs_auto_recompile'] ? ' checked="checked"' : '',
	'XS_PHP'					=> htmlspecialchars($board_config['xs_php']),
	'XS_ADD_COMMENTS_0'			=> $board_config['xs_add_comments'] ? '' : ' checked="checked"',
	'XS_ADD_COMMENTS_1'			=> $board_config['xs_add_comments'] ? ' checked="checked"' : '',
	'FORM_ACTION'				=> append_sid('xs_config.' . $phpEx),
	));

for($i=0; $i<count($lang['XS_CONFIG_SHOWNAV']); $i++)
{
	$num = pow(2, $i);
	if($i != XS_SHOWNAV_DOWNLOAD) // downloads feature is disabled
	{
		$template->assign_block_vars('shownav', array(
			'NUM'		=> $i,
			'LABEL'		=> $lang['XS_CONFIG_SHOWNAV'][$i],
			'CHECKED'	=> (($board_config['xs_shownav'] & $num) > 0) ? 'checked="checked"' : ''
			));
	}
}

// test cache
$tpl_filename = $template->make_filename('_xs_test.tpl');
$cache_filename = $template->make_filename_cache($tpl_filename);
$str = '';
if(!xs_check_cache($cache_filename))
{
	$template->assign_block_vars('switch_xs_warning', array());
}
@unlink($cache_filename);
$debug_data = $str;
$template->assign_vars(array(
					'XS_DEBUG_HDR1'			=> sprintf($lang['XS_CHECK_HDR'], '_xs_test.tpl'),
					'XS_DEBUG_FILENAME1'	=> $tpl_filename,
					'XS_DEBUG_FILENAME2'	=> $cache_filename,
					'XS_DEBUG_DATA'			=> $debug_data,
					));

$template->set_filenames(array('body' => XS_TPL_PATH . 'config.tpl'));
$template->pparse('body');
xs_exit();

