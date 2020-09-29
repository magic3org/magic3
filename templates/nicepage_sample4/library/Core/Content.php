<?php
defined('_JEXEC') or die;

/**
 * Contains the article factory method and content component rendering helpers.
 */
Core::load("Core_Content_ArchivedArticle");
Core::load("Core_Content_SingleArticle");
Core::load("Core_Content_CategoryArticle");
Core::load("Core_Content_FeaturedArticle");

class CoreContent
{
    protected $_component;
    protected $_componentParams;

    public $pageClassSfx;

    public $pageHeading;

    public function __construct($component, $params)
    {
        $this->_component = $component;
        $this->_componentParams = $params;

        $this->pageClassSfx = $component->pageclass_sfx;
        $this->pageHeading = $this->_componentParams->get('show_page_heading', 1)
                               ? $this->_componentParams->get('page_heading') : '';
    }

    public function pageHeading($title = null)
    {
        $heading = '';
        if (strlen($this->pageHeading)) {
            ob_start();
            echo '<section class="u-clearfix"><div class="u-clearfix u-sheet"><h1>';
            echo $this->pageHeading;
            echo '</h1></div></section>';
            $heading = ob_get_clean();
        }
        return $heading;
    }

    public function article($view, $article, $params, $properties = array())
    {
        switch ($view) {
            case 'archive':
                return new CoreContentArchivedArticle($this->_component, $this->_componentParams,
                                                      $article, $params);
            case 'article':
                return new CoreContentSingleArticle($this->_component, $this->_componentParams,
                                                    $article, $params, $properties);
            case 'category':
                return new CoreContentCategoryArticle($this->_component, $this->_componentParams,
                                                      $article, $params);
            case 'featured':
                return new CoreContentFeaturedArticle($this->_component, $this->_componentParams,
                                                      $article, $params);
        }
    }

    public function beginPageContainer($class, $attrs = array())
    {
        $str = '';
        foreach($attrs as $name => $value) {
            $str .= ' ' . $name . (!is_null($value) ? ('="' . $value . '"') : '');
        }
        return '<div class="' . $class . $this->pageClassSfx .'"' . $str . '>';
    }

    public function endPageContainer()
    {
        return '</div>';
    }
}
