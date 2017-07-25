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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## HAD追加項目一覧 ##########
$HELP['head_list']['title'] = 'HEAD追加設定一覧';
$HELP['head_list']['body'] = '登録されているHEAD追加設定の一覧です。';
$HELP['head_detail']['title'] = 'HEAD追加設定';
$HELP['head_detail']['body'] = 'HEAD追加についての設定を行います。';
$HELP['head_check']['title'] = '選択用チェックボックス';
$HELP['head_check']['body'] = '削除を行う項目を選択します。';
$HELP['head_name']['title'] = '名前';
$HELP['head_name']['body'] = 'HEAD追加設定名です。';
$HELP['head_name_input']['title'] = '名前';
$HELP['head_name_input']['body'] = 'HEAD追加設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['head_head_text']['title'] = 'ヘッダ部文字列';
$HELP['head_head_text']['body'] = 'HTMLのHEADタグに追加する文字列です。';
$HELP['head_id']['title'] = 'HEAD追加設定ID';
$HELP['head_id']['body'] = '自動的に振られるHEAD追加設定IDです。';
$HELP['head_ref']['title'] = '使用';
$HELP['head_ref']['body'] = 'HEAD追加設定を使用しているウィジェット数を示します。使用が0のHEAD追加設定のみ削除可能です。';
$HELP['head_list_btn']['title'] = '一覧ボタン';
$HELP['head_list_btn']['body'] = 'HEAD追加設定一覧を表示します。';
$HELP['head_del_btn']['title'] = '削除ボタン';
$HELP['head_del_btn']['body'] = '選択されているHEAD追加設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['head_ret_btn']['title'] = '戻るボタン';
$HELP['head_ret_btn']['body'] = 'HEAD追加詳細へ戻ります。';
?>
