<?php
defined('_JEXEC') or die;

/**
 * Contains the article factory method and content component rendering helpers.
 */
Designer::load("Designer_Content_ArchivedArticle");
Designer::load("Designer_Content_SingleArticle");
Designer::load("Designer_Content_CategoryArticle");
Designer::load("Designer_Content_FeaturedArticle");

class DesignerContent
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
        return $this->_component->escape(null == $title ? $this->pageHeading : $title);
    }

    public function article($view, $article, $params, $properties = array())
    {
        switch ($view) {
            case 'archive':
                return new DesignerContentArchivedArticle($this->_component, $this->_componentParams,
                                                      $article, $params);
            case 'article':
                return new DesignerContentSingleArticle($this->_component, $this->_componentParams,
                                                    $article, $params, $properties);
            case 'category':
                return new DesignerContentCategoryArticle($this->_component, $this->_componentParams,
                                                      $article, $params);
            case 'featured':
                return new DesignerContentFeaturedArticle($this->_component, $this->_componentParams,
                                                      $article, $params);
        }
    }

    public function beginPageContainer($class)
    {
        return '<div class="' . $class . $this->pageClassSfx .'">';
    }

    public function endPageContainer()
    {
        return '</div>';
    }
}