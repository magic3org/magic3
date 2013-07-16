<?php
/**
 * 携帯用アクセスポイントindex.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 3103 2010-05-07 06:05:52Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(dirname(dirname(__FILE__)) . '/include/global.php');

// プログラム実行
$gLaunchManager->goSmartphone(__FILE__);
?>
