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
 * @version    SVN: $Id: contactus_modalWidgetContainer.php 2363 2009-09-26 14:45:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class contactus_modalWidgetContainer extends BaseWidgetContainer
{
	private $langId;		// 現在の言語
	private $paramObj;		// 定義取得用
	private $sendStatus;	// フォーム送信ステータス
	const DEFAULT_CONFIG_ID = 0;
	const ACT_SHOW_FORM = 'showform';			// フォームを表示させる動作
	const ACT_SEND_FORM = 'sendform';			// フォームを送信する動作
	const CONTACTUS_FORM = 'contact_us';		// お問い合わせフォーム
	const DEFAULT_TITLE_NAME = 'お問い合わせ';	// デフォルトのタイトル名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// フォーム送信ステータス取得
		$this->sendStatus = '';
		$cmd = $this->gRequest->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		$act = $this->gRequest->trimValueOf('act');
		if ($cmd == M3_REQUEST_CMD_DO_WIDGET){
			switch ($act){
				case self::ACT_SHOW_FORM:	// フォーム表示
				case self::ACT_SEND_FORM:	// フォームを送信
					$this->sendStatus = $act;
					break;
			}
		}
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
		if ($this->sendStatus == self::ACT_SHOW_FORM){// フォーム表示
			return 'form.tmpl.html';
		} else if ($this->sendStatus == self::ACT_SEND_FORM){	// フォームを送信
			return '';
		} else {
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		
		if ($this->sendStatus == self::ACT_SHOW_FORM){		// フォーム表示
			// ハッシュキー作成
			$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
			$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
			$this->tmpl->addVar("_widget", "act", self::ACT_SEND_FORM);				// 送信処理
		} else if ($this->sendStatus == self::ACT_SEND_FORM){		// フォーム送信
			$postTicket = $request->trimValueOf('ticket');		// POST確認用
			$ret = false;
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// メール作成用のデータを取得
				$name = $request->trimValueOf('name');
				$fromEmail = $request->trimValueOf('email');
				$subject = $request->trimValueOf('subject');
				$message = $request->trimValueOf('message');
				$cc		= ($request->trimValueOf('cc') == 'on') ? 1 : 0;
				$ccEmail = '';
				if ($cc) $ccEmail = $fromEmail;

				// 送信元、送信先
				$toEmail = $this->gEnv->getSiteEmail();		// デフォルトのサイト向けEメールアドレス
				
				// メールを送信
				if (empty($toEmail)){
					$this->gOpeLog->writeError(__METHOD__, 'メール送信に失敗しました。基本情報のEメールアドレスが設定されていません。', 1100, 'body=[' . $mailBody . ']');
				} else {
					// メール内容作成
					$mailBody = '';
					if (!empty($name))			$mailBody .= 'お名前　　　　　: ' . $name . "\n";
					if (!empty($fromEmail))		$mailBody .= 'Ｅメールアドレス: ' . $fromEmail . "\n";
					if (!empty($message))		$mailBody .= 'お問い合わせ内容: ' . "\n" . $message . "\n";
					
					$mailParam = array();
					$mailParam['BODY'] = $mailBody;
					$ret = $this->gInstance->getMailManager()->sendFormMail(2/*手動送信*/, $this->gEnv->getCurrentWidgetId(), $toEmail, $fromEmail, $fromEmail, $subject, self::CONTACTUS_FORM, $mailParam, $ccEmail);
				}
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
			
			// 実行結果のメッセージを返す
			if ($ret){
				echo '送信に成功しました。';
			} else {
				echo '送信に失敗しました。';
			}
		} else {
			// 定義ID取得
			$configId = $this->gEnv->getCurrentWidgetConfigId();
			if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
			// パラメータオブジェクトを取得
			$targetObj = $this->getWidgetParamObjByConfigId($configId);
			if (!empty($targetObj)){		// 定義データが取得できたとき
				$menuId		= $targetObj->menuId;	// メニューID
				$name		= $targetObj->name;// 定義名
			
				// モーダル表示用のURLを作成
				$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
				$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
				$modalUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam . '&act=' . self::ACT_SHOW_FORM;
				
				// 値を埋め込む
				$this->tmpl->addVar('_widget', 'url',	$this->getUrl($modalUrl));			// モーダル表示用のURL
				$this->tmpl->addVar('_widget', 'post_url',	$this->getUrl($this->gEnv->getDefaultUrl() . '?' . $urlparam));// フォームデータ送信用URL
				$this->tmpl->addVar('_widget', 'image_url',	$this->getUrl($this->gEnv->getCurrentWidgetImagesUrl()));// 画像ディレクトリ
			}
		}
	}
}
?>
