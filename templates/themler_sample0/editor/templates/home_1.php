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
    <header class="data-control-id-751822 bd-headerarea-1">
        <?php
    renderTemplateFromIncludes('hmenu_1', array());
?>
	
		<div class="data-control-id-759 bd-boxcontrol-1">
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
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
	
		<form id="search-3" role="form" class="data-control-id-716 bd-search-3 form-inline" name="search" <?php echo funcBuildRoute(JFactory::getDocument()->baseurl . '/index.php', 'action'); ?> method="post">
    <div class="bd-container-inner">
        <input type="hidden" name="task" value="search">
        <input type="hidden" name="option" value="com_search">
        <div class="bd-search-wrapper">
            
                <input type="text" name="searchword" class="data-control-id-707 bd-bootstrapinput form-control" placeholder="Search">
                <a href="#" class="data-control-id-715 bd-icon-30" link-disable="true"></a>
        </div>
        <script>
            (function (jQuery, $) {
                jQuery('.bd-search-3 .bd-icon-30').on('click', function (e) {
                    e.preventDefault();
                    jQuery('#search-3').submit();
                });
            })(window._$, window._$);
        </script>
    </div>
</form>
        </div>
    </div>
</div>
</header>
	
		<div id="carousel-1" class="data-control-id-847 bd-slider-1 carousel slide">
    

    

    
        <div class="data-control-id-551786 bd-sliderindicators-3"><ol class="data-control-id-551785 bd-indicators-1">
    
        <li class="data-control-id-555452 bd-menuitem-5 
 active"><a href="#" data-target="#carousel-1" data-slide-to="0"></a></li>
        <li class="data-control-id-555452 bd-menuitem-5 "><a href="#" data-target="#carousel-1" data-slide-to="1"></a></li>
</ol></div>

    <div class="bd-slides carousel-inner">
        <div class="data-control-id-833 bd-slide-1 item"
    
    
    >
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
            <div class="data-control-id-953026 bd-animation-4 animated" style="display:none"data-animation-name="fadeInLeft"
                                    data-animation-event="slidein"
                                    data-animation-duration="1000ms"
                                    data-animation-delay="0ms"
                                    data-animation-infinited="false">
<div class="data-control-id-953016 bd-animation-3 animated" data-animation-name="fadeOutLeft"
                                    data-animation-event="slideout"
                                    data-animation-duration="1000ms"
                                    data-animation-delay="0ms"
                                    data-animation-infinited="false">
<img class="bd-imagestyles bd-imagelink-1 hidden-xs   data-control-id-762" src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/7c67db6270d1f35cd950cb2add81ed86default_image.png"></div>
</div>
	
		<div class="data-control-id-953079 bd-animation-6 animated" style="display:none"data-animation-name="fadeInRight"
                                    data-animation-event="slidein"
                                    data-animation-duration="1000ms"
                                    data-animation-delay="0ms"
                                    data-animation-infinited="false">
<div class="data-control-id-953069 bd-animation-5 animated" data-animation-name="fadeOutRight"
                                    data-animation-event="slideout"
                                    data-animation-duration="1000ms"
                                    data-animation-delay="0ms"
                                    data-animation-infinited="false"><div class="data-control-id-794 bd-textblock-13 bd-tagstyles">
    <p>Enjoy DESIGN,</p>
<p>while THEMLER codes!</p>
</div></div>
</div>
        </div>
    </div>
</div>
	
		<div class="data-control-id-835 bd-slide-2 item"
    
    
    >
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
            <div class="data-control-id-954546 bd-animation-10 animated" style="display:none"data-animation-name="fadeInRight"
                                    data-animation-event="slidein"
                                    data-animation-duration="1000ms"
                                    data-animation-delay="0ms"
                                    data-animation-infinited="false">
<div class="data-control-id-954536 bd-animation-9 animated" data-animation-name="fadeOutRight"
                                    data-animation-event="slideout"
                                    data-animation-duration="1000ms"
                                    data-animation-delay="0ms"
                                    data-animation-infinited="false">
<img class="bd-imagestyles bd-imagelink-2 hidden-xs   data-control-id-798" src="<?php echo JURI::base() . 'templates/' . JFactory::getApplication()->getTemplate(); ?>/images/designer/135c133a6460adbc143861a9e5acd566default_image.png"></div>
</div>
	
		<div class="data-control-id-954488 bd-animation-8 animated" style="display:none"data-animation-name="fadeInLeft"
                                    data-animation-event="slidein"
                                    data-animation-duration="1000ms"
                                    data-animation-delay="0ms"
                                    data-animation-infinited="false">
<div class="data-control-id-953134 bd-animation-7 animated" data-animation-name="fadeOutLeft"
                                    data-animation-event="slideout"
                                    data-animation-duration="1000ms"
                                    data-animation-delay="0ms"
                                    data-animation-infinited="false"><div class="data-control-id-830 bd-textblock-14 bd-tagstyles">
    <p>Think about DESIGN,</p><p>not CODE!</p>
</div></div>
</div>
        </div>
    </div>
</div>
    </div>

    

    

    
        <div class="left-button">
    <a class="data-control-id-844 bd-carousel-1" href="#">
        <span class="data-control-id-843 bd-icon-2"></span>
    </a>
</div>

<div class="right-button">
    <a class="data-control-id-844 bd-carousel-1" href="#">
        <span class="data-control-id-843 bd-icon-2"></span>
    </a>
</div>

    <script>
        if ('undefined' !== typeof initSlider){
            initSlider('.bd-slider-1', 'left-button', 'right-button', '.bd-carousel-1', '.bd-indicators-1', 3000, "hover", true, true);
        }
    </script>
</div>
	
		<div class="data-control-id-1068410 bd-stretchtobottom-1 bd-stretch-to-bottom" data-control-selector=".bd-contentlayout-9"><div class="bd-sheetstyles bd-contentlayout-9 data-control-id-954268">
    <div class="bd-container-inner">
        <div class="bd-flex-vertical">
            
            <div class="bd-flex-horizontal bd-flex-wide">
                
 <?php renderTemplateFromIncludes('sidebar_area_3'); ?>
                <div class="bd-flex-vertical bd-flex-wide">
                    
                    <div class="data-control-id-954266 bd-layoutitemsbox-27 bd-flex-wide">
    <div class="data-control-id-954269 bd-content-9">
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
	
		<footer class="data-control-id-751829 bd-footerarea-1">
        <div class="data-control-id-2241 bd-layoutcontainer-28">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class="data-control-id-2233 
 col-md-6
 col-sm-12
 col-xs-24">
    <div class="bd-layoutcolumn-60"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_2');
?></div></div>
</div>
	
		<div class="data-control-id-2235 
 col-md-6
 col-sm-12
 col-xs-24">
    <div class="bd-layoutcolumn-61"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_3');
?></div></div>
</div>
	
		<div class="data-control-id-2237 
 col-md-6
 col-sm-12
 col-xs-24">
    <div class="bd-layoutcolumn-62"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_4');
?></div></div>
</div>
	
		<div class="data-control-id-2239 
 col-md-6
 col-sm-12
 col-xs-24">
    <div class="bd-layoutcolumn-63"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_5');
?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class="data-control-id-2248 bd-pagefooter-1">
    <div class="bd-container-inner">
        <a href="http://www.billionthemes.com/joomla_templates" target="_blank">Joomla Template</a> created with <a href ='http://www.themler.com' target="_blank">Themler</a>.
    </div>
</div>
</footer>
	
		<div data-animation-time="250" class="data-control-id-491381 bd-smoothscroll-3"><a href="#" class="data-control-id-2256 bd-backtotop-1">
    <span class="data-control-id-2255 bd-icon-66"></span>
</a></div>
</body>
</html>