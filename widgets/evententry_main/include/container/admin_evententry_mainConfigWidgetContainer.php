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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('evententry_main') . '/admin_evententry_mainBaseWidgetContainer.php');

class admin_evententry_mainConfigWidgetContainer extends admin_evententry_mainBaseWidgetContainer
{
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
		return 'admin_config.tmpl.html';
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
		$showEntryCount 	= $request->trimCheckedValueOf('item_show_entry_count');		// 参加者数を表示するかどうか
		$showEntryMember 	= $request->trimCheckedValueOf('item_show_entry_member');		// 参加者を表示するかどうか(会員対象)
		$layoutEntrySingle	= $request->valueOf('item_layout_entry_single');				// イベント予約レイアウト(記事詳細)
		$msgEntryExceedMax		= $request->valueOf('item_msg_entry_exceed_max');		// 予約定員オーバーメッセージ
		$msgEntryTermExpired	= $request->valueOf('item_msg_entry_term_expired');	// 受付期間終了メッセージ
		$msgEntryStopped		= $request->valueOf('item_msg_entry_stopped');	// 受付中断メッセージ
		$msgEntryClosed			= $request->valueOf('item_msg_entry_closed');	// 受付終了メッセージ
		$msgEventClosed			= $request->valueOf('item_msg_event_closed');	// イベント終了メッセージ
		$msgEntryUserRegistered	= $request->valueOf('item_msg_entry_user_registered');	// 予約済みメッセージ
		
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				if (empty($layoutEntrySingle)) $layoutEntrySingle = evententry_mainCommonDef::DEFAULT_LAYOUT_ENTRY_SINGLE;	// イベント予約レイアウト(記事詳細)
				
				$ret = true;
				if ($ret) $ret = self::$_mainDb->updateConfig(evententry_mainCommonDef::CF_SHOW_ENTRY_COUNT, $showEntryCount);			// 参加者数を表示するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(evententry_mainCommonDef::CF_SHOW_ENTRY_MEMBER, $showEntryMember);		// 参加者を表示するかどうか(会員対象)
				if ($ret) $ret = self::$_mainDb->updateConfig(evententry_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE, $layoutEntrySingle);	// イベント予約レイアウト(記事詳細)
				if ($ret) $ret = self::$_mainDb->updateConfig(evententry_mainCommonDef::CF_MSG_ENTRY_EXCEED_MAX,		$msgEntryExceedMax);		// 予約定員オーバーメッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(evententry_mainCommonDef::CF_MSG_ENTRY_TERM_EXPIRED,		$msgEntryTermExpired);		// 受付期間終了メッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(evententry_mainCommonDef::CF_MSG_ENTRY_STOPPED,			$msgEntryStopped);			// 受付中断メッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(evententry_mainCommonDef::CF_MSG_ENTRY_CLOSED,			$msgEntryClosed);			// 受付終了メッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(evententry_mainCommonDef::CF_MSG_EVENT_CLOSED,			$msgEventClosed);			// イベント終了メッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(evententry_mainCommonDef::CF_MSG_ENTRY_USER_REGISTERED, 	$msgEntryUserRegistered);	// 予約済みメッセージ
	
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$reloadData = true;		// データの再ロード
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {		// 初期表示の場合
			$reloadData = true;		// データの再ロード
		}
		// データ再取得
		if ($reloadData){
			$showEntryCount		= self::$_mainDb->getConfig(evententry_mainCommonDef::CF_SHOW_ENTRY_COUNT);			// 参加者数を表示するかどうか
			$showEntryMember	= self::$_mainDb->getConfig(evententry_mainCommonDef::CF_SHOW_ENTRY_MEMBER);		// 参加者を表示するかどうか(会員対象)
			$layoutEntrySingle	= self::$_mainDb->getConfig(evententry_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE);		// イベント予約レイアウト(記事詳細)
			$msgEntryExceedMax		= self::$_mainDb->getConfig(evententry_mainCommonDef::CF_MSG_ENTRY_EXCEED_MAX);			// 予約定員オーバーメッセージ
			$msgEntryTermExpired	= self::$_mainDb->getConfig(evententry_mainCommonDef::CF_MSG_ENTRY_TERM_EXPIRED);		// 受付期間終了メッセージ
			$msgEntryStopped		= self::$_mainDb->getConfig(evententry_mainCommonDef::CF_MSG_ENTRY_STOPPED);			// 受付中断メッセージ
			$msgEntryClosed			= self::$_mainDb->getConfig(evententry_mainCommonDef::CF_MSG_ENTRY_CLOSED);				// 受付終了メッセージ
			$msgEventClosed			= self::$_mainDb->getConfig(evententry_mainCommonDef::CF_MSG_EVENT_CLOSED);				// イベント終了メッセージ
			$msgEntryUserRegistered	= self::$_mainDb->getConfig(evententry_mainCommonDef::CF_MSG_ENTRY_USER_REGISTERED);	// 予約済みメッセージ
		}
		
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "show_entry_count_checked", $this->convertToCheckedString($showEntryCount));		// 参加者数を表示するかどうか
		$this->tmpl->addVar("_widget", "show_entry_member_checked", $this->convertToCheckedString($showEntryMember));		// 参加者を表示するかどうか(会員対象)
		$this->tmpl->addVar("_widget", "layout_entry_single",		$layoutEntrySingle);		// 参加者を表示するかどうか(会員対象)
		$this->tmpl->addVar("_widget", "msg_entry_exceed_max",		$msgEntryExceedMax);		// 予約定員オーバーメッセージ
		$this->tmpl->addVar("_widget", "msg_entry_term_expired",	$msgEntryTermExpired);		// 受付期間終了メッセージ
		$this->tmpl->addVar("_widget", "msg_entry_stopped",			$msgEntryStopped);			// 受付中断メッセージ
		$this->tmpl->addVar("_widget", "msg_entry_closed",			$msgEntryClosed);			// 受付終了メッセージ
		$this->tmpl->addVar("_widget", "msg_event_closed",			$msgEventClosed);			// イベント終了メッセージ
		$this->tmpl->addVar("_widget", "msg_entry_user_registered", $msgEntryUserRegistered);	// 予約済みメッセージ
	}
}
?>
