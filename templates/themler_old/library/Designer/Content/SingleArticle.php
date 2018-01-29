<?php
defined('_JEXEC') or die;

Designer::load("Designer_Content_Item");

class DesignerContentSingleArticle extends DesignerContentItem
{
    public $print;

    public $toc;

    public $intro;

    public $text;

    public $tags = array();

    public function __construct($component, $componentParams, $article, $articleParams, $properties)
    {
        parent::__construct($component, $componentParams, $article, $articleParams);
        $this->print = isset($properties['print']) ? $properties['print'] : '';
        $this->pageHeading = $this->_componentParams->get('show_page_heading', 1)
                               ? $this->_componentParams->get('page_heading') : '';
        $this->titleLink = $this->_articleParams->get('link_titles') && !empty($this->_article->readmore_link)
                             ? $this->_article->readmore_link : '';
        $this->emailIconVisible = $this->emailIconVisible && !$this->print;
        $this->editIconVisible = $this->editIconVisible && !$this->print;
        $this->categoryLink = $this->_articleParams->get('link_category') && $this->_article->catslug
                                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug))
                                : '';
        $this->category = $this->_articleParams->get('show_category') ? $this->_article->category_title : '';
        $this->categoryLink = $this->_articleParams->get('link_category') && $this->_article->catslug
                                ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->catslug))
                                : '';
        $this->parentCategory = $this->_articleParams->get('show_parent_category') && $this->_article->parent_slug != '1:root'
                                  ? $this->_article->parent_title : '';
        $this->parentCategoryLink = $this->_articleParams->get('link_parent_category') && $this->_article->parent_slug
                                      ? JRoute::_(ContentHelperRoute::getCategoryRoute($this->_article->parent_slug))
                                      : '';
        $this->author = $this->_articleParams->get('show_author') && !empty($this->_article->author)
                          ? ($this->_article->created_by_alias ? $this->_article->created_by_alias : $this->_article->author)
                          : '';
        if (strlen($this->author) && $this->_articleParams->get('link_author') && !empty($this->_article->contactid)) {
            $needle = 'index.php?option=com_contact&view=contact&id=' . $this->_article->contactid;
            $menu = JFactory::getApplication()->getMenu();
            $item = $menu->getItems('link', $needle, true);
            $this->authorLink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
        } else
            $this->authorLink = '';

        if ($this->_articleParams->get('show_tags', 1) && !empty($this->_article->tags)) {
            $this->_article->tagLayout = new JLayoutFile('joomla.content.tags');
            $this->_article->tags->itemTags['custom'] = true;
            $this->tags = json_decode($this->_article->tagLayout->render($this->_article->tags->itemTags), true);
        }

        $this->toc = isset($this->_article->toc) ? $this->_article->toc : '';
        $this->text = $this->_articleParams->get('access-view') ? $this->_article->text : '';
        $user = JFactory::getUser();
        $this->introVisible = !$this->_articleParams->get('access-view') && $params->get('show_noauth') && $user->get('guest');
        $this->intro = $this->_article->introtext;
        if (!$this->_articleParams->get('access-view') && $this->_articleParams->get('show_noauth') && $user->get('guest')
            && $this->_articleParams->get('show_readmore') && $this->_article->fulltext != null)
        {
            $attribs = json_decode($this->_article->attribs);
            if ($attribs->alternative_readmore == null)
                $this->readmore = JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
            elseif ($this->readmore = $this->_article->alternative_readmore) {
                if ($this->_articleParams->get('show_readmore_title', 0) != 0)
                    $this->readmore .= JHtml::_('string.truncate', ($this->_article->title), $this->_articleParams->get('readmore_limit'));
            } elseif ($this->_articleParams->get('show_readmore_title', 0) == 0)
                $this->readmore = JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
            else
                $this->readmore = JText::_('COM_CONTENT_READ_MORE')
                                    . JHtml::_('string.truncate', $this->_article->title,
                                               $this->_articleParams->get('readmore_limit'));
            $link = new JURI(JRoute::_('index.php?option=com_users&view=login'));
            $this->readmoreLink = $link->__toString();
        } else {
            $this->readmore = '';
            $this->readmoreLink = '';
        }
        $this->paginationPosition = (isset($this->_article->pagination) && $this->_article->pagination && isset($this->_article->paginationposition))
            ? (($this->_article->paginationposition ? 'below' : 'above') . ' ' . ($this->_article->paginationrelative ? 'full article' : 'text'))
            : '';
        $this->showLinks = isset($this->_article->urls) && is_string($this->_article->urls) && !empty($this->_article->urls);
        $this->isPage = false;
        if (!strlen($this->created) && !strlen($this->modified) && !strlen($this->published) && !strlen($this->author)
            && !$this->printIconVisible && !$this->emailIconVisible && !strlen($this->category)) {
            $this->isPage = true;
        }
    }

    public function printIconInfo()
    {
        $info = array();
        $info['content'] = JHTML::_($this->print ? 'icon.print_screen' : 'icon.print_popup',
            $this->_article, $this->_articleParams);
        if ($this->showIcons)
            $info['showIcon'] = true;
        else
            $info['showIcon'] = false;
        return $info;
    }

    public function toc($toc)
    {
        return $this->proccessingContent($toc);
    }

    public function intro($intro)
    {
        return $intro;
    }

    public function text($text)
    {
        return $this->proccessingContent($text);
    }

    public function pagination() {
        return $this->_article->pagination;
    }

    private function buildTabs($matches) {
        preg_match_all('|<dt\b[^>]*>[\s\S]*?<span><h3><a\b[^>]*>(.*?)</a></h3></span>[\s\S]*?</dt>|', $matches[0], $tabsMatches);
        preg_match_all('|<dd class=\\"tabs\\">([\s\S]*?)</dd>|', $matches[0], $contentsMatches);

        $tabs = $tabsMatches[1];
        $contents = $contentsMatches[1];
        $tabsCount = count($tabs);
        $tabsItems = array();

        for ($i = 0; $i < $tabsCount; $i++) {
            $item = array('id'        => $this->buildSlug($tabs[$i]),
                          'caption'   => $tabs[$i],
                          'content'   => $contents[$i]);
            $tabsItems[$i] = $item;
        }

        return funcBuildTabs($tabsItems);
    }

    private function buildSliders($content)
    {
        $sliderItems = array();
        if(preg_match_all('/<h3 class="pane-toggler title" id="[^"]*"><a href="javascript:void\(0\);"><span>([\s\S]*?)<\/span><\/a><\/h3>/', $content, $headers)) {
            foreach($headers[1] as $key => $value) {
                $sliderItems[$key]['header'] = $value;
                $content = str_replace($headers[0][$key], '', $content);
            }
        }
        if(preg_match_all('/<div class=\"pane-slider content\">([\s\S]*?)<\/div>/', $content, $contents)) {
            foreach($contents[1] as $key => $value) {
                $sliderItems[$key]['content'] = $value;
                $content = str_replace($contents[0][$key], '', $content);
            }
        }
        $slidersMatchesCount = preg_match('/<div id="[^"]*-sliders" class="pane-sliders">/', $content, $matches, PREG_OFFSET_CAPTURE);
        $openDivs = array();
        $closeDivs = array();
        if (0 !== $slidersMatchesCount) {
            $openDivs[] = $matches[0][1];
            $pos1 = $matches[0][1] + 4;
            $pos2 = $pos1;
            while(true) {
                $openDivPos = strpos($content, '<div', $pos1);
                if (false !== $openDivPos)
                    $openDivs[] = $openDivPos;
                $closeDivPos = strpos($content, '</div>', $pos2);
                if (false !== $closeDivPos)
                    $closeDivs[] = $closeDivPos;
                if (count($openDivs) === count($closeDivs)){
                    break;
                } else {
                    $pos1 = end($openDivs) + 4;
                    $pos2 = end($closeDivs) + 6;
                }
            }
            $content = substr_replace($content, funcBuildSliders($sliderItems), $matches[0][1], array_pop($closeDivs) + 6);
        }
        return $content;
    }

    private function buildPages($content) {
        $pagesItems = array();
        if (preg_match('/<div id=\"article-index\">([\s\S]*?)<\/div>/', $content, $contents)) {
            preg_match_all('/<a href=\"([^"]*)\"[^>]*>([\s\S]*?)<\/a>/', $contents[1], $matches, PREG_SET_ORDER);
            foreach($matches as $match)
                $pagesItems[] = array('href' => $match[1], 'text' => $match[2]);
            return preg_replace('/<ul>[\s\S]*<\/ul>/', funcBuildPages($pagesItems), $content);
        }
        return $content;
    }
    
    public function proccessingContent($content) {
        $plugin = JPluginHelper::getPlugin('content', 'pagebreak');
        if (count($plugin) > 0) {
            $params = new JRegistry($plugin->params);
            switch($params->get('style')) {
                case 'sliders':
                    $content = $this->buildSliders($content);
                    break;
                case 'tabs':
                    $content = preg_replace_callback('|<dl\b[^>]*>[\s\S]*</dl>|', array( &$this, 'buildTabs'), $content);
                    break;
                default:
                    $content = $this->buildPages($content);
                    break;
            }
        }
        return $content;
    }


    public function buildSlug($str) {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        return $str;
    }
}