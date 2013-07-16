/*
 * FCKBlink2Command Class: represents the "Blink2" command.
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: fckplugin.js 3736 2010-10-26 06:29:51Z fishbone $
 * @link       http://www.magic3.org
 */
var FCKBlink2Command = function(){};
FCKBlink2Command.prototype =
{
	Execute : function()
	{
		// タグのスタイル
		var styleDesc = {
			'Blink' : {
				Element : 'span',
				Styles : {
					'text-decoration' : 'blink'
				}
			}
		};
		
		// 選択がなければ終了
		if (!this._IsSelectedString()) return;
		
		FCKUndo.SaveUndoStep() ;

		var baseStyle;
		baseStyle = new FCKStyle(styleDesc['Blink']);
		FCK.Styles.ApplyStyle(baseStyle);
		
		FCKUndo.SaveUndoStep();

		FCK.Focus() ;
		FCK.Events.FireEvent('OnSelectionChange');
	},
	
	GetState : function()
	{
		// ソース編集モードのときは使用不可
		if (FCK.EditMode != FCK_EDITMODE_WYSIWYG) return FCK_TRISTATE_DISABLED;
		
		if (!this._IsSelectedString()) return FCK_TRISTATE_DISABLED;
		return FCK_TRISTATE_OFF;
	},
	
	_IsSelectedString : function()
	{
		if (FCKBrowserInfo.IsIE){
			return true;
			var selObj = FCK.ToolbarSet.CurrentInstance.EditorDocument.selection;
			if (selObj.type == 'Text'){
				var rangeObj = selObj.createRange();
				if (rangeObj.htmlText.length > 0) return true;
			}
		} else {
			if (FCKSelection.GetType() == 'Text'){
				var selObj = FCK.EditorWindow.getSelection();
				if (selObj && selObj.anchorOffset < selObj.focusOffset) return true;
			}
		}
		return false;
	}
}

// コマンドを登録
FCKCommands.RegisterCommand('Blink2', new FCKBlink2Command());

// ツールバーボタン作成
var toolbarItem = new FCKToolbarButton('Blink2'/*commandName*/, FCKLang.Blink2Btn/*label*/, FCKLang.Blink2Btn/*tooltip*/,
									  null/*style*/, false/*sourceView*/, true/*contextSensitive*//*icon*/);
toolbarItem.IconPath = FCKPlugins.Items['Blink2'].Path + 'icon.gif';
FCKToolbarItems.RegisterItem('Blink2', toolbarItem);
