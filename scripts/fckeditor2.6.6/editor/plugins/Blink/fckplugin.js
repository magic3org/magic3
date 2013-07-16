/*
 * FCKBlinkCommand Class: represents the "Blink" command.
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: fckplugin.js 3480 2010-08-16 09:27:01Z fishbone $
 * @link       http://www.magic3.org
 */
var FCKBlinkCommand = function()
{}

FCKBlinkCommand.prototype =
{
	Name : 'Blink',

	Execute : function()
	{
		// タグのスタイル
		var styleDesc = {
			'Blink' : {
				Element : 'blink'
			},
			'IEBlink' : {
				Element : 'blink',
				Styles : {
					'font' : 'italic bold',
					'text-decoration' : 'blink'
				}
			}
		};
		// エラーチェック
		if (!this._IsSelectedString()) return;
		
		// Undo用データ保存
		FCKUndo.SaveUndoStep();

		var parentObj = FCKSelection.GetParentElement();
		if (parentObj && parentObj.tagName == 'BLINK'){
			FCKTools.RemoveOuterTags(parentObj);
		} else {
			var baseStyle;
			if (FCKBrowserInfo.IsIE){
				baseStyle = new FCKStyle(styleDesc['IEBlink']);
			} else {
				baseStyle = new FCKStyle(styleDesc['Blink']);
			}
			FCK.Styles.ApplyStyle(baseStyle);
		}

		FCK.Focus();
		FCK.Events.FireEvent('OnSelectionChange');
	},

	GetState : function()
	{
		// ソース編集モードのときは使用不可
		if (FCK.EditMode != FCK_EDITMODE_WYSIWYG) return FCK_TRISTATE_DISABLED;
		
		if (!this._IsSelectedString()) return FCK_TRISTATE_DISABLED;
		var parentObj = FCKSelection.GetParentElement();
		return (parentObj && parentObj.tagName == 'BLINK') ? FCK_TRISTATE_ON : FCK_TRISTATE_OFF;
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
				//var selObj = FCK.ToolbarSet.CurrentInstance.EditorWindow.getSelection();
				var selObj = FCK.EditorWindow.getSelection();
				if (selObj && selObj.anchorOffset < selObj.focusOffset) return true;
			}
		}
		return false;
	}
};
// コマンドを登録
FCKCommands.RegisterCommand('Blink', new FCKBlinkCommand());

// ツールバーボタン作成
var toolbarItem = new FCKToolbarButton('Blink'/*commandName*/, FCKLang.BlinkBtn/*label*/, FCKLang.BlinkBtn/*tooltip*/,
									  null/*style*/, false/*sourceView*/, true/*contextSensitive*//*icon*/);
toolbarItem.IconPath = FCKPlugins.Items['Blink'].Path + 'icon.gif';
FCKToolbarItems.RegisterItem('Blink', toolbarItem);
