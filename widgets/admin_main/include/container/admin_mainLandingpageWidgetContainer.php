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
	//	const USER_OPTION = ';photo_main=author;';		// ログインユーザのユーザオプション
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
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
					
					// 削除可能かチェック
					$refCount = $this->_db->getMenuIdRefCount($listedItem[$i]);		// ランディングページID使用数
					if ($refCount > 0){		// 参照ありのときは削除できない
						$this->setMsg(self::MSG_USER_ERR, '使用中のランディングページIDは削除できません。ランディングページID=' . $listedItem[$i]);
						break;
					}
				}
			}
			if ($this->getMsgCount() == 0 && count($delItems) > 0){
				$ret = $this->db->delMenuId($delItems);
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
		
		$this->db->getLandingPageList(self::DEFAULT_LIST_COUNT, $pageNo, array($this, 'itemListLoop'));
		if (!$this->isExistsContent) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// コンテンツ項目がないときは、一覧を表示しない
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
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
		$menuId = $request->trimValueOf('serial');		// ランディングページID

		$newId = $request->trimValueOf('item_id');		// 新規ランディングページID
		$name = $request->trimValueOf('item_name');		// 名前

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkSingleByte($newId, 'ランディングページID', false/*空白不可*/, 1/*英小文字のみ*/, true/*英字数値のみ*/);
			$this->checkInput($name, '名前');
			
			// ランディングページ名でユーザが登録されていないかチェック
			if ($this->getMsgCount() == 0){
				if ($this->_db->isExistsAccount($newId) ||
					$this->db->isExistsLandingPage($newId)){
					$this->setMsg(self::MSG_USER_ERR, 'すでに登録済みのランディングページIDです');
				}
			}
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// システム管理者は常にログイン可能
				if ($this->userType == UserInfo::USER_TYPE_SYS_ADMIN) $canLogin = 1;
				
				// ユーザ種別が負のときはログイン不可
				if (intval($this->userType) < 0) $canLogin = 0;
				
				// 保存データ作成
				if (empty($start_date)){
					$startDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$startDt = $start_date . ' ' . $start_time;
				}
				if (empty($end_date)){
					$endDt = $this->gEnv->getInitValueOfTimestamp();
				} else {
					$endDt = $end_date . ' ' . $end_time;
				}
				if ($this->userType == UserInfo::USER_TYPE_SYS_ADMIN){		// システム管理者は有効期間の設定不可
					$startDt = $this->gEnv->getInitValueOfTimestamp();
					$endDt = $this->gEnv->getInitValueOfTimestamp();
				}
				
				// 追加項目
				$otherParams = array();
				$otherParams['lu_email'] = $email;		// Eメール
				$otherParams['lu_skype_account'] = $skypeAccount;		// Skypeアカウント
				
				$ret = $this->_db->addNewLoginUser($name, $account, $password, $this->userType, $canLogin, $startDt, $endDt, $newSerial, '', '', $this->userGroupArray, $otherParams);
				
				$ret = $this->_db->addNewLoginUser($name, $account, $password, UserInfo::USER_TYPE_MANAGER/*システム運用者*/, $canLogin, null/*有効期間開始*/, null/*有効期間終了*/, $newSerial, 
																		$this->gEnv->getCurrentWidgetId()/*制限ウィジェット*/, photo_mainCommonDef::USER_OPTION);
																		
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Item added.'));	// データを追加しました
					
					// 運用ログ出力
					$ret = $this->_mainDb->getUserBySerial($newSerial, $row, $groupRows);
					if ($ret) $loginUserId = $row['lu_id'];
					$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザ情報を追加しました。アカウント: ' . $account, 2100, 'userid=' . $loginUserId . ', username=' . $name);
					
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in adding item.'));	// データ追加に失敗しました
				}
			}
		} else if ($act == 'update'){		// 更新のとき
			// 入力チェック
			$this->checkSingleByte($menuId, 'ランディングページID');
			$this->checkInput($name, '名前');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// ページIDの更新
				$ret = $this->db->updateMenuId($menuId, $name, $sortOrder, $this->deviceType, $targetWidget);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			// 参照ありのときは削除できない
			$refCount = $this->_db->getMenuIdRefCount($menuId);		// ランディングページID使用数
			if ($refCount > 0) $this->setMsg(self::MSG_USER_ERR, '使用中のランディングページIDは削除できません');
			
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->db->delMenuId(array($menuId));
				if ($ret){		// データ削除成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを削除しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ削除に失敗しました');
				}
			}
		} else {		// 初期状態
			$replaceNew = true;			// データを再取得
		}
		// 表示データ再取得
		if ($replaceNew){
			$ret = $this->db->getMenuId($menuId, $row);
			if ($ret){
				$name = $row['mn_name'];
			}
		}
		
		if (empty($menuId)){		// 新規追加のとき
			$this->tmpl->setAttribute('show_id', 'visibility', 'visible');// ランディングページID入力領域表示
			$this->tmpl->setAttribute('show_account_input', 'visibility', 'visible');		// 初期パスワード入力領域表示
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 追加ボタン表示
			
			$this->tmpl->addVar("show_id", "id", $this->convertToDispString($newId));			// ランディングページID
		} else {
			$this->tmpl->setAttribute('show_account', 'visibility', 'visible');		// アカウント情報領域表示
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
			$this->tmpl->addVar("_widget", "menu_id", $menuId);			// ランディングページID
		}
		
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// ページ名
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
		$value = $this->convertToDispString($fetchedRow['mn_id']);
		
		$accessPointName = str_replace('用アクセスポイント', '', $fetchedRow['pg_name']);		// アクセスポイント名
		$row = array(
			'index'		=> $index,			// インデックス番号
			'value'		=> $value,			// ランディングページID
			'name'		=> $this->convertToDispString($fetchedRow['mn_name']),			// ランディングページID名
			'access_point_name'	=> $this->convertToDispString($accessPointName),
			'sort_order'	=> $this->convertToDispString($fetchedRow['mn_sort_order']),	// ソート順
			'ref_count' => $this->_db->getMenuIdRefCount($value)		// ランディングページID使用数
		);
		$this->tmpl->addVars('id_list', $row);
		$this->tmpl->parseTemplate('id_list', 'a');
		
		// 表示中項目のページサブIDを保存
		$this->serialArray[] = $value;
		return true;
	}
}
?>
