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
		
			break;
            /*$key++;
            $article = $content->create();
            $article->catid = $defaultCategoryId;
            list($title, $alias) = $this->_generateNewTitle($defaultCategoryId, $articleData['caption'], $key);
            $article->title = $title;
            $article->alias = $alias;
            $article->introtext = isset($articleData['introHtml']) ? $articleData['introHtml'] : '';
            $article->attribs = $this->_paramsToString(
                array (
                    'show_title' => '',
                    'link_titles' => '',
                    'show_intro' => '',
                    'show_category' => '',
                    'link_category' => '',
                    'show_parent_category' => '',
                    'link_parent_category' => '',
                    'show_author' => '',
                    'link_author' => '',
                    'show_create_date' => '',
                    'show_modify_date' => '',
                    'show_publish_date' => '',
                    'show_item_navigation' => '',
                    'show_icons' => '',
                    'show_print_icon' => '',
                    'show_email_icon' => '',
                    'show_vote' => '',
                    'show_hits' => '',
                    'show_noauth' => '',
                    'alternative_readmore' => '',
                    'article_layout' => ''
                )
            );
            $article->metadata = $this->_paramsToString(array('robots' => '', 'author' => '', 'rights' => '', 'xreference' => '', 'tags' => ''));
            $article->metakey = ''; //support postgresql
            $article->metadesc = ''; //support postgresql
            $status = $content->save($article);
            if (is_string($status)) {
                return $this->_error($status, 1);
            }
            $articleData['joomla_id'] = $article->id;
            $this->_dataIds[$contentPageId] = $article->id;
			*/
        }
		return true;
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
}
?>
