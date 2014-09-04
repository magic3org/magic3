/**
 * Magic3 CKEditorプラグイン
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    1.1
 * @link       http://www.magic3.org
 */
var GoogleMapsHandler = {
	maps: {},		// マップ情報

	// マップ情報取得
	getMap: function(id){
		return this.maps[id];
	},

	// マップ情報検出
	detectMapScript: function(script)
	{
		if (!(/Magic3 googlemaps v1\.(\d+)/.test(script))) return false;

		return true
	},

	// マップ新規作成
	createNew: function()
	{
		var map = new GoogleMap();
		this.maps[map.number] = map;
		return map;
	},
};

// マップのパラメータ初期化
var GoogleMap = function() 
{
	var now = new Date();
	this.number = '' + now.getFullYear() + now.getMonth() + now.getDate() + now.getHours() + now.getMinutes() + now.getSeconds();

	this.width = CKEDITOR.config.googlemaps_width || 400;
	this.height = CKEDITOR.config.googlemaps_height || 240;
	this.widthType = 'px';
	this.heightType = 'px';
	this.alignCenter = false;
	
	this.centerLat = CKEDITOR.config.googlemaps_centerLat || 35.594757;
	this.centerLon =  CKEDITOR.config.googlemaps_centerLon || 139.620739;
	this.zoom = CKEDITOR.config.googlemaps_zoom || 11;
	this.style = '';
	this.zoomControl = true;
	this.panControl = true;
	this.mapTypeControl = true;
	this.scaleControl = true;
	this.streetViewControl = false;
	this.rotateControl = false;
	this.overviewMapControl = false;
	
	this.markerPoints = [];
	this.linePoints = '';
	this.lineLevels = '';
	this.mapType = 0;
}

GoogleMap.prototype.generateStaticMap = function()
{
	var w = Math.min(this.width, 640);
	var h = Math.min(this.height, 640);
	if (this.widthType == '%') w = 640;
	if (this.heightType == '%') h = 640;
	
	var staticMapTypes = ['roadmap', 'satellite', 'hybrid', 'terrain'];

	var mapUrl = 'http://maps.google.com/maps/api/staticmap?center=' + this.centerLat + ',' + this.centerLon 
					+ '&zoom=' + this.zoom + '&size=' + w + 'x' + h 
					+ '&maptype=' + staticMapTypes[ this.mapType ]
					+ this.generateStaticMarkers() + '&sensor=false';
	return mapUrl;
}

GoogleMap.prototype.generateStaticMarkers = function()
{
	if (this.markerPoints.length == 0) return '';
	var aPoints = [];
	for (var i = 0; i < this.markerPoints.length; i++)
	{
		var point = this.markerPoints[i];
		aPoints.push(point.lat + ',' + point.lon);
	}
	return ('&markers=' + aPoints.join('|'));
}
// Read the dimensions back from the fake node (the user might have manually resized it)
GoogleMap.prototype.updateDimensions = function( oFakeNode )
{
	var iWidth, iHeight;
	var regexSize = /^\s*(\d+)px\s*$/i;
	var regexSizePer = /^\s*(\d+)%\s*$/i;

	if (oFakeNode.style.width)
	{
		var aMatchW  = oFakeNode.style.width.match(regexSize);
		if (aMatchW)
		{
			iWidth = aMatchW[1];
			oFakeNode.style.width = '';
			oFakeNode.width = iWidth;
			this.widthType = 'px';
		} else {
			aMatchW  = oFakeNode.style.width.match(regexSizePer);
			if (aMatchW)
			{
				iWidth = aMatchW[1];
				oFakeNode.style.width = '';
				oFakeNode.width = iWidth;
				this.widthType = '%';
			}
		}
	}

	if (oFakeNode.style.height)
	{
		var aMatchH  = oFakeNode.style.height.match(regexSize);
		if (aMatchH)
		{
			iHeight = aMatchH[1];
			oFakeNode.style.height = '';
			oFakeNode.height = iHeight;
			this.heightType = 'px';
		} else {
			aMatchH  = oFakeNode.style.height.match(regexSizePer);
			if (aMatchH)
			{
				iHeight = aMatchH[1];
				oFakeNode.style.height = '';
				oFakeNode.height = iHeight;
				this.heightType = '%';
			}
		}
	}

	this.width	= iWidth ? iWidth : oFakeNode.width;
	this.height	= iHeight ? iHeight : oFakeNode.height;
}
GoogleMap.prototype.setDimensions = function(width, height, widthType, heightType, alignCenter)
{
	this.width	= width;
	this.height	= height;
	this.widthType = widthType;
	this.heightType = heightType;
	this.alignCenter = alignCenter;
}
GoogleMap.prototype.decodeText = function(string)
{
	return string.replace(/<\\\//g, "</").replace(/\\n/g, "\n").replace(/\\'/g, "'").replace(/\\\\/g, "\\");
}
GoogleMap.prototype.encodeText = function(string)
{
	return string.replace(/\\/g, "\\\\").replace(/'/g, "\\'").replace(/\n/g, "\\n").replace(/<\//g, "<\\/");
}

GoogleMap.prototype.parse = function( script )
{
	var regExp = /Magic3 googlemaps v1\.(\d+) mapid:(\d+)/;
	if (!(regExp.test(script))) return false;

//	var version = parseInt(RegExp.$1, 10);
	delete GoogleMapsHandler.maps[this.number];
	this.number = RegExp.$2;
	GoogleMapsHandler.maps[this.number] = this;
	
	// マップ高さ、幅を取得
	var regexpDimensions = /<div id="gmap(\d+)" style="width\:\s*(\d+)px; height\:\s*(\d+)px;">/;
	if (regexpDimensions.test( script ) )
	{
		this.width = RegExp.$2;
		this.height = RegExp.$3;
	}

	// マップ位置座標を取得
	var regexpPosition = /map\.setCenter\(new google\.maps\.LatLng\((-?\d{1,3}\.\d{1,6}), (-?\d{1,3}\.\d{1,6})\)\);/;
	if (regexpPosition.test(script))
	{
		this.centerLat = RegExp.$1;
		this.centerLon = RegExp.$2;
	}

	// マップズームレベルを取得
	var regexpPosition = /map\.setZoom\((\d{1,2})\);/;
	if (regexpPosition.test(script))
	{
		this.zoom = RegExp.$1;
	}

	// コントロールの状態を取得
	regExp = /zoomControl:\s*(.*)\s*,/;
	if (regExp.test(script)) this.zoomControl = (RegExp.$1 == 'true');
	regExp = /panControl:\s*(.*)\s*,/;
	if (regExp.test(script)) this.panControl = (RegExp.$1 == 'true');
	regExp = /scaleControl:\s*(.*)\s*,/;
	if (regExp.test(script)) this.scaleControl = (RegExp.$1 == 'true');
	regExp = /mapTypeControl:\s*(.*)\s*,/;
	if (regExp.test(script)) this.mapTypeControl = (RegExp.$1 == 'true');
	regExp = /streetViewControl:\s*(.*)\s*,/;
	if (regExp.test(script)) this.streetViewControl = (RegExp.$1 == 'true');
	regExp = /rotateControl:\s*(.*)\s*,/;
	if (regExp.test(script)) this.rotateControl = (RegExp.$1 == 'true');
	regExp = /overviewMapControl:\s*(.*)\s*,/;
	if (regExp.test(script)) this.overviewMapControl = (RegExp.$1 == 'true');
	
	// マーカーを追加
	var regexpMarkers = /\{lat\:(-?\d{1,3}\.\d{1,6}),\s*lon\:(-?\d{1,3}\.\d{1,6}),\s*text\:("|')(.*)\3}(?:,|])/;
	var point;
	var sampleText = script;
	var startIndex = 0;
	var totalLength = sampleText.length;
	var result, pos;
	while (startIndex != totalLength) {
		result = regexpMarkers.exec(sampleText);
		if (result && result.length > 0) {
			pos = sampleText.indexOf(result[0]);
			startIndex += pos;

			this.markerPoints.push( {lat:result[1], lon:result[2], text:this.decodeText(result[4])} );

			sampleText = sampleText.substr(pos + result[0].length);
			startIndex += result[0].length;
		} else {
			break;
		}
	}
//	var encodedPoints = "iuowFf{kbMzH}N`IbJb@zBpYzO{dAvfF{LwDyN`_@`NzKqB|Ec@|L}BKmBbCoPjrBeEdy@uJ`Mn@zoAer@bjA~Xz{JczBa]pIps@de@tW}rCdxSwhPl`XgikCl{soA{dLdAaaF~cCyxCk_Aao@jp@kEvnCgoJ`]y[pVguKhCkUflAwrEzKk@yzCv^k@?mI";
	var regexpLinePoints = /var encodedPoints\s*=\s*("|')(.*)\1;\s*\n/;
	if (regexpLinePoints.test(script))
	{
		this.linePoints = RegExp.$2;
	}

//	var encodedLevels = "B????????????????????????????????????B";
	var regexpLineLevels = /var encodedLevels\s*=\s*("|')(.*)\1;\s*\n/;
	if (regexpLineLevels.test(script))
	{
		this.lineLevels = RegExp.$2;
	}

// 1.8 mapType
//	map.setMapType( allMapTypes[ 1 ] );
	//var regexpMapType = /setMapType\([^\[]*\[\s*(\d+)\s*\]\s*\)/;
	var regexpMapType = /setMapTypeId\([^\[]*\[\s*(\d+)\s*\]\s*\)/;
	if (regexpMapType.test(script))
	{
		this.mapType = RegExp.$1;
	}
	
	// スタイル
	var regexpStyle = /var mapStyle\s*=\s*(.*)\s*;/;
	if (regexpStyle.test(script))
	{
		this.style = RegExp.$1;
	}
	return true;
}

GoogleMap.prototype.buildScript = function()
{
	var versionMarker = '// Magic3 googlemaps v1.00 mapid:' + this.number;

	var aScript = [];
	aScript.push('<script type="text/javascript">');
	aScript.push('//<![CDATA[');
	aScript.push(versionMarker);

	aScript.push('$(function(){');
	if (this.style == ''){
		aScript.push('	var allMapTypes = [	google.maps.MapTypeId.ROADMAP,');
	} else {
		aScript.push('	var mapStyle = ' + this.style + ';');
		aScript.push('	var allMapTypes = [	"original",');
	}
	aScript.push('						google.maps.MapTypeId.SATELLITE,');
	aScript.push('						google.maps.MapTypeId.HYBRID,');
	aScript.push('						google.maps.MapTypeId.TERRAIN	];');
	aScript.push('	var opts = {');
	if (this.zoomControl != 'undefined')		aScript.push('					zoomControl: ' + this.zoomControl + ',');
	if (this.panControl != 'undefined')			aScript.push('					panControl: ' + this.panControl + ',');
	if (this.scaleControl != 'undefined')		aScript.push('					scaleControl: ' + this.scaleControl + ',');
	if (this.mapTypeControl != 'undefined')		aScript.push('					mapTypeControl: ' + this.mapTypeControl + ',');
	if (this.streetViewControl != 'undefined')	aScript.push('					streetViewControl: ' + this.streetViewControl + ',');
	if (this.rotateControl != 'undefined')		aScript.push('					rotateControl: ' + this.rotateControl + ',');
	if (this.overviewMapControl != 'undefined')	aScript.push('					overviewMapControl: ' + this.overviewMapControl + ',');
	aScript.push('					mapTypeControlOptions: {	mapTypeIds: allMapTypes } };');
			 
	aScript.push('	var mapDiv = document.getElementById("gmap' + this.number + '");');
	aScript.push('	var map = new google.maps.Map(mapDiv, opts);');
	if (this.style != ''){
		aScript.push('	var originalMapType = new google.maps.StyledMapType(mapStyle, { name: "地図" });');
		aScript.push('	map.mapTypes.set("original", originalMapType);');
		aScript.push('	map.setMapTypeId("original");');
	}
	aScript.push('	map.setMapTypeId(allMapTypes[' + this.mapType + ']);');
	
	aScript.push('	map.setCenter(new google.maps.LatLng(' + this.centerLat + ', ' + this.centerLon + '));');
	aScript.push('	map.setZoom(' + this.zoom + ');');
	aScript.push('	mapDiv.style.display = "";');

	var aPoints = [];
	for (var i = 0; i < this.markerPoints.length; i++)
	{
		var point = this.markerPoints[i];
		aPoints.push('{lat:' + point.lat + ', lon:' + point.lon + ', text:\'' + this.encodeText(point.text) + '\'}');	
	}
	aScript.push('	m3GooglemapsAddMarkers(map, [' + aPoints.join(',\r\n') + ']);');

	if ((this.linePoints && this.linePoints != '') && (this.lineLevels && this.lineLevels != ''))
	{
		aScript.push('	var encodedPoints = "' + this.linePoints + '";');
		aScript.push('	var encodedLevels = "' + this.lineLevels + '";');
		aScript.push('	var polylinePoints = m3GooglemapsDecodePolyline(encodedPoints)');
		aScript.push('	var encodedPolyline = new google.maps.Polyline({	strokeColor:"#3333cc",');
		aScript.push('														strokeWeight:5,');
		aScript.push('														path:polylinePoints		});');
		aScript.push('	encodedPolyline.setMap(map);');
	}
	aScript.push('});');
	aScript.push('//]]>');
	aScript.push('</script>');

	return aScript.join('\r\n');
}
Number.prototype.RoundTo = function(precission)
{
	var base = Math.pow(10, precission);
	return Math.round(this * base) / base;
};
