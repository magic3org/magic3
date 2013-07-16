<?php
/**
 * relatedプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: related.inc.php 1162 2008-10-31 04:27:21Z fishbone $
 * @link       http://www.magic3.org
 */

function plugin_related_convert()
{
	// modified for Magic3 by naoki on 2008/10/10
	//global $vars;
	//
	//return make_related($vars['page'], 'p');
	return make_related(WikiParam::getPage(), 'p');
}

// Show Backlinks: via related caches for the page
function plugin_related_action()
{
	// modified for Magic3 by naoki on 2008/10/10
	//global $vars, $script, $defaultpage, $whatsnew;
	global $script, $_title_related;

	/*$_page = isset($vars['page']) ? $vars['page'] : '';
	if ($_page == '') $_page = $defaultpage;*/
	$_page = WikiParam::getPage();
	if ($_page == '') $_page = WikiConfig::getDefaultPage();

	// Get related from cache
	$data = links_get_related_db($_page);
	if (! empty($data)) {
		// Hide by array keys (not values)
		/*foreach(array_keys($data) as $page)
			if ($page == $whatsnew || check_non_list($page))
				unset($data[$page]);*/
				
		foreach (array_keys($data) as $page){
			if ($page == WikiConfig::getWhatsnewPage() || check_non_list($page)) unset($data[$page]);
		}
	}

	// Result
	$r_word = rawurlencode($_page);
	$s_word = htmlspecialchars($_page);
	//$msg = 'Backlinks for: ' . $s_word;
	// modified for Magic3 by naoki on 2008/10/10
	/*$retval  = '<a href="' . $script . '?' . $r_word . '">' .
		'Return to ' . $s_word .'</a><br />'. "\n";*/
	$retval  = '<a href="' . $script . WikiParam::convQuery('?' . $r_word) . '">' .
		'Return to ' . $s_word .'</a><br />'. "\n";

	if (empty($data)) {
		$retval .= '<ul><li>No related pages found.</li></ul>' . "\n";	
	} else {
		// Show count($data)?
		ksort($data);
		$retval .= '<ul>' . "\n";
		foreach ($data as $page=>$time) {
			$r_page  = rawurlencode($page);
			$s_page  = htmlspecialchars($page);
			$passage = get_passage($time);
			// modified for Magic3 by naoki on 2008/10/10
			/*$retval .= ' <li><a href="' . $script . '?' . $r_page . '">' . $s_page .
				'</a> ' . $passage . '</li>' . "\n";*/
			$retval .= ' <li><a href="' . $script . WikiParam::convQuery('?' . $r_page) . '">' . $s_page .
				'</a> ' . $passage . '</li>' . "\n";
		}
		$retval .= '</ul>' . "\n";
	}
	WikiParam::setRefer($_page);		// add by magic3
	//return array('msg'=>$msg, 'body'=>$retval);
	return array('msg'=>$_title_related, 'body'=>$retval);
}
?>
