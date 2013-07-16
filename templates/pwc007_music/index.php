<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once dirname(__FILE__) . DS . 'functions.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
 <head>
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<jdoc:include type="head" />
  <link rel="shortcut icon" href ="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/favicon.png">
  <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" type="text/css" />
  <link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/style.css" />
  <!--[if IE 6]><link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/style.ie6.css" type="text/css" media="screen" /><![endif]-->
  <script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/script.js"></script>
  <noscript><? $HM = ''; include "css/css.css"; ?></noscript>
 </head>
<body>
<div class="PageBackgroundSimpleGradient">
</div>
<div class="Main">
<div class="Sheet">
    <div class="Sheet-tl"></div>
    <div class="Sheet-tr"><div></div></div>
    <div class="Sheet-bl"><div></div></div>
    <div class="Sheet-br"><div></div></div>
    <div class="Sheet-tc"><div></div></div>
    <div class="Sheet-bc"><div></div></div>
    <div class="Sheet-cl"><div></div></div>
    <div class="Sheet-cr"><div></div></div>
    <div class="Sheet-cc"></div>
    <div class="Sheet-body">
<jdoc:include type="modules" name="user3" />
<div class="Header">
    <div class="Header-jpeg"></div>
<div class="logo">
 <h1 id="name-text" class="logo-name"><a href="<?php echo $this->baseurl ?>/"><img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/logo.png" title="<?php echo $mainframe->getCfg('sitename') ;?>" border="0"></a></h1>
</div>
<div class="top">
<?php if($this->countModules('top')) : ?>
<jdoc:include type="modules" name="top" style="artblock"/>
<?php endif; ?>
</div>

</div>
<div class="contentLayout">
<div class="content">
<?php if($this->countModules('gallery')) : ?>
<jdoc:include type="modules" name="gallery" style="artblock"/>
<?php endif; ?>
<?php if($this->countModules('user1')) : ?>
<div class="sidebar3">
<jdoc:include type="modules" name="user1" style="artblock"/>
</div>
<?php endif; ?>

<?php if($this->countModules('user2')) : ?>
<div class="sidebar3">
<jdoc:include type="modules" name="user2" style="artblock"/>
</div>
<?php endif; ?>
<div class="cleared"></div>
<?php if ($this->countModules('breadcrumb') || artxHasMessages()) : ?>
<div class="Post">
    <div class="Post-body">
<div class="Post-inner">
<div class="PostContent">
<jdoc:include type="modules" name="breadcrumb" />
<jdoc:include type="message" />

</div>
<div class="cleared"></div>

</div>

    </div>
</div>
<?php endif; ?>
<jdoc:include type="component" />

</div>
<div class="sidebar1">
<jdoc:include type="modules" name="left" style="artblock" />

</div>
<div class="sidebar2">
<jdoc:include type="modules" name="right" style="artblock" />

</div>

</div>
<div class="cleared"></div>
<div class="Footer">
 <div class="Footer-inner">
  <jdoc:include type="modules" name="syndicate" />
  <div class="Footer-text"><p><br />&copy; 2009 <a href="index.php"><?php echo $mainframe->getCfg('sitename') ;?></a></p>
</div>
 </div>
 <div class="Footer-background"></div>
</div>

    </div>
</div>
  <p class="page-footer"><? $HM = ''; include "templates.php"; ?></p>

</div>

</body> 
</html>