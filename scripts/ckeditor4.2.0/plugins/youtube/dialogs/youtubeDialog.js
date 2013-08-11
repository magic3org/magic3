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
 * @version    SVN: $Id: youtubeDialog.js 6004 2013-05-17 01:46:23Z fishbone $
 * @link       http://www.magic3.org
 */
CKEDITOR.dialog.add( 'youtubeDialog', function( editor ){
	return {
		title: editor.lang.youtube.title,
		minWidth: 390,
		minHeight: 130,
		contents : [
		{
			id : 'tab_single',
			label : 'Settings',
			title : 'Settings',
			expand : true,
			padding : 0,
			elements :[
			{
				// 項目を縦に配置
				type: 'vbox',
				widths : [ null, null ],
				styles : [ 'vertical-align:top' ],
				padding: '5px',
				children: [
				{
					type : 'html',
					padding: '5px',
					html : editor.lang.youtube.instruction
				}, {
					type : 'text',
					id : 'txtVideoId',
					label: editor.lang.youtube.videoId,
					style: 'margin-top:5px;',
					'default': '',
					validate: function() {
						// Just a little light validation
						// 'this' is now a CKEDITOR.ui.dialog.textInput object which
						// is an extension of a CKEDITOR.ui.dialog.uiElement object
						var value = this.getValue();
						value = value.replace(/http:.*youtube.*?v=/, '');
						this.setValue(value);
					},
					// The commit function gets called for each form element
					// when the dialog's commitContent Function is called.
					// For our dialog, commitContent is called when the user
					// Clicks the "OK" button which is defined a little further down
					commit: function( data ) {
						var id = this.id;
						if ( !data.info ) data.info = {};
						data.info[id] = this.getValue();
					}
				} ]
			}, {
				// 項目を横に配置
				type: 'hbox',
				widths : [ null, null ],
				styles : [ 'vertical-align:top' ],
				padding: '5px',
				children: [
				{
					type : 'text',
					id : 'txtWidth',
					label: editor.lang.youtube.width,
					// We need to quote the default property since it is a reserved word
					// in javascript
					'default': 500,
					validate : function() {
						var pass = true,
						value = this.getValue();
						pass = pass && CKEDITOR.dialog.validate.integer()( value ) && value > 0;
						if ( !pass ){
							alert( "Invalid Width" );
							this.select();
						}
						return pass;
					},
					commit: function( data ) {
						var id = this.id;
						if ( !data.info ) data.info = {};
						data.info[id] = this.getValue();
					}
				}, {
					type : 'text',
					id : 'txtHeight',
					label: editor.lang.youtube.height,
					'default': 300,
					validate : function() {
						var pass = true,
						value = this.getValue();
						pass = pass && CKEDITOR.dialog.validate.integer()( value ) && value > 0;
						if ( !pass ){
							alert( "Invalid Height" );
							this.select();
						}
						return pass;
					},
					commit: function( data ) {
						var id = this.id;
						if ( !data.info ) data.info = {};
						data.info[id] = this.getValue();
					}
				}, {
					type : 'checkbox',
					id : 'chkAutoplay',
					label: editor.lang.youtube.autoplay,
					commit: function( data ) {
						var id = this.id;
						if ( !data.info ) data.info = {};
						data.info[id] = this.getValue();
					}
				} ]
			} ]		// elements
		} ],
		onOk : function() {
			// A container for our field data
			var data = {};

			// Commit the field data to our data object
			// This function calls the commit function of each field element
			// Each field has a commit function (that we define below) that will
			// dump it's value into the data object
			this.commitContent( data );

			if (data.info) {
				var info = data.info;
				var src = 'http://youtube.com/embed/' + info.txtVideoId;
				if (info.chkAutoplay) src += '?' + 'autoplay=1';
				
				// Create the iframe element
				var iframe = new CKEDITOR.dom.element( 'iframe' );
				// Add the attributes to the iframe.
				iframe.setAttributes({
					'width': info.txtWidth,
					'height': info.txtHeight,
					'type': 'text/html',
					'src': src,
					'frameborder': 0
				});
				// Finally insert the element into the editor.
				editor.insertElement(iframe);
			}
		}
	};
});
