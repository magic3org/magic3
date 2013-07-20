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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_reserve_mainUserWidgetContainer.php 566 2008-05-01 02:25:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_reserve_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/reserve_mainDb.php');
require_once($gEnvManager->getIncludePath() . '/common/userInfo.php');		// ユーザ情報クラス

class admin_reserve_mainUserWidgetContainer extends admin_reserve_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	private $serialNo;	// シリアルNo
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	const DEFAULT_COUNTRY_ID = 'JPN';	// デフォルト国ID
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const DEFAULT_PASSWORD = '********';	// 設定済みを示すパスワード
	const ACCESS_TYPE = 'rv';			// ログインユーザのアクセス可能な機能タイプ
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
		
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new reserve_mainDb();
		$this->sysDb = $gInstanceManager->getSytemDbObject();
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
		if ($task == 'user_detail'){		// 詳細画面
			return 'admin_user_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_user.tmpl.html';
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
		if ($task == 'user_detail'){	// 詳細画面
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
		global $gEnvManager;
		
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
				$ret = $this->deleteUser($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		$maxListCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 総数を取得
		$totalCount = $this->db->getAllUserListCount(self::ACCESS_TYPE);

		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
		
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
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		
		// ユーザリストを取得
		$this->db->getAllUserList(self::ACCESS_TYPE, $maxListCount, $pageNo, array($this, 'userListLoop'));
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
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		$now = date("Y/m/d H:i:s");	// 現在日時
		$countryId = self::DEFAULT_COUNTRY_ID;			// デフォルト国ID
		$langId	= $gEnvManager->getCurrentLanguage();		// 表示言語を取得
								
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		if (empty($this->serialNo)) $this->serialNo = 0;
			
		$userNo				= $request->trimValueOf('user_no');			// 会員No
		$email				= $request->trimValueOf('email');			// eメール(ログインアカウント)
		$mobile				= $request->trimValueOf('mobile');			// 携帯電話
		$gender				= $request->trimValueOf('gender');			// 性別
		$birthday			= $request->trimValueOf('birthday');
		if (!empty($birthday)) $birthday = $this->convertToProperDate($birthday);			// 生年月日
		$familyName			= $request->trimValueOf('family_name');		// 会員名(姓)
		$firstName			= $request->trimValueOf('first_name');		// 会員名(名)
		$familyNameKana		= $request->trimValueOf('family_name_kana');		// 会員名カナ(姓)
		$firstNameKana		= $request->trimValueOf('first_name_kana');		// 会員名カナ(名)
		$zipcode			= $request->trimValueOf('zipcode');				// 郵便番号
		$this->state		= $request->trimValueOf('state');					// 都道府県
		$address1			= $request->trimValueOf('address');			// 住所
		$address2			= $request->trimValueOf('address2');			// 住所2
		$phone				= $request->trimValueOf('phone');			// 電話番号
		$fax				= $request->trimValueOf('fax');				// FAX
		$password			= $request->trimValueOf('password');
			
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			$this->checkInput($familyName, '会員名(姓)');
			$this->checkInput($firstName, '会員名(名)');
			$this->checkInput($familyNameKana, '会員名カナ(姓)');
			$this->checkInput($firstNameKana, '会員名カナ(名)');
			$this->checkMailAddress($email, 'Eメール');
			$this->checkDate($birthday, '生年月日', true);
			
			// メールアドレスが変更されているかチェック
			$updateLoginUser = false;	// ログインユーザデータを更新するかどうか
			$ret = $this->db->getUserBySerial($this->serialNo, $row);
			if ($ret){
				// メールアドレスかパスワードが変更されているときはログインユーザを更新
				if ($email != $row['li_email']){		// ログインユーザデータを更新するとき
					if ($this->sysDb->isExistsAccount($email)){// メールアドレスがログインIDとして既に登録されているかチェック
						$this->setAppErrorMsg('このEメールアドレスは既に登録されています');
					} else {
						$updateLoginUser = true;
					}
				}
				if (!empty($password) && $password != $row['lu_password']) $updateLoginUser = true;
				
				// 会員NOの重複チェック
				if ($userNo != $row['li_no'] && $this->db->isExistsUserNo($userNo)){
					$this->setAppErrorMsg('このNoは既に登録されています');
				}
				$loginUserId = $row['li_id'];
				$newSerial = $this->serialNo;
			} else {
				$this->setAppErrorMsg('該当のレコードが見つかりません');
			}
						
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 保存データ作成
				$name = $familyName . $firstName;
				if (empty($birthday)) $birthday = $gEnvManager->getInitValueOfTimestamp();
				
				// トランザクションスタート
				$this->db->startTransaction();
				
				// ログインユーザデータを更新
				$ret = true;
				if ($updateLoginUser){		// ログインユーザ更新のとき
					$ret = $this->db->updateUser($this->serialNo, $name, $email, $password, $newSerial);
				}
				
				// ユーザ情報追加
				if ($ret) $ret = $this->sysDb->updateLoginUserInfo($loginUserId, $userNo, $familyName, $firstName, $familyNameKana, $firstNameKana, $gender, $birthday, $email, $mobile,
													$zipcode, $this->state, $address1, $address2, $phone, $fax, $countryId, $tmp);
				
				// トランザクション終了
				$ret = $this->db->endTransaction();
				if ($ret){		// データ更新成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($familyName, '会員名(姓)');
			$this->checkInput($firstName, '会員名(名)');
			$this->checkInput($familyNameKana, '会員名カナ(姓)');
			$this->checkInput($firstNameKana, '会員名カナ(名)');
			$this->checkMailAddress($email, 'Eメール');
			$this->checkDate($birthday, '生年月日', true);
			
			// メールアドレスが登録済みかチェック
			if ($this->sysDb->isExistsAccount($email)){// メールアドレスがログインIDとして既に登録されているかチェック
				$this->setAppErrorMsg('このEメールアドレスは既に登録されています');
			}
			// 会員NOの重複チェック
			if (!empty($userNo) && $this->db->isExistsUserNo($userNo)){
				$this->setAppErrorMsg('このNoは既に登録されています2');
			}
						
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 保存データ作成
				$name = $familyName . $firstName;
				if (empty($birthday)) $birthday = $gEnvManager->getInitValueOfTimestamp();
				
				// トランザクションスタート
				$this->db->startTransaction();
				
				// 新規のログインユーザ作成
				$ret = $this->db->addUser($name, $email, $password, $gEnvManager->getCurrentWidgetId(), $loginUserId, $newSerial);
				
				// ユーザ情報追加
				if ($ret) $ret = $this->sysDb->updateLoginUserInfo($loginUserId, $userNo, $familyName, $firstName, $familyNameKana, $firstNameKana, $gender, $birthday, $email, $mobile,
													$zipcode, $this->state, $address1, $address2, $phone, $fax, $countryId, $tmp);
				
				// トランザクション終了
				$ret = $this->db->endTransaction();
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			$ret = $this->deleteUser(array($this->serialNo));
			if ($ret){		// データ削除成功のとき
				$this->setMsg(self::MSG_GUIDANCE, 'データを削除しました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'データ削除に失敗しました');
			}
		} else {
			$reloadData = true;		// データの再読み込み
		}
		if ($reloadData){		// データの再読み込み
			// 設定データを取得
			$ret = $this->db->getUserBySerial($this->serialNo, $row);
			if ($ret){
				// 取得値を設定
				$userNo				= $this->convertToDispString($row['li_no']);			// 会員No
				$email				= $this->convertToDispString($row['li_email']);			// eメール(ログインアカウント)
				$mobile				= $this->convertToDispString($row['li_mobile']);			// 携帯電話
				$gender				= $this->convertToDispString($row['li_gender']);			// 性別
				$birthday = $this->convertToDispDate($row['li_birthday']);			// 生年月日
				if ($birthday == $gEnvManager->getInitValueOfTimestamp()) $birthday = '';
				$familyName			= $this->convertToDispString($row['li_family_name']);		// 会員名(姓)
				$firstName			= $this->convertToDispString($row['li_first_name']);		// 会員名(名)
				$familyNameKana		= $this->convertToDispString($row['li_family_name_kana']);		// 会員名カナ(姓)
				$firstNameKana		= $this->convertToDispString($row['li_first_name_kana']);		// 会員名カナ(名)
				$zipcode			= $this->convertToDispString($row['li_zipcode']);				// 郵便番号
				$this->state		= $row['li_state_id'];					// 都道府県
				$address1			= $this->convertToDispString($row['li_address1']);			// 住所
				$address2			= $this->convertToDispString($row['li_address2']);			// 住所2
				$phone				= $this->convertToDispString($row['li_phone']);			// 電話番号
				$fax				= $this->convertToDispString($row['li_fax']);				// FAX
				$updateDt			= $this->convertToDispDateTime($row['li_create_dt']);	// 更新日時
				
				// 更新者名取得
				if ($this->sysDb->getLoginUserRecordById($row['li_create_user_id'], $userRow)) $updateUser	= $this->convertToDispString($userRow['lu_name']);	// 更新者
			}
		}
		// 都道府県を設定
		$this->db->getAllState('JPN', $langId, array($this, 'stateLoop'));
		
		// #### 更新、新規登録部をを作成 ####
		$this->tmpl->addVar("_widget", "user_no", $userNo);		// ユーザNo
		$this->tmpl->addVar("_widget", "email", $email);		// eメール(ログインアカウント)
		$this->tmpl->addVar("_widget", "mobile", $mobile);		// 携帯電話
		$this->tmpl->addVar("_widget", "birthday", $birthday);			// 生年月日
		$this->tmpl->addVar("_widget", "family_name", $familyName);		// 会員名(姓)
		$this->tmpl->addVar("_widget", "first_name", $firstName);		// 会員名(名)
		$this->tmpl->addVar("_widget", "family_name_kana", $familyNameKana);		// 会員名カナ(姓)
		$this->tmpl->addVar("_widget", "first_name_kana", $firstNameKana);		// 会員名カナ(名)
		$this->tmpl->addVar("_widget", "zipcode", $zipcode);		// 郵便番号
		$this->tmpl->addVar("_widget", "address", $address1);		// 住所
		$this->tmpl->addVar("_widget", "address2", $address2);		// 住所2
		$this->tmpl->addVar("_widget", "phone", $phone);		// 電話番号
		$this->tmpl->addVar("_widget", "fax", $fax);		// FAX
		$this->tmpl->addVar("_widget", "update_user", $updateUser);	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $updateDt);	// 更新日時
		// 性別
		if ($gender == 0){		// 未設定のとき
			$this->tmpl->addVar("_widget", "gender_none", 'selected');
		} else if ($gender == 1){
			$this->tmpl->addVar("_widget", "gender_male", 'selected');
		} else {
			$this->tmpl->addVar("_widget", "gender_female", 'selected');
		}
				
		if (empty($this->serialNo)){		// ユーザIDが空のときは新規とする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
			$this->tmpl->addVar("_widget", "password", self::DEFAULT_PASSWORD);// 入力済みを示すパスワードの設定
		}
		
		// ディレクトリを設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "script_url", $gEnvManager->getScriptsUrl());
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
		global $gEnvManager;
		
		// 行カラーの設定
		$lineColor = '';
		if ($index % 2 != 0){
			$lineColor = 'class="even"';		// 偶数行
		}
		// 性別
		$gender = '';
		if ($fetchedRow['li_gender'] == 1){
			$gender = '男';
		} else if ($fetchedRow['pi_gender'] == 2){
			$gender = '女';
		} else {
			$gender = '未';
		}
		$serial = $fetchedRow['lu_serial'];
		$row = array(
			'line_color' => $lineColor,						// 行のカラー
			'no' => $this->firstNo + $index,				// 行番号
			'index' => $index,								// 項目番号
			'serial' => $serial,	// シリアル番号
			'id' => $id,			// ID
			'user_no' => $this->convertToDispString($fetchedRow['li_no']),			// ユーザNo
			'family_name' => $this->convertToDispString($fetchedRow['li_family_name']),	// 顧客名(姓)
			'first_name' => $this->convertToDispString($fetchedRow['li_first_name']),	// 顧客名(名)
			'family_name_kana' => $this->convertToDispString($fetchedRow['li_family_name_kana']),	// 顧客名カナ(姓)
			'first_name_kana' => $this->convertToDispString($fetchedRow['li_first_name_kana']),	// 顧客名カナ(名)
			'email' => $this->convertToDispString($fetchedRow['li_email']),	// Eメールアドレス
			'gender' => $gender,			// 性別
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('userlist', $row);
		$this->tmpl->parseTemplate('userlist', 'a');
		
		// ユーザシリアルNoを保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * 取得した都道府県をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function stateLoop($index, $fetchedRow, $param)
	{
		global $gEnvManager;

		$selected = '';
		if ($fetchedRow['gz_id'] == $this->state){		// 選択中の都道府県
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['gz_id']),			// ID
			'name'     => $this->convertToDispString($fetchedRow['gz_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('state_list', $row);
		$this->tmpl->parseTemplate('state_list', 'a');
		return true;
	}
	/**
	 * ユーザの削除、ログインアカウントも削除
	 *
	 * @param array $serial		シリアル番号
	 * @return bool				true=成功、false=失敗
	 */
	function deleteUser($serialArray)
	{
		for ($i = 0; $i < count($serialArray); $i++){
			// ログインユーザデータを取得
			$ret = $this->db->getUserBySerial($serialArray[$i], $row);
			if ($ret){
				$loginUserId = $row['lu_id'];
				
				// トランザクションスタート
				$this->db->startTransaction();
				
				// ログインユーザを削除
				$ret = $this->sysDb->releaseLoginUser($loginUserId, self::ACCESS_TYPE, true/*アサインが消えたときはユーザを削除*/);
				
				// ユーザ情報を削除
				if ($ret) $this->sysDb->delLoginUserInfo($loginUserId);
				
				// トランザクション終了
				$ret = $this->db->endTransaction();
				if (!$ret) return false;
			} else {
				return false;
			}
		}
		return true;
	}
}
?>
