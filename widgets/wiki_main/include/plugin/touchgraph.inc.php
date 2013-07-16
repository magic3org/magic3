<?php
/**
 * touchgraphプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: touchgraph.inc.php 1113 2008-10-24 03:16:00Z fishbone $
 * @link       http://www.magic3.org
 */
//
// Output an index for 'TouchGraph WikiBrowser'
// http://www.touchgraph.com/
//
// Usage: (Check also TGWikiBrowser's sample)
//    java -Dfile.encoding=EUC-JP \
//    -cp TGWikiBrowser.jar;BrowserLauncher.jar com.touchgraph.wikibrowser.TGWikiBrowser \
//    http://<pukiwiki site>/index.php?plugin=touchgraph \
//    http://<pukiwiki site>/index.php? FrontPage 2 true
//
// Note: -Dfile.encoding=EUC-JP (or UTF-8) may not work with Windows OS
//   http://www.simeji.com/wiki/pukiwiki.php?Java%A4%CE%CD%AB%DD%B5 (in Japanese)

function plugin_touchgraph_action()
{
//	global $vars;
	global $gPageManager;
	
	// ページ作成処理中断
	$gPageManager->abortPage();

	pkwk_headers_sent();
	header('Content-type: text/plain');
	//if (isset($vars['reverse'])) {
	if (WikiParam::getVar('reverse') != '') {
		plugin_touchgraph_ref();
	} else {
		plugin_touchgraph_rel();
	}
	
	// システム強制終了
	$gPageManager->exitSystem();
}
// Normal
function plugin_touchgraph_rel()
{
	foreach (get_existpages() as $page) {
		if (check_non_list($page)) continue;

/*		$file = CACHE_DIR . encode($page) . '.rel';
		if (file_exists($file)) {
			echo $page;
			$data = file($file);
			foreach(explode("\t", trim($data[0])) as $name) {
				if (check_non_list($name)) continue;
				echo ' ', $name;
			}
			echo "\n";
		}
		*/
		$lines = WikiPage::getPageCacheRel($page);
		if (!empty($lines)){
			echo $page;
			foreach(explode("\t", trim($lines[0])) as $name) {
				if (check_non_list($name)) continue;
				echo ' ', $name;
			}
			echo "\n";
		}
	}
}

// Reverse
function plugin_touchgraph_ref()
{
	foreach (get_existpages() as $page) {
		if (check_non_list($page)) continue;

/*		$file = CACHE_DIR . encode($page) . '.ref';
		if (file_exists($file)) {
			echo $page;
			foreach (file($file) as $line) {
				list($name) = explode("\t", $line);
				if (check_non_list($name)) continue;
				echo ' ', $name;
			}
			echo "\n";
		}
*/
		$lines = WikiPage::getPageCacheRef($page);
		if (!empty($lines)){
			echo $page;
			foreach ($lines as $line) {
				list($name) = explode("\t", $line);
				if (check_non_list($name)) continue;
				echo ' ', $name;
			}
			echo "\n";
		}
	}
}
?>
