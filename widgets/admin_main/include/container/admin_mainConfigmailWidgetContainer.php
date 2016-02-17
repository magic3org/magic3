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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConfigsystemBaseWidgetContainer.php');

class admin_mainConfigmailWidgetContainer extends admin_mainConfigsystemBaseWidgetContainer
{
	private $encryptType;			// 暗号化タイプ
	private $encryptTypeArray;		// 暗号化タイプ
	
	const TEST_MAIL_FORM = 'test';					// テストメールフォーム
	const CF_SMTP_USE_SERVER	= 'smtp_use_server';	// SMTP外部サーバを使用するかどうか
	const CF_SMTP_HOST			= 'smtp_host';			// SMTPホスト名
	const CF_SMTP_PORT			= 'smtp_port';			// SMTPポート番号
	const CF_SMTP_ENCRYPT_TYPE	= 'smtp_encrypt_type';	// SMTP暗号化タイプ
	const CF_SMTP_AUTHENTICATION	= 'smtp_authentication';		// SMTP認証
	const CF_SMTP_ACCOUNT		= 'smtp_account';		// SMTP接続アカウント
	const CF_SMTP_PASSWORD		= 'smtp_password';		// SMTPパスワード
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 暗号化タイプ
		$this->encryptTypeArray = array(	array(	'name' => 'なし',	'value' => ''),
											array(	'name' => 'SSL',	'value' => 'ssl'),
											array(	'name' => 'TLS',	'value' => 'tls')			// デフォルト
										);
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
		return 'configmail.tmpl.html';
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
		
		$useServer	= $request->trimCheckedValueOf('item_smtp_use_server');			// SMTP外部サーバを使用するかどうか
		$host		= $request->trimValueOf('item_smtp_host');			// SMTPホスト名
		$port		= $request->trimValueOf('item_smtp_port');			// SMTPポート番号
		$this->encryptType	= $request->trimValueOf('item_smtp_encrypt_type');		// 暗号化タイプ
		$authentication = $request->trimCheckedValueOf('item_smtp_authentication');		// SMTP認証
		$account	= $request->trimValueOf('item_smtp_account');		// SMTP接続アカウント
		$password	= $request->trimValueOf('item_smtp_password');		// SMTPパスワード
	
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			$isErr = false;

			if (!$isErr){
				if (!$this->_mainDb->updateSystemConfig(self::CF_SMTP_USE_SERVER, $useServer)) $isErr = true;// SMTP外部サーバを使用するかどうか
			}
			if (!$isErr){
				if (!$this->_mainDb->updateSystemConfig(self::CF_SMTP_HOST, $host)) $isErr = true;// SMTPホスト名
			}
			if (!$isErr){
				if (!$this->_mainDb->updateSystemConfig(self::CF_SMTP_PORT, $port)) $isErr = true;// SMTPポート番号
			}
			if (!$isErr){
				if (!$this->_mainDb->updateSystemConfig(self::CF_SMTP_ENCRYPT_TYPE, $this->encryptType)) $isErr = true;// 暗号化タイプ
			}
			if (!$isErr){
				if (!$this->_mainDb->updateSystemConfig(self::CF_SMTP_AUTHENTICATION, $authentication)) $isErr = true;// SMTP認証
			}
			if (!$isErr){
				if (!$this->_mainDb->updateSystemConfig(self::CF_SMTP_ACCOUNT, $account)) $isErr = true;// SMTP接続アカウント
			}
			if (!$isErr){
				if (!$this->_mainDb->updateSystemConfig(self::CF_SMTP_PASSWORD, $password)) $isErr = true;// SMTPパスワード
			}
	
			if ($isErr){		// エラー発生のとき
				$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
			} else {
				$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
			
				$replaceNew = true;		// データを再取得
			}
		} else if ($act == 'testmail'){		// テストメール送信のとき
			// サイト情報のメールアドレス取得
			$siteEmail = $this->gEnv->getSiteEmail();
			$host			= $this->_mainDb->getSystemConfig(self::CF_SMTP_HOST);			// SMTPホスト名
			$port			= $this->_mainDb->getSystemConfig(self::CF_SMTP_PORT);			// SMTPポート番号
			$account		= $this->_mainDb->getSystemConfig(self::CF_SMTP_ACCOUNT);			// SMTP接続アカウント
					
			$emailParam = array();
			$emailParam['BODY']  = $this->_('URL   :') . ' ' . $this->gEnv->getRootUrl() . M3_NL;		// URL     :
			$emailParam['BODY'] .= $this->_('Date  :') . ' ' . date("Y年m月d日 H時i分s秒") . M3_NL;		// 送信日時:
			$emailParam['BODY'] .= $this->_('Sender:') . ' ' . $this->gEnv->getCurrentUserName() . M3_NL;	// 送信者  :
			$this->gInstance->getMailManager()->setSmtpTestMode(true);		// SMTPテストモード起動
			$ret = $this->gInstance->getMailManager()->sendFormMail(-1/*テスト用*/, $this->gEnv->getCurrentWidgetId(), $siteEmail, $siteEmail, '', '', self::TEST_MAIL_FORM, $emailParam);
			$this->gInstance->getMailManager()->setSmtpTestMode(false);		// SMTPテストモード解除
			if ($ret){
				$this->setMsg(self::MSG_GUIDANCE, $this->_('Email sent. To:') . ' ' . $siteEmail);// メールを送信しました。メールアドレス:
			} else {
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in sending email. To:') . ' ' . $siteEmail);			// メール送信に失敗しました。メールアドレス:
			}
//			$ret = $this->gInstance->getMailManager()->smtpTest($errors);

/*			if ($ret){
				$this->setGuidanceMsg('テストメール送信しました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'テストメール送信に失敗しました');
				
				if (!empty($errors)){
					foreach ($errors as $error) {
						$this->setMsg(self::MSG_APP_ERR, $error);
					}
				}
			}*/
		} else {
			$replaceNew = true;		// データを再取得
		}
		
		if ($replaceNew){
			$useServer		= $this->_mainDb->getSystemConfig(self::CF_SMTP_USE_SERVER);			// SMTP外部サーバを使用するかどうか
			$host			= $this->_mainDb->getSystemConfig(self::CF_SMTP_HOST);			// SMTPホスト名
			$port			= $this->_mainDb->getSystemConfig(self::CF_SMTP_PORT);			// SMTPポート番号
			$this->encryptType = $this->_mainDb->getSystemConfig(self::CF_SMTP_ENCRYPT_TYPE);			// 暗号化タイプ
			$authentication	= $this->_mainDb->getSystemConfig(self::CF_SMTP_AUTHENTICATION);			// SMTP認証
			$account		= $this->_mainDb->getSystemConfig(self::CF_SMTP_ACCOUNT);			// SMTP接続アカウント
			$password		= $this->_mainDb->getSystemConfig(self::CF_SMTP_PASSWORD);			// SMTPパスワード
		}
		
		// 暗号化タイプメニュー作成
		$this->createEncryptTypeMenu();
		
		// ボタンの設定
		$testMailDisabled = false;			// メール送信ボタンの使用可否状態
		if (empty($host)) $testMailDisabled = true;
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar('_widget', 'smtp_use_server',	$this->convertToCheckedString($useServer));		// SMTP外部サーバを使用するかどうか
		$this->tmpl->addVar('_widget', 'smtp_host',			$this->convertToDispString($host));			// SMTPホスト名
		$this->tmpl->addVar('_widget', 'smtp_port',			$this->convertToDispString($port));			// SMTPポート番号
		$this->tmpl->addVar('_widget', 'smtp_authentication', $this->convertToCheckedString($authentication));	// SMTP認証
		$this->tmpl->addVar('_widget', 'smtp_account',		$this->convertToDispString($account));				// SMTP接続アカウント
		$this->tmpl->addVar('_widget', 'smtp_password',		$this->convertToDispString($password));				// SMTPパスワード
		$this->tmpl->addVar('_widget', 'test_mail_disabled',		$this->convertToDisabledString($testMailDisabled));		// メールテストボタン
	}
	/**
	 * 暗号化タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createEncryptTypeMenu()
	{
		for ($i = 0; $i < count($this->encryptTypeArray); $i++){
			$value = $this->encryptTypeArray[$i]['value'];
			$name = $this->encryptTypeArray[$i]['name'];
		
			$row = array(
				'value'    => $this->convertToDispString($value),			// 値
				'name'     => $this->convertToDispString($name),			// 名前
				'selected' => $this->convertToSelectedString($value, $this->encryptType)			// 選択中かどうか
			);
			$this->tmpl->addVars('encrypt_type_list', $row);
			$this->tmpl->parseTemplate('encrypt_type_list', 'a');
		}
	}
}
?>
