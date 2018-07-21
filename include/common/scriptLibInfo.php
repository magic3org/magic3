<?php
/**
 * Javascriptライブラリ情報クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
class ScriptLibInfo
{
	private static $libs;						// ライブラリ情報
	private static $jQueryVer = '1.9';			// デフォルトで使用するjQueryのバージョン
	private static $jQueryVersionArray = array(
											'1.6'	=> 'jquery-1.6.4.min.js',// jQueryバージョン
											'1.7'	=> 'jquery-1.7.2.min.js',
											'1.8'	=> 'jquery-1.8.3.min.js',
											'1.9'	=> 'jquery-1.9.1.min.js',
											'1.10'	=> 'jquery-1.10.2.min.js',
											'1.11'	=> 'jquery-1.11.3.min.js',
											'1.12'	=> 'jquery-1.12.4.min.js',
											'2.0'	=> 'jquery-2.0.3.min.js',
											'2.1'	=> 'jquery-2.1.4.min.js',
											'2.2'	=> 'jquery-2.2.4.min.js',
											'3.1'	=> 'jquery-3.1.0.min.js'
										);
	private static $wysiwygEditorType = 'fckeditor';		// WYSIWYGエディタータイプ
	private static $ckeditorVer = 1;			// 使用するCKEditorのバージョン(0=デフォルト, 1=最新)

	// ##### 選択中のライブラリ #####
//	const SELECTED_LIB_ELFINDER = 'elfinder112';		// 選択中のelFinder、「elfinder」または「elfinder112」「elfinder115」「elfinder123」が設定可能。(PHP v5.3対応) 
//	const SELECTED_LIB_ELFINDER = 'elfinder115';		// 選択中のelFinder、「elfinder」または「elfinder112」「elfinder115」「elfinder123」が設定可能。(PHP v5.3対応) 
//	const SELECTED_LIB_ELFINDER = 'elfinder123';		// 選択中のelFinder、「elfinder」または「elfinder112」「elfinder115」「elfinder123」が設定可能。(PHP v5.3対応) 
	const SELECTED_LIB_ELFINDER = 'elfinder130';		// 選択中のelFinder、「elfinder」または「elfinder112」「elfinder115」「elfinder123」「elfinder130」が設定可能。(PHP v5.3対応) 
//	const SELECTED_LIB_ELFINDER = 'elfinder139';		// 選択中のelFinder、「elfinder」または「elfinder112」「elfinder115」「elfinder123」「elfinder130」「elfinder139」が設定可能。
		
	// ##### Javascriptライブラリ(DBでの設定値) #####
	// ライブラリセット(複数ライブラリの構成)
	const LIB_SET_CKEDITOR_M3_TOOLBAR	= 'ckeditor_m3toolbar';		// CKEditorのツールバー用ライブラリ
	
	// ベースライブラリ
	const LIB_JQUERY				= 'jquery';
	const LIB_JQUERY_UI				= 'jquery-ui';
	const LIB_WYSIWYG_EDITOR		= 'wysiwyg_editor';			// LIB_FCKEDITORまたはLIB_CKEDITOR
	const LIB_FCKEDITOR				= 'fckeditor';
	const LIB_CKEDITOR				= 'ckeditor';
	const LIB_ELFINDER				= 'elfinder';
	const LIB_ELFINDER112			= 'elfinder112';			// v2.1.12
	const LIB_ELFINDER115			= 'elfinder115';			// v2.1.15
//	const LIB_ELFINDER121			= 'elfinder121';			// v2.1.21
	const LIB_ELFINDER123			= 'elfinder123';			// v2.1.23
	const LIB_ELFINDER130			= 'elfinder130';			// v2.1.30
	const LIB_ELFINDER139			= 'elfinder139';			// v2.1.39
	const LIB_MD5					= 'md5';
	const LIB_MOMENT				= 'moment';
	const LIB_SWFOBJECT				= 'swfobject';
	const LIB_JSCALENDAR			= 'jscalendar';			// カレンダーライブラリ
	const LIB_BOOTSTRAP				= 'bootstrap';
	const LIB_BOOTSTRAP_ADMIN		= 'bootstrap_admin';		// Bootstrap管理画面用オプション
	const LIB_NOBOOTSTRAP			= 'nobootstrap';			// Bootstrapを使用しない場合の管理画面用ライブラリ
	const LIB_SOCKETIO				= 'socketio';				// socket.io
	const LIB_WEBRTC				= 'webrtc';				// WebRTC

	// Bootstrapプラグイン
	const LIB_BOOTSTRAP_DATETIMEPICKER		= 'bootstrap.datetimepicker';
	const LIB_BOOTSTRAP_TOGGLE				= 'bootstrap.toggle';

	// スマートフォン用jQueryライブラリ
	const LIB_JQUERYS				= 'jquerys';
	const LIB_JQUERYS_MOBILE		= 'jquery.mobile';

	// jQueryプラグイン
	const LIB_JQUERY_EASING			= 'jquery.easing';
	const LIB_JQUERY_JCAROUSEL		= 'jquery.jcarousel';
	const LIB_JQUERY_THICKBOX		= 'jquery.thickbox';
	const LIB_JQUERY_CYCLE			= 'jquery.cycle';
	const LIB_JQUERY_CODEPRESS		= 'jquery.codepress';
	const LIB_JQUERY_CLUETIP		= 'jquery.cluetip';
	const LIB_JQUERY_SIMPLETREE		= 'jquery.simpletree';
	const LIB_JQUERY_BGIFRAME		= 'jquery.bgiframe';
	const LIB_JQUERY_HOVERINTENT	= 'jquery.hoverintent';
	const LIB_JQUERY_TABLEDND		= 'jquery.tablednd';
	const LIB_JQUERY_SIMPLEMODAL	= 'jquery.simplemodal';
	const LIB_JQUERY_COOKIE			= 'jquery.cookie';
	const LIB_JQUERY_FORMAT			= 'jquery.format';
	const LIB_JQUERY_FORMTIPS		= 'jquery.formtips';
	const LIB_JQUERY_FACEBOX		= 'jquery.facebox';
	const LIB_JQUERY_CURVYCORNERS	= 'jquery.curvycorners';
	const LIB_JQUERY_PRETTYPHOTO	= 'jquery.prettyphoto';
	const LIB_JQUERY_QTIP			= 'jquery.qtip';
	const LIB_JQUERY_QTIP2			= 'jquery.qtip2';
	const LIB_JQUERY_CALCULATION	= 'jquery.calculation';
	const LIB_JQUERY_JQPLOT			= 'jquery.jqplot';
	const LIB_JQUERY_YOUTUBEPLAYER	= 'jquery.youtubeplayer';
	const LIB_JQUERY_JSTREE			= 'jquery.jstree';
	const LIB_JQUERY_IFRAME			= 'jquery.iframe';
	const LIB_JQUERY_RATY			= 'jquery.raty';
	const LIB_JQUERY_MOUSEWHEEL		= 'jquery.mousewheel';
	const LIB_JQUERY_CLOUDCAROUSEL	= 'jquery.cloudcarousel';
	const LIB_JQUERY_SCROLLTO		= 'jquery.scrollto';
	const LIB_JQUERY_FULLCALENDAR	= 'jquery.fullcalendar';
	const LIB_JQUERY_FULLCALENDAR_GOOGLE	= 'jquery.fullcalendar.google';			// Google連携オプション
	const LIB_JQUERY_TIMEPICKER		= 'jquery.timepicker';
	const LIB_JQUERY_JSON			= 'jquery.json';
	const LIB_JQUERY_FITTEXT		= 'jquery.fittext';
	const LIB_JQUERY_IDTABS			= 'jquery.idtabs';
	const LIB_JQUERY_BXSLIDER		= 'jquery.bxslider';
	const LIB_JQUERY_FITVIDS		= 'jquery.fitvids';
	const LIB_JQUERY_RESPONSIVETABLE	= 'jquery.responsivetable';
	const LIB_JQUERY_FORM			= 'jquery.form';
	const LIB_JQUERY_UPLOADFILE		= 'jquery.uploadfile';
//	const LIB_JQUERY_UPLOADFILE4	= 'jquery.uploadfile4';		// jQuery Uploadfile v4版
	const LIB_JQUERY_JCROP			= 'jquery.jcrop';
	const LIB_JQUERY_NUMERIC		= 'jquery.numeric';
	const LIB_JQUERY_STICKY			= 'jquery.sticky';

	// Magic3管理画面専用jQueryプラグイン
	const LIB_JQUERY_M3_SLIDEPANEL		= 'jquery.m3slidepanel';		// スライドパネル
	const LIB_JQUERY_M3_DROPDOWN		= 'jquery.m3dropdown';		// ドロップダウンメニュー
	const LIB_JQUERY_M3_STICKHEADER		= 'jquery.m3stickyheader';		// スクロールバー付きテーブル

	// CodeMirror
	const LIB_CODEMIRROR_JAVASCRIPT	= 'codemirror.javascript';		// CodeMirror Javascript

	// Bootstrapプラグインバージョン
	const BOOTSTRAP_DATETIMEPICKER_VER	= '4.0.0';
	const BOOTSTRAP_TOGGLE_VER			= '2.2.2';

	// jQueryプラグインバージョン
	const JQUERY_JCAROUSEL_VER		= '0.2.8';
	const JQUERY_THICKBOX_VER		= '3.1';
	const JQUERY_COOKIE_VER			= '1.4.0';
	const JQUERY_PRETTYPHOTO_VER	= '3.1.6';
	const JQUERY_QTIP_VER			= '1.0';
	const JQUERY_QTIP2_VER			= '2.1.1';
	const JQUERY_CALCULATION_VER	= '0.4.07';
	const JQUERY_JQPLOT_VER			= '1.0.8';
	const JQUERY_JSTREE_VER			= '1.0-rc3';
	const JQUERY_IFRAME_VER			= '2.0.0';
	const JQUERY_RATY_VER			= '1.4.3';
	const JQUERY_MOUSEWHEEL_VER		= '3.0.6';
	const JQUERY_CLOUDCAROUSEL_VER	= '1.0.5';
	const JQUERY_SCROLLTO_VER		= '1.4.3.1';
//	const JQUERY_FULLCALENDAR_VER	= '2.2.6';
	const JQUERY_FULLCALENDAR_VER	= '2.3.1';
	const JQUERY_TIMEPICKER_VER		= '0.3.2';
	const JQUERY_JSON_VER			= '2.4.0';
	const JQUERY_FITTEXT_VER		= '1.2';
	const JQUERY_IDTABS_VER			= '2.2';
	const JQUERY_BXSLIDER_VER		= '4.1.2';
	const JQUERY_FITVIDS_VER		= '1.1';
	const JQUERY_RESPONSIVETABLE_VER	= '5.0.4';
	const JQUERY_FORM_VER			= '3.51.0';
//	const JQUERY_UPLOADFILE_VER		= '3.1.10';
	const JQUERY_UPLOADFILE_VER		= '4.0.10';
	const JQUERY_JCROP_VER			= '0.9.12';
	const JQUERY_NUMERIC_VER		= '1.4.1';
	const JQUERY_STICKY_VER			= '1.0.4';
	// その他ライブラリバージョン
	const CODEMIRROR_VER			= '3.1';

	// ライブラリディレクトリ
	const JQUERY_JQPLOT_DIR			= 'jquery/jqplot1.0.8';

	// jQuery UI
	const LIB_JQUERY_UI_WIDGETS_ACCORDION		= 'jquery-ui.accordion';		// Widgets Accordion
	const LIB_JQUERY_UI_WIDGETS_AUTOCOMPLETE	= 'jquery-ui.autocomplete';		// Widgets Autocomplete
	const LIB_JQUERY_UI_WIDGETS_BUTTON			= 'jquery-ui.button';			// Widgets Button
	const LIB_JQUERY_UI_WIDGETS_DATEPICKER		= 'jquery-ui.datepicker';		// Widgets Datepicker
	const LIB_JQUERY_UI_WIDGETS_DIALOG			= 'jquery-ui.dialog';			// Widgets Dialog
	const LIB_JQUERY_UI_WIDGETS_PROGRESSBAR		= 'jquery-ui.progressbar';		// Widgets Progressbar
	const LIB_JQUERY_UI_WIDGETS_SLIDER			= 'jquery-ui.slider';			// Widgets Slider
	const LIB_JQUERY_UI_WIDGETS_TABS			= 'jquery-ui.tabs';				// Widgets Tabs
	const LIB_JQUERY_UI_EFFECTS					= 'jquery-ui.effects';			// Effects

	// Bootstrapプラグイン用のファイル
	const BOOTSTRAP_DATETIMEPICKER_FILENAME		= 'bootstrap/bootstrap-datetimepicker-4.0.0/bootstrap-datetimepicker.js';
	const BOOTSTRAP_DATETIMEPICKER_CSS			= 'bootstrap/bootstrap-datetimepicker-4.0.0/bootstrap-datetimepicker.min.css';
	const BOOTSTRAP_TOGGLE_FILENAME				= 'bootstrap/bootstrap-toggle-2.2.2/bootstrap-toggle.min.js';		// Bootstrap Toggleボタン
	const BOOTSTRAP_TOGGLE_CSS					= 'bootstrap/bootstrap-toggle-2.2.2/bootstrap-toggle.css';

	// jQueryプラグイン用のファイル
	const JQUERY_EASING_FILENAME		= 'jquery/jquery.easing.1.3.js';
	//const JQUERY_JCAROUSEL_FILENAME		= 'jquery/jquery.jcarousel.0.2.4.min.js';		// jQuery1.4.2対応版
	const JQUERY_JCAROUSEL_FILENAME		= 'jquery/jquery.jcarousel.0.2.8.min.js';		// jQuery1.4.2対応版
//	const JQUERY_CONTEXTMENU_FILENAME	= 'jquery/jquery.contextmenu.r2.packed.js';
	const JQUERY_CONTEXTMENU_FILENAME	= 'jquery/jquery.contextmenu.r2.js';
	const JQUERY_THICKBOX_FILENAME		= 'jquery/thickbox3.1.js';
	const JQUERY_THICKBOX_CSS			= 'jquery/thickbox.css';
	const JQUERY_CYCLE_FILENAME			= 'jquery/jquery.cycle.all.pack.js';
	const JQUERY_CODEPRESS_FILENAME		= 'jquery/jquery.Codepress.js';
	const JQUERY_CLUETIP_FILENAME		= 'jquery/jquery.cluetip.js';
	const JQUERY_CLUETIP_CSS			= 'jquery/jquery.cluetip.css';
	const JQUERY_SIMPLETREE_FILENAME	= 'jquery/jquery.simple.tree.js';
	const JQUERY_SIMPLETREE_CSS			= 'jquery/simple.tree.css';
	const JQUERY_BGIFRAME_FILENAME		= 'jquery/jquery.bgiframe.min.js';
	const JQUERY_HOVERINTENT_FILENAME	= 'jquery/jquery.hoverIntent.min.js';
//	const JQUERY_TABLEDND_FILENAME		= 'jquery/jquery.tablednd_0_5.js';
const JQUERY_TABLEDND_FILENAME		= 'jquery/tablednd/jquery.tablednd-0.9.2.js';
	const JQUERY_TABLEDND_CSS			= 'jquery/jquery.tablednd.css';
	const JQUERY_SIMPLEMODAL_FILENAME	= 'jquery/jquery.simplemodal-1.3.5.min.js';
	const JQUERY_COOKIE_FILENAME		= 'jquery/jquery.cookie.js';
//	const JQUERY_FORMAT_FILENAME		= 'jquery/jquery.format.1.03.js';
	const JQUERY_FORMAT_FILENAME		= 'jquery/jquery.format.1.05.js';
	const JQUERY_FORMTIPS_FILENAME		= 'jquery/jquery.formtips.1.2.packed.js';
	const JQUERY_FACEBOX_FILENAME		= 'jquery/facebox.js';
	const JQUERY_FACEBOX_CSS			= 'jquery/facebox.css';
	const JQUERY_CURVYCORNERS_FILENAME	= 'jquery/jquery.curvycorners.packed.js';
	const JQUERY_PRETTYPHOTO_DIR		= 'jquery/prettyphoto-3.1.6';
	const JQUERY_PRETTYPHOTO_FILENAME	= 'jquery/prettyphoto-3.1.6/js/jquery.prettyPhoto.js';
	const JQUERY_PRETTYPHOTO_CSS		= 'jquery/prettyphoto-3.1.6/css/prettyPhoto.css';
//	const JQUERY_QTIP_FILENAME			= 'jquery/jquery.qtip-1.0.min.js';
	const JQUERY_QTIP_FILENAME			= 'jquery/jquery.qtip-1.0.0-rc3.min.js';
	const JQUERY_QTIP2_FILENAME			= 'jquery/qtip2-2.1.1/jquery.qtip.min.js';
	const JQUERY_QTIP2_CSS				= 'jquery/qtip2-2.1.1/jquery.qtip.min.css';
	const JQUERY_CALCULATION_FILENAME	= 'jquery/jquery.calculation.js';
	const JQUERY_JQPLOT_FILENAME		= 'jquery/jqplot1.0.8/jquery.jqplot.min.js';
	const JQUERY_JQPLOT_CSS				= 'jquery/jqplot1.0.8/jquery.jqplot.min.css';
	const JQUERY_YOUTUBEPLAYER_FILENAME	= 'jquery/jquery.youtube.player.js';
	const JQUERY_JSTREE_FILENAME		= 'jquery/jstree/jquery.jstree.js';
//	const JQUERY_IFRAME_FILENAME		= 'jquery/jquery.iframe-auto-height.plugin.1.5.0.min.js';
//	const JQUERY_IFRAME_FILENAME		= 'jquery/jquery.iframe-auto-height.plugin.1.7.1.min.js';
//	const JQUERY_IFRAME_FILENAME		= 'jquery/jquery.iframe-auto-height.plugin.1.9.1.min.js';
	const JQUERY_IFRAME_FILENAME		= 'jquery/jquery.iframe-auto-height.2.0.0.min.js';
	const JQUERY_IFRAME_BROWSER_FILENAME	= 'jquery/jquery.iframe-auto-height.browser.js';			// jQuery.iframe用ブラウザ判定
	const JQUERY_RATY_FILENAME			= 'jquery/raty/jquery.raty.js';
	const JQUERY_MOUSEWHEEL_FILENAME	= 'jquery/jquery.mousewheel.js';
	const JQUERY_CLOUDCAROUSEL_FILENAME	= 'jquery/cloud-carousel.1.0.5.min.js';
	const JQUERY_SCROLLTO_FILENAME		= 'jquery/jquery.scrollTo-1.4.3.1-min.js';
	const JQUERY_FULLCALENDAR_FILENAME			= 'jquery/fullcalendar-2.3.1/fullcalendar.js';
	const JQUERY_FULLCALENDAR_LANG_FILENAME		= 'jquery/fullcalendar-2.3.1/lang/{LANG}.js';			// 言語ファイル
	const JQUERY_FULLCALENDAR_CSS				= 'jquery/fullcalendar-2.3.1/fullcalendar.css';
	const JQUERY_FULLCALENDAR_GOOGLE_FILENAME	= 'jquery/fullcalendar-2.3.1/gcal.js';				// FullCalendarプラグインのGoogle連携オプション
	const JQUERY_TIMEPICKER_FILENAME	= 'jquery/timepicker/jquery.ui.timepicker.js';
	const JQUERY_TIMEPICKER_LANG_FILENAME	= 'jquery/timepicker/i18n/jquery.ui.timepicker-ja.js';
	const JQUERY_TIMEPICKER_CSS			= 'jquery/timepicker/jquery.ui.timepicker.css';
	const JQUERY_JSON_FILENAME			= 'jquery/jquery.json-2.4.min.js';
	const JQUERY_FITTEXT_FILENAME		= 'jquery/jquery.fittext.js';
	const JQUERY_IDTABS_FILENAME		= 'jquery/jquery.idTabs.min.js';
	const JQUERY_BXSLIDER_FILENAME		= 'jquery/bxslider/jquery.bxslider.min.js';
	const JQUERY_BXSLIDER_CSS			= 'jquery/bxslider/jquery.bxslider.css';
	const JQUERY_FITVIDS_FILENAME		= 'jquery/jquery.fitvids.js';
	const JQUERY_RESPONSIVETABLE_FILENAME	= 'jquery/responsiveTable/js/rwd-table.js';
	const JQUERY_RESPONSIVETABLE_CSS		= 'jquery/responsiveTable/css/rwd-table.css';
	const JQUERY_FORM_FILENAME			= 'jquery/jquery.form.min.js';
//	const JQUERY_UPLOADFILE_FILENAME	= 'jquery/uploadfile/jquery.uploadfile.js';
//	const JQUERY_UPLOADFILE_CSS			= 'jquery/uploadfile/uploadfile.css';
	const JQUERY_UPLOADFILE_FILENAME	= 'jquery/uploadfile/jquery.uploadfile-4.0.10.js';		// jQuery Uploadfile v4版
	const JQUERY_UPLOADFILE_CSS			= 'jquery/uploadfile/uploadfile.css';
	const JQUERY_JCROP_FILENAME			= 'jquery/jcrop0.9.12/jquery.Jcrop.js';
	const JQUERY_JCROP_CSS				= 'jquery/jcrop0.9.12/jquery.Jcrop.css';
	const JQUERY_NUMERIC_FILENAME		= 'jquery/jquery.numeric.min.js';
	const JQUERY_STICKY_FILENAME		= 'jquery/jquery.sticky-1.0.4.js';

	// Magic3管理画面jQueryプラグインのファイル名
	const JQUERY_M3_SLIDEPANEL_FILENAME	= 'jquery/jquery.m3slidepanel.js';	// スライドパネル
	const JQUERY_M3_DROPDOWN_FILENAME	= 'jquery/jquery.m3dropdown.js';	// ドロップダウンメニュー
	const JQUERY_M3_DROPDOWN_CSS		= 'jquery/jquery.m3dropdown.css';	// ドロップダウンメニュー
	const JQUERY_M3_STICKHEADER_FILENAME		= 'jquery/jquery.m3stickyheader.js';	// スクロールバー付きテーブル
	const JQUERY_M3_STICKHEADER_OTHER_FILENAME	= 'jquery/jquery.ba-throttle-debounce.min.js';	// スクロールバー付きテーブル用ライブラリ

	// ライブラリの公式サイトのURL
	const BOOTSTRAP_DATETIMEPICKER_URL	= 'https://github.com/Eonasdan/bootstrap-datetimepicker';
	const BOOTSTRAP_TOGGLE_URL			= 'https://github.com/minhur/bootstrap-toggle';
	const JQUERY_JCAROUSEL_URL			= 'http://sorgalla.com/projects/jcarousel/';
	const JQUERY_THICKBOX_URL			= 'http://thickbox.net/';
	const JQUERY_CLUETIP_URL			= 'http://plugins.learningjquery.com/cluetip/';
	const JQUERY_COOKIE_URL				= 'https://github.com/carhartl/jquery-cookie';
	const JQUERY_FACEBOX_URL			= 'http://defunkt.github.com/facebox/';
	const JQUERY_CURVYCORNERS_URL		= 'http://code.google.com/p/jquerycurvycorners/';
	const JQUERY_PRETTYPHOTO_URL		= 'http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/';
	const JQUERY_QTIP_URL				= 'http://craigsworks.com/projects/qtip/';
	const JQUERY_QTIP2_URL				= 'http://qtip2.com/';
	const JQUERY_CALCULATION_URL		= 'http://www.pengoworks.com/workshop/jquery/calculation/calculation.plugin.htm';
	const JQUERY_JQPLOT_URL				= 'http://www.jqplot.com/';
	const JQUERY_JSTREE_URL				= 'http://www.jstree.com/';
	const JQUERY_IFRAME_URL				= 'https://github.com/house9/jquery-iframe-auto-height';
	const JQUERY_RATY_URL				= 'http://www.wbotelhos.com/raty/';
	const JQUERY_MOUSEWHEEL_URL			= 'https://github.com/brandonaaron/jquery-mousewheel';
	const JQUERY_CLOUDCAROUSEL_URL		= 'http://www.professorcloud.com/mainsite/carousel.htm';
	const JQUERY_SCROLLTO_URL			= 'http://flesler.blogspot.jp/2007/10/jqueryscrollto.html';
	const JQUERY_FULLCALENDAR_URL		= 'http://arshaw.com/fullcalendar/';
	const JQUERY_TIMEPICKER_URL			= 'http://fgelinas.com/code/timepicker/';
	const JQUERY_JSON_URL				= 'http://code.google.com/p/jquery-json/';
	const JQUERY_FITTEXT_URL			= 'http://fittextjs.com/';
	const JQUERY_IDTABS_URL				= 'http://www.sunsean.com/idTabs/';
	const CODEMIRROR_URL				= 'http://codemirror.net/';		// CodeMirror
	const JQUERY_BXSLIDER_URL			= 'http://bxslider.com/';
	const JQUERY_FITVIDS_URL			= 'http://fitvidsjs.com/';
	const JQUERY_RESPONSIVETABLE_URL	= 'http://gergeo.se/RWD-Table-Patterns';
	const JQUERY_FORM_URL				= 'http://malsup.com/jquery/form/';
	const JQUERY_UPLOADFILE_URL			= 'https://github.com/hayageek/jquery-upload-file/';
	const JQUERY_JCROP_URL				= 'http://deepliquid.com/content/Jcrop.html';
	const JQUERY_NUMERIC_URL			= 'http://www.texotela.co.uk/code/jquery/numeric/';
	const JQUERY_STICKY_URL				= 'http://stickyjs.com/';

	// ディレクトリ名
	const FCKEDITOR_DIRNAME				= 'fckeditor2.6.6';				// FCKEditor

	// ファイル名
	//const JQUERY_L_FILENAME			= 'jquery-1.6.4.min.js';					// JQuery最新版(v1.6.4)		// 2012/10/13～
//	const JQUERY_UI_CORE_FILENAME	= 'jquery-ui-core-1.9.2.min.js';			// JQuery UI Core (Core,Interactions)
	const JQUERY_UI_CORE_FILENAME	= 'jquery-ui-core-1.11.4.min.js';			// JQuery UI Core (Core,Interactions)
	const FCKEDITOR_FILENAME		= 'fckeditor2.6.6/fckeditor.js';			// FCKEditor
//	const CKEDITOR_FILENAME			= 'ckeditor4.4.2/ckeditor.js';				// CKEditor(デフォルト)→廃止
	const CKEDITOR462_FILENAME		= 'ckeditor4.6.2/ckeditor.js';				// CKEditor(最新スマートフォン対応)
	
	// elFinder v2.0版
	const ELFINDER_VER				= '2.1';									// elFinderバージョン
	const ELFINDER_FILENAME			= 'elfinder-2.1/js/elfinder.full.js';		// elFinder
	const ELFINDER_LANG_FILENAME	= 'elfinder-2.1/js/i18n/elfinder.ja.js';	// elFinder言語ファイル
	const ELFINDER_CSS				= 'elfinder-2.1/css/elfinder.full.css';		// elFinder CSS
	const ELFINDER_OPTION_CSS		= 'elfinder-2.1/css/theme.css';				// elFinder CSS
//	const ELFINDER_THEME_CSS		= 'elfinder-2.1/theme/smoothness/jquery-ui.custom.min.css';		// テーマファイル
	const ELFINDER_THEME_CSS		= 'elfinder-2.1/theme/smoothness/jquery-ui.custom.css';		// テーマファイル
/*
	const ELFINDER_FILENAME			= 'elfinder-2.1.12/js/elfinder.full.js';		// elFinder
	const ELFINDER_LANG_FILENAME	= 'elfinder-2.1.12/js/i18n/elfinder.ja.js';	// elFinder言語ファイル
	const ELFINDER_CSS				= 'elfinder-2.1.12/css/elfinder.full.css';		// elFinder CSS
	const ELFINDER_OPTION_CSS		= 'elfinder-2.1.12/css/theme.css';				// elFinder CSS
//	const ELFINDER_THEME_CSS		= 'elfinder-2.1.12/theme/smoothness/jquery-ui.custom.min.css';		// テーマファイル
	const ELFINDER_THEME_CSS		= 'elfinder-2.1.12/theme/smoothness/jquery-ui.custom.css';		// テーマファイル
*/
	// elFinder v2.1.12版
	const ELFINDER112_VER			= '2.1.12';									// elFinderバージョン
//	const ELFINDER112_FILENAME		= 'elfinder-2.1.12/js/elfinder.full.js';		// elFinder
	const ELFINDER112_FILENAME		= 'elfinder-2.1.12/js/elfinder.min.js';		// elFinder
	const ELFINDER112_LANG_FILENAME	= 'elfinder-2.1.12/js/i18n/elfinder.ja.js';	// elFinder言語ファイル
	const ELFINDER112_CSS			= 'elfinder-2.1.12/css/elfinder.full.css';		// elFinder CSS
	const ELFINDER112_OPTION_CSS		= 'elfinder-2.1.12/css/theme.css';				// elFinder CSS
//	const ELFINDER112_THEME_CSS		= 'elfinder-2.1.12/theme/smoothness/jquery-ui.min.css';		// テーマファイル
	const ELFINDER112_THEME_CSS		= 'elfinder-2.1.12/theme/smoothness/jquery-ui.css';		// テーマファイル

	// elFinder v2.1.15版
	const ELFINDER115_VER			= '2.1.15';									// elFinderバージョン
//	const ELFINDER115_FILENAME		= 'elfinder-2.1.15/js/elfinder.full.js';		// elFinder
	const ELFINDER115_FILENAME		= 'elfinder-2.1.15/js/elfinder.min.js';		// elFinder
	const ELFINDER115_LANG_FILENAME	= 'elfinder-2.1.15/js/i18n/elfinder.ja.js';	// elFinder言語ファイル
	const ELFINDER115_CSS			= 'elfinder-2.1.15/css/elfinder.full.css';		// elFinder CSS
	const ELFINDER115_OPTION_CSS		= 'elfinder-2.1.15/css/theme.css';				// elFinder CSS
//	const ELFINDER115_THEME_CSS		= 'elfinder-2.1.15/theme/smoothness/jquery-ui.min.css';		// テーマファイル
	const ELFINDER115_THEME_CSS		= 'elfinder-2.1.15/theme/smoothness/jquery-ui.css';		// テーマファイル

/*	// elFinder v2.1.21版
	const ELFINDER121_VER			= '2.1.21';									// elFinderバージョン
//	const ELFINDER121_FILENAME		= 'elfinder-2.1.21/js/elfinder.full.js';		// elFinder
	const ELFINDER121_FILENAME		= 'elfinder-2.1.21/js/elfinder.min.js';		// elFinder
	const ELFINDER121_LANG_FILENAME	= 'elfinder-2.1.21/js/i18n/elfinder.ja.js';	// elFinder言語ファイル
	const ELFINDER121_CSS			= 'elfinder-2.1.21/css/elfinder.full.css';		// elFinder CSS
	const ELFINDER121_OPTION_CSS		= 'elfinder-2.1.21/css/theme.css';				// elFinder CSS
//	const ELFINDER121_THEME_CSS		= 'elfinder-2.1.21/theme/smoothness/jquery-ui.min.css';		// テーマファイル
	const ELFINDER121_THEME_CSS		= 'elfinder-2.1.21/theme/smoothness/jquery-ui.css';		// テーマファイル
*/
	// elFinder v2.1.23版
	const ELFINDER123_VER			= '2.1.23';									// elFinderバージョン
	const ELFINDER123_FILENAME		= 'elfinder-2.1.23/js/elfinder.full.js';		// elFinder
//	const ELFINDER123_FILENAME		= 'elfinder-2.1.23/js/elfinder.min.js';		// elFinder
	const ELFINDER123_LANG_FILENAME	= 'elfinder-2.1.23/js/i18n/elfinder.ja.js';	// elFinder言語ファイル
	const ELFINDER123_CSS			= 'elfinder-2.1.23/css/elfinder.full.css';		// elFinder CSS
	const ELFINDER123_OPTION_CSS	= 'elfinder-2.1.23/css/theme.css';				// elFinder CSS
//	const ELFINDER123_THEME_CSS		= 'elfinder-2.1.23/theme/smoothness/jquery-ui.min.css';		// テーマファイル
//	const ELFINDER123_THEME_CSS		= 'elfinder-2.1.23/theme/smoothness/jquery-ui.css';		// テーマファイル

	// elFinder v2.1.30版
	// elfinder.min.jsは変更なしで使用し、elfinder.full.cssはMagic3用にカスタマイズ
	const ELFINDER130_VER			= '2.1.30';									// elFinderバージョン
//	const ELFINDER130_FILENAME		= 'elfinder-2.1.30/js/elfinder.full.js';		// elFinder
	const ELFINDER130_FILENAME		= 'elfinder-2.1.30/js/elfinder.min.js';		// elFinder
	const ELFINDER130_LANG_FILENAME	= 'elfinder-2.1.30/js/i18n/elfinder.ja.js';	// elFinder言語ファイル
	const ELFINDER130_CSS			= 'elfinder-2.1.30/css/elfinder.full.css';		// elFinder CSS
//	const ELFINDER130_CSS			= 'elfinder-2.1.30/css/elfinder.min.css';		// elFinder CSS
	const ELFINDER130_OPTION_CSS	= 'elfinder-2.1.30/css/theme.css';				// elFinder CSS

	// 新バージョンリリースのためv2.1.39は削除
	const ELFINDER139_VER			= '2.1.39';									// elFinderバージョン
//	const ELFINDER139_FILENAME		= 'elfinder-2.1.39/js/elfinder.full.js';		// elFinder
	const ELFINDER139_FILENAME		= 'elfinder-2.1.39/js/elfinder.min.js';		// elFinder
	const ELFINDER139_LANG_FILENAME	= 'elfinder-2.1.39/js/i18n/elfinder.ja.js';	// elFinder言語ファイル
	const ELFINDER139_CSS			= 'elfinder-2.1.39/css/elfinder.full.css';		// elFinder CSS
//	const ELFINDER139_CSS			= 'elfinder-2.1.39/css/elfinder.min.css';		// elFinder CSS
	const ELFINDER139_OPTION_CSS	= 'elfinder-2.1.39/css/theme.css';				// elFinder CSS
	
	const MD5_FILENAME				= 'md5.js';									// MD5
	const SOCKETIO_FILENAME			= 'socket.io/socket.io.js';					// socket.io
	const MOMENT_FILENAME			= 'moment-with-locales-2.9.0.js';			// Moment.js
	const SWFOBJECT_FILENAME		= 'swfobject/swfobject.js';					// swfobject
	const JSCALENDAR_FILENAME		= 'jscalendar-1.0/calendar.js';				// jscalendar
	const JSCALENDAR_LANG_FILENAME	= 'jscalendar-1.0/lang/calendar-ja.js';		// jscalendar言語ファイル
	const JSCALENDAR_SETUP_FILENAME	= 'jscalendar-1.0/calendar-setup.js';		// jscalendarセットアップファイル
	const JSCALENDAR_CSS			= 'jscalendar-1.0/calendar-win2k-1.css';	// jscalendarCSS
	const WEBRTC_ADAPTER_FILENAME	= 'adapter-1.1.0.js';				// WebRTC

	// Bootstrapライブラリ
	const BOOTSTRAP_FILENAME		= 'bootstrap-3.3.7/js/bootstrap.min.js';			// bootstrap
	const BOOTSTRAP_CSS				= 'bootstrap-3.3.7/css/bootstrap.min.css';
	const BOOTSTRAP_ADMIN_CSS		= 'bootstrap-3.3.7/css/bootswatch_flatly_ja.css';	// 管理画面用のCSS
	// BootstrapオプションはBootstrapのディレクトリ配下に格納
	const BOOTSTRAP_BOOTSWATCH_FLATLY_CSS			= 'bootstrap-3.3.7/css/bootswatch_flatly_ja.css';	// bootstrap配色(日本語フォント)
	const BOOTSTRAP_BOOTSNIPP_LARGEDROPDOWNMENU_CSS = 'bootstrap/css/bootsnipp_largedropdownmenu.css';
	const BOOTSTRAP_YAMM_CSS						= 'bootstrap/css/yamm.css';					// bootstrapメガメニュー
//	const BOOTSTRAP_DIALOG_FILENAME					= 'bootstrap/bootstrap3-dialog/bootstrap-dialog.js';		// Bootstrap Dialog拡張
//	const BOOTSTRAP_DIALOG_CSS						= 'bootstrap/bootstrap3-dialog/bootstrap-dialog.css';
//	const BOOTSTRAP_DIALOG_FILENAME					= 'bootstrap/bootstrap3-dialog-1.34.1/bootstrap-dialog.js';		// Bootstrap Dialog拡張		// スクロールバーが消えるバグあり
//	const BOOTSTRAP_DIALOG_CSS						= 'bootstrap/bootstrap3-dialog-1.34.1/bootstrap-dialog.css';
//	const BOOTSTRAP_DIALOG_FILENAME					= 'bootstrap/bootstrap3-dialog-1.34.2/bootstrap-dialog.js';		// Bootstrap Dialog拡張		// スクロールバーが消えるバグあり
//	const BOOTSTRAP_DIALOG_CSS						= 'bootstrap/bootstrap3-dialog-1.34.2/bootstrap-dialog.css';	// (未使用)
	const BOOTSTRAP_DIALOG_FILENAME					= 'bootstrap/bootstrap3-dialog-1.34.7/bootstrap-dialog.js';		// Bootstrap Dialog拡張
	const BOOTSTRAP_DIALOG_CSS						= 'bootstrap/bootstrap3-dialog-1.34.7/bootstrap-dialog.css';	// (タイトルヘッダーカラーに使用)
//	const BOOTSTRAP_DIALOG_FILENAME					= 'bootstrap/bootstrap3-dialog-1.34.0/bootstrap-dialog.js';		// Bootstrap Dialog拡張
//	const BOOTSTRAP_DIALOG_CSS						= 'bootstrap/bootstrap3-dialog-1.34.0/bootstrap-dialog.css';	// (未使用)
	const NOBOOTSTRAP_CSS							= 'm3/nobootstrap/style.css';
	const NOBOOTSTRAP_TOOLTIP_FILENAME				= 'm3/nobootstrap/tooltip.js';			// bootstrapツールチップ
	const NOBOOTSTRAP_DROPDOWN_FILENAME				= 'm3/nobootstrap/dropdown.js';			// bootstrapドロップダウンメニュー

	const CODEMIRROR_FILENAME				= 'codemirror-3.1/lib/codemirror.js';				// CodeMirror
	const CODEMIRROR_CSS					= 'codemirror-3.1/lib/codemirror.css';				// CodeMirror
	const CODEMIRROR_JAVASCRIPT_FILENAME	= 'codemirror-3.1/mode/javascript/javascript.js';	// CodeMirror Javascript

	// jQuery UI用ファイル
/*
	// jQuery UI 1.9.2
	const JQUERY_UI_WIDGETS_ACCORDION_FILENAME		= 'jquery/ui/1.9.2/jquery.ui.accordion.min.js';		// Widgets Accordion
	const JQUERY_UI_WIDGETS_AUTOCOMPLETE_FILENAME	= 'jquery/ui/1.9.2/jquery.ui.autocomplete.min.js';		// Widgets Autocomplete
	const JQUERY_UI_WIDGETS_BUTTON_FILENAME			= 'jquery/ui/1.9.2/jquery.ui.button.min.js';			// Widgets Button
	const JQUERY_UI_WIDGETS_DATEPICKER_FILENAME		= 'jquery/ui/1.9.2/jquery.ui.datepicker.min.js';		// Widgets Datepicker
	const JQUERY_UI_WIDGETS_DATEPICKER_LANG_FILENAME	= 'jquery/ui/1.9.2/jquery.ui.datepicker-ja.js';		// Widgets Datepicker
	const JQUERY_UI_WIDGETS_DIALOG_FILENAME			= 'jquery/ui/1.9.2/jquery.ui.dialog.min.js';			// Widgets Dialog
	const JQUERY_UI_WIDGETS_PROGRESSBAR_FILENAME	= 'jquery/ui/1.9.2/jquery.ui.progressbar.min.js';		// Widgets Progressbar
	const JQUERY_UI_WIDGETS_SLIDER_FILENAME			= 'jquery/ui/1.9.2/jquery.ui.slider.min.js';			// Widgets Slider
	const JQUERY_UI_WIDGETS_TABS_FILENAME			= 'jquery/ui/1.9.2/jquery.ui.tabs.min.js';				// Widgets Tabs
	const JQUERY_UI_EFFECTS_FILENAME				= 'jquery/ui/1.9.2/jquery.effects.min.js';					// Effects
*/
	// jQuery UI 1.11.4
	const JQUERY_UI_WIDGETS_ACCORDION_FILENAME		= 'jquery/ui/1.11.4/jquery.ui.accordion.min.js';		// Widgets Accordion
	const JQUERY_UI_WIDGETS_AUTOCOMPLETE_FILENAME	= 'jquery/ui/1.11.4/jquery.ui.autocomplete.min.js';		// Widgets Autocomplete
	const JQUERY_UI_WIDGETS_BUTTON_FILENAME			= 'jquery/ui/1.11.4/jquery.ui.button.min.js';			// Widgets Button
	const JQUERY_UI_WIDGETS_DATEPICKER_FILENAME		= 'jquery/ui/1.11.4/jquery.ui.datepicker.min.js';		// Widgets Datepicker
	const JQUERY_UI_WIDGETS_DATEPICKER_LANG_FILENAME	= 'jquery/ui/1.11.4/jquery.ui.datepicker-ja.js';		// Widgets Datepicker
	const JQUERY_UI_WIDGETS_DIALOG_FILENAME			= 'jquery/ui/1.11.4/jquery.ui.dialog.min.js';			// Widgets Dialog
	const JQUERY_UI_WIDGETS_PROGRESSBAR_FILENAME	= 'jquery/ui/1.11.4/jquery.ui.progressbar.min.js';		// Widgets Progressbar
	const JQUERY_UI_WIDGETS_SLIDER_FILENAME			= 'jquery/ui/1.11.4/jquery.ui.slider.min.js';			// Widgets Slider
	const JQUERY_UI_WIDGETS_TABS_FILENAME			= 'jquery/ui/1.11.4/jquery.ui.tabs.min.js';				// Widgets Tabs
	const JQUERY_UI_EFFECTS_FILENAME				= 'jquery/ui/1.11.4/jquery.effects.min.js';					// Effects

	// スマートフォン用jQueryファイル
	//const JQUERYS_FILENAME			= 'jquery-1.6.4.min.js';					// JQuery MobileはjQuery v1.6以上が必要
	const JQUERYS_FILENAME			= 'jquery-1.7.2.min.js';					// JQuery Mobile v1.3はjQuery v1.7以上が必要
//	const JQUERYS_MOBILE_FILENAME	= 'jquery_mobile/jquery.mobile-1.2.1.min.js';					// JQuery Mobile
//	const JQUERYS_MOBILE_CSS		= 'jquery_mobile/jquery.mobile.structure-1.2.1.min.css';				// JQuery Mobile
//	const JQUERYS_MOBILE_FILENAME	= 'jquery_mobile/jquery.mobile-1.3.0.min.js';					// JQuery Mobile
//	const JQUERYS_MOBILE_CSS		= 'jquery_mobile/jquery.mobile.structure-1.3.0.min.css';				// JQuery Mobile
	const JQUERYS_MOBILE_FILENAME	= 'jquery_mobile/jquery.mobile-1.3.2.min.js';					// JQuery Mobile
	const JQUERYS_MOBILE_CSS		= 'jquery_mobile/jquery.mobile.structure-1.3.2.min.css';				// JQuery Mobile

	// ##### 外部ライブラリ #####
	const LIB_GOOGLEMAPS			= 'googlemaps';
//	const GOOGLEMAPS_FILENAME		= 'http://maps.google.com/maps/api/js?sensor=true';
	const GOOGLEMAPS_FILENAME		= 'http://maps.googleapis.com/maps/api/js';		// 2016/9/19 更新

	// DB定義値取得用
	const CF_GOOGLE_MAPS_KEY = 'google_maps_key';				// GoogleマップAPIキー

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * jQueryのバージョンを設定
	 *
	 * @param int			$version	jQueryのバージョン(0=v2.6、1=最新)
	 */
	static function setJQueryVer($version)
	{
		if (!empty($version)) self::$jQueryVer = $version;
	}
	/**
	 * jQueryファイル名取得
	 *
	 * @param  int    $type	ファイルの種別(0=jQuery、1=jQuery UI Core、2=jQuery UI Plus、10=スマートフォン用jQuery)
	 * @return string		jQueryファイル名を取得
	 */
	static function getJQueryFilename($type = 0)
	{
		$filename = '';
		if ($type == 0){	// jQuery
			$filename = self::$jQueryVersionArray[(string)self::$jQueryVer];
			//if (empty($filename)) $filename = self::JQUERY_L_FILENAME;	// jQuery本体
		} else if ($type == 1){	// Core
			$filename = self::JQUERY_UI_CORE_FILENAME;	// jquery UI
//		} else if ($type == 2){	// Plus
//			$filename = self::JQUERY_L_UI_PLUS_FILENAME;	// jquery UI
		} else if ($type == 10){	// スマートフォン用jQuery
			$filename = self::JQUERYS_FILENAME;
		}
		return $filename;
	}
	/**
	 * CKEditorのバージョンを設定
	 *
	 * @param int			$version	CKEditorのバージョン(0=デフォルト, 1=最新)
	 */
	static function setCkeditorVer($version)
	{
		self::$ckeditorVer = $version;
	}
	/**
	 * ライブラリ情報取得
	 *
	 * @return array		ライブラリ情報
	 */
	static function getLib()
	{
		global $gSystemManager;
		
		if (!isset(self::$libs)){
			// GoogleMaps用ライブラリ用のパラメータ取得
			$googleMapsParams = '';
			$param = $gSystemManager->getSystemConfig(self::CF_GOOGLE_MAPS_KEY);
			if (!empty($param)) $googleMapsParams = '?key=' . $param;
			
			// CKEditorのスクリプトファイルを取得
//			$ckeditorFile = self::CKEDITOR_FILENAME;		// CKEditor(デフォルト)
//			if (self::$ckeditorVer == 1) $ckeditorFile = self::CKEDITOR462_FILENAME;		// CKEditor(最新スマートフォン対応)
			$ckeditorFile = self::CKEDITOR462_FILENAME;		// CKEditor(最新スマートフォン対応)
			
			// ##### ライブラリ情報初期化 ####
			self::$libs = array(
						self::LIB_MD5					=>	array(	'script' 	=> array(self::MD5_FILENAME)),			// MD5
						self::LIB_SOCKETIO			=>	array(	'script' 	=> ''/*空文字列は直前で作成*/),			// socket.io
						self::LIB_WEBRTC				=>	array(	'script' 	=> array(self::WEBRTC_ADAPTER_FILENAME)),			// WebRTC
						self::LIB_MOMENT				=>	array(	'script' 	=> array(self::MOMENT_FILENAME)),		// Moment.js
						self::LIB_FCKEDITOR				=>	array(	'script' 	=> array(self::FCKEDITOR_FILENAME)),	// FCKEditor
						self::LIB_CKEDITOR				=>	array(	'script' 	=> array($ckeditorFile)),		// CKEditor
						self::LIB_ELFINDER				=>	array(	'script' 	=> array(self::ELFINDER_FILENAME, self::ELFINDER_LANG_FILENAME),		// elFinder
																	'css'		=> array(self::ELFINDER_THEME_CSS, self::ELFINDER_CSS, self::ELFINDER_OPTION_CSS),		// テーマは最初に読み込む
																	'version'	=> self::ELFINDER_VER					// elFinderバージョン
																	),
						self::LIB_SWFOBJECT				=>	array(	'script' 	=> array(self::SWFOBJECT_FILENAME)),	// swfobject
						self::LIB_JSCALENDAR			=>	array(	'script' 	=> array(
																							self::JSCALENDAR_FILENAME,			// jscalendar
																							self::JSCALENDAR_LANG_FILENAME,		// jscalendar言語ファイル
																							self::JSCALENDAR_SETUP_FILENAME		// jscalendarセットアップファイル
																						),
																	'css'		=> array(
																							self::JSCALENDAR_CSS				// jscalendarCSS
																						)),
/*						self::LIB_BOOTSTRAP				=>	array(	'script' 	=> array(self::BOOTSTRAP_FILENAME),		// bootstrap
																	'css'		=> array(self::BOOTSTRAP_CSS)),			// CSSファイル必要?
																	*/
						self::LIB_BOOTSTRAP				=>	array(	'script' 	=> array(self::BOOTSTRAP_FILENAME)),		// bootstrap
						self::LIB_BOOTSTRAP_ADMIN		=>	array(	'script' 	=> array(self::BOOTSTRAP_DIALOG_FILENAME),
																	'css'		=> array(	self::BOOTSTRAP_BOOTSWATCH_FLATLY_CSS,
																							self::BOOTSTRAP_BOOTSNIPP_LARGEDROPDOWNMENU_CSS,
																							self::BOOTSTRAP_DIALOG_CSS)),	// Bootstrap管理画面用オプション
/*																	'css'		=> array(	self::BOOTSTRAP_BOOTSWATCH_FLATLY_CSS,
																							self::BOOTSTRAP_BOOTSNIPP_LARGEDROPDOWNMENU_CSS)),*/
						self::LIB_NOBOOTSTRAP			=>	array(	'script' 	=> array(self::NOBOOTSTRAP_TOOLTIP_FILENAME, self::NOBOOTSTRAP_DROPDOWN_FILENAME),// Bootstrapなし管理画面用スクリプト
																	'css'		=> array(self::NOBOOTSTRAP_CSS)),

						// Bootstrapプラグイン
						self::LIB_BOOTSTRAP_DATETIMEPICKER		=>	array(	'script' 	=> array(self::BOOTSTRAP_DATETIMEPICKER_FILENAME),		// bootstrap.datetimepicker用
																			'css'		=> array(self::BOOTSTRAP_DATETIMEPICKER_CSS),
																			'url'		=> self::BOOTSTRAP_DATETIMEPICKER_URL,
																			'version'	=> self::BOOTSTRAP_DATETIMEPICKER_VER),
						self::LIB_BOOTSTRAP_TOGGLE				=> array(	'script' 	=> array(self::BOOTSTRAP_TOGGLE_FILENAME),		// Bootstrap Toggleボタン
																			'css'		=> array(self::BOOTSTRAP_TOGGLE_CSS),
																			'url'		=> self::BOOTSTRAP_TOGGLE_URL,
																			'version'	=> self::BOOTSTRAP_TOGGLE_VER),

						// jQueryライブラリ
						self::LIB_JQUERY_EASING			=>	array(	'script' 	=> array(self::JQUERY_EASING_FILENAME)),		// jquery.easing用のファイル
						self::LIB_JQUERY_JCAROUSEL		=>	array(	'script' 	=> array(self::JQUERY_JCAROUSEL_FILENAME),
																	'url'		=> self::JQUERY_JCAROUSEL_URL,
																	'version'	=> self::JQUERY_JCAROUSEL_VER),			// jquery.jcarousel用のファイル
						self::LIB_JQUERY_THICKBOX		=>	array(	'script' 	=> array(self::JQUERY_THICKBOX_FILENAME),// jquery.thickbox用のファイル
																	'css'		=> array(self::JQUERY_THICKBOX_CSS),
																	'url'		=> self::JQUERY_THICKBOX_URL,
																	'version'	=> self::JQUERY_THICKBOX_VER),
						self::LIB_JQUERY_CYCLE			=>	array(	'script' 	=> array(self::JQUERY_CYCLE_FILENAME)),		// jquery.cycle用のファイル
						self::LIB_JQUERY_CODEPRESS		=>	array(	'script' 	=> array(self::JQUERY_CODEPRESS_FILENAME)),	// jquery.codepress用のファイル
						self::LIB_JQUERY_CLUETIP		=>	array(	'script' 	=> array(self::JQUERY_CLUETIP_FILENAME),// jquery.cluetip用のファイル
																	'css'		=> array(self::JQUERY_CLUETIP_CSS),
																	'url'		=> self::JQUERY_CLUETIP_URL),
						self::LIB_JQUERY_SIMPLETREE		=>	array(	'script' 	=> array(self::JQUERY_SIMPLETREE_FILENAME),// jquery.simpletree用のファイル
																	'css'		=> array(self::JQUERY_SIMPLETREE_CSS)),
						self::LIB_JQUERY_BGIFRAME		=>	array(	'script' 	=> array(self::JQUERY_BGIFRAME_FILENAME)),	// jquery.bgiframe用のファイル
						self::LIB_JQUERY_HOVERINTENT	=>	array(	'script' 	=> array(self::JQUERY_HOVERINTENT_FILENAME)),
						self::LIB_JQUERY_TABLEDND		=>	array(	'script' 	=> array(self::JQUERY_TABLEDND_FILENAME),	// jquery.tablednd用のファイル
																	'css'		=> array(self::JQUERY_TABLEDND_CSS)),
						self::LIB_JQUERY_SIMPLEMODAL	=>	array(	'script'	=> array(self::JQUERY_SIMPLEMODAL_FILENAME)),// jquery.simplemodal用のファイル
						self::LIB_JQUERY_COOKIE			=>	array(	'script' 	=> array(self::JQUERY_COOKIE_FILENAME),
																	'url'		=> self::JQUERY_COOKIE_URL,
																	'version'	=> self::JQUERY_COOKIE_VER),
						self::LIB_JQUERY_FORMAT			=>	array(	'script' 	=> array(self::JQUERY_FORMAT_FILENAME)),
						self::LIB_JQUERY_FORMTIPS		=>	array(	'script' 	=> array(self::JQUERY_FORMTIPS_FILENAME)),
						self::LIB_JQUERY_FACEBOX		=>	array(	'script' 	=> array(self::JQUERY_FACEBOX_FILENAME),	// jquery.facebox用のファイル
																	'css'		=> array(self::JQUERY_FACEBOX_CSS),
																	'url'		=> self::JQUERY_FACEBOX_URL),
						self::LIB_JQUERY_CURVYCORNERS	=> array(	'script'	=> array(self::JQUERY_CURVYCORNERS_FILENAME),
																	'url'		=> self::JQUERY_CURVYCORNERS_URL),
						self::LIB_JQUERY_PRETTYPHOTO	=>	array(	'script' 	=> array(self::JQUERY_PRETTYPHOTO_FILENAME),	// jquery.prettyPhoto用のファイル
																	'css'		=> array(self::JQUERY_PRETTYPHOTO_CSS),
																	'dir'		=> self::JQUERY_PRETTYPHOTO_DIR,				// 格納ディレクトリ
																	'url'		=> self::JQUERY_PRETTYPHOTO_URL,
																	'version'	=> self::JQUERY_PRETTYPHOTO_VER),
						self::LIB_JQUERY_QTIP			=>	array(	'script' 	=> array(self::JQUERY_QTIP_FILENAME),	// jquery.qtip用のファイル
																	'url'		=> self::JQUERY_QTIP_URL,
																	'version'	=> self::JQUERY_QTIP_VER),
						self::LIB_JQUERY_QTIP2			=>	array(	'script' 	=> array(self::JQUERY_QTIP2_FILENAME),	// jquery.qtip2用のファイル
																	'css'		=> array(self::JQUERY_QTIP2_CSS),
																	'url'		=> self::JQUERY_QTIP2_URL,
																	'version'	=> self::JQUERY_QTIP2_VER),
						self::LIB_JQUERY_CALCULATION	=>	array(	'script' 	=> array(self::JQUERY_CALCULATION_FILENAME),	// jquery.calculation用のファイル
																	'url'		=> self::JQUERY_CALCULATION_URL,
																	'version'	=> self::JQUERY_CALCULATION_VER),
						self::LIB_JQUERY_JQPLOT			=>	array(	'script' 	=> array(self::JQUERY_JQPLOT_FILENAME),	// jquery.jqplot用のファイル
																	'css'		=> array(self::JQUERY_JQPLOT_CSS),
																	'dir'		=> self::JQUERY_JQPLOT_DIR,
																	'url'		=> self::JQUERY_JQPLOT_URL,
																	'version'	=> self::JQUERY_JQPLOT_VER),
						self::LIB_JQUERY_YOUTUBEPLAYER	=>	array(	'script'	=> array(self::JQUERY_YOUTUBEPLAYER_FILENAME)),// jquery.youtubeplayer用のファイル
						self::LIB_JQUERY_JSTREE			=>	array(	'script' 	=> array(self::JQUERY_JSTREE_FILENAME),	// jquery.jstree用のファイル
																	'url'		=> self::JQUERY_JSTREE_URL,
																	'version'	=> self::JQUERY_JSTREE_VER),
						self::LIB_JQUERY_IFRAME			=>	array(	'script'	=> array(self::JQUERY_IFRAME_FILENAME, self::JQUERY_IFRAME_BROWSER_FILENAME),		// jquery.iframe-auto-height用のファイル
																	'url'		=> self::JQUERY_IFRAME_URL,
																	'version'	=> self::JQUERY_IFRAME_VER),
						self::LIB_JQUERY_RATY			=>	array(	'script'	=> array(self::JQUERY_RATY_FILENAME),		// jquery.raty用のファイル
																	'url'		=> self::JQUERY_RATY_URL,
																	'version'	=> self::JQUERY_RATY_VER),
						self::LIB_JQUERY_MOUSEWHEEL		=>	array(	'script'	=> array(self::JQUERY_MOUSEWHEEL_FILENAME),		// jquery.mousewheel用のファイル
																	'url'		=> self::JQUERY_MOUSEWHEEL_URL,
																	'version'	=> self::JQUERY_MOUSEWHEEL_VER),
						self::LIB_JQUERY_CLOUDCAROUSEL	=>	array(	'script'	=> array(self::JQUERY_CLOUDCAROUSEL_FILENAME),		// jquery.cloudcarousel用のファイル
																	'url'		=> self::JQUERY_CLOUDCAROUSEL_URL,
																	'version'	=> self::JQUERY_CLOUDCAROUSEL_VER),
						self::LIB_JQUERY_SCROLLTO		=>	array(	'script' 	=> array(self::JQUERY_SCROLLTO_FILENAME),	// jquery.scrollto用のファイル
																	'url'		=> self::JQUERY_SCROLLTO_URL,
																	'version'	=> self::JQUERY_SCROLLTO_VER),
						self::LIB_JQUERY_FULLCALENDAR	=>	array(	'script' 	=> array(self::JQUERY_FULLCALENDAR_FILENAME),	// jquery.FullCalendar用のファイル
																	'css'		=> array(self::JQUERY_FULLCALENDAR_CSS),
																	'url'		=> self::JQUERY_FULLCALENDAR_URL,
																	'version'	=> self::JQUERY_FULLCALENDAR_VER,
																	'script_lang'	=> array(	array( 'script' 	=> self::JQUERY_FULLCALENDAR_LANG_FILENAME,	'default_lang'	=> ''))),	// 言語ファイル(ファイルパスのLANG値を現在の言語IDに変換するためのオプション。デフォルトの言語IDを指定。デフォルトが空の場合、ファイルパスを返さないの意。)
						self::LIB_JQUERY_FULLCALENDAR_GOOGLE	=> array(	'script' 	=> array(self::JQUERY_FULLCALENDAR_GOOGLE_FILENAME)),	// jquery.FullCalendarのGoogle連携オプション
						self::LIB_JQUERY_TIMEPICKER		=>	array(	'script' 	=> array(self::JQUERY_TIMEPICKER_FILENAME,		// jquery.timepicker用のファイル
																						self::JQUERY_TIMEPICKER_LANG_FILENAME),	// 言語ファイル
																	'css'		=> array(self::JQUERY_TIMEPICKER_CSS),
																	'url'		=> self::JQUERY_TIMEPICKER_URL,
																	'version'	=> self::JQUERY_TIMEPICKER_VER),
						self::LIB_JQUERY_JSON			=>	array(	'script' 	=> array(self::JQUERY_JSON_FILENAME),	// jquery.json用のファイル
																	'url'		=> self::JQUERY_JSON_URL,
																	'version'	=> self::JQUERY_JSON_VER),
						self::LIB_JQUERY_FITTEXT		=>	array(	'script' 	=> array(self::JQUERY_FITTEXT_FILENAME),	// jquery.fittext用のファイル
																	'url'		=> self::JQUERY_FITTEXT_URL,
																	'version'	=> self::JQUERY_FITTEXT_VER),
						self::LIB_JQUERY_IDTABS			=>	array(	'script' 	=> array(self::JQUERY_IDTABS_FILENAME),	// jquery.idtabs用のファイル
																	'url'		=> self::JQUERY_IDTABS_URL,
																	'version'	=> self::JQUERY_IDTABS_VER),
						self::LIB_JQUERY_BXSLIDER		=>	array(	'script' 	=> array(self::JQUERY_BXSLIDER_FILENAME),		// jquery.bxslider用のファイル
																	'css'		=> array(self::JQUERY_BXSLIDER_CSS),
																	'url'		=> self::JQUERY_BXSLIDER_URL,
																	'version'	=> self::JQUERY_BXSLIDER_VER),
						self::LIB_JQUERY_FITVIDS		=>	array(	'script' 	=> array(self::JQUERY_FITVIDS_FILENAME),		// jquery.fitvids用のファイル
																	'url'		=> self::JQUERY_FITVIDS_URL,
																	'version'	=> self::JQUERY_FITVIDS_VER),
						self::LIB_JQUERY_RESPONSIVETABLE	=> array(	'script' 	=> array(self::JQUERY_RESPONSIVETABLE_FILENAME),
																		'css'		=> array(self::JQUERY_RESPONSIVETABLE_CSS),
																		'url'		=> self::JQUERY_RESPONSIVETABLE_URL,
																		'version'	=> self::JQUERY_RESPONSIVETABLE_VER),
						self::LIB_JQUERY_FORM			=>	array(	'script' 	=> array(self::JQUERY_FORM_FILENAME),
																	'url'		=> self::JQUERY_FORM_URL,
																	'version'	=> self::JQUERY_FORM_VER),
						self::LIB_JQUERY_UPLOADFILE		=> array(	'script' 	=> array(self::JQUERY_UPLOADFILE_FILENAME),
																	'css'		=> array(self::JQUERY_UPLOADFILE_CSS),
																	'url'		=> self::JQUERY_UPLOADFILE_URL,
																	'version'	=> self::JQUERY_UPLOADFILE_VER),
/*						self::LIB_JQUERY_UPLOADFILE4	=> array(	'script' 	=> array(self::JQUERY_UPLOADFILE4_FILENAME),			// jQuery Uploadfile v4版
																	'css'		=> array(self::JQUERY_UPLOADFILE4_CSS),
																	'url'		=> self::JQUERY_UPLOADFILE_URL,
																	'version'	=> self::JQUERY_UPLOADFILE4_VER),*/
						self::LIB_JQUERY_JCROP			=> array(	'script' 	=> array(self::JQUERY_JCROP_FILENAME),
																	'css'		=> array(self::JQUERY_JCROP_CSS),
																	'url'		=> self::JQUERY_JCROP_URL,
																	'version'	=> self::JQUERY_JCROP_VER),
						self::LIB_JQUERY_NUMERIC		=> array(	'script' 	=> array(self::JQUERY_NUMERIC_FILENAME),
																	'url'		=> self::JQUERY_NUMERIC_URL,
																	'version'	=> self::JQUERY_NUMERIC_VER),
						self::LIB_JQUERY_STICKY			=> array(	'script' 	=> array(self::JQUERY_STICKY_FILENAME),
																	'url'		=> self::JQUERY_STICKY_URL,
																	'version'	=> self::JQUERY_STICKY_VER),

						// Magic3管理画面専用jQueryプラグイン
						self::LIB_JQUERY_M3_SLIDEPANEL	=>	array(	'script' 	=> array(self::JQUERY_M3_SLIDEPANEL_FILENAME)),	// スライドパネル
						self::LIB_JQUERY_M3_DROPDOWN	=>	array(	'script' 	=> array(self::JQUERY_M3_DROPDOWN_FILENAME),	// ドロップダウンメニュー
																	'css'		=> array(self::JQUERY_M3_DROPDOWN_CSS)),
						self::LIB_JQUERY_M3_STICKHEADER	=>	array(	'script' 	=> array(self::JQUERY_M3_STICKHEADER_OTHER_FILENAME, self::JQUERY_M3_STICKHEADER_FILENAME)),	// スクロールバー付きテーブル

						// その他ライブラリ
						self::LIB_CODEMIRROR_JAVASCRIPT		=>	array(	'script' 	=> array(self::CODEMIRROR_FILENAME, self::CODEMIRROR_JAVASCRIPT_FILENAME),	// CodeMirror用のファイル
																		'css'		=> array(self::CODEMIRROR_CSS),
																		'url'		=> self::CODEMIRROR_URL,
																		'version'	=> self::CODEMIRROR_VER)
																	);
																	
			// ##### ライブラリ情報更新 ####
			// elFinderの選択状態に応じてライブラリを入れ替え
			if (self::SELECTED_LIB_ELFINDER == self::LIB_ELFINDER112){
				self::$libs[self::LIB_ELFINDER] = array(	'script' 	=> array(self::ELFINDER112_FILENAME, self::ELFINDER112_LANG_FILENAME),		// elFinder v2.1.12
															'css'		=> array(self::ELFINDER112_THEME_CSS, self::ELFINDER112_CSS, self::ELFINDER112_OPTION_CSS),	// テーマは最初に読み込む
															'version'	=> self::ELFINDER112_VER					// elFinderバージョン
														);
			} else if (self::SELECTED_LIB_ELFINDER == self::LIB_ELFINDER115){
				self::$libs[self::LIB_ELFINDER] = array(	'script' 	=> array(self::ELFINDER115_FILENAME, self::ELFINDER115_LANG_FILENAME),		// elFinder v2.1.15
															'css'		=> array(self::ELFINDER115_THEME_CSS, self::ELFINDER115_CSS, self::ELFINDER115_OPTION_CSS),	// テーマは最初に読み込む
															'version'	=> self::ELFINDER115_VER					// elFinderバージョン
														);
/*			} else if (self::SELECTED_LIB_ELFINDER == self::LIB_ELFINDER121){
				self::$libs[self::LIB_ELFINDER] = array(	'script' 	=> array(self::ELFINDER121_FILENAME, self::ELFINDER121_LANG_FILENAME),		// elFinder v2.1.15
															'css'		=> array(self::ELFINDER121_THEME_CSS, self::ELFINDER121_CSS, self::ELFINDER121_OPTION_CSS),	// テーマは最初に読み込む
															'version'	=> self::ELFINDER121_VER					// elFinderバージョン
														);*/
			} else if (self::SELECTED_LIB_ELFINDER == self::LIB_ELFINDER123){
				self::$libs[self::LIB_ELFINDER] = array(	'script' 	=> array(self::ELFINDER123_FILENAME, self::ELFINDER123_LANG_FILENAME),		// elFinder v2.1.23
														//	'css'		=> array(self::ELFINDER123_THEME_CSS, self::ELFINDER123_CSS, self::ELFINDER123_OPTION_CSS),	// テーマは最初に読み込む
															'css'		=> array(self::ELFINDER123_CSS, self::ELFINDER123_OPTION_CSS),	// テーマは最初に読み込む
															'version'	=> self::ELFINDER123_VER					// elFinderバージョン
														);
			} else if (self::SELECTED_LIB_ELFINDER == self::LIB_ELFINDER130){
				self::$libs[self::LIB_ELFINDER] = array(	'script' 	=> array(self::ELFINDER130_FILENAME, self::ELFINDER130_LANG_FILENAME),		// elFinder v2.1.30
															'css'		=> array(self::ELFINDER130_CSS, self::ELFINDER130_OPTION_CSS),	// テーマは最初に読み込む
															'version'	=> self::ELFINDER130_VER					// elFinderバージョン
														);
			} else if (self::SELECTED_LIB_ELFINDER == self::LIB_ELFINDER139){
				self::$libs[self::LIB_ELFINDER] = array(	'script' 	=> array(self::ELFINDER139_FILENAME, self::ELFINDER139_LANG_FILENAME),		// elFinder v2.1.39
															'css'		=> array(self::ELFINDER139_CSS, self::ELFINDER139_OPTION_CSS),	// テーマは最初に読み込む
															'version'	=> self::ELFINDER139_VER					// elFinderバージョン
														);
			}

			// WYSIWYGエディターに合わせてライブラリを設定
			self::$libs[self::LIB_WYSIWYG_EDITOR] = self::$libs[self::getWysiwygEditorLibId()];		// LIB_FCKEDITORまたはLIB_CKEDITOR

			// 使用するjQueryバージョンに合わせてファイルを追加
			self::$libs[self::LIB_JQUERY] = array(	'script' => array(self::getJQueryFilename(0)));	// jquery
			self::$libs[self::LIB_JQUERY_UI] = array(	'script' => array(self::JQUERY_UI_CORE_FILENAME));	// jquery ui
//				self::$libs[self::LIB_JQUERY_UI_PLUS] = array(	'script' => array(self::JQUERY_L_UI_PLUS_FILENAME));	// jquery ui plus(追加分)

			// jQuery UI
			self::$libs[self::LIB_JQUERY_UI_WIDGETS_ACCORDION]	= array(	'script' => array(self::JQUERY_UI_WIDGETS_ACCORDION_FILENAME));		// Widgets Accordion
			self::$libs[self::LIB_JQUERY_UI_WIDGETS_AUTOCOMPLETE] = array(	'script' => array(self::JQUERY_UI_WIDGETS_AUTOCOMPLETE_FILENAME));	// Widgets Autocomplete
			self::$libs[self::LIB_JQUERY_UI_WIDGETS_BUTTON]		= array(	'script' => array(self::JQUERY_UI_WIDGETS_BUTTON_FILENAME));		// Widgets Button
			self::$libs[self::LIB_JQUERY_UI_WIDGETS_DATEPICKER]	= array(	'script' => array(self::JQUERY_UI_WIDGETS_DATEPICKER_FILENAME,
																							self::JQUERY_UI_WIDGETS_DATEPICKER_LANG_FILENAME));	// Widgets Datepicker
			self::$libs[self::LIB_JQUERY_UI_WIDGETS_DIALOG]		= array(	'script' => array(self::JQUERY_UI_WIDGETS_DIALOG_FILENAME));		// Widgets Dialog
			self::$libs[self::LIB_JQUERY_UI_WIDGETS_PROGRESSBAR]	= array(	'script' => array(self::JQUERY_UI_WIDGETS_PROGRESSBAR_FILENAME));	// Widgets Progressbar
			self::$libs[self::LIB_JQUERY_UI_WIDGETS_SLIDER]		= array(	'script' => array(self::JQUERY_UI_WIDGETS_SLIDER_FILENAME));		// Widgets Slider
			self::$libs[self::LIB_JQUERY_UI_WIDGETS_TABS]			= array(	'script' => array(self::JQUERY_UI_WIDGETS_TABS_FILENAME));			// Widgets Tabs
			self::$libs[self::LIB_JQUERY_UI_EFFECTS]				= array(	'script' => array(self::JQUERY_UI_EFFECTS_FILENAME));				// Effects


			// スマートフォン用jQueryライブラリ
			self::$libs[self::LIB_JQUERYS] 			= array(	'script' => array(self::getJQueryFilename(10)));		// スマートフォン用jQuery
			self::$libs[self::LIB_JQUERYS_MOBILE]		= array(	'script' 	=> array(self::JQUERYS_MOBILE_FILENAME),	// JQuery Mobile
															'css'		=> array(self::JQUERYS_MOBILE_CSS));
			// 外部ライブラリ
			self::$libs[self::LIB_GOOGLEMAPS]			= array(	'script'	=> array(self::GOOGLEMAPS_FILENAME . $googleMapsParams));
		}
		return self::$libs;
	}

	/**
	 * 言語ファイル取得
	 *
	 * @param $string $lib	ライブラリID
	 * @return array		スクリプトファイル
	 */
	static function getLangScript($lib)
	{
		global $gEnvManager;

		$langId = $gEnvManager->getCurrentLanguage();
		$scriptsPath = $gEnvManager->getScriptsPath();
		$scriptFiles = array();
		$scripts = self::$libs[$lib]['script_lang'];

		if (isset($scripts)){
			for ($i = 0; $i < count($scripts); $i++){
				$scriptInfo = $scripts[$i];
				$script = str_replace('{LANG}', $langId, $scriptInfo['script'], $count);		// 言語IDを変換
				$filePath = $scriptsPath . '/' . $script;
				$defaultLang = $scriptInfo['default_lang'];
				if (file_exists($filePath)){		// ファイルが存在する場合はスクリプトファイルを追加
					$scriptFiles[] = $script;
				} else {		// ファイルが存在しないとき
					if (!empty($defaultLang)){
						$script = str_replace('{LANG}', $defaultLang, $scriptInfo['script'], $count);		// 言語IDを変換
						$filePath = $scriptsPath . '/' . $script;
						if (file_exists($filePath)) $scriptFiles[] = $script;		// ファイルが存在する場合はスクリプトファイルを追加
					}
				}
			}
		}
		return $scriptFiles;
	}
	/**
	 * 依存ライブラリ取得
	 *
	 * @param $string $lib	ライブラリID
	 * @return array		ライブラリ
	 */
	static function getDependentLib($lib)
	{
		// ##### 依存ライブラリ情報 #####
		static $dependentLib = array(
										self::LIB_ELFINDER						=> array(self::LIB_JQUERY_UI, self::LIB_JQUERY_UI_WIDGETS_DIALOG, self::LIB_JQUERY_UI_WIDGETS_SLIDER),	// 最新ではBootstrapを先に読ませない。elFinder v2.1.30以降で問題がなくなった?(2018/6/25)
										self::LIB_ELFINDER112					=> array(self::LIB_BOOTSTRAP, self::LIB_JQUERY_UI, self::LIB_JQUERY_UI_WIDGETS_DIALOG, self::LIB_JQUERY_UI_WIDGETS_SLIDER),	// 画像リサイズが実行できないバグの対応。jQuery UIよりも前にBootstrapを読ませる必要がある。(2015/1/25)
										self::LIB_ELFINDER115					=> array(self::LIB_BOOTSTRAP, self::LIB_JQUERY_UI, self::LIB_JQUERY_UI_WIDGETS_DIALOG, self::LIB_JQUERY_UI_WIDGETS_SLIDER),	// 画像リサイズが実行できないバグの対応。jQuery UIよりも前にBootstrapを読ませる必要がある。(2015/1/25)
//										self::LIB_ELFINDER121					=> array(self::LIB_BOOTSTRAP, self::LIB_JQUERY_UI, self::LIB_JQUERY_UI_WIDGETS_DIALOG, self::LIB_JQUERY_UI_WIDGETS_SLIDER),	// 画像リサイズが実行できないバグの対応。jQuery UIよりも前にBootstrapを読ませる必要がある。(2015/1/25)
										self::LIB_ELFINDER123					=> array(self::LIB_BOOTSTRAP, self::LIB_JQUERY_UI, self::LIB_JQUERY_UI_WIDGETS_DIALOG, self::LIB_JQUERY_UI_WIDGETS_SLIDER),	// 画像リサイズが実行できないバグの対応。jQuery UIよりも前にBootstrapを読ませる必要がある。(2015/1/25)
										self::LIB_ELFINDER130					=> array(self::LIB_BOOTSTRAP, self::LIB_JQUERY_UI, self::LIB_JQUERY_UI_WIDGETS_DIALOG, self::LIB_JQUERY_UI_WIDGETS_SLIDER),	// 画像リサイズが実行できないバグの対応。jQuery UIよりも前にBootstrapを読ませる必要がある。(2017/11/29)
										self::LIB_JQUERY_TIMEPICKER				=> array(self::LIB_JQUERY_UI),	// JQUERY_TIMEPICKERはJQUERY_UIを使用する
										self::LIB_JQUERY_UPLOADFILE				=> array(self::LIB_JQUERY_FORM),
//										self::LIB_JQUERY_UPLOADFILE4			=> array(self::LIB_JQUERY_FORM),
										self::LIB_JQUERY_FULLCALENDAR			=> array(self::LIB_MOMENT),
										self::LIB_JQUERY_FULLCALENDAR_GOOGLE	=> array(self::LIB_MOMENT,		self::LIB_JQUERY_FULLCALENDAR),		// MomentをFullCalendarより先に読み込む
										self::LIB_BOOTSTRAP_DATETIMEPICKER		=> array(self::LIB_MOMENT)
									);

		return $dependentLib[$lib];
	}
	/**
	 * ライブラリセットの構成ライブラリ取得
	 *
	 * @param $string $libSetId		ライブラリセットID
	 * @return array				ライブラリ
	 */
	static function getLibSet($libSetId)
	{
		static $libSet = array(	self::LIB_SET_CKEDITOR_M3_TOOLBAR	=>	array(self::LIB_ELFINDER));		// CKEditorのMagic3拡張ツールバー用
		$libs = $libSet[$libSetId];
		if (isset($libs)){
			return $libs;
		} else {
			return array();
		}
	}
	/**
	 * jQuery UIライブラリ情報取得
	 *
	 * @return array		ライブラリ情報
	 */
	static function getJQueryUiInfo()
	{
		static $jQueryUiInfo;		// jQuery UIの情報

		if (!isset($jQueryUiInfo)){
/*			if (self::$jQueryVer == 0){		// デフォルトのとき
				$jQueryUiInfo = array();
			} else {*/
			/*
				$jQueryUiInfo = array(
					self::LIB_JQUERY_UI_WIDGETS_ACCORDION		=> array(self::LIB_JQUERY_UI),		// Widgets Accordion
					self::LIB_JQUERY_UI_WIDGETS_AUTOCOMPLETE	=> array(self::LIB_JQUERY_UI),		// Widgets Autocomplete
					self::LIB_JQUERY_UI_WIDGETS_BUTTON			=> array(self::LIB_JQUERY_UI),		// Widgets Button
					self::LIB_JQUERY_UI_WIDGETS_DATEPICKER		=> array(self::LIB_JQUERY_UI),		// Widgets Datepicker
					self::LIB_JQUERY_UI_WIDGETS_DIALOG			=> array(self::LIB_JQUERY_UI,		// Widgets Dialog
																			self::LIB_JQUERY_UI_PLUS,
																			self::LIB_JQUERY_UI_WIDGETS_BUTTON),
					self::LIB_JQUERY_UI_WIDGETS_PROGRESSBAR		=> array(self::LIB_JQUERY_UI),		// Widgets Progressbar
					self::LIB_JQUERY_UI_WIDGETS_SLIDER			=> array(self::LIB_JQUERY_UI),		// Widgets Slider
					self::LIB_JQUERY_UI_WIDGETS_TABS			=> array(self::LIB_JQUERY_UI),		// Widgets Tabs
					self::LIB_JQUERY_UI_EFFECTS					=> array(self::LIB_JQUERY_UI)		// Effects
				);*/
				$defaultLib = array(self::LIB_JQUERY, self::LIB_JQUERY_UI);
				$jQueryUiInfo = array(
					self::LIB_JQUERY_UI_WIDGETS_ACCORDION		=> $defaultLib,		// Widgets Accordion
					self::LIB_JQUERY_UI_WIDGETS_AUTOCOMPLETE	=> $defaultLib,		// Widgets Autocomplete
					self::LIB_JQUERY_UI_WIDGETS_BUTTON			=> $defaultLib,		// Widgets Button
					self::LIB_JQUERY_UI_WIDGETS_DATEPICKER		=> $defaultLib,		// Widgets Datepicker
					self::LIB_JQUERY_UI_WIDGETS_DIALOG			=> array_merge($defaultLib,		// Widgets Dialog
																			array(self::LIB_JQUERY_UI_WIDGETS_BUTTON)),
					self::LIB_JQUERY_UI_WIDGETS_PROGRESSBAR		=> $defaultLib,		// Widgets Progressbar
					self::LIB_JQUERY_UI_WIDGETS_SLIDER			=> $defaultLib,		// Widgets Slider
					self::LIB_JQUERY_UI_WIDGETS_TABS			=> $defaultLib,		// Widgets Tabs
					self::LIB_JQUERY_UI_EFFECTS					=> $defaultLib		// Effects
				);
//			}
		}
		return $jQueryUiInfo;
	}
	/**
	 * jQuery バージョン情報取得
	 *
	 * @return array		バージョン情報
	 */
	static function getJQueryVersionInfo()
	{
		return self::$jQueryVersionArray;
	}
	/**
	 * WYSIWYGエディターのタイプを設定
	 *
	 * @param string $type	エディタータイプ(fckeditorまたはckeditor)
	 * @return			なし
	 */
	static function setWysiwygEditorType($type)
	{
		self::$wysiwygEditorType = $type;
	}
	/**
	 * WYSIWYGエディターライブラリIDを取得
	 *
	 * @return array		ライブラリファイル
	 */
	static function getWysiwygEditorLibId()
	{
		switch (self::$wysiwygEditorType){
			case 'fckeditor':
			default:
				return self::LIB_FCKEDITOR;
			case 'ckeditor':
				return self::LIB_CKEDITOR;
		}
	}
	/**
	 * スクリプトを取得
	 *
	 * @param string $libId			ライブラリID
	 * @return string				スクリプトファイル名またはURL
	 */
	static function getScript($libId)
	{
		$lib = self::getLib();
		$scripts = $lib[$libId]['script'];
		if (is_array($scripts) && count($scripts) == 1) $scripts = $scripts[0];
		return $scripts;
	}

	/**
	 * スクリプトライブラリ情報を作成
	 *
	 * @param string $libId			ライブラリID
	 * @return array				ライブラリ情報
	 */
	static function generateLib($libId)
	{
		global $gEnvManager;

		$lib = array();

		switch ($libId){
		case self::LIB_SOCKETIO:
			// Socket.io用のURLを作成
			$scriptUrl = $gEnvManager->getRealtimeServerUrl() . '/' . self::SOCKETIO_FILENAME;
			$lib['script'] = array($scriptUrl);

			// ライブラリ情報更新
			self::$libs[self::LIB_SOCKETIO] = $lib;
			break;
		}
		return $lib;
	}
}
?>
