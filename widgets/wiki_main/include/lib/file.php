<?php
/**
 * ファイル管理ライブラリ
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2002-2006 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// 運用ログメッセージ
define('LOG_MSG_ADD_CONTENT',		'Wikiコンテンツを追加しました。タイトル: %s');
define('LOG_MSG_UPDATE_CONTENT',	'Wikiコンテンツを更新しました。タイトル: %s');
define('LOG_MSG_DEL_CONTENT',		'Wikiコンテンツを削除しました。タイトル: %s');
	
/**
 * ページのソースコードを取得
 *
 * @param string $page		Wikiページ名
 * @param bool   $join		行を連結するかどうか
 * @param int    $serial	取得したレコードのシリアル番号
 * @return string,array		ソースコード。存在しない場合は空文字列または空配列が返る。
 */
function get_source($page, $join = false, &$serial = null)
{
	$result = $join ? '' : array();
	if (is_page($page)){
		// 改行コード(CR)を削除
		$result = str_replace("\r", '', WikiPage::getPage($page, $join, $serial));
	}
	return $result;
}
// Get last-modified filetime of the page
function get_filetime($page)
{
	// ##### ページの更新日時はWikiPage::$availablePagesに含める? #####
	static $fileTimes = array();
	
	$fileTime = $fileTimes[$page];
	if (!isset($fileTime)){
		$fileTime = is_page($page) ? WikiPage::getPageTime($page) : 0;
		$fileTimes[$page] = $fileTime;
	}
	return $fileTime;
}
// Put a data(wiki text) into a physical file(diff, backup, text)
// modified for Magic3 by naoki on 2008/10/15
function page_write($page, $postdata, $notimestamp = FALSE)
{
	global $notify, $notify_diff_only, $notify_subject;
	global $whatsdeleted, $maxshow_deleted;

	$postdata = make_str_rules($postdata);

	// diffデータを作成
	$isExistsPage = is_page($page);			// ページが存在するか
	$oldpostdata = $isExistsPage ? get_source($page, true) : '';
	$diffdata    = do_diff($oldpostdata, $postdata);

	// ページdiffデータ更新
	if ($diffdata != " \n"){
		$diffdata = rtrim(preg_replace('/' . "\r" . '/', '', $diffdata)) . "\n";
		WikiPage::updatePageDiff($page, $diffdata);
		
		if ($notify){
			if ($notify_diff_only) $diffdata = preg_replace('/^[^-+].*\n/m', '', $diffdata);
			$footer['ACTION'] = 'Page update';
			$footer['PAGE']   = $page;
			$footer['URI']    = get_script_uri() . WikiParam::convQuery('?' . rawurlencode($page));
			$footer['USER_AGENT']  = TRUE;
			$footer['REMOTE_ADDR'] = TRUE;
			pkwk_mail_notify($notify_subject, $diffdata, $footer) or die('pkwk_mail_notify(): Failed');
		}
	}

	// Create backup
	//make_backup($page, $postdata == ''); // Is $postdata null?

	// Create wiki text
	if ($postdata === ''){		// データが空のときはページを削除
		// ページデータとページに関するデータを削除
		$ret = WikiPage::deletePage($page, $delSerial, true/*ページ一覧更新*/);
		if ($ret){
			// 最終削除ページに記録
			add_recentdeleted($page, $delSerial, $maxshow_deleted);
			
			// 運用ログを残す
			$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_WIKI,
									M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $page,
									M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
			_writeUserInfoEvent(__METHOD__, sprintf(LOG_MSG_DEL_CONTENT, $page), 2402, 'ID=' . $page, $eventParam);
		}
		
		// ##### 添付ファイル削除 #####
		// アップロード用のディレクトリ内のファイルリストを取得
		$dir = opendir(UPLOAD_DIR) or die('directory ' . UPLOAD_DIR . ' is not exist or not readable.');

		$delFiles = array();
		$page_pattern = ($page == '') ? '(?:[0-9A-F]{2})+' : preg_quote(encode($page), '/');
		$pattern = "/^({$page_pattern})_((?:[0-9A-F]{2})+)$/";
		$matches = array();
		while ($file = readdir($dir)){
			if (!preg_match($pattern, $file, $matches)) continue;
			$delFiles[] = UPLOAD_DIR . $file;
		}
		closedir($dir);
		
		// 添付ファイル削除
		foreach ($delFiles as $filename){
			@unlink($filename);
		}
		
		// 最終更新情報(最終更新データ、最終更新ページ)を更新
		lastmodified_add($whatsdeleted, $page);
	} else {		// 更新データがあるときはデータを更新
		$postdata = rtrim(preg_replace('/' . "\r" . '/', '', $postdata)) . "\n";		// 改行付加
		$ret = WikiPage::updatePage($page, $postdata, $notimestamp, true/*ページ一覧更新*/);
		if ($ret){
			// 運用ログを残す
			$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_WIKI,
									M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $page,
									M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
			if ($isExistsPage){			// 更新の場合
				_writeUserInfoEvent(__METHOD__, sprintf(LOG_MSG_UPDATE_CONTENT, $page), 2401, 'ID=' . $page, $eventParam);
			} else {			// 新規の場合
				_writeUserInfoEvent(__METHOD__, sprintf(LOG_MSG_ADD_CONTENT, $page), 2400, 'ID=' . $page, $eventParam);
			}
		}
		
		// 最終更新情報(最終更新データ、最終更新ページ)を更新
		if ($notimestamp === FALSE) lastmodified_add($page);		// 更新日時を更新する場合
	}
	// キャッシュクリア
	//is_page($page, TRUE); // Clear is_page() cache
	
	// リンクを更新
	links_update($page);
}

// Modify original text with user-defined / system-defined rules
function make_str_rules($source)
{
	global $str_rules, $fixed_heading_anchor;

	$lines = explode("\n", $source);
	$count = count($lines);

	$modify    = TRUE;
	$multiline = 0;
	$matches   = array();
	for ($i = 0; $i < $count; $i++) {
		$line = &$lines[$i]; // Modify directly

		// Ignore null string and preformatted texts
		if ($line == '' || $line{0} == ' ' || $line{0} == "\t") continue;

		// Modify this line?
		if ($modify) {
			if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK &&
			    $multiline == 0 &&
			    preg_match('/#[^{]*(\{\{+)\s*$/', $line, $matches)) {
			    	// Multiline convert plugin start
				$modify    = FALSE;
				$multiline = strlen($matches[1]); // Set specific number
			}
		} else {
			if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK &&
			    $multiline != 0 &&
			    preg_match('/^\}{' . $multiline . '}\s*$/', $line)) {
			    	// Multiline convert plugin end
				$modify    = TRUE;
				$multiline = 0;
			}
		}
		if ($modify === FALSE) continue;

		// Replace with $str_rules
		foreach ($str_rules as $pattern => $replacement)
			$line = preg_replace('/' . $pattern . '/', $replacement, $line);
		
		// Adding fixed anchor into headings
		if ($fixed_heading_anchor &&
		    preg_match('/^(\*{1,3}.*?)(?:\[#([A-Za-z][\w-]*)\]\s*)?$/', $line, $matches) &&
		    (! isset($matches[2]) || $matches[2] == '')) {
			// Generate unique id
			$anchor = generate_fixed_heading_anchor_id($matches[1]);
			$line = rtrim($matches[1]) . ' [#' . $anchor . ']';
		}
	}

	// Multiline part has no stopper
	if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK &&
	    $modify === FALSE && $multiline != 0)
		$lines[] = str_repeat('}', $multiline);

	return implode("\n", $lines);
}

// Generate ID
function generate_fixed_heading_anchor_id($seed)
{
	// A random alphabetic letter + 7 letters of random strings from md()
	return chr(mt_rand(ord('a'), ord('z'))) . substr(md5(uniqid(substr($seed, 0, 100), TRUE)), mt_rand(0, 24), 7);
}
// Update RecentDeleted
function add_recentdeleted($deletePage, $delSerial, $limit = 0)
{
	if ($limit == 0 || in_array($deletePage, WikiConfig::getNoLinkPages()) || check_non_list($deletePage)) return;

	// ユーザ名を表示するかどうか
	$showUserName = WikiConfig::isShowUserName();

	// 削除ページの情報を取得
	$recent_pages = array();
	$lines = WikiPage::getCacheRecentDeleted();
	$lineCount = count($lines);
	for ($i = 0; $i < $lineCount; $i++){
		// 最終更新データの行を解析
		list($time, $page, $userName) = explode("\t", rtrim($lines[$i]));
		if (empty($page)) continue;			// ページ名が見つからないときは取得しない
		
		$newObj = new stdClass;
		$newObj->time		= $time;// コンテンツ更新日時
		$newObj->userName	= $userName;								// 更新ユーザ名
		$recent_pages[$page] = $newObj;
	}
	
	// 既に削除済みページとして登録されていれば削除
	if (isset($recent_pages[$deletePage])) unset($recent_pages[$deletePage]);

	// 先頭に削除したページの情報を追加
	$pageInfoObj = WikiPage::getDeletedPageInfoBySerial($delSerial);
	if (isset($pageInfoObj)) $recent_pages = array($deletePage => $pageInfoObj) + $recent_pages;

	// 行数の制限
	$recent_pages = array_splice($recent_pages, 0, $limit);

	// 最終削除データを更新
	$newData = '';
	foreach ($recent_pages as $page => $pageInfoObj){
		if ($showUserName){		// ユーザ名を表示する場合
			$newData .= $pageInfoObj->time . "\t" . $page . "\t" . $pageInfoObj->userName ."\n";
		} else {
			$newData .= $pageInfoObj->time . "\t" . $page . "\n";
		}
	}
	WikiPage::updateCacheRecentDeleted($newData);
	
	// 最終削除ページを更新
	$newData = '';
	foreach ($recent_pages as $page => $pageInfoObj){
		// 文字エスケープ必要なし?
		if ($showUserName){		// ユーザ名を表示する場合
			$newData .= '-' . format_date($pageInfoObj->time) . ' - [[' . $page . ']] -- [[' . $pageInfoObj->userName . "]]\n";
		} else {
			$newData .= '-' . format_date($pageInfoObj->time) . ' - [[' . $page . ']]' . "\n";
		}
	}
	$newData .= '#norelated' . "\n";
	WikiPage::updatePage(WikiConfig::getWhatsdeletedPage(), $newData, false/*更新日時維持しない*/, true/*ページ一覧更新*/);
}

/**
 * 最終更新情報(最終更新データ、最終更新ページ)を更新
 *
 * @param string $update			更新したページ
 * @param string $remove			削除したページ
 * @return							なし
 */
function lastmodified_add($update = '', $remove = '')
{
	global $maxshow, $autolink;

	// AutoLink implimentation needs everything, for now
	if ($autolink) {
		put_lastmodified(); // Try to (re)create ALL
		return;
	}

	if (($update == '' || check_non_list($update)) && $remove == '') return; // No need

	// ##### 以下はページ削除の場合のみ実行される? #####
	// 最終更新データを取得
	$lines = WikiPage::getCacheRecentChanges();
	if (empty($lines)){
		put_lastmodified();			// 最終更新データを再作成
		return;
	}

	// ユーザ名を表示するかどうか
	$showUserName = WikiConfig::isShowUserName();
	
	// Read (keep the order of the lines)
	//$recent_pages = $matches = array();
	$recent_pages = array();

	$lineCount = $maxshow < count($lines) ? $maxshow : count($lines);
	for ($i = 0; $i < $lineCount; $i++){
		// 最終更新データの行を解析
		list($time, $page, $userName) = explode("\t", rtrim($lines[$i]));
//		$recent_pages[$page] = $time;
/*		if (preg_match('/^([0-9]+)\t(.+)/', $lines[$i], $matches)){
			$recent_pages[$matches[2]] = $matches[1];
		}*/

		$newObj = new stdClass;
		$newObj->time		= $time;// コンテンツ更新日時
		$newObj->userName	= $userName;								// 更新ユーザ名
		$recent_pages[$page] = $newObj;
	}

	// Remove if it exists inside
	if (isset($recent_pages[$update])) unset($recent_pages[$update]);
	if (isset($recent_pages[$remove])) unset($recent_pages[$remove]);

	// 先頭に更新したページの情報を追加
//	$recent_pages = array($update => get_filetime($update)) + $recent_pages;
	$pageInfoObj = WikiPage::getPageInfo($update);
	if (isset($pageInfoObj)) $recent_pages = array($update => $pageInfoObj) + $recent_pages;

	// Check
	$abort = count($recent_pages) < $maxshow;

	if ($abort){
		put_lastmodified(); // Try to (re)create ALL
	} else {		// 最大行数を超えた場合は最新更新データを更新
		$newData = '';
/*		foreach ($recent_pages as $_page => $time){
			$newData .= $time . "\t" . $_page . "\n";
		}*/
		foreach ($recent_pages as $_page => $pageInfoObj){
			if ($showUserName){		// ユーザ名を表示する場合
				$newData .= $pageInfoObj->time . "\t" . $_page . "\t" . $pageInfoObj->userName ."\n";
			} else {
				$newData .= $pageInfoObj->time . "\t" . $_page . "\n";
			}
		}
		WikiPage::updateCacheRecentChanges($newData);
		
		// 最終更新ページを更新
		$recent_pages = array_splice($recent_pages, 0, $maxshow);
	
		$newData = '';
/*		foreach ($recent_pages as $_page => $time){
			// 文字エスケープ必要なし?
		//	$newData .= '-' . htmlspecialchars(format_date($time)) . ' - ' . '[[' . htmlspecialchars($_page) . ']]' . "\n";
			$newData .= '-' . format_date($time) . ' - ' . '[[' . $_page . ']]' . "\n";
		}*/
		foreach ($recent_pages as $_page => $pageInfoObj){
			// 文字エスケープ必要なし?
			if ($showUserName){		// ユーザ名を表示する場合
				$newData .= '-' . format_date($pageInfoObj->time) . ' - ' . '[[' . $_page . ']] -- [[' . $pageInfoObj->userName . "]]\n";
			} else {
				$newData .= '-' . format_date($pageInfoObj->time) . ' - ' . '[[' . $_page . ']]' . "\n";
			}
		}
		$newData .= '#norelated' . "\n";
		WikiPage::updatePage(WikiConfig::getWhatsnewPage(), $newData, false/*更新日時維持しない*/, true/*ページ一覧更新*/);
	}
}

/**
 * 最終更新情報(最終更新データ、最終更新ページ)を更新
 *
 * @return				なし
 */
function put_lastmodified()
{
	global $maxshow, $whatsnew, $autolink;

	// ユーザ名を表示するかどうか
	$showUserName = WikiConfig::isShowUserName();
/*
	// Get WHOLE page list
	$pages = get_existpages();

	// Check ALL filetime
	$recent_pages = array();
	foreach($pages as $page){
		if ($page == $whatsnew || check_non_list($page)) continue;
		
		// ページの更新日時、更新ユーザを取得
//		$recent_pages[$page] = get_filetime($page);
		$recent_pages[$page] = WikiPage::getPageInfo($page);		// ページ情報オブジェクト取得
	}

	// Sort decending order of last-modification date
	arsort($recent_pages, SORT_NUMERIC);*/

/*	// Cut unused lines
	// BugTrack2/179: array_splice() will break integer keys in hashtable
	$count   = $maxshow;
	$_recent = array();
	foreach($recent_pages as $key=>$value) {
		unset($recent_pages[$key]);
		$_recent[$key] = $value;
		if (--$count < 1) break;
	}
	$recent_pages = $_recent;*/

	// 最新更新分からページの更新日時、更新ユーザの情報を取得
	$recent_pages = WikiPage::getLastPageInfo($maxshow, $whatsnew);

	// 最終更新データを更新
	$newData = '';
/*	foreach ($recent_pages as $page => $time){
		$newData .= $time . "\t" . $page . "\n";
	}*/
	foreach ($recent_pages as $page => $pageInfoObj){
		if ($showUserName){		// ユーザ名を表示する場合
			$newData .= $pageInfoObj->time . "\t" . $page . "\t" . $pageInfoObj->userName ."\n";
		} else {
			$newData .= $pageInfoObj->time . "\t" . $page . "\n";
		}
	}
	WikiPage::updateCacheRecentChanges($newData);
	
	// 最終更新ページを更新
	$newData = '';
/*	foreach (array_keys($recent_pages) as $page){
		$time      = $recent_pages[$page];
//		$s_lastmod = htmlspecialchars(format_date($time));
//		$s_page    = htmlspecialchars($page);
//		$newData .= '-' . $s_lastmod . ' - [[' . $s_page . ']]' . "\n";
		// 文字エスケープ必要なし?
		$newData .= '-' . format_date($time) . ' - [[' . $page . ']]' . "\n";
	}*/
//	foreach (array_keys($recent_pages) as $page){
//		$pageInfoObj = $recent_pages[$page];
	foreach ($recent_pages as $page => $pageInfoObj){
		// 文字エスケープ必要なし?
		if ($showUserName){		// ユーザ名を表示する場合
			$newData .= '-' . format_date($pageInfoObj->time) . ' - [[' . $page . ']] -- [[' . $pageInfoObj->userName . "]]\n";
		} else {
			$newData .= '-' . format_date($pageInfoObj->time) . ' - [[' . $page . ']]' . "\n";
		}
	}
	$newData .= '#norelated' . "\n";
	WikiPage::updatePage(WikiConfig::getWhatsnewPage(), $newData, false/*更新日時維持しない*/, true/*ページ一覧更新*/);

	// For AutoLink
	if ($autolink) {
		list($pattern, $pattern_a, $forceignorelist) = get_autolink_pattern($pages);

		// 自動リンクデータを更新
		$newData = '';
		$newData .= $pattern   . "\n";
		$newData .= $pattern_a . "\n";
		$newData .= join("\t", $forceignorelist) . "\n";
		WikiPage::updateCacheAutolink($newData);
	}
}

// Get elapsed date of the page
function get_pg_passage($page, $sw = TRUE)
{
	global $show_passage;
	if (! $show_passage) return '';

	$time = get_filetime($page);
	$pg_passage = ($time != 0) ? get_passage($time) : '';

	return $sw ? '<small>' . $pg_passage . '</small>' : ' ' . $pg_passage;
}

// Last-Modified header
function header_lastmod($page = NULL)
{
	global $lastmod;

	if ($lastmod && is_page($page)) {
		pkwk_headers_sent();
		header('Last-Modified: ' .
			date('D, d M Y H:i:s', get_filetime($page)) . ' GMT');
	}
}

// Get a page list of this wiki
function get_existpages()
{
	$pages = WikiPage::getPages();
	return $pages;
}

// Get PageReading(pronounce-annotated) data in an array()
function get_readings()
{
	global $pagereading_enable, $pagereading_kanji2kana_converter;
	global $pagereading_kanji2kana_encoding, $pagereading_chasen_path;
	global $pagereading_kakasi_path, $pagereading_config_page;
	global $pagereading_config_dict;

	$pages = get_existpages();

	$readings = array();
	foreach ($pages as $page) 
		$readings[$page] = '';

	$deletedPage = FALSE;
	$matches = array();
	foreach (get_source($pagereading_config_page) as $line) {
		$line = chop($line);
		if(preg_match('/^-\[\[([^]]+)\]\]\s+(.+)$/', $line, $matches)) {
			if(isset($readings[$matches[1]])) {
				// This page is not clear how to be pronounced
				$readings[$matches[1]] = $matches[2];
			} else {
				// This page seems deleted
				$deletedPage = TRUE;
			}
		}
	}

	// If enabled ChaSen/KAKASI execution
	if($pagereading_enable) {

		// Check there's non-clear-pronouncing page
		$unknownPage = FALSE;
		foreach ($readings as $page => $reading) {
			if($reading == '') {
				$unknownPage = TRUE;
				break;
			}
		}

		// Execute ChaSen/KAKASI, and get annotation
		if($unknownPage) {
			switch(strtolower($pagereading_kanji2kana_converter)) {
			case 'chasen':
				if(! file_exists($pagereading_chasen_path))
					die_message('ChaSen not found: ' . $pagereading_chasen_path);

				$tmpfname = tempnam(realpath(CACHE_DIR), 'PageReading');
				$fp = fopen($tmpfname, 'w') or
					die_message('Cannot write temporary file "' . $tmpfname . '".' . "\n");
				foreach ($readings as $page => $reading) {
					if($reading != '') continue;
					fputs($fp, mb_convert_encoding($page . "\n",
						$pagereading_kanji2kana_encoding, SOURCE_ENCODING));
				}
				fclose($fp);

				$chasen = "$pagereading_chasen_path -F %y $tmpfname";
				$fp     = popen($chasen, 'r');
				if($fp === FALSE) {
					unlink($tmpfname);
					die_message('ChaSen execution failed: ' . $chasen);
				}
				foreach ($readings as $page => $reading) {
					if($reading != '') continue;

					$line = fgets($fp);
					$line = mb_convert_encoding($line, SOURCE_ENCODING,
						$pagereading_kanji2kana_encoding);
					$line = chop($line);
					$readings[$page] = $line;
				}
				pclose($fp);

				unlink($tmpfname) or
					die_message('Temporary file can not be removed: ' . $tmpfname);
				break;

			case 'kakasi':	/*FALLTHROUGH*/
			case 'kakashi':
				if(! file_exists($pagereading_kakasi_path))
					die_message('KAKASI not found: ' . $pagereading_kakasi_path);

				$tmpfname = tempnam(realpath(CACHE_DIR), 'PageReading');
				$fp       = fopen($tmpfname, 'w') or
					die_message('Cannot write temporary file "' . $tmpfname . '".' . "\n");
				foreach ($readings as $page => $reading) {
					if($reading != '') continue;
					fputs($fp, mb_convert_encoding($page . "\n",
						$pagereading_kanji2kana_encoding, SOURCE_ENCODING));
				}
				fclose($fp);

				$kakasi = "$pagereading_kakasi_path -kK -HK -JK < $tmpfname";
				$fp     = popen($kakasi, 'r');
				if($fp === FALSE) {
					unlink($tmpfname);
					die_message('KAKASI execution failed: ' . $kakasi);
				}

				foreach ($readings as $page => $reading) {
					if($reading != '') continue;

					$line = fgets($fp);
					$line = mb_convert_encoding($line, SOURCE_ENCODING,
						$pagereading_kanji2kana_encoding);
					$line = chop($line);
					$readings[$page] = $line;
				}
				pclose($fp);

				unlink($tmpfname) or
					die_message('Temporary file can not be removed: ' . $tmpfname);
				break;

			case 'none':
				$patterns = $replacements = $matches = array();
				foreach (get_source($pagereading_config_dict) as $line) {
					$line = chop($line);
					if(preg_match('|^ /([^/]+)/,\s*(.+)$|', $line, $matches)) {
						$patterns[]     = $matches[1];
						$replacements[] = $matches[2];
					}
				}
				foreach ($readings as $page => $reading) {
					if($reading != '') continue;

					$readings[$page] = $page;
					foreach ($patterns as $no => $pattern)
						$readings[$page] = mb_convert_kana(mb_ereg_replace($pattern,
							$replacements[$no], $readings[$page]), 'aKCV');
				}
				break;

			default:
				die_message('Unknown kanji-kana converter: ' . $pagereading_kanji2kana_converter . '.');
				break;
			}
		}

		if($unknownPage || $deletedPage) {

			asort($readings); // Sort by pronouncing(alphabetical/reading) order
			$body = '';
			foreach ($readings as $page => $reading)
				$body .= '-[[' . $page . ']] ' . $reading . "\n";

			page_write($pagereading_config_page, $body);
		}
	}

	// Pages that are not prounouncing-clear, return pagenames of themselves
	foreach ($pages as $page) {
		if($readings[$page] == '')
			$readings[$page] = $page;
	}

	return $readings;
}

// Get a list of encoded files (must specify a directory and a suffix)
/*function get_existfiles($dir, $ext)
{
	$pattern = '/^(?:[0-9A-F]{2})+' . preg_quote($ext, '/') . '$/';
	$aryret = array();
	$dp = @opendir($dir) or die_message($dir . ' is not found or not readable.');
	while ($file = readdir($dp))
		if (preg_match($pattern, $file))
			$aryret[] = $dir . $file;
	closedir($dp);
	return $aryret;
}*/

// Get a list of related pages of the page
function links_get_related($page)
{
	global $related;
	static $links = array();

	if (isset($links[$page])) return $links[$page];

	// If possible, merge related pages generated by make_link()
	$links[$page] = ($page == WikiParam::getPage()) ? $related : array();

	// Get repated pages from DB
	$links[$page] += links_get_related_db(WikiParam::getPage());

	return $links[$page];
}
/**
 * ユーザ操作運用ログ出力とイベント処理
 *
 * 以下の状況で運用ログメッセージを出力するためのインターフェイス
 * ユーザの通常の操作で記録すべきもの
 * 例) コンテンツの更新等
 *
 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
 * @param string $msg   	メッセージ
 * @param int    $code		メッセージコード
 * @param string $msgExt   	詳細メッセージ
 * @param array  $eventParam	イベント処理用パラメータ(ログに格納しない)
 * @return なし
 */
function _writeUserInfoEvent($method, $msg, $code = 0, $msgExt = '', $eventParam = array())
{
	global $gOpeLogManager;
		
	$gOpeLogManager->writeUserInfo($method, $msg, $code, $msgExt, '', '', false, $eventParam);
}
?>
