<?php
defined('_JEXEC') or die;

$styles = dirname(__FILE__) . '/default_styles.php';
if (file_exists($styles)) {
    ob_start();
    include_once dirname(__FILE__) . '/default_styles.php';
    JFactory::getDocument()->addCustomTag(ob_get_clean());
}

$themePath = dirname(dirname(dirname(dirname(__FILE__))));
require_once $themePath . DIRECTORY_SEPARATOR . 'functions.php';


Core::load("Core_Content");

$component = new CoreContent($this, $this->params);
$allItems = $this->items;
$all = count($allItems);

$document = JFactory::getDocument();
$document->bodyClass = 'u-body';
$document->bodyStyle = "";
$document->localFontsFile = "";
$document->backToTop=<<<BACKTOTOP

BACKTOTOP;
?>
<?php

$templatesCount = 5;
$firstRepeatable = 0;
$lastRepeatable = 3;

$funcsInfo = array(
   array('repeatable' => true, 'name' => 'blogTemplate_0', 'count' => 1),
   array('repeatable' => true, 'name' => 'blogTemplate_1', 'count' => 1),
   array('repeatable' => true, 'name' => 'blogTemplate_2', 'count' => 1),
   array('repeatable' => true, 'name' => 'blogTemplate_3', 'count' => 1),

);

$funcsStaticInfo = array(
   array('repeatable' => false, 'name' => 'blogTemplate_4', 'count' => 0),

);

if ($this->params->get('show_page_heading')) {
    echo '<section class="u-clearfix"><div class="u-clearfix u-sheet"><h1>';
    echo $this->params->get('page_heading');
    echo '</h1></div></section>';
}

if (count($funcsInfo)) {
    $indx = 0;
    $customItems = array();
    for ($i = 0; $i < $all; $i++) {
        while( ($funcInfo = $funcsInfo[$indx]) && isset($funcInfo['excluded']))
            $indx++;

        if ($funcInfo['count'] < 1) {
            include $themePath . '/views/' . $funcInfo['name'] . '.php';
            $i--;
            $indx++;
            continue;
        }

        $customItems[] = $allItems[$i];
        if ($funcInfo['count'] > count($customItems))
            continue;

        $count = count($customItems);
        $params = array();
        for ($j = 0; $j < $count; $j++) {
            $item = $customItems[$j];
            $article = $component->article('archive', $item, $item->params);

            ${'title' . $j} = strlen($article->title) ? $this->escape($article->title) : '';
            ${'titleLink' . $j} = strlen($article->titleLink) ? $article->titleLink : '';

            // Readmore button not need on archive blog
            ${'readmore' . $j} = '';
            ${'readmoreLink' . $j} = '';

            ${'shareLink' . $j} = strlen($article->shareLink) ? $article->shareLink : '';
            ${'content' . $j} = $article->intro(funcBalanceTags($article->intro));
            ${'image' . $j} = null;
            ${'tags' . $j} = null;

            ${'metadata' . $j} = array();
            if (strlen($article->author)) {
                ${'metadata' . $j}['author'] = $article->authorInfo($article->author, $article->authorLink);
            }
            if (strlen($article->published)) {
                ${'metadata' . $j}['date'] = $article->publishedDateInfo($article->published);
            }
            if (strlen($article->category)) {
                ${'metadata' . $j}['category'] = $article->categories($article->parentCategory, $article->parentCategoryLink, $article->category, $article->categoryLink);
            }
        }
        include $themePath . '/views/' . $funcInfo['name'] . '.php';

        $customItems = array();

        if ($funcInfo['repeatable'] == false) {
            if (count($funcsInfo) == 1) {
                break;
            } else {
                $funcsInfo[$indx]['excluded'] = true;
            }
        }

        if (count($funcsInfo) - 1 == $indx)
            $indx = 0;
        else
            $indx++;
    }
}

if (count($funcsStaticInfo)) {
    for ($i = 0; $i < count($funcsStaticInfo); $i++) {
        include_once $themePath . '/views/' . $funcsStaticInfo[$i]['name'] . '.php';
    }
}
?>