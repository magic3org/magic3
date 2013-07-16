<?php
/**
 * ウィジェット呼び出し用ファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    フリーレイアウトお問い合わせ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2009 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 3648 2010-09-29 05:14:36Z fishbone $
 * @link       http://www.m-media.co.jp
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

// ウィジェット実行
global $gLaunchManager;
$gLaunchManager->goWidget(__FILE__);
?>
