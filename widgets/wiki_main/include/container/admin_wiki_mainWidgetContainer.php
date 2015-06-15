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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/wiki_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/wiki_mainDb.php');

class admin_wiki_mainWidgetContainer extends BaseAdminWidgetContainer
{
	private $authType;		// 認証方法
	const DEFAULT_PASSWORD = '********';	// 設定済みを示すパスワード
	
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
		return 'admin.tmpl.html';
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
		// 詳細設定画面
		return $this->createDetail($request);
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
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
		$act = $request->trimValueOf('act');

		$this->authType	= $request->trimValueOf('item_auth');				// 認証方法
		$password		= $request->trimValueOf('password');				// パスワード
		$defaultPage	= $request->trimValueOf('item_default_page');		// デフォルトページ名
		$showTitle			= $request->trimCheckedValueOf('item_showtitle');		// タイトルを表示するかどうか
		$showPageRelated	= $request->trimCheckedValueOf('item_showpagerelated');		// 関連ページを表示するかどうか
		$showPageAttachFiles	= $request->trimCheckedValueOf('item_showpageattachfiles');		// 添付ファイルを表示するかどうか
		$showPageLastModified	= $request->trimCheckedValueOf('item_showlastmodified');		// 最終更新を表示するかどうか
		$showToolbar			= $request->trimCheckedValueOf('item_show_toolbar');		// ツールバーを表示するかどうか
		
		$replaceNew = false;		// データを再取得するかどうか
		if (empty($act)){// 初期起動時
			// デフォルト値設定
			$replaceNew = true;			// データ再取得
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$ret = true;		// エラー値リセット
				// 認証タイプ
				if ($ret) $ret = $this->db->updateConfig(wiki_mainCommonDef::CF_AUTH_TYPE, $this->authType);
	
				// パスワードが設定されているときは更新
				if (!empty($password)) $ret = $this->db->updateConfig(wiki_mainCommonDef::CF_PASSWORD, $password);

				// デフォルトページ
				if ($ret) $ret = $this->db->updateConfig(wiki_mainCommonDef::CF_DEFAULT_PAGE, $defaultPage);
				
				// ##### ページ構成 #####
				// タイトルの表示状態
				if ($ret) $ret = $this->db->updateConfig(wiki_mainCommonDef::CF_SHOW_PAGE_TITLE, $showTitle);
				// 関連ページを表示
				if ($ret) $ret = $this->db->updateConfig(wiki_mainCommonDef::CF_SHOW_PAGE_RELATED, $showPageRelated);
				// 添付ファイルを表示
				if ($ret) $ret = $this->db->updateConfig(wiki_mainCommonDef::CF_SHOW_PAGE_ATTACH_FILES, $showPageAttachFiles);
				// 最終更新を表示
				if ($ret) $ret = $this->db->updateConfig(wiki_mainCommonDef::CF_SHOW_PAGE_LAST_MODIFIED, $showPageLastModified);
				
				if ($ret) $ret = $this->db->updateConfig(wiki_mainCommonDef::CF_SHOW_TOOLBAR, $showToolbar);// ツールバーを表示するかどうか
				
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
			$this->authType = $this->db->getConfig(wiki_mainCommonDef::CF_AUTH_TYPE);// 認証方法
			if (empty($this->authType)) $this->authType = wiki_mainCommonDef::AUTH_TYPE_ADMIN;		// 認証タイプ(管理権限ユーザ)
			$defaultPage = $this->db->getConfig(wiki_mainCommonDef::CF_DEFAULT_PAGE);// デフォルトページ
			if (empty($defaultPage)) $defaultPage = wiki_mainCommonDef::DEFAULT_DEFAULT_PAGE;	// デフォルトページ
			$showTitle = $this->db->getConfig(wiki_mainCommonDef::CF_SHOW_PAGE_TITLE);// タイトル表示状態
			if ($showTitle == '') $showTitle = '1';		// タイトル表示状態
			$showPageRelated = $this->db->getConfig(wiki_mainCommonDef::CF_SHOW_PAGE_RELATED);// 関連ページを表示
			if ($showPageRelated == '') $showPageRelated = '1';		// 関連ページを表示
			$showPageAttachFiles = $this->db->getConfig(wiki_mainCommonDef::CF_SHOW_PAGE_ATTACH_FILES);// 添付ファイルを表示
			if ($showPageAttachFiles == '') $showPageAttachFiles = '1';		// 添付ファイルを表示
			$showPageLastModified = $this->db->getConfig(wiki_mainCommonDef::CF_SHOW_PAGE_LAST_MODIFIED);// 最終更新を表示
			if ($showPageLastModified == '') $showPageLastModified = '1';		// 最終更新を表示
			$showToolbar = $this->db->getConfig(wiki_mainCommonDef::CF_SHOW_TOOLBAR);// ツールバーを表示するかどうか
			if ($showToolbar == '') $showToolbar = '1';		// ツールバーを表示するかどうか
		}
		
		// 認証方法メニュー作成
		$this->createAuthMenu();
		
		// パスワード領域の表示
		if ($this->authType != 'password') $this->tmpl->addVar("_widget", "pwd_style", 'style="display:none;"');
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "default_page", $defaultPage);		// デフォルトページ
		$this->tmpl->addVar("_widget", "show_title", $this->convertToCheckedString($showTitle));	// タイトルを表示するかどうか
		$this->tmpl->addVar("_widget", "show_page_related", $this->convertToCheckedString($showPageRelated));	// 関連ページを表示するかどうか
		$this->tmpl->addVar("_widget", "show_page_attach_files", $this->convertToCheckedString($showPageAttachFiles));	// 添付ファイルを表示するかどうか
		$this->tmpl->addVar("_widget", "show_last_modified", $this->convertToCheckedString($showPageLastModified));	// 最終更新を表示するかどうか
		$this->tmpl->addVar("_widget", "show_toolbar", $this->convertToCheckedString($showToolbar));	// ツールバーを表示するかどうか
		
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
		$authMenu = array(	array(	'name' => '管理権限ユーザ',	'value' => wiki_mainCommonDef::AUTH_TYPE_ADMIN),
							array(	'name' => 'ログインユーザ', 'value' => wiki_mainCommonDef::AUTH_TYPE_LOGIN_USER),
							array(	'name' => '共通パスワード', 'value' => wiki_mainCommonDef::AUTH_TYPE_PASSWORD));
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
}
?>
