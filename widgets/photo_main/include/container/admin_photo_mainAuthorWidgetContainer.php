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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_photo_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/photo_mainDb.php');
require_once($gEnvManager->getIncludePath() . '/common/userInfo.php');		// ユーザ情報クラス

class admin_photo_mainAuthorWidgetContainer extends admin_photo_mainBaseWidgetContainer
{
	private $serialNo;	// シリアルNo
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 10;			// リンクページ数
	const DEFAULT_PASSWORD = '********';	// 設定済みを示すパスワード
	const ACTIVE_ICON_FILE = '/images/system/active.png';			// 公開中アイコン
	const INACTIVE_ICON_FILE = '/images/system/inactive.png';		// 非公開アイコン
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * ヘルプデータを設定
	 *
	 * ヘルプの設定を行う場合はヘルプIDを返す。
	 * ヘルプデータの読み込むディレクトリは「自ウィジェットディレクトリ/include/help」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ヘルプID。ヘルプデータはファイル名「help_[ヘルプID].php」で作成。ヘルプを使用しない場合は空文字列「''」を返す。
	 */
	function _setHelp($request, &$param)
	{	
		return 'author';
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
		if ($task == self::TASK_AUTHER_DETAIL){		// 詳細画面
			return 'admin_author_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_author.tmpl.html';
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
		$localeText = array();
		$task = $request->trimValueOf('task');
		
		if ($task == self::TASK_AUTHER_DETAIL){		// 詳細画面
			$this->createDetail($request);
			
			// テキストをローカライズ
			$localeText['msg_add'] = $this->_('Add new user?');		// 新規ユーザを追加しますか?
			$localeText['msg_update'] = $this->_('Update user configuration?');		// ユーザ情報を更新しますか?
			$localeText['msg_delete'] = $this->_('Delete user?');		// ユーザを削除しますか?
			$localeText['msg_no_name'] = $this->_('No name entered.');		// 名前が入力されていません
			$localeText['msg_no_account'] = $this->_('No login account entered.');		// ログインアカウントが入力されていません
			$localeText['msg_no_password'] = $this->_('No password entered.');		// パスワードが入力されていません
			$localeText['label_author_detail'] = $this->_('Image Manager Detail');		// 画像管理者詳細
			$localeText['label_name'] = $this->_('Name');		// 名前
			$localeText['label_account'] = $this->_('Login Account');		// ログインアカウント
			$localeText['label_password'] = $this->_('Password');		// パスワード
			$localeText['label_login_enable'] = $this->_('Login Enable');		// ログイン可
			$localeText['label_go_back'] = $this->_('Go back');		// 戻る
			$localeText['label_delete'] = $this->_('Delete');	// 削除
			$localeText['label_update'] = $this->_('Update');	// 更新
			$localeText['label_add'] = $this->_('Add');	// 新規追加
		} else {			// 一覧画面
			$this->createList($request);
			
			// テキストをローカライズ
			$localeText['msg_select_item'] = $this->_('Select item to edit.');		// 編集する項目を選択してください
			$localeText['msg_select_del_item'] = $this->_('Select item to delete.');		// 削除する項目を選択してください
			$localeText['msg_delete_item'] = $this->_('Delete selected item?');// 選択項目を削除しますか?
			$localeText['label_author'] = $this->_('Image Manager List');					// 画像管理者詳細一覧
			$localeText['label_check'] = $this->_('Select');			// 選択
			$localeText['label_name'] = $this->_('Name');			// 名前
			$localeText['label_account'] = $this->_('Login Account');		// ログインアカウント
			$localeText['label_image_count'] = $this->_('Image Count');		// 画像数
			$localeText['label_published_image_count'] = $this->_('Published Image Count');		// 公開数
			$localeText['label_login_count'] = $this->_('Login Count');		// ログイン回数
			//$localeText['label_update_dt'] = $this->_('Update Date');		// 更新日
			$localeText['label_regist_dt'] = $this->_('Registered Date');		// 登録日
			$localeText['label_new'] = $this->_('New');					// 新規
			$localeText['label_edit'] = $this->_('Edit');				// 編集
			$localeText['label_delete'] = $this->_('Delete');			// 削除
			$localeText['label_range'] = $this->_('Range:');		// 範囲：
		}
		$this->setLocaleText($localeText);
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$act = $request->trimValueOf('act');
		
		if ($act == 'delete'){		// メニュー項目の削除
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
				$ret = self::$_mainDb->delUserBySerial($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg($this->_('Item deleted.'));		// データを削除しました
				} else {
					$this->setAppErrorMsg($this->_('Failed in deleting item.'));		// データ削除に失敗しました
				}
			}
		}
		
		$viewCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 総数を取得
		$totalCount = self::$_mainDb->getAllUserListCount(photo_mainCommonDef::USER_OPTION);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $viewCount);
		
		// ページングリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
/*		
		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $viewCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $viewCount + 1;		// 先頭番号
		$startNo = ($pageNo -1) * $viewCount +1;		// 先頭の行番号
		$endNo = $pageNo * $viewCount > $totalCount ? $totalCount : $pageNo * $viewCount;// 最後の行番号
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}*/
		
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
//		$this->tmpl->addVar("_widget", "total_count", sprintf($this->_('%d Total'), $totalCount));
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
//		$this->tmpl->addVar("search_range", "start_no", $startNo);
//		$this->tmpl->addVar("search_range", "end_no", $endNo);
//		if ($totalCount > 0) $this->tmpl->setAttribute('search_range', 'visibility', 'visible');// 検出範囲を表示
		
		// ユーザリストを取得
		self::$_mainDb->getAllUserList(photo_mainCommonDef::USER_OPTION, $viewCount, $pageNo, array($this, 'userListLoop'));
		if (count($this->serialArray) > 0){
			$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		} else {
			// 項目がないときは、一覧を表示しない
			$this->tmpl->setAttribute('authorlist', 'visibility', 'hidden');
		}
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
		$this->serialNo = intval($request->trimValueOf('serial'));		// 選択項目のシリアル番号
		$name = $request->trimValueOf('item_name');
		$account = $request->trimValueOf('item_account');
		$password = $request->trimValueOf('password');
		$canLogin = ($request->trimValueOf('item_canlogin') == 'on') ? 1 : 0;		// ログインできるかどうか
		
		$isAdmin = false;			// 管理権限ありかどうか
		$limitedMenu = false;		// ユーザタイプメニューの項目を制限するかどうか
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			$this->checkInput($name, $this->_('Name'));		// 名前
			$this->checkInput($account, $this->_('Login Account'));	// アカウント
			
			// アカウント重複チェック
			// 設定データを取得
			$ret = self::$_mainDb->getUserBySerial($this->serialNo, $row);
			if ($ret){
				if ($row['lu_account'] != $account && $this->_db->isExistsAccount($account)) $this->setMsg(self::MSG_USER_ERR, $this->_('Login account is duplicated.'));		// アカウントが重複しています
			} else {
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in getting data.'));			// データ取得に失敗しました
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				
				$ret = $this->_db->updateLoginUser($this->serialNo, $name, $account, $password, UserInfo::USER_TYPE_MANAGER/*システム運用者*/, $canLogin, $startDt, $endDt, $newSerial);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Item updated.'));		// データを更新しました
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in deleting item.'));		// データ更新に失敗しました
				}
			}
		} else if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, $this->_('Name'));		// 名前
			$this->checkInput($account, $this->_('Login Account'));	// アカウント
			
			// アカウント重複チェック
			if ($this->_db->isExistsAccount($account)) $this->setMsg(self::MSG_USER_ERR, $this->_('Login account is duplicated.'));	// アカウントが重複しています
						
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = $this->_db->addNewLoginUser($name, $account, $password, UserInfo::USER_TYPE_MANAGER/*システム運用者*/, $canLogin, null/*有効期間開始*/, null/*有効期間終了*/, $newSerial, 
																		$this->gEnv->getCurrentWidgetId()/*制限ウィジェット*/, photo_mainCommonDef::USER_OPTION);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Item added.'));	// データを追加しました
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in adding item.'));	// データ追加に失敗しました
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			$ret = self::$_mainDb->delUserBySerial(array($this->serialNo));
			if ($ret){		// データ削除成功のとき
				$this->setMsg(self::MSG_GUIDANCE, $this->_('Item deleted.'));	// データを削除しました
			} else {
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in deleting item.'));	// データ削除に失敗しました
			}
		} else {
			// 初期値を設定
			$canLogin = 1;		// ログインできるかどうか
			$reloadData = true;		// データの再読み込み
		}
		if ($reloadData){		// データの再読み込み
			// 設定データを取得
			$ret = self::$_mainDb->getUserBySerial($this->serialNo, $row);
			if ($ret){
				$name = $row['lu_name'];
				$account = $row['lu_account'];

				$canLogin = $row['lu_enable_login'];		// ログインできるかどうか
				$loginUserId = $row['lu_id'];				// ユーザID
			}
		}
		
		// 取得データを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "account", $this->convertToDispString($account));
		$canLoginCheck = '';
		if ($canLogin) $canLoginCheck = 'checked';
		$this->tmpl->addVar("_widget", "can_login", $canLoginCheck);
		$this->tmpl->addVar("_widget", "userid", $loginUserId);// ユーザID
		
		if (empty($this->serialNo)){		// ユーザIDが空のときは新規とする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
			$this->tmpl->addVar("_widget", "password", self::DEFAULT_PASSWORD);// 入力済みを示すパスワードの設定
		}
		
		// ディレクトリを設定
		$this->tmpl->addVar("_widget", "script_url", $this->getUrl($this->gEnv->getScriptsUrl()));
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
		// ログイン回数
		$loginCount = $fetchedRow['ll_login_count'];
		if (empty($loginCount)) $loginCount = '0';
		
		// 画像数を取得
		self::$_mainDb->getPhotoCount($fetchedRow['lu_id'], $allCount, $visbleCount);
		
		$row = array(
			'index' => $index,													// 行番号
			'serial' => $this->convertToDispString($fetchedRow['lu_serial']),			// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['lu_id']),			// ID
			'name' => $this->convertToDispString($fetchedRow['lu_name']),		// 名前
			'account' => $this->convertToDispString($fetchedRow['lu_account']),		// アカウント
			'image_count' => $this->convertToDispString($allCount),		// 画像数
			'visible_count' => $this->convertToDispString($visbleCount),		// 公開画像数
			'regist_dt' => $this->convertToDispDateTime($fetchedRow['lu_regist_dt']),	// 登録日時
			'login_count' => $this->convertToDispString($loginCount),		// ログイン回数
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('authorlist', $row);
		$this->tmpl->parseTemplate('authorlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['lu_serial'];
		return true;
	}
}
?>
