
<div class="spacer_6"></div>

<div id="pm_header">
	<!-- IF PM_BOX_SIZE_INFO -->
	<table class="bordered pm_box_size pad_2 floatL">
	<tr>
		<td colspan="3" class="row1 med tCenter nowrap">{BOX_SIZE_STATUS}</td>
	</tr>
	<tr>
		<td colspan="3" class="row3">
			<div class="spacer_8 progress" style="width: {INBOX_LIMIT_IMG_WIDTH}px;"></div>
		</td>
	</tr>
	<tr class="row1 small">
		<td width="30%">0%</td>
		<td width="40%" class="tCenter">50%</td>
		<td width="30%" class="tRight">100%</td>
	</tr>
	</table>

	<table class="bordered pm_box_size pad_2 floatR">
	<tr>
		<td colspan="3" class="row1 med tCenter nowrap">{BOX_SIZE_STATUS}</td>
	</tr>
	<tr>
		<td colspan="3" class="row3">
			<div class="spacer_8 progress" style="width: {INBOX_LIMIT_IMG_WIDTH}px;"></div>
		</td>
	</tr>
	<tr class="row1 small">
		<td width="30%">0%</td>
		<td width="40%" class="tCenter">50%</td>
		<td width="30%" class="tRight">100%</td>
	</tr>
	</table>
	<!-- ENDIF / PM_BOX_SIZE_INFO -->

	<table class="pm_nav bCenter">
	<tr>
		<td>{INBOX_IMG}</td><td>{INBOX}</td>
		<td>{SENTBOX_IMG}</td><td>{SENTBOX}</td>
	</tr>
	<tr>
		<td>{OUTBOX_IMG}</td><td>{OUTBOX}</td>
		<td>{SAVEBOX_IMG}</td><td>{SAVEBOX}</td>
	</tr>
	</table>

</div><!--/pm_header-->
<div class="clear"></div>

<!-- IF BOX_EXPL -->
<p class="small">{BOX_EXPL}</p>
<!-- ENDIF -->

<div class="spacer_6"></div>

<form method="post" name="privmsg_list" action="{S_PRIVMSGS_ACTION}">
{S_HIDDEN_FIELDS}

<table width="100%">
<tr>
	<td>{POST_PM_IMG}</td>
	<td width="100%" class="nav">&nbsp;<a href="{U_INDEX}">{T_INDEX}</a></td>
	<td class="tRight nowrap med">
		{L_DISPLAY_MESSAGES}:
		<select name="msgdays">{S_SELECT_MSG_DAYS}</select>
		<input type="submit" value="{L_GO}" name="submit_msgdays" class="liteoption" />
	</td>
</tr>
</table>

<table class="forumline">
<tr>
	<th width="5%">&nbsp;</th>
	<th width="50%">{L_SUBJECT}</th>
	<th width="20%">{L_FROM_OR_TO}</th>
	<th width="20%">{L_DATE}</th>
	<th width="5%">&nbsp;</th>
</tr>
<!-- BEGIN listrow -->
<tr class="{listrow.ROW_CLASS} med tCenter">
	<td><img src="{listrow.PRIVMSG_FOLDER_IMG}" alt="{listrow.L_PRIVMSG_FOLDER_ALT}" title="{listrow.L_PRIVMSG_FOLDER_ALT}" /></td>
	<td class="tLeft"><a href="{listrow.U_READ}" class="med bold">{listrow.SUBJECT}</a></td>
	<td><a href="{listrow.U_FROM_USER_PROFILE}">{listrow.FROM}</a></td>
	<td>{listrow.DATE}</td>
	<td><input type="checkbox" name="mark[]2" value="{listrow.S_MARK_ID}" /></td>
</tr>
<!-- END listrow -->
<!-- BEGIN switch_no_messages -->
<tr>
	<td class="row1 pad_10 tCenter" colspan="5">{L_NO_MESSAGES}</td>
</tr>
<!-- END switch_no_messages -->
<tr>
	<td class="catBottom tRight pad_4" colspan="5">
		<div class="floatL">
			<input type="submit" name="deleteall" value="{L_DELETE_ALL}" class="liteoption" />
		</div>
		<div class="floatR">
			<input type="submit" name="save" value="{L_SAVE_MARKED}" class="mainoption" />&nbsp;&nbsp;
			<input type="submit" name="delete" value="{L_DELETE_MARKED}" class="liteoption" />&nbsp;
		</div>
 </td>
</tr>
</table>

</form>

<p class="small bold tRight">
	<a href="javascript:select_switch(true);" class="small">{L_MARK_ALL}</a>
	::
	<a href="javascript:select_switch(false);" class="small">{L_UNMARK_ALL}</a>
</p>

<!--bottom_info-->
<div class="bottom_info">

	<div class="spacer_6"></div>

	<div class="nav">
		<p style="float: left">{PAGE_NUMBER}</p>
		<p style="float: right">{PAGINATION}</p>
		<div class="clear"></div>
	</div>

	<div class="spacer_4"></div>

	<div id="timezone">
		<p>{LAST_VISIT_DATE}</p>
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->


<script type="text/javascript">
function select_switch(status)
{
	for (i = 0; i < document.privmsg_list.length; i++)
	{
		document.privmsg_list.elements[i].checked = status;
	}
}
</script>

