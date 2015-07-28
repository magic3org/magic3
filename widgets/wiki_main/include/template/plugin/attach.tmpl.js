/*
 * Javascript生成用テンプレート
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
<patTemplate:tmpl name="_tmpl">
$(function(){
	$('#wiki_main_submit').click(function(){
		if (document.wiki_main.attach_file.value){
			document.wiki_main.pass.value = hex_md5(document.wiki_main.password.value);
			return true;			// 送信する
		} else {
			alert('ファイルが選択されていません');
			return false;			// 送信しない
		}
	});
});
</patTemplate:tmpl>
