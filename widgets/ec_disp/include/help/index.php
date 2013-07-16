<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 5322 2012-10-25 15:06:19Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## その他 ##########
$HELP['product_list_count']['title'] = '商品一覧の表示項目数';
$HELP['product_list_count']['body'] = '商品を一覧表示する場合の商品の表示項目数です。';
$HELP['cart_widget']['title'] = 'カート表示ウィジェット';
$HELP['cart_widget']['body'] = 'カート内容を表示するためのウィジェットです。';
$HELP['stock']['title'] = '在庫数';
$HELP['stock']['body'] = '「表示」にチェックを入れると商品詳細で在庫数を表示します。<br />「フォーマット」で在庫数に応じたメッセージを表示できます。<br />記述例)「0:なし;3:残り僅か;:あり」　在庫0の場合「なし」、3以下の場合「残り僅か」、それ以上で「あり」を表示。';
?>
