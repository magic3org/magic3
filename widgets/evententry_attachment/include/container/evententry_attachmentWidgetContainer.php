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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/evententry_attachmentCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/evententry_attachmentDb.php');

class evententry_attachmentWidgetContainer extends BaseWidgetContainer
{
	private $db;
	private $entryStatus;			// 予約情報の状態
	const EVENT_OBJ_ID = 'eventlib';		// イベント情報取得用オブジェクト
	const DEFAULT_TITLE = 'イベント予約';			// デフォルトのウィジェットタイトル
	const DATE_FORMAT = 'Y年 n月 j日';		// 日付フォーマット
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new evententry_attachmentDb();
		
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
		// イベント情報が単体で表示されてる場合のみウィジェットを表示する
		$this->contentType = $this->gPage->getContentType();		// ページのコンテンツタイプを取得
		if ($this->contentType == M3_VIEW_TYPE_EVENT){		// イベント情報
			$eventId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID);
			if (empty($eventId)) $eventId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID_SHORT);
		}

		// イベントIDがない場合は非表示にする
		if (empty($eventId)){
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// イベントが非公開の場合は表示しない
		$ret = $this->db->getEntry($this->_langId, $eventId, ''/*予約タイプ*/, $row);
		if ($ret){
			// イベントの表示状態を取得
			$visible =$this->eventObj->isEntryVisible($row);
			if (!$visible){
				$this->cancelParse();		// テンプレート変換処理中断
				return;
			}
		} else {			// 受付イベントがない場合
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// イベント予約情報が非公開の場合は表示しない
		$this->entryStatus = $row['et_status'];			// 予約情報の状態
		if ($this->entryStatus < 2){				// 	未設定(0),非公開(1)のときは非表示。受付中(2),受付停止(3),受付終了(4)のとき表示。
			$this->cancelParse();		// テンプレート変換処理中断
			return;
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
		// 初期化
		$layout = evententry_attachmentCommonDef::DEFAULT_LAYOUT;	// イベント予約レイアウト
		
		// 保存値取得
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$layout	= $paramObj->layout;
		}
		
/*		// イベントが開始されている場合は受付終了
		$iconTitle = '';
		if (strtotime($this->_now) >= strtotime($fetchedRow['ee_start_dt'])){
			$iconTitle = '受付期間終了';
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
		} else if (($fetchedRow['et_start_dt'] != $this->gEnv->getInitValueOfTimestamp() && strtotime($this->_now) < strtotime($fetchedRow['et_start_dt'])) ||
					($fetchedRow['et_end_dt'] != $this->gEnv->getInitValueOfTimestamp() && strtotime($fetchedRow['et_end_dt']) < strtotime($this->_now))){		// 受付期間外のとき
			$iconTitle = '受付期間外';
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
		}*/
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
}
?>
