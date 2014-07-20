<?php
/**
 * lookupプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: lookup.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// InterWiki lookup plugin

define('PLUGIN_LOOKUP_USAGE', '#lookup(interwikiname[,button_name[,default]])');

function plugin_lookup_convert()
{
	global $gEnvManager;
	static $id = 0;

	$num = func_num_args();
	if ($num == 0 || $num > 3) return PLUGIN_LOOKUP_USAGE;

	$args = func_get_args();
	$interwiki = htmlspecialchars(trim($args[0]));
	$button    = isset($args[1]) ? trim($args[1]) : '';
	$button    = ($button != '') ? htmlspecialchars($button) : 'lookup';
	$default   = ($num > 2) ? htmlspecialchars(trim($args[2])) : '';
	$s_page    = htmlspecialchars(WikiParam::getPage());
	++$id;

	$script = get_script_uri();
	$postScript = $script . WikiParam::convQuery("?");
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
		$ret = <<<EOD
<form action="$postScript" method="post" class="form form-inline" role="form">
  <input type="hidden" name="plugin" value="lookup" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="inter"  value="$interwiki" />
  <div class="form-group"><label for="_p_lookup_$id">$interwiki:</label>
  <input type="text" name="page" id="_p_lookup_$id" class="form-control" size="30" value="$default" /></div>
  <input type="submit" class="button btn" value="$button" />
</form>
EOD;
	} else {
		$ret = <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="plugin" value="lookup" />
  <input type="hidden" name="refer"  value="$s_page" />
  <input type="hidden" name="inter"  value="$interwiki" />
  <label for="_p_lookup_$id">$interwiki:</label>
  <input type="text" name="page" id="_p_lookup_$id" size="30" value="$default" />
  <input type="submit" class="button" value="$button" />
 </div>
</form>
EOD;
	}
	return $ret;
}

function plugin_lookup_action()
{
	$page  = WikiParam::getPostVar('page');
	$inter = WikiParam::getPostVar('inter');

	if ($page == '') return FALSE; // Do nothing
	if ($inter == '') return array('msg'=>'Invalid access', 'body'=>'');

	$url = get_interwiki_url($inter, $page);
	if ($url === FALSE) {
		$msg = sprintf('InterWikiName "%s" not found', $inter);
		$msg = htmlspecialchars($msg);
		return array('msg'=>'Not found', 'body'=>$msg);
	}

	pkwk_headers_sent();
	header('Location: ' . $url); // Publish as GET method
	exit;
}
?>
