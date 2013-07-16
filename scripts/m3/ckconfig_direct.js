/*
 * CKEditor(WYSIWYGエディター)の設定
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ckconfig_direct.js 5950 2013-04-19 13:10:35Z fishbone $
 * @link       http://www.magic3.org
 */


CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config
	config.language = 'ja';
	config.enterMode = CKEDITOR.ENTER_BR; // 改行をbrに変更
	config.shiftEnterMode = CKEDITOR.ENTER_DIV;
	config.autoParagraph = false;
	config.fillEmptyBlocks = false;		// 「&nbsp;」が自動的に入るのを防ぐ
	config.toolbarCanCollapse = true;		// ツールバー表示制御
	
	// ツールバーの設定
	config.toolbar = [
		{ name: 'others', items: [ 'LinkInfo' ] }
	];
	
	// 追加プラグインの設定
	config.extraPlugins = 'linkinfo';
//	config.autoGrow_maxHeight = 800;		// 指定サイズまで入力に合わせて拡大
	
};
/*CKEDITOR.on('dialogDefinition', function(ev){
	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;
	var dialog = dialogDefinition.dialog;
	
	if (dialogName == 'image' || dialogName == 'flash'){
		dialogDefinition.removeContents('Upload');	// 「アップロード」タブ削除
	}
});*/
