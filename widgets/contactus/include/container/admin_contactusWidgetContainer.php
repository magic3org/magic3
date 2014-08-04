<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_contactusWidgetContainer.php 5164 2012-09-05 22:58:27Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() .			'/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/contactus_mainDb.php');

class admin_contactusWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const DEFAULT_SEND_MESSAGE = 1;		// メール送信機能を使用するかどうか(デフォルト使用)
	const DEFAULT_TITLE_NAME = 'お問い合わせ';	// デフォルトのタイトル名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new contactus_mainDb();
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
		return 'admin.tmpl.html';
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
		$defaultLang	= $this->gEnv->getDefaultLanguage();
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			$sendMessage = ($request->trimValueOf('send_message') == 'on') ? 1 : 0;		// メール送信機能を使用するかどうか
			$emailReceiver = trim($request->valueOf('email_receiver'));			// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
			$showTitle = ($request->trimValueOf('show_title') == 'on') ? 1 : 0;		// タイトルの表示
			$titleName = trim($request->valueOf('title_name'));				// タイトル名
			$explanation = trim($request->valueOf('explanation'));				// 説明
			
			$nameVisible		= ($request->trimValueOf('name_visible') == 'on') ? 1 : 0;		// 名前入力フィールドの表示
			$nameKanaVisible	= ($request->trimValueOf('name_kana_visible') == 'on') ? 1 : 0;		// フリガナ入力フィールドの表示
			$emailVisible		= ($request->trimValueOf('email_visible') == 'on') ? 1 : 0;		// Eメール入力フィールドの表示
			$companyVisible 	= ($request->trimValueOf('company_visible') == 'on') ? 1 : 0;		// 会社名入力フィールドの表示
			$zipcodeVisible 	= ($request->trimValueOf('zipcode_visible') == 'on') ? 1 : 0;		// 郵便番号入力フィールドの表示
			$stateVisible 		= ($request->trimValueOf('state_visible') == 'on') ? 1 : 0;		// 都道府県入力フィールドの表示
			$addressVisible		= ($request->trimValueOf('address_visible') == 'on') ? 1 : 0;		// 住所入力フィールドの表示
			$telVisible			= ($request->trimValueOf('tel_visible') == 'on') ? 1 : 0;		// 電話番号入力フィールドの表示
			$bodyVisible		= ($request->trimValueOf('body_visible') == 'on') ? 1 : 0;		// 内容入力フィールドの表示
			
			$nameRequired		= ($request->trimValueOf('name_required') == 'on') ? 1 : 0;		// 名前入力フィールドの必須
			$nameKanaRequired	= ($request->trimValueOf('name_kana_required') == 'on') ? 1 : 0;		// フリガナ入力フィールドの必須
			$emailRequired		= ($request->trimValueOf('email_required') == 'on') ? 1 : 0;		// Eメール入力フィールドの必須
			$companyRequired 	= ($request->trimValueOf('company_required') == 'on') ? 1 : 0;		// 会社名入力フィールドの必須
			$zipcodeRequired 	= ($request->trimValueOf('zipcode_required') == 'on') ? 1 : 0;		// 郵便番号入力フィールドの必須
			$stateRequired 		= ($request->trimValueOf('state_required') == 'on') ? 1 : 0;		// 都道府県入力フィールドの必須
			$addressRequired	= ($request->trimValueOf('address_required') == 'on') ? 1 : 0;		// 住所入力フィールドの必須
			$telRequired		= ($request->trimValueOf('tel_required') == 'on') ? 1 : 0;		// 電話番号入力フィールドの必須
			$bodyRequired		= ($request->trimValueOf('body_required') == 'on') ? 1 : 0;		// 内容入力フィールドの必須
			
			// 入力値のエラーチェック
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->sendMessage = $sendMessage;			// メール送信機能を使用するかどうか
				$paramObj->emailReceiver = $emailReceiver;		// メール受信者
				$paramObj->showTitle = $showTitle;				// タイトルの表示
				$paramObj->titleName = $titleName;				// タイトル名
				$paramObj->explanation = $explanation;			// 説明
				
				$paramObj->nameVisible		= $nameVisible;		// 名前入力フィールドの表示
				$paramObj->nameKanaVisible	= $nameKanaVisible;		// フリガナ入力フィールドの表示
				$paramObj->emailVisible		= $emailVisible;		// Eメール入力フィールドの表示
				$paramObj->companyVisible 	= $companyVisible;		// 会社名入力フィールドの表示
				$paramObj->zipcodeVisible 	= $zipcodeVisible;		// 郵便番号入力フィールドの表示
				$paramObj->stateVisible 	= $stateVisible;		// 都道府県入力フィールドの表示
				$paramObj->addressVisible	= $addressVisible;		// 住所入力フィールドの表示
				$paramObj->telVisible		= $telVisible;		// 電話番号入力フィールドの表示
				$paramObj->bodyVisible		= $bodyVisible;		// 内容入力フィールドの表示
			
				$paramObj->nameRequired		= $nameRequired;		// 名前入力フィールドの必須
				$paramObj->nameKanaRequired	= $nameKanaRequired;		// フリガナ入力フィールドの必須
				$paramObj->emailRequired	= $emailRequired;		// Eメール入力フィールドの必須
				$paramObj->companyRequired 	= $companyRequired;		// 会社名入力フィールドの必須
				$paramObj->zipcodeRequired 	= $zipcodeRequired;		// 郵便番号入力フィールドの必須
				$paramObj->stateRequired 	= $stateRequired;		// 都道府県入力フィールドの必須
				$paramObj->addressRequired	= $addressRequired;		// 住所入力フィールドの必須
				$paramObj->telRequired		= $telRequired;		// 電話番号入力フィールドの必須
				$paramObj->bodyRequired		= $bodyRequired;		// 内容入力フィールドの必須
				
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->clearCache();			// キャッシュをクリア
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値の設定
			$sendMessage = self::DEFAULT_SEND_MESSAGE;			// メール送信機能を使用するかどうか
			$emailReceiver = '';		// メール受信者
			$showTitle = 0;				// タイトルの表示
			$titleName = self::DEFAULT_TITLE_NAME;			// デフォルトタイトル名
			$explanation = '';			// 説明
			
			$nameVisible		= 1;		// 名前入力フィールドの表示
			$nameKanaVisible	= 1;		// フリガナ入力フィールドの表示
			$emailVisible		= 1;		// Eメール入力フィールドの表示
			$companyVisible 	= 0;		// 会社名入力フィールドの表示
			$zipcodeVisible 	= 0;		// 郵便番号入力フィールドの表示
			$stateVisible 		= 0;		// 都道府県入力フィールドの表示
			$addressVisible		= 0;		// 住所入力フィールドの表示
			$telVisible			= 0;		// 電話番号入力フィールドの表示
			$bodyVisible		= 1;		// 内容入力フィールドの表示
			
			$nameRequired		= 1;		// 名前入力フィールドの必須
			$nameKanaRequired	= 1;		// フリガナ入力フィールドの必須
			$emailRequired		= 1;		// Eメール入力フィールドの必須
			$companyRequired 	= 0;		// 会社名入力フィールドの必須
			$zipcodeRequired 	= 0;		// 郵便番号入力フィールドの必須
			$stateRequired 		= 0;		// 都道府県入力フィールドの必須
			$addressRequired	= 0;		// 住所入力フィールドの必須
			$telRequired		= 0;		// 電話番号入力フィールドの必須
			$bodyRequired		= 1;		// 内容入力フィールドの必須

			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$sendMessage = $paramObj->sendMessage;			// メール送信機能を使用するかどうか
				$emailReceiver = $paramObj->emailReceiver;		// メール受信者
				$showTitle = $paramObj->showTitle;				// タイトルの表示
				$titleName = $paramObj->titleName;				// タイトル名
				$explanation = $paramObj->explanation;			// 説明
				
				$nameVisible		= $paramObj->nameVisible;		// 名前入力フィールドの表示
				$nameKanaVisible 	= $paramObj->nameKanaVisible;		// フリガナ入力フィールドの表示
				$emailVisible		= $paramObj->emailVisible;		// Eメール入力フィールドの表示
				$companyVisible		= $paramObj->companyVisible;		// 会社名入力フィールドの表示
				$zipcodeVisible		= $paramObj->zipcodeVisible;		// 郵便番号入力フィールドの表示
				$stateVisible		= $paramObj->stateVisible;		// 都道府県入力フィールドの表示
				$addressVisible		= $paramObj->addressVisible;		// 住所入力フィールドの表示
				$telVisible			= $paramObj->telVisible;		// 電話番号入力フィールドの表示
				$bodyVisible		= $paramObj->bodyVisible;		// 内容入力フィールドの表示
			
				$nameRequired		= $paramObj->nameRequired;		// 名前入力フィールドの必須
				$nameKanaRequired	= $paramObj->nameKanaRequired;		// フリガナ入力フィールドの必須
				$emailRequired		= $paramObj->emailRequired;		// Eメール入力フィールドの必須
				$companyRequired	= $paramObj->companyRequired;		// 会社名入力フィールドの必須
				$zipcodeRequired	= $paramObj->zipcodeRequired;		// 郵便番号入力フィールドの必須
				$stateRequired		= $paramObj->stateRequired;		// 都道府県入力フィールドの必須
				$addressRequired	= $paramObj->addressRequired;		// 住所入力フィールドの必須
				$telRequired		= $paramObj->telRequired;		// 電話番号入力フィールドの必須
				$bodyRequired		= $paramObj->bodyRequired;		// 内容入力フィールドの必須
			}
		}
		// 画面に書き戻す
		$checked = '';
		if ($sendMessage) $checked = 'checked';
		$this->tmpl->addVar("_widget", "send_message", $checked);
		$this->tmpl->addVar("_widget", "email_receiver", $emailReceiver);		// メール受信者
		$checked = '';
		if ($showTitle) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_title", $checked);
		$this->tmpl->addVar("_widget", "title_name", $titleName);		// タイトル名
		$this->tmpl->addVar("_widget", "explanation", $explanation);		// 説明
			
		$checked = '';
		if ($nameVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "name_visible", $checked);// 名前入力フィールドの表示
		$checked = '';
		if ($nameKanaVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "name_kana_visible", $checked);// フリガナ入力フィールドの表示
		$checked = '';
		if ($emailVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "email_visible", $checked);// Eメール入力フィールドの表示
		$checked = '';
		if ($companyVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "company_visible", $checked);// 会社名入力フィールドの表示
		$checked = '';
		if ($zipcodeVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "zipcode_visible", $checked);// 郵便番号入力フィールドの表示
		$checked = '';
		if ($stateVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "state_visible", $checked);// 都道府県入力フィールドの表示
		$checked = '';
		if ($addressVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "address_visible", $checked);// 住所入力フィールドの表示
		$checked = '';
		if ($telVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "tel_visible", $checked);// 電話番号入力フィールドの表示
		$checked = '';
		if ($bodyVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "body_visible", $checked);// 内容入力フィールドの表示
			
		$checked = '';
		if ($nameRequired) $checked = 'checked';
		$this->tmpl->addVar("_widget", "name_required", $checked);// 名前入力フィールドの必須
		$checked = '';
		if ($nameKanaRequired) $checked = 'checked';
		$this->tmpl->addVar("_widget", "name_kana_required", $checked);// フリガナ入力フィールドの必須
		$checked = '';
		if ($emailRequired) $checked = 'checked';
		$this->tmpl->addVar("_widget", "email_required", $checked);// Eメール入力フィールドの必須
		$checked = '';
		if ($companyRequired) $checked = 'checked';
		$this->tmpl->addVar("_widget", "company_required", $checked);// 会社名入力フィールドの必須
		$checked = '';
		if ($zipcodeRequired) $checked = 'checked';
		$this->tmpl->addVar("_widget", "zipcode_required", $checked);// 郵便番号入力フィールドの必須
		$checked = '';
		if ($stateRequired) $checked = 'checked';
		$this->tmpl->addVar("_widget", "state_required", $checked);// 都道府県入力フィールドの必須
		$checked = '';
		if ($addressRequired) $checked = 'checked';
		$this->tmpl->addVar("_widget", "address_required", $checked);// 住所入力フィールドの必須
		$checked = '';
		if ($telRequired) $checked = 'checked';
		$this->tmpl->addVar("_widget", "tel_required", $checked);// 電話番号入力フィールドの必須
		$checked = '';
		if ($bodyRequired) $checked = 'checked';
		$this->tmpl->addVar("_widget", "body_required", $checked);// 内容入力フィールドの必須
	}
}
?>
