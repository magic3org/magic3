/*
 * Googlemaps Plugin for FCKeditor
 * Based on version 1.98 by Alfonso Martinez de Lizarrondo
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: fckplugin.js 5256 2012-09-30 08:44:13Z fishbone $
 * @link       http://www.magic3.org
 */
// Toolbar button

// Register the related command.
FCKCommands.RegisterCommand('googlemaps', new FCKDialogCommand('googlemaps', FCKLang.DlgGMapsTitle, FCKPlugins.Items['googlemaps'].Path + 'dialog/googleMaps.html', 450, 428));

// Create the "googlemaps" toolbar button.
var oGoogleMapsButton = new FCKToolbarButton('googlemaps', FCKLang.GMapsBtn, FCKLang.GMapsBtnTooltip);
oGoogleMapsButton.IconPath = FCKPlugins.Items['googlemaps'].Path + 'images/mapIcon.gif';
FCKToolbarItems.RegisterItem('googlemaps', oGoogleMapsButton);

// Detection of existing maps
/**
	FCKCommentsProcessor
	---------------------------
	It's run after a document has been loaded, it detects all the protected source elements

	In order to use it, you add your comment parser with 
	FCKCommentsProcessor.AddParser( function )
*/
if (typeof FCKCommentsProcessor === 'undefined')
{
	var FCKCommentsProcessor = FCKDocumentProcessor.AppendNew();
	FCKCommentsProcessor.ProcessDocument = function( oDoc )
	{
		if (FCK.EditMode != FCK_EDITMODE_WYSIWYG) return;

		if (!oDoc) return;

	//Find all the comments: <!--{PS..0}-->
	//try to choose the best approach according to the browser:
		if (oDoc.evaluate){
			this.findCommentsXPath(oDoc);
		} else {
			if (oDoc.all){
				this.findCommentsIE(oDoc.body);
			} else {
				this.findComments(oDoc.body);
			}
		}
	}

	FCKCommentsProcessor.findCommentsXPath = function(oDoc){
		var nodesSnapshot = oDoc.evaluate('//body//comment()', oDoc.body, null, XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null);

		for (var i=0; i < nodesSnapshot.snapshotLength; i++)
		{
			this.parseComment(nodesSnapshot.snapshotItem(i));
		}
	}

	FCKCommentsProcessor.findCommentsIE = function(oNode){
		var aComments = oNode.getElementsByTagName('!');
		for (var i = aComments.length -1; i >= 0; i--)
		{
			var comment = aComments[i];
			if (comment.nodeType == 8) this.parseComment(comment);
		}
	}

	// Fallback function, iterate all the nodes and its children searching for comments.
	FCKCommentsProcessor.findComments = function(oNode)
	{
		if (oNode.nodeType == 8) // oNode.COMMENT_NODE) 
		{
			this.parseComment( oNode );
		} else {
			if (oNode.hasChildNodes())
			{
				var children = oNode.childNodes;
				for (var i = children.length -1; i >= 0; i--){
					this.findComments(children[ i ]);
				}
			}
		}
	}

	// We get a comment node
	// Check that it's one that we are interested on:
	FCKCommentsProcessor.parseComment = function(oNode)
	{
		var value = oNode.nodeValue;

		// Difference between 2.4.3 and 2.5
		var prefix = (FCKConfig.ProtectedSource._CodeTag || 'PS\\.\\.');

		var regex = new RegExp("\\{" + prefix + "(\\d+)\\}", "g");

		if (regex.test(value))
		{
			var index = RegExp.$1;
			var content = FCKTempBin.Elements[index];

			// Now call the registered parser handlers.
			var oCalls = this.ParserHandlers;
			if (oCalls)
			{
				for (var i = 0; i < oCalls.length; i++){
					oCalls[i](oNode, content, index);
				}
			}
		}
	}

	/**
		The users of the object will add a parser here, the callback function gets two parameters:
			oNode: it's the node in the editorDocument that holds the position of our content
			oContent: it's the node (removed from the document) that holds the original contents
			index: the reference in the FCKTempBin of our content
	*/
	FCKCommentsProcessor.AddParser = function(handlerFunction)
	{
		if (!this.ParserHandlers){
			this.ParserHandlers = [handlerFunction];
		} else {
			// Check that the event handler isn't already registered with the same listener
			// It doesn't detect function pointers belonging to an object (at least in Gecko)
			if (this.ParserHandlers.IndexOf(handlerFunction) == -1) this.ParserHandlers.push(handlerFunction);
		}
	}
}
/**
	END of FCKCommentsProcessor
	---------------------------
*/
// Check if the comment it's one of our scripts:
var GoogleMaps_CommentsProcessorParser = function(oNode, oContent, index)
{
	if ( FCK.GoogleMapsHandler.detectMapScript(oContent))
	{
		var oMap = FCK.GoogleMapsHandler.createNew();
		oMap.parse(oContent);
		oMap.createHtmlElement(oNode, index);
	} else {
		if (FCK.GoogleMapsHandler.detectGoogleScript(oContent)) oNode.parentNode.removeChild(oNode);
	}
}

FCKCommentsProcessor.AddParser(GoogleMaps_CommentsProcessorParser);

// Context menu
FCK.ContextMenu.RegisterListener( {
	AddItems : function(menu, tag, tagName)
	{
		// under what circumstances do we display this option
		if (tagName == 'IMG' && tag.getAttribute('MapNumber'))
		{
			// No other options:
			menu.RemoveAllItems();
			// the command needs the registered command name, the title for the context menu, and the icon path
			menu.AddItem( 'googlemaps', FCKLang.DlgGMapsTitle, oGoogleMapsButton.IconPath);
		}
	}}
);

// Double click
FCK.RegisterDoubleClickHandler(editMap, 'IMG');

function editMap(oNode)
{
	if (!oNode.getAttribute('MapNumber')) return;

	FCK.Commands.GetCommand('googlemaps').Execute();
}
// Object that handles the common functions about all the maps
FCK.GoogleMapsHandler = {
	// Object to store a reference to each map
	maps: {},

	getMap: function(id){
		return this.maps[id];
	},

	// Verify that the node is a script generated by this plugin.
	detectMapScript: function( script )
	{
		// We only know about version 1:
		//if (!(/FCK googlemaps v1\.(\d+)/.test(script))) return false;
		if (!(/FCK googlemaps v2\.(\d+)/.test(script))) return false;

		return true
	},

	// Detects both the google script as well as our ending block
	// both must be removed and then added later only if neccesary
	detectGoogleScript: function( script )
	{
		// Our final script
		//if (/FCK googlemapsEnd v1\./.test(script)) return true;
		if (/FCK googlemapsEnd v2\./.test(script)) return true;

//		if ( !/^<script src="http:\/\/maps\.google\.com\/.*key=(.*?)("|&)/.test(script) )
//			return false;
//		this.publicKey = RegExp.$1;
		return ( true );
	},

	GenerateGoogleScript : function()
	{
		//return '\r\n<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' + this.publicKey + '" type="text/javascript" charset="utf-8"></script>';
		return '\r\n<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript" charset="utf-8"></script>';
	},

	// This can be called from the dialog
	createNew: function()
	{
		var map = new FCKGoogleMap();
		this.maps[map.number] = map;
		return map;
	},

	BuildEndingScript: function()
	{
		var versionMarker = '// FCK googlemapsEnd v2.00';

		var aScript = [];
		aScript.push('\r\n<script type="text/javascript">');
		aScript.push('//<![CDATA[');
		aScript.push(versionMarker);

		aScript.push('function AddMarkers(map, aPoints)');
		aScript.push('{');
		aScript.push('	for (var i = 0; i < aPoints.length; i++)');
		aScript.push('	{');
		aScript.push('		var point = aPoints[i];');
		//aScript.push('		map.addOverlay(createMarker(new GLatLng(point.lat, point.lon), point.text));');
		aScript.push('		createMarker(map, new google.maps.LatLng(point.lat, point.lon), point.text);');
		aScript.push('	}');
		aScript.push('}');

		aScript.push('function createMarker(map, point, html)');
		aScript.push('{');
		//aScript.push('	var marker = new GMarker(point);');
		aScript.push('	var marker = new google.maps.Marker({ position: point, map: map });');
		//aScript.push('	GEvent.addListener(marker, "click", function(){');
		aScript.push('	google.maps.event.addListener(marker, "click", function(){');
		//aScript.push('		marker.openInfoWindowHtml(html, {maxWidth:200});');
		aScript.push('		new google.maps.InfoWindow({ content: html, maxWidth: 200 }).open(map, marker);');
		aScript.push('	});');
		aScript.push('	return marker;');
		aScript.push('}');

		var maps = this.CreatedMapsNames;
		for (var i = 0; i < maps.length; i++)
		{
			// Append event listeners instead of replacing previous ones
			aScript.push('if (window.addEventListener) {');
			aScript.push('    window.addEventListener("load", CreateGMap' + maps[i]  + ', false);');
			aScript.push('} else {');
			aScript.push('    window.attachEvent("onload", CreateGMap' + maps[i]  + ');');
			aScript.push('}');
		}

//		aScript.push('onunload = GUnload;');

		// ポリライン用データ作成
		aScript.push('function decodePolyline(encodedValue)');
		aScript.push('{');
		aScript.push('	var points = [];');
		aScript.push('	encodedValue = encodedValue.replace(/\\\\\\\\/g, "\\\\");');
		aScript.push('	if (encodedValue.length == 0) return points;');
		aScript.push('	points = getPolylinePoints(encodedValue);');
		aScript.push('	return points;');
		aScript.push('}');
		
		aScript.push('function getPolylinePoints(encoded)');
		aScript.push('{');
		aScript.push('	var len = encoded.length;');
		aScript.push('	var index = 0;');
		aScript.push('	var array = [];');
		aScript.push('	var lat = 0;');
		aScript.push('	var lng = 0;');
		aScript.push('');
		aScript.push('	while (index < len){');
		aScript.push('		var b;');
		aScript.push('		var shift = 0;');
		aScript.push('		var result = 0;');
		aScript.push('		do {');
		aScript.push('			b = encoded.charCodeAt(index++) - 63;');
		aScript.push('			result |= (b & 0x1f) << shift;');
		aScript.push('			shift += 5;');
		aScript.push('		} while (b >= 0x20);');
		aScript.push('		var dlat = ((result & 1) ? ~(result >> 1) : (result >> 1));');
		aScript.push('		lat += dlat;');
		aScript.push('');
		aScript.push('		shift = 0;');
		aScript.push('		result = 0;');
		aScript.push('		do {');
		aScript.push('			b = encoded.charCodeAt(index++) - 63;');
		aScript.push('			result |= (b & 0x1f) << shift;');
		aScript.push('			shift += 5;');
		aScript.push('		} while (b >= 0x20);');
		aScript.push('		var dlng = ((result & 1) ? ~(result >> 1) : (result >> 1));');
		aScript.push('		lng += dlng;');
		aScript.push('');
		//aScript.push('		array.push([lat * 1e-5, lng * 1e-5]);');
		aScript.push('		array.push(new google.maps.LatLng(lat * 1e-5, lng * 1e-5));');
		aScript.push('	}');
		aScript.push('	return array;');
		aScript.push('}');
		
		aScript.push('//]]>');
		aScript.push('</script>');

		return aScript.join('\r\n');
	},

	// We will use this to track the number of maps that are generated
	// This way we know if we must add the Google Script or not.
	// We store their names so they are called properly from BuildEndingScript
	CreatedMapsNames : [],

	// Function that will be injected into the normal core
	GetXHTMLAfter: function( node, includeNode, format, Result )
	{
		if (FCK.GoogleMapsHandler.CreatedMapsNames.length > 0)
		{
			Result += FCK.GoogleMapsHandler.BuildEndingScript();
		}
		// Reset the counter each time the GetXHTML function is called
		FCK.GoogleMapsHandler.CreatedMapsNames = [];

		return Result;
	},

	// Store any previous processor so nothing breaks
	previousProcessor: FCKXHtml.TagProcessors[ 'img' ] 
}
// Our object that will handle parsing of the script and creating the new one.
var FCKGoogleMap = function() 
{
	var now = new Date();
	this.number = '' + now.getFullYear() + now.getMonth() + now.getDate() + now.getHours() + now.getMinutes() + now.getSeconds();

	this.width = FCKConfig.GoogleMaps_Width || 400;
	this.height = FCKConfig.GoogleMaps_Height || 240;

	this.centerLat = FCKConfig.GoogleMaps_CenterLat || 35.594757;
	this.centerLon =  FCKConfig.GoogleMaps_CenterLon || 139.620739;
	this.zoom = FCKConfig.GoogleMaps_Zoom || 11;

	this.markerPoints = [];

	this.LinePoints = '';
	this.LineLevels = '';

	this.mapType = 0;

	this.WrapperClass = FCKConfig.GoogleMaps_WrapperClass || '';
}
FCKGoogleMap.prototype.createHtmlElement = function(oReplacedNode, index)
{
	var oFakeNode = FCK.EditorDocument.createElement('IMG');

	// Are we creating a new map?
	if (!oReplacedNode)
	{
    index = FCKTempBin.AddElement(this.BuildScript());
		var prefix = (FCKConfig.ProtectedSource._CodeTag || 'PS..');
		oReplacedNode = FCK.EditorDocument.createComment('{' + prefix + index + '}');
		FCK.InsertElement(oReplacedNode);
	}
	oFakeNode.contentEditable = false;
//	oFakeNode.setAttribute('_fckfakelement', 'true', 0);

	oFakeNode.setAttribute('_fckrealelement', FCKTempBin.AddElement( oReplacedNode ), 0);
	oFakeNode.setAttribute('_fckBinNode', index, 0);

	oFakeNode.src = FCKConfig.FullBasePath + 'images/spacer.gif';
	oFakeNode.style.display = 'block';
	oFakeNode.style.border = '1px solid black';
	oFakeNode.style.background = 'white center center url("' + FCKPlugins.Items['googlemaps'].Path + 'images/maps_res_logo.png' + '") no-repeat';

	oFakeNode.setAttribute("MapNumber", this.number, 0);

	oReplacedNode.parentNode.insertBefore(oFakeNode, oReplacedNode);
	oReplacedNode.parentNode.removeChild(oReplacedNode);

	// dimensions
	this.updateHTMLElement(oFakeNode);

	return oFakeNode;
}

FCKGoogleMap.prototype.updateScript = function( oFakeNode )
{
	this.updateDimensions( oFakeNode );

	var index = oFakeNode.getAttribute( '_fckBinNode' );
	FCKTempBin.Elements[ index ] =  this.BuildScript();
}

FCKGoogleMap.prototype.updateHTMLElement = function( oFakeNode )
{
	oFakeNode.width = this.width;
	oFakeNode.height = this.height;

	// Static maps preview :-)
	oFakeNode.src = this.generateStaticMap();
	oFakeNode.style.border = 0;

	// The wrapper class is applied to the IMG not to a wrapping DIV !!!
	if (this.WrapperClass !== '') oFakeNode.className = this.WrapperClass;
}

FCKGoogleMap.prototype.generateStaticMap = function()
{
	var w = Math.min(this.width, 640);
	var h = Math.min(this.height, 640);
	var staticMapTypes = ['roadmap', 'satellite', 'hybrid', 'terrain'];
/*
	return 'http://maps.google.com/staticmap?center=' + this.centerLat + ',' + this.centerLon 
		+ '&zoom=' + this.zoom + '&size=' + w + 'x' + h 
		+ '&maptype=' + staticMapTypes[ this.mapType ]
		+ this.generateStaticMarkers()
		+ '&key=' + FCKConfig.GoogleMaps_Key;*/
	var mapUrl = 'http://maps.google.com/maps/api/staticmap?center=' + this.centerLat + ',' + this.centerLon 
					+ '&zoom=' + this.zoom + '&size=' + w + 'x' + h 
					+ '&maptype=' + staticMapTypes[ this.mapType ]
					+ this.generateStaticMarkers() + '&sensor=false';
	return mapUrl;
}

FCKGoogleMap.prototype.generateStaticMarkers = function()
{
	if (this.markerPoints.length == 0) return '';

	var aPoints = [];
	for (var i=0; i < this.markerPoints.length; i++)
	{
		var point = this.markerPoints[i];
		aPoints.push(point.lat + ',' + point.lon);	
	}
	return ('&markers=' + aPoints.join('|'));
}

// Paths: http://code.google.com/p/gmaps-api-issues/issues/detail?id=205


// Read the dimensions back from the fake node (the user might have manually resized it)
FCKGoogleMap.prototype.updateDimensions = function( oFakeNode )
{
	var iWidth, iHeight;
	var regexSize = /^\s*(\d+)px\s*$/i;

	if (oFakeNode.style.width)
	{
		var aMatchW  = oFakeNode.style.width.match(regexSize);
		if (aMatchW)
		{
			iWidth = aMatchW[1];
			oFakeNode.style.width = '';
			oFakeNode.width = iWidth;
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
		}
	}

	this.width	= iWidth ? iWidth : oFakeNode.width;
	this.height	= iHeight ? iHeight : oFakeNode.height;
}

FCKGoogleMap.prototype.decodeText = function(string)
{
	return string.replace(/<\\\//g, "</").replace(/\\n/g, "\n").replace(/\\'/g, "'").replace(/\\\\/g, "\\");
}
FCKGoogleMap.prototype.encodeText = function(string)
{
	return string.replace(/\\/g, "\\\\").replace(/'/g, "\\'").replace(/\n/g, "\\n").replace(/<\//g, "<\\/");
}

FCKGoogleMap.prototype.parse = function( script )
{
	// We only know about version 1:
	//if (!(/FCK googlemaps v1\.(\d+)/.test(script))) return false;
	if (!(/FCK googlemaps v2\.(\d+)/.test(script))) return false;

//	var version = parseInt(RegExp.$1, 10);

	// マップ高さ、幅を取得
//	document.writeln('<div id="gmap1" style="width: 544px; height: 350px;">.</div>');
	var regexpDimensions = /<div id="gmap(\d+)" style="width\:\s*(\d+)px; height\:\s*(\d+)px;">/;
	if (regexpDimensions.test( script ) )
	{
		delete FCK.GoogleMapsHandler.maps[this.number];
		this.number = RegExp.$1;
		FCK.GoogleMapsHandler.maps[this.number] = this;

		this.width = RegExp.$2;
		this.height = RegExp.$3;
	}

	// マップ位置座標を取得
//	map.setCenter(new GLatLng(42.4298,-8.07756), 8);
	//var regexpPosition = /map\.setCenter\(new GLatLng\((-?\d{1,3}\.\d{1,6}),(-?\d{1,3}\.\d{1,6})\), (\d{1,2})\);/;
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
/*
// v <= 1.5
	if ( version<=5 )
	{
	//	var text = 'En O Carballino ha estado la d\'elegacion diplomatica japonesa';
		var markerText, markerLat=0, markerLon=0;
		var regexpText = /var text\s*=\s*("|')(.*)\1;\s*\n/;
		if (regexpText.test( script ) )
		{
			markerText = RegExp.$2;
		}

	//	var point = new GLatLng(42.4298,-8.07756);
		var regexpMarker = /var point\s*=\s*new GLatLng\((-?\d{1,3}\.\d{1,6}),(-?\d{1,3}\.\d{1,6})\)/;
		if (regexpMarker.test( script ) )
		{
			markerLat = RegExp.$1;
			markerLon = RegExp.$2;
		}
		if (markerLat!=0 && markerLon!=0)
			this.markerPoints.push( {lat:markerLat, lon:markerLon, text:this.decodeText(markerText)} );
	}
	else
	{
	// v > 1.5. multiple points.
*/
	// AddMarkers( [{lat:37.45088, lon:-122.21123, text:'Write your text'}] );
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
	/*
		while (result = regexpMarkers.exec( script ) )
		{
			this.markerPoints.push( {lat:result[1], lon:result[2], text:result[4]} );
		}
	*/
//	}

//	var encodedPoints = "iuowFf{kbMzH}N`IbJb@zBpYzO{dAvfF{LwDyN`_@`NzKqB|Ec@|L}BKmBbCoPjrBeEdy@uJ`Mn@zoAer@bjA~Xz{JczBa]pIps@de@tW}rCdxSwhPl`XgikCl{soA{dLdAaaF~cCyxCk_Aao@jp@kEvnCgoJ`]y[pVguKhCkUflAwrEzKk@yzCv^k@?mI";
	var regexpLinePoints = /var encodedPoints\s*=\s*("|')(.*)\1;\s*\n/;
	if (regexpLinePoints.test(script))
	{
		this.LinePoints = RegExp.$2;
	}

//	var encodedLevels = "B????????????????????????????????????B";
	var regexpLineLevels = /var encodedLevels\s*=\s*("|')(.*)\1;\s*\n/;
	if (regexpLineLevels.test(script))
	{
		this.LineLevels = RegExp.$2;
	}

// 1.8 mapType
//	map.setMapType( allMapTypes[ 1 ] );
	//var regexpMapType = /setMapType\([^\[]*\[\s*(\d+)\s*\]\s*\)/;
	var regexpMapType = /setMapTypeId\([^\[]*\[\s*(\d+)\s*\]\s*\)/;
	if (regexpMapType.test(script))
	{
		this.mapType = RegExp.$1;
	}

// 1.9 wrapper div with custom class
//	if ( version >= 9 )
//	{
	var regexpWrapper = /<div class=("|')(.*)\1.*\/\/wrapper/;
	if (regexpWrapper.test(script)){
		this.WrapperClass = RegExp.$2;
	} else {
		this.WrapperClass = '';
	}
//	}

	return true;
}

FCKGoogleMap.prototype.BuildScript = function()
{
	var versionMarker = '// FCK googlemaps v2.00';

	var aScript = [];
	aScript.push('\r\n<script type="text/javascript">');
	aScript.push('//<![CDATA[');
	aScript.push(versionMarker);

	if (this.WrapperClass !== '') aScript.push('document.write(\'<div class="' + this.WrapperClass + '">\'); //wrapper');
	aScript.push('document.write(\'<div id="gmap' + this.number + '" style="width:' + this.width + 'px; height:' + this.height + 'px;display:none;">.<\\\/div>\');');
	if (this.WrapperClass !== '') aScript.push('document.write(\'<\\\/div>\'); ');

	aScript.push('function CreateGMap' + this.number + '() {');
//	aScript.push('	if(!GBrowserIsCompatible()) return;');
	
/*
	aScript.push('	var allMapTypes = [G_NORMAL_MAP, G_SATELLITE_MAP, G_HYBRID_MAP, G_PHYSICAL_MAP];');
	aScript.push('	var map = new GMap2(document.getElementById("gmap' + this.number + '"), {mapTypes:allMapTypes});');
	aScript.push('	map.setCenter(new GLatLng(' + this.centerLat + ',' + this.centerLon + '), ' + this.zoom + ');');
	aScript.push('	map.setMapType( allMapTypes[ ' + this.mapType + ' ] );');
	aScript.push('	map.addControl(new GSmallMapControl());');
	aScript.push('	map.addControl(new GMapTypeControl());');
*/
	aScript.push('	var allMapTypes = [	google.maps.MapTypeId.ROADMAP,');
	aScript.push('						google.maps.MapTypeId.SATELLITE,');
	aScript.push('						google.maps.MapTypeId.HYBRID,');
	aScript.push('						google.maps.MapTypeId.TERRAIN	];');
	//aScript.push('	var centerPos = new google.maps.LatLng(' + this.centerLat + ', ' + this.centerLon + ');');
	//aScript.push('	var opts = { 	zoom:' + this.zoom + ',');
	//aScript.push('					center:centerPos,');
	//aScript.push('					mapTypeId: allMapTypes[' + this.mapType + '],');
	aScript.push('	var opts = {	mapTypeControlOptions: {	mapTypeIds: allMapTypes } };');
	aScript.push('	var mapDiv = document.getElementById("gmap' + this.number + '");');
	aScript.push('	var map = new google.maps.Map(mapDiv, opts);');
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
	aScript.push('	AddMarkers(map, [' + aPoints.join(',\r\n') + ']);');

	if ((this.LinePoints !== '') && (this.LineLevels !== ''))
	{
		aScript.push('var encodedPoints = "' + this.LinePoints + '";');
		aScript.push('var encodedLevels = "' + this.LineLevels + '";');
		aScript.push('var polylinePoints = decodePolyline(encodedPoints)');
/*		aScript.push('var encodedPolyline = new GPolyline.fromEncoded({');
		aScript.push('	color: "#3333cc",');
		aScript.push('	weight: 5,');
		aScript.push('	points: encodedPoints,');
		aScript.push('	levels: encodedLevels,');
		aScript.push('	zoomFactor: 32,');
		aScript.push('	numLevels: 4');
		aScript.push('	});');
		aScript.push('map.addOverlay(encodedPolyline);');*/
		aScript.push('var encodedPolyline = new google.maps.Polyline({	strokeColor:"#3333cc",');
		aScript.push('													strokeWeight:5,');
		aScript.push('													path:polylinePoints		});');
		aScript.push('encodedPolyline.setMap(map);');
	}
	aScript.push('}');
	aScript.push('//]]>');
	aScript.push('</script>');

	return aScript.join('\r\n');
}

// Modifications of the core routines of FCKeditor:

FCKXHtml.GetXHTML = Inject(FCKXHtml.GetXHTML, null, FCK.GoogleMapsHandler.GetXHTMLAfter);

FCKXHtml.TagProcessors.img = function(node, htmlNode, xmlNode)
{
	if (htmlNode.getAttribute('MapNumber'))
	{
		var oMap = FCK.GoogleMapsHandler.getMap(htmlNode.getAttribute('MapNumber'));
		FCK.GoogleMapsHandler.CreatedMapsNames.push(oMap.number);

		oMap.updateScript( htmlNode );
		node = FCK.GetRealElement( htmlNode );
		if (FCK.GoogleMapsHandler.CreatedMapsNames.length == 1)
		{
			// If it is the first map, insert the google maps script
			var index = FCKTempBin.AddElement( FCK.GoogleMapsHandler.GenerateGoogleScript() );
			var prefix = ( FCKConfig.ProtectedSource._CodeTag || 'PS..' );
			oScriptCommentNode = xmlNode.ownerDocument.createComment( '{' + prefix + index + '}' );
			xmlNode.appendChild( oScriptCommentNode );
		}

		return xmlNode.ownerDocument.createComment( node.nodeValue );
	}

	if (typeof FCK.GoogleMapsHandler.previousProcessor == 'function'){
		node = FCK.GoogleMapsHandler.previousProcessor(node, htmlNode, xmlNode);
	} else {
		node = FCKXHtml._AppendChildNodes(node, htmlNode, false);
	}

	return node;
};

/**
  @desc  inject the function
  @author  Aimingoo&Riceball
*/
function Inject(aOrgFunc, aBeforeExec, aAtferExec)
{
	return function(){
		if (typeof(aBeforeExec) == 'function') arguments = aBeforeExec.apply(this, arguments) || arguments;
		//convert arguments object to array
		var Result, args = [].slice.call(arguments); 
		args.push(aOrgFunc.apply(this, args));
		if (typeof(aAtferExec) == 'function') Result = aAtferExec.apply(this, args);
		return (typeof(Result) != 'undefined')?Result:args.pop();
	};
}
