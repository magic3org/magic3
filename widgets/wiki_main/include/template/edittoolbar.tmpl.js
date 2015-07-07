/*
 * Javascript生成用テンプレート
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
<patTemplate:tmpl name="_tmpl">
// ファイルブラウザ起動
function selectImage()
{
	m3OpenImageFileBrowser(SetUrl_image);
}
function selectFile()
{
	m3OpenFileBrowser(SetUrl_file);
}
// ファイルブラウザからの設定用
function SetUrl_image(url)
{
	$('#{TEXTAREA}').insertAtCaret('#ref(' + url + ')');
}
function SetUrl_file(url)
{
	$('#{TEXTAREA}').insertAtCaret('#ref(' + url + ')');
}
$(function(){
	// CKEditorプラグイン直接実行
	m3LoadCKTools();
	
	// ボタンイベント
	$("#{BUTTON_IMAGE}").click(function (){
		selectImage();
	});
	$("#{BUTTON_FILE}").click(function (){
		selectFile();
	});
});
</patTemplate:tmpl>
