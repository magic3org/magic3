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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainMainteBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainLandingpageWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialArray = array();		// 表示されている項目シリアル番号

	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 20;			// リンクページ数
	const DEFAULT_USER_NAME_SUFFIX = 'のページ運営者';	// ページ運営者ユーザ名
	const USER_TYPE_OPTION = ';page_manager;';		// ランディングページ管理者用のユーザタイプオプション(ページ運営者)
	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive32.png';		// 非公開アイコン
	const ICON_SIZE = 32;		// アイコンのサイズ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
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
		if ($task == 'landingpage_detail'){		// 詳細画面
			return 'landingpage_detail.tmpl.html';
		} else {			// 一覧画面
			return 'landingpage.tmpl.html';
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
		if ($task == 'landingpage_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// パラメータの取得
		$task = $request->trimValueOf('task');
		$act = $request->trimValueOf('act');
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号

		if ($act == 'delete'){		// メニュー項目の削除
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue) $delItems[] = $listedItem[$i];		// チェック項目
			}
			if (count($delItems) > 0){
				$ret = $this->_deleteLandingPage($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		// コンテンツ総数を取得
		$totalCount = $this->db->getLandingPageListCount();
		
		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, self::DEFAULT_LIST_COUNT);

		// ページングリンク作成
		$currentBaseUrl = '';		// POST用のリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $currentBaseUrl, 'selpage($1);return false;');
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		
		$this->db->getLandingPageList(self::DEFAULT_LIST_COUNT, $pageNo, array($this, 'itemListLoop'));
		if (count($this->serialArray) == 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// コンテンツ項目がないときは、一覧を表示しない
		
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
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
		$serialNo = $request->trimValueOf('serial');		// シリアル番号

		$newId = $request->trimValueOf('item_id');		// 新規ランディングページID
		$name = $request->trimValueOf('item_name');		// ランディングページ名
		$password = $request->trimValueOf('password');	// ページ運用者用初期パスワード
		$visible = $request->trimCheckedValueOf('item_visible');		// 公開制御

		$reloadData = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkSingleByte($newId, 'ランディングページID', false/*空白不可*/, 1/*英小文字のみ*/, true/*英字数値のみ*/);
			$this->checkInput($name, '名前');
			$this->checkInput($password, '初期パスワード');
			
			// ランディングページ名でユーザが登録されていないかチェック
			if ($this->getMsgCount() == 0){
				if ($this->_db->isExistsAccount($newId) ||
					$this->db->isExistsLandingPage($newId)){
					$this->setMsg(self::MSG_USER_ERR, 'すでに登録済みのランディングページIDです');
				}
			}
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ownerName = $newId . self::DEFAULT_USER_NAME_SUFFIX;
				
				// ランディングページのページ運用ユーザを追加
				$ret = $this->_db->addNewLoginUser($ownerName, $newId, $password, UserInfo::USER_TYPE_MANAGER/*システム運用者*/, 1/*ログイン可能*/, null/*有効期間開始*/, null/*有効期間終了*/, $newSerial, 
															''/*制限ウィジェットなし*/, self::USER_TYPE_OPTION/*ページ運営者*/);
				if ($ret){
					// ユーザ情報取得
					$ret = $this->_db->getLoginUserRecordBySerial($newSerial, $row);
					if ($ret){
						$ownerId = $row['lu_id'];			// ランディングページ所有者ID
						$ownerAccount = $row['lu_account'];		// 所有者アカウント
						$ownerName = $row['lu_name'];
					
						// 運用ログ出力
						$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザ情報を追加しました。アカウント: ' . $ownerAccount, 2100, 'userid=' . $ownerId . ', username=' . $ownerName);
						
						// ランディングページ情報を新規追加
						$ret= $this->db->updateLandingPage(0/*新規*/, $newId, $name, $visible, $ownerId, $newSerialNo);
					}
				}
																		
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					$serialNo = $newSerialNo;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 更新のとき
			// 入力チェック
			$this->checkInput($name, '名前');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// ランディングページ情報更新
				$ret= $this->db->updateLandingPage($serialNo, ''/*未使用*/, $name, $visible, 0/*未使用*/, $newSerialNo);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					$serialNo = $newSerialNo;
					$reloadData = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			// ランディングページ削除
			$ret = $this->_deleteLandingPage(array($serialNo));

			if ($ret){		// データ削除成功のとき
				$this->setMsg(self::MSG_GUIDANCE, 'データを削除しました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'データ削除に失敗しました');
			}
		} else {		// 初期状態
			$reloadData = true;			// データを再取得
			$visible = 1;			// 公開制御
		}
		// 表示データ再取得
		if ($reloadData){
			$ret = $this->db->getLandingPageBySerial($serialNo, $row);
			if ($ret){
				$id = $row['lp_id'];			// ランディングページID
				$name = $row['lp_name'];
				$visible = $row['lp_visible'];
				$date = $row['lp_regist_dt'];		// 作成日時
				$ownerId = $row['lp_owner_id'];			// ランディングページ所有者ID
				$ownerAccount = $row['lu_account'];		// 所有者アカウント
			}
		}
		
		if (empty($serialNo)){		// 新規追加のとき
			$this->tmpl->setAttribute('show_id_input', 'visibility', 'visible');// ランディングページID入力領域表示
			$this->tmpl->setAttribute('show_account_input', 'visibility', 'visible');		// 初期パスワード入力領域表示
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 追加ボタン表示
			
			$this->tmpl->addVar("show_id_input", "id", $this->convertToDispString($newId));			// ランディングページID
		} else {
			$this->tmpl->setAttribute('show_account', 'visibility', 'visible');		// アカウント情報領域表示
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
			
			$this->tmpl->addVar("_widget", "id", $this->convertToDispString($id));		// ランディングページID
			$this->tmpl->addVar("show_account", "account", $this->convertToDispString($ownerAccount));			// 所有者アカウント
			
			// ランディングページURL
			$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . M3_PAGE_SUB_ID_PREFIX_LANDING_PAGE . $id;
			$this->tmpl->addVar("_widget", "url", $this->convertToDispString($url));		// ランディングページURL
			
			// ユーザ情報へのリンク
			$userDetailUrl	= '?task=userlist_detail&' . M3_REQUEST_PARAM_USER_ID . '=' . $ownerId;		// ユーザ詳細画面URL
			$buttonTag = $this->gDesign->createEditButton($userDetailUrl, 'ユーザ情報を編集');
			$this->tmpl->addVar("show_account", "user_detail_button", $buttonTag);
		}
		
		$this->tmpl->addVar("_widget", "serial", $this->convertToDispString($serialNo));		// シリアル番号
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// ページ名
		$this->tmpl->addVar("_widget", "visible", $this->convertToCheckedString($visible));		// 公開制御
		$this->tmpl->addVar("_widget", "date", $this->convertToDispDateTime($date, 0/*ロングフォーマット*/, 10/*時分*/));		// 作成日時
	}
	/**
	 * ランディングページIDをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		if ($fetchedRow['lp_visible']){		// ランディングページが公開状態のとき
			$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;			// 公開中アイコン
			$iconTitle = '公開中';
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::INACTIVE_ICON_FILE;		// 非公開アイコン
			$iconTitle = '非公開';
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		$row = array(
			'index'		=> $index,			// インデックス番号
			'serial'	=> $this->convertToDispString($fetchedRow['lp_serial']),			// シリアル番号
			'id'		=> $this->convertToDispString($fetchedRow['lp_id']),			// ランディングページID
			'name'		=> $this->convertToDispString($fetchedRow['lp_name']),			// ランディングページID名
			'status'	=> $statusImg,												// 公開状況
			'date'		=> $this->convertToDispDateTime($fetchedRow['lp_regist_dt'], 0/*ロングフォーマット*/, 10/*時分*/)		// 作成日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のページサブIDを保存
		$this->serialArray[] = $fetchedRow['lp_serial'];
		return true;
	}
	/**
	 * ランディングページ削除
	 *
	 * @param array $serialArray	削除するランディングページのシリアル番号
	 * @param bool					true=成功、false=失敗
	 */
	function _deleteLandingPage($serialArray)
	{
		$retStatus = true;		// 戻りステータス
		
		for ($i = 0; $i < count($serialArray); $i++){
			$serialNo = $serialArray[$i];
			
			// ランディングページ情報取得
			$ret = $this->db->getLandingPageBySerial($serialNo, $row);
			if ($ret){
				$userSerial = $row['lu_serial'];		// 所有者のユーザ情報のシリアル番号
			
				// ユーザ情報取得
				$ret = $this->_db->getLoginUserRecordBySerial($userSerial, $row);
				if ($ret){
					$ownerId = $row['lu_id'];			// ランディングページ所有者ID
					$ownerAccount = $row['lu_account'];		// 所有者アカウント
					$ownerName = $row['lu_name'];
				
					// ユーザ情報削除
					$ret = $this->db->delUserBySerial(array($userSerial));
					if ($ret){
						// 運用ログ出力
						$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザ情報を削除しました。アカウント: ' . $ownerAccount, 2100, 'userid=' . $ownerId . ', username=' . $ownerName);
					}
				}
				// ランディングページ情報削除
				$ret = $this->db->delLandingPage(array($serialNo));
			}
			if (!$ret) $retStatus = false;
		}
		
		return $retStatus;
	}
}
?>
