<?php
defined('_JEXEC') or die;

Designer::load("Designer_Content_ListItem");

class DesignerContentCategoryArticle extends DesignerContentListItem
{
    public $tags = array();

    function __construct($component, $componentParams, $article, $articleParams)
    {
        parent::__construct($component, $componentParams, $article, $articleParams);
        $this->category = $this->_articleParams->get('show_category') ? $this->_article->category_title : '';
        $this->categoryLink = $this->_articleParams->get('link_category')
                                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catid))
                                : '';
        $this->parentCategory = $this->_articleParams->get('show_parent_category') && $this->_article->parent_id != 1
                                  ? $this->_article->parent_title : '';
        $this->parentCategoryLink = $this->_articleParams->get('link_parent_category')
                                      ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->parent_id))
                                      : '';
        if ($component->get('show_tags', 1) && !empty($this->_article->tags->itemTags)) {
            $this->_article->tags->itemTags['custom'] = true;
            $this->tags = json_decode(JLayoutHelper::render('joomla.content.tags', $this->_article->tags->itemTags), true);
        }
    }
}