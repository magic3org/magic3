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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_event_mainBaseWidgetContainer.php');

class admin_event_mainConfigWidgetContainer extends admin_event_mainBaseWidgetContainer
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
		$defaultLang	= $this->gEnv->getDefaultLanguage();
		
		$act = $request->trimValueOf('act');
		$entryViewCount = $request->trimValueOf('entry_view_count');		// 記事表示数
		$entryViewOrder = $request->trimValueOf('entry_view_order');		// 記事表示順
		$categoryCount = $request->trimValueOf('category_count');		// カテゴリ数
		$useCalendar	= $request->trimCheckedValueOf('item_use_calendar');	// カレンダーを使用するかどうか
		$receiveComment = $request->trimCheckedValueOf('receive_comment');		// コメントを受け付けるかどうか
		$topContents = $request->valueOf('top_contents');	// トップコンテンツ
		$maxCommentLength = $request->trimValueOf('max_comment_length');	// コメント最大文字数
		$msgNoEntryInFuture = $request->trimValueOf('item_msg_no_entry_in_future');	// 予定イベントなし時メッセージ
		
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($entryViewCount, '記事表示順');
			$this->checkNumeric($maxCommentLength, 'コメント最大文字数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_ENTRY_VIEW_COUNT, $entryViewCount); // 記事表示数
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_ENTRY_VIEW_ORDER, $entryViewOrder);// 記事表示順
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_CATEGORY_COUNT, $categoryCount);		// カテゴリ数
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_USE_CALENDAR, $useCalendar);		// カレンダーを使用するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_RECEIVE_COMMENT, $receiveComment);// コメントを受け付けるかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_MAX_COMMENT_LENGTH, $maxCommentLength);// コメント最大文字数
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_TOP_CONTENTS, $topContents);// トップコンテンツ
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_MSG_NO_ENTRY_IN_FUTURE, $msgNoEntryInFuture);	// 予定イベントなし時メッセージ

				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$reloadData = true;		// データの再ロード
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
/*				// 値を再取得
				$entryViewCount	= self::$_mainDb->getConfig(event_mainCommonDef::CF_ENTRY_VIEW_COUNT);// 記事表示数
				$entryViewOrder	= self::$_mainDb->getConfig(event_mainCommonDef::CF_ENTRY_VIEW_ORDER);// 記事表示順
				$categoryCount	= self::$_mainDb->getConfig(event_mainCommonDef::CF_CATEGORY_COUNT);// カテゴリ数
				$receiveComment	= self::$_mainDb->getConfig(event_mainCommonDef::CF_RECEIVE_COMMENT);
				$maxCommentLength = self::$_mainDb->getConfig(event_mainCommonDef::CF_MAX_COMMENT_LENGTH);	// コメント最大文字数
				$topContents = self::$_mainDb->getConfig(event_mainCommonDef::CF_TOP_CONTENTS);// トップコンテンツ
				$msgNoEntryInFuture = self::$_mainDb->getConfig(event_mainCommonDef::CF_MSG_NO_ENTRY_IN_FUTURE);	// 予定イベントなし時メッセージ
				*/
			}
		} else {		// 初期表示の場合
			$reloadData = true;		// データの再ロード
		}
		// データ再取得
		if ($reloadData){
			$entryViewCount	= self::$_mainDb->getConfig(event_mainCommonDef::CF_ENTRY_VIEW_COUNT);// 記事表示数
			if (empty($entryViewCount)) $entryViewCount = event_mainCommonDef::DEFAULT_VIEW_COUNT;
			$entryViewOrder	= self::$_mainDb->getConfig(event_mainCommonDef::CF_ENTRY_VIEW_ORDER);// 記事表示順
			$categoryCount	= self::$_mainDb->getConfig(event_mainCommonDef::CF_CATEGORY_COUNT);// カテゴリ数
			if (empty($categoryCount)) $categoryCount = event_mainCommonDef::DEFAULT_CATEGORY_COUNT;
			$useCalendar	= self::$_mainDb->getConfig(event_mainCommonDef::CF_USE_CALENDAR);	// カレンダーを使用するかどうか
			$receiveComment	= self::$_mainDb->getConfig(event_mainCommonDef::CF_RECEIVE_COMMENT);
			$maxCommentLength = self::$_mainDb->getConfig(event_mainCommonDef::CF_MAX_COMMENT_LENGTH);	// コメント最大文字数
			if ($maxCommentLength == '') $maxCommentLength = event_mainCommonDef::DEFAULT_COMMENT_LENGTH;
			$topContents = self::$_mainDb->getConfig(event_mainCommonDef::CF_TOP_CONTENTS);// トップコンテンツ
			$msgNoEntryInFuture = self::$_mainDb->getConfig(event_mainCommonDef::CF_MSG_NO_ENTRY_IN_FUTURE);	// 予定イベントなし時メッセージ
			if (empty($msgNoEntryInFuture)) $msgNoEntryInFuture = event_mainCommonDef::DEFAULT_MSG_NO_ENTRY_IN_FUTURE;
		}
		
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "view_count", $entryViewCount);// 記事表示数
		if (empty($entryViewOrder)){	// 順方向
			$this->tmpl->addVar("_widget", "view_order_inc_selected", 'selected');// 記事表示順
		} else {
			$this->tmpl->addVar("_widget", "view_order_dec_selected", 'selected');// 記事表示順
		}
		$this->tmpl->addVar("_widget", "category_count", $categoryCount);// カテゴリ数
		$this->tmpl->addVar("_widget", "use_calendar", $this->convertToCheckedString($useCalendar));// カレンダーを使用するかどうか
		$this->tmpl->addVar("_widget", "receive_comment", $this->convertToCheckedString($receiveComment));// コメントを受け付けるかどうか
		$this->tmpl->addVar("_widget", "max_comment_length", $this->convertToDispString($maxCommentLength));// コメント最大文字数
		$this->tmpl->addVar("_widget", "top_contents", $this->convertToDispString($topContents));		// トップコンテンツ
		$this->tmpl->addVar("_widget", "msg_no_entry_in_future", $this->convertToDispString($msgNoEntryInFuture));		// 予定イベントなし時メッセージ
	}
}
?>
