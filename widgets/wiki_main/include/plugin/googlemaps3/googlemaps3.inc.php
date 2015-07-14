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
 * 2005-09-25 1.1   -Release
 * 2006-04-20 2.0   -GoogleMaps API ver2
 * 2006-07-15 2.1   -googlemaps2_insertmarker.inc.phpを追加。usetoolオプションの廃止。
 *                   ブロック型の書式を使えるようにした。
 *                  -googlemaps2にdbclickzoom, continuouszoomオプションを追加。
 *                  -googlemaps2_markのimageオプションで添付画像を使えるようにした。
 *                  -OverViewMap, マウスクリック操作の改良。
 *                  -XSS対策。googlemaps2_markのformatlist, formatinfoの廃止。
 *                  -マーカーのタイトルをツールチップで表示。
 *                  -アンカー名にpukiwikigooglemaps2_というprefixをつけるようにした。
 * 2006-07-29 2.1.1 -includeやcalender_viewerなど複数のページを一つのページにまとめて
 *                   出力するプラグインでマップが表示されないバグを修正。
 * 2006-08-24 2.1.2 -単語検索でマーカー名がハイライトされた時のバグを修正。
 * 2006-09-30 2.1.3 -携帯電話,PDAなど非対応のデバイスでスクリプトを出力しないようにした。
 * 2006-12-30 2.2   -マーカーのフキダシを開く時に画像の読み込みを待つようにした。
 *                  -GMapTypeControlを小さく表示。
 *                  -GoogleのURLをmaps.google.comからmaps.google.co.jpに。
 *                  -googlemaps2にgeoctrl, crossctrlオプションの追加。
 *                  -googlemaps2_markにmaxurl, minzoom, maxzoomオプションの追加。
 *                  -googlemaps2_insertmarkerでimage, maxurl, minzoom, maxzoomを入力可能に。
 *                  -googlemaps2_drawにfillopacity, fillcolor, inradiusオプションの追加。
 *                  -googlemaps2_drawにpolygonコマンドの追加。
 * 2007-01-10 2.2.1 -googlemaps2のoverviewctrlのパラメータで地図が挙動不審になるバグを修正。
 *                  -googlemaps2_insertmarkerがincludeなどで複数同時に表示されたときの挙動不審を修正
 * 2007-01-22 2.2.2 -googlemaps2のwidth, heightで単位が指定されていないときは暗黙にpxを補う。
 *                  -googlemaps2のoverviewtypeにautoを追加。地図のタイプにオーバービューが連動。
 * 2007-01-31 2.2.3 -googlemaps2でcrossコントロール表示時にフキダシのパンが挙動不審なのを修正。
 *                  -GoogleのロゴがPukiwikiのCSSによって背景を透過しない問題を暫定的に修正。
 * 2007-08-04 2.2.4 -IEで図形を描画できないバグを修正。
 *                  -googlemaps2にgeoxmlオプションの追加。
 * 2007-09-25 2.2.5 -geoxmlでエラーがあるとinsertmarkerが動かないバグを修正。
 * 2007-12-01 2.3.0 -googlemaps2のgeoctrl, overviewtypeオプションの廃止
 *                  -googlemaps2にgooglebar, importicon, backlinkmarkerオプションの追加
 *                  -googlemaps2_markのmaxurlオプションの廃止。（一時的にmaxcontentにマッピングした）
 *                  -googlemaps2_markにmaxcontent, maxtitle, titleispagenameオプションを追加。
 * 2008-10-21 2.3.1 -apiのバージョンを2.132dに固定した。
 * 2012-10-28 3.0.0 GoogleMaps API v3 (3.10)
 *                  -プラグインの名称をgooglemaps3に変更した
 *                  -googlemaps3
 *                      -廃止オプション
 *                          -key
 *                          -api
 *                          -mapctrl        zoomctrlとpanctrlに分離した
 *                          -overviewwidth  v3ではサイズ変更できないみたい
 *                          -overviewheight v3ではサイズ変更できないみたい
 *                          -continuouszoom
 *                          -googlebar		searchctrlになりました
 *                          -geoxml         kmlになりました
 *                      -追加オプション
 *                          -zoomctrl       拡縮コントロール
 *                          -panctrl        移動コントロール
 *                          -rotatectrl     45度回転コントロール(45度回転に地図が対応してないとダメ)
 *                          -streetviewctrl ストリートビューコントロール
 *                          -searchctrl		検索ボックス
 *                          -kml            KMLファイルへのURL,添付ファイル名      
 *                      -変更オプション
 *                          -type           (追加)roadmap, terrain
 *                          -typectrl       (追加)horizontal, dropdown
 *                          -crossctrl      (追加)normal
 *                                          (廃止)show
 *                  -googlemaps3_mark
 *                      -廃止オプション
 *                          -maxcontent    v3で無くなった
 *                          -maxtitle      v3で無くなった
 *                          -maxurl        v3で無くなった
 *                      -追加オプション
 *                          -flat           アイコンの影を無くす
 *
 *                  -googlemaps3_icon
 *                      -変更なし
 *
 * 2012-12-01 3.1.0 GoogleMaps API v3 (3.10)
 *                  -googlemaps3
 *                      -kmlオプションの追加。(旧geoxml)
 *
 * 2013-07-21 3.1.1 -googlemaps3
 *                      -マーカーのズームが機能しなかった問題を修正した
 *
 * 2014-01-06 3.2.0 -googlemaps3
 *                      -preserveviewportオプションの追加。
 *  
 */

define ('PLUGIN_GOOGLEMAPS3_DEF_MAPNAME', 'googlemaps3');     //Map名
define ('PLUGIN_GOOGLEMAPS3_DEF_WIDTH'  , '400px');           //横幅
define ('PLUGIN_GOOGLEMAPS3_DEF_HEIGHT' , '400px');           //縦幅
define ('PLUGIN_GOOGLEMAPS3_DEF_LAT'    ,  35.036198);        //経度
define ('PLUGIN_GOOGLEMAPS3_DEF_LNG'    ,  135.732103);       //緯度
define ('PLUGIN_GOOGLEMAPS3_DEF_ZOOM'   ,  13);       //ズームレベル
define ('PLUGIN_GOOGLEMAPS3_DEF_TYPE'   ,  'roadmap'); //マップのタイプ(normal, roadmap, satellite, hybrid, terrain) normal=roadmap
define ('PLUGIN_GOOGLEMAPS3_DEF_MAPCTRL'       , 'none');     //【廃止】マップコントロール(none,smallzoom,small,normal,large)
define ('PLUGIN_GOOGLEMAPS3_DEF_ZOOMCTRL'      , 'normal');   //拡縮コントロール(none,normal,small,large)
define ('PLUGIN_GOOGLEMAPS3_DEF_PANCTRL'       , 'none');   //移動コントロール(none,normal)
define ('PLUGIN_GOOGLEMAPS3_DEF_TYPECTRL'      , 'dropdown'); //maptype切替コントロール(none, normal, horizontal, dropdown)
define ('PLUGIN_GOOGLEMAPS3_DEF_SCALECTRL'     , 'none');  //定規コントロール(none, normal)
define ('PLUGIN_GOOGLEMAPS3_DEF_ROTATECTRL'    , 'none');  //45度回転コントロール(none, normal)
define ('PLUGIN_GOOGLEMAPS3_DEF_STREETVIEWCTRL', 'none');  //ストリートビューコントロール(none, normal)
define ('PLUGIN_GOOGLEMAPS3_DEF_OVERVIEWCTRL'  , 'none');  //オーバービューマップ(none, hide, show)
define ('PLUGIN_GOOGLEMAPS3_DEF_CROSSCTRL'     , 'none');  //センタークロスコントロール(none, normal)
define ('PLUGIN_GOOGLEMAPS3_DEF_SEARCHCTRL'    , 'none');  //検索ボックスコントロール(none, normal)
define ('PLUGIN_GOOGLEMAPS3_DEF_TOGGLEMARKER', false);     //マーカーの表示切替チェックの表示
define ('PLUGIN_GOOGLEMAPS3_DEF_NOICONNAME'  , 'Unnamed'); //アイコン無しマーカーのラベル
define ('PLUGIN_GOOGLEMAPS3_DEF_DBCLICKZOOM' , true);      //ダブルクリックでズームする(true, false)
define ('PLUGIN_GOOGLEMAPS3_DEF_KML'         , '');        //読み読むKMLファイルのURLかページに添付されたファイル名
define ('PLUGIN_GOOGLEMAPS3_DEF_PRESERVEVIEWPORT', false); //KMLレイヤー表示時にビューポートの設定を変更しない
define ('PLUGIN_GOOGLEMAPS3_DEF_IMPORTICON', '');          //アイコンを取得するPukiwikiページ
define ('PLUGIN_GOOGLEMAPS3_DEF_BACKLINKMARKER', false);   //バックリンクでマーカーを集める

//Pukiwikiは1.4.5から携帯電話などのデバイスごとにプロファイルを用意して
//UAでスキンを切り替えて表示できるようになったが、この定数ではGoogleMapsを
//表示可能なプロファイルを設定する。
//対応デバイスのプロファイルをカンマ(,)区切りで記入する。
//Pukiwiki1.4.5以降でサポートしてるデフォルトのプロファイルはdefaultとkeitaiの二つ。
//ユーザーが追加したプロファイルがあり、それもGoogleMapsが表示可能なデバイスなら追加すること。
//またデフォルトのプロファイルを"default"以外の名前にしている場合も変更すること。
//注:GoogleMapsは携帯電話で表示できない。
define ('PLUGIN_GOOGLEMAPS3_PROFILE', 'default');

function plugin_googlemaps3_is_supported_profile () {
	if (defined("UA_PROFILE")) {
		return in_array(UA_PROFILE, preg_split('/[\s,]+/', PLUGIN_GOOGLEMAPS3_PROFILE));
	} else {
		return 1;
	}
}

function plugin_googlemaps3_get_default () {
	global $vars;
	return array(
		'mapname'          => PLUGIN_GOOGLEMAPS3_DEF_MAPNAME,
		'width'            => PLUGIN_GOOGLEMAPS3_DEF_WIDTH,
		'height'           => PLUGIN_GOOGLEMAPS3_DEF_HEIGHT,
		'lat'              => PLUGIN_GOOGLEMAPS3_DEF_LAT,
		'lng'              => PLUGIN_GOOGLEMAPS3_DEF_LNG,
		'zoom'             => PLUGIN_GOOGLEMAPS3_DEF_ZOOM,
		'mapctrl'          => PLUGIN_GOOGLEMAPS3_DEF_MAPCTRL, //廃止
		'zoomctrl'         => PLUGIN_GOOGLEMAPS3_DEF_ZOOMCTRL,
		'panctrl'          => PLUGIN_GOOGLEMAPS3_DEF_PANCTRL,
		'type'             => PLUGIN_GOOGLEMAPS3_DEF_TYPE,
		'typectrl'         => PLUGIN_GOOGLEMAPS3_DEF_TYPECTRL,
		'scalectrl'        => PLUGIN_GOOGLEMAPS3_DEF_SCALECTRL,
		'rotatectrl'       => PLUGIN_GOOGLEMAPS3_DEF_ROTATECTRL,
		'streetviewctrl'   => PLUGIN_GOOGLEMAPS3_DEF_STREETVIEWCTRL,
		'overviewctrl'     => PLUGIN_GOOGLEMAPS3_DEF_OVERVIEWCTRL,
		'crossctrl'        => PLUGIN_GOOGLEMAPS3_DEF_CROSSCTRL,
		'searchctrl'       => PLUGIN_GOOGLEMAPS3_DEF_SEARCHCTRL,
		'overviewwidth'    => PLUGIN_GOOGLEMAPS3_DEF_OVERVIEWWIDTH,  //廃止
		'overviewheight'   => PLUGIN_GOOGLEMAPS3_DEF_OVERVIEWHEIGHT, //廃止
		'togglemarker'     => PLUGIN_GOOGLEMAPS3_DEF_TOGGLEMARKER,
		'noiconname'       => PLUGIN_GOOGLEMAPS3_DEF_NOICONNAME,
		'dbclickzoom'      => PLUGIN_GOOGLEMAPS3_DEF_DBCLICKZOOM,
		'kml'              => PLUGIN_GOOGLEMAPS3_DEF_KML,
		'preserveviewport' => PLUGIN_GOOGLEMAPS3_DEF_PRESERVEVIEWPORT,
		'importicon'       => PLUGIN_GOOGLEMAPS3_DEF_IMPORTICON,
		'backlinkmarker'   => PLUGIN_GOOGLEMAPS3_DEF_BACKLINKMARKER,
	);
}

function plugin_googlemaps3_convert() {
	static $init = true;
	$args = func_get_args();
	$ret = "<div>".plugin_googlemaps3_output($init, $args)."</div>";
	$init = false;
	return $ret;
}

function plugin_googlemaps3_inline() {
	static $init = true;
	$args = func_get_args();
	array_pop($args);
	$ret = plugin_googlemaps3_output($init, $args);
	$init = false;
	return $ret;
}

function plugin_googlemaps3_action() {
	global $vars;
	$action = isset($vars['action']) ? $vars['action'] : '';
	$page = isset($vars['page']) ? $vars['page'] : '';

	switch($action) {
		case '':
			break;
		// maxContent用のレイアウトスタイルでページのbodyを出力
		case 'showbody':
			if (is_page($page)) {
				$body = convert_html(get_source($page));
			} else {
				if ($page == '') {
					$page = '(Empty Page Name)';
				}
				$body = htmlspecialchars($page);
				$body .= '<br>Unknown page name';
			}
			pkwk_common_headers();
			header('Cache-control: no-cache');
			header('Pragma: no-cache');
			header('Content-Type: text/html; charset='.CONTENT_CHARSET);
			print <<<EOD
<div>
$body
</div>
EOD;
			break;
	}
	exit;
}

function plugin_googlemaps3_getbool($val) {
	if ($val == false) return false;
	if (!strcasecmp ($val, "false") || 
		!strcasecmp ($val, "no"))
		return false;
	return true;
}

function plugin_googlemaps3_addprefix($page, $name) {
	return "pukiwikigooglemaps3_".$page.'_'.$name;
}

function plugin_googlemaps3_output($doInit, $params) {
	global $vars, $script;

	if (!plugin_googlemaps3_is_supported_profile()) {
		return "googlemaps3:unsupported device";
	}
	$defoptions = plugin_googlemaps3_get_default();

	$inoptions = array();
	foreach ($params as $param) {
		$pos = strpos($param, '=');
		if ($pos == false) continue;
		$index = trim(substr($param, 0, $pos));
		$value = htmlspecialchars(trim(substr($param, $pos+1)));
		$inoptions[$index] = $value;
		if ($index == 'cx') {$cx = (float)$value;}//for old api
		if ($index == 'cy') {$cy = (float)$value;}//for old api
	}

	if (array_key_exists('define', $inoptions)) {
		$vars['googlemaps3'][$inoptions['define']] = $inoptions;
		return "";
	}

	$coptions = array();
	if (array_key_exists('class', $inoptions)) {
		$class = $inoptions['class'];
		if (array_key_exists($class, $vars['googlemaps3'])) {
			$coptions = $vars['googlemaps3'][$class];
		}
	}
	$options = array_merge($defoptions, $coptions, $inoptions);
	$mapname          = plugin_googlemaps3_addprefix($vars['page'], $options['mapname']);
	$width            = $options['width'];
	$height           = $options['height'];
	$lat              = (float)$options['lat'];
	$lng              = (float)$options['lng'];
	$zoom             = (integer)$options['zoom'];
	$mapctrl          = $options['mapctrl'];
	$zoomctrl         = $options['zoomctrl'];
	$panctrl          = $options['panctrl'];
	$type             = $options['type'];
	$typectrl         = $options['typectrl'];
	$scalectrl        = $options['scalectrl'];
	$rotatectrl       = $options['rotatectrl'];
	$streetviewctrl   = $options['streetviewctrl'];
	$overviewctrl     = $options['overviewctrl'];
	$crossctrl        = $options['crossctrl'];
	$searchctrl       = $options['searchctrl'];
	$api              = (integer)$options['api'];
	$noiconname       = $options['noiconname'];
	$togglemarker     = plugin_googlemaps3_getbool($options['togglemarker']);
	$dbclickzoom      = plugin_googlemaps3_getbool($options['dbclickzoom']);
	$kml              = preg_replace("/&amp;/i", '&', $options['kml']);
	$preserveviewport = plugin_googlemaps3_getbool($options['preserveviewport']);
	$importicon       = $options['importicon'];
	$backlinkmarker   = plugin_googlemaps3_getbool($options['backlinkmarker']);

	$page = $vars['page'];
	if (isset($cx)) $lng = $cx;
	if (isset($cy)) $lat = $cy;

	// width, heightの値チェック
	if (is_numeric($width)) { $width = (int)$width . "px"; }
	if (is_numeric($height)) { $height = (int)$height . "px"; }

	// Mapタイプの名前を正規化
	$type = plugin_googlemaps3_get_maptype($type);

	// 初期化処理の出力
	if ($doInit) {
		$output = plugin_googlemaps3_init_output($noiconname);
	} else {
		$output = "";
	}
	$pukiwikiname = $options['mapname'];
	$output .= <<<EOD
<div id="$mapname" style="width: $width; height: $height;"></div>

<script type="text/javascript">
//<![CDATA[
onloadfunc.push( function () {

if (typeof(googlemaps_maps['$page']) == 'undefined') {
	googlemaps_maps['$page'] = new Array();
	googlemaps_markers['$page'] = new Array();
	googlemaps_infowindow['$page'] = new Array();
	googlemaps_icons['$page'] = new Array();
	googlemaps_crossctrl['$page'] = new Array();
	googlemaps_searchctrl['$page'] = new Array();
}

//地図パラメータの設定
var map_options = {
    zoom: $zoom, 
    center: new google.maps.LatLng($lat, $lng),
    mapTypeId: $type,
    disableDefaultUI: true 
};

EOD;

	// 廃止につき後方互換性のための処理。
	// zoomctrl, movectrlにパラメータを振り分ける。
	// Show Map Control/Zoom 
	switch($mapctrl) {
		case "small":
		case "smallzoom":
			$zoomctrl = "small";
			$panctrl  = "none";
			break;
		case "none":
			break;
		case "large":
		default:
			$zoomctrl = "large";
			$panctrl  = "normal";
			break;
	}

	// 拡縮コントロール
	if ($zoomctrl != "none") {
		$output .= "map_options['zoomControl'] = true;\n";
		switch ($zoomctrl) {
			case "none":    break;
			case "small":   $output .= "map_options['zoomControlOptions'] = { style: google.maps.ZoomControlStyle.SMALL };\n"; break;
			case "large":   $output .= "map_options['zoomControlOptions'] = { style: google.maps.ZoomControlStyle.LARGE };\n"; break;
			case "normal": 
			default:        $output .= "map_options['zoomControlOptions'] = { style: google.maps.ZoomControlStyle.DEFAULT };\n"; break; 
		}
	}

	// 移動コントロール
	if ($panctrl != "none") {
		$output .= "map_options['panControl'] = true;\n";
	}

	// 地図タイプコントロール
	if ($typectrl != "none") {
		$output .= "map_options['mapTypeControl'] = true;\n";
		switch (strtolower(substr($typectrl, 0, 1))) {
			//horizontal
			case "h": $output .= "map_options['mapTypeControlOptions'] = { style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR };\n"; break;
			//dropdown
			case "d": $output .= "map_options['mapTypeControlOptions'] = { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU };\n"; break;
			//normal(default)
			case "n":       
		   default:$output .= "map_options['mapTypeControlOptions'] = { style: google.maps.MapTypeControlStyle.DEFAULT };\n"; break;
		}
	}

	// 定規コントロール 
	if ($scalectrl != "none") {
		$output .= "map_options['scaleControl'] = true;\n";
	}
	
	// 45度回転コントロール
	if ($rotatectrl != "none") {
		$output .= "map_options['rotateControl'] = true;\n";
	}

	//ストリートビューコントロール
	if ($streetviewctrl != "none") {
		$output .= "map_options['streetViewControl'] = true;\n";
	}

	// オーバービューマップコントロール
	if ($overviewctrl != "none") {
		$output .= "map_options['overviewMapControl'] = true;\n";
		if ($overviewctrl == "hide") {
			$output .= "map_options['overviewMapControlOptions'] = { opened: false };\n";
		} else {
			$output .= "map_options['overviewMapControlOptions'] = { opened: true };\n";
		}
	}

	$output .= <<<EOD

var map = new google.maps.Map(document.getElementById("$mapname"), map_options);
map.pukiwikiname = "$pukiwikiname";
googlemaps_maps['$page']['$mapname'] = map;
google.maps.event.addListener(map, "dblclick", function() {
	googlemaps_infowindow["$page"]["$mapname"].close();
});

google.maps.event.addListener(map, "zoom_changed", function() {
	var markers = googlemaps_markers["$page"]["$mapname"];

	var zoom = map.getZoom();
	for (name in markers) {
		var m = markers[name];
		var minzoom = m.minzoom <  0 ?  0 : m.minzoom;
		var maxzoom = m.maxzoom > 17 ? 17 : m.maxzoom;
		if (minzoom > maxzoom) {
			maxzoom = minzoom;
		}
		if (zoom >= minzoom && zoom <= maxzoom) {
			m.show();
		} else {
			m.hide();
		}
	}
});

onloadfunc2.push( function () {
	p_googlemaps_mark_to_map("$page", "$mapname");

	//マーカーのmaxzoom,minzoomを反映させるためにzoom_changedをfireする。
	map.setZoom(map.getZoom());
});

var infowindow = new google.maps.InfoWindow();
infowindow.maxWidth = 300;
googlemaps_infowindow['$page']['$mapname'] = infowindow;

EOD;

	// センタークロスコントロール
	if ($crossctrl != "none") {
		$output .= "var crossctrl = new PGCross();\n";
		$output .= "crossctrl.initialize(map);\n";
		$output .= "var crossChangeStyleFunc = function () {\n";
		$output .= "	switch (map.getMapTypeId()) {\n";
		$output .= "		case google.maps.MapTypeId.ROADMAP:   crossctrl.changeStyle('#000000', 0.5); break;\n";
		$output .= "		case google.maps.MapTypeId.SATELLITE: crossctrl.changeStyle('#FFFFFF', 0.9); break;\n";
		$output .= "		case google.maps.MapTypeId.HYBRID:    crossctrl.changeStyle('#FFFFFF', 0.9); break;\n";
		$output .= "		case google.maps.MapTypeId.TERRAIN:   crossctrl.changeStyle('#000000', 0.5); break;\n";
		$output .= "		default: crossctrl.changeStyle('#000000', 0.5); break;\n";
		$output .= "	}\n";
		$output .= "}\n";
		$output .= "google.maps.event.addListener(map, 'maptypeid_changed', crossChangeStyleFunc);\n";
		$output .= "crossChangeStyleFunc();\n";
		$output .= "googlemaps_crossctrl['$page']['$mapname'] = crossctrl;\n";
	}

	// 検索ボックスコントロール
	if ($searchctrl != "none") {
		$output .= "var searchctrl = new PGSearch();\n";
		$output .= "searchctrl.initialize(map);\n";
		$output .= "googlemaps_searchctrl['$page']['$mapname'] = searchctrl;\n";
	}

	// Double click zoom
	if ($dbclickzoom) {
		$output .= "map.disableDoubleClickZoom = false;\n";
	} else {
		$output .= "map.disableDoubleClickZoom = true;\n";
	}

	// マーカーの表示非表示チェックボックス
	if ($togglemarker) {
		$output .= "onloadfunc.push( function(){p_googlemaps_togglemarker_checkbox('$page', '$mapname', '$noiconname');} );";
	}

	// KML Layer
	if ($kml) {
		$ismatch = preg_match("/^https?:\/\/.*/", $kml, $matches);
		if (!$ismatch) {
			$query = "?plugin=attach&pcmd=open&file=".rawurlencode($kml)."&refer=".rawurlencode($page);
			$kml = $script . $query;
		}
		$output .= "var kmllayer = new google.maps.KmlLayer(\"$kml\", {preserveViewport: ";
		$output .= $preserveviewport ? "true" : "false";
		$output .= "});\n";
		$output .= "kmllayer.setMap(map);\n";
	}

	$output .= "PGTool.transparentGoogleLogo(map);\n";
	$output .= "googlemaps_markers['$page']['$mapname'] = new Array();\n";
	$output .= "});\n";
	$output .= "//]]>\n";
	$output .= "</script>\n";
    

	// 指定されたPukiwikiページからアイコンを収集する
	if ($importicon != "") {
		$lines = get_source($importicon);
		foreach ($lines as $line) {
			$ismatch = preg_match('/googlemaps3_icon\(.*?\)/i', $line, $matches);
			if ($ismatch) {
				$output .= convert_html("#" . $matches[0]) . "\n";
			}
		}
	}

	// このページのバックリンクからマーカーを収集する。
	if ($backlinkmarker) {
		$links = links_get_related_db($vars['page']);
		if (! empty($links)) {
			$output .= "<ul>\n";
			foreach(array_keys($links) as $page) {
				$ismatch = preg_match('/#googlemaps3_mark\(([^, \)]+), *([^, \)]+)(.*?)\)/i', 
					get_source($page, TRUE, TRUE), $matches);
				if ($ismatch) {
					$markersource = "&googlemaps3_mark(" . 
						$matches[1] . "," . $matches[2] . 
						", title=" . $page;
					if ($matches[3] != "") {
						preg_match('/caption=[^,]+/', $matches[3], $m_caption);
						if ($m_caption) $markersource .= "," . $m_caption[0];
						preg_match('/icon=[^,]+/', $matches[3], $m_icon);
						if ($m_icon) $markersource .= "," . $m_icon[0];
					}
					$markersource .= ");";
					$output .= "<li>" . make_link($markersource) . "</li>\n";
				}
			}
			$output .= "</ul>\n";
		}
	}

	return $output;
}

function plugin_googlemaps3_get_maptype($type) {
	switch (strtolower(substr($type, 0, 1))) {
		case "r":
		case "n": return "google.maps.MapTypeId.ROADMAP";
		case "s": return "google.maps.MapTypeId.SATELLITE";
		case "h": return "google.maps.MapTypeId.HYBRID";
		case "t": return "google.maps.MapTypeId.TERRAIN";
		default:  return "google.maps.MapTypeId.ROADMAP";
	}
	return "google.maps.MapTypeId.ROADMAP";
}

function plugin_googlemaps3_init_output($noiconname) {
	global $vars;
	$page = $vars['page'];
	return <<<EOD
<script src="http://maps.google.com/maps/api/js?v=3.10&sensor=true&libraries=places" type="text/javascript" charset="UTF-8"></script>
<script type="text/javascript">
//<![CDATA[

if (typeof(googlemaps_maps) == 'undefined') {
	// add vml namespace for MSIE
	var agent = navigator.userAgent.toLowerCase();
	if (agent.indexOf("msie") != -1 && agent.indexOf("opera") == -1) {
		try {
		document.namespaces.add('v', 'urn:schemas-microsoft-com:vml');
		document.createStyleSheet().addRule('v\:*', 'behavior: url(#default#VML);');
		} catch(e) {}
	}

	var googlemaps_maps = new Array();
	var googlemaps_markers = new Array();
	var googlemaps_infowindow = new Array();
	var googlemaps_icons = new Array();
	var googlemaps_crossctrl = new Array();
	var googlemaps_searchctrl = new Array();
	var onloadfunc = new Array();
	var onloadfunc2 = new Array();//onloadfuncの後に実行（アイコンの一括登録などをする）
}

function PGMarker (latlng, icon, flat, page, mapname, hidden, visible, title, minzoom, maxzoom) {
	var marker = null;
	if (hidden == false) {
		var opt = new Object();
		opt.position = latlng;
		opt.map = googlemaps_maps[page][mapname];
		if (icon != '') {
			var iconobj = googlemaps_icons[page][icon];
			if (iconobj != null) {
				opt.icon =iconobj.image;
				opt.shadow = iconobj.shadow;
			}
		}
		if (flat == true) {
			opt.flat = true;
		} else {
			opt.flat = false;
		}
		if (title != '') { opt.title = title; }
		marker = new google.maps.Marker(opt);
		google.maps.event.addListener(marker, "click", function() { this.pukiwikigooglemaps.onclick(); });
		marker.pukiwikigooglemaps = this;
	}

	this.marker = marker;
	this.icon = icon;
	this.map = googlemaps_maps[page][mapname];
	this.mapname = mapname;
	this.position = latlng;
	this.minzoom = minzoom;
	this.maxzoom = maxzoom;

	var _visible = false;
	var _html = null;
	var _zoom = null;

	this.setHtml = function(h) {_html = h;}
	this.setZoom = function(z) {_zoom = parseInt(z);}
	this.getHtml = function() {return _html;}
	this.getZoom = function() {return _zoom;}

	this.onclick = function () {
		var map = this.map;

		if (_zoom) {
			if (map.getZoom() != _zoom) {
				map.setZoom(_zoom);
			}
			map.panTo(this.position);
		}

		if ( _html && this.marker ) {
			
			//未ロードの画像があれば読み込みを待ってから開く
			var root = document.createElement('div');
			root.innerHTML = _html;

			var checkNodes = new Array();
			var doneOpenInfoWindow = false;
			checkNodes.push(root);

			var infowindow = googlemaps_infowindow[page][mapname];

			while (checkNodes.length) {
				var node = checkNodes.shift();
				if (node.hasChildNodes()) {
					for (var i=0; i<node.childNodes.length; i++) {
						checkNodes.push(node.childNodes.item(i));
					}
				} else {
					var tag = node.tagName;
					if (tag && tag.toUpperCase() == "IMG") {
						if (node.complete == false) {
							//画像の読み込みを待ってからInfoWindowを開く
							var openInfoWindowFunc = function (xmlhttp) {
								infowindow.setContent(_html);
								infowindow.open(map, marker);
							}
							var async = false;
							if (agent.indexOf("msie") != -1 && agent.indexOf("opera") == -1) {
								async = true;
							}
							if (PGTool.downloadURL(node.src, openInfoWindowFunc, async, null, null)) {
								doneOpenInfoWindow = true;
							}
							break;
						}
					}
				}
			}
			if (doneOpenInfoWindow == false) {
				infowindow.setContent(_html);
				infowindow.open(map, marker);
			}
		} else {
			map.panTo(this.position);
		}
	}

	this.isVisible = function () {
		return _visible;
	}
	this.show = function () {
		if (_visible) return;
		if (this.marker) this.marker.setVisible(true);
		_visible = true;
	}

	this.hide = function () {
		if (!_visible) return;
		if (this.marker != null) this.marker.setVisible(false);
		_visible = false;
	}

	if (visible) {
		this.show();
	} else {
		this.hide();
	}
	return this;
}


var PGTool = new function () {
	this.fmtNum = function (x) {
		var n = x.toString().split(".");
		n[1] = (n[1] + "000000").substr(0, 6);
		return n.join(".");
	}
	this.getLatLng = function (x, y) {
		return new google.maps.LatLng(x, y);
	}
	this.getXYPoint = function (x, y, api) {
		nx = 1.000083049 * x + 0.00004604674815 * y - 0.01004104571;
		ny = 1.000106961 * y - 0.00001746586797 * x - 0.004602192204;
		x = nx;
		y = ny;
		return {x:x, y:y};
	}
	this.createXmlHttp = function () {
		if (typeof(XMLHttpRequest) == "function") {
			return new XMLHttpRequest();
		}
		if (typeof(ActiveXObject) == "function") {
			try {
				return new ActiveXObject("Msxml2.XMLHTTP");
			} catch(e) {};
			try {
				return new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {};
		}
		return null;
	}
	this.downloadURL = function (url, func, async, postData, contentType) {
		var xmlhttp = this.createXmlHttp();
		if (!xmlhttp) {
			return null;
		}
		if (async && func) {
			xmlhttp.onreadystatechange = function () {
				if (xmlhttp.readyState == 4) {
					func(xmlhttp);
				}
			};
		}
		try {
			if (postData) {
				xmlhttp.open("POST", url, async);
				if (!contentType) {
					contentType = "application/x-www-form-urlencoded";
				}
				xmlhttp.setRequestHeader("Content-Type", contentType);
				xmlhttp.send(postData);
			} else {
				xmlhttp.open("GET", url, async);
				xmlhttp.send(null);
			}
		} catch(e) {
			return false;
		}
		if (!async && func) func(xmlhttp);
	}

	// Pukiwiki のデフォルトのCSSではGoogleのロゴがFirefox, Operaで
	// 透過しなくなったので、透過させる。
	this.transparentGoogleLogo = function(map) {
		var container = map.getDiv();
		for (var i=0; i<container.childNodes.length; i++) {
			var node = container.childNodes.item(i);
			if (node.tagName != "A") continue;
			if (node.hasChildNodes() == false) continue;

			var img = node.firstChild;
			if (img.tagName != "IMG") continue;
			if (img.src.match(/http:.*\/poweredby\.png/) == null) continue;

			node.style.backgroundColor = "transparent";
			break;
		}
		return;
	}
}

var PGDraw = new function () {
	var self = this;
	this.weight = 10;
	this.opacity = 0.5;
	this.color = "#00FF00";
	this.fillopacity = 0;
	this.fillcolor = "#FFFF00";

	this.line = function (plist) {
		return new google.maps.Polyline(plist, this.color, this.weight, this.opacity);
	}
	
	this.rectangle = function (p1, p2) {
		var points = new Array (
			p1,
			new google.maps.LatLng(p1.lat(), p2.lng()),
			p2,
			new google.maps.LatLng(p2.lat(), p1.lng()),
			p1
		);
		return draw_polygon (plist);
	}
	
	this.circle  = function (point, radius) {
		return draw_ngon(point, radius, 0, 48, 0, 360);
	}
	
	this.arc = function (point, outradius, inradius, st, ed) {
		while (st > ed) { ed += 360; }
		if (st == ed) {
			return this.circle(point, outradius, inradius);
		}
		return draw_ngon(point, outradius, inradius, 48, st, ed);
	}
	
	this.ngon = function (point, radius, n, rotate) {
		if (n < 3) return null;
		return draw_ngon(point, radius, 0, n, rotate, rotate+360);
	}
	
	this.polygon = function (plist) {
		return draw_polygon (plist);
	}
	
	function draw_ngon (point, outradius, inradius, div, st, ed) {
		if (div <= 2) return null;

		var incr = (ed - st) / div;
		var lat = point.lat();
		var lng = point.lng();
		var out_plist = new Array();
		var in_plist  = new Array();
		var rad = 0.017453292519943295; /* Math.PI/180.0 */
		var en = 0.00903576399827824;   /* 1/(6341km * rad) */
		var out_clat = outradius * en; 
		var out_clng = out_clat/Math.cos(lat * rad);
		var in_clat = inradius * en; 
		var in_clng = in_clat/Math.cos(lat * rad);
		
		for (var i = st ; i <= ed; i+=incr) {
			if (i+incr > ed) {i=ed;}
			var nx = Math.sin(i * rad);
			var ny = Math.cos(i * rad);

			var ox = lat + out_clat * nx;
			var oy = lng + out_clng * ny;
			out_plist.push(new google.maps.LatLng(ox, oy));

			if (inradius > 0) {
			var ix = lat + in_clat  * nx;
			var iy = lng + in_clng  * ny;
			in_plist.push (new google.maps.LatLng(ix, iy));
			}
		}

		var plist;
		if (ed - st == 360) {
			plist = out_plist;
			plist.push(plist[0]);
		} else {
			if (inradius > 0) {
				plist = out_plist.concat( in_plist.reverse() );
				plist.push(plist[0]);
			} else {
				out_plist.unshift(point);
				out_plist.push(point);
				plist = out_plist;
			}
		}

		return draw_polygon(plist);
	}

	function draw_polygon (plist) {
		if (self.fillopacity <= 0) {
			return new google.maps.Polyline({
				path:          plist,
				strokeColor:   self.color,
				strokeWeight:  self.weight,
				strokeOpacity: self.opacity});
		}
		return new google.maps.Polygon({
			path:          plist, 
			strokeColor:   self.color,
			strokeWeight:  self.weight,
			strokeOpacity: self.opacity,
			fillColor:     self.fillcolor,
			fillOpacity:   self.fillopacity}); 
	}
}


//
// Center Cross コントロール
//
function PGCross() {
	this.map = null;
	this.container = null;

	this.initialize = function(map) {
		this.map = map;
		this.container = document.createElement("div");
		this.container.style.position = "absolute";
		this.container.style.zIndex = "99999";
		var crossDiv = this.createWidget(16, 2, "#000000");
		this.container.appendChild(crossDiv);
		this.container.width = crossDiv.width;
		this.container.height = crossDiv.height;

		var cross = this;
		google.maps.event.addDomListener(map.getDiv(), "resize", function(e) {
			cross.moveCenter();
		});
		
		map.getDiv().appendChild(this.container);
		this.moveCenter();

		infowindow = googlemaps_infowindow["$page"][map.getDiv().id];
		var container = this.container;
		var hidefunc = function() { try {map.getDiv().removeChild(container);} catch (e) {} }
		var showfunc = function() { try {map.getDiv().appendChild(container);} catch (e) {} }
		google.maps.event.addListener(infowindow, "closeclick", function(){ showfunc(); });
		google.maps.event.addListener(infowindow, "domready", function(){ hidefunc(); });

		google.maps.event.addListener(map.getStreetView(), "visible_changed", function() {
			if (map.getStreetView().getVisible() == true) {
				hidefunc();
			} else {
				showfunc();
			}
		});

		return this.container;
	}

	this.getCrossPosition = function() {
		var mapdiv = this.map.getDiv();
		var x = (mapdiv.clientWidth  - this.container.clientWidth)/2.0;
		var y = (mapdiv.clientHeight - this.container.clientHeight)/2.0;
		return new google.maps.Point(Math.ceil(x), Math.ceil(y));
	}

	this.moveCenter = function() {
		var pos = this.getCrossPosition();
		this.container.style.top  = pos.y.toString() + "px";
		this.container.style.left = pos.x.toString() + "px";
	}

	this.changeStyle = function(color, opacity) {
		var table = this.container.firstChild.firstChild;
		var children = table.getElementsByTagName("td");
		for (var i = 0; i < children.length; i++) {
			var node = children[i];
			if (node.style.backgroundColor != "transparent") {
				node.style.backgroundColor = color;
			}
		}
		table.style.MozOpacity = opacity;
		table.style.filter = 'alpha(opacity=' + (opacity * 100) + ')';
	}

	this.createWidget = function(nsize, lwidth, lcolor) {
		var hsize = (nsize - lwidth) / 2;
		var nsize = hsize * 2 + lwidth;
		var border = document.createElement("div");
		border.width = nsize;
		border.height = nsize;
		var table = '\
<table width="'+nsize+'" border="0" cellspacing="0" cellpadding="0">\
  <tr>\
  <td style="width:'+ hsize+'px; height:'+hsize+'px; background-color:transparent; border:0px;"></td>\
  <td style="width:'+lwidth+'px; height:'+hsize+'px; background-color:'+lcolor+';  border:0px;"></td>\
  <td style="width:'+ hsize+'px; height:'+hsize+'px; background-color:transparent; border:0px;"></td>\
  </tr>\
  <tr>\
  <td style="width:'+ hsize+'px; height:'+lwidth+'px; background-color:'+lcolor+'; border:0px;"></td>\
  <td style="width:'+lwidth+'px; height:'+lwidth+'px; background-color:'+lcolor+'; border:0px;"></td>\
  <td style="width:'+ hsize+'px; height:'+lwidth+'px; background-color:'+lcolor+'; border:0px;"></td>\
  </tr>\
  <tr>\
  <td style="width:'+ hsize+'px; height:'+hsize+'px; background-color:transparent; border:0px;"></td>\
  <td style="width:'+lwidth+'px; height:'+hsize+'px; background-color:'+lcolor+';  border:0px;"></td>\
  <td style="width:'+ hsize+'px; height:'+hsize+'px; background-color:transparent; border:0px;"></td>\
  </tr>\
</table>';
		border.innerHTML = table;
		border.firstChild.style.MozOpacity = 0.5;
		border.firstChild.style.filter = 'alpha(opacity=50)';
		return border;
	}
};


//
// 検索ボックスコンロール
//
function PGSearch() {
	this.map = null;
	this.container = null;

	this.initialize = function(map) {
		this.map = map;
		this.container = document.createElement("div");
		this.container.style.backgroundColor = "#ffffff";
		this.container.style.padding = "5px"
		this.container.style.border = "1px solid #999999";
		var txtbox = document.createElement("input");
		txtbox.type = "text";
		txtbox.style.width = "300px";
		this.container.appendChild(txtbox);
		
		var searchbox = new google.maps.places.SearchBox(txtbox);
		var markers = new Array();

		google.maps.event.addListener(searchbox, 'places_changed', function () {
			var places = searchbox.getPlaces();
			
			for (var i = 0, marker; marker = markers[i]; i++) {
				marker.setMap(null);
			}
			markers = new Array();

			var bounds = new google.maps.LatLngBounds();
			for (var i = 0, place; place = places[i]; i++) {
				var image = new google.maps.MarkerImage(
					place.icon, new google.maps.Size(71, 71),
					new google.maps.Point(0, 0), new google.maps.Point(17, 34),
					new google.maps.Size(25, 25));

				var marker = new google.maps.Marker({
					map: map,
					icon: image,
					title: place.name,
					position: place.geometry.location
				});

				markers.push(marker);

				bounds.extend(place.geometry.location);
			}

			google.maps.event.addListener(map, 'bounds_changed', function() {
				searchbox.setBounds(map.getBounds());
			});

			map.fitBounds(bounds);
		});

		searchbox.setBounds(map.getBounds());
		map.controls[google.maps.ControlPosition.TOP_CENTER].push(this.container);
		
	}

}

//
// マーカーON/OFF
//

function p_googlemaps_marker_toggle (page, mapname, check, name) {
	var markers = googlemaps_markers[page][mapname];
	for (key in markers) {
		var m = markers[key];
		if (m.icon == name) {
			if (check.checked) {
				m.show();
			} else {
				m.hide();
			}
		}
	}
}

function p_googlemaps_togglemarker_checkbox (page, mapname, undefname) {
	var icons = {};
	var markers = googlemaps_markers[page][mapname];
	for (key in markers) {
		var map = markers[key].map;
		var icon = markers[key].icon;
		icons[icon] = 1;
	}
	var iconlist = new Array();
	for (n in icons) {
		iconlist.push(n);
	}
	iconlist.sort();

	var r = document.createElement("div");
	var map = document.getElementById(mapname);
	map.parentNode.insertBefore(r, map.nextSibling);

	for (i in iconlist) {
		var name = iconlist[i];
		var id = "ti_" + mapname + "_" + name;
		var input = document.createElement("input");
		var label = document.createElement("label");
		input.setAttribute("type", "checkbox");
		input.id = id;
		label.htmlFor = id;
		if (name == "") {
		label.appendChild(document.createTextNode(undefname));
		} else {
		label.appendChild(document.createTextNode(name));
		}
		eval("input.onclick = function(){p_googlemaps_marker_toggle('" + page + "','" + mapname + "', this, '" + name + "');}");

		r.appendChild(input);
		r.appendChild(label);
		input.setAttribute("checked", "checked");
	}
}

function p_googlemaps_regist_marker (page, mapname, center, key, option) {
	if (document.getElementById(mapname) == null) {
		mapname = mapname.replace(/^pukiwikigooglemaps3_/, "");
		page = mapname.match(/(^.*?)_/)[1];
		mapname = mapname.replace(/^.*?_/, "");
		alert("googlemaps3: '" + option.title + "' のマーカーの登録に失敗しました。'" + 
		"ページ：" + page + " に" + mapname + "' というマップが見つかりません。");
		return;
	}
	var m = new PGMarker(center, option.icon, option.flat, page, mapname, option.noicon, true, option.title, option.minzoom, option.maxzoom);
	m.setHtml(option.infohtml);
	m.setZoom(option.zoom);
	googlemaps_markers[page][mapname][key] = m;
}

function p_googlemaps_mark_to_map (page, mapname) {
	var markers = googlemaps_markers[page][mapname];
	
	for (key in markers) {
		var m = markers[key];

		if (m.marker) {
			m.marker.setMap(googlemaps_maps[page][mapname]);
		}
	}
}

window.onload = function () {
	//if (GBrowserIsCompatible()) {
		while (onloadfunc.length > 0) {
			onloadfunc.shift()();
		}
		while (onloadfunc2.length > 0) {
			onloadfunc2.shift()();
		}
	//}
}

window.onunload = function () {
	//GUnload();
}
//]]>
</script>\n
EOD;
}

?>
