<?php
/**
 * METAタグ生成プラグイン
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
function plugin_meta_convert()
{
	$args = func_get_args();
	return plugin_meta_process($args);
}
function plugin_meta_inline()
{
	$args = func_get_args();
	array_pop($args);
	return plugin_meta_process($args);
}
function plugin_meta_process($args)
{
	global $gPageManager;
	
	// パラメータエラーチェック
	if (count($args) < 2) return false;

	$name = strtolower($args[0]);
	$content = $args[1];
	if (empty($name) || empty($content)) return false;

	// METAタグ追加
	switch ($name){
	case 'keywords':
		$gPageManager->setHeadKeywords($content);
		break;
	case 'description':
		$gPageManager->setHeadDescription($content);
		break;
	default:
		$metaStr = '<meta name="' . htmlspecialchars($name) . '" content="' . htmlspecialchars($content) . '" />';
	
		// HTMLヘッダ部にMETAタグを追加
		$gPageManager->addHeadOthers($metaStr);
		break;
	}
	
	return '';
}
?>
