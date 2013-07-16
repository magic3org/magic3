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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: index.php 5547 2013-01-14 08:59:13Z fishbone $
 * @link       http://www.magic3.org
 */
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

global $HELP;

// ########## カテゴリー管理 ##########
$HELP['productcategory_list']['title'] = '商品カテゴリー一覧';
$HELP['productcategory_list']['body'] = '商品カテゴリー一覧です。商品カテゴリーは商品のカテゴリー分けに使用します。';
$HELP['productcategory_detail']['title'] = '商品カテゴリー詳細';
$HELP['productcategory_detail']['body'] = '商品カテゴリーの情報を編集します。';
$HELP['productcategory_new_btn']['title'] = '新規ボタン';
$HELP['productcategory_new_btn']['body'] = '新規に商品カテゴリーを追加します。';
$HELP['productcategory_edit_btn']['title'] = '編集ボタン';
$HELP['productcategory_edit_btn']['body'] = '選択されている商品カテゴリーを編集します。<br />商品カテゴリーを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['productcategory_del_btn']['title'] = '削除ボタン';
$HELP['productcategory_del_btn']['body'] = '選択されている商品カテゴリーを削除します。<br />商品カテゴリーを選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['productcategory_ret_btn']['title'] = '戻るボタン';
$HELP['productcategory_ret_btn']['body'] = '商品カテゴリー一覧へ戻ります。';
$HELP['productcategory_check']['title'] = '選択用チェックボックス';
$HELP['productcategory_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['productcategory_id']['title'] = '商品カテゴリーID';
$HELP['productcategory_id']['body'] = '商品カテゴリーのIDです。';
$HELP['productcategory_name']['title'] = '商品カテゴリー名';
$HELP['productcategory_name']['body'] = '商品カテゴリーの名前です。';
$HELP['productcategory_parent_name']['title'] = '商品親カテゴリー名';
$HELP['productcategory_parent_name']['body'] = '親の商品カテゴリーの名前です。商品カテゴリーを階層化する場合に使用します。';
$HELP['productcategory_index']['title'] = '表示順';
$HELP['productcategory_index']['body'] = '商品カテゴリーを一覧表示する際の表示順です。';
$HELP['productcategory_visible']['title'] = '公開';
$HELP['productcategory_visible']['body'] = '商品カテゴリーを購入者に公開するかどうかを制御します。';

// ########## 商品管理 ##########
$HELP['product_list']['title'] = '商品一覧';
$HELP['product_list']['body'] = '商品一覧です。';
$HELP['product_detail']['title'] = '商品詳細';
$HELP['product_detail']['body'] = '商品の情報を編集します。';
$HELP['product_search']['title'] = '商品情報検索';
$HELP['product_search']['body'] = '商品情報を検索します。';
$HELP['product_detail_btn']['title'] = '商品詳細ボタン';
$HELP['product_detail_btn']['body'] = '商品の詳細情報を表示します。';
$HELP['product_new_btn']['title'] = '新規ボタン';
$HELP['product_new_btn']['body'] = '新規に商品を追加します。';
$HELP['product_edit_btn']['title'] = '編集ボタン';
$HELP['product_edit_btn']['body'] = '選択されている商品を編集します。<br />商品を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['product_del_btn']['title'] = '削除ボタン';
$HELP['product_del_btn']['body'] = '選択されている商品を削除します。<br />商品を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['product_ret_btn']['title'] = '戻るボタン';
$HELP['product_ret_btn']['body'] = '商品一覧へ戻ります。';
$HELP['product_search_btn']['title'] = '商品情報検索ボタン';
$HELP['product_search_btn']['body'] = '商品情報を検索します。';
$HELP['product_check']['title'] = '選択用チェックボックス';
$HELP['product_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['product_id']['title'] = '商品ID';
$HELP['product_id']['body'] = '商品のIDです。商品IDは新規登録時に自動生成されます。';
$HELP['product_name']['title'] = '商品名';
$HELP['product_name']['body'] = '商品の名前です。';
$HELP['product_code']['title'] = '商品コード';
$HELP['product_code']['body'] = '商品のコードです。半角英数字で任意に設定できます。';
$HELP['product_index']['title'] = '表示順';
$HELP['product_index']['body'] = '商品を一覧表示する際の表示順です。';
$HELP['product_visible']['title'] = '公開';
$HELP['product_visible']['body'] = '商品を購入者に公開するかどうかを制御します。';
$HELP['product_status']['title'] = '商品ステータス';
$HELP['product_status']['body'] = '商品の各種状態を設定します。「新着おすすめ」ウィジェット等で、ステータスから表示する商品を取得します。';
$HELP['product_price']['title'] = '販売価格';
$HELP['product_price']['body'] = '商品の販売価格です。税抜価格を設定すると、税率と課税種別から税込価格が自動計算されます。';
$HELP['product_price_with_tax']['title'] = '税込価格';
$HELP['product_price_with_tax']['body'] = '商品の税込販売価格です。';
$HELP['product_stock_count']['title'] = '在庫数';
$HELP['product_stock_count']['body'] = '商品の在庫数です。基本設定で「在庫自動処理」がチェックされている場合は購入時に自動的に更新されます。';
$HELP['product_category']['title'] = '商品カテゴリー';
$HELP['product_category']['body'] = '商品の所属する商品カテゴリーを設定します。<br />1番目のカテゴリーは主カテゴリーです。「商品メニュー」ウィジェット等で、商品が選択された場合にデフォルトで選択されるカテゴリーです。<br />選択可能なカテゴリー数は基本情報の「商品カテゴリー選択可能数」で設定します。';
$HELP['product_image_small']['title'] = '商品画像(小)';
$HELP['product_image_small']['body'] = '商品画像です。カートに表示されます。<br />画像を変更するには、アイコンをクリックすると表示されるアップロードエリアを使用します。画像のアップロードは、アップロードエリアに画像をドラッグ&ドロップするか、エリアをクリックして表示されるダイアログで画像を選択します。';
$HELP['product_image_middle']['title'] = '商品画像(標準)';
$HELP['product_image_middle']['body'] = '商品画像です。商品一覧画面に表示されます。<br />画像を変更するには、アイコンをクリックすると表示されるアップロードエリアを使用します。画像のアップロードは、アップロードエリアに画像をドラッグ&ドロップするか、エリアをクリックして表示されるダイアログで画像を選択します。';
$HELP['product_image_large']['title'] = '商品画像(大)';
$HELP['product_image_large']['body'] = '商品画像です。商品詳細画面に表示されます。<br />画像を変更するには、アイコンをクリックすると表示されるアップロードエリアを使用します。画像のアップロードは、アップロードエリアに画像をドラッグ&ドロップするか、エリアをクリックして表示されるダイアログで画像を選択します。';
$HELP['product_image_all']['title'] = '商品画像(全)';
$HELP['product_image_all']['body'] = '画像アップロード専用のエリアです。一度に小、標準、大のすべての商品画像がアップロードできます。<br />画像を変更するには、アイコンをクリックすると表示されるアップロードエリアを使用します。画像のアップロードは、アップロードエリアに画像をドラッグ&ドロップするか、エリアをクリックして表示されるダイアログで画像を選択します。';
$HELP['product_update_user']['title'] = '更新者';
$HELP['product_update_user']['body'] = '商品情報を更新したユーザです。';
$HELP['product_update_date']['title'] = '更新日時';
$HELP['product_update_date']['body'] = '商品情報の更新日時です。';
$HELP['product_meta_keywords']['title'] = '検索キーワード';
$HELP['product_meta_keywords']['body'] = 'ヘッダ部のkeywordsタグに設定される文字列です。検索エンジン用のキーワードを「,」区切りで10個以下で記述します。';
$HELP['product_desc']['title'] = '説明';
$HELP['product_desc']['body'] = '商品の詳細説明です。商品の詳細画面で表示されます。';
$HELP['product_desc_short']['title'] = '説明(簡易)';
$HELP['product_desc_short']['body'] = '商品の簡易説明です。商品の一覧画面、およびヘッダ部のdescriptionタグに設定されます。120文字程度で記述します。Googleでは検索結果に表示されます。';
$HELP['product_admin_note']['title'] = '管理者用備考';
$HELP['product_admin_note']['body'] = '任意に設定可能な管理者用の備考欄です。';

// ########## 配送方法 ##########
$HELP['delivmethod_list']['title'] = '配送方法一覧';
$HELP['delivmethod_list']['body'] = '配送方法一覧です。配送方法は、購入者が商品の配送方法を選択するための項目です。';
$HELP['delivmethod_detail']['title'] = '配送方法詳細';
$HELP['delivmethod_detail']['body'] = '配送方法の情報を編集します。';
$HELP['delivmethod_new_btn']['title'] = '新規ボタン';
$HELP['delivmethod_new_btn']['body'] = '新規に配送方法を追加します。';
$HELP['delivmethod_edit_btn']['title'] = '編集ボタン';
$HELP['delivmethod_edit_btn']['body'] = '選択されている配送方法を編集します。<br />配送方法を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['delivmethod_del_btn']['title'] = '削除ボタン';
$HELP['delivmethod_del_btn']['body'] = '選択されている配送方法を削除します。<br />配送方法を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['delivmethod_ret_btn']['title'] = '戻るボタン';
$HELP['delivmethod_ret_btn']['body'] = '配送方法一覧へ戻ります。';
$HELP['delivmethod_preview_btn']['title'] = 'プレビューボタン';
$HELP['delivmethod_preview_btn']['body'] = '購入者が配送方法を選択するときの実際の画面です。';
$HELP['delivmethod_check']['title'] = '選択用チェックボックス';
$HELP['delivmethod_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['delivmethod_id']['title'] = '配送方法ID';
$HELP['delivmethod_id']['body'] = '配送方法のIDです。IDは新規登録時に任意のアルファベット文字で設定します。';
$HELP['delivmethod_name']['title'] = '配送方法名';
$HELP['delivmethod_name']['body'] = '配送方法の名前です。';
$HELP['delivmethod_index']['title'] = '表示順';
$HELP['delivmethod_index']['body'] = '配送方法を一覧表示する際の表示順です。';
$HELP['delivmethod_visible']['title'] = '公開';
$HELP['delivmethod_visible']['body'] = '配送方法を購入者に公開するかどうかを制御します。';
$HELP['delivmethod_desc']['title'] = '説明';
$HELP['delivmethod_desc']['body'] = '購入者が配送方法を選択するときの詳細説明です。';
$HELP['delivmethod_calc']['title'] = '料金計算方法';
$HELP['delivmethod_calc']['body'] = '配送料金を自動計算する場合はこの選択肢から選びます。';
$HELP['delivmethod_calc_detail']['title'] = '料金計算詳細';
$HELP['delivmethod_calc_detail']['body'] = '配送料金を自動計算する場合の詳細設定です。';

// ########## 支払方法 ##########
$HELP['paymethod_list']['title'] = '支払方法一覧';
$HELP['paymethod_list']['body'] = '支払方法一覧です。支払方法は、購入者が商品の支払方法を選択するための項目です。';
$HELP['paymethod_detail']['title'] = '支払方法詳細';
$HELP['paymethod_detail']['body'] = '支払方法の情報を編集します。';
$HELP['paymethod_new_btn']['title'] = '新規ボタン';
$HELP['paymethod_new_btn']['body'] = '新規に支払方法を追加します。';
$HELP['paymethod_edit_btn']['title'] = '編集ボタン';
$HELP['paymethod_edit_btn']['body'] = '選択されている支払方法を編集します。<br />支払方法を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['paymethod_del_btn']['title'] = '削除ボタン';
$HELP['paymethod_del_btn']['body'] = '選択されている支払方法を削除します。<br />支払方法を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['paymethod_ret_btn']['title'] = '戻るボタン';
$HELP['paymethod_ret_btn']['body'] = '支払方法一覧へ戻ります。';
$HELP['paymethod_preview_btn']['title'] = 'プレビューボタン';
$HELP['paymethod_preview_btn']['body'] = '購入者が支払方法を選択するときの実際の画面です。';
$HELP['paymethod_check']['title'] = '選択用チェックボックス';
$HELP['paymethod_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['paymethod_id']['title'] = '支払方法ID';
$HELP['paymethod_id']['body'] = '支払方法のIDです。IDは新規登録時に任意のアルファベット文字で設定します。';
$HELP['paymethod_name']['title'] = '支払方法名';
$HELP['paymethod_name']['body'] = '支払方法の名前です。';
$HELP['paymethod_index']['title'] = '表示順';
$HELP['paymethod_index']['body'] = '支払方法を一覧表示する際の表示順です。';
$HELP['paymethod_visible']['title'] = '公開';
$HELP['paymethod_visible']['body'] = '支払方法を購入者に公開するかどうかを制御します。';
$HELP['paymethod_desc']['title'] = '説明';
$HELP['paymethod_desc']['body'] = '購入者が支払方法を選択するときの詳細説明です。';
$HELP['paymethod_calc']['title'] = '料金計算方法';
$HELP['paymethod_calc']['body'] = '支払手数料を自動計算する場合はこの選択肢から選びます。';
$HELP['paymethod_calc_detail']['title'] = '料金計算詳細';
$HELP['paymethod_calc_detail']['body'] = '支払手数料を自動計算する場合の詳細設定です。';

// ########## 置換文字列 ##########
$HELP['keyword_list']['title'] = '置換文字列一覧';
$HELP['keyword_list']['body'] = '置換文字列一覧です。置換文字列は、コンテンツテキストに埋め込み、コンテンツ表示時に自動変換される文字列です。';
$HELP['keyword_detail']['title'] = '置換文字列詳細';
$HELP['keyword_detail']['body'] = '置換文字列の情報を編集します。';
$HELP['keyword_new_btn']['title'] = '新規ボタン';
$HELP['keyword_new_btn']['body'] = '新規に置換文字列を追加します。';
$HELP['keyword_edit_btn']['title'] = '編集ボタン';
$HELP['keyword_edit_btn']['body'] = '選択されている置換文字列を編集します。<br />置換文字列を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['keyword_del_btn']['title'] = '削除ボタン';
$HELP['keyword_del_btn']['body'] = '選択されている置換文字列を削除します。<br />置換文字列を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['keyword_ret_btn']['title'] = '戻るボタン';
$HELP['keyword_ret_btn']['body'] = '置換文字列一覧へ戻ります。';
$HELP['keyword_check']['title'] = '選択用チェックボックス';
$HELP['keyword_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['keyword_key']['title'] = '置換キー文字列';
$HELP['keyword_key']['body'] = 'コンテンツテキストに埋め込むキー文字列です。コンテンツ表示時に、この文字列が「置換内容」に変換されます。';
$HELP['keyword_name']['title'] = '置換文字列名';
$HELP['keyword_name']['body'] = '置換文字列の管理上の名前です。';
$HELP['keyword_value']['title'] = '置換内容';
$HELP['keyword_value']['body'] = 'コンテンツ表示時に、コンテンツ上の置換キーから変換される文字列です。';

// ########## 会員管理 ##########
$HELP['member_list']['title'] = 'Eコマース会員一覧';
$HELP['member_list']['body'] = 'Eコマース会員一覧です。右のメニューから「正会員」「仮会員」の種別を選択します。自分で会員登録を行った会員は、一旦仮会員で登録された後、最初のログインで自動的に正会員に変更されます。';
$HELP['member_detail']['title'] = 'Eコマース会員詳細';
$HELP['member_detail']['body'] = 'Eコマース会員の情報を設定します。';
$HELP['member_csv']['title'] = 'Eコマース会員CSVデータ';
$HELP['member_csv']['body'] = 'Eコマースの正会員の情報をCSV形式でアップロードやダウンロードを行います。';
$HELP['member_new_btn']['title'] = '新規ボタン';
$HELP['member_new_btn']['body'] = '新規にEコマース会員を追加します。';
$HELP['member_edit_btn']['title'] = '編集ボタン';
$HELP['member_edit_btn']['body'] = '選択されているEコマース会員を編集します。<br />会員を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['member_del_btn']['title'] = '削除ボタン';
$HELP['member_del_btn']['body'] = '選択されているEコマース会員を削除します。<br />会員を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['member_ret_btn']['title'] = '戻るボタン';
$HELP['member_ret_btn']['body'] = 'Eコマース会員一覧へ戻ります。';
$HELP['member_csv_download_btn']['title'] = '会員CSVデータダウンロード';
$HELP['member_csv_download_btn']['body'] = 'Eコマース正会員の情報をCSV形式のファイルで一括ダウンロードします。';
$HELP['member_csv_upload_btn']['title'] = '会員CSVデータアップロード';
$HELP['member_csv_upload_btn']['body'] = 'Eコマース正会員の情報をCSV形式のファイルで一括アップロードします。会員Noがすでに存在するデータは、アップロードするデータで更新されます。アップロードするファイルを左の参照ボタンから選択してください。';
$HELP['member_check']['title'] = '選択用チェックボックス';
$HELP['member_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['member_view_no']['title'] = 'No';
$HELP['member_view_no']['body'] = '項目番号';
$HELP['member_no']['title'] = '会員No';
$HELP['member_no']['body'] = '任意設定可能な会員Noです。';
$HELP['member_name']['title'] = '会員名';
$HELP['member_name']['body'] = '会員名を設定します。';
$HELP['member_name_kana']['title'] = '会員名カナ';
$HELP['member_name_kana']['body'] = '会員名の読みカナを設定します。';
$HELP['member_email']['title'] = 'Eメール';
$HELP['member_email']['body'] = '会員のEメールアドレスを設定します。';
$HELP['member_gender']['title'] = '会員性別';
$HELP['member_gender']['body'] = '会員の性別を設定します。';
$HELP['member_state']['title'] = '都道府県';
$HELP['member_state']['body'] = '会員の住所の都道府県です。';
$HELP['member_zip']['title'] = '郵便番号';
$HELP['member_zip']['body'] = '会員の郵便番号です。';
$HELP['member_add1']['title'] = '住所1';
$HELP['member_add1']['body'] = '会員の住所の市区町村を設定します。';
$HELP['member_add2']['title'] = '住所2';
$HELP['member_add2']['body'] = '会員の住所の建物名以降を設定します。';
$HELP['member_phone']['title'] = '電話番号';
$HELP['member_phone']['body'] = '会員の電話番号を設定します。';
$HELP['member_fax']['title'] = 'FAX';
$HELP['member_fax']['body'] = '会員のFAX番号を設定します。';
$HELP['member_mobile']['title'] = '携帯電話';
$HELP['member_mobile']['body'] = '会員の携帯電話番号を設定します。';
$HELP['member_birth']['title'] = '生年月日';
$HELP['member_birth']['body'] = '会員の生年月日を設定します。';
$HELP['member_pwd']['title'] = 'パスワード';
$HELP['member_pwd']['body'] = 'パスワードを再作成して、会員にメールで送信します。会員はログインを行って、任意のパスワードを設定します。';

// ########## 受注管理 ##########
$HELP['order_list']['title'] = '受注一覧';
$HELP['order_list']['body'] = '受注情報の一覧を表示します。';
$HELP['order_detail']['title'] = '受注詳細';
$HELP['order_detail']['body'] = '受注情報の詳細です。';
$HELP['order_search']['title'] = '受注情報検索';
$HELP['order_search']['body'] = '受注情報を検索します。';
$HELP['order_deliv']['title'] = '配送先';
$HELP['order_deliv']['body'] = '商品の配送先を示します。';
$HELP['order_bill']['title'] = '請求先';
$HELP['order_bill']['body'] = '支払の請求先を示します。';
$HELP['order_content']['title'] = '受注内容';
$HELP['order_content']['body'] = '受注内容を示します。';

$HELP['order_new_btn']['title'] = '新規ボタン';
$HELP['order_new_btn']['body'] = '新規に受注情報を追加します。';
$HELP['order_edit_btn']['title'] = '編集ボタン';
$HELP['order_edit_btn']['body'] = '選択されている受注情報を編集します。<br />受注情報を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['order_del_btn']['title'] = '削除ボタン';
$HELP['order_del_btn']['body'] = '選択されている受注情報を削除します。<br />受注情報を選択するには、一覧の左端のチェックボックスにチェックを入れます。';
$HELP['order_ret_btn']['title'] = '戻るボタン';
$HELP['order_ret_btn']['body'] = '受注一覧へ戻ります。';
$HELP['order_create_deliv_sheet_btn']['title'] = '納品書作成ボタン';
$HELP['order_create_deliv_sheet_btn']['body'] = '納品書をPDFで作成します。<br />納品元を表示するには基本設定の「ショップ情報」を設定します。';
$HELP['order_check']['title'] = '選択用チェックボックス';
$HELP['order_check']['body'] = '編集や削除を行う項目を選択します。';
$HELP['order_no']['title'] = '受注No';
$HELP['order_no']['body'] = '受注用の番号です。受注時に自動的に作成されます。';
$HELP['order_name']['title'] = '顧客名';
$HELP['order_name']['body'] = '発注した顧客の名前です。';
$HELP['order_name_kana']['title'] = '顧客名カナ';
$HELP['order_name_kana']['body'] = '顧客名の読みカナを設定します。';
$HELP['order_email']['title'] = 'Eメール';
$HELP['order_email']['body'] = '顧客のEメールアドレスを設定します。';
$HELP['order_zip']['title'] = '郵便番号';
$HELP['order_zip']['body'] = '顧客の郵便番号です。';
$HELP['order_state']['title'] = '都道府県';
$HELP['order_state']['body'] = '顧客の住所の都道府県です。';
$HELP['order_add1']['title'] = '住所1';
$HELP['order_add1']['body'] = '顧客の住所の市区町村を設定します。';
$HELP['order_add2']['title'] = '住所2';
$HELP['order_add2']['body'] = '顧客の住所の建物名以降を設定します。';
$HELP['order_phone']['title'] = '電話番号';
$HELP['order_phone']['body'] = '顧客の電話番号を設定します。';
$HELP['order_fax']['title'] = 'FAX';
$HELP['order_fax']['body'] = '顧客のFAX番号を設定します。';
$HELP['order_deliv_name']['title'] = '配送先名';
$HELP['order_deliv_name']['body'] = '配送先の名前です。';
$HELP['order_deliv_name_kana']['title'] = '配送先名カナ';
$HELP['order_deliv_name_kana']['body'] = '配送先の読みカナを設定します。';
$HELP['order_deliv_zip']['title'] = '郵便番号';
$HELP['order_deliv_zip']['body'] = '配送先の郵便番号です。';
$HELP['order_deliv_state']['title'] = '都道府県';
$HELP['order_deliv_state']['body'] = '配送先の住所の都道府県です。';
$HELP['order_deliv_add1']['title'] = '住所1';
$HELP['order_deliv_add1']['body'] = '配送先の住所の市区町村を設定します。';
$HELP['order_deliv_add2']['title'] = '住所2';
$HELP['order_deliv_add2']['body'] = '配送先の住所の建物名以降を設定します。';
$HELP['order_deliv_phone']['title'] = '電話番号';
$HELP['order_deliv_phone']['body'] = '配送先の電話番号を設定します。';
$HELP['order_deliv_fax']['title'] = 'FAX';
$HELP['order_deliv_fax']['body'] = '配送先のFAX番号を設定します。';
$HELP['order_bill_name']['title'] = '請求先名';
$HELP['order_bill_name']['body'] = '請求先の名前です。';
$HELP['order_bill_name_kana']['title'] = '請求先名カナ';
$HELP['order_bill_name_kana']['body'] = '請求先の読みカナを設定します。';
$HELP['order_bill_zip']['title'] = '郵便番号';
$HELP['order_bill_zip']['body'] = '請求先の郵便番号です。';
$HELP['order_bill_state']['title'] = '都道府県';
$HELP['order_bill_state']['body'] = '請求先の住所の都道府県です。';
$HELP['order_bill_add1']['title'] = '住所1';
$HELP['order_bill_add1']['body'] = '請求先の住所の市区町村を設定します。';
$HELP['order_bill_add2']['title'] = '住所2';
$HELP['order_bill_add2']['body'] = '請求先の住所の建物名以降を設定します。';
$HELP['order_bill_phone']['title'] = '電話番号';
$HELP['order_bill_phone']['body'] = '請求先の電話番号を設定します。';
$HELP['order_bill_fax']['title'] = 'FAX';
$HELP['order_bill_fax']['body'] = '請求先のFAX番号を設定します。';

$HELP['order_paymethod']['title'] = '支払方法';
$HELP['order_paymethod']['body'] = '顧客が選択した支払方法を示します。';
$HELP['order_delivmethod']['title'] = '配送方法';
$HELP['order_delivmethod']['body'] = '顧客が選択した配送方法を示します。';

$HELP['order_total']['title'] = '購入額';
$HELP['order_total']['body'] = '手数料、送料を含む受注総額です。';
$HELP['order_status']['title'] = '受注ステータス';
$HELP['order_status']['body'] = '受注の状態を示します。以下のステータスへの変更は変更時に内部処理が行われます。<br />・入金済み<br />ステータスの更新で会員の商品購入履歴に反映されます。<br />・キャンセル<br />基本設定で「在庫自動処理」を選択している場合は、ステータスの更新で商品の在庫数が戻ります。';
$HELP['order_regist_dt']['title'] = '受注日時';
$HELP['order_regist_dt']['body'] = '受注した日時を示します。';
$HELP['order_deliv_dt']['title'] = '配送日時';
$HELP['order_deliv_dt']['body'] = '配送予定の日時を示します。';
$HELP['order_update_dt']['title'] = '更新日時';
$HELP['order_update_dt']['body'] = '受注情報の更新日時を示します。';
$HELP['order_update_user']['title'] = '更新者';
$HELP['order_update_user']['body'] = '受注情報の更新者を示します。';

// ########## その他 ##########
$HELP['other_operation']['title'] = '運用機能';
$HELP['other_operation']['body'] = 'ショップの機能を制御します。<br />・注文の受け付け<br />購入者が注文処理を実行できるかどうか制御します。<br />・非会員からの注文受付<br />会員登録なしで注文処理が実行できるかどうかを制御します。<br />・在庫自動処理<br />商品が購入された場合、在庫数に反映させるかどうかを制御します。';
$HELP['other_shop_info']['title'] = 'ショップ情報';
$HELP['other_shop_info']['body'] = 'ショップに関する情報です。Eメールや納品書等に表示されます。';
$HELP['other_shop_signature']['title'] = 'ショップメール署名';
$HELP['other_shop_signature']['body'] = 'ショップから送信するEメールの署名です。設定値がメールフォーム内の[#SIGNATURE#]と置き換えられます。';
$HELP['other_email']['title'] = 'Eメール機能';
$HELP['other_email']['body'] = 'Eメール機能についての設定です。<br />・メール送信機能使用<br />Eコマース機能でメール送信機能を使用するかどうかを設定します。<br />・自動送信メールの送信元メールアドレス<br />自動的にメールが送られる場合の送信元のメールアドレスです。<br />・受注時送信先メールアドレス<br />商品受注時に送信するショップ管理者向けメールの送信先メールアドレスです。';
$HELP['other_product_category']['title'] = '商品カテゴリー';
$HELP['other_product_category']['body'] = '商品情報設定画面で選択可能な商品カテゴリーの数(メニュー数)です。';
$HELP['other_mail_form']['title'] = 'メールフォーム';
$HELP['other_mail_form']['body'] = '購入者向けに自動送信される各種Eメールの雛形です。<br />・パスワード送信<br />パスワード再送信時に送られるEメールです。<br />・注文受付<br />注文受付時に購入者に送られるEメールです。<br />[#SHOP_NAME#]には「ショップ情報」の「ショップ名」が、[#SIGNATURE#]には「ショップメール署名」が置き換えられます。';
?>
