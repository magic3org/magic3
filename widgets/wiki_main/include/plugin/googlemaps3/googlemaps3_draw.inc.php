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

define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_OPACITY' ,0.5);       //透明度
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_WEIGHT'  ,10);        //太さ
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_COLOR'   ,"#FF0000"); //色
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_LAT'     ,35.0);      //緯度
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_LNG'     ,135.0);     //経度
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_LAT1'    ,35.0);      //座標1緯度
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_LNG1'    ,135.0);     //座標1経度
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_LAT2'    ,36.0);      //座標2緯度
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_LNG2'    ,136.0);     //座標2経度
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_RADIUS'  ,1);         //半径または外径(単位はkm)
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_INRADIUS',0);         //内径(単位はkm)
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_STANGLE' ,0);         //開始角
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_EDANGLE' ,180);       //終了角
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_ROTATE'  ,0);         //回転度数
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_N'       ,3);         //正n角形の頂点数
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_FILLOPACITY' ,0.5);       //塗りつぶし透明度
define ('PLUGIN_GOOGLEMAPS3_DRAW_DEF_FILLCOLOR'   ,"#FFFF00"); //塗りつぶし色

function plugin_googlemaps3_draw_get_default () {
	global $vars;
	return array(
		'opacity '    => PLUGIN_GOOGLEMAPS3_DRAW_DEF_OPACITY,
		'weight'      => PLUGIN_GOOGLEMAPS3_DRAW_DEF_WEIGHT,
		'color'       => PLUGIN_GOOGLEMAPS3_DRAW_DEF_COLOR,
		'map'         => PLUGIN_GOOGLEMAPS3_DEF_MAPNAME,
		'lat'         => PLUGIN_GOOGLEMAPS3_DRAW_DEF_LAT,
		'lng'         => PLUGIN_GOOGLEMAPS3_DRAW_DEF_LNG,
		'lat1'        => PLUGIN_GOOGLEMAPS3_DRAW_DEF_LAT1,
		'lng1'        => PLUGIN_GOOGLEMAPS3_DRAW_DEF_LNG1,
		'lat2'        => PLUGIN_GOOGLEMAPS3_DRAW_DEF_LAT2,
		'lng2'        => PLUGIN_GOOGLEMAPS3_DRAW_DEF_LNG2,
		'radius'      => PLUGIN_GOOGLEMAPS3_DRAW_DEF_RADIUS,
		'inradius'    => PLUGIN_GOOGLEMAPS3_DRAW_DEF_INRADIUS,
		'stangle'     => PLUGIN_GOOGLEMAPS3_DRAW_DEF_STANGLE,
		'edangle'     => PLUGIN_GOOGLEMAPS3_DRAW_DEF_EDANGLE,
		'rotate'      => PLUGIN_GOOGLEMAPS3_DRAW_DEF_ROTATE,
		'n'           => PLUGIN_GOOGLEMAPS3_DRAW_DEF_N,
		'fillopacity' => PLUGIN_GOOGLEMAPS3_DRAW_DEF_FILLOPACITY,
		'fillcolor'   => PLUGIN_GOOGLEMAPS3_DRAW_DEF_FILLCOLOR
	);

}

function plugin_googlemaps3_draw_convert() {
	$args = func_get_args();
	if (sizeof($args)<2) {
		return "error: plugin googlemaps3_mark wrong args\n";
	}
	return plugin_googlemaps3_draw_output($args[0], array_slice($args, 1));
}

function plugin_googlemaps3_draw_inline() {
	$args = func_get_args();
	$str = array_pop($args);
	if (sizeof($args)<2) {
		return "error: plugin googlemaps3_mark wrong args\n";
	}
	return plugin_googlemaps3_draw_output($args[0], array_slice($args, 1));
}

function plugin_googlemaps3_draw_output($command, $params) {
	global $vars;

	if (!defined('PLUGIN_GOOGLEMAPS3_DEF_MAPNAME')) {
		return "googlemaps3_draw: error googlemapsを先に呼び出してください。<br/>";
	}
	if (!plugin_googlemaps3_is_supported_profile()) {
		return '';
	}

	if (!array_key_exists('googlemaps3_draw_history', $vars)) {
		$vars['googlemaps3_draw_history'] = plugin_googlemaps3_draw_get_default ();
	}
	
	$inoptions = array();
	$narray = array();
	foreach ($params as $param) {
		$pos = strpos($param, '=');
		if ($pos == false) {
			if (is_numeric($param)) {
				array_push($narray, trim($param));
			}
			continue;
		}
		$index = trim(substr($param, 0, $pos));
		$value = htmlspecialchars(trim(substr($param, $pos+1)));

		$inoptions[$index] = $value;
	}

	if (array_key_exists('define', $inoptions)) {
		$vars['googlemaps3_draw'][$inoptions['define']] = $inoptions;
		return "";
	}

	$coptions = array();
	if (array_key_exists('class', $inoptions)) {
		$class = $inoptions['class'];
		if (array_key_exists($class, $vars['googlemaps3_draw'])) {
			$coptions = $vars['googlemaps3_draw'][$class];
		}
	}
	$options = array_merge($vars['googlemaps3_draw_history'], $coptions, $inoptions);
	$vars['googlemaps3_draw_history'] = $options;

	$opacity     = (float)$options['opacity'];
	$weight      = (float)$options['weight'];
	$color       = $options['color'];
	$map         = plugin_googlemaps3_addprefix($vars['page'], $options['map']);
	$lat         = (float)$options['lat'];
	$lng         = (float)$options['lng'];
	$lat1        = (float)$options['lat1'];
	$lng1        = (float)$options['lng1'];
	$lat2        = (float)$options['lat2'];
	$lng2        = (float)$options['lng2'];
	$radius      = (float)$options['radius'];
	$inradius    = (float)$options['inradius'];
	$stangle     = (float)$options['stangle'];
	$edangle     = (float)$options['edangle'];
	$rotate      = (float)$options['rotate'];
	$n           = (integer)$options['n'];
	$fillopacity = (float)$options['fillopacity'];
	$fillcolor   = $options['fillcolor'];
	
	$page = $vars['page'];
	// Create Marker
	$output = <<<EOD
<script type="text/javascript">
//<![CDATA[
onloadfunc.push(function() {
	if (document.getElementById("$map") == null) {
		alert("googlemaps error: mapname \\"$map\\" is not defined");
		return;
	}
	PGDraw.weight = $weight;
	PGDraw.opacity = $opacity;
	PGDraw.color = "$color";
	PGDraw.fillopacity = $fillopacity;
	PGDraw.fillcolor = "$fillcolor";

EOD;
	switch ($command) {
		case "line":
		case "polygon":
			if (count($narray) < 2)
				break;
			$output .= "	var points = new Array();\n";
			for ($i=0; $i < count($narray); $i++) {
				$lat = $narray[$i];
				$lng = $narray[++$i];
				$output .= "	points.push(new google.maps.LatLng($lat, $lng));\n";
			}
			if ($command == "line") {
				$output .= "	var poly = PGDraw.line(points);\n";
			} else {
				$output .= "	var poly = PGDraw.polygon(points);\n";
			}
			break;
		case "rectangle":
			$output .= "	var p1 = new google.maps.LatLng(".$lat1.", ".$lng1.");\n";
			$output .= "	var p2 = new google.maps.LatLng(".$lat2.", ".$lng2.");\n";
			$output .= "	var poly = PGDraw.rectangle(p1, p2);\n";
			break;
		case "circle":
			$output .= "	var center = new google.maps.LatLng(".$lat.", ".$lng.");\n";
			$output .= "	var outradius = ".$radius.";\n";
			$output .= "	var inradius = ".$inradius.";\n";
			$output .= "	var poly = PGDraw.circle(center, outradius, inradius);\n";
			break;
		case "arc":
			$output .= "	var center = new google.maps.LatLng(".$lat.", ".$lng.");\n";
			$output .= "	var outradius = ".$radius.";\n";
			$output .= "	var inradius = ".$inradius.";\n";
			$output .= "	var st = ".$stangle.";\n";
			$output .= "	var ed = ".$edangle.";\n";
			$output .= "	var poly = PGDraw.arc(center, outradius, inradius, st, ed);\n";
			break;
		case "ngon":
			$output .= "	var center = new google.maps.LatLng(".$lat.", ".$lng.");\n";
			$output .= "	var radius = ".$radius.";\n";
			$output .= "	var rotate = ".$rotate.";\n";
			$output .= "	var n = ".$n.";\n";
			$output .= "	var poly = PGDraw.ngon(center, radius, n, rotate);\n";
			break;
		default:
			$output .= "	var poly = null;\n";
	}
	$output .= "	if (poly) poly.setMap(googlemaps_maps['$page']['$map']);\n";
	$output .= <<<EOD
});
//]]>
</script>\n
EOD;

	return $output;
}
?>
