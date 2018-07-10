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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() .			'/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/contactus_mainDb.php');

class contactusWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $state;		// 都道府県
	private $tagRequired;		// 「必須」ラベルタグ
	const CONTACTUS_FORM = 'contact_us';		// お問い合わせフォーム
	const DEFAULT_SEND_MESSAGE = 1;		// メール送信機能を使用するかどうか(デフォルト使用)
	const DEFAULT_TITLE_NAME = 'お問い合わせ';	// デフォルトのタイトル名
	const DEFAULT_STR_REQUIRED = '<span class="required">*必須</span>';		// 「必須」表示用テキスト
	const BOOTSTRAP_STR_REQUIRED = '<span class="label label-danger required">必須</span>';		// 「必須」表示用テキスト(Bootstrap v3出力用)
	const BOOTSTRAP4_STR_REQUIRED = '<span class="badge badge-danger required">必須</span>';		// 「必須」表示用テキスト(Bootstrap v4出力用)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new contactus_mainDb();
		
		// ##### Postデータのトークン認証機能を初期化 #####
		$this->initPostToken();
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
		if ($this->_renderType == M3_RENDER_BOOTSTRAP){
			if ($this->_templateType == 10){	// Bootstrap v3.0のとき
				$this->tagRequired = self::BOOTSTRAP_STR_REQUIRED;		// 「必須」ラベルタグ
				return 'index_bootstrap.tmpl.html';
			} else {			// Bootstrap v4.0のとき
				$this->tagRequired = self::BOOTSTRAP4_STR_REQUIRED;		// 「必須」ラベルタグ
				return 'index_bootstrap4.tmpl.html';
			}
		} else {
			$this->tagRequired = self::DEFAULT_STR_REQUIRED;		// 「必須」ラベルタグ
			return 'index.tmpl.html';
		}
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
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// 設定値の取得
		$sendMessage = self::DEFAULT_SEND_MESSAGE;			// メール送信機能を使用するかどうか
		$emailReceiver = '';		// メール受信者
		$showTitle = 0;				// タイトルを表示するかどうか
		$titleName = self::DEFAULT_TITLE_NAME;			// タイトル名
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
			$showTitle = $paramObj->showTitle;				// タイトルを表示するかどうか
			if (!empty($paramObj->titleName)) $titleName = $paramObj->titleName;			// タイトル名
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
		
		// 入力値を取得
		$act = $request->trimValueOf('act');
		$this->state = $request->trimValueOf('item_state');		// 都道府県
		$name = $request->trimValueOf('item_name');		// 名前
		$nameKana = $request->trimValueOf('item_name_kana');		// 名前(カナ)
		$email = $request->trimValueOf('item_email');		// Eメール
		$email2 = $request->trimValueOf('item_email2');		// Eメール確認用
		$companyName = $request->trimValueOf('item_company_name');		// 会社名
		$zipcode = $request->trimValueOf('item_zipcode');		// 郵便番号
		$address = $request->trimValueOf('item_address');		// 住所
		$phone = $request->trimValueOf('item_phone');		// 電話番号
		$body = $request->trimValueOf('item_body');		// 問い合わせ内容
		if ($act == 'send'){		// お問い合わせメール送信
			// ##### Postデータのトークン認証を行う #####
			$isVerified = $this->verifyPostToken();
			if ($isVerified){		// 正常なPOSTデータのとき
				// 入力状況のチェック
				if ($nameVisible && $nameRequired) $this->checkInput($name, 'お名前');
				if ($nameKanaVisible && $nameKanaRequired) $this->checkInput($nameKana, 'お名前(カナ)');
				if ($emailVisible){
					if ($this->checkMailAddress($email, 'Eメール', !$emailRequired)){
						if ($email != $email2) $this->setAppErrorMsg('Eメールアドレスに誤りがあります');
					}
				}
				if ($companyVisible && $companyRequired) $this->checkInput($companyName, '会社名');
				if ($zipcodeVisible && $zipcodeRequired) $this->checkInput($zipcode, '郵便番号');
				if ($stateVisible && $stateRequired) $this->checkInput($this->state, '都道府県');
				if ($addressVisible && $addressRequired) $this->checkInput($address, '住所');
				if ($telVisible && $telRequired) $this->checkInput($phone, '電話番号');
				if ($bodyVisible && $bodyRequired) $this->checkInput($body, 'お問い合わせ内容');

				// エラーなしの場合はメール送信
				if ($this->getMsgCount() == 0){
					$this->setGuidanceMsg('送信完了しました');
				
					// メール送信設定のときはメールを送信
					if ($sendMessage){
						// メール本文の作成
						$stateName = $this->db->getStateName('JPN', $this->_langId, $this->state);
						$mailBody = '';
						if ($nameVisible)		$mailBody .= 'お名前　　　　　: ' . $name . "\n";
						if ($nameKanaVisible)	$mailBody .= 'お名前（カナ）　: ' . $nameKana . "\n";
						if ($emailVisible)		$mailBody .= 'Ｅメールアドレス: ' . $email . "\n";
						if ($companyVisible)	$mailBody .= '会社名　　　　　: ' . $companyName . "\n";
						if ($zipcodeVisible)	$mailBody .= '郵便番号　　　　: ' . $zipcode . "\n";
						if ($stateVisible)		$mailBody .= '都道府県　　　　: ' . $stateName . "\n";
						if ($addressVisible)	$mailBody .= '住所　　　　　　: ' . $address . "\n";
						if ($telVisible)		$mailBody .= '電話番号　　　　: ' . $phone . "\n";
						if ($bodyVisible)		$mailBody .= 'お問い合わせ内容: ' . $body . "\n";
					
						// 送信元、送信先
						$fromAddress = $this->gEnv->getSiteEmail();	// 送信元はサイト情報のEメールアドレス
						$toAddress = $this->gEnv->getSiteEmail();		// デフォルトのサイト向けEメールアドレス
						if (!empty($emailReceiver)) $toAddress = $emailReceiver;		// 受信メールアドレスが設定されている場合
					
						// メールを送信
						if (empty($toAddress)){
							$this->gOpeLog->writeError(__METHOD__, 'メール送信に失敗しました。基本情報のEメールアドレスが設定されていません。', 1100, 'body=[' . $mailBody . ']');
						} else {
							$mailParam = array();
							$mailParam['BODY'] = $mailBody;
							$ret = $this->gInstance->getMailManager()->sendFormMail(2/*手動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, $email, '', self::CONTACTUS_FORM, $mailParam);
						}
					}

					$this->tmpl->addVar("show_name", "name_disabled", 'disabled');
					$this->tmpl->addVar("show_name_kana", "name_kana_disabled", 'disabled');
					$this->tmpl->addVar("show_email", "email_disabled", 'disabled');
					$this->tmpl->addVar("show_email", "email2_disabled", 'disabled');
					$this->tmpl->addVar("show_company_name", "company_name_disabled", 'disabled');
					$this->tmpl->addVar("show_zipcode", "zipcode_disabled", 'disabled');
					$this->tmpl->addVar("show_state", "state_disabled", 'disabled');
					$this->tmpl->addVar("show_address", "address_disabled", 'disabled');
					$this->tmpl->addVar("show_tel", "phone_disabled", 'disabled');
					$this->tmpl->addVar("show_body", "body_disabled", 'disabled');
					$this->tmpl->addVar("_widget", "send_button_disabled", 'disabled');// 送信ボタン
					$sendButtonLabel = '送信済み';			// 送信ボタンラベル
					
					// ##### Postデータのトークン認証を終了 #####
					$this->closePostToken();
				} else {		// 入力エラーの場合はハッシュキーを再設定
					$sendButtonLabel = '送信する';			// 送信ボタンラベル
					
					// ##### Postデータのトークン認証を更新 #####
					$this->openPostToken(true/*トークン更新*/);
				}
			} else {		// ハッシュキーが異常のとき
				$this->setAppErrorMsg('送信に失敗しました');

				$sendButtonLabel = '送信する';			// 送信ボタンラベル
					
				// ##### Postデータのトークン認証を終了 #####
				$this->closePostToken();
			}
			$this->tmpl->addVar("show_name", "name", $name);
			$this->tmpl->addVar("show_name_kana", "name_kana", $nameKana);
			$this->tmpl->addVar("show_email", "email", $email);
			$this->tmpl->addVar("show_email", "email2", $email2);
			$this->tmpl->addVar("show_company_name", "company_name", $companyName);
			$this->tmpl->addVar("show_zipcode", "zipcode", $zipcode);
			$this->tmpl->addVar("show_address", "address", $address);
			$this->tmpl->addVar("show_tel", "phone", $phone);
			$this->tmpl->addVar("show_body", "body", $body);
			$this->tmpl->addVar("_widget", "send_button_label", $sendButtonLabel);// 送信ボタンラベル
		} else {
			// ##### Postデータのトークン認証を開始 #####
			$this->openPostToken();
			
			// メール送信不可の場合はボタンを使用不可にする
			if ($sendMessage){
				$this->tmpl->addVar("_widget", "send_button_label", '送信する');// 送信ボタンラベル
			} else {
				$this->tmpl->addVar("_widget", "send_button_label", '送信停止中');// 送信ボタンラベル
				$this->tmpl->addVar("_widget", "send_button_disabled", 'disabled');// 送信ボタン
			}
		}
		// HTMLサブタイトルを設定
//		$this->gPage->setHeadSubTitle(self::DEFAULT_TITLE_NAME);
		$this->gPage->setHeadSubTitle();			// 共通設定画面の「タイトル」値を使用する
			
		// タイトルの表示
		if ($showTitle){
			$headClassStr = $this->gDesign->getDefaultContentHeadClassString();			// コンテンツタイトル用CSSクラス
			$this->tmpl->addVar("show_title", "class", $headClassStr);
			$this->tmpl->setAttribute('show_title', 'visibility', 'visible');
			$this->tmpl->addVar("show_title", "title_name", $titleName);// タイトル名
		}
		// 説明の表示
		if (!empty($explanation)){
			$this->tmpl->setAttribute('show_explanation', 'visibility', 'visible');
			$this->tmpl->addVar("show_explanation", "explanation", $explanation);// 説明
		}
		// 入力フィールドの表示制御
		if ($nameVisible){
			if ($nameRequired) $this->tmpl->addVar('show_name', 'required', $this->tagRequired);// 「必須」表示
			$this->tmpl->setAttribute('show_name', 'visibility', 'visible');// 名前入力フィールドの表示
		}
		if ($nameKanaVisible){
			if ($nameKanaRequired) $this->tmpl->addVar('show_name_kana', 'required', $this->tagRequired);// 「必須」表示
			$this->tmpl->setAttribute('show_name_kana', 'visibility', 'visible');// 名前カナ入力フィールドの表示
		}
		if ($emailVisible){
			if ($emailRequired) $this->tmpl->addVar('show_email', 'required', $this->tagRequired);// 「必須」表示
			$this->tmpl->setAttribute('show_email', 'visibility', 'visible');// Eメール入力フィールドの表示
		}
		if ($companyVisible){
			if ($companyRequired) $this->tmpl->addVar('show_company_name', 'required', $this->tagRequired);// 「必須」表示
			$this->tmpl->setAttribute('show_company_name', 'visibility', 'visible');// 会社名入力フィールドの表示
		}
		if ($zipcodeVisible){
			if ($zipcodeRequired) $this->tmpl->addVar('show_zipcode', 'required', $this->tagRequired);// 「必須」表示
			$this->tmpl->setAttribute('show_zipcode', 'visibility', 'visible');// 郵便番号入力フィールドの表示
		}
		if ($stateVisible){
			if ($stateRequired) $this->tmpl->addVar('show_state', 'required', $this->tagRequired);// 「必須」表示
			$this->tmpl->setAttribute('show_state', 'visibility', 'visible');//都道府県入力フィールドの表示
			$this->db->getAllState('JPN', $this->_langId, array($this, 'stateLoop'));// 都道府県メニュー
		}
		if ($addressVisible){	// 住所入力フィールドの表示
			if ($addressRequired) $this->tmpl->addVar('show_address', 'required', $this->tagRequired);// 「必須」表示
			$this->tmpl->setAttribute('show_address', 'visibility', 'visible');// 住所入力フィールドの表示
		}					
		if ($telVisible){
			if ($telRequired) $this->tmpl->addVar('show_tel', 'required', $this->tagRequired);// 「必須」表示
			$this->tmpl->setAttribute('show_tel', 'visibility', 'visible');// 電話番号入力フィールドの表示
		}
		if ($bodyVisible){
			if ($bodyRequired) $this->tmpl->addVar('show_body', 'required', $this->tagRequired);// 「必須」表示
			$this->tmpl->setAttribute('show_body', 'visibility', 'visible');// 内容入力フィールドの表示
		}
	}
	/**
	 * 取得した都道府県をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function stateLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['gz_id'] == $this->state){		// 選択中の都道府県
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['gz_id']),			// ID
			'name'     => $this->convertToDispString($fetchedRow['gz_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('state_list', $row);
		$this->tmpl->parseTemplate('state_list', 'a');
		return true;
	}
}
?>
