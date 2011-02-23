<?php
define('IN_PHPBB', TRUE);
//define('IN_AJAX', TRUE);
define('BB_ROOT', './');
require(BB_ROOT .'common.php');
require(BB_ROOT .'attach_mod/attachment_mod.'. PHP_EXT);
require(INC_DIR .'functions_torrent.'. PHP_EXT);
require(INC_DIR .'base32.'. PHP_EXT);
require(INC_DIR .'links_build.'. PHP_EXT);

$user->session_start();

require(LANG_DIR.'lang_main.'. PHP_EXT);

$attach_id = isset($_GET['a']) ? (int) $_GET['a'] : 0;

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
       
$torrent = get_torrent_info($attach_id);
         
$filename = $phpbb_root_path . $attach_config['upload_dir'] .'/'. $torrent['physical_filename'];
unset($torrent);

$tor = bdecode_file($filename);

$info =& $tor['info'];

$html  = '<table width="95%" cellpadding="2" cellspacing="1" class="attach bordered med" align="center">';
$html .= '<tbody><tr class="row2">';
$html .= '<td class="genmed" align="center"> '.(!empty($info["length"]) ? "File" : "Folder").'</td>';
$html .= '<td class="genmed" align="left"> '.$info["name"].'</td>';
//hashes
$sha1  = isset($info["sha1"]) ? strtoupper(base32_encode($info["sha1"])) : '';
$ed2k  = isset($info["ed2k"]) ? bin2hex($info["ed2k"]) : '';
$tiger = isset($info["tiger"]) ? strtoupper(base32_encode($info["tiger"])) : '';
$md5   = isset($info["md5sum"]) ? bin2hex($info["md5sum"]) : '';
//end
$ext_hashes = (isset($info["ed2k"]) || isset($info["sha1"]) || isset($info["tiger"]) || isset($info["md5sum"]));
if ($ext_hashes && isset($info["length"])) 
{
	if (isset($info["ed2k"])) 
	{
		$html .= '<td class="genmed" align="center"><a href="'.create_ed2k(($info["name"]), $info["length"], bin2hex($info["ed2k"])).'"><b>ED2K</b></a>, ';	
	}
	else 
	{
		$html .= '<td class="genmed" align="center">';
	}
	$html .= '<a href="'.@create_magnet(($info["name"]), $info["length"], '', $sha1, $ed2k, $tiger, $md5).'"><b>Magnet</b></a></td>';	
}

$fhtml = '';
if (!empty($info['files']) && is_array($info['files']))
{	
	$ext_hashes = false;
    foreach ($info['files'] as $fn => $f)
    {
		$fhtml .= '<tr class="row1">';
        $fhtml .= '<td class="genmed" align="center"> '.($fn+1).'</td>';
        $fhtml .= '<td class="genmed" align="left"> '.implode('/',$f['path']).'</td>';
		//hashes
		$sha1  = isset($f["sha1"]) ? strtoupper(base32_encode($f["sha1"])) : '';
		$ed2k  = isset($f["ed2k"]) ? bin2hex($f["ed2k"]) : '';
		$tiger = isset($f["tiger"]) ? strtoupper(base32_encode($f["tiger"])) : '';
		$md5   = isset($f["md5sum"]) ? bin2hex($f["md5sum"]) : '';
		//hashes is set?
		$ext_hashes = (isset($f["ed2k"]) || isset($f["sha1"]) || isset($f["tiger"]) || isset($f["md5sum"]));
		if ($ext_hashes && isset($f["length"])) 
		{
			if (isset($f["ed2k"])) 
			{
				$fhtml .= '<td class="genmed" align="center"><a href="'.create_ed2k(max($f['path']), $f["length"], bin2hex($f["ed2k"])).'"><b>ED2K</b></a>, ';	
			}
			else 
			{
				$fhtml .= '<td class="genmed" align="center">';
			}
			$fhtml .= '<a href="'.@create_magnet(max($f['path']), $f["length"], '', $sha1, $ed2k, $tiger, $md5).'"><b>Magnet</b></a></td>';	
		}
        $fhtml .= '<td class="genmed" align="right"> '.humn_size($f['length']).'</td>';
        $fhtml .= '</tr>';				
    }
	if($ext_hashes)
	{
		$html .= '<td class="genmed" align="center"></td>';
	}
}
$html .= '<td class="genmed" align="right"> '.(!empty($info["length"]) ? humn_size($info["length"]) : 'Size').'</td>';
$html .= '</tr>';
$html .= $fhtml;
unset($fhtml);

$html .= '</tbody></table>';
	
// Escape data
$html = str_replace("'", "\'", $html);
$html = str_replace ("\r\n", '\n', $html);
$html = str_replace ("\r", '\n', $html);
$html = str_replace ("\n", '\n', $html);

echo utf8_to_win($html);