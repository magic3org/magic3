/*
 * デフォルトHTML編集エリアの設定
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: fckconfig.js 5494 2012-12-30 03:23:17Z fishbone $
 * @link       http://www.magic3.org
 */
//FCKConfig.EnterMode = 'div' ;			// p | div | br
FCKConfig.EnterMode = 'br' ;			// p | div | br		// P,DIVタグで囲まない
FCKConfig.ShiftEnterMode = 'br' ;	// p | div | br

FCKConfig.LinkBrowser = false ;
FCKConfig.LinkDlgHideTarget		= false ;
FCKConfig.LinkDlgHideAdvanced	= true ;
FCKConfig.LinkUpload = false ;

FCKConfig.ImageBrowser = true ;
FCKConfig.ImageDlgHideLink		= true ;
FCKConfig.ImageDlgHideAdvanced	= true ;
FCKConfig.ImageUpload = false ;

FCKConfig.FlashBrowser = true ;
FCKConfig.FlashDlgHideAdvanced	= false ;
FCKConfig.FlashUpload = false ;

FCKConfig.TemplateReplaceAll = false ;
//FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/silver/';
FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/office2003/';

// Googleマップ用設定
if (window.parent) FCKConfig.GoogleMaps_Key = window.parent.M3_GOOGLE_MAPS_KEY;

// プラグインの追加
FCKConfig.Plugins.Add('FileBrowser_Thumbnail', 'en,ja');// ファイルブラウザ
//FCKConfig.Plugins.Add('Blink', 'en,ja');// 携帯用blinkタグ
FCKConfig.Plugins.Add('Blink2', 'en,ja');// 携帯用blinkタグ
FCKConfig.Plugins.Add('Marquee', 'en,ja');// 携帯用marqueeタグ
//FCKConfig.Plugins.Add('Marquee2', 'en,ja');// 携帯用marqueeタグ
FCKConfig.Plugins.Add('Emoji', 'en,ja');// 携帯用絵文字タグ
FCKConfig.Plugins.Add('MobileLink', 'en,ja');// 携帯用リンクタグ
FCKConfig.Plugins.Add('googlemaps', 'en,ja');// Googleマップ
FCKConfig.Plugins.Add('YouTube', 'en,ja');// YouTube

// ツールバーの設定
// PC用
FCKConfig.ToolbarSets["M3Default"] = [
	['FitWindow','Source','ShowBlocks','Preview'],
	['NewPage','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent','Blockquote'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink','Anchor'],
	['Image','Flash','YouTube','googlemaps','Table','Rule','Smiley','SpecialChar','PageBreak'],
	'/',
	['Style','FontFormat','FontName','FontSize'],
	['TextColor','BGColor'],
	['About']
];
// 携帯用
FCKConfig.ToolbarSets["M3MobileDefault"] = [
	['FitWindow','Source','ShowBlocks','Preview'],
	['NewPage','Templates'],
	['Cut','Copy','Paste','PasteText','PasteWord'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript','-','Blink2','Marquee'],
	['OrderedList','UnorderedList','-','Outdent','Indent','Blockquote'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['MobileLink','Unlink','Anchor'],
	['Image','Flash','Table','Rule','Emoji','SpecialChar','PageBreak'],
	'/',
	['Style','FontFormat','FontName','FontSize'],
	['TextColor','BGColor'],
	['About']
];
