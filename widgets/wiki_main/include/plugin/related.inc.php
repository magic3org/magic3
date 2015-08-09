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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */

function plugin_related_convert()
{
	return make_related(WikiParam::getPage(), 'p');
}

// Show Backlinks: via related caches for the page
function plugin_related_action()
{
	global $script, $_title_related;
	global $_msg_returnto, $_msg_no_related_pages;

	$_page = WikiParam::getPage();
	if ($_page == '') $_page = WikiConfig::getDefaultPage();

	// 関連ページを取得
	$data = links_get_related_db($_page);
	if (! empty($data)) {
		foreach (array_keys($data) as $page){
			if ($page == WikiConfig::getWhatsnewPage() || check_non_list($page)) unset($data[$page]);
		}
	}

	// Result
	$r_word = rawurlencode($_page);
	$s_word = htmlspecialchars($_page);

	//$body  = '<a href="' . $script . WikiParam::convQuery('?' . $r_word) . '">' . 'Return to ' . $s_word .'</a><br />'. "\n";
	$body  = '<a href="' . $script . WikiParam::convQuery('?' . $r_word) . '">' . str_replace('$1', $s_word, $_msg_returnto) .'</a><br />'. "\n";

	if (empty($data)) {
	//	$body .= '<ul><li>No related pages found.</li></ul>' . "\n";
		$body .= '<ul><li>' . $_msg_no_related_pages . '</li></ul>' . "\n";
	} else {
		// Show count($data)?
		ksort($data);
		$body .= '<ul>' . "\n";
		foreach ($data as $page=>$time) {
			$r_page  = rawurlencode($page);
			$s_page  = htmlspecialchars($page);
			$passage = get_passage($time);

			$body .= ' <li><a href="' . $script . WikiParam::convQuery('?' . $r_page) . '">' . $s_page . '</a> ' . $passage . '</li>' . "\n";
		}
		$body .= '</ul>' . "\n";
	}
	WikiParam::setRefer($_page);		// add by magic3

	return array('msg' => $_title_related, 'body' => $body);
}
?>
