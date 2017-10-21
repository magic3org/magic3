<?php
/**
 * 共通定義クラス
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
class ec_mainCommonDef
{
	static $_deviceType = 0;	// デバイスタイプ(PC)
	
	const THUMBNAIL_DIR = '/widgets/photo/image';		// 画像格納ディレクトリ
	const PHOTO_DIR = '/etc/photo';		// マスター画像格納ディレクトリ
	const DEFAULT_THUMBNAIL_SIZE = 128;		// サムネール画像サイズ
	const DEFAULT_IMAGE_EXT = 'jpg';			// 画像ファイルのデフォルト拡張子
	const DEFAULT_PUBLIC_IMAGE_SIZE = 450;		// 一般表示用画像(ウォータマーク入り画像)の縦または横の最大サイズ
	const BUTTON_ICON_SIZE = 16;				// ボタン用アイコンサイズ
	const REF_CONTENT_TYPE = 'pt';		// 参照数カウント用
	const DEFAULT_CATEGORY_COUNT = 2;		// デフォルトの画像カテゴリー数
	const DEFAULT_PHOTO_LIST_VIEW_COUNT = 24;		// デフォルトの画像カテゴリー数
	const DEFAULT_PHOTO_TITLE_SHORT_LENGTH = 10;	// デフォルトの画像タイトル文字数
	const DEFAULT_COMMENT_COUNT = 30;		// コメント数
	const DEFAULT_SEARCH_AREA_TMPL = 'default_search.tmpl.html';		// デフォルトの検索エリアテンプレート
	const SEARCH_TEXT_ID = 'photo_main_text';
	const SEARCH_BUTTON_ID = 'photo_main_button';
	const SEARCH_RESET_ID = 'photo_main_reset';
	const SEARCH_SORT_ID = 'photo_main_sort';
	const SEARCH_FORM_ID = 'photo_main_form';
	const USER_OPTION = ';photo_main=author;';		// ログインユーザのユーザオプション
	const EC_LIB_OBJ_ID = 'eclib';		// Eコマースオブジェクト
	// 商品
	const DEFAULT_PRODUCT_IMAGE_TYPE = 'c.jpg';			// 商品画像ファイルのタイプ
		
	// DB定義値
	// Eコマース機能追加分
	const MEMBER_INFO_OPTION	= false;		// 会員の追加情報を使用するかどうか
	const DEFAULT_COUNTRY_ID	= 'JPN';	// デフォルト国ID
	const PRODUCT_CLASS_DEFAULT	= '';		// 商品クラス
	const PRODUCT_CLASS_PHOTO	= 'photo';		// 商品クラス
	const STANDARD_PRICE 		= 'selling';		// 通常価格
	const PRODUCT_TYPE_DOWNLOAD = 'download';		// 商品タイプ
	const PRODUCT_IMAGE_SMALL = 'small-product';		// 小サイズ商品画像ID
	const TAX_TYPE				= 'sales';						// 課税タイプ(外税)
	const DEFAULT_CURRENCY 		= 'JPY';			// デフォルト通貨
	const AGREE_MEMBER_TEXT_KEY = 'agreement';				// 会員規約のコンテンツデータキー
	const PRODUCT_NAME_FORMAT	= '%s(%s)';		// 商品名表示フォーマット
	const PRODUCT_CODE_FORMAT	= '%s-%s';		// 商品コード表示フォーマット
	const IWIDGET_METHOD_CALC_ORDER = 'calcorder';			// 注文計算用インナーウィジェットメソッド
	const EMAIL_LOGIN_URL		= '&task=emaillogin&photo_account=%s&pwd=%s';		// Eメールからのログイン用URL
	const CART_ICON_SIZE = 64;			// サムネール画像サイズ
//	const THUMBNAIL_DIR = '/widgets/photo/image';		// 画像格納ディレクトリ
//	const DEFAULT_IMAGE_EXT = 'jpg';			// 画像ファイルのデフォルト拡張子
//	const DEFAULT_THUMBNAIL_SIZE = 128;		// サムネール画像サイズ
	// DB定義値
	
	// Eコマース設定マスター
	const CF_E_ACCEPT_ORDER			= 'accept_order';			// 注文の受付
	const CF_E_AUTO_STOCK				= 'auto_stock';				// 在庫自動処理
	const CF_E_PERMIT_NON_MEMBER_ORDER = 'permit_non_member_order';			// 非会員の購入許可
	const CF_E_USE_EMAIL				= 'use_email';				// メール送信機能の使用
	const CF_E_SHOP_EMAIL				= 'shop_email';				// ショップ宛てメールアドレス
	const CF_E_AUTO_EMAIL_SENDER		= 'auto_email_sender';		// 自動送信メール送信元アドレス
	const CF_E_USE_MEMBER_ADDRESS		= 'use_member_address';		// 会員登録の住所使用
	const CF_E_MEMBER_NOTICE			= 'member_notice';			// 会員向けお知らせ
	const CF_E_EMAIL_TO_ORDER_PRODUCT	= 'email_to_order_product';		// 商品受注時送信先メールアドレス
//	const CF_E_CONTENT_NO_STOCK		= 'content_no_stock';		// 在庫なし時コンテンツ
	const CF_E_AUTO_REGIST_MEMBER			= 'auto_regist_member';		// 自動会員登録
	const CF_E_SELL_PRODUCT_PHOTO			= 'sell_product_photo';		// フォト商品販売
	const CF_E_SELL_PRODUCT_DOWNLOAD		= 'sell_product_download';	// ダウンロード商品販売
	const CF_E_CATEGORY_SELECT_COUNT	= 'category_select_count';	// 商品カテゴリー選択可能数
	const CF_E_HIERARCHICAL_CATEGORY	= 'hierarchical_category';	// 階層化商品カテゴリー
	const CF_E_SHOP_SIGNATURE			= 'shop_signature';		// ショップメール署名
	const CF_E_SHOP_NAME				= 'shop_name';		// ショップ名
	const CF_E_SHOP_OWNER				= 'shop_owner';		// ショップオーナー
	const CF_E_SHOP_ZIPCODE				= 'shop_zipcode';	// ショップ郵便番号
	const CF_E_SHOP_ADDRESS				= 'shop_address';	// ショップ住所
	const CF_E_SHOP_PHONE				= 'shop_phone';		// ショップ電話番号
	const CF_E_PRODUCT_DEFAULT_IMAGE	= 'product_default_image';		// 製品デフォルト画像
	const CF_E_THUMB_TYPE				= 'thumb_type';				// サムネールタイプ定義
	const CF_E_USE_BASE_PRICE			= 'use_base_price';			// 価格(基準価格)を使用するかどうか
	
	// フォトギャラリー設定マスター
	const CF_P_THUMBNAIL_BG_COLOR		= 'thumbnail_bg_color';				// サムネール背景色
	const CF_P_IMAGE_CATEGORY_COUNT		= 'image_category_count';			// 画像カテゴリー数
	const CF_P_PHOTO_LIST_ITEM_COUNT	= 'photo_list_item_count';			// 画像一覧表示項目数
	const CF_P_PHOTO_LIST_ORDER			= 'photo_list_order';				// 画像一覧並び順
	const CF_P_PHOTO_TITLE_SHORT_LENGTH	= 'photo_title_short_length';		// 略式写真タイトルの長さ
	const CF_P_IMAGE_PROTECT_COPYRIGHT	= 'image_protect_copyright';		// 画像著作権保護
	const CF_P_ONLINE_SHOP				= 'online_shop';					// オンラインショップ機能
	
	// セッションキー
	const SK_AGREE_MEMBER		= 'agree_member';		// 会員規約に同意したかどうか
//	const SK_INIT_ORDER = 'init_order';			// 注文処理初期化したかどうか
	// 注文状態
	const ORDER_STATUS_REGIST	= 200;			// 登録時のステータス(受注受付)
	const ORDER_STATUS_CLOSE	= 900;			// 登録時のステータス(終了)
	const ORDER_STATUS_CANCEL	= 901;			// 登録時のステータス(キャンセル)
	const ORDER_STATUS_PAYMENT_COMPLETED = 301;	// 登録時のステータス(入金済み)
	// メールフォーマット
	const MAIL_FORM_ORDER_PRODUCT_TO_SHOP_MANAGER	= 'order_product_to_shop_manager';		// 商品受注時ショップ管理者向けメール
	const MAIL_FORM_ORDER_PRODUCT_TO_CUSTOMER		= 'order_product_to_customer';		// 商品受注時ショップ購入者向けメール
	const MAIL_FORM_SEND_PASSWORD					= 'send_password';		// パスワード送信用フォーム
	
	/**
	 * サムネール画像のパスを取得
	 *
	 * @param string $photoId		画像ID
	 * @return string				画像パス
	 */
	static function getThumbnailPath($photoId)
	{
		global $gEnvManager;
		
		return $gEnvManager->getResourcePath() . self::THUMBNAIL_DIR . '/' . $photoId . '_' . self::DEFAULT_THUMBNAIL_SIZE . '.' . self::DEFAULT_IMAGE_EXT;
	}
	/**
	 * サムネール画像のURLを取得
	 *
	 * @param string $photoId		画像ID
	 * @return string				画像パス
	 */
	static function getThumbnailUrl($photoId)
	{
		global $gEnvManager;
		
		return $gEnvManager->getResourceUrl() . self::THUMBNAIL_DIR . '/' . $photoId . '_' . self::DEFAULT_THUMBNAIL_SIZE . '.' . self::DEFAULT_IMAGE_EXT;
	}
	/**
	 * 公開画像のパスを取得
	 *
	 * @param string $photoId		画像ID
	 * @return string				画像パス
	 */
	static function getPublicImagePath($photoId)
	{
		global $gEnvManager;
		
		return $gEnvManager->getResourcePath() . self::THUMBNAIL_DIR . '/' . $photoId . '.' . self::DEFAULT_IMAGE_EXT;
	}
	/**
	 * 公開画像のURLを取得
	 *
	 * @param string $photoId		画像ID
	 * @return string				画像パス
	 */
	static function getPublicImageUrl($photoId)
	{
		global $gEnvManager;
		
		return $gEnvManager->getResourceUrl() . self::THUMBNAIL_DIR . '/' . $photoId . '.' . self::DEFAULT_IMAGE_EXT;
	}
	/**
	 * 画像取得
	 *
	 * @param string $photoId		画像ID
	 * @param object $db			DBオブジェクト
	 * @return						なし
	 */
	static function getImage($photoId, $db)
	{
		global $gEnvManager;
		global $gPageManager;
		
		// ページ作成処理中断
		$gPageManager->abortPage();

		$ret = $db->getPhotoInfo($photoId, $gEnvManager->getCurrentLanguage(), $row);
		if ($ret){
			header('Content-type: ' . $row['ht_mime_type']);// 画像タイプ
			// キャッシュの設定
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');// 過去の日付
			header('Cache-Control: no-store, no-cache, must-revalidate');// HTTP/1.1
			header('Cache-Control: post-check=0, pre-check=0');
			header('Pragma: no-cache');

			$imagePath = $gEnvManager->getIncludePath() . self::PHOTO_DIR . $row['ht_dir'] . DIRECTORY_SEPARATOR . $row['ht_public_id'];
			readfile($imagePath);
		}
	
		// システム強制終了
		$gPageManager->exitSystem();
	}
	/**
	 * 画像サイズ調整
	 *
	 * @param int $width		画像横幅
	 * @param int $height		画像高さ
	 * @param int $maxSize		最大サイズ
	 * @return					なし
	 */
	static function adjustImageSize(&$width, &$height, $maxSize)
	{
		// 画像サイズ
		if ($width > $height){
			$height = ($height / $width) * $maxSize;
			$width = $maxSize;
		} else {
			$width = ($width / $height) * $maxSize;
			$height = $maxSize;
		}
	}
	/**
	 * フォトギャラリー定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	static function loadConfig($db)
	{
		$retVal = array();

		// フォトギャラリー定義を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['cg_id'];
				$value = $rows[$i]['cg_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * ログイン用のURL作成
	 *
	 * @param string $account		アカウント
	 * @param string $password		パスワード
	 * @return string				URL
	 */
	static function createLoginUrl($account, $password)
	{
		global $gEnvManager;
		global $gPageManager;
		
		$url = $gPageManager->getDefaultPageUrlByWidget($gEnvManager->getCurrentWidgetId(), sprintf(self::EMAIL_LOGIN_URL, urlencode($account), urlencode($password)));		// ログイン用URL
		return $url;
	}
	/**
	 * アイキャッチ用画像のURLを取得
	 *
	 * @param string $filenames				作成済みファイル名(「;」区切り)
	 * @param string $defaultFilenames		作成済みデフォルトファイル名(「;」区切り)
	 * @param string $thumbTypeDef			サムネール画像タイプ定義(タイプ指定の場合)
	 * @param string $thumbType				サムネール画像タイプ(s,m,l)(タイプ指定の場合)
	 * @return string						画像URL
	 */
	static function getEyecatchImageUrl($filenames, $defaultFilenames, $thumbTypeDef = '', $thumbType = '')
	{
		global $gInstanceManager;
		static $thumbTypeArray;

		$thumbUrl = '';
		if (empty($filenames)) $filenames = $defaultFilenames;		// 記事デフォルト画像
		if (!empty($filenames)){
			$thumbFilename = $gInstanceManager->getImageManager()->getSystemThumbFilenameByType($filenames, $thumbTypeDef, $thumbType);
			if (!empty($thumbFilename)) $thumbUrl = $gInstanceManager->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_PRODUCT, self::$_deviceType, $thumbFilename);
		}
		return $thumbUrl;
	}
}
?>
