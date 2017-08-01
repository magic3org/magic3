<?php
/**
 * menuプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// サブメニューを使用する
define('MENU_ENABLE_SUBMENU', FALSE);

// サブメニューの名称
define('MENU_SUBMENUBAR', 'MenuBar');

function plugin_menu_convert()
{
	//global $vars, $menubar;
	static $menu = NULL;

	$num = func_num_args();
	if ($num > 0) {
		// Try to change default 'MenuBar' page name (only)
		if ($num > 1)       return '#menu(): Zero or One argument needed';
		if ($menu !== NULL) return '#menu(): Already set: ' . htmlspecialchars($menu);
		$args = func_get_args();
		if (! WikiPage::isPage($args[0])) {
			return '#menu(): No such page: ' . htmlspecialchars($args[0]);
		} else {
			$menu = $args[0]; // Set
			return '';
		}
	} else {
		// Output menubar page data
		//$page = ($menu === NULL) ? $menubar : $menu;
		$page = ($menu === NULL) ? WikiConfig::getMenuBarPage() : $menu;

		if (MENU_ENABLE_SUBMENU) {
			//$path = explode('/', strip_bracket($vars['page']));
			$path = explode('/', strip_bracket(WikiParam::getPage()));
			while(! empty($path)) {
				$_page = join('/', $path) . '/' . MENU_SUBMENUBAR;
				if (WikiPage::isPage($_page)) {
					$page = $_page;
					break;
				}
				array_pop($path);
			}
		}

		if (! WikiPage::isPage($page)) {
			return '';
		//} else if ($vars['page'] == $page) {
		} else if (WikiParam::getPage() == $page){
			return '<!-- #menu(): You already view ' . htmlspecialchars($page) . ' -->';
		} else {
			// Cut fixed anchors
			$menutext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', get_source($page));

			return preg_replace('/<ul[^>]*>/', '<ul>', convert_html($menutext));  
		}
	}
}
?>
