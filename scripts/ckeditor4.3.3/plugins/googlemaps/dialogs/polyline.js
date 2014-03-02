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
 * @version    SVN: $Id: polyline.js 6021 2013-05-20 04:21:13Z fishbone $
 * @link       http://www.magic3.org
 */
function Polyline()
{
	this.map;
	this.points = [];
	this.highlighted_marker = null;
	this.point_markers = [];
	this.currentIndex = -1;
	this.encodedLevels = '';
	this.encodedPolyline = '';
}
Polyline.prototype.setMap = function(map)
{
	this.map = map;
}
Polyline.prototype.findMarkerIndex = function(point_marker)
{
	var index = -1;

	for (var i = 0; i < this.point_markers.length; ++i){
		if (this.point_markers[i] == point_marker){
			index = i;
			break;
		}
	}
	return index;
}

Polyline.prototype.createPoint = function(lat, lng, pLevel)
{
	var newPoint = {	lat: lat,
						lon: lng,
						Level: pLevel };

	if (this.currentIndex > -1){
		this.points.splice(this.currentIndex + 1, 0, newPoint);
	} else {
		this.points.push(newPoint);
	}

	//var point_marker = this.createPointMarker(new GLatLng(lat, lng), false);
	var point_marker = this.createPointMarker(new google.maps.LatLng(lat, lng), false);
	point_marker.focusable = true; // To signal that the map must get the focus.

	//map.addOverlay(point_marker);
	point_marker.setMap(this.map);

	if (this.currentIndex > -1){
		this.point_markers.splice(this.currentIndex + 1, 0, point_marker);
	} else {
		this.point_markers.push(point_marker);
	}

	this.highlight(this.currentIndex + 1);
}

Polyline.prototype.createPointMarker = function(point, highlighted)
{
	var clr = highlighted ? "yellow" : "blue";
	var point_marker = this.createColorMarker(point, clr);

	//GEvent.addListener(point_marker, "drag", function(){
	google.maps.event.addListener(point_marker, 'drag', function(){
		var index = this.findMarkerIndex(point_marker);

		if (index >= 0){
//			var nLat = point_marker.getPoint().lat();
//			var nLng = point_marker.getPoint().lng();
			var nLat = point_marker.getPosition().lat();
			var nLng = point_marker.getPosition().lng();
			var pLevel = this.points[index].Level;

			var modifiedPoint = {	lat: nLat,
									lon: nLng,
									Level: pLevel	};

			this.points[index] = modifiedPoint;
			this.createEncodings();
		}
	});
/*  GEvent.addListener(point_marker, "click", function() {
    highlight(this.findMarkerIndex(point_marker));
  });*/
	google.maps.event.addListener(point_marker, "click", function(){
		this.highlight(this.findMarkerIndex(point_marker));
	});
	return point_marker;
}

Polyline.prototype.highlight = function(index)
{
	if (this.point_markers[index] != null && this.point_markers[index] != this.highlighted_marker){
		//map.removeOverlay(this.point_markers[index]);
		this.point_markers[index].setMap(null);
	}

	if (this.highlighted_marker != null){
		var oldIndex = this.findMarkerIndex(this.highlighted_marker);
		//map.removeOverlay(this.highlighted_marker);
		this.highlighted_marker.setMap(null);

		if (oldIndex != index){
			//this.point_markers[oldIndex] = this.createPointMarker(this.highlighted_marker.getPoint(), false);
			//map.addOverlay(this.point_markers[oldIndex]);
			this.point_markers[oldIndex] = this.createPointMarker(this.highlighted_marker.getPosition(), false);
			this.point_markers[oldIndex].setMap(this.map);
		}
	}

	//this.highlighted_marker = this.createPointMarker(this.point_markers[index].getPoint(), true);
	this.highlighted_marker = this.createPointMarker(this.point_markers[index].getPosition(), true);
	this.point_markers[index] = this.highlighted_marker;
	//map.addOverlay(this.highlighted_marker);
	this.highlighted_marker.setMap(this.map);

	this.currentIndex = index ;
}

Polyline.prototype.encodeSignedNumber = function(num)
{
	var sgn_num = num << 1;

	if (num < 0) sgn_num = ~(sgn_num);

	return (this.encodeNumber(sgn_num));
}

Polyline.prototype.encodeNumber = function(num)
{
	var encodeString = "";

	while (num >= 0x20) {
		encodeString += (String.fromCharCode((0x20 | (num & 0x1f)) + 63));
		num >>= 5;
	}

	encodeString += (String.fromCharCode(num + 63));
	return encodeString;
}

Polyline.prototype.deletePoint = function()
{
	if (this.points.length > 0){
		var point_index = this.currentIndex;

		if (point_index >= 0 && point_index < this.points.length){
			this.points.splice(point_index, 1);

			if (this.highlighted_marker == this.point_markers[point_index]){
				this.highlighted_marker = null;
				this.currentIndex=-1;
			}

			//map.removeOverlay(this.point_markers[point_index]);
			this.point_markers[point_index].setMap(null);
			this.point_markers.splice(point_index, 1);
			this.createEncodings();
		}

		if (this.points.length > 0){
			if (point_index == 0) point_index++;

			this.highlight(point_index - 1);
		}
	}
}
Polyline.prototype.createEncodings = function()
{
	if (this.points.length == 0)
	{
//		document.getElementById('this.encodedLevels').value = '';
//		document.getElementById('encodedPolyline').value = '';
		this.encodedLevels = '';
		this.encodedPolyline = '';
		if (document.overlay){
			//map.removeOverlay(document.overlay);
			document.overlay.setMap(null);
		}
		return;
	}

	var encoded_levels='';
	var encoded_points='';
	var vZoom, vLevels;

	vLevels = 4;
	vZoom = 32;

	var plat = 0;
	var plng = 0;
	var pathCoordinates = [];
	for (var i = 0; i < this.points.length; ++i){
		var point = this.points[i];
		var lat = point.lat;
		var lng = point.lon;
		var level = point.Level;

		var late5 = Math.floor(lat * 1e5);
		var lnge5 = Math.floor(lng * 1e5);

		dlat = late5 - plat;
		dlng = lnge5 - plng;

		plat = late5;
		plng = lnge5;

		encoded_points += this.encodeSignedNumber(dlat) + this.encodeSignedNumber(dlng);
		encoded_levels += this.encodeNumber(level);
		
		pathCoordinates.push(new google.maps.LatLng(lat, lng));
	}

//	document.getElementById('encodedLevels').value = encoded_levels.replace(/\\/g, "\\\\");
//	document.getElementById('encodedPolyline').value = encoded_points.replace(/\\/g, "\\\\");
	this.encodedLevels = encoded_levels.replace(/\\/g, "\\\\");
	this.encodedPolyline = encoded_points.replace(/\\/g, "\\\\");
	
	if (document.overlay){
		//map.removeOverlay(document.overlay);
		document.overlay.setMap(null);
	}

	if (this.points.length > 1){
		/*
		document.overlay = GPolyline.fromEncoded({	color: "#3333cc",
		                                          	weight: 5,
		                                          	points: encoded_points,
		                                          	zoomFactor: vZoom,
		                                          	levels: encoded_levels,
		                                          	numLevels: vLevels		});*/
		document.overlay = new google.maps.Polyline({	strokeColor:'#3333cc',
														strokeWeight:5,
														path:pathCoordinates	});

		//map.addOverlay(document.overlay);
		document.overlay.setMap(this.map);
	}
}
Polyline.prototype.decodeLine = function(encoded)
{
	var len = encoded.length;
	var index = 0;
	var array = [];
	var lat = 0;
	var lng = 0;

	while (index < len){
		var b;
		var shift = 0;
		var result = 0;
		do {
			b = encoded.charCodeAt(index++) - 63;
			result |= (b & 0x1f) << shift;
			shift += 5;
		} while (b >= 0x20);
		var dlat = ((result & 1) ? ~(result >> 1) : (result >> 1));
		lat += dlat;

		shift = 0;
		result = 0;
		do {
			b = encoded.charCodeAt(index++) - 63;
			result |= (b & 0x1f) << shift;
			shift += 5;
		} while (b >= 0x20);
		var dlng = ((result & 1) ? ~(result >> 1) : (result >> 1));
		lng += dlng;

		array.push([lat * 1e-5, lng * 1e-5]);
	}

	return array;
}

Polyline.prototype.decodeLevels = function(encoded)
{
	var levels = [];

	for (var pointIndex = 0; pointIndex < encoded.length; ++pointIndex){
		var pointLevel = encoded.charCodeAt(pointIndex) - 63;
		levels.push(pointLevel);
	}

	return levels;
}

Polyline.prototype.decodePolyline = function(encoded_points)
{
	encoded_points = encoded_points.replace(/\\\\/g, "\\");
	if (encoded_points.length == 0) return;

 	var enc_points = this.decodeLine(encoded_points);
	if (enc_points.length == 0) return;

	this.points = [];
	for (var i = 0; i < enc_points.length; ++i){
		this.createPoint(enc_points[i][0], enc_points[i][1], 3);
	}
	this.createEncodings();
}

Polyline.prototype.showLinePoints = function()
{
	if (this.points.length == 0) return;

	for (var i = 0; i < this.points.length; i++)
	{
		var point = this.points[i] ;
		//var point_marker = this.createPointMarker(new GLatLng(point.lat, point.lon), false);
		var point_marker = this.createPointMarker(new google.maps.LatLng(point.lat, point.lon), false);
		//map.addOverlay(point_marker);
		point_marker.setMap(this.map);
		
	  	this.point_markers.push(point_marker);
	}

	this.highlight(this.points.length - 1);
}

Polyline.prototype.hideLinePoints = function()
{
	for (var i = this.point_markers.length -1; i >= 0; i--)
	{
      //map.removeOverlay(this.point_markers[i]);
		this.point_markers[i].setMap(null);
	}
	this.point_markers = [] ;
	this.highlighted_marker = null;
	this.currentIndex=-1;
}

Polyline.prototype.createColorMarker = function(point, color)
{
	var pluginUrl = CKEDITOR.getUrl(CKEDITOR.plugins.getPath( 'googlemaps' ));
	var markerImage = new google.maps.MarkerImage(	pluginUrl + 'images/mm_20_' + color + '.png',
											new google.maps.Size(12, 20),	// size
											new google.maps.Point(0,0),		// origin
											new google.maps.Point(6, 20));	// anchor
	var shadowImage = new google.maps.MarkerImage(	pluginUrl + 'images/mm_20_shadow.png',
											new google.maps.Size(22, 20),	// size
											new google.maps.Point(0,0),		// origin
											new google.maps.Point(6, 20));	// anchor
	var newMarker = new google.maps.Marker({ position:point, icon:markerImage, shadow:shadowImage, draggable:true });
	return newMarker;
}
