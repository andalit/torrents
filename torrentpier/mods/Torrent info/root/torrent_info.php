<?php

define('IN_PHPBB', TRUE);
define('IN_AJAX', TRUE);
define('BB_ROOT', './');
require(BB_ROOT .'common.php');
require(BB_ROOT .'attach_mod/attachment_mod.php');
require(INC_DIR .'functions_torrent.php');

// Init userdata
$user->session_start();

$id = isset($_GET["a"]) ? (int) $_GET["a"] : 0;

if (!$torrent = get_torrent_info($id))
{
	die('Torrent not found');
}

$topic_id = $torrent['topic_id'];

$fn = get_attachments_dir() .'/'. $torrent['physical_filename'];

if (!is_file($fn))
{
	die('File name error');
}

if (!file_exists($fn))
{
	die('File not exists');
}

if (!$dict = bdecode_file($fn))
{
	die('This is not a bencoded file');
}

?>
<style type="text/css">
#infobox-wrap { width: 760px; }
#infobox-body {
  background: #FFFFFF; color: #000000; padding: 1em;
  height: 200px; overflow: auto; border: 1px inset #000000;
}
/* list styles */
ul ul { margin-left: 15px; }
ul, li { padding: 0px; margin: 0px; list-style-type: none; color: #000; font-weight: normal;}
ul a, li a { color: #009; text-decoration: none; font-weight: normal; }
li { display: inline; } /* fix for IE blank line bug */
ul > li { display: list-item; }

li div.string  {padding: 3px;}
li div.integer {padding: 3px;}
li div.dictionary {padding: 3px;}
li div.list {padding: 3px;}
li div.string span.icon {color:#090;padding: 2px;}
li div.integer span.icon {color:#990;padding: 2px;}
li div.dictionary span.icon {color:#909;padding: 2px;}
li div.list span.icon {color:#009;padding: 2px;}

li span.title {font-weight: bold;}

</style>
<br />
<div id="infobox-wrap" class="bCenter row1">
  <fieldset class="pad_6">
  <legend class="med bold mrg_2 warnColor1"><span style="color:black">Данные торрента</span></legend>
    <div class="bCenter">
      <table width="100%" border="0" cellspacing="0" cellpadding="10">
        <tr>
          <td>
            <table width="100%" border="0" cellspacing="1" cellpadding="4" class="forumline">
              <tr>
              <td valign="top" class="row1">
                <span class="gen">
<?php
$dict = bdec_file($fn, (1024*1024));

// Start table
$dict['value']['info']['value']['pieces']['value'] = "0x".bin2hex(substr($dict['value']['info']['value']['pieces']['value'], 0, 25))."...";
@$dict['value']['info']['value']['sha1']['value'] = strtoupper(base32_encode($dict['value']['info']['value']['sha1']['value']));
@$dict['value']['info']['value']['ed2k']['value'] = bin2hex($dict['value']['info']['value']['ed2k']['value']);
@$dict['value']['info']['value']['tiger']['value'] = strtoupper(base32_encode($dict['value']['info']['value']['tiger']['value']));
@$dict['value']['info']['value']['md5sum']['value'] = $dict['value']['info']['value']['md5sum']['value'];

echo "<ul id=colapse>";
print_array($dict,"*", "", "root");
echo "</ul>";

// End table
print("</td></table>");

?>

<script type="text/javascript" language="javascript1.2"><!--
var openLists = [], oIcount = 0;
function compactMenu(oID,oAutoCol,oPlMn,oMinimalLink) {
  if( !document.getElementsByTagName || !document.childNodes || !document.createElement ) { return; }
  var baseElement = document.getElementById( oID ); if( !baseElement ) { return; }
  compactChildren( baseElement, 0, oID, oAutoCol, oPlMn, baseElement.tagName.toUpperCase(), oMinimalLink && oPlMn );
}
function compactChildren( oOb, oLev, oBsID, oCol, oPM, oT, oML ) {
  if( !oLev ) { oBsID = escape(oBsID); if( oCol ) { openLists[oBsID] = []; } }
  for( var x = 0, y = oOb.childNodes; x < y.length; x++ ) { if( y[x].tagName ) {
    //for each immediate LI child
    var theNextUL = y[x].getElementsByTagName( oT )[0];
    if( theNextUL ) {
      //collapse the first UL/OL child
      theNextUL.style.display = 'none';
      //create a link for expanding/collapsing
      var newLink = document.createElement('A');
      newLink.setAttribute( 'href', '#' );
      newLink.onclick = new Function( 'clickSmack(this,' + oLev + ',\'' + oBsID + '\',' + oCol + ',\'' + escape(oT) + '\');return false;' );
      //wrap everything upto the child U/OL in the link
      if( oML ) { var theHTML = ''; } else {
        var theT = y[x].innerHTML.toUpperCase().indexOf('<'+oT);
        var theA = y[x].innerHTML.toUpperCase().indexOf('<A');
        var theHTML = y[x].innerHTML.substr(0, ( theA + 1 && theA < theT ) ? theA : theT );
        while( !y[x].childNodes[0].tagName || ( y[x].childNodes[0].tagName.toUpperCase() != oT && y[x].childNodes[0].tagName.toUpperCase() != 'A' ) ) {
          y[x].removeChild( y[x].childNodes[0] ); }
      }
      y[x].insertBefore(newLink,y[x].childNodes[0]);
      y[x].childNodes[0].innerHTML = oPM + theHTML.replace(/^\s*|\s*$/g,'');
      theNextUL.MWJuniqueID = oIcount++;
      compactChildren( theNextUL, oLev + 1, oBsID, oCol, oPM, oT, oML );
} } } }
function clickSmack( oThisOb, oLevel, oBsID, oCol, oT ) {
  if( oThisOb.blur ) { oThisOb.blur(); }
  oThisOb = oThisOb.parentNode.getElementsByTagName( unescape(oT) )[0];
  if( oCol ) {
    for( var x = openLists[oBsID].length - 1; x >= oLevel; x-=1 ) { if( openLists[oBsID][x] ) {
      openLists[oBsID][x].style.display = 'none'; if( oLevel != x ) { openLists[oBsID][x] = null; }
    } }
    if( oThisOb == openLists[oBsID][oLevel] ) { openLists[oBsID][oLevel] = null; }
    else { oThisOb.style.display = 'block'; openLists[oBsID][oLevel] = oThisOb; }
  } else { oThisOb.style.display = ( oThisOb.style.display == 'block' ) ? 'none' : 'block'; }
}
function stateToFromStr(oID,oFStr) {
  if( !document.getElementsByTagName || !document.childNodes || !document.createElement ) { return ''; }
  var baseElement = document.getElementById( oID ); if( !baseElement ) { return ''; }
  if( !oFStr && typeof(oFStr) != 'undefined' ) { return ''; } if( oFStr ) { oFStr = oFStr.split(':'); }
  for( var oStr = '', l = baseElement.getElementsByTagName(baseElement.tagName), x = 0; l[x]; x++ ) {
    if( oFStr && MWJisInTheArray( l[x].MWJuniqueID, oFStr ) && l[x].style.display == 'none' ) { l[x].parentNode.getElementsByTagName('a')[0].onclick(); }
    else if( l[x].style.display != 'none' ) { oStr += (oStr?':':'') + l[x].MWJuniqueID; }
  }
  return oStr;
}
function MWJisInTheArray(oNeed,oHay) { for( var i = 0; i < oHay.length; i++ ) { if( oNeed == oHay[i] ) { return true; } } return false; }
function selfLink(oRootElement,oClass,oExpand) {
  if(!document.getElementsByTagName||!document.childNodes) { return; }
  oRootElement = document.getElementById(oRootElement);
  for( var x = 0, y = oRootElement.getElementsByTagName('a'); y[x]; x++ ) {
    if( y[x].getAttribute('href') && !y[x].href.match(/#$/) && getRealAddress(y[x]) == getRealAddress(location) ) {
      y[x].className = (y[x].className?(y[x].className+' '):'') + oClass;
      if( oExpand ) {
        oExpand = false;
        for( var oEl = y[x].parentNode, ulStr = ''; oEl != oRootElement && oEl != document.body; oEl = oEl.parentNode ) {
          if( oEl.tagName && oEl.tagName == oRootElement.tagName ) { ulStr = oEl.MWJuniqueID + (ulStr?(':'+ulStr):''); } }
        stateToFromStr(oRootElement.id,ulStr);
} } } }
function getRealAddress(oOb) { return oOb.protocol + ( ( oOb.protocol.indexOf( ':' ) + 1 ) ? '' : ':' ) + oOb.hostname + ( ( typeof(oOb.pathname) == typeof(' ') && oOb.pathname.indexOf('/') != 0 ) ? '/' : '' ) + oOb.pathname + oOb.search; }

compactMenu('colapse',false,'');
//--></script>
                </span>
                <br/>
              </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
  </fieldset>
</div><!--/infobox-wrap-->
<?php

function print_array($array, $offset_symbol = "|--", $offset = "", $parent = "")
{
	global $bb_cfg;
	
  if (!is_array($array))
  {
    echo "[$array] is not an array!<BR>";
    return;
  }
 
  reset($array);


  switch(@$array['type'])
  {
	case "string":
		if(strpos($parent, 'announce') !== false)
		{
			if((strpos($array['value'], 'passkey') !== false) || 
			   (strpos($array['value'], "?". $bb_cfg['passkey_key'] ."=") !== false))
			{
				$array['value'] = $bb_cfg['bt_announce_url'];
			}
		}
		if(strpos($array['value'], 'http://') !== false)
		{
			$array['value'] = "<a href=\"{$array['value']}\" target=\"_blank\">{$array['value']}</a>";
		}
      printf("<li><div class=string> - <span class=icon>[STRING]</span> <span class=title>[%s]</span> <span class=length>(%d)</span>: <span class=value>%s</span></div></li>",$parent,$array['strlen'],utf8_to_win($array['value']));
      break;
    case "integer":
	 if(strpos($parent, 'length') !== false)
	 {
		 $array['value'] = humn_size($array['value']) . " ({$array['value']})";
	 }
	 if(strpos($parent, 'date') !== false)
	 {
		 $array['value'] = create_date($bb_cfg['post_date_format'], $array['value']) . " ({$array['value']})";
	 }
      printf("<li><div class=integer> - <span class=icon>[INT]</span> <span class=title>[%s]</span> <span class=length>(%d)</span>: <span class=value>%s</span></div></li>",$parent,$array['strlen'],($array['value']));
      break;
    case "list":
      printf("<li><div class=list> + <span class=icon>[LIST]</span> <span class=title>[%s]</span> <span class=length>(%d)</span></div>",$parent,$array['strlen']);
      echo "<ul>";
      print_array($array['value'], $offset_symbol, $offset.$offset_symbol);
      echo "</ul></li>";
      break;
    case "dictionary":
      printf("<li><div class=dictionary> + <span class=icon>[DICT]</span> <span class=title>[%s]</span> <span class=length>(%d)</span></div>",$parent,$array['strlen']);
      while (list($key, $val) = each($array))
      {
        if (is_array($val))
        {
          echo "<ul>";
          print_array($val, $offset_symbol, $offset.$offset_symbol,$key);
          echo "</ul>";
        }
      }
      echo "</li>";

      break;
    default:
        while (list($key, $val) = each($array))
        {
          if (is_array($val))
          {
            //echo $offset;
            print_array($val, $offset_symbol, $offset, $key);
          }
        }
      break;
  
  }
 
} 
function benc($obj) {
  if (!is_array($obj) || !isset($obj["type"]) || !isset($obj["value"]))
    return;
  $c = $obj["value"];
  switch ($obj["type"]) {
    case "string":
      return benc_str($c);
    case "integer":
      return benc_int($c);
    case "list":
      return benc_list($c);
    case "dictionary":
      return benc_dict($c);
    default:
      return;
  }
}
function benc_str($s) {
  return strlen($s) . ":$s";
}
function benc_int($i) {
  return "i" . $i . "e";
}
function benc_list($a) {
  $s = "l";
  foreach ($a as $e) {
    $s .= benc($e);
  }
  $s .= "e";
  return $s;
}
function benc_dict($d) {
  $s = "d";
  $keys = array_keys($d);
  sort($keys);
  foreach ($keys as $k) {
    $v = $d[$k];
    $s .= benc_str($k);
    $s .= benc($v);
  }
  $s .= "e";
  return $s;
}
function bdec_file($f, $ms) {
  $fp = fopen($f, "rb");
  if (!$fp)
    return;
  $e = fread($fp, $ms);
  fclose($fp);
  return bdec($e);
}
function bdec($s) {
  if (preg_match('/^(\d+):/', $s, $m)) {
    $l = $m[1];
    $pl = strlen($l) + 1;
    $v = substr($s, $pl, $l);
    $ss = substr($s, 0, $pl + $l);
    if (strlen($v) != $l)
      return;
    return array('type' => "string", 'value' => $v, 'strlen' => strlen($ss), 'string' => $ss);
  }
  if (preg_match('/^i(\d+)e/', $s, $m)) {
    $v = $m[1];
    $ss = "i" . $v . "e";
    if ($v === "-0")
      return;
    if ($v[0] == "0" && strlen($v) != 1)
      return;
    return array('type' => "integer", 'value' => $v, 'strlen' => strlen($ss), 'string' => $ss);
  }
  switch ($s[0]) {
    case "l":
      return bdec_list($s);
    case "d":
      return bdec_dict($s);
    default:
      return;
  }
}
function bdec_list($s) {
  if ($s[0] != "l")
    return;
  $sl = strlen($s);
  $i = 1;
  $v = array();
  $ss = "l";
  for (;;) {
    if ($i >= $sl)
      return;
    if ($s[$i] == "e")
      break;
    $ret = bdec(substr($s, $i));
    if (!isset($ret) || !is_array($ret))
      return;
    $v[] = $ret;
    $i += $ret["strlen"];
    $ss .= $ret["string"];
  }
  $ss .= "e";
  return array('type' => "list", 'value' => $v, 'strlen' => strlen($ss), 'string' => $ss);
}
function bdec_dict($s) {
  if ($s[0] != "d")
    return;
  $sl = strlen($s);
  $i = 1;
  $v = array();
  $ss = "d";
  for (;;) {
    if ($i >= $sl)
      return;
    if ($s[$i] == "e")
      break;
    $ret = bdec(substr($s, $i));
    if (!isset($ret) || !is_array($ret) || $ret["type"] != "string")
      return;
    $k = $ret["value"];
    $i += $ret["strlen"];
    $ss .= $ret["string"];
    if ($i >= $sl)
      return;
    $ret = bdec(substr($s, $i));
    if (!isset($ret) || !is_array($ret))
      return;
    $v[$k] = $ret;
    $i += $ret["strlen"];
    $ss .= $ret["string"];
  }
  $ss .= "e";
  return array('type' => "dictionary", 'value' => $v, 'strlen' => strlen($ss), 'string' => $ss);
}
   function utf8_to_win($string){
        for ($c=0;$c<strlen($string);$c++){
            $i=ord($string[$c]);
            if ($i <= 127) @$out .= $string[$c];
                if (@$byte2){
                    $new_c2=($c1&3)*64+($i&63);
                    $new_c1=($c1>>2)&5;
                    $new_i=$new_c1*256+$new_c2;
                    if ($new_i==1025){
                        $out_i=168;
                    } else {
                       if ($new_i==1105){
                            $out_i=184;
                        } else {
                            $out_i=$new_i-848;
                        }
                    }
                    @$out .= chr($out_i);
                    $byte2 = false;
                }
                if (($i>>5)==6) {
                    $c1 = $i;
                    $byte2 = true;
                }
        }
        return $out;
    }

function base32_encode ($inString) 
{ 
    $outString = ""; 
    $compBits = ""; 
    $BASE32_TABLE = array( 
                          '00000' => 0x61, 
                          '00001' => 0x62, 
                          '00010' => 0x63, 
                          '00011' => 0x64, 
                          '00100' => 0x65, 
                          '00101' => 0x66, 
                          '00110' => 0x67, 
                          '00111' => 0x68, 
                          '01000' => 0x69, 
                          '01001' => 0x6a, 
                          '01010' => 0x6b, 
                          '01011' => 0x6c, 
                          '01100' => 0x6d, 
                          '01101' => 0x6e, 
                          '01110' => 0x6f, 
                          '01111' => 0x70, 
                          '10000' => 0x71, 
                          '10001' => 0x72, 
                          '10010' => 0x73, 
                          '10011' => 0x74, 
                          '10100' => 0x75, 
                          '10101' => 0x76, 
                          '10110' => 0x77, 
                          '10111' => 0x78, 
                          '11000' => 0x79, 
                          '11001' => 0x7a, 
                          '11010' => 0x32, 
                          '11011' => 0x33, 
                          '11100' => 0x34, 
                          '11101' => 0x35, 
                          '11110' => 0x36, 
                          '11111' => 0x37, 
                          ); 
    
    /* Turn the compressed string into a string that represents the bits as 0 and 1. */
    for ($i = 0; $i < strlen($inString); $i++) {
        $compBits .= str_pad(decbin(ord(substr($inString,$i,1))), 8, '0', STR_PAD_LEFT);
    }
    
    /* Pad the value with enough 0's to make it a multiple of 5 */
    if((strlen($compBits) % 5) != 0) {
        $compBits = str_pad($compBits, strlen($compBits)+(5-(strlen($compBits)%5)), '0', STR_PAD_RIGHT);
    }
    
    /* Create an array by chunking it every 5 chars */
    $fiveBitsArray = preg_split("/\n/",rtrim(chunk_split($compBits, 5, "\n"))); 
    
    /* Look-up each chunk and add it to $outstring */
    foreach($fiveBitsArray as $fiveBitsString) { 
        $outString .= chr($BASE32_TABLE[$fiveBitsString]); 
    } 
    
    return $outString; 
} 

