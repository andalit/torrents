<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$datastore->enqueue(array(
	'smile_replacements',
));

$page_cfg['include_bbcode_js'] = true;

define('BBCODE_UID_LEN', 10);

// global that holds loaded-and-prepared bbcode templates, so we only have to do
// that stuff once.

$bbcode_tpl = null;

/**
 * Loads bbcode templates from the bbcode.tpl file of the current template set.
 * Creates an array, keys are bbcode names like "b_open" or "url", values
 * are the associated template.
 * Probably pukes all over the place if there's something really screwed
 * with the bbcode.tpl file.
 *
 * Nathan Codding, Sept 26 2001.
 */
function load_bbcode_template()
{
	global $template;
	$tpl_filename = $template->make_filename('bbcode.tpl');
	$tpl = fread(fopen($tpl_filename, 'r'), filesize($tpl_filename));

	// replace \ with \\ and then ' with \'.
	$tpl = str_replace('\\', '\\\\', $tpl);
	$tpl  = str_replace('\'', '\\\'', $tpl);

	// strip newlines.
	$tpl  = str_replace("\n", '', $tpl);

	// Turn template blocks into PHP assignment statements for the values of $bbcode_tpls..
	$tpl = preg_replace('#<!-- BEGIN (.*?) -->(.*?)<!-- END (.*?) -->#', "\n" . '$bbcode_tpls[\'\\1\'] = \'\\2\';', $tpl);

	$bbcode_tpls = array();

	eval($tpl);

	return $bbcode_tpls;
}


/**
 * Prepares the loaded bbcode templates for insertion into preg_replace()
 * or str_replace() calls in the bbencode_second_pass functions. This
 * means replacing template placeholders with the appropriate preg backrefs
 * or with language vars. NOTE: If you change how the regexps work in
 * bbencode_second_pass(), you MUST change this function.
 *
 * Nathan Codding, Sept 26 2001
 *
 */
function prepare_bbcode_template($bbcode_tpl)
{
	global $lang;

	$bbcode_tpl['olist_open'] = str_replace('{LIST_TYPE}', '\\1', $bbcode_tpl['olist_open']);

	$bbcode_tpl['color_open'] = str_replace('{COLOR}', '\\1', $bbcode_tpl['color_open']);

	$bbcode_tpl['size_open'] = str_replace('{SIZE}', '\\1', $bbcode_tpl['size_open']);

	$bbcode_tpl['align_open'] = str_replace('{ALIGN}', '\\1', $bbcode_tpl['align_open']);

	$bbcode_tpl['font_open'] = str_replace('{FONT}', '\\1', $bbcode_tpl['font_open']);

	$bbcode_tpl['spoiler_title_open'] = str_replace('{SPOILER_HEAD}', '\\1', $bbcode_tpl['spoiler_title_open']);
	$bbcode_tpl['spoiler_open'] = str_replace('{SPOILER_HEAD}', $lang['SPOILER_HEAD'], $bbcode_tpl['spoiler_open']);

	$bbcode_tpl['quote_open'] = str_replace('{L_QUOTE}', $lang['QUOTE'], $bbcode_tpl['quote_open']);

	$bbcode_tpl['quote_username_open'] = str_replace('{L_QUOTE}', $lang['QUOTE'], $bbcode_tpl['quote_username_open']);
	$bbcode_tpl['quote_username_open'] = str_replace('{L_WROTE}', $lang['WROTE'], $bbcode_tpl['quote_username_open']);
	$bbcode_tpl['quote_username_open'] = str_replace('{USERNAME}', '\\1', $bbcode_tpl['quote_username_open']);

	$bbcode_tpl['code_open'] = str_replace('{L_CODE}', $lang['CODE'], $bbcode_tpl['code_open']);

	// [img]image_url[/img]
	$bbcode_tpl['img'] = str_replace('{URL}', '\\1', $bbcode_tpl['img']);
	// [img=align]image_url[/img]
	$bbcode_tpl['img_aligned'] = str_replace('{URL}', '\\2', $bbcode_tpl['img_aligned']);
	$bbcode_tpl['img_aligned'] = str_replace('{ALIGN}', '\\1', $bbcode_tpl['img_aligned']);

	// We do URLs in several different ways..
	$bbcode_tpl['url1'] = str_replace('{URL}', '\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url1'] = str_replace('{DESCRIPTION}', '\\1', $bbcode_tpl['url1']);

	$bbcode_tpl['url2'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url2'] = str_replace('{DESCRIPTION}', '\\1', $bbcode_tpl['url2']);

	$bbcode_tpl['url3'] = str_replace('{URL}', '\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url3'] = str_replace('{DESCRIPTION}', '\\2', $bbcode_tpl['url3']);

	$bbcode_tpl['url4'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url4'] = str_replace('{DESCRIPTION}', '\\3', $bbcode_tpl['url4']);

	$bbcode_tpl['email'] = str_replace('{EMAIL}', '\\1', $bbcode_tpl['email']);

	define("BBCODE_TPL_READY", true);

	return $bbcode_tpl;
}


/**
 * Does second-pass bbencoding. This should be used before displaying the message in
 * a thread. Assumes the message is already first-pass encoded, and we are given the
 * correct UID as used in first-pass encoding.
 */
function bbencode_second_pass($text, $uid)
{
	global $lang, $bbcode_tpl;

	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);
	$text = str_replace("\r", '', $text);

	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;

	// First: If there isn't a "[" and a "]" in the message, don't bother.
	if (strpos($text, '[') === false)
	{
		// Remove padding, return.
		return substr($text, 1);
	}

	// Only load the templates ONCE..
	if (!defined("BBCODE_TPL_READY"))
	{
		// load templates from file into array.
		$bbcode_tpl = load_bbcode_template();

		// prepare array for use in regexps.
		$bbcode_tpl = prepare_bbcode_template($bbcode_tpl);
	}

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$text = bbencode_second_pass_code($text, $uid, $bbcode_tpl);

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
	$text = preg_replace("#\s*(\[quote:$uid)#", ' $1', $text);
	$text = preg_replace("#\s*(\[/quote:$uid\])\s*#", ' $1 ', $text);

	$text = str_replace("[quote:$uid]", $bbcode_tpl['quote_open'], $text);
	$text = str_replace("[/quote:$uid]", $bbcode_tpl['quote_close'], $text);

	$text = preg_replace("/\[quote:$uid=\"(.*?)\"\]/si", $bbcode_tpl['quote_username_open'], $text);

	// [SPOILER]
	$text = preg_replace("#\s*(\[spoiler:$uid)#", ' $1', $text);
	$text = preg_replace("#\s*(\[/spoiler:$uid\])\s*#", ' $1 ', $text);

	$text = preg_replace("/\[spoiler:$uid=\"(.*?)\"\]/si", $bbcode_tpl['spoiler_title_open'], $text);
	$text = str_replace("[spoiler:$uid]", $bbcode_tpl['spoiler_open'], $text);
	$text = str_replace("[/spoiler:$uid]", $bbcode_tpl['spoiler_close'], $text);

	// [list] and [list=x] for (un)ordered lists.
	// unordered lists
	$text = str_replace("[list:$uid]", $bbcode_tpl['ulist_open'], $text);
	// li tags
	$text = str_replace("[*:$uid]", $bbcode_tpl['listitem'], $text);
	// ending tags
	$text = str_replace("[/list:u:$uid]", $bbcode_tpl['ulist_close'], $text);
	$text = str_replace("[/list:o:$uid]", $bbcode_tpl['olist_close'], $text);
	// Ordered lists
	$text = preg_replace("/\[list=([a1]):$uid\]/si", $bbcode_tpl['olist_open'], $text);

	// colours
	$text = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+):$uid\]/si", $bbcode_tpl['color_open'], $text);
	$text = str_replace("[/color:$uid]", $bbcode_tpl['color_close'], $text);

	// size
	$text = preg_replace("/\[size=([1-2]?[0-9]):$uid\]/si", $bbcode_tpl['size_open'], $text);
	$text = str_replace("[/size:$uid]", $bbcode_tpl['size_close'], $text);

	// align
	$text = preg_replace("/\[align=(left|right|center|justify):$uid\]/si", $bbcode_tpl['align_open'], $text);
	$text = str_replace("[/align:$uid]", $bbcode_tpl['align_close'], $text);

	// font
	$text = preg_replace("#\[font:$uid=(?:&quot;|\")?([\w ']+?)(?:&quot;|\")?\]#i", $bbcode_tpl['font_open'], $text);
	$text = str_replace("[/font:$uid]", $bbcode_tpl['font_close'], $text);

	// [tab] for adding indent
	$text = str_replace('[tab]', '&nbsp; ', $text);

	// [br] for adding line breaks
	$text = str_replace('[br]', '<br clear="all" />', $text);

	// [hr] for adding a horizontal rule
	$text = preg_replace("#\n{2,}\[hr\]\n{2,}#", ' <br clear="all" /><br /><hr /><br />', $text);
	$text = preg_replace("#\s*\[hr\]\s*#", ' <hr />', $text);

	// [b] and [/b] for bolding text.
	$text = str_replace("[b:$uid]", $bbcode_tpl['b_open'], $text);
	$text = str_replace("[/b:$uid]", $bbcode_tpl['b_close'], $text);

	// [u] and [/u] for underlining text.
	$text = str_replace("[u:$uid]", $bbcode_tpl['u_open'], $text);
	$text = str_replace("[/u:$uid]", $bbcode_tpl['u_close'], $text);

	// [i] and [/i] for italicizing text.
	$text = str_replace("[i:$uid]", $bbcode_tpl['i_open'], $text);
	$text = str_replace("[/i:$uid]", $bbcode_tpl['i_close'], $text);

	// [s] and [/s] for strikethrough text.
	$text = str_replace("[s:$uid]", $bbcode_tpl['s_open'], $text);
	$text = str_replace("[/s:$uid]", $bbcode_tpl['s_close'], $text);

	// Patterns and replacements for URL and email tags..
	$patterns = array();
	$replacements = array();

	// [img]image_url_here[/img] code..
	// This one gets first-passed..
	$patterns[] = "#\[img:$uid\]([^?].*?)\[/img:$uid\]#i";
	$replacements[] = $bbcode_tpl['img'];

	// [img=align]image_url[/img]
	$patterns[] = "#\[img=(left|right)\:$uid\]([^\s\?&=\#\"<>]+?)\[/img:$uid\]\s*#i";
	$replacements[] = $bbcode_tpl['img_aligned'];

	// matches a [url]xxxx://www.phpbb.com[/url] code..
	$patterns[] = "#\[url\]([\w]+?://([\w\#!$%&~/.\-;:=,?@\]+]|\[(?!url=))*?)\[/url\]#is";
	$replacements[] = $bbcode_tpl['url1'];

	// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[url\]((www|ftp)\.([\w\#!$%&~/.\-;:=,?@\]+]|\[(?!url=))*?)\[/url\]#is";
	$replacements[] = $bbcode_tpl['url2'];

	// [url=xxxx://www.phpbb.com]phpBB[/url] code..
	$patterns[] = "#\[url=([\w]+?://[\w\#!$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
	$replacements[] = $bbcode_tpl['url3'];

	// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
	$patterns[] = "#\[url=((www|ftp)\.[\w\#!$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
	$replacements[] = $bbcode_tpl['url4'];

	// [email]user@domain.tld[/email] code..
	$patterns[] = "#\[email\]([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#si";
	$replacements[] = $bbcode_tpl['email'];

	$text = preg_replace($patterns, $replacements, $text);

	$text = preg_replace("#\n{2,}#", ' <br /><br /> ', $text);
	$text = str_replace("\n", ' <div></div> ', $text);

	// Remove our padding from the string..
	$text = substr($text, 1);

	return $text;

} // bbencode_second_pass()

function make_bbcode_uid ()
{
	// Unique ID for this message..
	return make_rand_str(BBCODE_UID_LEN);
}

function bbencode_first_pass($text, $uid)
{
	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$text = bbencode_first_pass_pda($text, $uid, '[code]', '[/code]', '', true, '');

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
	$text = bbencode_first_pass_pda($text, $uid, '[quote]', '[/quote]', '', false, '');
	$text = bbencode_first_pass_pda($text, $uid, '/\[quote=\\\\&quot;(.*?)\\\\&quot;\]/is', '[/quote]', '', false, '', "[quote:$uid=\\\"\\1\\\"]");

	// [SPOILER]
	$text = bbencode_first_pass_pda($text, $uid, '[spoiler]', '[/spoiler]', '', false, '');
	$text = bbencode_first_pass_pda($text, $uid, '/\[spoiler=\\\\&quot;(.*?)\\\\&quot;\]/is', '[/spoiler]', '', false, '', "[spoiler:$uid=\\\"\\1\\\"]");

	// [list] and [list=x] for (un)ordered lists.
	$open_tag = array();
	$open_tag[0] = "[list]";

	// unordered..
	$text = bbencode_first_pass_pda($text, $uid, $open_tag, "[/list]", "[/list:u]", false, 'replace_listitems');

	$open_tag[0] = "[list=1]";
	$open_tag[1] = "[list=a]";

	// ordered.
	$text = bbencode_first_pass_pda($text, $uid, $open_tag, "[/list]", "[/list:o]",  false, 'replace_listitems');

	// [color] and [/color] for setting text color
	$text = preg_replace("#\[color=(\#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/color\]#si", "[color=\\1:$uid]\\2[/color:$uid]", $text);

	// [size] and [/size] for setting text size
	$text = preg_replace("#\[size=([1-2]?[0-9])\](.*?)\[/size\]#si", "[size=\\1:$uid]\\2[/size:$uid]", $text);

	// [align] and [/align] for text align
	$text = preg_replace("#\[align=(left|right|center|justify)\](.*?)\[/align\]#si", "[align=\\1:$uid]\\2[/align:$uid]", $text);

	// [font] and [/font] for setting font style
	$text = bbencode_first_pass_pda($text, $uid, "#\[font=(?:\\\&quot;|\")?([\w \\\']+?)(?:\\\&quot;|\")?\]#i", '[/font]', '', false, '', "[font:$uid=\\\"\\1\\\"]");

	// [b] and [/b] for bolding text.
	$text = preg_replace("#\[b\](.*?)\[/b\]#si", "[b:$uid]\\1[/b:$uid]", $text);

	// [u] and [/u] for underlining text.
	$text = preg_replace("#\[u\](.*?)\[/u\]#si", "[u:$uid]\\1[/u:$uid]", $text);

	// [i] and [/i] for italicizing text.
	$text = preg_replace("#\[i\](.*?)\[/i\]#si", "[i:$uid]\\1[/i:$uid]", $text);

	// [s] and [/s] for strikethrough text.
	$text = preg_replace("#\[s\](.*?)\[/s\]#si", "[s:$uid]\\1[/s:$uid]", $text);

	// [img]image_url_here[/img] code..
	$text = preg_replace("#\[img\]((http|ftp|https|ftps)://)([^\s\?&=\#\"<>]+?(\.(jpg|jpeg|gif|png)))\[/img\]#i", "[img:$uid]\\1\\3[/img:$uid]", $text);

	// [img=left]image_url_here[/img] code.. and [img=right]image_url_here[/img] code..
	$text = preg_replace("#\[img=(left|right)\]((http|ftp|https|ftps)://)([^\s\?&=\#\"<>]+?(\.(jpg|jpeg|gif|png)))\[/img\]#i", "[img=\\1:$uid]\\2\\4[/img:$uid]", $text);

	// Remove our padding from the string..
	return substr($text, 1);

} // bbencode_first_pass()

/**
 * $text - The text to operate on.
 * $uid - The UID to add to matching tags.
 * $open_tag - The opening tag to match. Can be an array of opening tags.
 * $close_tag - The closing tag to match.
 * $close_tag_new - The closing tag to replace with.
 * $mark_lowest_level - boolean - should we specially mark the tags that occur
 * 					at the lowest level of nesting? (useful for [code], because
 *						we need to match these tags first and transform HTML tags
 *						in their contents..
 * $func - This variable should contain a string that is the name of a function.
 *				That function will be called when a match is found, and passed 2
 *				parameters: ($text, $uid). The function should return a string.
 *				This is used when some transformation needs to be applied to the
 *				text INSIDE a pair of matching tags. If this variable is FALSE or the
 *				empty string, it will not be executed.
 * If open_tag is an array, then the pda will try to match pairs consisting of
 * any element of open_tag followed by close_tag. This allows us to match things
 * like [list=A]...[/list] and [list=1]...[/list] in one pass of the PDA.
 *
 * NOTES:	- this function assumes the first character of $text is a space.
 *				- every opening tag and closing tag must be of the [...] format.
 */
function bbencode_first_pass_pda($text, $uid, $open_tag, $close_tag, $close_tag_new, $mark_lowest_level, $func, $open_regexp_replace = false)
{
	$open_tag_count = 0;

	if (!$close_tag_new || ($close_tag_new == ''))
	{
		$close_tag_new = $close_tag;
	}

	$close_tag_length = strlen($close_tag);
	$close_tag_new_length = strlen($close_tag_new);
	$uid_length = strlen($uid);

	$use_function_pointer = ($func && ($func != ''));

	$stack = array();

	if (is_array($open_tag))
	{
		if (0 == count($open_tag))
		{
			// No opening tags to match, so return.
			return $text;
		}
		$open_tag_count = count($open_tag);
	}
	else
	{
		// only one opening tag. make it into a 1-element array.
		$open_tag_temp = $open_tag;
		$open_tag = array();
		$open_tag[0] = $open_tag_temp;
		$open_tag_count = 1;
	}

	$open_is_regexp = false;

	if ($open_regexp_replace)
	{
		$open_is_regexp = true;
		if (!is_array($open_regexp_replace))
		{
			$open_regexp_temp = $open_regexp_replace;
			$open_regexp_replace = array();
			$open_regexp_replace[0] = $open_regexp_temp;
		}
	}

	if ($mark_lowest_level && $open_is_regexp)
	{
		message_die(GENERAL_ERROR, "Unsupported operation for bbcode_first_pass_pda().");
	}

	// Start at the 2nd char of the string, looking for opening tags.
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($text)))
	{
		$curr_pos = strpos($text, "[", $curr_pos);

		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending tag.
			$found_start = false;
			$which_start_tag = "";
			$start_tag_index = -1;

			for ($i = 0; $i < $open_tag_count; $i++)
			{
				// Grab everything until the first "]"...
				$possible_start = substr($text, $curr_pos, strpos($text, ']', $curr_pos + 1) - $curr_pos + 1);

				//
				// We're going to try and catch usernames with "[' characters.
				//
				if( preg_match('#\[quote=\\\&quot;#si', $possible_start, $match) && !preg_match('#\[quote=\\\&quot;(.*?)\\\&quot;\]#si', $possible_start) )
				{
					// OK we are in a quote tag that probably contains a ] bracket.
					// Grab a bit more of the string to hopefully get all of it..
					if ($close_pos = strpos($text, '&quot;]', $curr_pos + 14))
					{
						if (strpos(substr($text, $curr_pos + 14, $close_pos - ($curr_pos + 14)), '[quote') === false)
						{
							$possible_start = substr($text, $curr_pos, $close_pos - $curr_pos + 7);
						}
					}
				}

				// Now compare, either using regexp or not.
				if ($open_is_regexp)
				{
					$match_result = array();
					if (preg_match($open_tag[$i], $possible_start, $match_result))
					{
						$found_start = true;
						$which_start_tag = $match_result[0];
						$start_tag_index = $i;
						break;
					}
				}
				else
				{
					// straightforward string comparison.
					if (0 == strcasecmp($open_tag[$i], $possible_start))
					{
						$found_start = true;
						$which_start_tag = $open_tag[$i];
						$start_tag_index = $i;
						break;
					}
				}
			}

			if ($found_start)
			{
				// We have an opening tag.
				// Push its position, the text we matched, and its index in the open_tag array on to the stack, and then keep going to the right.
				$match = array("pos" => $curr_pos, "tag" => $which_start_tag, "index" => $start_tag_index);
				array_push($stack, $match);
				//
				// Rather than just increment $curr_pos
				// Set it to the ending of the tag we just found
				// Keeps error in nested tag from breaking out
				// of table structure..
				//
				$curr_pos += strlen($possible_start);
			}
			else
			{
				// check for a closing tag..
				$possible_end = substr($text, $curr_pos, $close_tag_length);
				if (0 == strcasecmp($close_tag, $possible_end))
				{
					// We have an ending tag.
					// Check if we've already found a matching starting tag.
					if (sizeof($stack) > 0)
					{
						// There exists a starting tag.
						$curr_nesting_depth = sizeof($stack);
						// We need to do 2 replacements now.
						$match = array_pop($stack);
						$start_index = $match['pos'];
						$start_tag = $match['tag'];
						$start_length = strlen($start_tag);
						$start_tag_index = $match['index'];

						if ($open_is_regexp)
						{
							$start_tag = preg_replace($open_tag[$start_tag_index], $open_regexp_replace[$start_tag_index], $start_tag);
						}

						// everything before the opening tag.
						$before_start_tag = substr($text, 0, $start_index);

						// everything after the opening tag, but before the closing tag.
						$between_tags = substr($text, $start_index + $start_length, $curr_pos - $start_index - $start_length);

						// Run the given function on the text between the tags..
						if ($use_function_pointer)
						{
							$between_tags = $func($between_tags, $uid);
						}

						// everything after the closing tag.
						$after_end_tag = substr($text, $curr_pos + $close_tag_length);

						// Mark the lowest nesting level if needed.
						if ($mark_lowest_level && ($curr_nesting_depth == 1))
						{
							if ($open_tag[0] == '[code]')
							{
								$code_entities_match = array('#<#', '#>#', '#"#', '#:#', '#\[#', '#\]#', '#\(#', '#\)#', '#\{#', '#\}#');
								$code_entities_replace = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');
								$between_tags = preg_replace($code_entities_match, $code_entities_replace, $between_tags);
							}
							$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$curr_nesting_depth:$uid]";
							$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$curr_nesting_depth:$uid]";
						}
						else
						{
							if ($open_tag[0] == '[code]')
							{
								$text = $before_start_tag . '&#91;code&#93;';
								$text .= $between_tags . '&#91;/code&#93;';
							}
							else
							{
								if ($open_is_regexp)
								{
									$text = $before_start_tag . $start_tag;
								}
								else
								{
									$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$uid]";
								}
								$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$uid]";
							}
						}

						$text .= $after_end_tag;

						// Now.. we've screwed up the indices by changing the length of the string.
						// So, if there's anything in the stack, we want to resume searching just after it.
						// otherwise, we go back to the start.
						if (sizeof($stack) > 0)
						{
							$match = array_pop($stack);
							$curr_pos = $match['pos'];
//							bbcode_array_push($stack, $match);
//							++$curr_pos;
						}
						else
						{
							$curr_pos = 1;
						}
					}
					else
					{
						// No matching start tag found. Increment pos, keep going.
						++$curr_pos;
					}
				}
				else
				{
					// No starting tag or ending tag.. Increment pos, keep looping.,
					++$curr_pos;
				}
			}
		}
	} // while

	return $text;

} // bbencode_first_pass_pda()

/**
 * Does second-pass bbencoding of the [code] tags. This includes
 * running htmlspecialchars() over the text contained between
 * any pair of [code] tags that are at the first level of
 * nesting. Tags at the first level of nesting are indicated
 * by this format: [code:1:$uid] ... [/code:1:$uid]
 * Other tags are in this format: [code:$uid] ... [/code:$uid]
 */
function bbencode_second_pass_code($text, $uid, $bbcode_tpl)
{
	global $lang;

	$code_start_html = $bbcode_tpl['code_open'];
	$code_end_html =  $bbcode_tpl['code_close'];

	// First, do all the 1st-level matches. These need an htmlspecialchars() run,
	// so they have to be handled differently.
	$match_count = preg_match_all("#\[code:1:$uid\](.*?)\[/code:1:$uid\]#si", $text, $matches);

	for ($i = 0; $i < $match_count; $i++)
	{
		$before_replace = $matches[1][$i];
		$after_replace = $matches[1][$i];

		// Replace 2 spaces with "&nbsp; " so non-tabbed code indents without making huge long lines.
		$after_replace = str_replace("  ", "&nbsp; ", $after_replace);
		// now Replace 2 spaces with " &nbsp;" to catch odd #s of spaces.
		$after_replace = str_replace("  ", " &nbsp;", $after_replace);

		// Replace tabs with "&nbsp; &nbsp;" so tabbed code indents sorta right without making huge long lines.
		$after_replace = str_replace("\t", "&nbsp; ", $after_replace);

		// now Replace space occurring at the beginning of a line
		$after_replace = preg_replace("/^ {1}/m", '&nbsp;', $after_replace);

		$str_to_match = "[code:1:$uid]" . $before_replace . "[/code:1:$uid]";

		$replacement = $code_start_html;
		$replacement .= $after_replace;
		$replacement .= $code_end_html;

		$text = str_replace($str_to_match, $replacement, $text);
	}

	// Now, do all the non-first-level matches. These are simple.
	$text = str_replace("[code:$uid]", $code_start_html, $text);
	$text = str_replace("[/code:$uid]", $code_end_html, $text);

	return $text;

} // bbencode_second_pass_code()

/**
 * Rewritten by Nathan Codding - Feb 6, 2001.
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *		to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */

function ed2k_link_callback ($m)
{
	$max_len = 80;
	$href    = $m[1];
	$size    = humn_size($m[3]);
	$fname   = $m[2];

	if (strlen($fname) > $max_len)
	{
		$fname = substr($fname, 0, $max_len - 19) .'...'. substr($fname, -16);
	}

	return "<a href=\"$href\" class=\"postLink\">$fname&nbsp;($size)</a>";
}

function make_url_clickable_callback ($m)
{
	$max_len = 70;
	$href    = $m[1];
	$scheme  = strtolower($m[2]);
	$name    = $href;

	if (strlen($name) > $max_len)
	{
		$name = substr($name, 0, $max_len - 19) .'...'. substr($name, -16);
	}
	if (strpos($scheme, '://') === false)
	{
		$href = (strpos($scheme, 'www') !== false) ? "http://$href" : "ftp://$href";
	}

	return "<a href=\"$href\" class=\"postLink\">$name</a>";
}

function make_clickable ($text)
{
	global $bb_cfg;

	$url_regexp = "#
		(?<![\"'=])
		\b
		(
			(https?://|ftp://|www\.|ftp\.)
			[\w\#!$%&~/.\-;:=?@\[\]+]+
		)
		(?![\"']|\[/url|\[/img|</a)
		(?=[,!]?\s|[\)<!])
	#xi";

	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);

	// pad it with a space so we can match things at the start of the 1st line.
	$ret = " $text ";

	if ($bb_cfg['parse_ed2k_links'])
	{
		// ed2k file links (Meithar):
		// ed2k://|file|fileName|fileSize|fileHash|(optional params)|(optional params)|etc|
		$ret = preg_replace_callback("#\b(ed2k://\|file\|([^\\/\|:<>\*\?\"]+?)\|(\d+?)\|([a-f0-9]{32})\|(.*?)/?)(?![\"'])(?=([,\.]*?[\s<\[])|[,\.]*?$)#i", "ed2k_link_callback", $ret);
		// ed2k server links:
		// ed2k://|server|serverIP|serverPort
		$ret = preg_replace("#\b(ed2k://\|server\|([\d\.]+?)\|(\d+?)\|/?)\B#i", "<a href=\"\\1\" class=\"postLink\">\\2:\\3</a>", $ret);
	}

	// hide passkey
	$ret = preg_replace('#\?'. $bb_cfg['passkey_key'] .'=[a-zA-Z0-9]{'. BT_AUTH_KEY_LENGTH .'}&#', '?passkey&', $ret);
	// hide sid
	$ret = preg_replace('#([\?&;])sid=[a-zA-Z0-9]{'. SID_LENGTH .'}#', '$1sid', $ret);

	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, comma, double quote or <
#	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" class=\"postLink\">\\2</a>", $ret);
	$ret = preg_replace_callback($url_regexp, 'make_url_clickable_callback', $ret);

	// matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// zzzz is optional.. will contain everything up to the first space, newline,
	// comma, double quote or <.
#	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" class=\"postLink\">\\2</a>", $ret);

	// matches an email@domain type address at the start of a line, or after a space.
	// Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
	$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\" class=\"postLink\">\\2@\\3</a>", $ret);

	// Remove our padding..
	$ret = substr(substr($ret, 0, -1), 1);

	return($ret);
}

/**
 * Nathan Codding - Feb 6, 2001
 * Reverses the effects of make_clickable(), for use in editpost.
 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
 *
 */
function undo_make_clickable($text)
{
	$text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\">.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);
	$text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\">.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);

	return $text;

}

/**
 * Nathan Codding - August 24, 2000.
 * Takes a string, and does the reverse of the PHP standard function
 * htmlspecialchars().
 */
function undo_htmlspecialchars($input)
{
	$input = preg_replace("/&gt;/i", ">", $input);
	$input = preg_replace("/&lt;/i", "<", $input);
	$input = preg_replace("/&quot;/i", "\"", $input);
	$input = preg_replace("/&amp;/i", "&", $input);

	return $input;
}

/**
 * This is used to change a [*] tag into a [*:$uid] tag as part
 * of the first-pass bbencoding of [list] tags. It fits the
 * standard required in order to be passed as a variable
 * function into bbencode_first_pass_pda().
 */
function replace_listitems($text, $uid)
{
	$text = str_replace("[*]", "[*:$uid]", $text);

	return $text;
}

/**
 * Escapes the "/" character with "\/". This is useful when you need
 * to stick a runtime string into a PREG regexp that is being delimited
 * with slashes.
 */
function escape_slashes($input)
{
	$output = str_replace('/', '\/', $input);
	return $output;
}

/**
 * This function does exactly what the PHP4 function array_push() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with our own
 * method of doing it.
 * This function was deprecated in phpBB 2.0.18
 */
function bbcode_array_push(&$stack, $value)
{
   $stack[] = $value;
   return(sizeof($stack));
}

/**
 * This function does exactly what the PHP4 function array_pop() does
 * however, to keep phpBB compatable with PHP 3 we had to come up with our own
 * method of doing it.
 * This function was deprecated in phpBB 2.0.18
 */
function bbcode_array_pop(&$stack)
{
   $arrSize = count($stack);
   $x = 1;

   while(list($key, $val) = each($stack))
   {
      if($x < count($stack))
      {
	 		$tmpArr[] = $val;
      }
      else
      {
	 		$return_val = $val;
      }
      $x++;
   }
   $stack = @$tmpArr;

   return($return_val);
}

//
// Smilies code ... would this be better tagged on to the end of bbcode.php?
// Probably so and I'll move it before B2
//
function smilies_pass ($message)
{
	static $smilies;

	if (!isset($smilies))
	{
		if (!$smilies = $GLOBALS['datastore']->get('smile_replacements'))
		{
			$GLOBALS['datastore']->update('smile_replacements');
			$smilies = $GLOBALS['datastore']->get('smile_replacements');
		}
	}
	if ($smilies)
	{
		$message = preg_replace($smilies['orig'], $smilies['repl'], $message);
	}

	return $message;
}

// $mode == 'briefly' currently disabled
// last version: http://trac.torrentpier.com/trac/browser/torrentpier/trunk/forum/includes/bbcode.php?rev=559#L868
function get_parsed_post ($postrow, $mode = 'full', $return_chars = 600)
{
	global $bb_cfg, $db;

	if ($bb_cfg['use_posts_cache'] && !empty($postrow['post_html']))
	{
		return $postrow['post_html'];
	}

	$message    = $postrow['post_text'];
	$bbcode_uid = $postrow['bbcode_uid'];

	if ($bbcode_uid)
	{
		$message = ($bb_cfg['allow_bbcode']) ? bbencode_second_pass($message, $bbcode_uid) : str_replace(":$bbcode_uid", '', $message);
	}

	$message = make_clickable($message);

	if ($bb_cfg['allow_smilies'] && $postrow['enable_smilies'])
	{
		$message = smilies_pass($message);
	}

	$message = str_replace("\n", "\n<br />\n", $message);

	// Posts cache
	if ($bb_cfg['use_posts_cache'])
	{
		$db->shutdown['post_html'][] = array(
			'post_id'   => (int) $postrow['post_id'],
			'post_html' => (string) $message,
		);
	}

	return $message;
}

function update_post_html ($postrow)
{
	$GLOBALS['db']->query("DELETE FROM ". POSTS_HTML_TABLE ." WHERE post_id = ". (int) $postrow['post_id'] ." LIMIT 1");
}

// some functions from vB
// #############################################################################
/**
* Strips away [quote] tags and their contents from the specified string
*
* @param	string	Text to be stripped of quote tags
*
* @return	string
*/
function strip_quotes ($text)
{
	$lowertext = strtolower($text);

	// find all [quote tags
	$start_pos = array();
	$curpos = 0;
	do
	{
		$pos = strpos($lowertext, '[quote', $curpos);
		if ($pos !== false)
		{
			$start_pos["$pos"] = 'start';
			$curpos = $pos + 6;
		}
	}
	while ($pos !== false);

	if (sizeof($start_pos) == 0)
	{
		return $text;
	}

	// find all [/quote] tags
	$end_pos = array();
	$curpos = 0;
	do
	{
		$pos = strpos($lowertext, '[/quote', $curpos);
		if ($pos !== false)
		{
			$end_pos["$pos"] = 'end';
			$curpos = $pos + 8;
		}
	}
	while ($pos !== false);

	if (sizeof($end_pos) == 0)
	{
		return $text;
	}

	// merge them together and sort based on position in string
	$pos_list = $start_pos + $end_pos;
	ksort($pos_list);

	do
	{
		// build a stack that represents when a quote tag is opened
		// and add non-quote text to the new string
		$stack = array();
		$newtext = '[...] ';
		$substr_pos = 0;
		foreach ($pos_list AS $pos => $type)
		{
			$stacksize = sizeof($stack);
			if ($type == 'start')
			{
				// empty stack, so add from the last close tag or the beginning of the string
				if ($stacksize == 0)
				{
					$newtext .= substr($text, $substr_pos, $pos - $substr_pos);
				}
				array_push($stack, $pos);
			}
			else
			{
				// pop off the latest opened tag
				if ($stacksize)
				{
					array_pop($stack);
					$substr_pos = $pos + 8;
				}
			}
		}

		// add any trailing text
		$newtext .= substr($text, $substr_pos);

		// check to see if there's a stack remaining, remove those points
		// as key points, and repeat. Allows emulation of a non-greedy-type
		// recursion.
		if ($stack)
		{
			foreach ($stack AS $pos)
			{
				unset($pos_list["$pos"]);
			}
		}
	}
	while ($stack);

	return $newtext;
}

// #############################################################################
/**
* Strips away bbcode from a given string, leaving plain text
*
* @param	string	Text to be stripped of bbcode tags
* @param	boolean	If true, strip away quote tags AND their contents
* @param	boolean	If true, use the fast-and-dirty method rather than the shiny and nice method
*
* @return	string
*/
function strip_bbcode ($message, $stripquotes = true, $fast_and_dirty = false, $showlinks = true)
{
	$find = array();
	$replace = array();

	if ($stripquotes)
	{
		// [quote=username] and [quote]
		$message = strip_quotes($message);
	}

	// a really quick and rather nasty way of removing bbcode
	if ($fast_and_dirty)
	{
		// any old thing in square brackets
		$find[] = '#\[.*/?\]#siU';
		$replace = '';

		$message = preg_replace($find, $replace, $message);
	}
	// the preferable way to remove bbcode
	else
	{
		// simple links
		$find[] = '#\[(email|url)=("??)(.+)\\2\]\\3\[/\\1\]#siU';
		$replace[] = '\3';

		// named links
		$find[] = '#\[(email|url)=("??)(.+)\\2\](.+)\[/\\1\]#siU';
		$replace[] = ($showlinks ? '\4 (\3)' : '\4');

		// smilies
		$find[] = '#(?<=^|\W)(:\w+?:)(?=$|\W)#';
		$replace[] = '';

		// replace
		$message = preg_replace($find, $replace, $message);

		// strip out all other instances of [x]...[/x]
		while (preg_match('#\[([a-z]+)\s*?(?:[^\]]*?)\](.*?)(\[/\1\])#is', $message, $m))
		{
			$message = str_replace($m[0], $m[2], $message);
		}

		$replace = array('[*]', '[hr]', '[br]', '[tab]');
		$message = str_replace($replace, ' ', $message);
	}

	return $message;
}

function extract_search_words ($text)
{
	global $bb_cfg, $lang;

	$max_words_count = $bb_cfg['max_search_words_per_post'];
	$min_word_len    = max(2, $bb_cfg['search_min_word_len'] - 1);
	$max_word_len    = $bb_cfg['search_max_word_len'];

	$text = ' ' . str_compact(strip_tags(mb_strtolower($text, $lang['CONTENT_ENCODING']))) . ' ';
	$text = str_replace(array('&#91;', '&#93;'), array('[', ']'), $text);

	// HTML entities like &nbsp;
	$text = preg_replace('/(\w*?)&#?[0-9a-z]+;(\w*?)/u', '', $text);
	// Remove URL's
	$text = preg_replace('#\b[a-z0-9]+://[0-9a-z\.\-]+(/[0-9a-z\?\.%_\-\+=&/]+)?#u', ' ', $text);

	$text = strip_bbcode($text, true, true);

	// Filter out characters like ^, $, &, change "it's" to "its"
	$text = preg_replace('#[.,:;]#u', ' ', $text);

	// short & long words
	$text = preg_replace('#(?<=^|\s)(\S{1,'.$min_word_len.'}|\S{'.$max_word_len.',}|\W*)(?=$|\s)#', ' ', $text);

	$text = remove_stopwords($text);
#	$text = replace_synonyms($text);

	// Trim 1+ spaces to one space and split this string into unique words
	$text = array_unique(explode(' ', str_compact($text)));

	if (sizeof($text) > $max_words_count)
	{
#		shuffle($text);
		$text = array_splice($text, 0, $max_words_count);
	}

	return $text;
}

function replace_synonyms ($text)
{
	static $syn_match = null, $syn_replace = null;

	if (is_null($syn_match))
	{
		preg_match_all("#(\w+) (\w+)(\r?\n|$)#", @file_get_contents(DEFAULT_LANG_DIR .'search_synonyms.txt'), $m);

		$syn_match   = $m[2];
		$syn_replace = $m[1];

		array_deep($syn_match,   'pad_with_space');
		array_deep($syn_replace, 'pad_with_space');
	}

	return ($syn_match && $syn_replace) ? str_replace($syn_match, $syn_replace, $text) : $text;
}

function add_search_words ($post_id, $post_message, $post_title = '', $bbcode_uid = '', $only_return_words = false)
{
	global $db;

	$text  = $post_title .' '. $post_message;
	$text  = strip_bbcode_uid($text, $bbcode_uid);
	$words = ($text) ? extract_search_words($text) : array();

	if ($only_return_words)
	{
		return join("\n", $words);
	}
	else
	{
		$db->query("DELETE FROM ". POSTS_SEARCH_TABLE ." WHERE post_id = $post_id");

		if ($words_sql = $db->escape(join("\n", $words)))
		{
			$db->query("REPLACE INTO ". POSTS_SEARCH_TABLE ." (post_id, search_words) VALUES ($post_id, '$words_sql')");
		}
	}
}

function strip_bbcode_uid ($text, $bbcode_uid)
{
	return ($bbcode_uid) ? preg_replace("#:(\d+:)*?$bbcode_uid#", '', $text) : $text;
}