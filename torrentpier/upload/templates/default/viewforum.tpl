<!-- IF AUTH_MOD -->
	<!-- IF SESSION_ADMIN -->

		<script type="text/javascript">
		<!-- IF MODERATION_ON -->
		$(document).ready(function(){
			show_forum_mod_options();
		});
		<!-- ELSE -->
		$(document).ready(function(){
			$('#show_mod_options a').click( function(){ show_forum_mod_options(); return false; } );
		});
		<!-- ENDIF -->

		function show_forum_mod_options ()
		{
			$('td.topic_id').each(function(){
				var topic_id = $(this).attr('id');
				var input = '<input id="sel-'+ topic_id +'" type="checkbox" value="'+ topic_id +'" class="topic-chbox" />';
				if ($.browser.msie) {
					$(this).html(input);
				} else {
					$(this).after('<td>'+input+'</td>');
					$(this).attr('colSpan', 1);
				}
			});

			$('input.topic-chbox').click(function(){ $('#tr-'+this.value).toggleClass('hl-selected-topic'); });
			$('#pagination a').each(function(){ this.href += '&mod=1'; });
			$('#mod-action-cell').append( $('#mod-action-content')[0] );
			$('#mod-action-row, #mod-action-content').show();
			$('#show_mod_options').html($('#show_mod_options').text());

			$('#mod-action').submit(function(){
				var form = $(this);
				$('input.topic-chbox:checked').each(function(){
					form.append('<input type="hidden" name="topic_id_list[]" value="'+ this.value +'" />');
				});
			});
		}
		</script>

		<div id="mod-action-content" style="display: none;">
		<form id="mod-action" action="modcp.php" method="post">
			<input type="hidden" name="f" value="{FORUM_ID}" />
			<input type="hidden" name="sid" value="{SID}" />

			<select name="mod_action" id="mod-select" onchange="$('#mod-confirm').attr('checked', 0); $('#mod-submit').attr('disabled', 1)">
				<option value="" selected="selected" class="select-action">&raquo; {L_SELECT_ACTION}</option>
				<option value="topic_delete">{L_DELETE}</option>
				<option value="topic_move">{L_MOVE}</option>
				<option value="topic_lock">{L_LOCK}</option>
				<option value="topic_unlock">{L_UNLOCK}</option>
			</select>
			<label for="mod-confirm">
				<input id="mod-confirm" type="checkbox" value="1" onclick="if( $('#mod-select')[0].selectedIndex != 0 ){ $('#mod-submit').attr('disabled', !this.checked); } else { return false; }" />&nbsp;{L_CONFIRM}&nbsp;
			</label>
			<input id="mod-submit" type="submit" value="{L_DO_SUBMIT}" class="liteoption" style="width: 110px;" disabled="disabled" />&nbsp;
		</form>
		</div>

	<!-- ELSE -->
		<script type="text/javascript">
			$(document).ready(function(){
				$('#show_mod_options a').attr('href', '{MOD_REDIRECT_URL}');
			});
		</script>
	<!-- ENDIF / !SESSION_ADMIN -->
<!-- ENDIF / AUTH_MOD -->

<table width="100%">
	<tr>
		<td valign="bottom">
			<h1 class="maintitle"><a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></h1>
			<!-- IF MODERATORS -->
			<p class="small">{L_MODERATORS}: <b>{MODERATORS}</b></p>
			<!-- ENDIF -->
			<!-- IF SHOW_ONLINE_LIST -->
			<p class="small">{LOGGED_IN_USER_LIST}</p>
			<!-- ENDIF -->
		</td>
		<td class="tRight vBottom nowrap small"><b>{PAGINATION}</b></td>
	</tr>
</table>

<table width="100%">
	<tr>
		<td><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" alt="{T_POST_NEW_TOPIC}" /></a></td>
		<td class="nav" width="100%">
			&nbsp;<a href="{U_INDEX}">{T_INDEX}</a>
			<!-- IF HAS_PARENT_FORUM --><em>&raquo;</em>&nbsp;<a href="{PARENT_FORUM_HREF}">{PARENT_FORUM_NAME}</a><!-- ENDIF -->
			<em>&raquo;</em>&nbsp;<a href="{U_VIEW_FORUM}">{FORUM_NAME}</a>
		</td>
	</tr>
</table>

<!-- IF SHOW_SUBFORUMS -->

<table class="forumline forum">
<col class="row1">
<col class="row1" width="60%">
<col class="row2" width="10%">
<col class="row2" width="10%">
<col class="row2" width="20%">
<tr>
	<th colspan="2">{L_FORUM}</th>
	<th>{L_TOPICS}</th>
	<th>{L_POSTS_SHORT}</th>
	<th>{L_LASTPOST}</th>
</tr>
<!-- BEGIN f -->
<tr>
	<td class="f_icon"><img class="forum_icon" src="{f.FORUM_FOLDER_IMG}" /></td>
	<td class="pad_4">{f.TOPIC_TYPE}
		<h4 class="forumlink"><a href="{f.U_VIEWFORUM}">{f.FORUM_NAME}</a></h4>
		<!-- IF f.FORUM_DESC --><p class="forum_desc">{f.FORUM_DESC}</p><!-- ENDIF -->
		<!-- IF f.MODERATORS --><p class="moderators"><em>{L_MODERATORS}:</em> {f.MODERATORS}</p><!-- ENDIF -->
	</td>
	<td class="med tCenter">{f.TOPICS}</td>
	<td class="med tCenter">{f.POSTS}</td>
	<td class="small tCenter" nowrap="nowrap" style="padding: 4px 8px;">
		<!-- BEGIN last -->
			<!-- IF f.last.FORUM_LAST_POST -->

				<!-- IF f.last.SHOW_LAST_TOPIC -->

				<h6 class="last_topic">
					<a title="{f.last.LAST_TOPIC_TIP}" href="{TOPIC_URL}{f.last.LAST_TOPIC_ID}{NEWEST_URL}">{f.last.LAST_TOPIC_TITLE}</a>
					<a href="{POST_URL}{f.last.LAST_POST_ID}#{f.last.LAST_POST_ID}">{ICON_LATEST_REPLY}</a>
				</h6>
				<p class="small" style="margin-top:4px;">
					{f.last.LAST_POST_TIME}
					by
					<!-- IF f.last.LAST_POST_USER_ID -->
					<a href="{PROFILE_URL}{f.last.LAST_POST_USER_ID}">{f.last.LAST_POST_USER_NAME}</a>
					<!-- ELSE -->
					{f.last.LAST_POST_USER_NAME}
					<!-- ENDIF -->
				</p>

				<!-- ELSE / start of !f.last.SHOW_LAST_TOPIC -->

				<p class="small">{f.last.LAST_POST_TIME}</p>
				<p class="small" style="margin-top:3px;">
					<!-- IF f.last.LAST_POST_USER_ID -->
					<a href="{PROFILE_URL}{f.last.LAST_POST_USER_ID}">{f.last.LAST_POST_USER_NAME}</a>
					<!-- ELSE -->
					{f.last.LAST_POST_USER_NAME}
					<!-- ENDIF -->
					<a href="{POST_URL}{f.last.LAST_POST_ID}#{f.last.LAST_POST_ID}">{ICON_LATEST_REPLY}</a>
				</p>

				<!-- ENDIF / !f.last.SHOW_LAST_TOPIC -->
			<!-- ELSE -->
			<span class="med">{L_NO_POSTS}</span>
			<!-- ENDIF -->
		<!-- END last -->
	</td>
</tr>
<!-- END f -->
<tr>
	<td colspan="5" class="spaceRow"><div class="spacer_6"></div></td>
</tr>
</table>
<div class="spacer_6"></div>

<!-- ENDIF / SHOW_SUBFORUMS -->

<table class="forumline">
<tr>
	<td class="cat bw_TRL pad_2">

	<table cellspacing="0" cellpadding="0" class="borderless w100">
	<tr>
		<!-- IF AUTH_MOD -->
		<td class="small bold nowrap" style="padding: 0px 0px 0px 4px;">
			<span id="show_mod_options"><a href="#" class="small bold">{L_MODERATE_FORUM}</a></span>
		</td>
		<td class="med" style="padding: 0px 4px 2px 4px;">|</td>
		<td class="small nowrap" style="padding: 0px 0px 0px 0px;">{L_TOPICS_PER_PAGE}:</td>
		<td class="small nowrap" style="padding: 0px 0px 0px 3px;">
			<form id="tpp" action="{PAGE_URL_TPP}" method="post">{SELECT_TPP}</form>
		</td>
		<!-- ENDIF / AUTH_MOD -->

		<td class="small bold nowrap tRight w100">
			&nbsp;
			<!-- IF LOGGED_IN -->
			<a class="small" href="{U_SEARCH_SELF}">{L_SEARCH_SELF}</a> &nbsp;|&nbsp; 
			<a class="menu-root" href="#only-new-options">{L_DISPLAYING_OPTIONS}</a>
			<!-- ENDIF / LOGGED_IN -->
		</td>

		<td class="nowrap" style="padding: 0px 4px 2px 4px;">
			<form action="{PAGE_URL}" method="post" onsubmit="var txt=$('#search-text').val(); return !(txt=='{L_TITLE_SEARCH_HINT}' || !txt);">
				<input id="search-text" type="text" name="nm"
				<!-- IF TITLE_MATCH -->
					value="{TITLE_MATCH}" <!-- IF FOUND_TOPICS -->class="found"<!-- ELSE -->class="error"<!-- ENDIF -->
				<!-- ELSE -->
					value="{L_TITLE_SEARCH_HINT}" class="hint"
				<!-- ENDIF -->
				style="width: 150px;" />
				<input type="submit" class="bold" value="&raquo;" style="width: 30px;" />
			</form>
		</td>
	</tr>
	</table>

	</td>
</tr>
</table>

<!-- IF TORRENTS -->

<table class="forumline forum" id="forum-table">
<col class="row1">
<col class="row1">
<col class="row1" width="70%">
<col class="row2" width="5%">
<col class="row2" width="5%">
<col class="row2" width="20%">
<tr>
	<th colspan="3">{L_TOPICS}</th>
	<th>{L_TORRENT}</th>
	<th>{L_REPLIES_SHORT}</th>
	<th>{L_LASTPOST}</th>
</tr>
<!-- BEGIN t -->
<!-- IF t.TOPICS_SEPARATOR -->
<tr>
	<td colspan="6" class="row3 topicSep">{t.TOPICS_SEPARATOR}</td>
</tr>
<!-- ENDIF -->
<tr id="tr-{t.TOPIC_ID}">
	<td colspan="2" id="{t.TOPIC_ID}" class="topic_id"><img class="topic_icon" src="{t.TOPIC_ICON}" /></td>

	<td style="padding: 2px 5px 3px 3px;">
	<div class="torTopic">
		<!-- BEGIN tor -->
         <!-- IF t.TOR_STATUS == 0 --><b><span title="{L_TOR_STATUS_NOT_CHECKED}" style="color: purple;">*</span></b><!-- ENDIF -->
         <!-- IF t.TOR_STATUS == 1 --><b><span title="{L_TOR_STATUS_CLOSED}" style="color: red;">x</span></b><!-- ENDIF -->
         <!-- IF t.TOR_STATUS == 2 --><b><span title="{L_TOR_STATUS_CHECKED}" style="color: green;">&radic;</span></b><!-- ENDIF -->
         <!-- IF t.TOR_STATUS == 3 --><b><span title="{L_TOR_STATUS_D}" style="color: blue;">D</span></b><!-- ENDIF -->
         <!-- IF t.TOR_STATUS == 4 --><b><span title="{L_TOR_STATUS_NOT_PERFECT}" style="color: red;">!</span></b><!-- ENDIF -->   
         <!-- IF t.TOR_STATUS == 5 --><b><span title="{L_TOR_STATUS_PART_PERFECT}" style="color: red;">?</span></b><!-- ENDIF -->
         <!-- IF t.TOR_STATUS == 6 --><b><span title="{L_TOR_STATUS_FISHILY}" style="color:green;">#</span></b><!-- ENDIF -->
         <!-- IF t.TOR_STATUS == 7 --><b><span title="{L_TOR_STATUS_COPY}" style="color: red;">&copy;</span></b><!-- ENDIF -->&#0183;
		<!-- END tor -->
		<!-- IF t.IS_UNREAD --><a href="{TOPIC_URL}{t.HREF_TOPIC_ID}{NEWEST_URL}">{ICON_NEWEST_REPLY}</a><!-- ENDIF -->
		<!-- IF t.STATUS == MOVED --><span class="topicMoved">{L_MOVED}</span>
			<!-- ELSEIF t.DL_CLASS --><span class="{t.DL_CLASS} iconDL"><b>{L_DL_TOPIC}</b></span>
		<!-- ENDIF -->
		<!-- IF t.POLL --><span class="topicPoll">{L_POLL}</span><!-- ENDIF -->
		<!-- IF t.TOR_STALED || t.TOR_FROZEN -->
			<!-- IF t.ATTACH --><span>{TOPIC_ATTACH_ICON}</span><!-- ENDIF -->
			<a href="{TOPIC_URL}{t.HREF_TOPIC_ID}" class="gen">{t.TOPIC_TITLE}</a>
		<!-- ELSE -->
			{t.TOR_TYPE}<a href="{TOPIC_URL}{t.HREF_TOPIC_ID}" class="torTopic"><b>{t.TOPIC_TITLE}</b></a>
		<!-- ENDIF -->
	</div>
	<div class="topicAuthor" style="padding-top: 2px;">
		<!-- IF t.TOPIC_AUTHOR_ID --><a href="{PROFILE_URL}{t.TOPIC_AUTHOR_ID}" class="topicAuthor">{t.TOPIC_AUTHOR_NAME}</a>
		<!-- ELSE -->{t.TOPIC_AUTHOR_NAME}<!-- ENDIF -->
		<!-- IF t.PAGINATION --><span class="topicPG">&nbsp;[{ICON_GOTOPOST}{L_GOTO_SHORT} {t.PAGINATION} ]</span><!-- ENDIF -->
	</div>
	</td>

	<td class="tCenter nowrap" style="padding: 2px 4px;">
	<!-- BEGIN tor -->
		<div title="{L_DL_TORRENT}">
			<div><span class="seedmed" title="Seeders"><b>{t.tor.SEEDERS}</b></span><span class="med"> | </span><span class="leechmed" title="Leechers"><b>{t.tor.LEECHERS}</b></span></div>
			<div style="padding-top: 2px" class="small"><!-- IF t.TOR_FROZEN -->{t.tor.TOR_SIZE}<!-- ELSE --><a href="{DOWNLOAD_URL}{t.tor.ATTACH_ID}" class="small" style="text-decoration: none">{t.tor.TOR_SIZE}</a><!-- ENDIF --></div>
		</div>
	<!-- END tor -->
	</td>

	<td class="tCenter small nowrap" style="padding: 3px 4px 2px;">
	<p>
		<span title="{L_REPLIES}">{t.REPLIES}</span>
		<span class="small"> | </span>
		<span title="{L_VIEWS}">{t.VIEWS}</span>
	</p>
	<!-- BEGIN tor -->
	<p style="padding-top: 2px" class="med" title="{L_COMPLETED}">
		<b>{t.tor.COMPL_CNT}</b>
	</p>
	<!-- END tor -->
	</td>

	<td class="tCenter small nowrap" style="padding: 3px 6px 2px;">
		<p>{t.LAST_POST_TIME}</p>
		<p style="padding-top: 2px">
			<!-- IF t.LAST_POSTER_HREF --><a href="{PROFILE_URL}{t.LAST_POSTER_HREF}">{t.LAST_POSTER_NAME}</a><!-- ELSE -->{t.LAST_POSTER_NAME}<!-- ENDIF -->
			<a href="{POST_URL}{t.LAST_POST_ID}#{t.LAST_POST_ID}">{ICON_LATEST_REPLY}</a>
		</p>
	</td>
</tr>
<!-- END t -->
<!-- IF NO_TOPICS -->
<tr>
	<td colspan="6" class="row1 pad_10 tCenter">{NO_TOPICS}</td>
</tr>
<!-- ENDIF / NO_TOPICS -->
<!-- IF SESSION_ADMIN -->
<tr id="mod-action-row" style="display: none;">
	<td colspan="6" id="mod-action-cell" class="row5 med tCenter pad_4"></td>
</tr>
<!-- ENDIF -->
<tr>
	<td colspan="6" class="catBottom med pad_4">
	<!-- IF LOGGED_IN -->
	<form method="post" action="{S_POST_DAYS_ACTION}">
		{L_DISPLAY_TOPICS}: {S_SELECT_TOPIC_DAYS} {S_DISPLAY_ORDER}
		<input type="submit" value="{L_GO}" />
	</form>
	<!-- ELSE -->
	&nbsp;
	<!-- ENDIF -->
	</td>
</tr>
</table>

<!-- ELSE / start of !TORRENTS -->

<table class="forumline forum" id="forum-table">
<col class="row1">
<col class="row1">
<col class="row1" width="60%">
<col class="row2" width="3%">
<col class="row2" width="10%">
<col class="row2" width="7%">
<col class="row2" width="20%">
<tr>
	<th colspan="3">{L_TOPICS}</th>
	<th>{L_REPLIES}</th>
	<th>{L_AUTHOR}</th>
	<th>{L_VIEWS}</th>
	<th>{L_LASTPOST}</th>
</tr>
<!-- BEGIN t -->
<!-- IF t.TOPICS_SEPARATOR -->
<tr>
	<td colspan="7" class="row3 topicSep">{t.TOPICS_SEPARATOR}</td>
</tr>
<!-- ENDIF -->
<tr id="tr-{t.TOPIC_ID}">
	<td colspan="2" id="{t.TOPIC_ID}" class="topic_id"><img class="topic_icon" src="{t.TOPIC_ICON}" /></td>
	<td>
		<span class="topictitle">
			<!-- IF t.IS_UNREAD --><a href="{TOPIC_URL}{t.HREF_TOPIC_ID}{NEWEST_URL}">{ICON_NEWEST_REPLY}</a><!-- ENDIF -->
			<!-- IF t.STATUS == MOVED --><span class="topicMoved">{L_MOVED}</span>
				<!-- ELSEIF t.DL --><span class="">{L_DL_TOPIC}</span>
				<!-- ELSEIF t.ATTACH -->{TOPIC_ATTACH_ICON}
			<!-- ENDIF -->
			<!-- IF t.POLL --><span class="topicPoll">{L_POLL}</span><!-- ENDIF -->
			<a href="{TOPIC_URL}{t.HREF_TOPIC_ID}" class="topictitle">{t.TOPIC_TITLE}</a>
		</span>
		<!-- IF t.PAGINATION --><span class="topicPG">[{ICON_GOTOPOST}{L_GOTO_SHORT} {t.PAGINATION} ]</span><!-- ENDIF -->
	</td>
	<td class="tCenter med">{t.REPLIES}</td>
	<td class="tCenter med"><!-- IF t.TOPIC_AUTHOR_ID --><a href="{PROFILE_URL}{t.TOPIC_AUTHOR_ID}">{t.TOPIC_AUTHOR_NAME}</a><!-- ELSE -->{t.TOPIC_AUTHOR_NAME}<!-- ENDIF --></td>
	<td class="tCenter med">{t.VIEWS}</td>
	<td class="tCenter nowrap small" style="padding: 1px 6px 2px;">
		<p>{t.LAST_POST_TIME}</p>
		<p>
			<!-- IF t.LAST_POSTER_HREF --><a href="{PROFILE_URL}{t.LAST_POSTER_HREF}">{t.LAST_POSTER_NAME}</a><!-- ELSE -->{t.LAST_POSTER_NAME}<!-- ENDIF -->
			<a href="{POST_URL}{t.LAST_POST_ID}#{t.LAST_POST_ID}">{ICON_LATEST_REPLY}</a>
		</p>
	</td>
</tr>
<!-- END t -->
<!-- IF NO_TOPICS -->
<tr>
	<td colspan="7" class="row1 pad_10 tCenter">{NO_TOPICS}</td>
</tr>
<!-- ENDIF / NO_TOPICS -->
<!-- IF SESSION_ADMIN -->
<tr id="mod-action-row" style="display: none;">
	<td colspan="7" id="mod-action-cell" class="row5 med tCenter pad_4"></td>
</tr>
<!-- ENDIF -->
<tr>
	<td colspan="7" class="catBottom med pad_4">
	<!-- IF LOGGED_IN -->
	<form method="post" action="{S_POST_DAYS_ACTION}">
		{L_DISPLAY_TOPICS}: {S_SELECT_TOPIC_DAYS} {S_DISPLAY_ORDER}
		<input type="submit" value="{L_GO}" />
	</form>
	<!-- ELSE -->
	&nbsp;
	<!-- ENDIF -->
	</td>
</tr>
</table>

<!-- ENDIF / !TORRENTS -->

<table width="100%">
	<tr>
		<td><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" alt="{T_POST_NEW_TOPIC}" /></a></td>
		<td class="nav" width="100%">
			&nbsp;<a href="{U_INDEX}">{T_INDEX}</a>
			<!-- IF HAS_PARENT_FORUM --><em>&raquo;</em>&nbsp;<a href="{PARENT_FORUM_HREF}">{PARENT_FORUM_NAME}</a><!-- ENDIF -->
			<em>&raquo;</em>&nbsp;<a href="{U_VIEW_FORUM}">{FORUM_NAME}</a>
		</td>
	</tr>
</table>

<!--bottom_info-->
<div class="bottom_info">

<!-- IF PAGINATION -->
<div class="nav" id="pagination">
	<p style="float: left">{PAGE_NUMBER}</p>
	<p style="float: right">{PAGINATION}</p>
	<div class="clear"></div>
</div>
<!-- ENDIF -->

<div class="jumpbox"></div>

<div id="timezone">
	<p>{LAST_VISIT_DATE}</p>
	<p>{CURRENT_TIME}</p>
	<p>{S_TIMEZONE}</p>
</div>
<div class="clear"></div>

<!-- IF LOGGED_IN -->
<p class="med"><a href="{U_MARK_READ}">{L_MARK_TOPICS_READ}</a></p>
<!-- ENDIF -->

<table width="100%" cellspacing="0">
<tr>
	<td width="40%" class="small"><span>{S_AUTH_LIST}</span></td>
	<td width="60%" valign="top">
		<table class="bRight small">
		<tr>
			<td><img class="topic_icons" src="{FOLDER_NEW_IMG}" /></td>
			<td>{L_NEW_POSTS}</td>
			<td><img class="topic_icons" src="{FOLDER_ANNOUNCE_IMG}" /></td>
			<td>{L_POST_ANNOUNCEMENT}</td>
		</tr>
		<tr>
			<td><img class="topic_icons" src="{FOLDER_IMG}" /></td>
			<td>{L_NO_NEW_POSTS}</td>
			<td><img class="topic_icons" src="{FOLDER_STICKY_IMG}" /></td>
			<td>{L_POST_STICKY}</td>
		</tr>
		<tr>
			<td><img class="topic_icons" src="{FOLDER_LOCKED_IMG}" /></td>
			<td>{L_NO_NEW_POSTS_LOCKED}</td>
			<td><img class="topic_icons" src="{FOLDER_DOWNLOAD_IMG}" /></td>
			<td>{L_POST_DOWNLOAD}</td>
		</tr>
		</table>
	</td>
</tr>
</table>

</div><!--/bottom_info-->

