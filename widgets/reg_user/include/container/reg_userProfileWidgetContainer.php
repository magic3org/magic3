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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/reg_userBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/reg_userDb.php');
require_once($gEnvManager->getLibPath()			. '/qqFileUploader/fileuploader.php');

class reg_userProfileWidgetContainer extends reg_userBaseWidgetContainer
{
	const DEFAULT_TITLE = 'プロフィール';		// 画面タイトル
	private $db;	// DB接続オブジェクト
	private $genderArray;	// 性別選択メニュー用
	private $gender;	// 性別
	private $year;		// 生年月日(年)
	private $month;	// 生年月日(月)
	private $day;		// 生年月日(日)
	private $sessionParamObj;		// セッション保存データ
	const FILE_UPLOAD_SCRIPT_FILE	= '/fileuploader/fileuploader.js';		// ファイルアップロードクリプトファイル
	const FILE_UPLOAD_CSS_FILE		= '/fileuploader/fileuploader.css';		// ファイルアップロードCSSファイル
//	const AVATAR_DIR = '/etc/avatar/';		// アバターディレクトリ
//	const DEFAULT_AVATAR_BASE = 'default_';		// デフォルトのアバターファイル名ヘッド部
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new reg_userDb();
		
		// 性別選択メニュー項目
		$this->genderArray = array(	array(	'name' => '男',	'value' => '1'),
									array(	'name' => '女',	'value' => '2'));
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
		if ($this->_renderType == M3_RENDER_BOOTSTRAP){			// Bootstrap型テンプレートのとき
			return 'profile_bootstrap.tmpl.html';
		} else {
			return 'profile.tmpl.html';
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
		$now = date("Y/m/d H:i:s");	// 現在日時
		$avatarFormat = $this->gInstance->getImageManager()->getDefaultAvatarFormat();		// 画像フォーマット取得
		
		// ##### セッションパラメータ取得 #####
		$this->sessionParamObj = $this->getWidgetSessionObj();		// セッション保存パラメータ
		if (empty($this->sessionParamObj)){			// 空の場合は作成
			$this->sessionParamObj = new stdClass;		
			$this->sessionParamObj->uploadFile = '';		// アップロードしたファイル
			$this->sessionParamObj->avatarFile = '';		// アバターファイル
		}
		
		// 入力値を取得
		$act = $request->trimValueOf('act');
		$name = $request->trimValueOf('item_name');			// 名前
		$this->gender = $request->trimValueOf('item_gender');		// 性別
		$this->year = $request->trimValueOf('item_year');		// 生年月日(年)
		$this->month = $request->trimValueOf('item_month');	// 生年月日(月)
		$this->day = $request->trimValueOf('item_day');		// 生年月日(日)
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){			// 会員情報更新
			$this->checkInput($name, '名前');
//			$this->checkInput($this->gender, '性別');
//			if (empty($this->year) || empty($this->month) || empty($this->day)) $this->setUserErrorMsg('生年月日が入力されていません');
			
			// エラーなしの場合は、更新
			if ($this->getMsgCount() == 0){
				// 旧アバターを取得
				$avatar = '';
				$ret = $this->_db->getLoginUserRecordById($this->_userId, $userRow);
				if ($ret) $avatar = $userRow['lu_avatar'];		// アバター
					
				if (!empty($this->sessionParamObj->avatarFile)){
					// 旧アバターを削除
					if (!empty($avatar)){
						//$avatarPath = $this->gEnv->getResourcePath() . self::AVATAR_DIR . $avatar;
						$avatarPath = $this->gInstance->getImageManager()->getAvatarPath($avatar);
						unlink($avatarPath);
					}
					
					// 新アバター画像を移動
					$avatar = basename($this->sessionParamObj->avatarFile);
					//$avatarPath = $this->gEnv->getResourcePath() . self::AVATAR_DIR . $avatar;
					$avatarPath = $this->gInstance->getImageManager()->getAvatarPath($avatar);
					renameFile($this->sessionParamObj->avatarFile, $avatarPath);
				}
				
				// セッションの画像情報をクリア
				$this->resetUploadImage();
		
				// トランザクションスタート
				$this->db->startTransaction();

				// ログインユーザ情報を更新
				$fieldArray = array();
				$fieldArray['lu_name'] = $name;				// 名前
				$fieldArray['lu_avatar'] = $avatar;			// アバター
				$ret = $this->_db->updateLoginUserByField($this->_userId, $fieldArray, $newSerial);
					
				// ユーザ情報を更新
				if ($ret){
					if (!empty($this->year) && !empty($this->month) && !empty($this->day)) $birthday = $this->convertToProperDate($this->year . '/' . $this->month . '/' . $this->day);
					
					if (!empty($this->gender) || !empty($birthday)){
						$fieldArray = array();
						if (!empty($this->gender)) $fieldArray['li_gender'] = $this->gender;				// 性別
						if (!empty($birthday)) $fieldArray['li_birthday'] = $birthday;			// 生年月日
						$ret = $this->_db->updateLoginUserInfoByField($this->_userId, $fieldArray, $newSerial);
					}
				}
													
				// トランザクション終了
				$ret = $this->db->endTransaction();
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					$replaceNew = true;			// 会員情報を再取得
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'uploadfile'){		// 添付ファイルアップロード
			$uploader = new qqFileUploader(array());
			$tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
			$resultObj = $uploader->handleUpload($tmpDir);
			
			if ($resultObj['success']){
				$fileInfo = $resultObj['file'];
				$ret = $this->gInstance->getImageManager()->createImageByFormat($fileInfo['path'], $avatarFormat, dirname($fileInfo['path']), $fileInfo['fileid'], $destFilename);
				if ($ret){
					// 旧画像を削除
					$this->resetUploadImage();
					
					// 新規画像を登録
					$this->sessionParamObj->uploadFile = $fileInfo['path'];		// アップロードしたファイル
					$this->sessionParamObj->avatarFile = dirname($fileInfo['path']) . DIRECTORY_SEPARATOR . $destFilename[0];		// アバターファイル
					$this->setWidgetSessionObj($this->sessionParamObj);
					
					// アバター画像URL
					$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
					$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
					$urlparam .= M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_PROFILE . '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'getavatar&' . date('YmdHis');
					$avatarUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;
					$resultObj['avatar'] = $avatarUrl;
				} else {			// 画像作成失敗のとき
					unlink($fileInfo['path']);
					$resultObj = array('error' => 'Could not create file information.');
				}
			}
			// ##### 添付ファイルアップロード結果を返す #####
			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// 添付ファイルの登録データを返す
			if (function_exists('json_encode')){
				$destStr = json_encode($resultObj);
			} else {
				$destStr = $this->gInstance->getAjaxManager()->createJsonString($resultObj);
			}
			//$destStr = htmlspecialchars($destStr, ENT_NOQUOTES);		// 「&」が「&amp;」に変換されるので使用しない
			//header('Content-type: application/json; charset=utf-8');
			header('Content-Type: text/html; charset=UTF-8');		// JSONタイプを指定するとIE8で動作しないのでHTMLタイプを指定
			echo $destStr;
			
			// システム強制終了
			$this->gPage->exitSystem();
		} else if ($act == 'getavatar'){		// アバター画像取得
			if (empty($this->sessionParamObj->avatarFile)) return;
			
			// ページ作成処理中断
			$this->gPage->abortPage();
		
			$ret = $this->gPage->downloadFile($this->sessionParamObj->avatarFile, basename($this->sessionParamObj->avatarFile));
		
			// システム強制終了
			$this->gPage->exitSystem();
		} else {		// 初期状態のとき
			$replaceNew = true;			// 会員情報を再取得
			
			// アップロードファイル初期化
			$this->resetUploadImage();
		}

		if ($replaceNew){		// 会員情報を取得のとき
			// ユーザ情報を取得
			$ret = $this->_db->getLoginUserRecordById($this->_userId, $userRow);
			if ($ret){
				$account	= $userRow['lu_account'];
				$name		= $userRow['lu_name'];
				$avatar		= $userRow['lu_avatar'];		// アバター
							
				$this->gender	= $userRow['li_gender'];
				$this->timestampToYearMonthDay($userRow['li_birthday'], $this->year, $this->month, $this->day);
			}
		} else {
			// アカウントは毎回取得
			$ret = $this->_db->getLoginUserRecordById($this->_userId, $userRow);
			if ($ret){
				$account	= $userRow['lu_account'];
			}
		}
		
		// 性別選択メニュー作成
		$this->createGenderMenu();
		
		// 生年月日メニュー作成
		$this->createBirthMenu();
		
		// アップロード実行用URL
		$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
		$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
		$urlparam .= M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_PROFILE . '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . 'uploadfile';
		$uploadUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;
		$this->tmpl->addVar("_widget", "upload_url", $this->getUrl($uploadUrl));
		
		// アバター
		$this->gInstance->getImageManager()->parseImageFormat($avatarFormat, $imageType, $imageAttr, $imageSize);		// 画像情報取得
		$avatarUrl = $this->gInstance->getImageManager()->getAvatarUrl($avatar);
/*		if (empty($avatar)){
			$avatarUrl = $this->gEnv->getResourceUrl() . self::AVATAR_DIR . self::DEFAULT_AVATAR_BASE . $avatarFormat;
		} else {
			$avatarUrl = $this->gEnv->getResourceUrl() . self::AVATAR_DIR . $avatar;
		}*/
		$iconTitle = 'アバター画像';
		$avatarTag = '<img id="avatar" class="avatar" src="' . $this->getUrl($avatarUrl) . '" width="' . $imageSize . '" height="' . $imageSize . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// 入力値を戻す
		$this->tmpl->addVar("_widget", "account", $this->convertToDispString($account));
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "image", $avatarTag);
	}
	/**
	 * JavascriptファイルをHTMLヘッダ部に設定
	 *
	 * JavascriptファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						Javascriptファイル。出力しない場合は空文字列を設定。
	 */
	function _addScriptFileToHead($request, &$param)
	{
		$scriptArray = array($this->getUrl($this->gEnv->getScriptsUrl() . self::FILE_UPLOAD_SCRIPT_FILE));	// カレンダーセットアップファイル
		return $scriptArray;

	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		//return array(parent::_addCssFileToHead($request, $param), $this->getUrl($this->gEnv->getScriptsUrl() . self::FILE_UPLOAD_CSS_FILE));
		$parentCss = parent::_addCssFileToHead($request, $param);
		if (is_array($parentCss)){
			return array_merge($parentCss, array($this->getUrl($this->gEnv->getScriptsUrl() . self::FILE_UPLOAD_CSS_FILE)));
		} else {
			return array($parentCss, $this->getUrl($this->gEnv->getScriptsUrl() . self::FILE_UPLOAD_CSS_FILE));
		}
	}
	/**
	 * 性別選択メニュー作成
	 *
	 * @return なし
	 */
	function createGenderMenu()
	{
		for ($i = 0; $i < count($this->genderArray); $i++){
			$value = $this->genderArray[$i]['value'];
			$name = $this->genderArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->gender) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('gender_list', $row);
			$this->tmpl->parseTemplate('gender_list', 'a');
		}
	}
	/**
	 * 生年月日メニュー作成
	 *
	 * @return なし
	 */
	function createBirthMenu()
	{
		$nowYear = date("Y");	// 現在年
		$startYear = $nowYear - 100;
		for ($i = $startYear; $i < $nowYear; $i++){
			$value = $name = $i;
			
			$selected = '';
			if ($value == $this->year) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 表示タイトル
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('year_list', $row);
			$this->tmpl->parseTemplate('year_list', 'a');
		}
		
		for ($i = 1; $i <= 12; $i++){
			$value = $name = $i;
			
			$selected = '';
			if ($value == $this->month) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 表示タイトル
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('month_list', $row);
			$this->tmpl->parseTemplate('month_list', 'a');
		}
		
		for ($i = 1; $i <= 31; $i++){
			$value = $name = $i;
			
			$selected = '';
			if ($value == $this->day) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 表示タイトル
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('day_list', $row);
			$this->tmpl->parseTemplate('day_list', 'a');
		}
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
	/**
	 * アップロード画像初期化
	 *
	 * @return なし
	 */
	function resetUploadImage()
	{
		if (!empty($this->sessionParamObj->uploadFile)){		// アップロードしたファイル
			if (file_exists($this->sessionParamObj->uploadFile)) unlink($this->sessionParamObj->uploadFile);
		}
		if (!empty($this->sessionParamObj->avatarFile)){		// アバターファイル
			if (file_exists($this->sessionParamObj->avatarFile)) unlink($this->sessionParamObj->avatarFile);
		}
		$this->sessionParamObj->uploadFile = '';		// アップロードしたファイル
		$this->sessionParamObj->avatarFile = '';		// アバターファイル
		
		$this->setWidgetSessionObj($this->sessionParamObj);
	}
}
?>
