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
<body class=" bootstrap bd-body-6 bd-pagebackground-10">
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
	
		<?php 
    renderTemplateFromIncludes('breadcrumbs_1');
?>
	
		<div class="container  bd-containereffect-28"><div class="bd-sheetstyles-10 bd-sheet-6 ">
    <div class="bd-container-inner">
        <img class="bd-imagestyles bd-imagelink-9   " src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/1153b78153b51dbf55b6fc41b386761b7efa00b4533e40759b23cde91ac9d0c0.png">
	
		<div class=" bd-layoutcontainer-9">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class=" 
 col-md-24">
    <div class="bd-layoutcolumn-21"><div class="bd-vertical-align-wrapper"><div class=" bd-content-6">
    <div class="bd-container-inner">
        <?php
            $document = JFactory::getDocument();
            echo $document->view->renderSystemMessages();
            $document->view->componentWrapper('blog_3');
            echo '<jdoc:include type="component" />';
        ?>
    </div>
</div></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<img class="bd-imagestyles bd-imagelink-10   " src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/57a6436315f3c8dd6cdb33fcb4d6ed941bd4c86823e3442188ae38150b3c42a2.png">
    </div>
</div></div>
	
		<div data-animation-time="250" class=" bd-smoothscroll-3"><a href="#" class=" bd-backtotop-1">
    <span class=" bd-icon-66"></span>
</a></div>
	
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
</body>
</html>