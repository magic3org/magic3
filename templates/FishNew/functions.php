<?php
defined('_JEXEC') or die('Restricted access'); // no direct access

if (!defined('_ARTX_FUNCTIONS')) {

	define('_ARTX_FUNCTIONS', 1);

	function artxHasMessages()
	{
		global $mainframe;
		$messages = $mainframe->getMessageQueue();
		if (is_array($messages) && count($messages))
			foreach ($messages as $msg)
				if (isset($msg['type']) && isset($msg['message']))
					return true;
		return false;
	}

	function artxUrlToHref($url)
	{
		$result = '';
		$p = parse_url($url);
		if (isset($p['scheme']) && isset($p['host'])) {
			$result = $p['scheme'] . '://';
			if (isset($p['user'])) {
				$result .= $p['user'];
				if (isset($p['pass']))
					$result .= ':' . $p['pass'];
				$result .= '@';
			}
			$result .= $p['host'];
			if (isset($p['port']))
				$result .= ':' . $p['port'];
			if (!isset($p['path']))
				$result .= '/';
		}
		if (isset($p['path']))
			$result .= $p['path'];
		if (isset($p['query'])) {
			$result .= '?' . str_replace('&', '&amp;', $p['query']);
		}
		if (isset($p['fragment']))
			$result .= '#' . $p['fragment'];
		return $result;
	}

	function artxPost($caption, $content)
	{
		$hasCaption = (null !== $caption && strlen(trim($caption)) > 0);
		$hasContent = (null !== $content && strlen(trim($content)) > 0);

		if (!$hasCaption && !$hasContent)
			return '';

		ob_start();
?>
<div class="Post">
		    <div class="Post-body">
		<div class="Post-inner">
		
		<?php if ($hasCaption): ?>
<h2 class="PostHeaderIcon-wrapper"> <span class="PostHeader">
		<?php echo $caption; ?>
</span>
		</h2>
		
		<?php endif; ?>
		<?php if ($hasContent): ?>
<div class="PostContent">
		
		<?php echo $content; ?>

		</div>
		<div class="cleared"></div>
		
		<?php endif; ?>

		</div>
		
		    </div>
		</div>
		
<?php
		return ob_get_clean();
	}

	function artxBlock($caption, $content)
	{
		$hasCaption = (null !== $caption && strlen(trim($caption)) > 0);
		$hasContent = (null !== $content && strlen(trim($content)) > 0);

		if (!$hasCaption && !$hasContent)
			return '';

		ob_start();
?>
<div class="Block">
		    <div class="Block-tl"></div>
		    <div class="Block-tr"></div>
		    <div class="Block-bl"></div>
		    <div class="Block-br"></div>
		    <div class="Block-tc"></div>
		    <div class="Block-bc"></div>
		    <div class="Block-cl"></div>
		    <div class="Block-cr"></div>
		    <div class="Block-cc"></div>
		    <div class="Block-body">
		
		<?php if ($hasCaption): ?>
<div class="BlockHeader">
		    <div class="l"></div>
		    <div class="r"></div>
		    <div class="header-tag-icon">
		        <div class="t">
		<?php echo $caption; ?>
</div>
		    </div>
		</div>
		<?php endif; ?>
		<?php if ($hasContent): ?>
<div class="BlockContent">
		    <div class="BlockContent-body">
		
		<?php echo $content; ?>

		    </div>
		</div>
		
		<?php endif; ?>

		    </div>
		</div>
		
<?php
		return ob_get_clean();
	}

	function artxPageTitle($page, $criteria = null, $key = null)
	{
		if ($criteria === null)
			$criteria = $page->params->def('show_page_title', 1);
		return $criteria
			? ('<span class="componentheading' . $page->params->get('pageclass_sfx') . '">'
				. $page->escape($page->params->get($key === null ? 'page_title' : $key)) . '</span>')
			: '';
	}
	
	function artxCountModules($document, $position)
	{
		if (null == $document)
			// for Joomla 1.0
			return mosCountModules($position);
		// for Joomla 1.5
		return $document->countModules($position);
	}
	
	function artxPositions($document, $positions, $style)
	{
		ob_start();
		if (count($positions) == 3) {
			if (artxCountModules($document, $positions[0])
				&& artxCountModules($document, $positions[1])
				&& artxCountModules($document, $positions[2]))
			{
				?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
  <td width="33%"><?php echo artxModules($document, $positions[0], $style); ?></td>
  <td width="33%"><?php echo artxModules($document, $positions[1], $style); ?></td>
  <td><?php echo artxModules($document, $positions[2], $style); ?></td>
</tr>
</table>
<?php
			} elseif (artxCountModules($document, $positions[0]) && artxCountModules($document, $positions[1])) {
?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
  <td width="33%"><?php echo artxModules($document, $positions[0], $style); ?></td>
  <td><?php echo artxModules($document, $positions[1], $style); ?></td>
</tr>
</table>
<?php
			} elseif (artxCountModules($document, $positions[1]) && artxCountModules($document, $positions[2])) {
?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
  <td width="67%"><?php echo artxModules($document, $positions[1], $style); ?></td>
  <td><?php echo artxModules($document, $positions[2], $style); ?></td>
</tr>
</table>
<?php
			} elseif (artxCountModules($document, $positions[0]) && artxCountModules($document, $positions[2])) {
?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
  <td width="50%"><?php echo artxModules($document, $positions[0], $style); ?></td>
  <td><?php echo artxModules($document, $positions[2], $style); ?></td>
</tr>
</table>
<?php
			} else {
				echo artxModules($document, $positions[0], $style);
				echo artxModules($document, $positions[1], $style);
				echo artxModules($document, $positions[2], $style);
			}
		} elseif (count($positions) == 2) {
			if (artxCountModules($document, $positions[0]) && artxCountModules($document, $positions[1])) {
?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
<td width="50%"><?php echo artxModules($document, $positions[0], $style); ?></td>
<td><?php echo artxModules($document, $positions[1], $style); ?></td>
</tr>
</table>
<?php
			} else {
				echo artxModules($document, $positions[0], $style);
				echo artxModules($document, $positions[1], $style);
			}
		} // count($positions)
		return ob_get_clean();
	}
	
	function artxGetContentCellStyle($document)
	{
		$leftCnt = artxCountModules($document, 'left');
		$rightCnt = artxCountModules($document, 'right');
		if ($leftCnt > 0 && $rightCnt > 0)
			return 'content';
		if ($rightCnt > 0)
			return 'content-sidebar1';
		if ($leftCnt > 0)
			return 'content-sidebar2';
		return 'content-wide';
	}
	
	function artxHtmlFixMoveScriptToHead($re, $content)
	{
		if (preg_match($re, $content, $matches, PREG_OFFSET_CAPTURE)) {
			$content = substr($content, 0, $matches[0][1])
				. substr($content, $matches[0][1] + strlen($matches[0][0]));
			$document =& JFactory::getDocument();
			$document->addScriptDeclaration($matches[1][0]); 
		}
		return $content;
	}

	function artxHtmlFixFormAction($content)
	{
		if (preg_match('~ action="([^"]+)" ~', $content, $matches, PREG_OFFSET_CAPTURE)) {
			$content = substr($content, 0, $matches[0][1])
				. ' action="' . artxUrlToHref($matches[1][0]) . '" '
				. substr($content, $matches[0][1] + strlen($matches[0][0]));
		}
		return $content;
	}

	function artxHtmlFixRemove($re, $content)
	{
		if (preg_match($re, $content, $matches, PREG_OFFSET_CAPTURE)) {
			$content = substr($content, 0, $matches[0][1])
				. substr($content, $matches[0][1] + strlen($matches[0][0]));
		}
		return $content;
	}

	function artxComponentWrapper($document)
	{
		if (null == $document) {
			// for Joomla 1.0
			return;
		}
		// for Joomla 1.5
		if ($document->getType() != 'html') return;
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$layout = JRequest::getCmd('layout');
		$content = $document->getBuffer('component');
		// fixes for w3.org validation
		if ('com_contact' == $option) {
			if ('category' == $view) {
				$content = artxHtmlFixFormAction($content);
			} elseif ('contact' == $view) {
				$content = artxHtmlFixMoveScriptToHead('~<script [^>]+>\s*(<!--[^>]*-->)\s*</script>~', $content);
			}
		} elseif ('com_content' == $option) {
			if ('category' == $view) {
				if ('' == $layout) {
					$content = artxHtmlFixMoveScriptToHead('~<script [^>]+>([^<]*)</script>~', $content);
					$content = artxHtmlFixFormAction($content);
				}
			} elseif ('archive' == $view) {
				$content = artxHtmlFixRemove('~<ul id="archive-list" style="list-style: none;">\s*</ul>~', $content);
			}
		} elseif ('com_user' == $option) {
			if ('user' == $view) {
				if ('form' == $layout) {
					$content = artxHtmlFixRemove('~autocomplete="off"~', $content);
				}
			}
		}
		if (false === strpos($content, '<div class="Post">')) {
			$title = null;
			if (preg_match('~<div\s+class="(componentheading[^"]*)"([^>]*)>([^<]+)</div>~', $content, $matches, PREG_OFFSET_CAPTURE)) {
				$content = substr($content, 0, $matches[0][1]) . substr($content, $matches[0][1] + strlen($matches[0][0]));
				$title = '<span class="' . $matches[1][0] . '"' . $matches[2][0] . '>' . $matches[3][0] . '</span>';
			}
			$document->setBuffer(artxPost($title, $content), 'component');
		}
	}
	
	function artxComponent()
	{
		// for Joomla 1.0
		ob_start();
		mosMainBody();
		$content = ob_get_clean();
		if (false === strpos($content, '<div class="Post">')) {
			$title = null;
			if (preg_match('~<div\s+class="(componentheading[^"]*)"([^>]*)>([^<]+)</div>~', $content, $matches, PREG_OFFSET_CAPTURE)) {
				$content = substr($content, 0, $matches[0][1]) . substr($content, $matches[0][1] + strlen($matches[0][0]));
				$title = '<span class="' . $matches[1][0] . '"' . $matches[2][0] . '>' . $matches[3][0] . '</span>';
			}
			return artxPost($title, $content);
		}
		return $content;
	}
	
	function artxModules($document, $position, $style = null)
	{
		if ($document == null) {
			// for Joomla 1.0
			ob_start();
			mosLoadModules($position, -2);
			$content = ob_get_clean();
			if (null == $style || 'xhtml' == $style)
				return $content;
			$decorator = 'artblock' == $style ? 'artxBlock' : ('artpost' == $style ? 'artxPost' : null);
			$result = '';
			$modules = preg_split('~</div>\s*<div class="moduletable">~', $content);
			$lastModule = count($modules) - 1;
			if ($lastModule > -1) {
				$modules[0] = preg_replace('~^\s*<div class="moduletable">~', '', $modules[0]);
				$modules[$lastModule] = preg_replace('~</div>\s*$~', '', $modules[$lastModule]);
				foreach ($modules as $module) {
					if (preg_match('~^\s*<h3>([^<]*)</h3>~', $module, $matches, PREG_OFFSET_CAPTURE)) {
						$result .= $decorator($matches[1][0], substr($module, 0, $matches[0][1])
							. substr($module, $matches[0][1] + strlen($matches[0][0])));
					} else {
						$result .= $decorator(null, $module);
					}
				}
			}
			return $result;
		}
		// for Joomla 1.5
		return '<jdoc:include type="modules" name="' . $position . '"' . (null != $style ? ' style="' . $style . '"' : '') . ' />';
	}
}