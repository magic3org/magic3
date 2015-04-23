<?php
defined('_JEXEC') or die;

Artx::load("Artx_Content_ListItem");

class ArtxContentFeaturedArticle extends ArtxContentListItem
{
    public $tags = '';
    
    public function __construct($component, $componentParams, $article, $articleParams)
    {
        parent::__construct($component, $componentParams, $article, $articleParams);
        $this->category = $this->_articleParams->get('show_category') ? $this->_article->category_title : '';
        $this->categoryLink = $this->_articleParams->get('link_category') && $this->_article->catslug
                                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug))
                                : '';
        $this->parentCategory = $this->_articleParams->get('show_parent_category') && $this->_article->parent_id != 1
                                  ? $this->_article->parent_title : '';
        $this->parentCategoryLink = $this->_articleParams->get('link_parent_category') && $this->_article->parent_slug
                                      ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->parent_slug))
                                      : '';
        if (version_compare(JVERSION, '3.1.0') >= 0) {
            $useDefList = ($this->_articleParams->get('show_modify_date') || 
                $this->_articleParams->get('show_publish_date') || 
                $this->_articleParams->get('show_create_date') || 
                $this->_articleParams->get('show_hits') || 
                $this->_articleParams->get('show_category') || 
                $this->_articleParams->get('show_parent_category') || 
                $this->_articleParams->get('show_author'));
            $info = $this->_article->params->get('info_block_position', 0);    
            if ($useDefList && ($info == 1 ||  $info == 2) && $this->_component->get('show_tags', 1) && !empty($this->_article->tags)) {
                $this->_article->tagLayout = new JLayoutFile('joomla.content.tags');
                $this->tags = $this->_article->tagLayout->render($this->_article->tags->itemTags);
            }
        }
    }
}
