<?php

defined('_JEXEC') or die;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php';

JHtml::_('bootstrap.framework');

$app = JFactory::getApplication();
$config = JFactory::getConfig();

$defaultLogo = getLogoInfo(array('src' => ''));

// Create alias for $this object reference:
$document = $this;

if ($app::getRouter()->getMode() == JROUTER_MODE_SEF)
{
	$document->setBase(JUri::getInstance()->toString());
}

$metaGeneratorContent = 'Nicepage 2.25.0, nicepage.com';
if ($metaGeneratorContent) {
    $document->setMetaData('generator', $metaGeneratorContent);
}

$templateUrl = $document->baseurl . '/templates/' . $document->template;
$faviconPath = "" ? $templateUrl . '/images/' . "" : '';

Core::load("Core_Page");
// Initialize $view:
$this->view = new CorePage($this);
$bodyClass = 'class="' . (isset($this->bodyClass) ? $this->bodyClass : 'u-body') . '"';
$bodyStyle = isset($this->bodyStyle) && $this->bodyStyle ? ' style="' . $this->bodyStyle . '"' : '';
$backToTop = isset($this->backToTop) && $this->backToTop ? $this->backToTop : '';
$indexDir = dirname(__FILE__);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <?php if ($faviconPath) : ?>
        <link href="<?php echo $faviconPath; ?>" rel="icon" type="image/x-icon" />
    <?php endif; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <?php echo CoreStatements::head(); ?>
    <meta name="theme-color" content="#a58243">
    <link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/default.css" media="screen" type="text/css" />
    <?php if($this->view->isFrontEditing()) : ?>
        <link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/frontediting.css" media="screen" type="text/css" />
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/template.css" media="screen" type="text/css" />
    <link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/media.css" id="theme-media-css" media="screen" type="text/css" />
    <link id="u-google-font" rel="stylesheet" href="https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i,900,900i|Raleway:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i">
    <?php include_once "$indexDir/styles.php"; ?>
    <?php if ($this->params->get('jquery', '0') == '1') : ?>
        <script src="<?php echo $templateUrl; ?>/scripts/jquery.js"></script>
    <?php endif; ?>
    <script src="<?php echo $templateUrl; ?>/scripts/script.js"></script>
    <?php if ($this->params->get('jsonld', '0') == '1') : ?>
    <script type="application/ld+json">
{
	"@context": "http://schema.org",
	"@type": "Organization",
	"name": "<?php echo $config->get('sitename'); ?>",
	"sameAs": [],
	"url": "<?php echo JUri::getInstance()->toString(); ?>"
}
</script>
    <?php
    if (JUri::getInstance()->toString() == JUri::base()) {
    ?>
    <script type="application/ld+json">
    {
      "@context": "http://schema.org",
      "@type": "WebSite",
      "name": "<?php echo $config->get('sitename'); ?>",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "<?php echo JUri::base() . 'index.php?searchword={query' . '}&option=com_search'; ?>",
        "query-input": "required name=query"
      },
      "url": "<?php echo JUri::getInstance()->toString(); ?>"
    }
    </script>
    <?php } ?>
    <?php endif; ?>
    <?php if ($this->params->get('metatags', '0') == '1') : ?>
    <?php
    renderSeoTags($document->seoTags);
    ?>
    <?php endif; ?>
    
    
    
</head>
<body <?php echo $bodyClass . $bodyStyle; ?>>
<?php $this->view->renderHeader($indexDir, $this->params); ?>
<?php $this->view->renderLayout(); ?>
<?php $this->view->renderFooter($indexDir, $this->params); ?>
<section class="u-backlink u-clearfix u-grey-80">
            <a class="u-link" href="https://nicepage.com/joomla-templates" target="_blank">
        <span>Joomla Templates</span>
            </a>
        <p class="u-text"><span>created with</span></p>
        <a class="u-link" href="https://nicepage.com/joomla" target="_blank"><span>Nicepage</span></a>.
    </section>

<?php echo $backToTop; ?>
</body>
</html>
