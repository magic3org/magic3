<?php
defined('_JEXEC') or die('Restricted access'); // no direct access

if (!defined('_ARTX_FUNCTIONS')) {

	define('_ARTX_FUNCTIONS', 1);

	$GLOBALS['artx_settings'] = array(
		'block' => array('has_header' => true),
		'menu' => array('show_submenus' => true),
		'vmenu' => array('show_submenus' => false, 'simple' => false)
	);

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

	function artxPost($caption, $content)
	{
		$hasCaption = (null !== $caption && strlen(trim($caption)) > 0);
		$hasContent = (null !== $content && strlen(trim($content)) > 0);

		if (!$hasCaption && !$hasContent)
			return '';

		ob_start();
?>
<div class="art-post">
		    <div class="art-post-body">
		<div class="art-post-inner">
		
		<?php if ($hasCaption): ?>
<div class="art-postmetadataheader">
		<h2 class="art-postheader"><?php echo JHTML::_('image.site', 'postheadericon.png', null, null, null, JText::_("postheadericon"), array('width' => '32', 'height' => '32')); ?> 
		<?php echo $caption; ?>

		</h2>
		
		</div>
		
		<?php endif; ?>
		<?php if ($hasContent): ?>
<div class="art-postcontent">
		    <!-- article-content -->
		
		<?php echo artxReplaceButtons($content); ?>

		    <!-- /article-content -->
		</div>
		<div class="cleared"></div>
		
		<?php endif; ?>

		</div>
		
				<div class="cleared"></div>
		    </div>
		</div>
		
<?php
		return ob_get_clean();
	}

	function artxBlock($caption, $content)
	{
		$hasCaption = ($GLOBALS['artx_settings']['block']['has_header']
			&& null !== $caption && strlen(trim($caption)) > 0);
		$hasContent = (null !== $content && strlen(trim($content)) > 0);

		if (!$hasCaption && !$hasContent)
			return '';

		ob_start();
?>
<div class="art-block">
		    <div class="art-block-body">
		
		<?php if ($hasCaption): ?>
<div class="art-blockheader">
		     <div class="t">
		<?php echo $caption; ?>
</div>
		</div>
		
		<?php endif; ?>
		<?php if ($hasContent): ?>
<div class="art-blockcontent">
		    <div class="art-blockcontent-body">
		<!-- block-content -->
		
		<?php echo artxReplaceButtons($content); ?>

		<!-- /block-content -->
		
				<div class="cleared"></div>
		    </div>
		</div>
		
		<?php endif; ?>

				<div class="cleared"></div>
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
	
	function artxCountModules(&$document, $position)
	{
		return $document->countModules($position);
	}
	
	function artxPositions(&$document, $positions, $style)
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
	
	function artxGetContentCellStyle(&$document)
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

	function artxHtmlFixRemove($re, $content)
	{
		if (preg_match($re, $content, $matches, PREG_OFFSET_CAPTURE)) {
			$content = substr($content, 0, $matches[0][1])
				. substr($content, $matches[0][1] + strlen($matches[0][0]));
		}
		return $content;
	}

	function artxComponentWrapper(&$document)
	{
		if ($document->getType() != 'html')
			return;
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
		if (false === strpos($content, '<div class="art-post">')) {
			$title = null;
			if (preg_match('~<div\s+class="(componentheading[^"]*)"([^>]*)>([^<]+)</div>~', $content, $matches, PREG_OFFSET_CAPTURE)) {
				$content = substr($content, 0, $matches[0][1]) . substr($content, $matches[0][1] + strlen($matches[0][0]));
				$title = '<span class="' . $matches[1][0] . '"' . $matches[2][0] . '>' . $matches[3][0] . '</span>';
			}
			$document->setBuffer(artxPost($title, $content), 'component');
		}
	}

	function artxModules(&$document, $position, $style = null)
	{
		return '<jdoc:include type="modules" name="' . $position . '"' . (null != $style ? ' style="artstyle" artstyle="' . $style . '"' : '') . ' />';
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
	
		function artxReplaceButtonsRegex() {
			return '' .
				'~<input\b[^>]*'
					. '(?:'
						. '[^>]*\bclass=(?:"(?:[^"]*\s)?button(?:\s[^"]*)?"|\'(?:[^\']*\s)?button(?:\s[^\']*)?\'|button\b)[^>]*'
						. '(?:\bvalue=(?:"[^"]*"|\'[^\']*\'|[^>\s]*))'
					. '|'
						. '(?:\bvalue=(?:"[^"]*"|\'[^\']*\'|[^>\s]*))'
						. '[^>]*\bclass=(?:"(?:[^"]*\s)?button(?:\s[^"]*)?"|\'(?:[^\']*\s)?button(?:\s[^\']*)?\'|button\b)[^>]*'
					. '|'
						. '[^>]*\bclass=(?:"(?:[^"]*\s)?button(?:\s[^"]*)?"|\'(?:[^\']*\s)?button(?:\s[^\']*)?\'|button\b)[^>]*'
					. ')'
				. '[^>]*/?\s*>~i';
		}
	
		function artxReplaceButtons($content)
		{
			$re = artxReplaceButtonsRegex();
			if (!preg_match_all($re, $content, $matches, PREG_OFFSET_CAPTURE))
				return $content;
	
			$result = '';
			$position = 0;
			foreach ($matches[0] as $match) {
				$result .= substr($content, $position, $match[1] - $position);
				$position = $match[1] + strlen($match[0]);
				$result .= '<span class="art-button-wrapper"><span class="l"> </span><span class="r"> </span>'
					. preg_replace('~\bclass=(?:"([^"]*\s)?button(\s[^"]*)?"|\'([^\']*\s)?button(\s[^\']*)?\'|button\b)~i',
						'class="\1\3button art-button\2\4"', $match[0]) . '</span>';
			}
			$result .= substr($content, $position);
			return $result;
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
	
		$artxFragments = array();
	
		function artxFragmentBegin($head = '')
		{
			global $artxFragments;
			$artxFragments[] = array('head' => $head, 'content' => '', 'tail' => '');
		}
	
		function artxFragmentContent($content = '')
		{
			global $artxFragments;
			$artxFragments[count($artxFragments) - 1]['content'] = $content;
		}
	
		function artxFragmentEnd($tail = '', $separator = '')
		{
			global $artxFragments;
			$fragment = array_pop($artxFragments);
			$fragment['tail'] = $tail;
			$content = trim($fragment['content']);
			if (count($artxFragments) == 0) {
				echo (trim($content) == '') ? '' : ($fragment['head'] . $content . $fragment['tail']);
			} else {
				$result = (trim($content) == '') ? '' : ($fragment['head'] . $content . $fragment['tail']);
				$fragment =& $artxFragments[count($artxFragments) - 1];
				$fragment['content'] .= (trim($fragment['content']) == '' ? '' : $separator) . $result;
			}
		}
	
		function artxFragment($head = '', $content = '', $tail = '', $separator = '')
		{
			global $artxFragments;
			if ($head != '' && $content == '' && $tail == '' && $separator == '') {
				$content = $head;
				$head = '';
			} elseif ($head != '' && $content != '' && $tail == '' && $separator == '') {
				$separator = $content;
				$content = $head;
				$head = '';
			}
			artxFragmentBegin($head);
			artxFragmentContent($content);
			artxFragmentEnd($tail, $separator);
		}
	

}