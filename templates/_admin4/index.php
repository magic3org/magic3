<?php
defined('_JEXEC') or die;

/**
 * Template for Joomla! CMS, created with Artisteer.
 * See readme.txt for more details on how to use the template.
 */

//require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php';

// Create alias for $this object reference:
$document = $this;

// Shortcut for template base url:
$templateUrl = $document->baseurl . '/templates/' . $document->template;

?>
<!DOCTYPE html>
<html dir="ltr" lang="<?php echo $document->language; ?>">
<head>
    <jdoc:include type="head" />
    <link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style.css" media="screen">
    <!--[if IE]><link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/iestyles.css" media="screen"><![endif]-->
    <!--[if lt IE 9]>
    <script src="<?php echo $templateUrl; ?>/html5shiv.js"></script>
    <script src="<?php echo $templateUrl; ?>/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<jdoc:include type="modules" name="top" style="none" />
<div class="container">
<?php if ($document->countModules('left') || $document->countModules('right')): ?><div class="row"><?php endif; ?>
<?php if ($document->countModules('left')): ?>
    <div class="col-lg-3 m3sideblock"><jdoc:include type="modules" name="left" style="none" /></div>
<?php endif; ?>
<?php if ($document->countModules('left') && $document->countModules('right')): ?>
    <div class="col-lg-6 m3centerblock"><jdoc:include type="component" style="none" /></div>
<?php elseif ($document->countModules('left') || $document->countModules('right')): ?>
    <div class="col-lg-9 m3centerblock"><jdoc:include type="component" style="none" /></div>
<?php else: ?>
    <jdoc:include type="component" style="none" />
<?php endif; ?>
<?php if ($document->countModules('right')): ?>
    <div class="col-lg-3 m3sideblock"><jdoc:include type="modules" name="right" style="none" /></div>
<?php endif; ?>
<?php if ($document->countModules('left') || $document->countModules('right')): ?></div><?php endif; ?>
<jdoc:include type="modules" name="bottom" style="none" />
</div>
</body>
</html>