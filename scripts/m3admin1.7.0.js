/**
 * Magic3管理機能用JavaScriptライブラリ
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
// ライブラリ用変数
var _m3Url;
var _m3AccessPoint;		// アクセスポイント(空=PC,m=携帯,s=スマートフォン)
var _m3SetUrlCallback;	// リンク作成用コールバック
var _m3ContentEdited;		// 入力コンテンツが変更されたかどうか
var _m3CheckContentEdit;	// 入力コンテンツの変更をチェックするかどうか

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
		window.open(url);
	}
}
// 設定用ウィンドウ表示
function m3ShowConfigWindow(widgetId, configId, serial)
{
	if (M3_CONFIG_WINDOW_OPEN_TYPE == 0){
		window.open(M3_DEFAULT_ADMIN_URL + "?cmd=configwidget&openby=other&widget=" + widgetId + 
					"&_defconfig=" + configId + "&_defserial=" + serial, "",
					"toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900");
	} else {
		window.open(M3_DEFAULT_ADMIN_URL + "?cmd=configwidget&openby=other&widget=" + widgetId + 
					"&_defconfig=" + configId + "&_defserial=" + serial);
	}
}
// ウィジェット表示微調整用ウィンドウ表示
function m3ShowAdjustWindow(configId, serial, pageId, pageSubId)
{
	if (M3_CONFIG_WINDOW_OPEN_TYPE == 0){
		window.open(M3_DEFAULT_ADMIN_URL + "?task=adjustwidget&openby=simple" + 
					"&_defconfig=" + configId + "&_defserial=" + serial + "&_page=" + pageId + "&_sub=" + pageSubId, "",
					"toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1050,height=900");
	} else {
		window.open(M3_DEFAULT_ADMIN_URL + "?task=adjustwidget&openby=simple" + 
					"&_defconfig=" + configId + "&_defserial=" + serial + "&_page=" + pageId + "&_sub=" + pageSubId);
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
// 処理中ダイアログ表示
function m3ShowProcessModal()
{
	// 画面上のフォーカスを外す
	$('input,textarea,select').blur();
	
	if ($('#processing-modal').size() == 0){		// ダイアログが存在しない場合
		var modal  = '<div class="modal modal-processing fade" id="processing-modal" role="dialog" aria-hidden="true">';
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
	$('#processing-modal').modal('show');
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
		width: "80%",
		zIndex: 99999,
		open: function(){
			$(this).parent().css("padding", "0px");
			$(this).css("padding", "0px");
		},
		create: function(event, ui){
			$(this).elfinder({
				url : M3_ROOT_URL + '/scripts/elfinder-2.0/php/connector.php?dirtype=image',
				height: '500px',
				lang: 'ja',
				resizable: false,
				getFileCallback: function(url){
					seturl_callback(url.url);
					$('a.ui-dialog-titlebar-close[role="button"]').click();
				}
			}).elfinder('instance');
		}
	});
}
/**
 * 画像ファイルブラウザを表示(廃止予定)
 *
 * @return なし
 */
function m3_openImageFileBrowser()
{
	var imageConnector = 'connectors/php/connector.php';
	var imageBrowser = M3_ROOT_URL + '/scripts/fckeditor2.6.6/editor/plugins/FileBrowser_Thumbnail/browser.html?Type=Image&Lang=ja&Connector=' + imageConnector;
	var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes,scrollbars=yes";
	var screenWidth, screenHeight;
	var width, height;
	try
	{
		screenWidth	= screen.width;
		screenHeight	= screen.height;
	} catch (e){
		screenWidth		= 800;
		screenHeight	= 600;
	}
	width	= screenWidth * 0.7;
	height	= screenHeight * 0.7;
	window.open(imageBrowser, 'FCKBrowseWindow', sOptions + ",width=" + width + ",height=" + height);
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
		width: "80%",
		zIndex: 99999,
		open: function(){
			$(this).parent().css("padding", "0px");
			$(this).css("padding", "0px");
		},
		create: function(event, ui){
			$(this).elfinder({
				url : M3_ROOT_URL + '/scripts/elfinder-2.0/php/connector.php?dirtype=flash',
				height: '500px',
				lang: 'ja',
				resizable: false,
				getFileCallback: function(url){
					seturl_callback(url.url);
					$('a.ui-dialog-titlebar-close[role="button"]').click();
				}
			}).elfinder('instance');
		}
	});
}
/**
 * Flashファイルブラウザを表示(廃止予定)
 *
 * @return なし
 */
function m3_openFlashFileBrowser()
{
	var imageConnector = 'connectors/php/connector.php';
	var imageBrowser = M3_ROOT_URL + '/scripts/fckeditor2.6.6/editor/plugins/FileBrowser_Thumbnail/browser.html?Type=Flash&Lang=ja&Connector=' + imageConnector;
	var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes,scrollbars=yes";
	var screenWidth, screenHeight;
	var width, height;
	try
	{
		screenWidth	= screen.width;
		screenHeight	= screen.height;
	} catch (e){
		screenWidth		= 800;
		screenHeight	= 600;
	}
	width	= screenWidth * 0.7;
	height	= screenHeight * 0.7;
	window.open(imageBrowser, 'FCKBrowseWindow', sOptions + ",width=" + width + ",height=" + height);
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
	if (M3_WYSIWYG_EDITOR == 'ckeditor'){
		var config = {};
		if (M3_USE_GOOGLEMAPS){
			config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig.js';
		} else {
			config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig_intranet.js';
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
	config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig_direct.js';
//	config['toolbar'] = 'Full';
	CKEDITOR.replace('_dummy_ckeditor', config);
	dummyCKParent.hide();
}
/**
 * リンク用のURLを作成
 * あらかじめを実行しておく。
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
	$("form input, form select, form textarea").change(function(){
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
 * @return なし
 */
function m3SetHelp()
{
    $('span.m3help').cluetip({splitTitle: '|', cluezIndex: 2000});
    if (jQuery().tooltip) $('[rel=m3help]').tooltip({ placement: 'top'});
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
/*
 * 初期処理
 */
$(function(){
	//** jQuery Scroll to Top Control script- (c) Dynamic Drive DHTML code library: http://www.dynamicdrive.com.
	//** Available/ usage terms at http://www.dynamicdrive.com (March 30th, 09')
	//** v1.1 (April 7th, 09'):
	//** 1) Adds ability to scroll to an absolute position (from top of page) or specific element on the page instead.
	//** 2) Fixes scroll animation not working in Opera. 
	var scrolltotop={
		//startline: Integer. Number of pixels from top of doc scrollbar is scrolled before showing control
		//scrollto: Keyword (Integer, or "Scroll_to_Element_ID"). How far to scroll document up when control is clicked on (0=top).
		setting: {startline:100, scrollto: 0, scrollduration:1000, fadeduration:[500, 100]},
		controlHTML: '<img src="' + M3_ROOT_URL + '/images/system/gotop48.png" style="width:48px; height:48px" />', //HTML for control, which is auto wrapped in DIV w/ ID="topcontrol"
		controlattrs: {offsetx:5, offsety:5}, //offset of control relative to right/ bottom of window corner
		anchorkeyword: '#top', //Enter href value of HTML anchors on the page that should also act as "Scroll Up" links

		state: {isvisible:false, shouldvisible:false},

		scrollup:function(){
			if (!this.cssfixedsupport) //if control is positioned using JavaScript
				this.$control.css({opacity:0}) //hide control immediately after clicking it
			var dest=isNaN(this.setting.scrollto)? this.setting.scrollto : parseInt(this.setting.scrollto)
			if (typeof dest=="string" && $('#'+dest).length==1) //check element set by string exists
				dest=$('#'+dest).offset().top
			else
				dest=0
			this.$body.animate({scrollTop: dest}, this.setting.scrollduration);
		},

		keepfixed:function(){
			var $window=$(window)
			var controlx=$window.scrollLeft() + $window.width() - this.$control.width() - this.controlattrs.offsetx
			var controly=$window.scrollTop() + $window.height() - this.$control.height() - this.controlattrs.offsety
			this.$control.css({left:controlx+'px', top:controly+'px'})
		},

		togglecontrol:function(){
			var scrolltop=$(window).scrollTop()
			if (!this.cssfixedsupport)
				this.keepfixed()
			this.state.shouldvisible=(scrolltop>=this.setting.startline)? true : false
			if (this.state.shouldvisible && !this.state.isvisible){
				this.$control.stop().animate({opacity:1}, this.setting.fadeduration[0])
				this.state.isvisible=true
			}
			else if (this.state.shouldvisible==false && this.state.isvisible){
				this.$control.stop().animate({opacity:0}, this.setting.fadeduration[1])
				this.state.isvisible=false
			}
		},
	
		init:function(){
			$(document).ready(function($){
				var mainobj=scrolltotop
				var iebrws=document.all
				mainobj.cssfixedsupport=!iebrws || iebrws && document.compatMode=="CSS1Compat" && window.XMLHttpRequest //not IE or IE7+ browsers in standards mode
				mainobj.$body=(window.opera)? (document.compatMode=="CSS1Compat"? $('html') : $('body')) : $('html,body')
				mainobj.$control=$('<div id="topcontrol">'+mainobj.controlHTML+'</div>')
					.css({position:mainobj.cssfixedsupport? 'fixed' : 'absolute', bottom:mainobj.controlattrs.offsety, right:mainobj.controlattrs.offsetx, opacity:0, cursor:'pointer'})
					.attr({title:'先頭へスクロール'})
					.click(function(){mainobj.scrollup(); return false})
					.appendTo('body')
				if (document.all && !window.XMLHttpRequest && mainobj.$control.text()!='') //loose check for IE6 and below, plus whether control contains any text
					mainobj.$control.css({width:mainobj.$control.width()}) //IE6- seems to require an explicit width on a DIV containing text
				mainobj.togglecontrol()
				$('a[href="' + mainobj.anchorkeyword +'"]').click(function(){
					mainobj.scrollup()
					return false
				})
				$(window).bind('scroll resize', function(e){
					mainobj.togglecontrol()
				})
			})
		}
	}
	scrolltotop.init()
});
