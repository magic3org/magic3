<?php

// no direct access
defined('_JEXEC') or die('Restricted access');


if ( ! defined('modMainMenuXMLCallbackDefined') )
{
function modMainMenuXMLCallback(&$node, $args)
{
	$user	= &JFactory::getUser();
	$menu	= &JSite::getMenu();
	$active	= $menu->getActive();
	$path	= isset($active) ? array_reverse($active->tree) : null;

	if (($args['end']) && ($node->attributes('level') >= $args['end']))
{
		$children = $node->children();
		foreach ($node->children() as $child)
{
			if ($child->name() == 'ul') {
				$node->removeChild($child);
			}
		}
	}

	if ($node->name() == 'ul') {
		foreach ($node->children() as $child)
{
			if ($child->attributes('access') > $user->get('aid', 0)) {
				$node->removeChild($child);
			}
		}
	}

	if (($node->name() == 'li') && isset($node->ul)) {
		$node->addAttribute('class', 'parent');
	}

	if (isset($path) && in_array($node->attributes('id'), $path))
{
		if ($node->attributes('class')) {
			$node->addAttribute('class', $node->attributes('class').' active');
		} else {
			$node->addAttribute('class', 'active');
		}
	}
	else
{
		if (isset($args['children']) && !$args['children'])
{
			$children = $node->children();
			foreach ($node->children() as $child)
{
				if ($child->name() == 'ul') {
					$node->removeChild($child);
				}
			}
		}
	}

	if (($node->name() == 'li') && ($id = $node->attributes('id'))) {
		if ($node->attributes('class')) {
			$node->addAttribute('class', $node->attributes('class').' item'.$id);
		} else {
			$node->addAttribute('class', 'item'.$id);
		}
	}

	if (isset($path) && $node->attributes('id') == $path[0]) {
		$node->addAttribute('id', 'current');
	} else {
		$node->removeAttribute('id');
	}
	$node->removeAttribute('level');
	$node->removeAttribute('access');
}
	define('modMainMenuXMLCallbackDefined', true);
}

if ( ! defined('modMainMenuArtXMLCallbackDefined') )
{
function modMainMenuArtXMLCallback(&$node, $args)
{
		if (!$GLOBALS['menu_showSubmenus'] && $node->name() == 'li' && $node->level() == 1) {
			if ($ul = $node->getElementByPath('ul'))
				$node->removeChild($ul);
		}
		if ($node->name() == 'a') {
			$linkChildren = & $node->children();
			$span = & $linkChildren[0];
			if ($node->level() == 2) {
				$secondSpan = & $span->addChild('span');
				$secondSpan->setData($span->data());
				$span->setData('');
			} else {
				$node->removeAttribute('class');
				$node->setData($span->data());
				$node->removeChild($span);
			}
		}
		modMainMenuXMLCallback($node, $args);
		if ($node->name() == 'li') {
			$class = $node->attributes('class');
			if ($class && false !== strpos(' ' . $class, ' active')) {
				$itemChildren = & $node->children();
				$itemChildren[0]->addAttribute('class', 'active');
			}
		}
	}

	define('modMainMenuArtXMLCallbackDefined', true);
}

if ($attribs['name'] == 'user3') {
	$GLOBALS['menu_showSubmenus'] = true && 1 == $params->get('showAllChildren');
	$xml = modMainMenuHelper::getXML($params->get('menutype'), $params, 'modMainMenuArtXMLCallback');
	if ($xml) {
		$xml->addAttribute('class', 'artmenu');
		if ($tagId = $params->get('tag_id')) {
			$xml->addAttribute('id', $tagId);
		}
		$result = JFilterOutput::ampReplace($xml->toString((bool)$params->get('show_whitespace')));
		$result = str_replace(array('<ul/>', '<ul />'), '', $result);
		echo '<div class="nav">' . $result . '<div class="l"></div><div class="r"><div></div></div></div>';
	}
} else {
	modMainMenuHelper::render($params, 'modMainMenuXMLCallback');
}
