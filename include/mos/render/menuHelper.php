<?php
/**
 * Joomlaテンプレート用関数
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: menuHelper.php 5336 2012-10-27 09:34:40Z fishbone $
 * @link       http://www.magic3.org
 */
// no direct access
defined('_JEXEC') or die;

global $gEnvManager;

$menuData	= $gEnvManager->getJoomlaMenuData();
$list		= $menuData['tree'];			// メニュー階層データ
$active_id	= $menuData['active_id'];		// 選択されている項目ID
$path		= $menuData['path'];			// 階層パス

for ($i = 0; $i < count($list); $i++){
	$list[$i]->params = new JParameter();
}

//$list	= modMenuHelper::getList($params);
//$app	= JFactory::getApplication();
//$menu	= $app->getMenu();
/*$active	= $menu->getActive();
$active_id = isset($active) ? $active->id : $menu->getDefault()->id;
$path	= isset($active) ? $active->tree : array();
$showAll	= $params->get('showAllChildren');
$class_sfx	= htmlspecialchars($params->get('class_sfx'));
*/
