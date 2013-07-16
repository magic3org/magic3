<?php
/**
 * link管理ライブラリ
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: link.php 1151 2008-10-29 02:34:45Z fishbone $
 * @link       http://www.magic3.org
 */
// Copyright (C) 2003-2006 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Backlinks / AutoLinks related functions

// ------------------------------------------------------------
// DATA STRUCTURE of *.ref and *.rel files

// CACHE_DIR/encode('foobar').ref
// ---------------------------------
// Page-name1<tab>0<\n>
// Page-name2<tab>1<\n>
// ...
// Page-nameN<tab>0<\n>
//
//	0 = Added when link(s) to 'foobar' added clearly at this page
//	1 = Added when the sentence 'foobar' found from the page
//	    by AutoLink feature

// CACHE_DIR/encode('foobar').rel
// ---------------------------------
// Page-name1<tab>Page-name2<tab> ... <tab>Page-nameN
//
//	List of page-names linked from 'foobar'

// ------------------------------------------------------------

// modified for Magic3 by naoki on 2008/10/15
// データベースから関連ページを得る
function links_get_related_db($page)
{
	//$ref_name = CACHE_DIR . encode($page) . '.ref';
	//if (! file_exists($ref_name)) return array();
	$refPage = WikiPage::getPageCacheRef($page);

	$times = array();
	//foreach (file($ref_name) as $line) {
	foreach ($refPage as $line){
		list($_page) = explode("\t", rtrim($line));
		if (empty($_page)) continue;
		$time = get_filetime($_page);	
		if($time != 0) $times[$_page] = $time;
	}
	return $times;
}

//ページの関連を更新する
// modified for Magic3 by naoki on 2008/10/15
function links_update($page)
{
	if (PKWK_READONLY) return; // Do nothing

	//if (ini_get('safe_mode') == '0') set_time_limit(0);

	//$time = is_page($page, TRUE) ? get_filetime($page) : 0;

	/*$rel_old        = array();
	$rel_file       = CACHE_DIR . encode($page) . '.rel';
	$rel_file_exist = file_exists($rel_file);
	if ($rel_file_exist === TRUE) {
		$lines = file($rel_file);
		unlink($rel_file);
		if (isset($lines[0]))
			$rel_old = explode("\t", rtrim($lines[0]));
	}*/
	$rel_old = explode("\t", WikiPage::getPageCacheRel($page, true));
	
	$rel_new  = array(); // 参照先
	$rel_auto = array(); // オートリンクしている参照先
	$links    = links_get_objects($page, TRUE);
	foreach ($links as $_obj) {
		if (! isset($_obj->type) || $_obj->type != 'pagename' || $_obj->name == $page || $_obj->name == '') continue;

		if (is_a($_obj, 'Link_autolink')) { // 行儀が悪い
			$rel_auto[] = $_obj->name;
		} else {
			$rel_new[]  = $_obj->name;
		}
	}
	$rel_new = array_unique($rel_new);
	
	// autolinkしか向いていないページ
	$rel_auto = array_diff(array_unique($rel_auto), $rel_new);

	// 全ての参照先ページ
	$rel_new = array_merge($rel_new, $rel_auto);

	// .rel:$pageが参照しているページの一覧
	/*if ($time) {
		// ページが存在している
		if (!empty($rel_new)){
    		$fp = fopen($rel_file, 'w')
    				or die_message('cannot write ' . htmlspecialchars($rel_file));
			fputs($fp, join("\t", $rel_new));
			fclose($fp);
		}
	}*/
	// キャッシュを更新
	if (!empty($rel_new)) WikiPage::updatePageCacheRel($page, join("\t", $rel_new));
	
	// .ref:$_pageを参照しているページの一覧
	links_add($page, array_diff($rel_new, $rel_old), $rel_auto);
	links_delete($page, array_diff($rel_old, $rel_new));

	global $WikiName, $autolink, $nowikiname, $search_non_list;

	// $pageが新規作成されたページで、AutoLinkの対象となり得る場合
	/*if ($time && ! $rel_file_exist && $autolink
		&& (preg_match("/^$WikiName$/", $page) ? $nowikiname : strlen($page) >= $autolink))*/
	if ($autolink && (preg_match("/^$WikiName$/", $page) ? $nowikiname : strlen($page) >= $autolink))
	{
		// $pageを参照していそうなページを一斉更新する(おい)
		$search_non_list = 1;
		$pages           = do_search($page, 'AND', TRUE);
		foreach ($pages as $_page) {
			if ($_page != $page)
				links_update($_page);
		}
	}
	//$ref_file = CACHE_DIR . encode($page) . '.ref';

	// $pageが削除されたときに、
	//if (! $time && file_exists($ref_file)) {
	if (!WikiPage::isPage($page)){
		//foreach (file($ref_file) as $line) {
		$refPage = WikiPage::getPageCacheRef($page);
		foreach ($refPage as $line){
			list($ref_page, $ref_auto) = explode("\t", rtrim($line));
			if (empty($ref_page)) continue;

			// $pageをAutoLinkでしか参照していないページを一斉更新する(おいおい)
			if ($ref_auto) links_delete($ref_page, array($page));
		}
	}
}
// modified for Magic3 by naoki on 2008/10/15
// Init link cache (Called from link plugin)
function links_init()
{
//	global $whatsnew;
	if (PKWK_READONLY) return; // Do nothing

	//if (ini_get('safe_mode') == '0') set_time_limit(0);

	// Init database
	/*foreach (get_existfiles(CACHE_DIR, '.ref') as $cache)
		unlink($cache);
	foreach (get_existfiles(CACHE_DIR, '.rel') as $cache)
		unlink($cache);*/
	// キャッシュクリア
	WikiPage::clearCacheRef();
	WikiPage::clearCacheRel();

	$ref   = array(); // 参照元
	foreach (get_existpages() as $page) {
		// ページ名エラーチェック
		$value = trim($page);
		if (empty($value)) continue;
		
		//if ($page == $whatsnew) continue;
		// キャッシュを作成しないページはとばす
		if ($page == WikiConfig::getWhatsnewPage()) continue;

		$rel   = array(); // 参照先
		$links = links_get_objects($page);
		foreach ($links as $_obj) {
			if (! isset($_obj->type) || $_obj->type != 'pagename' || $_obj->name == $page || $_obj->name == '') continue;

			$rel[] = $_obj->name;
			if (! isset($ref[$_obj->name][$page]))
				$ref[$_obj->name][$page] = 1;
			if (! is_a($_obj, 'Link_autolink'))
				$ref[$_obj->name][$page] = 0;
		}
		$rel = array_unique($rel);
		if (!empty($rel)){
			/*$fp = fopen(CACHE_DIR . encode($page) . '.rel', 'w')
				or die_message('cannot write ' . htmlspecialchars(CACHE_DIR . encode($page) . '.rel'));
			fputs($fp, join("\t", $rel));
			fclose($fp);*/
			WikiPage::updatePageCacheRel($page, join("\t", $rel));
		}
	}

	foreach ($ref as $page=>$arr) {
		/*$fp  = fopen(CACHE_DIR . encode($page) . '.ref', 'w')
			or die_message('cannot write ' . htmlspecialchars(CACHE_DIR . encode($page) . '.ref'));
		foreach ($arr as $ref_page=>$ref_auto)
			fputs($fp, $ref_page . "\t" . $ref_auto . "\n");
		fclose($fp);*/
		$updateData = '';
		foreach ($arr as $ref_page=>$ref_auto){
			$updateData .= $ref_page . "\t" . $ref_auto . "\n";
		}
		WikiPage::updatePageCacheRef($page, $updateData);
	}
}
// modified for Magic3 by naoki on 2008/10/15
function links_add($page, $add, $rel_auto)
{
	if (PKWK_READONLY) return; // Do nothing

	$rel_auto = array_flip($rel_auto);
	
	foreach ($add as $_page) {
		// ページ名エラーチェック
		$value = trim($_page);
		if (empty($value)) continue;
		
		$all_auto = isset($rel_auto[$_page]);
		$is_page  = is_page($_page);
		$ref      = $page . "\t" . ($all_auto ? 1 : 0) . "\n";

		/*$ref_file = CACHE_DIR . encode($_page) . '.ref';
		if (file_exists($ref_file)) {
			foreach (file($ref_file) as $line) {
				list($ref_page, $ref_auto) = explode("\t", rtrim($line));
				if (! $ref_auto) $all_auto = FALSE;
				if ($ref_page != $page) $ref .= $line;
			}
			unlink($ref_file);
		}*/
		$refPage = WikiPage::getPageCacheRef($_page);
		foreach ($refPage as $line){
			list($ref_page, $ref_auto) = explode("\t", rtrim($line));
			if (empty($ref_page)) continue;
			
			if (! $ref_auto) $all_auto = FALSE;
			if ($ref_page != $page) $ref .= $line;
		}
		/*if ($is_page || ! $all_auto) {
			$fp = fopen($ref_file, 'w')
				 or die_message('cannot write ' . htmlspecialchars($ref_file));
			fputs($fp, $ref);
			fclose($fp);
		}*/
		if ($is_page || !$all_auto){
			WikiPage::updatePageCacheRef($_page, $ref);
		} else {
			WikiPage::updatePageCacheRef($_page, '');
		}
	}
}
// modified for Magic3 by naoki on 2008/10/15
function links_delete($page, $del)
{
	if (PKWK_READONLY) return; // Do nothing

	foreach ($del as $_page) {
		// ページ名エラーチェック
		$value = trim($_page);
		if (empty($value)) continue;
		
		//$ref_file = CACHE_DIR . encode($_page) . '.ref';
		//if (! file_exists($ref_file)) continue;
		$refPage = WikiPage::getPageCacheRef($_page);
		if (empty($refPage)) continue;

		$all_auto = TRUE;
		$is_page = is_page($_page);

		$ref = '';
		//foreach (file($ref_file) as $line) {
		foreach ($refPage as $line) {
			list($ref_page, $ref_auto) = explode("\t", rtrim($line));
			if (empty($ref_page)) continue;
			
			if ($ref_page != $page) {
				if (! $ref_auto) $all_auto = FALSE;
				$ref .= $line;
			}
		}
		/*unlink($ref_file);
		if (($is_page || ! $all_auto) && $ref != '') {
			$fp = fopen($ref_file, 'w')
				or die_message('cannot write ' . htmlspecialchars($ref_file));
			fputs($fp, $ref);
			fclose($fp);
		}*/
		if (($is_page || !$all_auto) && $ref != ''){
			WikiPage::updatePageCacheRef($_page, $ref);
		} else {
			WikiPage::updatePageCacheRef($_page, '');
		}
	}
}

function links_get_objects($page, $refresh = FALSE)
{
	static $obj;

	if (! isset($obj) || $refresh)
		$obj = new InlineConverter(NULL, array('note'));

	$result = $obj->get_objects(join('', preg_grep('/^(?!\/\/|\s)./', get_source($page))), $page);
	return $result;
}
?>
