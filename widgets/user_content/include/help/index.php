<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ユーザ作成コンテンツ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 3566 2010-09-04 05:29:36Z fishbone $
 * @link       http://www.m-media.co.jp
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## コンテンツ管理 ##########
$HELP['content_list']['title'] = 'コンテンツ部品一覧';
$HELP['content_list']['body'] = '設定可能なコンテンツ部品の一覧です。';
$HELP['content_detail']['title'] = 'コンテンツ部品詳細';
$HELP['content_detail']['body'] = 'コンテンツ部品の設定を行います。';
$HELP['content_check']['title'] = '選択用チェックボックス';
$HELP['content_check']['body'] = '編集を行う項目を選択します。';
$HELP['content_name']['title'] = '名前';
$HELP['content_name']['body'] = 'コンテンツ部品の名前です。';
$HELP['content_room_id']['title'] = 'ルームID';
$HELP['content_room_id']['body'] = '編集対象のルームです。';
$HELP['content_tag']['title'] = '埋め込みタグ';
$HELP['content_tag']['body'] = 'コンテンツ部品を埋め込む位置を示すタグです。';
$HELP['content_type']['title'] = 'データタイプ';
$HELP['content_type']['body'] = 'コンテンツ部品のデータタイプです。<br />●[HTML] HTMLタグが使用できます。<br />●[テキスト] HTMLタグを含まないプレーンな文字列です。<br />●[数値] 比較可能な数値を設定できます。実際に入力するデータは「表示用文字列」が表示される文字列で「数値」は比較用です。';
$HELP['content_data']['title'] = '設定データ';
$HELP['content_data']['body'] = 'コンテンツ部品として設定されているデータです。';
$HELP['content_new_btn']['title'] = '新規ボタン';
$HELP['content_new_btn']['body'] = '新規にコンテンツ部品を追加します。';
$HELP['content_edit_btn']['title'] = '編集ボタン';
$HELP['content_edit_btn']['body'] = '選択されているコンテンツ部品を編集します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['content_ret_btn']['title'] = '戻るボタン';
$HELP['content_ret_btn']['body'] = 'コンテンツ部品一覧へ戻ります。';
$HELP['content_preview_btn']['title'] = 'プレビューボタン';
$HELP['content_preview_btn']['body'] = 'コンテンツ部品を表示した実際の画面です。';

// ########## ルーム管理 ##########
$HELP['room_list']['title'] = 'ルーム一覧';
$HELP['room_list']['body'] = '使用可能なルームの一覧です。';
$HELP['room_detail']['title'] = 'ルーム詳細';
$HELP['room_detail']['body'] = 'ルームについての設定を行います。';
$HELP['room_check']['title'] = '選択用チェックボックス';
$HELP['room_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['room_name']['title'] = '名前';
$HELP['room_name']['body'] = 'ルームの名前です。';
$HELP['room_id']['title'] = 'ルーム識別ID';
$HELP['room_id']['body'] = 'ルームを識別するためにユニークにIDを付けます。';
$HELP['room_visible']['title'] = '公開';
$HELP['room_visible']['body'] = 'ルームを一般ユーザに公開するかどうかを制御します。非公開に設定の場合は一般ユーザから参照することはできません。';
$HELP['room_new_btn']['title'] = '新規ボタン';
$HELP['room_new_btn']['body'] = '新規にルームを追加します。';
$HELP['room_edit_btn']['title'] = '編集ボタン';
$HELP['room_edit_btn']['body'] = '選択されているルームを編集します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['room_del_btn']['title'] = '削除ボタン';
$HELP['room_del_btn']['body'] = '選択されているルームを削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['room_ret_btn']['title'] = '戻るボタン';
$HELP['room_ret_btn']['body'] = 'ルーム一覧へ戻ります。';

// ########## タブ定義 ##########
$HELP['tab_list']['title'] = 'タブ項目一覧';
$HELP['tab_list']['body'] = '表示するタブ項目の一覧です。';
$HELP['tab_detail']['title'] = 'タブ項目詳細';
$HELP['tab_detail']['body'] = 'タブ項目についての設定を行います。';
$HELP['tab_check']['title'] = '選択用チェックボックス';
$HELP['tab_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['tab_name']['title'] = '名前';
$HELP['tab_name']['body'] = 'タブのタイトル名です。';
$HELP['tab_id']['title'] = 'タブ識別ID';
$HELP['tab_id']['body'] = 'タブを識別するためにユニークにIDを付けます。';
$HELP['tab_visible']['title'] = '公開';
$HELP['tab_visible']['body'] = 'タブをユーザに公開するかどうかを制御します。非公開に設定の場合はユーザから参照することはできません。';
$HELP['tab_use_item']['title'] = 'コンテンツ部品';
$HELP['tab_use_item']['body'] = 'タブ上に表示されるコンテンツ部品です。';
$HELP['tab_index']['title'] = '表示順';
$HELP['tab_index']['body'] = '左からのタブの表示順を指定します。';
$HELP['tab_template']['title'] = 'テンプレート';
$HELP['tab_template']['body'] = 'タブの内容として表示するテンプレートを設定します。テンプレートに「コンテンツ部品定義」で作成したコンテンツ部品の「埋め込みタグ」を配置すると、その位置にコンテンツ部品が表示されます。';
$HELP['tab_new_btn']['title'] = '新規ボタン';
$HELP['tab_new_btn']['body'] = '新規にタブ項目を追加します。';
$HELP['tab_edit_btn']['title'] = '編集ボタン';
$HELP['tab_edit_btn']['body'] = '選択されているタブ項目を編集します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['tab_del_btn']['title'] = '削除ボタン';
$HELP['tab_del_btn']['body'] = '選択されているタブ項目を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['tab_ret_btn']['title'] = '戻るボタン';
$HELP['tab_ret_btn']['body'] = 'タブ項目一覧へ戻ります。';

// ########## コンテンツ部品定義 ##########
$HELP['item_list']['title'] = 'コンテンツ部品定義一覧';
$HELP['item_list']['body'] = '使用可能なコンテンツ部品定義の一覧です。';
$HELP['item_detail']['title'] = 'コンテンツ部品定義詳細';
$HELP['item_detail']['body'] = 'コンテンツ部品についての定義を行います。';
$HELP['item_check']['title'] = '選択用チェックボックス';
$HELP['item_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['item_name']['title'] = '名前';
$HELP['item_name']['body'] = 'コンテンツ部品定義の名前です。';
$HELP['item_id']['title'] = 'コンテンツ部品識別ID';
$HELP['item_id']['body'] = 'コンテンツ部品を識別するためにユニークにIDを付けます。';
$HELP['item_type']['title'] = 'データタイプ';
$HELP['item_type']['body'] = 'コンテンツ部品のデータタイプです。<br />●[HTML] HTMLタグが使用できます。<br />●[テキスト] HTMLタグを含まないプレーンな文字列です。<br />●[数値] 比較可能な数値を設定できます。実際に入力するデータは「表示用文字列」が表示される文字列で「数値」は比較用です。';
$HELP['item_tag']['title'] = '埋め込みタグ';
$HELP['item_tag']['body'] = 'タブ定義のテンプレートに埋め込むタグです。タグの位置に実際のコンテンツ部品が表示されます。';
$HELP['item_new_btn']['title'] = '新規ボタン';
$HELP['item_new_btn']['body'] = '新規にコンテンツ部品定義を追加します。';
$HELP['item_edit_btn']['title'] = '編集ボタン';
$HELP['item_edit_btn']['body'] = '選択されているコンテンツ部品定義を編集します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['item_del_btn']['title'] = '削除ボタン';
$HELP['item_del_btn']['body'] = '選択されているコンテンツ部品定義を削除します。<br />項目を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['item_ret_btn']['title'] = '戻るボタン';
$HELP['item_ret_btn']['body'] = 'コンテンツ部品定義一覧へ戻ります。';

// ########## その他 ##########
$HELP['other_top_html']['title'] = 'トップページ用HTML';
$HELP['other_top_html']['body'] = 'ルームIDが指定されていない場合に表示されるトップ画面用のHTMLです。';
$HELP['other_css']['title'] = 'CSS';
$HELP['other_css']['body'] = 'タブのデザインなどのCSSを設定します。<br />タブの表示状況に関わらずHTMLヘッダに追加されます。';
$HELP['other_access']['title'] = 'アクセス権';
$HELP['other_access']['body'] = 'コンテンツ編集の権限を設定します。<br />「ルームIDと同じユーザIDのユーザのコンテンツの編集を許可する」にチェックを入れた場合、ログインしたユーザが同名のルームIDのコンテンツ管理が可能になります。';
$HELP['other_group_id']['title'] = '現在のグループID';
$HELP['other_group_id']['body'] = 'グループIDはルームをグループ化するために使用します。現在のグループIDを変更するには「画面編集」-「ページ定義詳細」の「定義ID」で変更します。';
?>
