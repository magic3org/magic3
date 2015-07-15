<?php
/**
 * GoogleMapsプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
/* Pukiwiki GoogleMaps plugin 3.2.0
 * http://reddog.s35.xrea.com
 * -------------------------------------------------------------------
 * Copyright (c) 2005-2014 OHTSUKA, Yoshio
 * This program is free to use, modify, extend at will. The author(s)
 * provides no warrantees, guarantees or any responsibility for usage.
 * Redistributions in any form must retain this copyright notice.
 * ohtsuka dot yoshio at gmail dot com
 * -------------------------------------------------------------------
 * 変更履歴はgooglemaps3.inc.php
 */

define ('PLUGIN_GOOGLEMAPS3_MK_DEF_TITLE', '名称未設定'); //マーカーの名前
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_CAPTION', '');         //マーカーのキャプション
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_NOLIST', false);       //マーカーのリストを出力しない
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_NOINFOWINDOW', false); //マーカーのinfoWindowを表示しない
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_ZOOM', null);          //マーカーの初期zoom値。nullは初期値無し。
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_MINZOOM',  0);         //マーカーが表示される最小ズームレベル
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_MAXZOOM', 17);         //マーカーが表示される最大ズームレベル
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_ICON', '');            //アイコン。空の時はデフォルト
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_NOICON', false);       //アイコンを表示しない。
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_FLAT', false);         //アイコンを影なしにする。
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_AJUMP', '[LIST]');     //infoWindowから本文中へのリンク文字
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_TITLEISPAGENAME', false); //title省略時にページ名を使う。

//FORMATLISTはhtmlに出力されるマーカーのリストの雛型
//FMTINFOはマップ上のマーカーをクリックして表示されるフキダシの（中の）雛型
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_FORMATLIST' , '<b>%title%</b> - %caption% ');
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_FORMATINFO' , '<b>%title%</b><br/><span style=\'float:left; padding-right: 3px; padding-bottom: 3px;\'>%image%</span>%caption%');

//リストをクリックするとマップにフォーカスさせる。(0 or 1)
define ('PLUGIN_GOOGLEMAPS3_MK_DEF_ALINK' , 1);

function plugin_googlemaps3_mark_get_default () {
	global $vars, $script;

	return array (
		'title'        => PLUGIN_GOOGLEMAPS3_MK_DEF_TITLE,
		'caption'      => PLUGIN_GOOGLEMAPS3_MK_DEF_CAPTION,
		'image'        => '',
		'icon'         => PLUGIN_GOOGLEMAPS3_MK_DEF_ICON,
		'nolist'       => PLUGIN_GOOGLEMAPS3_MK_DEF_NOLIST,
		'noinfowindow' => PLUGIN_GOOGLEMAPS3_MK_DEF_NOINFOWINDOW,
		'noicon'       => PLUGIN_GOOGLEMAPS3_MK_DEF_NOICON,
		'flat'       => PLUGIN_GOOGLEMAPS3_MK_DEF_FLAT,
		'zoom'         => PLUGIN_GOOGLEMAPS3_MK_DEF_ZOOM,
		'maxzoom'      => PLUGIN_GOOGLEMAPS3_MK_DEF_MAXZOOM,
		'minzoom'      => PLUGIN_GOOGLEMAPS3_MK_DEF_MINZOOM,
		'map'          => PLUGIN_GOOGLEMAPS3_DEF_MAPNAME,
		'formatlist'   => PLUGIN_GOOGLEMAPS3_MK_DEF_FORMATLIST,
		'formatinfo'   => PLUGIN_GOOGLEMAPS3_MK_DEF_FORMATINFO,
		'alink'        => PLUGIN_GOOGLEMAPS3_MK_DEF_ALINK,
		'titleispagename' => PLUGIN_GOOGLEMAPS3_MK_DEF_TITLEISPAGENAME,
	);
}

function plugin_googlemaps3_mark_convert() {
	$args = func_get_args();
	if (sizeof($args)<2) {
		return "error: plugin googlemaps3_mark wrong args\n";
	}
	return plugin_googlemaps3_mark_output($args[0], $args[1], array_slice($args, 2));
}

function plugin_googlemaps3_mark_inline() {
	$args = func_get_args();
	array_pop($args);
	if (sizeof($args)<2) {
		return "error: plugin googlemaps3_mark wrong args\n";
	}
	return plugin_googlemaps3_mark_output($args[0], $args[1], array_slice($args, 2));
}

function plugin_googlemaps3_mark_output($lat, $lng, $params) {
	global $vars;
	
	if (!defined('PLUGIN_GOOGLEMAPS3_DEF_MAPNAME')) {
		return "googlemaps3_mark: error googlemapsを先に呼び出してください。<br/>";
	}

	$defoptions = plugin_googlemaps3_mark_get_default();

	$inoptions = array();
	foreach ($params as $param) {
		list($index, $value) = split('=', $param);
		$index = trim($index);
		$value = htmlspecialchars(trim($value));
		$inoptions[$index] = $value;
	}

	if (array_key_exists('define', $inoptions)) {
		$vars['googlemaps3_mark'][$inoptions['define']] = $inoptions;
		return "";
	}

	$coptions = array();
	if (array_key_exists('class', $inoptions)) {
		$class = $inoptions['class'];
		if (array_key_exists($class, $vars['googlemaps3_mark'])) {
			$coptions = $vars['googlemaps3_mark'][$class];
		}
	}

	$options = array_merge($defoptions, $coptions, $inoptions);
	$lat = trim($lat);
	$lng = trim($lng);
	$title        = $options['title'];
	$caption      = $options['caption'];
	$image        = $options['image'];
	$icon         = $options['icon'];
	$nolist       = plugin_googlemaps3_getbool($options['nolist']);
	$noinfowindow = plugin_googlemaps3_getbool($options['noinfowindow']);
	$noicon       = plugin_googlemaps3_getbool($options['noicon']);
	$flat         = plugin_googlemaps3_getbool($options['flat']);
	$zoom         = $options['zoom'];
	$maxzoom      = (int)$options['maxzoom'];
	$minzoom      = (int)$options['minzoom'];
	$map          = plugin_googlemaps3_addprefix($vars['page'], $options['map']);
	//XSS対策のため次の二つのオプションはユーザーから設定不可にする。
	$formatlist   = $defoptions['formatlist'];
	$formatinfo   = $defoptions['formatinfo'];
	$alink        = $options['alink'];
	$titleispagename = plugin_googlemaps3_getbool($options['titleispagename']);
	$api = $vars['googlemaps3_info'][$map]['api'];

	if ($nolist) {
		$alink = false;
	}

	if ($titleispagename) {
		$title = $vars['page'];
	}

	if ($maxtitle == '') {
		$maxtitle = $title;
	}

	//携帯デバイス用リスト出力
	if (!plugin_googlemaps3_is_supported_profile()) {
		if ($nolist == false) {
			return plugin_googlemaps_mark_simple_format_listhtml(
				$formatlist, $title, $caption);
		}
		return '';
	}

	$page = $vars['page'];

	if ($zoom == null) $zoom = 'null';
	
	if ($noicon == true) {
		$noinfowindow = true;
	}

	//Pukiwikiの添付された画像の表示
	$q = '/^http:\/\/.*(\.jpg|\.gif|\.png)$/i';
	if ($image != '' && !preg_match($q, $image)) {
		$image = $script.'?plugin=ref'.'&page='.
		rawurlencode($vars["page"]).'&src='.rawurlencode($image);
	}
	if ($noinfowindow == false) {
		$infohtml = plugin_googlemaps_mark_format_infohtml(
			$map, $formatinfo, $alink,
			$title, $caption, $image);
	} else {
		$infohtml = null;
	}

	$key = "$map,$lat,$lng";

	if ($nolist == false) {
		$listhtml = plugin_googlemaps_mark_format_listhtml(
			$page, $map, $formatlist, $alink,
			$key, $infohtml, 
			$title, $caption, $image,
			$zoom);
	}

	if ($noicon == true) {
		$noiconstrval = "true";
	} else {
		$noiconstrval = "false";
	}

	if ($icon != "" && $flat == true) {
		$flat = "true";
	} else {
		$flat = "false";
	}

	// Create Marker
	$output = <<<EOD
<script type="text/javascript">
//<![CDATA[
onloadfunc.push(function() {
	p_googlemaps_regist_marker ('$page', '$map', new google.maps.LatLng($lat , $lng), '$key',
	{noicon: $noiconstrval, icon:'$icon', flat: $flat, zoom:$zoom, maxzoom:$maxzoom, minzoom:$minzoom, title:'$title', infohtml:'$infohtml' });
});
//]]>
</script>\n
EOD;

	//Show Markers List
	if ($nolist == false) {
		$output .= $listhtml;
	}

	return $output;
}

function plugin_googlemaps_mark_simple_format_listhtml($format, $title, $caption ) {
	$html = $format;
	$html = str_replace('%title%', $title, $html);
	$html = str_replace('%caption%', $caption, $html);
	$html = str_replace('%image%', '', $html);
	return $html;
}

function plugin_googlemaps_mark_format_listhtml($page, $map, $format, $alink, 
	$key, $infohtml, $title, $caption, $image, $zoomstr ) {

	if ($alink == true) {
		$atag = "<a id=\"".$map."_%title%\"></a>";
		$atag .= "<a href=\"#$map\"";
	}
	
	$atag .= " onclick=\"googlemaps_markers['$page']['$map']['$key'].onclick();\">%title%</a>";

	$html = $format;
	if ($alink == true) {
		$html = str_replace('%title%', $atag , $html);
	}
	$html = str_replace('%title%', $title, $html);
	$html = str_replace('%caption%', $caption, $html);
	$html = str_replace('%image%', '<img src="'.$image.'" border=0/>', $html);
	return $html;
}

function plugin_googlemaps_mark_format_infohtml($map, $format, $alink, $title, $caption, $image) {

	$html = str_replace('\'', '\\\'', $format);
	if ($alink == true) {
		$atag = "%title% <a href=\\'#".$map."_%title%\\'>"
			.PLUGIN_GOOGLEMAPS3_MK_DEF_AJUMP.'</a>';
		$html = str_replace('%title%', $atag , $html);
	}
	$html = str_replace('%title%',$title , $html);
	$html = str_replace('%caption%', $caption, $html);

	if ($image != '') {
		$image = '<img src=\\\''.$image.'\\\' border=0/>';
	}
	$html = str_replace('%image%', $image, $html);

	return $html;
}

?>
