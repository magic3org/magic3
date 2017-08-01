<?php
/**
 * mapプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
/*
 * プラグイン map: サイトマップ(のようなもの)を表示
 * Usage : http://.../pukiwiki.php?plugin=map
 * パラメータ
 *   &refer=ページ名
 *     起点となるページを指定
 *   &reverse=true
 *     あるページがどこからリンクされているかを一覧。
*/

// Show $non_list files
define('PLUGIN_MAP_SHOW_HIDDEN', 0); // 0, 1

function plugin_map_action()
{
	//global $vars, $whatsnew, $defaultpage, $non_list;
	global $non_list;

/*
	$reverse = isset($vars['reverse']);
	$refer   = isset($vars['refer']) ? $vars['refer'] : '';
	if ($refer == '' || ! WikiPage::isPage($refer))
		$vars['refer'] = $refer = $defaultpage;
*/
	$reverse = (WikiParam::getVar('reverse') != '');
	$refer   = WikiParam::getRefer();
	if ($refer == '' || !WikiPage::isPage($refer)){
		$refer = WikiConfig::getDefaultPage();
		WikiParam::setRefer($refer);
	}

	$retval['msg']  = $reverse ? 'Relation map (link from)' : 'Relation map, from $1';
	$retval['body'] = '';

	// Get pages
	//$pages = array_values(array_diff(get_existpages(), array($whatsnew)));
	$pages = array_values(array_diff(get_existpages(), array(WikiConfig::getWhatsnewPage())));
	if (! PLUGIN_MAP_SHOW_HIDDEN)
		$pages = array_diff($pages, preg_grep('/' . $non_list . '/', $pages));
	if (empty($pages)) {
		$retval['body'] = 'No pages.';
		return $retval;
	} else {
		$retval['body'] .= '<p>' . "\n" .  'Total: ' . count($pages) .
			' page(s) on this site.' . "\n" . '</p>' . "\n";
	}

	// Generate a tree
	$nodes = array();
	foreach ($pages as $page)
		$nodes[$page] = new MapNode($page, $reverse);

	// Node not found: Because of filtererd by $non_list
	//if (! isset($nodes[$refer])) $vars['refer'] = $refer = $defaultpage;
	if (!isset($nodes[$refer])){
		$refer = WikiConfig::getDefaultPage();
		WikiParam::setRefer($refer);
	}

	if ($reverse) {
		$keys = array_keys($nodes);
		sort($keys);
		$alone = array();
		$retval['body'] .= '<ul>' . "\n";
		foreach ($keys as $page) {
			if (! empty($nodes[$page]->rels)) {
				$retval['body'] .= $nodes[$page]->toString($nodes, 1, $nodes[$page]->parent_id);
			} else {
				$alone[] = $page;
			}
		}
		$retval['body'] .= '</ul>' . "\n";
		if (! empty($alone)) {
			$retval['body'] .= '<hr />' . "\n" .
				'<p>No link from anywhere in this site.</p>' . "\n";
			$retval['body'] .= '<ul>' . "\n";
			foreach ($alone as $page)
				$retval['body'] .= $nodes[$page]->toString($nodes, 1, $nodes[$page]->parent_id);
			$retval['body'] .= '</ul>' . "\n";
		}
	} else {
		$nodes[$refer]->chain($nodes);
		$retval['body'] .= '<ul>' . "\n" . $nodes[$refer]->toString($nodes) . '</ul>' . "\n";
		$retval['body'] .= '<hr />' . "\n" .
			'<p>Not related from ' . htmlspecialchars($refer) . '</p>' . "\n";
		$keys = array_keys($nodes);
		sort($keys);
		$retval['body'] .= '<ul>' . "\n";
		foreach ($keys as $page) {
			if (! $nodes[$page]->done) {
				$nodes[$page]->chain($nodes);
				$retval['body'] .= $nodes[$page]->toString($nodes, 1, $nodes[$page]->parent_id);
			}
		}
		$retval['body'] .= '</ul>' . "\n";
	}

	// 終了
	return $retval;
}

class MapNode
{
	var $page;
	var $is_page;
	var $link;
	var $id;
	var $rels;
	var $parent_id = 0;
	var $done;
	var $hide_pattern;

	function MapNode($page, $reverse = FALSE)
	{
		global $script, $non_list;

		static $id = 0;

		$this->page    = $page;
		$this->is_page = WikiPage::isPage($page);
//		$this->cache   = CACHE_DIR . encode($page);
		$this->done    = ! $this->is_page;
		$this->link    = make_pagelink($page);
		$this->id      = ++$id;
		$this->hide_pattern = '/' . $non_list . '/';

		$this->rels = $reverse ? $this->ref() : $this->rel();
		$mark       = $reverse ? '' : '<sup>+</sup>';
		//$this->mark = '<a id="rel_' . $this->id . '" href="' . $script . '?plugin=map&amp;refer=' . rawurlencode($this->page) . '">' . $mark . '</a>';
		$this->mark = '<a id="rel_' . $this->id . '" href="' . $script . WikiParam::convQuery('?plugin=map&amp;refer=' . rawurlencode($this->page)) . '">' . $mark . '</a>';
	}

	function hide(& $pages)
	{
		if (! PLUGIN_MAP_SHOW_HIDDEN)
			$pages = array_diff($pages, preg_grep($this->hide_pattern, $pages));
		return $pages;
	}

	function ref()
	{
		$refs = array();
		/*
		$file = $this->cache . '.ref';
		if (file_exists($file)) {
			foreach (file($file) as $line) {
				$ref = explode("\t", $line);
				$refs[] = $ref[0];
			}
			$this->hide($refs);
			sort($refs);
		}*/
		$lines = WikiPage::getPageCacheRef($this->page);
		foreach ($lines as $line) {
			if (trim($line) == '') continue;
			
			$ref = explode("\t", $line);
			$refs[] = $ref[0];
		}
		$this->hide($refs);
		sort($refs);
		return $refs;
	}

	function rel()
	{
		$rels = array();
		/*
		$file = $this->cache . '.rel';
		if (file_exists($file)) {
			$data = file($file);
			$rels = explode("\t", trim($data[0]));
			$this->hide($rels);
			sort($rels);
		}*/
		$lines = WikiPage::getPageCacheRel($this->page);
		if (count($lines) > 0){
			$rels = explode("\t", trim($data[0]));
			$this->hide($rels);
			sort($rels);
		}
		return $rels;
	}

	function chain(& $nodes)
	{
		if ($this->done) return;

		$this->done = TRUE;
		if ($this->parent_id == 0) $this->parent_id = -1;

		foreach ($this->rels as $page) {
			if (! isset($nodes[$page])) $nodes[$page] = new MapNode($page);
			if ($nodes[$page]->parent_id == 0)
				$nodes[$page]->parent_id = $this->id;
		}
		foreach ($this->rels as $page)
			$nodes[$page]->chain($nodes);
	}

	function toString(& $nodes, $level = 1, $parent_id = -1)
	{
		$indent = str_repeat(' ', $level);

		if (! $this->is_page) {
			return $indent . '<li>' . $this->link . '</li>' . "\n";
		} else if ($this->parent_id != $parent_id) {
			return $indent . '<li>' . $this->link .
				'<a href="#rel_' . $this->id . '">...</a></li>' . "\n";
		}
		$retval = $indent . '<li>' . $this->mark . $this->link . "\n";
		if (! empty($this->rels)) {
			$childs = array();
			$level += 2;
			foreach ($this->rels as $page)
				if (isset($nodes[$page]) && $this->parent_id != $nodes[$page]->id)
					$childs[] = $nodes[$page]->toString($nodes, $level, $this->id);

			if (! empty($childs))
				$retval .= $indent . ' <ul>' . "\n" .
					join('', $childs) . $indent . ' </ul>' . "\n";
		}
		$retval .= $indent . '</li>' . "\n";

		return $retval;
	}
}
?>
