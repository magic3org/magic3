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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConfigsystemBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getLibPath()			. '/qqFileUploader/fileuploader.php');

class admin_mainConfigimageWidgetContainer extends admin_mainConfigsystemBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $langId;		// 変更対象の言語ID
	const CF_USE_CONTENT_MAINTENANCE = 'use_content_maintenance';		// メンテナンス用コンテンツを汎用コンテンツから取得するかどうか
	const CF_USE_CONTENT_ACCESS_DENY = 'use_content_access_deny';		// アクセス不可用コンテンツを汎用コンテンツから取得するかどうか
	const CF_USE_CONTENT_PAGE_NOT_FOUND = 'use_content_page_not_found';		// 存在しないページ画面に汎用コンテンツを使用するかどうか
	
	const ATTACH_FILE_DIR = '/etc/content';				// 添付ファイル格納ディレクトリ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
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
		return 'configimage.tmpl.html';
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
		// ユーザ情報、表示言語
		$userInfo		= $this->gEnv->getCurrentUserInfo();
		$this->langId		= $this->gEnv->getCurrentLanguage();

		$act = $request->trimValueOf('act');
		$msg_siteInMaintenance = $request->trimValueOf('item_msg_site_in_maintenance');		// メンテナンス中メッセージ
		$useContentMaintenance = ($request->trimValueOf('item_use_content_maintenance') == 'on') ? 1 : 0;		// メンテナンス画面用コンテンツを汎用コンテンツから取得するかどうか
		$msg_accessDeny = $request->trimValueOf('item_msg_access_deny');		// アクセス不可メッセージ
		$useContentAccessDeny = ($request->trimValueOf('item_use_content_access_deny') == 'on') ? 1 : 0;		// アクセス不可用コンテンツを汎用コンテンツから取得するかどうか
		$msg_pageNotFound = $request->trimValueOf('item_msg_page_not_found');		// 存在しない画面メッセージ
		$useContentPageNotFound = ($request->trimValueOf('item_use_content_page_not_found') == 'on') ? 1 : 0;		// 存在しない画面用コンテンツを汎用コンテンツから取得するかどうか
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			$isErr = false;
			if (!$isErr){
				if (!$this->gInstance->getMessageManager()->updateMessage(MessageManager::MSG_SITE_IN_MAINTENANCE, $msg_siteInMaintenance, $this->langId)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_USE_CONTENT_MAINTENANCE, $useContentMaintenance)) $isErr = true;		// メンテナンス画面用コンテンツを汎用コンテンツから取得するかどうか
			}
			if (!$isErr){
				if (!$this->gInstance->getMessageManager()->updateMessage(MessageManager::MSG_ACCESS_DENY, $msg_accessDeny, $this->langId)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_USE_CONTENT_ACCESS_DENY, $useContentAccessDeny)) $isErr = true;		// アクセス不可画面用コンテンツを汎用コンテンツから取得するかどうか
			}
			if (!$isErr){
				if (!$this->gInstance->getMessageManager()->updateMessage(MessageManager::MSG_PAGE_NOT_FOUND, $msg_pageNotFound, $this->langId)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateSystemConfig(self::CF_USE_CONTENT_PAGE_NOT_FOUND, $useContentPageNotFound)) $isErr = true;		// 存在しない画面用コンテンツを汎用コンテンツから取得するかどうか
			}
			if ($isErr){		// エラー発生のとき
				$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
			} else {
				$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				
				$replaceNew = true;		// データを再取得
			}
		} else if ($act == 'uploadfile'){		// 添付ファイルアップロード
			$uploader = new qqFileUploader(array());
			$resultObj = $uploader->handleUpload($this->getAttachFileDir());
			
			if ($resultObj['success']){
				// 作業ディレクトリを作成
				$tmpDir = $gEnvManager->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
		
				$fileInfo = $resultObj['file'];
				$ret = $this->gInstance->getFileManager()->addAttachFileInfo(default_contentCommonDef::$_viewContentType, $fileInfo['fileid'], $fileInfo['path'], $fileInfo['filename']);
				if (!$ret){			// エラーの場合はファイルを添付ファイルを削除
					unlink($fileInfo['path']);
					$resultObj = array('error' => 'Could not create file information.');
				}
				
		// 作業ディレクトリ削除
		rmDirectory($tmpDir);
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
			//$destStr = htmlspecialchars($destStr, ENT_NOQUOTES);// 「&」が「&amp;」に変換されるので使用しない
			//header('Content-type: application/json; charset=utf-8');
			header('Content-Type: text/html; charset=UTF-8');		// JSONタイプを指定するとIE8で動作しないのでHTMLタイプを指定
			echo $destStr;
			
			// システム強制終了
			$this->gPage->exitSystem();
		} else {
			$replaceNew = true;		// データを再取得
		}
		
		if ($replaceNew){
			$msg_siteInMaintenance = $this->gInstance->getMessageManager()->getMessage(MessageManager::MSG_SITE_IN_MAINTENANCE, $this->langId);// メンテナンス中メッセージ
			$useContentMaintenance	= $this->db->getSystemConfig(self::CF_USE_CONTENT_MAINTENANCE);			// メンテナンス画面用コンテンツを汎用コンテンツから取得するかどうか
			$msg_accessDeny = $this->gInstance->getMessageManager()->getMessage(MessageManager::MSG_ACCESS_DENY, $this->langId);// アクセス不可メッセージ
			$useContentAccessDeny	= $this->db->getSystemConfig(self::CF_USE_CONTENT_ACCESS_DENY);			// アクセス不可用コンテンツを汎用コンテンツから取得するかどうか
			$msg_pageNotFound = $this->gInstance->getMessageManager()->getMessage(MessageManager::MSG_PAGE_NOT_FOUND, $this->langId);// 存在しない画面メッセージ
			$useContentPageNotFound	= $this->db->getSystemConfig(self::CF_USE_CONTENT_PAGE_NOT_FOUND);			// 存在しない画面用コンテンツを汎用コンテンツから取得するかどうか
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "msg_site_in_maintenance", $msg_siteInMaintenance);// メンテナンスメッセージ
		$this->tmpl->addVar("_widget", "content_key_maintenance", M3_CONTENT_KEY_MAINTENANCE);		// メンテナンス用コンテンツ取得キー
		$checked = '';
		if (!empty($useContentMaintenance)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_content_maintenance", $checked);// メンテナンス画面用コンテンツを汎用コンテンツから取得するかどうか
		
		$this->tmpl->addVar("_widget", "msg_access_deny", $msg_accessDeny);				// アクセス不可メッセージ
		$this->tmpl->addVar("_widget", "content_key_access_deny", M3_CONTENT_KEY_ACCESS_DENY);		// アクセス不可用コンテンツ取得キー
		$checked = '';
		if (!empty($useContentAccessDeny)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_content_access_deny", $checked);// アクセス不可用コンテンツを汎用コンテンツから取得するかどうか
		
		$this->tmpl->addVar("_widget", "msg_page_not_found", $msg_pageNotFound);				// 存在しない画面メッセージ
		$this->tmpl->addVar("_widget", "content_key_page_not_found", M3_CONTENT_KEY_PAGE_NOT_FOUND);		// 存在しない画面用コンテンツ取得キー
		$checked = '';
		if (!empty($useContentPageNotFound)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_content_page_not_found", $checked);// 存在しない画面用コンテンツを汎用コンテンツから取得するかどうか
	}
	/**
	 * 添付ファイル格納ディレクトリ取得
	 *
	 * @return string		ディレクトリパス
	 */
	function getAttachFileDir()
	{
		global $gEnvManager;
		$dir = $gEnvManager->getIncludePath() . self::ATTACH_FILE_DIR;
		if (!file_exists($dir)) mkdir($dir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
		return $dir;
	}
}
?>
