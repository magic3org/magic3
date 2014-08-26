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
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
(function() {
	// デフォルト値
	CKEDITOR.config.googlemaps_width = 500;		// マップデ幅
	CKEDITOR.config.googlemaps_height = 300;	// マップ高さ
//	CKEDITOR.config.googlemaps_centerLat = 35.594757;		// マップ緯度
//	CKEDITOR.config.googlemaps_centerLon = 139.620739;		// マップ経度
//	CKEDITOR.config.googlemaps_zoom = 11;					// ズームレベル

	var path = CKEDITOR.plugins.getPath('googlemaps');
	//CKEDITOR.scriptLoader.load(CKEDITOR.getUrl(CKEDITOR.plugins.getPath('googlemaps')) + 'dialogs/googlemaps.js');
	CKEDITOR.scriptLoader.load(CKEDITOR.getUrl(path) + 'dialogs/googlemaps.js');
	CKEDITOR.scriptLoader.load(CKEDITOR.getUrl(path) + 'dialogs/polyline.js');

	CKEDITOR.plugins.add( 'googlemaps', {
		lang: 'ja,en',
		icons: 'googlemaps',

		init: function(editor){
			// ダイアログ登録
			editor.addCommand('googlemaps', new CKEDITOR.dialogCommand('googlemaps'));
			CKEDITOR.dialog.add('googlemaps', this.path + 'dialogs/main.js');

			// ツールバーボタン登録
			if (editor.ui.addButton){
				editor.ui.addButton('Googlemaps', {
					label: editor.lang.googlemaps.toolbar,
					command: 'googlemaps',
					toolbar: 'others'
				});
			}
			
			// オブジェクトダブルクリック時にダイアログを開く
			editor.on( 'doubleclick', function( evt ) {
				var element = evt.data.element;
				var className = element.$.className;
				if (element.is('img') && element.data('cke-real-element-type') == 'div' && className.lastIndexOf('cke_googlemaps', 0) == 0) evt.data.dialog = 'googlemaps';
			});
		},
		// 初期起動時、ソースモード切替時に呼び出し
		afterInit: function(editor){
			// SCRIPTタグのGoogleマップ情報読み込み
			var div = document.createElement('div');
  			div.innerHTML = editor.getData();
			var scripts = div.getElementsByTagName('script');
			for (var i = 0; i < scripts.length; i++) {
				var content = scripts[i].outerHTML;
				if (GoogleMapsHandler.detectMapScript(content)){		// マップ情報の場合は保存
					var mapInfo = GoogleMapsHandler.createNew();
					mapInfo.parse(content);
				}
			}
/*			var jScripts = $(editor.getData()).filter("script");
			jScripts.each(function(index){
				var content = $(this).get(0).outerHTML;
				if (GoogleMapsHandler.detectMapScript(content)){		// マップ情報の場合は保存
					var mapInfo = GoogleMapsHandler.createNew();
					mapInfo.parse(content);
				}
			});*/
		
			var dataProcessor = editor.dataProcessor;
			var dataFilter = dataProcessor && dataProcessor.dataFilter;
			if (dataFilter) {
				dataFilter.addRules({
					elements: {
						div: function(element){
							// Googleマップの埋め込みタグの場合は固定マップ画像を設定
							var objectId = element.attributes.id;
							var className = element.attributes['class'];
							if (className == 'googlemaps'){
								// マップ情報取得
								var mapNumber;
								var regExp = /gmap(\d+)/;
								if (regExp.test(objectId)) mapNumber = RegExp.$1;
								var mapInfo = GoogleMapsHandler.getMap(mapNumber);
								if (mapInfo){
									// 幅、高さを設定
									var width, height;
									var style = element.attributes.style;
									if ((/width:\s*(\d+)px/i).test(style)) width = RegExp.$1;
									if ((/height:\s*(\d+)px/i).test(style)) height = RegExp.$1;
									if (!width || !height){
										width = CKEDITOR.config.googlemaps_width;
										height = CKEDITOR.config.googlemaps_height;
									}
									mapInfo.setDimensions(width, height);
									
									// 画像を背景に配置しリサイズ不可にする
									CKEDITOR.addCss(
										'img.cke_googlemaps' + mapNumber +
										'{' +
											'background-image: url(' + mapInfo.generateStaticMap() + ');' +
											'background-position: center center;' +
											'background-repeat: no-repeat;' +
											'border: 0px;' +
											'width: ' + width + 'px;' +
											'height: ' + height + 'px;' +
										'}'
									);
									var fakeImage = editor.createFakeParserElement(element, 'cke_googlemaps' + mapNumber, 'div', false/*リサイズ不可*/);
									return fakeImage;
								} else {		// マップ情報が見つからない場合はダミーの画像を表示
									// 画像を背景に配置しリサイズ不可にする
									CKEDITOR.addCss(
										'img.cke_googlemaps' + mapNumber +
										'{' +
											'background-image: url(' + CKEDITOR.getUrl(CKEDITOR.plugins.getPath('googlemaps')) + 'images/maps_res_logo.png' + ');' +
											'background-position: center center;' +
											'background-repeat: no-repeat;' +
											'border: 1px solid #a9a9a9;' +
											'width: ' + CKEDITOR.config.googlemaps_width + 'px;' +
											'height: ' + CKEDITOR.config.googlemaps_height + 'px;' +
										'}'
									);
									var fakeImage = editor.createFakeParserElement(element, 'cke_googlemaps' + mapNumber, 'div', false/*リサイズ不可*/);
									return fakeImage;
								}
							}
							return null;
						}
					}
				}, 5);
			}
		}
	});
})();
