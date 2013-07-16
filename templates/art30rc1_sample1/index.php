<?php

/**
 * Template for Joomla! CMS, created with Artisteer.
 * See readme.txt for more details on how to use the template.
 */

defined('_JEXEC') or die;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php';

// Create alias for $this object reference.
$document = & $this;

// Shortcut for template base url.
$templateUrl = $document->baseurl . '/templates/' . $document->template;

// Initialize version-specific view.
$view = $this->artx = ('1.6' == $GLOBALS['version']->RELEASE) ? new ArtxPage16($this) : new ArtxPage15($this);

// Decorate component with Artisteer style.
$view->componentWrapper();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $document->language; ?>" lang="<?php echo $document->language; ?>" >
<head>
 <jdoc:include type="head" />
 <link rel="stylesheet" href="<?php echo $document->baseurl; ?>/templates/system/css/system.css" type="text/css" />
 <link rel="stylesheet" href="<?php echo $document->baseurl; ?>/templates/system/css/general.css" type="text/css" />
 <link rel="stylesheet" type="text/css" href="<?php echo $templateUrl; ?>/css/template.css" media="screen" />
 <!--[if IE 6]><link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/template.ie6.css" type="text/css" media="screen" /><![endif]-->
 <!--[if IE 7]><link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/template.ie7.css" type="text/css" media="screen" /><![endif]-->
 <script type="text/javascript">if ('undefined' != typeof jQuery) document._artxJQueryBackup = jQuery;</script>
 <script type="text/javascript" src="<?php echo $templateUrl; ?>/jquery.js"></script>
 <script type="text/javascript">jQuery.noConflict();</script>
 <script type="text/javascript" src="<?php echo $templateUrl; ?>/script.js"></script>
 <script type="text/javascript">if (document._artxJQueryBackup) jQuery = document._artxJQueryBackup;</script>
</head>
<body class="<?php echo $view->bodyClass(); ?>">
<div id="art-page-background-middle-texture">
<div id="art-main">
<?php if ($view->containsModules('user3', 'extra1', 'extra2')) : ?>
<div class="art-nav">
	<div class="art-nav-l"></div>
	<div class="art-nav-r"></div>
<div class="art-nav-outer">
<div class="art-nav-wrapper">
<div class="art-nav-inner">
	<?php if ($view->containsModules('extra1')) : ?>
	<div class="art-hmenu-extra1"><?php echo $view->position('extra1'); ?></div>
	<?php endif; ?>
	<?php if ($view->containsModules('extra2')) : ?>
	<div class="art-hmenu-extra2"><?php echo $view->position('extra2'); ?></div>
	<?php endif; ?>
	<?php echo $view->position('user3'); ?>
</div>
</div>
</div>
</div>
<div class="cleared reset-box"></div>
<?php endif; ?>
<div class="art-header">
    <div class="art-header-clip">
    <div class="art-header-center">
        <div class="art-header-png"></div>
        <div class="art-header-jpeg"></div>
    </div>
    </div>
<div class="art-header-wrapper">
<div class="art-header-inner">
<div class="art-headerobject"></div>
<div class="art-logo">
 <h1 class="art-logo-name"><a href="<?php echo $document->baseurl; ?>/">Artisteer3テスト</a></h1>
 <h2 class="art-logo-text">Magic3テストテンプレート</h2>
</div>

</div>
</div>
</div>
<div class="cleared reset-box"></div><div class="art-sheet">
    <div class="art-sheet-body">
<?php echo $view->position('banner1', 'art-nostyle'); ?>
<?php echo $view->positions(array('top1' => 33, 'top2' => 33, 'top3' => 34), 'art-block'); ?>
<div class="art-content-layout">
    <div class="art-content-layout-row">
<?php if ($view->containsModules('left')) : ?>
<div class="art-layout-cell art-sidebar1">
<?php echo $view->position('left', 'art-block'); ?>

  <div class="cleared"></div>
</div>
<?php endif; ?>
<div class="art-layout-cell art-content">

<?php
  echo $view->position('banner2', 'art-nostyle');
  if ($view->containsModules('breadcrumb'))
    echo artxPost($view->position('breadcrumb'));
  echo $view->positions(array('user1' => 50, 'user2' => 50), 'art-article');
  echo $view->position('banner3', 'art-nostyle');
  if ($view->hasMessages())
    echo artxPost('<jdoc:include type="message" />');
  echo '<jdoc:include type="component" />';
  echo $view->position('banner4', 'art-nostyle');
  echo $view->positions(array('user4' => 50, 'user5' => 50), 'art-article');
  echo $view->position('banner5', 'art-nostyle');
?>

  <div class="cleared"></div>
</div>
<?php if ($view->containsModules('right')) : ?>
<div class="art-layout-cell art-sidebar2">
<?php echo $view->position('right', 'art-block'); ?>

  <div class="cleared"></div>
</div>
<?php endif; ?>

    </div>
</div>
<div class="cleared"></div>


<?php echo $view->positions(array('bottom1' => 33, 'bottom2' => 33, 'bottom3' => 34), 'art-block'); ?>
<?php echo $view->position('banner6', 'art-nostyle'); ?>

		<div class="cleared"></div>
    </div>
</div>
<div class="art-footer">
    <div class="art-footer-t"></div>
    <div class="art-footer-body">
        <div class="art-footer-center">
            <div class="art-footer-wrapper">
                <div class="art-footer-text">
                    <?php echo $view->position('syndicate'); ?>
                    <?php if ($view->containsModules('copyright')): ?>
                    <?php echo $view->position('copyright', 'art-nostyle'); ?>
                    <?php else: ?>
                    <?php ob_start(); ?>
<p><a href="#">Link1</a> | <a href="#">Link2</a> | <a href="#">Link3</a></p><p>Copyright © %YEAR%. All Rights Reserved.</p>
<div class="cleared"></div>
<p class="art-page-footer"><a href="http://www.artisteer.com/?p=joomla_templates">Joomla template</a> created with Artisteer.</p>

                    <?php echo str_replace('%YEAR%', date('Y'), ob_get_clean()); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="cleared"></div>
    </div>
</div>

</div>
</div>

<?php echo $view->position('debug'); ?>
</body>
</html>