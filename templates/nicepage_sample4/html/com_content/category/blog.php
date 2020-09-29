<?php
defined('_JEXEC') or die;

$styles = dirname(__FILE__) . '/blog_styles.php';
if (file_exists($styles)) {
    ob_start();
    include_once dirname(__FILE__) . '/blog_styles.php';
    JFactory::getDocument()->addCustomTag(ob_get_clean());
}

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$themePath = dirname(dirname(dirname(dirname(__FILE__))));
require_once $themePath . DIRECTORY_SEPARATOR . 'functions.php';


Core::load("Core_Content");

$component = new CoreContent($this, $this->params);
$allItems = array_merge($this->lead_items, $this->intro_items);
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

echo $component->pageHeading();

if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) {
    echo '<section class="u-clearfix"><div class="u-clearfix u-sheet"><h2>';
    echo $this->params->get('page_subheading');
    if ($this->params->get('show_category_title')) {
        echo ' <span class="subheading-category">' . $this->category->title . '</span>';
    }
    echo '</h2></div></section>';
}

if (count($funcsInfo)) {
    $indx = 0;
    $customItems = array();
    for ($i = 0; $i < $all; $i++) {
        if (!array_key_exists($indx, $funcsInfo)) {
            break;
        }
        while( ($funcInfo = $funcsInfo[$indx]) && isset($funcInfo['excluded']))
            $indx++;

        if ($funcInfo['count'] < 1) {
            include $themePath . '/views/' . $funcInfo['name'] . '.php';
            if ($indx == 0 && $funcInfo['repeatable'] === false) {
                $funcsInfo[$indx]['excluded'] = true;
            }
            $i--;
            $indx++;
            continue;
        }

        $customItems[] = $allItems[$i];
        if ($funcInfo['count'] > count($customItems))
            continue;

        $imagesEtalonItems = array();
        $imagesJsonPath = $themePath . '/views/images.json';
        if (file_exists($imagesJsonPath)) {
            ob_start();
            include_once $imagesJsonPath;
            $imagesEtalonItems = json_decode(ob_get_clean(), true);
        }
        $imagesEtalonItem = isset($imagesEtalonItems[$funcInfo['name']]) ? $imagesEtalonItems[$funcInfo['name']] : array();

        for ($j = 0; $j < count($customItems); $j++) {
            $item = $customItems[$j];
            $article = $component->article('category', $item, $item->params);

            ${'title' . $j} = strlen($article->title) ? $this->escape($article->title) : '';
            ${'titleLink' . $j} = strlen($article->titleLink) ? $article->titleLink : '';
            ${'readmore' . $j} = strlen($article->readmore) ? $article->readmore : '';
            ${'readmoreLink' . $j} = strlen($article->readmoreLink) ? $article->readmoreLink : '';
            ${'shareLink' . $j} = strlen($article->shareLink) ? $article->shareLink : '';
            ${'content' . $j} = $article->intro(funcBalanceTags($article->intro));
            if ($article->images['intro']['image']) {
                $image = $article->images['intro']['image'];
            } else {
                $imagesPostItem = property_exists($item, 'pageIntroImgStruct') ? $item-> pageIntroImgStruct : array();
                $image = getProportionImage($imagesPostItem, $imagesEtalonItem);
            }
            ${'image' . $j} = $image;
            ${'tags' . $j} = count($article->tags) > 0 ? implode('', $article->tags) : '';

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
            if ($item->params->get('access-edit')) {
                ${'metadata' . $j}['edit']  = $article->editIcon();
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