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
<body class="data-control-id-37 bootstrap bd-body-8 bd-pagebackground-6">
    <?php 
    renderTemplateFromIncludes('breadcrumbs_1');
?>
	
		<div class="container data-control-id-49435 bd-containereffect-12"><div class="bd-sheetstyles-5 bd-contentlayout-8 data-control-id-385">
    <div class="bd-container-inner">
        <div class="bd-flex-vertical">
            
            <div class="bd-flex-horizontal bd-flex-wide">
                
 <?php renderTemplateFromIncludes('sidebar_area_18'); ?>
                <div class="bd-flex-vertical bd-flex-wide">
                    
                    <div class="data-control-id-666566 bd-layoutitemsbox-17 bd-flex-wide">
    <div class="data-control-id-876 bd-content-8">
    <div class="bd-container-inner">
        <?php
            $document = JFactory::getDocument();
            echo $document->view->renderSystemMessages();
            $document->view->componentWrapper('common');
            echo '<jdoc:include type="component" />';
        ?>
    </div>
</div>
</div>
                    
                </div>
                
            </div>
            
        </div>
    </div>
</div></div>
	
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
	
		<div data-animation-time="250" class="data-control-id-519020 bd-smoothscroll-3"><a href="#" class="data-control-id-2256 bd-backtotop-1">
    <span class="data-control-id-2255 bd-icon-66"></span>
</a></div>
</body>
</html>