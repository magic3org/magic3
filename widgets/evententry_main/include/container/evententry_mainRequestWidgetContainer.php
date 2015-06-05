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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/evententry_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/evententry_mainDb.php');

class evententry_mainRequestWidgetContainer extends evententry_mainBaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $eventObj;			// イベント情報用取得オブジェクト
	private $eventEntryId;		// イベント予約ID
	private $userExists;		// ユーザが登録済みかどうか
	private $_contentParam;		// コンテンツ変換用
	private $showEntryCount;		// 参加者数を表示するかどうか
	private $showEntryMember;		// 参加者を表示するかどうか(会員対象)
	private $layoutEntrySingle;			// コンテンツレイアウト(記事詳細)
	const EVENT_OBJ_ID = 'eventlib';		// イベント情報取得用オブジェクト
	const EYECATCH_IMAGE_SIZE = 40;		// アイキャッチ画像サイズ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new evententry_mainDb();
		
		// イベント情報オブジェクト取得
		$this->eventObj = $this->gInstance->getObject(self::EVENT_OBJ_ID);
	}
	/**
	 * ウィジェット初期化
	 *
	 * 共通パラメータの初期化や、以下のパターンでウィジェット出力方法の変更を行う。
	 * ・組み込みの_setTemplate(),_assign()を使用
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string $task					処理タスク
	 * @return 								なし
	 */
	function _init($request, $task)
	{
		// ##### ウィジェットの表示制御 #####
		// イベントIDがない場合は非表示にする
		$eventId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID);
		if (empty($eventId)){
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// イベントが非公開の場合は表示しない
		$ret = $this->db->getEventEntryByEventId($this->_langId, $eventId, ''/*予約タイプ*/, $this->entryRow);
		if ($ret){
			// イベントの表示状態を取得
			$visible =$this->eventObj->isEntryVisible($this->entryRow);
			if (!$visible){
				$this->cancelParse();		// テンプレート変換処理中断
				return;
			}
		} else {			// 受付イベントがない場合
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// イベント予約情報が非公開の場合は表示しない
		$this->entryStatus = $this->entryRow['et_status'];			// 予約情報の状態
		if ($this->entryStatus < 2){				// 	未設定(0),非公開(1)のときは非表示。受付中(2),受付停止(3),受付終了(4)のとき表示。
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// DB定義値取得
		$this->showEntryCount = self::$_configArray[evententry_mainCommonDef::CF_SHOW_ENTRY_COUNT];		// 参加者数を表示するかどうか
		$this->showEntryMember = self::$_configArray[evententry_mainCommonDef::CF_SHOW_ENTRY_MEMBER];		// 参加者を表示するかどうか(会員対象)
		$this->layoutEntrySingle = self::$_configArray[evententry_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE];			// コンテンツレイアウト(記事詳細)
		if (empty($this->layoutEntrySingle)) $this->layoutEntrySingle = evententry_mainCommonDef::DEFAULT_LAYOUT_ENTRY_SINGLE;
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
		return 'request.tmpl.html';
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
		// 入力値取得
		$act		= $request->trimValueOf('act');
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		$eventId	= $request->trimValueOf('eventid');		// イベントID
		$entryType	= $request->trimValueOf('entrytype');	// イベント予約タイプ
		
		if ($act == 'regist'){		// 登録の場合
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// イベントID、予約タイプからイベント予約IDを取得
				$ret = $this->db->getEventEntryByEventId($this->_langId, $eventId, $entryType, $row);
				if ($ret) $eventEntryId	= $row['et_id'];			// イベント予約ID
				
				// ##### 入力エラーチェック #####
				if (empty($eventEntryId)) $this->setAppErrorMsg('イベント予約情報が見つかりません');
				
				// ユーザが登録済みかどうか確認
				$userExists = $this->db->isExistsEntryUser($eventEntryId, $this->_userId);
				if ($userExists) $this->setUserErrorMsg('登録済みです');

				// 入力エラーがない場合は登録
				if ($this->getMsgCount() == 0){
					// イベント予約登録
					$codeFormat = evententry_mainCommonDef::generateEntryCode($eventId, $entryType);
					$ret = $this->db->addEventEntryRequest($eventEntryId, $this->_userId, $codeFormat, $newSerial);

					if ($ret){
						$this->setGuidanceMsg('登録完了しました');
					} else {
						$this->setUserErrorMsg('登録に失敗しました');
					}
				} else {
					// ハッシュキー作成
					$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
					$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
				}
			} else {		// ハッシュキーが異常のとき
				$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
			}
		} else if ($act == 'cancel'){		// 登録キャンセルの場合
		} else {
			// ハッシュキー作成
			$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
			$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
		}
		
		// イベント予約画面作成
		$this->createSingle($request);
		
		// 画面確認用のハッシュを設定
		$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面確認用
	}
	/**
	 * イベント予約画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createSingle($request)
	{
		// ##### DB定義値取得 #####
		$msgEntryExceedMax		= self::$_configArray[evententry_mainCommonDef::CF_MSG_ENTRY_EXCEED_MAX];		// 予約定員オーバーメッセージ
		$msgEntryOutOfTerm		= self::$_configArray[evententry_mainCommonDef::CF_MSG_ENTRY_OUT_OF_TERM];		// 受付期間外メッセージ
		$msgEntryTermExpired	= self::$_configArray[evententry_mainCommonDef::CF_MSG_ENTRY_TERM_EXPIRED];	// 受付期間終了メッセージ
		$msgEntryStopped		= self::$_configArray[evententry_mainCommonDef::CF_MSG_ENTRY_STOPPED];			// 受付中断メッセージ
		$msgEntryClosed			= self::$_configArray[evententry_mainCommonDef::CF_MSG_ENTRY_CLOSED];			// 受付終了メッセージ
		$msgEventClosed			= self::$_configArray[evententry_mainCommonDef::CF_MSG_EVENT_CLOSED];			// イベント終了メッセージ
		$msgEntryUserRegistered	= self::$_configArray[evententry_mainCommonDef::CF_MSG_ENTRY_USER_REGISTERED];	// 予約済みメッセージ
		
		// イベント情報
		$entryId	= $this->entryRow['ee_id'];			// 記事ID
		$title		= $this->entryRow['ee_name'];		// タイトル
		$summary	= $this->entryRow['ee_summary'];	// 要約
		$url		= $this->entryRow['ee_url'];		// URL
		$isAllDay	= $this->entryRow['ee_is_all_day'];	// 終日イベントかどうか
		// イベント予約情報
		$eventEntryId	= $this->entryRow['et_id'];			// 予約ID
		$entryHtml		= $this->entryRow['et_html'];		// 説明

		// ユーザが登録済みかどうか確認
		$this->userExists = $this->db->isExistsEntryUser($eventEntryId, $this->_userId);
		
		// 登録数取得
		$userCount = $this->db->getEntryUserCount($eventEntryId);
		
		// ##### 出力メッセージを設定 #####
		// イベントの終了状況をチェック
		if ($this->entryRow['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// // 期間終了がないとき
			$endDt = $this->getNextDay($this->entryRow['ee_start_dt']);
		} else {
			if ($isAllDay){		// 終日イベントのときは時間を表示しない
				$endDt = $this->getNextDay($this->entryRow['ee_end_dt']);
			} else {
				$endDt = $this->entryRow['ee_end_dt'];
			}
		}
		if (strtotime($this->_now) >= strtotime($endDt)){			// イベント終了かどうか
			// イベント終了はメッセージを単独で表示
			$this->setUserErrorMsg($msgEventClosed);		// イベント終了メッセージ
		} else {
			// 受付状態をチェック
			if ($this->entryRow['et_status'] == 3){			// 受付停止(3)
				$this->setUserErrorMsg($msgEntryStopped);		// 受付中断メッセージ
			} else if ($this->entryRow['et_status'] == 4){			// 受付終了(4)
				$this->setUserErrorMsg($msgEntryClosed);		// 受付終了メッセージ
			} else if ($this->entryRow['et_start_dt'] != $this->gEnv->getInitValueOfTimestamp() && strtotime($this->_now) < strtotime($this->entryRow['et_start_dt'])){		// 受付開始前のとき
				$this->setUserErrorMsg($msgEntryOutOfTerm);		// 受付期間外メッセージ
			} else if (strtotime($this->_now) >= strtotime($this->entryRow['ee_start_dt']) ||			// イベントが開始されているとき、または、受付終了日時以降のとき
					($this->entryRow['et_end_dt'] != $this->gEnv->getInitValueOfTimestamp() && strtotime($this->entryRow['et_end_dt']) < strtotime($this->_now))){
				$this->setUserErrorMsg($msgEntryTermExpired);		// 受付期間終了メッセージ
			} else if (!empty($this->entryRow['et_max_entry']) && $userCount >= $this->entryRow['et_max_entry']){
				$this->setUserErrorMsg($msgEntryExceedMax);		// 予約定員オーバーメッセージ
			}
			// ユーザの登録状況はワーニングメッセージに関わらず表示
			if ($this->userExists) $this->setGuidanceMsg($msgEntryUserRegistered);		// 予約済みメッセージ
		}
		
		// ##### 表示コンテンツ作成 #####
		// 記事へのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $entryId, true/*リンク用*/);
		
		// タイトル作成
		$titleTag = '<h' . $this->itemTagLevel . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '">' . $this->convertToDispString($title) . '</a></h' . $this->itemTagLevel . '>';
				
		// アイキャッチ画像
		$iconUrl = $this->eventObj->getEyecatchImageUrl($this->entryRow['ee_thumb_filename'], 's'/*sサイズ画像*/);
		if (empty($this->entryRow['ee_thumb_filename'])){
			$iconTitle = 'アイキャッチ画像未設定';
		} else {
			$iconTitle = 'アイキャッチ画像';
		}
	//	$imageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		$imageTag = '<img src="' . $this->getUrl($iconUrl) . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// 変換データ作成
		$quotaStr = intval($this->entryRow['et_max_entry']) == 0 ? '定員なし' : $this->entryRow['et_max_entry'] . '名';		// 定員
		// 参加者数を表示する場合は表示文字列を作成
		$entryCountStr = ($this->showEntryCount && $this->entryRow['et_show_entry_count']) ? $userCount . '名' : '';// 参加数
		
		// イベント開催期間
		$dateHtml = '';
		if ($this->entryRow['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// 開催開始日時のみ表示のとき
			if ($isAllDay){		// 終日イベントのとき
				$dateHtml = $this->convertToDispDate($this->entryRow['ee_start_dt']);
			} else {
				$dateHtml = $this->convertToDispDateTime($this->entryRow['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
			}
		} else {
			if ($isAllDay){		// 終日イベントのとき
				$dateHtml = $this->convertToDispDate($this->entryRow['ee_start_dt']) . evententry_mainCommonDef::DATE_RANGE_DELIMITER;
				$dateHtml .= $this->convertToDispDate($this->entryRow['ee_end_dt']);
			} else {
				$dateHtml = $this->convertToDispDateTime($this->entryRow['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/) . evententry_mainCommonDef::DATE_RANGE_DELIMITER;
				$dateHtml .= $this->convertToDispDateTime($this->entryRow['ee_end_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
			}
		}
		
		// コンテンツレイアウトのプレマクロ変換(ブロック型マクロを変換してコンテンツマクロのみ残す)
		$contentParam = array(	
								// M3_TAG_MACRO_TITLE	=> $titleTag,
								M3_TAG_MACRO_IMAGE	=> $imageTag,
								M3_TAG_MACRO_DATE	=> $dateHtml,		// 開催期間
								M3_TAG_MACRO_BODY	=> $entryHtml		// 説明
							);
		$entryHtml = $this->createMacroContent($this->layoutEntrySingle, $contentParam);
		
		// Magic3マクロ変換
		// あらかじめ「CT_」タグをすべて取得する?
		$contentInfo = array();
		$contentInfo[M3_TAG_MACRO_CONTENT_ID]		= $entryId;			// コンテンツ置換キー(エントリーID)
		$contentInfo[M3_TAG_MACRO_CONTENT_URL]		= $linkUrl;			// コンテンツ置換キー(エントリーURL)
		$contentInfo[M3_TAG_MACRO_CONTENT_TITLE]	= $title;			// コンテンツ置換キー(タイトル)
		$contentInfo[M3_TAG_MACRO_CONTENT_SUMMARY]	= $summary;			// コンテンツ置換キー(要約)
		$contentInfo[M3_TAG_MACRO_CONTENT_PLACE]	= $this->getCurrentLangString($this->entryRow['ee_place']);// 開催場所
		$contentInfo[M3_TAG_MACRO_CONTENT_CONTACT]	= $this->getCurrentLangString($this->entryRow['ee_contact']);		// 連絡先
		// イベント予約情報
		$contentInfo[M3_TAG_MACRO_CONTENT_QUOTA]		= $quotaStr;			// コンテンツ置換キー(定員)
		$contentInfo[M3_TAG_MACRO_CONTENT_ENTRY_COUNT]	= $entryCountStr;		// コンテンツ置換キー(登録数)
		// 会員情報
		$contentInfo[M3_TAG_MACRO_CONTENT_MEMBER_NAME]	= $this->gEnv->getCurrentUserName();		// コンテンツ置換キー(会員名)
		
		$entryHtml = $this->convertM3ToHtml($entryHtml, true/*改行コーをbrタグに変換*/, $contentInfo);		// コンテンツマクロ変換
		
		// メイン領域を出力
		$this->tmpl->addVar("_widget", "entry", $entryHtml);
		
		// タイトル領域を出力
		if (!empty($title)){
			$this->startTitleTagLevel = 2;
			$this->tmpl->setAttribute('show_title', 'visibility', 'visible');		// 年月表示
			$this->tmpl->addVar("show_title", "title", '<h' . $this->startTitleTagLevel . '>' . $this->convertToDispString($title) . '</h' . $this->startTitleTagLevel . '>');
		}
		
		// フォーム用非表示パラメータ
		$entryType = '';				// イベント予約タイプ
		$this->tmpl->addVar("_widget", "event_id", $this->convertToDispString($entryId));
		$this->tmpl->addVar("_widget", "entry_type", $this->convertToDispString($entryType));
	}
	/**
	 * コンテンツのプレマクロ変換
	 *
	 * @param string $layout		レイアウト
	 * @param array	$contentParam	コンテンツ作成用パラメータ
	 * @return string				作成コンテンツ
	 */
	function createMacroContent($layout, $contentParam)
	{
		$this->_contentParam = $contentParam;
		$dest = preg_replace_callback(M3_PATTERN_TAG_MACRO, array($this, '_replace_macro_callback'), $layout);
		return $dest;
	}
	/**
	 * コンテンツマクロ変換コールバック関数
	 * 変換される文字列はHTMLタグではないテキストで、変換後のテキストはHTMLタグ(改行)を含むか、HTMLエスケープしたテキスト
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_macro_callback($matchData)
	{
		$destTag	= $matchData[0];		// マッチした文字列全体
		$typeTag	= $matchData[1];		// マクロキー
		$options	= $matchData[2];		// マクロオプション

		switch ($typeTag){
		case M3_TAG_MACRO_IMAGE:		// サムネール
		case M3_TAG_MACRO_DATE:		// 開催期間
		case M3_TAG_MACRO_BODY:		// 説明
			$destTag = $this->_contentParam[$typeTag];
			break;
		case M3_TAG_MACRO_BUTTON:		// ボタン
			// メッセージを出力する場合はボタンを非表示にする
			if ($this->getMsgCount() > 0){		// メッセージを出力する場合
				$destTag = '';
				break;
			}
			
			// コンテンツマクロオプションを解析
			$optionParams = $this->gInstance->getTextConvManager()->parseMacroOption($options);

			// コンテンツマクロオプション処理
			$keys = array_keys($optionParams);
			for ($i = 0; $i < count($keys); $i++){
				$optionKey = $keys[$i];
				$optionValue = $optionParams[$optionKey];

				switch ($optionKey){
				case 'type':		// ボタンタイプ
					$type = $optionValue;
					break;
				case 'title':		// ボタンタイトル
					$title = $optionValue;
					break;
				}
			}

			// タイトル解析
			list($title, $title2) = explode('|', $title);
				
			switch ($type){
			case 'ok':			// OKボタンのとき
				if ($this->userExists){			// 登録済みの場合
					$destTag = '<a class="button" href="#" onclick="return false;" style="pointer-events:none;">' . $this->convertToDispString($title2) . '</a>';
				} else {
					$destTag = '<a class="button" href="#" onclick="regist();">' . $this->convertToDispString($title) . '</a>';
				}
				break;
			case 'cancel':		// キャンセルボタンのとき
				$destTag = '<a class="button" href="#" onclick="cancel();">' . $this->convertToDispString($title) . '</a>';
				break;
			}
			break;		
		}
		return $destTag;
	}
}
?>
