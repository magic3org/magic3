<?php
/**
 * tbプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
/*
 * PukiWiki/TrackBack: TrackBack Ping receiver and viewer
 * (C) 2003-2005 PukiWiki Developers Team
 * (C) 2003 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * License: GPL
 *
 * plugin_tb_action()    action
 * plugin_tb_save($url, $tb_id)          Save or update TrackBack Ping data
 * plugin_tb_output_response($rc, $msg)  Show a response code of the ping via HTTP/XML (then exit)
 * plugin_tb_output_rsslist($tb_id)      Show pings for the page via RSS
 */

switch(LANG){
case 'ja': define('PLUGIN_TB_LANGUAGE', 'ja-jp'); break;
default  : define('PLUGIN_TB_LANGUAGE', 'en-us'); break;
}

define('PLUGIN_TB_ERROR',   1);
define('PLUGIN_TB_NOERROR', 0);

function plugin_tb_action()
{
	//global $trackback, $vars;
	global $trackback;

	$url = WikiParam::getVar('url');
	$tb_id = WikiParam::getVar('tb_id');
	//if ($trackback && isset($vars['url'])) {
	if ($trackback && ($url != '')) {
		// Receive and save a TrackBack Ping (both GET and POST)
		//$url   = $vars['url'];
		//$tb_id = isset($vars['tb_id']) ? $vars['tb_id'] : '';
		list($error, $message) = plugin_tb_save($url, $tb_id);

		// Output the response
		plugin_tb_output_response($error, $message);
		exit;

	} else {
		$mode = WikiParam::getVar('__mode');
		//if ($trackback && isset($vars['__mode']) && isset($vars['tb_id'])) {
		if ($trackback && ($mode != '') && ($tb_id != '')) {
			switch ($mode) {
				case 'rss' : plugin_tb_output_rsslist($tb_id);  break;
			}
			exit;
		} else {
			// Show List of pages that TrackBacks reached
			//$pages = get_existpages(TRACKBACK_DIR, '.txt');
			$pages = WikiPage::getTrackbackPages();
			if (! empty($pages)) {
				return array('msg'=>'Trackback list',
					'body'=>page_list($pages, 'read', FALSE));
			} else {
				return array('msg'=>'', 'body'=>'');
			}
		}
	}
}

// Save or update TrackBack Ping data
function plugin_tb_save($url, $tb_id)
{
	//global $vars, $trackback;
	global $trackback;
	static $fields = array( /* UTIME, */ 'url', 'title', 'excerpt', 'blog_name');

	$die = '';
	if (! $trackback) $die .= 'TrackBack feature disabled. ';
	if ($url   == '') $die .= 'URL parameter is not set. ';
	if ($tb_id == '') $die .= 'TrackBack Ping ID is not set. ';
	if ($die != '') return array(PLUGIN_TB_ERROR, $die);

	if (! file_exists(TRACKBACK_DIR)) return array(PLUGIN_TB_ERROR, 'No such directory: TRACKBACK_DIR');
	if (! is_writable(TRACKBACK_DIR)) return array(PLUGIN_TB_ERROR, 'Permission denied: TRACKBACK_DIR');

	$page = tb_id2page($tb_id);
	if ($page === FALSE) return array(PLUGIN_TB_ERROR, 'TrackBack ID is invalid.');

	// URL validation (maybe worse of processing time limit)
	$result = http_request($url, 'HEAD');
	if ($result['rc'] !== 200) return array(PLUGIN_TB_ERROR, 'URL is fictitious.');

	// Update TrackBack Ping data
	$filename = tb_get_filename($page);
	$data     = tb_get($filename);

	$items = array(UTIME);
	foreach ($fields as $key) {
		//$value = isset($vars[$key]) ? $vars[$key] : '';
		$value = WikiParam::getVar($key);
		if (preg_match('/[,"' . "\n\r" . ']/', $value))
			$value = '"' . str_replace('"', '""', $value) . '"';
		$items[$key] = $value;
	}
	$data[rawurldecode($items['url'])] = $items;

	$fp = fopen($filename, 'w');
	set_file_buffer($fp, 0);
	flock($fp, LOCK_EX);
	rewind($fp);
	foreach ($data as $line) {
		$line = preg_replace('/[\r\n]/s', '', $line); // One line, one ping
		fwrite($fp, join(',', $line) . "\n");
	}
	flock($fp, LOCK_UN);
	fclose($fp);

	return array(PLUGIN_TB_NOERROR, '');
}

// Show a response code of the ping via HTTP/XML (then exit)
function plugin_tb_output_response($rc, $msg = '')
{
	if ($rc == PLUGIN_TB_NOERROR) {
		$rc = 0; // for PLUGIN_TB_NOERROR
	} else {
		$rc = 1; // for PLUGIN_TB_ERROR
	}

	pkwk_common_headers();
	header('Content-Type: text/xml');
	echo '<?xml version="1.0" encoding="iso-8859-1"?>';
	echo '<response>';
	echo ' <error>' . $rc . '</error>';
	if ($rc) echo '<message>' . $msg . '</message>';
	echo '</response>';
	exit;
}

// Show pings for the page via RSS
function plugin_tb_output_rsslist($tb_id)
{
	//global $script, $vars, $entity_pattern;
	global $script, $entity_pattern;

	$page = tb_id2page($tb_id);
	if ($page === FALSE) return FALSE;

	$items = '';
	foreach (tb_get(tb_get_filename($page)) as $arr) {
		// _utime_, title, excerpt, _blog_name_
		array_shift($arr); // Cut utime
		list ($url, $title, $excerpt) = array_map(
			create_function('$a', 'return htmlspecialchars($a);'), $arr);
		$items .= <<<EOD

   <item>
    <title>$title</title>
    <link>$url</link>
    <description>$excerpt</description>
   </item>
EOD;
	}

	$title = htmlspecialchars($page);
	$link  = $script . '?' . rawurlencode($page);
	//$vars['page'] = $page;
	WikiParam::setPage($page);
	$excerpt = strip_htmltag(convert_html(get_source($page)));
	$excerpt = preg_replace("/&$entity_pattern;/", '', $excerpt);
	$excerpt = mb_strimwidth(preg_replace("/[\r\n]/", ' ', $excerpt), 0, 255, '...');
	$lang    = PLUGIN_TB_LANGUAGE;

	$rc = <<<EOD
<?xml version="1.0" encoding="utf-8" ?>
<response>
 <error>0</error>
 <rss version="0.91">
  <channel>
   <title>$title</title>
   <link>$link</link>
   <description>$excerpt</description>
   <language>$lang</language>$items
  </channel>
 </rss>
</response>
EOD;

	pkwk_common_headers();
	header('Content-Type: text/xml');
	echo mb_convert_encoding($rc, 'UTF-8', SOURCE_ENCODING);
	exit;
}
?>
