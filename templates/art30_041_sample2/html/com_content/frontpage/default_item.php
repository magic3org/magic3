<?php
defined('_JEXEC') or die;
// Create component view for Joomla! 1.5 or 1.6.
// The classes are defined in ../../../functions.php file and encapsulate 
// version-specific queries and formatting.
$version = new JVersion();
if ($version->RELEASE == '1.5') {
    $component = new ArtxContent15($this, $this->params);
    $article = $component->articleListItem($this->item);
} else {
    $component = new ArtxContent16($this, $this->params);
    $article = $component->articleListItem($this->item);
    JHtml::addIncludePath(JPATH_COMPONENT . DS . 'helpers');
}


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
if (!$article->isPublished)
    $content .= $article->beginUnpublishedArticle();
if (!$article->showIntro)
    $content .= $article->event('afterDisplayTitle');
$content .= $article->event('beforeDisplayContent');
$content .= $article->introText();
if ($article->showReadmore)
    $content .= $article->readmore();
$content .= $article->event('afterDisplayContent');
if (!$article->isPublished)
    $content .= $article->endUnpublishedArticle();
$params['content'] = $content;
// Change the order of "if" statements to change the order of article metadata footer items.
if ($article->showParentCategory || $article->showCategory)
  $params['metadata-footer-icons'][] = $article->categories();
// Render article
echo $article->article($params);

