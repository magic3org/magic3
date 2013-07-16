<?php
defined('_JEXEC') or die;

require_once dirname(__FILE__) . str_replace('/', DIRECTORY_SEPARATOR, '/../../../functions.php');

// Create component view for Joomla! 1.5 or 1.6.
// The classes are defined in ../../../functions.php file and encapsulate 
// version-specific queries and formatting.
$version = new JVersion();
if ($version->RELEASE == '1.5') {
    $component = new ArtxContent15($this, $this->params);
    $article = $component->article($this->article, $this->print);
} else {
    $component = new ArtxContent16($this, $this->params);
    $article = $component->article($this->item, $this->print);
    JHtml::addIncludePath(JPATH_COMPONENT . DS . 'helpers');
}


echo $component->beginPageContainer('item-page');
if ($component->showPageHeading && $article->title != $component->pageHeading)
    echo $component->pageHeading();
$params = $article->getArticleViewParameters();
if ($article->titleVisible) {
    $params['header-text'] = $this->escape($article->title);
    if (strlen($article->titleLink))
        $params['header-link'] = $article->titleLink;
}
// Change the order of "if" statements to change the order of article metadata header items.
if ($article->showCreateDate)
    $params['metadata-header-icons'][] = $article->createDateInfo();
if ($article->showModifyDate)
    $params['metadata-header-icons'][] = $article->modifyDateInfo();
if ($article->showPublishDate)
    $params['metadata-header-icons'][] = $article->publishDateInfo();
if ($article->showAuthor)
    $params['metadata-header-icons'][] = $article->authorInfo();
if (!$article->print && $article->showPdfIcon)
    $params['metadata-header-icons'][] = $article->pdfIcon();
if ($article->showPrintIcon)
    $params['metadata-header-icons'][] = $article->print ? $article->printScreenIcon() : $article->printPopupIcon();
if (!$article->print && $article->showEmailIcon)
    $params['metadata-header-icons'][] = $article->emailIcon();
if (!$article->print && $article->canEdit)
    $params['metadata-header-icons'][] = $article->editIcon();
if ($article->showHits && $article->hits)
    $params['metadata-header-icons'][] = $article->hitsInfo();
if ($article->showUrl)
    $params['metadata-header-icons'][] = $article->urlInfo();
// Build article content
$content = '';
if (!$article->showIntro)
    $content .= $article->event('afterDisplayTitle');
$content .= $article->event('beforeDisplayContent');
$content .= $article->toc();
if ($article->showText)
    $content .= $article->content();
else if ($article->showTeaser) {
    $content .= $article->introText();
    if ($this->showReadmore)
        $content .= $article->readmore();
}
$content .= $article->event('afterDisplayContent');
$params['content'] = $content;
// Change the order of "if" statements to change the order of article metadata footer items.
if ($article->showParentCategory || $article->showCategory)
  $params['metadata-footer-icons'][] = $article->categories();
// Render article
echo $article->article($params);
echo $component->endPageContainer();
