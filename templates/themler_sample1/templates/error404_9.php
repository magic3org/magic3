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
<body class=" bootstrap bd-body-9 bd-pagebackground-7">
    <div class="container  bd-containereffect-10"><div class="bd-sheetstyles bd-sheet-9 ">
    <div class="bd-container-inner">
        <div class=" bd-layoutcontainer-11">
    <div class="bd-container-inner">
        <div class="container-fluid">
            <div class="row
                
                
 bd-row-align-top
                
                ">
                <div class=" 
 col-md-24">
    <div class="bd-layoutcolumn-24"><div class="bd-vertical-align-wrapper"><?php
// set messages
$title   = $this->title;

$code = 'Code';
$msg = 'Message';

if (method_exists($this->error, 'get' . $code))
$error = $this->error->{'get' . $code}();
else
$error = $this->error->get(strtolower($code));

if (method_exists($this->error, 'get' . $msg))
$message = $this->error->{'get' . $msg}();
else
$message = $this->error->get(strtolower($msg));


?>

<div class=" bd-text404-41 bd-tagstyles shape-only">
<?php  echo $error . ' - ' . $message ?>
</div></div></div>
</div>
            </div>
        </div>
    </div>
</div>
    </div>
</div></div>
</body>
</html>