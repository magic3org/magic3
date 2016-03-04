<?php
defined('_JEXEC') or die;

Designer::load("Designer_Content_ArticleBase");

abstract class DesignerContentItem extends DesignerContentArticleBase
{
    public $isPublished;

    public $printIconVisible;

    public $emailIconVisible;

    public $editIconVisible;

    public $introVisible;

    public $readmore;

    public $readmoreLink;

    protected function __construct($component, $componentParams, $article, $articleParams)
    {
        parent::__construct($component, $componentParams, $article, $articleParams);
        $this->title = $this->_articleParams->get('show_title') ? $this->_article->title : '';
        $this->showIcons = $this->_articleParams->get('show_icons');
        $this->printIconVisible = $this->_articleParams->get('show_print_icon');
        $this->emailIconVisible = $this->_articleParams->get('show_email_icon');
        $this->editIconVisible = $this->_articleParams->get('access-edit');
        $this->introVisible = $this->_articleParams->get('show_intro');
        $this->images = $this->_buildImages($article, $articleParams);
    }

    private function _buildImages($article, $params) {
        $images = (isset($this->_article->images) && is_string($this->_article->images)) ? json_decode($this->_article->images) : null;
        return array(
            'intro' => $this->_buildImageInfo('intro', $params, $images),
            'fulltext' => $this->_buildImageInfo('fulltext', $params, $images));
    }

    private function _buildImageInfo($type, $params, $images) {
        $image = array('image' => '', 'float' => '', 'class' => '', 'caption' => '', 'alt' => '');
        if (is_null($images))
            return $image;
        $properties = array(
            'image' => 'image_' . $type,
            'float' => 'float_' . $type,
            'caption' => 'image_' . $type . '_caption',
            'alt' => 'image_' . $type . '_alt'
        );
        if (isset($images->{$properties['image']}) && !empty($images->{$properties['image']})) {
            $image['image'] = $images->{$properties['image']};
            $image['float'] = empty($images->{$properties['float']})
                ? $params->get($properties['float'])
                : $images->{$properties['float']};
            $image['class'] = 'img-' . $type . '-' . htmlspecialchars($image['float']);
            if ($images->{$properties['caption']})
                $image['caption'] = htmlspecialchars($images->{$properties['caption']});
            $image['alt'] = $images->{$properties['alt']};
        }
        return $image;
    }

    /**
     * @see $emailIconVisible
     */
    public function emailIconInfo()
    {

        $content = JHtml::_('icon.email', $this->_article, $this->_articleParams);
        return array('content' => $content, 'showIcon' => $this->showIcons ? true : false);
    }

    /**
     * @see $editIconVisible
     */
    public function editIconInfo()
    {
        $beforeChangesScript = '';
        $doc = JFactory::getDocument();
        if (count($doc->_script) > 0)
            $beforeChangesScript = $doc->_script['text/javascript'];
        $editIcon = JHtml::_('icon.edit', $this->_article, $this->_articleParams);
        $doc->_script['text/javascript'] = $beforeChangesScript;
        return array('content' => $editIcon, 'showIcon' => $this->showIcons ? true : false);
    }

    /**
     * @see $printIconVisible
     */
    public function printIconInfo()
    {
        $info = array();
        $info['content'] = JHtml::_('icon.print_popup', $this->_article, $this->_articleParams);
        if ($this->showIcons)
            $info['showIcon'] = true;
        else
            $info['showIcon'] = false;
        return $info;
    }

    /**
     * Returns decoration for unpublished articles.
     *
     * Together with endUnpublishedArticle() this function decorates
     * the unpublished article with <div class="system-unpublished">...</div>.
     * By default, this decoration is applied only to articles in lists.
     */
    public function beginUnpublishedArticle() { return '<div class="system-unpublished">'; }

    public function endUnpublishedArticle() { return '</div>'; }

    public function readmore($readmore, $readmoreLink)
    {
        return '<p class="readmore">' . funcLinkButton(array(
            'classes' => array('a' => 'readon'),
            'link' => $readmoreLink,
            'content' => str_replace(' ', '&#160;', $readmore))) . '</p>';
    }

    public function image($image) {
        $imgTagAttrs = array('src' => $image['image'], 'alt' => $image['alt'], 'itemprop' => 'image');
        if ($image['caption']) {
            $imgTagAttrs['class'] = 'caption';
            $imgTagAttrs['title'] = $image['caption'];
        }
        return funcTagBuilder('div', array('class' => $image['class']),
            funcTagBuilder('img', array('src' => $image['image'], 'alt' => $image['alt'])
                + ($image['caption'] ? array('class' => 'caption', 'title' => $image['caption']) : array())));
    }
}