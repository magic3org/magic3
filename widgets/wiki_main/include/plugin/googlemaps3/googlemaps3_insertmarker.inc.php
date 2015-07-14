<?php /* vim: set ts=4 noexpandtab : */
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

define ('PLUGIN_GOOGLEMAPS3_INSERTMARKER_DIRECTION', 'down'); //追加していく方向(up, down)
define ('PLUGIN_GOOGLEMAPS3_INSERTMARKER_TITLE_MAXLEN', 40); //タイトルの最長の長さ
define ('PLUGIN_GOOGLEMAPS3_INSERTMARKER_CAPTION_MAXLEN', 400); //キャプションの最長の長さ
define ('PLUGIN_GOOGLEMAPS3_INSERTMARKER_URL_MAXLEN', 1024); //URLの最長の長さ

function plugin_googlemaps3_insertmarker_action() {
	global $script, $vars, $now, $_title_update;
	global $_title_comment_collided, $_msg_comment_collided;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');
	
	if(is_numeric($vars['lat'])) $lat = $vars['lat']; else return;
	if(is_numeric($vars['lng'])) $lng = $vars['lng']; else return;
	if(is_numeric($vars['zoom'])) $zoom = $vars['zoom']; else return;

	$map    = htmlspecialchars(trim($vars['map']));
	$icon   = htmlspecialchars($vars['icon']);
	$title   = substr($vars['title'], 0, PLUGIN_GOOGLEMAPS3_INSERTMARKER_TITLE_MAXLEN);
	$caption = substr($vars['caption'], 0, PLUGIN_GOOGLEMAPS3_INSERTMARKER_CAPTION_MAXLEN);
	$image   = substr($vars['image'], 0, PLUGIN_GOOGLEMAPS3_INSERTMARKER_URL_MAXLEN);

	$minzoom = $vars['minzoom'] == '' ? '' : (int)$vars['minzoom'];
	$maxzoom = $vars['maxzoom'] == '' ? '' : (int)$vars['maxzoom'];

	$title   = htmlspecialchars(str_replace("\n", '', $title));
	$caption = htmlspecialchars(str_replace("\n", '', $caption));
	$image   = htmlspecialchars($image);

	if ($map == '') return;
	$marker = '-&googlemaps3_mark('.$lat.', '.$lng.', map='.$map.', title='.$title;
	if ($caption != '') $marker .= ', caption='.$caption;
	if ($icon != '')    $marker .= ', icon='.$icon;
	if ($image != '')   $marker .= ', image='.$image;
	if ($minzoom != '')  $marker .= ', minzoom='.$minzoom;
	if ($maxzoom != '')  $marker .= ', maxzoom='.$maxzoom;
	$marker .= ');';
	
	$no       = 0;
	$postdata = '';
	$above    = ($vars['direction'] == 'up');
	foreach (get_source($vars['refer']) as $line) {
		if (! $above) $postdata .= $line;
		if (preg_match('/^#googlemaps3_insertmarker/i', $line) && $no++ == $vars['no']) {
			if ($above) {
				$postdata = rtrim($postdata) . "\n" . $marker . "\n";
			} else {
				$postdata = rtrim($postdata) . "\n" . $marker . "\n";
			}
		}
		if ($above) $postdata .= $line;
	}

	$title = $_title_updated;
	$body = '';
	if (md5(@join('', get_source($vars['refer']))) != $vars['digest']) {
		$title = $_title_comment_collided;
		$body  = $_msg_comment_collided . make_pagelink($vars['refer']);
	}

	page_write($vars['refer'], $postdata);

	$retvars['msg']  = $title;
	$retvars['body'] = $body;
	$vars['page'] = $vars['refer'];

	//表示していたポジションを返すcookieを追加
	$cookieval = 'lat|'.$lat.'|lng|'.$lng.'|zoom|'.$zoom;
	if ($minzoom) $cookieval .= '|minzoom|'.$minzoom;
	if ($maxzoom) $cookieval .= '|maxzoom|'.$maxzoom;
	setcookie('pukiwkigooglemaps3insertmarker'.$vars['no'], $cookieval);
	return $retvars;
}

function plugin_googlemaps3_insertmarker_get_default() {
	global $vars;
	return array(
		'map'       => PLUGIN_GOOGLEMAPS3_DEF_MAPNAME,
		'direction' => PLUGIN_3OOGLEMAPS2_INSERTMARKER_DIRECTION
	);
}
//inline型はテキストのパースがめんどくさそうなのでとりあえず放置。
function plugin_googlemaps3_insertmarker_inline() {
	return "<div>インライン型は未実装です。ブロック型の#googlemaps3_insertmarkerを使ってください。</div>\n";
}
function plugin_googlemaps3_insertmarker_convert() {
	global $vars, $digest, $script;
	static $numbers = array();

	if (!defined('PLUGIN_GOOGLEMAPS3_DEF_MAPNAME')) {
		return "googlemaps3_insertmarker: error googlemapsを先に呼び出してください。<br/>";
	}
	if (!plugin_googlemaps3_is_supported_profile()) {
		return '';
	}

	if (PKWK_READONLY) {
		return "read only<br>";
	}

	//オプション
	
	$defoptions = plugin_googlemaps3_insertmarker_get_default();
	$inoptions = array();
	foreach (func_get_args() as $param) {
		$pos = strpos($param, '=');
		if ($pos == false) continue;
		$index = trim(substr($param, 0, $pos));
		$value = htmlspecialchars(trim(substr($param, $pos+1)));
		$inoptions[$index] = $value;
	}

	if (array_key_exists('define', $inoptions)) {
		$vars['googlemaps3_insertmarker'][$inoptions['define']] = $inoptions;
		return '';
	}

	$coptions = array();
	if (array_key_exists('class', $inoptions)) {
		$class = $inoptions['class'];
		if (array_key_exists($class, $vars['googlemaps3_insertmarker'])) {
			$coptions = $vars['googlemaps3_icon'][$class];
		}
	}
	$options = array_merge($defoptions, $coptions, $inoptions);
	$map       = plugin_googlemaps3_addprefix($vars['page'], $options['map']);
	$mapname   = $options['map'];//ユーザーに表示させるだけのマップ名（prefix除いた名前）
	$direction = $options['direction'];
	$script    = get_script_uri();
	$s_page    = $vars['page'];

	if (! isset($numbers[$s_page]))
		$numbers[$s_page] = 0;
	$no = $numbers[$s_page]++;

	$imprefix = "_p_googlemaps3_insertmarker_".$s_page."_".$no;
	$output = <<<EOD
<form action="$script" id="${imprefix}_form" method="post">
<div style="padding:2px;">
  <input type="hidden" name="plugin"    value="googlemaps3_insertmarker" />
  <input type="hidden" name="refer"     value="$s_page" />
  <input type="hidden" name="direction" value="$direction" />
  <input type="hidden" name="no"        value="$no" />
  <input type="hidden" name="digest"    value="$digest" />
  <input type="hidden" name="map"       value="$mapname" />
  <input type="hidden" name="zoom"      value="10" id="${imprefix}_zoom"/>

  LAT: <input type="text" name="lat" id="${imprefix}_lat" size="10" />
  LNG: <input type="text" name="lng" id="${imprefix}_lng" size="10" />
  タイトル:
  <input type="text" name="title"    id="${imprefix}_title" size="20" />
  アイコン:
  <select name="icon" id ="${imprefix}_icon">
  <option value="Default">Default</option>
  </select>
  <br />
  画像:
  <input type="text" name="image"    id="${imprefix}_image" size="20" />
  表示最小ズーム:
  <select name="minzoom" id ="${imprefix}_minzoom">
  <option value="">--</option>
  <option value="0"> 0</option> <option value="1"> 1</option>
  <option value="2"> 2</option> <option value="3"> 3</option>
  <option value="4"> 4</option> <option value="5"> 5</option>
  <option value="6"> 6</option> <option value="7"> 7</option>
  <option value="8"> 8</option> <option value="9"> 9</option>
  <option value="10">10</option> <option value="11">11</option>
  <option value="12">12</option> <option value="13">13</option>
  <option value="14">14</option> <option value="15">15</option>
  <option value="16">16</option> <option value="17">17</option>
  </select>
  表示最大ズーム:
  <select name="maxzoom" id ="${imprefix}_maxzoom">
  <option value="">--</option>
  <option value="0"> 0</option> <option value="1"> 1</option>
  <option value="2"> 2</option> <option value="3"> 3</option>
  <option value="4"> 4</option> <option value="5"> 5</option>
  <option value="6"> 6</option> <option value="7"> 7</option>
  <option value="8"> 8</option> <option value="9"> 9</option>
  <option value="10">10</option> <option value="11">11</option>
  <option value="12">12</option> <option value="13">13</option>
  <option value="14">14</option> <option value="15">15</option>
  <option value="16">16</option> <option value="17">17</option>
  </select>
  <br />
  詳細:
  <textarea name="caption" id="${imprefix}_caption" rows="2" cols="55"></textarea>
  <input type="submit" name="Mark" value="Mark"/>
</div>
</form>

<script type="text/javascript">
//<![CDATA[
onloadfunc.push(function() {
	var map = googlemaps_maps['$s_page']['$map'];
	if (!map) {
		var form = document.getElementById("${imprefix}_form");
		form.innerHTML = '<div>$mapname という名前のマップが見つかりませんでした。</div>';
	} else {
		var lat   = document.getElementById("${imprefix}_lat");
		var lng   = document.getElementById("${imprefix}_lng");
		var zoom  = document.getElementById("${imprefix}_zoom");
		var form  = document.getElementById("${imprefix}_form");
		var icon  = document.getElementById("${imprefix}_icon");

		//地図がドラッグされたりするたびに動的にパラメータを代入する
		google.maps.event.addListener(map, 'center_changed', function() {
			lat.value = PGTool.fmtNum(map.getCenter().lat());
			lng.value = PGTool.fmtNum(map.getCenter().lng());
			zoom.value = parseInt(map.getZoom());
		});
		
		//クッキーがあれば地図の位置を初期化をする。使い終えたらクッキーの中身をクリアする。
		(function () {
			var cookies = document.cookie.split(";");
			for (i in cookies) {
				var kv = cookies[i].split("=");
				for (j in kv) {
					kv[j] = kv[j].replace(/^\s+|\s+$/g, "");
				}
				if (kv[0] == "pukiwkigooglemaps3insertmarker$no") {
					if (kv.length == 2 && kv[1].length > 0) {
						var mparam = {lat:0, lng:0, zoom:10};
						var oparam = {maxzoom:"", minzoom:""};
						var params = decodeURIComponent(kv[1]).split("|");
						for (var j = 0; j < params.length; j++) {
							//dump(params[j] + "=" + params[j+1] + "\\n");
							switch (params[j]) {
								case "lat": mparam.lat = parseFloat(params[++j]); break;
								case "lng": mparam.lng = parseFloat(params[++j]); break;
								case "zoom": mparam.zoom = parseInt(params[++j]); break;
								case "maxzoom": oparam.maxzoom = parseInt(params[++j]); break;
								case "minzoom": oparam.minzoom = parseInt(params[++j]); break;
								default: j++; break;
							}
						}
						map.setCenter(new google.maps.LatLng(mparam.lat, mparam.lng));
						map.setZoom(mparam.zoom);

						var smz;
						var options;
						smz = document.getElementById("${imprefix}_minzoom")
						options = smz.childNodes;
						for (var j=0; j<options.length; j++) {
							var option = options.item(j);
							if (option.value == oparam.minzoom) {
								option.selected = true;
								break;
							}
						}

						smz = document.getElementById("${imprefix}_maxzoom")
						options = smz.childNodes;
						for (var j=0; j<options.length; j++) {
							var option = options.item(j);
							if (option.value == oparam.maxzoom) {
								option.selected = true;
								break;
							}
						}
					}
					break;
				}
			}
			document.cookie = "pukiwkigooglemaps3insertmarker$no=;";
		})();

		//入力チェック
		form.onsubmit = function () {
			if (isNaN(parseFloat(lat.value)) || isNaN(lat.value) || 
				isNaN(parseFloat(lng.value)) || isNaN(lng.value)) {
				alert("座標データが不正です。 LAT : " + lat.value + "  LNG : " + lng.value);
				return false;
			}
			return true;
		};
	}
	//このページに存在しているicon定義を全て読みこんでセレクトを更新。
	onloadfunc.push(function() {
		for(iconname in googlemaps_icons['$s_page']) {
			var opt = document.createElement("option");
			opt.value = iconname;
			opt.appendChild(document.createTextNode(iconname));
			icon.appendChild(opt);
		}
	});
});
//]]>
</script>
EOD;

	return $output;
}


?>
