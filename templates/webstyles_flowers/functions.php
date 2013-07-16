<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

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

}