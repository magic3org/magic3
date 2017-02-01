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
 * @version    SVN: $Id: plugin.js 5938 2013-04-16 23:20:50Z fishbone $
 * @link       http://www.magic3.org
 */
(function() {
	var pluginName = 'linkinfo';

	// Register a plugin named "linkinfo".
	CKEDITOR.plugins.add( pluginName, {
		lang: 'en,ja',
		icons: pluginName,
		init: function( editor ) {
			//if ( editor.blockless ) return;

			editor.addCommand( pluginName, new CKEDITOR.dialogCommand( 'linkinfoDialog' ) );
			CKEDITOR.dialog.add( 'linkinfoDialog', this.path + 'dialogs/linkinfo.js' );
			
			editor.ui.addButton && editor.ui.addButton( 'LinkInfo', {
				label: editor.lang.linkinfo.toolbar,
				command: pluginName,
				toolbar: 'others'
			});
		}
	});
})();
