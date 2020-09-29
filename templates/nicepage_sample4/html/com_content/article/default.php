<?php
defined('_JEXEC') or die;

$text = $this->item->introtext;
if (property_exists($this->item, 'nicepage') && strpos($text, '<!--np_landing-->') !== false) {
    echo $text;
    return;
}

require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'functions.php';

$defaultLogo = getLogoInfo(array('src' => ''));
$logoPath = $defaultLogo['src_path'];
$logoUrl = $defaultLogo['src'];
$logoInfo = @getimagesize($logoPath);

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$document = JFactory::getDocument();

Core::load("Core_Content");

$component = new CoreContent($this, $this->params);

$article = $component->article('article', $this->item, $this->item->params, array('print' => $this->print));

$document->seoTags['og:type'] = array('tag' => 'meta', 'property' => 'og:type', 'content' => 'article');
$document->seoTags['og:title'] = array('tag' => 'meta', 'property' => 'og:title', 'content' => $article->title);

$themeParams = JFactory::getApplication()->getTemplate(true)->params;
if ($themeParams->get('jsonld', '0') == '1') {
    ob_start();

    $config = JFactory::getConfig();

    ?>
    <script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "Article",
    "articleBody": <?php echo json_encode(strip_tags($article->text($article->text))); ?>,
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "<?php echo JUri::getInstance()->toString(); ?>"
    },
    "headline": "<?php echo $article->title; ?>",
    "description": <?php echo json_encode(strip_tags($article->intro($article->intro))); ?>,
    <?php
        $image = $article->images['fulltext']['image'];
        if ($image) {
            $root = str_replace(DIRECTORY_SEPARATOR, '/', JPATH_ROOT);
            $imagesPath = $root . '/' . $image;
            $imageUrl = dirname(JURI::current()) . '/' . $image;
            $info = @getimagesize($imagesPath);

            $document->seoTags['og:image'] = array('tag' => 'meta', 'property' => 'og:image', 'content' => $imageUrl);
            $document->seoTags['og:secure_url'] = array('tag' => 'meta', 'property' => 'og:image:secure_url', 'content' => str_replace('http:', 'https:', $imageUrl));
            $document->seoTags['og:height'] = array('tag' => 'meta', 'property' => 'og:image:height', 'content' => @$info[1]);
            $document->seoTags['og:width'] = array('tag' => 'meta', 'property' => 'og:image:width', 'content' => @$info[0]);
            $alt = $article->images['fulltext']['alt'];
            if ($alt) {
                $document->seoTags['og:alt'] = array('tag' => 'meta', 'property' => 'og:image:alt', 'content' => $alt);
            }
            ?>
    "image": {
        "@type": "ImageObject",
        "url": "<?php echo $imageUrl; ?>",
        "height": <?php echo @$info[1]; ?>,
        "width": <?php echo @$info[0]; ?>
    },
    <?php } ?>
    "datePublished": "<?php echo date(DATE_ISO8601, strtotime($this->item->publish_up)); ?>",
    "dateCreated": "<?php echo date(DATE_ISO8601, strtotime($this->item->created)); ?>",
    "dateModified": "<?php echo date(DATE_ISO8601, strtotime($this->item->modified)); ?>",
    "author": {
        "@type": "Person",
        "name": "<?php echo $article->author; ?>"
    },
    "publisher": {
        "@type": "Organization",
          "logo": {
            "@type": "ImageObject",
            "height": "<?php echo @$logoInfo[1] ?>",
            "width": "<?php echo @$logoInfo[0]; ?>",
            "url": "<?php echo $logoUrl; ?>"
          },
        "name": "<?php echo $config->get('sitename'); ?>"
    }
}

    </script>
    <?php
    $articleLdJson = ob_get_clean();
    JFactory::getDocument()->addCustomTag($articleLdJson);
}
// return if post is nicepage article
if (property_exists($this->item, 'nicepage')) {
    echo $article->text($article->text);
    return;
}

$document->bodyClass = 'u-body';
$document->bodyStyle = "";
$document->localFontsFile = "";
$document->backToTop=<<<BACKTOTOP

BACKTOTOP;

$styles = dirname(__FILE__) . '/default_styles.php';
if (file_exists($styles)) {
    ob_start();
    include_once dirname(__FILE__) . '/default_styles.php';
    JFactory::getDocument()->addCustomTag(ob_get_clean());
}

echo $component->pageHeading();

$index = 0;
${'title' . $index} = strlen($article->title) ? $this->escape($article->title) : '';
${'titleLink' . $index} = strlen($article->titleLink) ? $article->titleLink : '';
${'shareLink' . $index} = strlen($article->shareLink) ? $article->shareLink : '';
${'content' . $index} = $article->text($article->text);
${'image' . $index} = $article->images['fulltext']['image'];
${'tags' . $index} = count($article->tags) > 0 ? implode('', $article->tags) : '';

${'metadata' . $index} = array();
if (strlen($article->author)) {
    ${'metadata' . $index}['author'] = $article->authorInfo($article->author, $article->authorLink);
}
if (strlen($article->published)) {
    ${'metadata' . $index}['date'] = $article->publishedDateInfo($article->published);
}
if (strlen($article->category)) {
    ${'metadata' . $index}['category'] = $article->categories($article->parentCategory, $article->parentCategoryLink, $article->category, $article->categoryLink);
}

if ($this->item->params->get('access-edit')) {
    ${'metadata' . $index}['edit']  = $article->editIcon();
}

include_once dirname(dirname(dirname(dirname(__FILE__)))) . '/views/postTemplate_0.php';