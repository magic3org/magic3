<?php
/**
 * 初期化ライブラリ
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
// Init PukiWiki here
// PukiWiki用グローバル変数		// add for Magic3 by naoki on 2008/9/28
global $related;	// Related pages
global $head_tags;	// XHTML tags in <head></head>
global $line_rules;
global $WikiName;
global $BracketName;
global $InterWikiName;
global $NotePattern;
global $weeklabels;
global $script;
global $now;
global $datetime_rules;
global $str_rules;

// PukiWiki version / Copyright / Licence
define('S_VERSION', '1.4.7');
define('S_COPYRIGHT',
	'<strong>PukiWiki ' . S_VERSION . '</strong>' .
	' Copyright &copy; 2001-2006' .
	' <a href="http://pukiwiki.sourceforge.jp/">PukiWiki Developers Team</a>.' .
	' License is <a href="http://www.gnu.org/licenses/gpl.html">GPL</a>.<br />' .
	' Based on "PukiWiki" 1.3 by <a href="http://factage.com/yu-ji/">yu-ji</a>'
);

/////////////////////////////////////////////////
// Init server variables

foreach (array('SCRIPT_NAME', 'SERVER_ADMIN', 'SERVER_NAME',
	'SERVER_PORT', 'SERVER_SOFTWARE') as $key) {
	define($key, isset($_SERVER[$key]) ? $_SERVER[$key] : '');
// removed for Magic3 by naoki on 2008/9/26
//	unset(${$key}, $_SERVER[$key], $HTTP_SERVER_VARS[$key]);
}

/////////////////////////////////////////////////
// Init grobal variables
$related      = array();	// Related pages
$head_tags    = array();	// XHTML tags in <head></head>

/////////////////////////////////////////////////
// Time settings

define('LOCALZONE', date('Z'));
define('UTIME', time() - LOCALZONE);
define('MUTIME', getmicrotime());

/////////////////////////////////////////////////
// Require INI_FILE

//define('INI_FILE',  DATA_HOME . 'pukiwiki.ini.php');
// modified for Magic3 by naoki on 2008/9/22
define('INI_FILE',  dirname(dirname(__FILE__)) . '/conf/pukiwiki.ini.php');
$die = '';
if (! file_exists(INI_FILE) || ! is_readable(INI_FILE)) {
	$die .= 'File is not found. (INI_FILE)' . "\n";
} else {
	require_once(INI_FILE);
}
if ($die) die_message(nl2br("\n\n" . $die));

/////////////////////////////////////////////////
// INI_FILE: LANG に基づくエンコーディング設定

// MB_LANGUAGE: mb_language (for mbstring extension)
//   'uni'(means UTF-8), 'English', or 'Japanese'
// SOURCE_ENCODING: Internal content encoding (for mbstring extension)
//   'UTF-8', 'ASCII', or 'EUC-JP'
// CONTENT_CHARSET: Internal content encoding = Output content charset (for skin)
//   'UTF-8', 'iso-8859-1', 'EUC-JP' or ...

switch (LANG){
case 'en': define('MB_LANGUAGE', 'English' ); break;
case 'ja': define('MB_LANGUAGE', 'Japanese'); break;
case 'ko': define('MB_LANGUAGE', 'Korean'  ); break;
	// See BugTrack2/13 for all hack about Korean support,
	// and give us your report!
default: die_message('No such language "' . LANG . '"'); break;
}

// エンコーディングの設定
define('SOURCE_ENCODING', 'UTF-8');
define('CONTENT_CHARSET', 'UTF-8');

/////////////////////////////////////////////////
// INI_FILE: Require LANG_FILE
//define('LANG_FILE_HINT', DATA_HOME . LANG . '.lng.php');	// For encoding hint
//define('LANG_FILE',      DATA_HOME . UI_LANG . '.lng.php');	// For UI resource
// modified for Magic3 by naoki on 2008/9/22
define('LANG_FILE_HINT', dirname(dirname(__FILE__)) . '/lang/' . LANG . '.lng.php');	// For encoding hint
define('LANG_FILE',      dirname(dirname(__FILE__)) . '/lang/' . UI_LANG . '.lng.php');	// For UI resource
$die = '';
foreach (array('LANG_FILE_HINT', 'LANG_FILE') as $langfile) {
	if (! file_exists(constant($langfile)) || ! is_readable(constant($langfile))) {
		$die .= 'File is not found or not readable. (' . $langfile . ')' . "\n";
	} else {
		require_once(constant($langfile));
	}
}
if ($die) die_message(nl2br("\n\n" . $die));

/////////////////////////////////////////////////
// LANG_FILE: Init severn days of the week

$weeklabels = $_msg_week;

/////////////////////////////////////////////////
// INI_FILE: Init $script
if (isset($script)) {
	get_script_uri($script); // Init manually
} else {
	$script = get_script_uri(); // Init automatically
}

/////////////////////////////////////////////////
// INI_FILE: $agents:  UserAgentの識別
/*
$ua = 'HTTP_USER_AGENT';
$user_agent = $matches = array();

$user_agent['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
unset(${$ua}, $_SERVER[$ua], $HTTP_SERVER_VARS[$ua], $ua);	// safety

foreach ($agents as $agent) {
	if (preg_match($agent['pattern'], $user_agent['agent'], $matches)) {
		$user_agent['profile'] = isset($agent['profile']) ? $agent['profile'] : '';
		$user_agent['name']    = isset($matches[1]) ? $matches[1] : '';	// device or browser name
		$user_agent['vers']    = isset($matches[2]) ? $matches[2] : ''; // 's version
		break;
	}
}
unset($agents, $matches);

// Profile-related init and setting
define('UA_PROFILE', isset($user_agent['profile']) ? $user_agent['profile'] : '');
//define('UA_INI_FILE', DATA_HOME . UA_PROFILE . '.ini.php');
// modified for Magic3 by naoki on 2008/9/22
define('UA_INI_FILE', dirname(dirname(__FILE__)) . '/conf/' . UA_PROFILE . '.ini.php');
if (! file_exists(UA_INI_FILE) || ! is_readable(UA_INI_FILE)) {
	die_message('UA_INI_FILE for "' . UA_PROFILE . '" not found.');
} else {
	require_once(UA_INI_FILE); // Also manually
}

define('UA_NAME', isset($user_agent['name']) ? $user_agent['name'] : '');
define('UA_VERS', isset($user_agent['vers']) ? $user_agent['vers'] : '');
unset($user_agent);	// Unset after reading UA_INI_FILE
*/
require_once(dirname(dirname(__FILE__)) . '/conf/default.ini.php');

/////////////////////////////////////////////////
// ディレクトリのチェック

$die = '';
/*
remove temporary by naoki
foreach(array('DATA_DIR', 'DIFF_DIR', 'BACKUP_DIR', 'CACHE_DIR') as $dir){
	if (! is_writable(constant($dir)))
		$die .= 'Directory is not found or not writable (' . $dir . ')' . "\n";
}*/

// 設定ファイルの変数チェック
$temp = '';
foreach(array('rss_max', 'page_title', 'note_hr', 'related_link', 'show_passage',
	'rule_related_str', 'load_template_func') as $var){
	if (! isset(${$var})) $temp .= '$' . $var . "\n";
}
if ($temp) {
	if ($die) $die .= "\n";	// A breath
	$die .= 'Variable(s) not found: (Maybe the old *.ini.php?)' . "\n" . $temp;
}

$temp = '';
foreach(array('LANG', 'PLUGIN_DIR') as $def){
	if (! defined($def)) $temp .= $def . "\n";
}
if ($temp) {
	if ($die) $die .= "\n";	// A breath
	$die .= 'Define(s) not found: (Maybe the old *.ini.php?)' . "\n" . $temp;
}

if($die) die_message(nl2br("\n\n" . $die));
unset($die, $temp);

// ### 必須のページが存在しなければ、空のページを作成する ###
foreach (array(WikiConfig::getDefaultPage()) as $page){
	// 初期化前にDBの内容を必ず確認する
	if (!WikiPage::isExistsPage($page)) WikiPage::initPage($page, '', true/*ページ一覧更新*/);		// ページ初期化
}
	
// 入力チェック
if (!WikiParam::checkParam()) die('Using both cmd= and plugin= is not allowed');

/////////////////////////////////////////////////
// 初期設定($WikiName,$BracketNameなど)
// $WikiName = '[A-Z][a-z]+(?:[A-Z][a-z]+)+';
// $WikiName = '\b[A-Z][a-z]+(?:[A-Z][a-z]+)+\b';
// $WikiName = '(?<![[:alnum:]])(?:[[:upper:]][[:lower:]]+){2,}(?![[:alnum:]])';
// $WikiName = '(?<!\w)(?:[A-Z][a-z]+){2,}(?!\w)';

// BugTrack/304暫定対処
$WikiName = '(?:[A-Z][a-z]+){2,}(?!\w)';

// $BracketName = ':?[^\s\]#&<>":]+:?';
$BracketName = '(?!\s):?[^\r\n\t\f\[\]<>#&":]+:?(?<!\s)';

// InterWiki
$InterWikiName = '(\[\[)?((?:(?!\s|:|\]\]).)+):(.+)(?(1)\]\])';

// 注釈
/*$NotePattern = '/\(\(((?:(?>(?:(?!\(\()(?!\)\)(?:[^\)]|$)).)+)|(?R))*)\)\)/ex';*/
$NotePattern = '/\(\(((?:(?>(?:(?!\(\()(?!\)\)(?:[^\)]|$)).)+)|(?R))*)\)\)/';		// PHP5.5以降用修正

/////////////////////////////////////////////////
// 初期設定(ユーザ定義ルール読み込み)
//require(DATA_HOME . 'rules.ini.php');
// modified for Magic3 by naoki on 2008/9/22
//require_once(dirname(dirname(__FILE__)) . '/conf/rules.ini.php');
/////////////////////////////////////////////////
// 日時置換ルール (閲覧時に置換)
// $usedatetime = 1なら日時置換ルールが適用されます
// 必要のない方は $usedatetimeを0にしてください。
$datetime_rules = array(
	'&amp;_now;'	=> format_date(UTIME),
	'&amp;_date;'	=> get_date($date_format),
	'&amp;_time;'	=> get_date($time_format),
);

/////////////////////////////////////////////////
// ユーザ定義ルール(保存時に置換)
//  正規表現で記述してください。?(){}-*./+\$^|など
//  は \? のようにクォートしてください。
//  前後に必ず / を含めてください。行頭指定は ^ を頭に。
//  行末指定は $ を後ろに。
//
$page_array = explode('/', WikiParam::getPage()); // with array_pop()

$str_rules = array(
	'now\?' 	=> format_date(UTIME),
	'date\?'	=> get_date($date_format),
	'time\?'	=> get_date($time_format),
	'&now;' 	=> format_date(UTIME),
	'&date;'	=> get_date($date_format),
	'&time;'	=> get_date($time_format),
	'&page;'	=> array_pop($page_array),
	'&fpage;'	=> WikiParam::getPage(),
	'&t;'   	=> "\t",
);

unset($page_array);

/////////////////////////////////////////////////
// 初期設定(その他のグローバル変数)

// 現在時刻
$now = format_date(UTIME);

// 日時置換ルールを$line_rulesに加える
if ($usedatetime) $line_rules += $datetime_rules;
unset($datetime_rules);

// フェイスマークを$line_rulesに加える
if ($usefacemark) $line_rules += $facemark_rules;
unset($facemark_rules);

// 実体参照パターンおよびシステムで使用するパターンを$line_rulesに加える
//$entity_pattern = '[a-zA-Z0-9]{2,8}';
//$entity_pattern = trim(join('', file(CACHE_DIR . 'entities.dat')));
// modified for Magic3 by naoki on 2008/9/22
$entity_pattern = trim(WikiPage::getEntityData());

$line_rules = array_merge(array(
	'&amp;(#[0-9]+|#x[0-9a-f]+|' . $entity_pattern . ');' => '&$1;',
	"\r"          => '<br />' . "\n",	/* 行末にチルダは改行 */
), $line_rules);
?>
