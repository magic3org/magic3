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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_contactusWidgetContainer.php 2363 2009-09-26 14:45:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() .			'/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/contactus_mainDb.php');

class m_contactusWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	private $langId;	// 表示言語
	private $state;		// 都道府県
	const CONTACTUS_FORM = 'contact_us';		// お問い合わせフォーム
	const DEFAULT_SEND_MESSAGE = 1;		// メール送信機能を使用するかどうか(デフォルト使用)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
			
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new contactus_mainDb();
		$this->sysDb = $gInstanceManager->getSytemDbObject();
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
		return 'index.tmpl.html';
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
		global $gEnvManager;
		global $gErrorManager;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->langId	= $gEnvManager->getCurrentLanguage();		// 表示言語を取得
		
		// 設定値の取得
		$sendMessage = self::DEFAULT_SEND_MESSAGE;			// メール送信機能を使用するかどうか
		$emailReceiver = '';		// メール受信者
		$companyVisible = 0;	// 会社名入力フィールドの表示
		$addressVisible = 0;	// 住所入力フィールドの表示
		$telVisible = 0;		// 電話番号入力フィールドの表示
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$sendMessage = $paramObj->sendMessage;			// メール送信機能を使用するかどうか
			$emailReceiver = $paramObj->emailReceiver;		// メール受信者
			$companyVisible = $paramObj->companyVisible;	// 会社名入力フィールドの表示
			$addressVisible = $paramObj->addressVisible;	// 住所入力フィールドの表示
			$telVisible = $paramObj->telVisible;		// 電話番号入力フィールドの表示
		}
		
		// 入力値を取得
		$act = $request->mobileTrimValueOf('act');
		$this->state = $request->mobileTrimValueOf('item_state');		// 都道府県
		$name = $request->mobileTrimValueOf('item_name');		// 名前
		$nameKana = $request->mobileTrimValueOf('item_name_kana');		// 名前(カナ)
		$email = $request->mobileTrimValueOf('item_email');		// Eメール
		$email2 = $request->mobileTrimValueOf('item_email2');		// Eメール確認用
		$companyName = $request->mobileTrimValueOf('item_company_name');		// 会社名
		$zipcode = $request->mobileTrimValueOf('item_zipcode');		// 郵便番号
		$address = $request->mobileTrimValueOf('item_address');		// 住所
		$phone = $request->mobileTrimValueOf('item_phone');		// 電話番号
		$body = $request->mobileTrimValueOf('item_body');		// 問い合わせ内容
		if ($act == 'send'){		// お問い合わせメール送信
			// 入力チェック
			$this->checkInput($name, 'お名前');
			$this->checkInput($nameKana, 'お名前(カナ)');
			$this->checkMailAddress($email, 'Eメール');
			$this->checkInput($body, 'お問い合わせ内容');
			if ($companyVisible){	// 会社名入力フィールドの表示
				$this->checkInput($companyName, '会社名');
			}
			if ($addressVisible){	// 住所入力フィールドの表示
				$this->checkInput($zipcode, '郵便番号');
				$this->checkInput($this->state, '都道府県');
				$this->checkInput($address, '住所');
			}					
			if ($telVisible){		// 電話番号入力フィールドの表示
				$this->checkInput($phone, '電話番号');
			}
			if ($this->getMsgCount() == 0){			// メールアドレスのチェック
				if ($email != $email2){
					$this->setAppErrorMsg('Eメールアドレスに誤りがあります');
				}
			}
			// エラーなしの場合はメール送信
			if ($this->getMsgCount() == 0){
				$this->setGuidanceMsg('送信完了しました');
				
				// メール送信設定のときはメールを送信
				if ($sendMessage){
					// メール本文の作成
					$mailBody = 'お名前　　　　　: ' . $name . "\n";
					$mailBody .= 'お名前（カナ）　: ' . $nameKana . "\n";
					$mailBody .= 'Ｅメールアドレス: ' . $email . "\n";
					if ($companyVisible){	// 会社名入力フィールドの表示
						$mailBody .= '会社名　　　　　: ' . $companyName . "\n";
					}
					if ($addressVisible){	// 住所入力フィールドの表示
						$stateName = $this->db->getStateName('JPN', $this->langId, $this->state);
						$mailBody .= '郵便番号　　　　: ' . $zipcode . "\n";
						$mailBody .= '都道府県　　　　: ' . $stateName . "\n";
						$mailBody .= '住所　　　　　　: ' . $address . "\n";
					}
					if ($telVisible){		// 電話番号入力フィールドの表示
						$mailBody .= '電話番号　　　　: ' . $phone . "\n";
					}
					$mailBody .= 'お問い合わせ内容: ' . $body . "\n";
					
					// 送信元、送信先
					$fromAddress = $gEnvManager->getSiteEmail();	// 送信元はサイト情報のEメールアドレス
					$toAddress = $gEnvManager->getSiteEmail();		// デフォルトのサイト向けEメールアドレス
					if (!empty($emailReceiver)) $toAddress = $emailReceiver;		// 受信メールアドレスが設定されている場合
					
					// メールを送信
					if (empty($toAddress)){
						$gErrorManager->writeError(__METHOD__, "基本情報のEメールアドレスが設定されていません。\n(メール本文)\n" . $mailBody);
					} else {
						$mailParam = array();
						$mailParam['BODY'] = $mailBody;
						$ret = $this->gInstance->getMailManager()->sendFormMail(2/*手動送信*/, $gEnvManager->getCurrentWidgetId(), $toAddress, $fromAddress, $email, '', self::CONTACTUS_FORM, $mailParam);
					}
				}

				$this->tmpl->addVar("_widget", "name_disabled", 'disabled');
				$this->tmpl->addVar("_widget", "name_kana_disabled", 'disabled');
				$this->tmpl->addVar("_widget", "email_disabled", 'disabled');
				$this->tmpl->addVar("_widget", "email2_disabled", 'disabled');
				$this->tmpl->addVar("_widget", "body_disabled", 'disabled');
				
				$this->tmpl->addVar("show_company_name", "company_name_disabled", 'disabled');
				$this->tmpl->addVar("show_address", "zipcode_disabled", 'disabled');
				$this->tmpl->addVar("show_address", "state_disabled", 'disabled');
				$this->tmpl->addVar("show_address", "address_disabled", 'disabled');
				$this->tmpl->addVar("show_tel", "phone_disabled", 'disabled');
				
				$this->tmpl->addVar("_widget", "send_button_disabled", 'disabled');// 送信ボタン
			}
			$this->tmpl->addVar("_widget", "name", $name);
			$this->tmpl->addVar("_widget", "name_kana", $nameKana);
			$this->tmpl->addVar("_widget", "email", $email);
			$this->tmpl->addVar("_widget", "email2", $email2);
			$this->tmpl->addVar("_widget", "body", $body);
			$this->tmpl->addVar("show_company_name", "company_name", $companyName);
			$this->tmpl->addVar("show_address", "zipcode", $zipcode);
			$this->tmpl->addVar("show_address", "address", $address);
			$this->tmpl->addVar("show_tel", "phone", $phone);
			$this->tmpl->addVar("_widget", "send_button_label", '送信する');// 送信ボタンラベル
		} else {
			// メール送信不可の場合はボタンを使用不可にする
			if ($sendMessage){
				$this->tmpl->addVar("_widget", "send_button_label", '送信する');// 送信ボタンラベル
			} else {
				$this->tmpl->addVar("_widget", "send_button_label", '送信停止中');// 送信ボタンラベル
				$this->tmpl->addVar("_widget", "send_button_disabled", 'disabled');// 送信ボタン
			}
		}
		// 入力フィールドの表示制御
		if ($companyVisible) $this->tmpl->setAttribute('show_company_name', 'visibility', 'visible');// 会社名入力フィールドの表示
		if ($addressVisible){	// 住所入力フィールドの表示
			// 都道府県メニュー
			$this->db->getAllState('JPN', $this->langId, array($this, 'stateLoop'));
			$this->tmpl->setAttribute('show_address', 'visibility', 'visible');// 住所入力フィールドの表示
		}					
		if ($telVisible) $this->tmpl->setAttribute('show_tel', 'visibility', 'visible');// 住所入力フィールドの表示
		$this->tmpl->addVar("_widget", "url", $gEnvManager->createCurrentPageUrlForMobile());
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
		global $gEnvManager;

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
