<?php
defined('_JEXEC') or die('Restricted access'); // no direct access

ob_start();
require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../../../../modules/mod_mainmenu/tmpl/default.php');
ob_clean();

if (!defined('modMainMenuArtXMLCallbackDefined'))
{
function modMainMenuArtXMLCallback(&$node, $args)
{
		if (!$GLOBALS['menu_showSubmenus'] && $node->name() == 'li' && $node->level() == 1) {
			if ($ul = $node->getElementByPath('ul'))
				$node->removeChild($ul);
		}
		
		if ($node->name() == 'li') {
			$liChildren = & $node->_children;
			if (count($liChildren) > 0) {
				$liFirstChild = & $liChildren[0];
				$linkChildren = & $liFirstChild->_children;
				$span = & $linkChildren[0];
				$text = $span->data();
				if ($liFirstChild->name() == 'span' && $liFirstChild->attributes('class') == 'separator') {
					$liFirstChild->_name = 'a';
					$liFirstChild->addAttribute('href', '#');
					$liFirstChild->addAttribute('onclick', 'return false;');
				}
				if ($liFirstChild->name() == 'a') {
					if ($liFirstChild->level() == 2) {
						$liFirstChild->removeChild($span);
						$lspan = & $liFirstChild->addChild('span', array('class' => 'l'));
						$lspan->setData(' ');
						$rspan = & $liFirstChild->addChild('span', array('class' => 'r'));
						$rspan->setData(' ');
						$tspan = & $liFirstChild->addChild('span', array('class' => 't'));
						$tspan->setData($text);
					} else {
						$liFirstChild->removeAttribute('class');
						$liFirstChild->setData($text);
						$liFirstChild->removeChild($span);
					}
				}
			}
		}
		modMainMenuXMLCallback($node, $args);
		if ($node->name() == 'li') {
			$class = $node->attributes('class');
			if ($class && false !== strpos(' ' . $class, ' active')) {
				$itemChildren = & $node->_children;
				$itemChildren[0]->addAttribute('class', 'active');
			}
		}
	}

	define('modMainMenuArtXMLCallbackDefined', true);
}

if (!defined('artxMenuDecorator'))
{

	function artxMenuDecorator($content)
	{
		$result = '';
		ob_start();
?>
<div class="art-nav">
	<div class="l"></div>
	<div class="r"></div>
<?php
		$result .= ob_get_clean() . $content;
		ob_start();
?>
</div>
<?php
		$result .= ob_get_clean();
		return $result;
	}

	define('artxMenuDecorator', true);
}

if ($attribs['name'] == 'user3') {
	$GLOBALS['menu_showSubmenus'] = true && 1 == $params->get('showAllChildren');
	$xml = modMainMenuHelper::getXML($params->get('menutype'), $params, 'modMainMenuArtXMLCallback');
	if ($xml) {
		$xml->addAttribute('class', 'art-menu');
		if ($tagId = $params->get('tag_id')) {
			$xml->addAttribute('id', $tagId);
		}
		$result = JFilterOutput::ampReplace($xml->toString((bool)$params->get('show_whitespace')));
		$result = str_replace(array('<ul/>', '<ul />'), '', $result);
		echo artxMenuDecorator($result);
	}
} else {
	modMainMenuHelper::render($params, 'modMainMenuXMLCallback');
}
