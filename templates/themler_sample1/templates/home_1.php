<!DOCTYPE html>
<html dir="ltr">
<head>
	<meta charset="utf-8" />
    <?php
        $base = $document->getBase();
        if (!empty($base)) {
            echo '<base href="' . $base . '" />';
            $document->setBase('');
        }
    ?>
    
    <script>
    var themeHasJQuery = !!window.jQuery;
</script>
<script src="<?php echo addThemeVersion($document->templateUrl . '/jquery.js'); ?>"></script>
<script>
    window._$ = jQuery.noConflict(themeHasJQuery);
</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="<?php echo addThemeVersion($document->templateUrl . '/bootstrap.min.js'); ?>"></script>
<link class="" href='//fonts.googleapis.com/css?family=Open+Sans:300,300italic,regular,italic,600,600italic,700,700italic,800,800italic&subset=latin' rel='stylesheet' type='text/css'>
<script src="<?php echo addThemeVersion($document->templateUrl . '/CloudZoom.js'); ?>" type="text/javascript"></script>
    
    <?php echo $document->head; ?>
    <?php if ($GLOBALS['theme_settings']['is_preview'] || !file_exists($themeDir . '/css/bootstrap.min.css')) : ?>
    <link rel="stylesheet" href="<?php echo addThemeVersion($document->templateUrl . '/css/bootstrap.css'); ?>" media="screen" />
    <?php else : ?>
    <link rel="stylesheet" href="<?php echo addThemeVersion($document->templateUrl . '/css/bootstrap.min.css'); ?>" media="screen" />
    <?php endif; ?>
    <?php if ($GLOBALS['theme_settings']['is_preview'] || !file_exists($themeDir . '/css/template.min.css')) : ?>
    <link rel="stylesheet" href="<?php echo addThemeVersion($document->templateUrl . '/css/template.css'); ?>" media="screen" />
    <?php else : ?>
    <link rel="stylesheet" href="<?php echo addThemeVersion($document->templateUrl . '/css/template.min.css'); ?>" media="screen" />
    <?php endif; ?>
    <!--[if lte IE 9]>
    <link rel="stylesheet" href="<?php echo addThemeVersion($document->templateUrl . '/css/template.ie.css'); ?>" media="screen"/>
    <![endif]-->
    <script src="<?php echo addThemeVersion($document->templateUrl . '/script.js'); ?>"></script>
    <!--[if lte IE 9]>
    <script src="<?php echo addThemeVersion($document->templateUrl . '/script.ie.js'); ?>"></script>
    <![endif]-->
    
</head>
<body class=" bootstrap bd-body-1 bd-pagebackground">
    <header class=" bd-headerarea-1">
        <div class="container  bd-containereffect-18"><div class=" bd-layoutbox-4 clearfix">
    <div class="bd-container-inner">
        <div class=" bd-boxcontrol-1">
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
            <img class="bd-imagestyles bd-imagelink-3   " src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/9b411535e75f09e8596b914ec97ce47f23aec4197dd2425e9f52aa471d337dfc.png">
	
		<div class=" bd-headline-1">
    <div class="bd-container-inner">
        <h3>
            <?php $hlDoc = JFactory::getDocument(); ?>
            <a <?php echo funcBuildRoute($hlDoc->baseurl, 'href'); ?>>
            <?php echo $hlDoc->params->get('siteTitle'); ?>
            </a>
        </h3>
    </div>
</div>
	
		<div class=" bd-slogan-1">
    <div class="bd-container-inner">
        <?php echo JFactory::getDocument()->params->get('siteSlogan'); ?>
    </div>
</div>
        </div>
    </div>
</div>
	
		<?php
    renderTemplateFromIncludes('hmenu_1', array());
?>
    </div>
</div>
</div>
</header>
	
		<div class="container  bd-containereffect-1"><div id="carousel-1" class=" bd-slider-1 carousel slide">
    

    
    <div class="bd-container-inner">

    

    <div class="bd-slides carousel-inner">
        <div class=" bd-slide-1 item"
    
    
    >
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
            <?php
    renderTemplateFromIncludes('joomlaposition_7');
?>
        </div>
    </div>
</div>
	
		<div class=" bd-slide-2 item"
    
    
    >
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
            <?php
    renderTemplateFromIncludes('joomlaposition_9');
?>
        </div>
    </div>
</div>
    </div>

    
        <div class="left-button">
    <a class=" bd-carousel-2" href="#">
        <span class=" bd-icon-33"></span>
    </a>
</div>

<div class="right-button">
    <a class=" bd-carousel-2" href="#">
        <span class=" bd-icon-33"></span>
    </a>
</div>

    
    </div>

    

    <script>
        if ('undefined' !== typeof initSlider){
            initSlider('.bd-slider-1', 'left-button', 'right-button', '.bd-carousel-2', '.bd-indicators', 3000, "hover", true, true);
        }
    </script>
</div></div>
	
		<div class="container  bd-containereffect-31"><div class=" bd-layoutbox-8 clearfix">
    <div class="bd-container-inner">
        <?php
    renderTemplateFromIncludes('joomlaposition_10');
?>
    </div>
</div>
</div>
	
		<div class="container  bd-containereffect-19">
<img class="bd-imagestyles-6 bd-imagelink-1   " src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/f408ec8336adf47fec5d9e681f4ff21a1bd4c86823e3442188ae38150b3c42a2.png"></div>
	
		<div class="container  bd-containereffect-35"><div class=" bd-layoutbox-11 clearfix">
    <div class="bd-container-inner">
        <?php
    renderTemplateFromIncludes('joomlaposition_14');
?>
    </div>
</div>
</div>
	
		<div class="container  bd-containereffect-9">
<img class="bd-imagestyles bd-imagelink-2   " src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/9401844da5712bb4a955c864d91ab0b97efa00b4533e40759b23cde91ac9d0c0.png"></div>
	
		<div class="container  bd-containereffect-39"><div class=" bd-layoutbox-14 clearfix">
    <div class="bd-container-inner">
        <?php
    renderTemplateFromIncludes('joomlaposition_6');
?>
    </div>
</div>
</div>
	
		<div class="container  bd-containereffect-20">
<img class="bd-imagestyles bd-imagelink-4   " src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/50af2b6e0d2d8529d7356335861603ff1bd4c86823e3442188ae38150b3c42a2.png"></div>
	
		<footer class=" bd-footerarea-1">
        <div class="container  bd-containereffect-14"><div class=" bd-layoutbox-2 clearfix">
    <div class="bd-container-inner">
        <?php
    renderTemplateFromIncludes('joomlaposition_2');
?>
    </div>
</div>
</div>
	
		<div class="container  bd-containereffect-15">
<div class=" bd-pagefooter-1">
    <div class="bd-container-inner">
        <a href="http://www.billionthemes.com/joomla_templates" target="_blank">Joomla Template</a> created with <a href ='http://www.themler.com' target="_blank">Themler</a>.
    </div>
</div>
</div>
</footer>
	
		<div data-animation-time="250" class=" bd-smoothscroll-3"><a href="#" class=" bd-backtotop-1">
    <span class=" bd-icon-66"></span>
</a></div>
</body>
</html>