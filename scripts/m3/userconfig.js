/*
 * 一般ユーザ向けHTML編集エリアの設定
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: userconfig.js 3726 2010-10-24 09:15:09Z fishbone $
 * @link       http://www.magic3.org
 */
FCKConfig.EnterMode = 'div' ;			// p | div | br
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
if (window.parent) FCKConfig.GoogleMaps_Key = window.parent.M3_GOOGLEMAPS_KEY;

// プラグインの追加
FCKConfig.Plugins.Add('FileBrowser_Thumbnail', 'en,ja');// ファイルブラウザ
FCKConfig.Plugins.Add('Blink', 'en,ja');// 携帯用blinkタグ
FCKConfig.Plugins.Add('Marquee', 'en,ja');// 携帯用marqueeタグ
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
