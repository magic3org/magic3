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
 * @copyright  Copyright 2009-2016 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.m-media.co.jp
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## 設定項目一覧 ##########
$HELP['untitled_list']['title'] = '設定一覧';
$HELP['untitled_list']['body'] = '登録されている設定の一覧です。';
$HELP['untitled_detail']['title'] = '設定';
$HELP['untitled_detail']['body'] = 'お問い合わせについての設定を行います。';
$HELP['untitled_preview']['title'] = 'プレビュー';
$HELP['untitled_preview']['body'] = 'プレビューを表示します。項目の行をマウスクリックして選択します。';
$HELP['untitled_check']['title'] = '選択用チェックボックス';
$HELP['untitled_check']['body'] = '削除を行う項目を選択します。';
$HELP['untitled_name']['title'] = '名前';
$HELP['untitled_name']['body'] = '設定名です。';
$HELP['untitled_name_input']['title'] = '名前';
$HELP['untitled_name_input']['body'] = '設定名です。新規に登録するか、登録済みの設定を選択します。';
$HELP['untitled_id']['title'] = '設定ID';
$HELP['untitled_id']['body'] = '自動的に振られる設定IDです。';
$HELP['untitled_page_title']['title'] = '画面タイトル';
$HELP['untitled_page_title']['body'] = '画面タイトルを設定します。';
$HELP['untitled_template']['title'] = 'レイアウト';
$HELP['untitled_template']['body'] = 'お問い合わせ画面のレイアウトを作成します。<br />[#ITEM_KEY_n#](nはお問い合わせ項目のNoを指定)形式のタグを埋め込むとその位置に該当するお問い合わせ項目が表示できます。';
$HELP['untitled_field']['title'] = 'お問い合わせ項目';
$HELP['untitled_field']['body'] = 'お問い合わせ項目を定義します。定義した項目のタグを「レイアウト」に埋め込んで使用します。<br />「定義」の記述方法は以下の通りです。(m,nは数値、str,valは文字列を示します。)<br />●テキストボックス共通<br />「size=m」でフィールドサイズを設定します。<br />●テキストボックス(Eメール)<br />Eメール形式のテキストのみ入力可能なテキストボックスです。<br />確認用のフィールドを使用する場合は追加したテキストボックスに「ref=m」で参照先フィールドの項目Noを設定します。<br />●テキストボックス(計算)<br />数値のみ入力可能なテキストボックスで複数使用して入力値の自動計算が行えます。<br />「フィールドID」は「計算式」で使用するIDで英小文字で設定します。「計算式」に計算方法を設定します。使用可能な演算子は「+-*/」です。<br />●テキストエリア<br />「rows=m;cols=n」で行、列数を設定します。<br />●セレクトメニュー,チェックボックス,ラジオボタン<br />「str1;str2;str3;...」<br />表示値、送信値が異なる場合は「str1=val1;str2=val2;str3=val3;...」<br />チェックボックス、ラジオボタンの場合は、「;」が連続した空項目があると改行(BRタグ)を挿入します。';
$HELP['untitled_email']['title'] = 'メール送信';
$HELP['untitled_email']['body'] = 'お問い合わせメールの件名と送信先メールアドレスを設定します。メールアドレスが空の場合は基本情報のメールアドレスへ送信されます。';
$HELP['untitled_user_email']['title'] = '確認メール';
$HELP['untitled_user_email']['body'] = 'お問い合わせ項目タイプが「テキストボックス(Eメール)」のアドレス宛に確認メールを送信する場合の設定を行います。基本情報のメールアドレスが送信元になり、「送信元メールアドレス」が設定されます。<br />「本文」の「[#BODY#]」には「メール送信」のメール内容がデフォルトの出力形式で挿入されます。個別に項目の入力値を取得するには「[#ITEM_KEY_n#]」タグを使用します。';
$HELP['untitled_ref']['title'] = '使用';
$HELP['untitled_ref']['body'] = '設定を使用しているウィジェット数を示します。使用が0の設定のみ削除可能です。';
$HELP['untitled_list_btn']['title'] = '一覧ボタン';
$HELP['untitled_list_btn']['body'] = '設定一覧を表示します。';
$HELP['untitled_del_btn']['title'] = '削除ボタン';
$HELP['untitled_del_btn']['body'] = '選択されている設定を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['untitled_ret_btn']['title'] = '戻るボタン';
$HELP['untitled_ret_btn']['body'] = '設定詳細へ戻ります。';
$HELP['untitled_preview_btn']['title'] = 'プレビューボタン';
$HELP['untitled_preview_btn']['body'] = '実際の画面を表示します。';
?>
