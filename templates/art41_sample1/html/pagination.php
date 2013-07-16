<?php

defined('_JEXEC') or die;

function pagination_list_render($list)
{
	// Initialise variables.
	$lang = JFactory::getLanguage();
	$html = "<div class=\"art-pager\">";

	if ($list['start']['active']) {
		$html .= $list['start']['data'];
	} else {
		$html .= str_replace("pagenav", "active", $list['start']['data']);
	}
	if ($list['previous']['active']) {
		$html .= $list['previous']['data'];
	} else {
		$html .= str_replace("pagenav", "active", $list['previous']['data']);
	}

	foreach($list['pages'] as $page) {
		if (!$page['active']) {
			$html .= str_replace("pagenav", "active", $page['data']);
		} else {
			$html .= $page['data'];
		}
 	}

	if ($list['next']['active']) {
		$html .= $list['next']['data'];
	} else {
		$html .= str_replace("pagenav", "active", $list['next']['data']);
	}
	if ($list['end']['active']) {
		$html .= $list['end']['data'];
	} else {
		$html .= str_replace("pagenav", "active", $list['end']['data']);
	}

	$html .= "</div>";
	return $html;
}