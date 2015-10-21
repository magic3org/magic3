<?php
/**
 * Joomla!モジュールHTML作成クラス
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
require_once($this->gEnv->getJoomlaRootPath() . '/JParameter.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/html.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/arrayhelper.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/uri.php');

require_once($this->gEnv->getJoomlaRootPath() . '/class/event.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/dispatcher.php');

require_once($this->gEnv->getJoomlaRootPath() . '/class/plugin.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/pluginHelper.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/filteroutput.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/factory.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/application.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/menu.php');
//require_once($this->gEnv->getJoomlaRootPath() . '/class/tree.php');		// 不要?
////require_once($this->gEnv->getJoomlaRootPath() . '/class/document.php');
////require_once($this->gEnv->getJoomlaRootPath() . '/class/language.php');
////require_once($this->gEnv->getJoomlaRootPath() . '/class/cache.php');
////require_once($this->gEnv->getJoomlaRootPath() . '/class/database.php');
	
class JRender extends JParameter
{
	private $templateId;		// テンプレートID
	private $viewBaseDir;			// ビュー作成用スクリプトベースディレクトリ
	private $viewRenderType;		// ビュー作成タイプ
	private $_hookPoints = array();			// フック管理用
	const DEFAULT_READMORE_TITLE = 'もっと読む';			// もっと読むボタンのデフォルトタイトル
	const DEFAULT_RENDER_DIR = '/render/';					// デフォルトのビュー作成スクリプトディレクトリ
	const WIDGET_INNER_CLASS = 'm3_widget_inner';			// ウィジェットの内側クラス
	const TEMPLATE_GENERATOR_THEMLER = 'themler';			// テンプレート作成アプリケーション(Themler)

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * テンプレートの設定
	 *
	 * @param string $templateId	テンプレートID
	 * @return						なし
	 */
	function setTemplate($id)
	{
		$this->templateId = $id;
	}
	/**
	 * Joomlaモジュール用コンテンツ取得
	 * 
	 * @param string $style			表示スタイル
	 * @param string $content		ウィジェット出力
	 * @param string $title			タイトル(空のときはタイトル非表示)
	 * @param array $attribs		その他タグ属性
	 * @param array $paramsOther	その他パラメータ
	 * @param array $pageDefParam	画面定義パラメータ
	 * @param int   $templateVer	テンプレートバージョン(0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
	 * @return string				モジュール出力
	 */
	public function getModuleContents($style, $content, $title = '', $attribs = array(), $paramsOther = array(), $pageDefParam = array(), $templateVer = 0)
	{
		global $gEnvManager;

		// 必要なスクリプトを読み込む
		$templateId = empty($this->templateId) ? $gEnvManager->getCurrentTemplateId() : $this->templateId;
		$path = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/modules.php';		// テンプレート独自の変換処理
		require_once($gEnvManager->getJoomlaRootPath() . '/render/modules.php');		// デフォルトの出力方法
		if (is_readable($path)) require_once($path);
		
		$contents = '';
		$params   = new JParameter();
		if (isset($paramsOther['moduleclass_sfx'])){
			$params->set('moduleclass_sfx', $paramsOther['moduleclass_sfx']);
			
			// メニュータイプの場合はメニュー表示用データに変換
			if ($paramsOther['moduleclass_sfx'] == 'art-vmenu'){		// 縦型メニュー
				if ($templateVer == 2){// Joomla!v2.5テンプレート
					$content = $this->getMenuContents($style, $content, $title, $attribs, $paramsOther, $pageDefParam, $templateVer);
				} else {
				//	$params->set('startLevel',		0);
					$params->set('startLevel',		1);
					$params->set('endLevel',		0);
					$params->set('showAllChildren',	1);		// サブメニュー表示
					$path = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/mod_mainmenu/default.php';		// メニュー出力用スクリプト
					
					if (file_exists($path)){
						// ウィジェットが出力したメニューコンテンツを設定
						$gEnvManager->setJoomlaMenuContent($content);

						ob_clean();
						require_once($gEnvManager->getJoomlaRootPath() . '/render/mainmenuHelper.php');		// デフォルトの出力方法
						require($path);		// 毎回実行する
						$content = ob_get_contents();
						ob_clean();
					}
				}
			}
		}

		// 前後コンテンツ追加
		$content = $pageDefParam['pd_top_content'] . $content . $pageDefParam['pd_bottom_content'];
		// 「もっと読む」ボタンを追加
		if ($pageDefParam['pd_show_readmore']) $content = $this->addReadMore($content, $pageDefParam['pd_readmore_title'], $pageDefParam['pd_readmore_url']);
		
		// ウィジェットの内枠(コンテンツ外枠)を設定
		$content = '<div class="' . self::WIDGET_INNER_CLASS . '">' . $content . '</div>';
		
		// 指定された表示スタイルでウィジェットを出力
		$chromeMethod = 'modChrome_' . $style;
		if (function_exists($chromeMethod))
		{
			$module = new stdClass;
			//$module->style = $attribs['style'];
			$module->content = $content;
			if (!empty($title)){
				$module->showtitle = 1;
//				$module->title = htmlentities($title, ENT_COMPAT, M3_HTML_CHARSET);
				$module->title = convertToHtmlEntity($title);
			}
			
			// Joomla!2.5テンプレート用追加設定(2012/4/4 追加)
			$GLOBALS['artx_settings'] = array('block' => array('has_header' => true));
			
			ob_clean();
			$chromeMethod($module, $params, $attribs);
			$contents = ob_get_contents();
			ob_clean();
		}
		return $contents;
	}
	/**
	 * Joomlaコンポーネント用コンテンツ取得
	 * 
	 * @param string $style			表示スタイル
	 * @param string $content		ウィジェット出力
	 * @param string $title			タイトル(空のときはタイトル非表示)
	 * @param array $attribs		その他タグ属性
	 * @param array $paramsOther			その他パラメータ
	 * @param array $pageDefParam	画面定義パラメータ
	 * @param int   $templateVer	テンプレートバージョン(0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
	 * @return string				コンポーネント出力
	 */
	public function getComponentContents($style, $content, $title = '', $attribs = array(), $paramsOther = array(), $pageDefParam = array(), $templateVer = 0)
	{
		global $gEnvManager;
		global $gInstanceManager;

		// ビューの描画タイプ(archive,article(デフォルト),category,featured,form)を取得
		$renderType = $gEnvManager->getCurrentRenderType();
		if (empty($renderType)) $renderType = 'article';
		if (isset($paramsOther['moduleclass_sfx']) && $paramsOther['moduleclass_sfx'] == 'featured') $renderType = 'featured';		// 特集コンテンツ表示の場合
		
		// 前後コンテンツ追加
		$content = $pageDefParam['pd_top_content'] . $content . $pageDefParam['pd_bottom_content'];
		// 「もっと読む」ボタンを追加
		if ($pageDefParam['pd_show_readmore']) $content = $this->addReadMore($content, $pageDefParam['pd_readmore_title'], $pageDefParam['pd_readmore_url']);
		
		// ウィジェットの内枠(コンテンツ外枠)を設定
		$content = '<div class="' . self::WIDGET_INNER_CLASS . '">' . $content . '</div>';
		
		// 設定を作成
		// 「$this->item」はテンプレート内では「$this->article」として認識される
		$contents = '';
		$this->params   = new JParameter();
		$this->item = new stdClass;
		$this->article = new stdClass;
		$this->item->params = new JParameter();
		$this->pagination = new JParameter();
		$this->user = new JUser();
		// Bootstrapテンプレート用
		$this->pageheading = '';		// ページタイトル用
		
		// ページタイトルの設定
		if (!empty($title) && $title != M3_TAG_START . M3_TAG_MACRO_NOTITLE . M3_TAG_END){
			// 旧タイトル設定方法
/*			$this->params->set('show_title', 1);
			//$this->params->set('link_titles', 1);
			$this->article->title = $title;
			
			// Joomla!2.5テンプレート用追加設定(2012/4/4 追加)
			$this->item->title = $title;
			$this->item->params->set('show_title', 1);
			*/
			// ページタイトルの設定方法を以下に変更(2015/10/6)
			// Joomla!新型描画処理用(Joomla!1.5以上、Themler)
			$this->params->set('show_page_heading', 1);
			$this->params->set('page_heading', $title);
			// Bootstrapテンプレート用
			$this->pageheading = $title;		// ページタイトル用
		}
		$this->article->text = $content;
		
		// Joomla!2.5テンプレート用追加設定(2012/4/4 追加)
		/*
		$this->params->set('show_intro', 1);
		$this->params->set('show_readmore', 1);
		$this->params->set('show_readmore_title', 1);
		$this->params->set('readmore_limit', 100);*/
		$this->item->params->set('access-view', true);
		$this->item->text = $content;
//		$this->item->toc = '---タイトル下説明---';
//		$this->item->intro = '---不明---';
/*$this->item->params->set('show_readmore', true);
$this->item->params->set('show_readmore_title', true);
$this->item->params->set('show_noauth', true);
$this->item->params->set('readmore_limit', 100);
$this->item->readmore_link = '******';
$this->item->readmore = '******';
$this->item->title = '****';*/

		// Artisteer,Themlerテンプレート用
		// ページ遷移の設定(com_contentのarchive,article,category,featuredで有効)
		$this->item->pagination = '';		// 出力内容
		$this->item->paginationposition = 1;// 0(前置)または1(後置)
		$this->item->paginationrelative = 0;// 0または1
		$this->item->event = new stdClass;
		$this->item->event->beforeDisplayContent = '';		// ページ遷移用タグ(前置)
		$this->item->event->afterDisplayContent = '';		// ページ遷移用タグ(後置)
		
		// ページ前後遷移ナビゲーション作成
		$pageNavData = $gEnvManager->getJoomlaPageNavData();		// ナビゲーション情報取得
		if (!empty($pageNavData)){
			$this->item->params->set('show_item_navigation', 1);		// ページ遷移ナビゲーション表示
			
			JPluginHelper::importPlugin('content');		// JEventDispatcherのattach()が実行される
			$dispatcher = JEventDispatcher::getInstance();
			$results = $dispatcher->trigger(
				'onContentBeforeDisplay', array ('com_content.article', &$this->item, &$this->item->params)
			);
			$this->item->event->beforeDisplayContent = trim(implode("\n", $results));		// ページ遷移用タグ(前置)
			$results = $dispatcher->trigger(
				'onContentAfterDisplay', array ('com_content.article', &$this->item, &$this->item->params)
			);
			$this->item->event->afterDisplayContent = trim(implode("\n", $results));		// ページ遷移用タグ(後置)
		}
		// ページ番号遷移ナビゲーション作成
		$paginationData = $gEnvManager->getJoomlaPaginationData();
		if (!empty($paginationData)){
			require_once($gEnvManager->getJoomlaRootPath() . '/class/paginationObject.php');
			require_once($gEnvManager->getJoomlaRootPath() . '/class/pagination.php');
			
			$totalCount = $paginationData['total'];				// 項目総数
			$itemOffset	= $paginationData['offset'];			// 最初の項目のオフセット
			$viewCount	= $paginationData['viewcount'];			// 1ページあたりの表示項目数
			$this->pagination = new JPagination($totalCount, $itemOffset, $viewCount);
//			$this->pagination = new JPagination(10, 2, 3);
		}
		
		// 処理を行うテンプレートを取得
		$templateId = empty($this->templateId) ? $gEnvManager->getCurrentTemplateId() : $this->templateId;
		
		switch ($templateVer){
		case 1:		// Joomla!v1.5テンプレート
		case 2:		// Joomla!v2.5テンプレート(Artisteer,Themler)
			switch ($renderType){
			case 'article':		// 単一記事型出力
			default:
				// ウィジェットで生成したコンテンツ情報を取得
				$viewData = $gEnvManager->getJoomlaViewData();
				if (!empty($viewData)){
					$contentItems = $viewData['Items'];		// コンテンツ情報
					$contentItem = $contentItems[0];
					$this->item->title = $contentItem->title;
					$this->item->params->set('show_title', 1);
//					$this->item->titleLink = $contentItem->url;
//					$this->item->params->set('link_titles', 1);		// リンク表示
					if (!empty($contentItem->published)){		// 登録日時
						$this->item->publish_up = $contentItem->published;
						$this->item->params->set('show_publish_date', 1);
					}
					if (!empty($contentItem->author)){		// 投稿者
						$this->item->author = $contentItem->author;
						$this->item->params->set('show_author', 1);
					}
					if (isset($contentItem->hits)){		// アクセス数
						$this->item->hits = $contentItem->hits;
						$this->item->params->set('show_hits', 1);
					}
				}

				$path = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/article/default.php';		// ビュー作成処理
				$this->viewBaseDir = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/article';			// ビュー作成用スクリプトベースディレクトリ
				$this->viewRenderType = 'com_content/article';		// ビュー作成タイプ
				break;
			case 'category':		// リスト記事型出力
			case 'featured':		// 特集表示型出力
				// ウィジェットで生成したコンテンツ情報を取得
				$viewData = $gEnvManager->getJoomlaViewData();
				$contentItems = $viewData['Items'];		// コンテンツ情報
				$leadContentCount = $viewData['leadContentCount'];		// 先頭のコンテンツ数(Magic3拡張)
				if ($leadContentCount > count($contentItems)) $leadContentCount = count($contentItems);
				$columnContentCount = $viewData['columnContentCount'];		// カラム部のコンテンツ数(Magic3拡張)
				if ($columnContentCount > count($contentItems) - $leadContentCount) $columnContentCount = count($contentItems) - $leadContentCount;
				
				// 「もっと読む」ボタンのデフォルトのタイトルを設定
				$readMoreTitle = $viewData['readMoreTitle'];		// 「もっと読む」ボタンタイトル(ウィジェットのデフォルト値)
				$defaultReadMoreTitle = $gInstanceManager->getMessageManager()->getJoomlaText('COM_CONTENT_READ_MORE_TITLE');
				if (!empty($readMoreTitle)) $gInstanceManager->getMessageManager()->replaceJoomlaText('COM_CONTENT_READ_MORE_TITLE', $readMoreTitle);		// 「もっと読む」デフォルトのタイトルを変更
			
				// 出力制御用のフック処理追加
				$this->_addHook('loadtemplate.start', array($this, '_loadtemplateStartHook'));
	
				// ### カテゴリーの情報 ###
				// カテゴリー説明を優先
				$categoryDesc = $viewData['categoryDesc'];
				if (!empty($categoryDesc)){
					// カテゴリーの説明
					$this->category = new stdClass;
					$this->category->description = $categoryDesc;
					$this->params->set('show_description', 1);
				} else if ($viewData['withDefaultOutput']){				// ウィジェット出力をカテゴリー説明部に出力する場合
					// カテゴリーの説明
					$this->category = new stdClass;
					$this->category->description = $content;
					$this->params->set('show_description', 1);
				}
				// カテゴリータイトル(サブタイトル)
				//$this->params->set('show_category_title', 1);
				//$this->article->params->set('show_category', 1);

				// 個別のコンテンツの付加情報
				for ($i = 0; $i < count($contentItems); $i++){
					$contentItem = $contentItems[$i];

					$contentViewInfo = new JParameter();
					$contentItem->params = $contentViewInfo;
					$contentItem->slug = $contentItem->url;			// コンテンツへのリンク
					if (!empty($contentItem->title)) $contentViewInfo->set('show_title', 1);		// タイトルが設定されている場合は表示
					if (!empty($contentItem->created)) $contentViewInfo->set('show_create_date', 1);
					if (!empty($contentItem->modified)) $contentViewInfo->set('show_modify_date', 1);
					if (!empty($contentItem->published)){			// 登録日時
						$contentItem->publish_up = $contentItem->published;
						$contentViewInfo->set('show_publish_date', 1);
					}
					if (!empty($contentItem->author)) $contentViewInfo->set('show_author', 1);		// 投稿者
					if (isset($contentItem->hits)) $contentViewInfo->set('show_hits', 1);		// アクセス数

					// 「もっと読む」のボタンを表示するかどうかは$contentItem->readmoreに値が設定されているかどうかで判断する
					if (!empty($contentItem->readmore)){
						$contentViewInfo->set('show_readmore', 1);		// 「もっと読む」ボタン表示
						//$contentViewInfo->set('show_readmore_title', 1);		// 「もっと読む」の後にタイトル名付加
						//$contentViewInfo->set('readmore_limit', 10);			// タイトル名の長さ
						
						//if (empty($contentItem->readmore)) $contentItem->readmore = $defaultReadMoreTitle;		// $contentItem->readmoreにダミー値をセット
						// 記事個別に$contentItem->readmoreが設定されている場合は、「もっと読む」ボタンのタイトルを変更する(現在は変更不可)
					}
					$contentViewInfo->set('link_titles', 1);		// タイトルにリンクを付加(タイトルのリンク作成用)
					$contentViewInfo->set('access-view', 1);		// (タイトルのリンク作成用)
					//$contentItem->catslug = '';			// カテゴリーID
				}
				
				$this->lead_items = array();
				$this->intro_items = array();
				$this->link_items = array();
				for ($i = 0; $i < $leadContentCount; $i++){
					$this->lead_items[$i] = $contentItems[$i];
				}
				for (; $i < $leadContentCount + $columnContentCount; $i++){
					$this->intro_items[$i] = $contentItems[$i];
				}
				for (; $i < count($contentItems); $i++){
					$this->link_items[$i] = $contentItems[$i];
				}
				$this->columns = $viewData['columnCount'];		// カラム数(Magic3拡張)

				if ($renderType == 'category'){
					$path = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/category/blog.php';		// ビュー作成処理
					$this->viewBaseDir = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/category';			// ビュー作成用スクリプトベースディレクトリ
					$this->viewRenderType = 'com_content/category';		// ビュー作成タイプ
				} else if ($renderType == 'featured'){
					$path = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/featured/default.php';		// ビュー作成処理
					$this->viewBaseDir = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/featured';			// ビュー作成用スクリプトベースディレクトリ
					$this->viewRenderType = 'com_content/featured';		// ビュー作成タイプ
				}
				break;
			}
			break;
		case 10:		// Bootstrap v3.0
			$path = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/article/default.php';		// ビュー作成処理
			break;
		}
		if (!is_readable($path)){// テンプレートの変換処理がない場合はデフォルトを使用
			$path = $gEnvManager->getJoomlaRootPath() . '/render/default.php';
		}

		// ビューの作成
		ob_clean();
		require($path);		// 毎回実行する
		$contents = ob_get_contents();
		ob_clean();
		
		// 出力制御用のフック処理を戻す
		$this->_removeAllHook();
		
		// 変更したリソースを戻す
		if (!empty($readMoreTitle)) $gInstanceManager->getMessageManager()->replaceJoomlaText('COM_CONTENT_READ_MORE_TITLE', $defaultReadMoreTitle);		// 「もっと読む」デフォルトのタイトルを変更
			
		// テンプレート固有の追加処理
		if ($gEnvManager->getCurrentTemplateGenerator() == self::TEMPLATE_GENERATOR_THEMLER){			// テンプレート作成アプリケーションがThemlerの場合
			// サブテンプレートIDを取得。取得できない場合はテンプレートのデフォルト値を取得。
			$subTemplateId = $gEnvManager->getCurrentSubTemplateId();
			if (empty($subTemplateId)) $subTemplateId = getCurrentTemplateByType('');		// トップ用テンプレート

			$subTemplateFile = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/templates/' . $subTemplateId . '.php';
			if (is_readable($subTemplateFile)){
				$subTemplate = file_get_contents($subTemplateFile);
				
				// 必要な部分のみフィルタリングする
				if (preg_match('/\$document->view->componentWrapper\(\'(.*?)\'\);/', $subTemplate, $matches)){
					$contents = getCustomComponentContent($contents, $matches[1]);
//debug('#content area='.$matches[1]);		// デバッグ用
				}
				// ### ウィジェットエラーメッセージはここでコンテンツの先頭に追加して表示する? ###
			}
		}
		return $contents;
	}
	/**
	 * Joomlaナビゲーションメニュー用コンテンツ取得
	 * 
	 * @param string $style			表示スタイル
	 * @param string $content		ウィジェット出力
	 * @param string $title			タイトル(空のときはタイトル非表示)
	 * @param array $attribs		その他タグ属性
	 * @param array $paramsOther	その他パラメータ
	 * @param array $pageDefParam	画面定義パラメータ
	 * @param int   $templateVer	テンプレートバージョン(0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
	 * @return string				モジュール出力
	 */
	public function getNavMenuContents($style, $content, $title = '', $attribs = array(), $paramsOther = array(), $pageDefParam = array(), $templateVer = 0)
	{
		$content = $this->getMenuContents($style, $content, $title, $attribs, $paramsOther, $pageDefParam, $templateVer);

		// ウィジェットの内枠(コンテンツ外枠)を設定
		if (!empty($content)) $content = '<div class="' . self::WIDGET_INNER_CLASS . '">' . $content . '</div>';
		return $content;
	}
	/**
	 * Joomlaメニュー用コンテンツ取得
	 * 
	 * @param string $style			表示スタイル
	 * @param string $content		ウィジェット出力
	 * @param string $title			タイトル(空のときはタイトル非表示)
	 * @param array $attribs		その他タグ属性
	 * @param array $paramsOther	その他パラメータ
	 * @param array $pageDefParam	画面定義パラメータ
	 * @param int   $templateVer	テンプレートバージョン(0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
	 * @return string				モジュール出力
	 */
	public function getMenuContents($style, $content, $title = '', $attribs = array(), $paramsOther = array(), $pageDefParam = array(), $templateVer = 0)
	{
		global $gEnvManager;

//		// 前後コンテンツ追加
//		$content = $pageDefParam['pd_top_content'] . $content . $pageDefParam['pd_bottom_content'];
//		// 「もっと読む」ボタンを追加
//		if ($pageDefParam['pd_show_readmore']) $content = $this->addReadMore($content, $pageDefParam['pd_readmore_title'], $pageDefParam['pd_readmore_url']);
		
		// パラメータ作成
		$params   = new JParameter();
	//	$params->set('startLevel',		0);
		$params->set('startLevel',		1);
		$params->set('endLevel',		0);
		$params->set('showAllChildren',	1);		// サブメニュー表示
		if (isset($paramsOther['moduleclass_sfx'])) $params->set('moduleclass_sfx', $paramsOther['moduleclass_sfx']);

		// 必要なスクリプトを読み込む
		$templateId = empty($this->templateId) ? $gEnvManager->getCurrentTemplateId() : $this->templateId;
		switch ($templateVer){
			case 1:		// Joomla!v1.5テンプレート
				$helper = $gEnvManager->getJoomlaRootPath() . '/render/mainmenuHelper.php';
				$menuPath = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/mod_mainmenu/default.php';		// メニュー出力用スクリプト
				break;
			case 2:		// Joomla!v2.5テンプレート
				$helper = $gEnvManager->getJoomlaRootPath() . '/render/menuHelper.php';
				$menuPath = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/mod_menu/default.php';		// メニュー出力用スクリプト
				break;
			default:
				$helper = '';
				$menuPath = '';
		}

		// メニュー出力を取得
		$contents = '';
		if (is_readable($menuPath)){
			// ウィジェットが出力したメニューコンテンツを設定
			$gEnvManager->setJoomlaMenuContent($content);

			// Joomla!2.5テンプレート用追加設定(2012/5/1 追加)
			$GLOBALS['artx_settings']['menu']['show_submenus'] = true;
			$GLOBALS['artx_settings']['vmenu']['show_submenus'] = true;
			
			ob_clean();
			if ($templateVer == 2){// Joomla!v2.5テンプレート
				require_once($gEnvManager->getJoomlaRootPath() . '/class/moduleHelper.php');
			}
			//require_once($helper);		// デフォルトの出力方法
			require($helper);		// デフォルトの出力方法
			require($menuPath);		// 毎回実行する
			$contents = ob_get_contents();
			ob_clean();
		}
		return $contents;
	}
	/**
	 * ウィジェットに「もっと読む...」ボタンを追加
	 *
	 * @param string $src		ウィジェットコンテンツ
	 * @param string $title		ボタンタイトル
	 * @param string $url		リンク先
	 * @return string			ボタンを追加したコンテンツ
	 */
	public function addReadMore($src, $title, $url)
	{
		$dest = $src;
		if (empty($title)) $title = self::DEFAULT_READMORE_TITLE;
		
		if (function_exists('artxLinkButton')){
			$dest .= '<p class="readmore">' . artxLinkButton(array(
						'classes' => array('a' => 'readon'),
						'link' => $url,
						'content' => str_replace(' ', '&#160;', $title))) . '</p>';
		} else if (function_exists('funcLinkButton')){
			$dest .= '<p class="readmore">' . funcLinkButton(array(
						'classes' => array('a' => 'readon'),
						'link' => $url,
						'content' => str_replace(' ', '&#160;', $title))) . '</p>';
//			$dest .= readmore($title, $url);
		} else {
			$dest .= '<p class="readmore"><a class="button art-button" href="' . convertUrlToHtmlEntity($url) . '">' . convertToHtmlEntity($title) . '</a></p>';
		}
		return $dest;
	}
	/**
	 * HTML文字エスケープ
	 *
	 * @param string $src		変換元文字列
	 * @return string			変換文字列
	 */
	public function escape($src)
	{
//		return htmlentities($src, ENT_COMPAT, M3_HTML_CHARSET);
		return convertToHtmlEntity($src);
	}
	/**
	 * ビュー作成処理(テンプレート側からの呼び出し用)
	 *
	 * @param string $viewId	ビューファイル識別ID
	 * @return string			変換文字列
	 */
	public function loadTemplate($viewId = null)
	{
		global $gEnvManager;
		
		$renderType = $gEnvManager->getCurrentRenderType();
		$templateId = empty($this->templateId) ? $gEnvManager->getCurrentTemplateId() : $this->templateId;
		$viewFileHead = ($renderType == 'category') ? 'blog' : 'default';
//		$viewFile = $this->viewBaseDir . '/default_' . $viewId . '.php';
		$viewFile = $this->viewBaseDir . '/' . $viewFileHead . '_' . $viewId . '.php';

		// テンプレートのビュー作成スクリプトがない場合はデフォルトのスクリプトを読み込む
		if (!is_readable($viewFile)){
			$viewFile = $gEnvManager->getJoomlaRootPath() . self::DEFAULT_RENDER_DIR . $this->viewRenderType . '/' . $viewFileHead . '_' . $viewId . '.php';
		}
		
		// フック処理を実行
		$this->_execHook('loadtemplate.start', array($this->item));
		
		// 出力を取得
		ob_start();
		if (is_readable($viewFile)) include $viewFile;

		$this->_output = ob_get_contents();
		ob_end_clean();

		// フック処理を実行
		$this->_execHook('loadtemplate.end');
		
		return $this->_output;
	}
	/**
	 * イベントフック処理
	 *
	 * @return				なし
	 */
	public function _loadtemplateStartHook()
	{
		global $gInstanceManager;
		
		$item = func_get_arg(0);
		
		// 個別記事で「もっと読む」ボタンのラベルが設定されている場合は、ラベルを変更
		if (!empty($item->readmorelavel)) $gInstanceManager->getMessageManager()->replaceJoomlaText('COM_CONTENT_READ_MORE_TITLE', $item->readmorelavel);
	}
	/**
	 * フック処理を追加
	 *
	 * @param string $name			フックポイント名
	 * @param object $method		実行メソッド
	 * @param array $methodParam	メソッド用パラメータ
	 * @return bool					true=成功、false=失敗
	 */
	private function _addHook($name, $method)
	{
		$this->_hookPoints[$name] = $method;			// フック管理用
		return true;
	}
	/**
	 * フック処理を削除
	 *
	 * @param string $name			フックポイント名
	 * @return bool					true=成功、false=失敗
	 */
	private function _removeHook($name)
	{
		unset($this->_hookPoints[$name]);
		return true;
	}
	/**
	 * すべてのフック処理を削除
	 *
	 * @return bool					true=成功、false=失敗
	 */
	private function _removeAllHook()
	{
		if (!empty($this->_hookPoints)) $this->_hookPoints = array();			// フック管理用
		return true;
	}
	/**
	 * フック処理を実行
	 *
	 * @param string $name			フックポイント名
	 * @param array $methodParam	メソッド用パラメータ
	 * @return bool					true=成功、false=失敗
	 */
	private function _execHook($name, $methodParam = array())
	{
		$method = $this->_hookPoints[$name];			// フック管理用
		
		if (is_callable($method)){
			call_user_func_array($method, $methodParam);
		}
		return true;
	}
}
?>
