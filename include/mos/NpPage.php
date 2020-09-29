<?php
/**
 * @package Nicepage Website Builder
 * @author Nicepage https://www.nicepage.com
 * @copyright Copyright (c) 2016 - 2019 Nicepage
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

//JLoader::register('Nicepage_Theme_Nicepage', JPATH_ADMINISTRATOR . '/components/com_nicepage/library/theme.php');
//require JPATH_ADMINISTRATOR . '/components/com_nicepage/helpers/modules.php';

require_once($this->gEnv->getJoomlaRootPath() . '/nicepage/library/theme.php');
require_once($this->gEnv->getJoomlaRootPath() . '/nicepage/nicepage.php');

/**
 * Class NpPage
 */
class NpPage
{
    private static $_instance;

    private $_originalName = 'nicepage';
    private $_isNicepageTheme = '0';
    private $_pageTable = null;

    private $_pageView = 'landing';
    private $_config = null;
    private $_props = null;

    private $_scripts = '';
    private $_styles = '';
    private $_backlink = '';
    private $_sectionsHtml = '';
    private $_cookiesConsent = '';
    private $_cookiesConfirmCode = '';
    private $_backToTop = '';
    private $_canonicalUrl = '';

    private $_context;
    private $_row;
    private $_params;

    private $_header = '';
    private $_footer = '';

    private $_buildedPageElements = false;

    /**
     * NpPage constructor.
     *
     * @param null   $pageTable Page table
     * @param string $context   Component context
     * @param null   $row       Component row
     * @param null   $params    Component params
     */
    //public function __construct($pageTable, $context, &$row, &$params) {
	public function __construct() {
		global $gEnvManager;
		
		// テンプレートからページ作成用のパラメータ取得
		$optionParams = $gEnvManager->getCurrentTemplateCustomParam();
		if (empty($optionParams)){
			$props = array();
		} else {
			$props = unserialize($optionParams);		// 連想配列に変換
		}

		$this->_config = $props;
		$this->_props = $this->prepareProps(
            $props,
            null,
            '',
            null,
            null
        );
	/*
        $this->_pageTable = $pageTable;
        $this->_context = $context;
        $this->_row = $row;
        $this->_params = $params;

        $props = $this->_pageTable->getProps();
        $this->_config = NicepageHelpersNicepage::getConfig($props['isPreview']);

        $this->_props = $this->prepareProps(
            $props,
            $this->_config,
            $this->_context,
            $this->_row,
            $this->_params
        );

        if (isset($props['pageView'])) {
            $this->_pageView = $props['pageView'];
        }

        $originalName = $this->_originalName;
        if ($this->_row) {
            $this->_row->{$originalName} = true;
        }

        $this->_isNicepageTheme = JFactory::getApplication()->getTemplate(true)->params->get($originalName . 'theme', '0');
		*/
    }

    /**
     * Get page id
     *
     * @return mixed
     */
    public function getPageId() {
        return $this->_props['pageId'];
    }

    /**
     * Build page elements
     */
    public function buildPageElements() {
        $this->setPageProperties();
        $this->setScripts();
        $this->setStyles();
        $this->setBacklink();
        $this->setSectionsHtml();
        $this->setCookiesConsent();
        $this->setBackToTop();
        $this->setCanonicalUrl();

        if ($this->_pageView == 'landing') {
            $this->setHeader();
            $this->setFooter();
        }

        $this->_buildedPageElements = true;
    }

    /**
     * Build page header
     */
    public function setHeader() {
        $content = $this->fixImagePaths($this->_config['header']);
        $hideHeader = $this->_props['hideHeader'];
        if ($content && !$hideHeader) {
            $headerItem = json_decode($content, true);
            if ($headerItem) {
                ob_start();
                echo $headerItem['styles'];
                echo $headerItem['php'];
                $publishHeader = ob_get_clean();
                $this->_header = NicepageHelpersNicepage::processSectionsHtml($publishHeader, true, 'header');
            }
        }
    }

    /**
     * Build page footer
     */
    public function setFooter() {
        $content = $this->fixImagePaths($this->_config['footer']);
        $hideFooter = $this->_props['hideFooter'];
        if ($content && !$hideFooter) {
            $footerItem = json_decode($content, true);
            if ($footerItem) {
                ob_start();
                echo $footerItem['styles'];
                echo $footerItem['php'];
                $publishFooter = ob_get_clean();
                $this->_footer = NicepageHelpersNicepage::processSectionsHtml($publishFooter, true, 'footer');
            }
        }
    }

    /**
     * Get page header
     *
     * @return string
     */
    public function getHeader() {
        return $this->_header;
    }

    /**
     * Get page footer
     *
     * @return string
     */
    public function getFooter() {
        return $this->_footer;
    }

    /**
     * Build page
     */
    public function prepare() {
        $isBlog = $this->_context === 'com_content.featured' || $this->_context === 'com_content.category';
        if ($isBlog) {
            $introImgStruct = isset($this->_props['introImgStruct']) ? $this->_props['introImgStruct'] : '';
            if ($introImgStruct) {
                $this->_row->pageIntroImgStruct = json_decode($this->fixImagePaths($introImgStruct), true);
            }
        } else {
            $this->buildPageElements();
            $content = "<!--np_content-->" . $this->getSectionsHtml() . $this->getEditLinkHtml() . "<!--/np_content-->";
            $content .= "<!--np_page_id-->" . $this->_row->id . "<!--/np_page_id-->";
            $this->_row->introtext = $this->_row->text = $content;
        }
    }

    /**
     * Get page content
     *
     * @param string $pageContent Page content
     *
     * @return mixed|string|string[]|null
     */
    public function get($pageContent) {
        if (!$this->_buildedPageElements) {
            $this->buildPageElements();
        }
        $sectionsHtml = '';
        if (preg_match('/<\!--np\_content-->([\s\S]+?)<\!--\/np\_content-->/', $pageContent, $matches)) {
            $sectionsHtml = $matches[1];
        }
        if ($this->_pageView === 'landing') {
            $pageContent = '<!DOCTYPE html>' .
                '<html>' .
//                '<head>' .
//                JFactory::getDocument()->getBuffer('head') .
				'<head>{{HEAD_TAGS}}' .		// ### Magic3ヘッダタグ埋め込み ###
                '</head>' .
                '<body class="' . $this->getBodyClass() .'" style="' . $this->getBodyStyle() . '">' .
                $this->getHeader() .
                $sectionsHtml .
                $this->getFooter() .
                '</body>' .
                '</html>';
        } else if ($this->_pageView === 'landing_with_header_footer') {
            $bodyStartTag = '<body>';
            $bodyContent = '';
            $bodyEndTag = '</body>';
            if (preg_match('/(<body[^>]+>)([\s\S]*)(<\/body>)/', $pageContent, $matches)) {
                $bodyStartTag = $matches[1];
                $bodyContent = trim($matches[2]);
                $bodyEndTag = $matches[3];
            }

            if ($bodyContent == '') {
                $newPageContent = $bodyStartTag . $sectionsHtml . $bodyEndTag;
            } else {
                $newPageContent = $bodyStartTag;
                if (preg_match('/<header[^>]+>[\s\S]*<\/header>/', $bodyContent, $matches2)) {
                    $newPageContent .= $matches2[0];
                }
                $newPageContent .= $sectionsHtml;
                if (preg_match('/<footer[^>]+>[\s\S]*<\/footer>/', $bodyContent, $matches3)) {
                    $newPageContent .= $matches3[0];
                }
                if (preg_match('/<\/footer>([\s\S]*)/', $bodyContent, $matches4)) {
                    $newPageContent .= $matches4[1];
                }
                $newPageContent .= $bodyEndTag;
            }
            $pageContent = preg_replace('/(<body[^>]+>)([\s\S]*)(<\/body>)/', '[[body]]', $pageContent);
            $pageContent = str_replace('[[body]]', $newPageContent, $pageContent);
        } else {
            $pageContent = preg_replace('/<!--\/?np\_content-->/', '', $pageContent);
        }
        if (strpos($pageContent, '<meta name="viewport"') === false) {
            $pageContent = str_replace('<head>', '<head><meta name="viewport" content="width=device-width, initial-scale=1.0">', $pageContent);
        }
        $pageContent = str_replace('</head>', $this->getStyles() . $this->getScripts() . $this->getCookiesConfirmCode() . '</head>', $pageContent);
        $pageContent = str_replace('</body>', $this->getBacklink() . $this->getCookiesConsent() . $this->getBackToTop() . '</body>', $pageContent);
        $pageCanonical = $this->getCanonicalUrl();
        if ($pageCanonical) {
            if (preg_match('/<link\s+?rel="canonical"\s+?href="[^"]+?"\s*>/', $pageContent, $canonicalMatches)) {
                $pageContent = str_replace($canonicalMatches[0], $pageCanonical, $pageContent);
            } else {
                $pageContent = str_replace('<head>', '<head>' . $pageCanonical, $pageContent);
            }
        }
        return $pageContent;
    }

    /**
     * Add custom page properties
     */
    public function setPageProperties()
    {
        $document = JFactory::getDocument();
        if ($this->_props['metaTags']) {
            $document->addCustomTag($this->_props['metaTags']);
        }
        if ($this->_props['customHeadHtml']) {
            $document->addCustomTag($this->_props['customHeadHtml']);
        }
        if ($this->_props['metaGeneratorContent']) {
            $document->setMetaData('generator', $this->_props['metaGeneratorContent']);
        }
    }

    /**
     * Set plugin scripts
     */
    public function setScripts()
    {
        if ($this->_isNicepageTheme !== '1' || $this->_pageView == 'landing') {
            //$assets = JURI::root(true) . '/components/com_nicepage/assets';
			$assets = JURI::root(true) . '/resource/np/assets';
            if (isset($this->_config['jquery']) && $this->_config['jquery'] == '1') {
                $this->_scripts .= '<script src="' . $assets . '/js/jquery.js"></script>';
            }
            $this->_scripts .= '<script src="' . $assets . '/js/nicepage.js"></script>';
        }
    }

    /**
     * Get plugin scripts
     *
     * @return string
     */
    public function getScripts()
    {
        return $this->_scripts;
    }

    /**
     * Set plugin styles
     */
    public function setStyles()
    {
        //$assets = JURI::root(true) . '/components/com_nicepage/assets';
		$assets = JURI::root(true) . '/resource/np/assets';

        //$siteStyleCss = NicepageHelpersNicepage::buildSiteStyleCss(
		$siteStyleCss = $this->_buildSiteStyleCss(
            $this->_config,
            $this->_props['pageCssUsedIds'],
            $this->_props['publishHtml'],
            $this->_props['pageId']
        );
        $sectionsHead = $this->_props['head'];

        if ($this->_pageView == 'landing') {
            $this->_styles = '<link rel="stylesheet" type="text/css" media="all" href="' . $assets . '/css/nicepage.css" rel="stylesheet" id="nicepage-style-css">';
            $this->_styles .= '<link rel="stylesheet" type="text/css" media="all" href="' . $assets . '/css/media.css" rel="stylesheet" id="theme-media-css">';
            $this->_styles .= $this->_props['fonts'];
            $this->_styles .= '<style>' . $siteStyleCss . $sectionsHead . '</style>';
        } else {

            $autoResponsive = isset($this->_config['autoResponsive']) ? !!$this->_config['autoResponsive'] : true;

            if ($autoResponsive && $this->_isNicepageTheme == '0') {
                $sectionsHead = preg_replace('#\/\*RESPONSIVE_MEDIA\*\/([\s\S]*?)\/\*\/RESPONSIVE_MEDIA\*\/#', '', $sectionsHead);
                $this->_styles .= '<link href="' . $assets . '/css/responsive.css" rel="stylesheet">';
            } else {
                $sectionsHead = preg_replace('#\/\*RESPONSIVE_CLASS\*\/([\s\S]*?)\/\*\/RESPONSIVE_CLASS\*\/#', '', $sectionsHead);
                if ($this->_isNicepageTheme == '0') {
                    $this->_styles .= '<link href="' . $assets . '/css/media.css" rel="stylesheet">';
                }
            }
            $dynamicCss = $siteStyleCss . $sectionsHead;
            if ($this->_isNicepageTheme !== '1') {
                $this->_styles .= '<link href="' . $assets . '/css/page-styles.css" rel="stylesheet">';
                $dynamicCss = $this->wrapStyles($dynamicCss);
            }
            $this->_styles .= $this->_props['fonts'];
            $this->_styles .= '<style id="nicepage-style-css">' . $dynamicCss . '</style>';
        }
    }

    /**
     * Wrap styles by container
     *
     * @param string $dynamicCss Additional styles
     *
     * @return null|string|string[]
     */
    public function wrapStyles($dynamicCss)
    {
        return preg_replace_callback(
            '/([^{}]+)\{[^{}]+?\}/',
            function ($match) {
                $selectors = $match[1];
                $parts = explode(',', $selectors);
                $newSelectors = implode(
                    ',',
                    array_map(
                        function ($part) {
                            if (!preg_match('/html|body|sheet|keyframes/', $part)) {
                                return ' .nicepage-container ' . $part;
                            } else {
                                return $part;
                            }
                        },
                        $parts
                    )
                );
                return str_replace($selectors, $newSelectors, $match[0]);
            },
            $dynamicCss
        );
    }

    /**
     * Get plugin styles
     *
     * @return string
     */
    public function getStyles()
    {
        return $this->_styles;
    }

    /**
     * Set page backlink
     */
    public function setBacklink()
    {
        $backlink = $this->_props['backlink'];
        if ($backlink && ($this->_pageView == 'default' || $this->_pageView === 'landing_with_header_footer')) {
            if ($this->_isNicepageTheme !== '1') {
                $backlink = '<div class="nicepage-container"><div class="'. $this->_props['bodyClass'] .'">' . $backlink . '</div></div>';
            } else {
                $backlink = '';
            }
        }
        $this->_backlink = $backlink;
    }

    /**
     * Get page backlink
     *
     * @return string
     */
    public function getBacklink()
    {
        return $this->_backlink;
    }

    /**
     * Set sections html
     */
    public function setSectionsHtml()
    {
        //$this->_sectionsHtml = NicepageHelpersNicepage::processSectionsHtml($this->_props['publishHtml'], true, $this->_props['pageId']);
		$this->_sectionsHtml = $this->_props['publishHtml'];

        if ($this->_pageView == 'landing') {
            return;
        }
/*
        $autoResponsive = isset($this->_config['autoResponsive']) ? !!$this->_config['autoResponsive'] : true;
        if ($autoResponsive && $this->_isNicepageTheme == '0') {
            $responsiveScript = <<<SCRIPT
<script>
    (function ($) {
        var ResponsiveCms = window.ResponsiveCms;
        if (!ResponsiveCms) {
            return;
        }
        ResponsiveCms.contentDom = $('script:last').parent();
        
        if (typeof ResponsiveCms.recalcClasses === 'function') {
            ResponsiveCms.recalcClasses();
        }
    })(jQuery);
</script>
SCRIPT;
            $this->_sectionsHtml = $responsiveScript . $this->_sectionsHtml;
        }

        if ($this->_isNicepageTheme === '0') {
            $this->_sectionsHtml = '<div class="nicepage-container"><div style="' . $this->_props['bodyStyle'] . '" class="'. $this->_props['bodyClass'] .'">' . $this->_sectionsHtml . '</div></div>';
        } else {
            $bodyScript = <<<SCRIPT
<script>
var body = document.body;
    
    body.className += " {$this->_props['bodyClass']}";
    body.style.cssText += " {$this->_props['bodyStyle']}";
</script>
SCRIPT;
            $this->_sectionsHtml = $bodyScript . $this->_sectionsHtml;
        }*/
    }

    /**
     * Get sections html
     *
     * @return string
     */
    public function getSectionsHtml()
    {
        return $this->_sectionsHtml;
    }

    /**
     * Set page cookies consent
     */
    public function setCookiesConsent()
    {
        if ($this->_isNicepageTheme === '1' && $this->_pageView !== 'landing') {
            return;
        }

        if (isset($this->_config['cookiesConsent'])) {
            $cookiesConsent = json_decode($this->_config['cookiesConsent'], true);
            if ($cookiesConsent && (!$cookiesConsent['hideCookies'] || $cookiesConsent['hideCookies'] === 'false')) {
                $content = $this->fixImagePaths($cookiesConsent['publishCookiesSection']);
                if ($this->_pageView == 'landing') {
                    $this->_cookiesConsent = $content;
                } else {
                    $this->_cookiesConsent = '<div class="nicepage-container"><div class="' . $this->_props['bodyClass'] . '">' . $content . '</div></div>';
                }
                $this->_cookiesConfirmCode = $cookiesConsent['cookieConfirmCode'];
            }
        }
    }

    /**
     * Get page cookies consent
     *
     * @return string
     */
    public function getCookiesConsent()
    {
        return $this->_cookiesConfirmCode;
    }

    /**
     * Get page cookies confirm code
     *
     * @return string
     */
    public function getCookiesConfirmCode()
    {
        return $this->_cookiesConsent;
    }

    /**
     * Set backtotop in content
     */
    public function setBackToTop() {
        $hideBackToTop = $this->_props['hideBackToTop'];
        if (isset($this->_config['backToTop']) && !$hideBackToTop) {
            if ($this->_pageView == 'landing') {
                $this->_backToTop = $this->_config['backToTop'];
            } else {
                $this->_backToTop = '<div class="nicepage-container"><div class="' . $this->_props['bodyClass'] . '">' . $this->_config['backToTop'] . '</div></div>';
            }
        }
    }

    /**
     * Get page backlink
     *
     * @return string
     */
    public function getBackToTop()
    {
        return $this->_backToTop;
    }

    /**
     * Set canonical url
     */
    public function setCanonicalUrl() {
        $this->_canonicalUrl = $this->_props['canonical'];
    }

    /**
     * @return string
     */
    public function getCanonicalUrl() {
        $canonical = $this->_canonicalUrl;
        if (!$canonical && $this->_pageView == 'landing') {
            $canonical = JUri::getInstance()->toString();
        }
        return $canonical ? '<link rel="canonical" href="' . $canonical . '">' : '';
    }

    /**
     * Get page view
     *
     * @return mixed|string
     */
    public function getPageView() {
        return $this->_pageView;
    }

    /**
     * Get body style
     *
     * @return mixed
     */
    public function getBodyStyle() {
        return $this->_props['bodyStyle'];
    }

    /**
     * Get body class
     *
     * @return mixed
     */
    public function getBodyClass() {
        return $this->_props['bodyClass'];
    }

    /**
     * Get edit link html
     *
     * @return string
     */
    public function getEditLinkHtml() {
        $html = '';
        $adminUrl = JURI::root() . '/administrator';
        $icon = dirname($adminUrl) . '/components/com_nicepage/assets/images/button-icon.png?r=' . md5(mt_rand(1, 100000));
        $link = $adminUrl . '/index.php?option=com_nicepage&task=nicepage.autostart&postid=' . $this->_row->id;
        if ($this->_params->get('access-edit')) {
            $html= <<<HTML
        <div><a href="$link" target="_blank" class="edit-nicepage-button">Edit Page</a></div>
        <style>
            a.edit-nicepage-button {
                position: fixed;
                top: 0;
                right: 0;
                background: url($icon) no-repeat 5px 6px;
                background-size: 16px;
                color: #4184F4;
                font-family: Georgia;
                margin: 10px;
                display: inline-block;
                padding: 5px 5px 5px 25px;
                font-size: 14px;
                line-height: 18px;
                background-color: #fff;
                border-radius: 3px;
                border: 1px solid #eee;
                z-index: 9999;
                text-decoration: none;
            }
            a.edit-nicepage-button:hover {
                color: #BC5A5B;
            }
        </style>
HTML;
        }
        return $html;
    }

    /**
     * Prepare page props
     *
     * @param array  $props          Page props
     * @param array  $comConfig      Plg config
     * @param string $articleContext Article context
     * @param object $article        Article
     * @param object $articleParams  Article params
     *
     * @return mixed
     */
    public function prepareProps($props, $comConfig, $articleContext, $article, $articleParams) {
        // Process image paths
        $props['publishHtml'] = $this->fixImagePaths($props['publishHtml']);
        $props['head']        = $this->fixImagePaths($props['head']);
        $props['bodyStyle']   = $this->fixImagePaths($props['bodyStyle']);
        $props['fonts']       = $this->fixImagePaths($props['fonts']);

        // Process backlink
		// バックリンクは表示しない
        /*if ($comConfig) {
            $hideBacklink = isset($comConfig['hideBacklink']) ? (bool)$comConfig['hideBacklink'] : false;
            $backlink = $props['backlink'];
            $props['backlink'] = $hideBacklink ? str_replace('u-backlink', 'u-backlink u-hidden', $backlink) : $backlink;
        }*/

        // Process content
        if ($article) {
            $article->doubleCall = true;
            $currentText = $article->text;
            $currentPostId = $article->id;
            $article->text = $props['publishHtml'];
            $article->id = '-1';
            JPluginHelper::importPlugin('content');
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onContentPrepare', array($articleContext, &$article, &$articleParams, 0));
            $props['publishHtml'] = $article->text;
            $article->text = $currentText;
            $article->id = $currentPostId;
        }
        $props['bodyClass']   = isset($props['bodyClass']) ? $props['bodyClass'] : '';
        $props['bodyStyle']   = isset($props['bodyStyle']) ? $props['bodyStyle'] : '';
        $props['head']        = isset($props['head']) ? $props['head'] : '';
        $props['fonts']       = isset($props['fonts']) ? $props['fonts'] : '';
        $props['publishHtml'] = isset($props['publishHtml']) ? $props['publishHtml'] : '';

        $props['backlink']       = isset($props['backlink']) ? $props['backlink'] : '';
        $props['pageCssUsedIds'] = isset($props['pageCssUsedIds']) ? $props['pageCssUsedIds'] : '';
        $props['hideHeader']     = isset($props['hideHeader']) ? $props['hideHeader'] : false;
        $props['hideFooter']     = isset($props['hideFooter']) ? $props['hideFooter'] : false;
        $props['hideBackToTop']  = isset($props['hideBackToTop']) ? $props['hideBackToTop'] : false;

        $props['metaTags']       = isset($props['metaTags']) ? $props['metaTags'] : '';
        $props['customHeadHtml'] = isset($props['customHeadHtml']) ? $props['customHeadHtml'] : '';
        $props['metaGeneratorContent'] = isset($props['metaGeneratorContent']) ? $props['metaGeneratorContent'] : '';
        $props['canonical'] = isset($props['canonical']) ? $props['canonical'] : '';

        return $props;
    }

    /**
     * Fix image paths
     *
     * @param string $content Content
     *
     * @return mixed
     */
    public function fixImagePaths($content) {
		global $gEnvManager;
		
        //return str_replace('[[site_path_live]]', JURI::root(), $content);
		return str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $gEnvManager->getRootUrl(), $content);
    }

    /**
     * Get page instance
     *
     * @param null   $pageId  Page id
     * @param string $context Component context
     * @param null   $row     Component row
     * @param null   $params  Component params
     *
     * @return NpPage
     */
    public static function getInstance($pageId, $context, &$row, &$params)
    {
        $pageTable = NicepageHelpersNicepage::getSectionsTable();
        if (!$pageTable->load(array('page_id' => $pageId))) {
            return null;
        }

        if (!is_object(self::$_instance)) {
            self::$_instance = new self($pageTable, $context, $row, $params);
        }

        return self::$_instance;
    }
	/***************************************************************************
	以下NicepageHelpersNicepageクラスからの移植
	****************************************************************************/
	
    /**
     * Build site style css
     *
     * @param array  $params         plugin config parameters
     * @param string $pageCssUsedIds used css ids
     * @param string $html           public html of page
     * @param string $pageId         id of page
     *
     * @return string
     */
    public static function _buildSiteStyleCss($params, $pageCssUsedIds, $html, $pageId)
    {
        $result = isset($params['siteStyleCss']) ? $params['siteStyleCss'] : '';
        $partsJson = isset($params['siteStyleCssParts']) ? $params['siteStyleCssParts'] : '';
        if ($partsJson) {
            $cssParts = json_decode($partsJson, true);
            if (!$pageCssUsedIds) {
                $usedIds = self::processUsedColors($cssParts, $html);
                self::updateUsedColor($usedIds, $pageId);
            } else {
                $usedIds = json_decode($pageCssUsedIds, true);
            }
            $headerFooterCssUsedIds = isset($params['headerFooterCssUsedIds']) ? json_decode($params['headerFooterCssUsedIds'], true) : [];
            $cookiesCssUsedIds = isset($params['cookiesCssUsedIds']) ? json_decode($params['cookiesCssUsedIds'], true) : [];
            $result   = '';
            foreach ($cssParts as $part) {
                if ($part['type'] !== 'color' || !empty($usedIds[$part['id']]) || !empty($headerFooterCssUsedIds[$part['id']]) || !empty($cookiesCssUsedIds[$part['id']])) {
                    $result .= $part['css'];
                }
            }
        }
        return $result;
    }
}