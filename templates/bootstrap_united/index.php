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
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/bootswatch_united_ja.css" media="screen">
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style.css" media="screen">
	<?php global $gPageManager;if ($gPageManager->isLayout()): ?>
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style_layout.css" media="screen">
	<?php endif; ?>
	<!--[if lt IE 9]>
	<script src="<?php echo $templateUrl; ?>/html5shiv.js"></script>
	<script src="<?php echo $templateUrl; ?>/respond.min.js"></script>
	<![endif]-->
	<script src="<?php echo $templateUrl; ?>/m3custom.js"></script>
<?php global $gPageManager;if (!$gPageManager->isLayout()): ?>
<script type="text/javascript">
//<![CDATA[
$(function(){
	if ($(window).width() >= 768){
		if ($('#pos-slide').offset()){
			$('#pos-slide').affix({
				offset: {
					top: $('#pos-slide').offset().top -70
				}
			});
		}
	}
});
//]]>
</script>
<?php endif; ?>
</head>
<body>
<header>
<jdoc:include type="modules" name="user3" style="bootstyle" bootstyle="navbar-fixed-top" />
<div class="hidden-xs"><jdoc:include type="modules" name="header-hide" style="none" /></div>
</header>
<div class="container">
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
	<?php if ($document->countModules('top')): ?><div class="col-sm-12"><jdoc:include type="modules" name="top" style="bootblock" /></div><?php endif; ?>
	<div class="col-sm-12"><jdoc:include type="component" style="bootblock" /></div>
	<?php if ($document->countModules('bottom')): ?><div class="col-sm-12"><jdoc:include type="modules" name="bottom" style="bootblock" /></div><?php endif; ?>
	<?php if ($document->countModules('bottom-hide')): ?><div class="col-sm-12 hidden-xs"><div id="pos-slide"><jdoc:include type="modules" name="bottom-hide" style="bootblock" /></div></div><?php endif; ?>
	</div></div>
<?php else: ?>
	<?php if ($document->countModules('banner')): ?><div class="col-sm-offset-2 col-sm-8"><jdoc:include type="modules" name="banner" style="bootblock" /></div><?php endif; ?>
	<?php if ($document->countModules('top')): ?><div class="col-sm-offset-2 col-sm-8"><jdoc:include type="modules" name="top" style="bootblock" /></div><?php endif; ?>
	<div class="col-sm-offset-2 col-sm-8"><jdoc:include type="component" style="bootblock" /></div>
	<?php if ($document->countModules('bottom')): ?><div class="col-sm-offset-2 col-sm-8"><jdoc:include type="modules" name="bottom" style="bootblock" /></div><?php endif; ?>
	<?php if ($document->countModules('bottom-hide')): ?><div class="col-sm-offset-2 col-sm-8 hidden-xs"><div id="pos-slide"><jdoc:include type="modules" name="bottom-hide" style="bootblock" /></div></div><?php endif; ?>
<?php endif; ?>
</div>
</div>
<footer><jdoc:include type="modules" name="footer" style="none" /></footer>
</body>
</html>