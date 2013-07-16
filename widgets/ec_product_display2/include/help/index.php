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
 * @version    SVN: $Id: index.php 4494 2011-12-05 12:05:35Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 新着おすすめ項目一覧 ##########
$HELP['item_list']['title'] = '新着おすすめ設定一覧';
$HELP['item_list']['body'] = '登録されている「新着おすすめ」の設定一覧です。';
$HELP['item_detail']['title'] = '新着おすすめ設定';
$HELP['item_detail']['body'] = '「新着おすすめ」についての設定を行います。';
$HELP['item_check']['title'] = '選択用チェックボックス';
$HELP['item_check']['body'] = '削除を行う項目を選択します。';
$HELP['item_name']['title'] = '名前';
$HELP['item_name']['body'] = '「新着おすすめ」の設定名です。「新着おすすめ」のタイトルにも使用されます。';
$HELP['item_name_input']['title'] = '名前';
$HELP['item_name_input']['body'] = '「新着おすすめ」の設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['item_id']['title'] = '新着おすすめ設定ID';
$HELP['item_id']['body'] = '自動的に振られる「新着おすすめ」の設定IDです。';
$HELP['item_product_items']['title'] = '商品ID';
$HELP['item_product_items']['body'] = '「新着おすすめ」として表示される商品を指定します。商品IDを「,」区切りで設定します。';
$HELP['item_ref']['title'] = '使用';
$HELP['item_ref']['body'] = '設定を使用しているウィジェット数を示します。使用が0の設定のみ削除可能です。';
$HELP['item_list_btn']['title'] = '一覧ボタン';
$HELP['item_list_btn']['body'] = '設定一覧を表示します。';
$HELP['item_del_btn']['title'] = '削除ボタン';
$HELP['item_del_btn']['body'] = '選択されている設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['item_ret_btn']['title'] = '戻るボタン';
$HELP['item_ret_btn']['body'] = '設定詳細へ戻ります。';
?>
