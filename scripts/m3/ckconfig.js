/*
 * CKEditor(WYSIWYGエディター)の設定
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    2.0
 * @link       http://www.magic3.org
 */
// スタイルメニューの定義
CKEDITOR.stylesSet.add( 'default', [
	/* Block Styles */
	{ name: 'イタリックタイトル',		element: 'h2', styles: { 'font-style': 'italic' } },
	{ name: 'サブタイトル',			element: 'h3', styles: { 'color': '#aaa', 'font-style': 'italic' } },
	{
		name: 'グレーブロック',
		element: 'div',
		styles: {
			padding: '5px 10px',
			background: '#eee',
			border: '1px solid #ccc'
		}
	},

	/* Inline Styles */
	{ name: '文字大(big)',		element: 'big' },
	{ name: '文字小(small)',	element: 'small' },
	{ name: '削除文(del)',		element: 'del' },
	{ name: '挿入文(ins)',		element: 'ins' },
	{ name: '引用(cite)',		element: 'cite' },
	{ name: '引用文(q)',		element: 'q' },

	/* Object Styles */
	{
		name: '画像左寄せ',
		element: 'img',
		attributes: { 'class': 'left' }
	},
	{
		name: '画像右寄せ',
		element: 'img',
		attributes: { 'class': 'right' }
	},
	{
		name: '簡易テーブル',
		element: 'table',
		attributes: {
			cellpadding: '5',
			cellspacing: '0',
			border: '1',
			bordercolor: '#ccc'
		},
		styles: {
			'border-collapse': 'collapse'
		}
	},
	{ name: '枠なしテーブル',		element: 'table',	styles: { 'border-style': 'hidden', 'background-color': '#E6E6FA' } }
]);
CKEDITOR.stylesSet.add( 'bootstrap', [
	/* Block Styles */
	{ name: 'イタリックタイトル',		element: 'h2', styles: { 'font-style': 'italic' } },
	{ name: 'サブタイトル',			element: 'h3', styles: { 'color': '#aaa', 'font-style': 'italic' } },
	{
		name: 'グレーブロック',
		element: 'div',
		styles: {
			padding: '5px 10px',
			background: '#eee',
			border: '1px solid #ccc'
		}
	},

	/* Inline Styles */
	{ name: '文字大(big)',		element: 'big' },
	{ name: '文字小(small)',	element: 'small' },
	{ name: '削除文(del)',		element: 'del' },
	{ name: '挿入文(ins)',		element: 'ins' },
	{ name: '引用(cite)',		element: 'cite' },
	{ name: '引用文(q)',		element: 'q' },

	/* Object Styles */
	{
		name: 'ラベル(default)',
		element: 'span',
		attributes: { 'class': 'label label-default' }
	},
	{
		name: 'ラベル(primary)',
		element: 'span',
		attributes: { 'class': 'label label-primary' }
	},
	{
		name: 'ラベル(success)',
		element: 'span',
		attributes: { 'class': 'label label-success' }
	},
	{
		name: 'ラベル(info)',
		element: 'span',
		attributes: { 'class': 'label label-info' }
	},
	{
		name: 'ラベル(warning)',
		element: 'span',
		attributes: { 'class': 'label label-warning' }
	},
	{
		name: 'ラベル(danger)',
		element: 'span',
		attributes: { 'class': 'label label-danger' }
	},
	{
		name: '画像(角丸)',
		element: 'img',
		attributes: { 'class': 'img-rounded' }
	},
	{
		name: '画像(丸)',
		element: 'img',
		attributes: { 'class': 'img-circle' }
	},
	{
		name: '画像(サムネール)',
		element: 'img',
		attributes: { 'class': 'img-thumbnail' }
	},
	{
		name: '画像左寄せ',
		element: 'img',
		attributes: { 'class': 'pull-left' }
	},
	{
		name: '画像右寄せ',
		element: 'img',
		attributes: { 'class': 'pull-right' }
	},
	{
		name: '簡易テーブル',
		element: 'table',
		attributes: {
			cellpadding: '5',
			cellspacing: '0',
			border: '1',
			bordercolor: '#ccc'
		},
		styles: {
			'border-collapse': 'collapse'
		}
	},
	{ name: '枠なしテーブル',		element: 'table',	styles: { 'border-style': 'hidden', 'background-color': '#E6E6FA' } }
]);

CKEDITOR.editorConfig = function(config){
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config
	config.language = 'ja';
	config.enterMode = CKEDITOR.ENTER_BR; // 改行をbrに変更
	config.shiftEnterMode = CKEDITOR.ENTER_DIV;
	config.autoParagraph = false;
	config.fillEmptyBlocks = false;		// 「&nbsp;」が自動的に入るのを防ぐ
	config.toolbarCanCollapse = true;		// ツールバー表示制御
//	config.width = 800;
	config.width = 796;		// IE Edgeのカラム幅に合わない問題を調整(2017/1/22)
	
	// ツールバーの設定
	if (M3_USE_GOOGLEMAPS){			// GoogleMapsを使用する場合
		config.toolbar_Full = [
			{ name: 'tools', items: [ 'Maximize', 'ShowBlocks', 'Source' ] },
			{ name: 'clipboard', items: [ 'M3Templates', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
			{ name: 'editing', items : [ 'Find', 'Replace', 'SelectAll' ] },
			{ name: 'insert', items: [ 'Image', 'YouTube', 'Googlemaps', 'Table', 'HorizontalRule' ] },
			{ name: 'colors', items : [ 'TextColor', 'BGColor', '-', 'FontSize' ] },
			'/',
			{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
			{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', /*'-', 'Blockquote', 'CreateDiv',*/ '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
			{ name: 'links', items: [ 'M3Link', 'M3Unlink', 'M3Anchor' ] },
			{ name: 'styles', items: [ 'Styles', 'Format' ] }
		];
		
		// APIキーを設定
		if (typeof(M3_GOOGLEMAPS_KEY) !== "undefined"){
			config.googlemapsPlugin = {
				apiKey: M3_GOOGLEMAPS_KEY
			};
		}
	} else {
		config.toolbar_Full = [
			{ name: 'tools', items: [ 'Maximize', 'ShowBlocks', 'Source' ] },
			{ name: 'clipboard', items: [ 'M3Templates', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
			{ name: 'editing', items : [ 'Find', 'Replace', 'SelectAll' ] },
			{ name: 'insert', items: [ 'Image', /*'YouTube', 'Googlemaps',*/ 'Table', 'HorizontalRule' ] },
			{ name: 'colors', items : [ 'TextColor', 'BGColor', '-', 'FontSize' ] },
			'/',
			{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
			{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', /*'-', 'Blockquote', 'CreateDiv',*/ '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
			{ name: 'links', items: [ 'M3Link', 'M3Unlink', 'M3Anchor' ] },
			{ name: 'styles', items: [ 'Styles', 'Format' ] }
		];
	}
	config.toolbar_Layout = [
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks', 'Source' ] },
		{ name: 'clipboard', items: [ 'Undo', 'Redo' ] },
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
		{ name: 'paragraph', items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'styles', items: [ 'Styles', 'Format', 'FontSize' ] }
	];
	config.toolbar_Small = [
		{ name: 'tools', items: [ 'Maximize', 'Source', '-', 'Undo', 'Redo', '-', 'NumberedList', 'BulletedList', 'JustifyLeft', 'JustifyCenter', 'JustifyRight' ] },
		'/',
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'M3Link', 'M3Unlink',  '-', 'Format', 'Image' ] }
	];
	if (typeof(M3_CONFIG_WIDGET_CKEDITOR_TEMPLATE_TYPE) != "undefined" && M3_CONFIG_WIDGET_CKEDITOR_TEMPLATE_TYPE == 10){
		config.stylesCombo_stylesSet = 'bootstrap';
	} else {
		config.stylesCombo_stylesSet = 'default';
	}
	
	// メッセージの変更
	config.image_previewText = '<strong>「サーバブラウザ」ボタン</strong>をクリックすると画像ブラウザが立ち上がります。サムネール表示の画像をダブルクリックすると画像が取得できます。画像をアップロードするには、画像ブラウザ上部の「アップロード」ボタンをクリックするか、画像ブラウザ上へ画像ファイルをドロップします。';
	
	// 追加プラグインの設定
//	config.extraPlugins = 'youtube,googlemaps';
//	config.removePlugins = 'iframe';
	config.extraPlugins = 'youtube,googlemaps,m3link,m3templates';
	config.removePlugins = 'iframe,link,templates';
	config.allowedContent = true;		// ACF(Advanced Content Filter)を使用しない。SCRIPT,IFRAMEタグ等許可。
//	config.extraAllowedContent = 'iframe';
//	config.autoGrow_maxHeight = 800;		// 指定サイズまで入力に合わせて拡大
	config.protectedSource.push(/<i[^>]*><\/i>/g);	// iタグ許可
	config.dialog_noConfirmCancel = true;		// ダイアログキャンセル時のダイアログを非表示にする
};
// HTML5対応
CKEDITOR.dtd.a.div = 1;
CKEDITOR.dtd.a.p = 1;

CKEDITOR.on('dialogDefinition', function(ev){
	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;
	var dialog = dialogDefinition.dialog;
	
	if (dialogName == 'image'){
		dialogDefinition.removeContents('Upload');	// 「アップロード」タブ削除
	}
	
	// ツールバー項目
	var widthRatio, height, options;
	if (M3_SMALL_DEVICE_OPTIMIZE){		// 小画面デバイス最適化の場合
		widthRatio = 1.0;
		height = '500px';
		options = { toolbar: [['view', 'sort'], ['fullscreen']] };
	} else {
		widthRatio = M3_FILEBROWSER_WIDTH_RATIO;
		height = '600px';
		options = {	toolbar:[
						['back', 'forward'],
						// ['netmount'],
						// ['reload'],
						// ['home', 'up'],
						['mkdir', 'mkfile', 'upload'],
						['open', 'download', 'getfile'],
						['info', 'chmod'],
						['quicklook'],
						['copy', 'cut', 'paste'],
						['rm'],
						['duplicate', 'rename', 'edit', 'resize'],
						['extract', 'archive'],
						['search'],
						['view', 'sort'],
						['help'],
						['fullscreen']
					] };
	}
	// ファイルブラウザ
	if (jQuery().elfinder){			// elFinderが使用できる場合
		var editor = ev.editor;
		var tabCount = dialogDefinition.contents.length;
		for (var i = 0; i < tabCount; i++){
			var browseButton = dialogDefinition.contents[i].get('browse');

			if (browseButton !== null){
				browseButton.hidden = false;
				browseButton.onClick = function(dialog, i){
					var title;
					
					editor._.filebrowserSe = this;
					
					switch (dialogName){
					case 'image':
						title = '画像を選択';
						break;
					default:
						title = 'ファイルを選択';
						break;
					}
					
					$('<div />').dialog({
						title: title,
						modal: true,
						width: $(window).width() * widthRatio,
						open: function(){
							$(this).parent().css("padding", "0px");
							$(this).css("padding", "0px");
						},
						create: function(event, ui){
							var option = '';
							if (dialogName == 'image'){
								option = '?dirtype=' + dialogName;
							} else if (dialogName == 'm3link'){
								option = '?dirtype=file';
							}
							$(this).elfinder({
								url: M3_ROOT_URL + '/scripts/elfinder-' + M3_FILEBROWSER_VER + '/php/connector.php' + option,
								height: height,
								lang: 'ja',
								resizable: false,
								ui: ['toolbar', 'places', 'tree', 'path', 'stat'],
								uiOptions: options,
								getFileCallback: function(file){
									var url = file.url;
									CKEDITOR.tools.callFunction(editor._.filebrowserFn, url);
									//$('a.ui-dialog-titlebar-close[role="button"]').click();
									$('.ui-dialog-titlebar-close[role="button"]').click();
								}
							}).elfinder('instance');
						}
					});
				}
			}
		}
	}
});
CKEDITOR.on('instanceReady',function(){
	if (typeof(m3AdjustParentWindow) == "function") m3AdjustParentWindow();		// フレームサイズ調整
});