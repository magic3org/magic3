/**
 * Magic3標準追加用JavaScriptライブラリ
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m3plus1.6.1.js 6010 2013-05-19 11:38:31Z fishbone $
 * @link       http://www.magic3.org
 */
// 親ウィンドウを更新
function m3UpdateParentWindow()
{
	var href = 	window.opener.location.href.split('#');
	window.opener.location.href = href[0];
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
		config['customConfig'] = M3_ROOT_URL + '/scripts/m3/ckconfig.js';
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
