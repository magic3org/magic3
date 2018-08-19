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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainUserBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getIncludePath() . '/common/userInfo.php');		// ユーザ情報クラス

class admin_mainUserlistWidgetContainer extends admin_mainUserBaseWidgetContainer
{
	private $serialNo;	// シリアルNo
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $userTypeArray;		// ユーザ種別(-1=未承認ユーザ、0=仮ユーザ、10=一般ユーザ、50=システム運営者、100=システム管理者)
	private $changeUserTypeArray;// ユーザタイプ変更用
	private $userType;		// ユーザタイプ
	private $userGroupListData;		// 全ユーザグループ
	private $userGroupArray;		// 選択中のユーザグループ
	private $canSelectGroup;		// グループ選択可能かどうか
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const USER_GROUP_COUNT = 2;				// ユーザグループの選択可能数
	const DEFAULT_PASSWORD = '********';	// 設定済みを示すパスワード
	const STATUS_ICON_SIZE = 32;			// 状態表示アイコンサイズ
	const CALENDAR_ICON_FILE = '/images/system/calendar.png';		// カレンダーアイコン
	const LOGIN_ENABLED_ICON_FILE = '/images/system/active32.png';			// ログイン可アイコン
	const CLOSED_ICON_FILE = '/images/system/closed32.png';	// ログイン不可アイコン
	const SKYPE_STATUS_ICON_HEIGHT = 22;	// Skype状態アイコン
	const SKYPE_STATUS_ICON_WIDTH = 91; 	// Skype状態アイコン
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// ユーザタイプメニュー項目
		$this->userTypeArray = array(	array(	'name' => '-- ' . $this->_('Unselected') . ' --',		'value' => ''),		// 未選択
										array(	'name' => $this->_('Normal User'),		'value' => strval(UserInfo::USER_TYPE_NORMAL)),		// 一般ユーザ
										array(	'name' => $this->_('Author'),			'value' => strval(UserInfo::USER_TYPE_AUTHOR)),		// 投稿ユーザ
										array(	'name' => $this->_('Site Manager'),		'value' => strval(UserInfo::USER_TYPE_MANAGER)),	// システム運営者。このレベル以上が管理機能が使用できる
										array(	'name' => $this->_('Administrator'),	'value' => strval(UserInfo::USER_TYPE_SYS_ADMIN)));		// システム管理者
		// ユーザタイプ変更用
		$this->changeUserTypeArray = array(	array(	'name' => $this->_('Not authenticated'),	'value' => strval(UserInfo::USER_TYPE_NOT_AUTHENTICATED)),	// 未承認ユーザ
											array(	'name' => $this->_('Temporary User'),		'value' => strval(UserInfo::USER_TYPE_TMP)),		// 仮登録ユーザ
											array(	'name' => $this->_('Normal User'),			'value' => strval(UserInfo::USER_TYPE_NORMAL)),	// 一般ユーザ
											array(	'name' => $this->_('Author'),				'value' => strval(UserInfo::USER_TYPE_AUTHOR)));		// 投稿ユーザ
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
		return 'userlist';
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
		if ($task == 'userlist_detail'){		// 詳細画面
			if ($this->gPage->isPersonalMode()){		// パーソナルモードの場合
				return 'userlist_detail_personal.tmpl.html';
			} else {
				return 'userlist_detail.tmpl.html';
			}
		} else {			// 一覧画面
			return 'userlist.tmpl.html';
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
		
		// ##### アクセス権のチェック #####
		// パーソナルモードの場合はログインしているユーザの情報のみ取得可能
		if ($this->gPage->isPersonalMode()){
			$enableAccess = true;
			
			// ユーザ情報詳細のみアクセス可能
			if ($task == 'userlist') $enableAccess = false;

			// ユーザIDのチェック
			if ($enableAccess){
				$userId = $request->trimValueOf(M3_REQUEST_PARAM_USER_ID);		// URLで付加されたユーザID
				if ($userId != $this->_userId) $enableAccess = false;
			}

			// シリアル番号が設定されている場合はチェック
			$serialNo = intval($request->trimValueOf('serial'));		// 選択項目のシリアル番号
			if (!empty($serialNo)){
				$ret = $this->_mainDb->getUserById($userId, $row);// ユーザ情報を取得
				if (!$ret || ($ret && $serialNo != $row['lu_serial'])) $enableAccess = false;
			}

			// アクセス不可の場合はエラーメッセージを出力して終了
			if (!$enableAccess){
				$this->replaceTemplateFile('message.tmpl.html');
				$this->SetMsg(self::MSG_APP_ERR, $this->_('Can not access the page.'));		// アクセスできません
				return;
			}
		}
				
		if ($task == 'userlist_detail'){		// 詳細画面
			$this->createDetail($request);
			
			// テキストをローカライズ
			$localeText['msg_add'] = $this->_('Add new user?');		// 新規ユーザを追加しますか?
			$localeText['msg_update'] = $this->_('Update user configuration?');		// ユーザ情報を更新しますか?
			$localeText['msg_delete'] = $this->_('Delete user?');		// ユーザを削除しますか?
			$localeText['msg_no_name'] = $this->_('No name entered.');		// 名前が入力されていません
			$localeText['msg_no_account'] = $this->_('No login account entered.');		// ログインアカウントが入力されていません
			$localeText['msg_no_password'] = $this->_('No password entered.');		// パスワードが入力されていません
			$localeText['label_userlist_detail'] = $this->_('User Detail');		// ユーザ詳細
			$localeText['label_name'] = $this->_('Name');		// 名前
			$localeText['label_account'] = $this->_('Login') . '<br />' . $this->_('Account');		// ログインアカウント
			$localeText['label_password'] = $this->_('Password');		// パスワード
			$localeText['label_user_type'] = $this->_('User Type');		// ユーザ種別
			$localeText['label_login_permission'] = $this->_('Login Permission');		// ログイン許可
			$localeText['label_active_term'] = $this->_('Active Term');		// 有効期間
			$localeText['label_user_group'] = $this->_('User Group');		// ユーザグループ
			$localeText['label_start_date'] = $this->_('Start Date:');		// 開始日
			$localeText['label_end_date'] = $this->_('End Date:');		// 終了日
			$localeText['label_hour'] = $this->_('Hour:');		// 時間
			$localeText['label_email'] = $this->_('Email');		// Eメール
			$localeText['label_skype_account'] = $this->_('Skype Account');		// Skypeアカウント
			$localeText['label_update_user'] = $this->_('Update User');		// 更新者
			$localeText['label_update_dt'] = $this->_('Update Date Time');		// 更新日時
			$localeText['label_calendar'] = $this->_('Calendar');		// カレンダー
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
			$localeText['label_userlist'] = $this->_('User List');					// ユーザ一覧
			$localeText['label_check'] = $this->_('Select');			// 選択
			$localeText['label_name'] = $this->_('Name');			// 名前
			$localeText['label_account'] = $this->_('Login Account');		// ログインアカウント
			$localeText['label_user_type'] = $this->_('User Type');		// ユーザ種別
			$localeText['label_login'] = $this->_('Login');		// ログイン
			$localeText['label_admin'] = $this->_('Administration');		// 管理権限
			$localeText['label_login_count'] = $this->_('Count');		// 回数
			$localeText['label_update_dt'] = $this->_('Update Date');		// 更新日
			$localeText['label_email'] = $this->_('Email');		// Eメール
			$localeText['label_others'] = $this->_('Others');		// その他
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
		
		if ($this->checkSafePost()/*CSRF対策用*/ && $act == 'delete'){		// メニュー項目の削除
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
				$ret = $this->_mainDb->delUserBySerial($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg($this->_('Item deleted.'));		// データを削除しました
					
					// 運用ログ出力
					for ($i = 0; $i < count($delItems); $i++){
						$ret = $this->_mainDb->getUserBySerial($delItems[$i], $row, $groupRows);
						if ($ret){
							$account = $row['lu_account'];
							$loginUserId = $row['lu_id'];
							$name = $row['lu_name'];
						}
						$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザ情報を削除しました。アカウント: ' . $account, 2100, 'userid=' . $loginUserId . ', username=' . $name);
					}
				} else {
					$this->setAppErrorMsg($this->_('Failed in deleting item.'));		// データ削除に失敗しました
				}
			}
		}
		
		$viewCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 総数を取得
		$totalCount = $this->_mainDb->getAllUserListCount();

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
		}
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		//$this->tmpl->addVar("_widget", "total_count", $totalCount);
		$this->tmpl->addVar("_widget", "total_count", sprintf($this->_('%d Total'), $totalCount));// 全 x件
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("search_range", "start_no", $startNo);
		$this->tmpl->addVar("search_range", "end_no", $endNo);
		if ($totalCount > 0) $this->tmpl->setAttribute('search_range', 'visibility', 'visible');// 検出範囲を表示
		
		// ユーザリストを取得
		$this->_mainDb->getAllUserList($viewCount, $pageNo, array($this, 'userListLoop'));
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
		// 入力値を取得
		$act = $request->trimValueOf('act');
		$userIdByUrl = $request->trimValueOf(M3_REQUEST_PARAM_USER_ID);		// URLで付加されたユーザID
		$this->serialNo = intval($request->trimValueOf('serial'));		// 選択項目のシリアル番号
		$name = $request->trimValueOf('item_name');
		$account = $request->trimValueOf('item_account');
		$password = $request->trimValueOf('password');
		$this->userType = $request->trimValueOf('item_usertype');		// ユーザ種別
		$canLogin = ($request->trimValueOf('item_canlogin') == 'on') ? 1 : 0;		// ログインできるかどうか
		$start_date = $request->trimValueOf('item_start_date');		// 公開期間開始日付
		if (!empty($start_date)) $start_date = $this->convertToProperDate($start_date);
		$start_time = $request->trimValueOf('item_start_time');		// 公開期間開始時間
		if (empty($start_date)){
			$start_time = '';					// 日付が空のときは時刻も空に設定する
		} else {
			if (empty($start_time)) $start_time = '00:00';		// 日付が入っているときは時間にデフォルト値を設定
		}
		if (!empty($start_time)) $start_time = $this->convertToProperTime($start_time, 1/*時分フォーマット*/);
		
		$end_date = $request->trimValueOf('item_end_date');		// 公開期間終了日付
		if (!empty($end_date)) $end_date = $this->convertToProperDate($end_date);
		$end_time = $request->trimValueOf('item_end_time');		// 公開期間終了時間
		if (empty($end_date)){
			$end_time = '';					// 日付が空のときは時刻も空に設定する
		} else {
			if (empty($end_time)) $end_time = '00:00';		// 日付が入っているときは時間にデフォルト値を設定
		}
		if (!empty($end_time)) $end_time = $this->convertToProperTime($end_time, 1/*時分フォーマット*/);
		$email = $request->trimValueOf('item_email');		// Eメール
		$skypeAccount = $request->trimValueOf('item_skype_account');		// Skypeアカウント
		
		// ユーザグループを取得
		$this->userGroupArray = array();
		for ($i = 0; $i < self::USER_GROUP_COUNT; $i++){
			$itemName = 'item_group' . $i;
			$itemValue = $request->trimValueOf($itemName);
			if (!empty($itemValue)){		// 0以外の値を取得
				$this->userGroupArray[] = $itemValue;
			}
		}
		
		$isAdmin = false;			// 管理権限ありかどうか
		$limitedMenu = false;		// ユーザタイプメニューの項目を制限するかどうか
		$reloadData = false;		// データの再読み込み
		if ($this->checkSafePost()/*CSRF対策用*/ && $act == 'update'){		// 行更新のとき
			// ##### パーソナルモードの場合とパーソナルモードでない場合の処理を分ける #####
			if ($this->gPage->isPersonalMode()){		// パーソナルモードの場合
				// ### アカウントの変更は不可 ##
				// 入力チェック
				$this->checkInput($name, $this->_('Name'));		// 名前
				$this->checkMailAddress($email, $this->_('Email'), true);		// Eメール
		
				// ユーザ情報を取得
				$ret = $this->_mainDb->getUserBySerial($this->serialNo, $row, $groupRows);
				if (!$ret) $this->setMsg(self::MSG_APP_ERR, $this->_('Failed in getting data.'));			// データ取得に失敗しました
				
				// エラーなしの場合は、データを更新
				if ($this->getMsgCount() == 0){
					// 変更項目を取得
					$chengedFields = array();
					if (!empty($password) && $password != $row['lu_password']) $chengedFields[] = 'パスワード';
					if ($name != $row['lu_name']) $chengedFields[] = 'ユーザ名';
					if ($email != $row['lu_email']) $chengedFields[] = 'Eメール';
			
					// 追加項目
					$otherParams = array();
					$otherParams['lu_email'] = $email;		// Eメール
					$ret = $this->_db->updateLoginUser($this->serialNo, $name, null, $password, null, null, null, null, $newSerial,
														null, null, null, $otherParams);
					if ($ret){		// データ追加成功のとき
						$this->setMsg(self::MSG_GUIDANCE, $this->_('Item updated.'));		// データを更新しました
				
						// 運用ログ出力
						$changeFieldInfo = '';
						if (!empty($chengedFields)) $changeFieldInfo = '('. implode(',', $chengedFields) . ')';
						$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザ情報' . $changeFieldInfo . 'を更新しました。アカウント: ' . $row['lu_account'], 2100, 'userid=' . $row['lu_id'] . ', username=' . $row['lu_name']);
				
						$this->serialNo = $newSerial;
						$reloadData = true;		// データの再読み込み
					} else {
						$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating item.'));		// データ更新に失敗しました
					}
				}
			} else {	// パーソナルモードではない場合
				// 入力チェック
				$this->checkInput($name, $this->_('Name'));		// 名前
				$this->checkLoginAccount($account, $this->_('Login Account'));// アカウント
				$this->checkMailAddress($email, $this->_('Email'), true);		// Eメール
	
				// 期間範囲のチェック
				if (!empty($start_date) && !empty($end_date)){
					if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg($this->_('Invalid active term.'));	// 有効期間が不正です
				}
		
				// アカウント重複チェック
				// ユーザ情報を取得
				$ret = $this->_mainDb->getUserBySerial($this->serialNo, $row, $groupRows);
				if ($ret){
					if ($row['lu_account'] != $account && $this->_db->isExistsAccount($account)) $this->setMsg(self::MSG_USER_ERR, $this->_('Login account is duplicated.'));		// アカウントが重複しています
				} else {
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in getting data.'));			// データ取得に失敗しました
				}
		
				// エラーなしの場合は、データを更新
				if ($this->getMsgCount() == 0){
					// 管理権限ありのときは、ユーザタイプが変更できない
					if ($row['lu_user_type'] >= UserInfo::USER_TYPE_MANAGER) $this->userType = $row['lu_user_type']; 	// 管理画面が使用できるかどうか
			
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
			
					// 変更項目を取得
					$chengedFields = array();
					if ($account != $row['lu_account']) $chengedFields[] = 'アカウント';
					if (!empty($password) && $password != $row['lu_password']) $chengedFields[] = 'パスワード';
					if ($name != $row['lu_name']) $chengedFields[] = 'ユーザ名';
					if ($this->userType != $row['lu_user_type']) $chengedFields[] = 'ユーザ種別';
					if ($email != $row['lu_email']) $chengedFields[] = 'Eメール';
			
					// 追加項目
					$otherParams = array();
					$otherParams['lu_email'] = $email;		// Eメール
					$otherParams['lu_skype_account'] = $skypeAccount;		// Skypeアカウント
					$ret = $this->_db->updateLoginUser($this->serialNo, $name, $account, $password, $this->userType, $canLogin, $startDt, $endDt, $newSerial,
														null, null, $this->userGroupArray, $otherParams);
					if ($ret){		// データ追加成功のとき
						$this->setMsg(self::MSG_GUIDANCE, $this->_('Item updated.'));		// データを更新しました
				
						// 運用ログ出力
						$changeFieldInfo = '';
						if (!empty($chengedFields)) $changeFieldInfo = '('. implode(',', $chengedFields) . ')';
						$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザ情報' . $changeFieldInfo . 'を更新しました。アカウント: ' . $account, 2100, 'userid=' . $row['lu_id'] . ', username=' . $name);
				
						$this->serialNo = $newSerial;
						$reloadData = true;		// データの再読み込み
					} else {
						$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating item.'));		// データ更新に失敗しました
					}
				}
			}
		} else if ($this->checkSafePost()/*CSRF対策用*/ && $act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, $this->_('Name'));		// 名前
			$this->checkLoginAccount($account, $this->_('Login Account'));// アカウント
			$this->checkMailAddress($email, $this->_('Email'), true);		// Eメール
			if ($this->userType == '') $this->setUserErrorMsg($this->_('User type not selected.'));		// ユーザ種別が選択されていません
			
			// 期間範囲のチェック
			if (!empty($start_date) && !empty($end_date)){
				if (strtotime($start_date . ' ' . $start_time) >= strtotime($end_date . ' ' . $end_time)) $this->setUserErrorMsg($this->_('Invalid active term.'));		// 有効期間が不正です
			}
			
			// アカウント重複チェック
			if ($this->_db->isExistsAccount($account)) $this->setMsg(self::MSG_USER_ERR, $this->_('Login account is duplicated.'));	// アカウントが重複しています
						
			// エラーなしの場合は、データを登録
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
		} else if ($this->checkSafePost()/*CSRF対策用*/ && $act == 'delete'){		// 削除のとき
			$ret = $this->_mainDb->delUserBySerial(array($this->serialNo));
			if ($ret){		// データ削除成功のとき
				$this->setMsg(self::MSG_GUIDANCE, $this->_('Item deleted.'));	// データを削除しました
				
				// 運用ログ出力
				$ret = $this->_mainDb->getUserBySerial($this->serialNo, $row, $groupRows);
				if ($ret) $loginUserId = $row['lu_id'];
				$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザ情報を削除しました。アカウント: ' . $account, 2100, 'userid=' . $loginUserId . ', username=' . $name);
			} else {
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in deleting item.'));	// データ削除に失敗しました
			}
		} else {
			// 初期値を設定
			$canLogin = 1;		// ログインできるかどうか
			$reloadData = true;		// データの再読み込み
				
			// ##### ユーザIDが設定されているとき(他ウィジェットからの表示)は、データを取得 #####
			if (!empty($userIdByUrl)){
				// ユーザ情報を取得
				$ret = $this->_mainDb->getUserById($userIdByUrl, $row);
				if ($ret){
					$this->serialNo = $row['lu_serial'];		// ユーザシリアル番号
				} else {
					$this->serialNo = 0;
				}
			}
		}
		if ($reloadData){		// データの再読み込み
			// ユーザ情報を取得
			$ret = $this->_mainDb->getUserBySerial($this->serialNo, $row, $groupRows);
			if ($ret){
				$name = $row['lu_name'];
				$account = $row['lu_account'];
				$this->userType = $row['lu_user_type'];			// ユーザ種別
				if ($this->userType < UserInfo::USER_TYPE_MANAGER){	// 管理権限なしの場合は、選択用メニューを表示
					$limitedMenu = true;
				} else {
					$isAdmin = true;			// 管理権限ありかどうか
				}
				$canLogin = $row['lu_enable_login'];		// ログインできるかどうか
				$loginUserId = $row['lu_id'];				// ユーザID
				$start_date = $this->convertToDispDate($row['lu_active_start_dt']);	// 有効期間開始日
				$start_time = $this->convertToDispTime($row['lu_active_start_dt'], 1/*時分*/);	// 有効期間開始時間
				$end_date = $this->convertToDispDate($row['lu_active_end_dt']);	// 有効期間終了日
				$end_time = $this->convertToDispTime($row['lu_active_end_dt'], 1/*時分*/);	// 有効期間終了時間
				$email = $row['lu_email'];		// Eメール
				$skypeAccount = $row['lu_skype_account'];		// Skypeアカウント
		
				// ユーザグループ取得
				$this->userGroupArray = $this->getUserGroup($groupRows);
				
				// 一般ユーザの場合のみグループ選択可能
				if ($row['lu_user_type'] == UserInfo::USER_TYPE_NORMAL) $this->canSelectGroup = true;
				
				// データ登録情報取得
				$ret = $this->_db->getLoginUserRecordById($row['lu_create_user_id'], $userInfo);
				if ($ret) $updateUser = $userInfo['lu_name'];	// データ登録者
				$updateDt = $row['lu_create_dt'];			// データ登録日時
				
				// ユーザタイプ文字列を作成
				$userOptType = UserInfo::parseUserTypeOption($row['lu_user_type_option']);
				if ($userOptType == UserInfo::USER_OPT_TYPE_PAGE_MANAGER) $userTypeStr = 'ページ運用者';
			}
		}
		
		// ##### パーソナルモードの場合はユーザタイプ、グループの選択不可
		if (!$this->gPage->isPersonalMode()){
			// ユーザタイプ選択メニュー作成
			$this->createUserTypeMenu($limitedMenu);
		
			// ユーザグループメニュー作成
			$ret = $this->_mainDb->getAllUserGroupRows($this->_langId, $this->userGroupListData);
			$this->createUserGroupMenu(self::USER_GROUP_COUNT);
		}
		
		// 取得データを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "account", $this->convertToDispString($account));
		$canLoginCheck = '';
		if ($canLogin) $canLoginCheck = 'checked';
		$this->tmpl->addVar("_widget", "can_login", $canLoginCheck);
		$this->tmpl->addVar("_widget", "userid", $this->convertToDispString($loginUserId));// ユーザID
		$this->tmpl->addVar("_widget", "user_type", $this->convertToDispString($userTypeStr));
		$this->tmpl->addVar('_widget', 'calendar_img', $this->getUrl($this->gEnv->getRootUrl() . self::CALENDAR_ICON_FILE));	// カレンダーアイコン
		$this->tmpl->addVar("_widget", "start_date", $start_date);	// 有効期間開始日
		$this->tmpl->addVar("_widget", "start_time", $start_time);	// 有効期間開始時間
		$this->tmpl->addVar("_widget", "end_date", $end_date);	// 有効期間終了日
		$this->tmpl->addVar("_widget", "end_time", $end_time);	// 有効期間終了時間
		$this->tmpl->addVar("_widget", "email", $this->convertToDispString($email));		// Eメール
		$this->tmpl->addVar("_widget", "skype_account", $this->convertToDispString($skypeAccount));		// Skypeアカウント
		$this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($updateUser));	// データ登録者
		$this->tmpl->addVar("_widget", "update_dt", $this->convertToDispDateTime($updateDt));	// データ登録日時

		if (empty($this->serialNo)){		// ユーザIDが空のときは新規とする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
			$this->tmpl->addVar("_widget", "password", self::DEFAULT_PASSWORD);// 入力済みを示すパスワードの設定
			
			// 管理権限ありの場合は変更不可にする
			if ($isAdmin) $this->tmpl->addVar('_widget', 'usertype_disabled', 'disabled');
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
		// アクセス制限
		$adminWidget = '';
		if (!empty($fetchedRow['lu_admin_widget'])) $adminWidget = '(' . $this->_('Limited') . ')';

		// ユーザ種別
		switch ($fetchedRow['lu_user_type']){
			case UserInfo::USER_TYPE_NOT_AUTHENTICATED:
				$userType = '<font color="darkgray">' . $this->_('Not authenticated') . '</font>';		// 未承認
				break;
			case UserInfo::USER_TYPE_TMP:
				$userType = '<font color="black">' . $this->_('Temporary User') . '</font>';		// 仮登録
				break;
			case UserInfo::USER_TYPE_NORMAL:
				$userType = '<font color="green">' . $this->_('Normal User') . '</font>';		// 一般ユーザ
				break;
			case UserInfo::USER_TYPE_AUTHOR:
				$userType = '<font color="yellowgreen">' . $this->_('Author') . '</font>';		// 投稿ユーザ
				break;
			case UserInfo::USER_TYPE_MANAGER:
				$userType = '<font color="orange">' . $this->_('Site Manager') . $adminWidget . '</font>';		// 運営者
				break;
			case UserInfo::USER_TYPE_SYS_ADMIN:
				$userType = '<font color="red">' . $this->_('Administrator') . '</font>';		// 管理者
				break;
			default:
				$userType = $this->_('Out of Range');		// 該当なし
				break;
		}
		
		// ログイン回数
		$loginCount = $fetchedRow['ll_login_count'];
		if (empty($loginCount)) $loginCount = '0';

		// ログイン履歴画面URL
		//$loginStatusUrl = '?task=loginstatus_history&account=' . $fetchedRow['lu_account'];
		$loginStatusUrl = '?task=' . self::TASK_LOGINHISTORY . '&userid=' . $fetchedRow['lu_id'];
		
		// ユーザ状態
		if ($fetchedRow['lu_enable_login']){		// ログイン可能のとき
			$iconUrl = $this->gEnv->getRootUrl() . self::LOGIN_ENABLED_ICON_FILE;			// ログイン可アイコン
			$iconTitle = 'ログイン可';
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::CLOSED_ICON_FILE;		// ログイン不可アイコン
			$iconTitle = 'ログイン不可';
		}
		$loginPermissionTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::STATUS_ICON_SIZE . '" height="' . self::STATUS_ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" />';
					
		// Skype状態表示用タグ作成
/*		$skypeStatusTag = '';
		$skypeAccount = $fetchedRow['lu_skype_account'];
		if (!empty($skypeAccount)){
			$skypeStatusTag = '<a href="skype:' . $skypeAccount . '?call"><img src="http://mystatus.skype.com/bigclassic/' 
								. $skypeAccount . '" style="border: none;" width="' . self::SKYPE_STATUS_ICON_WIDTH . '" height="' . self::SKYPE_STATUS_ICON_HEIGHT . '" alt="ログイン状態" /></a>';
		}*/
		
		$row = array(
			'index' => $index,													// 行番号
			'serial' => $this->convertToDispString($fetchedRow['lu_serial']),			// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['lu_id']),			// ID
			'name' => $this->convertToDispString($fetchedRow['lu_name']),		// 名前
			'account' => $this->convertToDispString($fetchedRow['lu_account']),		// アカウント
			'email' => $this->convertToDispString($fetchedRow['lu_email']),		// Eメール
			'user_type' => $userType,		// ユーザ種別
			'update_dt' => $this->convertToDispDateTime($fetchedRow['lu_create_dt'], 0, 10/*時分表示*/),	// 更新日時
			'login_permission'	=> $loginPermissionTag,				// ログイン可能かどうか
			'login_count' => $loginCount,			// ログイン回数
			'login_status_url' => $this->convertUrlToHtmlEntity($loginStatusUrl),	// ログイン状況画面URL
			'selected' => $selected												// 項目選択用ラジオボタン
//			'others' => $skypeStatusTag												// その他
		);
		$this->tmpl->addVars('userlist', $row);
		$this->tmpl->parseTemplate('userlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['lu_serial'];
		return true;
	}
	/**
	 * ユーザタイプ選択メニュー作成
	 *
	 * @param bool $limited			// メニュー項目を制限するかどうか
	 * @return なし
	 */
	function createUserTypeMenu($limited)
	{
		// 管理権限なしのユーザに設定されている場合は変更可能メニューを表示
		if ($limited){
			for ($i = 0; $i < count($this->changeUserTypeArray); $i++){
				$value = $this->changeUserTypeArray[$i]['value'];
				$name = $this->changeUserTypeArray[$i]['name'];
			
				$selected = '';
				if ($value == $this->userType) $selected = 'selected';
			
				$row = array(
					'value'    => $value,			// 値
					'name'     => $name,			// 名前
					'selected' => $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('usertype_list', $row);
				$this->tmpl->parseTemplate('usertype_list', 'a');
			}
		} else {
			for ($i = 0; $i < count($this->userTypeArray); $i++){
				$value = $this->userTypeArray[$i]['value'];
				$name = $this->userTypeArray[$i]['name'];
			
				$selected = '';
				if ($value == $this->userType) $selected = 'selected';
			
				$row = array(
					'value'    => $value,			// 値
					'name'     => $name,			// 名前
					'selected' => $selected														// 選択中かどうか
				);
				$this->tmpl->addVars('usertype_list', $row);
				$this->tmpl->parseTemplate('usertype_list', 'a');
			}
		}
	}
	/**
	 * ユーザグループメニューを作成
	 *
	 * @param int  	$size			メニューの表示数
	 * @return なし						
	 */
	function createUserGroupMenu($size)
	{
		for ($j = 0; $j < $size; $j++){
			// selectメニューの作成
			$this->tmpl->clearTemplate('group_list');
			for ($i = 0; $i < count($this->userGroupListData); $i++){
				$userGroupId = $this->userGroupListData[$i]['ug_id'];
				$selected = '';
				if ($j < count($this->userGroupArray) && $this->userGroupArray[$j] == $userGroupId){
					$selected = 'selected';
				}
				$menurow = array(
					'value'		=> $userGroupId,									// ユーザグループID
					'name'		=> $this->userGroupListData[$i]['ug_name'],			// ユーザグループ名
					'selected'	=> $selected										// 選択中かどうか
				);
				$this->tmpl->addVars('group_list', $menurow);
				$this->tmpl->parseTemplate('group_list', 'a');
			}
			// メニューの選択可否
			$disabled = '';
			if (!$this->canSelectGroup) $disabled = 'disabled';
			$itemRow = array(
					'unselected' => $this->_('Unselected'),		// 未選択項目
					'index'		=> $j,			// 項目番号
					'menu_disabled'	=> $disabled
			);
			$this->tmpl->addVars('group', $itemRow);
			$this->tmpl->parseTemplate('group', 'a');
		}
	}
	/**
	 * ユーザグループ取得
	 *
	 * @param array  	$srcRows			取得行
	 * @return array						取得した行
	 */
	function getUserGroup($srcRows)
	{
		$destArray = array();
		$itemCount = 0;
		for ($i = 0; $i < count($srcRows); $i++){
			if (!empty($srcRows[$i]['uw_group_id'])){
				$destArray[] = $srcRows[$i]['uw_group_id'];
				$itemCount++;
				if ($itemCount >= self::USER_GROUP_COUNT) break;
			}
		}
		return $destArray;
	}
}
?>
