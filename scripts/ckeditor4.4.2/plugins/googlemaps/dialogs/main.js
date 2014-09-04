/**
 * Magic3 CKEditorプラグイン
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    1.2
 * @link       http://www.magic3.org
 */

(function(){
	CKEDITOR.dialog.add('googlemaps', function(editor){
		var dialog;
		var mode;
		var mapDiv;
		var mapInfo;
		var mapObj;
		var markers = [];
		var activeMarker;
		var fieldZoom;
		var fieldCenterLatitude;
		var fieldCenterLongitude;
		var fakeImage;
		var infoWindow;
		var polyline;
		var inLoading;
		
		// スクリプト読み込み
		var pluginUrl = CKEDITOR.getUrl(CKEDITOR.plugins.getPath( 'googlemaps' ));
			
		var loadSelectionData = function()
		{
			inLoading = true;		// データロード中
			
			if (fakeImage){		// マップ選択からの起動の場合
				var className = fakeImage.$.className;
				var mapNumber;
				var regExp = /cke_googlemaps(\d+)/;
				if (regExp.test(className)) mapNumber = RegExp.$1;
				mapInfo = GoogleMapsHandler.getMap(mapNumber);
				if (!mapInfo) mapInfo = GoogleMapsHandler.createNew();
			} else {		// マップが選択されていない場合は新規作成
				mapInfo = GoogleMapsHandler.createNew();
			}

			// 入力値初期化
			dialog.setValueOf('tab_map', 'txtWidth', mapInfo.width);
			dialog.setValueOf('tab_map', 'txtHeight', mapInfo.height);
			dialog.setValueOf('tab_map', 'cmbWidthType', mapInfo.widthType);
			dialog.setValueOf('tab_map', 'cmbHeightType', mapInfo.heightType);
			dialog.setValueOf('tab_map', 'chkAlignCenter', mapInfo.alignCenter);
			dialog.setValueOf('tab_map', 'cmbZoom', mapInfo.zoom);
			dialog.setValueOf('tab_map', 'txtCenterLat', mapInfo.centerLat);
			dialog.setValueOf('tab_map', 'txtCenterLon', mapInfo.centerLon);
			dialog.setValueOf('tab_option', 'chkZoomControl', mapInfo.zoomControl);
			dialog.setValueOf('tab_option', 'chkPanControl', mapInfo.panControl);
			dialog.setValueOf('tab_option', 'chkScaleControl', mapInfo.scaleControl);
			dialog.setValueOf('tab_option', 'chkMapTypeControl', mapInfo.mapTypeControl);
			dialog.setValueOf('tab_option', 'chkStreetViewControl', mapInfo.streetViewControl);
			dialog.setValueOf('tab_option', 'chkRotateControl', mapInfo.rotateControl);
			dialog.setValueOf('tab_option', 'chkOverviewMapControl', mapInfo.overviewMapControl);
			dialog.setValueOf('tab_option', 'chkRoadmapMapType', mapInfo.roadmapMapType);
			dialog.setValueOf('tab_option', 'chkSatelliteMapType', mapInfo.satelliteMapType);
			dialog.setValueOf('tab_option', 'chkHybridMapType', mapInfo.hybridMapType);
			dialog.setValueOf('tab_option', 'chkTerrainMapType', mapInfo.terrainMapType);
			dialog.setValueOf('tab_style', 'txtStyle', mapInfo.style);
//			dialog.setValueOf('tab_line', 'txtEncodedPolyline', mapInfo.linePoints);
//			dialog.setValueOf('tab_line', 'txtEncodedLevels', mapInfo.lineLevels);
			
			inLoading = false;		// データロード終了
			
			// プレビューマップ作成
			createMap(mapInfo.mapType);
			
			// コントローラ設定
			changeMap();
				
			// マーカー設定
			var markerPoints = mapInfo.markerPoints;
			for (var i = 0; i < markerPoints.length; i++)
			{
				var point = new google.maps.LatLng(parseFloat(markerPoints[i].lat), parseFloat(markerPoints[i].lon));
				addMarkerAtPoint(point, markerPoints[i].text, false);
			}

			// ライン描画
			polyline.setMap(mapObj);
			polyline.decodePolyline(mapInfo.linePoints);
		};
		var createMap = function(mapType)
		{
			mapDiv = document.getElementById("gmapPreview" + editor.id);
			resizeMap();
	
			var allMapTypes = [	google.maps.MapTypeId.ROADMAP,
							google.maps.MapTypeId.SATELLITE,
							google.maps.MapTypeId.HYBRID,
							google.maps.MapTypeId.TERRAIN	];
			var opts = { 	mapTypeId:allMapTypes[mapType],
							mapTypeControlOptions:{	mapTypeIds:allMapTypes } };
			mapObj = new google.maps.Map(mapDiv, opts);

			updateMap();
			
			// イベント設定
			google.maps.event.addListener(mapObj, 'zoom_changed', function(){
				fieldZoom.value = mapObj.getZoom();
			});
			google.maps.event.addListener(mapObj, 'center_changed', function(){			// out of memory on IE8
				var point = mapObj.getCenter();
				fieldCenterLatitude.value = point.lat().RoundTo(5);
				fieldCenterLongitude.value = point.lng().RoundTo(5);
			});
			google.maps.event.addListener(mapObj, 'click', function(e){
				var point = e.latLng;
				var selectedTab = CKEDITOR.dialog.getCurrent().definition.dialog._.currentTabId;		// 選択中のタブIDを取得
				switch (selectedTab){
					case 'tab_map':
					case 'tab_option':
					case 'tab_search':
						break;
					case 'tab_marker':
						if (mode == 'AddMarker') addMarkerAtPoint(point, editor.lang.googlemaps.markerDefaultText || '', true);
						break;
					case 'tab_line':
						polyline.createPoint(point.lat(), point.lng(), 3);
						polyline.createEncodings(false);
					break;
				}
				//mapDiv.focus();
			});
			google.maps.event.addDomListener(mapDiv, 'keydown', function(e){
				if (!e) e = window.event;

				var iCode = (e.keyCode || e.charCode);
				var selectedTab = CKEDITOR.dialog.getCurrent().definition.dialog._.currentTabId;		// 選択中のタブIDを取得
				if (iCode == 46){
					switch(selectedTab){
						case 'tab_map':
						case 'tab_option':
						case 'tab_search':
						case 'tab_marker':
							break;
						case 'tab_line':
							polyline.deletePoint();
							break;
					}
				}
			});
		};
		var changeMap = function(id)
		{
			// ダイアログ用のデータ読み込み中のときは終了
			if (inLoading) return;
			
			var val;
			if (!id || id == 'chkZoomControl'){
				val = dialog.getValueOf('tab_option', 'chkZoomControl');
				mapObj.setOptions({ zoomControl: val });
			}
			if (!id || id == 'chkPanControl'){
				val = dialog.getValueOf('tab_option', 'chkPanControl');
				mapObj.setOptions({ panControl: val });
			}
			if (!id || id == 'chkScaleControl'){
				val = dialog.getValueOf('tab_option', 'chkScaleControl');
				mapObj.setOptions({ scaleControl: val });
			}
			if (!id || id == 'chkMapTypeControl'){
				val = dialog.getValueOf('tab_option', 'chkMapTypeControl');
				mapObj.setOptions({ mapTypeControl: val });
			}
			if (!id || id == 'chkStreetViewControl'){
				val = dialog.getValueOf('tab_option', 'chkStreetViewControl');
				mapObj.setOptions({ streetViewControl: val });
			}
			if (!id || id == 'chkRotateControl'){
				val = dialog.getValueOf('tab_option', 'chkRotateControl');
				mapObj.setOptions({ rotateControl: val });
			}
			if (!id || id == 'chkOverviewMapControl'){
				val = dialog.getValueOf('tab_option', 'chkOverviewMapControl');
				mapObj.setOptions({ overviewMapControl: val, overviewMapControlOptions: { opened: true } });
			}
		};
		var resizeMap = function()
		{
			if (!mapDiv) return;
			
//			mapDiv.style.width = dialog.getValueOf('tab_map', 'txtWidth') + 'px';
//			mapDiv.style.height = dialog.getValueOf('tab_map', 'txtHeight') + 'px';
			mapDiv.style.width = dialog.getValueOf('tab_map', 'txtWidth') + dialog.getValueOf('tab_map', 'cmbWidthType');
			mapDiv.style.height = dialog.getValueOf('tab_map', 'txtHeight') + dialog.getValueOf('tab_map', 'cmbHeightType');
			
			//ResizeParent();
		};
		var updateMap = function()
		{
			if (!mapObj) return;

			mapObj.setCenter(new google.maps.LatLng(fieldCenterLatitude.value, fieldCenterLongitude.value));
			mapObj.setZoom(parseInt(fieldZoom.value, 10));
		};
		var doSearch = function()
		{
			var address = dialog.getValueOf('tab_search', 'txtAddress');
			var geocoder = new google.maps.Geocoder();
	
			function processPoint(point)
			{
				if (point){
					dialog.setValueOf('tab_map', 'txtCenterLat', point.lat().RoundTo(5));
					dialog.setValueOf('tab_map', 'txtCenterLon', point.lng().RoundTo(5));
					
					// 検索位置にマーカーを設定
					addMarkerAtPoint(point, address);

					updateMap();
				} else {
					alert(editor.lang.googlemaps.msgNotFound.replace("%s", address));
				}
			}

			geocoder.geocode({ 'address':address }, function(results, status){
				if (status == google.maps.GeocoderStatus.OK){
					processPoint(results[0].geometry.location);
				} else {
					alert(editor.lang.googlemaps.msgNotFound.replace("%s", address));
				}
			});
		};
		var addMarker = function()
		{
			if (mode == 'AddMarker'){
				finishAddMarker();
				return;
			}
			$('#btnAddNewMarker' + editor.id).attr('src', pluginUrl + 'images/AddMarkerDown.png');
			$('#msgMarkerInstruction' + editor.id).text(editor.lang.googlemaps.msgMarkerInstruction2);
			mode = 'AddMarker';
			mapObj.setOptions({ draggableCursor:'crosshair' });
		}
		var addMarkerAtPoint = function(point, text, interactive)
		{
			var marker = createMarker(point, text);
			marker.setMap(mapObj);
			markers.push(marker);
			finishAddMarker();

			if (interactive) editMarker(marker);
		};
		var createMarker = function(point, html)
		{
			var marker = new google.maps.Marker({ position:point, title:html, draggable:true });
			google.maps.event.addListener(marker, "click", function(){
				editMarker(this);
			});
			return marker;
		}
		var finishAddMarker = function()
		{
			mode = '';
			
			$('#btnAddNewMarker' + editor.id).attr('src', pluginUrl + 'images/AddMarker.png');
			$('#msgMarkerInstruction' + editor.id).text(editor.lang.googlemaps.msgMarkerInstruction1);
			mapObj.setOptions({ draggableCursor:'default' });
		};
		var editMarker = function(marker)
		{
			var selectedTab = CKEDITOR.dialog.getCurrent().definition.dialog._.currentTabId;		// 選択中のタブIDを取得
			if (selectedTab == 'tab_marker'){		// マーカーコンテンツを編集
				activeMarker = marker;
				mode = 'EditMarker';

				if (infoWindow) infoWindow.close();
				infoWindow = new google.maps.InfoWindow({ content:generateEditPopupString(marker.getTitle()) });
				google.maps.event.addListener(infoWindow, 'domready', function(){			// 吹き出しの入力画面のイベントを設定
					$("#btnOk").click(function (){
						updateCurrentMarker();
					});
					$("#btnCancel").click(function (){
						closeInfoWindow();
					});
					$("#btnDeleteMarker").click(function (){
						deleteCurrentMarker();
					});
				});
				infoWindow.open(mapObj, marker);
			} else {
				if (infoWindow) infoWindow.close();
				infoWindow = new google.maps.InfoWindow({ content:marker.getTitle() });
				infoWindow.open(mapObj, marker);
			}
		};
		var closeInfoWindow = function()
		{
			mode = '';

			if (infoWindow) infoWindow.close();
			infoWindow = null;
			activeMarker = null;
		};
		var updateCurrentMarker = function ()
		{
			if (activeMarker) activeMarker.setTitle($('#txtMarkerText' + editor.id).val().replace(/\n/g, '<br>'));
			closeInfoWindow();
		};
		var deleteCurrentMarker = function()
		{
			for (var j = 0; j < markers.length; j++){
				if (markers[j] == activeMarker){
					markers.splice(j, 1);
					break;
				}
			}
			var tmp = activeMarker;
			closeInfoWindow();

			tmp.setMap(null);
		};
		var generateEditPopupString = function(text)
		{
			return '<div><label for="txtMarkerText' + editor.id + '">' + editor.lang.googlemaps.markerText + '</label></div>' +
				'<div><textarea id="txtMarkerText' + editor.id + '" class="cke_dialog_ui_input_textarea" style="width:300px; height:120px;">' + text.replace(/<br>/g, '\n') + '</textarea></div>' +
				'<div class="cke_dialog_footer_buttons"><div style="width:50%;display:inline-block;float:left;"><div class="cke_dialog_ui_hbox_child" style="display:inline-block;"><a id="btnDeleteMarker" class="cke_dialog_ui_button"><span class="cke_dialog_ui_button">' + editor.lang.googlemaps.deleteMarker + '</span></a></div></div>' +
				'<div style="width:40%;display:inline-block;float:right;"><div class="cke_dialog_ui_hbox_first" style="display:inline-block;"><a id="btnOk" class="cke_dialog_ui_button cke_dialog_ui_button_ok"><span class="cke_dialog_ui_button">' + editor.lang.common.ok + '</span></a></div><div class="cke_dialog_ui_hbox_last" style="display:inline-block;">' +
				'<a id="btnCancel" class="cke_dialog_ui_button cke_dialog_ui_button_cancel"><span class="cke_dialog_ui_button">' + editor.lang.common.cancel + '</span></a></div></div></div>';
		};
		var commitValue = function(data){
			var id = this.id;
			if ( !data.info ) data.info = {};
			data.info[id] = this.getValue();
		};
		var isJson = function(str){
			try {
				JSON.parse(str);
			} catch (e) {
				return false;
			}
			return true;
		};
		return {
			title: editor.lang.googlemaps.title,
			minWidth: 420,
			minHeight: 310,
			onLoad: function(){		// 初期処理
				dialog = this;		// 参照取得
				
				// ダイアログ画面作成
				// ダイアログサイズの設定
//				var width = dialog.getSize().width;
//				var height = dialog.getSize().height;
//				dialog.resize(width, height);
				
				// タブが変更された場合はマップを移動
				dialog.on('selectPage', function(e){
					switch (e.data.page){
					case 'tab_map':
						$('#gmapPreview' + editor.id).appendTo('#placeholder_map' + editor.id);
						break;
					case 'tab_option':
						$('#gmapPreview' + editor.id).appendTo('#placeholder_option' + editor.id);
						break;
					case 'tab_search':
						$('#gmapPreview' + editor.id).appendTo('#placeholder_search' + editor.id);
						break;
					case 'tab_marker':
						$('#gmapPreview' + editor.id).appendTo('#placeholder_marker' + editor.id);
						break;
					case 'tab_line':
						$('#gmapPreview' + editor.id).appendTo('#placeholder_line' + editor.id);
						break;
					}

					if (e.data.page == 'tab_line'){
						polyline.showLinePoints();
					} else {
						polyline.hideLinePoints();
					}

					if (e.data.page != 'tab_marker') finishAddMarker();
				});
				// イベント登録
				$('#btnAddNewMarker' + editor.id).click(function(){
					addMarker();
				}).attr({ title:editor.lang.googlemaps.addMarker, alt:editor.lang.googlemaps.addMarker });
			},
			onFocus: function() {		// 特定フィールドからフォーカスをはずす
				mapDiv.focus();
			},
			onShow: function(){
				// マップ初期化
				markers = [];
				polyline = new Polyline();

				// 選択されているマップの情報を取り込む
				var selectedElement = this.getSelectedElement();
				if (selectedElement){
					fakeImage = selectedElement;
					var realElement = editor.restoreRealElement(selectedElement);
				} else {
					fakeImage = null;
				}
				
				// データロード
				loadSelectionData();
			},
			onOk: function(){
				// A container for our field data
				var data = {};

				// 入力データの確定
				this.commitContent(data);

				// マップ情報更新
				mapInfo.width = data.info['txtWidth'];
				mapInfo.height = data.info['txtHeight'];
				mapInfo.widthType = data.info['cmbWidthType'];
				mapInfo.heightType = data.info['cmbHeightType'];
				mapInfo.alignCenter = data.info['chkAlignCenter'];
				mapInfo.zoom = data.info['cmbZoom'];
				mapInfo.centerLat = data.info['txtCenterLat'];
				mapInfo.centerLon = data.info['txtCenterLon'];
				mapInfo.zoomControl = data.info['chkZoomControl'];
				mapInfo.panControl = data.info['chkPanControl'];
				mapInfo.scaleControl = data.info['chkScaleControl'];
				mapInfo.mapTypeControl = data.info['chkMapTypeControl'];
				mapInfo.streetViewControl = data.info['chkStreetViewControl'];
				mapInfo.rotateControl = data.info['chkRotateControl'];
				mapInfo.overviewMapControl = data.info['chkOverviewMapControl'];
				mapInfo.roadmapMapType = data.info['chkRoadmapMapType'];
				mapInfo.satelliteMapType = data.info['chkSatelliteMapType'];
				mapInfo.hybridMapType = data.info['chkHybridMapType'];
				mapInfo.terrainMapType = data.info['chkTerrainMapType'];
				mapInfo.style = data.info['txtStyle'];
				var markerPoints = [];
				for (var i=0; i < markers.length; i++){
					var point = markers[i].getPosition();
					markerPoints.push({ lat:point.lat().RoundTo(5), lon:point.lng().RoundTo(5), text:markers[i].getTitle() });
				}
				mapInfo.markerPoints = markerPoints;
				
				// マップ埋め込みタグの作成
				mapInfo.linePoints = polyline.encodedPolyline;
				mapInfo.lineLevels = polyline.encodedLevels;
				var script = mapInfo.buildScript();
				var newMapElement = CKEDITOR.dom.element.createFromHtml('<div>' + script + '</div>', editor.document);		// IE8 not work.
				var style = 'width:' + mapInfo.width + mapInfo.widthType + ';height:' + mapInfo.height + mapInfo.heightType + ';display:none;';
	//			var style = 'width:' + mapInfo.width + mapInfo.widthType + ';height:' + mapInfo.height + mapInfo.heightType + ';';
				if (mapInfo.alignCenter) style += 'margin:0 auto;';
					
				newMapElement.setAttributes({
					'id': 'gmap' + mapInfo.number,
					'style': style,
					'class': 'googlemaps'
				});

				// ビュー更新前、画像のみ変更
				var extraStyles = {	'background-image': 'url(' + mapInfo.generateStaticMap() + ')',
							'background-position': 'center center',
							'background-repeat': 'no-repeat',
							'border': '0px',
							'width': mapInfo.width + mapInfo.widthType,
							'height': mapInfo.height + mapInfo.heightType
							};
				var newFakeImage = editor.createFakeElement(newMapElement, 'cke_googlemaps' + mapInfo.number, 'div', false);
				newFakeImage.setStyles( extraStyles );

				if (fakeImage){		// マップ更新の場合
					newFakeImage.replace( fakeImage );
					editor.getSelection().selectElement( newFakeImage );
				} else {
					editor.insertElement( newFakeImage );
				}
			},
			onCancel: function(){
				fakeImage = null;
			},
			contents: [{
				id: 'tab_map',
				label: editor.lang.googlemaps.mapTitle,
				elements :[
				{
					// 項目を横に配置
					type: 'hbox',
					widths: [ '10%', '10%', '10%', '20%' ],		// 項目間幅を調整
	/*				padding: '5px',*/
					children: [
					{
						type : 'text',
						id : 'txtWidth',
						label: editor.lang.googlemaps.width,
						width: '40px',
						'default': 400,
						validate : function() {
							var pass = true,
							value = this.getValue();
							pass = pass && CKEDITOR.dialog.validate.integer()( value ) && value > 0;
							if ( !pass ){
								alert( "Invalid Width" );
								this.select();
							}
							return pass;
						},
						onChange: function(){
							resizeMap();
						},
						commit: commitValue
					}, {
						type: 'select',
						id: 'cmbWidthType',
						label: editor.lang.googlemaps.widthType,
						style: 'width:50px',
						'default': 'px',
						items: [
							[ 'px', 'px' ],
							[ '%', '%' ]
						],
						commit: commitValue
					}, {
						type : 'text',
						id : 'txtHeight',
						label: editor.lang.googlemaps.height,
						width: '40px',
						'default': 240,
						validate : function() {
							var pass = true,
							value = this.getValue();
							pass = pass && CKEDITOR.dialog.validate.integer()( value ) && value > 0;
							if ( !pass ){
								alert( "Invalid Height" );
								this.select();
							}
							return pass;
						},
						onChange: function(){
							resizeMap();
						},
						commit: commitValue
					}, {
						type: 'select',
						id: 'cmbHeightType',
						label: editor.lang.googlemaps.heightType,
						style: 'width:50px',
						'default': 'px',
						items: [
							[ 'px', 'px' ],
							[ '%', '%' ]
						],
						commit: commitValue
					}, {
						type: 'checkbox',
						id: 'chkAlignCenter',
						label: editor.lang.googlemaps.alignCenter,
						style: 'margin-top:20px;',
						'default': false,
						commit: commitValue
					} ]
				}, {
					type: 'hbox',
					widths: [ '10%', '10%', '10%' ],
					children: [
					{
						type: 'text',
						id: 'txtCenterLat',
					//	requiredContent: 'img(cke-xyz)', // Random text like 'xyz' will check if all are allowed.
						label: editor.lang.googlemaps.latitude,
						'default': '',
						onLoad: function(){
							fieldCenterLatitude = document.getElementById(this.getInputElement().$.id);			// 参照を取得
						},
						onChange: function() {
							if (mapObj) mapObj.setCenter(new google.maps.LatLng(this.getValue(), fieldCenterLongitude.value));
						},
						commit: commitValue
					}, {
						type: 'text',
						id: 'txtCenterLon',
						//requiredContent: 'img[title]',
						label: editor.lang.googlemaps.longitude,
						'default': '',
						onLoad: function(){
							fieldCenterLongitude = document.getElementById(this.getInputElement().$.id);		// 参照を取得
						},
						onChange: function() {
							if (mapObj) mapObj.setCenter(new google.maps.LatLng(fieldCenterLatitude.value, this.getValue()));
						},
						commit: commitValue
					}, {
						type: 'select',
						id: 'cmbZoom',
						label: editor.lang.googlemaps.zoomLevel,
						style: 'width:50px',
						'default': '',
						items: [
							[ '0', '0' ],
							[ '1', '1' ],
							[ '2', '2' ],
							[ '3', '3' ],
							[ '4', '4' ],
							[ '5', '5' ],
							[ '6', '6' ],
							[ '7', '7' ],
							[ '8', '8' ],
							[ '9', '9' ],
							[ '10', '10' ],
							[ '11', '11' ],
							[ '12', '12' ],
							[ '13', '13' ],
							[ '14', '14' ],
							[ '15', '15' ],
							[ '16', '16' ],
							[ '17', '17' ]
						],
						onLoad: function(){
							fieldZoom = document.getElementById(this.getInputElement().$.id);			// 参照を取得
						},
						onChange: function() {
							if (mapObj) mapObj.setZoom(parseInt(this.getValue()), 10);
						},
						commit: commitValue
					} ]
				}, {
					type: 'html',
					html: '<div id="placeholder_map' + editor.id + '"><div id="gmapPreview' + editor.id + '" style="outline:0;" tabIndex="-1"></div></div>'
				} ]		// elements
			}, {
				id: 'tab_option',
				label: editor.lang.googlemaps.optionTitle,
				elements: [
				{
					type: 'html',
					html: editor.lang.googlemaps.control
				}, {
					type: 'hbox',
					widths: [ '25%', '25%', '25%', '25%' ],
					children: [
					{
						type: 'checkbox',
						id: 'chkZoomControl',
						label: editor.lang.googlemaps.zoomControl,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					}, {
						type: 'checkbox',
						id: 'chkPanControl',
						label: editor.lang.googlemaps.panControl,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					}, {
						type: 'checkbox',
						id: 'chkMapTypeControl',
						label: editor.lang.googlemaps.mapTypeControl,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					}, {
						type: 'checkbox',
						id: 'chkScaleControl',
						label: editor.lang.googlemaps.scaleControl,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					} ]
				}, {
					type: 'hbox',
					widths: [ '25%', '25%', '50%' ],
					children: [
					{
						type: 'checkbox',
						id: 'chkStreetViewControl',
						label: editor.lang.googlemaps.streetViewControl,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					}, {
						type: 'checkbox',
						id: 'chkRotateControl',
						label: editor.lang.googlemaps.rotateControl,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					}, {
						type: 'checkbox',
						id: 'chkOverviewMapControl',
						label: editor.lang.googlemaps.overviewMapControl,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					} ]
				}, {
					type: 'html',
					html: editor.lang.googlemaps.mapType
				}, {
					type: 'hbox',
					widths: [ '25%', '25%', '25%', '25%' ],
					children: [
					{
						type: 'checkbox',
						id: 'chkRoadmapMapType',
						label: editor.lang.googlemaps.roadmapMapType,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					}, {
						type: 'checkbox',
						id: 'chkSatelliteMapType',
						label: editor.lang.googlemaps.satelliteMapType,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					}, {
						type: 'checkbox',
						id: 'chkHybridMapType',
						label: editor.lang.googlemaps.hybridMapType,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					}, {
						type: 'checkbox',
						id: 'chkTerrainMapType',
						label: editor.lang.googlemaps.terrainMapType,
						'default': false,
						onChange: function(){
							changeMap(this.id);
						},
						commit: commitValue
					} ]
				}, {
					type: 'html',
					html: '<div id="placeholder_option' + editor.id + '"></div>'
				} ]
			}, {
				id: 'tab_search',
				label: editor.lang.googlemaps.searchTitle,
				elements: [
				{
					type: 'hbox',
					widths: [ '5%', '5%' ],
					children: [
					{
						type: 'text',
						id: 'txtAddress',
						label: editor.lang.googlemaps.searchLabel,
						width: '300px',
						labelLayout: 'horizontal'
						/*onLoad: function(){
							fieldAddress = $('#' + this.getInputElement().$.id);			// 参照を取得
						}*/
					}, {
						type: 'button',
						id: 'btnSearch',
						align: 'left',
						//style: 'width:50px',
						label: editor.lang.googlemaps.search,
						onClick: function(){
							doSearch();
						}
					} ]
				}, {
					type: 'html',
					html: '<div id="placeholder_search' + editor.id + '"></div>'
				} ]
			}, {
				id: 'tab_marker',
				label: editor.lang.googlemaps.markerTitle,
				elements: [
				{
					type: 'html',
html: '<img id="btnAddNewMarker' + editor.id + '" src="' + pluginUrl + 'images/AddMarker.png" style="cursor:pointer;" /><div id="msgMarkerInstruction' + editor.id + '" style="display:inline-block;">' + editor.lang.googlemaps.msgMarkerInstruction1 + '</div>'
				}, {
					type: 'html',
					html: '<div id="placeholder_marker' + editor.id + '"></div>'
				}, {		
					type: 'text',
					hidden : true,
					label: 'dummy item for the bug that the tab is auto disabled when all items are html type.'
				} ]
			}, {
				id: 'tab_line',
				label: editor.lang.googlemaps.lineTitle,
				elements: [
				{
					type: 'html',
					html: '<p>' + editor.lang.googlemaps.msgLineInstruction + '</p>'
				}, {
					type: 'html',
					html: '<div id="placeholder_line' + editor.id + '"></div>'
				}, {		
					type: 'text',
					hidden : true,
					label: 'dummy item for the bug that the tab is auto disabled when all items are html type.'
				} ]
			}, {
				id: 'tab_style',
				label: editor.lang.googlemaps.styleTitle,
				elements: [
				{
					type: 'html',
					html: '<p>' + editor.lang.googlemaps.msgInputStyleJsonData + '</p>'
				}, {
					type: 'textarea',
					id: 'txtStyle',
					label: '',
					'default': '',
					inputStyle: 'cursor:auto;' +
						'width:' + CKEDITOR.config.googlemaps_width + 'px;' +
						'height:' + CKEDITOR.config.googlemaps_height + 'px;' +
						'tab-size:4;' +
						'text-align:left;',
					validate : function() {
						var pass = true,
						value = this.getValue().trim();
						pass = pass && (value == '' || isJson(value));
						if ( !pass ){
							alert( editor.lang.googlemaps.msgInvalidStyle );
							this.select();
						}
						return pass;
					},
					commit: commitValue
				} ]
			} ]
		};
	});
})();
