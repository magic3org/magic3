/*
 * jQuery Mobileメニュー初期化用スクリプト
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: add_head.tmpl.js 4559 2012-01-03 16:26:59Z fishbone $
 * @link       http://www.magic3.org
 */
<patTemplate:tmpl name="_tmpl">
$(document).bind("mobileinit", function(){
$(':jqmData(role="page")').live('pagebeforecreate', function(event){
 //alert( 'This page was just hidden: '+ ui.prevPage);
		//var queryStr = window.location.href.split("?");
		var queryStr = $(this).jqmData('url').split("?");
		if (queryStr[1]){
		//$(':jqmData(role="header")').append('<a href="' + queryStr[0] + '" data-transition="slideup" data-role="button"　data-icon="home" data-iconpos="notext" class="ui-btn-right">Home</a>');
		//	$(':jqmData(role="header")').append('<a href="http://192.168.24.45/magic3/s" data-transition="slideup" data-role="button" data-icon="home" data-iconpos="notext" class="ui-btn-right">Home</a>');
		}
});
});
</patTemplate:tmpl>
