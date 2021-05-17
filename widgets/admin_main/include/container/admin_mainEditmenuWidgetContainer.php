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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainMainteBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainEditmenuWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $menuBasicItems;			// 元となるメニュー項目
	private $adminPages;			// 管理機能ウィジェットの選択可能画面
	private $widgets = array();				// ウィジェット情報
	private $widgetInfoStr;			// ウィジェット情報文字列
	private $pageInfoStr;			// 管理画面情報文字列
	private $menuItemExists;			// メニュー項目があるかどうか
	const TOPPAGE_IMAGE_PATH = 'toppage_image_path';				// トップページ画像
	const DEFAULT_NAV_ID = 'admin_menu';		// メニューID
	const DEFAULT_IMAGE_SIZE = 32;	// アイコンサイズ
	const TASK_ID_HEADE_WIDGET = 'configwidget_';		// ウィジェット管理画面
	const TASK_ID_HEAD_TITLE = '_';						// タイトル表示
	const DEF_FILE_HEAD = 'menu_';		// メニュー定義ファイル名
	
	// メニュー項目タイプ
	const ITEM_TYPE_TITLE		= 'type_title';		// タイトル
	const ITEM_TYPE_CR			= 'type_cr';			// 改行
	const ITEM_TYPE_ADMIN_PAGE	= 'type_admin';	// 管理ウィジェットの管理画面
	const ITEM_TYPE_WIDGET_PAGE	= 'type_widget';	// ウィジェットの管理画面
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
		
		// 元となるメニュー項目
		$this->menuBasicItems = array(	array('type' => self::ITEM_TYPE_ADMIN_PAGE,		'name' => $this->_('Administration Page'),				// 管理機能の画面
																						'desc' => $this->_('Page of administration widget.')),// 管理機能の画面を表示します。
										array('type' => self::ITEM_TYPE_WIDGET_PAGE,	'name' => $this->_('Widget Page'),						// ウィジェットの管理画面
																						'desc' => $this->_('Administration page of widgets.')),	// ウィジェットの管理画面を表示します。
										array('type' => self::ITEM_TYPE_TITLE,			'name' => $this->_('Title'),							// タイトル
																						'desc' => $this->_('Title of menu category.')),			// メニューカテゴリのタイトル名を作成します。
										array('type' => self::ITEM_TYPE_CR,				'name' => $this->_('Newline'),							// 改行
																						'desc' => $this->_('Separate menu categories.'))		// メニューの項目ブロックを改行します。
								);
		// 管理機能ウィジェットの選択可能画面
		$this->adminPages = array(	array('task' => 'pagedef',				'name' => $this->_('PC Page'),		// PC用画面編集
																			'desc' => $this->_('Edit page for PC.')),		// PC用Webサイトの画面を作成します。
									array('task' => 'pagedef_smartphone',	'name' => $this->_('Smartphone Page'),		// スマートフォン用画面編集
																			'desc' => $this->_('Edit page for Smartphone.')),		// スマートフォン用Webサイトの画面を作成します。
									array('task' => 'widgetlist',			'name' => $this->_('Widget Administration'),		// ウィジェット管理
																			'desc' => $this->_('Manage widgets to be installed.')),		// ウィジェットの管理を行います。
									array('task' => 'templist',				'name' => $this->_('Template Administration'),		// テンプレート管理
																			'desc' => $this->_('Manage templates to be installed.')),		// テンプレートの管理を行います。
									array('task' => 'menudef',				'name' => $this->_('Menu Administration (Tree)'),		// メニュー定義(多階層)
																			'desc' => $this->_('Manage menus with tree type.')),		// 多階層でメニューを定義します。
									array('task' => 'smenudef',				'name' => $this->_('Menu Administration (Single)'),		// メニュー定義(単階層)
																			'desc' => $this->_('Manage menus with single type.')),		// 単階層でメニューを定義します。
//									array('task' => 'master',				'name' => $this->_('System Master'),		// システムマスター管理
//																			'desc' => $this->_('Administrate system master data.')),		// システムに関するマスターデータの管理を行います。
									array('task' => 'mainte',				'name' => $this->_('Maintenance'),		// メンテナンス
																			'desc' => $this->_('Administrate system master data.')),		// システムに関するマスターデータの管理を行います。
									array('task' => 'tenantserver',			'name' => $this->_('Tenant Server Administration'),		// テナントサーバ管理
																			'desc' => $this->_('Manage tenant server.')),		// テナントサーバを管理します。
									array('task' => 'userlist',				'name' => $this->_('User List'),		// ユーザ一覧
																			'desc' => $this->_('Manage user to login.')),		// ログイン可能なユーザを管理します。
									array('task' => 'usergroup',			'name' => $this->_('User Group'),		// ユーザグループ
																			'desc' => $this->_('Manage user group.')),		// ユーザグループを管理します。
//									array('task' => 'loginstatus',			'name' => $this->_('Login Status'),		// ログイン状況
//																			'desc' => $this->_('View user login status.')),		// ユーザのログイン状況を表示します。
									array('task' => 'configsite',			'name' => $this->_('Site Information'),		// 基本情報
																			'desc' => $this->_('Configure site information.')),		// サイトの設定を行います。
									array('task' => 'usercustom',			'name' => $this->_('User Custom Parameter'),		// ユーザ定義変数管理
																			'desc' => $this->_('Configure user defined paramater.')),		// ユーザ定義の変数を管理します。
									array('task' => 'configsys',			'name' => $this->_('System Information'),		// システム情報
																			'desc' => $this->_('Configure sytem basic information.')),	// システムの基本的な設定を行います。
									array('task' => 'configlang',			'name' => $this->_('System Lang'),		// システム言語
																			'desc' => $this->_('Configure language for system.')),	// システムの言語設定を行います。
									array('task' => 'configmessage',		'name' => $this->_('System Message'),		// システムメッセージ
																			'desc' => $this->_('Configure message for system.')),	// システムのメッセージ設定を行います。
									array('task' => 'pagehead',				'name' => $this->_('Page Header'),		// ページヘッダ情報
																			'desc' => $this->_('Configure page header information.')),	// ページヘッダ情報を設定します。
									array('task' => 'analyzecalc',			'name' => $this->_('Access Analytics'),	// アクセス解析
																			'desc' => $this->_('Analyze site access.')),	// サイトへのアクセス状況を参照します。
									array('task' => 'opelog',				'name' => $this->_('Operation Log'),	// 運用ログ参照
																			'desc' => $this->_('View system operation log.')),	// 運用ログを参照します。
									array('task' => 'accesslog',			'name' => $this->_('Access Log'),	// アクセスログ参照
																			'desc' => $this->_('View site access log.')),	// アクセスログを参照します。
									array('task' => 'searchwordlog',		'name' => $this->_('Search Word Log'),	// 検索語ログ参照
																			'desc' => $this->_('View words to search.')),	// 検索語ログを参照します。
									array('task' => 'filebrowse',			'name' => $this->_('File Browser'),	// ファイルブラウザ
																			'desc' => $this->_('Administrate files under resource directory.')),	// リソースディレクトリ以下のファイルを管理します。
									array('task' => 'initsystem',			'name' => $this->_('Datebase Initialize'),	// DB初期化
																			'desc' => $this->_('Initialize database data.')),	// DBデータの初期化を行います。
									array('task' => 'dbbackup',				'name' => $this->_('Database Backup'),	// DBバックアップ
																			'desc' => $this->_('Backup and restore database.')),	// DBのバックアップリストアを行います。
									array('task' => 'editmenu',				'name' => $this->_('Edit Administration Menu'),	// 管理メニュー編集
																			'desc' => $this->_('Configure administration menu.')),	// 管理メニューの編集を行います。									
									array('task' => 'pageinfo',				'name' => $this->_('Page Information'),	// ページ情報
																			'desc' => $this->_('Configure page information.')),	// ページの情報設定を行います。
									array('task' => 'pageid',				'name' => $this->_('Page Id'),	// ページID
																			'desc' => $this->_('Configure page id.')),	// ページIDを管理します。
									array('task' => 'menuid',				'name' => $this->_('Menu Id'),	// メニューID
																			'desc' => $this->_('Configure menu id.')),	// メニューIDを管理します。										
									array('task' => 'createtable',			'name' => $this->_('Create User Table'),	// ユーザ定義テーブル
																			'desc' => $this->_('Manage user original table.')),	// ユーザ定義のテーブルの作成、データの編集を行います。
									array('task' => 'logout',				'name' => $this->_('Logout'),	// ログアウト
																			'desc' => $this->_('Logout from system.'))	// ログアウト処理を行います。
								);
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
		if ($task == 'editmenu_others'){		// その他設定画面
			return 'editmenu_others.tmpl.html';
		} else {
			return 'editmenu.tmpl.html';
		}
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
		return 'editmenu';
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
		if ($task == 'editmenu_others'){	// その他設定画面
			$this->configOthers($request);
		
			// テキストをローカライズ
			$localeText['msg_update'] = $this->_('Update configration?');		// 設定を更新しますか?
			$localeText['label_others'] = $this->_('Administration Menu Others');	// 管理メニューその他
			$localeText['label_go_back'] = $this->_('Go back');	// 戻る
			$localeText['label_image'] = $this->_('Image');	// 画像
			$localeText['label_change'] = $this->_('Change');	// 変更
			$localeText['label_update'] = $this->_('Update');	// 更新
		} else {			// メニュー項目編集
			$this->editMenuItems($request);
			
			// テキストをローカライズ
			$localeText['msg_update'] = $this->_('Update menu definition?');		// 項目を更新しますか?
			$localeText['msg_upload_file_not_selected'] = $this->_('Files to upload not selected.');		// アップロードするファイルが選択されていません
			$localeText['msg_upload'] = $this->_('Upload files.');		// ファイルをアップロードします
			$localeText['label_admin'] = $this->_('Administration');		// 管理機能
			$localeText['label_widget'] = $this->_('Widget');		// ウィジェット
			$localeText['label_untitled'] = $this->_('Untitled');		// 未設定
			$localeText['label_name'] = $this->_('Name');		// 名前
			$localeText['label_option'] = $this->_('Option');		// オプション
			$localeText['label_desc'] = $this->_('Description');		// 説明
			$localeText['label_edit_menu'] = $this->_('Edit Administration Menu');		// 管理メニュー編集
			$localeText['label_others'] = $this->_('Others');		// その他
			$localeText['label_menu_item'] = $this->_('Menu Item');		// メニュー項目
			$localeText['label_configured_menu_item'] = $this->_('Configured Menu Item');// 設定されたメニュー項目
			$localeText['label_update'] = $this->_('Update');// 更新
			$localeText['label_menu_script'] = $this->_('Menu Definition Script');// 管理メニュー定義ファイル
			$localeText['label_upload'] = $this->_('Upload');// アップロード
			$localeText['label_download'] = $this->_('Download');// ダウンロード
		}
		$this->setLocaleText($localeText);
	}
	/**
	 * メニュー項目編集
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function editMenuItems($request)
	{
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		// 現在の言語からメニューIDを作成
		// メニューは表示する言語ごとに作成する
		$menuId = self::DEFAULT_NAV_ID;
		if ($langId != $this->gEnv->getDefaultLanguage()){		// デフォルト言語でないときは拡張子を付加
			 $menuId .= '.' . $langId;
		}

		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			$valuesType = $request->trimValueOf('items_type');
			$valuesName = $request->trimValueOf('items_name');
			$valuesOption = $request->trimValueOf('items_option');
			$valuesParam = $request->trimValueOf('items_param');
			$valuesDesc = $request->trimValueOf('items_desc');
			
			// トランザクション開始
			$this->db->startTransaction();
		
			// 一旦すべて削除
			$ret = $this->db->DelNavItems($menuId);
		
			// IDを求める
			$maxId = $this->db->getNavItemsMaxId();
			if (empty($maxId)){
				$maxId = 1;
			} else {
				$maxId++;
			}
			$parentId = $maxId;
			$id = $maxId;
			$index = 0;		// 表示順
			for ($i = 0; $i < count($valuesType); $i++){
				$type = $valuesType[$i];
				$name = $valuesName[$i];		// 表示名
				$param = $valuesParam[$i];
				$taskId = '';
				$control = 0;
				$helpTitle = $name;
				$helpBody = $valuesDesc[$i];// ヘルプ本文
				if ($type == self::ITEM_TYPE_ADMIN_PAGE){		// 管理機能ページ
					$index++;		// 表示順更新
					$taskId = $valuesOption[$i];		// 管理機能画面
				} else if ($type == self::ITEM_TYPE_WIDGET_PAGE){	// ウィジェットの管理画面
					$index++;		// 表示順更新
					$taskId = self::TASK_ID_HEADE_WIDGET . $valuesOption[$i];		// ウィジェット管理画面
				} else if ($type == self::ITEM_TYPE_TITLE){			// タイトル
					$parentId = 0;
					$index = 0;
					$taskId = self::TASK_ID_HEAD_TITLE . $id;						// 「_」付きのIDはリンクなし
				} else if ($type == self::ITEM_TYPE_CR){			// 改行
					$parentId = 0;
					$index = 0;
					$taskId = self::TASK_ID_HEAD_TITLE . $id;						// 「_」付きのIDはリンクなし
					$control = 1;
				}
				// キーの存在チェック
				$ret = $this->db->isExistsNavItemKey($menuId, $taskId, $param);
				if ($ret){			// キーが存在する場合は終了
					//$this->setMsg(self::MSG_USER_ERR, '項目「' . $name . '」は重複しています');
					$this->setMsg(self::MSG_USER_ERR, sprintf($this->_('\'%s\' is duplicated.'), $name));		// 項目「%s」は重複しています
					
					// トランザクション中止
					$this->db->cancelTransaction();
					break;
				} else {
					$ret = $this->db->addNavItems($menuId, $id, $parentId, $index, $taskId, $param, $control, $name, $helpTitle, $helpBody);
					// 項目ID更新
					if ($type == self::ITEM_TYPE_TITLE){			// タイトル
						$parentId = $id;
					}
					$id++;
				}
			}
			
			// トランザクション終了
			$ret = $this->db->endTransaction();
			if ($ret){
				//$this->setMsg(self::MSG_GUIDANCE, '管理メニューを更新しました');
				$this->setMsg(self::MSG_GUIDANCE, $this->_('Administration menu updated.'));			// 管理メニューを更新しました
			} else {
				//if ($this->getMsgCount() == 0) $this->setMsg(self::MSG_APP_ERR, '管理メニュー更新に失敗しました');
				if ($this->getMsgCount() == 0) $this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating administration menu.'));		// 管理メニュー更新に失敗しました
			}
		} else if ($act == 'upload'){		// メニュー定義ファイルアップロード
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])) {
				$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得
				
				// ファイルを保存するサーバディレクトリを指定
				$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);

				// アップされたテンポラリファイルを保存ディレクトリにコピー
				$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
				if ($ret){
					// スクリプト実行
					if ($this->gInstance->getDbManager()->execScriptWithConvert($tmpFile, $errors)){// 正常終了の場合
						//$this->setMsg(self::MSG_GUIDANCE, 'スクリプト実行完了しました');
						$this->setMsg(self::MSG_GUIDANCE, $this->_('Script run successfully.'));		// スクリプト実行完了しました
					} else {
						//$this->setMsg(self::MSG_APP_ERR, "スクリプト実行に失敗しました");
						$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in running script.'));		// スクリプト実行に失敗しました
					}
					if (!empty($errors)){
						foreach ($errors as $error) {
							$this->setMsg(self::MSG_APP_ERR, $error);
						}
					}
				}
				// テンポラリファイル削除
				unlink($tmpFile);
			} else {
				//$msg = 'アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります - ' . $gSystemManager->getMaxFileSizeForUpload() . 'バイト)';
				$msg = sprintf($this->_('Uploded file not found. (detail: The file may be over maximum size to be allowed to upload. Size %s bytes.'), $this->gSystem->getMaxFileSizeForUpload());	// アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります。%sバイト)
				$this->setAppErrorMsg($msg);
			}
		} else if ($act == 'download'){		// メニュー定義ファイルダウンロード
			// メニュー定義ファイル作成
			$ret = $this->db->getNavItemsAllRecords(self::DEFAULT_NAV_ID, $rows);
			if ($ret){
				$writeData  = 'DELETE FROM _nav_item WHERE ni_nav_id = \'' . self::DEFAULT_NAV_ID . '\';' . M3_NL;			// データを削除
				$writeData .= 'INSERT INTO _nav_item ' . M3_NL;
				$writeData .= '(ni_id, ni_parent_id, ni_index, ni_nav_id, ni_task_id, ni_group_id, ni_view_control, ni_param, ni_name, ni_help_title, ni_help_body, ni_visible) VALUES ' . M3_NL;
				$lineCount = count($rows);
				for ($i = 0; $i < $lineCount; $i++){
					$line = $rows[$i];
					$writeData .= '(';
					$writeData .= $line['ni_id'] . ', ';
					$writeData .= $line['ni_parent_id'] . ', ';
					$writeData .= $line['ni_index'] . ', ';
					$writeData .= '\'' . addslashes($line['ni_nav_id']) . '\', ';
					$writeData .= '\'' . addslashes($line['ni_task_id']) . '\', ';
					$writeData .= '\'' . addslashes($line['ni_group_id']) . '\', ';
					$writeData .= '\'' . addslashes($line['ni_view_control']) . '\', ';
					$writeData .= '\'' . addslashes($line['ni_param']) . '\', ';
					$writeData .= '\'' . addslashes($line['ni_name']) . '\', ';
					$writeData .= '\'' . addslashes($line['ni_help_title']) . '\', ';
					$writeData .= '\'' . addslashes($line['ni_help_body']) . '\', ';
					if (empty($line['ni_visible'])){
						$writeData .= 'false';
					} else {
						$writeData .= 'true';
					}
					if ($i < $lineCount -1){
						$writeData .= '),' . M3_NL;
					} else {
						$writeData .= ');' . M3_NL;
					}
				}
				
				// テンポラリファイル作成
				$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
				if ($tmpFile !== false){
					$ret = writeFile($tmpFile, $writeData);
					if ($ret){
						// ページ作成処理中断
						$this->gPage->abortPage();
				
						// ダウンロード処理
						$downloadFilename = self::DEF_FILE_HEAD . date("YmdHi") . '.sql';// ダウンロード時のデフォルトファイル名
						$ret = $this->gPage->downloadFile($tmpFile, $downloadFilename, true/*実行後ファイル削除*/);
				
						// システム強制終了
						$this->gPage->exitSystem();
					}
				}
			}
		}
		// 管理画面メニュー作成
		$this->pageInfoStr = '';			// 管理画面情報
		$this->createAdminPageMenu();
		
		// ウィジェットメニュー作成
		$this->widgetInfoStr = '';
		$this->db->getAvailableWidgetListForEditMenu(array($this, 'widgetListLoop'));
		
		// メニュー基本項目一覧作成
		$this->createMenuBasicItemList();
		
		// メニュー設定項目作成
		$this->db->getNavItemsAll(self::DEFAULT_NAV_ID, array($this, 'menuItemListLoop'));
		if (!$this->menuItemExists) $this->tmpl->setAttribute('menuassignedlist', 'visibility', 'hidden');// メニュー項目を非表示
		
		// ウィジェット情報を設定
		$this->widgetInfoStr = rtrim($this->widgetInfoStr, ',');
		$this->widgetInfoStr = '[' . $this->widgetInfoStr . ']';
		$this->tmpl->addVar("_widget", "widget_info", $this->widgetInfoStr);
		// 管理画面情報を設定
		$this->pageInfoStr = rtrim($this->pageInfoStr, ',');
		$this->pageInfoStr = '[' . $this->pageInfoStr . ']';
		$this->tmpl->addVar("_widget", "page_info", $this->pageInfoStr);
		
		// パスを設定
		$this->tmpl->addVar("_widget", "top_url", $this->getUrl($this->gEnv->getDefaultAdminUrl()));		// トップメニュー画面URL
	}
	/**
	 * その他の設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function configOthers($request)
	{
		$reloadData = false;
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 行更新のとき
			$imageUrl = $request->trimValueOf('item_image');		// 管理メニュー画像
			if (!empty($imageUrl)) $imageUrl = $this->gEnv->getMacroPath($imageUrl);
			
			// 入力チェック
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$isErr = false;
				if (!$isErr){
					if (!$this->db->updateSystemConfig(self::TOPPAGE_IMAGE_PATH, $imageUrl)) $isErr = true;
				}
				if ($isErr){		// エラーのとき
					//$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
					$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating configration.'));		// データ更新に失敗しました
				} else {
					//$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$this->setMsg(self::MSG_GUIDANCE, $this->_('Configration updated.'));		// データを更新しました
					$reloadData = true;		// データの再読み込み					
				}
			}
		} else {
			$reloadData = true;
		}
		if ($reloadData){		// データ再取得のとき
			$imageUrl = $this->db->getSystemConfig(self::TOPPAGE_IMAGE_PATH);
			$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
		}
		$this->tmpl->addVar('_widget', 'image_url', $imageUrl);
	}
	/**
	 * ウィジェットリスト、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function widgetListLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['wd_id'];
		$name = $this->convertToDispString($fetchedRow['wd_name']);
		$selected = '';
		$row = array(
			'value'    => $this->convertToDispString($id),			// ウィジェットID
			'name'     => $name,			// ウィジェット名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('widget_list', $row);
		$this->tmpl->parseTemplate('widget_list', 'a');
		
		$this->widgets[] = $row;				// ウィジェット情報
		$image = $this->getUrl($this->gDesign->_getWidgetIconUrl($id, self::DEFAULT_IMAGE_SIZE));
		$desc = $this->convertToDispString($fetchedRow['wd_description']);
		$this->widgetInfoStr .= '{id:"' . $id . '",image:"' . $image . '",desc:"' . $desc . '"},';
		return true;
	}
	/**
	 * 管理画面メニュー作成
	 *
	 * @return なし						
	 */
	function createAdminPageMenu()
	{
		for ($j = 0; $j < count($this->adminPages); $j++){
			$task = $this->adminPages[$j]['task'];
			$name = $this->adminPages[$j]['name'];
			$selected = '';
			$menurow = array(
				'value'		=> $task,			// 機能値
				'name'		=> $name,			// 機能名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('admin_list', $menurow);
			$this->tmpl->parseTemplate('admin_list', 'a');
			
			$desc = $this->convertToDispString($this->adminPages[$j]['desc']);
			$this->pageInfoStr .= '{id:"' . $task . '",desc:"' . $desc . '"},';
		}
	}
	/**
	 * メニュー基本項目一覧作成
	 *
	 * @return なし						
	 */
	function createMenuBasicItemList()
	{
		for ($i = 0; $i < count($this->menuBasicItems); $i++){
			$targetObj = $this->menuBasicItems[$i];

			if ($targetObj['type'] == self::ITEM_TYPE_ADMIN_PAGE){		// 管理機能画面への遷移項目のとき
				$this->tmpl->addVar('menuavailablelist', 'type', 'admin_page');// 管理機能画面
			} else if ($targetObj['type'] == self::ITEM_TYPE_WIDGET_PAGE){		// ウィジェット管理画面への遷移項目のとき
				$this->tmpl->addVar('menuavailablelist', 'type', 'widget');// ウィジェット管理画面
			} else {
			}
			$row = array(
				'item_type' => $targetObj['type'],		// メニュー項目タイプ
				'name' => $targetObj['name'],		// 名前
				'desc' => $targetObj['desc']		// 説明
			);
			$this->tmpl->addVars('menuavailablelist', $row);
			$this->tmpl->parseTemplate('menuavailablelist', 'a');
		}
	}
	/**
	 * メニュー項目リスト、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function menuItemListLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['ni_id'];
		$name = $this->convertToDispString($fetchedRow['ni_name']);
		$task = $fetchedRow['ni_task_id'];
		$param = $fetchedRow['ni_param'];
		$desc = $fetchedRow['ni_help_body'];
		$title = '';	// 項目タイトル
		$option = '';	// 追加情報
		if (empty($fetchedRow['ni_view_control'])){			// 通常項目のとき
			if (strncmp($task, '_', strlen('_')) == 0){		// タイトル項目
				$type = 'type_title';		// 項目タイプ
				$title = 'タイトル';	// 項目タイトル
				$this->tmpl->addVar('menuassignedlist', 'type', 'title');		// タイトル、
			} else if (strncmp($task, self::TASK_ID_HEADE_WIDGET, strlen(self::TASK_ID_HEADE_WIDGET)) == 0){		// ウィジェット管理画面
				$widgetId = str_replace(self::TASK_ID_HEADE_WIDGET, '', $task);
				$widgetName = '';
				for ($i = 0; $i < count($this->widgets); $i++){
					if ($this->widgets[$i]['value'] == $widgetId){
						$widgetName = $this->widgets[$i]['name'];
						break;
					}
				}
				$type = 'type_widget';		// 項目タイプ
				$option = $widgetId;	// 追加情報
				$title = $this->_('Widget') . '[' . $widgetName . ']';		// ウィジェット
				$this->tmpl->addVar('menuassignedlist', 'type', 'subitem');// 管理機能、ウィジェット管理画面
			} else {		// 管理機能画面
				// 管理機能画面情報取得
				$taskName = '';
				for ($i = 0; $i < count($this->adminPages); $i++){
					if ($this->adminPages[$i]['task'] == $task){
						$taskName = $this->adminPages[$i]['name'];
						break;
					}
				}
				$type = 'type_admin';		// 項目タイプ
				$option = $task;	// 追加情報
				$title = $this->_('Administration') . '[' . $taskName . ']';			// 管理機能
				$this->tmpl->addVar('menuassignedlist', 'type', 'subitem');// 管理機能、ウィジェット管理画面
			}
		} else {	// 改行項目のとき
			$type = 'type_cr';		// 項目タイプ
			$title = $this->_('Newline');	// 項目タイトル(改行)
		}
		$row = array(
			'item_type'		=> $type,
			'option'	=> $option,
			'title'		=> $this->convertToDispString($title),
			'name'		=> $name,			// 表示名
			'desc'		=> $desc,			// 説明
			'param'		=> $param,			// 追加パラメータ
			'label_name' => $this->_('Name'),			// 名前
			'label_option' => $this->_('Option'),		// オプション
			'label_desc' => $this->_('Description')		// 説明
		);
		$this->tmpl->addVars('menuassignedlist', $row);
		$this->tmpl->parseTemplate('menuassignedlist', 'a');
		
		$this->menuItemExists = true;			// メニュー項目があるかどうか
		return true;
	}
}
?>
