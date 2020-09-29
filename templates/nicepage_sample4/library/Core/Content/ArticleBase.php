<?php
defined('_JEXEC') or die;

abstract class CoreContentArticleBase
{
    protected $_component;
    protected $_componentParams;
    protected $_article;
    protected $_articleParams;

    public $title;

    public $titleLink;

    public $created;

    public $modified;

    public $published;

    public $hits;

    public $author;

    public $authorLink;
    
    public $category;
    
    public $categoryLink;

    public $parentCategory;
    
    public $parentCategoryLink;

    protected function __construct($component, $componentParams, $article, $articleParams)
    {
        // Initialization:
        $this->_component = $component;
        $this->_componentParams = $componentParams; 
        $this->_article = $article;
        $this->_articleParams = $articleParams;

        // Configuring properties:
        $this->title = $this->_article->title;
        $this->created = $this->_articleParams->get('show_create_date')
                           ? $this->_article->created : '';
        $this->modified = $this->_articleParams->get('show_modify_date')
                            ? $this->_article->modified : '';
        $this->published = $this->_articleParams->get('show_publish_date')
                             ? $this->_article->publish_up : '';
        $this->hits = $this->_articleParams->get('show_hits')
                        ? $this->_article->hits : '';
        $this->author = $this->_articleParams->get('show_author') && !empty($this->_article->author)
                          ? ($this->_article->created_by_alias ? $this->_article->created_by_alias : $this->_article->author)
                          : '';
        $this->authorLink = strlen($this->author) && !empty($this->_article->contactid) && $this->_articleParams->get('link_author')
                              ? 'index.php?option=com_contact&view=contact&id=' . $this->_article->contactid
                              : '';
        $this->shareLink = dirname(JURI::current()) . '/' . ContentHelperRoute::getArticleRoute($this->_article->slug, $this->_article->catid);
    }

    /**
     * @see $created
     */
    public function createdDateInfo($created)
    {
        return '<time datetime="' . JHtml::_('date', $created, 'c') . '" itemprop="dateCreated">' .
            JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHtml::_('date', $created, JText::_('DATE_FORMAT_LC3'))) . 
        '</time>';
    }

    /**
     * @see $modified
     */
    public function modifiedDateInfo($modified)
    {
        return '<time datetime="' . JHtml::_('date', $modified, 'c') . '" itemprop="dateModified">' .
            JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $modified, JText::_('DATE_FORMAT_LC3'))) .
        '</time>';
    }

    /**
     * @see $published
     */
    public function publishedDateInfo($published)
    {
        return '<time datetime="' . JHtml::_('date', $published, 'c') . '" itemprop="datePublished">' .
            JText::sprintf('COM_CONTENT_PUBLISHED_DATE_ON', JHtml::_('date', $published, JText::_('DATE_FORMAT_LC3'))) . 
        '</time>';
    }

    /**
     * @see $author
     */
    public function authorInfo($author, $authorLink)
    {
        if (strlen($authorLink))
            return JText::sprintf('COM_CONTENT_WRITTEN_BY',
                                  JHtml::_('link', JRoute::_($authorLink), $author, array('itemprop' => 'url')));
        return JText::sprintf('COM_CONTENT_WRITTEN_BY', $author);
    }

    public function articleSeparator() { return '<div class="item-separator">&nbsp;</div>'; }

    /**
     * @see $section, $sectionLink, $category, $categoryLink
     */
    public function categories($parentCategory, $parentCategoryLink, $category, $categoryLink)
    {
        if (0 == strlen($parentCategory) && 0 == strlen($category))
            return '';
        ob_start();
        if (strlen($parentCategory)) {
            if (strlen($parentCategoryLink)) {
                echo '<a href="' . $parentCategoryLink . '" itemprop="genre">' . $this->_component->escape($parentCategory) . '</a>';
            } else {
                echo '<span  itemprop="genre">' . $this->_component->escape($parentCategory) . '</span>';
            }
            if (strlen($category)) {
                echo ' / ';
            }
        }
        if (strlen($category)) {
            if (strlen($categoryLink)) {
                echo '<a href="' . $categoryLink . '" itemprop="genre">' . $this->_component->escape($category) . '</a>';
            } else {
                echo '<span itemprop="genre">' . $this->_component->escape($category) . '</span>';
            }
        }
        return JText::sprintf('COM_CONTENT_CATEGORY', ob_get_clean());
    }

    public function hitsInfo($hits)
    {
        return '<meta itemprop="interactionCount" content="UserPageVisits:' . $hits . '" />' .
            JText::sprintf('COM_CONTENT_ARTICLE_HITS', $hits);
    }

    public function event($name)
    {
        return $this->_article->event->{$name};
    }

    public function getArticleViewParameters()
    {
        return array('metadata-header-icons' => array(), 'metadata-footer-icons' => array());
    }

    public function article($article)
    {
        return funcPost($article);
    }
}
