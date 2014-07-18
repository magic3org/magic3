<?php
/**
 * tracker_listプラグイン
 *
 * 機能：入力フォーム一覧
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: tracker_list.inc.php 1154 2008-10-29 04:23:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(PLUGIN_DIR . 'tracker.inc.php');

function plugin_tracker_list_init()
{
	if (function_exists('plugin_tracker_init')) plugin_tracker_init();
}
?>
