<?php
defined('_JEXEC') or die;

// Create alias for $this object reference:
$document = $this;

// Shortcut for template base url:
$templateUrl = $document->baseurl . '/templates/' . $document->template;
?>
<!doctype html>
<html dir="ltr" lang="<?php echo $document->language; ?>">
<head>
    <jdoc:include type="head" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/default.css" media="screen">
	<link rel="stylesheet" href="<?php global $gPageManager; echo $gPageManager->getFontAwesomeUrl($document->baseurl); ?>" media="screen">
	<script src="<?php echo $templateUrl; ?>/bootstrap.min.js"></script>
</head>
<body>

<div class="container" style="height:60vh;">
    <div class="row h-100">
        <div class="col align-self-center text-center">
			<h1 class="display-3">
				<?php global $gPageManager; if ($gPageManager->getSystemHandleMode() == 10): ?>
				<i class="fas fa-tractor"></i>
				<?php else: ?>
				<i class="fas fa-sad-tear text-danger"></i>
				<?php endif; ?>
            </h1>
			<jdoc:include type="component" />
        </div>
    </div>
</div>

<footer class="footer">
    <p class="text-center">
        <span class="text-muted">powered by <a href="http://magic3.org">Magic3</a></span>
    </p>
</footer>
</body>
</html>