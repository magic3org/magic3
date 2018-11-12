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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class repl_chatWidgetContainer extends BaseWidgetContainer
{
	private $cssFilePath;				// CSSファイル
	const CHATBOT_LIB_OBJ_ID = 'chatbotlib';			// チャットボットアドオンオブジェクト
	const CHATBOT_TYPE = 'repl';						// チャットボットタイプ
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'Repl-AIチャットボット';		// デフォルトのウィジェットタイトル名
	const DEFAULT_BOT_NAME = 'サポート';			// 対話するボットのデフォルト名
	const DEFAULT_CSS_FILE = '/default.css';				// CSSファイル
	const REPLAI_INIT_URL = 'https://api.repl-ai.jp/v1/registration';			// チャット初期化用API
	const REPLAI_MESSAGE_URL = 'https://api.repl-ai.jp/v1/dialogue';		// チャットメッセージ送受信用API
	const SESSION_KEY_APP_USER_ID = 'app_user_id';				// チャットユーザID保存用セッションキー
	const DEFAULT_BOT_AVATAR = 'bot.png';		// チャットボットアバター画像
	const DEFAULT_GUEST_AVATAR = 'guest.png';	// ゲストアバター画像
	
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
		// CSSファイルの設定
//		$templateType = $gEnvManager->getCurrentTemplateType();
////		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
//			$this->cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_BOOTSTRAP_CSS_FILE);		// CSSファイル
//		} else {
			$this->cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_CSS_FILE);		// CSSファイル
//		}
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){// 定義データが取得できないときは終了
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}

		// 設定値取得
		$apiKey = $targetObj->apiKey;		// Repl-AIのAPIキー
		$botId = $targetObj->botId;		// ボットID
		$scenarioId = $targetObj->scenarioId;		// シナリオID
		$isPanelOpen = $targetObj->isPanelOpen;		// 起動時にパネルを開くかどうか
					
		$act = $request->trimValueOf('act');
		$message = $request->trimValueOf('message');
		$token = $request->trimValueOf('token');
		if ($act == 'chatinit'){	// チャット開始
			// ##### ウィジェット出力処理中断 ######
			$this->gPage->abortWidget();
        
			$headers = array(
				'Content-Type: application/json; charset=UTF-8',
				'x-api-key: ' . $apiKey				// APIキー
			);
			$data = array(
				'botId' => $botId		// ボットID
			);

			$options = array('http' => array(
				'method' => 'POST',
				'header' => implode("\r\n", $headers),
				'content' => json_encode($data)
			));

			$context = stream_context_create($options);
			$response = file_get_contents(self::REPLAI_INIT_URL, false, $context);
			$res = json_decode($response);
        
			$retMessage = '';
			if (!empty($res->appUserId)){
				// セッションにユーザIDを保存
				$this->setWidgetSession(self::SESSION_KEY_APP_USER_ID, $res->appUserId, $token);

				// 初回メッセージを取得
				$data = array(
					'appUserId'	=> $res->appUserId,
					'botId'		=> $botId,				// ボットID
					'voiceText'	=> 'init',
					'initTalkingFlag'	=> true,
					'initTopicId'		=> $scenarioId	// シナリオID
				);

				$options = array('http' => array(
					'method' => 'POST',
					'header' => implode("\r\n", $headers),
					'content' => json_encode($data)
				));

				$context = stream_context_create($options);
				$response = file_get_contents(self::REPLAI_MESSAGE_URL, false, $context);
				$res = json_decode($response);
				$retMessage = $res->systemText->expression;
				//$res->systemText->utterance		// 音声合成用テキスト
				//$res->serverSendTime		// レスポンス時刻
			}
			// フロントへ返す値を設定
			$nowTime = time();
			$this->gInstance->getAjaxManager()->addData('message', $retMessage);
			$this->gInstance->getAjaxManager()->addData('name', self::DEFAULT_BOT_NAME);	// 対話するボットの名前
			$this->gInstance->getAjaxManager()->addData('time', $nowTime);	// 応対日時
			
			// チャット会話をログに残す
			$this->gInstance->getObject(self::CHATBOT_LIB_OBJ_ID)->writeChatLog(self::CHATBOT_TYPE, '', $retMessage, $retMessage, date("Y/m/d H:i:s", $nowTime));
			return;
		} else if ($act == 'chatmsg'){	// フロントからのメッセージを受信
			// ##### ウィジェット出力処理中断 ######
			$this->gPage->abortWidget();

			// セッションからユーザIDを取得
			$appUserId = $this->getWidgetSession(self::SESSION_KEY_APP_USER_ID, ''/*未使用*/, $token);

			$retMessage = '';
			if (!empty($appUserId)){
				$headers = array(
					'Content-Type: application/json; charset=UTF-8',
					'x-api-key: ' . $apiKey				// APIキー
				);
				
				// メッセージを取得
				$data = array(
					'appUserId'	=> $appUserId,
					'botId'		=> $botId,				// ボットID
					'voiceText'	=> $message,				// Repl-AIに送信するメッセージ
					'initTalkingFlag'	=> false
//					'initTopicId'		=> $scenarioId	// シナリオID
				);
				$options = array('http' => array(
					'method' => 'POST',
					'header' => implode("\r\n", $headers),
					'content' => json_encode($data)
				));

				$context = stream_context_create($options);
				$response = file_get_contents(self::REPLAI_MESSAGE_URL, false, $context);
				$res = json_decode($response);
				$retMessage = $res->systemText->expression;
			}
			// フロントへ返す値を設定
			$nowTime = time();
			$this->gInstance->getAjaxManager()->addData('message', $retMessage);
			$this->gInstance->getAjaxManager()->addData('name', self::DEFAULT_BOT_NAME);	// 対話するボットの名前
			$this->gInstance->getAjaxManager()->addData('time', $nowTime);	// 応対日時
			
			// チャット会話をログに残す
			$this->gInstance->getObject(self::CHATBOT_LIB_OBJ_ID)->writeChatLog(self::CHATBOT_TYPE, $message, $retMessage, $retMessage, date("Y/m/d H:i:s", $nowTime));
			return;
		}
		// アバター画像URL設定
		$avatarUrl = $this->getUrl($this->gEnv->getCurrentWidgetRootUrl()) . '/images/';
		$botAvatar = $avatarUrl . self::DEFAULT_BOT_AVATAR;		// チャットボットアバター画像
		$guestAvatar = $avatarUrl . self::DEFAULT_GUEST_AVATAR;	// ゲストアバター画像
		$this->tmpl->addVar("_widget", "bot_avatar", $botAvatar);
		$this->tmpl->addVar("_widget", "guest_avatar", $guestAvatar);
	
		// パネル初期状態を設定
		if (!empty($isPanelOpen)) $this->tmpl->setAttribute("show_panel_open", "visibility", "visible");
		
		// 画面埋め込みデータ
		$this->tmpl->addVar("_widget", "token", $this->generateToken());// 画面識別用トークン
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return self::DEFAULT_TITLE;
	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		return $this->cssFilePath;
	}
}
?>
