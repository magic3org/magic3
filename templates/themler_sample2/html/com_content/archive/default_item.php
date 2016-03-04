<?php
defined('_JEXEC') or die;
?>
<?php /*BEGIN_EDITOR_OPEN*/
$app = JFactory::getApplication('site');
$templateName = $app->getTemplate();

$ret = false;
$templateDir = JPATH_THEMES . '/' . $templateName;
$editorClass = $templateDir . '/app/' . 'Editor.php';

if (!$app->isAdmin() && file_exists($editorClass)) {
    require_once $templateDir . '/app/' . 'Editor.php';
    $ret = DesignerEditor::override($templateName, __FILE__);
}

if ($ret) {
    $editorDir = $templateName . '/editor';
    require($ret);
    return;
} else {
/*BEGIN_EDITOR_CLOSE*/ ?>
<?php
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'functions.php';

Designer::load("Designer_Content");
Designer::load("Designer_Shortcodes");

$component = new DesignerContent($this, $this->params);
$article = $component->article('archive', $this->item, $this->params);
$params = array();

if (strlen($article->title)) {
    $params['header-text'] = $this->escape($article->title);
    if (strlen($article->titleLink))
        $params['header-link'] = $article->titleLink;
}

// Change the order of ""if"" statements to change the order of article metadata header items.
if (strlen($article->created))
    $params['date-icons'][] = $article->createdDateInfo($article->created);
if (strlen($article->modified))
    $params['date-icons'][] = $article->modifiedDateInfo($article->modified);
if (strlen($article->published))
    $params['date-icons'][] = $article->publishedDateInfo($article->published);
if (strlen($article->author))
    $params['author-icon'] = $article->authorInfo($article->author, $article->authorLink);

if (strlen($article->hits))
    $params['hits-icons'][] = $article->hitsInfo($article->hits);

// Build article content
$content = '';
if (strlen($article->intro))
    $content .= $article->intro($article->intro);
$params['content'] = DesignerShortcodes::process($content);

// Change the order of ""if"" statements to change the order of article metadata footer items.
if (strlen($article->category))
    $params['category-icon'] = $article->categories($article->parentCategory, $article->parentCategoryLink, $article->category, $article->categoryLink);
// Render article
echo renderTemplateFromIncludes($this->articleTemplate, array($params));
?>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>