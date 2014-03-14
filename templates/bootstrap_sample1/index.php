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
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/bootswatch_yeti_ja.min.css" media="screen">
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style.css" media="screen">
	<!--[if lt IE 9]>
	<script src="<?php echo $templateUrl; ?>/html5shiv.js"></script>
	<script src="<?php echo $templateUrl; ?>/respond.min.js"></script>
	<![endif]-->
<script type="text/javascript">
//<![CDATA[
$(function(){
    $('.button').addClass('btn btn-default');
});
//]]>
</script>
</head>
<body>
<div class="container">
<jdoc:include type="modules" name="user3" />
<div class="hidden-xs"><jdoc:include type="modules" name="top-hide" style="none" /></div>
<div class="row">
<?php if ($document->countModules('left')): ?>
    <div class="col-sm-3"><div class="row">
	<div class="col-sm-12"><jdoc:include type="modules" name="left" style="none" /></div>
	<div class="col-sm-12 hidden-xs"><jdoc:include type="modules" name="left-hide" style="none" /></div>
	</div></div>
<?php endif; ?>
<?php if ($document->countModules('left')): ?>
    <div class="col-sm-9"><div class="row">
	<div class="col-sm-12"><jdoc:include type="modules" name="banner" style="none" /></div>
	<div class="col-sm-12"><jdoc:include type="component" style="none" /></div>
	<div class="col-sm-12 hidden-xs"><jdoc:include type="modules" name="center-hide" style="none" /></div>
	</div></div>
<?php else: ?>
    <jdoc:include type="component" style="none" />
<?php endif; ?>
</div>
<jdoc:include type="modules" name="bottom" style="none" />
</div>
</body>
</html>