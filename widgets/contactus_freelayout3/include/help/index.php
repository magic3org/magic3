<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    フリーレイアウトお問い合わせ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2009-2013 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.m-media.co.jp
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 設定項目一覧 ##########
$HELP['contactus_list']['title'] = '設定一覧';
$HELP['contactus_list']['body'] = '登録されている設定の一覧です。';
$HELP['contactus_detail']['title'] = '設定';
$HELP['contactus_detail']['body'] = 'お問い合わせについての設定を行います。';
$HELP['contactus_preview']['title'] = 'プレビュー';
$HELP['contactus_preview']['body'] = 'プレビューを表示します。項目の行をマウスクリックして選択します。';
$HELP['contactus_check']['title'] = '選択用チェックボックス';
$HELP['contactus_check']['body'] = '削除を行う項目を選択します。';
$HELP['contactus_name']['title'] = '名前';
$HELP['contactus_name']['body'] = '設定名です。';
$HELP['contactus_name_input']['title'] = '名前';
$HELP['contactus_name_input']['body'] = '設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['contactus_id']['title'] = '設定ID';
$HELP['contactus_id']['body'] = '自動的に振られる設定IDです。';
$HELP['contactus_page_title']['title'] = '画面タイトル';
$HELP['contactus_page_title']['body'] = '画面タイトルを設定します。';
$HELP['contactus_template']['title'] = 'テンプレート';
$HELP['contactus_template']['body'] = 'レイアウト用のテンプレートです。<br />[#ITEM_KEY_n#](nはお問い合わせ項目のNoを指定)形式のタグを埋め込むとその位置に該当するお問い合わせ項目が表示できます。';
$HELP['contactus_field']['title'] = 'お問い合わせ項目';
$HELP['contactus_field']['body'] = 'お問い合わせ項目を定義します。「定義」の記述方法は以下の通りです。(m,nは数値、str,valは文字列を示します。)<br />●テキストボックス共通<br />「size=m」でフィールドサイズを設定します。<br />●テキストボックス(Eメール)<br />Eメール形式のテキストのみ入力可能なテキストボックスです。<br />確認用のフィールドを使用する場合は追加したテキストボックスに「ref=m」で参照先フィールドの項目Noを設定します。<br />●テキストボックス(計算)<br />数値のみ入力可能なテキストボックスで複数使用して入力値の自動計算が行えます。<br />「フィールドID」は「計算式」で使用するIDで英小文字で設定します。「計算式」に計算方法を設定します。使用可能な演算子は「+-*/」です。<br />●テキストエリア<br />「rows=m;cols=n」で行、列数を設定します。<br />●セレクトメニュー,チェックボックス,ラジオボタン<br />「str1;str2;str3;...」<br />表示値、送信値が異なる場合は「str1=val1;str2=val2;str3=val3;...」<br />チェックボックス、ラジオボタンの場合は、「;」が連続した空項目があると改行(BRタグ)を挿入します。';
$HELP['contactus_email']['title'] = 'メール送信';
$HELP['contactus_email']['body'] = 'お問い合わせメールの件名と送信先メールアドレスを設定します。メールアドレスが空の場合は基本情報のメールアドレスへ送信されます。';
$HELP['contactus_user_email']['title'] = '確認メール';
$HELP['contactus_user_email']['body'] = 'お問い合わせ項目タイプが「テキストボックス(Eメール)」のアドレス宛に確認メールを送信する場合の設定を行います。基本情報のメールアドレスが送信元になり、「送信元メールアドレス」が設定されます。<br />「本文」の「[#BODY#]」には「メール送信」のメール内容が挿入されます。「[#ITEM_KEY_n#]」で個別の入力値が挿入されます。';
$HELP['contactus_ref']['title'] = '使用';
$HELP['contactus_ref']['body'] = '設定を使用しているウィジェット数を示します。使用が0の設定のみ削除可能です。';
$HELP['contactus_list_btn']['title'] = '一覧ボタン';
$HELP['contactus_list_btn']['body'] = '設定一覧を表示します。';
$HELP['contactus_del_btn']['title'] = '削除ボタン';
$HELP['contactus_del_btn']['body'] = '選択されている設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['contactus_ret_btn']['title'] = '戻るボタン';
$HELP['contactus_ret_btn']['body'] = '設定詳細へ戻ります。';
$HELP['contactus_preview_btn']['title'] = 'プレビューボタン';
$HELP['contactus_preview_btn']['body'] = '実際の画面を表示します。';
?>
