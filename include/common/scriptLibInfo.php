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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
class ScriptLibInfo
{
	private static $jQueryVer = 1.8;			// デフォルトで使用するjQueryのバージョン
	private static $jQueryVersionArray = array(	'1.6'	=> 'jquery-1.6.4.min.js',// jQueryバージョン
												'1.7'	=> 'jquery-1.7.2.min.js',
												'1.8'	=> 'jquery-1.8.3.min.js',
												'1.9'	=> 'jquery-1.9.1.min.js',
												'1.10'	=> 'jquery-1.10.2.min.js',
												'2.0'	=> 'jquery-2.0.3.min.js');
	private static $wysiwygEditorType = 'fckeditor';		// WYSIWYGエディタータイプ
	
	// Javascriptライブラリ(DBでの設定値)
	// ベースライブラリ
	const LIB_JQUERY				= 'jquery';
	const LIB_JQUERY_UI				= 'jquery-ui';
//	const LIB_JQUERY_UI_PLUS		= 'jquery-ui-plus';
	const LIB_WYSIWYG_EDITOR		= 'wysiwyg_editor';			// LIB_FCKEDITORまたはLIB_CKEDITOR
	const LIB_FCKEDITOR				= 'fckeditor';
	const LIB_CKEDITOR				= 'ckeditor';
	const LIB_MD5					= 'md5';
	const LIB_SWFOBJECT				= 'swfobject';
	const LIB_BOOTSTRAP				= 'bootstrap';
	
	// スマートフォン用jQueryライブラリ
	const LIB_JQUERYS				= 'jquerys';
	const LIB_JQUERYS_MOBILE		= 'jquery.mobile';
		
	// jQueryプラグイン
	const LIB_JQUERY_EASING			= 'jquery.easing';
	const LIB_JQUERY_JCAROUSEL		= 'jquery.jcarousel';
	const LIB_JQUERY_THICKBOX		= 'jquery.thickbox';
//	const LIB_JQUERY_JSHOTKEYS		= 'jquery.jshotkeys';
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
	const LIB_JQUERY_CALCULATION	= 'jquery.calculation';
	const LIB_JQUERY_JQPLOT			= 'jquery.jqplot';
	const LIB_JQUERY_YOUTUBEPLAYER	= 'jquery.youtubeplayer';
	const LIB_JQUERY_JSTREE			= 'jquery.jstree';
	const LIB_JQUERY_IFRAME			= 'jquery.iframe';
	const LIB_JQUERY_RATY			= 'jquery.raty';
	const LIB_JQUERY_MOUSEWHEEL		= 'jquery.mousewheel';
	const LIB_JQUERY_CLOUDCAROUSEL	= 'jquery.cloudcarousel';
//	const LIB_JQUERY_JQEASYPANEL	= 'jquery.jqeasypanel';
	const LIB_JQUERY_SCROLLTO		= 'jquery.scrollto';
	const LIB_JQUERY_FULLCALENDAR	= 'jquery.fullcalendar';
	
	// CodeMirror
	const LIB_CODEMIRROR_JAVASCRIPT	= 'codemirror.javascript';		// CodeMirror Javascript
	
	// jQueryプラグインバージョン
	const JQUERY_JCAROUSEL_VER		= '0.2.8';
	const JQUERY_THICKBOX_VER		= '3.1';
//	const JQUERY_PRETTYPHOTO_VER	= '2.5.6';
	const JQUERY_PRETTYPHOTO_VER	= '3.1.4';
	const JQUERY_QTIP_VER			= '1.0';
	const JQUERY_CALCULATION_VER	= '0.4.07';
	//const JQUERY_JQPLOT_VER			= '0.9.7';
//const JQUERY_JQPLOT_VER			= '1.0.4r1120';
	const JQUERY_JQPLOT_VER			= '1.0.7';
	const JQUERY_JSTREE_VER			= '1.0-rc3';
	const JQUERY_IFRAME_VER			= '1.5.0';
	const JQUERY_RATY_VER			= '1.4.3';
	const JQUERY_MOUSEWHEEL_VER		= '3.0.6';
	const JQUERY_CLOUDCAROUSEL_VER	= '1.0.5';
//	const JQUERY_JQEASYPANEL_VER	= '1.0';
	const JQUERY_SCROLLTO_VER		= '1.4.3.1';
	const JQUERY_FULLCALENDAR_VER	= '1.6.2';
	// その他ライブラリバージョン
	const CODEMIRROR_VER			= '3.1';
	
	// ライブラリディレクトリ
	//const JQUERY_JQPLOT_DIR			= 'jquery/jqplot';
//	const JQUERY_JQPLOT_DIR			= 'jquery/jqplot1.0';
	const JQUERY_JQPLOT_DIR			= 'jquery/jqplot1.0.7';
	
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

	// Javascriptライブラリ用のファイル
	const JQUERY_EASING_FILENAME		= 'jquery/jquery.easing.1.3.js';
	//const JQUERY_JCAROUSEL_FILENAME		= 'jquery/jquery.jcarousel.0.2.4.min.js';		// jQuery1.4.2対応版
	const JQUERY_JCAROUSEL_FILENAME		= 'jquery/jquery.jcarousel.0.2.8.min.js';		// jQuery1.4.2対応版
	const JQUERY_CONTEXTMENU_FILENAME	= 'jquery/jquery.contextmenu.r2.packed.js';
	const JQUERY_THICKBOX_FILENAME		= 'jquery/thickbox3.1.js';
	const JQUERY_THICKBOX_CSS			= 'jquery/thickbox.css';
//	const JQUERY_JSHOTKEYS_FILENAME		= 'jquery/jquery.hotkeys.js';
	const JQUERY_CYCLE_FILENAME			= 'jquery/jquery.cycle.all.pack.js';
	const JQUERY_CODEPRESS_FILENAME		= 'jquery/jquery.Codepress.js';
	const JQUERY_CLUETIP_FILENAME		= 'jquery/jquery.cluetip.js';
	const JQUERY_CLUETIP_CSS			= 'jquery/jquery.cluetip.css';
	const JQUERY_SIMPLETREE_FILENAME	= 'jquery/jquery.simple.tree.js';
	const JQUERY_SIMPLETREE_CSS			= 'jquery/simple.tree.css';
	const JQUERY_BGIFRAME_FILENAME		= 'jquery/jquery.bgiframe.min.js';
	const JQUERY_HOVERINTENT_FILENAME	= 'jquery/jquery.hoverIntent.min.js';
	const JQUERY_TABLEDND_FILENAME		= 'jquery/jquery.tablednd_0_5.js';
	const JQUERY_TABLEDND_CSS			= 'jquery/jquery.tablednd.css';
	const JQUERY_SIMPLEMODAL_FILENAME	= 'jquery/jquery.simplemodal-1.3.5.min.js';
	const JQUERY_COOKIE_FILENAME		= 'jquery/jquery.cookie.js';
//	const JQUERY_FORMAT_FILENAME		= 'jquery/jquery.format.1.03.js';
	const JQUERY_FORMAT_FILENAME		= 'jquery/jquery.format.1.05.js';
	const JQUERY_FORMTIPS_FILENAME		= 'jquery/jquery.formtips.1.2.packed.js';
	const JQUERY_FACEBOX_FILENAME		= 'jquery/facebox.js';
	const JQUERY_FACEBOX_CSS			= 'jquery/facebox.css';
	const JQUERY_CURVYCORNERS_FILENAME	= 'jquery/jquery.curvycorners.packed.js';

	const JQUERY_PRETTYPHOTO_FILENAME	= 'jquery/prettyPhoto3.1.4/js/jquery.prettyPhoto.js';
	const JQUERY_PRETTYPHOTO_CSS		= 'jquery/prettyPhoto3.1.4/css/prettyPhoto.css';
	const JQUERY_QTIP_FILENAME			= 'jquery/jquery.qtip-1.0.min.js';
	const JQUERY_CALCULATION_FILENAME	= 'jquery/jquery.calculation.js';
	//const JQUERY_JQPLOT_FILENAME		= 'jquery/jqplot/jquery.jqplot.min.js';
//const JQUERY_JQPLOT_FILENAME		= 'jquery/jqplot1.0/jquery.jqplot.min.js';
const JQUERY_JQPLOT_FILENAME		= 'jquery/jqplot1.0.7/jquery.jqplot.min.js';
//	const JQUERY_JQPLOT_EX_FILENAME		= 'jquery/jqplot/excanvas.min.js';
	//const JQUERY_JQPLOT_CSS				= 'jquery/jqplot/jquery.jqplot.css';
//const JQUERY_JQPLOT_CSS				= 'jquery/jqplot1.0/jquery.jqplot.min.css';
const JQUERY_JQPLOT_CSS				= 'jquery/jqplot1.0.7/jquery.jqplot.min.css';
	const JQUERY_YOUTUBEPLAYER_FILENAME	= 'jquery/jquery.youtube.player.js';
	const JQUERY_JSTREE_FILENAME		= 'jquery/jstree/jquery.jstree.js';
//	const JQUERY_IFRAME_FILENAME		= 'jquery/jquery.iframe-auto-height.plugin.1.5.0.min.js';
//	const JQUERY_IFRAME_FILENAME		= 'jquery/jquery.iframe-auto-height.plugin.1.7.1.min.js';
	const JQUERY_IFRAME_FILENAME		= 'jquery/jquery.iframe-auto-height.plugin.1.9.1.min.js';
	const JQUERY_RATY_FILENAME			= 'jquery/raty/jquery.raty.js';
	const JQUERY_MOUSEWHEEL_FILENAME	= 'jquery/jquery.mousewheel.js';
	const JQUERY_CLOUDCAROUSEL_FILENAME	= 'jquery/cloud-carousel.1.0.5.min.js';
//	const JQUERY_JQEASYPANEL_FILENAME	= 'jquery/jquery.jqEasyPanel.js';
//	const JQUERY_JQEASYPANEL_CSS		= 'jquery/jqeasypanel.css';
	const JQUERY_SCROLLTO_FILENAME		= 'jquery/jquery.scrollTo-1.4.3.1-min.js';
//	const JQUERY_FULLCALENDAR_FILENAME	= 'jquery/fullcalendar-1.6.2/fullcalendar.min.js';
	const JQUERY_FULLCALENDAR_FILENAME	= 'jquery/fullcalendar-1.6.3/fullcalendar.js';
	const JQUERY_FULLCALENDAR_CSS		= 'jquery/fullcalendar-1.6.3/fullcalendar.css';
	
	// ライブラリの公式サイトのURL
	const JQUERY_JCAROUSEL_URL			= 'http://sorgalla.com/projects/jcarousel/';
	const JQUERY_THICKBOX_URL			= 'http://thickbox.net/';
	const JQUERY_CLUETIP_URL			= 'http://plugins.learningjquery.com/cluetip/';
	const JQUERY_FACEBOX_URL			= 'http://defunkt.github.com/facebox/';
	const JQUERY_CURVYCORNERS_URL		= 'http://code.google.com/p/jquerycurvycorners/';
	const JQUERY_PRETTYPHOTO_URL		= 'http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/';
	const JQUERY_QTIP_URL				= 'http://craigsworks.com/projects/qtip/';
	const JQUERY_CALCULATION_URL		= 'http://www.pengoworks.com/workshop/jquery/calculation/calculation.plugin.htm';
	const JQUERY_JQPLOT_URL				= 'http://www.jqplot.com/';
	const JQUERY_JSTREE_URL				= 'http://www.jstree.com/';
	const JQUERY_IFRAME_URL				= 'https://github.com/house9/jquery-iframe-auto-height';
	const JQUERY_RATY_URL				= 'http://www.wbotelhos.com/raty/';
	const JQUERY_MOUSEWHEEL_URL			= 'https://github.com/brandonaaron/jquery-mousewheel';
	const JQUERY_CLOUDCAROUSEL_URL		= 'http://www.professorcloud.com/mainsite/carousel.htm';
//	const JQUERY_JQEASYPANEL_URL		= 'http://www.jqeasy.com/';
	const JQUERY_SCROLLTO_URL			= 'http://flesler.blogspot.jp/2007/10/jqueryscrollto.html';
	const JQUERY_FULLCALENDAR_URL		= 'http://arshaw.com/fullcalendar/';
	const CODEMIRROR_URL				= 'http://codemirror.net/';		// CodeMirror
	
	// ディレクトリ名
	const FCKEDITOR_DIRNAME				= 'fckeditor2.6.6';				// FCKEditor
	
	// ファイル名
	//const JQUERY_L_FILENAME			= 'jquery-1.6.4.min.js';					// JQuery最新版(v1.6.4)		// 2012/10/13～
	//const JQUERY_UI_CORE_FILENAME	= 'jquery-ui-core-1.8.2.min.js';			// JQuery UI Core (Core)
	//const JQUERY_L_UI_PLUS_FILENAME	= 'jquery-ui-plus-1.8.2.min.js';			// JQuery UI Plus (Interactions)
//	const JQUERY_UI_CORE_FILENAME	= 'jquery-ui-core-1.10.1.min.js';			// JQuery UI Core (Core,Interactions)
	const JQUERY_UI_CORE_FILENAME	= 'jquery-ui-core-1.9.2.min.js';			// JQuery UI Core (Core,Interactions)
	const FCKEDITOR_FILENAME		= 'fckeditor2.6.6/fckeditor.js';			// FCKEditor
	const CKEDITOR_FILENAME			= 'ckeditor4.2.0/ckeditor.js';				// CKEditor
	const MD5_FILENAME				= 'md5.js';									// MD5
	const SWFOBJECT_FILENAME		= 'swfobject/swfobject.js';					// swfobject
//	const BOOTSTRAP_FILENAME		= 'bootstrap/bootstrap2.3.min.js';			// bootstrap
	const BOOTSTRAP_FILENAME		= 'bootstrap/bootstrap3.0rc2.js';			// bootstrap
//	const BOOTSTRAP_CSS				= 'bootstrap/bootstrap2.3.css';
	const BOOTSTRAP_CSS				= 'bootstrap/bootstrap3.0rc2.css';
	const BOOTSTRAP_OPTION_CSS		= 'bootstrap/bootstrap-box2.3.css';
	
	const CODEMIRROR_FILENAME				= 'codemirror-3.1/lib/codemirror.js';				// CodeMirror
	const CODEMIRROR_CSS					= 'codemirror-3.1/lib/codemirror.css';				// CodeMirror
	const CODEMIRROR_JAVASCRIPT_FILENAME	= 'codemirror-3.1/mode/javascript/javascript.js';	// CodeMirror Javascript
	
	// jQuery UI用ファイル
/*	const JQUERY_UI_WIDGETS_ACCORDION_FILENAME		= 'jquery/ui/1.8.2/jquery.ui.accordion.min.js';		// Widgets Accordion
	const JQUERY_UI_WIDGETS_AUTOCOMPLETE_FILENAME	= 'jquery/ui/1.8.2/jquery.ui.autocomplete.min.js';		// Widgets Autocomplete
	const JQUERY_UI_WIDGETS_BUTTON_FILENAME			= 'jquery/ui/1.8.2/jquery.ui.button.min.js';			// Widgets Button
	const JQUERY_UI_WIDGETS_DATEPICKER_FILENAME		= 'jquery/ui/1.8.2/jquery.ui.datepicker.min.js';		// Widgets Datepicker
	const JQUERY_UI_WIDGETS_DIALOG_FILENAME			= 'jquery/ui/1.8.2/jquery.ui.dialog.min.js';			// Widgets Dialog
	const JQUERY_UI_WIDGETS_PROGRESSBAR_FILENAME	= 'jquery/ui/1.8.2/jquery.ui.progressbar.min.js';		// Widgets Progressbar
	const JQUERY_UI_WIDGETS_SLIDER_FILENAME			= 'jquery/ui/1.8.2/jquery.ui.slider.min.js';			// Widgets Slider
	const JQUERY_UI_WIDGETS_TABS_FILENAME			= 'jquery/ui/1.8.2/jquery.ui.tabs.min.js';				// Widgets Tabs
	const JQUERY_UI_EFFECTS_FILENAME				= 'jquery/ui/1.8.2/jquery.effects.min.js';					// Effects
	*/
	// jQuery UI 1.10.1
/*	const JQUERY_UI_WIDGETS_ACCORDION_FILENAME		= 'jquery/ui/1.10.1/jquery.ui.accordion.min.js';		// Widgets Accordion
	const JQUERY_UI_WIDGETS_AUTOCOMPLETE_FILENAME	= 'jquery/ui/1.10.1/jquery.ui.autocomplete.min.js';		// Widgets Autocomplete
	const JQUERY_UI_WIDGETS_BUTTON_FILENAME			= 'jquery/ui/1.10.1/jquery.ui.button.min.js';			// Widgets Button
	const JQUERY_UI_WIDGETS_DATEPICKER_FILENAME		= 'jquery/ui/1.10.1/jquery.ui.datepicker.min.js';		// Widgets Datepicker
	const JQUERY_UI_WIDGETS_DIALOG_FILENAME			= 'jquery/ui/1.10.1/jquery.ui.dialog.min.js';			// Widgets Dialog
	const JQUERY_UI_WIDGETS_PROGRESSBAR_FILENAME	= 'jquery/ui/1.10.1/jquery.ui.progressbar.min.js';		// Widgets Progressbar
	const JQUERY_UI_WIDGETS_SLIDER_FILENAME			= 'jquery/ui/1.10.1/jquery.ui.slider.min.js';			// Widgets Slider
	const JQUERY_UI_WIDGETS_TABS_FILENAME			= 'jquery/ui/1.10.1/jquery.ui.tabs.min.js';				// Widgets Tabs
	const JQUERY_UI_EFFECTS_FILENAME				= 'jquery/ui/1.10.1/jquery.effects.min.js';					// Effects
	*/
	
	// jQuery UI 1.9.2
	const JQUERY_UI_WIDGETS_ACCORDION_FILENAME		= 'jquery/ui/1.9.2/jquery.ui.accordion.min.js';		// Widgets Accordion
	const JQUERY_UI_WIDGETS_AUTOCOMPLETE_FILENAME	= 'jquery/ui/1.9.2/jquery.ui.autocomplete.min.js';		// Widgets Autocomplete
	const JQUERY_UI_WIDGETS_BUTTON_FILENAME			= 'jquery/ui/1.9.2/jquery.ui.button.min.js';			// Widgets Button
	const JQUERY_UI_WIDGETS_DATEPICKER_FILENAME		= 'jquery/ui/1.9.2/jquery.ui.datepicker.min.js';		// Widgets Datepicker
	const JQUERY_UI_WIDGETS_DIALOG_FILENAME			= 'jquery/ui/1.9.2/jquery.ui.dialog.min.js';			// Widgets Dialog
	const JQUERY_UI_WIDGETS_PROGRESSBAR_FILENAME	= 'jquery/ui/1.9.2/jquery.ui.progressbar.min.js';		// Widgets Progressbar
	const JQUERY_UI_WIDGETS_SLIDER_FILENAME			= 'jquery/ui/1.9.2/jquery.ui.slider.min.js';			// Widgets Slider
	const JQUERY_UI_WIDGETS_TABS_FILENAME			= 'jquery/ui/1.9.2/jquery.ui.tabs.min.js';				// Widgets Tabs
	const JQUERY_UI_EFFECTS_FILENAME				= 'jquery/ui/1.9.2/jquery.effects.min.js';					// Effects
	
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
	const GOOGLEMAPS_FILENAME		= 'http://maps.google.com/maps/api/js?sensor=true';
	
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
		self::$jQueryVer = $version;
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
			$filename = self::$jQueryVersionArray[self::$jQueryVer];
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
	 * ライブラリ情報取得
	 *
	 * @return array		ライブラリ情報
	 */
	static function getLib()
	{
		$libs = array(self::LIB_MD5					=>	array(	'script' 	=> array(self::MD5_FILENAME)),			// MD5
					self::LIB_FCKEDITOR				=>	array(	'script' 	=> array(self::FCKEDITOR_FILENAME)),	// FCKEditor
					self::LIB_CKEDITOR				=>	array(	'script' 	=> array(self::CKEDITOR_FILENAME)),		// CKEditor
					self::LIB_SWFOBJECT				=>	array(	'script' 	=> array(self::SWFOBJECT_FILENAME)),	// swfobject
					self::LIB_BOOTSTRAP				=>	array(	'script' 	=> array(self::BOOTSTRAP_FILENAME),		// bootstrap
																'css'		=> array(self::BOOTSTRAP_CSS,
																					self::BOOTSTRAP_OPTION_CSS)),
					// jQueryライブラリ
					self::LIB_JQUERY_EASING			=>	array(	'script' 	=> array(self::JQUERY_EASING_FILENAME)),		// jquery.easing用のファイル
					self::LIB_JQUERY_JCAROUSEL		=>	array(	'script' 	=> array(self::JQUERY_JCAROUSEL_FILENAME),
																'url'		=> self::JQUERY_JCAROUSEL_URL,
																'version'	=> self::JQUERY_JCAROUSEL_VER),			// jquery.jcarousel用のファイル
					self::LIB_JQUERY_THICKBOX		=>	array(	'script' 	=> array(self::JQUERY_THICKBOX_FILENAME),// jquery.thickbox用のファイル
																'css'		=> array(self::JQUERY_THICKBOX_CSS),
																'url'		=> self::JQUERY_THICKBOX_URL,
																'version'	=> self::JQUERY_THICKBOX_VER),
//					self::LIB_JQUERY_JSHOTKEYS		=>	array(	'script' 	=> array(self::JQUERY_JSHOTKEYS_FILENAME)),	// jquery.js-hotkeys用のファイル
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
					self::LIB_JQUERY_COOKIE			=>	array(	'script' 	=> array(self::JQUERY_COOKIE_FILENAME)),
					self::LIB_JQUERY_FORMAT			=>	array(	'script' 	=> array(self::JQUERY_FORMAT_FILENAME)),
					self::LIB_JQUERY_FORMTIPS		=>	array(	'script' 	=> array(self::JQUERY_FORMTIPS_FILENAME)),
					self::LIB_JQUERY_FACEBOX		=>	array(	'script' 	=> array(self::JQUERY_FACEBOX_FILENAME),	// jquery.facebox用のファイル
																'css'		=> array(self::JQUERY_FACEBOX_CSS),
																'url'		=> self::JQUERY_FACEBOX_URL),
					self::LIB_JQUERY_CURVYCORNERS	=> array(	'script'	=> array(self::JQUERY_CURVYCORNERS_FILENAME),
																'url'		=> self::JQUERY_CURVYCORNERS_URL),
					self::LIB_JQUERY_PRETTYPHOTO	=>	array(	'script' 	=> array(self::JQUERY_PRETTYPHOTO_FILENAME),	// jquery.prettyPhoto用のファイル
																'css'		=> array(self::JQUERY_PRETTYPHOTO_CSS),
																'url'		=> self::JQUERY_PRETTYPHOTO_URL,
																'version'	=> self::JQUERY_PRETTYPHOTO_VER),
					self::LIB_JQUERY_QTIP			=>	array(	'script' 	=> array(self::JQUERY_QTIP_FILENAME),	// jquery.qtip用のファイル
																'url'		=> self::JQUERY_QTIP_URL,
																'version'	=> self::JQUERY_QTIP_VER),
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
					self::LIB_JQUERY_IFRAME			=>	array(	'script'	=> array(self::JQUERY_IFRAME_FILENAME),		// jquery.iframe-auto-height用のファイル
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
																'version'	=> self::JQUERY_FULLCALENDAR_VER),
																
/*					self::LIB_JQUERY_JQEASYPANEL	=>	array(	'script' 	=> array(self::JQUERY_JQEASYPANEL_FILENAME),	// jquery.jqEasyPanel用のファイル
																'css'		=> array(self::JQUERY_JQEASYPANEL_CSS),
																'url'		=> self::JQUERY_JQEASYPANEL_URL,
																'version'	=> self::JQUERY_JQEASYPANEL_VER)*/
					// その他ライブラリ
					self::LIB_CODEMIRROR_JAVASCRIPT		=>	array(	'script' 	=> array(self::CODEMIRROR_FILENAME, self::CODEMIRROR_JAVASCRIPT_FILENAME),	// CodeMirror用のファイル
																	'css'		=> array(self::CODEMIRROR_CSS),
																	'url'		=> self::CODEMIRROR_URL,
																	'version'	=> self::CODEMIRROR_VER)
																);
																
		// WYSIWYGエディターに合わせてライブラリを設定
		$libs[self::LIB_WYSIWYG_EDITOR] = $libs[self::getWysiwygEditorLibId()];		// LIB_FCKEDITORまたはLIB_CKEDITOR
					
		// 使用するjQueryバージョンに合わせてファイルを追加
/*		switch (self::$jQueryVer){
			case 0:		// デフォルトのとき
				$libs[self::LIB_JQUERY] = array(	'script' => array(self::getJQueryFilename(0)));	// jquery
				$libs[self::LIB_JQUERY_UI] = array(	'script' => array(self::JQUERY_UI_CORE_FILENAME));	// jquery ui
				$libs[self::LIB_JQUERY_UI_PLUS] = array(	'script' => array(self::JQUERY_UI_PLUS_FILENAME));	// jquery ui plus(追加分)
				break;
			case 1:		// 最新のとき*/
				$libs[self::LIB_JQUERY] = array(	'script' => array(self::getJQueryFilename(0)));	// jquery
				$libs[self::LIB_JQUERY_UI] = array(	'script' => array(self::JQUERY_UI_CORE_FILENAME));	// jquery ui
//				$libs[self::LIB_JQUERY_UI_PLUS] = array(	'script' => array(self::JQUERY_L_UI_PLUS_FILENAME));	// jquery ui plus(追加分)
				
				// jQuery UI
				$libs[self::LIB_JQUERY_UI_WIDGETS_ACCORDION]	= array(	'script' => array(self::JQUERY_UI_WIDGETS_ACCORDION_FILENAME));		// Widgets Accordion
				$libs[self::LIB_JQUERY_UI_WIDGETS_AUTOCOMPLETE] = array(	'script' => array(self::JQUERY_UI_WIDGETS_AUTOCOMPLETE_FILENAME));	// Widgets Autocomplete
				$libs[self::LIB_JQUERY_UI_WIDGETS_BUTTON]		= array(	'script' => array(self::JQUERY_UI_WIDGETS_BUTTON_FILENAME));		// Widgets Button
				$libs[self::LIB_JQUERY_UI_WIDGETS_DATEPICKER]	= array(	'script' => array(self::JQUERY_UI_WIDGETS_DATEPICKER_FILENAME));	// Widgets Datepicker
				$libs[self::LIB_JQUERY_UI_WIDGETS_DIALOG]		= array(	'script' => array(self::JQUERY_UI_WIDGETS_DIALOG_FILENAME));		// Widgets Dialog
				$libs[self::LIB_JQUERY_UI_WIDGETS_PROGRESSBAR]	= array(	'script' => array(self::JQUERY_UI_WIDGETS_PROGRESSBAR_FILENAME));	// Widgets Progressbar
				$libs[self::LIB_JQUERY_UI_WIDGETS_SLIDER]		= array(	'script' => array(self::JQUERY_UI_WIDGETS_SLIDER_FILENAME));		// Widgets Slider
				$libs[self::LIB_JQUERY_UI_WIDGETS_TABS]			= array(	'script' => array(self::JQUERY_UI_WIDGETS_TABS_FILENAME));			// Widgets Tabs
				$libs[self::LIB_JQUERY_UI_EFFECTS]				= array(	'script' => array(self::JQUERY_UI_EFFECTS_FILENAME));				// Effects
//				break;
//		}
		
		// スマートフォン用jQueryライブラリ
		$libs[self::LIB_JQUERYS] 			= array(	'script' => array(self::getJQueryFilename(10)));		// スマートフォン用jQuery
		$libs[self::LIB_JQUERYS_MOBILE]		= array(	'script' 	=> array(self::JQUERYS_MOBILE_FILENAME),	// JQuery Mobile
														'css'		=> array(self::JQUERYS_MOBILE_CSS));
		// 外部ライブラリ
		$libs[self::LIB_GOOGLEMAPS]			= array(	'script'	=> array(self::GOOGLEMAPS_FILENAME));
		return $libs;
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
}
?>
