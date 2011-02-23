
<h1 class="pagetitle">{PAGE_TITLE}</h1>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<form action="{S_PROFILE_ACTION}" {S_FORM_ENCTYPE} method="post">
{S_HIDDEN_FIELDS}

<table class="forumline usercp_register">
<col class="row1">
<col class="row2">
<tbody class="pad_4">
<tr>
	<th colspan="2">{L_REGISTRATION_INFO}</th>
</tr>
<tr>
	<td class="row2 small" colspan="2">{L_ITEMS_REQUIRED}</td>
</tr>
<!-- BEGIN switch_namechange_disallowed -->
<tr>
	<td width="40%">{L_USERNAME}: *</td>
	<td><input type="hidden" name="username" value="{USERNAME}" /><b>{USERNAME}</b></td>
</tr>
<!-- END switch_namechange_disallowed -->
<!-- BEGIN switch_namechange_allowed -->
<tr>
	<td width="40%">{L_USERNAME}: *</td>
	<td><input type="text" name="username" size="35" maxlength="25" value="{USERNAME}" /></td>
</tr>
<!-- END switch_namechange_allowed -->
<tr>
	<td>{L_EMAIL_ADDRESS}: *</td>
	<td><input type="text" name="email" size="35" value="{EMAIL}" <!-- IF EDIT_PROFILE --><!-- IF $bb_cfg['email_change_disabled'] -->readonly="readonly" style="color: gray;"<!-- ENDIF --><!-- ENDIF --> /></td>
</tr>
<!-- BEGIN switch_edit_profile -->
<tr>
	<td>{L_CURRENT_PASSWORD}: * <h6>{L_CONFIRM_PASSWORD_EXPLAIN}</h6></td>
	<td><input type="password" name="cur_password" size="35" maxlength="20" /></td>
</tr>
<!-- END switch_edit_profile -->
<tr>
	<td>{T_NEW_PASSWORD}: * <h6>{L_PASSWORD_IF_CHANGED}</h6></td>
	<td><input type="password" name="new_password" size="35" maxlength="20" /></td>
</tr>
<tr>
	<td>{L_CONFIRM_PASSWORD}: * <h6>{L_PASSWORD_CONFIRM_IF_CHANGED}</h6></td>
	<td><input type="password" name="password_confirm" size="35" maxlength="20" /></td>
</tr>

<!-- IF EDIT_PROFILE -->
<tr>
	<td>{L_AUTOLOGIN}:</td>
	<td><a href="{U_RESET_AUTOLOGIN}">{L_RESET_AUTOLOGIN}</a><h6>{L_RESET_AUTOLOGIN_EXPL}</h6></td>
</tr>
<!-- ENDIF -->

<!-- Visual Confirmation -->
<!-- BEGIN switch_confirm -->
<tr>
	<td colspan="2" class="med tCenter">{L_CONFIRM_CODE_IMPAIRED}<p class="mrg_10">{CONFIRM_IMG}</p></td>
</tr>
<tr>
	<td>{L_CONFIRM_CODE}: * <h6>{L_CONFIRM_CODE_EXPLAIN}</h6></td>
	<td><input type="text" name="cfmcd" size="35" maxlength="6" /></td>
</tr>
<!-- END switch_confirm -->
<!-- BEGIN switch_bittorrent -->
<tr>
	<th colspan="2"><a name="bittorrent"></a>TorrentPier</th>
</tr>
<tr>
	<td>{L_GEN_PASSKEY}<h6>{L_GEN_PASSKEY_EXPLAIN}</h6></td>
	<td class="med">{L_GEN_PASSKEY_EXPLAIN_2}<br />{S_GEN_PASSKEY}</td>
</tr>
<tr>
	<td>{L_CURR_PASSKEY}</td>
	<td class="med">{CURR_PASSKEY}</td>
</tr>
<!-- END switch_bittorrent -->
<tr>
	<th colspan="2">{L_PROFILE_INFO}</th>
</tr>
<tr>
	<td colspan="2" class="row2 small">{L_PROFILE_INFO_NOTICE}</td>
</tr>
<tr>
	<td>{L_ICQ}:</td>
	<td><input type="text" name="icq" size="30" maxlength="15" value="{ICQ}" /></td>
</tr>
<tr>
	<td>{L_AIM}:</td>
	<td><input type="text" name="aim" size="30" value="{AIM}" /></td>
</tr>
<tr>
	<td>{L_MSNM}:</td>
	<td><input type="text" name="msn" size="30" value="{MSN}" /></td>
</tr>
<tr>
	<td>{L_YIM}:</td>
	<td><input type="text" name="yim" size="30" value="{YIM}" /></td>
</tr>
<tr>
	<td>{L_WEBSITE}:</td>
	<td><input type="text" name="website" size="50" value="{WEBSITE}" /></td>
</tr>
<tr>
	<td>{L_LOCATION}:</td>
	<td><input type="text" name="location" size="50" value="{LOCATION}" /></td>
</tr>
<tr>
	<td>{L_FLAG}:</td>
	<td>{FLAG_SELECT}&nbsp;&nbsp;<img src="images/flags/{FLAG_START}" align="absmiddle" name="user_flag" /></td>
</tr>
<tr>
	<td>{L_OCCUPATION}:</td>
	<td><input type="text" name="occupation" size="50" value="{OCCUPATION}" /></td>
</tr>
<tr>
	<td>{L_INTERESTS}:</td>
	<td><input type="text" name="interests" size="50" value="{INTERESTS}" /></td>
</tr>
<tr>
	<td>{L_SIGNATURE}:<h6>{L_SIGNATURE_EXPLAIN_PROFILE}</h6><p class="small">{BBCODE_STATUS}<br />{SMILIES_STATUS}</p></td>
	<td><textarea name="signature" rows="6" cols="60">{SIGNATURE}</textarea></td>
</tr>
<tr>
	<th colspan="2">{L_PREFERENCES}</th>
</tr>
<tr>
	<td>{L_PUBLIC_VIEW_EMAIL}:</td>
	<td>
		<input type="radio" name="viewemail" value="1" {VIEW_EMAIL_YES} /> {L_YES}&nbsp;&nbsp;
		<input type="radio" name="viewemail" value="0" {VIEW_EMAIL_NO} /> {L_NO}
	</td>
</tr>
<tr>
	<td>{L_HIDE_USER}:</td>
	<td>
		<input type="radio" name="hideonline" value="1" {HIDE_USER_YES} /> {L_YES}&nbsp;&nbsp;
		<input type="radio" name="hideonline" value="0" {HIDE_USER_NO} /> {L_NO}
	</td>
</tr>
<tr>
	<td>{L_NOTIFY_ON_REPLY}:<h6>{L_NOTIFY_ON_REPLY_EXPLAIN}</h6></td>
	<td>
		<input type="radio" name="notifyreply" value="1" {NOTIFY_REPLY_YES} />	{L_YES}&nbsp;&nbsp;
		<input type="radio" name="notifyreply" value="0" {NOTIFY_REPLY_NO} />	{L_NO}
	</td>
</tr>
<tr>
	<td>{L_NOTIFY_ON_PRIVMSG}:</td>
	<td>
		<input type="radio" name="notifypm" value="1" {NOTIFY_PM_YES} />	{L_YES}&nbsp;&nbsp;
		<input type="radio" name="notifypm" value="0" {NOTIFY_PM_NO} />	{L_NO}
	</td>
</tr>
<tr>
	<td>{L_ALWAYS_ADD_SIGNATURE}:</td>
	<td>
		<input type="radio" name="attachsig" value="1" {ALWAYS_ADD_SIGNATURE_YES} />	{L_YES}&nbsp;&nbsp;
		<input type="radio" name="attachsig" value="0" {ALWAYS_ADD_SIGNATURE_NO} />	{L_NO}
	</td>
</tr>
<!-- IF LOGGED_IN && $bb_cfg['porno_forums'] -->
<tr>
	<td>{L_HIDE_PORN_FORUMS}:</td>
	<td>
		<input type="radio" name="hide_porn_forums" value="1" {HIDE_PORN_FORUMS_YES} />	{L_YES}&nbsp;&nbsp;
		<input type="radio" name="hide_porn_forums" value="0" {HIDE_PORN_FORUMS_NO} />	{L_NO}
	</td>
</tr>
<!-- ENDIF -->
<!-- IF SHOW_LANG -->
<tr>
	<td>{L_BOARD_LANGUAGE}:</td>
	<td>{LANGUAGE_SELECT}</td>
</tr>
<!-- ENDIF -->
<tr>
	<td>{L_TIMEZONE}:</td>
	<td>{TIMEZONE_SELECT}</td>
</tr>

<!-- IF EDIT_PROFILE -->

<!-- IF SHOW_DATEFORMAT -->
<tr>
	<td>{L_DATE_FORMAT_PROFILE}:<h6>{L_DATE_FORMAT_EXPLAIN}</h6></td>
	<td><input type="text" name="dateformat" value="{DATE_FORMAT}" maxlength="14" /></td>
</tr>
<!-- ENDIF -->
<!-- BEGIN switch_avatar_block -->
<tr>
	<th colspan="2">{L_AVATAR_PANEL}</th>
</tr>
<tr>
	<td colspan="2">
		<table class="borderless bCenter w80 med">
		<tr>
			<td>{L_AVATAR_EXPLAIN_PROFILE}</td>
			<td class="tCenter nowrap">
				<p>{L_CURRENT_IMAGE}</p>
				<p class="mrg_6">{AVATAR}</p>
				<p><label><input type="checkbox" name="avatardel" /> {L_DELETE_AVATAR}</label></p>
			</td>
		</tr>
		</table>
	</td>
</tr>
<!-- BEGIN switch_avatar_local_upload -->
<tr>
	<td>{L_UPLOAD_AVATAR_FILE}:</td>
	<td>
		<input type="file" name="avatar" size="40" />
		<input type="hidden" name="MAX_FILE_SIZE" value="{AVATAR_SIZE}" />
	</td>
</tr>
<!-- END switch_avatar_local_upload -->
<!-- BEGIN switch_avatar_remote_upload -->
<tr>
	<td>{L_UPLOAD_AVATAR_URL}:<h6>{L_UPLOAD_AVATAR_URL_EXPLAIN}</h6></td>
	<td><input type="text" name="avatarurl" size="44" /></td>
</tr>
<!-- END switch_avatar_remote_upload -->
<!-- BEGIN switch_avatar_remote_link -->
<tr>
	<td>{L_LINK_REMOTE_AVATAR}:<h6>{L_LINK_REMOTE_AVATAR_EXPLAIN}</h6></td>
	<td><input type="text" name="avatarremoteurl" size="44" /></td>
</tr>
<!-- END switch_avatar_remote_link -->
<!-- BEGIN switch_avatar_local_gallery -->
<tr>
	<td>{L_AVATAR_GALLERY}:</td>
	<td><input type="submit" name="avatargallery" value="{L_SHOW_GALLERY}" class="lite" /></td>
</tr>
<!-- END switch_avatar_local_gallery -->
<!-- END switch_avatar_block -->
<!-- ENDIF -->

<!-- IF EDIT_PROFILE -->
<!-- ELSE -->
	<!-- IF $bb_cfg['user_agreement_html_path'] -->

	<style type="text/css">
	#submit-buttons { display: none; }
	#infobox-wrap { width: 740px; }
	#infobox-body {
		background: #FFFFFF; color: #000000; padding: 1em;
		height: 300px; overflow: auto; border: 1px inset #000000;
	}
	</style>

	<tr>
		<td class="row2" colspan="2">
		<div id="infobox-wrap" class="bCenter row1">
			<fieldset class="pad_6">
			<legend class="med bold mrg_2 warnColor1">{L_USER_AGREEMENT_HEAD}</legend>
				<div class="bCenter">
					<?php include($bb_cfg['user_agreement_html_path']) ?>
				</div>
				<p class="med bold mrg_4 tCenter"><label><input type="checkbox" value="" onclick="$('#submit-buttons').slideToggle();"> {L_USER_AGREEMENT_AGREE}</label></p>
			</fieldset>
		</div><!--/infobox-wrap-->
		</td>
	</tr>

	<!-- ENDIF / $bb_cfg['user_agreement_html_path'] -->
<!-- ENDIF / !EDIT_PROFILE -->

<tr>
	<td class="catBottom" colspan="2">
	<div id="submit-buttons">
		<input type="submit" name="submit" value="{L_SUBMIT}" class="main" />&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" name="reset" class="lite" />
	</div>
	</td>
</tr>

</tbody>
</table>

</form>