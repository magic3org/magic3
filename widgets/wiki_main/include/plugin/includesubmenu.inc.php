<?php
/**
 * includesubmenuプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: includesubmenu.inc.php 1135 2008-10-26 11:35:35Z fishbone $
 * @link       http://www.magic3.org
 */

function plugin_includesubmenu_convert()
{
 	//global $script,$vars;
	global $script;
	
	$ShowPageName = FALSE;

	if (func_num_args()) {
		$aryargs = func_get_args();
		if ($aryargs[0] == 'showpagename') {
			$ShowPageName = TRUE;
		}
	}

	$SubMenuPageName = '';

	//$tmppage = strip_bracket($vars['page']);
	$tmppage = WikiParam::getPage();
	//下階層のSubMenuページ名
	$SubMenuPageName1 = $tmppage . '/SubMenu';

	//同階層のSubMenuページ名
	$LastSlash= strrpos($tmppage,'/');
	if ($LastSlash === FALSE) {
		$SubMenuPageName2 = 'SubMenu';
	} else {
		$SubMenuPageName2 = substr($tmppage,0,$LastSlash) . '/SubMenu';
	}
	//echo "$SubMenuPageName1 <br>";
	//echo "$SubMenuPageName2 <br>";
	//下階層にSubMenuがあるかチェック
	//あれば、それを使用
	if (is_page($SubMenuPageName1)) {
		//下階層にSubMenu有り
		$SubMenuPageName = $SubMenuPageName1;
	} else if (is_page($SubMenuPageName2)) {
		//同階層にSubMenu有り
		$SubMenuPageName = $SubMenuPageName2;
	} else {
		//SubMenu無し
		return "";
	}

	$body = convert_html(get_source($SubMenuPageName));

	if ($ShowPageName) {
		$r_page = rawurlencode($SubMenuPageName);
		$s_page = htmlspecialchars($SubMenuPageName);
		//$link = "<a href=\"$script?cmd=edit&amp;page=$r_page\">$s_page</a>";
		$url = $script . WikiParam::convQuery("?cmd=edit&amp;page=$r_page");
		$link = "<a href=\"$url\">$s_page</a>";
		$body = "<h1>$link</h1>\n$body";
	}
	return $body;
}
?>
