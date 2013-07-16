<?php
/**
* @version		$Id: mainmenuHelper.php 5528 2013-01-08 12:00:41Z fishbone $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * mod_mainmenu Helper class
 *
 * @static
 * @package		Joomla
 * @subpackage	Menus
 * @since		1.5
 */
class modMainMenuHelper
{
	function &getXML($type, &$params, $decorator)
	{
		global $gEnvManager;
		static $xmls;

		// Joomla用のメニューコンテンツを取得
		$xmls[$type] = $gEnvManager->getJoomlaMenuContent();

		// Get document
		$xml = JFactory::getXMLParser('Simple');
		$xml->loadString($xmls[$type]);
		$doc = &$xml->document;

		//$menu	= &JSite::getMenu();
	//	$menu	= new JMenuTree($params);
	//	$active	= $menu->getActive();
		$start	= $params->get('startLevel');
		$end	= $params->get('endLevel');
		$sChild	= $params->get('showAllChildren');
//		$path	= array();

		if ($doc && is_callable($decorator)) {
			$doc->map($decorator, array('end'=>$end, 'children'=>$sChild));
		}
		return $doc;
	}

	function render(&$params, $callback)
	{
		// Include the new menu class
		$xml = modMainMenuHelper::getXML($params->get('menutype'), $params, $callback);
		if ($xml) {
			$class = $params->get('class_sfx');
			$xml->addAttribute('class', 'menu'.$class);
			if ($tagId = $params->get('tag_id')) {
				$xml->addAttribute('id', $tagId);
			}

			$result = JFilterOutput::ampReplace($xml->toString((bool)$params->get('show_whitespace')));
			$result = str_replace(array('<ul/>', '<ul />'), '', $result);
			echo $result;
		}
	}
}
