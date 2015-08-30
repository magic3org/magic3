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
<body class="data-control-id-55 bootstrap bd-body-5 bd-pagebackground-9">
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
	
		<?php 
    renderTemplateFromIncludes('breadcrumbs_1');
?>
	
		<div class="container data-control-id-353 bd-containereffect-27"><div class="bd-sheetstyles-8 bd-sheet-5 data-control-id-355">
    <div class="bd-container-inner">
        <img class="bd-imagestyles bd-imagelink-7   data-control-id-498587" src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/4f505997e954cdce5e1ca5fe717b4ae37efa00b4533e40759b23cde91ac9d0c0.png">
	
		<div class="data-control-id-349 bd-layoutcontainer-8">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
 bd-collapsed-gutter
                ">
                <div class="data-control-id-347 
 col-md-24">
    <div class="bd-layoutcolumn-19"><div class="bd-vertical-align-wrapper"><div class="data-control-id-868 bd-content-4">
    <div class="bd-container-inner">
        <?php
            $document = JFactory::getDocument();
            echo $document->view->renderSystemMessages();
            $document->view->componentWrapper('common');
            echo '<jdoc:include type="component" />';
        ?>
    </div>
</div></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<img class="bd-imagestyles bd-imagelink-8   data-control-id-498643" src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/311575b5da44b0957850f78cc197803c1bd4c86823e3442188ae38150b3c42a2.png">
    </div>
</div></div>
	
		<div data-animation-time="250" class="data-control-id-519020 bd-smoothscroll-3"><a href="#" class="data-control-id-2256 bd-backtotop-1">
    <span class="data-control-id-2255 bd-icon-66"></span>
</a></div>
	
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
</body>
</html>