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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_ec_mainMemberWidgetContainer.php 5572 2013-01-23 08:43:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_ec_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_mainMemberDb.php');

class admin_ec_mainMemberWidgetContainer extends admin_ec_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;			// シリアル番号
	private $firstNo;			// 項目番号
	private $state;				// 都道府県
	private $memberType;		// 会員タイプ
	private $ecMailObj;	// メール連携オブジェクト
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $gender;			// 性別
	const EC_LIB_ID = "eclib";		// EC共通ライブラリオブジェクトID
	const DEFAULT_COUNTRY_ID = 'JPN';	// デフォルト国ID
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const USE_EMAIL		= 'use_email';		// EMAIL機能が使用可能かどうか
//	const AUTO_EMAIL_SENDER	= 'auto_email_sender';		// 自動送信メール用送信者アドレス
	const MAIL_OBJ_ID = 'ecmail';			// メール連携オブジェクト
//	const TARGET_WIDGET = 'ec_main';		// パスワード送信後にログインするための画面呼び出しウィジェットID
	const PERSON_INFO_OPT_SPORTS = 'sports';		// 個人情報オプション(現在やっているスポーツ)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_mainMemberDb();
		
		// メール連携オブジェクト取得
		$this->ecMailObj = $this->gInstance->getObject(self::MAIL_OBJ_ID);
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
		if ($task == 'member_detail'){		// 詳細画面
			return 'admin_member_detail.tmpl.html';
		} else {
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
		if ($task == 'member_detail'){	// 詳細画面
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
		$pageNo = $request->trimValueOf('page');				// ページ番号
		if (empty($pageNo)) $pageNo = 1;
		$this->memberType	= $request->trimValueOf('member_type');				// 会員タイプ
		
		// デフォルト値を取得
		$maxListCount = self::DEFAULT_LIST_COUNT;
		$serializedParam = $this->_db->getWidgetParam($this->gEnv->getCurrentWidgetId());
		if (!empty($serializedParam)){
			$dispInfo = unserialize($serializedParam);
			$maxListCount = $dispInfo->maxMemberListCountByAdmin;		// 会員リスト最大表示数
		}
		$act = $request->trimValueOf('act');
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
				for ($i = 0; $i < count($delItems); $i++){
					$ret = $this->deleteUser($delItems[$i]);
					if (!$ret) break;
				}
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		// 会員タイプ
		if (empty($this->memberType)){		// 正会員のとき
			$this->tmpl->addVar("_widget", "member_regular", 'selected');
		} else {
			$this->tmpl->addVar("_widget", "member_tmp", 'selected');
		}
		
		// ###### 会員一覧を作成 #####
		if (empty($this->memberType)){		// 正会員のとき
			// 総数を取得
			$totalCount = $this->db->getMemberCount(0);
		} else {
			// 総数を取得
			$totalCount = $this->db->getMemberCount(1);
		}
		
		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
		
		if (empty($this->memberType)){		// 正会員のとき
			// 会員リストを表示
			$this->db->getMemberList(0, $maxListCount, ($pageNo -1) * $maxListCount, array($this, 'memberListLoop'));
		} else {
			// 仮会員リストを表示
			$this->db->getMemberList(1, $maxListCount, ($pageNo -1) * $maxListCount, array($this, 'memberListLoop'));
		}
		if (empty($this->serialArray)) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 会員リストがないときは、一覧を表示しない
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				//$linkUrl = $this->currentPageUrl . '&category=' . $this->categoryId . '&page=' . $i;
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					//$link = '&nbsp;<a href="' . $linkUrl . '" >' . $i . '</a>';
					$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "page", $pageNo);		// 現在のページ番号
		
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
		// ユーザ情報、表示言語
		$now = date("Y/m/d H:i:s");	// 現在日時
		$countryId = self::DEFAULT_COUNTRY_ID;			// デフォルト国ID
				
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->memberType	= $request->trimValueOf('member_type');				// 会員タイプ
		$memberNo			= $request->trimValueOf('member_no');			// 会員No
		$email				= $request->trimValueOf('email');			// eメール(ログインアカウント)
		$mobile				= $request->trimValueOf('mobile');			// 携帯電話
		$familyName			= $request->trimValueOf('family_name');		// 会員名(姓)
		$firstName			= $request->trimValueOf('first_name');		// 会員名(名)
		$familyNameKana		= $request->trimValueOf('family_name_kana');		// 会員名カナ(姓)
		$firstNameKana		= $request->trimValueOf('first_name_kana');		// 会員名カナ(名)
		$zipcode			= $request->trimValueOf('zipcode');				// 郵便番号
		$this->state		= $request->trimValueOf('state');					// 都道府県
		$address			= $request->trimValueOf('address');			// 住所
		$address2			= $request->trimValueOf('address2');			// 住所2
		$phone				= $request->trimValueOf('phone');			// 電話番号
		$fax				= $request->trimValueOf('fax');				// FAX
		
		// フォトギャラリー追加分
		if (photo_shopCommonDef::MEMBER_INFO_OPTION){
			$this->gender			= $request->trimValueOf('gender');			// 性別
			$birthday			= $request->trimValueOf('birthday');
			if (!empty($birthday)) $birthday = $this->convertToProperDate($birthday);			// 生年月日
			$sports = $request->trimValueOf('item_sports');			// 現在やってるスポーツ
		}
		
		//$withLoginUser		= ($request->trimValueOf('withloginuser') == 'on') ? 1 : 0;		// ログインアカウントも削除するかどうか
		$withLoginUser = 1;			// 常にログインアカウントは削除に設定
				
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($familyName, '会員名(姓)');
			$this->checkInput($firstName, '会員名(名)');
			$this->checkInput($familyNameKana, '会員名カナ(姓)');
			$this->checkInput($firstNameKana, '会員名カナ(名)');
			$this->checkMailAddress($email, 'Eメール');
			
			// フォトギャラリー追加分
			if (photo_shopCommonDef::MEMBER_INFO_OPTION){
				$this->checkDate($birthday, '生年月日', true);
			}
			
			// メールアドレスが登録済みかチェック
			if ($this->_db->isExistsAccount($email)){// メールアドレスがログインIDとして既に登録されているかチェック
				$this->setAppErrorMsg('このEメールアドレスは既に登録されています');
			}
			// 会員NOの重複チェック
			if ($this->db->isExistsMemberNo($memberNo)){
				$this->setAppErrorMsg('この会員Noは既に登録されています');
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// パスワードは自動生成
				$password = $this->makePassword();
				
				// トランザクションスタート
				$this->db->startTransaction();

				// ログインユーザを作成
				$ret = $this->db->addUser(1/* 正会員 */, $familyName . $firstName, $email, $password, $this->gEnv->getCurrentWidgetId(), $this->_userId, $now, $loginUserId);		// 新規ログインユーザIDを取得

				// 住所登録
				if ($ret) $ret = $this->db->updateAddress(0, $this->_langId, '', $zipcode, $this->state, $address, $address2, $phone, $fax, $countryId, $this->_userId, $now, $addressId);

				// 個人情報登録
				$personOptions = array();
				if (photo_shopCommonDef::MEMBER_INFO_OPTION){
					$personOptions[self::PERSON_INFO_OPT_SPORTS] = $sports;
					if (empty($birthday)) $birthday = $this->gEnv->getInitValueOfDate();
				} else {
					$birthday = $this->gEnv->getInitValueOfDate();
					$this->gender = 0;
				}
				if ($ret) $ret = $this->db->updatePersonInfo(0, $this->_langId, $firstName, $familyName, $firstNameKana, $familyNameKana, $this->gender, $birthday, $email, $mobile, $addressId, $this->_userId, $now, $personalInfoId, $personOptions);

				// 会員情報を登録
				if ($ret) $ret = $this->db->updateMember(0, $this->_langId, 1/* 個人 */, 0/* 法人情報ID */, $personalInfoId, $memberNo, $loginUserId, $this->_userId, $now, $newSerial);

				// トランザクション終了
				$ret = $this->db->endTransaction();
				if ($ret){
					$this->setGuidanceMsg('会員を追加しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$replaceNew = true;			// 会員情報を再取得
					
					// パスワード変更のメッセージ
					$this->tmpl->addVar("_widget", "pwd_message", '新規パスワード: ' . $password);
					
					// ######## 会員登録のメールをイントラネット側に送信 ########
					//$stateName = $this->db->getStateName('JPN', $this->_langId, $this->state);			// 都道府県
					//$address1 = $stateName . $address;
					//$this->sendMailToBackoffice(0/*新規登録*/, $memberNo, $email, $familyName . $firstName, $familyNameKana . $firstNameKana, $zipcode, $address1, $address2, $phone);
					$this->ecMailObj->sendMemberInfoToBackoffice(0/*新規登録*/, $loginUserId);
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($familyName, '会員名(姓)');
			$this->checkInput($firstName, '会員名(名)');
			$this->checkInput($familyNameKana, '会員名カナ(姓)');
			$this->checkInput($firstNameKana, '会員名カナ(名)');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// トランザクションスタート
				$this->db->startTransaction();

				// 登録済み会員情報を取得
				$ret = $this->db->getMemberBySerial(0, $this->serialNo, $row);
				if ($ret){
					$personId = $row['sm_person_info_id'];
					$loginUserId = $row['sm_login_user_id'];
					$ret = $this->db->getPersonInfo($personId, $row, $personOptions);
					if ($ret){
						$addressId = $row['pi_address_id'];
						$email = $row['pi_email'];
					}
				}
				// 住所登録
				if ($ret) $ret = $this->db->updateAddress($addressId, $this->_langId, '', $zipcode, $this->state, $address, $address2, $phone, $fax, $countryId, $this->_userId, $now, $addressId);

				// 個人情報登録
				$personOptions = array();
				if (photo_shopCommonDef::MEMBER_INFO_OPTION){
					$personOptions[self::PERSON_INFO_OPT_SPORTS] = $sports;
					if (empty($birthday)) $birthday = $this->gEnv->getInitValueOfDate();
				} else {
					$birthday = $this->gEnv->getInitValueOfDate();
					$this->gender = 0;
				}
				if ($ret) $ret = $this->db->updatePersonInfo($personId, $this->_langId, $firstName, $familyName, $firstNameKana, $familyNameKana, $this->gender, $birthday, $email, $mobile, $addressId, $this->_userId, $now, $personalInfoId, $personOptions);

				// 会員情報更新
				if ($ret) $ret = $this->db->updateMember($this->serialNo, $this->_langId, 1/* 個人 */, 0/* 法人情報ID */, $personalInfoId, $memberNo, $loginUserId, $this->_userId, $now, $newSerial);
			
				// ### ログインユーザ名を更新 ###
				if ($ret){
					$fieldArray = array();
					$fieldArray['lu_name'] = $familyName . $firstName;		// 名前を更新
					$ret = $this->_db->updateLoginUserByField($loginUserId, $fieldArray, $newSerial);
				}
				
				// トランザクション終了
				$ret = $this->db->endTransaction();
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					// 登録済みのカテゴリーを取得
					$this->serialNo = $newSerial;
					$replaceNew = true;			// 会員情報を再取得
					
					// ######## 会員登録のメールをイントラネット側に送信 ########
					//$stateName = $this->db->getStateName('JPN', $this->_langId, $this->state);			// 都道府県
					//$address1 = $stateName . $address;
					//$this->sendMailToBackoffice(1/*更新*/, $memberNo, $email, $familyName . $firstName, $familyNameKana . $firstNameKana, $zipcode, $address1, $address2, $phone);
					$this->ecMailObj->sendMemberInfoToBackoffice(1/*更新*/, $loginUserId);
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除する会員が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->deleteUser($this->serialNo);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					
					// ######## 会員削除のメールをイントラネット側に送信 ########
					//$this->sendMailToBackoffice(2/*削除*/, $memberNo, $email, $familyName . $firstName, $familyNameKana . $firstNameKana, $zipcode, $address1, $address2, $phone);
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'search'){		// 検索再実行
		} else if ($act == 'sendpassword'){	// パスワードを送信
			$useEmail = self::$_mainDb->getConfig(self::USE_EMAIL);
			if ($useEmail == '1'){		// メール送信可能のとき
				// 登録済み会員情報を取得
				if (empty($this->memberType)){		// 正会員のとき
					$ret = $this->db->getMemberBySerial(0, $this->serialNo, $row);
				} else {
					$ret = $this->db->getMemberBySerial(1, $this->serialNo, $row);
				}
				if ($ret){
					if (empty($this->memberType)){		// 正会員のとき
						$loginUserId = $row['sm_login_user_id'];
					} else {
						$loginUserId = $row['sb_login_user_id'];
					}
					// パスワードを送信するときは、常に再作成する
					$password = $this->makePassword();// パスワード作成
					
					// パスワード変更
					$ret = $this->_db->updateLoginUserPassword($loginUserId, $password);
					if ($ret){
						//$fromAddress = self::$_mainDb->getConfig(self::AUTO_EMAIL_SENDER);	// 自動送信送信元
						$fromAddress = $this->_getConfig(photo_shopCommonDef::CF_AUTO_EMAIL_SENDER);	// 自動送信送信元
						if (empty($fromAddress)) $fromAddress = $this->gEnv->getSiteEmail();// 送信元が取得できないときは、システムのデフォルトメールアドレスを使用
						$toAddress = $this->convertToDispString($row['pi_email']);			// eメール(ログインアカウント)
						
						//$loginParam = 'task=login&act=ec_maillogin&account=' . urlencode($toAddress) . '&pwd=' . urlencode($password);// ログイン用パラメータ
						//$url = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, ''/*送信元ウィジェット指定なし*/, $loginParam, $this->gEnv->getDefaultPageId());
						//$url = $this->gEnv->createCurrentPageUrl() . sprintf(photo_shopCommonDef::EMAIL_LOGIN_URL, urlencode($toAddress), urlencode($password));		// ログイン用URL
						//$url = $this->gPage->getDefaultPageUrlByWidget($this->gEnv->getCurrentWidgetId(), 
						//				sprintf(photo_shopCommonDef::EMAIL_LOGIN_URL, urlencode($toAddress), urlencode($password)));		// ログイン用URL
						$url = photo_shopCommonDef::createLoginUrl($toAddress, $password);		// ログイン用URL
						$mailParam = array();
						$mailParam['PASSWORD'] = $password;
						$mailParam['URL']		= $this->getUrl($url, true);		// ログイン用URL
						$mailParam['SIGNATURE']	= self::$_mainDb->getCommerceConfig(photo_shopCommonDef::CF_E_SHOP_SIGNATURE);	// ショップメール署名
						$ret = $this->gInstance->getMailManager()->sendFormMail(2/*手動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, '', '',
																				photo_shopCommonDef::MAIL_FORM_SEND_PASSWORD, $mailParam);// 手動送信
																				
						// パスワード変更のメッセージ
						$this->tmpl->addVar("_widget", "pwd_message", '新規パスワード: ' . $password);
					}
				}

				if ($ret){
					$this->setGuidanceMsg('メール送信完了しました');
				} else {
					$this->setAppErrorMsg('メール送信に失敗しました');
				}
			} else {
				$this->setAppErrorMsg('メール送信できません');
			}
			$replaceNew = true;			// 会員情報を再取得
		} else {	// 初期表示
			// 会員IDが指定されている場合は正会員情報を表示
			$memberId = $request->trimValueOf('member');			// 会員ID
			if (empty($memberId)){
				if (empty($this->serialNo)){		// 新規登録のとき
					// 入力値初期化
					$this->memberType = 0;			// 正会員のみ新規登録可能
					$memberNo = '';			// 会員No
					$email = '';			// eメール(ログインアカウント)
					$mobile = '';			// 携帯電話
					$familyName = '';		// 会員名(姓)
					$firstName = '';		// 会員名(名)
					$familyNameKana = '';		// 会員名カナ(姓)
					$firstNameKana = '';		// 会員名カナ(名)
					$zipcode = '';				// 郵便番号
					$this->state = 0;					// 都道府県
					$address = '';			// 住所
					$address2 = '';			// 住所2
					$phone = '';			// 電話番号
					$fax = '';				// FAX
					$updateUser = '';	// 更新者
					$updateDt = '';	// 更新日時
					
					$this->gender = 0;			// 性別
					$birthday = '';			// 生年月日
			
					// 会員NOのデフォルト値を自動生成
					$memberNo = $this->gInstance->getObject(self::EC_LIB_ID)->generateMemberNo();
				} else {
					$replaceNew = true;			// 会員情報を再取得
				}
			} else {
				$this->serialNo = $this->db->getMemberSerialById($memberId);
				if (!empty($this->serialNo)) $replaceNew = true;			// 会員情報を再取得
				$this->memberType = 0;			// 正会員
			}
		}
		// 会員情報を再取得
		if ($replaceNew){
			// 指定会員の情報を取得
			if (empty($this->memberType)){		// 正会員のとき
				// 登録済み会員情報を取得
				$ret = $this->db->getMemberBySerial(0, $this->serialNo, $row);
			} else {		// 仮会員のとき
				// 登録済み会員情報を取得
				$ret = $this->db->getMemberBySerial(1, $this->serialNo, $row);
			}
			if ($ret){
				// 取得値を設定
				$email = $row['pi_email'];			// eメール(ログインアカウント)
				$mobile = $row['pi_mobile'];			// 携帯電話
				$familyName = $row['pi_family_name'];		// 会員名(姓)
				$firstName = $row['pi_first_name'];		// 会員名(名)
				$familyNameKana = $row['pi_family_name_kana'];		// 会員名カナ(姓)
				$firstNameKana = $row['pi_first_name_kana'];		// 会員名カナ(名)
				$zipcode = $row['ad_zipcode'];				// 郵便番号
				$this->state = $row['ad_state_id'];					// 都道府県
				$address = $row['ad_address1'];			// 住所
				$address2 = $row['ad_address2'];			// 住所2
				$phone = $row['ad_phone'];			// 電話番号
				$fax = $row['ad_fax'];				// FAX				
				$updateUser = $row['lu_name'];	// 更新者
				if (photo_shopCommonDef::MEMBER_INFO_OPTION){
					$this->gender = $row['pi_gender'];			// 性別
					$birthday = $row['pi_birthday'];			// 生年月日
				}
				
				if (empty($this->memberType)){		// 正会員のとき
					$memberNo = $row['sm_member_no'];			// 会員No
					$updateDt = $row['sm_create_dt'];	// 更新日時
				} else {
					$memberNo = $row['sb_member_no'];			// 会員No
					$updateDt = $row['sb_create_dt'];	// 更新日時
				}
				
				// 個人情報取得
				$personId = $row['pi_id'];
				$ret = $this->db->getPersonInfo($personId, $row, $personOptions);
				if ($ret){
					// フォトギャラリー追加分
					if (photo_shopCommonDef::MEMBER_INFO_OPTION){
						$sports	= $personOptions[self::PERSON_INFO_OPT_SPORTS];		// 現在やってるスポーツ
					}
				}
			}
		}
		// フォトギャラリー追加分
		if (photo_shopCommonDef::MEMBER_INFO_OPTION){
			$this->tmpl->setAttribute('script_member_info_option', 'visibility', 'visible');		// スクリプト表示
			$this->tmpl->setAttribute('show_member_info_option', 'visibility', 'visible');
		}
		
		// 都道府県を設定
		$this->db->getAllState('JPN', $this->_langId, array($this, 'stateLoop'));
		
		// #### 更新、新規登録部をを作成 ####
		$this->tmpl->addVar("_widget", "member_no", $this->convertToDispString($memberNo));		// 会員No
		$this->tmpl->addVar("_widget", "email", $this->convertToDispString($email));		// eメール(ログインアカウント)
		$this->tmpl->addVar("_widget", "mobile", $this->convertToDispString($mobile));		// 携帯電話
		$this->tmpl->addVar("_widget", "family_name", $this->convertToDispString($familyName));		// 会員名(姓)
		$this->tmpl->addVar("_widget", "first_name", $this->convertToDispString($firstName));		// 会員名(名)
		$this->tmpl->addVar("_widget", "family_name_kana", $this->convertToDispString($familyNameKana));		// 会員名カナ(姓)
		$this->tmpl->addVar("_widget", "first_name_kana", $this->convertToDispString($firstNameKana));		// 会員名カナ(名)
		$this->tmpl->addVar("_widget", "zipcode", $this->convertToDispString($zipcode));		// 郵便番号
		$this->tmpl->addVar("_widget", "address", $this->convertToDispString($address));		// 住所
		$this->tmpl->addVar("_widget", "address2", $this->convertToDispString($address2));		// 住所2
		$this->tmpl->addVar("_widget", "phone", $this->convertToDispString($phone));		// 電話番号
		$this->tmpl->addVar("_widget", "fax", $this->convertToDispString($fax));		// FAX
		$this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($updateUser));	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $this->convertToDispDateTime($updateDt));	// 更新日時
		
		// フォトギャラリー追加分
		if (photo_shopCommonDef::MEMBER_INFO_OPTION){
			// 性別
			if ($this->gender == 0){		// 未設定のとき
				$this->tmpl->addVar("show_member_info_option", "gender_none", 'selected');
			} else if ($this->gender == 1){
				$this->tmpl->addVar("show_member_info_option", "gender_male", 'selected');
			} else {
				$this->tmpl->addVar("show_member_info_option", "gender_female", 'selected');
			}
			$this->tmpl->addVar("show_member_info_option", "birthday", $this->convertToDispDate($birthday));			// 生年月日
				
			// フォトギャラリー追加分
			$this->tmpl->addVar("show_member_info_option", "sports", $sports);		// 現在やっているスポーツ
		}
		
		if (!empty($this->memberType)){		// 仮会員のときは変更不可
			$this->tmpl->addVar("_widget", "member_no_disabled", 'disabled');		// 会員No
			$this->tmpl->addVar("_widget", "email_disabled", 'readonly');		// eメール(ログインアカウント)
			$this->tmpl->addVar("_widget", "mobile_disabled", 'disabled');		// 携帯電話
			$this->tmpl->addVar("_widget", "family_name_disabled", 'disabled');		// 会員名(姓)
			$this->tmpl->addVar("_widget", "first_name_disabled", 'disabled');		// 会員名(名)
			$this->tmpl->addVar("_widget", "family_name_kana_disabled", 'disabled');		// 会員名カナ(姓)
			$this->tmpl->addVar("_widget", "first_name_kana_disabled", 'disabled');		// 会員名カナ(名)
			$this->tmpl->addVar("_widget", "zipcode_disabled", 'disabled');		// 郵便番号
			$this->tmpl->addVar("_widget", "address_disabled", 'disabled');		// 住所
			$this->tmpl->addVar("_widget", "address2_disabled", 'disabled');		// 住所2
			$this->tmpl->addVar("_widget", "phone_disabled", 'disabled');		// 電話番号
			$this->tmpl->addVar("_widget", "fax_disabled", 'disabled');		// FAX
			$this->tmpl->addVar("_widget", "state_disabled", 'disabled');		// 都道府県
			
			if (photo_shopCommonDef::MEMBER_INFO_OPTION){
				$this->tmpl->addVar("show_member_info_option", "birthday_disabled", 'disabled');		// 生年月日
				$this->tmpl->addVar("show_member_info_option", "calender_disabled", 'disabled');		// カレンダー
				$this->tmpl->addVar("show_member_info_option", "gender_disabled", 'disabled');		// 性別
			}
		}
		
		// ボタンの設定
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			// 画面タイトル
			$this->tmpl->addVar("_widget", "title", '正会員詳細');
			
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
			
			// パスワード送信ボタンの使用制御
			//$this->tmpl->addVar("_widget", "password_disabled", 'disabled');
			$this->tmpl->addVar("_widget", "send_pwd", 'disabled');
		} else {
			// 画面タイトル
			if (empty($this->memberType)){		// 正会員
				$this->tmpl->addVar("_widget", "title", '正会員詳細');
			} else {
				$this->tmpl->addVar("_widget", "title", '仮会員詳細');
			}
			
			$this->tmpl->addVar("_widget", "email_disabled", 'readonly');		// eメール(ログインアカウント)
			
			// パスワード送信ボタンの使用制御
			//$this->tmpl->setAttribute('send_password', 'visibility', 'visible');
			
			// データ更新、削除ボタン表示
			$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');// 削除ボタン
			
			if (empty($this->memberType)){		// 正会員のときは変更可
				$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
			}
		}
		// メール送信可否状況によるボタンの制御
		$useEmail = self::$_mainDb->getConfig(self::USE_EMAIL);
		if ($useEmail != '1'){		// メール送信不可のとき
			$this->tmpl->addVar("_widget", "send_message", 'メール送信不可に設定されています<br>');
			$this->tmpl->addVar("_widget", "send_pwd", 'disabled');
		}
		//$this->tmpl->addVar("send_password", "recreate_pwd", 'checked readonly');		// パスワード再作成は常にオン
		
		// 値を埋め込む
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "member_type", $this->memberType);

		// 「戻る」ボタンの表示
		if ($this->_openBy == 'simple' || $this->_openBy == 'tabs') $this->tmpl->setAttribute('cancel_button', 'visibility', 'hidden');		// 詳細画面のみの表示またはタブ表示のときは戻るボタンを隠す
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function memberListLoop($index, $fetchedRow, $param)
	{
		// 項目選択のラジオボタンの状態
		if (empty($this->memberType)){
			$serial = $this->convertToDispString($fetchedRow['sm_serial']);
		} else {
			$serial = $this->convertToDispString($fetchedRow['sb_serial']);
		}
		$selected = '';
		if ($serial == $this->serialNo){
			$selected = 'checked';
		}
		// 性別
		$gender = '';
		if ($fetchedRow['pi_gender'] == 1){
			$gender = '男';
		} else if ($fetchedRow['pi_gender'] == 2){
			$gender = '女';
		} else {
			$gender = '未';
		}
		// 都道府県名
		$stateName = '未';
		if (!empty($fetchedRow['gz_name'])) $stateName = $this->convertToDispString($fetchedRow['gz_name']);
		$row = array(
			'line_color' => $lineColor,						// 行のカラー
			'no' => $this->firstNo + $index,				// 行番号
			'index' => $index,								// 項目番号
			'serial' => $serial,	// シリアル番号
			'id' => $id,			// ID
			'member_no' => $this->convertToDispString($fetchedRow['sm_member_no']),			// 会員No
			'family_name' => $this->convertToDispString($fetchedRow['pi_family_name']),	// 顧客名(姓)
			'first_name' => $this->convertToDispString($fetchedRow['pi_first_name']),	// 顧客名(名)
			'family_name_kana' => $this->convertToDispString($fetchedRow['pi_family_name_kana']),	// 顧客名カナ(姓)
			'first_name_kana' => $this->convertToDispString($fetchedRow['pi_first_name_kana']),	// 顧客名カナ(名)
			'email' => $this->convertToDispString($fetchedRow['pi_email']),	// Eメールアドレス
			'gender' => $gender,			// 性別
			'state_name' => $stateName,	// 都道府県
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
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
	 * @param int $serial		シリアル番号
	 * @return bool				true=成功、false=失敗
	 */
	function deleteUser($serial)
	{
		$loginUserId = '';
		
		// ログインユーザIDを取得
		if (empty($this->memberType)){		// 正会員のとき
			if ($this->db->getMemberBySerial(0, $serial, $row)) $loginUserId = $row['sm_login_user_id'];
		} else {
			if ($this->db->getMemberBySerial(1, $serial, $row)) $loginUserId = $row['sb_login_user_id'];
		}
		if (empty($this->memberType)){		// 正会員のとき
			$ret = $this->db->delMemberBySerial(0, $serial);
		} else {
			$ret = $this->db->delMemberBySerial(1, $serial);
		}
		if ($ret) $this->_db->delLoginUser($loginUserId);

		if ($ret){		// データ削除成功のとき
			return true;
		} else {
			return false;
		}
	}
}
?>
