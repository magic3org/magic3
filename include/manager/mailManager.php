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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class MailManager extends Core
{
	private $db;						// DBオブジェクト
	const EMAIL_SEPARATOR = ';';		// メールアドレスセパレータ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
			
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
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
	 * @param int $type				メール送信タイプ(0=未設定、1=自動送信、2=手動送信)
	 * @param string $widgetId		送信を行ったウィジェットID
	 * @param string $toAddress		送信先メールアドレス(「|」区切りで複数送信可。フォーマット「アドレス1|cc:アドレス2|bcc:アドレス3」)
	 * @param string $fromAddress	送信元メールアドレス
	 * @param string $replytoAddress	返信先メールアドレス(空の場合は$fromAddressを使用)
	 * @param string $subject		件名(空のときは、メールフォームテーブルから取得)
	 * @param string $formId		メールフォームID(空のときは$mailFormにメールフォームを設定)
	 * @param array,string  $params	本文置き換え文字列、連想配列で設定。
	 * @param string $ccAddress		CCメールアドレス
	 * @param string $bccAddress	BCCメールアドレス
	 * @param string $mailForm		メールフォームデータ($formIdが空のときに使用)
	 * @return bool					true=成功、false=失敗
	 */
	public function sendFormMail($type, $widgetId, $toAddress, $fromAddress, $replytoAddress, $subject, $formId, $params, $ccAddress = '', $bccAddress = '', $mailForm = '')
	{
		global $gEnvManager;
		
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
		
		// メール件名、本文を取得
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

		// 本文を置き換え
		if (!empty($params)){		// 変換パラメータが設定されているとき
			while (list($key, $val) = each($params)){
				$destContent = preg_replace("/\[#" . $key . "#\]/", $val, $destContent);
			}
		}
		
		// 半角カナを全角に変換(メールでは半角カナは使用できないため)
		if (function_exists('mb_convert_kana')) $destContent = mb_convert_kana($destContent, "KV");
		
		$option = '-f' . $errAddress;		// エラーメールを返すアドレスを設定

		if (function_exists('mb_send_mail')){		// mbが使用可能なとき
			if (separateMailAddress($toAddress, $mail, $name)){		// メールアドレス、名前を取り出す
				$toAddress = mb_encode_mimeheader($name) . '<' . $mail . '>';
			}
			if (ini_get('safe_mode')){		// 「sefe mode」 が効いているときは、mb_send_mail()の5番目の引数が使用できない
				$ret = mb_send_mail($toAddress, $destSubject, $destContent, $destHeader);
			} else {
				$ret = mb_send_mail($toAddress, $destSubject, $destContent, $destHeader, $option);
			}
		} else {
			if (ini_get('safe_mode')){		// 「sefe mode」 が効いているときは、mail()の5番目の引数が使用できない
				$ret = mail($toAddress, $destSubject, $destContent, $destHeader);
			} else {
				$ret = mail($toAddress, $destSubject, $destContent, $destHeader, $option);
			}
		}
		
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
}
?>
