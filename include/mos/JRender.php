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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($this->gEnv->getJoomlaRootPath() . '/JParameter.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/html.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/arrayhelper.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/uri.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/plugin.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/filteroutput.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/factory.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/application.php');
require_once($this->gEnv->getJoomlaRootPath() . '/class/menu.php');
//require_once($this->gEnv->getJoomlaRootPath() . '/class/tree.php');		// 不要?
////require_once($this->gEnv->getJoomlaRootPath() . '/class/document.php');
////require_once($this->gEnv->getJoomlaRootPath() . '/class/language.php');
////require_once($this->gEnv->getJoomlaRootPath() . '/class/cache.php');
////require_once($this->gEnv->getJoomlaRootPath() . '/class/database.php');
	
class JRender
{
	private $templateId;		// テンプレートID
	private $viewBaseDir;			// ビュー作成用スクリプトベースディレクトリ
	private $viewRenderType;		// ビュー作成タイプ
	const DEFAULT_READMORE_TITLE = 'もっと読む';			// もっと読むボタンのデフォルトタイトル
	const DEFAULT_RENDER_DIR = '/render/';					// デフォルトのビュー作成スクリプトディレクトリ
	const WIDGET_INNER_CLASS = 'm3_widget_inner';			// ウィジェットの内側クラス

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
					$params->set('startLevel',		0);
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
				$module->title = htmlentities($title, ENT_COMPAT, M3_HTML_CHARSET);
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

		// 前後コンテンツ追加
		$content = $pageDefParam['pd_top_content'] . $content . $pageDefParam['pd_bottom_content'];
		// 「もっと読む」ボタンを追加
		if ($pageDefParam['pd_show_readmore']) $content = $this->addReadMore($content, $pageDefParam['pd_readmore_title'], $pageDefParam['pd_readmore_url']);
		
		// ウィジェットの内枠(コンテンツ外枠)を設定
		$content = '<div class="' . self::WIDGET_INNER_CLASS . '">' . $content . '</div>';
		
		// 設定を作成
		$contents = '';
		$this->params   = new JParameter();
		$this->item = new stdClass;
		$this->article = new stdClass;
		$this->item->params = new JParameter();
		$this->pagination = new JParameter();		// 暫定(2013/1/7)
		$this->user = new JUser();
		if (!empty($title)){
			$this->params->set('show_title', 1);
			//$this->params->set('link_titles', 1);
			$this->article->title = $title;
			
			// Joomla!2.5テンプレート用追加設定(2012/4/4 追加)
			$this->item->title = $title;
			$this->item->params->set('show_title', 1);
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

		// スクリプトを実行
		$templateId = empty($this->templateId) ? $gEnvManager->getCurrentTemplateId() : $this->templateId;
		
		if (isset($paramsOther['moduleclass_sfx']) && $paramsOther['moduleclass_sfx'] == 'featured'){		// 特集コンテンツ表示の場合
			// ウィジェットで生成したコンテンツ情報を取得
			$viewData = $gEnvManager->getJoomlaViewData();
			$contentItems = $viewData['Items'];		// コンテンツ情報
			$leadContentCount = $viewData['leadContentCount'];		// 先頭のコンテンツ数(Magic3拡張)
			if ($leadContentCount > count($contentItems)) $leadContentCount = count($contentItems);
			$columnContentCount = $viewData['columnContentCount'];		// カラム部のコンテンツ数(Magic3拡張)
			if ($columnContentCount > count($contentItems) - $leadContentCount) $columnContentCount = count($contentItems) - $leadContentCount;
			
			$readMoreTitle = $viewData['readMoreTitle'];		// 「続きを読む」ボタンタイトル
			if (!empty($readMoreTitle)) $gInstanceManager->getMessageManager()->replaceJoomlaText('COM_CONTENT_READ_MORE_TITLE', $readMoreTitle);		// メッセージを変更
			
			// 個別のコンテンツの付加情報
			for ($i = 0; $i < count($contentItems); $i++){
				$contentItem = $contentItems[$i];

				$contentViewInfo = new JParameter();
				$contentItem->params = $contentViewInfo;
				if (!empty($contentItem->title)) $contentViewInfo->set('show_title', 1);		// タイトルが設定されている場合は表示
				if (!empty($contentItem->created)) $contentViewInfo->set('show_create_date', 1);
				if (!empty($contentItem->modified)) $contentViewInfo->set('show_modify_date', 1);
				if (!empty($contentItem->published)) $contentViewInfo->set('show_publish_date', 1);
				if (!empty($contentItem->readmore)) $contentViewInfo->set('show_readmore', 1);		// 「もっと読む」ボタン表示
				$contentViewInfo->set('link_titles', 1);		// タイトルにリンクを付加(タイトルのリンク作成用)
				$contentViewInfo->set('access-view', 1);		// (タイトルのリンク作成用)
				$contentItem->slug = $contentItem->url;			// コンテンツへのリンク
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
			
			$path = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/featured/default.php';		// ビュー作成処理
			$this->viewBaseDir = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/featured';			// ビュー作成用スクリプトベースディレクトリ
			$this->viewRenderType = 'com_content/featured';		// ビュー作成タイプ
		} else {
			$path = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/article/default.php';		// ビュー作成処理
			$this->viewBaseDir = $gEnvManager->getTemplatesPath() . '/' . $templateId . '/html/com_content/article';			// ビュー作成用スクリプトベースディレクトリ
			$this->viewRenderType = 'com_content/article';		// ビュー作成タイプ
		}
		if (!is_readable($path)){// テンプレートの変換処理がない場合はデフォルトを使用
			$path = $gEnvManager->getJoomlaRootPath() . '/render/default.php';
		}
		ob_clean();
		require($path);		// 毎回実行する
		$contents = ob_get_contents();
		ob_clean();
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
		$params->set('startLevel',		0);
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
		return htmlentities($src, ENT_COMPAT, M3_HTML_CHARSET);
	}
	/**
	 * ビュー作成処理(Joomla!v2.5テンプレート用)
	 *
	 * @param string $viewId	ビューファイル識別ID
	 * @return string			変換文字列
	 */
	public function loadTemplate($viewId = null)
	{
		global $gEnvManager;
		
		ob_start();

		$templateId = empty($this->templateId) ? $gEnvManager->getCurrentTemplateId() : $this->templateId;
		$viewFile = $this->viewBaseDir . '/default_' . $viewId . '.php';
		
		// テンプレートのビュー作成スクリプトがない場合はデフォルトのスクリプトを読み込む
		if (!is_readable($viewFile)){
			$viewFile = $gEnvManager->getJoomlaRootPath() . self::DEFAULT_RENDER_DIR . $this->viewRenderType . '/default_' . $viewId . '.php';
		}
		if (is_readable($viewFile)) include $viewFile;

		$this->_output = ob_get_contents();
		ob_end_clean();

		return $this->_output;
	}
}
?>
