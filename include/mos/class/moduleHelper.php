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
 * @version    SVN: $Id: moduleHelper.php 4840 2012-04-11 01:44:05Z fishbone $
 * @link       http://www.magic3.org
 */
/**
 * Module helper class
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
abstract class JModuleHelper
{
	/**
	 * Get the path to a layout for a module
	 *
	 * @param   string  $module  The name of the module
	 * @param   string  $layout  The name of the module layout. If alternative layout, in the form template:filename.
	 *
	 * @return  string  The path to the module layout
	 *
	 * @since   11.1
	 */
	public static function getLayoutPath($module, $layout = 'default')
	{
/*		$template = JFactory::getApplication()->getTemplate();
		$defaultLayout = $layout;

		if (strpos($layout, ':') !== false)
		{
			// Get the template and file name from the string
			$temp = explode(':', $layout);
			$template = ($temp[0] == '_') ? $template : $temp[0];
			$layout = $temp[1];
			$defaultLayout = ($temp[1]) ? $temp[1] : 'default';
		}

		// Build the template and base path for the layout
		$tPath = JPATH_THEMES . '/' . $template . '/html/' . $module . '/' . $layout . '.php';
		$bPath = JPATH_BASE . '/modules/' . $module . '/tmpl/' . $defaultLayout . '.php';

		// If the template has a layout override use it
		if (file_exists($tPath))
		{
			return $tPath;
		}
		else
		{
			return $bPath;
		}*/
		global $gEnvManager;
		
		$templeteRender = $gEnvManager->getTemplatesPath() . '/' . $gEnvManager->getCurrentTemplateId() . '/html/' . $module . '/' . $layout . '.php';		// テンプレート独自の変換処理
		if (is_readable($templeteRender)) return $templeteRender;
		
		$joomlaRender = $gEnvManager->getSystemRootPath() . '/modules/' . $module . '/tmpl/' . $layout . '.php';
		return $joomlaRender;
	}
}
