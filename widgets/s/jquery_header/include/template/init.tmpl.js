<patTemplate:tmpl name="_tmpl">
/*
 * jQuery Mobileメニュー初期化用スクリプト
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: init.tmpl.js 4543 2012-01-01 12:36:29Z fishbone $
 * @link       http://www.magic3.org
 */
$(document).bind("mobileinit", function(){
	$.mobile.page.prototype.options.addBackBtn = {AUTO_BACK_BUTTON};
});
</patTemplate:tmpl>
