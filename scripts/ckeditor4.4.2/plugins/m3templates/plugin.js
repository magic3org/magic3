/**
 * Magic3 CKEditorプラグイン
 *
 * JavaScript 1.5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    1.0
 * @link       http://www.magic3.org
 */
( function() {
	CKEDITOR.plugins.add( 'm3templates', {
		requires: 'dialog',
		lang: 'ja,en',
		icons: 'm3templates,m3templates-rtl',
		hidpi: true,
		init: function( editor ) {
			CKEDITOR.dialog.add( 'm3templates', CKEDITOR.getUrl( this.path + 'dialogs/templates.js' ) );

			editor.addCommand( 'm3templates', new CKEDITOR.dialogCommand( 'm3templates' ) );

			editor.ui.addButton && editor.ui.addButton( 'M3Templates', {
				label: editor.lang.templates.button,
				command: 'm3templates',
				toolbar: 'doctools,10'
			} );
		}
	} );

	var templates = {},
		loadedTemplatesFiles = {};

	CKEDITOR.addTemplates = function( name, definition ) {
		templates[ name ] = definition;
	};

	CKEDITOR.getTemplates = function( name ) {
		return templates[ name ];
	};

	CKEDITOR.loadTemplates = function( templateFiles, callback ) {
		// Holds the templates files to be loaded.
		var toLoad = [];

		// Look for pending template files to get loaded.
		for ( var i = 0, count = templateFiles.length; i < count; i++ ) {
			if ( !loadedTemplatesFiles[ templateFiles[ i ] ] ) {
				toLoad.push( templateFiles[ i ] );
				loadedTemplatesFiles[ templateFiles[ i ] ] = 1;
			}
		}

		if ( toLoad.length )
			CKEDITOR.scriptLoader.load( toLoad, callback );
		else
			setTimeout( callback, 0 );
	};
} )();

/**
 * The templates definition set to use. It accepts a list of names separated by
 * comma. It must match definitions loaded with the {@link #templates_files} setting.
 *
 *		config.templates = 'my_templates';
 *
 * @cfg {String} [templates='default']
 * @member CKEDITOR.config
 */
CKEDITOR.config.templates = 'default';

/**
 * The list of templates definition files to load.
 *
 *		config.templates_files = [
 *			'/editor_templates/site_default.js',
 *			'http://www.example.com/user_templates.js
 *		];
 *
 * @cfg
 * @member CKEDITOR.config
 */
CKEDITOR.config.templates_files = [
	CKEDITOR.getUrl( 'plugins/m3templates/templates/default.js' )
];

// ##### Bootstrap型テンプレートの場合はコンテンツテンプレートを追加 #####
if (typeof(M3_CONFIG_WIDGET_CKEDITOR_TEMPLATE_TYPE) != "undefined" && M3_CONFIG_WIDGET_CKEDITOR_TEMPLATE_TYPE == 10){
	CKEDITOR.config.templates_files = [
		CKEDITOR.getUrl( 'plugins/m3templates/templates/default.js' ),
		CKEDITOR.getUrl('plugins/m3templates/templates/default_bootstrap.js')
	];
	CKEDITOR.config.templates = 'default,bootstrap';
}

/**
 * Whether the "Replace actual contents" checkbox is checked by default in the
 * Templates dialog.
 *
 *		config.templates_replaceContent = false;
 *
 * @cfg
 * @member CKEDITOR.config
 */
CKEDITOR.config.templates_replaceContent = false;
