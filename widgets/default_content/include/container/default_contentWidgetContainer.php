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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('default_content') . '/default_contentBaseWidgetContainer.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class default_contentWidgetContainer extends default_contentBaseWidgetContainer
{
	private $viewMode;					// 画面表示モード
	private $_contentCreated;	// コンテンツが取得できたかどうか
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $sessionParamObj;		// セッションパラメータ
	private $currentDay;		// 現在日
	private $currentHour;		// 現在時間
	private $headTitle;		// METAタグタイトル
	private $headDesc;		// METAタグ要約
	private $headKeyword;	// METAタグキーワード
	private $showTitle;			// コンテンツタイトルの表示制御
	private $showEdit;			// 編集ボタンの表示
	private $isSystemManageUser;	// システム運用可能ユーザかどうか
	private $pageTitle;				// 画面タイトル、パンくずリスト用タイトル
	private $editIconPos;			// 編集アイコンの位置
	private $usePassword;			// パスワードによるコンテンツ閲覧制限
	private $outputHead;			// ヘッダ出力するかどうか
	private $passwordFormCount;		// パスワード入力フォーム数
	private $inputPassword = false;			// パスワード入力かどうか
	private $jQueryMobileFormat;			// jQueryMobile用のフォーマットで出力するかどうか
	private $useJQuery;						// jQueryスクリプト作成を行うかどうか
	private $headScript;	// HTMLヘッダに埋め込むJavascript
	private $addLib = array();		// 追加スクリプトライブラリ
	private $autoGenerateAttachFileList;		// 添付ファイルリストを自動作成するかどうか
	private $attachFileRows;		// 添付ファイル情報(コンテンツ本文作成用)
	private $attachFileDownloadUrl;			// 添付ファイルのリンク先URL
	const DEFAULT_LIST_COUNT = 10;			// 最大リスト表示数
	const DEFAULT_SEARCH_LIST_COUNT = 20;			// 最大リスト表示数(検索用)
	const MESSAGE_NO_CONTENT		= 'コンテンツが見つかりません';
	const CONTENT_SIZE = 200;			// 検索結果コンテンツの文字列最大長
	const DEFAULT_MESSAGE_DENY = 'コンテンツを表示できません';		// アクセス不可の場合のメッセージ
	const ICON_SIZE = 32;		// アイコンのサイズ
	const DOWNLOAD_ICON_SIZE = 32;		// アイコンのサイズ
	const EDIT_ICON_FILE = '/images/system/page_edit32.png';		// 編集アイコン
	const NEW_ICON_FILE = '/images/system/page_add32.png';		// 新規アイコン
	const DOWNLOAD_ICON_FILE = '/images/system/download32.png';		// 添付ファイルダウンロードアイコン
	const DEFAULT_TITLE_SEARCH = 'コンテンツ検索';		// 検索時のデフォルトタイトル
	const DEFAULT_TITLE_SEARCH_RESULTS = 'コンテンツ検索結果';	// 検索実行後のデフォルトタイトル
	const EDIT_ICON_MIN_POS = 10;			// 編集アイコンの位置
	const EDIT_ICON_NEXT_POS = 35;			// 編集アイコンの位置
	const PASSWORD_FORM_NAME = 'check_password';		// パスワードチェック用フォーム名
	const LIB_MD5 = 'md5';
	const CONTENT_OBJ_ID	= 'contentlib';	// 汎用コンテンツオブジェクトID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// HTMLメタタグを初期化
		$this->headTitle = '';
		$this->headDesc = '';
		$this->headKeyword = '';
		
		$this->editIconPos = self::EDIT_ICON_MIN_POS;			// 編集アイコンの位置
		
		$this->usePassword = self::$_configArray[default_contentCommonDef::$CF_USE_PASSWORD];			// パスワードによるコンテンツ閲覧制限
		$this->outputHead = self::$_configArray[default_contentCommonDef::$CF_OUTPUT_HEAD];			// ヘッダ出力するかどうか
		$this->passwordFormCount = 0;		// パスワード入力フォーム数
		$this->useJQuery = self::$_configArray[default_contentCommonDef::$CF_USE_JQUERY];			// jQueryスクリプトを作成するかどうか
		$this->autoGenerateAttachFileList = self::$_configArray[default_contentCommonDef::CF_AUTO_GENERATE_ATTACH_FILE_LIST];		// 添付ファイルリストを自動作成するかどうか
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
		$act = $request->trimValueOf('act');
		$keyword = $request->trimValueOf(M3_REQUEST_PARAM_KEYWORD);// 検索キーワード
		
		if ($act == 'search' || !empty($keyword)){
			$this->viewMode = 'search';					// 画面表示モード(検索結果)
			return 'search.tmpl.html';
		} else {
			// Joomlaテンプレートのバージョンに合わせて出力
			$this->templateType = $this->gEnv->getCurrentTemplateType();
			if ($this->templateType == 0){			// Joomla!v1.0のとき
				return 'index_old.tmpl.html';
			} else {
				return 'index.tmpl.html';
			}
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
		// 現在日時を取得
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		$this->isSystemManageUser = $this->gEnv->isSystemManageUser();	// システム運用可能ユーザかどうか
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		
		// 履歴インデックス
		$historyIndex = -1;
		$value = $request->trimValueOf(M3_REQUEST_PARAM_HISTORY);		// 履歴インデックス
		if ($value != '') $historyIndex = intval($value);
		
		// 管理者でプレビューモードのときは表示制限しない
		$preview = false;
		if ($this->isSystemManageUser && $cmd == M3_REQUEST_CMD_PREVIEW){		// システム運用者以上
			$preview = true;
		}
		// プレビューでないときは履歴表示不可
		if (!$preview) $historyIndex = -1;
		
		// ログインユーザでないときは、ユーザ制限のない項目だけ表示
		$all = false;
		if ($this->gEnv->isCurrentUserLogined()) $all = true;
		
		// ##### セッションパラメータ取得 #####
		$this->sessionParamObj = $this->getWidgetSessionObj();		// セッション保存パラメータ
		if (empty($this->sessionParamObj)){
			$this->sessionParamObj = new stdClass;		// 空の場合は作成
			$this->sessionParamObj->authContentId = array();		// 参照可能なコンテンツID
		}
			
		// ウィジェットパラメータ取得
		$this->showTitle = 0;			// コンテンツタイトルを表示するかどうか
		$showMessageDeny = 1;					// アクセス不可の場合にメッセージを表示するかどうか
		$messageDeny = self::DEFAULT_MESSAGE_DENY;							// アクセス不可の場合のメッセージ
		$this->showEdit = 1;			// 編集ボタンを表示するかどうか
		// デバイスタイプごとの処理
		if (default_contentCommonDef::$_deviceType == 2){		// スマートフォンの場合
			$this->jQueryMobileFormat = 1;			// jQueryMobile用のフォーマットで出力するかどうか
		}
		
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->showTitle = $paramObj->showTitle;			// コンテンツタイトルを表示するかどうか
			$showMessageDeny = $paramObj->showMessageDeny;					// アクセス不可の場合にメッセージを表示するかどうか
			$messageDeny = $paramObj->messageDeny;							// アクセス不可の場合のメッセージ
			$this->showEdit = $paramObj->showEdit;			// 編集ボタンを表示するかどうか
			// デバイスタイプごとの処理
			if (default_contentCommonDef::$_deviceType == 2){		// スマートフォンの場合
				$this->jQueryMobileFormat = $paramObj->jQueryMobileFormat;			// jQueryMobile用のフォーマットで出力するかどうか
			}
		}
		// プレビューモードで履歴表示のときは編集ボタンを表示しない
		if ($preview && $historyIndex >= 0) $this->showEdit = 0;

		$this->pageTitle = '';		// 画面タイトル、パンくずリスト用タイトル
		$act = $request->trimValueOf('act');
		$contentId = $request->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID);
		if (empty($contentId)) $contentId = $request->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID_SHORT);		// 略式コンテンツID
		$keyword = $request->trimValueOf(M3_REQUEST_PARAM_KEYWORD);// 検索キーワード
		$password = $request->trimValueOf('password');// パスワード
		
		if ($cmd == M3_REQUEST_CMD_DO_WIDGET){	// ウィジェット単体実行
			$fileId = $request->trimValueOf('fileid');	// ファイルID
			$canDownload = false;			// ダウンロード可能かどうか

			// ファイルIDからコンテンツID取得
			// 多言語の場合の添付ファイル情報はデフォルト言語の添付ファイル情報を使用
			$contentId = '';
			$ret = $this->gInstance->getFileManager()->getAttachFileInfoByFileId(default_contentCommonDef::$_viewContentType, $fileId, $attachFileRow);
			if ($ret){
				$contentId = $attachFileRow[af_content_id];		// コンテンツID
				
				// コンテンツへのアクセス可能状況をチェック
				self::$_mainDb->getContentItems(default_contentCommonDef::$_contentType, array($this, 'checkItemsLoop'), array($contentId), $this->gEnv->getDefaultLanguage(), $all, $now, false);
				if ($this->_contentCreated){		// コンテンツにアクセスできるとき
					// パスワード入力のアクセス権はデフォルト言語でチェック
					$ret = self::$_mainDb->getContentByContentId(default_contentCommonDef::$_contentType, $contentId, $this->gEnv->getDefaultLanguage(), $defaultContentRow);
					if ($ret){
						// アクセス権のチェック
						$inputPassword = false;			// パスワード入力必要かどうか
						if ($this->usePassword && !empty($defaultContentRow['cn_password'])){			// パスワードによるコンテンツ閲覧制限、かつ、パスワードが設定されている
							// 認証状況をチェック
							if (!is_array($this->sessionParamObj->authContentId) || !in_array($contentId, $this->sessionParamObj->authContentId)) $inputPassword = true;	// パスワード入力かどうか
						}
						if (!$inputPassword) $canDownload = true;		// ダウンロード可能
						
						// 添付ファイルへのアクセス情報を取得
						$accessKey = $defaultContentRow['cn_attach_access_key'];		// アクセスキー
						$accessUrl = $defaultContentRow['cn_attach_access_url'];		// アクセスキー取得用URL
					}
				}
			}
			// ページ作成処理中断
			$this->gPage->abortPage();
							
			if ($canDownload){		// ダウンロード可能なとき
				// ##### 添付ファイルにアクセスキーが設定されている場合アクセス制御を行う #####
				$canDownloadAttachFile = false;			// 添付ファイルダウンロード不可
				$sessionAccessKey = '';
				if (empty($accessKey)){
					$canDownloadAttachFile = true;		// 添付ファイルダウンロード可
				} else {
					// セッションのアクセスキーを確認
					$sessionAccessKey = $this->gAccess->getSessionAccessKey($accessKey);
					if (!empty($sessionAccessKey)) $canDownloadAttachFile = true;		// 添付ファイルダウンロード可
				}
			
				if ($canDownloadAttachFile){		// 添付ファイルダウンロード可能な場合
					// 添付ファイル情報を取得
					$downloadCompleted = false;				// ダウンロード処理完了かどうか
					$contentSerial = $this->serialArray[0];		// アクセス可能なコンテンツのシリアル番号
					$ret = $this->gInstance->getFileManager()->getAttachFileInfo(default_contentCommonDef::$_viewContentType, $contentSerial, $attachFileRows);
					if ($ret){
						for ($i = 0; $i < count($attachFileRows); $i++){
							$fileRow = $attachFileRows[$i];
							if ($fileId == $fileRow['af_file_id']){
								$downloadFile = default_contentCommonDef::getAttachFileDir() . DIRECTORY_SEPARATOR . $fileId;
								$downloadFilename = $fileRow['af_filename'];
								if (empty($downloadFilename)) $downloadFilename = $fileRow['af_original_filename'];
							
								// ダウンロード処理
								$ret = $this->gPage->downloadFile($downloadFile, $downloadFilename);
								$downloadCompleted = true;				// ダウンロード処理完了かどうか
								break;
							}
						}
					}
					if ($downloadCompleted){		// ダウンロード処理完了のとき
						// ダウンロードログを残す
						$this->gInstance->getAnalyzeManager()->logContentDownload(default_contentCommonDef::$_viewContentType . default_contentCommonDef::DOWNLOAD_CONTENT_TYPE, $fileId);
					} else {
						$msgDetail = '';
						if (!empty($contentId)) $msgDetail .= 'コンテンツID=' . $contentId;
						$this->writeError(__METHOD__, '添付ファイルのダウンロードに失敗しました。添付ファイルが見つかりません。ファイルID=' . $fileId, 2200, $msgDetail);
					}
				} else {		// 添付ファイルダウンロード不可の場合
					// アクセスキー取得用のURLが設定されている場合はリダイレクト
					if (empty($accessUrl)){		// アクセスキー取得用URLが設定されていない場合
						$this->writeError(__METHOD__, '添付ファイルのアクセスキー取得用のURLが設定されていません。コンテンツID=' . $contentId, 2200);
					} else {
						$accessUrl = $this->getUrl($accessUrl, true/*リンク用*/);
						//$this->gPage->redirect($accessUrl);
						// ### キャッシュに残さない方法でリダイレクトする ###
						$this->gPage->redirect($accessUrl, false, 303, false/*SSLは自動制御しない*/);
						return;
					}
				}
			} else {
				// ダウンロード不可のときはエラーログを残す
				$msgDetail = '';
				if (!empty($contentId)) $msgDetail .= 'コンテンツID=' . $contentId;
				$this->writeUserError(__METHOD__, '添付ファイルへの不正なアクセスを検出しました。ファイルID=' . $fileId, 2200, $msgDetail);
			}
			// システム強制終了
			$this->gPage->exitSystem();
		} else if ($act == 'checkpassword'){
			// パスワードチェックが必要な場合のみ実行
			if ($this->usePassword){
				// パスワードのチェックはデフォルト言語で行う
				$ret = self::$_mainDb->getContentByContentId(default_contentCommonDef::$_contentType, $contentId, $this->gEnv->getDefaultLanguage(), $row);
				if ($ret){
					if (!empty($row['cn_password']) && $row['cn_password'] == $password){		// パスワードチェックOKのとき
						if (!in_array($contentId, $this->sessionParamObj->authContentId)){
							$this->sessionParamObj->authContentId[] = $contentId;
						}
					} else {
						if (in_array($contentId, $this->sessionParamObj->authContentId)){
							$newAuthContentId = array();
							for ($i = 0; $i < count($this->sessionParamObj->authContentId); $i++){
								if ($this->sessionParamObj->authContentId[$i] != $contentId) $newAuthContentId[] = $this->sessionParamObj->authContentId[$i];
							}
							$this->sessionParamObj->authContentId = $newAuthContentId;
						}
					}
					// セッション更新
					$this->setWidgetSessionObj($this->sessionParamObj);
				}
				// 画面を全体を再表示
				$this->gPage->redirect($this->gEnv->getCurrentRequestUri());
				return;
			}
		} else if ($this->viewMode == 'search'){			// 検索
			$itemCount = self::DEFAULT_SEARCH_LIST_COUNT;		// 取得数
			
			// キーワード検索のとき
			if (empty($keyword)){
				$keyword = '検索キーワードが入力されていません';
				$this->headTitle = self::DEFAULT_TITLE_SEARCH;
			} else {
				// キーワード分割
				$parsedKeywords = $this->gInstance->getTextConvManager()->parseSearchKeyword($keyword);

				// 検索キーワードを記録
				for ($i = 0; $i < count($parsedKeywords); $i++){
					$this->gInstance->getAnalyzeManager()->logSearchWord($this->gEnv->getCurrentWidgetId(), $parsedKeywords[$i]);
				}
				
				self::$_mainDb->searchContentByKeyword(default_contentCommonDef::$_contentType, $itemCount, 1, $parsedKeywords, $this->gEnv->getCurrentLanguage(), $all, $now, array($this, 'searchItemsLoop'));
				$this->headTitle = self::DEFAULT_TITLE_SEARCH_RESULTS . '[' . $keyword . ']';
				if (!$this->isExistsViewData) $keyword .= '&nbsp;&nbsp;' . self::MESSAGE_NO_CONTENT;
			}
			$this->pageTitle = $this->headTitle;// 画面タイトル、パンくずリスト用タイトル
			$this->tmpl->addVar("_widget", "keyword", $keyword);
			
			// コンテンツタイトルの設定
			if (!empty($this->showTitle)){		// タイトルを表示するとき
				$this->tmpl->setAttribute('show_title', 'visibility', 'visible');// タイトル表示
				$headClassStr = $this->gDesign->getDefaultContentHeadClassString();			// コンテンツヘッダ用CSSクラス
				$this->tmpl->addVar("show_title", "class", $headClassStr);
				$this->tmpl->addVar("show_title", "title", $this->pageTitle);
			}
		} else {
			// ##### コンテンツの表示 #####
			// 検索以外で管理者権限がある場合は「新規」アイコンを表示
			$buttonList = '';// 共通ボタン作成
			if (!empty($this->showEdit) && $this->isSystemManageUser){// コンテンツ編集権限がある
				// 新規作成ボタン
				$iconUrl = $this->gEnv->getRootUrl() . self::NEW_ICON_FILE;		// 新規アイコン
				$iconTitle = '新規';
				$editImg = '<img class="m3icon" src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" data-container="body" />';
				$buttonList = '<a href="javascript:void(0);" onclick="editContent(0);">' . $editImg . '</a>';
				switch (default_contentCommonDef::$_deviceType){		// デバイスごとの処理
					case 0:		// PC
					case 2:		// スマートフォン
					default:
						//$buttonList = '<div style="text-align:right;position:absolute;top:' . $this->editIconPos . 'px;z-index:10;width:100%;">' . $buttonList . '</div>';
						$buttonList = '<div class="m3edittool" style="top:' . $this->editIconPos . 'px;">' . $buttonList . '</div>';
						break;
					case 1:		// 携帯
						$buttonList = '<div style="text-align:right;position:absolute;top:' . $this->editIconPos . 'px;right:5px;z-index:10;width:100%;">' . $buttonList . '</div>';
						break;
				}
				$this->editIconPos += self::EDIT_ICON_NEXT_POS;			// 編集アイコンの位置を更新
			}
			$this->tmpl->addVar("_widget", "button_list", $buttonList);
			
			if (empty($contentId)){	// コンテンツIDがないときは一覧を表示
				// トップ画面用一覧を表示
				$this->showTopList($request);
				
/*				// 定義IDが0以外のときは、定義IDをコンテンツIDとする
				$contentIdArray = array();
			
				// 定義ID取得
				$configId = $this->gEnv->getCurrentWidgetConfigId();
				if (!empty($configId)) $contentIdArray[] = $configId;

				$contentIdArray = array();
				self::$_mainDb->getContentItems(default_contentCommonDef::$_contentType, array($this, 'itemsLoop'), $contentIdArray, $this->gEnv->getCurrentLanguage(), $all, $now, $preview);
				if (!$this->_contentCreated){		// コンテンツが取得できなかったときはデフォルト言語で取得
					self::$_mainDb->getContentItems(default_contentCommonDef::$_contentType, array($this, 'itemsLoop'), $contentIdArray, $this->gEnv->getDefaultLanguage(), $all, $now, $preview);
				}
				*/
			} else {	// コンテンツIDで指定
				if ($historyIndex >= 0){		// 履歴表示のとき
					self::$_mainDb->getContentItemsByHistory(default_contentCommonDef::$_contentType, array($this, 'itemsLoop'), $contentId, $this->gEnv->getCurrentLanguage(), $historyIndex);
				} else {
					// データエラーチェック
					$contentIdArray = explode(',', $contentId);
					if (ValueCheck::isNumeric($contentIdArray)){		// すべて数値であるかチェック
						self::$_mainDb->getContentItems(default_contentCommonDef::$_contentType, array($this, 'itemsLoop'), $contentIdArray, $this->gEnv->getCurrentLanguage(), $all, $now, $preview);
						if (!$this->_contentCreated){		// コンテンツが取得できなかったときはデフォルト言語で取得
							self::$_mainDb->getContentItems(default_contentCommonDef::$_contentType, array($this, 'itemsLoop'), $contentIdArray, $this->gEnv->getDefaultLanguage(), $all, $now, $preview);
						}
				
						// コンテンツアクセス不可のときはアクセス不可メッセージを出力
						if ($showMessageDeny && !$this->_contentCreated) $this->setAppErrorMsg($messageDeny);
						
						// 単体コンテンツ表示の場合はカノニカル属性を設定
						if ($this->_contentCreated && count($contentIdArray) == 1){
							$accessPointUrl = '';		// コンテンツアクセスポイント
							switch (default_contentCommonDef::$_deviceType){		// デバイスごとの処理
								case 0:		// PC
								default:
									$accessPointUrl = $this->gEnv->getDefaultUrl();
									break;
								case 1:		// 携帯
									$accessPointUrl = $this->gEnv->getDefaultMobileUrl();
									break;
								case 2:		// スマートフォン
									$accessPointUrl = $this->gEnv->getDefaultSmartphoneUrl();
									break;
							}
			
							$url = $this->getUrl($accessPointUrl . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentIdArray[0]);
							$this->gPage->setCanonicalUrl($url);
						}
					} else {
						$this->setAppErrorMsg('IDにエラー値があります');
					}
				}
			}
		}
		// パスワード入力が必要なコンテンツがある場合はスクリプトを追加
		if ($this->passwordFormCount > 0){		// パスワード入力フォーム数
			$this->tmpl->setAttribute('password_script', 'visibility', 'visible');
		}
		// デバイスタイプごとの処理
		if (default_contentCommonDef::$_deviceType == 2){		// スマートフォンの場合
			// ベースDIVのタグに属性を追加
			$divAttr = 'style="position:relative;"';		// ツールボタン表示用CSS
			
			// jQuery Mobile対応
			if ($this->jQueryMobileFormat) $divAttr .= ' data-role="content"';
			
			$this->tmpl->addVar("_widget", "data_role", $divAttr);
		}
		// ##### HTMLサブタイトルを設定 #####
		// _setHeadMeta()でMETAタグタイトル($this->headTitle)がサブタイトルとして設定される。METAタグのタイトル名が設定されていない場合はページタイトル名(コンテンツ名)をサブタイトルに設定する。
		if (empty($this->headTitle)) $this->gPage->setHeadSubTitle($this->pageTitle);
		
		// 運用可能ユーザの場合は編集用ボタンを表示
		if ($this->isSystemManageUser && $this->viewMode != 'search'){		// 検索画面以外
			// 設定画面表示用のスクリプトを埋め込む
			$editUrl = $this->getConfigAdminUrl('openby=simple&task=content_detail');
			$this->tmpl->setAttribute('admin_script', 'visibility', 'visible');
			$this->tmpl->addVar("admin_script", "edit_url", $editUrl);
		}
		
		// ##### セッションパラメータ保存 #####
		if ($this->usePassword){			// セッション保存データがある場合
			$this->setWidgetSessionObj($this->sessionParamObj);
		} else {
			$this->setWidgetSessionObj(null);		// セッションデータ削除
		}
	}
	/**
	 * トップ一覧画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function showTopList($request)
	{
		$contentLibObj = $this->gInstance->getObject(self::CONTENT_OBJ_ID);
		$contentLibObj->getPublicContentList(self::DEFAULT_LIST_COUNT, 1/*最初のページ*/, 0/*コンテンツID(一覧)*/, $this->_now, null/*期間開始*/, null/*期間終了*/, ''/*検索キーワード*/, $this->_langId, 1/*降順*/, array($this, 'itemsLoop'));
	}
	/**
	 * ヘッダ部メタタグの設定
	 *
	 * HTMLのheadタグ内に出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ
	 * @return array 						設定データ。連想配列で「title」「description」「keywords」を設定。
	 */
	function _setHeadMeta($request, &$param)
	{
		$headData = array(	'title' => $this->headTitle,
							'description' => $this->headDesc,
							'keywords' => $this->headKeyword);
		return $headData;
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
		return $this->pageTitle;
	}
	/**
	 * JavascriptライブラリをHTMLヘッダ部に設定
	 *
	 * JavascriptライブラリをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string,array 				Javascriptライブラリ。出力しない場合は空文字列を設定。
	 */
	function _addScriptLibToHead($request, &$param)
	{
		return $this->addLib;
	}
	/**
	 * JavascriptをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addScriptToHead($request, &$param)
	{
		return $this->headScript;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$contentId = $fetchedRow['cn_id'];
		$accessKey = $fetchedRow['cn_attach_access_key'];		// アクセスキー
		$accessUrl = $fetchedRow['cn_attach_access_url'];		// アクセスキー取得用URL
		
		// ページタイトルの設定
		if (empty($this->pageTitle)) $this->pageTitle = $fetchedRow['cn_name'];		// 画面タイトル、パンくずリスト用タイトル
		
		// コンテンツ編集権限がある場合はボタンを表示
		$buttonList = '';
		if (!empty($this->showEdit) && $this->isSystemManageUser){
			$iconUrl = $this->gEnv->getRootUrl() . self::EDIT_ICON_FILE;		// 編集アイコン
			$iconTitle = '編集';
			$editImg = '<img class="m3icon" src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" data-container="body" />';
			$buttonList = '<a href="javascript:void(0);" onclick="editContent(' . $contentId . ');">' . $editImg . '</a>';
			switch (default_contentCommonDef::$_deviceType){		// デバイスごとの処理
				case 0:		// PC
				case 2:		// スマートフォン
				default:
					//$buttonList = '<div style="text-align:right;position:absolute;top:' . $this->editIconPos . 'px;z-index:10;width:100%;">' . $buttonList . '</div>';
					$buttonList = '<div class="m3edittool" style="top:' . $this->editIconPos . 'px;">' . $buttonList . '</div>';
					break;
				case 1:		// 携帯
					$buttonList = '<div style="text-align:right;position:absolute;top:' . $this->editIconPos . 'px;right:5px;z-index:10;width:100%;">' . $buttonList . '</div>';
					break;
			}
			$this->editIconPos += self::EDIT_ICON_NEXT_POS;			// 編集アイコンの位置を更新
		}
		
		// コンテンツの出力形式を設定
		$this->inputPassword = false;			// パスワード入力かどうか
		if ($this->usePassword && !empty($fetchedRow['cn_password'])){			// パスワードによるコンテンツ閲覧制限、かつ、パスワードが設定されている
			// 認証状況をチェック
			if (!is_array($this->sessionParamObj->authContentId) || !in_array($contentId, $this->sessionParamObj->authContentId)) $this->inputPassword = true;			// パスワード入力かどうか
		}
		
		// ##### 表示コンテンツ作成 #####
		if ($this->inputPassword){			// パスワード入力のとき
			$this->tmpl->addVar('contentlist', 'type', 'input_password');		// パスワード入力画面
			
			$formName = self::PASSWORD_FORM_NAME . ($this->passwordFormCount + 1);		// パスワードチェック用フォーム名
			$funcName = str_replace('_', '', $formName);
			$this->passwordFormCount++;		// パスワード入力フォーム数
			
			$row = array(
				'func_name'	=> $funcName,	// パスワードチェック用関数
				'form_name'	=> $formName	// パスワードチェック用フォーム名
			);
			$this->tmpl->addVars('form_list', $row);
			$this->tmpl->parseTemplate('form_list', 'a');
			
			$contentText = self::$_configArray[default_contentCommonDef::$CF_PASSWORD_CONTENT];		// パスワード画面コンテンツ
			
			$this->addLib[] = self::LIB_MD5;		// 認証用の暗号化ライブラリを追加
		} else {
			// ビューカウントを更新
			$this->gInstance->getAnalyzeManager()->logContentView(default_contentCommonDef::$_viewContentType, $fetchedRow['cn_serial'], $contentId);
		
			// コンテンツタイトルの出力設定
			if (empty($this->showTitle)) $this->tmpl->addVar('contentlist', 'type', 'hide_title');
			
			$formName = '';
			$funcName = '';
			$contentText = $fetchedRow['cn_html'];		// コンテンツ本文
			
			$accessPointUrl = '';		// コンテンツアクセスポイント
			switch (default_contentCommonDef::$_deviceType){		// デバイスごとの処理
				case 0:		// PC
				default:
					$accessPointUrl = $this->gEnv->getDefaultUrl();
					break;
				case 1:		// 携帯
					$accessPointUrl = $this->gEnv->getDefaultMobileUrl();
					break;
				case 2:		// スマートフォン
					$accessPointUrl = $this->gEnv->getDefaultSmartphoneUrl();
					break;
			}
			
			// ユーザ定義フィールド値取得
			// 埋め込む文字列はHTMLエスケープする
			$contentLayout = self::$_configArray[default_contentCommonDef::$CF_LAYOUT_VIEW_DETAIL];
			$fieldInfoArray = default_contentCommonDef::parseUserMacro($contentLayout);
			$fieldValueArray = $this->unserializeArray($fetchedRow['cn_option_fields']);
			$userFields = array();
			$fieldKeys = array_keys($fieldInfoArray);
			for ($i = 0; $i < count($fieldKeys); $i++){
				$key = $fieldKeys[$i];
				$value = $fieldValueArray[$key];
				$userFields[$key] = isset($value) ? $this->convertToDispString($value) : '';
			}
			
			// カレント言語がデフォルト言語と異なる場合はデフォルト言語の添付ファイルを取得
			$isDefaltContent = false;	// デフォルト言語のコンテンツを取得したかどうか
			if ($this->_isMultiLang && $this->_langId != $this->gEnv->getDefaultLanguage()){
				$ret = self::$_mainDb->getContentByContentId(default_contentCommonDef::$_contentType, $contentId, $this->gEnv->getDefaultLanguage(), $defaltContentRow);
				if ($ret) $isDefaltContent = true;
			}
			// コンテンツのサムネールを取得
			$thumbUrl = '';
			$thumbFilename = $fetchedRow['cn_thumb_filename'];
			if ($isDefaltContent) $thumbFilename = $defaltContentRow['cn_thumb_filename'];
			if (!empty($thumbFilename)){
				$thumbFilenameArray = explode(';', $thumbFilename);
				$thumbUrl = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_CONTENT, default_contentCommonDef::$_deviceType, $thumbFilenameArray[count($thumbFilenameArray) -1]);
			}

			// アクセスキーが設定されている場合アクセス制御を行う
			$canDownloadAttachFile = false;			// 添付ファイルダウンロード不可
			$sessionAccessKey = '';
			if (empty($accessKey)){
				$canDownloadAttachFile = true;		// 添付ファイルダウンロード可
			} else {
				// セッションのアクセスキーを確認
				$sessionAccessKey = $this->gAccess->getSessionAccessKey($accessKey);
				if (!empty($sessionAccessKey)) $canDownloadAttachFile = true;		// 添付ファイルダウンロード可
			}
			
			// 添付ファイルダウンロード用リンク
			$attachFileTag = '';
			$attachContentSerial = $fetchedRow['cn_serial'];
			if ($isDefaltContent) $attachContentSerial = $defaltContentRow['cn_serial'];
			$ret = $this->gInstance->getFileManager()->getAttachFileInfo(default_contentCommonDef::$_viewContentType, $attachContentSerial, $attachFileRows);
			if ($ret){
				if (!$canDownloadAttachFile){			// ダウンロード不可のとき
					// アクセスキー取得用URL
					if (empty($accessUrl)){		// アクセスキー取得用URLが設定されていない場合
						$this->writeError(__METHOD__, '添付ファイルのアクセスキー取得用のURLが設定されていません。コンテンツID=' . $contentId, 2200);
					} else {
						$accessUrl = $this->getUrl($accessUrl, true/*リンク用*/);
					}
				}
				
				$optionAttr = '';		// 追加属性
				if ($this->jQueryMobileFormat) $optionAttr = 'rel="external"';			// jQueryMobile用のフォーマットで出力するかどうか
				
				if (empty($this->autoGenerateAttachFileList)){		// 添付ファイルリストを自動作成しない場合
					// ##### コンテンツ本文を解析して添付ファイルダウンロード用のリンクを埋め込む #####
					$this->attachFileRows = $attachFileRows;		// 添付ファイル情報(コンテンツ本文作成用)
					$this->attachFileDownloadUrl = $canDownloadAttachFile ? '' : $accessUrl;			// 添付ファイルのリンク先URL(ダウンロード不可時のみ設定)
					$pattern = '/' . preg_quote(M3_TAG_START . M3_TAG_MACRO_ITEM_KEY) . '(\d+)\|?(.*?)' . preg_quote(M3_TAG_END) . '/u';			// オプションパラメータは「|」以降(2015/4/20変更)
					$contentText = preg_replace_callback($pattern, array($this, '_replace_content_macro_callback'), $contentText);
				} else {		// 添付ファイルリストを自動生成する場合
					$attachFileTag .= '<ul>';
					for ($i = 0; $i < count($attachFileRows); $i++){
						$fileTitle = $attachFileRows[$i]['af_title'];
						if (empty($fileTitle)) $fileTitle = $attachFileRows[$i]['af_filename'];
					
						// ダウンロード用のリンク
						// 添付ファイルがダウンロードできない状態のときはアクセスキー取得用のURLへリンク
						if ($canDownloadAttachFile){			// ダウンロード可能のとき
							$downloadUrl  = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET;
							$downloadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
							$downloadUrl .= '&fileid=' . $attachFileRows[$i]['af_file_id'];
							$downloadUrl = $this->getUrl($downloadUrl);
						} else {
							$downloadUrl = $accessUrl;
						}
						
						$attachFileTag .= '<li>' . $this->convertToDispString($fileTitle);
						$attachFileTag .= '<a href="' . $this->convertUrlToHtmlEntity($downloadUrl) . '" ' . $optionAttr . '>';
						$attachFileTag .= '<img src="' . $this->getUrl($this->gEnv->getRootUrl() . self::DOWNLOAD_ICON_FILE) . '" width="' . self::DOWNLOAD_ICON_SIZE . '" height="' . self::DOWNLOAD_ICON_SIZE . '" title="ダウンロード" alt="ダウンロード" style="border:none;margin:0;padding:0;vertical-align:text-top;" />';
						$attachFileTag .= '</a></li>';
					}
					$attachFileTag .= '</ul>';
				}
			}
			
			// 関連コンテンツリンク
			$relatedContentTag = '';	// 関連コンテンツリンク
			$relatedContent = $fetchedRow['cn_related_content'];
			if ($isDefaltContent) $relatedContent = $defaltContentRow['cn_related_content'];
			if (!empty($relatedContent)){
				$contentIdArray = array_map('trim', explode(',', $relatedContent));
				$ret = self::$_mainDb->getContentItemsById(default_contentCommonDef::$_contentType, $contentIdArray, $this->_langId, $rows);
				if ($ret){
					$relatedContentTag .= '<ul>';
					for ($i = 0; $i < count($rows); $i++){
						$relatedUrl = $accessPointUrl . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $rows[$i]['cn_id'];	// 関連コンテンツリンク先
						$relatedContentTag .= '<li><a href="' . $this->convertUrlToHtmlEntity($this->getUrl($relatedUrl)) . '">' . $this->convertToDispString($rows[$i]['cn_name']);
						$relatedContentTag .= '</a></li>';
					}
					$relatedContentTag .= '</ul>';
				}
			}
			// コンテンツレイアウトに埋め込む
			$contentParam = array_merge($userFields, array('BODY' => $contentText, 'FILES' => $attachFileTag, 'PAGES' => '', 'LINKS' => $relatedContentTag));
			$contentText = $this->createDetailContent($contentParam);
			
			// Magic3マクロ変換
			// あらかじめ「CT_」タグをすべて取得する?
			$contentInfo = array();
			$contentInfo[M3_TAG_MACRO_CONTENT_BREAK] = '';		// コンテンツ置換キー(コンテンツ区切り)
			$contentInfo[M3_TAG_MACRO_CONTENT_ID] = $fetchedRow['cn_id'];			// コンテンツ置換キー(コンテンツID)
			$contentInfo[M3_TAG_MACRO_CONTENT_URL] = $this->getUrl($accessPointUrl . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $fetchedRow['cn_id']);			// コンテンツ置換キー(コンテンツURL)
			$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $fetchedRow['cn_name'];			// コンテンツ置換キー(タイトル)
			$contentInfo[M3_TAG_MACRO_CONTENT_DESCRIPTION] = $fetchedRow['cn_description'];			// コンテンツ置換キー(簡易説明)
			$contentInfo[M3_TAG_MACRO_CONTENT_IMAGE] = $this->getUrl($thumbUrl);		// コンテンツ置換キー(画像)
			$contentInfo[M3_TAG_MACRO_CONTENT_UPDATE_DT] = $fetchedRow['cn_create_dt'];		// コンテンツ置換キー(更新日時)
			$contentInfo[M3_TAG_MACRO_CONTENT_START_DT] = $fetchedRow['cn_active_start_dt'];		// コンテンツ置換キー(公開開始日時)
			$contentInfo[M3_TAG_MACRO_CONTENT_END_DT] = $fetchedRow['cn_active_end_dt'];		// コンテンツ置換キー(公開終了日時)
			$contentText = $this->convertM3ToHtml($contentText, true/*改行コードをbrタグに変換*/, $contentInfo);
			
			// ##### jQueryスクリプト作成機能 #####
			if ($this->useJQuery){
				$scriptLib = $fetchedRow['cn_script_lib'];
				if ($isDefaltContent) $scriptLib = $defaltContentRow['cn_script_lib'];
				if (!empty($scriptLib)) $this->addLib = array_merge($this->addLib, explode(',', $scriptLib));	// ライブラリを追加
				
				$script = $fetchedRow['cn_script'];
				if ($isDefaltContent) $script = $defaltContentRow['cn_script'];
				if (!empty($script)) $this->headScript .= $script . M3_NL;// jQueryスクリプト
			}
			
			// ##### HTMLヘッダ処理 #####
			// METAタグを設定
			if (!empty($this->headTitle) && !strEndsWith($this->headTitle, ',')) $this->headTitle .= ',';
			if (!empty($this->headDesc) && !strEndsWith($this->headDesc, ',')) $this->headDesc .= ',';
			if (!empty($this->headKeyword) && !strEndsWith($this->headKeyword, ',')) $this->headKeyword .= ',';
			$this->headTitle .= $fetchedRow['cn_meta_title'];
			$this->headDesc .= $fetchedRow['cn_meta_description'];
			$this->headKeyword .= $fetchedRow['cn_meta_keywords'];
			$headOthers .= $fetchedRow['cn_head_others'];		// ヘッダ部その他
		
			// HTMLヘッダにタグ出力
			$headText = '';
			if ($this->outputHead){			// ヘッダ出力するかどうか
				$headText = self::$_configArray[default_contentCommonDef::$CF_HEAD_VIEW_DETAIL];
				$headText = $this->convertM3ToHead($headText, $contentInfo);
			}
			if (!empty($headOthers)) $headText .= $headOthers;		// コンテンツごとの個別タグ設定
			if (!empty($headText)) $this->gPage->setHeadOthers($headText);
		}
		
		$headClassStr = $this->gDesign->getDefaultContentHeadClassString();			// コンテンツヘッダ用CSSクラス
		
		$row = array(
			'class' => $headClassStr,		// コンテンツヘッダ用CSSクラス
			'title' => $this->convertToDispString($fetchedRow['cn_name']),
			'content' => $contentText,	// コンテンツ
			'content_id' => $contentId,
			'func_name'	=> $funcName,	// パスワードチェック用関数
			'form_name'	=> $formName,	// パスワードチェック用フォーム名
			'button_list' => $buttonList	// 記事編集ボタン
		);
		$this->tmpl->addVars('contentlist', $row);
		$this->tmpl->parseTemplate('contentlist', 'a');
		
		// コンテンツが取得できた
		$this->_contentCreated = true;
		return true;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function checkItemsLoop($index, $fetchedRow)
	{
		$this->serialArray[] = $fetchedRow['cn_serial'];// コンテンツのシリアル番号を保存
		
		$this->_contentCreated = true;// コンテンツが取得できた
		return true;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function searchItemsLoop($index, $fetchedRow)
	{
		// タイトルを設定
		$title = $fetchedRow['cn_name'];

		// 記事へのリンクを生成
		$linkUrl = $this->getUrl($this->currentPageUrl . '&' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $fetchedRow['cn_id']);
		$link = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . $title . '</a>';

		// テキストに変換
		//$contentText = strip_tags($fetchedRow['cn_html']);
		$contentText = $this->gInstance->getTextConvManager()->htmlToText($fetchedRow['cn_html']);
		
		// アプリケーションルートを変換
		$rootUrl = $this->getUrl($this->gEnv->getRootUrl());
		$contentText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $rootUrl, $contentText);
		
		// 登録したキーワードを変換
		$this->gInstance->getTextConvManager()->convByKeyValue($contentText, $contentText);
		
		// 検索結果用にテキストを詰める。改行、タブ、スペース削除。
		$contentText = str_replace(array("\r", "\n", "\t", " "), '', $contentText);
		
		// 文字列長を修正
		if (function_exists('mb_strimwidth')){
			$contentText = mb_strimwidth($contentText, 0, self::CONTENT_SIZE, '…');
		} else {
			$contentText = substr($contentText, 0, self::CONTENT_SIZE) . '...';
		}

		$headClassStr = $this->gDesign->getDefaultContentHeadClassString();			// コンテンツヘッダ用CSSクラス
		
		$row = array(
			'class' => $headClassStr,		// コンテンツヘッダ用CSSクラス
			'title' => $link,			// リンク付きタイトル
			'content' => $this->convertToDispString($contentText)	// コンテンツ
		);
		$this->tmpl->addVars('contentlist', $row);
		$this->tmpl->parseTemplate('contentlist', 'a');
		$this->isExistsViewData = true;				// 表示データがあるかどうか
		return true;
	}
	/**
	 * 期間から公開可能かチェック
	 *
	 * @param timestamp	$startDt		公開開始日時
	 * @param timestamp	$endDt			公開終了日時
	 * @param timestamp	$now			基準日時
	 * @return bool						true=公開可能、false=公開不可
	 */
	function isActive($startDt, $endDt, $now)
	{
		$isActive = false;		// 公開状態

		if ($startDt == $this->gEnv->getInitValueOfTimestamp() && $endDt == $this->gEnv->getInitValueOfTimestamp()){
			$isActive = true;		// 公開状態
		} else if ($startDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		} else if ($endDt == $this->gEnv->getInitValueOfTimestamp()){
			if (strtotime($now) >= strtotime($startDt)) $isActive = true;		// 公開状態
		} else {
			if (strtotime($startDt) <= strtotime($now) && strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		}
		return $isActive;
	}
	/**
	 * 詳細コンテンツを作成
	 *
	 * @param array	$contentParam		コンテンツ作成用パラメータ
	 * @return string			作成コンテンツ
	 */
	function createDetailContent($contentParam)
	{
		$contentText = self::$_configArray[default_contentCommonDef::$CF_LAYOUT_VIEW_DETAIL];			// コンテンツレイアウト(詳細表示)
		
		// コンテンツを作成
		$keys = array_keys($contentParam);
		for ($i = 0; $i < count($keys); $i++){
			$key = $keys[$i];
			//$value = $contentParam[$key];
			$value = str_replace('\\', '\\\\', $contentParam[$key]);		// ##### (注意)preg_replaceで変換値のバックスラッシュが解釈されるので、あらかじめバックスラッシュを2重化しておく必要がある
			
			$pattern = '/' . preg_quote(M3_TAG_START . $key) . ':?(.*?)' . preg_quote(M3_TAG_END) . '/u';
			$contentText = preg_replace($pattern, $value, $contentText);
		}
		return $contentText;
	}
	/**
	 * 添付ファイルリンク変換コールバック関数
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_content_macro_callback($matchData)
	{
		$destTag	= $matchData[0];
		$itemNo		= $matchData[1];	// 添付ファイルの項目No
		$options	= $matchData[2];	// オプション文字列

		// オプションを解析
		$option = '';
		if (!empty($options)){
			list($option, $optionValue) = array_map('trim', explode('=', $options));
			$option = strtolower($option);
		}

		// デフォルトのファイルタイトルを取得
		$index = $itemNo -1;
		$fileTitle = $this->attachFileRows[$index]['af_title'];
		if (empty($fileTitle)) $fileTitle = $this->attachFileRows[$index]['af_filename'];
		if (empty($fileTitle)) $fileTitle = $this->attachFileRows[$index]['af_original_filename'];
		
		switch ($option){
		case 'title':
			if (!empty($optionValue)) $fileTitle = $optionValue;
			break;
		case 'tag':
		default:
			break;
		}
		
		// ダウンロード用のリンク
		if (empty($this->attachFileDownloadUrl)){
			$downloadUrl  = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET;
			$downloadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
			$downloadUrl .= '&fileid=' . $this->attachFileRows[$index]['af_file_id'];
			$downloadUrl = $this->getUrl($downloadUrl);
		} else {
			$downloadUrl = $this->attachFileDownloadUrl;
		}
		$destTag = '<a href="' . $this->convertUrlToHtmlEntity($downloadUrl) . '">';
		if ($option == 'tag'){
			$destTag .= $optionValue;
		} else {
			$destTag .= $this->convertToDispString($fileTitle);
		}
		$destTag .= '</a>';

		return $destTag;
	}
}
?>
