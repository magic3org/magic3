<?php
/**
 * コンテンツマネージャー
 *
 *  コンテンツのインポート、エクスポート等のデータ操作を行う
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class ContentManager extends _Core
{
	private $db;					// DBオブジェクト
	// データ解析用
	private $_data = array();
	private $_foundImages = array();	// コンテンツに含まれる画像の画像名
	private $_contentId;	// 現在処理中のコンテンツのID
	
	const NICEPAGE_IMPORT_DATA_FILE = '/content/content.json';
	const NICEPAGE_TITLE_HEAD = '[Nicepage]';
	const NICEPAGE_CONTENT_DIR = '/resource/np/';	// Nicepageコンテンツリソース格納ディレクトリ(このディレクトリ以下にコンテンツIDのディレクトリを作成する)
	const NICEPAGE_IMAGE_DIR = '/image/';		// 画像ディレクトリ
	const NICEPAGE_IMAGE_SRC_DIR = '/content/images/';	// テンプレート内の画像ディレクトリ
	const NICEPAGE_CONTENT_VIEW_WIDGET = 'static_content';	// Nicepageコンテンツ表示用のウィジェット
	const NICEPAGE_CONTENT_CONFIG_TITLE = 'の表示用';	// Nicepageコンテンツ表示用のウィジェットの設定名
	const CONTENT_OBJ_ID	= 'contentlib';	// 汎用コンテンツオブジェクトID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
		
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $gInstanceManager->getSytemDbObject();
	}
	
	/**
	 * テンプレートのページコンテンツをインポートする
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $templateId	テンプレートID
	 * @return 		bool			true=成功、false=失敗
	 */
	function importPageContentFromTemplate($pageId, $pageSubId, $templateId)
	{
		global $gEnvManager;
		
		$templateDir = $this->gEnv->getTemplatesPath() . '/' . $templateId;			// テンプレートのディレクトリ
		$importDataFile = $templateDir . self::NICEPAGE_IMPORT_DATA_FILE;
		if (!file_exists($importDataFile)) return false;

		// インポートデータファイルを読み込んで解凍
		$content = file_get_contents($importDataFile);
		$importData = json_decode($content, true);
		
		// コンテンツ情報取得
		$contentPageIds = array_keys($importData['Pages']);
		if (count($contentPageIds) == 0) return false;
		
		$this->_data['Images'] = $importData['Images'];	// 画像データ
		$parameters = $importData['Parameters'];
		
		$contentLibObj = $this->gInstance->getObject(self::CONTENT_OBJ_ID);
        foreach ($importData['Pages'] as &$articleData) {
            $contentPageId = array_shift($contentPageIds);
			
			// コンテンツデータ取得
			$title = self::NICEPAGE_TITLE_HEAD . $articleData['caption'];	// コンテンツタイトル
			$metaTitle = isset($articleData['titleInBrowser']) ? $articleData['titleInBrowser'] : '';		// METAタイトル
			$metaDesc = isset($articleData['description']) ? $articleData['description'] : '';	// META説明
			$metaKeyword = isset($articleData['keywords']) ? $articleData['keywords'] : '';	// METAキーワード
			$html = $articleData['properties']['publishHtml'];	// 本文
			
			// 追加パラメータ
			$otherParams =	array('cn_generator'		=> M3_TEMPLATE_GENERATOR_NICEPAGE);		// コンテンツ作成アプリケーション
				
			// コンテンツIDを仮取得
			$this->_contentId = $contentLibObj->reserveNextId();
			
			// コンテンツ内の画像パス変換
			$html = $this->_replacePlaceholdersForImages($html);
			
			// DBにコンテンツを登録
			$ret = $contentLibObj->addContent($title, $html, $metaTitle, $metaDesc, $metaKeyword, $newId, $otherParams);
			if (!$ret) return false;
			
			// テンプレート情報更新
			$templateCustomObj = array();
			$templateCustomObj['fonts'] = $articleData['properties']['fonts'];
			$templateCustomObj['bodyClass'] = $articleData['properties']['bodyClass'];
			//$templateCustomObj['head'] = $articleData['properties']['head'];
			//$templateCustomObj['head'] = $this->_processingContent($articleData['properties']['head'], 'publish');
			$templateCustomObj['head'] = $this->_replacePlaceholdersForImages($articleData['properties']['head']);// コンテンツ内の画像パス変換
			$templateCustomObj['hideHeader'] = $articleData['properties']['hideHeader'];
			$templateCustomObj['hideFooter'] = $articleData['properties']['hideFooter'];
			$templateCustomObj['hideBackToTop'] = $articleData['properties']['hideBackToTop'];
			
			// ヘッダ部、フッタ部のコンテンツ内の画像パス変換
			$parameters['header']['php'] = $this->_replacePlaceholdersForImages($parameters['header']['php']);
			$parameters['footer']['php'] = $this->_replacePlaceholdersForImages($parameters['footer']['php']);
			
			$publishNicepageCss = $parameters['publishNicePageCss'];
            //list($siteStyleCssParts, $pageCssUsedIds) = NicepageHelpersNicepage::processAllColors($publishNicepageCss, $properties['publishHtml']);
			list($siteStyleCssParts, $pageCssUsedIds) = self::processAllColors($publishNicepageCss, $html);
			$templateCustomObj['siteStyleCssParts'] = $siteStyleCssParts;
            $templateCustomObj['pageCssUsedIds'] = $pageCssUsedIds;
			$templateCustomObj['header'] = json_encode($parameters['header']);
			$templateCustomObj['footer'] = json_encode($parameters['footer']);
			
			$updateParam = array();
			$updateParam['tm_custom_params'] = serialize($templateCustomObj);
			$ret = $this->db->updateTemplate($templateId, $updateParam);
			if (!$ret) return false;
			
			// 画像ディレクトリ作成
			$imageDir = $gEnvManager->getSystemRootPath() . self::NICEPAGE_CONTENT_DIR . $this->_contentId . self::NICEPAGE_IMAGE_DIR;
			if (!file_exists($imageDir)) mkdir($imageDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
			
			// 画像コピー
			for ($i = 0; $i < count($this->_foundImages); $i++){
				$imageFile = $this->_foundImages[$i];
				copy($templateDir . self::NICEPAGE_IMAGE_SRC_DIR . $imageFile, $imageDir . $imageFile);
			}
			
			// ##### ウィジェットを配置し、コンテンツを表示させる #####
			$ret = $this->db->delPageDefAll($pageId, $pageSubId);
			if (!$ret) return false;
			
			$now = date("Y/m/d H:i:s");	// 現在日時
			$configTitle = $title . ' ' . self::NICEPAGE_CONTENT_CONFIG_TITLE;// 設定名

			// ウィジェットのパラメータオブジェクト作成
			$newObj = new stdClass;
			$newObj->name	= $configTitle;		// 設定名
			$newObj->contentId = $this->_contentId;		// 参照するコンテンツのID
			$newObj->showReadMore	= '';		// 「続きを読む」ボタンを表示
			$newObj->readMoreTitle	= '';		// 「続きを読む」ボタンタイトル
			
			// ウィジェットの設定追加
			$configId = -1;		// 新規追加
			$ret = $this->db->updateWidgetParam(self::NICEPAGE_CONTENT_VIEW_WIDGET, serialize($newObj), $gEnvManager->getCurrentUserId(), $now, $configId);
			if (!$ret) return false;
			
			// ウィジェットを配置
			$ret = $this->db->addWidget($pageId, $pageSubId, 'main', self::NICEPAGE_CONTENT_VIEW_WIDGET, 0/*インデックス*/);
			if (!$ret) return false;
			
			// 配置したウィジェットのページ定義シリアル番号を取得
			$ret = $this->db->getPageDefOnPageByWidgetId($pageId, $pageSubId, self::NICEPAGE_CONTENT_VIEW_WIDGET, $row);
			if (!$ret) return false;
			
			// ウィジェットに設定を結びつける
			$serial = $row['pd_serial'];		// シリアル番号
			$ret = $this->db->updateWidgetConfigId(self::NICEPAGE_CONTENT_VIEW_WIDGET, $serial, $configId, $configTitle);
			if (!$ret) return false;
			
			break;		// 1コンテンツで処理終了
        }
		return true;
	}
	/**
	 * Nicepageの画面を作成
	 *
	 * @param string $pageContent	画面データ
	 * @return 		string			変換後画面データ
	 */
	function createNicepagePage($pageContent)
	{
		require_once($this->gEnv->getJoomlaRootPath() . '/NpPage.php');
		
		// 画面データからコンテンツだけを抜き出し、Nicepage専用のデータを作成する
		if (strpos($pageContent, '<!--np_content-->') !== false && preg_match('/<\!--np\_page_id-->([\s\S]+?)<\!--\/np\_page_id-->/', $pageContent, $matches)) {
            $pageId = $matches[1];
            $pageContent = str_replace($matches[0], '', $pageContent);
            //$page = NpFactory::getPage($pageId);
			$page = new NpPage();
            if ($page) {
                $pageContent = $page->get($pageContent);
            }
        }
		return $pageContent;
	}
    /**
     * Replace image placeholders in page content
     *
     * @param string $content Page sample content
     *
     * @return mixed
     */
    private function _replacePlaceholdersForImages($content)
    {
        //change default image
       // $content = str_replace('[image_default]', $this->_rootUrl . 'components/com_nicepage/assets/images/nicepage-images/default-image.jpg', $content);
        $content = preg_replace_callback('/\[image_(\d+)\]/', array(&$this, '_replacerImages'), $content);
        return $content;
    }
    /**
     * Callback function for replacement image placeholders
     *
     * @param array $match
     *
     * @return string
     */
    private function _replacerImages($match)
    {
        $full = $match[0];
        $n = $match[1];
        if (isset($this->_data['Images'][$n])) {
            $imageName = $this->_data['Images'][$n]['fileName'];
            array_push($this->_foundImages, $imageName);
            return M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . self::NICEPAGE_CONTENT_DIR . $this->_contentId . self::NICEPAGE_IMAGE_DIR . $imageName;
        }
        return $full;
    }
    /**
     * @param string $styles                common styles
     * @param string $publishHtml           publish html of page
     * @param string $publishHeaderFooter   publish html of page
     * @param string $publishCookiesSection publish cookies section
     *
     * @return array
     */
    public static function processAllColors($styles, $publishHtml, $publishHeaderFooter = '', $publishCookiesSection = '') {
        $split = preg_split('#(\/\*begin-color [^*]+\*\/[\s\S]*?\/\*end-color [^*]+\*\/)#', $styles, -1, PREG_SPLIT_DELIM_CAPTURE);
        $parts = array();
        foreach ($split as $part) {
            $part = trim($part);
            if (!$part) {
                continue;
            }

            if (preg_match('#\/\*begin-color ([^*]+)\*\/#', $part, $m)) {
                $id = 'color_' . $m[1];
                $parts[] = array(
                    'type' => 'color',
                    'id' => $id,
                    'css' => $part,
                );
                if (strpos($publishHtml, $m[1]) !== false) {
                    $usedIds[$id] = true;
                }
            } else {
                $parts[] = array(
                    'type' => '',
                    'css' => $part,
                );
            }
        }
        $headerFooterUsedIds = $publishHeaderFooter ? self::processUsedColors($parts, $publishHeaderFooter) : [];
        $cookiesCssUsedIds = $publishCookiesSection ? self::processUsedColors($parts, $publishCookiesSection) : [];
        $usedIds = self::processUsedColors($parts, $publishHtml);
        return array(json_encode($parts), json_encode($usedIds), json_encode($headerFooterUsedIds), json_encode($cookiesCssUsedIds));
    }

    /**
     * @param array  $parts       All styles array
     * @param string $publishHtml Html of page
     *
     * @return array
     */
    public static function processUsedColors($parts, $publishHtml)
    {
        $usedIds = array();
        foreach ($parts as &$part) {
            if (isset($part['id']) && strpos($publishHtml, preg_replace('#^color_#', '', $part['id'])) !== false) {
                $usedIds[$part['id']] = true;
            }
        }
        return $usedIds;
    }
}
?>
