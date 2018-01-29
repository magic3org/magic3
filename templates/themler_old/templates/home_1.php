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
<link class="" href='//fonts.googleapis.com/css?family=Days+One:regular|Open+Sans:300,300italic,regular,italic,600,600italic,700,700italic,800,800italic|PT+Sans:regular,italic,700,700italic&subset=latin' rel='stylesheet' type='text/css'>
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
    <?php if(('edit' == JRequest::getVar('layout') && 'form' == JRequest::getVar('view')) ||
        ('com_config' == JRequest::getVar('option') && 'config.display.modules' == JRequest::getVar('controller'))) : ?>
    <link rel="stylesheet" href="<?php echo addThemeVersion($document->templateUrl . '/css/media.css'); ?>" media="screen" />
    <script src="<?php echo addThemeVersion($document->templateUrl . '/js/template.js'); ?>"></script>
    <?php endif; ?>
    <script src="<?php echo addThemeVersion($document->templateUrl . '/script.js'); ?>"></script>
    <!--[if lte IE 9]>
    <script src="<?php echo addThemeVersion($document->templateUrl . '/script.ie.js'); ?>"></script>
    <![endif]-->
    
</head>
<body class=" bootstrap bd-body-1 bd-pagebackground">
    <header class=" bd-headerarea-1">
        <div data-affix
     data-offset=""
     data-fix-at-screen="top"
     data-clip-at-control="top"
     
 data-enable-lg
     
 data-enable-md
     
 data-enable-sm
     
     class=" bd-affix-3"><div class=" bd-layoutbox-6 clearfix">
    <div class="bd-container-inner">
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
	
		<?php
    renderTemplateFromIncludes('hmenu_1', array());
?>
    </div>
</div>
</div>
</header>
	
		<div id="carousel-1" class="bd-slider bd-slider-1 hidden-xs bd-background-width  carousel slide bd-carousel-left">
    

    

    
        
    <div class="bd-sliderindicators-3  bd-slider-indicators"><ol class="bd-indicators-1 ">
        
        <li><a class="
 active" href="#" data-target="#carousel-1" data-slide-to="0"></a></li>
        <li><a class="" href="#" data-target="#carousel-1" data-slide-to="1"></a></li>
    </ol></div>

    <div class="bd-slides carousel-inner">
        <div class=" bd-slide-1 bd-background-width bd-slide item"
    
    
    >
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
            <div class=" bd-layoutbox-2 clearfix">
    <div class="bd-container-inner">
        
    </div>
</div>
	
		<?php
    renderTemplateFromIncludes('joomlaposition_12');
?>
        </div>
    </div>
</div>
	
		<div class=" bd-slide-2 bd-background-width bd-slide item"
    
    
    >
    <div class="bd-container-inner">
        <div class="bd-container-inner-wrapper">
            <div class=" bd-layoutbox-4 clearfix">
    <div class="bd-container-inner">
        
    </div>
</div>
	
		<?php
    renderTemplateFromIncludes('joomlaposition_7');
?>
        </div>
    </div>
</div>
    </div>

    

    

    
        <div class="bd-left-button">
    <a class=" bd-carousel-2" href="#">
        <span></span>
    </a>
</div>

<div class="bd-right-button">
    <a class=" bd-carousel-2" href="#">
        <span></span>
    </a>
</div>

    <script type="text/javascript">
        /* <![CDATA[ */
        if ('undefined' !== typeof initSlider){
            initSlider(
                '.bd-slider-1',
                'bd-left-button',
                'bd-right-button',
                '.bd-carousel-2',
                '.bd-indicators-1',
                3000,
                "hover",
                true,
                true
            );
        }
        /* ]]> */
    </script>
</div>
	
		<div class="container  bd-containereffect-1"><div class=" bd-layoutcontainer-30">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                ">
                <div class=" bd-layoutcolumn-col-20 
 col-md-6
 col-sm-12">
    <div class="bd-layoutcolumn-20"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_9');
?></div></div>
</div>
	
		<div class=" bd-layoutcolumn-col-22 
 col-md-6
 col-sm-12">
    <div class="bd-layoutcolumn-22"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_11');
?></div></div>
</div>
	
		<div class=" bd-layoutcolumn-col-31 
 col-md-6
 col-sm-12">
    <div class="bd-layoutcolumn-31"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_13');
?></div></div>
</div>
	
		<div class=" bd-layoutcolumn-col-39 
 col-md-6
 col-sm-12">
    <div class="bd-layoutcolumn-39"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_15');
?></div></div>
</div>
            </div>
        </div>
    </div>
</div></div>
	
		<div class=" bd-customhtml-78 bd-tagstyles">
    <div class="bd-container-inner bd-content-element">
        <?php
echo <<<'CUSTOM_CODE'
<hr style="width:20%; color:red; border:1px solid #f53858;">
CUSTOM_CODE;
?>
    </div>
</div>
	
		<div class=" bd-layoutcontainer-34">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                ">
                <div class=" bd-layoutcolumn-col-72 
 col-md-24">
    <div class="bd-layoutcolumn-72"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_16');
?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class=" bd-layoutcontainer-31">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                ">
                <div class=" bd-layoutcolumn-col-18 
 col-md-12">
    <div class="bd-layoutcolumn-18"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_8');
?></div></div>
</div>
	
		<div class=" bd-layoutcolumn-col-7 
 col-md-12">
    <div class="bd-layoutcolumn-7"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_14');
?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class=" bd-layoutcontainer-32">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                ">
                <div class=" bd-layoutcolumn-col-68 
 col-md-24">
    <div class="bd-layoutcolumn-68"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_10');
?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class=" bd-layoutcontainer-14">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                ">
                <div class=" bd-layoutcolumn-col-11 
 col-md-24">
    <div class="bd-layoutcolumn-11"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_17');
?></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class=" bd-layoutcontainer-25">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                ">
                <div class=" bd-layoutcolumn-col-75 
 col-md-24">
    <div class="bd-layoutcolumn-75"><div class="bd-vertical-align-wrapper"><a class="bd-iconlink-2 " href="https://www.facebook.com/">
    <span class=" bd-icon-60"></span>
</a>
	
		<a class="bd-iconlink-4 " href="https://twitter.com/">
    <span class=" bd-icon-71"></span>
</a>
	
		<a class="bd-iconlink-5 " href="https://www.linkedin.com/">
    <span class=" bd-icon-72"></span>
</a>
	
		<a class="bd-iconlink-6 " href="http://www.pinterest.com/">
    <span class=" bd-icon-73"></span>
</a>
	
		<a class="bd-iconlink-7 " href="https://plus.google.com/">
    <span class=" bd-icon-74"></span>
</a></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<div class=" bd-layoutcontainer-36">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                ">
                <div class=" bd-layoutcolumn-col-73 
 col-md-24">
    <div class="bd-layoutcolumn-73"><div class="bd-vertical-align-wrapper"><div class="bd-imagestyles bd-googlemap-1 ">
    <div class="embed-responsive" style="height: 100%; width: 100%;">
        <iframe class="embed-responsive-item"
                src="http://maps.google.com/maps?output=embed&q=Manhattan, New York&t="></iframe>
    </div>
</div></div></div>
</div>
            </div>
        </div>
    </div>
</div>
	
		<footer class=" bd-footerarea-1">
        <div class=" bd-layoutbox-3 clearfix">
    <div class="bd-container-inner">
        <div class=" bd-layoutcontainer-28">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                ">
                <div class=" bd-layoutcolumn-col-60 
 col-md-6
 col-sm-12
 col-xs-24">
    <div class="bd-layoutcolumn-60"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_2');
?></div></div>
</div>
	
		<div class=" bd-layoutcolumn-col-61 
 col-md-6
 col-sm-12
 col-xs-24">
    <div class="bd-layoutcolumn-61"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_3');
?></div></div>
</div>
	
		<div class=" bd-layoutcolumn-col-62 
 col-md-6
 col-sm-12
 col-xs-24">
    <div class="bd-layoutcolumn-62"><div class="bd-vertical-align-wrapper"><?php
    renderTemplateFromIncludes('joomlaposition_4');
?></div></div>
</div>
	
		<div class=" bd-layoutcolumn-col-63 
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
    </div>
</div>
</footer>
	
		<div class=" bd-pagefooter-1">
    <div class="bd-container-inner">
        <a href="http://www.billionthemes.com/joomla_templates" target="_blank">Joomla Template</a> created with <a href ='http://www.themler.com' target="_blank">Themler</a>.
    </div>
</div>
	
		<div data-smooth-scroll data-animation-time="250" class=" bd-smoothscroll-3"><a href="#" class=" bd-backtotop-1">
    <span class=" bd-icon-66"></span>
</a></div>
</body>
</html>