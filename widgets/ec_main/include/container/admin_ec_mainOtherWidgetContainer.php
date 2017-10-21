<?php
/**
 * index.php用コンテナクラス
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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_ec_mainBaseWidgetContainer.php');

class admin_ec_mainOtherWidgetContainer extends admin_ec_mainBaseWidgetContainer
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{
		return 'admin_other.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$act = $request->trimValueOf('act');
		
		$acceptOrder			= ($request->trimValueOf('accept_order') == 'on') ? 1 : 0;		// 注文処理を受け付けるかどうか
		$nonMemberOrder 		= ($request->trimValueOf('non_member_order') == 'on') ? 1 : 0;		// 非会員からの注文を許可するかどうか
		$autoStock				= ($request->trimValueOf('auto_stock') == 'on') ? 1 : 0;		// 在庫自動処理を行うかどうか
		$useEmail				= ($request->trimValueOf('use_email') == 'on') ? 1 : 0;		// EMAIL機能が使用可能かどうか
		$autoEmailSender		= $request->trimValueOf('auto_email');						// 自動送信メールの送信元アドレス
		$sellProductPhoto		= ($request->trimValueOf('item_sell_product_photo') == 'on') ? 1 : 0;				// フォト商品販売
		$sellProductDownload	= ($request->trimValueOf('item_sell_product_download') == 'on') ? 1 : 0;		// ダウンロード商品販売
		$useBasePrice			= $request->trimCheckedValueOf('item_use_base_price');// 価格(基準価格)を使用するかどうか
		$categorySelectCount	= $request->valueOf('item_category_select_count');		// 商品カテゴリー選択可能数
//		$contentNoStock			= $request->valueOf('item_content_no_stock');						// 在庫なし時コンテンツ
		$memberNotice			= $request->valueOf('item_member_notice');						// 会員向けお知らせ
		$emailToOrderProduct	= $request->valueOf('item_email_to_order_product');		// 商品受注時送信先メールアドレス
		$shopName		= $request->valueOf('shop_name');		// ショップ名
		$shopOwner		= $request->valueOf('shop_owner');		// ショップオーナー名
		$shopZipcode	= $request->valueOf('shop_zipcode');		// ショップ郵便番号
		$shopAddress	= $request->valueOf('shop_address');		// ショップ住所
		$shopPhone		= $request->valueOf('shop_phone');		// ショップ電話番号
		$shopSignature		= $request->valueOf('item_shop_signature');		// ショップ署名
		$mailFormSendPwd		= $request->valueOf('item_mail_form_send_pwd');		// メールフォーム(パスワード送信)
		$mailFormOrderProduct	= $request->valueOf('item_mail_form_order_product');		// メールフォーム(注文受付)
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkMailAddress($autoEmailSender, '自動送信メールの送信元メールアドレス', true);
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$isErr = false;

				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_ACCEPT_ORDER, $acceptOrder)) $isErr = true;
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_PERMIT_NON_MEMBER_ORDER, $nonMemberOrder)) $isErr = true;// 非会員からの注文受付
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_AUTO_STOCK, $autoStock)) $isErr = true;
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_USE_EMAIL, $useEmail)) $isErr = true;
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_AUTO_EMAIL_SENDER, $autoEmailSender)) $isErr = true;
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_SELL_PRODUCT_PHOTO, $sellProductPhoto)) $isErr = true;// フォト商品販売
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_SELL_PRODUCT_DOWNLOAD, $sellProductDownload)) $isErr = true;// ダウンロード商品販売
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_USE_BASE_PRICE, $useBasePrice)) $isErr = true;// 価格(基準価格)を使用するかどうか
				}
/*				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_CONTENT_NO_STOCK, $contentNoStock)) $isErr = true;		// 在庫なし時コンテンツ
				}*/
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_MEMBER_NOTICE, $memberNotice)) $isErr = true;		// 会員向けお知らせ
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_EMAIL_TO_ORDER_PRODUCT, $emailToOrderProduct)) $isErr = true;		// 商品受注時送信先メールアドレス
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_CATEGORY_SELECT_COUNT, $categorySelectCount)) $isErr = true;		// 商品カテゴリー選択可能数
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_SHOP_NAME, $shopName)) $isErr = true;		// ショップ名
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_SHOP_OWNER, $shopOwner)) $isErr = true;		// ショップオーナー名
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_SHOP_ZIPCODE, $shopZipcode)) $isErr = true;		// ショップ郵便番号
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_SHOP_ADDRESS, $shopAddress)) $isErr = true;		// ショップ住所
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_SHOP_PHONE, $shopPhone)) $isErr = true;		// ショップ電話番号
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(ec_mainCommonDef::CF_E_SHOP_SIGNATURE, $shopSignature)) $isErr = true;		// ショップ署名
				}
				if (!$isErr){
					if (!$this->updateMailForm(ec_mainCommonDef::MAIL_FORM_SEND_PASSWORD, $mailFormSendPwd)) $isErr = true;// メールフォーム(パスワード送信)
				}
				if (!$isErr){
					if (!$this->updateMailForm(ec_mainCommonDef::MAIL_FORM_ORDER_PRODUCT_TO_CUSTOMER, $mailFormOrderProduct)) $isErr = true;// メールフォーム(注文受付)
				}
				if ($isErr){
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				} else {
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				}
				// 値を再取得
				$acceptOrder = self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_ACCEPT_ORDER);
				$nonMemberOrder = self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_PERMIT_NON_MEMBER_ORDER);			// 非会員からの注文受付
				$autoStock = self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_AUTO_STOCK);// 在庫自動処理を行うかどうか
				$useEmail	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_USE_EMAIL);
				$autoEmailSender	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_AUTO_EMAIL_SENDER);
				$sellProductPhoto		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SELL_PRODUCT_PHOTO);				// フォト商品販売
				$sellProductDownload	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SELL_PRODUCT_DOWNLOAD);		// ダウンロード商品販売
//				$contentNoStock			= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_CONTENT_NO_STOCK);						// 在庫なし時コンテンツ
				$memberNotice			= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_MEMBER_NOTICE);		// 会員向けお知らせ
				$emailToOrderProduct	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_EMAIL_TO_ORDER_PRODUCT);		// 商品受注時送信先メールアドレス
				$categorySelectCount = self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_CATEGORY_SELECT_COUNT);		// 商品カテゴリー選択可能数
				$shopName		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_NAME);		// ショップ名
				$shopOwner		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_OWNER);		// ショップオーナー名
				$shopZipcode	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_ZIPCODE);		// ショップ郵便番号
				$shopAddress	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_ADDRESS);		// ショップ住所
				$shopPhone		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_PHONE);		// ショップ電話番号
				$shopSignature	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_SIGNATURE);		// ショップ署名
				$mailFormSendPwd		= $this->getMailForm(ec_mainCommonDef::MAIL_FORM_SEND_PASSWORD);		// メールフォーム(パスワード送信)
				$mailFormOrderProduct	= $this->getMailForm(ec_mainCommonDef::MAIL_FORM_ORDER_PRODUCT_TO_CUSTOMER);		// メールフォーム(注文受付)
			}
		} else {		// 初期表示の場合
			$acceptOrder = self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_ACCEPT_ORDER);
			$nonMemberOrder = self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_PERMIT_NON_MEMBER_ORDER);			// 非会員からの注文受付
			$autoStock = self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_AUTO_STOCK);// 在庫自動処理を行うかどうか
			$useEmail	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_USE_EMAIL);
			$autoEmailSender	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_AUTO_EMAIL_SENDER);
			$sellProductPhoto		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SELL_PRODUCT_PHOTO);				// フォト商品販売
			$sellProductDownload	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SELL_PRODUCT_DOWNLOAD);		// ダウンロード商品販売
			$useBasePrice			= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_USE_BASE_PRICE);// 価格(基準価格)を使用するかどうか
//			$contentNoStock			= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_CONTENT_NO_STOCK);						// 在庫なし時コンテンツ
			$memberNotice			= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_MEMBER_NOTICE);		// 会員向けお知らせ
			$emailToOrderProduct	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_EMAIL_TO_ORDER_PRODUCT);		// 商品受注時送信先メールアドレス
			$categorySelectCount = self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_CATEGORY_SELECT_COUNT);		// 商品カテゴリー選択可能数
			$shopName		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_NAME);		// ショップ名
			$shopOwner		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_OWNER);		// ショップオーナー名
			$shopZipcode	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_ZIPCODE);		// ショップ郵便番号
			$shopAddress	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_ADDRESS);		// ショップ住所
			$shopPhone		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_PHONE);		// ショップ電話番号
			$shopSignature	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_SIGNATURE);		// ショップ署名
			$mailFormSendPwd		= $this->getMailForm(ec_mainCommonDef::MAIL_FORM_SEND_PASSWORD);		// メールフォーム(パスワード送信)
			$mailFormOrderProduct	= $this->getMailForm(ec_mainCommonDef::MAIL_FORM_ORDER_PRODUCT_TO_CUSTOMER);		// メールフォーム(注文受付)
		}
		// 画面に書き戻す
		$checked = '';
		if ($acceptOrder) $checked = 'checked';
		$this->tmpl->addVar("_widget", "accept_order", $checked);
		$checked = '';
		if (!empty($nonMemberOrder)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "non_member_order", $checked);			// 非会員からの注文受付
		$checked = '';
		if ($autoStock) $checked = 'checked';
		$this->tmpl->addVar("_widget", "auto_stock", $checked);
		$checked = '';
		if ($useEmail) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_email", $checked);
		$this->tmpl->addVar("_widget", "auto_email", $autoEmailSender);
		$checked = '';
		if ($sellProductPhoto) $checked = 'checked';
		$this->tmpl->addVar("_widget", "sell_product_photo_checked", $checked);		// フォト商品販売
		$checked = '';
		if ($sellProductDownload) $checked = 'checked';
		$this->tmpl->addVar("_widget", "sell_product_download_checked", $checked);		// ダウンロード商品販売
		$this->tmpl->addVar("_widget", "use_base_price_checked",	$this->convertToCheckedString($useBasePrice));// 価格(基準価格)を使用するかどうか
//		$this->tmpl->addVar("_widget", "content_no_stock", $contentNoStock);			// 在庫なし時コンテンツ
		$this->tmpl->addVar("_widget", "member_notice", $memberNotice);// 会員向けお知らせ
		$this->tmpl->addVar("_widget", "email_to_order_product", $emailToOrderProduct);// 商品受注時送信先メールアドレス
		$this->tmpl->addVar("_widget", "category_select_count", $categorySelectCount);// 商品カテゴリー選択可能数
		$this->tmpl->addVar("_widget", "shop_name", $shopName);		// ショップ名
		$this->tmpl->addVar("_widget", "shop_owner", $shopOwner);		// ショップオーナー名
		$this->tmpl->addVar("_widget", "shop_zipcode", $shopZipcode);		// ショップ郵便番号
		$this->tmpl->addVar("_widget", "shop_address", $shopAddress);		// ショップ住所
		$this->tmpl->addVar("_widget", "shop_phone", $shopPhone);		// ショップ電話番号
		$this->tmpl->addVar("_widget", "shop_signature", $shopSignature);		// ショップ署名
		$this->tmpl->addVar("_widget", "mail_form_send_pwd", $mailFormSendPwd);		// メールフォーム(パスワード送信)
		$this->tmpl->addVar("_widget", "mail_form_order_product", $mailFormOrderProduct);		// メールフォーム(注文受付)
	}
	/**
	 * メールフォームデータを取得
	 *
	 * @param string $formId		フォームID
	 * @return string 				フォームデータ
	 */
	function getMailForm($formId)
	{
		$langId = $this->gEnv->getDefaultLanguage();
		$content = '';
		
		$ret = $this->gInstance->getMailManager()->getMailForm($formId, $langId, $row);
		if ($ret){
			$content = $row['mf_content'];
		}
		return $content;
	}
	/**
	 * メールフォームデータを更新
	 *
	 * @param string $formId		フォームID
	 * @param string $content		メール本文
	 * @return bool 				true=成功、false=失敗
	 */
	function updateMailForm($formId, $content)
	{
		$langId = $this->gEnv->getDefaultLanguage();
		$ret = $this->gInstance->getMailManager()->updateMailForm($formId, $langId, $content);
		return $ret;
	}
}
?>
