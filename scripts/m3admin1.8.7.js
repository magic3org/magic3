/**
 * Magic3管理機能用JavaScriptライブラリ
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// ライブラリ用変数
var _m3Url;
var _m3AccessPoint;		// アクセスポイント(空=PC,m=携帯,s=スマートフォン)
var _m3SetUrlCallback;	// リンク作成用コールバック
var _m3ContentEdited;		// 入力コンテンツが変更されたかどうか
var _m3CheckContentEdit;	// 入力コンテンツの変更をチェックするかどうか
var _m3ShowWidgetTool;		// ウィジェットツールを表示するかどうか
var _m3ConfigWindowMinHeight = 600;			// 設定画面の高さ最小値

// 親ウィンドウを更新
function m3UpdateParentWindow()
{
	var href = 	window.opener.location.href.split('#');
	window.opener.location.href = href[0];
	//window.opener.location.href = window.opener.location.href;
}
// 設定ウィンドウから親ウィンドウを更新
function m3UpdateParentWindowByConfig(serial)
{
	if (window.opener){
		if (window.opener.m3UpdateByConfig) window.opener.m3UpdateByConfig(serial);
		if (window.opener.m3UpdateByChildWindow) window.opener.m3UpdateByChildWindow(serial);
	}
	if (window.parent != window.self){	// 呼び出し元がiframeの場合のみ実行
		// iframeから親フレームを更新
		if (window.parent.m3UpdateByChildWindow) window.parent.m3UpdateByChildWindow(serial);
		
		// iframeから起動元ウィンドウを更新
		if (window.parent.opener && window.parent.opener.m3UpdateByConfig) window.parent.opener.m3UpdateByConfig(serial);
	}
}
// 親ウィンドウサイズを調整
function m3AdjustParentWindow()
{
	if (window.parent){
		if (window.parent.m3AdjustWindow) window.parent.m3AdjustWindow();
	}
}
// ウィンドウサイズ調整
function m3AdjustHeight(obj, min)
{
	var name = obj.name;
	
	if (!name) name = 0;

	var app = navigator.appName.charAt(0);
	var height = min;
	if(navigator.userAgent.indexOf('Safari') != -1){
		height = parent.frames[name].document.body.scrollHeight + 80;
	}else if (app == "N"){
		height = parent.frames[name].document.height +80;
	} else {
		try {
			height = parent.frames[name].document.body.scrollHeight + 80;
		} catch (e){}
	}
	if (height > min){
		obj.height = height;
	} else {
		obj.height = min;
	}
}
// 一般ウィンドウ表示
function m3ShowStandardWindow(url)
{
	if (M3_CONFIG_WINDOW_OPEN_TYPE == 0){
		window.open(url, "", "toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900");
	} else {
		window.open(url, "");
	}
}
// 設定用ウィンドウ表示
function m3ShowConfigWindow(widgetId, configId, serial)
{
	if (M3_CONFIG_WINDOW_OPEN_TYPE == 0){
		window.open(M3_DEFAULT_ADMIN_URL + "?cmd=configwidget&openby=other&widget=" + widgetId + 
					"&_defconfig=" + configId + "&_defserial=" + serial, "config_" + widgetId,
					"toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900");
	} else {
		window.open(M3_DEFAULT_ADMIN_URL + "?cmd=configwidget&openby=other&widget=" + widgetId + 
					"&_defconfig=" + configId + "&_defserial=" + serial, "config_" + widgetId);
	}
}
// ウィジェット表示微調整用ウィンドウ表示
function m3ShowAdjustWindow(configId, serial, pageId, pageSubId)
{
	if (M3_CONFIG_WINDOW_OPEN_TYPE == 0){
		window.open(M3_DEFAULT_ADMIN_URL + "?task=adjustwidget&openby=simple" + 
					"&_defconfig=" + configId + "&_defserial=" + serial + "&_page=" + pageId + "&_sub=" + pageSubId, "adjust_" + serial,
					"toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900");
	} else {
		window.open(M3_DEFAULT_ADMIN_URL + "?task=adjustwidget&openby=simple" + 
					"&_defconfig=" + configId + "&_defserial=" + serial + "&_page=" + pageId + "&_sub=" + pageSubId, "adjust_" + serial);
	}
}
// 各種端末用プレビューウィンドウ表示
function m3ShowPreviewWindow(type, url)
{
	var width, height;
	
	switch (type){
		case 0:		// PC用
		default:
			width = 1000;
			height = 800;
			break;
		case 1:		// 携帯用
			width = 240;
			height = 320;
			break;
		case 2:		// スマートフォン用
			width = 320;
			height = 480;
			break;
	}
	window.open(url, "", "toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=" + width + ",height=" + height);
}
/**
 * 管理画面用部品
 *
 * @return なし
 */
function m3LoadOptionUI()
{
	// ダイアログ用設定
	$('.modal-header').addClass('bg-primary');		// タイトル背景色
	if (typeof(BootstrapDialog) != "undefined"){
		BootstrapDialog.DEFAULT_TEXTS['CANCEL'] = 'キャンセル';
	}

	// 処理中ダイアログ準備
	m3PrepareProcessModal();
}
/**
 * 処理中ダイアログ表示
 *
 * @return なし
 */
function m3ShowProcessModal()
{
	// 画面上のフォーカスを外す
	$('input,textarea,select').blur();
	
	$('#processing-modal').modal('show');
}
/**
 * 処理中ダイアログ非表示
 *
 * @return なし
 */
function m3HideProcessModal()
{
	$('#processing-modal').modal('hide');
}
/**
 * 処理中ダイアログ準備
 *
 * @return なし
 */
function m3PrepareProcessModal()
{
	// ダイアログ作成
	if ($('#processing-modal').size() == 0){		// ダイアログが存在しない場合
		var modal  = '<div class="modal modal-processing fade" id="processing-modal" role="dialog" aria-hidden="true" style="display:none;">';
			modal += '<div class="modal-dialog">';
			modal += '<div class="modal-content">';
			modal += '<div class="modal-body">';
			modal += '<div class="text-center">';
			modal += '<img src="' + M3_ROOT_URL + '/images/system/processing.gif" class="icon" />';
			modal += '<h4>処理中</h4>';
			modal += '</div>';
			modal += '</div>';
			modal += '</div>';
			modal += '</div>';
			modal += '</div>';
		$("body").append(modal);
	}
}
/**
 * アラートを表示
 *
 * @param string type			アラートタイプ(空文字列,info,notice,success,failure,warinig,error)
 * @param string message		メッセージ
 * @param function	after_callback	コールバック関数
 * @param string title			タイトル
 * @return なし
 */
function m3Alert(type, message, after_callback, title)
{
	var dialogType;
	var config = {};
	
	switch (type){
	case '':
		dialogType = BootstrapDialog.TYPE_DEFAULT;
		break;
	case 'info':
		dialogType = BootstrapDialog.TYPE_INFO;
		break;
	case 'notice':
		dialogType = BootstrapDialog.TYPE_PRIMARY;
		break;
	case 'success':
		dialogType = BootstrapDialog.TYPE_SUCCESS;
		break;
	case 'failure':
		dialogType = BootstrapDialog.TYPE_WARNING;
		break;
	case 'waring':
		dialogType = BootstrapDialog.TYPE_WARNING;
		break;
	case 'error':
		dialogType = BootstrapDialog.TYPE_DANGER;
		break;
	}
	config['type'] = dialogType;
	config['message'] = message;
	if (!title){
		switch (type){
		case '':
			break;
		case 'info':
			title = '情報';
			break;
		case 'notice':
			title = '注意';
			break;
		case 'success':
			title = '成功';
			break;
		case 'failure':
			title = '失敗';
			break;
		case 'warning':
			title = "警告";
			break;
		case 'danger':
			title = 'エラー';
			break;
		}
	}
	if (title) config['title'] = title;
	if (after_callback) config['callback'] = function(result){ after_callback(); };
	
	BootstrapDialog.alert(config);
}
/**
 * 確認画面を表示
 *
 * @param string type			アラートタイプ(空文字列,info,notice,success,failure,warinig,error)
 * @param string message		メッセージ
 * @param function	callback	ボタンが押されときのコールバック関数(第1引数1にOK(true),キャンセル(false)が渡る。)
 * @param string title			タイトル
 * @return なし
 */
function m3Confirm(type, message, callback, title)
{
	var dialogType;
	var config = {};
	
	switch (type){
	case '':
	default:
		//dialogType = BootstrapDialog.TYPE_DEFAULT;
		dialogType = BootstrapDialog.TYPE_INFO;
		break;
	case 'info':
		dialogType = BootstrapDialog.TYPE_INFO;
		break;
	case 'notice':
		dialogType = BootstrapDialog.TYPE_PRIMARY;
		break;
	case 'success':
		dialogType = BootstrapDialog.TYPE_SUCCESS;
		break;
	case 'failure':
		dialogType = BootstrapDialog.TYPE_WARNING;
		break;
	case 'waring':
		dialogType = BootstrapDialog.TYPE_WARNING;
		break;
	case 'error':
		dialogType = BootstrapDialog.TYPE_DANGER;
		break;
	}

	if (!title){
		switch (type){
		case '':
		default:
			title = '確認';
			break;
		case 'info':
			title = '情報';
			break;
		case 'notice':
			title = '注意';
			break;
		case 'success':
			title = '成功';
			break;
		case 'failure':
			title = '失敗';
			break;
		case 'warning':
			title="警告";
			break;
		case 'danger':
			title = 'エラー';
			break;
		}
	}
	
	new BootstrapDialog({
		type: dialogType,
		title: title,
		message: message,
		closable: false,
		data: {
			'callback': callback
		},
		buttons: [{
			label: BootstrapDialog.DEFAULT_TEXTS.CANCEL,
			action: function(dialog) {
				typeof dialog.getData('callback') === 'function' && dialog.getData('callback')(false);
				dialog.close();
			}
		}, {
			label: BootstrapDialog.DEFAULT_TEXTS.OK,
			cssClass: 'btn-primary',
			action: function(dialog) {
				typeof dialog.getData('callback') === 'function' && dialog.getData('callback')(true);
				dialog.close();
			}
		}]
	}).open();
}
/**
 * ファイルブラウザを表示
 *
 * @param function	seturl_callback	コールバック関数
 * @return なし
 */
function m3OpenFileBrowser(seturl_callback)
{
	$('<div />').dialog({
		title: "ファイルを選択",
		modal: true,
		width: $(window).width() * M3_FILEBROWSER_WIDTH_RATIO,
		open: function(){
			$(this).parent().css("padding", "0px");
			$(this).css("padding", "0px");
		},
		create: function(event, ui){
			$(this).elfinder({
				url : M3_ROOT_URL + '/scripts/elfinder-' + M3_FILEBROWSER_VER + '/php/connector.php?dirtype=file',
				height: '500px',
				lang: 'ja',
				resizable: false,
				ui: ['toolbar', 'places', 'tree', 'path', 'stat'],
				getFileCallback: function(url){
					seturl_callback(url.url);
					$('.ui-dialog-titlebar-close[role="button"]').click();
				}
			}).elfinder('instance');
		}
	});
}
/**
 * 画像ファイルブラウザを表示
 *
 * @param function	seturl_callback	コールバック関数
 * @return なし
 */
function m3OpenImageFileBrowser(seturl_callback)
{
	$('<div />').dialog({
		title: "画像を選択",
		modal: true,
		width: $(window).width() * M3_FILEBROWSER_WIDTH_RATIO,
		open: function(){
			$(this).parent().css("padding", "0px");
			$(this).css("padding", "0px");
		},
		create: function(event, ui){
			$(this).elfinder({
				url : M3_ROOT_URL + '/scripts/elfinder-' + M3_FILEBROWSER_VER + '/php/connector.php?dirtype=image',
				height: '500px',
				lang: 'ja',
				resizable: false,
				ui: ['toolbar', 'places', 'tree', 'path', 'stat'],
				getFileCallback: function(url){
					seturl_callback(url.url);
					$('.ui-dialog-titlebar-close[role="button"]').click();
				}
			}).elfinder('instance');
		}
	});
}
/**
 * Flashファイルブラウザを表示
 *
 * @param function	seturl_callback	コールバック関数
 * @return なし
 */
function m3OpenFlashFileBrowser(seturl_callback)
{
	$('<div />').dialog({
		title: "Flashを選択",
		modal: true,
		width: $(window).width() * M3_FILEBROWSER_WIDTH_RATIO,
		open: function(){
			$(this).parent().css("padding", "0px");
			$(this).css("padding", "0px");
		},
		create: function(event, ui){
			$(this).elfinder({
				url : M3_ROOT_URL + '/scripts/elfinder-' + M3_FILEBROWSER_VER + '/php/connector.php?dirtype=flash',
				height: '500px',
				lang: 'ja',
				resizable: false,
				ui: ['toolbar', 'places', 'tree', 'path', 'stat'],
				getFileCallback: function(url){
					seturl_callback(url.url);
					$('.ui-dialog-titlebar-close[role="button"]').click();
				}
			}).elfinder('instance');
		}
	});
}
/**
 * TextAreaをHTMLエディターに変更
 *
 * @param string id			TextAreaタグのIDまたはname
 * @param bool	isMobile	携帯用のツールバー表示
 * @return なし
 */
function m3_setHtmlEditor(id, isMobile)
{
	var oFCKeditor		= new FCKeditor(id);
	oFCKeditor.BasePath	= M3_ROOT_URL + '/scripts/fckeditor2.6.6/';
	oFCKeditor.Config['CustomConfigurationsPath'] = M3_ROOT_URL + '/scripts/m3/fckconfig.js';
	if (isMobile == null || isMobile == false){
		oFCKeditor.ToolbarSet	= "M3Default";			// ツールバーリソース名
	} else {
		oFCKeditor.ToolbarSet	= "M3MobileDefault";	// ツールバーリソース名
	}
	oFCKeditor.Width	= "100%";
	oFCKeditor.Height	= "100%";
	oFCKeditor.Value	= 'This is some <strong>sample text<\/strong>. You are using <a href="http://www.fckeditor.net/">FCKeditor<\/a>.';
	oFCKeditor.ReplaceTextarea();
}
/**
 * TextAreaをWYSIWYGエディターに変更
 *
 * @param string id			TextAreaタグのIDまたはname
 * @param int height		エディター領域の高さ
 * @param bool toolbarVisible	ツールバーを表示するかどうか
 * @param string barType		ツールバータイプ(full=全項目,layout=レイアウト用)
 * @return なし
 */
function m3SetWysiwygEditor(id, height, toolbarVisible, barType)
{
	// アクセスポイントの設定
	_m3SetAccessPoint(M3_CONFIG_WIDGET_DEVICE_TYPE);
		
	if (M3_WYSIWYG_EDITOR == 'ckeditor'){
		var config = {};
		config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig.js';
		if (barType == 'layout'){
			if (typeof(M3_CONFIG_WIDGET_CKEDITOR_LAYOUT_CSS_FILES) != "undefined") config['contentsCss'] = M3_CONFIG_WIDGET_CKEDITOR_LAYOUT_CSS_FILES;
		} else {
			if (typeof(M3_CONFIG_WIDGET_CKEDITOR_CSS_FILES) != "undefined") config['contentsCss'] = M3_CONFIG_WIDGET_CKEDITOR_CSS_FILES;
		}
		if (height) config['height'] = height;
		if (toolbarVisible != null && !toolbarVisible) config['toolbarStartupExpanded'] = false;
		if (barType){
			switch (barType){
			case 'full':
			default:
				config['toolbar'] = 'Full';
				break;
			case 'layout':
				config['toolbar'] = 'Layout';
				break;
			}
		} else {
			config['toolbar'] = 'Full';
		}
		CKEDITOR.replace(id, config);
	} else {
		var oFCKeditor		= new FCKeditor(id);
		oFCKeditor.BasePath	= M3_ROOT_URL + '/scripts/fckeditor2.6.6/';
		oFCKeditor.Config['CustomConfigurationsPath'] = M3_ROOT_URL + '/scripts/m3/fckconfig.js';
		oFCKeditor.ToolbarSet	= "M3Default";			// ツールバーリソース名
		if (height) oFCKeditor.Height = String(height) + 'px';
		oFCKeditor.Value	= 'This is some <strong>sample text<\/strong>. You are using <a href="http://www.fckeditor.net/">FCKeditor<\/a>.';
		oFCKeditor.ReplaceTextarea();
	}
}
/**
 * TextAreaをスクリプト編集エディターに変更
 *
 * @param string id			TextAreaタグのIDまたはname
 * @param int height		エディター領域の高さ
 * @return なし
 */
function m3SetScriptEditor(id, height)
{
	if (typeof CodeMirror == 'undefined') return;
	
	var obj;
	obj = document.getElementById(id);
	if (!obj) obj = document.getElementsByName(id);
	if (!obj) alert(id + "not found.");
	var editor = CodeMirror.fromTextArea(obj, {
		mode: "javascript",
		lineNumbers: true,
		matchBrackets: true,
		extraKeys: {"Enter": "newlineAndIndentContinueComment"}
	});
	if (height) editor.setSize('100%', height);
/*	if (M3_WYSIWYG_EDITOR == 'ckeditor'){
		var config = {};
		config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig_script.js';
		if (height) config['height'] = height;
		CKEDITOR.replace(id, config);
	} else {
	}*/
}
/**
 * CKEditorツールバー機能の直接実行準備
 *
 * @return なし
 */
function m3LoadCKTools()
{
	var dummyCKParent = $('#_dummy_ck_parent');
	if (!dummyCKParent[0]){
		var dummyHtml = '<div id="_dummy_ck_parent"><textarea type="text" id="_dummy_ckeditor" ></textarea></div>';
		$("body").append(dummyHtml);
	}
	dummyCKParent = $('#_dummy_ck_parent');

	var config = {};
//	config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig_direct.js';
	config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig.js';
	config['extraPlugins'] = 'linkinfo';
	CKEDITOR.replace('_dummy_ckeditor', config);
	dummyCKParent.hide();
}
/**
 * URL作成用のアクセスポイントを設定
 *
 * @param int deviceType				デバイスタイプ(0=PC,1=携帯,2=スマートフォン)
 * @return なし
 */
function _m3SetAccessPoint(deviceType)
{
	// アクセスポイント(空=PC,m=携帯,s=スマートフォン)取得
	var accessPoint;
	switch (deviceType){
		case 0:		// PC用
		default:
			accessPoint = '';
			break;
		case 1:		// 携帯用
			accessPoint = 'm';
			break;
		case 2:		// スマートフォン用
			accessPoint = 's';
			break;
	}
	_m3AccessPoint = accessPoint;
}
/**
 * リンク用のURLを作成
 *
 * @param int deviceType				デバイスタイプ(0=PC,1=携帯,2=スマートフォン)
 * @param string url					URL初期値
 * @param function	seturl_callback		コールバック関数
 * @return なし
 */
function m3CreateLinkUrl(deviceType, url, seturl_callback)
{
	// アクセスポイント(空=PC,m=携帯,s=スマートフォン)取得
	var accessPoint;
	switch (deviceType){
		case 0:		// PC用
		default:
			accessPoint = '';
			break;
		case 1:		// 携帯用
			accessPoint = 'm';
			break;
		case 2:		// スマートフォン用
			accessPoint = 's';
			break;
	}
	_m3AccessPoint = accessPoint;
	_m3Url = url;
	_m3SetUrlCallback = seturl_callback;
	
	var dummyCKParent = $('#_dummy_ck_parent');
	dummyCKParent.show();			// IE8バグ回避用
	CKEDITOR.instances['_dummy_ckeditor'].execCommand( 'linkinfo' );
	dummyCKParent.hide();			// IE8バグ回避用
}
/**
 * 「前へ」ボタンのクリックイベントにコールバックを設定
 *
 * @param function	callback	コールバック関数
 * @param string    param		コールバック用パラメータ
 * @return なし
 */
function m3SetPrevButtonEvent(callback, param)
{
	if (typeof(callback) == 'function'){
		$('#m3configprev').click(function(){
			callback(param);
		});
		$('.m3configprev').show();
	}
}
/**
 * 「次へ」ボタンのクリックイベントにコールバックを設定
 *
 * @param function	callback	コールバック関数
 * @param string    param		コールバック用パラメータ
 * @return なし
 */
function m3SetNextButtonEvent(callback, param)
{
	if (typeof(callback) == 'function'){
		$('#m3confignext').click(function(){
			callback(param);
		});
		$('.m3confignext').show();
	}
}
/**
 * 入力データ編集中のページ離脱を防止
 *
 * @return なし
 */
function m3SetSafeContentEdit()
{
	_m3ContentEdited = false;		// 入力コンテンツが変更されたかどうか
	_m3CheckContentEdit = true;	// 入力コンテンツの変更をチェックするかどうか
	
	$(window).bind("beforeunload", function(){
		// CKEditorの入力内容の変更を確認
		var ckeChanged = false;
		if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances){
			for (instance in CKEDITOR.instances){
				ckeChanged = CKEDITOR.instances[instance].checkDirty();
				if (ckeChanged) break;
			}
		}

		if (_m3CheckContentEdit && (_m3ContentEdited || ckeChanged)){
			_m3CheckContentEdit = false;
			setTimeout(function(){
				_m3CheckContentEdit = true;
			}, 10);
			return "このページを離れようとしています。";
		}
	});
/*	$("form input, form select, form textarea").change(function(){
		_m3ContentEdited = true;
	});*/
	// Firefox対応(2015/12/1)
	$("form input:not(.noeditcheck), form select, form textarea").change(function(){
		_m3ContentEdited = true;
	});
}
/**
 * 入力データ編集中のページ離脱を許可
 *
 * @return なし
 */
function m3CancelSafeContentEdit()
{
	$(window).unbind("beforeunload");
}
/**
 * ヘルプを設定
 *
 * @param object parentObj		親オブジェクトまたはタグID
 * @return なし
 */
function m3SetHelp(parentObj)
{
	if (parentObj){
		if (typeof parentObj == 'string'){
			parentObj = $('#' + parentObj);
		}
	    if (jQuery().cluetip){
			parentObj.find('span.m3help').cluetip({ splitTitle: '|', cluezIndex: 2000, hoverIntent:{ sensitivity:1, interval:500, timeout:0 }});
			parentObj.find('div.m3help').cluetip({ splitTitle: '|', cluezIndex: 2000, hoverIntent:{ sensitivity:1, interval:500, timeout:0 }});
		}
	    if (jQuery().tooltip) parentObj.find('[rel=m3help]').tooltip({ 'delay': { show: 1000, hide: 0 }});
	} else {
	    if (jQuery().cluetip){
			$('span.m3help').cluetip({ splitTitle: '|', cluezIndex: 2000, hoverIntent:{ sensitivity:1, interval:500, timeout:0 }});
			$('div.m3help').cluetip({ splitTitle: '|', cluezIndex: 2000, hoverIntent:{ sensitivity:1, interval:500, timeout:0 }});
		}
	    if (jQuery().tooltip) $('[rel=m3help]').tooltip({ 'delay': { show: 1000, hide: 0 }});
	}
}
/**
 * ファイル選択ボタンを設定
 *
 * @return なし
 */
function m3SetFileSelectButton()
{
	$('.btn-file :file').on('fileselect', function(event, numFiles, label){
		var input = $(this).parents('.input-group').find(':text'),
			log = numFiles > 1 ? numFiles + ' files selected' : label;
		
		if (input.length){
			input.val(log);
		} else {
			if (log) alert(log);
		}
	});
	
	$(document).on('change', '.btn-file :file', function(){
	    var input = $(this),
	        numFiles = input.get(0).files ? input.get(0).files.length : 1,
	        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
	    input.trigger('fileselect', [numFiles, label]);
	});
}
/**
 * 設定入力用テーブルのカラー設定
 *
 * @param object  object			テーブルオブジェクトまたはテーブルID文字列
 * @return なし
 */
function m3SetConfigTable(object)
{
	var tableObj;		// テーブルオブジェクト
	
	if (typeof object == 'string'){
		//tableObj = document.getElementById(object);
		tableObj = $('#' + object);
	} else {
		tableObj = object;
	}
	// カラー設定
	tableObj.addClass('table table-condensed table-bordered table-striped m3config_table');
	tableObj.find('th').addClass('info');		// ヘッダ部
	
	tableObj.find('textarea').addClass('form-control');
	tableObj.find('select').addClass('form-control');
	tableObj.find('input[type=text]').addClass('form-control');
}
/**
 * 設定入力用サブテーブルのカラー設定
 *
 * @param object  object			テーブルオブジェクトまたはテーブルID文字列
 * @return なし
 */
function m3SetConfigSubTable(object)
{
	var tableObj;		// テーブルオブジェクト
	
	if (typeof object == 'string'){
		//tableObj = document.getElementById(object);
		tableObj = $('#' + object);
	} else {
		tableObj = object;
	}
	// カラー設定
	tableObj.addClass('table table-condensed table-bordered table-striped');
	tableObj.find('th').addClass('info');		// ヘッダ部
	
	tableObj.find('select').addClass('form-control');
	tableObj.find('input[type=text]').addClass('form-control');
}
/**
 * モーダル入力用テーブルのカラー設定
 *
 * @param object  object			テーブルオブジェクトまたはテーブルID文字列
 * @return なし
 */
function m3SetModalTable(object)
{
	var tableObj;		// テーブルオブジェクト
	
	if (typeof object == 'string'){
		tableObj = document.getElementById(object);
	} else {
		tableObj = object;
	}
	// カラー設定
	$(tableObj).addClass('table table-condensed table-bordered table-striped m3config_modal_table');
	$(tableObj).find('th').addClass('info');		// ヘッダ部
}
/**
 * テーブルに行の並び替え機能を追加
 *
 * @param object   object			テーブルオブジェクトまたはテーブルID文字列
 * @param function reorder_callback	並び替え時コールバック関数
 * @return bool						true=作成成功、false=作成失敗
 */
function m3SetDragDropTable(object, reorder_callback)
{
	if (!jQuery().tableDnD) return false;
	
	var tableObj;		// テーブルオブジェクト
	
	if (typeof object == 'string'){
		tableObj = $('#' + object);
	} else {
		tableObj = object;
	}

	// 行のIDを設定
	tableObj.find('tr').attr('id',function(i){
		return 'm3drag_rowid_' + i;
	});
	
	// ドラッグ&ドロップテーブル作成
	tableObj.tableDnD({
		onDrop: function(table, row){
			_setupDragDropTable(tableObj, reorder_callback);
		},
		dragHandle: ".m3drag_handle"
	});
	tableObj.find('tr').hover(function(){
		$(this.cells[0]).addClass('m3drag_current');
	}, function() {
		$(this.cells[0]).removeClass('m3drag_current');
	});
	
	// 画像項目削除処理
	tableObj.find('tr .m3drag_delrow').off('click').on('click', function(){
		var rowObj = $(this);

		m3Confirm('waring', '項目を削除しますか?', function(result){
			if (result){
			    rowObj.parents('.m3drag_row').fadeTo(400, 0, function(){
			        $(this).remove();
		
					_setupDragDropTable(tableObj, reorder_callback);
			    });
			}
		});
		
    	return false;
	});
	// インデックスNo再設定
	_setupDragDropTable(tableObj, reorder_callback);
	
	// スタイル再設定
	m3SetConfigSubTable(tableObj);
	
	// HELP追加
	m3SetHelp(tableObj);
	
	return true;
}
/**
 * 行の並び替えテーブル用関数
 *
 * @param object  object		テーブルオブジェクトまたはテーブルID文字列
 * @param function	callback	コールバック関数
 * @return なし
 */
function _setupDragDropTable(object, callback)
{
	// インデックスNo再設定
	object.find('tr .m3drag_rowno').each(function(index){
		$(this).text(index + 1);
	});
	
	// コールバック関数を実行
	if (typeof(callback) == 'function') callback();
}
/**
 * ドラッグ&ドロップファイルアップロード機能を作成
 *
 * @param string    id			表示領域タグID
 * @param string    url			アップロード先URL
 * @param function	callback	成功時コールバック関数
 * @param string    extensions	アップロード許可するファイルの拡張子を「,」区切りで列挙
 * @return bool					true=作成成功、false=作成失敗
 */
function m3CreateDragDropUploadFile(id, url, callback, extensions)
{
	if (!jQuery().uploadFile) return false;
	
	if (!extensions) extensions = 'png,gif,jpg,jpeg';
	
	var artisteerStyle = false;
	var bootstrapStyle = true;
	
	$('#' + id).uploadFile({
		url: url,
		allowedTypes: extensions,
		showFileCounter: false,		// ファイルNoなし
		showProgress: true,
		artisteerStyle: artisteerStyle,
		bootstrapStyle: bootstrapStyle,
		stripedBar: true,
		uploadStr: '',
		dragDropStr: '',
		returnType: 'json',
		customErrorKeyStr: 'error',
		abortStr: '中断',
		cancelStr: 'キャンセル',
		deletelStr: '削除',
		doneStr: '完了',
		onSuccess:function(files, data)
		{
			if (typeof(callback) == 'function') callback(files, data);
		}
	});
	return true;
}
/**
 * ウィジェットツールの準備
 *
 * @param string switchButtonClass			切り替えボタンのクラス
 * @return なし
 */
function m3SetupWidgetTool(switchButtonClass)
{
	$('.' + switchButtonClass).each(function ()
	{
		// Settings
		var $widget = $(this);
		var $button = $widget.find('button');
		var $checkbox = $widget.find('input:checkbox');
		var color = $button.data('color'),
			settings = {
				on: {
					icon: 'glyphicon glyphicon-check'
				},
				off: {
					icon: 'glyphicon glyphicon-unchecked'
				}
			};

		// Event Handlers
		$button.on('click', function ()
		{
			// チェックボックスの状態を変更し、ボタンを再描画
			$checkbox.prop('checked', !$checkbox.is(':checked'));
//			$checkbox.triggerHandler('change');
			updateButton();
			
			// ウィジェットツールの表示制御
			updateWidgetTool();
		});
		$checkbox.on('change', function ()
		{
			updateButton();
		});

		// ボタン更新
		function updateButton()
		{
			var isChecked = $checkbox.is(':checked');

			// Set the button's state
			$button.data('state', (isChecked) ? "on" : "off");

			// Set the button's icon
			$button.find('.state-icon').removeClass().addClass('state-icon ' + settings[$button.data('state')].icon);

			// Update the button's color
			if (isChecked){
				$button.removeClass('btn-default').addClass('btn-' + color + ' active');
			} else {
				$button.removeClass('btn-' + color + ' active').addClass('btn-default');
			}
		}

		// ウィジェットツールの表示制御
		function updateWidgetTool()
		{
			var isChecked = $checkbox.is(':checked');
			if (isChecked){
				_m3ShowWidgetTool = true;		// ウィジェットツールを表示するかどうか
				
				// クッキーに状態を保存
				$.cookie('M3WIDGETTOOL', 'on', { expires: 30 });
			} else {
				_m3ShowWidgetTool = false;		// ウィジェットツールを表示するかどうか
				
				// クッキーに状態を保存
				$.cookie('M3WIDGETTOOL', 'off', { expires: 30 });
			}
		}
		
		// Initialization
		function init(){
			// ボタン初期化
			var status = $.cookie('M3WIDGETTOOL');
			if (status == "undefined") status = 'on';		// 初期値設定
			if (status == 'on'){
				$checkbox.prop('checked', true);
			} else {
				$checkbox.prop('checked', false);
			}
			updateWidgetTool();
			
			// ボタン表示
			updateButton();

			// Inject the icon if applicable
			if ($button.find('.state-icon').length == 0){
				$button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i> ');
			}
		}
		init();
	});
}
/**
 * 拡張表示機能を作成
 *
 * @param string showButtonId		表示ボタンのタグID
 * @param string hideButtonId		非表示ボタンのタグID
 * @param string optionAreaClass	表示制御する領域のクラス
 * @param bool isShow				拡張領域の初期状態(true=表示、false=非表示)
 * @return なし
 */
function m3CreateOptionButton(showButtonId, hideButtonId, optionAreaClass, isShow)
{
	// オプション入力制御
	if (isShow){
		$('.' + optionAreaClass).slideDown(300);
		$('#' + showButtonId).css({'display':'none'});
		$('#' + hideButtonId).css({'display':'block'});
	} else {
		$('.' + optionAreaClass).slideUp(300);
		$('#' + showButtonId).css({'display':'block'});
		$('#' + hideButtonId).css({'display':'none'});
	}
	
	$('#' + showButtonId).click(function(){
		$('.' + optionAreaClass).slideDown(300);
		$('#' + showButtonId).css({'display':'none'});
		$('#' + hideButtonId).css({'display':'block'});
		
		// 画面サイズ調整
		m3AdjustParentWindow();
		return false;
	});
	$('#' + hideButtonId).click(function(){
		$('.' + optionAreaClass).slideUp(300);
		$('#' + showButtonId).css({'display':'block'});
		$('#' + hideButtonId).css({'display':'none'});
		
		// 画面サイズ調整
		m3AdjustParentWindow();
		return false;
	});
}
/**
 * 管理画面初期処理
 *
 * @return なし
 */
$(function(){
	//** jQuery Scroll to Top Control script- (c) Dynamic Drive DHTML code library: http://www.dynamicdrive.com.
	//** Available/ usage terms at http://www.dynamicdrive.com (March 30th, 09')
	//** v1.1 (April 7th, 09'):
	//** 1) Adds ability to scroll to an absolute position (from top of page) or specific element on the page instead.
	//** 2) Fixes scroll animation not working in Opera. 
	var scrolltotop = {
		//startline: Integer. Number of pixels from top of doc scrollbar is scrolled before showing control
		//scrollto: Keyword (Integer, or "Scroll_to_Element_ID"). How far to scroll document up when control is clicked on (0=top).
		setting: {startline:100, scrollto: 0, scrollduration:1000, fadeduration:[500, 100]},
		controlHTML: '<img src="' + M3_ROOT_URL + '/images/system/gotop48.png" style="width:48px; height:48px" />', //HTML for control, which is auto wrapped in DIV w/ ID="topcontrol"
		controlattrs: {offsetx:5, offsety:5}, //offset of control relative to right/ bottom of window corner
		anchorkeyword: '#top', //Enter href value of HTML anchors on the page that should also act as "Scroll Up" links

		state: {isvisible:false, shouldvisible:false},

		scrollup:function(){
			if (!this.cssfixedsupport) //if control is positioned using JavaScript
				this.$control.css({ opacity:0 }); //hide control immediately after clicking it
			var dest = isNaN(this.setting.scrollto) ? this.setting.scrollto : parseInt(this.setting.scrollto);
			if (typeof dest == "string" && $('#'+dest).length == 1) //check element set by string exists
				dest=$('#'+dest).offset().top;
			else
				dest=0;
			this.$body.animate({ scrollTop: dest }, this.setting.scrollduration);
		},

		keepfixed:function(){
			var $window=$(window);
			var controlx=$window.scrollLeft() + $window.width() - this.$control.width() - this.controlattrs.offsetx;
			var controly=$window.scrollTop() + $window.height() - this.$control.height() - this.controlattrs.offsety;
			this.$control.css({ left:controlx+'px', top:controly+'px' });
		},

		togglecontrol:function(){
			var scrolltop = $(window).scrollTop();
			if (!this.cssfixedsupport) this.keepfixed();
			this.state.shouldvisible = (scrolltop >= this.setting.startline) ? true : false;
			if (this.state.shouldvisible && !this.state.isvisible){
				this.$control.stop().animate({ opacity:1 }, this.setting.fadeduration[0]);
				this.state.isvisible = true;
			}
			else if (this.state.shouldvisible == false && this.state.isvisible){
				this.$control.stop().animate({ opacity:0 }, this.setting.fadeduration[1]);
				this.state.isvisible = false;
			}
		},
	
		init:function(){
			$(document).ready(function($){
				var mainobj = scrolltotop;
				var iebrws = document.all;
				mainobj.cssfixedsupport = !iebrws || iebrws && document.compatMode == "CSS1Compat" && window.XMLHttpRequest; //not IE or IE7+ browsers in standards mode
				mainobj.$body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
				mainobj.$control = $('<div id="topcontrol">' + mainobj.controlHTML + '</div>')
					.css({ position:mainobj.cssfixedsupport ? 'fixed' : 'absolute', bottom:mainobj.controlattrs.offsety, right:mainobj.controlattrs.offsetx, opacity:0, cursor:'pointer' })
					.attr({ title:'先頭へスクロール' })
					.click(function(){ mainobj.scrollup(); return false; })
					.appendTo('body');
				if (document.all && !window.XMLHttpRequest && mainobj.$control.text() != '') //loose check for IE6 and below, plus whether control contains any text
					mainobj.$control.css({ width:mainobj.$control.width() }); //IE6- seems to require an explicit width on a DIV containing text
				mainobj.togglecontrol();
				$('a[href="' + mainobj.anchorkeyword + '"]').click(function(){
					mainobj.scrollup();
					return false;
				});
				$(window).bind('scroll resize', function(e){
					mainobj.togglecontrol();
				});
			});
		}
	}
	scrolltotop.init();
	
	// 管理画面用部品
	m3LoadOptionUI();
	
	// 最後にヘルプ作成
	$(window).load( function(){ m3SetHelp(); });
});
