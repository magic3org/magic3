<?php
/**
 * backプラグイン
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
// Copyright (C)
//   2003-2004 PukiWiki Developers Team
//   2002      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>

// Allow specifying back link by page name and anchor, or
// by relative or site-abusolute path
define('PLUGIN_BACK_ALLOW_PAGELINK', PKWK_SAFE_MODE); // FALSE(Compat), TRUE

// Allow JavaScript (Compat)
define('PLUGIN_BACK_ALLOW_JAVASCRIPT', TRUE); // TRUE(Compat), FALSE

// ----
define('PLUGIN_BACK_USAGE', '#back([text],[center|left|right][,0(no hr)[,Page-or-URI-to-back]])');
function plugin_back_convert()
{
	global $_msg_back_word, $script;

	if (func_num_args() > 4) return PLUGIN_BACK_USAGE;
	list($word, $align, $hr, $href) = array_pad(func_get_args(), 4, '');

	$word = trim($word);
	$word = ($word == '') ? $_msg_back_word : htmlspecialchars($word);

	$align = strtolower(trim($align));
	switch($align){
	case ''      : $align = 'center';
	               /*FALLTHROUGH*/
	case 'center': /*FALLTHROUGH*/
	case 'left'  : /*FALLTHROUGH*/
	case 'right' : break;
	default      : return PLUGIN_BACK_USAGE;
	}

	//$hr = (trim($hr) != '0') ? '<hr class="full_hr" />' . "\n" : '';
	$hr = (trim($hr) != '0') ? '<hr class="wiki_hr" />' . "\n" : '';

	$link = TRUE;
	$href = trim($href);
	if ($href != '') {
		if (PLUGIN_BACK_ALLOW_PAGELINK) {
			if (is_url($href)) {
				$href = rawurlencode($href);
			} else {
				$array = anchor_explode($href);
				$array[0] = rawurlencode($array[0]);
				$array[1] = ($array[1] != '') ? '#' . rawurlencode($array[1]) : '';
				//$href = $script . '?' . $array[0] .  $array[1];
				$href = $script . WikiParam::convQuery('?' . $array[0] .  $array[1]);
				$link = WikiPage::isPage($array[0]);
			}
		} else {
			$href = rawurlencode($href);
		}
	} else {
		if (! PLUGIN_BACK_ALLOW_JAVASCRIPT)
			return PLUGIN_BACK_USAGE . ': Set a page name or an URI';
		$href  = 'javascript:history.go(-1)';
	}

	if($link){
		// Normal link
		return $hr . '<div style="text-align:' . $align . '">' .
			'[ <a href="' . $href . '">' . $word . '</a> ]</div>' . "\n";
	} else {
		// Dangling link
		return $hr . '<div style="text-align:' . $align . '">' .
			'[ <span class="noexists">' . $word . '<a href="' . $href .
			'">?</a></span> ]</div>' . "\n";
	}
}
?>
