/*
 * jQuery Mobileローカライズスクリプト
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: init.js 4540 2012-01-01 08:22:22Z fishbone $
 * @link       http://www.magic3.org
 */
$(document).bind("mobileinit", function(){
	$.mobile.loadingMessage = '読込み中';
	$.mobile.pageLoadErrorMessage = '読込みに失敗しました';
	$.mobile.page.prototype.options.backBtnText = '戻る';
	$.mobile.dialog.prototype.options.closeBtnText = '閉じる';
	$.mobile.selectmenu.prototype.options.closeText= '閉じる';
	$.mobile.listview.prototype.options.filterPlaceholder = '検索文字列...';
});
