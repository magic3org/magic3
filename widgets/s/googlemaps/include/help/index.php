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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 3738 2010-10-27 01:05:56Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 設定項目一覧 ##########
$HELP['item_list']['title'] = '設定一覧';
$HELP['item_list']['body'] = '登録されている設定の一覧です。';
$HELP['item_detail']['title'] = '設定詳細';
$HELP['item_detail']['body'] = '詳細設定を行います。';
$HELP['item_check']['title'] = '選択用チェックボックス';
$HELP['item_check']['body'] = '削除を行う項目を選択します。';
$HELP['item_name']['title'] = '名前';
$HELP['item_name']['body'] = '設定名です。';
$HELP['item_name_input']['title'] = '名前';
$HELP['item_name_input']['body'] = '設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['item_map_key']['title'] = 'Googleマップ利用キー';
$HELP['item_map_key']['body'] = 'Googleマップを利用するにはドメイン登録してキーを入手する必要があります。';
$HELP['item_map_pos']['title'] = 'マップ表示位置(中心)';
$HELP['item_map_pos']['body'] = '地図の中心の位置の緯度、経度を指定します。';
$HELP['item_map_size']['title'] = '表示サイズ';
$HELP['item_map_size']['body'] = '地図の幅、高さをpxで指定します。';
$HELP['item_marker']['title'] = 'マーカー';
$HELP['item_marker']['body'] = '緯度、経度指定でマーカーが表示できます。';
$HELP['item_controller']['title'] = 'コントローラ';
$HELP['item_controller']['body'] = '地図を操作するコントローラの表示、非表示を制御します。';
$HELP['item_info']['title'] = '吹き出し';
$HELP['item_info']['body'] = '地図上に情報を表示する吹き出しの表示位置を設定します。';
$HELP['item_info_content']['title'] = '吹き出し内容';
$HELP['item_info_content']['body'] = '吹き出しの表示内容を設定します。';
$HELP['item_map_preview']['title'] = 'Googleマッププレビュー';
$HELP['item_map_preview']['body'] = '実際に表示されるGoogleマップです。';
$HELP['item_id']['title'] = '設定ID';
$HELP['item_id']['body'] = '自動的に振られる設定IDです。';
$HELP['item_ref']['title'] = '使用';
$HELP['item_ref']['body'] = '設定を使用しているウィジェット数を示します。使用が0の設定のみ削除可能です。';
$HELP['item_list_btn']['title'] = '一覧ボタン';
$HELP['item_list_btn']['body'] = '設定一覧を表示します。';
$HELP['item_del_btn']['title'] = '削除ボタン';
$HELP['item_del_btn']['body'] = '選択されている設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['item_ret_btn']['title'] = '戻るボタン';
$HELP['item_ret_btn']['body'] = '設定詳細へ戻ります。';
?>
