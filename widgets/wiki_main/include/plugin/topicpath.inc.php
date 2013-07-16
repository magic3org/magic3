<?php
/**
 * topicpathプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: topicpath.inc.php 1134 2008-10-26 11:12:03Z fishbone $
 * @link       http://www.magic3.org
 */
// Show a link to $defaultpage or not
define('PLUGIN_TOPICPATH_TOP_DISPLAY', 1);

// Label for $defaultpage
define('PLUGIN_TOPICPATH_TOP_LABEL', 'Top');

// Separetor / of / topic / path
define('PLUGIN_TOPICPATH_TOP_SEPARATOR', ' / ');

// Show the page itself or not
define('PLUGIN_TOPICPATH_THIS_PAGE_DISPLAY', 1);

// If PLUGIN_TOPICPATH_THIS_PAGE_DISPLAY, add a link to itself
define('PLUGIN_TOPICPATH_THIS_PAGE_LINK', 0);

function plugin_topicpath_convert()
{
	return '<div>' . plugin_topicpath_inline() . '</div>';
}

function plugin_topicpath_inline()
{
	//global $script, $vars, $defaultpage;
	global $script;

	//$page = isset($vars['page']) ? $vars['page'] : '';
	$page = WikiParam::getPage();
	//if ($page == '' || $page == $defaultpage) return '';
	if ($page == '' || $page == WikiConfig::getDefaultPage()) return '';

	$parts = explode('/', $page);

	$b_link = TRUE;
	if (PLUGIN_TOPICPATH_THIS_PAGE_DISPLAY) {
		$b_link = PLUGIN_TOPICPATH_THIS_PAGE_LINK;
	} else {
		array_pop($parts); // Remove the page itself
	}

	$topic_path = array();
	while (! empty($parts)) {
		$_landing = join('/', $parts);
		$landing  = rawurlencode($_landing);
		$element = htmlspecialchars(array_pop($parts));
		if (! $b_link)  {
			// This page ($_landing == $page)
			$b_link = TRUE;
			$topic_path[] = $element;
		} else if (PKWK_READONLY && ! is_page($_landing)) {
			// Page not exists
			$topic_path[] = $element;
		} else {
			// Page exists or not exists
			//$topic_path[] = '<a href="' . $script . '?' . $landing . '">' . $element . '</a>';
			$topic_path[] = '<a href="' . $script . WikiParam::convQuery('?' . $landing) . '">' . $element . '</a>';
		}
	}

	if (PLUGIN_TOPICPATH_TOP_DISPLAY)
		$topic_path[] = make_pagelink(WikiConfig::getDefaultPage(), PLUGIN_TOPICPATH_TOP_LABEL);
		//$topic_path[] = make_pagelink($defaultpage, PLUGIN_TOPICPATH_TOP_LABEL);

	return join(PLUGIN_TOPICPATH_TOP_SEPARATOR, array_reverse($topic_path));
}
?>
