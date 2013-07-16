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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_wiki_mainWidgetContainer.php 3478 2010-08-14 08:33:30Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/wiki_mainDb.php');

class admin_wiki_mainWidgetContainer extends BaseAdminWidgetContainer
{
	private $sysDb;	// DB接続オブジェクト
	private $langId;
	private $authType;		// 認証方法
	const DEFAULT_PASSWORD = '********';	// 設定済みを示すパスワード
	const DEFAULT_PAGE = 'FrontPage';		// デフォルトのページ
	const AUTH_TYPE_ADMIN		= 'admin';		// 認証タイプ(管理権限ユーザ)
	const AUTH_TYPE_LOGIN_USER	= 'loginuser';		// 認証タイプ(ログインユーザ)
	const AUTH_TYPE_PASSWORD	= 'password';		// 認証タイプ(共通パスワード)
	const CONFIG_KEY_AUTH_TYPE = 'auth_type';			// 認証タイプ(admin=管理権限ユーザ、loginuser=ログインユーザ、password=共通パスワード)
	const CONFIG_KEY_SHOW_PAGE_TITLE		= 'show_page_title';		// タイトル表示
	const CONFIG_KEY_SHOW_PAGE_RELATED		= 'show_page_related';// 関連ページ
	const CONFIG_KEY_SHOW_PAGE_ATTACH_FILES	= 'show_page_attach_files';// 添付ファイル
	const CONFIG_KEY_SHOW_PAGE_LAST_MODIFIED	= 'show_page_last_modified';// 最終更新
	const CONFIG_KEY_PASSWORD = 'password';		// 共通パスワード
	const CONFIG_KEY_DEFAULT_PAGE = 'default_page';		// デフォルトページ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new wiki_mainDb();
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
		if ($task == 'list'){		// 一覧画面
			return 'admin_list.tmpl.html';
		} else {			// 一覧画面
			return 'admin.tmpl.html';
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
		if ($task == 'list'){		// 一覧画面
			return $this->createList($request);
		} else {			// 詳細設定画面
			return $this->createDetail($request);
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
		$userId			= $this->gEnv->getCurrentUserId();
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');

		$this->authType	= $request->trimValueOf('item_auth');				// 認証方法
		$password		= $request->trimValueOf('password');				// パスワード
		$defaultPage	= $request->trimValueOf('item_default_page');		// デフォルトページ名
		$showTitle = ($request->trimValueOf('item_showtitle') == 'on') ? 1 : 0;		// タイトルを表示するかどうか
		$showPageRelated = ($request->trimValueOf('item_showpagerelated') == 'on') ? 1 : 0;		// 関連ページを表示するかどうか
		$showPageAttachFiles = ($request->trimValueOf('item_showpageattachfiles') == 'on') ? 1 : 0;		// 添付ファイルを表示するかどうか
		$showPageLastModified = ($request->trimValueOf('item_showlastmodified') == 'on') ? 1 : 0;		// 最終更新を表示するかどうか
		
		$replaceNew = false;		// データを再取得するかどうか
		if (empty($act)){// 初期起動時
			// デフォルト値設定
			$replaceNew = true;			// データ再取得
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$ret = true;		// エラー値リセット
				// 認証タイプ
				if ($ret) $ret = $this->db->updateConfig(self::CONFIG_KEY_AUTH_TYPE, $this->authType);
	
				// パスワードが設定されているときは更新
				if (!empty($password)) $ret = $this->db->updateConfig(self::CONFIG_KEY_PASSWORD, $password);

				// デフォルトページ
				if ($ret) $ret = $this->db->updateConfig(self::CONFIG_KEY_DEFAULT_PAGE, $defaultPage);
				
				// ##### ページ構成 #####
				// タイトルの表示状態
				if ($ret) $ret = $this->db->updateConfig(self::CONFIG_KEY_SHOW_PAGE_TITLE, $showTitle);
				// 関連ページを表示
				if ($ret) $ret = $this->db->updateConfig(self::CONFIG_KEY_SHOW_PAGE_RELATED, $showPageRelated);
				// 添付ファイルを表示
				if ($ret) $ret = $this->db->updateConfig(self::CONFIG_KEY_SHOW_PAGE_ATTACH_FILES, $showPageAttachFiles);
				// 最終更新を表示
				if ($ret) $ret = $this->db->updateConfig(self::CONFIG_KEY_SHOW_PAGE_LAST_MODIFIED, $showPageLastModified);
				
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		}
		if ($replaceNew){			// データ再取得
			$value = $this->db->getConfig(self::CONFIG_KEY_AUTH_TYPE);// 認証方法
			if ($value == ''){
				$this->authType = self::AUTH_TYPE_PASSWORD;		// 認証タイプ(共通パスワード)
			} else {
				$this->authType = $value;
			}
			$value = $this->db->getConfig(self::CONFIG_KEY_DEFAULT_PAGE);// デフォルトページ
			if (empty($value)){
				$defaultPage = self::DEFAULT_PAGE;
			} else {
				$defaultPage = $value;
			}
			// ##### ページ構成 #####
			$value = $this->db->getConfig(self::CONFIG_KEY_SHOW_PAGE_TITLE);// タイトル表示状態
			if ($value == ''){
				$showTitle = '1';		// 表示
			} else {
				$showTitle = $value;
			}
			$value = $this->db->getConfig(self::CONFIG_KEY_SHOW_PAGE_RELATED);// 関連ページを表示
			if ($value == ''){
				$showPageRelated = '1';		// 表示
			} else {
				$showPageRelated = $value;
			}
			$value = $this->db->getConfig(self::CONFIG_KEY_SHOW_PAGE_ATTACH_FILES);// 添付ファイルを表示
			if ($value == ''){
				$showPageAttachFiles = '1';		// 表示
			} else {
				$showPageAttachFiles = $value;
			}
			$value = $this->db->getConfig(self::CONFIG_KEY_SHOW_PAGE_LAST_MODIFIED);// 最終更新を表示
			if ($value == ''){
				$showPageLastModified = '1';		// 表示
			} else {
				$showPageLastModified = $value;
			}
		}
		
		// 認証方法メニュー作成
		$this->createAuthMenu();
		
		// パスワード領域の表示
		if ($this->authType != 'password') $this->tmpl->addVar("_widget", "pwd_style", 'style="display:none;"');
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "default_page", $defaultPage);		// デフォルトページ
		$checked = '';
		if ($showTitle) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_title", $checked);	// タイトルを表示するかどうか
		
		$checked = '';
		if ($showPageRelated) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_page_related", $checked);	// 関連ページを表示するかどうか
		$checked = '';
		if ($showPageAttachFiles) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_page_attach_files", $checked);	// 添付ファイルを表示するかどうか
		$checked = '';
		if ($showPageLastModified) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_last_modified", $checked);	// 最終更新を表示するかどうか
		
		// アップロードディレクトリ
		//$uploadDir = $this->gEnv->getCurrentWidgetRootPath() . '/upload';		// 暫定
		$uploadDir = $this->gEnv->getResourcePath() . '/widgets/wiki/upload';
		$this->tmpl->addVar("_widget", "upload_dir", $uploadDir);
		if (is_writable($uploadDir)){
			$data = '<b><font color="green">書き込み可能</font></b>';
		} else {
			$data = '<b><font color="red">書き込み不可</font></b>';
		}
		$this->tmpl->addVar("_widget","upload_dir_access", $data);		// 一時ディレクトリの書き込み権限
		
		$this->tmpl->addVar("_widget", "pwd", self::DEFAULT_PASSWORD);// 入力済みを示すパスワードの設定
	}
	/**
	 * 認証方法メニューを作成
	 *
	 * @return なし						
	 */
	function createAuthMenu()
	{
		$authMenu = array(	array(	'name' => '管理権限ユーザ',	'value' => 'admin'),
							array(	'name' => 'ログインユーザ', 'value' => 'loginuser'),
							array(	'name' => '共通パスワード', 'value' => 'password'));
		for ($i = 0; $i < count($authMenu); $i++){
			$name = $authMenu[$i]['name'];// 定義名
			$value = $authMenu[$i]['value'];// 設定値
			$selected = '';
			if ($this->authType == $value) $selected = 'selected';
			$row = array(
				'name' => $name,		// 名前
				'value' => $value,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('auth_list', $row);
			$this->tmpl->parseTemplate('auth_list', 'a');
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
		$userId	= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act	= $request->trimValueOf('act');
		
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
				// 更新オブジェクト作成
				$newParamObj = array();

				for ($i = 0; $i < count($this->paramObj); $i++){
					$targetObj = $this->paramObj[$i];
					$id = $targetObj->id;// 定義ID
					if (!in_array($id, $delItems)){		// 削除対象でないときは追加
						$newParamObj[] = $targetObj;
					}
				}
				
				// ウィジェットパラメータオブジェクト更新
				$ret = $this->updateWidgetParamObj($newParamObj);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					$this->paramObj = $newParamObj;
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 定義一覧作成
		$this->createItemList();
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
		// 画面定義用の情報を戻す
		$this->tmpl->addVar("_widget", "def_serial", $defSerial);	// ページ定義のレコードシリアル番号
		$this->tmpl->addVar("_widget", "def_config", $defConfigId);	// ページ定義の定義ID
	}
	/**
	 * 定義一覧作成
	 *
	 * @return なし						
	 */
	function createItemList()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$targetObj = $this->paramObj[$i];
			$id = $targetObj->id;// 定義ID
			$name = $targetObj->name;// 定義名
			
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->sysDb->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			$row = array(
				'index' => $i,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'movie_id' => $this->convertToDispString($targetObj->movieId),	// 動画ID
				'width' => $targetObj->width,					// 動画幅
				'height' => $targetObj->height,					// 動画高さ
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
}
?>
