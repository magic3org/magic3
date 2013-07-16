/*
 * FCKMobileLinkCommand Class: represents the "Emoji" command.
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
var linkObj = new FCKDialogCommand('MobileLink', FCKLang.DlgLnkWindowTitle, FCKPlugins.Items['MobileLink'].Path + 'mobilelink.html', 400, 300);
FCKCommands.RegisterCommand('MobileLink', linkObj);

// Emojiクラスに関数追加
linkObj.GetState = function()
{
	// ソース編集モードのときは使用不可
	if (FCK.EditMode != FCK_EDITMODE_WYSIWYG) return FCK_TRISTATE_DISABLED;
	
	if (!this._IsSelectedString()) return FCK_TRISTATE_DISABLED;
	
	return FCK_TRISTATE_OFF;// ツールバー上のボタンを使用可にする
}
linkObj._IsSelectedString = function()
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

// ツールバーボタン作成
var toolbarItem = new FCKToolbarButton('MobileLink', FCKLang.InsertLinkLbl, FCKLang.InsertLink, null, false, true, 34);
FCKToolbarItems.RegisterItem('MobileLink', toolbarItem);

// コンテキストメニュー作成
FCK.ContextMenu.RegisterListener({
	AddItems : function(menu, tag, tagName)
	{
		var bInsideLink = ( tagName == 'A' || FCKSelection.HasAncestorNode( 'A' ) ) ;

		if ( bInsideLink || FCK.GetNamedCommandState( 'Unlink' ) != FCK_TRISTATE_DISABLED )
		{
			// Go up to the anchor to test its properties
			var oLink = FCKSelection.MoveToAncestorNode( 'A' ) ;
			var bIsAnchor = ( oLink && oLink.name.length > 0 && oLink.href.length == 0 ) ;
			// If it isn't a link then don't add the Link context menu
			if ( bIsAnchor )
				return ;

			// 既存項目をすべて削除後追加(デフォルトのリンク作成項目と二重に登録されるため)
			menu.RemoveAllItems();
			if ( bInsideLink )
				menu.AddItem( 'MobileLink', FCKLang.EditLink		, 34 ) ;
			menu.AddItem( 'Unlink'	, FCKLang.RemoveLink	, 35 ) ;
		}
	}
});
