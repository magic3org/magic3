<?php
defined('_JEXEC') or die;

if (!defined('_ARTX_FUNCTIONS')) {

    define('_ARTX_FUNCTIONS', 1);

    $GLOBALS['artx_settings'] = array(
        'block' => array('has_header' => true),
        'menu' => array('show_submenus' => true),
        'vmenu' => array('show_submenus' => false, 'simple' => false)
    );

    /**
     * Base class with index.php view routines. Contains method for placing positions,
     * calculating classes, decorating components. Version-specific routines are defined
     * in subclasses: ArtxPage15 and ArtxPage16.
     *
     * @abstract
     */
    class ArtxPageView
    {

        /**
         * @access public
         */
        var $page;

        /**
         * @access protected
         */
        function __construct(&$page) {
            $this->page = & $page;
        }

        /**
         * Returns version-specific body class: art-j15 for joomla15 or art-j16 for joomla16.
         *
         * Example:
         *  <body class="art-j15">
         *
         * @access public
         * @abstract
         */
        function bodyClass() { }

        /**
         * Checks whether Joomla! has system messages to display.
         *
         * @access public
         * @abstract
         */
        function hasMessages() { }

        /**
         * Returns true when one of the given positions contains at least one module.
         * Example:
         *  if ($obj->containsModules('top1', 'top2', 'top3')) {
         *   // the following code will be executed when one of the positions contains modules:
         *   ...
         *  }
         *
         * @access public
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
         *   With one empty position: -:50%:25%:25%, 50%:-:25%:25%, 25%:50%:-:25%, 25%:25%:50%:-
         *   With two empty positions: -:-:75%:25%, -:50%:-:50%, -:50%:50%:-, -:50%:50%:-, 75%:-:-:25%, 50%:-:50%:-, 25%:75%:-:-
         *   One non-empty position: 100%
         *  Three positions:
         *   No empty positions: 33%:33%:34%
         *   With one empty position: -:66%:34%, 50%:-:50%, 33%:67%:-
         *   One non-empty position: 100%
         *
         * @access public
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

        /**
         * @access public
         */
        function position($position, $style = null)
        {
            return '<jdoc:include type="modules" name="' . $position . '"' . (null != $style ? ' style="artstyle" artstyle="' . $style . '"' : '') . ' />';
        }

        /**
         * Preprocess component content before printing it.
         * 
         * @access public
         * @abstract
         */
        function componentWrapper()
        {
        }

        /**
         * @access public
         */
        function getWysiwygBackgroundImprovement($id)
        {
            ob_start();
            ?>

(function () {
    var waitFor = function (interval, criteria, callback) {
        var interval = setInterval(function () {
            if (!criteria())
                return;
            clearInterval(interval);
            callback();
        }, interval);
    };
    waitFor(20, function () { return 'undefined' != typeof jQuery; },
        function () {
            var editor = ('undefined' != typeof tinyMCE)
                ? tinyMCE
                : (('undefined' != typeof JContentEditor)
                    ? JContentEditor : null);
            if (null == editor)
                return;
            waitFor(20,
                function () {
                    if (editor.editors)
                        for (var key in editor.editors)
                            if (editor.editors.hasOwnProperty(key))
                                return editor.editors[key].initialized;
                    return false;
                },
                function () {
                    var ifr = jQuery('#<?php echo $id; ?>');
                    var ifrdoc = ifr.attr('contentDocument');
                    setTimeout(function () {
                        // check whether editor.css is included or not:
                        if (0 == jQuery('link[href*="/css/editor.css"]', ifrdoc).length)
                            return;
                        var background = ifr.css('background-color');
                        ifr.css('background', 'transparent');
                        ifr.attr('allowtransparency', 'true');
                        var layout = jQuery('table.mceLayout');
                        var background = layout.css('background-color');
                        layout.css('background', 'transparent');
                        layout.find('.mceToolbar').css('background', background);
                        layout.find('.mceStatusbar').css('background', background);
                        jQuery('body', ifrdoc).css('background', 'transparent');
                    }, 1);
                });
        });
})();

<?php
            return ob_get_clean();
        }
    }

    class ArtxPage15 extends ArtxPageView
    {
        /**
         * @access public
         */
        function __construct(&$page) {
            parent::__construct($page);
        }

        /**
         * @access public
         */
        function bodyClass() {
            return 'art-j15';
        }

        /**
         * @access public
         */
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
         * The componentWrapper method gets the content of the 'component' buffer and searches for the '<div class="art-post">' string in it.
         * Then it replaces the componentheading DIV tag with span (to fix the w3.org validation) and replaces content of the buffer with
         * wrapped content.
         *
         * @access public
         */
        function componentWrapper()
        {
            if ($this->page->getType() != 'html')
                return;
            $option = JRequest::getCmd('option');
            $view = JRequest::getCmd('view');
            $layout = JRequest::getCmd('layout');
            $task = JRequest::getCmd('task');
            $content = $this->page->getBuffer('component');
            // Workarounds for Joomla bugs and inconsistencies:
            switch ($option) {
                case "com_user":
                    switch ($view) {
                        case "remind":
                            if ("" == $layout)
                                $content = str_replace('<button type="submit" class="validate">', '<button type="submit" class="button validate">', $content);
                            break;
                        case "reset":
                            if ("" == $layout)
                                $content = str_replace('<button type="submit" class="validate">', '<button type="submit" class="button validate">', $content);
                            break;
                    }
                    break;
            }
            // Code injections:
            switch ($option) {
                case "com_content":
                    switch ($view) {
                        case "article":
                            if ("edit" == $task)
                                $this->page->addScriptDeclaration($this->getWysiwygBackgroundImprovement('text_ifr'));
                            break;
                    }
                    break;
            }
            if ('com_content' == $option && ('frontpage' == $view || 'article' == $view || ('category' == $view && 'blog' == $layout)))
                return;
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
        /**
         * @access public
         */
        function __construct($page) {
            parent::__construct($page);
        }

        /**
         * @access public
         */
        function bodyClass() {
            return 'art-j16';
        }

        /**
         * @access public
         */
        function hasMessages()
        {
            $app = JFactory::getApplication();
            $messages = $app->getMessageQueue();
            if (is_array($messages) && count($messages))
                foreach ($messages as $msg)
                    if (isset($msg['type']) && isset($msg['message']))
                        return true;
            return false;
        }

        /**
         * Wraps component content into article style unless it is not wrapped already.
         *
         * The componentWrapper method gets the content of the 'component' buffer and searches for the '<div class="art-post">' string in it.
         * Then it wraps content of the buffer with art-post.
         *
         * @access public
         */
        function componentWrapper()
        {
            if ($this->page->getType() != 'html')
                return;
            $option = JRequest::getCmd('option');
            $view = JRequest::getCmd('view');
            $layout = JRequest::getCmd('layout');
            $content = $this->page->getBuffer('component');
            // Workarounds for Joomla bugs and inconsistencies:
            switch ($option) {
                case "com_content":
                    switch ($view) {
                        case "form":
                            if ("edit" == $layout)
                                $content = str_replace('<button type="button" onclick="', '<button type="button" class="button" onclick="', $content);
                            break;
                    }
                    break;
                case "com_users":
                    switch ($view) {
                        case "remind":
                            if ("" == $layout)
                                $content = str_replace('<button type="submit">', '<button type="submit" class="button">', $content);
                            break;
                        case "reset":
                            if ("" == $layout)
                                $content = str_replace('<button type="submit">', '<button type="submit" class="button">', $content);
                            break;
                        case "registration":
                            if ("" == $layout)
                                $content = str_replace('<button type="submit" class="validate">', '<button type="submit" class="button validate">', $content);
                            break;
                    }
                    break;
            }
            // Code injections:
            switch ($option) {
                case "com_content":
                    switch ($view) {
                        case "form":
                            if ("edit" == $layout)
                                $this->page->addScriptDeclaration($this->getWysiwygBackgroundImprovement('jform_articletext_ifr'));
                            break;
                    }
                    break;
            }
            if ('com_content' == $option && ('featured' == $view || 'article' == $view || ('category' == $view && 'blog' == $layout)))
                return;
            if (false === strpos($content, '<div class="art-post'))
                $this->page->setBuffer(artxPost(array('header-text' => null, 'content' => $content)), 'component');
        }
    }

    /**
     * Base class with content page routines for rendering page header and article factory.
     *
     * @abstract
     */
    class ArtxContentView
    {
        /**
         * @access protected
         */
        var $_component;

        /**
         * @access protected
         */
        var $_componentParams;

        /**
         * Component page class suffix.
         * @var string
         * @access public
         */
        var $pageClassSfx;

        /**
         * @var boolean
         * @access public
         */
        var $showPageHeading;

        /**
         * Page heading (or page title).
         * @var string
         * @access public
         */
        var $pageHeading;

        /**
         * @access protected
         */
        function __construct(&$component, &$params)
        {
            $this->_component = $component;
            $this->_componentParams = $params;
        }

        /**
         * @access public
         * @abstract
         */
        function pageHeading($heading = null) { }

        /**
         * @access public
         * @abstract
         */
        function article($article, $print) { }

        /**
         * @access public
         * @abstract
         */
        function articleListItem($item) { }

        /**
         * @access public
         */
        function beginPageContainer($class)
        {
            return '<div class="' . $class . $this->pageClassSfx .'">';
        }

        /**
         * @access public
         */
        function endPageContainer()
        {
            return '</div>';
        }
    }

    class ArtxContent15 extends ArtxContentView
    {

        /**
         * @access public
         */
        function __construct(&$component, &$params)
        {
            parent::__construct($component, $params);
            $this->pageClassSfx = $this->_componentParams->get('pageclass_sfx');
            $this->showPageHeading = $this->_componentParams->def('show_page_title', 1);
            $this->pageHeading = $this->showPageHeading ? $this->_componentParams->get('page_title') : '';
        }

        /**
         * @access public
         */
        function pageHeading($heading = null)
        {
            return artxPost(array('header-text' => null == $heading ? $this->pageHeading : $heading));
        }

        /**
         * @access public
         */
        function article($article, $print)
        {
            return new ArtxContentArticleView15($this->_component, $this->_componentParams, $article, $print);
        }

        /**
         * @access public
         */
        function articleListItem($item)
        {
            return new ArtxContentFrontpageItemView15($this->_component, $this->_componentParams, $item);
        }
    }

    class ArtxContent16 extends ArtxContentView
    {
        /**
         * @access public
         */
        function __construct($component, $params)
        {
            parent::__construct($component, $params);
            $this->pageClassSfx = $this->_component->pageclass_sfx;
            $this->showPageHeading = $this->_componentParams->def('show_page_heading', 1);
            $this->pageHeading = $this->showPageHeading ? $this->_componentParams->get('page_heading') : '';
        }

        /**
         * @access public
         */
        function pageHeading($heading = null)
        {
            return artxPost(array('header-text' => null == $heading ? $this->pageHeading : $heading));
        }

        /**
         * @access public
         */
        function article($article, $print)
        {
            return new ArtxContentArticleView16($this->_component, $this->_componentParams, $article, $print);
        }

        /**
         * @access public
         */
        function articleListItem($item)
        {
            return new ArtxContentFeaturedItemView16($this->_component, $this->_componentParams, $item);
        }
    }

    /**
     * @abstract
     */
    class ArtxContentGeneralArticleView
    {
        /**
         * @access protected
         */
        var $_component;

        /**
         * @access protected
         */
        var $_componentParams;

        /**
         * @access protected
         */
        var $_article;

        /**
         * @access public
         */
        var $params;

        /**
         * @access public
         */
        var $isPublished;

        /**
         * @access public
         */
        var $canEdit;

        /**
         * @access public
         */
        var $title;

        /**
         * @access public
         */
        var $titleVisible;

        /**
         * @access public
         */
        var $titleLink;

        /**
         * @access public
         */
        var $hits;

        /**
         * @access public
         */
        var $print;

        /**
         * @access public
         */
        var $showCreateDate;

        /**
         * @access public
         */
        var $showModifyDate;

        /**
         * @access public
         */
        var $showPublishDate;

        /**
         * @access public
         */
        var $showAuthor;

        /**
         * @access public
         */
        var $showPdfIcon;

        /**
         * @access public
         */
        var $showPrintIcon;

        /**
         * @access public
         */
        var $showEmailIcon;

        /**
         * @access public
         */
        var $showHits;

        /**
         * @access public
         */
        var $showUrl;

        /**
         * @access public
         */
        var $showIntro;

        /**
         * @access public
         */
        var $showTeaser;

        /**
         * @access public
         */
        var $showText;

        /**
         * @access public
         */
        var $showReadmore;

        /**
         * @access public
         */
        var $showParentCategory;

        /**
         * @access public
         */
        var $parentCategoryLink;

        /**
         * @access public
         */
        var $parentCategory;

        /**
         * @access public
         */
        var $showCategory;

        /**
         * @access public
         */
        var $categoryLink;

        /**
         * @access public
         */
        var $category;

        /**
         * @access protected
         */
        function __construct(&$component, &$componentParams, &$article)
        {
            // Initialization:
            $this->_component = &$component;
            $this->_componentParams = &$componentParams; 
            $this->_article = &$article;
            // Calculate properties:
            $this->isPublished = 0 != $this->_article->state;
        }

        /**
         * @access public
         */
        function introText() { return ''; }

        /**
         * @access public
         */
        function createDateInfo() { return ''; }

        /**
         * @access public
         */
        function modifyDateInfo() { return ''; }

        /**
         * @access public
         */
        function publishDateInfo() { return ''; }

        /**
         * @access public
         */
        function authorInfo() { return ''; }

        /**
         * @access public
         */
        function hitsInfo() { return ''; }

        /**
         * @access public
         */
        function pdfIcon() { return ''; }

        /**
         * @access public
         */
        function emailIcon() { return ''; }

        /**
         * @access public
         */
        function editIcon() { return ''; }

        /**
         * @access public
         */
        function printPopupIcon() { return ''; }

        /**
         * @access public
         */
        function printScreenIcon() { return ''; }

        /**
         * @access public
         */
        function readmore() { return ''; }

        /**
         * @access public
         */
        function beginUnpublishedArticle() { return '<div class="system-unpublished">'; }

        /**
         * @access public
         */
        function endUnpublishedArticle() { return '</div>'; }

        /**
         * @access public
         */
        function articleSeparator() { return '<div class="item-separator"></div>'; }

        /**
         * @access public
         */
        function categories()
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

        /**
         * @access public
         */
        function urlInfo()
        {
            return '<a href="http://' . $this->_component->escape($this->_article->urls) . '" target="_blank">'
                . $this->_component->escape($this->_article->urls) . '</a>';
        }

        /**
         * @access public
         */
        function getArticleViewParameters()
        {
            return array('metadata-header-icons' => array(), 'metadata-footer-icons' => array());
        }

        /**
         * @access public
         */
        function event($name)
        {
            return $this->_article->event->{$name};
        }

        /**
         * @access public
         */
        function article($article)
        {
            return artxPost($article);
        }

        /**
         * @access public
         */
        function toc()
        {
            return isset($this->_article->toc) ? $this->_article->toc : '';
        }

        /**
         * @access public
         */
        function content()
        {
            return "<div class=\"art-article\">" . $this->_article->text . "</div>";
        }
    }

    class ArtxContentArticleView15 extends ArtxContentGeneralArticleView
    {
        /**
         * @access public
         */
        function __construct(&$component, &$componentParams, &$article, $print)
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
            $this->showTeaser = false; // - not available in J! 1.5
            $this->showText = true; // - not available in J! 1.5

            $this->showParentCategory = $this->_componentParams->get('show_section') && $this->_article->sectionid && isset($this->_article->section);
            $this->parentCategory = $this->showParentCategory ? $this->_article->section : '';
            $this->parentCategoryLink = ($this->showParentCategory && $this->_componentParams->get('link_section'))
                ? JRoute::_(ContentHelperRoute::getSectionRoute($this->_article->sectionid)) : '';
            $this->showCategory = $this->_componentParams->get('show_category') && $this->_article->catid;
            $this->category = $this->showCategory ? $this->_article->category : '';
            $this->categoryLink = ($this->showCategory && $this->_componentParams->get('link_category'))
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug, $this->_article->sectionid)) : '';
        }

        /**
         * @access public
         */
        function createDateInfo()
        {
            return JHTML::_('date', $this->_article->created, JText::_('DATE_FORMAT_LC2'));
        }

        /**
         * @access public
         */
        function modifyDateInfo()
        {
            return JText::sprintf('LAST_UPDATED2', JHTML::_('date', $this->_article->modified, JText::_('DATE_FORMAT_LC2')));
        }

        /**
         * @access public
         */
        function authorInfo()
        {
            return JText::sprintf('Written by', $this->_component->escape($this->_article->created_by_alias
                ? $this->_article->created_by_alias : $this->_article->author));
        }

        /**
         * @access public
         */
        function pdfIcon()
        {
            return JHTML::_('icon.pdf', $this->_article, $this->_componentParams, $this->_component->access);
        }

        /**
         * @access public
         */
        function emailIcon()
        {
            return JHTML::_('icon.email', $this->_article, $this->_componentParams, $this->_component->access);
        }

        /**
         * @access public
         */
        function editIcon()
        {
            return JHTML::_('icon.edit', $this->_article, $this->_componentParams, $this->_component->access);
        }

        /**
         * @access public
         */
        function printPopupIcon()
        {
            return JHTML::_('icon.print_popup', $this->_article, $this->_componentParams, $this->_component->access);
        }

        /**
         * @access public
         */
        function printScreenIcon()
        {
            return JHtml::_('icon.print_screen', $this->_article, $this->_componentParams, $this->_component->access);
        }
    }

    class ArtxContentArticleView16 extends ArtxContentGeneralArticleView
    {
        /**
         * @access public
         */
        function __construct($component, $componentParams, $article, $print)
        {
            parent::__construct($component, $componentParams, $article);

            $user = JFactory::getUser();

            $this->print = $print;
            $this->canEdit = $this->_article->params->get('access-edit');
            $this->title = $this->_article->title;
            $this->titleVisible = $this->_article->params->get('show_title');
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
            $this->showReadmore = $this->_article->params->get('show_readmore') && $this->_article->fulltext != null;
            $this->showTeaser = $this->_article->params->get('show_noauth') == true && $user->get('guest');
            $this->showText = $this->_article->params->get('access-view');

            $this->showParentCategory = $this->_article->params->get('show_parent_category') && $this->_article->parent_slug != '1:root';
            $this->parentCategory = $this->showParentCategory ? $this->_article->parent_title : '';
            $this->parentCategoryLink = ($this->showParentCategory && $this->_article->params->get('link_parent_category') && $this->_article->parent_slug)
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->parent_slug)) : '';
            $this->showCategory = $this->_article->params->get('show_category');
            $this->category = $this->showCategory ? $this->_article->category_title : '';
            $this->categoryLink = ($this->showCategory && $this->_article->params->get('link_category') && $this->_article->catslug)
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug)) : '';
        }

        /**
         * @access public
         */
        function createDateInfo()
        {
            return JHtml::_('date', $this->_article->created, JText::_('DATE_FORMAT_LC2'));
        }

        /**
         * @access public
         */
        function modifyDateInfo()
        {
            return JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $this->_article->modified, JText::_('DATE_FORMAT_LC2')));
        }

        /**
         * @access public
         */
        function publishDateInfo()
        {
            return JText::sprintf('COM_CONTENT_PUBLISHED_DATE', JHtml::_('date', $this->_article->publish_up, JText::_('DATE_FORMAT_LC2')));
        }

        /**
         * @access public
         */
        function authorInfo()
        {
            $author = $this->_article->created_by_alias ? $this->_article->created_by_alias : $this->_article->author;
            if (!empty($this->_article->contactid) && $this->_article->params->get('link_author'))
                return JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link',
                    JRoute::_('index.php?option=com_contact&view=contact&id=' . $this->_article->contactid), $author));
            return JText::sprintf('COM_CONTENT_WRITTEN_BY', $author);
        }

        /**
         * @access public
         */
        function emailIcon()
        {
            return JHtml::_('icon.email', $this->_article, $this->_article->params);
        }

        /**
         * @access public
         */
        function editIcon()
        {
            return JHtml::_('icon.edit', $this->_article, $this->_article->params);
        }

        /**
         * @access public
         */
        function printPopupIcon()
        {
            return JHtml::_('icon.print_popup', $this->_article, $this->_article->params);
        }

        /**
         * @access public
         */
        function printScreenIcon()
        {
            return JHtml::_('icon.print_screen', $this->_article, $this->_article->params);
        }

        /**
         * @access public
         */
        function hitsInfo()
        {
            return JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->_article->hits);
        }

        /**
         * @access public
         */
        function introText()
        {
            return "<div class=\"art-article\">" . $this->_article->introtext . "</div>";
        }

        /**
         * @access public
         */
        function readmore()
        {
            $content = '';
            $link1 = JRoute::_('index.php?option=com_users&view=login');
            $link = new JURI($link1);
            $content .= '<p class="readmore"><a href="' . $link . '">';
            $attribs = json_decode($this->_article->attribs);
            if ($attribs->alternative_readmore == null) {
                $content .= JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
            } elseif ($readmore = $this->_article->alternative_readmore) {
                $content .= $readmore;
                if ($this->_article->params->get('show_readmore_title', 0) != 0) {
                    $content .= JHTML::_('string.truncate', ($this->_article->title), $this->_article->params->get('readmore_limit'));
                }
            } elseif ($this->_article->get('show_readmore_title', 0) == 0) {
                $content .= JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
            } else {
                $content .= JText::_('COM_CONTENT_READ_MORE');
                $content .= JHTML::_('string.truncate', ($this->_article->title), $this->_article->params->get('readmore_limit'));
            }
            $content .= '</a></p>';
            return $content;
        }
    }

    /**
     * Based on Joomla 1.5.23.
     */
    class ArtxContentFrontpageItemView15 extends ArtxContentGeneralArticleView
    {
        /**
         * @access public
         */
        function __construct(&$component, &$componentParams, &$article)
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
            $this->showTeaser = true; // - not available in J! 1.5
            $this->showText = false; // - not available in J! 1.5


            $this->showParentCategory = $this->_article->params->get('show_section') && $this->_article->sectionid && isset($this->_article->section);
            $this->parentCategory = $this->showParentCategory ? $this->_article->section : '';
            $this->parentCategoryLink = ($this->showParentCategory && $this->_article->params->get('link_section'))
                ? JRoute::_(ContentHelperRoute::getSectionRoute($this->_article->sectionid)) : '';
            $this->showCategory = $this->_article->params->get('show_category') && $this->_article->catid;
            $this->category = $this->showCategory ? $this->_article->category : '';
            $this->categoryLink = ($this->showCategory && $this->_article->params->get('link_category'))
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug, $this->_article->sectionid)) : '';
        }

        /**
         * @access public
         */
        function createDateInfo()
        {
            return JHTML::_('date', $this->_article->created, JText::_('DATE_FORMAT_LC2'));
        }

        /**
         * @access public
         */
        function modifyDateInfo()
        {
            return JText::sprintf('LAST_UPDATED2', JHTML::_('date', $this->_article->modified, JText::_('DATE_FORMAT_LC2')));
        }

        /**
         * @access public
         */
        function authorInfo()
        {
            return JText::sprintf('Written by', $this->_component->escape($this->_article->created_by_alias
                ? $this->_article->created_by_alias : $this->_article->author));
        }

        /**
         * @access public
         */
        function pdfIcon()
        {
            return JHTML::_('icon.pdf', $this->_article, $this->_article->params, $this->_component->access);
        }

        /**
         * @access public
         */
        function emailIcon()
        {
            return JHTML::_('icon.email', $this->_article, $this->_article->params, $this->_component->access);
        }

        /**
         * @access public
         */
        function editIcon()
        {
            return JHTML::_('icon.edit', $this->_article, $this->_article->params, $this->_component->access);
        }

        /**
         * @access public
         */
        function printPopupIcon()
        {
            return JHTML::_('icon.print_popup', $this->_article, $this->_article->params, $this->_component->access);
        }

        /**
         * @access public
         */
        function introText()
        {
            return "<div class=\"art-article\">" . $this->_article->text . "</div>";
        }

        /**
         * @access public
         */
        function readmore()
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
     * Based on Joomla 1.6.1
     */
    class ArtxContentFeaturedItemView16 extends ArtxContentGeneralArticleView
    {
        /**
         * @access public
         */
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
            $this->showTeaser = true;
            $this->showText = false;

            // Because category blog layout view does not support catslug:
            if (!isset($this->_article->catslug))
                $this->_article->catslug = $this->_article->category_alias ? ($this->_article->catid . ':' . $this->_article->category_alias) : $this->_article->catid;
            if (!isset($this->_article->parent_slug))
                $this->_article->parent_slug = $this->_article->parent_alias ? ($this->_article->parent_id . ':' . $this->_article->parent_alias) : $this->_article->parent_id;

            $this->showParentCategory = $this->_article->params->get('show_parent_category') && $this->_article->parent_id != 1;
            $this->parentCategory = $this->showParentCategory ? $this->_article->parent_title : '';
            $this->parentCategoryLink = ($this->showParentCategory && $this->_article->params->get('link_parent_category') && $this->_article->parent_slug)
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->parent_slug)) : '';
            $this->showCategory = $this->_article->params->get('show_category');
            $this->category = $this->showCategory ? $this->_article->category_title : '';
            $this->categoryLink = ($this->showCategory && $this->_article->params->get('link_category') && $this->_article->catslug)
                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug)) : '';
        }

        /**
         * @access public
         */
        function createDateInfo()
        {
            return JHtml::_('date', $this->_article->created, JText::_('DATE_FORMAT_LC2'));
        }

        /**
         * @access public
         */
        function modifyDateInfo()
        {
            return JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $this->_article->modified, JText::_('DATE_FORMAT_LC2')));
        }

        /**
         * @access public
         */
        function publishDateInfo()
        {
            return JText::sprintf('COM_CONTENT_PUBLISHED_DATE', JHtml::_('date', $this->_article->publish_up, JText::_('DATE_FORMAT_LC2')));
        }

        /**
         * @access public
         */
        function authorInfo()
        {
            $author = $this->_article->created_by_alias ? $this->_article->created_by_alias : $this->_article->author;
            if (!empty($this->_article->contactid) && $this->_article->params->get('link_author'))
                return JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link',
                    JRoute::_('index.php?option=com_contact&view=contact&id=' . $this->_article->contactid), $author));
            return JText::sprintf('COM_CONTENT_WRITTEN_BY', $author);
        }

        /**
         * @access public
         */
        function emailIcon()
        {
            return JHtml::_('icon.email', $this->_article, $this->_article->params);
        }

        /**
         * @access public
         */
        function editIcon()
        {
            return JHtml::_('icon.edit', $this->_article, $this->_article->params);
        }

        /**
         * @access public
         */
        function printPopupIcon()
        {
            return JHtml::_('icon.print_popup', $this->_article, $this->_article->params);
        }

        /**
         * @access public
         */
        function hitsInfo()
        {
            return JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->_article->hits);
        }

        /**
         * @access public
         */
        function introText()
        {
            return "<div class=\"art-article\">" . $this->_article->introtext . "</div>";
        }

        /**
         * @access public
         */
        function readmore()
        {
            if ($this->_article->params->get('access-view')) {
                $link = JRoute::_(ContentHelperRoute::getArticleRoute($this->_article->slug, $this->_article->catid));
            } else {
                $app = JFactory::getApplication();
                $menu = $app->getMenu();
                $active = $menu->getActive();
                $itemId = $active->id;
                $link1 = JRoute::_('index.php?option=com_users&view=login&&Itemid=' . $itemId);
                $returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($this->_article->slug, $this->_article->catid));
                $link = new JURI($link1);
                $link->setVar('return', base64_encode($returnURL));
            }
            if (!$this->_article->params->get('access-view'))
                $text = JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
            elseif ($readmore = $this->_article->alternative_readmore) {
                $text = $readmore;
                if ($this->_article->params->get('show_readmore_title', 0) != 0)
                    $text .= JHtml::_('string.truncate', ($this->_article->title), $this->_article->params->get('readmore_limit'));
            } elseif ($this->_article->params->get('show_readmore_title', 0) == 0)
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
    	artxFragmentBegin("<h2 class=\"art-postheader\">" . JHTML::_('image.site', 'postheadericon.png', null, null, null, '', array('width' => '25', 'height' => '22')) . " ");
    	artxFragmentBegin("");
    	if (isset($data['header-text']) && strlen($data['header-text'])) {
    		if (isset($data['header-link']) && strlen($data['header-link']))
    			artxFragmentContent('<a href="' . $data['header-link'] . '" class="PostHeader">' . $data['header-text'] . '</a>');
    		else
    			artxFragmentContent($data['header-text']);
    	}
    	artxFragmentEnd("\r\n");
    	artxFragmentEnd("</h2>\r\n");
    	artxFragmentBegin("<div class=\"art-postheadericons art-metadata-icons\">\r\n");
    	if (isset($data['metadata-header-icons']) && count($data['metadata-header-icons']))
    		foreach ($data['metadata-header-icons'] as $icon)
    			artxFragment('', $icon, '', ' | ');
    	artxFragmentEnd("\r\n</div>\r\n");
    	artxFragmentBegin("<div class=\"art-postcontent\">\r\n");
    	if (isset($data['content']) && strlen($data['content']))
    		artxFragmentContent(artxPostprocessPostContent($data['content']));
    	artxFragmentEnd("\r\n</div>\r\n<div class=\"cleared\"></div>\r\n");
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
            <h3 class="t">
        <?php echo $caption; ?>
</h3>
        </div>
        <?php endif; ?>
        <?php if ($hasContent): ?>
<div class="art-blockcontent">
            <div class="art-blockcontent-body">
        
        <?php echo artxPostprocessBlockContent($content); ?>

        
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
    <h3 class="t">
        <?php echo $caption; ?></h3>
</div>
        <?php endif; ?>
        <?php if ($hasContent): ?><div class="art-vmenublockcontent">
    <div class="art-vmenublockcontent-body">

        <?php echo $content; ?>

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
     * Deprecated since Artisteer 3.0.
     */
    function artxCountModules(&$document, $position)
    {
        return $document->artx->countModules($position);
    }

    /**
     * Deprecated since Artisteer 3.0.
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
     * Deprecated since Artisteer 3.0.
     */
    function artxComponentWrapper(&$document)
    {
        $this->artx->componentWrapper();
    }

    /**
     * Deprecated since Artisteer 3.0.
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
    
        /**
         * Searches $content for tags and returns information about each found tag.
         *
         * Created to support button replacing process, e.g. wrapping submit/reset 
         * inputs and buttons with artisteer style.
         *
         * When all the $name tags are found, they are processed by the $filter specified.
         * Filter is applied to the attributes. When an attribute contains several values
         * (e.g. class attribute), only tags that contain all the values from filter
         * will be selected. E.g. filtering by the button class will select elements
         * with class="button" and class="button validate".
         *
         * Parsing does not support nested tags. Looking for span tags in
         * <span>1<span>2</span>3</span> will return <span>1<span>2</span> and
         * <span>2</span>.
         *
         * Examples:
         *  Select all tags with class='readon':
         *   artxFindTags($html, array('*' => array('class' => 'readon')))
         *  Select inputs with type='submit' and class='button':
         *   artxFindTags($html, array('input' => array('type' => 'submit', 'class' => 'button')))
         *  Select inputs with type='submit' and class='button validate':
         *   artxFindTags($html, array('input' => array('type' => 'submit', 'class' => array('button', 'validate'))))
         *  Select inputs with class != 'art-button'
         *   artxFindTags($html, array('input' => array('class' => '!art-button')))
         *  Select inputs with non-empty class
         *   artxFindTags($html, array('input' => array('class' => '!')))
         *  Select inputs with class != 'art-button' and non-empty class:
         *   artxFindTags($html, array('input' => array('class' => array('!art-button', '!'))))
         *  Select inputs with class = button but != 'art-button'
         *   artxFindTags($html, array('input' => array('class' => array('button', '!art-button'))))
         *
         * @return array of text fragments and tag information: position, length,
         *         name, attributes, raw_open_tag, body.
         */
        function artxFindTags($content, $filters)
        {
            $result = array('');
            $index = 0;
            $position = 0;
            $name = implode('|', array_keys($filters));
            $name = str_replace('*', '\w+', $name);
            // search for open tag
            if (preg_match_all(
                '~<(' . $name . ')\b(?:\s+[^\s]+\s*=\s*(?:"[^"]+"|\'[^\']+\'|[^\s>]+))*\s*/?>~i', $content,
                $tagMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER))
            {
                foreach ($tagMatches as $tagMatch) {
                    $rawMatch = $tagMatch[0][0];
                    $name = $tagMatch[1][0];
                    $tag = array('name' => $name, 'position' => $tagMatch[0][1]);
                    $openTagTail = (strlen($rawMatch) > 1 && '/' == $rawMatch[strlen($rawMatch) - 2])
                        ? '/>' : '>';
                    // different instructions for paired and unpaired tags
                    switch ($name)
                    {
                        case 'input':
                        case 'img':
                        case 'br':
                            $tag['paired'] = false;
                            $tag['length'] = strlen($tagMatch[0][0]);
                            $tag['body'] = null;
                            $tag['close'] = 2 == strlen($openTagTail);
                            break;
                        default:
                            $closeTag = '</' . $name . '>';
                            $closeTagLength = strlen($closeTag);
                            $tag['paired'] = true;
                            $end = strpos($content, $closeTag, $tag['position']);
                            if (false === $end)
                                continue;
                            $openTagLength = strlen($tagMatch[0][0]);
                            $tag['body'] = substr($content, $tag['position'] + $openTagLength,
                                $end - $openTagLength - $tag['position']);
                            $tag['length'] = $end + $closeTagLength - $tag['position'];
                            break;
                    }
                    // parse attributes
                    $rawAttributes = trim(substr($tagMatch[0][0], strlen($name) + 1,
                        strlen($tagMatch[0][0]) - strlen($name) - 1 - strlen($openTagTail)));
                    $attributes = array();
                    if (preg_match_all('~([^=\s]+)\s*=\s*(?:(")([^"]+)"|(\')([^\']+)\'|([^\s]+))~',
                        $rawAttributes, $attributeMatches, PREG_SET_ORDER))
                    {
                        foreach ($attributeMatches as $attrMatch) {
                            $attrName = $attrMatch[1];
                            $attrDelimeter = (isset($attrMatch[2]) && '' !== $attrMatch[2])
                                ? $attrMatch[2]
                                : ((isset($attrMatch[4]) && '' !== $attrMatch[4])
                                    ? $attrMatch[4] : '');
                            $attrValue = (isset($attrMatch[3]) && '' !== $attrMatch[3])
                                ? $attrMatch[3]
                                : ((isset($attrMatch[5]) && '' !== $attrMatch[5])
                                    ? $attrMatch[5] : $attrMatch[6]);
                            if ('class' == $attrName)
                                $attrValue = explode(' ', preg_replace('~\s+~', ' ', $attrValue));
                            $attributes[$attrName] = array('delimeter' => $attrDelimeter,
                                'value' => $attrValue);
                        }
                    }
                    // apply filter to attributes
                    $passed = true;
                    $filter = isset($filters[$name])
                        ? $filters[$name]
                        : (isset($filters['*']) ? $filters['*'] : array());
                    foreach ($filter as $key => $value) {
                        $criteria = is_array($value) ? $value : array($value);
                        for ($c = 0; $c < count($criteria) && $passed; $c++) {
                            $crit = $criteria[$c];
                            if ('' == $crit) {
                                // attribute should be empty
                                if ('class' == $key) {
                                    if (isset($attributes[$key]) && count($attributes[$key]['value']) != 0) {
                                        $passed = false;
                                        break;
                                    }
                                } else {
                                    if (isset($attributes[$key]) && strlen($attributes[$key]['value']) != 0) {
                                        $passed = false;
                                        break;
                                    }
                                }
                            } else if ('!' == $crit) {
                                // attribute should be not set or empty
                                if ('class' == $key) {
                                    if (!isset($attributes[$key]) || count($attributes[$key]['value']) == 0) {
                                        $passed = false;
                                        break;
                                    }
                                } else {
                                    if (!isset($attributes[$key]) || strlen($attributes[$key]['value']) == 0) {
                                        $passed = false;
                                        break;
                                    }
                                }
                            } else if ('!' == $crit[0]) {
                                // * attribute should not contain value
                                // * if attribute is empty, it does not contain value
                                if ('class' == $key) {
                                    if (isset($attributes[$key]) && count($attributes[$key]['value']) != 0
                                        && in_array(substr($crit, 1), $attributes[$key]['value']))
                                    {
                                        $passed = false;
                                        break;
                                    }
                                } else {
                                    if (isset($attributes[$key]) && strlen($attributes[$key]['value']) != 0
                                        && $crit == $attributes[$key]['value'])
                                    {
                                        $passed = false;
                                        break;
                                    }
                                }
                            } else {
                                // * attribute should contain value
                                // * if attribute is empty, it does not contain value
                                if ('class' == $key) {
                                    if (!isset($attributes[$key]) || count($attributes[$key]['value']) == 0) {
                                        $passed = false;
                                        break;
                                    }
                                    if (!in_array($crit, $attributes[$key]['value'])) {
                                        $passed = false;
                                        break;
                                    }
                                } else {
                                    if (!isset($attributes[$key]) || strlen($attributes[$key]['value']) == 0) {
                                        $passed = false;
                                        break;
                                    }
                                    if ($crit != $attributes[$key]['value']) {
                                        $passed = false;
                                        break;
                                    }
                                }
                            }
                        }
                        if (!$passed)
                            break;
                    }
                    if (!$passed)
                        continue;
                    // finalize tag info constrution
                    $tag['attributes'] = $attributes;
                    $result[$index] = substr($content, $position, $tag['position'] - $position);
                    $position = $tag['position'] + $tag['length'];
                    $index++;
                    $result[$index] = $tag;
                    $index++;
                }
            }
            $result[$index] = $position < strlen($content) ? substr($content, $position) : '';
            return $result;
        }
    
        /**
         * Converts tag info created by artxFindTags back to text tag.
         *
         * @return string
         */
        function artxTagInfoToString($info)
        {
            $result = '<' . $info['name'];
            if (isset($info['attributes']) && 0 != count($info['attributes'])) {
                $attributes = '';
                foreach ($info['attributes'] as $key => $value)
                    $attributes .= ' ' . $key . '=' . $value['delimeter']
                        . (is_array($value['value']) ? implode(' ', $value['value']) : $value['value'])
                        . $value['delimeter'];
                $result .= $attributes;
            }
            if ($info['paired']) {
                $result .= '>';
                $result .= $info['body'];
                $result .= '</' . $info['name'] . '>';
            } else
                $result .= ($info['close'] ? ' /' : '') . '>';
            return $result;
        }
    
        /**
         * Decorates the specified tag with artisteer button style.
         *
         * @param string $name tag name that should be decorated
         * @param array $filter select $name tags with attributes matching $filter
         * @return $content with replaced $name tags
         */
        function artxReplaceButtons($content, $filter = array('input' => array('class' => 'button')))
        {
            $result = '';
            foreach (artxFindTags($content, $filter) as $tag) {
                if (is_string($tag))
                    $result .= $tag;
                else {
                    $tag['attributes']['class']['value'][] = 'art-button';
                    $delimeter = '' == $tag['attributes']['class']['delimeter']
                        ? '"' : $tag['attributes']['class']['delimeter'];
                    $tag['attributes']['class']['delimeter'] = $delimeter;
                    $button = str_replace('"', $delimeter, '<span class="art-button-wrapper">'
                        . '<span class="art-button-l"> </span><span class="art-button-r"> </span>')
                        . artxTagInfoToString($tag) . '</span>';
                    $result .= $button;
                }
            }
            return $result;
        }
    
        function artxLinkButton($data = array())
        {
            return '<span class="art-button-wrapper"><span class="art-button-l"> </span><span class="art-button-r"> </span>'
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
    

function artxPostprocessBlockContent($content)
    {
        return artxPostprocessContent($content);
    }
    
    function artxPostprocessPostContent($content)
    {
        return artxPostprocessContent($content);
    }
    
    function artxPostprocessContent($content)
    {
        $content = artxReplaceButtons($content, array('input' => array('class' => array('button', '!art-button')),
            'button' => array('class' => array('button', '!art-button'))));
        return $content;
    }
    

}