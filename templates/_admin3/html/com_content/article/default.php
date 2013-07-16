<?php
defined('_JEXEC') or die;

require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'functions.php';

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

Artx::load("Artx_Content");

$component = new ArtxContent($this, $this->params);
$article = $component->article('article', $this->item, $this->item->params, array('print' => $this->print));

echo $component->beginPageContainer('item-page');
if (strlen($article->pageHeading))
    echo $component->pageHeading($article->pageHeading);
$params = $article->getArticleViewParameters();
if (strlen($article->title)) {
    $params['header-text'] = $this->escape($article->title);
    if (strlen($article->titleLink))
        $params['header-link'] = $article->titleLink;
}
// Change the order of ""if"" statements to change the order of article metadata header items.
if (strlen($article->created))
    $params['metadata-header-icons'][] = "<span class=\"art-postdateicon\">" . $article->createdDateInfo($article->created) . "</span>";
if (strlen($article->modified))
    $params['metadata-header-icons'][] = "<span class=\"art-postdateicon\">" . $article->modifiedDateInfo($article->modified) . "</span>";
if (strlen($article->published))
    $params['metadata-header-icons'][] = "<span class=\"art-postdateicon\">" . $article->publishedDateInfo($article->published) . "</span>";
if (strlen($article->author))
    $params['metadata-header-icons'][] = "<span class=\"art-postauthoricon\">" . $article->authorInfo($article->author, $article->authorLink) . "</span>";
if ($article->printIconVisible)
    $params['metadata-header-icons'][] = $article->printIcon();
if ($article->emailIconVisible)
    $params['metadata-header-icons'][] = $article->emailIcon();
if ($article->editIconVisible)
    $params['metadata-header-icons'][] = $article->editIcon();
if (strlen($article->hits))
    $params['metadata-header-icons'][] = $article->hitsInfo($article->hits);
// Build article content
$content = '';
if ('above full article' === $article->paginationPosition)
    $content .= $article->pagination();
if (!$article->introVisible)
    $content .= $article->event('afterDisplayTitle');
$content .= $article->event('beforeDisplayContent');
if (strlen($article->toc))
    $content .= $article->toc($article->toc);
if (strlen($article->text)) {
    if (strlen($article->images['fulltext']['image']))
        $content .= $article->image($article->images['fulltext']);
    if ('above text' === $article->paginationPosition)
        $content .= $article->pagination();
    $content .= $article->text($article->text);
    if ('below text' === $article->paginationPosition)
        $content .= $article->pagination();
    if ($article->showLinks)
        $content .= $this->loadTemplate('links');
}
if ($article->introVisible)
    $content .= $article->intro($article->intro);
if (strlen($article->readmore))
    $content .= $article->readmore($article->readmore, $article->readmoreLink);
if ('below full article' === $article->paginationPosition)
    $content .= $article->pagination();
$content .= $article->event('afterDisplayContent');
$params['content'] = $content;
// Change the order of ""if"" statements to change the order of article metadata footer items.
if (strlen($article->category))
    $params['metadata-footer-icons'][] = "<span class=\"art-postcategoryicon\">"
        . $article->categories($article->parentCategory, $article->parentCategoryLink, $article->category, $article->categoryLink)
        . "</span>";

// Render article
echo $article->article($params);
echo $component->endPageContainer();
