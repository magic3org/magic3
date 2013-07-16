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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_contactus_customWidgetContainer.php 2363 2009-09-26 14:45:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseMobileWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/contactus_customDb.php');

class m_contactus_customWidgetContainer extends BaseMobileWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $fieldInfoArray = array();			// お問い合わせ項目情報
	private $valueArray;		// 項目入力値
	const DEFAULT_CONFIG_ID = 0;
	const CONTACTUS_FORM = 'contact_us';		// お問い合わせフォーム
	const DEFAULT_SEND_MESSAGE = 1;		// メール送信機能を使用するかどうか(デフォルト使用)
	const DEFAULT_TITLE_NAME = 'お問い合わせ';	// デフォルトのタイトル名
	const DEFAULT_STR_REQUIRED = '<span style="color:#ff0000"><font color="#ff0000">*必須</font></span>';		// 「必須」表示用テキスト
	const FIELD_HEAD = 'item';			// フィールド名の先頭文字列
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new contactus_customDb();
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
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
	
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)) return;		// 定義データが取得できないときは終了
		
		// デフォルト値設定
		$inputEnabled = true;			// 入力の許可状態
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$sendMessage = self::DEFAULT_SEND_MESSAGE;			// メール送信機能を使用するかどうか
		$showTitle = 0;				// タイトルを表示するかどうか
		$titleName = self::DEFAULT_TITLE_NAME;			// タイトル名
		$titleBgColor = '';		// タイトルバックグランドカラー
		$explanation = '';			// 説明
		
		//$sendMessage = $targetObj->sendMessage;			// メール送信機能を使用するかどうか
		$emailReceiver = $targetObj->emailReceiver;		// メール受信者
		$emailSubject = $targetObj->emailSubject;		// メール件名
		$showTitle = $targetObj->showTitle;				// タイトルを表示するかどうか
		if (!empty($targetObj->titleName)) $titleName = $targetObj->titleName;			// タイトル名
		$titleBgColor = $targetObj->titleBgColor;		// タイトルバックグランドカラー
		$explanation = $targetObj->explanation;			// 説明
		$name	= $targetObj->name;// 名前
		if (!empty($targetObj->fieldInfo)) $this->fieldInfoArray = $targetObj->fieldInfo;			// お問い合わせフィールド情報
		
		// 入力値を取得
		$this->valueArray = array();
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$itemName = self::FIELD_HEAD . ($i + 1);
			$itemValue = $request->mobileTrimValueOf($itemName);
			$this->valueArray[] = $itemValue;
		}
		$act = $request->trimValueOf('act');
		
		if ($act == 'send'){		// お問い合わせメール送信
			$postTicket = $request->trimValueOf('ticket');		// POST確認用
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 入力状況のチェック
				for ($i = 0; $i < $fieldCount; $i++){
					$infoObj = $this->fieldInfoArray[$i];
					$title = $infoObj->title;// タイトル名
					$type = $infoObj->type;		// 項目タイプ
					$required = $infoObj->required;		// 必須入力
					if (!empty($required) && empty($this->valueArray[$i])) $this->setUserErrorMsg('「' . $title . '」は必須入力項目です');
				}

				// エラーなしの場合はメール送信
				if ($this->getMsgCount() == 0){
					$this->setGuidanceMsg('送信完了しました');
				
					// メール送信設定のときはメールを送信
					if ($sendMessage){
						// メール本文の作成
						$mailBody = '';
						for ($i = 0; $i < $fieldCount; $i++){
							$infoObj = $this->fieldInfoArray[$i];
							$title = $infoObj->title;// タイトル名
							$type = $infoObj->type;		// 項目タイプ
						
							$mailBody .= $title . "\n";
							if (!empty($this->valueArray[$i])){
								if (is_array($this->valueArray[$i])){		// 配列データのとき
									for ($j = 0; $j < count($this->valueArray[$i]); $j++){
										$mailBody .= $this->valueArray[$i][$j] . "\n";
									}
								} else {
									$mailBody .= $this->valueArray[$i] . "\n";
								}
							}
							$mailBody .= "\n";
						}
					
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
							$email = '';		// 返信先は空にする(暫定)
							$ret = $this->gInstance->getMailManager()->sendFormMail(2/*手動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, $email, $emailSubject, self::CONTACTUS_FORM, $mailParam);
						}
					}
					// 項目を入力不可に設定
					$inputEnabled = false;			// 入力の許可状態

					//$this->tmpl->addVar("_widget", "message", '送信しました');// 送信ボタンラベル
				} else {
					$this->tmpl->addVar("show_send_button", "send_button_label", '送信する');// 送信ボタンラベル
					$this->tmpl->setAttribute('show_send_button', 'visibility', 'visible');
				}
			}
		} else {
			// ハッシュキー作成
			$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
			$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
			
			// メール送信不可の場合はボタンを使用不可にする
			if ($sendMessage){
				$this->tmpl->addVar("show_send_button", "send_button_label", '送信する');// 送信ボタンラベル
			} else {
				$this->tmpl->addVar("show_send_button", "send_button_label", '送信停止中');// 送信ボタンラベル
				//$this->tmpl->addVar("_widget", "send_button_disabled", 'disabled');// 送信ボタン
			}
			$this->tmpl->setAttribute('show_send_button', 'visibility', 'visible');
		}
		
		// HTMLサブタイトルを設定
		$this->gPage->setHeadSubTitle(self::DEFAULT_TITLE_NAME);

		// パラメータ埋め込み
		$this->tmpl->addVar('_widget', 'url', $this->gEnv->createCurrentPageUrlForMobile());		// Post用URL
		$this->tmpl->addVar('_widget', 'act', 'send');
		
		// タイトルの表示
		if ($showTitle){
			$titleClassStr = 'align="center" style="text-align:center;';
			if (!empty($titleBgColor)) $titleClassStr .= 'background-color:' . $titleBgColor . ';';// タイトルバックグランドカラー
			$titleClassStr .= '"';
			$this->tmpl->addVar("show_title", "class", $titleClassStr);
			$this->tmpl->setAttribute('show_title', 'visibility', 'visible');
			$this->tmpl->addVar("show_title", "title_name", $this->convertToDispString($titleName));// タイトル名
		}
		// 説明の表示
		if (!empty($explanation)){
			$this->tmpl->setAttribute('show_explanation', 'visibility', 'visible');
			$this->tmpl->addVar("show_explanation", "explanation", $explanation);// 説明
		}
		// お問い合わせフィールド作成
		$fieldCount = $this->createFieldList($inputEnabled);
		if ($fieldCount == 0) $this->tmpl->setAttribute('field_list', 'visibility', 'hidden');
		
		$this->tmpl->addVar("_widget", "field_count", $fieldCount);// お問い合わせ項目数
	}
	/**
	 * お問い合わせフィールド作成
	 *
	 * @param bool $enabled		項目の入力許可状態
	 * @return int	フィールド項目数
	 */
	function createFieldList($enabled)
	{
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$infoObj = $this->fieldInfoArray[$i];
			$title = $infoObj->title;// タイトル名
			$desc = $infoObj->desc;		// 説明
			$type = $infoObj->type;		// 項目タイプ
			$def = $infoObj->def;		// 項目定義
			$required = '';
			if (!empty($infoObj->required)) $required = '&nbsp;' . self::DEFAULT_STR_REQUIRED;// 必須表示
			
			// 入力フィールドの作成
			$fieldName = self::FIELD_HEAD . ($i + 1);
			$inputValue = $this->valueArray[$i];		// 入力値
			$inputTag = '';
			switch ($type){
				case 'text':		// テキストボックス
					$param = array();
					$paramStr = '';
					$size = 0;
					$defArray = explode(';', $def);
					for ($j = 0; $j < count($defArray); $j++){
						list($key, $value) = explode('=', $defArray[$j]);
						$key = trim($key);
						$value = trim($value);
						if (strcasecmp($key, 'size') == 0){
							$size = intval($value);
							break;
						}
					}
					if ($size > 0) $param[] = 'size="' . $size . '"';
					if (!empty($inputValue)){
						$param[] = 'value="' . $inputValue . '"';
					}
					if (!$enabled) $param[] = 'disabled';		// 使用不可
					if (count($param) > 0) $paramStr = ' ' . implode($param, ' ');
					$inputTag = '<input type="text" name="' . $fieldName . '"' . $paramStr . ' /><br />' . M3_NL;
					break;
				case 'textarea':	// テキストエリア
					$param = array();
					$paramStr = '';
					$row = 0;
					$col = 0;
					$defArray = explode(';', $def);
					for ($j = 0; $j < count($defArray); $j++){
						list($key, $value) = explode('=', $defArray[$j]);
						$key = trim($key);
						$value = trim($value);
						if (strcasecmp($key, 'rows') == 0){
							$row = intval($value);
						} else if (strcasecmp($key, 'cols') == 0){
							$col = intval($value);
						}
					}
					if ($row > 0) $param[] = 'rows="' . $row . '"';
					if ($col > 0) $param[] = 'cols="' . $col . '"';
					if (!$enabled) $param[] = 'disabled';		// 使用不可
					if (count($param) > 0) $paramStr = ' ' . implode($param, ' ');
					$inputTag = '<textarea name="' . $fieldName . '"' . $paramStr . '>' . $this->convertToDispString($inputValue) . '</textarea><br />' . M3_NL;
					break;
				case 'select':	// セレクトメニュー
					$param = array();
					$paramStr = '';
					if (!$enabled) $param[] = 'disabled';		// 使用不可
					if (count($param) > 0) $paramStr = ' ' . implode($param, ' ');
					$inputTag = '<select name="' . $fieldName . '"'. $paramStr . '>' . M3_NL;
					$inputTag .= '<option value="">&nbsp;</option>' . M3_NL;
					$defArray = explode(';', $def);
					for ($j = 0; $j < count($defArray); $j++){
						$param = array();
						$paramStr = '';
						list($key, $value) = explode('=', $defArray[$j]);
						$key = trim($key);
						$value = trim($value);
						if (empty($value)) $value = $key;
						if (!empty($key)){
							if (!empty($inputValue) && strcmp($inputValue, $value) == 0) $param[] = 'selected';
							if (count($param) > 0) $paramStr = ' ' . implode($param, ' ');
							$inputTag .= '<option value="' . $this->convertToDispString($value) . '"' . $paramStr . '>' . $this->convertToDispString($key) . '</option>' . M3_NL;
						}
					}
					$inputTag .= '</select><br />' . M3_NL;
					break;
				case 'checkbox':	// チェックボックス
				case 'radio':	// ラジオボタン
					$fieldName .= '[]';
					$defArray = explode(';', $def);
					for ($j = 0; $j < count($defArray); $j++){
						$param = array();
						$paramStr = '';
						list($key, $value) = explode('=', $defArray[$j]);
						$key = trim($key);
						$value = trim($value);
						if (empty($value)) $value = $key;
						if (!empty($key) && !empty($value)){
							for ($k = 0; $k < count($inputValue); $k++){
								if (!empty($inputValue[$k]) && strcmp($inputValue[$k], $value) == 0) $param[] = 'checked';
							}
							if (!$enabled) $param[] = 'disabled';		// 使用不可
							if (count($param) > 0) $paramStr = ' ' . implode($param, ' ');
							$inputTag .= '<input type="' . $type . '" name="' . $fieldName . '" value="' . $this->convertToDispString($value) . '"' . $paramStr . ' />' . $this->convertToDispString($key) . '<br />' . M3_NL;
						}
					}
					break;
			}

			// 改行の設定
			$titleBr = '';
			if (!empty($title) || !empty($required)) $titleBr = '<br />';
			$descBr = '';
			if (!empty($desc)) $descBr = '<br />';
			$inputBr = '';
			if (!empty($inputTag)) $inputBr = '<br />';
			
			$row = array(
				'title' => $this->convertToDispString($title),			// タイトル名
				'desc' => $this->convertToDispString($desc),				// 説明
				'title_br' => $titleBr,			// タイトル名改行
				'desc_br' => $descBr,			// 説明改行
				'input_br' => $inputBr,			// 入力フィールド改行
				'required' => $required,			// 必須表示
				'input' => $inputTag			// 入力フィールド
			);
			$this->tmpl->addVars('field_list', $row);
			$this->tmpl->parseTemplate('field_list', 'a');
		}
		return $fieldCount;
	}
}
?>
