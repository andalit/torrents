<?php
/*
## P2P links builder. Written by RoadTrain, 26 July 2008; Updated 10 Jan 2009
*/
function create_magnet($dn, $xl = false, $btih = '', $sha1 = '', $ed2k = '', $tree_tiger = '', $md5 = '')
{
	$magnet = 'magnet:?';
	if ($dn){
		$magnet .= 'dn=' . $dn; // download name
	}
	if ($xl){
		$magnet .= '&xl=' . $xl; // size
	}
	if ($btih){
		$magnet .= '&xt=urn:btih:' . $btih; // bittorrent info_hash (Base32)
	}
	if ($sha1){
		$magnet .= '&xt=urn:sha1:' . $sha1; // gnutella sha1 (base32)
	}
	if ($ed2k){
		$magnet .= '&xt=urn:ed2k:' . $ed2k; // emule hash (Hex)
	}
	if ($tree_tiger){
		$magnet .= '&xt=urn:tree:tiger:' . $tree_tiger; // tiger (Base32)
	}
	if ($sha1 && $tree_tiger) {
		$magnet .= '&xt=urn:bitprint:' . $sha1 . '.' . $tree_tiger; // Gnutella 2 (Shareaza) bitprint (Base32)
	}
	if ($md5){
		$magnet .= '&xt=urn:md5:' . $md5; // md5 hash (Hex)
	}
	return $magnet;
}

function create_ed2k($fname, $fsize, $fhash){
	$ed2k_out = 'ed2k://|file|' . $fname . '|' . $fsize . '|' . $fhash . '|/';
	return $ed2k_out;
}
?>