<?php
/**
 * Joomla!テンプレートのパンくずリスト生成サポートプログラム
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
defined('_JEXEC') or die;

global $gEnvManager;

$menuData	= $gEnvManager->getJoomlaMenuData();
$list		= array_reverse($menuData['crumbs']);			// リストデータを逆に並べる
$count = count($list);

/*
// Get the breadcrumbs
$list  = ModBreadCrumbsHelper::getList($params);
$count = count($list);

// Set the default separator
$separator = ModBreadCrumbsHelper::setSeparator($params->get('separator'));
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');
*/
