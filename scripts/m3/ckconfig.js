/*
 * CKEditor(WYSIWYGエディター)の設定
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ckconfig.js 6010 2013-05-19 11:38:31Z fishbone $
 * @link       http://www.magic3.org
 */
// スタイルメニューの定義
CKEDITOR.stylesSet.add( 'default', [
	/* Block Styles */

	// These styles are already available in the "Format" combo ("format" plugin),
	// so they are not needed here by default. You may enable them to avoid
	// placing the "Format" combo in the toolbar, maintaining the same features.
	/*
	{ name: 'Paragraph',		element: 'p' },
	{ name: 'Heading 1',		element: 'h1' },
	{ name: 'Heading 2',		element: 'h2' },
	{ name: 'Heading 3',		element: 'h3' },
	{ name: 'Heading 4',		element: 'h4' },
	{ name: 'Heading 5',		element: 'h5' },
	{ name: 'Heading 6',		element: 'h6' },
	{ name: 'Preformatted Text',element: 'pre' },
	{ name: 'Address',			element: 'address' },
	*/

	{ name: 'イタリックタイトル',		element: 'h2', styles: { 'font-style': 'italic' } },
	{ name: 'サブタイトル',			element: 'h3', styles: { 'color': '#aaa', 'font-style': 'italic' } },
	{
		name: 'グレーブロック',
		element: 'div',
		styles: {
			padding: '5px 10px',
			background: '#eee',
			border: '1px solid #ccc'
		}
	},

	/* Inline Styles */

	// These are core styles available as toolbar buttons. You may opt enabling
	// some of them in the Styles combo, removing them from the toolbar.
	// (This requires the "stylescombo" plugin)
	/*
	{ name: 'Strong',			element: 'strong', overrides: 'b' },
	{ name: 'Emphasis',			element: 'em'	, overrides: 'i' },
	{ name: 'Underline',		element: 'u' },
	{ name: 'Strikethrough',	element: 'strike' },
	{ name: 'Subscript',		element: 'sub' },
	{ name: 'Superscript',		element: 'sup' },
	*/

//	{ name: '黄マーカー(span)',	element: 'span', styles: { 'background-color': 'Yellow' } },
//	{ name: '緑マーカー(span)',	element: 'span', styles: { 'background-color': 'Lime' } },

	{ name: '文字大(big)',		element: 'big' },
	{ name: '文字小(small)',	element: 'small' },

//	{ name: 'コード(code)',	element: 'code' },
//	{ name: 'Keyboard Phrase',	element: 'kbd' },
//	{ name: 'Sample Text',		element: 'samp' },
//	{ name: 'Variable',			element: 'var' },

	{ name: '削除文(del)',		element: 'del' },
	{ name: '挿入文(ins)',	element: 'ins' },

	{ name: '引用(cite)',		element: 'cite' },
	{ name: '引用文(q)',	element: 'q' },

//	{ name: 'Language: RTL',	element: 'span', attributes: { 'dir': 'rtl' } },
//	{ name: 'Language: LTR',	element: 'span', attributes: { 'dir': 'ltr' } },

	/* Object Styles */

	{
		name: '画像左寄せ',
		element: 'img',
		attributes: { 'class': 'left' }
	},

	{
		name: '画像右寄せ',
		element: 'img',
		attributes: { 'class': 'right' }
	},

	{
		name: '簡易テーブル',
		element: 'table',
		attributes: {
			cellpadding: '5',
			cellspacing: '0',
			border: '1',
			bordercolor: '#ccc'
		},
		styles: {
			'border-collapse': 'collapse'
		}
	},

	{ name: '枠なしテーブル',		element: 'table',	styles: { 'border-style': 'hidden', 'background-color': '#E6E6FA' } }
//	{ name: '四角リスト',	element: 'ul',		styles: { 'list-style-type': 'square' } }
]);

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
	config.toolbar_Full = [
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
		{ name: 'document', items: [ 'Source', 'Templates' ] },
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', items : [ 'Find','Replace','-','SelectAll' ] },
		{ name: 'insert', items: [ 'Image', 'Flash', 'YouTube', 'Googlemaps', 'Table', 'HorizontalRule' ] },
		{ name: 'colors', items : [ 'TextColor', 'BGColor' ] },
		'/',
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', /*'-', 'Blockquote', 'CreateDiv',*/ '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
		{ name: 'styles', items: [ 'Styles', 'Format', 'FontSize' ] }
//		{ name: 'others', items: [ '-' ] }
//		{ name: 'about', items: [ 'About' ] }
	];
	config.toolbar_Layout = [
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
		{ name: 'document', items: [ 'Source' ] },
		{ name: 'clipboard', items: [ 'Undo', 'Redo' ] },
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
		{ name: 'paragraph', items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'styles', items: [ 'Styles', 'Format', 'FontSize' ] }
	];
	config.stylesCombo_stylesSet = 'default';
	
	// 追加プラグインの設定
	config.extraPlugins = 'youtube,googlemaps';
	config.removePlugins = 'iframe';
	config.allowedContent = true;		// ACF(Advanced Content Filter)を使用しない。SCRIPT,IFRAMEタグ等許可。
//	config.extraAllowedContent = 'iframe';
//	config.autoGrow_maxHeight = 800;		// 指定サイズまで入力に合わせて拡大
	
	// KCFinderの設定
	config.filebrowserBrowseUrl			= M3_ROOT_URL + '/scripts/kcfinder-2.51/browse.php?type=file';
	config.filebrowserImageBrowseUrl	= M3_ROOT_URL + '/scripts/kcfinder-2.51/browse.php?type=image';
	//config.filebrowserImageBrowseUrl	= M3_ROOT_URL + '/admin/index.php?task=filebrowser&openby=dialog&mode=image';
	config.filebrowserFlashBrowseUrl	= M3_ROOT_URL + '/scripts/kcfinder-2.51/browse.php?type=flash';
	config.filebrowserUploadUrl			= M3_ROOT_URL + '/scripts/kcfinder-2.51/upload.php?type=file';
	config.filebrowserImageUploadUrl	= M3_ROOT_URL + '/scripts/kcfinder-2.51/upload.php?type=image';
	config.filebrowserFlashUploadUrl	= M3_ROOT_URL + '/scripts/kcfinder-2.51/upload.php?type=flash';
};
CKEDITOR.on('dialogDefinition', function(ev){
	var dialogName = ev.data.name;
	var dialogDefinition = ev.data.definition;
	var dialog = dialogDefinition.dialog;
	
	if (dialogName == 'image' || dialogName == 'flash'){
		dialogDefinition.removeContents('Upload');	// 「アップロード」タブ削除
	}
});
CKEDITOR.on('instanceReady',function(){
	m3AdjustParentWindow();		// フレームサイズ調整
});