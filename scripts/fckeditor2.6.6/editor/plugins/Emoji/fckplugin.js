/*
 * FCKEmojiCommand Class: represents the "Emoji" command.
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
var emojiObj = new FCKDialogCommand('Emoji', FCKLang.EmojiDlgTitle, FCKPlugins.Items['Emoji'].Path + 'emoji.html', 400, 400);
FCKCommands.RegisterCommand('Emoji', emojiObj);

// Emojiクラスに関数追加
emojiObj.GetState = function()
{
	// ソース編集モードのときは使用不可
	if (FCK.EditMode != FCK_EDITMODE_WYSIWYG) return FCK_TRISTATE_DISABLED;
	
	if (!this._IsSelectedString()) return FCK_TRISTATE_DISABLED;
	var parentObj = FCKSelection.GetParentElement();
	return (parentObj && parentObj.tagName == 'IMG') ? FCK_TRISTATE_ON : FCK_TRISTATE_OFF;
}
emojiObj._IsSelectedString = function()
{
	return true;
	/*if (FCKBrowserInfo.IsIE){
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
	return false;*/
}

// ツールバーボタン作成
var toolbarItem = new FCKToolbarButton('Emoji'/*commandName*/, FCKLang.EmojiBtn/*label*/, FCKLang.EmojiBtn/*tooltip*/,
									  null/*style*/, false/*sourceView*/, true/*contextSensitive*//*icon*/);
toolbarItem.IconPath = FCKPlugins.Items['Emoji'].Path + 'icon.gif';
FCKToolbarItems.RegisterItem('Emoji', toolbarItem);

// コンテキストメニュー作成
FCK.ContextMenu.RegisterListener({
	AddItems : function(menu, tag, tagName)
	{
		// under what circumstances do we display this option
		if (tagName == 'IMG'){
			// the command needs the registered command name, the title for the context menu, and the icon path
			menu.AddSeparator();
			menu.AddItem('Emoji', FCKLang.EmojiEdit, toolbarItem.IconPath);
		}
	}
});
