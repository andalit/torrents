
<!-- IF TPL_FLAGS_EDIT -->
<!--========================================================================-->

<h1>{L_FLAGS_TITLE}</h1>

<p>{L_FLAGS_TEXT}</p>
<br />

<form action="{S_FLAG_ACTION}" method="post">
{S_HIDDEN_FIELDS}
<table class="forumline">
<tr>
		<th colspan="2">{L_FLAGS_TITLE}</th>
	</tr>
	<tr>
		<td class="row1" width="38%"><span class="gen">{L_FLAG_NAME}:</span></td>
		<td class="row2"><input class="post" type="text" name="title" size="35" maxlength="40" value="{FLAG}" /></td>
	</tr>
	<tr>
		<td class="row1" width="38%"><span class="gen">{L_FLAG_IMAGE}:</span><br />
		<span class="small">{L_FLAG_IMAGE_EXPLAIN}</span></td>
		<td class="row2"><input class="post" type="text" name="flag_image" size="40" maxlength="255" value="{IMAGE}" /><br />{IMAGE_DISPLAY}</td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2"><input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" class="liteoption" /></td>
	</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_FLAGS_EDIT -->

<!-- IF TPL_FLAGS_LIST -->
<!--========================================================================-->

<h1>{L_FLAGS_TITLE}</h1>

<p>{L_FLAGS_TEXT}</p>
<br />

<form method="post" action="{S_FLAGS_ACTION}">
<table class="forumline">
<tr>
		<th>{L_FLAG}</th>
		<th>{L_FLAG_PIC}</th>
		<th>{L_EDIT}</th>
		<th>{L_DELETE}</th>
	</tr>
	<!-- BEGIN flags -->
	<tr>
		<td class="{flags.ROW_CLASS}" align="center">{flags.FLAG}</td>
		<td class="{flags.ROW_CLASS}" align="center">{flags.IMAGE_DISPLAY}</td>
		<td class="{flags.ROW_CLASS}" align="center"><a href="{flags.U_FLAG_EDIT}">{L_EDIT}</a></td>
		<td class="{flags.ROW_CLASS}" align="center"><a href="{flags.U_FLAG_DELETE}">{L_DELETE}</a></td>
	</tr>
	<!-- END flags -->
	<tr>
		<td class="catBottom" colspan="6"><input type="submit" class="mainoption" name="add" value="{L_ADD_FLAG}" /></td>
	</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_FLAGS_LIST -->


