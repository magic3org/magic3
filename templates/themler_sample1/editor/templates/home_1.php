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
<link class="data-control-id-9" href='//fonts.googleapis.com/css?family=Open+Sans:300,300italic,regular,italic,600,600italic,700,700italic,800,800italic&subset=latin' rel='stylesheet' type='text/css'>
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
<body class="data-control-id-13 bootstrap bd-body-1 bd-pagebackground">
    <header class="data-control-id-694120 bd-headerarea-1">
        <div class="container data-control-id-694183 bd-containereffect-18"><div class="data-control-id-694145 bd-layoutbox-4 clearfix">
    <div class="bd-container-inner">
        <div class="data-control-id-450251 bd-boxcontrol-1">
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
            <img class="bd-imagestyles bd-imagelink-3   data-control-id-450271" src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/9b411535e75f09e8596b914ec97ce47f23aec4197dd2425e9f52aa471d337dfc.png">
	
		<div class="data-control-id-701 bd-headline-1">
    <div class="bd-container-inner">
        <h3>
            <?php $hlDoc = JFactory::getDocument(); ?>
            <a <?php echo funcBuildRoute($hlDoc->baseurl, 'href'); ?>>
            <?php echo $hlDoc->params->get('siteTitle'); ?>
            </a>
        </h3>
    </div>
</div>
	
		<div class="data-control-id-702 bd-slogan-1">
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
	
		<div class="container data-control-id-449131 bd-containereffect-1"><div id="carousel-1" class="data-control-id-847 bd-slider-1 carousel slide">
    

    
    <div class="bd-container-inner">

    

    <div class="bd-slides carousel-inner">
        <div class="data-control-id-833 bd-slide-1 item"
    
    
    >
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
            <?php
    renderTemplateFromIncludes('joomlaposition_7');
?>
        </div>
    </div>
</div>
	
		<div class="data-control-id-835 bd-slide-2 item"
    
    
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
    <a class="data-control-id-844 bd-carousel-2" href="#">
        <span class="data-control-id-843 bd-icon-33"></span>
    </a>
</div>

<div class="right-button">
    <a class="data-control-id-844 bd-carousel-2" href="#">
        <span class="data-control-id-843 bd-icon-33"></span>
    </a>
</div>

    
    </div>

    

    <script>
        if ('undefined' !== typeof initSlider){
            initSlider('.bd-slider-1', 'left-button', 'right-button', '.bd-carousel-2', '.bd-indicators', 3000, "hover", true, true);
        }
    </script>
</div></div>
	
		<div class="container data-control-id-695104 bd-containereffect-31"><div class="data-control-id-695070 bd-layoutbox-8 clearfix">
    <div class="bd-container-inner">
        <?php
    renderTemplateFromIncludes('joomlaposition_10');
?>
    </div>
</div>
</div>
	
		<div class="container data-control-id-461195 bd-containereffect-19">
<img class="bd-imagestyles-6 bd-imagelink-1   data-control-id-461183" src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/f408ec8336adf47fec5d9e681f4ff21a1bd4c86823e3442188ae38150b3c42a2.png"></div>
	
		<div class="container data-control-id-695183 bd-containereffect-35"><div class="data-control-id-695149 bd-layoutbox-11 clearfix">
    <div class="bd-container-inner">
        <?php
    renderTemplateFromIncludes('joomlaposition_14');
?>
    </div>
</div>
</div>
	
		<div class="container data-control-id-482827 bd-containereffect-9">
<img class="bd-imagestyles bd-imagelink-2   data-control-id-482825" src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/9401844da5712bb4a955c864d91ab0b97efa00b4533e40759b23cde91ac9d0c0.png"></div>
	
		<div class="container data-control-id-695262 bd-containereffect-39"><div class="data-control-id-695228 bd-layoutbox-14 clearfix">
    <div class="bd-container-inner">
        <?php
    renderTemplateFromIncludes('joomlaposition_6');
?>
    </div>
</div>
</div>
	
		<div class="container data-control-id-483166 bd-containereffect-20">
<img class="bd-imagestyles bd-imagelink-4   data-control-id-483164" src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/50af2b6e0d2d8529d7356335861603ff1bd4c86823e3442188ae38150b3c42a2.png"></div>
	
		<footer class="data-control-id-694130 bd-footerarea-1">
        <div class="container data-control-id-2243 bd-containereffect-14"><div class="data-control-id-2245 bd-layoutbox-2 clearfix">
    <div class="bd-container-inner">
        <?php
    renderTemplateFromIncludes('joomlaposition_2');
?>
    </div>
</div>
</div>
	
		<div class="container data-control-id-2246 bd-containereffect-15">
<div class="data-control-id-2248 bd-pagefooter-1">
    <div class="bd-container-inner">
        <a href="http://www.billionthemes.com/joomla_templates" target="_blank">Joomla Template</a> created with <a href ='http://www.themler.com' target="_blank">Themler</a>.
    </div>
</div>
</div>
</footer>
	
		<div data-animation-time="250" class="data-control-id-519020 bd-smoothscroll-3"><a href="#" class="data-control-id-2256 bd-backtotop-1">
    <span class="data-control-id-2255 bd-icon-66"></span>
</a></div>
</body>
</html>