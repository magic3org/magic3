<?php
/**
 * メールマネージャー
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');
require_once(M3_SYSTEM_INCLUDE_PATH .	'/lib/PHPMailer-5.2.14/PHPMailerAutoload.php');

class MailManager extends Core
{
	private $db;						// DBオブジェクト
	private $smtpTestMode;				// SMTPテストモードかどうか
	private $errMessages;				// エラーメッセージ
	private $isMultipleSend;			// 連続送信かどうか
	private $maxMultipleSendCount;		// 連続送信数最大
	private $sendCount;					// 送信数
	const LOCAL_BY_PHPMAILER = true;	// ローカルの送信をPHPMailerで送信するかどうか。true=新規バージョン。
	const EMAIL_SEPARATOR = ';';		// メールアドレスセパレータ
	const DEFAULT_MULTIPLE_SEND_COUNT = 100;	// 連続送信数最大デフォルト値
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
			
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
		
		// 初期値設定
		$this->maxMultipleSendCount = self::DEFAULT_MULTIPLE_SEND_COUNT;			// 連続送信数最大
	}
	/**
	 * メールを送信
	 *
	 * mbstringが使用可能なときは、mb_send_mail()を使用してメールを送信。
	 * 使用不可の場合は、mail()で送信する。
	 * メールアドレスの指定は、「名前<name@sample.com>」形式のアドレスも可能とする。
	 * メールの内容は、メールフォームテーブルから取得し、パラメータを変換して作成。
	 * 送信したメールの内容はログテーブルに残す
	 *
	 * @param int          $type			メール送信タイプ(0=未設定、-1=テスト用、1=自動送信、2=手動送信)
	 * @param string       $widgetId		送信を行ったウィジェットID
	 * @param string       $toAddress		送信先メールアドレス(「;」区切りで複数送信可。フォーマット「アドレス1;cc:アドレス2;bcc:アドレス3」)
	 * @param string       $fromAddress		送信元メールアドレス
	 * @param string       $replytoAddress	返信先メールアドレス(空の場合は$fromAddressを使用)
	 * @param string       $subject			件名(空のときは、メールフォームテーブルから取得)
	 * @param string       $formId			メールフォームID(空のときは$mailFormにメールフォームを設定)
	 * @param array,string $params			本文置き換え文字列、連想配列で設定。
	 * @param string       $ccAddress		CCメールアドレス
	 * @param string       $bccAddress		BCCメールアドレス
	 * @param string       $mailForm		メールフォームデータ($formIdが空のときに使用)
	 * @param array,string $titleParams		本文置き換え文字列、連想配列で設定。
	 * @param string       $tilteHeadStr	タイトルの先頭に追加する文字列
	 * @param string       $contentHeadStr	本文の先頭に追加する文字列
	 * @return bool							true=成功、false=失敗
	 */
	public function sendFormMail($type, $widgetId, $toAddress, $fromAddress, $replytoAddress, $subject, $formId, $params, 
								$ccAddress = '', $bccAddress = '', $mailForm = '', $titleParams = '', $tilteHeadStr = '', $contentHeadStr = '')
	{
		global $gEnvManager;
		global $gSystemManager;
		
		// エラーメッセージ初期化
		$this->_resetErrorMessage();
		
		$langId = $gEnvManager->getCurrentLanguage();
		
		// 送信先アドレスの解析
		$toAddressArray = array();
		$ccAddressArray = array();
		$bccAddressArray = array();
		$toAddressParsedArray = explode(self::EMAIL_SEPARATOR, $toAddress);
		for ($i = 0; $i < count($toAddressParsedArray); $i++){
			$line = trim($toAddressParsedArray[$i]);
			if (empty($line)) continue;
			
			list($tag, $address) = array_map('trim', explode(':', $line));
			$compTag = strtolower($tag);
			switch ($compTag){
				case 'cc':
					$ccAddressArray[] = $address;
					break;
				case 'bcc':
					$bccAddressArray[] = $address;
					break;
				default:
					$toAddressArray[] = $tag;
					break;
			}
		}
		if (count($toAddressArray) > 0) $toAddress = $toAddressArray[0];

		// 送信元アドレスの修正
		// 送信元アドレスに「cc」「bcc」がある場合は削除
		$fromAddressArray = array();
		$fromAddressParsedArray = explode(self::EMAIL_SEPARATOR, $fromAddress);
		for ($i = 0; $i < count($fromAddressParsedArray); $i++){
			$line = trim($fromAddressParsedArray[$i]);
			if (empty($line)) continue;
			
			list($tag, $address) = array_map('trim', explode(':', $line));
			$compTag = strtolower($tag);
			switch ($compTag){
				case 'cc':
					break;
				case 'bcc':
					break;
				default:
					$fromAddressArray[] = $tag;
					break;
			}
		}
		if (count($fromAddressArray) > 0) $fromAddress = $fromAddressArray[0];
		
		// 送信元、送信先のチェック
		if (empty($toAddress) || empty($fromAddress)){
			$this->gOpeLog->writeError(__METHOD__, 'メールアドレスが設定されていません。(送信先=' . $toAddress . ', 送信元=' . $fromAddress . ')', 1100);
			return false;
		}
	
		// メールフォーマットのチェック
		if (!$this->checkEmail($toAddress) || !$this->checkEmail($fromAddress) || !$this->checkEmail($ccAddress) || !$this->checkEmail($bccAddress)){
			$this->gOpeLog->writeError(__METHOD__, '不正なメールアドレスが設定されています。(送信先=' . $toAddress . ', 送信元=' . $fromAddress . 
										', CC=' . $ccAddress . ', BCC=' . $bccAddress . ')', 1100);
			return false;
		}
		
		// ##### メール件名、本文を取得 #####
		if (empty($formId)){	// メールフォームIDが空のときは独自メールフォールを使用
			if (empty($subject) || empty($mailForm)){
				$this->gOpeLog->writeError(__METHOD__, 'メール件名または本文が設定されていません。。', 1100);
				return false;
			}
			$destSubject = $subject;
			$destContent = $mailForm;
		} else {
			if (!$this->db->getMailForm($formId, $langId, $row)){
				$this->gOpeLog->writeError(__METHOD__, 'メールフォームが見つかりません。(id=' . $formId . ')', 1100);
				return false;
			}
			$destSubject = empty($subject) ? $row['mf_subject'] : $subject;
			$destContent = $row['mf_content'];
		}
		
		$destHeader = '';
		if (empty($replytoAddress)) $replytoAddress = $fromAddress;
		$errAddress = $fromAddress;		// エラーメールの送信先
		if (function_exists('mb_send_mail')){		// mbが使用可能なとき
			// 送信元のメールアドレスを設定
			if (separateMailAddress($fromAddress, $mail, $name)){		// メールアドレス、名前を取り出す
				$destHeader .= 'From: ' . mb_encode_mimeheader($name) . '<' . $mail . ">\n";
				$errAddress = $mail;	// エラーメールの送信先
			} else {		// 取得失敗のとき
				$destHeader .= empty($fromAddress) ? '' : 'From: ' . $fromAddress . "\n";
			}
			// 返信先のメールアドレスを設定
			if (separateMailAddress($replytoAddress, $mail, $name)){		// メールアドレス、名前を取り出す
				$destHeader .= 'Reply-To: ' . mb_encode_mimeheader($name) . '<' . $mail . ">\n";
			} else {
				$destHeader .= empty($replytoAddress) ? '' : 'Reply-To: ' . $replytoAddress . "\n";
			}
		} else {
			$destHeader .= empty($fromAddress) ? '' : 'From: ' . $fromAddress . "\n";
			$destHeader .= empty($replytoAddress) ? '' : 'Reply-To: ' . $replytoAddress . "\n";
		}
		// CCのメールアドレスを設定
		if (!empty($ccAddress)) $destHeader .= 'Cc: ' . $ccAddress . "\n";
		$destHeader .= implode('', array_map(create_function('$a', 'return "Cc: " . $a . "\n";'), $ccAddressArray));
		// BCCのメールアドレスを設定
		if (!empty($bccAddress)) $destHeader .= 'Bcc: ' . $bccAddress . "\n";
		$destHeader .= implode('', array_map(create_function('$a', 'return "Bcc: " . $a . "\n";'), $bccAddressArray));

		// ##### メール件名、本文のマクロを置換 #####
		// 件名を置換
		if (!empty($titleParams)){		// 変換パラメータが設定されているとき
			while (list($key, $val) = each($titleParams)){
				$destSubject = str_replace(M3_TAG_START . $key . M3_TAG_END, $val, $destSubject);
			}
		}
		// 本文を置換
		if (!empty($params)){		// 変換パラメータが設定されているとき
			while (list($key, $val) = each($params)){
				//$destContent = preg_replace("/\[#" . $key . "#\]/", $val, $destContent);
				$destContent = str_replace(M3_TAG_START . $key . M3_TAG_END, $val, $destContent);
			}
		}
		
		// 半角カナを全角に変換(メールでは半角カナは使用できないため)
		if (function_exists('mb_convert_kana')) $destContent = mb_convert_kana($destContent, "KV");
		
		// メール件名、本文に追加文字列を連結
		$destSubject = $tilteHeadStr . $destSubject;
		$destContent = $contentHeadStr . $destContent;

		// ##### メール送信処理 #####
		$useSmtpServer	= $gSystemManager->getSystemConfig(self::CF_SMTP_USE_SERVER);		// SMTP外部サーバを使用するかどうか
		
		if ($this->smtpTestMode || $useSmtpServer){		// SMTPテストモードまたはSMTPサーバ使用のとき
			$ret = $this->_smtpSendMail(true/*SMTP認証で送信*/, $toAddress, $destSubject, $destContent, $destHeader, $errAddress, $type, $fromAddress, $replytoAddress, $ccAddressArray, $bccAddressArray);
		} else {
			if (self::LOCAL_BY_PHPMAILER){		// PHPMailerで送信(新規バージョン)
				$ret = $this->_smtpSendMail(false/*SMTP認証なしで送信*/, $toAddress, $destSubject, $destContent, $destHeader, $errAddress, $type, $fromAddress, $replytoAddress, $ccAddressArray, $bccAddressArray);
			} else {		// 旧バージョン
				$option = '-f' . $errAddress;		// エラーメールを返すアドレスを設定
		
				if (function_exists('mb_send_mail')){		// mbが使用可能なとき
					if (separateMailAddress($toAddress, $mail, $name)){		// メールアドレス、名前を取り出す
						$toAddressMime = mb_encode_mimeheader($name) . '<' . $mail . '>';
					} else {
						$toAddressMime = $toAddress;
					}
					if (ini_get('safe_mode')){		// 「sefe mode」 が効いているときは、mb_send_mail()の5番目の引数が使用できない
						$ret = mb_send_mail($toAddressMime, $destSubject, $destContent, $destHeader);
					} else {
						$ret = mb_send_mail($toAddressMime, $destSubject, $destContent, $destHeader, $option);
					}
				} else {
					if (ini_get('safe_mode')){		// 「sefe mode」 が効いているときは、mail()の5番目の引数が使用できない
						$ret = mail($toAddress, $destSubject, $destContent, $destHeader);
					} else {
						$ret = mail($toAddress, $destSubject, $destContent, $destHeader, $option);
					}
				}
			}
		}
		
		// ##### ログ出力 #####
		// 送信成功したときは、ログに残す
		if ($ret){
			$this->db->addMailLog($type, $widgetId, $toAddress, $fromAddress, $destSubject, $destContent);
			$msgDetail = 'subject=' . $destSubject . ', body=' . $destContent;
			$this->gOpeLog->writeInfo(__METHOD__, 'メールが送信されました。(送信先=' . $toAddress . ', 送信元=' . $fromAddress . ')', 1000, $msgDetail);
		}
		return $ret;
	}
	/**
	 * メールフォームを取得
	 *
	 * @param string $id		フォームID
	 * @param  string $langId	言語ID
	 * @param  array  $row		取得レコード
	 * @return bool				true=正常取得、false=取得できず
	 */
	function getMailForm($id, $langId, &$row)
	{
		$ret = $this->db->getMailForm($id, $langId, $row);
		return $ret;
	}
	/**
	 * メールフォーマットを更新
	 *
	 * @param string $id			フォームID
	 * @param string $langId		言語ID
	 * @param string $content		メール本文
	 * @param string $subject		メール件名
	 * @return bool 				true=正常、false=異常
	 */
	function updateMailForm($id, $langId, $content, $subject = null)
	{
		$ret = $this->db->updateMailForm($id, $langId, $content, $subject);
		return $ret;
	}
	/**
	 * メールアドレスに不正な文字列が入っていないかチェック
	 *
	 * @param string $value			Eメールアドレス
	 * @return bool 				true=正常、false=異常
	 */
	function checkEmail($value)
	{
		if (empty($value)) return true;
		$pattern = array("/\n/","/\r/","/content-type:/i","/to:/i", "/from:/i", "/cc:/i");
		$destValue = preg_replace($pattern, "", $value);
		if ($value == $destValue){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * SMTPテストモードを設定
	 *
	 * @param bool $on		SMTPテストモードかどうか
	 * @return				なし
	 */
	public function setSmtpTestMode($on)
	{
		$this->smtpTestMode = $on;
	}
	/**
	 * SMTPテストモードを取得
	 *
	 * @return bool		SMTPテストモードかどうか
	 */
	public function getSmtpTestMode()
	{
		return $this->smtpTestMode;
	}
	/**
	 * 連続メール送信を開始
	 *
	 * @return				なし
	 */
	public function startMultipleSend()
	{
		$this->isMultipleSend = true;
		$this->sendCount = 0;
	}
	/**
	 * 連続メール送信を終了
	 *
	 * @return				なし
	 */
	public function endMultipleSend()
	{
		$this->isMultipleSend = false;
		$this->sendCount = 0;
	}
	/**
	 * SMTPでメール送信
	 *
	 * @param bool $isSmtp			SMTP認証を行って送信するかどうか
	 * @param string $toAddress		メール送信先
	 * @param string $subject		タイトル
	 * @param string $content		メール本文
	 * @param string $header		メールヘッダ追加文字列
	 * @param string $errAddress	エラーメール戻り先
	 * @param int    $mailType		メール送信タイプ(0=未設定、-1=テスト用、1=自動送信、2=手動送信)
	 * @param string $fromAddress		メール送信元
	 * @param string $replytoAddress	メール返信先
	 * @param array $ccAddressArray		メール送信先(CC)
	 * @param array $bccAddressArray	メール送信先(BCC)
	 * @return bool 					true=正常、false=異常
	 */
	function _smtpSendMail($isSmtp, $toAddress, $subject, $content, $header, $errAddress, $mailType, $fromAddress, $replytoAddress, $ccAddressArray, $bccAddressArray)
	{
		global $gSystemManager;
		static $mail;			// メール送信オブジェクト

		// SMTP接続設定取得
		$smtpHost		= $gSystemManager->getSystemConfig(self::CF_SMTP_HOST);		// SMTPホスト名
		$smtpPort		= $gSystemManager->getSystemConfig(self::CF_SMTP_PORT);		// SMTPポート番号
		$smtpEncryptType = $gSystemManager->getSystemConfig(self::CF_SMTP_ENCRYPT_TYPE);			// SMTP暗号化タイプ
		$smtpAuthentication = $gSystemManager->getSystemConfig(self::CF_SMTP_AUTHENTICATION);		// SMTP認証
		$smtpAccount	= $gSystemManager->getSystemConfig(self::CF_SMTP_ACCOUNT);					// SMTP接続アカウント
		$smtpPassword	= $gSystemManager->getSystemConfig(self::CF_SMTP_PASSWORD);					// SMTPパスワード

		if (!isset($mail)){
			$mail = new PHPMailer;

			if ($isSmtp){		// SMTP認証付きで送信するかどうか
				// SMTP接続情報
				$mail->isSMTP();					// Set mailer to use SMTP
				$mail->Host = $smtpHost;			// Specify main and backup SMTP servers
				$mail->Port = $smtpPort;			// TCP port to connect to
				$mail->SMTPAuth = boolval($smtpAuthentication);		// Enable SMTP authentication
				$mail->Username = $smtpAccount;						// SMTP username
				$mail->Password = $smtpPassword;					// SMTP password
				$mail->SMTPSecure = $smtpEncryptType;				// Enable TLS encryption, `ssl` also accepted
			}
			
			// メール送信情報を設定
			$mail->Sender = $errAddress;		// エラーメールの戻り先
			// メール送信元
			if (separateMailAddress($fromAddress, $email, $name)){		// メールアドレス、名前を取り出す
				$mail->setFrom($email, mb_encode_mimeheader(mb_convert_encoding($name, 'JIS', M3_ENCODING)));
			} else {
				$mail->setFrom($fromAddress);
			}
			// メール返信先
			if (separateMailAddress($replytoAddress, $email, $name)){		// メールアドレス、名前を取り出す
				$mail->addReplyTo($email, mb_encode_mimeheader(mb_convert_encoding($name, 'JIS', M3_ENCODING)));
			} else {
				$mail->addReplyTo($replytoAddress);
			}
			
			if ($this->isMultipleSend){
				$mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead
			} else {			// メール連続送信の場合はCC,BCCを使用しない
				// メール送信先(CC)
				for ($i = 0; $i < count($ccAddressArray); $i++){
					if (separateMailAddress($ccAddressArray[$i], $email, $name)){		// メールアドレス、名前を取り出す
						$mail->addCC($email, mb_encode_mimeheader(mb_convert_encoding($name, 'JIS', M3_ENCODING)));
					} else {
						$mail->addCC($ccAddressArray[$i]);
					}
				}
				// メール送信先(BCC)
				for ($i = 0; $i < count($bccAddressArray); $i++){
					if (separateMailAddress($bccAddressArray[$i], $email, $name)){		// メールアドレス、名前を取り出す
						$mail->addBCC($email, mb_encode_mimeheader(mb_convert_encoding($name, 'JIS', M3_ENCODING)));
					} else {
						$mail->addBCC($bccAddressArray[$i]);
					}
				}
			}
		}

		// メール送信先
		if (separateMailAddress($toAddress, $email, $name)){		// メールアドレス、名前を取り出す
			$mail->addAddress($email, mb_encode_mimeheader(mb_convert_encoding($name, 'JIS', M3_ENCODING)));
		} else {
			$mail->addAddress($toAddress);     // Add a recipient
		}
		
		// SMTP認証付きで送信する場合、テストメールはタイトル、本文に情報を追加
		if ($isSmtp && $mailType == -1){
			$subject .= '(SMTP)';
			$content .= M3_NL;
			$content .= 'SMTPメールサーバ  : ' . $smtpHost . ':' . $smtpPort . M3_NL;
			$content .= 'SMTPメールユーザ名: ' . $smtpAccount . M3_NL;
		}
			
		// メール本文
		$mail->isHTML(false);		// テキストのみのメール
		$mail->Encoding = '7bit';
		$mail->CharSet = 'ISO-2022-JP';
		$mail->Subject	= mb_encode_mimeheader(mb_convert_encoding($subject, 'JIS', M3_ENCODING));
		$mail->Body		= mb_convert_encoding($content, 'JIS', M3_ENCODING);
		// HTMLメールの場合
		//mail->isHTML(true);                                  // Set email format to HTML
		//$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		// 添付ファイル
		//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

		$ret = $mail->send();
		if (!$ret) $this->_addErrorMessage($mail->ErrorInfo);		// エラーメッセージ追加

		// 連続送信の場合は送信数をカウント
		if ($this->isMultipleSend){			// メール連続送信の場合
			// 送信先をクリア
			$mail->clearAddresses();
			
			// 最大送信数に達したときは、一旦コネクションを切断
			$this->sendCount++;
			if ($this->sendCount >= $this->maxMultipleSendCount){
				unset($mail);
				$this->sendCount = 0;
			}
		} else {
			unset($mail);
		}
		return $ret;
	}
	/**
	 * エラーメッセージ初期化
	 *
	 * @return				なし
	 */
	function _resetErrorMessage()
	{
		$this->errMessages = array();				// エラーメッセージ
	}
	/**
	 * エラーメッセージ追加
	 *
	 * @param string $msg	エラーメッセージ
	 * @return				なし
	 */
	function _addErrorMessage($msg)
	{
		$this->errMessages[] = $msg;				// エラーメッセージ
	}
	/**
	 * エラーメッセージ取得
	 *
	 * @return array		エラーメッセージ
	 */
	function getErrorMessage()
	{
		return $this->errMessages;				// エラーメッセージ
	}
}
?>
