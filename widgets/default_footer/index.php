<?php
/**
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 2 2007-11-03 04:59:01Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

// フッタの表示
echo '<div align="center">(C) ' . date("Y") . ' ' . $gEnvManager->getSiteCopyRight() .'</div><div align="center"></div>';
echo '<div align="center"><a href="http://www.magic3.org">Magic3 ' . M3_SYSTEM_VERSION . '</a> is licensed under the terms of the GNU General Public License.</div>';
?>
