/*
 * CKEditor(WYSIWYGエディター)の設定
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
CKEDITOR.stylesSet.add('default', []);

CKEDITOR.editorConfig = function(config){
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
	config.stylesCombo_stylesSet = 'default';
	
	// 追加プラグインの設定
	config.extraPlugins = 'linkinfo';
};
CKEDITOR.on('dialogDefinition', function(e){
	var dialogName = e.data.name;
	var dialogDefinition = e.data.definition;
	dialogDefinition.onShow = function(){
		// 呼び出し元がiframeの場合は、ダイアログの表示位置を修正
		if (window.parent != window.self){
			this.move(this.getPosition().x, 0);
		}
	}
})


