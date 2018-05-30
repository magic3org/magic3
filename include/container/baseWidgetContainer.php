<?php
/**
 * ウィジェットコンテナ作成用ベースクラス
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
// テンプレートライブラリ読み込み
require_once($gEnvManager->getLibPath() . '/patTemplate/patTemplate.php');
require_once($gEnvManager->getLibPath() . '/patTemplate/patError.php');
require_once($gEnvManager->getLibPath() . '/patTemplate/patErrorManager.php');
require_once($gEnvManager->getCommonPath() . '/core.php');

class BaseWidgetContainer extends Core
{
	protected $_db;	// DB接続オブジェクト
	protected $_widgetType;						// ウィジェットタイプ
	protected $_defaultWidgetId;				// 代替処理用ウィジェットID
	protected $tmpl;		// テンプレートオブジェクト
	protected $parseCancel;			// テンプレート変換処理を中断するかどうか
	protected $displayMessage = true;		// メッセージを出力するかどうか
	protected $messageAttr;					// メッセージ用の追加属性
	protected $dangerMessage	= array();		// 危険メッセージ
	protected $errorMessage		= array();		// アプリケーションのエラー
	protected $warningMessage	= array();		// ユーザ操作の誤り
	protected $infoMessage		= array();		// 情報メッセージ
	protected $guideMessage		= array();		// ガイダンス
	protected $successMessage	= array();		// 成功メッセージ
	protected $optionUrlParam = array();		// URLに付加する値
	protected $localeText = array();			// ローカライズ用テキストの定義
	protected $inputFieldInfo = array();		// 入力フィールド情報
	protected $configMenubarBreadcrumbTitleDef;			// 設定画面用パンくずリストのタイトル定義
	protected $configMenubarMenuDef;					// 設定画面用メニューバーのメニュー定義
	protected $keepForeTaskForBackUrl = false;	// 遷移前のタスクを戻り先URLとするかどうか
	protected $urlParamOrder;					// URLパラメータの並び順
	protected $_configMode;						// 設定画面用の入力設定(TEXTフィールドの自動入力をオフにする等)にするかどうか
	protected $_useFormCheck;						// フォームチェック機能を使用するかどうか
	protected $_usePostToken;						// POSTデータのトークン認証機能を使用するかどうか
	protected $_useHierPage;						// 階層化ページを使用するかどうか
	protected $_isMultiDomain;						// マルチドメイン運用かどうか
	protected $_isSmallDeviceOptimize;				// 小画面デバイス最適化を行うかどうか
	protected $_linkPageCount;						// ページリンク作成用ページ総数
	protected $_renderType;							// 描画出力タイプ
	protected $_renderDetailType;					// 描画出力タイプ(詳細)
	protected $_templateGeneratorType;				// テンプレート作成アプリケーション
	protected $_assignTemplate;						// テンプレート処理置き換えを使用するかどうか
	protected $_assignTemplate_method;				// テンプレート処理置き換え用(_assign()メソッド名)
	protected $_assignTemplate_filename;			// テンプレート処理置き換え用(テンプレートファイル)
	protected $_assignTemplate_hookArray;			// テンプレート処理置き換え用(_assign()メソッド内のフック関数用)
	// POST,GETパラメータ(patTemplateのPostParam用)
	protected $_hiddenTagInfo = array();		// 非表示INPUTタグ作成情報(任意追加用)
	protected $_defConfigId;					// ページ定義のウィジェット定義ID
	protected $_defSerial;						// ページ定義のレコードシリアル番号
	protected $_backUrl;						// 戻り先URL
	protected $_openBy;							// ウィンドウオープンタイプ
	protected $_isCmdAccess;					// cmd付きアクセスかどうか
	protected static $_token;					// 1リクエストで共通のトークンを使用する
	// 現在の値
	protected $_widgetId;		// 現在のウィジェットID
	protected $_langId;			// 現在の言語
	protected $_userId;			// 現在のユーザ
	protected $_now;			// 現在日時
	protected $_configParamObj;	// 設定画面のパラメータオブジェクト
	
	// テンプレート置き換え処理用
	protected $_localParamObj;					// パラメータオブジェクト
	protected $_localSerialArray;					// シリアル番号
	
	const PASSWORD_LENGTH = 8;		// パスワード長
	const HELP_HEAD = '_help_';		// ヘルプ埋め込み用タグのヘッダ部
	const LOCAL_TEXT_HEAD = '_lc_';		// ローカライズテキストタグのヘッダ部
	const LOCAL_TYPE_SYSTEM = 'system';	// ローカライズテキストのタイプ(システム用言語)
	const DETECT_GOOGLEMAPS = 'Magic3 googlemaps v';		// Googleマップ検出用文字列
	const CF_HIERARCHICAL_PAGE = 'hierarchical_page';		// 階層化ページを使用するかどうか
	const CF_DEFAULT_H_TAG_LEVEL = 'default_h_tag_level';	// ウィジェットのコンテンツ用のHタグレベルデフォルト値
	
	// メッセージの種別
	const MSG_APP_ERR  = 1;		// アプリケーションエラー
	const MSG_USER_ERR = 2;		// ユーザ操作エラー
	const MSG_GUIDANCE = 3;		// ガイダンス
	
	// 設定画面用
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
		
	// テンプレート処理置き換え用
	const ASSIGN_TEMPLATE_DIR = '/widgets/template';		// // テンプレート読み込みディレクトリ
	// 出力パターン
	const ASSIGN_TEMPLATE_BASIC_CONFIG_LIST				= 'BASIC_CONFIG_LIST';					// 設定一覧(基本)
	const ASSIGN_TEMPLATE_BASIC_CONFIG_LIST_WITH_IFRAME	= 'BASIC_CONFIG_LIST_WITH_IFRAME';		// IFRAME画面付き(タブ切り替え)の設定一覧(基本)
	// メソッド名
	const ASSIGN_TEMPLATE_METHOD_BASIC_CONFIG_LIST		= 'assignTemplate_createList';					// テンプレート処理置き換え用(_assign())
	// テンプレートファイル名
	const ASSIGN_TEMPLATE_FILENAME_BASIC_CONFIG_LIST				= 'admin_list.tmpl.html';					// 設定一覧(基本)
	const ASSIGN_TEMPLATE_FILENAME_BASIC_CONFIG_LIST_WITH_IFRAME	= 'admin_list_with_iframe.tmpl.html';		// IFRAME画面付き(タブ切り替え)の設定一覧(基本)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// データ初期化
		$this->_widgetType = '';						// ウィジェットタイプ
		$this->_defaultWidgetId = '';				// 代替処理用ウィジェットID
		
		// DBオブジェクト取得
		$this->_db = $this->gInstance->getSytemDbObject();
		
		// URLパラメータ並び順
		$this->urlParamOrder = $this->gPage->getUrlParamOrder();
		
		// 各種設定取得
		$this->_useHierPage = $this->gSystem->hierarchicalPage();	// 階層化ページ
		$this->_isMultiDomain = $this->gEnv->isMultiDomain();			// マルチドメイン運用かどうか

		// 現在の値取得
		$this->_widgetId	= $this->gEnv->getCurrentWidgetId();		// 現在のウィジェットID
		$this->_langId		= $this->gEnv->getCurrentLanguage();		// 現在の言語
		$this->_userId		= $this->gEnv->getCurrentUserId();			// 現在のユーザ
		$this->_now			= date("Y/m/d H:i:s");						// 現在日時
		
		// 描画出力タイプ
		$templateType = $this->gEnv->getCurrentTemplateType();
		$this->_renderType = $this->_getRenderType($templateType);
/*		switch ($templateType){
			case 0:
				$this->_renderType = M3_RENDER_JOOMLA_OLD;		// Joomla! 1.0テンプレート
				break;
			case 10:
				$this->_renderType = M3_RENDER_BOOTSTRAP;		// Bootstrap 3.0テンプレート
				break;
			case 20:
				$this->_renderType = M3_RENDER_JQUERY_MOBILE;		// jQuery Mobileテンプレート
				break;
			default:
				$this->_renderType = M3_RENDER_JOOMLA_NEW;		// Joomla! 1.5以上のテンプレート
				break;
		}*/
		// テンプレート作成アプリケーション
		$this->_templateGeneratorType = $this->gEnv->getCurrentTemplateGenerator();
		
		// URL変換用コールバック関数を設定
		$this->gDesign->_setGetUrlCallback(array($this, 'getUrl'));
	}
	/**
	 * 起動マネージャから呼ばれる唯一のメソッド
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function process($request)
	{
		$useTemplate = false;		// テンプレートライブラリを使用するかどうか
		
		// 管理画面へのアクセスかどうか
		$isAdminDirAccess = $this->gEnv->isAdminDirAccess();
			
		// ウィジェット単位のアクセス制御
		if (method_exists($this, '_checkAccess')){
			// アクセス不可のときはここで終了
			if (!$this->_checkAccess($request)) return;
		}
	
		// ディスパッチ処理
		if (method_exists($this, '_dispatch')){
			// 処理を継続しない場合は終了
			if (!$this->_dispatch($request, $param)) return;
		}
		
		// POST,GETパラメータ取得
		$this->_openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);			// 処理タスク
		$this->_isCmdAccess = $request->isCmdAccess();			// cmd付きアクセスかどうか
		
		// 引き継ぎパラメータを退避
		$this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $this->_openBy);		// ウィンドウオープンタイプ
		
		// ##### 初期処理 #####
		// 独自のウィジェットメイン処理を行う場合は、_init()で設定を行う
		if (method_exists($this, '_preInit')) $this->_preInit($request, $task);		// ベースクラス用
		if (method_exists($this, '_init')) $this->_init($request, $task);
		if (method_exists($this, '_postInit')) $this->_postInit($request, $task);		// ベースクラス用
		
		// ##### ウィジェットメイン処理 #####
		if (!$this->parseCancel &&														// テンプレート変換しない場合はすべてのテンプレート処理をキャンセル
			(method_exists($this, '_setTemplate') || $this->_assignTemplate)){			// テンプレートがあるか、テンプレート処理置き換えを使用するとき
			// テンプレートファイル名を取得
			// $paramは、任意使用パラメータ
			if ($this->_assignTemplate){
				$templateFile = $this->_assignTemplate_filename;
			} else {
				$templateFile = $this->_setTemplate($request, $param);
			}
			
			// テンプレートファイル名が空文字列のときは、テンプレートライブラリを使用しない
			if (!empty($templateFile)){
				// テンプレートオブジェクト作成
				$this->__setTemplate($this->_assignTemplate);
				
				// テンプレートファイルを設定
				$this->tmpl->readTemplatesFromFile($templateFile);

				// マクロ変換、ヘルプ組み込み
				$this->__assign();
				
				$useTemplate = true;		// テンプレートライブラリを使用するかどうか
			}
			// 管理画面へのアクセスの場合は管理用POST値を設定
			if ($isAdminDirAccess){
				// ウィジェット定義IDとページ定義のレコードシリアル番号をURLから取得
				$this->_defConfigId = $this->getPageDefConfigId($request);	// ページ定義のウィジェット定義ID
				$this->_defSerial = $this->getPageDefSerial($request);		// ページ定義のレコードシリアル番号

				// POST値が設定されている場合は上書き
				$value = $request->trimValueOf('_pdefconfig');		// ページ定義のウィジェット定義ID
				if (!empty($value)) $this->_defConfigId = $value;
				$value = $request->trimValueOf('_pdefserial');		// ページ定義のレコードシリアル番号
				if (!empty($value)) $this->_defSerial = $value;
			}
				
			// 各ウィジェットごとのテンプレート処理、テンプレートを使用しないときは出力処理(Ajax等)
			if (method_exists($this, '_preAssign')) $this->_preAssign($request, $param);
			
			// 各ウィジェットごとのテンプレート処理、テンプレートを使用しないときは出力処理(Ajax等)
			if (method_exists($this, '_assign') || $this->_assignTemplate){
				if ($this->_assignTemplate){		// テンプレート処理置き換えを使用
					if (is_callable($this->_assignTemplate_method)) call_user_func($this->_assignTemplate_method, $request, $param);
				} else {
					$this->_assign($request, $param);
				}
				
				if (!empty($templateFile)){		// テンプレートを使用する場合
					// 管理画面へのアクセスの場合は管理用POST値を設定
					if ($isAdminDirAccess){
						// 画面定義用の情報を戻す
						if (!empty($this->_defSerial) && $useTemplate){		// シリアル番号が空のときは、ページ作成からの遷移ではないので、引き継がない
							$this->tmpl->addVar("_widget", "_def_config", $this->_defConfigId);	// ページ定義のウィジェット定義ID
							$this->tmpl->addVar("_widget", "_def_serial", $this->_defSerial);	// ページ定義のレコードシリアル番号
						}
					
						// 戻り先URL。GETで遷移してきた場合のみ戻り先を取得。
						if ($request->isGetMethod()){
							if ($this->keepForeTaskForBackUrl){		// 遷移前のタスクを戻り先URLとする場合
								// 戻り先URLを取得
								$value = $request->trimValueOf('_backurl');		// 戻り先URL
								if (!empty($value)) $this->_backUrl = $value;

								// リファラーを解析
								$backUrl = '';
								if (isset($_SERVER["HTTP_REFERER"])) $backUrl = $_SERVER["HTTP_REFERER"];
								$queryArray = array();
								$parsedUrl = parse_url($backUrl);
								if (!empty($parsedUrl['query'])) parse_str($parsedUrl['query'], $queryArray);		// クエリーの解析
								$oldTask = $queryArray['task'];
								$newTask = $request->trimValueOf('task');		// 現在のタスク
							
								// タスクに変更があった場合のみ戻り先URLを更新
								if ($oldTask != $newTask) $this->_backUrl = $backUrl;
							} else {
								if (isset($_SERVER["HTTP_REFERER"])) $this->_backUrl = $_SERVER["HTTP_REFERER"];
							}
						} else {		// POSTの場合は既存値を取得
							$value = $request->trimValueOf('_backurl');		// 戻り先URL
							if (!empty($value)) $this->_backUrl = $value;
						}
						if (!empty($this->_backUrl) && $useTemplate) $this->tmpl->addVar("_widget", "_back_url", $this->convertToDispString($this->_backUrl));	// 戻り先URL
					}
				
					// フォームチェック機能を使用するか、システム管理権限がある場合は、フォームIDを埋め込む
					if ($this->_useFormCheck || $this->gEnv->isSystemManageUser()){
						// 現在のウィジェットのポジションを取得
						$this->gPage->getCurrentWidgetPosition($position, $index);
					
						$formId = md5($this->_widgetId . '-' . $position . '-'  . $index);
						$this->tmpl->addVar("_widget", "_form_id", $formId);
					}
				}
			}
				
			// 各ウィジェットごとのテンプレート処理、テンプレートを使用しないときは出力処理(Ajax等)
			if (method_exists($this, '_postAssign')) $this->_postAssign($request, $param);
			
			// サブメニューバーの作成
			if ($isAdminDirAccess || $this->gPage->idEditMode()){			// 管理画面あるいは、フロント画面編集モードオンの場合
				// 設定画面用パンくずリストの作成
				if (!empty($this->configMenubarBreadcrumbTitleDef)) $this->gPage->setAdminBreadcrumbDef($this->configMenubarBreadcrumbTitleDef);
				
				// 設定画面用メニューバーの作成
				if (!empty($this->configMenubarMenuDef)){
					$navbarDef = new stdClass;
					$navbarDef->title = $this->gEnv->getCurrentWidgetTitle();		// ウィジェット名
					if ($isAdminDirAccess){
						$navbarDef->baseurl = $this->getAdminUrlWithOptionParam(true);	// 定義ID,画面定義シリアル番号を付加
					} else {
						$navbarDef->baseurl = $this->getUrlWithOptionParam();
					}
//					$navbarDef->help	= $this->gInstance->getHelpManager()->createHelpText('ウィジェットの設定画面',
//								'<strong>●' . M3_TITLE_BRACKET_START . $navbarDef->title . M3_TITLE_BRACKET_END . 'ウィジェットの機能</strong><br />' . $this->gEnv->getCurrentWidgetParams('desc'));// ヘルプ文字列
					$navbarDef->help	= $this->_createWidgetInfoHelp();		// ウィジェットの説明用ヘルプ
					$navbarDef->menu = $this->configMenubarMenuDef;
					$this->gPage->setAdminSubNavbarDef($navbarDef);
				}
			}
			
			// テキストのローカライズ
			if ($useTemplate){
				$localKeys = array_keys($this->localeText);			// ローカライズ用定義
				for ($i = 0; $i < count($localKeys); $i++){
					$key = $localKeys[$i];
					$localText = $this->localeText[$key];			// ロケールファイルからテキストを取得
					$this->tmpl->addGlobalVar(self::LOCAL_TEXT_HEAD . $key, $localText);		// グローバルパラメータを文字列変換
				}
			}

			// 各ウィジェットごとの追加スクリプトライブラリを取得し、ヘッダに設定
			if ($isAdminDirAccess){		// 管理画面へのアクセスの場合のライブラリ追加処理
				if (method_exists($this, '_addScriptLibToHead')){
					$scriptLib = $this->_addScriptLibToHead($request, $param);
				
					// ライブラリを追加
					if (!empty($scriptLib)) $this->gPage->addHeadAdminScriptLib($scriptLib);	// 管理画面用ライブラリ
				}
			} else {
				if (method_exists($this, '_addScriptLibToHead')){
					$scriptLib = $this->_addScriptLibToHead($request, $param);
				
					// ライブラリを追加
					if (!empty($scriptLib)) $this->gPage->addHeadScriptLib($scriptLib);
				}
			}
			// 各ウィジェットごとの追加CSSファイルを取得し、ヘッダに設定
			if (method_exists($this, '_addCssFileToHead')){
				$cssFile = $this->_addCssFileToHead($request, $param);
				
				// ヘッダにCSSを追加
				if (!empty($cssFile)) $this->gPage->addHeadCssFile($cssFile);
			}
			// 各ウィジェットごとの追加Javascriptを取得し、ヘッダに設定
			if (method_exists($this, '_addScriptFileToHead')){
				$scriptFile = $this->_addScriptFileToHead($request, $param);
				
				// ヘッダにJavascriptを追加
				if (!empty($scriptFile)) $this->gPage->addHeadScriptFile($scriptFile);
			}
			// 各ウィジェットごとの追加Javascript(jQueryMobile用挿入ファイル)を取得し、ヘッダに設定
			if (method_exists($this, '_addPreMobileScriptFileToHead')){
				$scriptFile = $this->_addPreMobileScriptFileToHead($request, $param);
				
				// ヘッダにJavascriptを追加
				if (!empty($scriptFile)) $this->gPage->addHeadPreMobileScriptFile($scriptFile);
			}
			// RSS情報を取得し、ヘッダに設定
			if (method_exists($this, '_addRssFileToHead')){
				$rssFileInfo = $this->_addRssFileToHead($request, $param);
				
				// ヘッダにRSSを追加
				if (!empty($rssFileInfo)) $this->gPage->addHeadRssFile($rssFileInfo);
			}
			
			// 各ウィジェットごとのCSSを取得し、ヘッダに設定
			if (method_exists($this, '_addCssToHead')){
				$css = $this->_addCssToHead($request, $param);
				
				// ヘッダにCSSを追加
				if (!empty($css)) $this->gPage->addHeadCss($css);
			}
			// 各ウィジェットごとのJavascriptを取得し、ヘッダに設定
			if (method_exists($this, '_addScriptToHead')){
				$script = $this->_addScriptToHead($request, $param);
				
				// ヘッダにJavascriptを追加
				if (!empty($script)) $this->gPage->addHeadScript($script);
			}
			// 各ウィジェットごとのJavascriptを取得し、ヘッダに設定
			if (method_exists($this, '_addPreMobileScriptToHead')){
				$script = $this->_addPreMobileScriptToHead($request, $param);
				
				// ヘッダにJavascriptを追加
				if (!empty($script)) $this->gPage->addHeadPreMobileScript($script);
			}
			// 各ウィジェットごとのHead追加文字列を取得し、ヘッダに設定
			if (method_exists($this, '_addStringToHead')){
				$str = $this->_addStringToHead($request, $param);
				
				// ヘッダにJavascriptを追加
				if (!empty($str)) $this->gPage->addHeadString($str);
			}
			// ウィジェットのタイトル設定処理
			if (method_exists($this, '_setTitle')){
				$str = $this->_setTitle($request, $param);
				
				// ウィジェットタイトルを設定
				if (!empty($str)) $this->gEnv->setCurrentWidgetTitle($str);
			}
			// ヘッダ部メタタグの設定
			if (method_exists($this, '_setHeadMeta')){
				$headData = $this->_setHeadMeta($request, $param);
				if (!empty($headData)){			// タグを設定する場合
					$this->gPage->setHeadSubTitle($headData['title']);
					$this->gPage->setHeadDescription($headData['description']);
					$this->gPage->setHeadKeywords($headData['keywords']);
				}
			}
			if ($useTemplate){
				// エラーメッセージ出力
				if ($this->displayMessage) $this->displayMsg();
	
				// HTML生成
				if (!$this->parseCancel) $this->__parse();		// _assign()でキャンセルされた場合はHTML出力しない
			}
//		} else {	// メソッドが存在しないときはエラーメッセージを出力
//			echo 'method not found: BaseWidgetContainer::_setTemplate()';
		}
	}
	/**
	 * テンプレートファイルの設定
	 * 
	 * @param bool $useSystemTemplate		システムの標準テンプレートを使用するかどうか
	 * @return 								なし
	 */
	function __setTemplate($useSystemTemplate = false)
	{
		global $gEnvManager;
		
		// テンプレートオブジェクト作成
		$this->tmpl = new PatTemplate();
 
		// ##### テンプレート読み込みディレクトリ #####
		$dirArray = array();
		
		if ($useSystemTemplate){	// システムの標準テンプレートを使用する場合
			$dir = $this->gEnv->getPrivateResourcePath() . self::ASSIGN_TEMPLATE_DIR;
			if (file_exists($dir)) $dirArray[] = $dir;
		} else {
			// カレントウィジェットのディレクトリを追加
			$dir = $this->gEnv->getCurrentWidgetTemplatePath();
			if (file_exists($dir)) $dirArray[] = $dir;
		
			// 代替処理ウィジェットが設定されている場合はディレクトリを追加
			if (!empty($this->_defaultWidgetId)){
				$dir = $this->gEnv->getWidgetsPath() . '/' . $this->_defaultWidgetId . '/include/template';				// 代替処理用ウィジェットID
				if (file_exists($dir)) $dirArray[] = $dir;
			}
		}

		$this->tmpl->setRoot($dirArray);
		
		// エラーメッセージテンプレートを埋め込む
		$this->tmpl->applyInputFilter('ErrorMessage');
		
		// 機能付きタグを変換
		//$this->tmpl->applyInputFilter('FunctionTag');
		
		// コメントを削除
		//$this->tmpl->applyInputFilter('StripComments');
		
		// フォームチェック機能を使用するか、システム管理権限がある場合は、管理画面用パラメータを埋め込む
//		if ($this->_useFormCheck || $gEnvManager->isSystemManageUser()) $this->tmpl->applyInputFilter('PostParam');
		$this->tmpl->applyInputFilter('PostParam');
	}
	/**
	 * 出力用の変数に値を設定する
	 * このクラスでは、共通項目を設定
	 */
	function __assign()
	{
		// システム用変数のデフォルト変換
		$rootUrl = $this->gEnv->getRootUrl();
		$currentWidgetUrl = $this->gEnv->getCurrentWidgetRootUrl();
		if ($this->gEnv->getUseSsl()){		// SSLを使用する場合
			// 現在のページがSSL使用設定になっているかどうかを取得
			$isSslPage = $this->gPage->isSslPage($this->gEnv->getCurrentPageId(), $this->gEnv->getCurrentPageSubId());
			if ($isSslPage){
				//$rootUrl = str_replace('http://', 'https://', $rootUrl);
				//$currentWidgetUrl = str_replace('http://', 'https://', $currentWidgetUrl);
				$rootUrl = $this->gEnv->getSslRootUrl();
				$currentWidgetUrl = $this->gEnv->getCurrentWidgetSslRootUrl();
			}
		}
		$this->tmpl->addVar("_widget", "_ROOT_URL", $rootUrl);
		$this->tmpl->addVar("_widget", "_WIDGET_URL", $currentWidgetUrl);	// ウィジェットのルートディレクトリ
		
		// デバッグ出力があるときは表示
		if ($this->gEnv->getSystemDebugOut() && method_exists($this,'_debugString')){
			$debugStr = $this->_debugString();
			if (strlen($debugStr) > 0){
				$this->tmpl->addVar("_widget", "_DEBUG", $debugStr);
			}
		}
	}
	/**
	 * 出力データ作成
	 */
	function __parse()
	{
		echo $this->tmpl->getParsedTemplate('_widget');
	}
	/**
	 * テンプレート変換処理中断
	 *
	 * @return 							なし
	 */
	function cancelParse()
	{
		$this->parseCancel = true;			// テンプレート変換処理を中断するかどうか
	}
	/**
	 * リスト出力型のテンプレートの表示状態を修正
	 *
	 * @param string $templateName			テンプレート名
	 * @return 								なし
	 */
	function setListTemplateVisibility($templateName)
	{
		// 出力がある場合はテンプレート出力を表示
		$isExistsOutput = $this->tmpl->_templates[$templateName]['parsed'];
		if ($isExistsOutput){
			$this->tmpl->setAttribute($templateName, 'visibility', 'visible');	// 表示
		} else {
			$this->tmpl->setAttribute($templateName, 'visibility', 'hidden');	// 非表示
		}
	}
	/**
	 * テンプレートファイルを強制入れ替え
	 *
	 * @param string $templateFilename		テンプレートファイル名
	 * @return 								なし
	 */
	function replaceTemplateFile($templateFilename)
	{
		// 現在設定されているテンプレートをキャンセル
		$this->tmpl->freeAllTemplates();
		
		// テンプレートファイルを再設定
		$this->tmpl->readTemplatesFromFile($templateFilename);
	}
	/**
	 * テンプレート処理置き換え
	 *
	 * @param string $outputType			テンプレート処理タイプ
	 * @param array $hook					フック関数(フック取得キーと関数名の連想配列)
	 * @return 								なし
	 */
	function replaceAssignTemplate($outputType, $hook = null)
	{
		switch ($outputType){
		case self::ASSIGN_TEMPLATE_BASIC_CONFIG_LIST:		// 設定一覧(基本)
			$this->_assignTemplate			= true;		// テンプレート処理置き換えを使用
			$this->_assignTemplate_method	= array($this, self::ASSIGN_TEMPLATE_METHOD_BASIC_CONFIG_LIST);					// テンプレート処理置き換え用(_assign())
			$this->_assignTemplate_filename = self::ASSIGN_TEMPLATE_FILENAME_BASIC_CONFIG_LIST;				// テンプレート処理置き換え用(ファイル名)
			break;
		case self::ASSIGN_TEMPLATE_BASIC_CONFIG_LIST_WITH_IFRAME:		// IFRAME画面付き(タブ切り替え)の設定一覧(基本)
			$this->_assignTemplate			= true;		// テンプレート処理置き換えを使用
			$this->_assignTemplate_method	= array($this, self::ASSIGN_TEMPLATE_METHOD_BASIC_CONFIG_LIST);				// テンプレート処理置き換え用(_assign())
			$this->_assignTemplate_filename = self::ASSIGN_TEMPLATE_FILENAME_BASIC_CONFIG_LIST_WITH_IFRAME;				// IFRAME画面付き(タブ切り替え)の設定一覧(基本)
			
			// フック関数
			if (!empty($hook)) $this->_assignTemplate_hookArray = $hook;			// テンプレート処理置き換え用(_assign()メソッド内のフック関数用)
			break;
		}
	}
	/**
	 * テンプレートタイプから描画出力タイプを取得
	 *
	 * @param string $templateType	テンプレートタイプ
	 * @return string				描画出力タイプ
	 */
	function _getRenderType($templateType)
	{
		switch ($templateType){
			case 0:
				$renderType = M3_RENDER_JOOMLA_OLD;		// Joomla! 1.0テンプレート
				break;
			case 10:
				$renderType = M3_RENDER_BOOTSTRAP;		// Bootstrap 3.0テンプレート
				break;
			case 20:
				$renderType = M3_RENDER_JQUERY_MOBILE;		// jQuery Mobileテンプレート
				break;
			case 100:
				$renderType = M3_RENDER_WORDPRESS;		// WordPressテンプレート
				break;
			default:
				$renderType = M3_RENDER_JOOMLA_NEW;		// Joomla! 1.5以上のテンプレート
				break;
		}
		return $renderType;
	}
	/**
	 * テンプレートIDからテンプレートタイプを取得
	 *
	 * @param string $templateId	テンプレートID
	 * @return string				テンプレートタイプ
	 */
	function _getTemplateType($templateId)
	{
		$templateType = '';
		if ($this->_db->getTemplate($templateId, $row)) $templateType = $row['tm_type'];		// テンプレートタイプ
		return $templateType;
	}
	/**
	 * patTemplateのPostParamを使って、非表示INPUTタグを追加
	 *
	 * 注意)_setTemplate()またはそれ以前に呼び出す。
	 *
	 * @param string $name			パラメータ名
	 * @param string $replaceKey	置換キー文字列
	 * @return 						なし
	 */
	function _addHiddenTag($name, $replaceKey)
	{
		$this->_hiddenTagInfo[$name] = $replaceKey;
	}
	/**
	 * 非表示INPUTタグ情報を取得(patTemplateのPostParam用)
	 *
	 * @return array 			パラメータの配列
	 */
	function _getHiddenTagInfo()
	{
		return $this->_hiddenTagInfo;
	}
	/**
	 * 設定画面用の入力設定にする(フロント画面用)
	 *
	 * ・TEXTフィールドの自動入力をオフにする
	 *
	 * @return なし
	 */
	function setConfigMode()
	{
		$this->_configMode = true;
	}
	/**
	 * 設定画面用の入力設定にするかどうかを取得(フロント画面用)
	 *
	 * @return bool			true=設定画面用の入力設定にする、false=設定画面用の入力設定にしない
	 */
	function getConfigMode()
	{
		return $this->_configMode;
	}
	/**
	 * フォームチェック機能を使用
	 *
	 * @return なし
	 */
	function setUseFormCheck()
	{
		$this->_useFormCheck = true;
	}
	/**
	 * フォームチェック機能を使用するかどうかを取得
	 *
	 * @return bool		true=使用する、false=使用しない
	 */
	function getUseFormCheck()
	{
		return $this->_useFormCheck;
	}
	/**
	 * フォームIDをチェック
	 *
	 * @return bool		true=正常、false=不正
	 */
	function checkFormId()
	{
		$formId = $this->gRequest->trimValueOf(M3_REQUEST_PARAM_FORM_ID);
		if (empty($formId)){
			return false;
		} else {
			// 現在のウィジェットのポジションを取得
			$this->gPage->getCurrentWidgetPosition($position, $index);
			
			$currentFormId = md5($this->_widgetId . '-' . $position . '-'  . $index);
			if ($currentFormId == $formId){
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 * POSTデータのトークン認証機能を使用するかどうかを取得
	 *
	 * @return bool		true=正常、false=不正
	 */
	function getUsePostToken()
	{
		return $this->_usePostToken;
	}
	/**
	 * Postデータのトークン認証機能を初期化
	 *
	 * @return なし
	 */
	function initPostToken()
	{
		// トークン認証機能を使用
		$this->_usePostToken = true;
		
		// フォームチェック機能使用
		$this->setUseFormCheck();
	}
	/**
	 * Postデータのトークン認証機能を開始
	 *
	 * @param bool $updateToken			トークンを更新するかどうか
	 * @return なし
	 */
	function openPostToken($updateToken = false)
	{
		// トークンを生成し、セッションと画面に書き出す
		if (!isset(self::$_token) || $updateToken) self::$_token = md5(time() . $this->gAccess->getAccessLogSerialNo());
		$this->gRequest->setSessionValue(M3_SESSION_POST_TOKEN, self::$_token);		// セッションに保存
		$this->tmpl->addVar('_widget', '_token', self::$_token);				// 画面に書き出し
	}
	/**
	 * Postデータのトークン認証機能を終了
	 *
	 * @return なし
	 */
	function closePostToken()
	{
		// トークン認証機能を終了
		$this->_usePostToken = false;
		
		$this->gRequest->unsetSessionValue(M3_SESSION_POST_TOKEN);		// セッション値をクリア
	}
	/**
	 * Postデータのトークン認証チェックを行う
	 *
	 * @return bool		true=正常、false=不正
	 */
	function verifyPostToken()
	{
		// POSTでの処理かどうかチェック
		if (!$this->gRequest->isPostMethod()) return false;
		
		// 該当するフォームかどうかチェック
		$checkForm = $this->checkFormId();
		if (!$checkForm) return false;
		
		// リファラーをチェック
		$referer	= $this->gRequest->trimServerValueOf('HTTP_REFERER');
		$uri		= $this->gEnv->getCurrentRequestUri();
		if (empty($referer) || $referer != $uri) return false;
		
		// トークンをチェック
		$token = $this->gRequest->trimValueOf(M3_REQUEST_PARAM_TOKEN);		// POST確認用
		if (!empty($token) && $token == $this->gRequest->getSessionValue(M3_SESSION_POST_TOKEN)){		// 正常なPOST値のとき
			return true;
		} else {
			return false;
		}
	}
	/**
	 * POST時のリファラーをチェックする(CSRF対策用)
	 *
	 * @return bool		true=正常、false=不正
	 */
	function checkSafePost()
	{
		// POSTでの処理かどうかチェック
		if (!$this->gRequest->isPostMethod()) return false;
		
		// リファラーをチェック
		$referer	= $this->gRequest->trimServerValueOf('HTTP_REFERER');
		$uri		= $this->gEnv->getCurrentRequestUri();
		if (empty($referer) || $referer != $uri){
			return false;
		} else {
			return true;
		}
	}
	/**
	 * 遷移前のタスクを戻り先URLとするに設定
	 *
	 * @return なし
	 */
	function setKeepForeTaskForBackUrl()
	{
		$this->keepForeTaskForBackUrl = true;				// 遷移前のタスクを戻り先URLとするかどうか
	}
	/**
	 * 代替処理用ウィジェットIDを設定
	 *
	 * @param string $widgetId		ウィジェットID
	 * @return なし
	 */
	function setDefaultWidgetId($widgetId)
	{
		$this->_defaultWidgetId = $widgetId;
	}
	/**
	 * 画面編集からの起動かどうかを取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return bool							true=画面編集からの起動、false=画面編集以外からの起動
	 */
	function getLaunchFromPagedef($request)
	{
		$launchFromPagedef = false;
		
		// ウィジェット定義IDとページ定義のレコードシリアル番号をURLから取得
		$defConfigId = $this->getPageDefConfigId($request);	// ページ定義のウィジェット定義ID
		$defSerial = $this->getPageDefSerial($request);		// ページ定義のレコードシリアル番号
		if (!empty($defSerial)) $launchFromPagedef = true;		// 画面編集からの起動かどうか
		return $launchFromPagedef;
	}
	/**
	 * Ajaxでのファイルアップロード処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param function $callback			コールバック関数
	 * @param string   $destDir				アップロード先ディレクトリ
	 * @param int      $maxFileSize			アップロード可能最大ファイルサイズ
	 * @param bool     $autoDel				アップロードファイルの自動削除
	 * @return 								なし
	 */
	function ajaxUploadFile($request, $callback, $destDir, $maxFileSize = null, $autoDel = true)
	{
		require_once($this->gEnv->getCommonPath() . '/uploadFile.php' );			// ファイルアップロード受信ライブラリ
		
		// ファイルアップロード処理
		$uploader = new uploadFile(array()/*アップロード可能ファイル拡張子*/, $maxFileSize);
		$resultObj = $uploader->handleUpload($destDir);
		$isSuccess = $resultObj['success'];
		
		$filePath = '';
		if ($isSuccess){
			$fileInfo = $resultObj['file'];
			$filePath = $fileInfo['path'];
		}
		// コールバック関数を呼び出す
//		if (is_callable($callback)) call_user_func($callback, $isSuccess, $resultObj, $request, $filePath, $destDir);		// 参照渡しできない
		if (is_callable($callback)) call_user_func_array($callback, array($isSuccess, &$resultObj, $request, $filePath, $destDir));
		
		// アップロードファイル削除
		if ($autoDel && $isSuccess && file_exists($filePath)) unlink($filePath);

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
	}
	/**
	 * Ajaxファイルアップロード処理時のエラー通知用
	 *
	 * @param string   $msg				エラーメッセージ
	 * @return 							なし
	 */
	function ajaxUploadFileError($msg)
	{
		$resultObj = array('error' => $msg);
		
		// ##### 添付ファイルアップロード結果を返す #####
		// ページ作成処理中断
		$this->gPage->abortPage();
		
		// 添付ファイルの登録データを返す
		if (function_exists('json_encode')){
			$destStr = json_encode($resultObj);
		} else {
			$destStr = $this->gInstance->getAjaxManager()->createJsonString($resultObj);
		}

		header('Content-Type: text/html; charset=UTF-8');		// JSONタイプを指定するとIE8で動作しないのでHTMLタイプを指定
		echo $destStr;
		
		// システム強制終了
		$this->gPage->exitSystem();
	}
	/**
	 * 描画出力タイプをリスト型に設定
	 *
	 * @return bool				true=成功、false=失敗
	 */
	function selectListRender()
	{
		$retStatus = false;
		
		switch ($this->_renderType){
			case M3_RENDER_JOOMLA_OLD:		// Joomla! 1.0テンプレート
			case M3_RENDER_BOOTSTRAP:		// Bootstrap 3.0テンプレート
			case M3_RENDER_JQUERY_MOBILE:	// jQuery Mobileテンプレート
			default:
				break;
			case M3_RENDER_JOOMLA_NEW:		// Joomla! 1.5以上のテンプレート(デフォルト)
				$this->gEnv->setCurrentRenderType('category');		// Joomla!テンプレートcom_contentのcategoryタイプ
				$this->_renderDetailType = 'list';					// 描画出力タイプ(詳細)
		
				$retStatus = true;
				break;
		}
		return $retStatus;
	}
	/**
	 * ウィジェットの説明のヘルプ文字列を作成
	 *
	 * @param string $templateName		テンプレート名
	 * @return string					ヘルプ文字列
	 */
	function _createWidgetInfoHelp()
	{
		$widgetTitle = $this->gEnv->getCurrentWidgetTitle();		// ウィジェット名
		$help	= $this->gInstance->getHelpManager()->createHelpText('ウィジェットの設定画面',
					'<strong>' . M3_TITLE_BRACKET_START . $widgetTitle . M3_TITLE_BRACKET_END . 'ウィジェット</strong><br />' .
					'<strong>●機能</strong><br />' . $this->gEnv->getCurrentWidgetParams('desc'));// ヘルプ文字列
		return $help;
	}
	/**
	 * patTemplateを使用した汎用データ変換処理
	 *
	 * ウィジェットのテンプレートディレクトリ内のテンプレートファイルを読み込みデータ変換後、文字列を返す。
	 *
	 * @param string $filename		テンプレートファイル名
	 * @param function $callback	コールバック関数。「callback($tmpl) $tmpl=テンプレートオブジェクト」の形式で呼ばれる。
	 * @param object $param			任意パラメータ
	 * @param string $templateName	テンプレート名(デフォルトは「_tmpl」)
	 * @return string				変換後データ
	 */
	function getParsedTemplateData($filename, $callback = NULL, $param = NULL, $templateName = '_tmpl')
	{
		// テンプレートオブジェクト作成
		$tmpl = new PatTemplate();

		// ##### テンプレート読み込みディレクトリ #####
		$dirArray = array();
		
		// カレントウィジェットのディレクトリを追加
		$dir = $this->gEnv->getCurrentWidgetTemplatePath();
		if (file_exists($dir)) $dirArray[] = $dir;
		
		// 代替処理ウィジェットが設定されている場合はディレクトリを追加
		if (!empty($this->_defaultWidgetId)){
			$dir = $this->gEnv->getWidgetsPath() . '/' . $this->_defaultWidgetId . '/include/template';				// 代替処理用ウィジェットID
			if (file_exists($dir)) $dirArray[] = $dir;
		}

		$tmpl->setRoot($dirArray);
		
		// テンプレートファイルを設定
		$tmpl->readTemplatesFromFile($filename);
		
		// コールバック関数を呼び出す
		if (is_callable($callback)) call_user_func($callback, $tmpl, $param);
		
		return $tmpl->getParsedTemplate($templateName);
	}
	/**
	 * 表示用文字列に変換
	 *
	 * @param string $src 		変換元文字列
	 * @param bool $keepTags	HTMLタグを変換するかどうか
	 * @return string			変換後文字列
	 */
	function convertToDispString($src, $keepTags = false)
	{
		// 変換文字「&<>"」
		//return htmlentities($src, ENT_COMPAT, M3_HTML_CHARSET);
		return convertToHtmlEntity($src, $keepTags);
	}
	/**
	 * ボタン用無効(disabled)文字列に変換
	 *
	 * @param string $src 		変換値(bool型またはint,string型(0または1))
	 * @return string			「disabled」または空文字列
	 */
	function convertToDisabledString($src)
	{
		$disabled = '';
		if (!empty($src)) $disabled = 'disabled';
		return $disabled;
	}
	/**
	 * チェックボックス、ラジオボタン選択用文字列に変換
	 *
	 * @param string $src 		変換値(bool型またはint,string型(0または1))
	 * @return string			「checked」または空文字列
	 */
	function convertToCheckedString($src)
	{
		$checked = '';
		if (!empty($src)) $checked = 'checked';
		return $checked;
	}
	/**
	 * SELECTメニュー選択用文字列に変換
	 *
	 * @param string $src 			変換値(bool型またはint,string型(0または1))
	 * @param string $compareValue 	比較値
	 * @return string				「selected」(値が同じ場合)または空文字列(値が異なる場合)
	 */
	function convertToSelectedString($src, $compareValue)
	{
		$selected = '';
		if ($src == $compareValue) $selected = 'selected';
		return $selected;
	}
	/**
	 * URLをエンティティ文字に変換
	 */
	function convertUrlToHtmlEntity($src)
	{
		// 変換文字「&<>"'」
		//return htmlspecialchars($src, ENT_QUOTES, M3_HTML_CHARSET);
		return convertUrlToHtmlEntity($src);
	}
	/**
	 * 入力フィールドの値を取得
	 *
	 * @param string $fieldValue		値が代入される変数
	 * @param string $name				POST,GET値の取得キー
	 * @param string $inputType			入力値のタイプ(text(trimValueOf)、int(trimIntOf)、html(valueOf)、url(trimValueOf)、checkbox(trimCheckedValueOf)、init(出力なし)、none(出力のみ)、空の場合はtext)。「:」でデフォルト値が付加可能。
	 * @param string $outputType		出力値のタイプ(text(HTMLエンティティのエスケープ)、html(エスケープなし)、url(URLのみのエスケープ、checkbox(checked文字列))。
	 * 									空の場合は、入力タイプ別の規定処理、textまたはint(HTMLエンティティのエスケープ)、html(エスケープなし)、url(URLのみのエスケープ、checkbox(checked文字列))を行う。
	 * @param string $validateRule		デフォルトの入力チェックルール
	 * @return							なし
	 */
	function getInputField(&$fieldValue, $name, $inputType = '', $outputType = '', $validateRule = '', $fieldNameHead = 'item_')
	{
		// パレメータエラーチェック
		// 名前が空の場合は終了
		if (empty($name)){
			echo 'Input field error: no name parameter.';
			return;
		}
		// 値が設定されている場合は終了
		if (isset($fieldValue)){
			echo 'Input field error: ' . $name . ' is already initialized.';
			return;
		}
		// 既存データの場合は終了
		if (in_array($name, $this->inputFieldInfo)){
			echo 'Input field error: ' . $name . ' already exists.';
			return;
		}
		// 変数を解析
		list($inputType, $inputParam) = explode(':', strtolower($inputType));
		$inputType = trim($inputType);
		$inputParam = trim($inputParam);
		list($outputType, $outputParam) = explode(':', strtolower($outputType));
		$outputType = trim($outputType);
		$outputParam = trim($outputParam);
		if (empty($inputType)) $inputType = 'text';
		if (empty($outputType)) $outputType = $inputType;
		
		// 入力値を取得
		$inputFieldName = $fieldNameHead . $name;
		switch ($inputType){
		case 'text':
		case 'url':
			$fieldValue = $this->gRequest->trimValueOf($inputFieldName);
			break;
		case 'int':
			if (is_int($inputParam)){			// デフォルト値がある場合
				$fieldValue = $this->gRequest->trimIntValueOf($inputFieldName, $inputParam);
			} else {
				echo 'Input field error: ' . $name . ' must have default value.';
				return;
			}
			$outputType = 'text';
			break;
		case 'html':
			$fieldValue = $this->gRequest->valueOf($inputFieldName);
			if ($inputParam == 'trim') $fieldValue = trim($fieldValue);		// 「trim」オプション付きの場合
			break;
		case 'checkbox':
			$fieldValue = $this->gRequest->trimCheckedValueOf($inputFieldName);
			break;
		case 'none':		// 出力のみ
			// 出力タイプが設定されていない場合はデフォルト変換を行う
			if ($outputType == 'none') $outputType = 'text';

			$fieldValue = '';		// 空文字列で初期化
			break;
		default:
			echo 'Input field error: ' . $name . ' must have valid input type.';
			break;
		}
		
		// フィールド情報を保存
		$fieldObj = new stdClass;
		$fieldObj->inputType	= $inputType;
		$fieldObj->inputParam	= $inputParam;
		$fieldObj->outputType	= $outputType;
		$fieldObj->outputParam	= $outputParam;
		$fieldObj->validateRule	= $validateRule;
		$fieldObj->fieldValue	= &$fieldValue;			// putInputFieldsで値を取得するために参照渡しにする
		$this->inputFieldInfo[$name] = $fieldObj;
	}
	/**
	 * すべての入力フィールドの値をテンプレート出力
	 *
	 * @return							なし
	 */
	function putInputField()
	{
		foreach ($this->inputFieldInfo as $name => $fieldObj){
			$inputType		= $fieldObj->inputType;
			$inputParam		= $fieldObj->inputParam;
			$outputType		= $fieldObj->outputType;
			$outputParam	= $fieldObj->outputParam;
			$validateRule	= $fieldObj->validateRule;
			$fieldValue		= $fieldObj->fieldValue;		// 現在の値を取得
			
			switch ($outputType){
			case 'text':
				$this->tmpl->addVar('_widget', $name, $this->convertToDispString($fieldValue));
				break;
			case 'html':
				$this->tmpl->addVar('_widget', $name, $fieldValue);
				break;
			case 'url':
				$this->tmpl->addVar('_widget', $name, $this->convertUrlToHtmlEntity($fieldValue));
				break;
			case 'checkbox':
				$this->tmpl->addVar('_widget', $name, $this->convertToCheckedString($fieldValue));
				break;
			case 'init':		// 出力なし
				break;
			default:
				echo 'Input field error: ' . $name . ' must have valid output type.';
				break;
			}
		}
	}
	/**
	 * 値をテンプレート出力
	 *
	 * @param string $name				テンプレートタグ名
	 * @param string $str				出力文字列
	 * @param string $outputType		出力値のタイプ(text(HTMLエンティティのエスケープ)、html(エスケープなし)、url(URLのみのエスケープ)、checkbox(checkedまたは空文字列)、disabled(disabledまたは空文字列))
	 * @return							なし
	 */
	function putTemplateVar($name, $str, $outputType = '')
	{
		$outputType = strtolower($outputType);
		switch ($outputType){
		case 'text':
		default:
			$this->tmpl->addVar('_widget', $name, $this->convertToDispString($str));
			break;
		case 'html':
			$this->tmpl->addVar('_widget', $name, $str);
			break;
		case 'url':
			$this->tmpl->addVar('_widget', $name, $this->convertUrlToHtmlEntity($str));
			break;
		case 'checkbox':
			$this->tmpl->addVar('_widget', $name, $this->convertToCheckedString($str));
			break;
		case 'disabled':
			$this->tmpl->addVar('_widget', $name, $this->convertToDisabledString($str));
			break;
		}
	}
	/**
	 * 未入力チェック
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param string $errorMsg		エラー時のメッセージ
	 * @return bool				false未入力、入力誤り
	 */
	function checkInput($str, $title, $errorMsg = '')
	{
		if (strlen($str) == 0){
			if (empty($errorMsg)){
				$msg = $this->_g('You must enter %s.');			// メッセージを取得「%sが入力されていません」
				array_push($this->warningMessage, sprintf($msg, $title));
			} else {		// エラーメッセージが設定されている場合
				array_push($this->warningMessage,  sprintf($errorMsg, $title));// 「%s」がある場合は項目名を設定
			}
			return false;
		}
		return true;
	}
	/**
	 * メールアドレスチェック
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが許可される文字でない場合、"$title."は半角英数字／ハイフン／アンダースコア／＠以外の入力はできません"を$WarningMessageに追加
	 * メールアドレスが正しい形式か否かもチェックする
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @param string $locale	ロケールを指定する場合は設定。指定しない場合はデフォルトのロケールを使用。
	 * @return bool				false入力誤り
	 */
	function checkMailAddress($str, $title, $passBlank = false, $locale = '')
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}
		if (strlen($str) && preg_match("/[^0-9a-zA-Z-.@_]/", $str)){
			//array_push($this->warningMessage, $title.'は半角英数字／ハイフン／アンダースコア／ピリオド／＠以外の入力はできません');
			$msg = $this->_g('%s can contain alphabets, numbers, hyphen, underscore, period, atmark.', $locale);			// メッセージを取得「%sは半角英数字／ハイフン／アンダースコア／ピリオド／＠以外の入力はできません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
			
		} elseif (strlen($str) && !preg_match("/[\w\d\-\.]+\@[\w\d\-\.]+/", $str)){
			//array_push($this->warningMessage, 'この'.$title.'は正しい形式ではありません');
			$msg = $this->_g('%s is not valid E-mail format.', $locale);			// メッセージを取得「この%sは正しい形式ではありません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * ログインアカウントチェック
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが許可される文字でない場合、"$title."は半角英数字／ハイフン／アンダースコア／＠以外の入力はできません"を$WarningMessageに追加
	 * メールアドレスが正しい形式か否かもチェックする
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false入力誤り
	 */
	function checkLoginAccount($str, $title, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}
		if (strlen($str) && preg_match("/[^0-9a-zA-Z-.@_]/", $str)){
			//array_push($this->warningMessage, $title.'は半角英数字／ハイフン／アンダースコア／ピリオド／＠以外の入力はできません');
			$msg = $this->_g('%s can contain alphabets, numbers, hyphen, underscore, period, atmark.');			// メッセージを取得「%sは半角英数字／ハイフン／アンダースコア／ピリオド／＠以外の入力はできません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * IPチェック
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが許可される文字でない場合、"$title."は数値／ピリオド以外の入力はできません"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkIp($str, $title, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}
		if (strlen($str) && preg_match("/[^0-9.]/", $str)){
			//array_push($this->warningMessage, $title.'は数値／ピリオド以外の入力はできません');
			$msg = $this->_g('%s can contain numbers and period.');			// メッセージを取得「%sは数値／ピリオド以外の入力はできません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * URLチェック
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが半角文字ではない場合、"$title."はURLの入力はできません"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkUrl($str, $title, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}
		if (!preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()\w;\/?:\@&=+\$,%#]+)$/', $str)) {
			//array_push($this->warningMessage, $title.'はURL以外の入力はできません');
			$msg = $this->_g('%s is not valid URL format.');			// メッセージを取得「%sはURL以外の入力はできません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * 半角文字チェック(整数)
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが半角数値文字ではない場合、$title.'は半角数字以外の入力はできません'を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkNumeric($str, $title, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}

		if (preg_match("/[^0-9]/", $str)){
			//array_push($this->warningMessage, $title.'は半角数字以外の入力はできません');
			$msg = $this->_g('%s can contain numbers.');			// メッセージを取得「%sは半角数字以外の入力はできません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * 半角文字チェック(小数)
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが半角文字ではない場合、"$title."は半角数字／ピリオド以外の入力はできません"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkNumericF($str, $title, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}

		if (preg_match("/[^0-9.]/", $str)){
			//array_push($this->warningMessage,$title.'は半角数字／ピリオド以外の入力はできません');
			$msg = $this->_g('%s can contain numbers and period.');			// メッセージを取得「%sは半角数字／ピリオド以外の入力はできません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * 半角数値文字チェック(汎用)
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが半角文字ではない場合、"$title."は半角数字／ピリオド以外の入力はできません"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @param string $format	入力制限用指定用
	 * @return bool				false未入力、入力誤り
	 */
	function checkNumber($str, $title, $passBlank = false, $format = '')
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}

		if (!is_numeric($str)){
			//array_push($this->warningMessage, $title.'は数値のみ入力可能です');
			$msg = $this->_g('%s is not valid number format.');			// メッセージを取得「%sは数値のみ入力可能です」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * パス文字列チェック(スペース不可英数)
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが半角文字ではない場合、"$title."は半角英数字／ハイフン／アンダースコア／スラッシュ以外の入力はできません"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkPath($str, $title, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}
		if (preg_match("/[^0-9a-zA-Z-_\/]/", $str)){
			//array_push($this->warningMessage, $title.'は半角英数字／ハイフン／アンダースコア／スラッシュ以外の入力はできません');
			$msg = $this->_g('%s can contain alphabets, numbers, hyphen, underscore, slash.');			// メッセージを取得「%sは半角英数字／ハイフン／アンダースコア／スラッシュ以外の入力はできません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * 半角文字チェック(スペース不可英数)
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが半角文字ではない場合、"$title."は半角英数字／ハイフン／アンダースコア以外の入力はできません"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @param int $alphaType	英字タイプ(0=大文字小文字許可、1=小文字のみ許可、2=大文字のみ許可)
	 * @return bool				false未入力、入力誤り
	 */
	function checkSingleByte($str, $title, $passBlank = false, $alphaType = 0)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}
		switch ($alphaType){
			case 0:			// 大文字小文字許可
			default:
				if (preg_match("/[^0-9a-zA-Z-_]/", $str)){
					//array_push($this->warningMessage, $title . 'は半角英数字／ハイフン／アンダースコア以外の入力はできません');
					$msg = $this->_g('%s can contain alphabets, numbers, hyphen, underscore.');			// メッセージを取得「%sは半角英数字／ハイフン／アンダースコア以外の入力はできません」
					array_push($this->warningMessage, sprintf($msg, $title));
					return false;
				}
				break;
			case 1:			// 小文字のみ許可
				if (preg_match("/[^0-9a-z-_]/", $str)){
					//array_push($this->warningMessage, $title . 'は半角英小文字数字／ハイフン／アンダースコア以外の入力はできません');
					$msg = $this->_g('%s can contain lower alphabets, numbers, hyphen, underscore.');			// メッセージを取得「%sは半角英小文字数字／ハイフン／アンダースコア以外の入力はできません」
					array_push($this->warningMessage, sprintf($msg, $title));
					return false;
				}
				break;
			case 2:			// 大文字のみ許可
				if (preg_match("/[^0-9A-Z-_]/", $str)){
					//array_push($this->warningMessage, $title . 'は半角英大文字数字／ハイフン／アンダースコア以外の入力はできません');
					$msg = $this->_g('%s can contain upper alphabets, numbers, hyphen, underscore.');			// メッセージを取得「%sは半角英大文字数字／ハイフン／アンダースコア以外の入力はできません」
					array_push($this->warningMessage, sprintf($msg, $title));
					return false;
				}
				break;
		}
		return true;
	}
	/**
	 * 半角文字チェック(スペース可英字)
	 *
	 * $strが未入力の場合、 "$title.が入力されていません"を$WarningMessageに追加
	 * $strが半角文字ではない場合、"$title."は半角英字／空白以外の入力はできません"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkAlphaSpace($str, $title, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}
		if (preg_match("/[^a-zA-Z_\ ]/", $str)){
			//array_push($this->warningMessage,$title.'は半角英字／空白以外の入力はできません');
			$msg = $this->_g('%s can contain alphabets and space.');			// メッセージを取得「%sは半角英字／空白以外の入力はできません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * 入力文字列サイズチェック
	 *
	 * $strが未入力の場合、 "$titleが入力されていません"を$WarningMessageに追加
	 * $byteSizeが最大バイト長よりも大の場合、"$titleは半角$maxSize文字までの入力が可能です"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param int $maxSize		最大文字数
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkByteSize($str, $title, $maxSize, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
			
		if (!self::checkInput($str, $title)){
			return false;
		}
		if (strlen($str) > $maxSize){
			//array_push($this->warningMessage, $title.'は半角'.$maxSize.'文字までの入力が可能です');
			$msg = $this->_g('%1$s can be max byte length %2$s.');			// メッセージを取得「%sは半角%s文字までの入力が可能です」
			array_push($this->warningMessage, sprintf($msg, $title, $maxSize));
			return false;
		}
		return true;
	}
	/**
	 * 入力文字数チェック
	 *
	 * $strが未入力の場合、 "$titleが入力されていません"を$WarningMessageに追加
	 * $byteSizeが最大バイト長よりも大の場合、"$titleは$maxSize文字までの入力が可能です"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param int $maxSize		最大文字数
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkLength($str, $title, $maxSize, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
			
		if (!self::checkInput($str, $title)){
			return false;
		}
		// 文字数を取得
		if (function_exists('mb_strlen')){
			$length = mb_strlen($str);
		} else {
			$length = strlen($str);
		}
		if ($length > $maxSize){
			$msg = $this->_g('%s can be max length %s.');			// メッセージを取得「%sは%s文字までの入力が可能です」
			array_push($this->warningMessage, sprintf($msg, $title, $maxSize));
			return false;
		}
		return true;
	}
	/**
	 * 日付チェック
	 *
	 * $strが未入力の場合、 "$title.の日付形式が正しくありません。"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkDate($str, $title, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}

		// セパレータ'/','.'をセパレータ'-'に変換する。
		$str1 = str_replace(array('/','.'), array('-','-'), $str);

		$sprcnt = 0;			// セパレータの数
		$pos = 0;
		while ($sprcnt < 10){
			if ($pos == 0)	$pos = strpos($str1, '-');
			else			$pos = strpos($str1, '-', $pos);
			// Note our use of ===.  Simply == would not work as expected
			// because the position of 'a' was the 0th (first) character.
			if ($pos === false)	break;

			$sprcnt++;			// セパレータの数
			$pos++;
		}

		if ($sprcnt == 0
		 || $sprcnt  > 2){			// セパレータの数
			//array_push($this->warningMessage,$title.'は正しい日付の形式ではありません。1');
			$msg = $this->_g('%s is not valid Date format.');			// メッセージを取得「%sは正しい日付の形式ではありません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		if ($sprcnt == 2){
			$pos  = strpos( $str1, '-' );
			$yy   = substr( $str1, 0, $pos );
			$str1 = substr( $str1, $pos+1 );
		}
		else{
			$yy = date('Y');
		}
		$pos  = strpos($str1, '-');
		$mm   = substr($str1, 0, $pos);
		$dd   = substr($str1, $pos+1);

		$ret = @checkdate($mm,$dd,$yy);	// 文字列から日付型を作成する。

		if ($ret === false){
			//array_push($this->warningMessage,$title.'は正しい日付の形式ではありません。2');
			$msg = $this->_g('%s is not valid Date format.');			// メッセージを取得「%sは正しい日付の形式ではありません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * 時刻チェック
	 *
	 * $strが未入力の場合、 "$title.の時刻形式が正しくありません。"を$WarningMessageに追加
	 *
	 * @param string $str		表示メッセージ
	 * @param string $title		項目名
	 * @param bool $passBlank	空を正常とするかどうか(true=空を正常とする、false=空の場合はエラーとする)
	 * @return bool				false未入力、入力誤り
	 */
	function checkTime($str, $title, $passBlank=false)
	{
		if ($passBlank && $str == '') return true;
		
		if (!self::checkInput($str, $title)){
			return false;
		}

		// セパレータ':'をセパレータ'-'に変換する。
		$str1 = str_replace(array(':'), array('-'), $str);

		$sprcnt = 0;			// セパレータの数
		$pos = 0;
		while ($sprcnt < 10){
			if ($pos == 0)	$pos = strpos($str1, '-');
			else			$pos = strpos($str1, '-', $pos);
			// Note our use of ===.  Simply == would not work as expected
			// because the position of 'a' was the 0th (first) character.
			if ($pos === false)	break;

			$sprcnt++;			// セパレータの数
			$pos++;
		}

		if ($sprcnt < 0 || 2 < $sprcnt){			// セパレータの数
			//array_push($this->warningMessage, $title.'は正しい時刻の形式ではありません。1');
			$msg = $this->_g('%s is not valid Time format.');			// メッセージを取得「%sは正しい時刻の形式ではありません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		$ret = true;
		list($hh, $mm, $ss) = preg_split('/[:\-]/', $str1);
		$hour = intval($hh);
		$minute = intval($mm);
		$second = intval($ss);
		
		if ($hour < 0 || 23 < $hour) $ret = false;
		if ($minute < 0 || 59 < $minute) $ret = false;
		if ($second < 0 || 59 < $second) $ret = false;
		if (!$ret){
			//array_push($this->warningMessage, $title.'は正しい時刻の形式ではありません。2');
			$msg = $this->_g('%s is not valid Time format.');			// メッセージを取得「%sは正しい時刻の形式ではありません」
			array_push($this->warningMessage, sprintf($msg, $title));
			return false;
		}
		return true;
	}
	/**
	 * Timestamp型の値を日付表記に変更
	 *
	 * @param timestamp $datestr	Timestamp型の値
	 * @param string $shortYear		省略形式かどうか
	 * @return string				日付文字列。日付が作成できないときは空文字列を返す。
	 */
	function timestampToDate($datestr, $shortYear = false)
	{
		$yyyy = '';
		$mm = '';
		$dd = '';
		$ret_stat = '';
		list($srcstr, $dummy) = explode(' ', $datestr);
		if (strlen($datestr) == 0) return "";
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', $srcstr);
		if ($shortYear){	// 短い表記
			$yy = floor($yyyy/100);
			$yy = $yyyy-($yy*100);
			$ret_stat = sprintf("%02s/%02s/%02s", $yy, $mm, $dd);
 		} else {
			$ret_stat = sprintf("%04s/%02s/%02s", $yyyy, $mm, $dd);
		}
  		return $ret_stat;
	}
	/**
	 * Timestamp型の値を時刻表記に変更
	 *
	 * @param timestamp $datestr	Timestamp型の値
	 * @return string				時刻文字列。時刻が作成できないときは空文字列を返す。
	 */
	function timestampToTime($datestr)
	{
		$hh = '';
		$mm = '';
		$ss = '';
		$ret_stat = '';
		list($dummy, $srcstr) = explode(' ', $datestr);// 後半を取得
		if (strlen($datestr) == 0) return false;
		list($hh, $mm, $ss) = preg_split('/[:\-]/', $srcstr);
		$ret_stat = sprintf("%02s:%02s:%02s", $hh, $mm, $ss);
  		return $ret_stat;
	}
	/**
	 * Timestamp型の値から年月日を取得
	 *
	 * @param timestamp $datestr	Timestamp型の値
	 * @param int $year			年
	 * @param int $month			月
	 * @param int $day			日
	 * @return bool					true=成功、false失敗
	 */
	function timestampToYearMonthDay($datestr, &$year, &$month, &$day)
	{
		$yyyy = '';
		$mm = '';
		$dd = '';
		$ret_stat = '';
		list($srcstr, $dummy) = explode(' ', $datestr);
		if (strlen($datestr) == 0) return false;
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', $srcstr);
		$year = intval($yyyy);
		$month = intval($mm);
		$day = intval($dd);
		return true;
	}
	/**
	 * Timestamp型の値から時分秒を取得
	 *
	 * @param timestamp $datestr	Timestamp型の値
	 * @param int $hour				時
	 * @param int $minute			分
	 * @param int $second			秒
	 * @return bool					true=成功、false失敗
	 */
	function timestampToHourMinuteSecond($datestr, &$hour, &$minute, &$second)
	{
		$hh = '';
		$mm = '';
		$ss = '';
		$ret_stat = '';
		list($dummy, $srcstr) = explode(' ', $datestr);// 後半を取得
		if (strlen($datestr) == 0) return false;
		list($hh, $mm, $ss) = preg_split('/[:\-]/', $srcstr);
		$hour = intval($hh);
		$minute = intval($mm);
		$second = intval($ss);
		return true;
	}
	/**
	 * 簡易日付(mm/dd, yy/m/d,etc)形式で入力された文字列を正式な日付表現(yyyy/mm/dd)に変換
	 *
	 * @param timestamp $datestr	Timestamp型の値
	 * @return string				日付文字列。日付が作成できないときは空文字列を返す。
	 */
	function convertToProperDate($datestr)
	{
		$yyyy = '';
		$mm = '';
		$dd = '';
		$ret_stat = '';
		list($srcstr, $dummy) = explode(' ', $datestr);
		if (strlen($datestr) == 0) return "";
		$srcarray = preg_split('/[\/\.\-]/', $srcstr);
		
		//月日しか入力されていないときは、年を追加
		if (count($srcarray) == 2){	// 月日のみ入力
			array_unshift($srcarray, date("Y"));
		} else if (count($srcarray) == 1){	// 日のみ入力
			array_unshift($srcarray, date("m"));
			array_unshift($srcarray, date("Y"));
		}

		// 年月日を取得
		list($yyyy, $mm, $dd) = $srcarray;

		// 年号が4桁ないときは、4桁にする
		if (strlen($yyyy) < 4) $yyyy += 2000;

		$ret_stat = sprintf("%04s/%02s/%02s", $yyyy, $mm, $dd);
  		return $ret_stat;
	}
	/**
	 * 簡易時刻(hh, hh:mm, hh:mm:ss, etc)形式で入力された文字列を正式な時刻表現(hh:mm:ss)に変換
	 *
	 * @param string $src			変換元時刻文字列
	 * @param int $format			出力フォーマット(0=時分秒(デフォルト)、1=時分、2=時のみ)
	 * @return string				時刻文字列。時刻が作成できないときは空文字列を返す。
	 */
	function convertToProperTime($src, $format = 0)
	{
		$hh = '';
		$mm = '';
		$ss = '';
		$ret_stat = '';
		$srcstr = trim($src);
		if (strlen($srcstr) == 0) return "";

		list($hh, $mm, $ss) = preg_split('/[:]/', $srcstr);
		$hour = intval($hh);
		$minute = intval($mm);
		$second = intval($ss);
		
		$ret = true;
		if ($hour < 0 || 23 < $hour) $ret = false;
		if ($minute < 0 || 59 < $minute) $ret = false;
		if ($second < 0 || 59 < $second) $ret = false;

		if ($ret){
			switch ($format){
				case 0:			// 時分秒
				default:
					$ret_stat = sprintf("%02s:%02s:%02s", $hour, $minute, $second);
					break;
				case 1:			// 時分
					$ret_stat = sprintf("%02s:%02s", $hour, $minute);
					break;
				case 2:			// 時のみ
					$ret_stat = sprintf("%02s", $hour);
					break;
			}
  			return $ret_stat;
		} else {
			return '';
		}
	}
	/**
	 * 年月(yyyy/mm)から末日を求める
	 *
	 * @param string $strYM			年月
	 * @return int					末日
	 */
	function getLastDayOfMonth($strYM)
	{
		// セパレータ'/','.'をセパレータ'-'に変換する。
		$strYM = str_replace(array('/','.'), array('-','-'), $strYM);
		$pos   = strpos( $strYM, '-' );
		$yy    = substr( $strYM, 0, $pos );
		$mm    = substr( $strYM, $pos+1 );

		switch( $mm ){
		// 検索
		case 1 :
		case 3 :
		case 5 :
		case 7 :
		case 8 :
		case 10 :
		case 12 :
			return 31;
		case 4 :
		case 6 :
		case 9 :
		case 11 :
			return 30;
		case 2 :
			$doy = date('z', strtotime("$yy-12-31"));	// 0-364(365=URUU)
			if( $doy == 364)return 28;
			else			return 29;
		}
	}
	/**
	 * 年月日(yyyy/mm/dd)から前日を求める
	 *
	 * @param string $strYMD		年月日
	 * @return string				前日
	 */
	function getForeDay($srcstr)
	{
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', $srcstr);
		return date("Y/m/d", mktime(0, 0, 0, $mm, $dd - 1, $yyyy));
	}
	/**
	 * 年月日(yyyy/mm/dd)から翌日を求める
	 *
	 * @param string $strYMD		年月日(時刻付きも可)
	 * @return string				翌日
	 */
	function getNextDay($srcstr)
	{
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\- ]/', $srcstr);
		return date("Y/m/d", mktime(0, 0, 0, $mm, $dd + 1, $yyyy));
	}
	/**
	 * 年月日(yyyy/mm/dd)から前月を求める
	 *
	 * @param string $strYMD		年月日
	 * @return string				前月
	 */
	function getForeMonth($srcstr)
	{
		list($yyyy, $mm) = preg_split('/[\/\.\-]/', $srcstr);
		return date("Y/m", mktime(0, 0, 0, $mm -1, 1, $yyyy));
	}
	/**
	 * 年月日(yyyy/mm/dd)から翌月を求める
	 *
	 * @param string $strYMD		年月日
	 * @return string				翌月
	 */
	function getNextMonth($srcstr)
	{
		list($yyyy, $mm) = preg_split('/[\/\.\-]/', $srcstr);
		return date("Y/m", mktime(0, 0, 0, $mm +1, 1, $yyyy));
	}
	/**
	 * タイムスタンプ型日時をHTML表示用のシステム標準のフォーマットに変換
	 *
	 * @param timestamp $src		変換するデータ
	 * @param int $type				日付タイプ(0=年ロングフォーマット、1,2,3=年ショートフォーマット、10=年なし)
	 * @param int $timeType			時間タイプ(0=時分秒、10=時分、20=時)
	 * @return string				変換後データ、初期化値のときは空文字列を返す
	 */
	function convertToDispDateTime($src, $type = 0, $timeType = 0)
	{
		// 入力チェック
		if (empty($src)) return '';
		
		// 接続するDBに応じて初期化値を判断
		$time = strtotime($src);
		if ($time === false) return '';		// エラーの場合は終了
		if ($time == strtotime($this->gEnv->getInitValueOfTimestamp())) return '';
		
		$dateFormat = '';
		$timeFormat = '';
		
		// 日付フォーマット
		switch ($type){
			case 0:		// デフォルトフォーマット(例 2000/01/01)
			default:
				$dateFormat = 'Y/m/d';
				break;
			case 1:		// 年ショートフォーマット(例 00/01/01)
				$dateFormat = 'y/m/d';
				break;
			case 2:		// 年ショートフォーマット(例 '00 01/01)
				$dateFormat = "\'y m/d";
				break;
			case 3:		// 年ショートフォーマット(例 '00 1/1)
				$dateFormat = "\'y n/j";
				break;
			case 10:		// 年なし(例 01/01)
				$dateFormat = 'm/d';
				break;
			case 11:		// 年なし(0なし年月)(例 1/1)
				$dateFormat = 'n/j';
				break;
		}
		
		// 時間フォーマット
		switch ($timeType){
			case 0:			// 時分秒(例 01:01:01)
			default:
				$timeFormat = 'H:i:s';
				break;
			case 1:			// 時分秒0なし(例 1:1:1)
				break;
			case 10:		// 時分(例 01:01)
				$timeFormat = 'H:i';
				break;
			case 20:		// 時のみ(例 01)
				$timeFormat = 'H';
				break;
		}
		/*if ($type == 0){	// ロングフォーマット(yyyy/mm/dd hh:mm:ss)
			return date("Y/m/d H:i:s", $time);
		} else if ($type == 1){	// ロングフォーマット(yyyy/mm/dd hh:mm)
			return date("Y/m/d H:i", $time);
		} else if ($type == 10){			// ショートフォーマット(yy/mm/dd hh:mm:ss)
			return date("y/m/d H:i:s", $time);
		}*/
		
		return date($dateFormat . ' ' . $timeFormat, $time);
	}
	/**
	 * タイムスタンプ型日時をHTML表示用のシステム標準のフォーマットに変換
	 *
	 * @param timestamp $src		変換するデータ
	 * @param int $type				convertToDispDateTime()の日付タイプと同じ
	 * @return string				変換後データ、初期化値のときは空文字列を返す
	 */
	function convertToDispDate($src, $type = 0)
	{
		// 入力チェック
		if (empty($src)) return '';
		
		// 接続するDBに応じて初期化値を判断
		$time = strtotime($src);
		if ($time === false) return '';		// エラーの場合は終了
		if ($time == strtotime($this->gEnv->getInitValueOfTimestamp())) return '';

/*
		if ($type == 0){	// ロングフォーマット(yyyy/mm/dd)
			return date("Y/m/d", $time);
		} else {			// ショートフォーマット(yy/mm/dd)
			return date("y/m/d", $time);
		}*/
		// 日付フォーマット
		switch ($type){
			case 0:		// デフォルトフォーマット(例 2000/01/01)
			default:
				$dateFormat = 'Y/m/d';
				break;
			case 1:		// 年ショートフォーマット(例 00/01/01)
				$dateFormat = 'y/m/d';
				break;
			case 2:		// 年ショートフォーマット(例 '00 01/01)
				$dateFormat = "\'y m/d";
				break;
			case 3:		// 年ショートフォーマット(例 '00 1/1)
				$dateFormat = "\'y n/j";
				break;
			case 10:		// 年なし(例 01/01)
				$dateFormat = 'm/d';
				break;
			case 11:		// 年なし(0なし年月)(例 1/1)
				$dateFormat = 'n/j';
				break;
		}
		return date($dateFormat, $time);
	}
	/**
	 * タイムスタンプ型日時をHTML表示用のシステム標準のフォーマットに変換
	 *
	 * @param timestamp $src		変換するデータ
	 * @param int $type				0=ロングフォーマット、1=ショートフォーマット
	 * @return string				変換後データ、初期化値のときは空文字列を返す
	 */
	function convertToDispTime($src, $type = 0)
	{
		// 入力チェック
		if (empty($src)) return '';
		
		// 接続するDBに応じて初期化値を判断
		$time = strtotime($src);
		if ($time === false) return '';		// エラーの場合は終了
		if ($time == strtotime($this->gEnv->getInitValueOfTimestamp())) return '';
		
		// 時刻部分を取得
		$time = strtotime($this->timestampToTime($src));
		
		if ($type == 0){	// ロングフォーマット(hh:mm:ss)
			return date("H:i:s", $time);
		} else {			// ショートフォーマット(hh:mm)
			return date("H:i", $time);
		}
	}
	/**
	 * タイムスタンプ型日時をテキスト表示用のシステム標準のフォーマットに変換
	 *
	 * @param timestamp $src		変換するデータ
	 * @param int $type				0=ロングフォーマット、1=ショートフォーマット
	 * @return string				変換後データ、初期化値のときは空文字列を返す
	 */
	function convertToDateTimeString($src, $type = 0)
	{
		// 入力チェック
		if (empty($src)) return '';
		
		// 接続するDBに応じて初期化値を判断
		$time = strtotime($src);
		if ($time === false) return '';		// エラーの場合は終了
		if ($time == strtotime($this->gEnv->getInitValueOfTimestamp())) return '';
		
		if ($type == 0){	// ロングフォーマット(yyyy/mm/dd hh:mm:ss)
			return date("Y/m/d H:i:s", $time);
		} else {			// ショートフォーマット(yy/mm/dd hh:mm:ss)
			return date("y/m/d H:i:s", $time);
		}
	}
	/**
	 * タイムスタンプ型日時をテキスト表示用のシステム標準のフォーマットに変換
	 *
	 * @param timestamp $src		変換するデータ
	 * @param int $type				0=ロングフォーマット、1=ショートフォーマット
	 * @return string				変換後データ、初期化値のときは空文字列を返す
	 */
	function convertToDateString($src, $type = 0)
	{
		// 入力チェック
		if (empty($src)) return '';
		
		// 接続するDBに応じて初期化値を判断
		$time = strtotime($src);
		if ($time === false) return '';		// エラーの場合は終了
		if ($time == strtotime($this->gEnv->getInitValueOfTimestamp())) return '';

		if ($type == 0){	// ロングフォーマット(yyyy/mm/dd)
			return date("Y/m/d", $time);
		} else {			// ショートフォーマット(yy/mm/dd)
			return date("y/m/d", $time);
		}
	}
	/**
	 * ロケールに対応した経過時間表記の文字列を作成
	 *
	 * @param int $time		経過時間(秒)
	 * @param int $type		0=ロングフォーマット、1=ショートフォーマット(m,h,dのアルファベット表記)
	 * @return string		変換後データ、初期化値のときは空文字列を返す
	 */
	function convertToDispPassageTime($time, $type = 0)
	{
		static $localeUnits;
	
		$values = array(60, 24, 1);
		
		if ($type == 0){		// ロングフォーマットのとき
			if (!isset($localeUnits)) $localeUnits = explode(';', $this->_g('minute;hour;day'));
			$unitArray = $localeUnits;
		} else {
			$unitArray = array('m', 'h', 'd');	// 分,時間,日
		}
		$time = $time / 60; // minutes

		for ($i = 0; $i < count($values); $i++){
			$value = $values[$i];
			$unit = $unitArray[$i];
			if ($time < $value) break;
			$time /= $value;
		}
		$time = floor($time) . $unit;		// 単位を付加
		return $time;
	}
	/**
	 * テキストデータを表示用のテキストに変換
	 *
	 * 変換内容　・改行コードを<br />に変換
	 *
	 * @param string $src			変換するデータ
	 * @return string				変換後データ
	 */
	function convertToPreviewText($src)
	{
		// 改行コードをbrタグに変換
		return $this->gInstance->getTextConvManager()->convLineBreakToBr($src);
	}
	/**
	 * Magic3マクロを変換してHTMLを作成
	 *
	 * ・デフォルトでマクロ変換した文字列に改行が含まれるときは改行をbrに変換する
	 *
	 * @param string $src			変換するデータ
	 * @param bool $convBr			キーワード変換部分の改行コードをBRタグに変換するかどうか
	 * @param array $contentInfo	コンテンツ情報
	 * @return string					変換後データ
	 */
	function convertM3ToHtml($src, $convBr = true, $contentInfo = array())
	{
		// ### コンテンツ内容のチェック ###
		// Googleマップが含まれている場合はコンテンツ情報として登録->Googleマップライブラリの読み込み指示
		$pos = strpos($src, self::DETECT_GOOGLEMAPS);
		if ($pos !== false) $this->gPage->setIsContentGooglemaps(true);
		
		// URLを求める
		$rootUrl = $this->gEnv->getRootUrlByCurrentPage();
		$widgetUrl = str_replace($this->gEnv->getRootUrl(), $rootUrl, $this->gEnv->getCurrentWidgetRootUrl());
		
		// パスを変換
		$dest = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $rootUrl, $src);// アプリケーションルートを変換
		$dest = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_URL . M3_TAG_END, $widgetUrl, $dest);// ウィジェットルートを変換
		
		// コンテンツマクロ変換
		$dest = $this->gInstance->getTextConvManager()->convContentMacro($dest, $convBr/*改行コードをbrタグに変換*/, $contentInfo, true/*変換後の値をHTMLエスケープ処理*/);
		
		// ウィジェット埋め込みタグを変換
		$this->gInstance->getTextConvManager()->convWidgetTag($dest, $dest);
		
		// 残っているMagic3タグ削除
		$dest = $this->gInstance->getTextConvManager()->deleteM3Tag($dest);
		return $dest;
	}
	/**
	 * Magic3マクロを変換してテキストを作成
	 *
	 * @param string $src			変換するデータ
	 * @param array $contentInfo	変換テキスト情報
	 * @param bool $removeLineBreak	改行コードを削除するかどうか
	 * @return string				変換後データ
	 */
	function convertM3ToText($src, $contentInfo = array(), $removeLineBreak = false)
	{
		// URLを求める
		$rootUrl = $this->gEnv->getRootUrlByCurrentPage();
		$widgetUrl = str_replace($this->gEnv->getRootUrl(), $rootUrl, $this->gEnv->getCurrentWidgetRootUrl());
		
		// パスを変換
		$dest = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $rootUrl, $src);// アプリケーションルートを変換
		$dest = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_URL . M3_TAG_END, $widgetUrl, $dest);// ウィジェットルートを変換
		
		// コンテンツマクロ変換
		$dest = $this->gInstance->getTextConvManager()->convContentMacro($dest, false/*改行コードをbrタグに変換しない*/, $contentInfo, false/*変換後の値をHTMLエスケープ処理はしない*/);
		
		// 残っているMagic3タグ削除
		$dest = $this->gInstance->getTextConvManager()->deleteM3Tag($dest);
		
		// 改行コードを削除
		if ($removeLineBreak) $dest = $this->gInstance->getTextConvManager()->deleteLineBreak($dest);
		return $dest;
	}
	/**
	 * Magic3マクロを変換してヘッダ用タグを作成
	 *
	 * @param string $src			変換するデータ
	 * @param array $contentInfo	コンテンツ情報
	 * @return string				変換後データ
	 */
	function convertM3ToHead($src, $contentInfo = array())
	{
		// コンテンツマクロ変換
		$dest = $this->gInstance->getTextConvManager()->convContentMacro($src, false/*改行コードをbrタグに変換しない*/, $contentInfo, true/*変換後の値はHTMLエスケープ処理する*/);
		return $dest;		
	}
	/**
	 * 値のデータをCSV用のエスケープデータに変換
	 *
	 * @param string $src			変換するデータ
	 * @return string				変換後データ
	 */
	function convertToEscapedCsv($src)
	{
		if (is_numeric($src)){
			return $src;
		} else {
			return '"' . $src . '"';
		}
	}
	/**
	 * メッセージ表示
	 */
	function displayMsg()
	{
		$messageClassStr = '';
		$useBootstrap = $this->gPage->getUseBootstrap();
		$closeTag = '';		// メッセージ消去用クローズボックス
		
		// 前後タグ
		$preTag = '';
		$postTag = '';
				
		// メッセージがある場合はメッセージタグを表示
		if ($this->getMsgCount() > 0){
			$this->tmpl->setAttribute('_messages', 'visibility', 'visible');
		} else {
			return;
		}
		
		// Bootstrap用のタグ
		if ($useBootstrap){
			$closeTag = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
		}
		
		// 危険メッセージ
		if (count($this->dangerMessage) > 0){
			$this->tmpl->setAttribute('_danger_message', 'visibility', 'visible');	// メッセージ表示タグを表示
			
			// メッセージ追加クラス
			if ($useBootstrap){
				$messageClassArray = $this->gDesign->getBootstrapMessageClass('danger', $preTag, $postTag);
				if (!empty($messageClassArray)) $messageClassStr = ' ' . implode(' ', $messageClassArray);
			}
			
			foreach ($this->dangerMessage as $value) {
				$row = array(
					'message' 	=> $closeTag . $value,
					'class' 	=> $messageClassStr,			// メッセージ追加クラス
					'pre_tag'	=> $preTag,				// 前タグ
					'post_tag'	=> $postTag				// 後タグ
				);
				$this->tmpl->addVars('_danger_message', $row);
				$this->tmpl->parseTemplate('_danger_message', 'a');
			}
		}
		// アプリケーションエラー
		if (count($this->errorMessage) > 0){
			$this->tmpl->setAttribute('_error_message', 'visibility', 'visible');	// メッセージ表示タグを表示
			
			// メッセージ追加クラス
			if ($useBootstrap){
				$messageClassArray = $this->gDesign->getBootstrapMessageClass('danger', $preTag, $postTag);
				if (!empty($messageClassArray)) $messageClassStr = ' ' . implode(' ', $messageClassArray);
			}

			foreach ($this->errorMessage as $value) {
				$row = array(
					'message'	=> $closeTag . $value,
					'class' 	=> $messageClassStr,			// メッセージ追加クラス
					'pre_tag'	=> $preTag,				// 前タグ
					'post_tag'	=> $postTag				// 後タグ
				);
				$this->tmpl->addVars('_error_message', $row);
				$this->tmpl->parseTemplate('_error_message', 'a');
			}
		}
		// 警告(ユーザエラー)
		if (count($this->warningMessage) > 0){
			$this->tmpl->setAttribute('_warning_message', 'visibility', 'visible');		// メッセージ表示タグを表示
			
			// メッセージ追加クラス
			if ($useBootstrap){
				$messageClassArray = $this->gDesign->getBootstrapMessageClass('warning', $preTag, $postTag);
				if (!empty($messageClassArray)) $messageClassStr = ' ' . implode(' ', $messageClassArray);
			}

			foreach ($this->warningMessage as $value) {
				$row = array(
					'message'	=> $closeTag . $value,
					'class' 	=> $messageClassStr,			// メッセージ追加クラス
					'pre_tag'	=> $preTag,				// 前タグ
					'post_tag'	=> $postTag				// 後タグ
				);
				$this->tmpl->addVars('_warning_message', $row);
				$this->tmpl->parseTemplate('_warning_message', 'a');
			}
		}
		// 情報メッセージ
		if (count($this->infoMessage) > 0){
			$this->tmpl->setAttribute('_info_message', 'visibility', 'visible');			// メッセージ表示タグを表示
			
			// メッセージ追加クラス
			if ($useBootstrap){
				$messageClassArray = $this->gDesign->getBootstrapMessageClass('info', $preTag, $postTag);
				if (!empty($messageClassArray)) $messageClassStr = ' ' . implode(' ', $messageClassArray);
			}
			
			foreach ($this->infoMessage as $value) {
				$row = array(
					'message'	=> $closeTag . $value,
					'class' 	=> $messageClassStr,			// メッセージ追加クラス
					'pre_tag'	=> $preTag,				// 前タグ
					'post_tag'	=> $postTag				// 後タグ
				);
				$this->tmpl->addVars('_info_message', $row);
				$this->tmpl->parseTemplate('_info_message', 'a');				
			}
		}
		// ガイダンス
		if (count($this->guideMessage) > 0){
			$this->tmpl->setAttribute('_guide_message', 'visibility', 'visible');			// メッセージ表示タグを表示
			
			// メッセージ追加クラス
			if ($useBootstrap){
				$messageClassArray = $this->gDesign->getBootstrapMessageClass('info', $preTag, $postTag);
				//$messageClassArray = $this->gDesign->getBootstrapMessageClass('success', $preTag, $postTag);
				if (!empty($messageClassArray)) $messageClassStr = ' ' . implode(' ', $messageClassArray);
			}
			
			foreach ($this->guideMessage as $value) {
				$row = array(
					'message' 	=> $closeTag . $value,
					'class' 	=> $messageClassStr,			// メッセージ追加クラス
					'pre_tag'	=> $preTag,				// 前タグ
					'post_tag'	=> $postTag				// 後タグ
				);
				$this->tmpl->addVars('_guide_message', $row);
				$this->tmpl->parseTemplate('_guide_message', 'a');				
			}
		}
		// 成功メッセージ
		if (count($this->successMessage) > 0){
			$this->tmpl->setAttribute('_success_message', 'visibility', 'visible');			// メッセージ表示タグを表示
			
			// メッセージ追加クラス
			if ($useBootstrap){
				$messageClassArray = $this->gDesign->getBootstrapMessageClass('success', $preTag, $postTag);
				if (!empty($messageClassArray)) $messageClassStr = ' ' . implode(' ', $messageClassArray);
			}
			
			foreach ($this->successMessage as $value) {
				$row = array(
					'message' 	=> $closeTag . $value,
					'class' 	=> $messageClassStr,			// メッセージ追加クラス
					'pre_tag'	=> $preTag,				// 前タグ
					'post_tag'	=> $postTag				// 後タグ
				);
				$this->tmpl->addVars('_success_message', $row);
				$this->tmpl->parseTemplate('_success_message', 'a');				
			}
		}
		// 追加属性を設定
		if (!empty($this->messageAttr)) $this->tmpl->addVar('_messages', 'attr', $this->messageAttr);
	}
	/**
	 * メッセージをクリアする
	 *
	 * @return			なし
	 */
	function clearMsg()
	{
		// アプリケーションエラー
		while (count($this->errorMessage)){
			array_shift($this->errorMessage);
		}
		// ユーザ操作のエラー
		while (count($this->warningMessage)){
			array_shift($this->warningMessage);
		}
		// ガイダンス
		while (count($this->guideMessage)){
			array_shift($this->guideMessage);
		}
	}
	/**
	 * メッセージテーブルにメッセージを設定する
	 *
	 * @param int $type		エラーメッセージのタイプ。
	 * @param string $msg	メッセージ
	 * @return 				なし
	 */
	function setMsg($type, $msg)
	{
		if($type == self::MSG_APP_ERR){	// アプリケーションエラー
			array_push($this->errorMessage, $msg);
		} else if($type == self::MSG_USER_ERR){	// ユーザ操作のエラー
			array_push($this->warningMessage, $msg);
		} else if($type == self::MSG_GUIDANCE){	// ガイダンス
			array_push($this->guideMessage, $msg);
		}
	}
	/**
	 * メッセージを追加する
	 *
	 * @param array $errorMessage		エラーメッセージ
	 * @param array $warningMessage		ユーザ操作のエラー
	 * @param array $guideMessage	ガイダンス
	 * @return 				なし
	 */
	function addMsg($errorMessage, $warningMessage, $guideMessage)
	{
		$this->errorMessage		= array_merge($this->errorMessage, $errorMessage);		// アプリケーションエラー
		$this->warningMessage	= array_merge($this->warningMessage, $warningMessage);	// ユーザ操作のエラー
		$this->guideMessage	= array_merge($this->guideMessage, $guideMessage);	// ガイダンス
	}
	/**
	 * グローバルメッセージを追加取得する
	 *
	 * @return 				なし
	 */
	function getGlobalMsg()
	{
		$this->addMsg($this->gInstance->getMessageManager()->getErrorMessage(), $this->gInstance->getMessageManager()->getWarningMessage(), $this->gInstance->getMessageManager()->getGuideMessage());
	}
	/**
	 * メッセージテーブルにアプリケーションエラーメッセージを設定する
	 *
	 * @param string $msg	メッセージ
	 * @return 				なし
	 */
	function setAppErrorMsg($msg)
	{
		array_push($this->errorMessage, $msg);
	}
	/**
	 * メッセージテーブルにユーザ操作エラーメッセージを設定する
	 *
	 * @param string $msg	メッセージ
	 * @return 				なし
	 */
	function setUserErrorMsg($msg)
	{
		array_push($this->warningMessage, $msg);
	}
	/**
	 * メッセージテーブルにガイダンスメッセージを設定する
	 *
	 * 廃止予定→setGuideMsg()
	 *
	 * @param string $msg	メッセージ
	 * @return 				なし
	 */
	function setGuidanceMsg($msg)
	{
		array_push($this->guideMessage, $msg);
	}
	/**
	 * メッセージテーブルに情報メッセージを設定する
	 *
	 * @param string $msg	メッセージ
	 * @return 				なし
	 */
	function setInfoMsg($msg)
	{
		array_push($this->infoMessage, $msg);
	}
	/**
	 * メッセージテーブルに成功メッセージを設定する
	 *
	 * @param string $msg	メッセージ
	 * @return 				なし
	 */
	function setSuccessMsg($msg)
	{
		array_push($this->successMessage, $msg);
	}
	/**
	 * アプリケーションエラーメッセージを取得
	 *
	 * @return array	アプリケーションエラーメッセージ
	 */
	function getAppErrorMsg()
	{	
		return $this->errorMessage;
	}
	/**
	 * ユーザ操作のエラーメッセージを取得
	 *
	 * @return array	ユーザ操作のエラーメッセージ
	 */
	function getUserErrorMsg()
	{	
		return $this->warningMessage;
	}
	/**
	 * ガイダンスメッセージを取得
	 *
	 * @return array	ガイダンスメッセージ
	 */
/*	function getGuidanceMsg()
	{	
		return $this->guideMessage;
	}*/
	/**
	 * メッセージ数を返す
	 *
	 * @param int $type		メッセージのタイプ。1=ErrorMessage,2=WarningMessage,3=GuideMessage,省略=すべてのメッセージ数
	 * @return 				なし
	 */
	function getMsgCount($type = 0)
	{
		if ($type == 0){		// すべてのメッセージ数
			//return count($this->errorMessage) + count($this->warningMessage) + count($this->guideMessage);
			return count($this->dangerMessage) + count($this->errorMessage) + count($this->warningMessage) + count($this->infoMessage) + count($this->guideMessage) + count($this->successMessage);
		} else if ($type == self::MSG_APP_ERR){	// アプリケーションエラー
			return count($this->errorMessage);
		} else if($type == self::MSG_USER_ERR){	// ユーザ操作のエラー
			return count($this->warningMessage);
		} else if($type == self::MSG_GUIDANCE){// ガイダンス
			return count($this->guideMessage);
		}
	}
	/**
	 * メッセージが存在するかを返す
	 *
	 * @return 				true=メッセージが存在、false=メッセージなし
	 */
	function isExistsMsg()
	{
		if (count($this->errorMessage) + count($this->warningMessage) + count($this->guideMessage) > 0){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * メッセージ表示タグに追加属性を設定
	 *
	 * @param string $attr	属性
	 * @return 				なし
	 */
	function setMessageAttr($attr)
	{
		$this->messageAttr = $attr;
	}
	/**
	 * システム共通の各言語対応のテキストを取得
	 *
	 * @param string $id		メッセージID
	 * @param string $locale	ロケール
	 * @return string			取得テキスト
	 */
	function _g($id, $locale = '')
	{
		static $init = false;
		
		// 初期化されていない場合はロケールデータをロード
		if (!$init){
			if (empty($locale)){			// ロケールが設定されていない場合はデフォルトのロケール
				$ret = $this->gInstance->getMessageManager()->loadGlobalLocaleText($this->gEnv->getCurrentLocale());
			} else {
				$ret = $this->gInstance->getMessageManager()->loadGlobalLocaleText($locale);
			}
			
			$init = true;		// ロケールデータロード完了
		}
		// テキストを取得
		$destStr = $this->gInstance->getMessageManager()->getGlobalLocaleText($id);
		return $destStr;
	}
	/**
	 * ウィジェット単位の各言語対応のテキストを取得
	 *
	 * @param string $id		メッセージID
	 * @return string			取得テキスト
	 */
	function _($id)
	{
		static $init = false;
		
		$widgetId = $this->gEnv->getCurrentWidgetId();

		// 初期化されていない場合はロケールデータをロード
		if (!$init){
			$ret = $this->gInstance->getMessageManager()->loadLocaleText($widgetId, $this->gEnv->getCurrentLocale(), ''/*ファイル名オプション*/);
			
			$init = true;		// ロケールデータロード完了
		}
		// テキストを取得
		$destStr = $this->gInstance->getMessageManager()->getLocaleText($widgetId, $id);
		return $destStr;
	}
	/**
	 * ウィジェット単位の各言語対応のテキストをシステム用言語で取得
	 *
	 * @param string $id		メッセージID
	 * @return string			取得テキスト
	 */
	function _s($id)
	{
		static $init = false;
		
		$widgetId = $this->gEnv->getCurrentWidgetId();
		$defaultLocale = $this->gEnv->getDefaultLocale();
		$currentLocale = $this->gEnv->getCurrentLocale();
			
		// 初期化されていない場合はロケールデータをロード
		if (!$init){
			if ($defaultLocale == $currentLocale){		// カレントの言語がデフォルト言語と同じとき
				$ret = $this->gInstance->getMessageManager()->loadLocaleText($widgetId, $this->gEnv->getCurrentLocale(), ''/*ファイル名オプション*/);
			} else {
				$ret = $this->gInstance->getMessageManager()->loadLocaleText($widgetId, $this->gEnv->getCurrentLocale(), ''/*ファイル名オプション*/, self::LOCAL_TYPE_SYSTEM);
			}
			
			$init = true;		// ロケールデータロード完了
		}
		// テキストを取得
		if ($defaultLocale == $currentLocale){		// カレントの言語がデフォルト言語と同じとき
			$destStr = $this->gInstance->getMessageManager()->getLocaleText($widgetId, $id);
		} else {
			$destStr = $this->gInstance->getMessageManager()->getLocaleText($widgetId, $id, self::LOCAL_TYPE_SYSTEM);
		}
		return $destStr;
	}
	/**
	 * ローカライズ用の定義を設定
	 *
	 * @param array $localeText	ローカライズ用テキストの定義(キー=「_LC_」で始まるテンプレートに埋め込むテキストのID,値=ロケールファイル内のメッセージID)
	 * @return					なし
	 */
	function setLocaleText($localeText)
	{
		$this->localeText = array_merge($this->localeText, $localeText);
	}
	/**
	 * 通常エラー出力
	 *
	 * 以下の状況で運用ログにメッセージ出力するためのインターフェイス
	 * 割合起こりやすいエラーで、アプリケーションの続行は可能なもの
	 * 例) ファイル読み込みエラー、接続タイムアウト等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @return なし
	 */
	public function writeError($method, $msg, $code = 0, $msgExt = '')
	{
		// ウィジェットIDを付加
		if (!empty($msgExt)) $msgExt .= ', ';
		$msgExt .= 'widgetid=' . $this->gEnv->getCurrentWidgetId();
		
		$this->gOpeLog->writeError($method, $msg, $code, $msgExt);
	}
	/**
	 * 致命的エラー出力
	 *
	 * 以下の状況で運用ログにメッセージ出力するためのインターフェイス
	 * アプリケーションの処理が続行不可能なエラーやシステム的エラー
	 * 例) DB例外発生等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @return なし
	 */
	public function writeFatal($method, $msg, $code = 0, $msgExt = '')
	{
		// ウィジェットIDを付加
		if (!empty($msgExt)) $msgExt .= ', ';
		$msgExt .= 'widgetid=' . $this->gEnv->getCurrentWidgetId();
		
		$this->gOpeLog->writeFatal($method, $msg, $code, $msgExt);
	}
	/**
	 * ユーザ操作通常エラー出力
	 *
	 * 以下の状況で運用ログにメッセージ出力するためのインターフェイス
	 * 割合起こりやすいエラーで、アプリケーションの続行は可能なもの
	 * 例) ファイル読み込みエラー、接続タイムアウト等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @return なし
	 */
	public function writeUserError($method, $msg, $code = 0, $msgExt = '')
	{
		// ウィジェットIDを付加
		if (!empty($msgExt)) $msgExt .= ', ';
		$msgExt .= 'widgetid=' . $this->gEnv->getCurrentWidgetId();
		
		$this->gOpeLog->writeUserError($method, $msg, $code, $msgExt);
	}
	/**
	 * ユーザ操作運用ログ出力とイベント処理
	 *
	 * 以下の状況で運用ログメッセージを出力するためのインターフェイス
	 * ユーザの通常の操作で記録すべきもの
	 * 例) コンテンツの更新等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param array  $eventParam	イベント処理用パラメータ(ログに格納しない)
	 * @return なし
	 */
	public function writeUserInfoEvent($method, $msg, $code = 0, $msgExt = '', $eventParam = array())
	{
		$this->gOpeLog->writeUserInfo($method, $msg, $code, $msgExt, '', '', false, $eventParam);
	}
	/**
	 * パスワード生成
	 *
	 * システム共通でパスワード生成
	 *
	 * @return string				生成したパスワード
	 */
	function makePassword()
	{
		if (M3_SYSTEM_DEMO){		// デモモードのときは簡易パスワードを使用
			$password = 'demo';
		} else {
			$password = makePassword(self::PASSWORD_LENGTH);
		}
		return $password;
	}
	/**
	 * ページ定義のHタグレベルを取得
	 *
	 * @return int				Hタグレベル
	 */
	function getHTagLevel()
	{
		$tagLevel = $this->gSystem->getSystemConfig(self::CF_DEFAULT_H_TAG_LEVEL);
		$pageDefRec = $this->gEnv->getCurrentPageDefRec();
		if (!empty($pageDefRec)){
			$level = $pageDefRec['pd_h_tag_level'];		// Hタグレベル
			if (!empty($level)) $tagLevel = $level;
		}
		return $tagLevel;
	}
	/**
	 * ウィジェットパラメータオブジェクトを取得
	 *
	 * @return object			ウィジェットオブジェクト。取得できないときはnull。
	 */
	function getWidgetParamObj()
	{
		$serializedParam = $this->gInstance->getSytemDbObject()->getWidgetParam($this->gEnv->getCurrentWidgetId());
		if (empty($serializedParam)){
			return null;
		} else {
			return unserialize($serializedParam);
		}
	}
	/**
	 * ウィジェットパラメータオブジェクトを定義IDで取得
	 *
	 * @param int $id		定義ID
	 * @return object		ウィジェットオブジェクト。取得できないときはnull。
	 */
	function getWidgetParamObjByConfigId($id)
	{
		$serializedParam = $this->_db->getWidgetParam($this->gEnv->getCurrentWidgetId(), $id);
		if (empty($serializedParam)){
			return null;
		} else {
			return unserialize($serializedParam);
		}
	}
	/**
	 * ウィジェットパラメータオブジェクトを更新
	 *
	 * @param object $obj		格納するウィジェットパラメータオブジェクト
	 * @return bool				true=更新成功、false=更新失敗
	 */
	function updateWidgetParamObj($obj)
	{
		if (empty($obj)){
			$updateObj = null;
		} else {
			$updateObj = serialize($obj);
		}
		$ret = $this->gInstance->getSytemDbObject()->updateWidgetParam($this->gEnv->getCurrentWidgetId(), $updateObj, $this->gEnv->getCurrentUserId());
		return $ret;
	}
	/**
	 * ウィジェットパラメータオブジェクトを定義IDで更新
	 *
	 * @param int $id			定義ID
	 * @param object $obj		格納するウィジェットパラメータオブジェクト
	 * @return bool				true=更新成功、false=更新失敗
	 */
	function updateWidgetParamObjByConfigId($id, $obj)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		if (empty($obj)){
			$updateObj = null;
		} else {
			$updateObj = serialize($obj);
		}
		$ret = $this->gInstance->getSytemDbObject()->updateWidgetParam($this->gEnv->getCurrentWidgetId(), $updateObj, $this->gEnv->getCurrentUserId(), $now, $id);
		return $ret;
	}
	/**
	 * ウィジェットパラメータオブジェクトを取得(定義ID付き)
	 *
	 * @param bool $withZeroId	定義ID=0のオブジェクトも取得
	 * @return array			ウィジェットオブジェクトの配列。
	 */
	function getWidgetParamObjectWithId($withZeroId = false)
	{
		// 該当するウィジェットのパラメータオブジェクトをすべて取得
		$ret = $this->gInstance->getSytemDbObject()->getAllWidgetParam($this->gEnv->getCurrentWidgetId(), $rows);
		if ($ret){
			$params = array();
			for ($i = 0; $i < count($rows); $i++){
				$newObj = new stdClass;
				$newObj->id = $rows[$i]['wp_config_id'];
				if (!$withZeroId && empty($newObj->id)) continue;			// 定義ID=0のデータは取得しない
				$newObj->object = unserialize($rows[$i]['wp_param']);		// オブジェクト化
				$params[] = $newObj;
			}
			return $params;
		} else {
			return null;
		}
	}
	/**
	 * ウィジェットパラメータオブジェクトを更新(定義ID付き)
	 *
	 * @param object $param		定義IDとオブジェクトをメンバーに持つオブジェクト。定義ID=-1のとき新規追加で、実行後に新規の定義IDが返る。オブジェクトが空のときは削除。
	 * @return bool				true=更新成功、false=更新失敗
	 */
	function updateWidgetParamObjectWithId(&$param)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$configId = $param->id;
		if (empty($param->object)){
			$obj = null;
		} else {
			$obj = serialize($param->object);
		}
		$ret = $this->gInstance->getSytemDbObject()->updateWidgetParam($this->gEnv->getCurrentWidgetId(), $obj, $this->gEnv->getCurrentUserId(), $now, $configId);
		if ($param->id == -1 && $ret) $param->id = $configId;
		return $ret;
	}
	/**
	 * 管理画面起動時のウィジェット定義ID、画面定義シリアル番号を取得
	 *
	 * @param string 	$defSerial			画面定義シリアル番号
	 * @param string 	$defConfigId		ウィジェット定義ID
	 * @param object	$paramObj			ウィジェットパラメータオブジェクトの配列
	 * @param bool $withZeroId				定義ID=0のオブジェクトも取得
	 * @return								なし
	 */
	function startPageDefParam(&$defSerial, &$defConfigId, &$paramObj, $withZeroId = false)
	{
		$defSerial = $this->_defSerial;
		$defConfigId = $this->_defConfigId;
		$paramObj = $this->getWidgetParamObjectWithId($withZeroId);
		$this->_configParamObj = $paramObj;	// 設定画面のパラメータオブジェクト
	}
	/**
	 * 管理画面起動時のウィジェット定義ID、画面定義シリアル番号を更新
	 *
	 * @param string 	$defSerial			画面定義シリアル番号
	 * @param string 	$defConfigId		ウィジェット定義ID
	 * @param object	$paramObj			ウィジェットパラメータオブジェクトの配列
	 * @return								なし
	 */
	function endPageDefParam(&$defSerial, &$defConfigId, &$paramObj)
	{
		//$this->_defSerial = $defSerial;		// シリアル番号は更新しない
		$this->_defConfigId = $defConfigId;
	}
	/**
	 * ウィジェットパラメータオブジェクトを追加
	 *
	 * @param string 	$defSerial			画面定義シリアル番号
	 * @param string 	$defConfigId		ウィジェット定義ID
	 * @param object	$paramObj			ウィジェットパラメータオブジェクトの配列
	 * @param object	$newObj				新規追加パラメータオブジェクト(nameメンバーに設定名をセット)
	 * @param string 	$menuId				メニューID(メニューの場合)
	 * @return bool							true=成功、false=失敗
	 */
	function addPageDefParam(&$defSerial, &$defConfigId, &$paramObj, $newObj, $menuId = '')
	{
		$newParam = new stdClass;
		$newParam->id = -1;		// 新規追加
		$newParam->object = $newObj;
	
		// ウィジェットパラメータオブジェクト更新
		$ret = $this->updateWidgetParamObjectWithId($newParam);
		if ($ret){
			$paramObj[] = $newParam;		// 新規定義を追加
			$this->_configParamObj = $paramObj;	// 設定画面のパラメータオブジェクト更新
			$defConfigId = $newParam->id;		// 定義定義IDを更新
		}
		
		// 画面定義更新
		if ($ret && !empty($defSerial)){		// 画面作成から呼ばれている場合のみ更新
			$ret = $this->_db->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $defConfigId, $newObj->name, $menuId);
		}
		
		// キャッシュをクリア
		$this->gCache->clearCacheByWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defConfigId);
		
		// 親ウィンドウを更新
		$this->gPage->updateParentWindow($defSerial);
		return $ret;
	}
	/**
	 * ウィジェットパラメータオブジェクトとコンテンツ情報を追加
	 *
	 * @param string $defSerial			画面定義シリアル番号
	 * @param string $defConfigId		ウィジェット定義ID
	 * @param object $paramObj			ウィジェットパラメータオブジェクトの配列
	 * @param object $newObj			新規追加パラメータオブジェクト(nameメンバーに設定名をセット)
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentId			コンテンツID
	 * @return bool							true=成功、false=失敗
	 */
	function addPageDefParamWithContent(&$defSerial, &$defConfigId, &$paramObj, $newObj, $contentType = '', $contentId = '')
	{
		$newParam = new stdClass;
		$newParam->id = -1;		// 新規追加
		$newParam->object = $newObj;
	
		// ウィジェットパラメータオブジェクト更新
		$ret = $this->updateWidgetParamObjectWithId($newParam);
		if ($ret){
			$paramObj[] = $newParam;		// 新規定義を追加
			$this->_configParamObj = $paramObj;	// 設定画面のパラメータオブジェクト更新
			$defConfigId = $newParam->id;		// 定義定義IDを更新
		}
		
		// 画面定義更新
		if ($ret && !empty($defSerial)){		// 画面作成から呼ばれている場合のみ更新
			$ret = $this->_db->updateWidgetConfigIdWithContent($this->gEnv->getCurrentWidgetId(), $defSerial, $defConfigId, $newObj->name, $contentType, $contentId);
		}
		
		// キャッシュをクリア
		$this->gCache->clearCacheByWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defConfigId);
		
		// 親ウィンドウを更新
		$this->gPage->updateParentWindow($defSerial);
		return $ret;
	}
	/**
	 * ウィジェットパラメータオブジェクトを更新
	 *
	 * @param string 	$defSerial			画面定義シリアル番号
	 * @param string 	$defConfigId		ウィジェット定義ID
	 * @param object	$paramObj			ウィジェットパラメータオブジェクトの配列
	 * @param int		$id					定義ID
	 * @param object	$updateObj			更新パラメータオブジェクト
	 * @param string 	$menuId				メニューID(メニューの場合)
	 * @return bool							true=成功、false=失敗
	 */
	function updatePageDefParam(&$defSerial, &$defConfigId, &$paramObj, $id, $updateObj, $menuId = '')
	{
		// 該当項目を更新
		$ret = false;
		for ($i = 0; $i < count($paramObj); $i++){
			$configId	= $paramObj[$i]->id;// 定義ID
			if ($configId == $id){
				// ウィジェットオブジェクト更新
				$paramObj[$i]->object = $updateObj;
				
				// ウィジェットパラメータオブジェクトを更新
				$ret = $this->updateWidgetParamObjectWithId($paramObj[$i]);
				if ($ret) $defConfigId = $id;		// 定義定義IDを更新
				break;
			}
		}
		$this->_configParamObj = $paramObj;	// 設定画面のパラメータオブジェクト更新
		
		// 画面定義更新
		if ($ret && !empty($defSerial)){		// 画面作成から呼ばれている場合のみ更新
			$ret = $this->_db->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $id, $updateObj->name, $menuId);
		}
		
		// キャッシュをクリア
		$this->gCache->clearCacheByWidgetConfigId($this->gEnv->getCurrentWidgetId(), $id);
		
		// 親ウィンドウを更新
		$this->gPage->updateParentWindow($defSerial);
		return $ret;
	}
	/**
	 * ウィジェットパラメータオブジェクトとコンテンツ情報を更新
	 *
	 * @param string $defSerial			画面定義シリアル番号
	 * @param string $defConfigId		ウィジェット定義ID
	 * @param object $paramObj			ウィジェットパラメータオブジェクトの配列
	 * @param int    $id				定義ID
	 * @param object $updateObj			更新パラメータオブジェクト
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentId			コンテンツID
	 * @return bool						true=成功、false=失敗
	 */
	function updatePageDefParamWithContent(&$defSerial, &$defConfigId, &$paramObj, $id, $updateObj, $contentType = '', $contentId = '')
	{
		// 該当項目を更新
		$ret = false;
		for ($i = 0; $i < count($paramObj); $i++){
			$configId	= $paramObj[$i]->id;// 定義ID
			if ($configId == $id){
				// ウィジェットオブジェクト更新
				$paramObj[$i]->object = $updateObj;
				
				// ウィジェットパラメータオブジェクトを更新
				$ret = $this->updateWidgetParamObjectWithId($paramObj[$i]);
				if ($ret) $defConfigId = $id;		// 定義定義IDを更新
				break;
			}
		}
		$this->_configParamObj = $paramObj;	// 設定画面のパラメータオブジェクト更新
		
		// 画面定義更新
		if ($ret && !empty($defSerial)){		// 画面作成から呼ばれている場合のみ更新
			$ret = $this->_db->updateWidgetConfigIdWithContent($this->gEnv->getCurrentWidgetId(), $defSerial, $id, $updateObj->name, $contentType, $contentId);
		}
		
		// キャッシュをクリア
		$this->gCache->clearCacheByWidgetConfigId($this->gEnv->getCurrentWidgetId(), $id);
		
		// 親ウィンドウを更新
		$this->gPage->updateParentWindow($defSerial);
		return $ret;
	}
	/**
	 * ウィジェットパラメータオブジェクトを削除
	 *
	 * @param string 	$defSerial			画面定義シリアル番号
	 * @param string 	$defConfigId		ウィジェット定義ID
	 * @param object	$paramObj			ウィジェットパラメータオブジェクトの配列
	 * @param int		$idArray			削除する定義ID
	 * @return bool							true=成功、false=失敗
	 */
	function delPageDefParam(&$defSerial, &$defConfigId, &$paramObj, $idArray)
	{
		$saveDefConfigId = $defConfigId;		// 削除対象を退避
		$delConfig = false;// 定義IDが削除対象かどうか
		$ret = false;
		for ($i = 0; $i < count($paramObj); $i++){
			$configId = $paramObj[$i]->id;// 定義ID
			if (in_array($configId, $idArray)){		// 削除対象のとき
				$newParam = new stdClass;
				$newParam->id = $configId;
				$newParam->object = null;		// 削除処理
				$ret = $this->updateWidgetParamObjectWithId($newParam);
				if ($ret){
					if ($configId == $defConfigId) $delConfig = true;
				} else {
					break;
				}
			}
		}
		// データ再設定
		if ($ret){
			if ($delConfig) $defConfigId = 0;
			
			// パラメータオブジェクトを再取得
			$paramObj = $this->getWidgetParamObjectWithId();
		}
		$this->_configParamObj = $paramObj;	// 設定画面のパラメータオブジェクト更新
		
		// 画面定義更新
		if ($ret && !empty($defSerial) && $delConfig){		// 画面作成から呼ばれている場合のみ更新
			$ret = $this->_db->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, 0, '');
		}
		
		// キャッシュをクリア
		$this->gCache->clearCacheByWidgetConfigId($this->gEnv->getCurrentWidgetId(), $saveDefConfigId);
		
		// 親ウィンドウを更新
		$this->gPage->updateParentWindow($defSerial);
		return $ret;
	}
	/**
	 * ウィジェットパラメータオブジェクトを取得
	 *
	 * @param string 	$defSerial			画面定義シリアル番号
	 * @param string 	$defConfigId		ウィジェット定義ID
	 * @param object	$paramObj			ウィジェットパラメータオブジェクトの配列
	 * @param int		$id					定義ID
	 * @param object	$destObj			取得パラメータオブジェクト
	 * @return bool							true=成功、false=失敗
	 */
	function getPageDefParam(&$defSerial, &$defConfigId, &$paramObj, $id, &$destObj)
	{
		for ($i = 0; $i < count($paramObj); $i++){
			$configId	= $paramObj[$i]->id;// 定義ID
			if ($configId == $id){
				$destObj = $paramObj[$i]->object;
				return true;
			}
		}
		return false;
	}
	/**
	 * 新規用のウィジェット定義IDを取得
	 *
	 * @param object	$paramObj			ウィジェットパラメータオブジェクトの配列
	 * @return int							新規用のウィジェットID
	 */
	function getTempConfigId($paramObj)
	{
		$maxId = 0;
		for ($i = 0; $i < count($paramObj); $i++){
			$configId	= $paramObj[$i]->id;// 定義ID
			if ($configId > $maxId) $maxId = $configId;
		}
		$maxId++;
		return $maxId;
	}
	/**
	 * ウィジェット専用セッション値を設定
	 *
	 * ウィジェット専用セッションは同一ウィジェット内でのみ使用するセッションである。
	 * 「ウィジェットID:キー名」形式のキーでセッションに格納する。
	 *
	 * @param string $key		セッション格納用のキー
	 * @param string $default	値を設定しない場合のデフォルト値
	 * @return なし
	 */
	function setWidgetSession($key, $default = '')
	{
		$keyName = M3_SESSION_WIDGET . $this->gEnv->getCurrentWidgetId() . ':' . $key;
		$this->gRequest->setSessionValue($keyName, $default);
	}
	/**
	 * ウィジェット専用セッション値を取得
	 *
	 * ウィジェット専用セッションは同一ウィジェット内でのみ使用するセッションである。
	 * 「ウィジェットID:キー名」形式のキーでセッションに格納する。
	 *
	 * @param string $key		セッション格納用のキー
	 * @param string $default	値が存在しない場合のデフォルト値
	 * @return string			値
	 */
	function getWidgetSession($key, $default = '')
	{
		$keyName = M3_SESSION_WIDGET . $this->gEnv->getCurrentWidgetId() . ':' . $key;
		return $this->gRequest->getSessionValue($keyName, $default);
	}
	/**
	 * ウィジェット専用セッション値(オブジェクト)を設定
	 *
	 * ウィジェット専用セッションは同一ウィジェット内でのみ使用するセッションである。
	 *
	 * @param object $paramObj	パラメータオブジェクト。nullをセットした場合は削除。
	 * @return なし
	 */
	function setWidgetSessionObj($paramObj)
	{
		$keyName = M3_SESSION_WIDGET . $this->gEnv->getCurrentWidgetId();
		if (is_null($paramObj)){
			$this->gRequest->unsetSessionValue($keyName);
		} else {
			$this->gRequest->setSessionValue($keyName, serialize($paramObj));
		}
	}
	/**
	 * ウィジェット専用セッション値(オブジェクト)を取得
	 *
	 * ウィジェット専用セッションは同一ウィジェット内でのみ使用するセッションである。
	 *
	 * @return object		ウィジェットオブジェクト。取得できないときはnull。
	 */
	function getWidgetSessionObj()
	{
		$keyName = M3_SESSION_WIDGET . $this->gEnv->getCurrentWidgetId();
		$serializedObj = $this->gRequest->getSessionValue($keyName);
		if (empty($serializedObj)){
			return null;
		} else {
			return unserialize($serializedObj);
		}
	}
	/**
	 * インナーウィジェットを出力を取得
	 *
	 * @param string $id		ウィジェットID+インナーウィジェットID
	 * @param string $configId	インナーウィジェット定義ID
	 * @param bool $isAdmin		管理者機能(adminディレクトリ以下)かどうか
	 * @return string 			出力内容
	 */
	function getIWidgetContent($id, $configId, $isAdmin = false)
	{
		$ret = $this->gPage->commandIWidget(10/*コンテンツ取得*/, $id, $configId, $paramObj, $optionParamObj, $resultObj, $content, $isAdmin);
		return $content;
	}
	/**
	 * インナーウィジェットにパラメータを設定
	 *
	 * @param string $id				ウィジェットID+インナーウィジェットID
	 * @param string $configId			インナーウィジェット定義ID
	 * @param string $param				シリアル化したパラメータオブジェクト
	 * @param object $optionParamObj	追加パラメータオブジェクト
	 * @param bool $isAdmin				管理者機能(adminディレクトリ以下)かどうか
	 * @return string 					出力内容
	 */
	function setIWidgetParam($id, $configId, $param, $optionParamObj, $isAdmin = false)
	{
		$paramObj = unserialize($param);			// パラメータをオブジェクト化
		$ret = $this->gPage->commandIWidget(0/*パラメータ設定*/, $id, $configId, $paramObj, $optionParamObj, $resultObj, $content, $isAdmin);
		return $ret;
	}
	/**
	 * インナーウィジェットのパラメータを入力値から更新
	 *
	 * @param string $id				ウィジェットID+インナーウィジェットID
	 * @param string $configId			インナーウィジェット定義ID
	 * @param string $param				シリアル化したパラメータオブジェクト
	 * @param object $optionParamObj	追加パラメータオブジェクト
	 * @param bool $isAdmin				管理者機能(adminディレクトリ以下)かどうか
	 * @return string 					出力内容
	 */
	function updateIWidgetParam($id, $configId, &$param, &$optionParamObj, $isAdmin = false)
	{
		$ret = $this->gPage->commandIWidget(1/*パラメータ更新*/, $id, $configId, $paramObj, $optionParamObj, $resultObj, $content, $isAdmin);
		$param = serialize($paramObj);		// シリアル化
		return $ret;
	}
	/**
	 * インナーウィジェットにパラメータを設定し計算処理を行う
	 *
	 * @param string $id				ウィジェットID+インナーウィジェットID
	 * @param string $configId			インナーウィジェット定義ID
	 * @param string $param				シリアル化したパラメータオブジェクト
	 * @param object $optionParamObj	追加パラメータオブジェクト
	 * @param bool $isAdmin				管理者機能(adminディレクトリ以下)かどうか
	 * @return string 					出力内容
	 */
	function calcIWidgetParam($id, $configId, $param, &$optionParamObj, &$resultObj, $isAdmin = false)
	{
		$paramObj = unserialize($param);			// パラメータをオブジェクト化
		$ret = $this->gPage->commandIWidget(2/*計算*/, $id, $configId, $paramObj, $optionParamObj, $resultObj, $content, $isAdmin);
		return $ret;
	}
	/**
	 * ウィジェット指定(FIND)でコマンドを実行するためのURLを作成
	 *
	 * @param string $widgetId	コマンドを送信先ウィジェット
	 * @param string $param		送信先ウィジェットに渡すURLパラメータ
	 * @return string			作成したURL
	 */
	function createCmdUrlToWidget($widgetId, $param = '')
	{
		return $this->gPage->createWidgetCmdUrl($widgetId, $this->gEnv->getCurrentWidgetId(), $param);
	}
	/**
	 * 自ウィジェットでコマンドを実行するためのURLを作成
	 *
	 * @param string $param		ウィジェットに渡すURLパラメータ
	 * @param bool $byMacro		マクロ変換で返すかどうか
	 * @return string			作成したURL
	 */
	function createCmdUrlToCurrentWidget($param, $byMacro = false)
	{
		return $this->gPage->createDirectWidgetCmdUrl($this->gEnv->getCurrentWidgetId(), $param, $byMacro);
	}
	/**
	 * 管理画面起動時の定義IDを取得
	 *
	 * @param RequestManager 	$request		HTTPリクエスト処理クラス
	 * @return int								定義ID(0以上)
	 */
	function getPageDefConfigId($request)
	{
		$configId = 0;				// デフォルト定義ID
		
		$value = $request->trimValueOf(M3_REQUEST_PARAM_PAGE_DEF_CONFIG_ID);
		if ($value != '') $configId = intval($value);
		if ($configId < 0) $configId = 0;		// エラー値を修正
		return $configId;
	}
	/**
	 * 管理画面起動時の画面定義シリアル番号を取得
	 *
	 * @param RequestManager 	$request		HTTPリクエスト処理クラス
	 * @return int								シリアル番号(0以上)
	 */
	function getPageDefSerial($request)
	{
		$serial = 0;				// シリアル番号
		
		$value = $request->trimValueOf(M3_REQUEST_PARAM_PAGE_DEF_SERIAL);
		if ($value != '') $serial = intval($value);
		if ($serial < 0) $serial = 0;		// エラー値を修正
		return $serial;
	}
	/**
	 * ウィジェットの呼び出しが可能か取得
	 *
	 * @param string $widgetId		呼び出し対象のウィジェット
	 * @return bool					true=呼び出し可能、false=呼び出し不可
	 */
	function canFindWidget($widgetId)
	{
		$ret = $this->gPage->isWidgetOnPage($this->gEnv->getCurrentPageId(), $widgetId);
		return $ret;
	}
	/**
	 * 指定コンテンツタイプの利用可能なウィジェットがあるか確認
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @return bool					true=呼び出し可能、false=呼び出し不可
	 */
	function canFindWidgetByContentType($contentType)
	{
		$widgetId = $this->gPage->getWidgetIdWithPageInfoByContentType($this->gEnv->getCurrentPageId(), $contentType);
		if (empty($widgetId)){
			return false;
		} else {
			return true;
		}
	}
	/**
	 * 自ウィジェットまたは他ウィジェットの管理画面用のURLを作成
	 *
	 * @param string,array $param		URLに付加するパラメータ
	 * @param string       $widgetId	ウィジェット指定の場合はウィジェットID。空の場合は現在のウィジェットが対象。
	 * @return string					作成したURL
	 */
	function getConfigAdminUrl($param = '', $widgetId = '')
	{
		// パラメータを作成
		$paramArray = array();
		$paramArray[M3_REQUEST_PARAM_OPERATION_COMMAND] = M3_REQUEST_CMD_CONFIG_WIDGET;			// ウィジェット定義
		if (empty($widgetId)){
			$paramArray[M3_REQUEST_PARAM_WIDGET_ID] = $this->gEnv->getCurrentWidgetId();			// ウィジェットID
		} else {
			$paramArray[M3_REQUEST_PARAM_WIDGET_ID] = $widgetId;			// ウィジェットID
		}
		if (is_array($param)){
			$paramArray = array_merge($paramArray, $param);
		} else if (is_string($param) && !empty($param)){
			parse_str($param, $addArray);
			$paramArray = array_merge($paramArray, $addArray);
		}
		
		// URL作成
		$destUrl = $this->gEnv->getDefaultAdminUrl();
		$paramStr = $this->_createParamStr($paramArray);
		if (!empty($paramStr)) $destUrl .= '?' . $paramStr;
		return $destUrl;
	}
	/**
	 * URLを作成
	 *
	 * ・ページのSSL設定状況に応じて、SSL用URLに変換
	 *
	 * @param string $path				URL作成用のパス
	 * @param bool $isLink				作成するURLがリンクかどうか。リンクの場合はリンク先のページのSSLの状態に合わせる
	 * @param string,array $param		URLに付加するパラメータ
	 * @return string					作成したURL
	 */
	function getUrl($path, $isLink = false, $param = '')
	{
		$destPath = '';
		$path = trim($path);
		
		// URLの示すファイルタイプを取得
		if ($this->gEnv->getUseSsl()){		// SSLを使用する場合
			// 現在のページがSSL使用設定になっているかどうかを取得
			$currentPageId = $this->gEnv->getCurrentPageId();
			$currentPageSubId = $this->gEnv->getCurrentPageSubId();
			$isSslPage = $this->gPage->isSslPage($currentPageId, $currentPageSubId);
			
			$baseUrl = $this->gEnv->getRootUrl();
			$sslBaseUrl = $this->gEnv->getSslRootUrl();		// SSL用URLが設定されている場合、設定値を使用
			
			// パスを解析
			if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
				$relativePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '', $path);
				if (empty($relativePath)){			// Magic3のルートURLの場合
					if ($isLink){		// リンクタイプのとき
						$destPath = $baseUrl;
					} else {		// リンクでないとき
						// 現在のページのSSLの状態に合わせる
						if ($isSslPage){
							$destPath = $sslBaseUrl;
						} else {
							$destPath = $baseUrl;
						}
					}
					// トップページへのリンクの場合とどう区別するか→トップページへのリンクの場合はフルパスで指定?
				} else {
					$destPath = $this->_createUrlByRelativePath($isSslPage, $baseUrl, $sslBaseUrl, $relativePath, $param, $isLink);
				}
			} else if (strncasecmp($path, 'http://', strlen('http://')) == 0 || strncasecmp($path, 'https://', strlen('https://')) == 0){				// 絶対パスURLのとき
				// パスを解析
				$relativePath = str_replace($baseUrl, '', $path);		// ルートURLからの相対パスを取得
				if (empty($relativePath)){			// Magic3のルートURLの場合
					if ($isLink){		// リンクタイプのとき
						$destPath = $baseUrl;
					} else {		// リンクでないとき
						// 現在のページのSSLの状態に合わせる
						if ($isSslPage){
							$destPath = $sslBaseUrl;
						} else {
							$destPath = $baseUrl;
						}
					}
					// トップページへのリンクの場合とどう区別するか→トップページへのリンクの場合はフルパスで指定?
				} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?')){		// ルートURL配下のとき
					$destPath = $this->_createUrlByRelativePath($isSslPage, $baseUrl, $sslBaseUrl, $relativePath, $param, $isLink);
				} else {		// ルートURL以外のURLのとき
					$destPath = $path;
				}
			} else {		// 相対パスのとき
			}
		} else {		// SSLを使用しない場合
			if ($this->_useHierPage){		// 階層化ページ使用のとき
				$createPath = true;		// パスを生成するかどうか
				$baseUrl = $this->gEnv->getRootUrl();
				$sslBaseUrl = $this->gEnv->getSslRootUrl();		// SSL用URLが設定されている場合、設定値を使用
				if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
					$relativePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '', $path);
				} else if (strncasecmp($path, 'http://', strlen('http://')) == 0 || strncasecmp($path, 'https://', strlen('https://')) == 0){				// 絶対パスURLのとき
					$relativePath = str_replace($baseUrl, '', $path);		// ルートURLからの相対パスを取得
					if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?')){		// ルートURL配下のとき
					} else {		// ルートURL以外のURLのとき
						$createPath = false;		// パスを生成するかどうか
					}
				}
				if ($createPath){		// パスを生成するかどうか
					$destPath = $this->_createUrlByRelativePath(false, $baseUrl, $sslBaseUrl, $relativePath, $param);
				} else {
					$destPath = $path;
				}
			} else {
				if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
					$destPath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $path);
				} else {
					$destPath = $path;
				}
			}
		}
		// マルチドメイン運用の場合はパスを修正
		if ($this->_isMultiDomain){
			if ($this->gEnv->getIsSmartphoneSite()){		// スマートフォンサイトの場合
				$domainUrl = $this->gEnv->getDefaultSmartphoneUrl(false/*ファイル名なし*/);
				$relativePath = str_replace($domainUrl . '/' . M3_DIR_NAME_SMARTPHONE, '', $destPath);
				if (strStartsWith($relativePath, '/')){
					$destPath = $domainUrl . $relativePath;
				} else {
					// メインのドメインの場合はアクセスポイント用ドメインに変換
					$relativePath = str_replace(M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_SMARTPHONE, '', $destPath);
					if (strStartsWith($relativePath, '/')) $destPath = $domainUrl . $relativePath;
				}
			} else if ($this->gEnv->getIsMobileSite()){		// 携帯サイトの場合
				$domainUrl = $this->gEnv->getDefaultMobileUrl(false, false/*ファイル名なし*/);
				$relativePath = str_replace($domainUrl . '/' . M3_DIR_NAME_MOBILE, '', $destPath);
				if (strStartsWith($relativePath, '/')){
					$destPath = $domainUrl . $relativePath;
				} else {
					// メインのドメインの場合はアクセスポイント用ドメインに変換
					$relativePath = str_replace(M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_MOBILE, '', $destPath);
					if (strStartsWith($relativePath, '/')) $destPath = $domainUrl . $relativePath;
				}
			}
		}
		return $destPath;
	}
	/**
	 * 相対パスからURLを作成
	 *
	 * @param bool $isSslPage		現在のページがSSL使用になっているかどうか
	 * @param string $baseUrl		ルートURL
	 * @param string $sslBaseUrl	SSL使用時のルートURL
	 * @param string $path			相対パス
	 * @param string,array $param		URLに付加するパラメータ
	 * @param bool $isLink				作成するURLがリンクかどうか。リンクの場合はリンク先のページのSSLの状態に合わせる
	 * @return string				作成したURLパラメータ
	 */
	function _createUrlByRelativePath($isSslPage, $baseUrl, $sslBaseUrl, $path, $param = '', $isLink = false)
	{
		$destPath = '';
		
		// ファイル名を取得
		$paramArray = array();
		list($filename, $query) = explode('?', basename($path));
		$saveFilename = $filename;		// ファイル名を退避
		if (empty($filename)) $filename = M3_FILENAME_INDEX;

		if (!empty($query)) parse_str($query, $paramArray);
		if (is_array($param)){
			$paramArray = array_merge($paramArray, $param);
		} else if (is_string($param) && !empty($param)){
			parse_str($param, $addArray);
			$paramArray = array_merge($paramArray, $addArray);
		}
		// ページIDを取得
		if (strEndsWith($filename, '.php')){			// PHPスクリプトのとき
			if ($isLink){		// リンクタイプのとき
				// ページIDを取得
				$pageId = basename($filename, '.php');
				$pageSubId = $paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID];
			
				// 目的のページのSSL設定状況を取得
				$isSslSelPage = $this->gPage->isSslPage($pageId, $pageSubId);
				if ($isSslSelPage){
					$destPath = $sslBaseUrl;
				} else {
					$destPath = $baseUrl;
				}
			} else {
				// 現在のページのSSLの状態に合わせる
				if ($isSslPage){
					//$destPath = $sslBaseUrl . $path;
					$destPath = $sslBaseUrl;
				} else {
					//$destPath = $baseUrl . $path;
					$destPath = $baseUrl;
				}
			}
			// 階層化パスで出力のとき
			if ($this->_useHierPage && $filename == M3_FILENAME_INDEX){
				$subId = $paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID];
				$dirName = dirname($path);
				if ($dirName == '/'){
					$destPath .= $dirName;
				} else {
					$destPath .= $dirName . '/';
				}
				if (!empty($subId)){
					$destPath .= $subId . '/';
					unset($paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID]);
				}
			} else {
				$dirName = dirname($path);
				if ($dirName == '/'){
					$destPath .= $dirName . $saveFilename;
				} else {
					$destPath .= $dirName . '/' . $saveFilename;
				}
			}
			// ページサブIDがデフォルト値のときはページサブIDを省略
			//if ($paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID] == 
			$paramStr = $this->_createParamStr($paramArray);
			if (!empty($paramStr)) $destPath .= '?' . $paramStr;
		} else {
			// 現在のページのSSLの状態に合わせる
			if ($isSslPage){
				$destPath = $sslBaseUrl . $path;
			} else {
				$destPath = $baseUrl . $path;
			}
		}
		return $destPath;
	}
	/**
	 * URLパラメータ文字列作成
	 *
	 * @param array $paramArray			URL作成用のパス
	 * @return string					作成したURLパラメータ
	 */
	function _createParamStr($paramArray)
	{
		$destParam = '';
		if (count($paramArray) == 0) return $destParam;

		$sortParam = array();
		$keys = array_keys($paramArray);
		$keyCount = count($keys);
		for ($i = 0; $i < $keyCount; $i++){
			$key = $keys[$i];
			$value = $paramArray[$key];
			$orderNo = $this->urlParamOrder[$key];
			if (!isset($orderNo)) $orderNo = 100;
			$sortParam[] = array('key' => $key, 'value' => $value, 'no' => $orderNo);
		}
		//usort($sortParam, create_function('$a,$b', 'return $a["no"] - $b["no"];'));
		usort($sortParam, function($a, $b)
		{
			return $a['no'] - $b['no'];
		});

		// 文字列を作成
		$sortCount = count($sortParam);
		for ($i = 0; $i < $sortCount; $i++){
			if ($i > 0) $destParam .= '&';
			$destParam .= rawurlencode($sortParam[$i]['key']) . '=' . rawurlencode($sortParam[$i]['value']);
		}
		return $destParam;
	}
	/**
	 * URLに追加設定するパラメータ
	 *
	 * @param string $key	キー
	 * @param string $value	値
	 * @return 				なし
	 */
	function addOptionUrlParam($key, $value)
	{
		if (!empty($key) && !empty($value)) $this->optionUrlParam[$key] = $value;
	}
	/**
	 * パラメータ付きのURLを取得
	 *
	 * @return string			パラメータ付きURL
	 */
	function getUrlWithOptionParam()
	{
		$cmd = $this->gRequest->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		
		if ($cmd == M3_REQUEST_CMD_DO_WIDGET){			// フロント画面のウィジェット設定画面の場合
			$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . 
						'&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
		} else {
			$url = $this->gEnv->getDefaultUrl();
		}
		// その他のパラメータ
		$url = createUrl($url, $this->optionUrlParam);

		return $url;
/*		
		$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
		$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
		$urlparam .= 'openby=other&' . M3_REQUEST_PARAM_BLOG_ID . '=' . $blogId;
		$baseUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;*/
	}
	/**
	 * キャッシュデータをクリア
	 *
	 * @param array $param	削除する対象のURLパラメータ。空のときはすべてのデータを削除。
	 * @return 				なし
	 */
	function clearCache($param = array())
	{
		$this->gCache->clearCacheByWidgetId($this->gEnv->getCurrentWidgetId(), $param);
	}
	/**
	 * 多言語対応文字列を作成
	 *
	 * @param array $langs		言語ごとの文字列の連想配列
	 * @return string				多言語対応文字列
	 */
	function serializeLangArray($langs)
	{
		$destStr = '';
		if (!is_array($langs)) return $destStr;
		
		$keys = array_keys($langs);
		$keyCount = count($keys);
		for ($i = 0; $i < $keyCount; $i++){
			$langId = $keys[$i];
			if (empty($langId)) continue;
			$destStr .= $langId . M3_LANG_SEPARATOR . $langs[$langId];
			if ($i < $keyCount -1) $destStr .= M3_TAG_START . M3_TAG_MACRO_SEPARATOR . M3_TAG_END;
		}
		return $destStr;
	}
	/**
	 * 多言語対応文字列をテキストの連想配列に変換
	 *
	 * @param string $str		多言語対応文字列
	 * @return array			言語ごとの文字列の連想配列
	 */
	function unserializeLangArray($str)
	{
		$langs = array();
		if (empty($str)) return $langs;
		
		$itemArray = explode(M3_TAG_START . M3_TAG_MACRO_SEPARATOR . M3_TAG_END, $str);		// セパレータ分割
		for ($i = 0; $i < count($itemArray); $i++){
			$line = $itemArray[$i];
			if (empty($line)) continue;
			$pos = strpos($line, M3_LANG_SEPARATOR);		// 言語ID取得
			if ($pos === false){		// 言語IDがないときはデフォルトの言語IDを使用
				$langId = $this->gEnv->getDefaultLanguage();
				$langStr = $line;
			} else {
				list($langId, $langStr) = explode(M3_LANG_SEPARATOR, $line, 2);
				if (empty($langId)) continue;
			}
			$langs[$langId] = $langStr;
		}
		return $langs;
	}
	/**
	 * 多言語対応文字列からデフォルト言語の文字列を取得
	 *
	 * @param string $str		多言語対応文字列
	 * @return string			デフォルト言語文字列
	 */
	function getDefaultLangString($str)
	{
		$langs = $this->unserializeLangArray($str);
		$langStr = $langs[$this->gEnv->getDefaultLanguage()];
		if (isset($langStr)){
			return $langStr;
		} else {
			return '';
		}
	}
	/**
	 * 多言語対応文字列から現在の言語の文字列を取得
	 *
	 * @param string $str		多言語対応文字列
	 * @return string			現在の言語文字列(存在しない場合はデフォルト言語文字列)
	 */
	function getCurrentLangString($str)
	{
		$langs = $this->unserializeLangArray($str);
		$langStr = $langs[$this->gEnv->getCurrentLanguage()];
		if (isset($langStr)){
			return $langStr;
		} else {
			$langStr = $langs[$this->gEnv->getDefaultLanguage()];
			if (isset($langStr)){
				return $langStr;
			} else {
				return '';
			}
		}
	}
	/**
	 * 現在の言語のラベル用の文字列を取得
	 *
	 * @param string $str		取得元文字列
	 * @return string			ラベル用テキスト
	 */
	function getLabelText($str)
	{
		$labelText = $this->getCurrentLangString($str);
		$labelText = $this->gDesign->createLinkFromLinkFomatText($labelText, false/*HTMLエスケープなし*/);
		return $labelText;
	}
	/**
	 * 連想配列のDB格納用文字列を作成
	 *
	 * @param array $fields		連想配列
	 * @return string			DB格納用文字列
	 */
	function serializeArray($fields)
	{
		$destStr = '';
		if (!is_array($fields)) return $destStr;
		
		$keys = array_keys($fields);
		$keyCount = count($keys);
		for ($i = 0; $i < $keyCount; $i++){
			$key = $keys[$i];
			if (empty($key)) continue;
			$destStr .= $key . M3_MACRO_SEPARATOR . $fields[$key];
			if ($i < $keyCount -1) $destStr .= M3_TAG_START . M3_TAG_MACRO_SEPARATOR . M3_TAG_END;
		}
		return $destStr;
	}
	/**
	 * DB格納用文字列を連想配列に変換
	 *
	 * @param string $str		DB格納用文字列
	 * @return array			連想配列
	 */
	function unserializeArray($str)
	{
		$fields = array();
		if (empty($str)) return $fields;
		
		$itemArray = explode(M3_TAG_START . M3_TAG_MACRO_SEPARATOR . M3_TAG_END, $str);		// セパレータ分割
		for ($i = 0; $i < count($itemArray); $i++){
			$line = $itemArray[$i];
			if (empty($line)) continue;
			
			$pos = strpos($line, M3_MACRO_SEPARATOR);		// キー取得
			if ($pos === false){		// キーがないとき
				continue;
			} else {
				list($key, $valueStr) = explode(M3_MACRO_SEPARATOR, $line, 2);
				if (empty($key)) continue;
			}
			$fields[$key] = $valueStr;
		}
		return $fields;
	}
	/**
	 * マクロ置換用データをクリーニング
	 *
	 * @param string $str		クリーニング文字列
	 * @return string			作成文字列
	 */
	function cleanMacroValue($str)
	{
		return str_replace(array(M3_TAG_START, M3_TAG_END), array('', ''), $str);
	}
	/**
	 * 前画面、次画面遷移用のボタンを追加(フロント画面用)
	 *
	 * @param int $pos			ボタンの表示位置(0=上部、1=下部)
	 * @param string $prevUrl	前画面のURL(作成しない場合は空文字列)
	 * @param string $nextUrl	次画面のURL(作成しない場合は空文字列)
	 * @param string $prevTitle	前画面のタイトル(省略時はデフォルトのテキスト)
	 * @param string $nextTitle	次画面のタイトル(省略時はデフォルトのテキスト)
	 * @return					なし
	 */
	function setJoomlaPageNavData($pos, $prevUrl, $nextUrl, $prevTitle = null, $nextTitle = null)
	{
		// Joomla!新型テンプレートでない場合は終了
		if ($this->_renderType != M3_RENDER_JOOMLA_NEW) return;
		
		$pageNavData = array();
		$pageNavData['pos']			= $pos;
		$pageNavData['prev']		= $prevUrl;
		$pageNavData['next']		= $nextUrl;
		$pageNavData['prev_label']	= $prevTitle;
		$pageNavData['next_label']	= $nextTitle;
		$this->gEnv->setJoomlaPageNavData($pageNavData);
	}
	/**
	 * 記事表示データを設定(フロント画面用)
	 *
	 * @param array $viewItemsData		記事データ一覧
	 * @param int $leadContentCount		先頭(leading部)のコンテンツ数
	 * @param int $columnContentCount	カラム部(intro部)のコンテンツ数
	 * @param int $columnCount			カラム部(intro部)のカラム数
	 * @param string $categoryDesc		カテゴリーの説明
	 * @param string $readMoreTitle		「もっと読む」ボタンのタイトル(ウィジェットでのデフォルト値)
	 * @param bool $withDefaultOutput	ウィジェットデフォルト描画出力をカテゴリー説明部に出力するかどうか
	 * @return					なし
	 */
	function setJoomlaViewData($viewItemsData, $leadContentCount, $columnContentCount, $columnCount, $categoryDesc = '', $readMoreTitle = '', $withDefaultOutput = false)
	{
		// Joomla!新型テンプレートでない場合は終了
		if ($this->_renderType != M3_RENDER_JOOMLA_NEW) return;
		
		// Joomla!ビュー用データを設定
		$viewData = array();
		$viewData['Items'] = $viewItemsData;
		// Magic3追加分
		$viewData['leadContentCount']	= $leadContentCount;			// 先頭(leading部)のコンテンツ数
		$viewData['columnContentCount']	= $columnContentCount;			// カラム部(intro部)のコンテンツ数
		$viewData['columnCount']		= $columnCount;					// カラム部(intro部)のカラム数
		$viewData['categoryDesc']		= $categoryDesc;				// カテゴリーの説明
		$viewData['readMoreTitle']		= $readMoreTitle;				// 「もっと読む」ボタンタイトル
		if ($withDefaultOutput) $viewData['withDefaultOutput']	= true;				// ウィジェット出力をカテゴリー説明部に出力するかどうか
		$this->gEnv->setJoomlaViewData($viewData);
	}
	/**
	 * ページ番号遷移データを設定(フロント画面用)
	 *
	 * @param array $pageLinkInfo	リンクページ情報
	 * @param int $totalCount		項目総数
	 * @param int $itemOffset		最初の項目のオフセット
	 * @param int $viewCount		1ページあたりの表示項目数
	 * @return						なし
	 */
	function setJoomlaPaginationData($pageLinkInfo, $totalCount, $itemOffset, $viewCount)
	{
		// Joomla!新型テンプレートでない場合は終了
		if ($this->_renderType != M3_RENDER_JOOMLA_NEW) return;
		
		$paginationData = array();
		$paginationData['pagelinks'] = $pageLinkInfo;
		
		$paginationData['total']	= $totalCount;				// 項目総数
		$paginationData['offset']	= $itemOffset;				// 最初の項目のオフセット
		$paginationData['viewcount']	= $viewCount;			// 1ページあたりの表示項目数
		$this->gEnv->setJoomlaPaginationData($paginationData);
	}
	/**
	 * ページリンク計算
	 *
	 * @param int $pageNo			ページ番号(1～)。ページ番号が範囲外にある場合は自動的に調整
	 * @param int $totalCount		総項目数
	 * @param int $viewItemCount	1ページあたりの項目数
	 * @return bool					true=成功、false=失敗(ページ番号修正)
	 */
	function calcPageLink(&$pageNo, $totalCount, $viewItemCount)
	{
		$isSucceed = true;	// 正常終了かどうか
		
		if ($totalCount <= 0) $isSucceed = false;
		
		// 表示するページ番号の修正
		$this->_linkPageCount = (int)(($totalCount -1) / $viewItemCount) + 1;		// 総ページ数
		if ($pageNo < 1){
			$pageNo = 1;
			$isSucceed = false;
		} else if ($pageNo > $this->_linkPageCount){
			$pageNo = $this->_linkPageCount;
			$isSucceed = false;
		}
		return $isSucceed;
	}
	/**
	 * ページリンク作成(Artisteer4.1対応)
	 *
	 * @param int $pageNo			ページ番号(1～)。
	 * @param int $linkCount		最大リンク数
	 * @param string $baseUrl		リンク用のベースURL
	 * @param string $clickEvent	リンククリックイベント用スクリプト
	 * @param int $style			0=Artisteerスタイル、1=括弧スタイル、2=Bootstrap型、-1=管理画面
	 * @return string				リンクHTML
	 */
	function createPageLink($pageNo, $linkCount, $baseUrl, $clickEvent = '', $style = 0)
	{
		$isAdminDirAccess = $this->gEnv->isAdminDirAccess();
		if ($isAdminDirAccess){		// 管理画面の場合
			$pageLink = $this->gDesign->createPageLink($pageNo, $this->_linkPageCount, $linkCount, $this->getUrl($baseUrl, true/*リンク用*/), ''/*追加パラメータなし*/, -1/*管理画面用*/, $clickEvent);
		} else {
			$pageLink = $this->gDesign->createPageLink($pageNo, $this->_linkPageCount, $linkCount, $this->getUrl($baseUrl, true/*リンク用*/), ''/*追加パラメータなし*/, $style);
		}
		return $pageLink;
	}
	/**
	 * ページリンク情報取得
	 *
	 * @return array			ページリンク情報
	 */
	function getPageLinkInfo()
	{
		return $this->gDesign->getPageLinkInfo();
	}
	/**
	 * 現在のウィジェットがナビゲーションメニュー対応すべきかを取得
	 *
	 * @return bool			true=対応、false=対応なし
	 */
	function isNavigationMenuStyle()
	{
		$isNavigation = false;
		
		$style = $this->gEnv->getCurrentWidgetStyle();
		if ($style == '_navmenu') $isNavigation = true;		// ナビゲーションメニューの場合
		return $isNavigation;
	}
	/**
	 * CKEditor用のCSSファイルURLを画面に取り込む
	 *
	 * @param string $url	取得元画面のURL
	 * @return 				なし
	 */
	function loadCKEditorCssFiles($url = '')
	{
		// DBのテンプレート情報からCSSファイルの情報が取得できる場合は取得し、できない場合は画面から直接取得
		$ret = $this->gPage->loadCssFiles();
		if (!$ret){
			if (empty($url)) $url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;
			$this->gPage->loadCssFilesByUrl($url);
		}
	}
	
	/**
	 * テンプレート処理置き換え用画面作成メソッド(一覧画面作成)
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function assignTemplate_createList($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->_localParamObj);
		
		// ##### フック処理(ACT前処理) #####
		if (!empty($this->_assignTemplate_hookArray)){
			$methodName = $this->_assignTemplate_hookArray['preAct'];		// ACT前処理
			if (!empty($methodName)){
				$method = array($this, $methodName);
				if (is_callable($method)) call_user_func($method, $request);
			}
		}

		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
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
				$ret = $this->delPageDefParam($defSerial, $defConfigId, $this->_localParamObj, $delItems);
				if ($ret){		// データ削除成功のとき
					// ##### アクセスキー情報を削除 #####
					for ($i = 0; $i < count($delItems); $i++){
						$this->gAccess->unegistAllSessionAccessKey($delItems[$i]);		// 一旦すべて削除
					}

					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 定義一覧作成
		$this->assignTemplate_createItemList();
		
		if (!empty($this->_localSerialArray)) $this->tmpl->addVar("_widget", "serial_list", implode($this->_localSerialArray, ','));// 表示項目のシリアル番号を設定
		
		// ##### フック処理(ACT後処理) #####
		if (!empty($this->_assignTemplate_hookArray)){
			$methodName = $this->_assignTemplate_hookArray['postAct'];		// ACT後処理
			if (!empty($methodName)){
				$method = array($this, $methodName);
				if (is_callable($method)) call_user_func($method, $request);
			}
		}
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->_localParamObj);
	}
	/**
	 * テンプレート処理置き換え用画面作成メソッド(定義一覧作成)
	 *
	 * @return なし						
	 */
	function assignTemplate_createItemList()
	{
		for ($i = 0; $i < count($this->_localParamObj); $i++){
			$id			= $this->_localParamObj[$i]->id;// 定義ID
			$targetObj	= $this->_localParamObj[$i]->object;
			$name = $targetObj->name;// 定義名
		
			// 使用数
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->_db->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			
			$row = array(
				'index' => $i,
				'id' => $id,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->_localSerialArray[] = $id;
		}
		// 一覧部の表示制御
		$this->setListTemplateVisibility('itemlist');
	}
	/**
	 * 設定画面用のメニューバー(ナビゲーションバー+パンくずリスト)の定義を設定
	 *
	 * @param array $titleDef		パンくずリストのタイトル定義
	 * @param array $menuDef		メニューバーのメニュー定義
	 * @return								なし
	 */
	function setConfigMenubarDef($titleDef, $menuDef)
	{
		$this->configMenubarBreadcrumbTitleDef = $titleDef;			// 設定画面用パンくずリストのタイトル定義
		$this->configMenubarMenuDef = $menuDef;					// 設定画面用メニューバーのメニュー定義
	}
	/**
	 * [設定画面用メソッド]
	 *
	 * デフォルトの設定名を作成
	 *
	 * @return string	デフォルト名
	 */
	function createConfigDefaultName()
	{
		$name = self::DEFAULT_NAME_HEAD;
		for ($j = 1; $j < 100; $j++){
			$name = self::DEFAULT_NAME_HEAD . $j;
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->_configParamObj); $i++){
				$targetObj = $this->_configParamObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					break;
				}
			}
			// 重複なしのときは終了
			if ($i == count($this->_configParamObj)) break;
		}
		return $name;
	}
	/**
	 * [設定画面用メソッド]
	 *
	 * 設定選択用メニューを作成
	 *
	 * @param string $configId		選択中の設定ID
	 * @return なし
	 */
	function createConfigNameMenu($configId)
	{
		for ($i = 0; $i < count($this->_configParamObj); $i++){
			$id = $this->_configParamObj[$i]->id;// 定義ID
			$targetObj = $this->_configParamObj[$i]->object;
			$name = $targetObj->name;// 定義名
			$selected = '';
	//		if ($this->configId == $id) $selected = 'selected';
			if ($configId == $id) $selected = 'selected';

			$row = array(
				'name' => $name,		// 名前
				'value' => $id,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('_config_name_list', $row);
			$this->tmpl->parseTemplate('_config_name_list', 'a');
		}
	}
}
?>
