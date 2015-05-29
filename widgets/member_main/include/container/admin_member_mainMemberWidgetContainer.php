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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_member_mainBaseWidgetContainer.php');

class admin_member_mainMemberWidgetContainer extends admin_member_mainBaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $totalCount;	// 会員総数
	private $firstNo;		// 一覧の先頭の番号
	
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT	= 5;			// リンクページ数
	const ICON_SIZE = 32;		// アイコンのサイズ
	const SEARCH_ICON_FILE = '/images/system/search16.png';		// 検索用アイコン
	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive32.png';		// 非公開アイコン
	
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
		$task = $request->trimValueOf('task');
		if ($task == self::TASK_MEMBER_DETAIL){		// 詳細画面
			return 'admin_member_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_member.tmpl.html';
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
		$task = $request->trimValueOf('task');
		if ($task == self::TASK_MEMBER_DETAIL){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// 初期化
		$maxListCount = self::DEFAULT_LIST_COUNT;
		
		// 入力値取得
		$act = $request->trimValueOf('act');

		// ##### 検索条件 #####
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号

		$search_startDt = $request->trimValueOf('search_start');		// 検索範囲開始日付
		if (!empty($search_startDt)) $search_startDt = $this->convertToProperDate($search_startDt);
		$search_endDt = $request->trimValueOf('search_end');			// 検索範囲終了日付
		if (!empty($search_endDt)) $search_endDt = $this->convertToProperDate($search_endDt);
		$keyword = $request->trimValueOf('search_keyword');			// 検索キーワード

		if ($act == 'delete'){		// 項目削除の場合
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				$ret = self::$_mainDb->delNewsItem($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'search'){		// 検索のとき
			if (!empty($search_startDt) && !empty($search_endDt) && $search_startDt > $search_endDt){
				$this->setUserErrorMsg('期間の指定範囲にエラーがあります。');
			}
			$pageNo = 1;		// ページ番号初期化
		}
		
		// ###### 一覧の取得条件を作成 ######
		if (!empty($search_endDt)) $endDt = $this->getNextDay($search_endDt);
		$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);
		
		// 総数を取得
		$this->totalCount = self::$_mainDb->getMemberListCount($parsedKeywords);

		// ページング計算
		$this->calcPageLink($pageNo, $this->totalCount, $maxListCount);
		$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
		
		// ページングリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
		
		// 会員一覧を取得
		self::$_mainDb->getMemberList($maxListCount, $pageNo, $parsedKeywords, array($this, 'userListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// データがないときは、一覧を表示しない

		// ボタン作成
		$searchImg = $this->getUrl($this->gEnv->getRootUrl() . self::SEARCH_ICON_FILE);
		$searchStr = '検索';
		$this->tmpl->addVar("_widget", "search_img", $searchImg);
		$this->tmpl->addVar("_widget", "search_str", $searchStr);
		
		// 検索結果
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $this->totalCount);
		
		// 検索条件
		$this->tmpl->addVar("_widget", "search_start", $search_startDt);	// 開始日付
		$this->tmpl->addVar("_widget", "search_end", $search_endDt);	// 終了日付
		$this->tmpl->addVar("_widget", "search_keyword", $keyword);	// 検索キーワード

		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "list_count", $maxListCount);	// 一覧表示項目数
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		
		$reloadData = false;		// データの再ロード
		if ($act == 'add'){		// 会員を追加
			// 入力チェック
			$this->checkDate($date, '登録日付');
			$this->checkTime($time, '登録時間');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 入力データの修正
				$regDt = $this->convertToProperDate($date) . ' ' . $this->convertToProperTime($time);		// 登録日時
				
				// 会員を追加
//				$ret = self::$_mainDb->updateNewsItem(0/*新規*/, $contentTitle, $message, $url, $mark, $this->status, $regDt, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow($this->serialNo);
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = self::$_mainDb->delNewsItem(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'authorize'){		// ユーザを会員承認するとき
			$ret = $this->_db->getLoginUserRecordBySerial($this->serialNo, $row);
			if ($ret){
				$userId	= $row['lu_id'];		// ユーザID
				$account = $row['lu_account'];		// アカウント
				
				$ret = $this->_db->makeNormalLoginUser($userId);// 一般ログインユーザに設定
				if ($ret){
					$fromAddress = $this->getFromAddress();	// 送信元アドレス
					$toAddress = $account;					// ログインユーザに送信
				
					// メール件名、本文マクロ
					$mailParam = array();
					$mailParam['ACCOUNT'] = $account;
					$titleParam = array();
					$titleParam[M3_TAG_MACRO_SITE_NAME] = $this->gEnv->getSiteName();			// サイト名
					$titleParam[M3_TAG_MACRO_ACCOUNT]	= $account;							// ログインアカウント
					$ret = $this->gInstance->getMailManager()->sendFormMail(1/*自動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, '', '', reg_userCommonDef::MAIL_TMPL_REGIST_USER_AUTO_COMPLETED, $mailParam,
																		''/*CCアドレス*/, ''/*BCCアドレス*/, ''/*デフォルトテンプレート*/, $titleParam);
					$this->setGuidanceMsg('ユーザを承認しました');
				} else {
					$this->setAppErrorMsg('ユーザの承認に失敗しました');
				}
			}
		} else {	// 初期画面表示のとき
			$reloadData = true;		// データの再ロード
		}
		// 設定データを再取得
		$userAuthorized = false;		// ユーザが承認されているかどうか
		if ($reloadData){		// データの再ロード
			$ret = $this->_db->getLoginUserRecordBySerial($this->serialNo, $row);
			if ($ret){
				$account	= $row['lu_account'];		// アカウント
				$name		= $row['lu_name'];			// 名前
				$regDate	= $row['lu_regist_dt'];		// 登録日時

				// 登録状態
				if ($row['lu_user_type'] == UserInfo::USER_TYPE_NORMAL){		// 正会員
					$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// アクティブアイコン
					$iconTitle = '正会員';
					
					$userAuthorized = true;		// ユーザが承認されているかどうか
				} else {		// 未承認または仮登録のとき
					$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非アクティブアイコン
					$iconTitle = '仮会員';
				}
				$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" />';
			} else {
				$this->serialNo = 0;
				$account	= '';		// アカウント
				$name		= '';			// 名前
				$regDate	= '';		// 登録日時
			}
		}
		// 承認ボタンの設定
		if ($userAuthorized){		// 承認済みのとき
			$authLabel = '承認済み';
			$authButtonDisabled = 'disabled';
		} else {
			$authLabel = '承認する';
			$authButtonDisabled = '';
		}
		$this->tmpl->addVar("_widget", "auth_button_label", $this->convertToDispString($authLabel));			// 承認ボタンラベル
		$this->tmpl->addVar("_widget", "auth_button_disabled", $authButtonDisabled);			// 承認ボタン
		
		// 入力フィールドの設定
		if (empty($this->serialNo)){		// 未登録データのとき
			// データ追加ボタン表示
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
		} else {
			// データ更新、削除ボタン表示
			$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');
//			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
		}
		
		// 表示項目を埋め込む
		$this->tmpl->addVar("_widget", "account", $this->convertToDispString($account));	// アカウント
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));			// 名前
		$this->tmpl->addVar("_widget", "reg_date", $this->convertToDispDateTime($regDate));	// 登録日時
		$this->tmpl->addVar("_widget", "status_img", $statusImg);	// 登録状態
		
		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial", $this->convertToDispString($this->serialNo));	// シリアル番号
	}
	/**
	 * ユーザリスト、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function userListLoop($index, $fetchedRow, $param)
	{
		// 会員No(会員、仮会員の登録順)
		$no = $this->totalCount - $this->firstNo - $index +1;
		
		// 登録状態
		if ($fetchedRow['lu_user_type'] == UserInfo::USER_TYPE_NORMAL){		// 正会員
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// アクティブアイコン
			$iconTitle = '正会員';
		} else {		// 未承認または仮登録のとき
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非アクティブアイコン
			$iconTitle = '仮会員';
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" />';
		
		$row = array(
			'serial'		=> $this->convertToDispString($fetchedRow['lu_serial']),	// シリアル番号
			'no'			=> $this->convertToDispString($no),							// 会員No(会員、仮会員の登録順)
			'id'			=> $this->convertToDispString($fetchedRow['lu_id']),		// ID
			'name'			=> $this->convertToDispString($fetchedRow['lu_name']),		// 名前
			'account'		=> $this->convertToDispString($fetchedRow['lu_account']),	// アカウント
			'status_img'	=> $statusImg,												// 登録状態
			'date'			=> $this->convertToDispDateTime($fetchedRow['lu_regist_dt'], 0, 10/*時分表示*/)	// 登録日時
		);
		$this->tmpl->addVars('userlist', $row);
		$this->tmpl->parseTemplate('userlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['lu_serial'];
		return true;
	}
}
?>
