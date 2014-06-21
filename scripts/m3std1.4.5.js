/**
 * Magic3標準JavaScriptライブラリ
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
/**
 * 数値入力判断
 *   テキストフィールドを数値のみ入力可能にするために使用(onkeypressイベントに設定)
 * @param event  e			イベント
 * @param bool   isFloat	小数点入力可否
 * @return bool             true=入力可能、false=入力不可
 */
function m3_numericKey(e, isFloat)
{
	if (document.all){	// IE
		code = event.keyCode;
	} else {		// IE以外
		if (e.which == 0x00)
		{
			if (e.keyCode == 0x08) return true;	// バックスペース
			if (e.keyCode == 0x09) return true;	// タブ
			if (e.keyCode == 0x25) return true;	// 左
			if (e.keyCode == 0x27) return true;	// 右
			if (e.keyCode == 0x2e) return true;	// デリート
			return false;
		}
		
		if (e.which == 0x08) return true;
		
		code = e.which;
	}
	
	if (isFloat){	// 小数点入力可能のとき
		if (code == '.'.charCodeAt(0)) return true;
	}
	
	if (code < '0'.charCodeAt(0)) return false;
	if (code > '9'.charCodeAt(0)) return false;
	
	return true;
}
/**
 * 配列のソート
 *
 * @param array 	dataArray		ソート対象配列データ
 * @param int 		fieldIndex		何番目の要素でソートするか(0～)
 * @param int 		order         		ソート方向(0=昇順、1=降順)
 * @return array 				ソート後のデータ
 */
function m3_sortArray(dataArray, fieldIndex, order)
{
	if (!dataArray) return;
	if (order == null || order == 0){	// 昇順
		dataArray.sort(function(a,b){return a[fieldIndex] - b[fieldIndex];});
	} else {	// 降順
		dataArray.sort(function(a,b){return b[fieldIndex] - a[fieldIndex];});
	}
	return dataArray;
}
/**
 * URLからパラメータの連想配列を取得
 *
 * @param string  url			url
 * @return array 				解析後データ
 */
function m3_getUrlParam(url)
{
	var queryParam = new Object;
	var splitUrl = url.split("?", 2);
	var params = splitUrl[1].split('&');
	for(var i = 0; i < params.length; i++){
		var keyValue = params[i].split("=", 2);
		queryParam[keyValue[0]] = keyValue[1];
	}
	return queryParam;
}
/**
 * HTMLタグ属性文字列の解析
 *
 * @param string  str			url
 * @return array 				解析後データ
 */
function m3_splitAttr(str)
{
	var queryParam = new Object;
	if (!str) return queryParam;
	var params = str.split(";");
	for(var i = 0; i < params.length; i++){
		var keyValue = params[i].split(":", 2);
		if (keyValue[0] && keyValue[1]){
			var key = keyValue[0].trim();
			var value = keyValue[1].trim();
			queryParam[key] = value;
		}
	}
	return queryParam;
}
/**
 * カスケードメニューの作成
 *
 *  リストタグ(ul,li)からカスケードメニューを生成する
 *  サブメニューのある項目には、「hassubmenu」クラス名が設定される
 *
 * @param object  obj	トップのulタグのIDまたはオブジェクト自身
 * @return なし
 */
function m3_cascadeMenu(obj)
{
	if (typeof(obj) != "object") obj = document.getElementById(obj);

	var ultags = obj.getElementsByTagName("ul");
	for (var t = 0; t < ultags.length; t++){
		ultags[t].parentNode.getElementsByTagName("a")[0].className = "hassubmenu";
		if (ultags[t].parentNode.parentNode == obj){ //if this is a first level submenu
			ultags[t].style.left = ultags[t].parentNode.offsetWidth+"px"; //dynamically position first level submenus to be width of main menu item
		} else {//else if this is a sub level submenu (ul)
			ultags[t].style.left = ultags[t-1].getElementsByTagName("a")[0].offsetWidth + "px"; //position menu to the right of menu item that activated it
		}
		ultags[t].parentNode.onmouseover = function(){
			this.getElementsByTagName("ul")[0].style.display = "block";
		}
		ultags[t].parentNode.onmouseout = function(){
			this.getElementsByTagName("ul")[0].style.display = "none";
		}
	}
	for (var t = ultags.length -1; t > -1; t--){ //loop through all sub menus again, and use "display:none" to hide menus (to prevent possible page scrollbars
		ultags[t].style.visibility = "visible";
		ultags[t].style.display = "none";
	}
}
/**
 * ソート可能なボックス
 *
 * @param object  object			テーブルオブジェクトまたはテーブルID文字列
 * @param string  childTag			ソート項目のタグ種別
 * @return なし
 */
function m3_sortableBox(object, childTag)
{
	var sortitemhead = 'sortableitem_';		// 項目のid作成用
	var obj;		// 対象オブジェクト
	var tags = ["dl", "div", "li", "span"];
	var children;
	
	if (typeof object == "string"){
		obj = document.getElementById(object);
	} else {
		obj = object;
	}
	if (!obj){
		alert("null object.");
		return;
	}
	
	if (childTag){
		children = obj.getElementsByTagName(childTag);
	} else {
		for (var i = 0; i < tags.length; i++){
			children = obj.getElementsByTagName(tags[i]);
			if (children.length > 0) break;
		}
	}
	for (var i = 0; i < children.length; i++){
		children[i].id = sortitemhead + i;
	}
}
/**
 * ソート可能ボックスの項目の並び順を取得
 *
 * @param string  src			変換元文字列
 * @return string 				変換後データ
 */
function m3_parseSortableBoxIndex(src)
{
	var dest = '';
	if (!src) return dest;

	var sortitemhead = 'sortableitem_';		// 項目のid作成用
	var params = new String(src).split(',');
	for (var i = 0; i < params.length; i++){
		var index = new String(params[i]).replace(sortitemhead, '');
		dest += index;
		if (i < params.length -1) dest += ',';
	}
	return dest;
}
/**
 * 画像サイズのウィンドウを表示
 *
 * @param string  src	画像へのパス
 * @param string  title	ウィンドウタイトル
 * @return なし
 */
function m3_showImagePopup(src, title)
{
	// 画像をロードしてサイズを取得
	var img = new Image();
	img.src = src;

	// 画像のサイズのウィンドウを開く
	var popupWin = window.open(
							"",
							"_blank",
							"width=" + img.width + ",height=" + img.height + ",scrollbars=no,resizable=yes");

	var html = '<html><head>';
	if (title) html += '<title>' + title + '</title>';
	html += '</head>' +
				'<body style="margin:0;pading:0;border:0;">' +
				'<img src="' + img.src + '" width="100%" height="100%" alt="" />' +
			'</body>' +
			'</html>';
	
	// HTML書き出し
 	popupWin.document.open();
 	popupWin.document.write(html);
 	popupWin.document.close();
}
/**
 * 半透明度の設定
 *
 * @param object  elm	半透明化するオブジェクト
 * @param int opacity	半透明度(0～100)
 * @return なし
 */
function m3_setOpacity(elm, opacity){
    if("opacity" in elm.style){
        elm.style.opacity = opacity / 100;
    } else if("MozOpacity" in elm.style) {
        elm.style.MozOpacity = opacity / 100;
    } else if("KHTMLOpacity" in elm.style) {
        elm.style.KHTMLOpacity = opacity / 100;
    } else if("filters" in elm) {
        elm.style.filter = "Alpha(opacity=" + opacity + ")";
    }
}
/**
 * 半透明画像の表示
 *
 * @param event  e			イベント
 * @param string imageUrl	画像URL
 * @return なし
 */
function m3_showImageToolTip(e, imageUrl)
{
	if (document.all) e = event;
	
	var obj = document.getElementById('image_tooltip');
	obj.style.display = 'block';
	obj.style.position = 'absolute';     //positionセット
	var st = Math.max(document.body.scrollTop, document.documentElement.scrollTop);
	if (navigator.userAgent.toLowerCase().indexOf('safari') >= 0) st = 0;
	var leftPos = e.clientX - 100;
	if (leftPos < 0) leftPos = 0;
	obj.style.left = leftPos + 'px';
	obj.style.top = e.clientY - obj.offsetHeight -1 + st + 'px';
	
	var image = document.getElementById('image_tooltip_i');
	image.src = imageUrl;
	
	// 半透明化
	m3_setOpacity(obj, 95);
}
/**
 * 設定用ウィンドウ表示
 *
 * @param string url	表示するURL
 * @return なし
 */
function m3_showConfigWindow(url)
{
	window.open(url);
}
/**
 * 半透明画像を隠す
 *
 * @return なし
 */
function m3_hideImageToolTip()
{
	document.getElementById('image_tooltip').style.display = 'none';
}
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g, "");
}
String.prototype.ltrim = function() {
	return this.replace(/^\s+/, "");
}
String.prototype.rtrim = function() {
	return this.replace(/\s+$/, "");
}
String.prototype.startsWith = function(prefix) {
    if (prefix.length > this.length) return false;
    return prefix == this.substring(0, prefix.length);
}
String.prototype.endsWith = function(suffix) {
    if (suffix.length > this.length) return false;
    return suffix == this.slice(~suffix.length + 1);
}
/**
 * 数値での配列のソート
 *
 * @param int 		order         	ソート方向(0=昇順、1=降順)
 * @return array 					ソート後のデータ
 */
Array.prototype.sortByNumber = function(order){
	if (order == null || order == 0){	// 昇順
		$(this).sort(function(a, b){
			return (parseInt(a) > parseInt(b)) ? 1 : -1;
		});
	} else {	// 降順
		$(this).sort(function(a, b){
			return (parseInt(a) < parseInt(b)) ? 1 : -1;
		});
	}
}
/**
 * Googleマップ用の処理
 */
function m3GooglemapsAddMarkers(map, aPoints)
{
	for (var i = 0; i < aPoints.length; i++)
	{
		var point = aPoints[i];
		m3GooglemapsCreateMarker(map, new google.maps.LatLng(point.lat, point.lon), point.text);
	}
}
function m3GooglemapsCreateMarker(map, point, html)
{
	var marker = new google.maps.Marker({ position: point, map: map });
	google.maps.event.addListener(marker, "click", function(){
		new google.maps.InfoWindow({ content: html, maxWidth: 200 }).open(map, marker);
	});
	return marker;
}
function m3GooglemapsDecodePolyline(encodedValue)
{
	var points = [];
	encodedValue = encodedValue.replace(/\\\\/g, "\\");
	if (encodedValue.length == 0) return points;
	points = m3GooglemapsGetPolylinePoints(encodedValue);
	return points;
}
function m3GooglemapsGetPolylinePoints(encoded)
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

		array.push(new google.maps.LatLng(lat * 1e-5, lng * 1e-5));
	}
	return array;
}
/**
 * TextAreaをWYSIWYGエディターに変更
 *
 * @param string id			TextAreaタグのIDまたはname
 * @param int height		エディター領域の高さ
 * @return なし
 */
function m3SetSafeWysiwygEditor(id, height)
{
	var config = {};
	config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig_safe.js';
	if (height) config['height'] = height;
	config['toolbar'] = 'Safe';
	CKEDITOR.replace(id, config);
}