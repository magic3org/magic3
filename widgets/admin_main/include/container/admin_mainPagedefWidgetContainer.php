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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainPagedefWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;	// シリアルNo
	private $pageId;	// ページID
	private $pageSubId;	// ページサブID
	private $position;	// 表示ポジション
	private $defaultPageSubId;		// デフォルトのページID
	private $templateId;		// テンプレートID
	private $subTemplateId;		// サブテンプレートID
	private $pageTemplateId;	// 個別ページのテンプレートID
	private $pageSubTemplateId;	// 個別ページのサブテンプレートID
	private $pageTitle;	// 選択ページのタイトル
	private $templateTitle;	// テンプレートタイトル
	private $pageInfoRows;			// ページ情報
	private $isExistsDefItems;		// ページ定義項目が存在するかどうか
	private $subTemplateInfo;		// サブテンプレート情報(Themler設定ファイルからの読み込み用)
	const BREADCRUMB_TITLE				= '画面構成';
	const BREADCRUMB_TITLE_PC			= 'PC画面';		// 画面タイトル名(パンくずリスト)
	const BREADCRUMB_TITLE_MOBILE		= '携帯画面';		// 画面タイトル名(パンくずリスト)
	const BREADCRUMB_TITLE_SMARTPHONE	= 'スマートフォン画面';		// 画面タイトル名(パンくずリスト)
	const TEMPLATE_NORMAL_ICON_FILE = '/images/system/layout16.png';		// 通常テンプレートアイコン
	const TEMPLATE_PLAIN_ICON_FILE = '/images/system/layout_plain16.png';		// デザインなしテンプレートアイコン
	const TEMPLATE_NORMAL32_ICON_FILE = '/images/system/layout32.png';		// 通常テンプレートアイコン
	const TEMPLATE_PLAIN32_ICON_FILE = '/images/system/layout_plain32.png';		// デザインなしテンプレートアイコン
	const TEMPLATE_THUMBNAIL_FILENAME = 'template_thumbnail.png';		// テンプレートサムネール
	const TEMPLATE_THUMBNAIL_FILENAME_WP = 'screenshot.png';				// テンプレートサムネール(WordPressテンプレート)
	const PLAIN_TEMPLATE_ID = '_layout';		// デザインなしテンプレート
	const TITLE_PRE_ICON_HOME = '<i class="glyphicon glyphicon-home" rel="m3help" title="トップページ"></i> ';		// タイトル付加用アイコン(ホーム)
	const TITLE_PRE_ICON_LOCK = '<i class="glyphicon glyphicon-lock" rel="m3help" title="SSL"></i> ';		// タイトル付加用アイコン(鍵)
	const TITLE_PRE_ICON_MINUS = '<i class="glyphicon glyphicon-minus-sign" rel="m3help" title="非表示"></i> ';		// タイトル付加用アイコン(マイナス記号)
	const BUTTON_ICON_TEMPLATE_CHECK = '<i class="glyphicon glyphicon-check"></i> ';		// テンプレート一覧付加用アイコン(チェックあり)
	const BUTTON_ICON_TEMPLATE_UNCHECKED = '<i class="glyphicon glyphicon-unchecked"></i> ';	// テンプレート一覧付加用アイコン(チェックなし)
	const BUTTON_ICON_TEMPLATE_CHECK_WITH_HELP = '<i class="glyphicon glyphicon-check" rel="m3help" title="ページに固定"></i> ';		// テンプレート一覧付加用アイコン(チェックあり)
	const HELP_SELECT_BUTTON = 'rel="m3help" title="ページに固定"';			// テンプレート一覧付加用ボタンのヘルプ(ページ専用テンプレート)
	const TEMPLATE_TYPE_LABEL_BOOTSTRAP = ' <span class="label label-info" rel="m3help" title="Bootstrap型">B</span>';			// Boostrap型テンプレートラベル
	const TEMPLATE_TYPE_LABEL_THEMLER = ' <span class="label label-info" rel="m3help" title="Themler製">T</span>';			// Themler製テンプレートラベル
	const TEMPLATE_TYPE_LABEL_WORDPRESS = ' <span class="label label-info" rel="m3help" title="WordPress型">W</span>';			// WordPress型テンプレートラベル
	
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
		$task = $request->trimValueOf('task');
		
		if ($task == 'pagedef_detail'){		// 詳細設定画面
			return 'pagedef_detail.tmpl.html';
		} else {			// 画面編集画面
			return 'pagedef.tmpl.html';
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
		return 'pagedef';
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
		$task = $request->trimValueOf('task');		// 処理区分
		
		// パンくずリストの作成
		$titles = array(self::BREADCRUMB_TITLE);
		
		switch ($task){
			case 'pagedef_mobile':		// 携帯用設定画面のとき
				$titles[] = self::BREADCRUMB_TITLE_MOBILE;
				break;
			case 'pagedef_smartphone':		// スマートフォン用設定画面のとき
				$titles[] = self::BREADCRUMB_TITLE_SMARTPHONE;
				break;
			default:						// PC用画面のとき
				$titles[] = self::BREADCRUMB_TITLE_PC;
				break;
		}
		$this->gPage->setAdminBreadcrumbDef($titles);
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
	function _assign($request, &$param)
	{
		// テキストをローカライズ
		$localeText = array();
		$localeText['label_page_id'] = $this->_('Page Id');		// ページID
		$localeText['label_page_sub_id'] = $this->_('Page Id');		// ページサブID
		$localeText['label_show_detail'] = $this->_('Show Detail');		// 詳細
		$localeText['label_default_template'] = $this->_('Default Template');		// デフォルトテンプレート
		$localeText['label_change_template'] = $this->_('Change Template');		// 変更
		$localeText['label_page_layout'] = $this->_('Page Layout');		// 画面デザイン
		$localeText['label_layout'] = $this->_('Layout');		// レイアウト
		$localeText['label_preview'] = $this->_('Preview');		// プレビュー
		$localeText['label_maximize'] = $this->_('Maximize');		// 最大化
		$localeText['label_preview_in_other_window'] = $this->_('Preview in other window');		// 別画面でプレビュー
		$localeText['label_site_preview'] = $this->_('Site Preview');// 実際の画面
		$localeText['label_page'] = $this->_('Page');		// ページ
		$localeText['label_template'] = $this->_('Template');		// テンプレート
		$localeText['label_default_value'] = $this->_('Default Value');		// デフォルト値
		
		// 詳細画面
		$localeText['msg_update_line'] = $this->_('Update line data?');		// データを更新しますか?
		$localeText['msg_delete_line'] = $this->_('Delete line data?');		// データを削除しますか?
		$localeText['msg_delete_all_line'] = $this->_('Delete all line data?');// すべての項目を削除しますか?
		$localeText['label_all'] = $this->_('All');	// すべて
		$localeText['label_page_def_detail'] = $this->_('Page Definition Detail');// ページ定義詳細
		$localeText['label_go_back'] = $this->_('Go back');// 戻る
		$localeText['label_position'] = $this->_('Position');// ポジション名
		$localeText['label_order'] = $this->_('Order');// 表示順
		$localeText['label_widget_id'] = $this->_('Widget ID');// ウィジェットID
		$localeText['label_widget_name'] = $this->_('Widget Name');// ウィジェット名
		$localeText['label_config_id'] = $this->_('Config ID');// 定義ID
		$localeText['label_visible'] = $this->_('Visible');// 表示
		$localeText['label_shared'] = $this->_('Global');// グローバル属性
		$localeText['label_operation'] = $this->_('Operation');// 操作
		$localeText['label_shared_item'] = $this->_('Include global widgets.');// グローバル属性ウィジェット含む
		$localeText['label_delete_all'] = $this->_('Delete all');// すべて削除
		$this->setLocaleText($localeText);
		
		$task = $request->trimValueOf('task');
		if ($task == 'pagedef_detail'){	// 詳細設定画面
			$this->createDetail($request);
		} else {			// 画面作成画面
			$this->createView($request);
		}
	}
	/**
	 * 画面定義画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function createView($request)
	{
		// パラメータの取得
		$task = $request->trimValueOf('task');		// 処理区分
		$act = $request->trimValueOf('act');
		$this->pageId = $request->trimValueOf('pageid');		// ページID
		$this->pageSubId = $request->trimValueOf('pagesubid');// ページサブID
		$layoutMode = $request->trimValueOf('layoutmode');// テンプレートモード(空=デザインテンプレート,plain=デザインなしテンプレート)
		if (empty($this->pageId)){
			switch ($task){
				case 'pagedef_mobile':		// 携帯用設定画面のとき
					$this->pageId = $this->gEnv->getDefaultMobilePageId();				// 携帯用デフォルト値取得
					break;
				case 'pagedef_smartphone':		// スマートフォン用設定画面のとき
					$this->pageId = $this->gEnv->getDefaultSmartphonePageId();				// スマートフォン用デフォルト値取得
					break;
				default:						// PC用画面のとき
					$this->pageId = $this->gEnv->getDefaultPageId();				// デフォルト値取得
					break;
			}
		}
		if (empty($this->pageSubId)) $this->pageSubId = $this->gEnv->getDefaultPageSubIdByPageId($this->pageId);// デフォルト値取得
		
		// デフォルトテンプレート取得
		switch ($task){
			case 'pagedef_mobile':	// 携帯用設定画面のとき
				$this->templateId = $this->gSystem->defaultMobileTemplateId();
				$deviceType = 1;		// デバイスタイプ(携帯)
				$taskStr = 'pagedef_mobile';
				$previewWidth = '600px';
				$this->tmpl->addVar("_widget", "preview_option_class", 'class="layout_top_border layout_side_border"');		// プレビューエリアにトップとサイドのボーダーラインを付加
				$this->tmpl->addVar("_widget", "template_normal_disabled", 'disabled');
				break;
			case 'pagedef_smartphone':		// スマートフォン用設定画面
				$this->templateId = $this->gSystem->defaultSmartphoneTemplateId();
				$deviceType = 2;		// デバイスタイプ(スマートフォン)
				$taskStr = 'pagedef_smartphone';
				$previewWidth = '600px';
				$this->tmpl->addVar("_widget", "preview_option_class", 'class="layout_top_border layout_side_border"');		// プレビューエリアにトップとサイドのボーダーラインを付加
				$this->tmpl->addVar("_widget", "template_normal_disabled", 'disabled');
				break;
			default:
				$this->templateId = $this->gSystem->defaultTemplateId();
				$this->subTemplateId = $this->gSystem->defaultSubTemplateId();			// サブテンプレートID
				$deviceType = 0;		// デバイスタイプ(PC)
				$taskStr = 'pagedef';
				$previewWidth = '100%';
				$this->tmpl->addVar("_widget", "preview_option_class", 'class="layout_top_border"');		// プレビューエリアにトップのボーダーラインを付加
				break;
		}
		
		if ($act == 'changetemplate'){		// テンプレート変更のとき
			$templateId = $request->trimValueOf('sel_template');		// テンプレートID
			if (!empty($templateId)){
				switch ($task){
					case 'pagedef_mobile':		// 携帯用設定画面のとき
						$this->gSystem->changeDefaultMobileTemplate($templateId);
						break;
					case 'pagedef_smartphone':		// スマートフォン用設定画面のとき
						$this->gSystem->changeDefaultSmartphoneTemplate($templateId);
						break;
					default:						// PC用画面のとき
						// Themlerテンプレートの場合はサブテンプレートIDを取得
						$subTemplateId = $this->getDefaultSubTemplateId($templateId);
						
						// 現在のテンプレート、サブテンプレートを変更
						$this->gSystem->changeDefaultTemplate($templateId, $subTemplateId);
			
						// セッションのテンプレートIDを更新
						$request->setSessionValue(M3_SESSION_CURRENT_TEMPLATE, $templateId);
						break;
				}
				// キャッシュデータをクリア
				$this->gCache->clearAllCache();
				
				// デフォルトテンプレート変更
				$this->templateId = $templateId;
				$this->subTemplateId = $subTemplateId;
				
//				// デフォルトテンプレートを使用しているページのサブテンプレートIDは初期化
//				// ページIDですべてのページ情報を取得
//				$ret = $this->db->getPageInfoByPageId($this->pageId, ''/*言語*/, $pageInfoRows);
//				if ($ret){
//					for ($i = 0; $i < count($pageInfoRows); $i++){
//						$pageInfo = $pageInfoRows[$i];
//						$pageSubId = $pageInfo['pg_id'];
//						$templateId = $pageInfo['pn_template_id'];
//						if (!is_null($templateId)/*データ情報あり*/ && empty($templateId) && !empty($pageInfo['pn_sub_template_id'])){		// デフォルトテンプレートを使用、サブテンプレート設定ありの場合
//							$ret = $this->db->getPageInfo($this->pageId, $pageSubId, $row);
//							if ($ret) $this->db->updatePageInfo($this->pageId, $pageSubId, $row['pn_content_type'], $row['pn_template_id'], ''/*サブテンプレートID*/, $row['pn_auth_type'], $row['pn_use_ssl'], $row['pn_user_limited']);
//						}
//					}
//				}
			}
		} else if ($act == 'changepagetemplate'){		// 個別ページ用テンプレート選択
			$templateId = $request->trimValueOf('sel_page_template');		// テンプレートID
			
			// ページ用テンプレートの更新
			// デフォルトと同じ場合はトグル切り替え
/*			if ($templateId == $this->templateId){
				$ret = $this->db->getPageInfo($this->pageId, $this->pageSubId, $row);
				if ($ret && !is_null($row['pn_template_id'])){		// ページ情報レコードがある場合
					if (empty($row['pn_template_id'])){
						// 個別のテンプレート設定の場合はデフォルトのサブテンプレートIDを取得
						$subTemplateId = $this->getDefaultSubTemplateId($templateId);
					} else {			// トグルオフの場合はテンプレートID、サブテンプレートIDを初期化
						$templateId = '';
						$subTemplateId = '';
					}
				} else {
					// 個別のテンプレート設定の場合はデフォルトのサブテンプレートIDを取得
					$subTemplateId = $this->getDefaultSubTemplateId($templateId);
				}
			} else {
				// 個別のテンプレート設定の場合はデフォルトのサブテンプレートIDを取得
				$subTemplateId = $this->getDefaultSubTemplateId($templateId);
			}*/
			if (!empty($templateId)){
				// 個別のテンプレート設定の場合はデフォルトのサブテンプレートIDを取得
				$subTemplateId = $this->getDefaultSubTemplateId($templateId);
				
				// 表示中のページについてページ情報更新
				$ret = $this->db->getPageInfo($this->pageId, $this->pageSubId, $row);
				if ($ret){
					if (is_null($row['pn_template_id'])){		// ページ情報レコードがない場合
						$ret = $this->db->updatePageInfo($this->pageId, $this->pageSubId,''/*コンテンツタイプ*/, $templateId, $subTemplateId);
					} else {
						// 既存の設定値と同じ場合はリセット(トグルオフ)
						if ($templateId == $row['pn_template_id']){
							$templateId = '';
							$subTemplateId = '';
						}
					
						$ret = $this->db->updatePageInfo($this->pageId, $this->pageSubId, $row['pn_content_type'], $templateId, $subTemplateId, $row['pn_auth_type'], $row['pn_use_ssl'], $row['pn_user_limited']);
					}
				}
			}
		} else if ($act == 'changesubtemplate'){		// サブテンプレート選択
			$subTemplateId = $request->trimValueOf('subtemplateid');		// サブテンプレートID
						
//			$ret = $this->db->getPageInfo($this->pageId, $this->pageSubId, $row);
//			if ($ret){
//				if (is_null($row['pn_content_type'])){		// ページ情報レコードがない場合
//					$ret = $this->db->updatePageInfo($this->pageId, $this->pageSubId,''/*コンテンツタイプ*/, $row['pn_template_id'], $subTemplateId);
//				} else {
//					$ret = $this->db->updatePageInfo($this->pageId, $this->pageSubId, $row['pn_content_type'], $row['pn_template_id'], $subTemplateId, $row['pn_auth_type'], $row['pn_use_ssl'], $row['pn_user_limited']);
//				}
//			}
			$ret = $this->db->getPageInfo($this->pageId, $this->pageSubId, $row);
			if ($ret && !is_null($row['pn_template_id'])){		// ページ情報レコードがある場合
				$templateId = $row['pn_template_id'];
				if (empty($templateId)){			// 個別にテンプレートが設定されていない場合
					// デフォルトのサブテンプレートを変更
					$this->subTemplateId = $subTemplateId;
					$this->gSystem->changeDefaultTemplate($this->templateId, $this->subTemplateId);
				} else {
					// 個別のページのサブテンプレートを変更
					$ret = $this->db->updatePageInfo($this->pageId, $this->pageSubId, $row['pn_content_type'], $row['pn_template_id'], $subTemplateId, $row['pn_auth_type'], $row['pn_use_ssl'], $row['pn_user_limited']);
				}
			}
		}
		// ページIDでページ情報を取得
		$ret = $this->db->getPageInfoByPageId($this->pageId, ''/*言語*/, $this->pageInfoRows);
		
		// ページメインIDメニュー作成
		$this->db->getPageIdList(array($this, 'pageIdLoop'), 0/*ページID*/, $deviceType);

		// ページサブIDメニュー作成(ページメインIDを先に作成してから)。デバイスで共通。
		$this->db->getPageIdList(array($this, 'pageSubIdLoop'), 1/*サブページID*/, -1/*デバイス関係なし*/, true/*メニューから選択可項目のみ*/);
		
		// ページ情報取得
/*		$contentTypeStr = '';		// コンテンツ種別
		$ret = $this->db->getPageInfo($this->pageId, $this->pageSubId, $row);
		if ($ret){
			if (!empty($row['pn_content_type'])) $contentTypeStr = $this->_('Page Attribute:') . $row['pn_content_type'];		// 「ページ属性：」
		}*/
		
		// テンプレート選択メニュー作成
		$this->db->getAllTemplateList($deviceType, array($this, 'templateIdLoop'));
		
		// サブテンプレート選択メニュー作成
		$this->createSubTemplateMenu();
		
		// タイトル
		$this->tmpl->addVar("_widget", "page_title", $this->pageTitle);			// ページタイトル(エスケープ済み)
		$this->tmpl->addVar("_widget", "template_title", $this->templateTitle);	// テンプレートタイトル(エスケープ済み)
		
		// URLを設定
		$path = '';
		$pathArray = explode('_', $this->pageId);
		for ($i = 0; $i < count($pathArray); $i++){
			$path .= '/' . $pathArray[$i];
		}
		$url = $this->gEnv->getRootUrlByPage($this->pageId, $this->pageSubId) . $path . '.php';
		$dispUrl = $url;
		if ($this->pageSubId == $this->defaultPageSubId){
			$urlWithSession = $url . '?' . $this->gAccess->getSessionIdUrlParam();		// セッションIDをURLに追加
		} else {
			$url .= '?sub=' . $this->pageSubId;
			$dispUrl .= '?<strong>sub=' . $this->pageSubId . '</strong>';
			$urlWithSession = $url . '&' . $this->gAccess->getSessionIdUrlParam();		// セッションIDをURLに追加
		}
		// デフォルトテンプレート、サブテンプレート
		$defaultTemplate = $this->templateId;
		if (!empty($this->subTemplateId)) $defaultTemplate .= '(' . $this->subTemplateId . ')';		// サブテンプレートがある場合は付加
		
		$this->tmpl->addVar("_widget", "url", $url);		// getUrl()は掛けない
		$this->tmpl->addVar("_widget", "disp_url", $dispUrl);		// 表示用URL
		$this->tmpl->addVar("_widget", "url_with_session", $urlWithSession);		// セッションID付きURL(携帯のみ使用)。getUrl()は掛けない
//		$this->tmpl->addVar("_widget", "content_type", $contentTypeStr);		// コンテンツ種別
		$this->tmpl->addVar("_widget", "device_type", $deviceType);			// デバイスタイプ
		$this->tmpl->addVar("_widget", "preview_width", $previewWidth);			// プレビュー幅
		$this->tmpl->addVar("_widget", "task", $taskStr);			// タスク
		$this->tmpl->addVar("_widget", "default_template_id", $this->convertToDispString($defaultTemplate));	// デフォルトのテンプレートID
		
		// 管理用URL設定
		$adminUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_DEF_PAGE_ID . '=' . $this->pageId . '&' . M3_REQUEST_PARAM_DEF_PAGE_SUB_ID . '=' . $this->pageSubId;
		$this->tmpl->addVar("_widget", "admin_url", $this->getUrl($adminUrl));
		
		// アイコンを設定
		$iconUrl = $this->gEnv->getRootUrl() . self::TEMPLATE_NORMAL_ICON_FILE;
		$this->tmpl->addVar("_widget", "template_normal", $this->getUrl($iconUrl));
		$iconUrl = $this->gEnv->getRootUrl() . self::TEMPLATE_PLAIN_ICON_FILE;
		$this->tmpl->addVar("_widget", "template_plain", $this->getUrl($iconUrl));
		$iconUrl = $this->gEnv->getRootUrl() . self::TEMPLATE_NORMAL32_ICON_FILE;
		$this->tmpl->addVar("_widget", "template_normal32", $this->getUrl($iconUrl));
		$iconUrl = $this->gEnv->getRootUrl() . self::TEMPLATE_PLAIN32_ICON_FILE;
		$this->tmpl->addVar("_widget", "template_plain32", $this->getUrl($iconUrl));
		
		// テンプレートモード(空=デザインテンプレート,plain=デザインなしテンプレート)を再設定
		$this->tmpl->addVar("_widget", "layout_mode", $layoutMode);
		if (empty($layoutMode)){
			$this->tmpl->addVar("_widget", "template_normal_style", 'display:inline;');
			$this->tmpl->addVar("_widget", "template_plain_style", 'display:none;');
			$previewTemplateParam = '';
		} else {
			$this->tmpl->addVar("_widget", "template_normal_style", 'display:none;');
			$this->tmpl->addVar("_widget", "template_plain_style", 'display:inline;');
			$previewTemplateParam = '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . self::PLAIN_TEMPLATE_ID;// プレビュー用のテンプレートID
		}
		$this->tmpl->addVar("_widget", "plain_template_id", self::PLAIN_TEMPLATE_ID);		// デザインなしテンプレートID
		$this->tmpl->addVar("_widget", "preview_template_param", $previewTemplateParam);		// プレビュー用のテンプレートID
		
		if ($this->db->canDetailConfig()){		// 詳細設定可のときは、ページID選択を可にする
			$this->tmpl->setAttribute('show_access_point', 'visibility', 'visible');// アクセスポイント選択メニュー
			$this->tmpl->addVar('_widget', 'page_id_col', 'colspan="4"');		// カラム数を調整
		} else {
			$this->tmpl->addVar('_widget', 'page_id_col', 'colspan="2"');		// カラム数を調整
		}
	}
	/**
	 * 詳細設定画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->pageId = $request->trimValueOf('pageid');		// ページID
		$this->pageSubId = $request->trimValueOf('pagesubid');// ページサブID
		if (empty($this->pageId)) $this->pageId = $this->gEnv->getDefaultPageId();				// デフォルト値取得
		if (empty($this->pageSubId)) $this->pageSubId = $this->gEnv->getDefaultPageSubIdByPageId($this->pageId);// デフォルト値取得
		$this->position = $request->trimValueOf('position');	// 表示ポジション
		
		if ($act == 'updateline'){		// 更新のとき
			// 変更可能値
			$selectedItemNo = $request->trimValueOf('no');		// 処理対象の項目番号
			$updateIndex = $request->trimValueOf('item' . $selectedItemNo . '_index');			// 表示インデックス
			$instanceDefId = $request->trimValueOf('item' . $selectedItemNo . '_def_id');		// 定義ID
			if ($instanceDefId == '') $instanceDefId = 0;
			$updateVisible = ($request->trimValueOf('item' . $selectedItemNo . '_visible') == 'on') ? 1 : 0;		// 表示状態

			// 変更前値を取得
			if ($this->db->getPageDef($this->serialNo, $row)){
				//$updatePageSubId = $row['pd_sub_id'];
				$updatePos = $row['pd_position_id'];
				$updateWidgetId = $row['pd_widget_id'];
				
				// 「グローバル」項目に合わせて、ページサブIDの修正
				$updatePageSubId = '';// グローバル属性使用
				if ($request->trimValueOf('item' . $selectedItemNo . '_shared') != 'on') $updatePageSubId = $this->pageSubId;
			
				// 入力チェック
				$this->checkNumeric($updateIndex, $this->_('Order'));		// 表示順
			
				// エラーなしの場合は、データを登録
				if ($this->getMsgCount() == 0){
					$ret = $this->db->updatePageDef($this->serialNo, $this->pageId, $updatePageSubId, $updatePos, $updateIndex, $updateWidgetId, $instanceDefId,
														'', '', $updateVisible);
					if ($ret){		// データ更新成功のとき
						$this->setMsg(self::MSG_GUIDANCE, $this->_('Data updated.'));		// データを更新しました
					} else {
						$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating data.'));			// データ更新に失敗しました
					}
				}
			} else {
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in updating data.'));			// データ更新に失敗しました
			}
			// キャッシュデータをクリア
			$this->gCache->clearAllCache();
		} else if ($act == 'deleteline'){		// 削除のとき
			$ret = $this->db->delPageDef($this->serialNo);
			if ($ret){		// データ削除成功のとき
				$this->setMsg(self::MSG_GUIDANCE, $this->_('Data deleted.'));			// データを削除しました
			} else {
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in deleting data.'));			// データ削除に失敗しました
			}
			// キャッシュデータをクリア
			$this->gCache->clearAllCache();
		} else if ($act == 'deleteall'){		// すべて削除のとき
			$withCommon = ($request->trimValueOf('with_common') == 'on') ? 1 : 0;		// グローバル属性項目も削除するかどうか
			$ret = $this->db->delPageDefAll($this->pageId, $this->pageSubId, $this->position, $withCommon);
			if ($ret){		// データ削除成功のとき
				$this->setMsg(self::MSG_GUIDANCE, $this->_('Data deleted.'));			// データを削除しました
			} else {
				$this->setMsg(self::MSG_APP_ERR, $this->_('Failed in deleting data.'));			// データ削除に失敗しました
			}
			// キャッシュデータをクリア
			$this->gCache->clearAllCache();
		}		

		// 定義詳細一覧表示
		$this->db->getPageDefList(array($this, 'pageListLoop'), $this->pageId, $this->pageSubId, $this->position);
		if (!$this->isExistsDefItems) $this->tmpl->setAttribute('page_def_list', 'visibility', 'hidden');
			
		// ポジションメニュー作成
		$this->db->getPagePositionList(array($this, 'pagePositionLoop'));
		
		// 値を再設定
		$this->tmpl->addVar('_widget', 'pageid', $this->pageId);		// ページID
		$this->tmpl->addVar('_widget', 'pagesubid', $this->pageSubId);		// ページサブID
		
		// 実際に表示する画面のURLを設定
		$path = '';
		$pathArray = explode('_', $this->pageId);
		for ($i = 0; $i < count($pathArray); $i++){
			$path .= '/' . $pathArray[$i];
		}
		//$url = $this->gEnv->getRootUrl() . $path . '.php';
		$url = $this->gEnv->getRootUrlByPage($this->pageId, $this->pageSubId) . $path . '.php';
		if ($this->pageSubId != $this->defaultPageSubId){
			$url .= '?sub=' . $this->pageSubId;
		}
		$this->tmpl->addVar("_widget", "url", $url);// getUrl()は掛けない
	}
	/**
	 * ページ定義、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageListLoop($index, $fetchedRow, $param)
	{
		// サブIDが空のときは、グローバル属性ウィジェットとする
		$isSharedItem = '';
		if (empty($fetchedRow['pd_sub_id'])){
			$isSharedItem = 'checked';
		}
		// 編集不可項目のときは、ボタンを使用不可にする
		$buttonEnabled = '';
		if (!$fetchedRow['pd_editable']) $buttonEnabled = 'disabled';
		
		// 設定画面がない場合はボタンを使用不可にする
		$configButtonEnabled = '';
		if (!$fetchedRow['wd_has_admin']) $configButtonEnabled = 'disabled';

		// 項目を画面に表示するかどうか
		$itemVisible = '';
		if ($fetchedRow['pd_visible']) $itemVisible = 'checked';
		
		// 定義ID
		$defId = $fetchedRow['pd_config_id'];
		
		$sharedColorClass = '';			// ウィジェットの共有状態
		if (empty($fetchedRow['pd_sub_id'])){
			$sharedColorClass = 'class="danger"';
		} else {
			$sharedColorClass = 'class="success"';
		}
		
		$row = array(
			'no'			=> $index + 1,											// 行番号
			'serial' 		=> $this->convertToDispString($fetchedRow['pd_serial']),			// シリアルNo
//			'id' 			=> $this->convertToDispString($fetchedRow['wd_id']),			// ウィジェットID
			'name'			=> $this->convertToDispString($fetchedRow['wd_name']),		// 名前
			'position'		=> $this->convertToDispString($fetchedRow['pd_position_id']),	// 表示ポジション
			'shared_color'	=> $sharedColorClass,			// ウィジェットの共有状態
			'index'			=> $this->convertToDispString($fetchedRow['pd_index']),				// 表示順
			'widget_id'		=> $this->convertToDispString($fetchedRow['pd_widget_id']),				// ウィジェットID
			'widget_name'		=> $this->convertToDispString($fetchedRow['wd_name']),				// ウィジェット名
			'def_id'		=> $this->convertToDispString($defId),			// 定義ID
			'suffix'		=> $this->convertToDispString($fetchedRow['pd_suffix']),			// サフィックス
			'shared'		=> $isSharedItem,												// グローバル属性ウィジェットかどうか
			'visible'		=> $itemVisible,												// 画面に表示するかどうか
			'update_line'	=> $this->convertToDispString($this->_('Update line')),							// 行を更新
			'delete_line'	=> $this->convertToDispString($this->_('Delete line')),							// 行を削除
			'update_button' => $buttonEnabled,												// 行更新ボタン
			'delete_button'	=> $buttonEnabled,												// 行削除ボタン
			'config_button_disabled'	=> $configButtonEnabled,												// 設定画面表示ボタン
			'label_config_window' => $this->_('Show config window'),			// 設定画面を表示
		);
		$this->tmpl->addVars('page_def_list', $row);
		$this->tmpl->parseTemplate('page_def_list', 'a');
		
		$this->isExistsDefItems = true;		// ページ定義項目が存在するかどうか
		return true;
	}
	/**
	 * ページID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['pg_id'] == $this->pageId){
			$selected = 'selected';
			
			// デフォルトのページサブIDを取得
			$this->defaultPageSubId = $fetchedRow['pg_default_sub_id'];		// デフォルトのページID
		}
		$name = $this->convertToDispString($fetchedRow['pg_id']) . ' - ' . $this->convertToDispString($fetchedRow['pg_name']);			// ページ名
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_id']),			// ページID
			'name'     => $name,			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('access_point_list', $row);
		$this->tmpl->parseTemplate('access_point_list', 'a');
		return true;
	}
	/**
	 * ページサブID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageSubIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		$checked = '';
		$value = $fetchedRow['pg_id'];

		// ページ情報
		$contentType = '';
		$templateId = '';
		$subTemplateId = '';
		$useSsl = false;		// SSL使用状況
		$pageInfoCount = count($this->pageInfoRows);
		for ($i = 0; $i < $pageInfoCount; $i++){
			$pageInfo = $this->pageInfoRows[$i];
			if ($pageInfo['pg_id'] == $value){
				$contentType = strval($pageInfo['pn_content_type']);			// NULL値あり
				$templateId = $pageInfo['pn_template_id'];
				$subTemplateId = $pageInfo['pn_sub_template_id'];
				$useSsl = $pageInfo['pn_use_ssl'];
				if ($value == $this->pageSubId){		// 表示中のページの場合
					$this->pageTemplateId = $templateId;	// 個別ページのテンプレートID
					$this->pageSubTemplateId = $subTemplateId;		// サブテンプレートID
				}
				break;
			}
		}

		// 表示ラベルを作成
		$name = $this->convertToDispString($fetchedRow['pg_name']);
		$nameWithAttr = $name . '(' . $this->convertToDispString($value) . ')';

		// ページタイトル
		$pageTitle = '';
		$preTitle = '';
		if ($value == $this->defaultPageSubId) $preTitle .= self::TITLE_PRE_ICON_HOME;		// デフォルトページ(homeアイコン)
		if ($useSsl) $preTitle .= self::TITLE_PRE_ICON_LOCK;		// SSL使用ページ(鍵アイコン)
		if (!$fetchedRow['pg_active']) $preTitle .= self::TITLE_PRE_ICON_MINUS;			// 非表示ページ(非表示アイコン)
		$pageTitle = $preTitle . $name;	// 選択ページのタイトル
			
		// 現在選択中の項目タイトル
		if ($value == $this->pageSubId){
			$selected = 'selected';
			$checked = 'checked';
			
			$this->pageTitle = $preTitle . $nameWithAttr;	// 選択ページのタイトル
//			if ($value == $this->defaultPageSubId) $this->pageTitle .= ' [' . $this->_('Default') . ']';			// デフォルトのページサブIDのときは、説明を付加
//			if (!$fetchedRow['pg_active']) $this->pageTitle .= ' [' . $this->_('Unpublished') . ']';			// 非公開
		}
		
		// 表示ラベル
		if ($value == $this->defaultPageSubId) $nameWithAttr .= ' [' . $this->_('Default') . ']';			// デフォルトのページサブIDのときは、説明を付加
		if (!$fetchedRow['pg_active']) $nameWithAttr .= ' [' . $this->_('Unpublished') . ']';			// 非公開
		
		// テンプレートIDの表示
		$dispTemplateId = $templateId;
		if (!empty($subTemplateId)) $dispTemplateId .= '(' . $subTemplateId . ')';
		
		$row = array(
			'value'    => $this->convertToDispString($value),			// ページID
			'name'     => $nameWithAttr,			// ページ名
			'selected' => $selected,														// 選択中かどうか
			
			'col_title'	=> $pageTitle,		// ページ名
			'col_id'	=> $this->convertToDispString($value),			// ページID
			'col_content_type'	=> $this->convertToDispString($contentType),		// コンテンツタイプ
//			'col_template_id'	=> $this->convertToDispString($templateId),			// テンプレートID
			'col_template_id'	=> $this->convertToDispString($dispTemplateId),			// テンプレートID
			'col_checked'		=> $checked				// 選択状態
		);
		$this->tmpl->addVars('sub_id_list', $row);
		$this->tmpl->parseTemplate('sub_id_list', 'a');
		return true;
	}
	/**
	 * テンプレート一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function templateIdLoop($index, $fetchedRow, $param)
	{
		$value = $fetchedRow['tm_id'];
		$name = $fetchedRow['tm_name'];
		$type = $fetchedRow['tm_type'];		// テンプレートタイプ
		$generator = $fetchedRow['tm_generator'];		// テンプレート作成アプリケーション
		$selected = '';
		$checked = '';
		
		if ($value == $this->templateId){			// デフォルトのテンプレート
			$selected = 'selected';
			$checked = 'checked';
		
			if (empty($this->templateTitle)){
				$this->templateTitle = $this->convertToDispString($name);	// 選択テンプレートのタイトル
				
				// テンプレートタイプのアイコンを付加
				if (10 <= $type && $type < 20){			// Bootstrap型
					$this->templateTitle .= self::TEMPLATE_TYPE_LABEL_BOOTSTRAP;
				} else if ($type == 100){				// WordPress型
					$this->templateTitle .= self::TEMPLATE_TYPE_LABEL_WORDPRESS;
				} else if ($generator == M3_TEMPLATE_GENERATOR_THEMLER){		// Themler製テンプレートの場合
					$this->templateTitle .= self::TEMPLATE_TYPE_LABEL_THEMLER;
				}
			}
		}
		// テンプレート画像
		if ($type == 100){		// WordPressテンプレートの場合
			$imageUrl = $this->gEnv->getTemplatesUrl() . '/' . $value . '/' . self::TEMPLATE_THUMBNAIL_FILENAME_WP;
		} else {
			$imageUrl = $this->gEnv->getTemplatesUrl() . '/' . $value . '/' . self::TEMPLATE_THUMBNAIL_FILENAME;
		}
		$imagetTag = '<img src="' . $imageUrl . '" name="templatepreview" border="1" width="70" height="45" />';
		
		// 個別選択ボタン
		if ($value == $this->pageTemplateId){			// 個別ページのテンプレートID
			$selectButtonIcon = self::BUTTON_ICON_TEMPLATE_CHECK;
			$selectButtonHelp = self::HELP_SELECT_BUTTON;
			
			// テンプレートのタイトルを個別ページのテンプレートに変更
			$this->templateTitle = self::BUTTON_ICON_TEMPLATE_CHECK_WITH_HELP . $this->convertToDispString($this->pageTemplateId);	// 選択テンプレートのタイトル
			
			// テンプレートタイプのアイコンを付加
			if (10 <= $type && $type < 20){			// Bootstrap型
				$this->templateTitle .= self::TEMPLATE_TYPE_LABEL_BOOTSTRAP;
			} else if ($type == 100){				// WordPress型
				$this->templateTitle .= self::TEMPLATE_TYPE_LABEL_WORDPRESS;
			} else if ($generator == M3_TEMPLATE_GENERATOR_THEMLER){		// Themler製テンプレートの場合
				$this->templateTitle .= self::TEMPLATE_TYPE_LABEL_THEMLER;
			}
		} else {
			$selectButtonIcon = self::BUTTON_ICON_TEMPLATE_UNCHECKED;
			$selectButtonHelp = self::HELP_SELECT_BUTTON;
		}
		$selectTag = '<span ' . $selectButtonHelp . '><a class="btn btn-sm btn-default" onclick="changePageTemplate(\'' . $value . '\');return false;">' . $selectButtonIcon . '</a></span>';
		
		$row = array(
			'value'    => $this->convertToDispString($value),			// テンプレートID
			'name'     => $this->convertToDispString($name),			// テンプレート名名
			'selected' => $selected,													// 選択中かどうか
			
			'col_id'		=> $this->convertToDispString($value),			// テンプレートID
			'col_image'		=> $imagetTag,									// テンプレート画像
			'col_checked'	=> $checked,				// 選択状態
			'col_select'	=> $selectTag				// 個別選択
		);
		$this->tmpl->addVars('sel_template_list', $row);
		$this->tmpl->parseTemplate('sel_template_list', 'a');
		return true;
	}
	/**
	 * ページポジション、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pagePositionLoop($index, $fetchedRow, $param)
	{
		// フィルタリング用
		$selected = '';
		if ($fetchedRow['tp_id'] == $this->position){
			$selected = 'selected';
		}
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['tp_id']),			// ページID
			'name'     => $this->convertToDispString($fetchedRow['tp_name']),			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('position_list', $row);
		$this->tmpl->parseTemplate('position_list', 'a');
		return true;
	}
	/**
	 * サブテンプレート選択メニュー作成
	 *
	 * @return								なし
	 */
	function createSubTemplateMenu()
	{
		// 選択中のテンプレート情報取得
		$selectedTemplateId = $this->templateId;
		if (!empty($this->pageTemplateId)) $selectedTemplateId = $this->pageTemplateId;// 個別ページのテンプレートが選択されている場合は優先
		
		$ret = $this->db->getTemplate($selectedTemplateId, $row);
		if (!$ret) return;
		
		$generator	= $row['tm_generator'];
		$version	= $row['tm_version'];		// テンプレートバージョン
		switch ($generator){
		case M3_TEMPLATE_GENERATOR_THEMLER:		// Themler
			// テンプレート選択メニューを表示
			$this->tmpl->setAttribute('select_subtemplate', 'visibility', 'visible');
			
			$subTemplateInfoFile = $this->gEnv->getTemplatesPath() . '/' . $selectedTemplateId . '/templates/list.php';
			if (is_readable($subTemplateInfoFile)){
				// サブテンプレート情報ファイル読み込み
				require_once($subTemplateInfoFile);
				if (!isset($this->subTemplateInfo)) $this->subTemplateInfo = $templatesInfo;		// ローカル変数へコピー
				
/*				// 選択なし値追加
				$row = array(
					'value'    => '',
					'name'     => $this->convertToDispString('-- ' . $this->_('No Select') . ' --'),
					'selected' => ''														// 選択中かどうか
				);
				$this->tmpl->addVars('subtemplate_list', $row);
				$this->tmpl->parseTemplate('subtemplate_list', 'a');*/
				
				foreach ($this->subTemplateInfo as $key => $templateInfo){
					$subTemplateId = $templateInfo['fileName'];
					$type = $templateInfo['kind'];
					if (empty($subTemplateId)) continue;
					if ($type == 'error404') continue;		// エラーメッセージ表示用の404タイプのサブテンプレートは表示しない
					
					$selected = '';
					if (empty($this->pageTemplateId)){			// ページ個別のテンプレートが設定されていない場合
						if ($subTemplateId == $this->subTemplateId) $selected = 'selected';		// サブテンプレートID
					} else {
						if ($subTemplateId == $this->pageSubTemplateId) $selected = 'selected';		// サブテンプレートID
					}
					
					$row = array(
						'value'    => $this->convertToDispString($subTemplateId),
						'name'     => $this->convertToDispString($templateInfo['defaultTemplateCaption'] . '(' . $templateInfo['fileName'] . ')'),
						'selected' => $selected														// 選択中かどうか
					);
					$this->tmpl->addVars('subtemplate_list', $row);
					$this->tmpl->parseTemplate('subtemplate_list', 'a');
				}
			}
			break;
		}
	}
	/**
	 * デフォルトのサブテンプレートIDを取得
	 *
	 * @param string  $templateId	テンプレートID
	 * @return string				サブテンプレートID
	 */
	function getDefaultSubTemplateId($templateId)
	{
		$subTemplateId = '';
		
		$ret = $this->db->getTemplate($templateId, $row);
		if (!$ret) return $subTemplateId;
		
		$generator	= $row['tm_generator'];
		$version	= $row['tm_version'];		// テンプレートバージョン
		switch ($generator){
		case M3_TEMPLATE_GENERATOR_THEMLER:		// Themler
			// デフォルトのサブテンプレートIDを取得
			$subTemplateInfoFile = $this->gEnv->getTemplatesPath() . '/' . $templateId . '/templates/list.php';
			if (is_readable($subTemplateInfoFile)){
				// サブテンプレート情報ファイル読み込み
				require_once($subTemplateInfoFile);
				if (!isset($this->subTemplateInfo)) $this->subTemplateInfo = $templatesInfo;		// ローカル変数へコピー
				
				foreach ($this->subTemplateInfo as $key => $templateInfo){
					$id = $templateInfo['fileName'];
					$type = $templateInfo['kind'];
					if (empty($id)) continue;
					
					if ($type == 'default'){
						$subTemplateId = $id;
						break;
					}
				}
			}
			break;
		}
		return $subTemplateId;
	}
}
?>
