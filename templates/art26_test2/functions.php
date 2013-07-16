<?php
defined('_JEXEC') or die;

if (!defined('_ARTX_FUNCTIONS')) {

    define('_ARTX_FUNCTIONS', 1);
    
    $GLOBALS['artx_settings'] = array(
        'block' => array('has_header' => true),
        'menu' => array('show_submenus' => true),
        'vmenu' => array('show_submenus' => true, 'simple' => false)
    );
    
    /**
     * Base class with index.php view routines. Contains method for placing positions,
     * calculating classes, decorating components. Version-specific routines are defined
     * in subclasses: ArtxPage15 and ArtxPage16.
     */
    abstract class ArtxPageView
    {

        public $page;

        protected function __construct($page) {
            $this->page = $page;
        }
        
        /**
         * Returns version-specific body class: joomla15 or joomla16.
         *
         * Example:
         *  <body class="joomla15">
         */
        abstract function bodyClass();
        
        /**
         * Checks whether Joomla! has system messages to display.
         */
        abstract function hasMessages();

        /**
         * Returns true when one of the given positions contains at least one module.
         * Example:
         *  if ($obj->containsModules('top1', 'top2', 'top3')) {
         *   // the following code will be executed when one of the positions contain modules:
         *   ...
         *  }
         */
        function containsModules()
        {
            foreach (func_get_args() as $position)
                if (0 != $this->page->countModules($position))
                    return true;
            return false;
        }

        /**
         * Builds list of positions, collapsing the empty ones.
         *
         * Samples:
         *  Four positions:
         *   No empty positions: 25%:25%:25%:25%
         *   With one empty positions: -:50%:25%:25%, 50%:-:25%:25%, 25%:50%:-:25%, 25%:25%:50%:-
         *   With two empty positions: -:-:75%:25%, -:50%:-:50%, -:50%:50%:-, -:50%:50%:-, 75%:-:-:25%, 50%:-:50%:-, 25%:75%:-:-
         *   One non-empty position: 100%
         *  Three positions:
         *   No empty positions: 33%:33%:34%
         *   With one empty position: -:66%:34%, 50%:-:50%, 33%:67%:-
         *   One non-empty position: 100%
         */
        function positions($positions, $style) {
            // Build $cells by collapsing empty positions:
            $cells = array();
            $buffer = 0;
            $cell = null;
            foreach ($positions as $name => $width) {
                if ($this->containsModules($name)) {
                    $cells[$name] = $buffer + $width;
                    $buffer = 0;
                    $cell = $name;
                } else if (null == $cell)
                    $buffer += $width;
                else
                    $cells[$cell] += $width;
            }

            // Backward compatibility:
            //  For three equal width columns with empty center position width should be 50/50:
            if (3 == count($positions) && 2 == count($cells)) {
                $columns1 = array_keys($positions);
                $columns2 = array_keys($cells);
                if (33 == $positions[$columns1[0]] && 33 == $positions[$columns1[1]] && 34 == $positions[$columns1[2]]
                    && $columns2[0] == $columns1[0] && $columns2[1] == $columns1[2])
                    $cells[$columns2[0]] = 50;
                    $cells[$columns2[1]] = 50;
            }

            // Render $cells:
            if (count($cells) == 0)
                return '';
            if (count($cells) == 1)
                foreach ($cells as $name => $width)
                    return $this->position($name, $style);
            $result = '<table class="position" cellpadding="0" cellspacing="0" border="0">';
            $result .= '<tr valign="top">';
            foreach ($cells as $name => $width)
                $result .='<td width="' . $width. '%">' . $this->position($name, $style) . '</td>';
            $result .= '</tr>';
            $result .= '</table>';
            return $result;
        }

        function position($position, $style = null)
        {
            return '<jdoc:include type="modules" name="' . $position . '"' . (null != $style ? ' style="artstyle" artstyle="' . $style . '"' : '') . ' />';
        }

        /**
         * Preprocess component content before printing it.
         */
        function componentWrapper()
        {
        }

        /**
         * Checks layout sidebar cells and returns content cell class that expands content cell over empty sidebar.
         */
        function contentCellClass($cells)
        {
            $nonEmptyCells = array();
            $emptyCells = array();
            foreach ($cells as $name => $class) {
                if ('content' == $name)
                    continue;
                if ($this->containsModules($name))
                    $nonEmptyCells[] = $class;
                else
                    $emptyCells[] = $class;
            }
            switch (count($emptyCells)) {
                case 2:
                    return 'content-wide';
                case 1:
                    if (count($nonEmptyCells))
                        return 'content-' . $emptyCells[0];
                    return 'content';
            }
            return 'content';
        }
    }
    
    class ArtxPage15 extends ArtxPageView
    {
        public function __construct($page) {
            parent::__construct($page);
        }
    
        function bodyClass() {
            return 'joomla15';
        }

        function hasMessages()
        {
            global $mainframe;
            $messages = $mainframe->getMessageQueue();
            if (is_array($messages) && count($messages))
                foreach ($messages as $msg)
                    if (isset($msg['type']) && isset($msg['message']))
                        return true;
            return false;
        }
        
        /**
         * Wraps component content into article style unless it is not wrapped already.
         *
         * The componentWrapper method gets the content of the 'component' buffer and search for the '<div class="art-post">' string in it.
         * Then it replaces the componentheading DIV tag with span (to fix the w3.org validation) and replaces content of the buffer with
         * wrapped content.
         */
        function componentWrapper()
        {
            if ($this->page->getType() != 'html')
                return;
            $option = JRequest::getCmd('option');
            $view = JRequest::getCmd('view');
            $layout = JRequest::getCmd('layout');
            if ('com_content' == $option && ('frontpage' == $view || 'article' == $view || ('category' == $view && 'blog' == $layout)))
                    return;
            $content = $this->page->getBuffer('component');
            if (false === strpos($content, '<div class="art-post')) {
                $title = null;
                if (preg_match('~<div\s+class="(componentheading[^"]*)"([^>]*)>([^<]+)</div>~', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $content = substr($content, 0, $matches[0][1]) . substr($content, $matches[0][1] + strlen($matches[0][0]));
                    $title = '<span class="' . $matches[1][0] . '"' . $matches[2][0] . '>' . $matches[3][0] . '</span>';
                }
                $this->page->setBuffer(artxPost(array('header-text' => $title, 'content' => $content)), 'component');
            }
        }
    }
    
    class ArtxPage16 extends ArtxPageView
    {
        public function __construct($page) {
            parent::__construct($page);
        }
        
        function bodyClass() {
            return 'joomla16';
        }

        function hasMessages()
        {
            $messages = JFactory::getApplication()->getMessageQueue();
            if (is_array($messages) && count($messages))
                foreach ($messages as $msg)
                    if (isset($msg['type']) && isset($msg['message']))
                        return true;
            return false;
        }
		
        /**
         * Wraps component content into article style unless it is not wrapped already.
         *
         * The componentWrapper method gets the content of the 'component' buffer and search for the '<div class="art-post">' string in it.
         * Then it wraps content of the buffer with art-post.
         */
        function componentWrapper()
        {
            if ($this->page->getType() != 'html')
                return;
            $option = JRequest::getCmd('option');
            $view = JRequest::getCmd('view');
            $layout = JRequest::getCmd('layout');
            if ('com_content' == $option && ('featured' == $view || 'article' == $view || ('category' == $view && 'blog' == $layout)))
                    return;
            $content = $this->page->getBuffer('component');
            if (false === strpos($content, '<div class="art-post'))
                $this->page->setBuffer(artxPost(array('header-text' => null, 'content' => $content)), 'component');
        }
    }
    
    /**
     * Base class with content page routines for rendering page header and article factory.
     */
    abstract class ArtxContentView
    {
        protected $_component;
        protected $_componentParams;
        
        /**
         * Component page class suffix.
         * @var string
         */
        public $pageClassSfx;
        
        /**
         * @var boolean
         */
        public $showPageHeading;
        
        /**
         * Page heading (or page title).
         * @var string
         */
        public $pageHeading;
        
        protected function __construct($component, $params)
        {
            $this->_component = $component;
            $this->_componentParams = $params;
        }
        
        abstract function pageHeading($heading = null);
        abstract function article($article, $print);
        abstract function articleListItem($item);
        
        public function beginPageContainer($class)
        {
            return '<div class="' . $class . $this->pageClassSfx .'">';
        }
        
        public function endPageContainer()
        {
            return '</div>';
        }
    }
    
    class ArtxContent15 extends ArtxContentView
    {
        public function __construct($component, $params)
        {
            parent::__construct($component, $params);
            $this->pageClassSfx = $this->_componentParams->get('pageclass_sfx');
            $this->showPageHeading = $this->_componentParams->def('show_page_title', 1);
            $this->pageHeading = $this->showPageHeading ? $this->_componentParams->get('page_title') : '';
        }
        
        function pageHeading($heading = null)
        {
            return artxPost(array('header-text' => null == $heading ? $this->pageHeading : $heading));
        }
        
        function article($article, $print)
        {
            return new ArtxContentArticleView15($this->_component, $this->_componentParams, $article, $print);
        }
        
        function articleListItem($item)
        {
            return new ArtxContentFrontpageItemView15($this->_component, $this->_componentParams, $item);
        }
    }
    
    class ArtxContent16 extends ArtxContentView
    {
        public function __construct($component, $params)
        {
            parent::__construct($component, $params);
            $this->pageClassSfx = $this->_component->pageclass_sfx;
            $this->showPageHeading = $this->_componentParams->def('show_page_heading', 1);
            $this->pageHeading = $this->showPageHeading ? $this->_componentParams->get('page_heading') : '';
        }
        
        function pageHeading($heading = null)
        {
            return artxPost(array('header-text' => null == $heading ? $this->pageHeading : $heading));
        }
        
        function article($article, $print)
        {
            return new ArtxContentArticleView16($this->_component, $this->_componentParams, $article, $print);
        }
        
        function articleListItem($item)
        {
            return new ArtxContentFeaturedItemView16($this->_component, $this->_componentParams, $item);
        }
    }

    abstract class ArtxContentGeneralArticleView
    {
        protected $_component;
        protected $_componentParams;
        protected $_article;
        
        public $params;
        public $isPublished;
        public $canEdit;
        public $title;
        public $titleVisible;
        public $titleLink;
        public $hits;
        public $print;
        public $showCreateDate;
        public $showModifyDate;
        public $showPublishDate;
        public $showAuthor;
        public $showPdfIcon;
        public $showPrintIcon;
        public $showEmailIcon;
        public $showHits;
        public $showUrl;
        public $showIntro;
        public $showReadmore;
        public $showParentCategory;
        public $parentCategoryLink;
        public $parentCategory;
        public $showCategory;
        public $categoryLink;
        public $category;
        
        protected function __construct($component, $componentParams, $article)
        {
            // Initialization:
            $this->_component = $component;
            $this->_componentParams = $componentParams; 
            $this->_article = $article;
            // Calculate properties:
            $this->isPublished = 0 != $this->_article->state;
        }
        
        public function introText() { return ''; }
        public function createDateInfo() { return ''; }
        public function modifyDateInfo() { return ''; }
        public function publishDateInfo() { return ''; }
        public function authorInfo() { return ''; }
        public function hitsInfo() { return ''; }
        public function pdfIcon() { return ''; }
        public function emailIcon() { return ''; }
        public function editIcon() { return ''; }
        public function printPopupIcon() { return ''; }
        public function printScreenIcon() { return ''; }
        public function readmore() { return ''; }
        
        public function beginUnpublishedArticle() { return '<div class="system-unpublished">'; }
        public function endUnpublishedArticle() { return '</div>'; }
        public function articleSeparator() { return '<div class="item-separator"></div>'; }
        
        public function categories()
        {
            $showParentCategory = $this->showParentCategory && !empty($this->parentCategory);
            $showCategory = $this->showCategory && !empty($this->category);
            $result = JText::_('Category') . ': ';
            if ($showParentCategory) {
                $result .= '<span class="art-post-metadata-category-parent">';
                $title = $this->_component->escape($this->parentCategory);
                if (!empty($this->parentCategoryLink))
                    $result .= '<a href="' . $this->parentCategoryLink . '">' . $title . '</a>';
                else
                    $result .= $title;
                $result .= '</span>';
            }
            if ($showParentCategory && $showCategory)
                $result .= ' / ';
            if ($showCategory) {
                $result .= '<span class="art-post-metadata-category-name">';
                $title = $this->_component->escape($this->category);
                if (!empty($this->categoryLink))
                    $result .= '<a href="' . $this->categoryLink . '">' . $title . '</a>';
                else
                    $result .= $title;
                $result .= '</span>';
            }
            return $result;
        }
        
        public function urlInfo()
        {
            return '<a href="http://' . $this->_component->escape($this->_article->urls) . '" target="_blank">'
                . $this->_component->escape($this->_article->urls) . '</a>';
        }
        
        public function getArticleViewParameters()
        {
            return array('metadata-header-icons' => array(), 'metadata-footer-icons' => array());
        }
        
        public function event($name)
        {
            return $this->_article->event->{$name};
        }
        
        public function article($article)
        {
            return artxPost($article);
        }
        
        public function content()
        {
            return (isset($this->_article->toc) ? $this->_article->toc : '') . "<div class=\"art-article\">" . $this->_article->text . "</div>";
        }
        
    }

    class ArtxContentArticleView15 extends ArtxContentGeneralArticleView
    {
        function __construct($component, $componentParams, $article, $print)
        {
            parent::__construct($component, $componentParams, $article);
            
            $this->print = $print;
            $this->canEdit = $this->_component->user->authorize('com_content', 'edit', 'content', 'all')
                || $this->_component->user->authorize('com_content', 'edit', 'content', 'own');
            $this->title = $this->_article->title;
            $this->titleVisible = $this->_componentParams->get('show_title') && strlen($this->title);
            $this->titleLink = $this->_componentParams->get('link_titles') && '' != $this->_article->readmore_link
                ? $this->_article->readmore_link : '';
            $this->showCreateDate = $this->_componentParams->get('show_create_date');
            $this->showModifyDate = 0 != intval($this->_article->modified) && $this->_componentParams->get('show_modify_date');
            $this->showPublishDate = false; // - not available in J! 1.5
            $this->showAuthor = $this->_componentParams->get('show_author') && '' != $this->_article->author;
            $this->showPdfIcon = $this->_componentParams->get('show_pdf_icon');
            $this->showPrintIcon = $this->_componentParams->get('show_print_icon');
            $this->showEmailIcon = $this->_componentParams->get('show_email_icon');
            $this->showHits = false; // - not available in J! 1.5
            $this->showUrl = $this->_componentParams->get('show_url') && $this->_article->urls;
            $this->showIntro = $this->_componentParams->get('show_intro');
            $this->showReadmore = false; // - no readmore in article
            
            $this->showParentCategory = $this->_componentParams->get('show_section') && $this->_article->sectionid && isset($this->_article->section);
            $this->parentCategory = $this->showParentCategory ? $this->_article->section : '';
            $this->parentCategoryLink = ($this->showParentCategory && $this->_componentParams->get('link_section'))
                ? JRoute::_(ContentHelperRoute::getSectionRoute($this->_article->sectionid)) : '';
            $this->showCategory = $this->_componentParams->get('show_category') && $this->_article->catid;
            $this->category = $this->showCategory ? $this->_article->category : '';
            $this->categoryLink = ($this->showCategory && $this->_componentParams->get('link_category'))
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug, $this->_article->sectionid)) : '';
        }
        
        public function createDateInfo()
        {
            return JHTML::_('date', $this->_article->created, JText::_('DATE_FORMAT_LC2'));
        }
        
        public function modifyDateInfo()
        {
            return JText::sprintf('LAST_UPDATED2', JHTML::_('date', $this->_article->modified, JText::_('DATE_FORMAT_LC2')));
        }
        
        public function authorInfo()
        {
            return JText::sprintf('Written by', $this->_component->escape($this->_article->created_by_alias
                ? $this->_article->created_by_alias : $this->_article->author));
        }
        
        public function pdfIcon()
        {
            return JHTML::_('icon.pdf', $this->_article, $this->_componentParams, $this->_component->access);
        }
        
        public function emailIcon()
        {
            return JHTML::_('icon.email', $this->_article, $this->_componentParams, $this->_component->access);
        }

        public function editIcon()
        {
            return JHTML::_('icon.edit', $this->_article, $this->_componentParams, $this->_component->access);
        }

        public function printPopupIcon()
        {
            return JHTML::_('icon.print_popup', $this->_article, $this->_componentParams, $this->_component->access);
        }
        
        public function printScreenIcon()
        {
            return JHtml::_('icon.print_screen', $this->_article, $this->_componentParams, $this->_component->access);
        }
    }

    class ArtxContentArticleView16 extends ArtxContentGeneralArticleView
    {
        function __construct($component, $componentParams, $article, $print)
        {
            parent::__construct($component, $componentParams, $article);
            
            $this->print = $print;
            $this->canEdit = $this->_article->params->get('access-edit');
            $this->title = $this->_article->title;
            $this->titleVisible = $this->_article->params->get('show_title') || $this->_article->params->get('access-edit');
            $this->titleLink = $this->_article->params->get('link_titles') && !empty($this->_article->readmore_link)
                ? $this->_article->readmore_link : '';
            $this->hits = $this->_article->hits;
            $this->showCreateDate = $this->_article->params->get('show_create_date');
            $this->showModifyDate = $this->_article->params->get('show_modify_date');
            $this->showPublishDate = $this->_article->params->get('show_publish_date');
            $this->showAuthor = $this->_article->params->get('show_author') && !empty($this->_article->author);
            $this->showPdfIcon = false; // - not available in J! 1.6
            $this->showPrintIcon = $this->_article->params->get('show_print_icon');
            $this->showEmailIcon = $this->_article->params->get('show_email_icon');
            $this->showHits = $this->_article->params->get('show_hits');
            $this->showUrl = false; // - not available in J! 1.6
            $this->showIntro = $this->_article->params->get('show_intro');
            $this->showReadmore = false; // - no readmore in article
            
            $this->showParentCategory = $this->_article->params->get('show_parent_category') && $this->_article->parent_slug != '1:root';
            $this->parentCategory = $this->showParentCategory ? $this->_article->parent_title : '';
            $this->parentCategoryLink = ($this->showParentCategory && $this->_article->params->get('link_parent_category') && $this->_article->parent_slug)
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->parent_slug)) : '';
            $this->showCategory = $this->_article->params->get('show_category');
            $this->category = $this->showCategory ? $this->_article->category_title : '';
            $this->categoryLink = ($this->showCategory && $this->_article->params->get('link_category') && $this->_article->catslug)
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug)) : '';
        }
        
        public function createDateInfo()
        {
            return JHtml::_('date', $this->_article->created, JText::_('DATE_FORMAT_LC2'));
        }
        
        public function modifyDateInfo()
        {
            return JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $this->_article->modified, JText::_('DATE_FORMAT_LC2')));
        }
        
        public function publishDateInfo()
        {
            return JText::sprintf('COM_CONTENT_PUBLISHED_DATE', JHtml::_('date', $this->_article->publish_up, JText::_('DATE_FORMAT_LC2')));
        }
        
        public function authorInfo()
        {
            $author = $this->_article->created_by_alias ? $this->_article->created_by_alias : $this->_article->author;
            if (!empty($this->_article->contactid) && $this->_article->params->get('link_author'))
                return JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link',
                    JRoute::_('index.php?option=com_contact&view=contact&id=' . $this->_article->contactid), $author));
            return JText::sprintf('COM_CONTENT_WRITTEN_BY', $author);
        }
        
        public function emailIcon()
        {
            return JHtml::_('icon.email', $this->_article, $this->_article->params);
        }

        public function editIcon()
        {
            return JHtml::_('icon.edit', $this->_article, $this->_article->params);
        }
        
        public function printPopupIcon()
        {
            return JHtml::_('icon.print_popup', $this->_article, $this->_article->params);
        }
        
        public function printScreenIcon()
        {
            return JHtml::_('icon.print_screen', $this->_article, $this->_article->params);
        }
        
        public function hitsInfo()
        {
            return JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->_article->hits);
        }
    }

    /**
     * Based on Joomla 1.5.22.
     */
    class ArtxContentFrontpageItemView15 extends ArtxContentGeneralArticleView
    {
        function __construct($component, $componentParams, $article)
        {
            parent::__construct($component, $componentParams, $article);
            
            $this->canEdit = $this->_component->user->authorize('com_content', 'edit', 'content', 'all')
                || $this->_component->user->authorize('com_content', 'edit', 'content', 'own');
            $this->title = $this->_article->title;
            $this->titleVisible = $this->_article->params->get('show_title') && strlen($this->title);
            $this->titleLink = $this->_article->params->get('link_titles') && '' != $this->_article->readmore_link
                ? $this->_article->readmore_link : '';
            $this->showCreateDate = $this->_article->params->get('show_create_date');
            $this->showModifyDate = 0 != intval($this->_article->modified) && $this->_article->params->get('show_modify_date');
            $this->showPublishDate = false; // - not available in J! 1.5
            $this->showAuthor = $this->_article->params->get('show_author') && '' != $this->_article->author;
            $this->showPdfIcon = $this->_article->params->get('show_pdf_icon');
            $this->showPrintIcon = $this->_article->params->get('show_print_icon');
            $this->showEmailIcon = $this->_article->params->get('show_email_icon');
            $this->showHits = false; // - not available in J! 1.5
            $this->showUrl = $this->_componentParams->get('show_url') && $this->_article->urls;
            $this->showIntro = $this->_article->params->get('show_intro');
            $this->showReadmore = $this->_article->params->get('show_readmore') && $this->_article->readmore;
            
            $this->showParentCategory = $this->_article->params->get('show_section') && $this->_article->sectionid && isset($this->_article->section);
            $this->parentCategory = $this->showParentCategory ? $this->_article->section : '';
            $this->parentCategoryLink = ($this->showParentCategory && $this->_article->params->get('link_section'))
                ? JRoute::_(ContentHelperRoute::getSectionRoute($this->_article->sectionid)) : '';
            $this->showCategory = $this->_article->params->get('show_category') && $this->_article->catid;
            $this->category = $this->showCategory ? $this->_article->category : '';
            $this->categoryLink = ($this->showCategory && $this->_article->params->get('link_category'))
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug, $this->_article->sectionid)) : '';
        }
        
        public function createDateInfo()
        {
            return JHTML::_('date', $this->_article->created, JText::_('DATE_FORMAT_LC2'));
        }
        
        public function modifyDateInfo()
        {
            return JText::sprintf('LAST_UPDATED2', JHTML::_('date', $this->_article->modified, JText::_('DATE_FORMAT_LC2')));
        }
        
        public function authorInfo()
        {
            return JText::sprintf('Written by', $this->_component->escape($this->_article->created_by_alias
                ? $this->_article->created_by_alias : $this->_article->author));
        }
        
        public function pdfIcon()
        {
            return JHTML::_('icon.pdf', $this->_article, $this->_article->params, $this->_component->access);
        }
        
        public function emailIcon()
        {
            return JHTML::_('icon.email', $this->_article, $this->_article->params, $this->_component->access);
        }
        
        public function editIcon()
        {
            return JHTML::_('icon.edit', $this->_article, $this->_article->params, $this->_component->access);
        }

        public function printPopupIcon()
        {
            return JHTML::_('icon.print_popup', $this->_article, $this->_article->params, $this->_component->access);
        }
        
        public function introText()
        {
            return "<div class=\"art-article\">" . $this->_article->text . "</div>";
        }
        
        public function readmore()
        {
            if ($this->_article->readmore_register)
                $text = JText::_('Register to read more...');
            elseif ($readmore = $this->_article->params->get('readmore'))
                $text = $readmore;
            else
                $text = JText::sprintf('Read more...');
            return '<p class="readmore">' . artxLinkButton(array(
                'classes' => array('a' => 'readon'),
                'link' => $this->_article->readmore_link,
                'content' => str_replace(' ', '&#160;', $text))) . '</p>';
        }
    }
    
    /**
     * Based on Joomla 1.6 RC1.
     */
    class ArtxContentFeaturedItemView16 extends ArtxContentGeneralArticleView
    {
        function __construct($component, $componentParams, $article)
        {
            parent::__construct($component, $componentParams, $article);
            
            $this->canEdit = $this->_article->params->get('access-edit');
            $this->title = $this->_article->title;
            $this->titleVisible = $this->_article->params->get('show_title') && strlen($this->title);
            $this->titleLink = $this->_article->params->get('link_titles') && $this->_article->params->get('access-view')
                ? JRoute::_(ContentHelperRoute::getArticleRoute($this->_article->slug, $this->_article->catid)) : '';
            $this->hits = $this->_article->hits;
            $this->showCreateDate = $this->_article->params->get('show_create_date');
            $this->showModifyDate = $this->_article->params->get('show_modify_date');
            $this->showPublishDate = $this->_article->params->get('show_publish_date');
            $this->showAuthor = $this->_article->params->get('show_author') && !empty($this->_article->author);
            $this->showPdfIcon = false; // - not available in J! 1.6
            $this->showPrintIcon = $this->_article->params->get('show_print_icon');
            $this->showEmailIcon = $this->_article->params->get('show_email_icon');
            $this->showHits = $this->_article->params->get('show_hits');
            $this->showUrl = false; // - not available in J! 1.6
            $this->showIntro = $this->_article->params->get('show_intro');
            $this->showReadmore = $this->_article->params->get('show_readmore') && $this->_article->readmore;

            // Because category blog layout view does not support catslug:
            if (!isset($this->_article->catslug))
                $this->_article->catslug = $this->_article->category_alias ? ($this->_article->catid . ':' . $this->_article->category_alias) : $this->_article->catid;
            if (!isset($this->_article->parent_slug))
                $this->_article->parent_slug = $this->_article->parent_alias ? ($this->_article->parent_id . ':' . $this->_article->parent_alias) : $this->_article->parent_id;

            $this->showParentCategory = $this->_article->params->get('show_parent_category');
            $this->parentCategory = $this->showParentCategory ? $this->_article->parent_title : '';
            $this->parentCategoryLink = ($this->showParentCategory && $this->_article->params->get('link_parent_category') && $this->_article->parent_slug)
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->parent_slug)) : '';
            $this->showCategory = $this->_article->params->get('show_category');
            $this->category = $this->showCategory ? $this->_article->category_title : '';
            $this->categoryLink = ($this->showCategory && $this->_article->params->get('link_category') && $this->_article->catslug)
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug)) : '';
        }
        
        public function createDateInfo()
        {
            return JHtml::_('date', $this->_article->created, JText::_('DATE_FORMAT_LC2'));
        }
        
        public function modifyDateInfo()
        {
            return JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $this->_article->modified, JText::_('DATE_FORMAT_LC2')));
        }
        
        public function publishDateInfo()
        {
            return JText::sprintf('COM_CONTENT_PUBLISHED_DATE', JHtml::_('date', $this->_article->publish_up, JText::_('DATE_FORMAT_LC2')));
        }
        
        public function authorInfo()
        {
            $author = $this->_article->created_by_alias ? $this->_article->created_by_alias : $this->_article->author;
            if (!empty($this->_article->contactid) && $this->_article->params->get('link_author'))
                return JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link',
                    JRoute::_('index.php?option=com_contact&view=contact&id=' . $this->_article->contactid), $author));
            return JText::sprintf('COM_CONTENT_WRITTEN_BY', $author);
        }
        
        public function emailIcon()
        {
            return JHtml::_('icon.email', $this->_article, $this->_article->params);
        }
        
        public function editIcon()
        {
            return JHtml::_('icon.edit', $this->_article, $this->_article->params);
        }
        
        public function printPopupIcon()
        {
            return JHtml::_('icon.print_popup', $this->_article, $this->_article->params);
        }
        
        public function hitsInfo()
        {
            return JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->_article->hits);
        }
        
        public function introText()
        {
            return "<div class=\"art-article\">" . $this->_article->introtext . "</div>";
        }
        
        public function readmore()
        {
            if ($this->_article->params->get('access-view')) {
                $link = JRoute::_(ContentHelperRoute::getArticleRoute($this->_article->slug, $this->_article->catid));
            } else {
                $menu = JFactory::getApplication()->getMenu();
                $active = $menu->getActive();
                $itemId = $active->id;
                $link1 = JRoute::_('index.php?option=com_users&view=login&&Itemid=' . $itemId);
                $returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($this->_article->slug, $this->_article->catid));
                $link = new JURI($link1);
                $link->setVar('return', base64_encode($returnURL));
            }
            if (!$this->_article->params->get('access-view'))
                $text = JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
            elseif ($readmore = $this->_article->alternative_readmore)
                $text = $readmore . JHtml::_('string.truncate', ($this->_article->title), $this->_article->params->get('readmore_limit'));
            elseif ($this->_article->params->get('show_readmore_title', 0) == 0)
                $text = JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
            else
                $text = JText::_('COM_CONTENT_READ_MORE') . JHtml::_('string.truncate', $this->_article->title, $this->_article->params->get('readmore_limit'));
            return '<p class="readmore">' . artxLinkButton(array(
                'classes' => array('a' => 'readon'),
                'link' => $link,
                'content' => str_replace(' ', '&#160;', $text))) . '</p>';
        }
    }
    

    /**
     * Renders article or block in the Post style.
     *
     * Elements of the $data array:
     *  'classes'
     *  'header-text'
     *  'header-icon'
     *  'header-link'
     *  'metadata-header-icons'
     *  'metadata-footer-icons'
     *  'content'
     */
    function artxPost($data)
    {
    	if (is_string($data))
    		$data = array('content' => $data);
    	$classes = isset($data['classes']) && strlen($data['classes']) ? $data['classes'] : '';
    	artxFragmentBegin(str_replace('class="art-post">', 'class="art-post' . $classes . '">', "<div class=\"art-post\">\r\n    <div class=\"art-post-body\">\r\n<div class=\"art-post-inner\">\r\n"));
    	artxFragmentBegin("<h2 class=\"art-postheader\"> ");
    	artxFragmentBegin("");
    	if (isset($data['header-text']) && strlen($data['header-text'])) {
    		if (isset($data['header-link']) && strlen($data['header-link']))
    			artxFragmentContent('<a href="' . $data['header-link'] . '" class="PostHeader">' . $data['header-text'] . '</a>');
    		else
    			artxFragmentContent($data['header-text']);
    	}
    	artxFragmentEnd("\r\n");
    	artxFragmentEnd("</h2>\r\n");
    	artxFragmentBegin("<div class=\"art-postmetadataheader\">\r\n");
    	artxFragmentBegin("<div class=\"art-postheadericons art-metadata-icons\">\r\n");
    	if (isset($data['metadata-header-icons']) && count($data['metadata-header-icons']))
    		foreach ($data['metadata-header-icons'] as $icon)
    			artxFragment('', $icon, '', ' | ');
    	artxFragmentEnd("\r\n</div>\r\n");
    	artxFragmentEnd("\r\n</div>\r\n");
    	artxFragmentBegin("<div class=\"art-postcontent\">\r\n    <!-- article-content -->\r\n");
    	if (isset($data['content']) && strlen($data['content']))
    		artxFragmentContent($data['content']);
    	artxFragmentEnd("\r\n    <!-- /article-content -->\r\n</div>\r\n<div class=\"cleared\"></div>\r\n");
    	artxFragmentBegin("<div class=\"art-postmetadatafooter\">\r\n");
    	artxFragmentBegin("<div class=\"art-postfootericons art-metadata-icons\">\r\n");
    	if (isset($data['metadata-footer-icons']) && count($data['metadata-footer-icons']))
    		foreach ($data['metadata-footer-icons'] as $icon)
    			artxFragment('', $icon, '', ' | ');
    	artxFragmentEnd("\r\n</div>\r\n");
    	artxFragmentEnd("\r\n</div>\r\n");
    	return artxFragmentEnd("\r\n</div>\r\n\r\n		<div class=\"cleared\"></div>\r\n    </div>\r\n</div>\r\n", '', true);
    }

    function artxBlock($caption, $content, $classes = '')
    {
        $hasCaption = ($GLOBALS['artx_settings']['block']['has_header']
            && null !== $caption && strlen(trim($caption)) > 0);
        $hasContent = (null !== $content && strlen(trim($content)) > 0);

        if (!$hasCaption && !$hasContent)
            return '';

        ob_start();
?>
        <?php ob_start(); ?>
<div class="art-block">
            <div class="art-block-body">
        
        <?php echo str_replace('class="art-block">', 'class="art-block' . $classes . '">', ob_get_clean()); ?>
        <?php if ($hasCaption): ?>
<div class="art-blockheader">
            <div class="l"></div>
            <div class="r"></div>
             <div class="t">
        <?php echo $caption; ?>
</div>
        </div>
        
        <?php endif; ?>
        <?php if ($hasContent): ?>
<div class="art-blockcontent">
            <div class="art-blockcontent-body">
        <!-- block-content -->
        
        <?php echo artxReplaceButtons($content); ?>

        <!-- /block-content -->
        
        		<div class="cleared"></div>
            </div>
        </div>
        
        <?php endif; ?>

        		<div class="cleared"></div>
            </div>
        </div>
        
<?php
        return ob_get_clean();
    }


    function artxVMenuBlock($caption, $content, $classes = '')
    {
        $hasCaption = (null !== $caption && strlen(trim($caption)) > 0);
        $hasContent = (null !== $content && strlen(trim($content)) > 0);

        if (!$hasCaption && !$hasContent)
            return '';

        ob_start();
?>
    <?php ob_start(); ?><div class="art-vmenublock">
    <div class="art-vmenublock-body">

        <?php echo str_replace('class="art-vmenublock">', 'class="art-vmenublock' . $classes . '">', ob_get_clean()); ?>
        <?php if ($hasCaption): ?><div class="art-vmenublockheader">
    <div class="l"></div>
    <div class="r"></div>
     <div class="t">
        <?php echo $caption; ?></div>
</div>

        <?php endif; ?>
        <?php if ($hasContent): ?><div class="art-vmenublockcontent">
    <div class="art-vmenublockcontent-body">
<!-- block-content -->

        <?php echo $content; ?>
<!-- /block-content -->

		<div class="cleared"></div>
    </div>
</div>

        <?php endif; ?>
		<div class="cleared"></div>
    </div>
</div>

<?php
        return ob_get_clean();
    }

    /**
     * Depricated since Artisteer 3.0.
     */
    function artxCountModules(&$document, $position)
    {
        return $document->artx->countModules($position);
    }

    /**
     * Depricated since Artisteer 3.0.
     */
    function artxPositions(&$document, $positions, $style)
    {
        ob_start();
        if (count($positions) == 3) {
            if (artxCountModules($document, $positions[0])
                && artxCountModules($document, $positions[1])
                && artxCountModules($document, $positions[2]))
            {
                ?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
  <td width="33%"><?php echo artxModules($document, $positions[0], $style); ?></td>
  <td width="33%"><?php echo artxModules($document, $positions[1], $style); ?></td>
  <td><?php echo artxModules($document, $positions[2], $style); ?></td>
</tr>
</table>
<?php
            } elseif (artxCountModules($document, $positions[0]) && artxCountModules($document, $positions[1])) {
?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
  <td width="33%"><?php echo artxModules($document, $positions[0], $style); ?></td>
  <td><?php echo artxModules($document, $positions[1], $style); ?></td>
</tr>
</table>
<?php
            } elseif (artxCountModules($document, $positions[1]) && artxCountModules($document, $positions[2])) {
?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
  <td width="67%"><?php echo artxModules($document, $positions[1], $style); ?></td>
  <td><?php echo artxModules($document, $positions[2], $style); ?></td>
</tr>
</table>
<?php
            } elseif (artxCountModules($document, $positions[0]) && artxCountModules($document, $positions[2])) {
?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
  <td width="50%"><?php echo artxModules($document, $positions[0], $style); ?></td>
  <td><?php echo artxModules($document, $positions[2], $style); ?></td>
</tr>
</table>
<?php
            } else {
                echo artxModules($document, $positions[0], $style);
                echo artxModules($document, $positions[1], $style);
                echo artxModules($document, $positions[2], $style);
            }
        } elseif (count($positions) == 2) {
            if (artxCountModules($document, $positions[0]) && artxCountModules($document, $positions[1])) {
?>
<table class="position" cellpadding="0" cellspacing="0" border="0">
<tr valign="top">
<td width="50%"><?php echo artxModules($document, $positions[0], $style); ?></td>
<td><?php echo artxModules($document, $positions[1], $style); ?></td>
</tr>
</table>
<?php
            } else {
                echo artxModules($document, $positions[0], $style);
                echo artxModules($document, $positions[1], $style);
            }
        } // count($positions)
        return ob_get_clean();
    }

    /**
     * Depricated since Artisteer 3.0.
     */
    function artxComponentWrapper(&$document)
    {
        $this->artx->componentWrapper();
    }

    /**
     * Depricated since Artisteer 3.0.
     */
    function artxModules(&$document, $position, $style = null)
    {
        $this->artx->position($position, $style);
    }


    	function artxUrlToHref($url)
    	{
    		$result = '';
    		$p = parse_url($url);
    		if (isset($p['scheme']) && isset($p['host'])) {
    			$result = $p['scheme'] . '://';
    			if (isset($p['user'])) {
    				$result .= $p['user'];
    				if (isset($p['pass']))
    					$result .= ':' . $p['pass'];
    				$result .= '@';
    			}
    			$result .= $p['host'];
    			if (isset($p['port']))
    				$result .= ':' . $p['port'];
    			if (!isset($p['path']))
    				$result .= '/';
    		}
    		if (isset($p['path']))
    			$result .= $p['path'];
    		if (isset($p['query'])) {
    			$result .= '?' . str_replace('&', '&amp;', $p['query']);
    		}
    		if (isset($p['fragment']))
    			$result .= '#' . $p['fragment'];
    		return $result;
    	}
    
    	function artxReplaceButtonsRegex() {
    		return '~<input\b[^>]*'
    			. '\bclass=(?:(")(?:[^"]*\s)?button(?:\s[^"]*)?"|(\')(?:[^\']*\s)?button(?:\s[^\']*)?\'|button(?=[/>\s]))'
    			. '[^>]*/?\s*>~i';
    	}
    
    	function artxReplaceButtons($content)
    	{
    		$re = artxReplaceButtonsRegex();
    		if (!preg_match_all($re, $content, $matches, PREG_OFFSET_CAPTURE))
    			return $content;
    		$result = '';
    		$position = 0;
    		for ($index = 0; $index < count($matches[0]); $index++) {
    			$match = $matches[0][$index];
    			if (is_array($matches[1][$index]) && strlen($matches[1][$index][0]) > 0)
    				$quote = $matches[1][$index][0];
    			else if (is_array($matches[2][$index]) && strlen($matches[2][$index][0]) > 0)
    				$quote = $matches[2][$index][0];
    			else
    				$quote = '"';
    			$result .= substr($content, $position, $match[1] - $position);
    			$position = $match[1] + strlen($match[0]);
    			$result .= str_replace('"', $quote, '<span class="art-button-wrapper"><span class="l"> </span><span class="r"> </span>')
    				. preg_replace('~\bclass=(?:"([^"]*\s)?button(\s[^"]*)?"|\'([^\']*\s)?button(\s[^\']*)?\'|button(?=[/>\s]))~i',
    					str_replace('"', $quote, 'class="\1\3button art-button\2\4"'), $match[0]) . '</span>';
    		}
    		$result .= substr($content, $position);
    		return $result;
    	}
    
    	function artxLinkButton($data = array())
    	{
    		return '<span class="art-button-wrapper"><span class="l"> </span><span class="r"> </span>'
    			. '<a class="' . (isset($data['classes']) && isset($data['classes']['a']) ? $data['classes']['a'] . ' ' : '')
    			. 'art-button" href="' . $data['link'] . '">' . $data['content'] . '</a></span>';
    	}
    
    	function artxHtmlFixFormAction($content)
    	{
    		if (preg_match('~ action="([^"]+)" ~', $content, $matches, PREG_OFFSET_CAPTURE)) {
    			$content = substr($content, 0, $matches[0][1])
    				. ' action="' . artxUrlToHref($matches[1][0]) . '" '
    				. substr($content, $matches[0][1] + strlen($matches[0][0]));
    		}
    		return $content;
    	}
    
    	function artxTagBuilder($tag, $attributes, $content) {
    		$result = '<' . $tag;
    		foreach ($attributes as $name => $value) {
    			if (is_string($value)) {
    				if (!empty($value))
    					$result .= ' ' . $name . '="' . $value . '"';
    			} else if (is_array($value)) {
    				$values = array_filter($value);
    				if (count($values))
    					$result .= ' ' . $name . '="' . implode(' ', $value) . '"';
    			}
    		}
    		$result .= '>' . $content . '</' . $tag . '>';
    		return $result;
    	}
    
    	$artxFragments = array();
    
    	function artxFragmentBegin($head = '')
    	{
    		global $artxFragments;
    		$artxFragments[] = array('head' => $head, 'content' => '', 'tail' => '');
    	}
    
    	function artxFragmentContent($content = '')
    	{
    		global $artxFragments;
    		$artxFragments[count($artxFragments) - 1]['content'] = $content;
    	}
    
    	function artxFragmentEnd($tail = '', $separator = '', $return = false)
    	{
    		global $artxFragments;
    		$fragment = array_pop($artxFragments);
    		$fragment['tail'] = $tail;
    		$content = trim($fragment['content']);
    		if (count($artxFragments) == 0) {
    			if ($return)
    				return (trim($content) == '') ? '' : ($fragment['head'] . $content . $fragment['tail']);
    			echo (trim($content) == '') ? '' : ($fragment['head'] . $content . $fragment['tail']);
    		} else {
    			$result = (trim($content) == '') ? '' : ($fragment['head'] . $content . $fragment['tail']);
    			$fragment =& $artxFragments[count($artxFragments) - 1];
    			$fragment['content'] .= (trim($fragment['content']) == '' ? '' : $separator) . $result;
    		}
    	}
    
    	function artxFragment($head = '', $content = '', $tail = '', $separator = '', $return = false)
    	{
    		global $artxFragments;
    		if ($head != '' && $content == '' && $tail == '' && $separator == '') {
    			$content = $head;
    			$head = '';
    		} elseif ($head != '' && $content != '' && $tail == '' && $separator == '') {
    			$separator = $content;
    			$content = $head;
    			$head = '';
    		}
    		artxFragmentBegin($head);
    		artxFragmentContent($content);
    		artxFragmentEnd($tail, $separator, $return);
    	}
    

}