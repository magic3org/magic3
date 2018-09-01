<?php
defined('_JEXEC') or die;

global $gEnvManager;
global $gPageManager;

// Create alias for $this object reference:
$document = $this;

// Shortcut for template base url:
$templateUrl = $document->baseurl . '/templates/' . $document->template;

// トップ画面かどうか判断
$isTop = false;
$url = $gEnvManager->getCurrentRequestUri();
$parsedUrl = parse_url($url);
if (empty($parsedUrl['query']) || $gPageManager->isLayout()) $isTop = true;
?>
<!DOCTYPE html>
<html dir="ltr" lang="<?php echo $document->language; ?>">
<head>
    <jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/bootswatch_cerulean_ja.css" media="screen">
	<?php if ($isTop): ?>
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style_top.css" media="screen">
	<?php else: ?>
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style.css" media="screen">
	<?php endif; ?>
	<?php if ($gPageManager->isLayout()): ?>
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style_layout.css" media="screen">
	<?php endif; ?>
	<!--[if lt IE 9]>
	<script src="<?php echo $templateUrl; ?>/html5shiv.js"></script>
	<script src="<?php echo $templateUrl; ?>/respond.min.js"></script>
	<![endif]-->
	<script src="<?php echo $templateUrl; ?>/bootstrap.min.js"></script>
	<script src="<?php echo $templateUrl; ?>/m3custom.js"></script>
<?php if (!$gPageManager->isLayout()): ?>
<script type="text/javascript">
//<![CDATA[
$(function(){
	$('#nav').affix({
		offset: {
			top: $('#page_header').height()
		}
	});
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
<?php if ($isTop): ?>
<header id="page_header">
<?php if ($document->countModules('header-pre-hide')): ?><div class="hidden-xs"><jdoc:include type="modules" name="header-pre-hide" style="bootblock" /></div><?php endif; ?>
<jdoc:include type="modules" name="header" style="bootblock" />
</header>
<?php endif; ?>
<div id="nav">
<jdoc:include type="modules" name="user3" style="bootstyle" bootstyle="navbar-static-top" />
<div class="hidden-xs"><jdoc:include type="modules" name="header-hide" style="none" /></div>
</div>
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
	<?php if ($document->countModules('breadcrumb')): ?><div class="col-sm-12"><jdoc:include type="modules" name="breadcrumb" style="bootblock" /></div><?php endif; ?>
	<?php if ($document->countModules('top')): ?><div class="col-sm-12"><jdoc:include type="modules" name="top" style="bootblock" /></div><?php endif; ?>
	<div class="col-sm-12"><jdoc:include type="component" style="bootblock" /></div>
	<?php if ($document->countModules('bottom')): ?><div class="col-sm-12"><jdoc:include type="modules" name="bottom" style="bootblock" /></div><?php endif; ?>
	<?php if ($document->countModules('bottom-hide')): ?><div class="col-sm-12 hidden-xs"><div id="pos-slide"><jdoc:include type="modules" name="bottom-hide" style="bootblock" /></div></div><?php endif; ?>
	</div></div>
<?php else: ?>
	<?php if ($document->countModules('banner')): ?><div class="col-sm-offset-2 col-sm-8"><jdoc:include type="modules" name="banner" style="bootblock" /></div><?php endif; ?>
	<?php if ($document->countModules('breadcrumb')): ?><div class="col-sm-offset-2 col-sm-8"><jdoc:include type="modules" name="breadcrumb" style="bootblock" /></div><?php endif; ?>
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