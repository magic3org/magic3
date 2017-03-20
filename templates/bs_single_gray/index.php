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
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style.css" media="screen">

	<link href="<?php echo $templateUrl; ?>/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
	<link href="<?php echo $templateUrl; ?>/css/grayscale.css" rel="stylesheet">
	<?php global $gPageManager;if ($gPageManager->isLayout()): ?>
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style_layout.css" media="screen">
	<?php endif; ?>
	<!--[if lt IE 9]>
	<script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<script src="<?php echo $templateUrl; ?>/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
	<script src="<?php echo $templateUrl; ?>/js/grayscale.js"></script>
	<script src="<?php echo $templateUrl; ?>/js/m3custom.js"></script>
</head>
<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">
    <!-- Navigation -->
    <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <jdoc:include type="modules" name="menutitle" style="boottitle" />
                <jdoc:include type="modules" name="brand" style="bootbrand" />
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <!--<ul class="nav navbar-nav">
                    <li>
                        <a class="page-scroll" href="#about">About</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#photo">Photo</a>
                    </li>
                    <li>
                        <a class="page-scroll" href="#contact">Contact</a>
                    </li>
                </ul>-->
				<jdoc:include type="navmenu" name="hmenu" />
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Intro Header -->
    <header class="intro">
        <div class="intro-body">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <jdoc:include type="modules" name="header" style="bootheader" />
                        <a href="#about" class="btn btn-circle page-scroll"><i class="fa fa-angle-double-down animated"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- About Section -->
    <section id="about" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
				<jdoc:include type="modules" name="about" style="bootblock" />
            </div>
        </div>
    </section>

    <!-- Photo Section -->
	<?php if ($document->countModules('photo')): ?>
    <section id="photo" class="content-section text-center">
        <div class="photo-section">
            <div class="container">
                <div class="col-lg-8 col-lg-offset-2">
					<jdoc:include type="modules" name="photo" style="bootblock" />
                </div>
            </div>
        </div>
    </section>
	<?php endif; ?>

    <!-- Other Sections -->
	<?php if ($document->countModules('others')): ?>
	<jdoc:include type="modules" name="others" style="bootother" />
	<?php endif; ?>
	
    <!-- Contact Section -->
	<?php if ($document->countModules('contact')): ?>
    <section id="contact" class="container content-section text-center">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
			<jdoc:include type="modules" name="contact" style="bootblock" />
            </div>
        </div>
    </section>
	<?php endif; ?>

    <!-- Map Section -->
	<?php if ($document->countModules('map')): ?>
	<div id="map"><jdoc:include type="modules" name="map" style="none" /></div>
	<?php endif; ?>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <jdoc:include type="modules" name="footer" style="none" />
        </div>
    </footer>
		
</body>
</html>