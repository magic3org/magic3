/**
 * Magic3 CKEditorプラグイン
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: plugin.js 5957 2013-04-22 05:50:34Z fishbone $
 * @link       http://www.magic3.org
 */
CKEDITOR.plugins.add( 'youtube', {
	lang: 'en,ja',
	icons: 'youtube',

	init: function( editor ) {
		// プラグインを登録
		editor.addCommand( 'youtube', new CKEDITOR.dialogCommand( 'youtubeDialog' ) );

		// 実行するダイアログを登録
		CKEDITOR.dialog.add( 'youtubeDialog', this.path + 'dialogs/youtubeDialog.js' );

		// ツールバーにボタンを登録
		if ( editor.ui.addButton ) {
			editor.ui.addButton( 'YouTube', {
				label: editor.lang.youtube.toolbar,
				command: 'youtube',
				toolbar: 'others'
			});
		}
	}
});
