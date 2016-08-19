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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## システム情報(ベース用) ##########
$HELP['configsys']['title'] = 'システム基本設定';
$HELP['configsys']['body'] = 'システムの動作に関する基本設定を行います。';

// ########## 言語設定 ##########
$HELP['configlang']['title'] = '言語設定';
$HELP['configlang']['body'] = '言語に関する設定を行います。';
$HELP['configlang_list']['title'] = 'アクセス可能言語';
$HELP['configlang_list']['body'] = 'アクセス可能な言語を設定します。';
$HELP['configlang_name']['title'] = '言語';
$HELP['configlang_name']['body'] = '言語名です。';
$HELP['configlang_value']['title'] = '値';
$HELP['configlang_value']['body'] = '識別用の値です。';
$HELP['configlang_accept']['title'] = '許可';
$HELP['configlang_accept']['body'] = 'フロント画面で切り替え可能な言語にチェックを入れます。';
$HELP['configlang_available']['title'] = 'メニュー項目';
$HELP['configlang_available']['body'] = '管理画面でメニュー表示する言語にチェックを入れます。';

// ########## テーブルデータ編集 ##########
$HELP['edittable_new_btn']['title'] = '新規ボタン';
$HELP['edittable_new_btn']['body'] = '新規レコードを追加します。';
$HELP['edittable_edit_btn']['title'] = '編集ボタン';
$HELP['edittable_edit_btn']['body'] = '選択されているレコードを編集します。<br />レコードを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['edittable_del_btn']['title'] = '削除ボタン';
$HELP['edittable_del_btn']['body'] = '選択されているレコードを削除します。<br />レコードを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['edittable_ret_btn']['title'] = '戻るボタン';
$HELP['edittable_ret_btn']['body'] = 'レコード一覧へ戻ります。';

// ########## 置換文字列 ##########
$HELP['usercustom_list']['title'] = '置換文字列一覧';
$HELP['usercustom_list']['body'] = '置換文字列一覧です。置換文字列は、コンテンツテキストに埋め込み、コンテンツ表示時に自動変換される文字列です。';
$HELP['usercustom_detail']['title'] = '置換文字列詳細';
$HELP['usercustom_detail']['body'] = '置換文字列の情報を編集します。';
$HELP['usercustom_new_btn']['title'] = '新規ボタン';
$HELP['usercustom_new_btn']['body'] = '新規に置換文字列を追加します。';
$HELP['usercustom_edit_btn']['title'] = '編集ボタン';
$HELP['usercustom_edit_btn']['body'] = '選択されている置換文字列を編集します。<br />置換文字列を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['usercustom_del_btn']['title'] = '削除ボタン';
$HELP['usercustom_del_btn']['body'] = '選択されている置換文字列を削除します。<br />置換文字列を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['usercustom_ret_btn']['title'] = '戻るボタン';
$HELP['usercustom_ret_btn']['body'] = '置換文字列一覧へ戻ります。';
$HELP['usercustom_check']['title'] = '選択用チェックボックス';
$HELP['usercustom_check']['body'] = '編集や削除を行う項目を選択します。';

$HELP['usercustom_check2']['title'] = '選択用ラジオボタン';
$HELP['usercustom_check2']['body'] = '編集を行う項目を選択します。新規追加する場合は「新規追加」項目を選択します。';
$HELP['usercustom_key']['title'] = '置換キー文字列';
$HELP['usercustom_key']['body'] = 'コンテンツテキストに埋め込むキー文字列です。コンテンツ表示時に、この文字列が「置換内容」に変換されます。';
$HELP['usercustom_name']['title'] = '置換文字列名';
$HELP['usercustom_name']['body'] = '置換文字列の管理上の名前です。';
$HELP['usercustom_value']['title'] = '置換内容';
$HELP['usercustom_value']['body'] = 'コンテンツ表示時に、コンテンツ上の置換キーから変換される文字列です。';

// ########## ページ情報 ##########
$HELP['pageinfo']['title'] = 'ページ情報';
$HELP['pageinfo']['body'] = 'サイトのアクセス単位であるページについての情報を設定します。';
$HELP['pageinfo_list']['title'] = 'ページ情報一覧';
$HELP['pageinfo_list']['body'] = 'ページはアクセスポイントである「ページID」とサブパラメータの「ページサブID」のセットで一意に決まります。<br />ページサブIDはURLのクエリーパラメータ「sub=サブページID」で指定されるIDです。';
$HELP['pageinfo_detail']['title'] = 'ページ情報詳細';
$HELP['pageinfo_detail']['body'] = 'ページ情報を編集します。';

$HELP['pageinfo_check']['title'] = '選択用チェックボックス';
$HELP['pageinfo_check']['body'] = '編集を行う項目を選択します。';
$HELP['pageinfo_pageid']['title'] = 'ページID';
$HELP['pageinfo_pageid']['body'] = 'URLで実行されるスクリプトファイルがアクセスポイントであるページIDです。';
$HELP['pageinfo_subid']['title'] = 'ページサブID';
$HELP['pageinfo_subid']['body'] = 'URLのクエリーパラメータ「sub=サブページID」で指定するIDです。';
$HELP['pageinfo_name']['title'] = '名前';
$HELP['pageinfo_name']['body'] = '選択メニュー等で表示される名前です。';
$HELP['pageinfo_attr']['title'] = 'ページ属性';
$HELP['pageinfo_attr']['body'] = 'ページに表示されるメインコンテンツのデータ種別を指定します。';
$HELP['pageinfo_template']['title'] = 'テンプレート';
$HELP['pageinfo_template']['body'] = 'ページで使用するテンプレートです。設定しない場合はアクセスポイントのデフォルトテンプレートが使用されます。';
$HELP['pageinfo_public']['title'] = '公開';
$HELP['pageinfo_public']['body'] = '管理者以外のユーザがアクセス可能であるかどうかを示します。';
$HELP['pageinfo_ssl']['title'] = 'SSL';
$HELP['pageinfo_ssl']['body'] = 'SSLを使用してページを表示するかどうかを設定します。';
$HELP['pageinfo_user_limited']['title'] = 'ユーザ制限';
$HELP['pageinfo_user_limited']['body'] = 'ページにアクセスできるユーザをログインしたユーザに制限するかどうかを設定します。チェックが入っている場合は、ログインユーザのみがアクセス可能です。';
$HELP['pageinfo_default']['title'] = 'デフォルト';
$HELP['pageinfo_default']['body'] = 'デフォルトで選択されている項目は、ページサブIDが省略されたときに指定されるページサブIDです。';
$HELP['pageinfo_ref']['title'] = '使用';
$HELP['pageinfo_ref']['body'] = 'ページ上に存在するウィジェット数です。共通属性が設定されているウィジェットは含みません。';
$HELP['pageinfo_edit_btn']['title'] = '編集ボタン';
$HELP['pageinfo_edit_btn']['body'] = '選択されているページサブIDの情報を編集します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['pageinfo_ret_btn']['title'] = '戻るボタン';
$HELP['pageinfo_ret_btn']['body'] = 'ページ情報一覧へ戻ります。';

// ########## ページヘッダ情報 ##########
$HELP['pagehead']['title'] = 'ページヘッダ情報';
$HELP['pagehead']['body'] = 'HTMLのヘッダ部のmetaタグに出力する文字列を設定します。';
$HELP['pagehead_list']['title'] = 'ページヘッダ情報一覧';
$HELP['pagehead_list']['body'] = 'HTMLのHEADタグ部に出力されるヘッダ情報のページ単位の設定一覧です。<br />ページヘッダ情報の優先順位は、低いものからサイト情報のヘッダ情報、ページのヘッダ情報、ページに表示される個別のコンテンツのヘッダ情報の順です。優先順位の高いヘッダ情報の値が有効値になります。';
$HELP['pagehead_detail']['title'] = 'ページヘッダ情報詳細';
$HELP['pagehead_detail']['body'] = 'ページヘッダ情報を編集します。';

$HELP['pagehead_check']['title'] = '選択用チェックボックス';
$HELP['pagehead_check']['body'] = '編集を行う項目を選択します。';
$HELP['pagehead_pageid']['title'] = 'ページID';
$HELP['pagehead_pageid']['body'] = 'URLで実行されるスクリプトファイルがアクセスポイントであるページIDです。';
$HELP['pagehead_subid']['title'] = 'ページID';
$HELP['pagehead_subid']['body'] = 'URLのクエリーパラメータ「sub=ページID」で指定するIDです。';
$HELP['pagehead_name']['title'] = 'ページ名';
$HELP['pagehead_name']['body'] = 'ページの名前です。';
$HELP['pagehead_title']['title'] = 'タイトル名';
$HELP['pagehead_title']['body'] = 'ヘッダ部のtitleタグに設定される文字列です。Webブラウザの画面タイトルとして表示されます。';
$HELP['pagehead_description']['title'] = 'ページ説明';
$HELP['pagehead_description']['body'] = 'ページの説明のためにヘッダ部のdescriptionタグに設定される文字列です。120文字程度で記述します。<br />Googleでは検索結果に表示されます。';
$HELP['pagehead_keywords']['title'] = '検索キーワード';
$HELP['pagehead_keywords']['body'] = 'ヘッダ部のkeywordsタグに設定される文字列です。検索エンジン用のキーワードを「,」区切りで10個以下で記述します。';
$HELP['pagehead_others']['title'] = 'その他(タグ形式)';
$HELP['pagehead_others']['body'] = 'ヘッダ部に出力するタグを設定します。';
$HELP['pagehead_public']['title'] = '公開';
$HELP['pagehead_public']['body'] = '管理者以外のユーザがアクセス可能であるかどうかを示します。';
$HELP['pagehead_default']['title'] = 'デフォルト';
$HELP['pagehead_default']['body'] = 'デフォルトで選択されている項目は、ページサブIDが省略されたときに使用されるページサブIDです。';
$HELP['pagehead_edit_btn']['title'] = '編集ボタン';
$HELP['pagehead_edit_btn']['body'] = '選択されているページサブIDの情報を編集します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['pagehead_ret_btn']['title'] = '戻るボタン';
$HELP['pagehead_ret_btn']['body'] = 'ページ情報一覧へ戻ります。';

// ########## ページID ##########
$HELP['pageid']['title'] = 'ページID';
$HELP['pageid']['body'] = 'ページIDの編集を行います';
$HELP['pageid_list']['title'] = 'ページID一覧';
$HELP['pageid_list']['body'] = 'ページIDの一覧です。';
$HELP['pageid_detail']['title'] = 'ページID詳細';
$HELP['pageid_detail']['body'] = 'ページIDを編集します。';
$HELP['pageid_check']['title'] = '選択用チェックボックス';
$HELP['pageid_check']['body'] = '編集を行う項目を選択します。';
$HELP['pageid_id']['title'] = 'ページID';
$HELP['pageid_id']['body'] = 'ページIDを示します。';
$HELP['pageid_name']['title'] = '名前';
$HELP['pageid_name']['body'] = '選択メニュー等で表示される名前です。';
$HELP['pageid_desc']['title'] = '説明';
$HELP['pageid_desc']['body'] = '項目についての説明です。';
$HELP['pageid_path']['title'] = 'パス';
$HELP['pageid_path']['body'] = 'URLでのアクセスパスを示します。';
$HELP['pageid_priority']['title'] = '優先順';
$HELP['pageid_priority']['body'] = '項目の優先順を指定します。';
$HELP['pageid_visible']['title'] = '公開';
$HELP['pageid_visible']['body'] = 'ページを一般ユーザに公開するかどうかを指定します。非公開のページは管理者のみが閲覧できます。';
$HELP['pageid_active']['title'] = '有効';
$HELP['pageid_active']['body'] = 'ページIDを使用するかどうかを指定します。';
$HELP['pageid_new_btn']['title'] = '新規ボタン';
$HELP['pageid_new_btn']['body'] = '新規ページIDを追加します。';
$HELP['pageid_edit_btn']['title'] = '編集ボタン';
$HELP['pageid_edit_btn']['body'] = '選択されているページIDを編集します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['pageid_del_btn']['title'] = '削除ボタン';
$HELP['pageid_del_btn']['body'] = '選択されているページIDを削除します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['pageid_ret_btn']['title'] = '戻るボタン';
$HELP['pageid_ret_btn']['body'] = 'ページID一覧へ戻ります。';

// ########## アクセスポイント ##########
$HELP['accesspoint']['title'] = 'アクセスポイント';
$HELP['accesspoint']['body'] = 'アクセスポイントの情報の編集を行います';
$HELP['accesspoint_list']['title'] = 'アクセスポイント一覧';
$HELP['accesspoint_list']['body'] = 'アクセスポイントの一覧です。';
$HELP['accesspoint_detail']['title'] = 'アクセスポイント詳細';
$HELP['accesspoint_detail']['body'] = 'アクセスポイントを編集します。';
$HELP['accesspoint_check']['title'] = '選択用チェックボックス';
$HELP['accesspoint_check']['body'] = '編集を行う項目を選択します。';
$HELP['accesspoint_id']['title'] = 'ID';
$HELP['accesspoint_id']['body'] = 'アクセスポイントのIDを示します。';
$HELP['accesspoint_name']['title'] = '名前';
$HELP['accesspoint_name']['body'] = '選択メニュー等で表示される名前です。';
$HELP['accesspoint_desc']['title'] = '説明';
$HELP['accesspoint_desc']['body'] = '項目についての説明です。';
$HELP['accesspoint_path']['title'] = 'パス';
$HELP['accesspoint_path']['body'] = 'URLでのアクセスパスを示します。';
$HELP['accesspoint_priority']['title'] = '優先順';
$HELP['accesspoint_priority']['body'] = '項目の優先順を指定します。';
$HELP['accesspoint_active']['title'] = '有効';
$HELP['accesspoint_active']['body'] = 'フロント画面用のアクセスポイントを使用可能にするかどうかを指定します。';
$HELP['accesspoint_edit_btn']['title'] = '編集ボタン';
$HELP['accesspoint_edit_btn']['body'] = '選択されているアクセスポイントを編集します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['accesspoint_ret_btn']['title'] = '戻るボタン';
$HELP['accesspoint_ret_btn']['body'] = 'アクセスポイント一覧へ戻ります。';

// ########## 運用ログ ##########
$HELP['opelog_list']['title'] = '運用ログ一覧';
$HELP['opelog_list']['body'] = '運用ログ一覧です。最新のメッセージから順にシステム運用状況のログを表示します。';
$HELP['opelog_detail']['title'] = '運用ログ詳細';
$HELP['opelog_detail']['body'] = '運用ログの情報を編集します。';
$HELP['opelog_log_level']['title'] = '表示ログ種別';
$HELP['opelog_log_level']['body'] = '一覧に表示するログをメッセージのレベルで制限します。メッセージは「要確認」または「通常」レベルのどちらかです。「要確認」レベルは確認する必要がある項目です。「通常」レベルは特に確認の必要のない項目です。';
$HELP['opelog_log_status']['title'] = '表示ログステータス';
$HELP['opelog_log_status']['body'] = '一覧に表示するログを運用ログの「確認」状況によって制限します。';
$HELP['opelog_check']['title'] = '選択用チェックボックス';
$HELP['opelog_check']['body'] = '編集を行う項目を選択します。';
$HELP['opelog_message_type']['title'] = 'メッセージ種別';
$HELP['opelog_message_type']['body'] = 'メッセージの種別を示します。メッセージの種別は、システム情報(システム運用の正常な動作を示す)、システム警告(システム運用の注意が必要な動作を示す)、システム通常エラー(システム運用の異常な動作を示す)、システム致命的エラー(システム運用の致命的に異常な動作を示す)、ユーザ操作(ユーザ操作の正常な動作を示す)、ユーザ操作エラー(ユーザ操作の異常な動作を示す)、ユーザ不正アクセス(ユーザ操作の不正なアクセスを示す)、ユーザ不正データ(ユーザ操作の不正なデータ送信を示す)があります。';
$HELP['opelog_message']['title'] = 'メッセージ';
$HELP['opelog_message']['body'] = 'ログメッセージを示します。';
$HELP['opelog_message_detail']['title'] = 'メッセージ詳細';
$HELP['opelog_message_detail']['body'] = '詳細なログメッセージを示します。';
$HELP['opelog_message_code']['title'] = 'メッセージコード';
$HELP['opelog_message_code']['body'] = 'ログメッセージの識別コードです。';
$HELP['opelog_ip']['title'] = 'IP';
$HELP['opelog_ip']['body'] = '処理を実行したクライアントのIPを示します。';
$HELP['opelog_access_log']['title'] = 'アクセスログ番号';
$HELP['opelog_access_log']['body'] = 'アクセスログのシリアル番号を示します。';
$HELP['opelog_message_check']['title'] = 'メッセージ確認状況';
$HELP['opelog_message_check']['body'] = 'メッセージの確認状況を示します。';
$HELP['opelog_message_dt']['title'] = '日時';
$HELP['opelog_message_dt']['body'] = 'ログを出力した日時です。';
$HELP['opelog_edit_btn']['title'] = '編集ボタン';
$HELP['opelog_edit_btn']['body'] = '選択されている運用ログを編集します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['opelog_ret_btn']['title'] = '戻るボタン';
$HELP['opelog_ret_btn']['body'] = '運用ログ一覧へ戻ります。';

// ########## アクセスログ ##########
$HELP['accesslog_list']['title'] = 'アクセスログ一覧';
$HELP['accesslog_list']['body'] = 'アクセスログ一覧です。最新のメッセージから順にシステム運用状況のログを表示します。';
$HELP['accesslog_detail']['title'] = 'アクセスログ詳細';
$HELP['accesslog_detail']['body'] = 'アクセスログの情報を編集します。';
$HELP['accesslog_check']['title'] = '選択用チェックボックス';
$HELP['accesslog_check']['body'] = '詳細表示を行う項目を選択します。';
$HELP['accesslog_no']['title'] = 'アクセスログ番号';
$HELP['accesslog_no']['body'] = 'アクセスログのシリアル番号です。';
$HELP['accesslog_uri']['title'] = 'URI';
$HELP['accesslog_uri']['body'] = 'アクセス先のURIです。';
$HELP['accesslog_country']['title'] = '国';
$HELP['accesslog_country']['body'] = 'ブラウザの使用言語から判断したユーザの所属国です。';
$HELP['accesslog_browser']['title'] = '種別';
$HELP['accesslog_browser']['body'] = 'USER_AGENTから判断したWebブラウザ、クローラ等の種別です。';
$HELP['accesslog_os']['title'] = 'OS';
$HELP['accesslog_os']['body'] = 'USER_AGENTから判断したOS種別です。';
$HELP['accesslog_referer']['title'] = 'REFERER';
$HELP['accesslog_referer']['body'] = '現在のページに遷移する前に参照していたURIです。';
$HELP['accesslog_request']['title'] = 'REQUEST';
$HELP['accesslog_request']['body'] = 'クライアントからの送信データです。';
$HELP['accesslog_agent']['title'] = 'AGENT';
$HELP['accesslog_agent']['body'] = 'リクエストヘッダの「User-Agent:」の値です。';
$HELP['accesslog_language']['title'] = 'LANGUAGE';
$HELP['accesslog_language']['body'] = 'リクエストヘッダの「Accept-Language:」の値です。';
$HELP['accesslog_method']['title'] = 'メソッド';
$HELP['accesslog_method']['body'] = '送信メソッドです。';
$HELP['accesslog_cookie']['title'] = 'クッキー値';
$HELP['accesslog_cookie']['body'] = 'ユーザ識別用のクッキー値です。';
$HELP['accesslog_ip']['title'] = 'アクセス元IP';
$HELP['accesslog_ip']['body'] = 'クライアントのIPアドレスです。';
$HELP['accesslog_user']['title'] = 'ユーザ名';
$HELP['accesslog_user']['body'] = 'クッキーからユーザが識別可能な場合はユーザ名を示します。';
$HELP['accesslog_dt']['title'] = '日時';
$HELP['accesslog_dt']['body'] = 'ログを出力した日時です。';
$HELP['accesslog_edit_btn']['title'] = '編集ボタン';
$HELP['accesslog_edit_btn']['body'] = '選択されているアクセスログの詳細を参照します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['accesslog_ret_btn']['title'] = '戻るボタン';
$HELP['accesslog_ret_btn']['body'] = 'アクセスログ一覧へ戻ります。';

// ########## 検索語ログ ##########
$HELP['searchwordlog_list']['title'] = '検索語ログ一覧';
$HELP['searchwordlog_list']['body'] = '検索語ログ一覧です。最新のメッセージから順にシステム運用状況のログを表示します。';
$HELP['searchwordlog_detail']['title'] = '検索語ログ詳細';
$HELP['searchwordlog_detail']['body'] = '検索語ログの情報を編集します。';
$HELP['searchwordlog_check']['title'] = '選択用チェックボックス';
$HELP['searchwordlog_check']['body'] = '詳細表示を行う項目を選択します。';
$HELP['searchwordlog_no']['title'] = '番号';
$HELP['searchwordlog_no']['body'] = '表示番号です。';
$HELP['searchwordlog_word']['title'] = '検索語';
$HELP['searchwordlog_word']['body'] = '検索された文字列です。';
$HELP['searchwordlog_compare_word']['title'] = '比較語';
$HELP['searchwordlog_compare_word']['body'] = '同じ検索語とみなす比較用文字列です。';
$HELP['searchwordlog_country']['title'] = '国';
$HELP['searchwordlog_country']['body'] = 'ブラウザの使用言語から判断したユーザの所属国です。';
$HELP['searchwordlog_browser']['title'] = '種別';
$HELP['searchwordlog_browser']['body'] = 'USER_AGENTから判断したWebブラウザ、クローラ等の種別です。';
$HELP['searchwordlog_access_log']['title'] = 'アクセスログ番号';
$HELP['searchwordlog_access_log']['body'] = 'アクセスログのシリアル番号を示します。';
$HELP['searchwordlog_method']['title'] = 'メソッド';
$HELP['searchwordlog_method']['body'] = '送信メソッドです。';
$HELP['searchwordlog_cookie']['title'] = 'クッキー値';
$HELP['searchwordlog_cookie']['body'] = 'ユーザ識別用のクッキー値です。';
$HELP['searchwordlog_user']['title'] = 'ユーザ名';
$HELP['searchwordlog_user']['body'] = 'クッキーからユーザが識別可能な場合はユーザ名を示します。';
$HELP['searchwordlog_dt']['title'] = '日時';
$HELP['searchwordlog_dt']['body'] = 'ログを出力した日時です。';
$HELP['searchwordlog_edit_btn']['title'] = '編集ボタン';
$HELP['searchwordlog_edit_btn']['body'] = '選択されている検索語ログの詳細を参照します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['searchwordlog_ret_btn']['title'] = '戻るボタン';
$HELP['searchwordlog_ret_btn']['body'] = '検索語ログ一覧へ戻ります。';

// ########## メニューID ##########
$HELP['menuid']['title'] = 'メニューID';
$HELP['menuid']['body'] = 'メニュー定義に使用するメニューIDの管理を行います。';
$HELP['menuid_list']['title'] = 'メニューID一覧';
$HELP['menuid_list']['body'] = 'メニュー定義に使用するメニューIDの管理を行います。';
$HELP['menuid_detail']['title'] = 'メニューID詳細';
$HELP['menuid_detail']['body'] = 'メニューIDを編集します。';
$HELP['menuid_check']['title'] = '選択用チェックボックス';
$HELP['menuid_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['menuid_id']['title'] = 'メニューID';
$HELP['menuid_id']['body'] = 'メニューIDを示します。';
$HELP['menuid_name']['title'] = '名前';
$HELP['menuid_name']['body'] = '選択メニュー等で表示される名前です。';
$HELP['menuid_desc']['title'] = '説明';
$HELP['menuid_desc']['body'] = '項目についての説明です。';
$HELP['menuid_priority']['title'] = '優先順';
$HELP['menuid_priority']['body'] = '項目の優先順を指定します。';
$HELP['menuid_ref']['title'] = '使用';
$HELP['menuid_ref']['body'] = 'メニューIDを使用しているメニュー型ウィジェットの総数を示します。「使用」が0のメニューIDのみ削除可能です。';
$HELP['menuid_new_btn']['title'] = '新規ボタン';
$HELP['menuid_new_btn']['body'] = '新規メニューIDを追加します。';
$HELP['menuid_edit_btn']['title'] = '編集ボタン';
$HELP['menuid_edit_btn']['body'] = '選択されているメニューIDを編集します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['menuid_del_btn']['title'] = '削除ボタン';
$HELP['menuid_del_btn']['body'] = '選択されているメニューIDを削除します。<br />メニューIDを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['menuid_ret_btn']['title'] = '戻るボタン';
$HELP['menuid_ret_btn']['body'] = 'メニューID一覧へ戻ります。';

// ########## テナントサーバ管理 ##########
$HELP['tenantserver']['title'] = 'テナントサーバ管理';
$HELP['tenantserver']['body'] = 'テナントサーバの管理を行います';
$HELP['tenantserver_list']['title'] = 'テナントサーバ一覧';
$HELP['tenantserver_list']['body'] = 'テナントサーバの一覧です。';
$HELP['tenantserver_detail']['title'] = 'テナントサーバ詳細';
$HELP['tenantserver_detail']['body'] = 'テナントサーバの情報を編集します。';
$HELP['tenantserver_check']['title'] = '選択用チェックボックス';
$HELP['tenantserver_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['tenantserver_name']['title'] = '名前';
$HELP['tenantserver_name']['body'] = 'サーバ識別用の名前です。任意に設定します。';
$HELP['tenantserver_server_id']['title'] = 'サーバID';
$HELP['tenantserver_server_id']['body'] = 'サーバ識別用のIDです。システム情報の「サーバID」の値です。';
$HELP['tenantserver_ip']['title'] = 'IPアドレス';
$HELP['tenantserver_ip']['body'] = 'サーバのIPアドレスです。';
$HELP['tenantserver_url']['title'] = 'URL';
$HELP['tenantserver_url']['body'] = 'サーバのURLです。';
$HELP['tenantserver_access']['title'] = 'ポータル接続可';
$HELP['tenantserver_access']['body'] = 'ポータルサーバに接続可能かどうかを設定します。一時的に接続不可にする場合等に使用します。';
$HELP['tenantserver_update_dt']['title'] = '更新日時';
$HELP['tenantserver_update_dt']['body'] = 'サーバ情報の更新日時です。';
$HELP['tenantserver_db_info']['title'] = 'DB接続情報';
$HELP['tenantserver_db_info']['body'] = 'サーバが使用しているDBの情報を設定します。DBに接続しない場合は空欄にします。';
$HELP['tenantserver_db_user']['title'] = 'DB接続ユーザ';
$HELP['tenantserver_db_user']['body'] = 'DB接続用のユーザ/パスワードを設定します。';
$HELP['tenantserver_test_db']['title'] = 'DB接続テスト';
$HELP['tenantserver_test_db']['body'] = 'DB接続情報でDB接続をテストします。';
$HELP['tenantserver_new_btn']['title'] = '新規ボタン';
$HELP['tenantserver_new_btn']['body'] = '新規にサーバ情報を追加します。';
$HELP['tenantserver_edit_btn']['title'] = '編集ボタン';
$HELP['tenantserver_edit_btn']['body'] = '選択されているサーバ情報を編集します。<br />選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['tenantserver_del_btn']['title'] = '削除ボタン';
$HELP['tenantserver_del_btn']['body'] = '選択されているサーバ情報を削除します。<br />サーバ情報を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['tenantserver_ret_btn']['title'] = '戻るボタン';
$HELP['tenantserver_ret_btn']['body'] = 'テナントサーバ一覧へ戻ります。';

// ########## ファイルブラウザ ##########
$HELP['resbrowse']['title'] = 'ファイルブラウザ';
$HELP['resbrowse']['body'] = 'サーバ上の各種ファイル操作を行います。';

// ########## DBデータ初期化 ##########
$HELP['initsystem']['title'] = 'DBデータ初期化';
$HELP['initsystem']['body'] = 'DBのデータを初期化します。';

// ########## DBバックアップ ##########
$HELP['dbbackup']['title'] = 'DBバックアップ';
$HELP['dbbackup']['body'] = 'DBのデータをバックアップします。';

// ########## 管理画面カスタムウィザード ##########
$HELP['initwizard_site_name']['title'] = 'サイト名';
$HELP['initwizard_site_name']['body'] = 'サイトの名前を設定します。';
$HELP['initwizard_site_email']['title'] = 'Eメール';
$HELP['initwizard_site_email']['body'] = 'このサイトのデフォルトのEメールアドレスです。このアドレスは必須項目です。「name@example.com」や「名前&lt; name@example.com&gt;」の設定が可能です。<br />このアドレスは、システムからユーザへ送信する場合の送信元アドレスとして、またはこのシステム上でユーザがメールを送信した場合に送信先アドレスとして使用されます。ユーザからのメール送信先は、次のフォーマットで「CC」や「BCC」でのメール送信も可能です。フォーマット「アドレス1;cc:アドレス2;bcc:アドレス3」。';
$HELP['initwizard_site_description']['title'] = 'サイト説明';
$HELP['initwizard_site_description']['body'] = 'サイトの説明のためにヘッダ部のdescriptionタグに設定される文字列です。120文字程度で記述します。<br />Googleでは検索結果に表示されます。';
$HELP['initwizard_site_keyword']['title'] = '検索キーワード';
$HELP['initwizard_site_keyword']['body'] = 'ヘッダ部のkeywordsタグに設定される文字列です。検索エンジン用のキーワードを「,」区切りで10個以下で記述します。';
$HELP['initwizard_admin_name']['title'] = '管理者名';
$HELP['initwizard_admin_name']['body'] = '画面上に表示される管理者の名前です。';
$HELP['initwizard_admin_account']['title'] = 'アカウント';
$HELP['initwizard_admin_account']['body'] = 'ログインに使用するアカウントです。';
$HELP['initwizard_admin_password']['title'] = 'パスワード';
$HELP['initwizard_admin_password']['body'] = 'ログインに使用するパスワードです。';
$HELP['initwizard_admin_email']['title'] = 'Eメール';
$HELP['initwizard_admin_email']['body'] = '管理者のメールアドレスです。パスワード再送などで使用されます。';

// ########## サイト管理 ##########
$HELP['sitelist_host_name']['title'] = 'ホスト名';
$HELP['sitelist_host_name']['body'] = 'サイトを識別するための名前を設定します。ホスト名は運用するサイトのURL http://xxxxx.xxxxx.xxxxx/ の「xxxxx.xxxxx.xxxxx」の部分を設定します。';
?>
