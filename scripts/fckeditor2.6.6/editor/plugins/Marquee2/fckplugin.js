/*
 * FCKMarquee2Command Class: represents the "Marquee2" command.
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: fckplugin.js 3742 2010-10-27 02:28:44Z fishbone $
 * @link       http://www.magic3.org
 */
// コマンド登録
var FCKMarquee2DeleteCommand = function()
{}
FCKMarquee2DeleteCommand.prototype =
{
	Name : 'Marquee2Delete',

	Execute : function()
	{
		var marqueeObj = this._GetSelectedMarquee();
		if (marqueeObj){
			// Undo用データ保存
			FCKUndo.SaveUndoStep();
			
			FCKTools.RemoveOuterTags(marqueeObj);
			
			FCK.Focus();
			FCK.Events.FireEvent('OnSelectionChange');
		}
	},
	
	_GetSelectedMarquee : function()
	{
		var marqueeObj = FCK.Selection.GetSelectedElement();
/*		if (!marqueeObj || marqueeObj.tagName != 'MARQUEE'){
			marqueeObj = FCK.Selection.GetParentElement() ;
			if (marqueeObj && marqueeObj.tagName != 'MARQUEE') marqueeObj = null ;
		}*/
		return marqueeObj;
	}
}
var marqueeObj = new FCKDialogCommand('Marquee2', FCKLang.Marquee2DlgTitle, FCKPlugins.Items['Marquee2'].Path + 'marquee.html', 500, 300);
FCKCommands.RegisterCommand('Marquee2', marqueeObj);
FCKCommands.RegisterCommand('Marquee2Delete', new FCKMarquee2DeleteCommand());

// Marqueeクラスに関数追加
marqueeObj.GetState = function()
{
	// ソース編集モードのときは使用不可
	if (FCK.EditMode != FCK_EDITMODE_WYSIWYG) return FCK_TRISTATE_DISABLED;
	
	if (!this._IsSelectedString()) return FCK_TRISTATE_DISABLED;
	var parentObj = FCKSelection.GetParentElement();
	return (parentObj && parentObj.tagName == 'MARQUEE') ? FCK_TRISTATE_ON : FCK_TRISTATE_OFF;
}
marqueeObj.ApplyStyle = function()
{
	// Undo用データ保存
	FCKUndo.SaveUndoStep();

	FCK.Styles.ApplyStyle(new FCKStyle(marqueeObj.styleDef));

	FCK.Focus();
	FCK.Events.FireEvent('OnSelectionChange');
}
marqueeObj._IsSelectedString = function()
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
//var styleDef = {Element : 'marquee', Attributes : {}};
//var styleDef = {Element : 'span', Attributes: {}};
		var styleDesc = {
			'Marquee' : {
				Element : 'span',
				Styles : {
				}
			}
		};
//marqueeObj.styleDef = styleDef;
marqueeObj.styleDef = styleDesc['Marquee'];

// ツールバーボタン登録
// マーキー作成
var marqueeItem = new FCKToolbarButton('Marquee2'/*commandName*/, FCKLang.Marquee2Btn/*label*/, FCKLang.Marquee2Btn/*tooltip*/,
									  null/*style*/, false/*sourceView*/, true/*contextSensitive*//*icon*/);
marqueeItem.IconPath = FCKPlugins.Items['Marquee2'].Path + 'marquee.gif';
FCKToolbarItems.RegisterItem('Marquee2', marqueeItem);
// マーキー削除
var marqueeDelItem = new FCKToolbarButton('Marquee2Delete'/*commandName*/, FCKLang.Marquee2DeleteBtn/*label*/, FCKLang.Marquee2DeleteBtn/*tooltip*/,
									  null/*style*/, false/*sourceView*/, true/*contextSensitive*//*icon*/);
marqueeDelItem.IconPath = FCKPlugins.Items['Marquee2'].Path + 'marquee_del.gif';
FCKToolbarItems.RegisterItem('Marquee2Delete', marqueeDelItem);

// コンテキストメニュー作成
FCK.ContextMenu.RegisterListener({
	AddItems : function(menu, tag, tagName)
	{
		// under what circumstances do we display this option
//		if (tagName == 'MARQUEE'){
			// the command needs the registered command name, the title for the context menu, and the icon path
			menu.AddSeparator();
			menu.AddItem('Marquee2', FCKLang.Marquee2, marqueeItem.IconPath);
			menu.AddItem('Marquee2Delete', FCKLang.Marquee2DeleteBtn, marqueeDelItem.IconPath);
//		}
	}
});
