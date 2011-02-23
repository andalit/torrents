<!-- IF QUIRKS_MODE --><!-- ELSE --><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><!-- ENDIF -->
<html dir="{L_CONTENT_DIRECTION}">

<head>
 <title><!-- IF PAGE_TITLE -->{PAGE_TITLE} :: {SITENAME}<!-- ELSE -->{SITENAME}<!-- ENDIF --></title>
 <meta http-equiv="Content-Type" content="text/html; charset={L_CONTENT_ENCODING}" />
 <meta http-equiv="Content-Style-Type" content="text/css" />
 {META}
 <link rel="stylesheet" href="{STYLESHEET}?v={$bb_cfg['css_ver']}" type="text/css">
 <!-- IF DEBUG --><link rel="stylesheet" href="{DBG_CSS}" type="text/css"><!-- ENDIF -->
 <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
 <link rel="search" type="application/opensearchdescription+xml" href="opensearch_desc.xml" title="{SITENAME} (Forum)" />
 <link rel="search" type="application/opensearchdescription+xml" href="opensearch_desc_bt.xml" title="{SITENAME} (Tracker)" />
 <!-- IF DEBUG -->
 <script type="text/javascript" src="{#BB_ROOT}misc/js/source/jquery.js"></script>
 <script type="text/javascript" src="{#BB_ROOT}misc/js/source/jquery_plugins/dimensions.js"></script>
 <script type="text/javascript" src="{#BB_ROOT}misc/js/source/jquery_plugins/json.js"></script>
 <script type="text/javascript" src="{#BB_ROOT}misc/js/source/jquery_plugins/metadata.js"></script>
 <script type="text/javascript" src="{#BB_ROOT}misc/js/source/jquery_plugins/jquery.media.js"></script>
 <script type="text/javascript" src="{#BB_ROOT}misc/js/firebug/firebug.js"></script>
 <!-- ELSE -->
 <script type="text/javascript" src="{#BB_ROOT}misc/js/jquery.pack.js?v={$bb_cfg['js_ver']}"></script>
 <!-- ENDIF -->
 <script type="text/javascript" src="{#BB_ROOT}misc/js/main.js?v={$bb_cfg['js_ver']}"></script>
 <!-- IF INCLUDE_BBCODE_JS -->
 <script type="text/javascript" src="{#BB_ROOT}misc/js/bbcode.js?v={$bb_cfg['js_ver']}"></script>
 <script type="text/javascript">
  var postImg_MaxWidth = screen.width - {POST_IMG_WIDTH_DECR_JS};
  var postImgAligned_MaxWidth = Math.round(screen.width/3);
  var attachImg_MaxWidth = screen.width - {ATTACH_IMG_WIDTH_DECR_JS};
  var ExternalLinks_InNewWindow = '{EXT_LINK_NEW_WIN}';
  var hidePostImg = false;

  function copyText_writeLink(node)
  {
  	if (!is_ie) return;
  	document.write('<p style="float: right;"><a class="txtb" onclick="if (ie_copyTextToClipboard('+node+')) alert(\'{L_CODE_COPIED}\'); return false;" href="#">{L_CODE_COPY}</a></p>');
  }
  function initPostBBCode(context)
  {
  	initSpoilers(context);
  	initExternalLinks(context);
  	initPostImages(context);	
  }
  function initPostImages(context)
  {
  	var context = context || 'body';
  	if (hidePostImg) return;
  	var $in_spoilers = $('div.sp-body var.postImg', context);
  	$('var.postImg', context).not($in_spoilers).each(function(){
  		var $v = $(this);
  		var src = $v.attr('title');
  		var $img = $('<img src="'+ src +'" class="'+ $v.attr('className') +'" alt="pic" />');
  		$img = fixPostImage($img);
  		var maxW = ($v.hasClass('postImgAligned')) ? postImgAligned_MaxWidth : postImg_MaxWidth;
  		$img.bind('click', function(){ return imgFit(this, maxW); });
  		if (user.opt_js.i_aft_l) {
  			$('#preload').append($img);
  			var loading_icon = '<a href="'+ src +'" target="_blank"><img src="images/pic_loading.gif" alt="" /></a>';
  			$v.html(loading_icon);
  			if ($.browser.msie) {
  				$v.after('<wbr>');
  			}
  			$img.one('load', function(){
  				imgFit(this, maxW);
  				$v.empty().append(this);
  			});
  		}
  		else {
  			$img.one('load', function(){ imgFit(this, maxW) });
  			$v.empty().append($img);
  			if ($.browser.msie) {
  				$v.after('<wbr>');
  			}
  		}
  	});
  }
	function initSpoilers(context)
	{
	$('div.sp-body', context).each(function(){
		var $sp_body = $(this);
		var name = this.title || '{L_SPOILER_HEAD}';
		this.title = '';
		$('<div class="sp-head folded clickable"></div>').text(name).insertBefore($sp_body).click(function(e){
			if (!$sp_body.hasClass('inited')) {
				initPostImages($sp_body);
				$sp_body.prepend('<div class="clear"></div>').append('<div class="clear"></div>').addClass('inited');
				$sp_body.after('<div class="sp-head clickable unfolded"  style="display:none" onclick="spoilerHide($(this));">{L_LOCK}</div>');
			}
			if (e.shiftKey) {
				e.stopPropagation();
				e.shiftKey = false;
				var fold = $(this).hasClass('unfolded');
				$('div.sp-head', $($sp_body.parents('td')[0])).filter( function(){ return $(this).hasClass('unfolded') ? fold : !fold } ).click();
			}
			else {
				$(this).toggleClass('unfolded');
				$sp_body.slideToggle('fast');
				$sp_body.next().slideToggle('fast');
			}
		});
	});
	}
	function spoilerHide($sp_body) 
	{
	    if ($(document).scrollTop() > $sp_body.prev().offset().top) {
	        $(document).scrollTop($sp_body.prev().offset().top - 200);
	    }
	   $sp_body.slideToggle('fast');
	    $sp_body.prev().slideToggle('fast');
	    $sp_body.prev().prev().toggleClass('unfolded');
	}

  function initExternalLinks(context)
  {
  	var context = context || 'body';
  	if (ExternalLinks_InNewWindow) {
  		$("a.postLink:not([href*='"+ window.location.hostname +"/'])", context).attr({ target: '_blank' });
  		//$("a.postLink:not([@href*='"+ window.location.hostname +"/'])", context).replaceWith('<span style="color: red;">Ссылки запрещены</span>');
  	}
  }
  function fixPostImage ($img)
{
	var banned_image_hosts = /imagebanana|hidebehind/i;  // imageshack
	var src = $img[0].src;
	if (src.match(banned_image_hosts)) {
		$img.wrap('<a href="'+ this.src +'" target="_blank"></a>').attr({ src: "{SMILES_URL}/tr_oops.gif", title: "Прочтите правила выкладывания скриншотов!" });
	}
	return $img;
}
  $(document).ready(function(){
  	$('div.post_wrap, div.signature').each(function(){ initPostBBCode( $(this) ) });
  });
 </script>
 <!-- ENDIF / INCLUDE_BBCODE_JS --><script type="text/javascript">
  var BB_ROOT       = "{#BB_ROOT}";
  var cookieDomain  = "{$bb_cfg['cookie_domain']}";
  var cookiePath    = "{$bb_cfg['cookie_path']}";
  var cookieSecure  = {$bb_cfg['cookie_secure']};
  var cookiePrefix  = "{$bb_cfg['cookie_prefix']}";
  var LOGGED_IN     = {LOGGED_IN};
  var InfoWinParams = 'HEIGHT=510,resizable=yes,WIDTH=780';

  var user = {
  	opt_js: {USER_OPTIONS_JS},

  	set: function(opt, val, days, reload) {
  		this.opt_js[opt] = val;
  		setCookie('opt_js', $.toJSON(this.opt_js), days);
  		if (reload) {
  			window.location.reload();
  		}
  	}
  }
  <!-- IF SHOW_JUMPBOX -->
  $(document).ready(function(){
  	$("div.jumpbox").html('\
  		<span id="jumpbox-container"> \
  		<select id="jumpbox"> \
  			<option id="jumpbox-title" value="-1">&nbsp;&raquo;&raquo; {L_JUMPBOX_TITLE} &nbsp;</option> \
  		</select> \
  		</span> \
  		<input id="jumpbox-submit" type="button" class="lite" value="{L_GO}" /> \
  	');
  	$('#jumpbox-container').one('click', function(){
  		$('#jumpbox-title').html('&nbsp;&nbsp; {L_LOADING} ... &nbsp;');
  		var jumpbox_src = '{AJAX_HTML_DIR}' + ({LOGGED_IN} ? 'jumpbox_user.html' : 'jumpbox_guest.html');
  		$(this).load(jumpbox_src);
  		$('#jumpbox-submit').click(function(){ window.location.href='{FORUM_URL}'+$('#jumpbox').val(); });
  	});
  });<!-- ENDIF -->

  var ajax = new Ajax('{AJAX_HANDLER}', 'POST', 'json');

  <!-- IF USE_TABLESORTER -->function getElText (e)
  {
  	var t = '';
  	if (e.textContent !== undefined) { t = e.textContent; } else if (e.innerText !== undefined) { t = e.innerText; } else { t = jQuery(e).text(); }
  	return t;
  }
  function escHTML (txt) {
  	return txt.replace(/</g, '&lt;');
  }

  $(document).ready(function(){
  	$('.tablesorter').tablesorter(); //	{debug: true}
  });<!-- ENDIF -->
 </script>

 <!--[if lt IE 7]><script type="text/javascript">
  $(document).ready(ie6_make_clickable_labels);

  $(document).ready(function(){
  	$('div.menu-sub').prepend('<iframe class="ie-fix-select-overlap"></iframe>'); // iframe for IE select box z-index issue
  	Menu.iframeFix = true;
  });
 </script><![endif]-->

 <!--[if gte IE 7]><style type="text/css">input[type="checkbox"] { margin-bottom: -1px; }</style><![endif]-->
 <!--[if lte IE 6]><style type="text/css">.forumline th { height: 24px; padding: 2px 4px; }</style><![endif]-->
 <!--[if IE]><style type="text/css">.code-copy { display: block; }.post-hr   { margin: 2px auto; }</style><![endif]-->

 <!-- IF INCLUDE_DEVELOP_JS -->
 <script type="text/javascript" src="{#BB_ROOT}misc/js/develop.js"></script>
 <script type="text/javascript">
  function OpenInEditor ($file, $line)
  {
  	$editor_path = '{EDITOR_PATH}';
  	$editor_args = '{EDITOR_ARGS}';

  	$url = BB_ROOT +'develop/open_editor.php';
  	$url += '?prog='+ $editor_path +'&args='+ $editor_args.sprintf($file, $line);

  	window.open($url,'','height=1,width=1,left=1,top=1,resizable=yes,scrollbars=no,toolbar=no');
  }
 </script>
 <!-- ENDIF / INCLUDE_DEVELOP_JS -->
	<style type="text/css">
	.menu-sub, #ajax-loading, #ajax-error, var.ajax-params { display: none; }
	</style>
</head>

<body>
<!-- IF EDITABLE_TPLS -->
<div id="editable-tpl-input" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<input type="text" class="editable-value" />
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;" />
		<input type="button" class="editable-cancel" value="x" style="width: 30px;" />
	</span>
</div>
<div id="editable-tpl-yesno-select" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<select class="editable-value"><option value="1">{L_YES}</option><option value="0">{L_NO}</option></select>
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;" />
		<input type="button" class="editable-cancel" value="x" style="width: 30px;" />
	</span>
</div>
<div id="editable-tpl-yesno-radio" style="display: none;">
	<span class="editable-inputs nowrap" style="display: none;">
		<label><input class="editable-value" type="radio" name="editable-value" value="1" />{L_YES}</label>
		<label><input class="editable-value" type="radio" name="editable-value" value="0" />{L_NO}</label>&nbsp;
		<input type="button" class="editable-submit" value="&raquo;" style="width: 30px; font-weight: bold;" />
		<input type="button" class="editable-cancel" value="x" style="width: 30px;" />
	</span>
</div>
<!-- ENDIF / EDITABLE_TPLS -->

<table id="ajax-loading" cellpadding="0" cellspacing="0"><tr><td class="icon"></td><td>Loading...</td></tr></table>
<table id="ajax-error" cellpadding="0" cellspacing="0"><tr><td>Error</td></tr></table>

<div id="preload" style="position: absolute; overflow: hidden; top: 0; left: 0; height: 1px; width: 1px;"></div>

<div id="body_container">

<!--******************-->
<!-- IF SIMPLE_HEADER -->
<!--==================-->

<style type="text/css">body { background: #E3E3E3; min-width: 10px; }</style>

<!--=================-->
<!-- ELSEIF IN_ADMIN -->
<!--=================-->

<!--======-->
<!-- ELSE -->
<!--======-->

<!--page_container-->
<div id="page_container">
<a name="top"></a>

<!--page_header-->
<div id="page_header">

<!--main_nav-->
<div id="main-nav" style="height: 17px;">
	<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td class="nowrap">
			<a href="{U_INDEX}"><b>{L_HOME}</b></a>&#0183;

			<a href="{U_TRACKER}"><b>{L_TRACKER}</b></a>&#0183;
<!-- IF LOGGED_IN -->			<a href="{U_GALLERY}"><b>{L_GALLERY}</b></a>&#0183;<!-- ENDIF -->
			<a href="{U_SEARCH}"><b>{L_SEARCH}</b></a>&#0183;
			<a href="{U_TERMS}"><b>{L_TERMS}</b></a>&#0183;
			<a href="{U_FAQ}"><b style="color: #993300;">{L_FAQ}</b></a>&#0183;
			<a href="{U_PRIVATEMSGS}"><b>{L_PRIVATE_MESSAGES}</b></a>&#0183;
			<a href="{U_GROUP_CP}"><b>{L_USERGROUPS}</b></a>&#0183;
			<a href="{U_MEMBERLIST}"><b>{L_MEMBERLIST}</b></a>
		</td>
	</tr>
	</table>
</div>
<!--/main_nav-->

<!--logo-->
<div id="logo">
	<!--<h1>{SITENAME}</h1>
	<h6>{SITE_DESCRIPTION}</h6> -->

	<a href="{U_INDEX}"><img src="images/logo/logo.gif" alt="{SITENAME}" /></a>

</div>
<!--/logo-->

<div style="position: absolute; top: 1px; right: 16px;">
	<form id="quick-search" action="" method="post" onsubmit="
		$(this).attr('action', $('#search-action').val());
		var txt=$('#search-text').val(); return !(txt=='{L_SEARCH_S}' || !txt);
	">
		<input type="hidden" name="max" value="1" />
		<input type="hidden" name="to" value="1" />
		<input id="search-text" type="text" name="nm" onfocus="if(this.value=='{L_SEARCH_S}') this.value='';" onblur="if(this.value=='') this.value='{L_SEARCH_S}';" value="{L_SEARCH_S}" class="hint" style="width: 120px;" />
		<select id="search-action">
			<option value="tracker.php#results" selected="selected"> {L_TRACKER_S} </option>
			<option value="search.php"> {L_FORUM_S} </option>
					</select>
		<input type="submit" class="med bold" value="&raquo;" style="width: 30px;" />
	</form>
</div>

<!-- IF LOGGED_IN -->
<!--logout-->
<div class="topmenu<!-- IF HAVE_NEW_PM -->  new-pm<!-- ENDIF -->">
   <table width="100%" cellpadding="0" cellspacing="0">
   <tr>
            <td width="40%">
         {L_USER_WELCOME} &nbsp;<a href="{U_PROFILE}"><b class="med">{THIS_USERNAME}</b></a>&nbsp; [ <a href="{U_LOGIN_LOGOUT}" onclick="return confirm('{L_CONFIRM_LOGOUT}');">{L_LOGOUT}</a> ]
      </td>
	<!-- Report -->
	<td align="center" nowrap="nowrap">
		<!-- BEGIN switch_report_list -->
		&nbsp;<a href="{U_REPORT_LIST}" class="mainmenu">{REPORT_LIST}</a>  &nbsp;&#0183; 
		<!-- END switch_report_list -->
		<!-- BEGIN switch_report_list_new -->
		&nbsp;<strong><a href="{U_REPORT_LIST}" class="mainmenu">{REPORT_LIST} &#0183; </a></strong> 
		<!-- END switch_report_list_new -->
	<!-- Report [END] -->
      <td>
         <a href="{U_READ_PM}"<!-- IF HAVE_NEW_PM --> class="new-pm-link"<!-- ENDIF -->>{L_PRIVATE_MESSAGES}: {PM_INFO}</a>
      </td>
      <td width="50%" class="tRight">
		<!-- Report -->
		<!-- BEGIN switch_report_general -->
		<a href="{U_WRITE_REPORT}">{L_WRITE_REPORT}</a> &#0183; 
		<!-- END switch_report_general -->
		<!-- Report [END] -->
         <a href="{U_OPTIONS}" style="color:#993300"><b>{L_OPTIONS}</b></a> &#0183; 
		 <a href="{U_CUR_DOWNLOADS}">{L_CUR_DOWNLOADS}</a> <a href="#dls-menu" class="menu-root menu-alt1"><img src="images/menu_open_1.gif" class="menu-alt1" width="9" height="9" align="middle" alt="" /></a> &#0183; 
		 <a href="{U_SEARCH_SELF_BY_LAST}">{L_SEARCH_SELF}</a>
      </td>
         </tr>
   </table>
</div>
<!--/logout-->
<style type="text/css">
.menu-a { background: #FFFFFF; border: 1px solid #92A3A4; }
.menu-a a { background: #EFEFEF; padding: 4px 10px 5px; margin: 1px; display: block; }
</style>
<div class="menu-sub" id="dls-menu">
	<div class="menu-a bold nowrap">
		<a class="med" href="{U_TRACKER}?rid={SESSION_USER_ID}#results">{L_CUR_UPLOADS}</a>
		<a class="med" href="{U_SEARCH}?dlu={SESSION_USER_ID}&dlc=1">{L_SEARCH_DL_COMPLETE_DOWNLOADS}</a>
		<a class="med" href="{U_SEARCH}?dlu={SESSION_USER_ID}&dlw=1">{L_SEARCH_DL_WILL_DOWNLOADS}</a>
	</div>
</div>
<!-- ELSE -->

<!--login form-->
<div class="topmenu">
   <table width="100%" cellpadding="0" cellspacing="0">
   <tr>
      
            <td class="tCenter pad_2">
         <a href="{U_REGISTER}" id="register_link"><b>{L_REGISTER}</b></a>
         &nbsp;&#0183;&nbsp;
         <form action="{S_LOGIN_ACTION}" method="post">
            {L_USERNAME}: <input type="text" name="login_username" size="12" tabindex="1" accesskey="l" />
            {L_PASSWORD}: <input type="password" name="login_password" size="12" tabindex="2" />
            <label title="{L_AUTO_LOGIN}"><input type="checkbox" name="autologin" value="1" tabindex="3" /> {L_REMEMBER}</label>&nbsp;
            <input type="submit" name="login" value="{L_LOGIN}" tabindex="4" />
         </form>
         &nbsp;&#0183;&nbsp;
         <a href="{U_SEND_PASSWORD}">{L_FORGOTTEN_PASSWORD}</a>
      </td>
         </tr>
   </table>
</div>

<!--/login form-->
<!-- ENDIF -->


<!--breadcrumb-->
<!--<div id="breadcrumb"></div>-->
<!--/breadcrumb-->

<!-- IF SHOW_IMPORTANT_INFO -->
<!--important_info-->
<!--<div id="important_info">
important_info
</div>-->
<!--/important_info-->
<!-- ENDIF -->

</div>
<!--/page_header-->

<!--menus-->

<!-- IF SHOW_ONLY_NEW_MENU -->
<div class="menu-sub" id="only-new-options">
	<table cellspacing="1" cellpadding="4">
	<tr>
		<th>{L_DISPLAYING_OPTIONS}</th>
	</tr>
	<tr>
		<td>
			<fieldset id="show-only">
			<legend>{L_SHOW_ONLY}</legend>
			<div class="pad_4">
				<label>
					<input id="only_new_posts" type="checkbox" <!-- IF ONLY_NEW_POSTS_ON -->{CHECKED}<!-- ENDIF -->
						onclick="
							user.set('only_new', ( this.checked ? {ONLY_NEW_POSTS} : 0 ), 365, true);
							$('#only_new_topics').attr('checked', 0);
						" />{L_ONLY_NEW_POSTS}
				</label>
				<label>
					<input id="only_new_topics" type="checkbox" <!-- IF ONLY_NEW_TOPICS_ON -->{CHECKED}<!-- ENDIF -->
						onclick="
							user.set('only_new', ( this.checked ? {ONLY_NEW_TOPICS} : 0 ), 365, true);
							$('#only_new_posts').attr('checked', 0);
						" />{L_ONLY_NEW_TOPICS}
				</label>
			</div>
			</fieldset>
		</td>
	</tr>
	</table>
</div><!--/only-new-options-->
<!-- ENDIF / SHOW_ONLY_NEW_MENU -->

<!--/menus-->



<!--page_content-->
<div id="page_content">
<table cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
 <tr><!-- IF SHOW_SIDEBAR1 -->
  <!--sidebar1-->
  <td id="sidebar1">
   <div id="sidebar1-wrap">

     <!-- IF SHOW_BT_USERDATA --><div id="user_ratio"> 
      <h3>{L_BT_RATIO}</h3>
       <table cellpadding="0">
	   <div align="center">{AVATAR}</div>
       <tr><td>{L_YOUR_RATIO}</td><td><!-- IF DOWN_TOTAL_BYTES gt MIN_DL_BYTES --><b>{USER_RATIO}</b><!-- ELSE --><b>{L_NONE}</b> (DL < {MIN_DL_FOR_RATIO})<!-- ENDIF --></td></tr> 
       <tr><td>{L_DOWNLOADED}</td><td class="leechmed"><b>{DOWN_TOTAL}</b></td></tr> 
       <tr><td>{L_UPLOADED}</td><td class="seedmed"><b>{UP_TOTAL}</b></td></tr> 
       <tr><td><i>{L_RELEASED}</i></td><td class="seedmed">{RELEASED}</td></tr> 
       <tr><td><i>{L_BT_BONUS_UP}</i></td><td class="seedmed">{UP_BONUS}</td></tr> 
       </table> 
     </div><!-- ENDIF -->

	<?php if (!empty($bb_cfg['sidebar1_static_content_path'])) include($bb_cfg['sidebar1_static_content_path']); ?>
	<img width="210" class="spacer" src="{SPACER}" alt="" />

   </div><!--/sidebar1-wrap-->
  </td><!--/sidebar1-->
<!-- ENDIF -->

<!--main_content-->
  <td id="main_content">
   <div id="main_content_wrap">
    <!-- IF SHOW_LATEST_NEWS -->
    <!--latest_news-->
     <div id="latest_news">
      <table cellspacing="0" cellpadding="0" width="100%">
       <tr>
        <td width="70%">
         <h3>{L_LATEST_NEWS}</h3>
          <table cellpadding="0">
            <!-- BEGIN news -->
             <tr>
               <td><div class="news_date">{news.NEWS_TIME}</div></td>
               <td width="100%"><div class="news_title<!-- IF news.NEWS_IS_NEW --> new<!-- ENDIF -->"><a href="{TOPIC_URL}{news.NEWS_TOPIC_ID}">{news.NEWS_TITLE}</a></div></td>
             </tr>
            <!-- END news -->
          </table>
      </table>
     </div>
     <!--/latest_news-->
<!-- ENDIF / SHOW_LATEST_NEWS -->

<!-- IF AD_BLOCK_200 --><div id="ad-200">{AD_BLOCK_200}</div><!--/ad-200--><!-- ELSEIF AD_BLOCK_100 --><div id="ad-100">{AD_BLOCK_100}</div><!--/ad-100--><!-- ENDIF / AD_BLOCK_100 -->

<!--=======================-->
<!-- ENDIF / COMMON_HEADER -->
<!--***********************-->

<!-- IF ERROR_MESSAGE -->
<div class="info_msg_wrap">
<table class="error">
	<tr><td><div class="msg">{ERROR_MESSAGE}</div></td></tr>
</table>
</div>
<!-- ENDIF / ERROR_MESSAGE -->

<!-- IF INFO_MESSAGE -->
<div class="info_msg_wrap">
<table class="info_msg">
	<tr><td><div class="msg">{INFO_MESSAGE}</div></td></tr>
</table>
</div>
<!-- ENDIF / INFO_MESSAGE -->

<!-- page_header.tpl END -->
<!-- module_xx.tpl START -->
