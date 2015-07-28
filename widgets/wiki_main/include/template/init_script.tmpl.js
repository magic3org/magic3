/*
 * BOOTSTRAP型テンプレートの場合の起動時実行Javaスクリプト
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
<patTemplate:tmpl name="_tmpl">
$(function(){
	// アンカーの移動位置を修正
	var paddingTop = $('body').css('padding-top');
	if (paddingTop) $('.anchor_super').css('padding-top', paddingTop).css('margin-top', '-' + paddingTop);
//	if (paddingTop) $('.anchor_super').css('padding-top', paddingTop);
<patTemplate:tmpl name="fileselect" visibility="hidden">
	// アップロードファイル選択
	$('.btn-file :file').on('fileselect', function(event, numFiles, label){
		var input = $(this).parents('.input-group').find(':text'),
			log = numFiles > 1 ? numFiles + ' files selected' : label;
		
		if (input.length){
			input.val(log);
		} else {
			if (log) alert(log);
		}
	});
	
	$(document).on('change', '.btn-file :file', function(){
	    var input = $(this),
	        numFiles = input.get(0).files ? input.get(0).files.length : 1,
	        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
	    input.trigger('fileselect', [numFiles, label]);
	});
</patTemplate:tmpl>
});
</patTemplate:tmpl>
