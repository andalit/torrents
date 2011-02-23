<!-- BEGIN attach -->
<div class="clear"></div>
<div class="spacer_8"></div>

<!-- BEGIN denyrow -->
<fieldset class="attach">
<legend>{ATTACHMENT_ICON} Attachment</legend>
	<p class="attach_link denied">{postrow.attach.denyrow.L_DENIED}</p>
</fieldset>

<div class="spacer_12"></div>
<!-- END denyrow -->

<!-- BEGIN cat_stream -->
<div><img src="{SPACER}" alt="" width="1" height="6" /></div>
<table width="95%" border="1" class="attachtable" align="center">
<tr>
	<td width="100%" colspan="2" class="attachheader" align="center"><b><span class="gen">{postrow.attach.cat_stream.DOWNLOAD_NAME}</span></b></td>
</tr>
<tr>
	<td width="15%" class="attachrow"><span class="med">&nbsp;{L_DESCRIPTION}:</span></td>
	<td width="75%" class="attachrow">
	<table width="100%" cellspacing="4">
		<tr>
			<td class="attachrow"><span class="med">{postrow.attach.cat_stream.COMMENT}</span></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td width="15%" class="attachrow"><span class="med">&nbsp;{L_FILESIZE}:</span></td>
	<td width="75%" class="attachrow"><span class="med">&nbsp;{postrow.attach.cat_stream.FILESIZE} {postrow.attach.cat_stream.SIZE_VAR}</td>
</tr>
<tr>
	<td width="15%" class="attachrow"><span class="med">&nbsp;{L_VIEWED}:</span></td>
	<td width="75%" class="attachrow"><span class="med">&nbsp;{postrow.attach.cat_stream.DOWNLOAD_COUNT}</span></td>
</tr>
<tr>
	<td colspan="2" align="center"><br />
	<object id="wmp" classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,0,0" standby="Loading Microsoft Windows Media Player components..." type="application/x-oleobject">
	<param name="FileName" value="{postrow.attach.cat_stream.U_DOWNLOAD_LINK}">
							<param name="ShowControls" value="1">
	<param name="ShowDisplay" value="0">
	<param name="ShowStatusBar" value="1">
	<param name="AutoSize" value="1">
	<param name="AutoStart" value="0">
	<param name="Visible" value="1">
	<param name="AnimationStart" value="0">
	<param name="Loop" value="0">
	<embed type="application/x-mplayer2" pluginspage="http://www.microsoft.com/windows95/downloads/contents/wurecommended/s_wufeatured/mediaplayer/default.asp" src="{postrow.attach.cat_stream.U_DOWNLOAD_LINK}" name=MediaPlayer2 showcontrols=1 showdisplay=0 showstatusbar=1 autosize=1 autostart=0 visible=1 animationatstart=0 loop=0></embed>
	</object> <br /><br />
	</td>
</tr>
</table>
<div><img src="{SPACER}" alt="" width="1" height="6" /></div>
<!-- END cat_stream -->

<!-- BEGIN cat_swf -->
<div><img src="{SPACER}" alt="" width="1" height="6" /></div>
<table width="95%" border="1" class="attachtable" align="center">
<tr>
	<td width="100%" colspan="2" class="attachheader" align="center"><b><span class="gen">{postrow.attach.cat_swf.DOWNLOAD_NAME}</span></b></td>
</tr>
<tr>
	<td width="15%" class="attachrow"><span class="med">&nbsp;{L_DESCRIPTION}:</span></td>
	<td width="75%" class="attachrow">
	<table width="100%" cellspacing="4">
		<tr>
			<td class="attachrow"><span class="med">{postrow.attach.cat_swf.COMMENT}</span></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td width="15%" class="attachrow"><span class="med">&nbsp;{L_FILESIZE}:</span></td>
	<td width="75%" class="attachrow"><span class="med">&nbsp;{postrow.attach.cat_swf.FILESIZE} {postrow.attach.cat_swf.SIZE_VAR}</td>
</tr>
<tr>
	<td width="15%" class="attachrow"><span class="med">&nbsp;{L_VIEWED}:</span></td>
	<td width="75%" class="attachrow"><span class="med">&nbsp;{postrow.attach.cat_swf.DOWNLOAD_COUNT}</span></td>
</tr>
<tr>
	<td colspan="2" align="center"><br />
	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width="{postrow.attach.cat_swf.WIDTH}" height="{postrow.attach.cat_swf.HEIGHT}">
	<param name=movie value="{postrow.attach.cat_swf.U_DOWNLOAD_LINK}">
	<param name=loop value=true>
	<param name=quality value=high>
	<param name=scale value=noborder>
	<param name=wmode value=transparent>
	<param name=bgcolor value=#000000>
	<embed src="{postrow.attach.cat_swf.U_DOWNLOAD_LINK}" loop=true quality=high scale=noborder wmode=transparent bgcolor=#000000  width="{postrow.attach.cat_swf.WIDTH}" height="{postrow.attach.cat_swf.HEIGHT}" type="application/x-shockwave-flash" pluginspace="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></embed>
	</object><br /><br />
	</td>
</tr>
</table>
<div><img src="{SPACER}" alt="" width="1" height="6" /></div>
<!-- END cat_swf -->

<!-- BEGIN cat_images -->
<fieldset class="attach">
<legend>{ATTACHMENT_ICON} Attachment ({postrow.attach.cat_images.FILESIZE} {postrow.attach.cat_images.SIZE_VAR})</legend>
	<p class="tCenter pad_6">
		<img src="{postrow.attach.cat_images.IMG_SRC}" id="attachImg" class="postImg" alt="img" border="0" />
	</p>
	<!-- IF postrow.attach.cat_images.COMMENT -->
	<p class="tCenter med lh_110">
		{postrow.attach.cat_images.COMMENT}
	</p>
	<!-- ENDIF -->
</fieldset>

<div class="spacer_12"></div>
<!-- END cat_images -->

<!-- BEGIN cat_thumb_images -->
<fieldset class="attach">
<legend>{ATTACHMENT_ICON} Attachment Thumbnail</legend>
	<p class="attach_link">
		<a href="{postrow.attach.cat_thumb_images.IMG_SRC}" target="_blank"><img src="{postrow.attach.cat_thumb_images.IMG_THUMB_SRC}" alt="{postrow.attach.cat_thumb_images.DOWNLOAD_NAME}" border="0" /></a>
	</p>
	<p class="attach_link">
		<a href="{postrow.attach.cat_thumb_images.IMG_SRC}" target="_blank"><b>{postrow.attach.cat_thumb_images.DOWNLOAD_NAME}</b></a>
		<span class="attach_stats med">({postrow.attach.cat_thumb_images.FILESIZE} {postrow.attach.cat_thumb_images.SIZE_VAR})</span>
	</p>
	<!-- IF postrow.attach.cat_thumb_images.COMMENT -->
	<p class="attach_comment med">
		{postrow.attach.cat_thumb_images.COMMENT}
	</p>
	<!-- ENDIF -->
</fieldset>

<div class="spacer_12"></div>
<!-- END cat_thumb_images -->

<!-- BEGIN attachrow -->
<fieldset class="attach">
<legend>{postrow.attach.attachrow.S_UPLOAD_IMAGE} Attachment</legend>
	<p class="attach_link">
		<a href="{postrow.attach.attachrow.U_DOWNLOAD_LINK}" {postrow.attach.attachrow.TARGET_BLANK}><b>{postrow.attach.attachrow.DOWNLOAD_NAME}</b></a>
		<span class="attach_stats med">({postrow.attach.attachrow.FILESIZE} {postrow.attach.attachrow.SIZE_VAR}, {L_DOWNLOADED}: {postrow.attach.attachrow.DOWNLOAD_COUNT})</span>
	</p>
	<!-- IF postrow.attach.attachrow.COMMENT -->
	<p class="attach_comment med">
		{postrow.attach.attachrow.COMMENT}
	</p>
	<!-- ENDIF -->
</fieldset>

<div class="spacer_12"></div>
<!-- END attachrow -->

<!-- BEGIN tor_not_reged -->
<table class="attach bordered med">
	<tr class="row3">
		<th colspan="3">{postrow.attach.tor_not_reged.DOWNLOAD_NAME}</th>
	</tr>
	<tr class="row1">
		<td width="15%">{L_TRACKER}:</td>
		<td width="70%">{postrow.attach.tor_not_reged.TRACKER_LINK}</td>
		<td width="15%" rowspan="3" class="tCenter pad_6">
			<p>{postrow.attach.tor_not_reged.S_UPLOAD_IMAGE}</p>
			<p>{L_DOWNLOAD}</p>
			<p class="small">{postrow.attach.tor_not_reged.FILESIZE}</p>
		</td>
	</tr>
	<tr class="row1">
		<td>{L_ADDED}:</td>
		<td>{postrow.attach.tor_not_reged.POSTED_TIME}</td>
	</tr>
	<tr class="row1">
		<td>{L_DOWNLOADED}:</td>
		<td>{postrow.attach.tor_not_reged.DOWNLOAD_COUNT} <!-- IF SHOW_DL_LIST_LINK -->&nbsp;[ <a href="{DL_LIST_HREF}" class="med">{L_SHOW_DL_LIST}</a> ] <!-- ENDIF --></td>
	</tr>
	<!-- BEGIN comment -->
	<tr class="row1 tCenter">
		<td colspan="3">{postrow.attach.tor_not_reged.comment.COMMENT}</td>
	</tr>
	<!-- END comment -->
	<tr class="row3 tCenter">
		<td colspan="3">&nbsp;
		<!-- IF TOR_CONTROLS -->
		<form method="POST" action="{TOR_ACTION}">
			<input type="hidden" name="id" value="{postrow.attach.tor_not_reged.ATTACH_ID}" />

			<select name="tor_action" id="tor-select-{postrow.attach.tor_not_reged.ATTACH_ID}" onchange="$('#tor-confirm-{postrow.attach.tor_not_reged.ATTACH_ID}').attr('checked', 0); $('#tor-submit-{postrow.attach.tor_not_reged.ATTACH_ID}').attr('disabled', 1)">
				<option value="" selected="selected" class="select-action">&raquo; {L_SELECT_ACTION}</option>
				<option value="del_torrent">{L_DELETE_TORRENT}</option>
				<option value="del_torrent_move_topic">{L_DEL_MOVE_TORRENT}</option>
			</select>
			<label>
				<input name="confirm" id="tor-confirm-{postrow.attach.tor_not_reged.ATTACH_ID}" type="checkbox" value="1" onclick="if( $('#tor-select-{postrow.attach.tor_not_reged.ATTACH_ID}')[0].selectedIndex != 0 ){ $('#tor-submit-{postrow.attach.tor_not_reged.ATTACH_ID}').attr('disabled', !this.checked); } else { return false; }" />&nbsp;{L_CONFIRM}&nbsp;
			</label>
			<input name="" id="tor-submit-{postrow.attach.tor_not_reged.ATTACH_ID}" type="submit" value="{L_DO_SUBMIT}" class="liteoption" style="width: 110px;" disabled="disabled" />&nbsp;

		</form>
		<!-- ENDIF -->
		&nbsp;</td>
	</tr>
</table>

<div class="spacer_12"></div>
<!-- END tor_not_reged -->

<!-- BEGIN tor_reged -->


<!-- IF TOR_BLOCKED -->
<table id="tor_blocked" class="error">
	<tr><td><p class="error_msg">{TOR_BLOCKED_MSG}</p></td></tr>
</table>

<div class="spacer_12"></div>
<!-- ELSE -->
<!-- IF SHOW_RATIO_WARN -->
<table id="tor_blocked" class="error">
	<tr><td><p class="error_msg">{RATIO_WARN_MSG}</p></td></tr>
</table>

<div class="spacer_12"></div>
<!-- ENDIF -->

<table class="attach bordered med">
	<tr class="row3">
		<th colspan="3" class="{postrow.attach.tor_reged.DL_LINK_CLASS}">{postrow.attach.tor_reged.DOWNLOAD_NAME}<!-- IF postrow.attach.tor_reged.TOR_FROZEN == 0 --><!-- IF MAGNET_LINKS -->&nbsp;{postrow.attach.tor_reged.MAGNET}<!-- ENDIF --><!-- ENDIF --></th>
	</tr>
	<!-- IF postrow.attach.tor_reged.TOR_SILVER_GOLD == 2 -->
	<tr class="row4">
	    <th colspan="3" class="row7"><img src="images/tor_silver.gif" width="16" height="15" title="{L_SILVER}" />&nbsp;{L_SILVER_STATUS}&nbsp;<img src="images/tor_silver.gif" width="16" height="15" title="{L_SILVER}" /></th>
	</tr>    
	<!-- ELSEIF postrow.attach.tor_reged.TOR_SILVER_GOLD == 1 -->
	<tr class="row4">
	    <th colspan="3" class="row7"><img src="images/tor_gold.gif" width="16" height="15" title="{L_GOLD}" />&nbsp;{L_GOLD_STATUS}&nbsp;<img src="images/tor_gold.gif" width="16" height="15" title="{L_GOLD}" /></th>
	</tr>
	<!-- ENDIF -->
	<tr class="row1">
		<td width="15%">{L_TRACKER}:</td>
		<td width="70%">
			{postrow.attach.tor_reged.TRACKER_LINK} &nbsp;
			[ <span title="{postrow.attach.tor_reged.REGED_DELTA}">{postrow.attach.tor_reged.REGED_TIME}</span> ]
		</td>
		<td width="15%" rowspan="4" class="tCenter pad_6">
			<!-- IF postrow.attach.tor_reged.TOR_FROZEN -->
			<p>{postrow.attach.tor_reged.S_UPLOAD_IMAGE}</p><p>{L_DOWNLOAD}</p>
			<!-- ELSE -->
			<a href="{postrow.attach.tor_reged.U_DOWNLOAD_LINK}" class="{postrow.attach.tor_reged.DL_LINK_CLASS}">
			<p>{postrow.attach.tor_reged.S_UPLOAD_IMAGE}</p><p><b>{L_DOWNLOAD}</b></p></a>
			<!-- ENDIF -->
			<p class="small">{postrow.attach.tor_reged.FILESIZE}</p>
		</td>
	</tr>
	<tr class="row1">
		<td>{L_TOR_STATUS}:</td>
		<td><!-- IF postrow.attach.tor_reged.TOR_STATUS == 0 --><b><span style="color: purple;">*</span> {L_TOR_STATUS_NOT_CHECKED}</b><!-- ENDIF -->
			<!-- IF postrow.attach.tor_reged.TOR_STATUS == 1 --><b><span style="color: red;">x</span> {L_TOR_STATUS_CLOSED}</b><!-- ENDIF -->
			<!-- IF postrow.attach.tor_reged.TOR_STATUS == 2 --><b><span style="color: green;">&radic;</span> {L_TOR_STATUS_CHECKED}</b><!-- ENDIF -->
			<!-- IF postrow.attach.tor_reged.TOR_STATUS == 3 --><b><span style="color: blue;">D</span> {L_TOR_STATUS_D}</b><!-- ENDIF -->
			<!-- IF postrow.attach.tor_reged.TOR_STATUS == 4 --><b><span style="color: red;">!</span> {L_TOR_STATUS_NOT_PERFECT}</b><!-- ENDIF -->
			<!-- IF postrow.attach.tor_reged.TOR_STATUS == 5 --><b><span style="color: red;">?</span> {L_TOR_STATUS_PART_PERFECT}</b><!-- ENDIF -->
			<!-- IF postrow.attach.tor_reged.TOR_STATUS == 6 --><b><span style="color: green;">#</span> {L_TOR_STATUS_FISHILY}</b><!-- ENDIF -->
			<!-- IF postrow.attach.tor_reged.TOR_STATUS == 7 --><b><span style="color: red;">&copy;</span> {L_TOR_STATUS_COPY}</b><!-- ENDIF -->
		<span>{postrow.attach.tor_reged.TOR_STATUS_BY}</span>	
		<!-- IF AUTH_MOD -->	</br>
		<form method="POST" action="{TOR_STATUS}">
			<input type="hidden" name="id" value="{postrow.attach.tor_reged.ATTACH_ID}" />

			<select name="tor_status" id="tor-select-{postrow.attach.tor_reged_.ATTACH_ID}" onchange="$('#tor-status_confirm-{postrow.attach.tor_reged_.ATTACH_ID}').attr('checked', 0); $('#tor-submit-{postrow.attach.tor_reged_.ATTACH_ID}').attr('disabled', 1)">
				<option value="" selected="selected" class="select-action">&raquo; {L_TOR_STATUS_SELECT_ACTION}</option>
				<option value="2">{L_TOR_STATUS_CHECKED}</option>
				<option value="gy">{L_TOR_STATUS_NOT_CHECKED}</option>
				<option value="1">{L_TOR_STATUS_CLOSED}</option>
				<option value="3">{L_TOR_STATUS_D}</option>
				<option value="4">{L_TOR_STATUS_NOT_PERFECT}</option>		
				<option value="5">{L_TOR_STATUS_PART_PERFECT}</option>
				<option value="6">{L_TOR_STATUS_FISHILY}</option>
				<option value="7">{L_TOR_STATUS_COPY}</option>				
			</select>
			<label>
				<input name="status_confirm" id="tor-status_confirm-{postrow.attach.tor_reged_.ATTACH_ID}" type="checkbox" value="1" onclick="if( $('#tor-select-{postrow.attach.tor_reged_.ATTACH_ID}')[0].selectedIndex != 0 ){ $('#tor-submit-{postrow.attach.tor_reged_.ATTACH_ID}').attr('disabled', !this.checked); } else { return false; }" />&nbsp;{L_CONFIRM}&nbsp;
			</label>
			<input name="" id="tor-submit-{postrow.attach.tor_reged_.ATTACH_ID}" type="submit" value="{L_DO_SUBMIT}" class="liteoption" style="width: 110px;" disabled="disabled" />&nbsp;

		</form><!-- ENDIF --></td>
	</tr>
	<tr class="row1">
		<td>{L_COMPLETED}:</td>
		<td><span title="{L_DOWNLOADED}: {postrow.attach.tor_reged.DOWNLOAD_COUNT}">{postrow.attach.tor_reged.COMPLETED}</span></td>
	</tr>
	<tr class="row1">
		<td>{L_SIZE}:</td>
		<td>{postrow.attach.tor_reged.TORRENT_SIZE}</td>
	</tr>
	<!-- BEGIN comment -->
	<tr class="row1 tCenter">
		<td colspan="3">{postrow.attach.tor_reged.comment.COMMENT}</td>
	</tr>
	<!-- END comment -->
	<tr class="row3 tCenter">
		<td colspan="3">
		<!-- IF TOR_CONTROLS -->
		<form method="POST" action="{TOR_ACTION}">
			<input type="hidden" name="id" value="{postrow.attach.tor_reged.ATTACH_ID}" />

			<select name="tor_action" id="tor-select-{postrow.attach.tor_reged.ATTACH_ID}" onchange="$('#tor-confirm-{postrow.attach.tor_reged.ATTACH_ID}').attr('checked', 0); $('#tor-submit-{postrow.attach.tor_reged.ATTACH_ID}').attr('disabled', 1)">
				<option value="" selected="selected" class="select-action">&raquo; {L_SELECT_ACTION}</option>
				<option value="del_torrent">{L_DELETE_TORRENT}</option>
				<option value="del_torrent_move_topic">{L_DEL_MOVE_TORRENT}</option>
				<!-- IF AUTH_MOD -->
				<!-- IF postrow.attach.tor_reged.TOR_SILVER_GOLD == 1 -->
				<option value="unset_silver_gold">{L_UNSET_GOLD_TORRENT} / {L_UNSET_SILVER_TORRENT}</option>
				<option value="set_silver">{L_SET_SILVER_TORRENT}</option>
				<!-- ELSEIF postrow.attach.tor_reged.TOR_SILVER_GOLD == 2 -->
				<option value="unset_silver_gold">{L_UNSET_GOLD_TORRENT} / {L_UNSET_SILVER_TORRENT}</option>
				<option value="set_gold">{L_SET_GOLD_TORRENT}</option>
				<!-- ELSE -->
				<option value="set_gold">{L_SET_GOLD_TORRENT}</option>
				<option value="set_silver">{L_SET_SILVER_TORRENT}</option>
				<!-- ENDIF -->
				<!-- ENDIF -->
			</select>
			<label>
				<input name="confirm" id="tor-confirm-{postrow.attach.tor_reged.ATTACH_ID}" type="checkbox" value="1" onclick="if( $('#tor-select-{postrow.attach.tor_reged.ATTACH_ID}')[0].selectedIndex != 0 ){ $('#tor-submit-{postrow.attach.tor_reged.ATTACH_ID}').attr('disabled', !this.checked); } else { return false; }" />&nbsp;{L_CONFIRM}&nbsp;
			</label>
			<input name="" id="tor-submit-{postrow.attach.tor_reged.ATTACH_ID}" type="submit" value="{L_DO_SUBMIT}" class="liteoption" style="width: 110px;" disabled="disabled" />&nbsp;

		</form>
		<!-- ELSEIF TOR_HELP_LINKS -->
		{TOR_HELP_LINKS}
		<!-- ELSE -->
		&nbsp;
		<!-- ENDIF -->
		</td>
	</tr>
</table>

<div class="spacer_12"></div>
<!-- ENDIF -->
<!-- END tor_reged -->

<!-- END attach -->