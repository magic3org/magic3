<?php
defined('_JEXEC') or die;

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
	<?php global $gPageManager;if ($gPageManager->isLayout()): ?>
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style_layout.css" media="screen">
	<?php endif; ?>
	<!--[if lt IE 9]>
	<script src="<?php echo $templateUrl; ?>/html5shiv.js"></script>
	<script src="<?php echo $templateUrl; ?>/respond.min.js"></script>
	<![endif]-->
<script type="text/javascript">
//<![CDATA[
$(function(){
    $('.button').addClass('btn btn-default');
	
<?php global $gPageManager;if (!$gPageManager->isLayout()): ?>
	if ($(window).width() >= 768){
		$('#pos-slide').affix({
			offset: {
				top: $('#pos-slide').offset().top -70
			}
		});
	}
<?php endif; ?>
});
//]]>
</script>
</head>
<body>
<div class="container">
<jdoc:include type="modules" name="user3" />
<div class="hidden-xs"><jdoc:include type="modules" name="top-hide" style="none" /></div>
<div class="row">
<?php if ($document->countModules('left') || $document->countModules('left-fixed') || $document->countModules('left-hide') || $document->countModules('left-slide')): ?>
    <div class="col-sm-3"><div class="row">
	<?php if ($document->countModules('left-fixed')): ?><div class="col-sm-12 hidden-xs"><div id="pos-fixed"><jdoc:include type="modules" name="left-fixed" style="bootblock" /></div></div><?php endif; ?>
	<?php if ($document->countModules('left')): ?><div class="col-sm-12"><jdoc:include type="modules" name="left" style="bootblock" /></div><?php endif; ?>
	<?php if ($document->countModules('left-hide')): ?><div class="col-sm-12 hidden-xs"><jdoc:include type="modules" name="left-hide" style="bootblock" /></div><?php endif; ?>
	<?php if ($document->countModules('left-slide')): ?><div class="col-sm-12"><div id="pos-slide"><jdoc:include type="modules" name="left-slide" style="bootblock" /></div></div><?php endif; ?>
	</div></div>
    <div class="col-sm-9"><div class="row">
	<?php if ($document->countModules('banner')): ?><div class="col-sm-12"><jdoc:include type="modules" name="banner" style="bootblock" /></div><?php endif; ?>
	<div class="col-sm-12"><jdoc:include type="component" style="bootblock" /></div>
	<?php if ($document->countModules('center-hide')): ?><div class="col-sm-12 hidden-xs"><div id="pos-slide"><jdoc:include type="modules" name="center-hide" style="bootblock" /></div></div><?php endif; ?>
	</div></div>
<?php else: ?>
	<?php if ($document->countModules('banner')): ?><div class="col-sm-12"><jdoc:include type="modules" name="banner" style="bootblock" /></div><?php endif; ?>
	<div class="col-sm-12"><jdoc:include type="component" style="bootblock" /></div>
	<?php if ($document->countModules('center-hide')): ?><div class="col-sm-12 hidden-xs"><jdoc:include type="modules" name="center-hide" style="bootblock" /></div><?php endif; ?>
<?php endif; ?>
</div>
<jdoc:include type="modules" name="bottom" style="none" />
</div>
</body>
</html>