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

  <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" type="text/css" />

  <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" type="text/css" />

  <link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/style.css" />

  <!--[if IE 6]><link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/style.ie6.css" type="text/css" media="screen" /><![endif]-->

  <script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/script.js"></script>

 </head>

<body>

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

<div class="Header">

    <div class="Header-png"></div>

    <div class="Header-jpeg"></div>

<div class="logo">

 <h1 id="name-text" class="logo-name"><a href="<?php echo $this->baseurl ?>/"><?php echo $mainframe->getCfg( 'sitename' ); ?></a></h1>

 <div id="slogan-text" class="logo-text"></div>

</div>





</div>

<jdoc:include type="modules" name="user3" />

<div class="contentLayout">

<div class="sidebar1">

<jdoc:include type="modules" name="left" style="artblock" />



</div>

<div class="content">

<?php if ($this->countModules('breadcrumb') || artxHasMessages()) : ?>

<div class="Post">

    <div class="Post-tl"></div>

    <div class="Post-tr"><div></div></div>

    <div class="Post-bl"><div></div></div>

    <div class="Post-br"><div></div></div>

    <div class="Post-tc"><div></div></div>

    <div class="Post-bc"><div></div></div>

    <div class="Post-cl"><div></div></div>

    <div class="Post-cr"><div></div></div>

    <div class="Post-cc"></div>

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



</div>

<div class="cleared"></div>

<div class="Footer">

 <div class="Footer-inner">

  <jdoc:include type="modules" name="syndicate" />

  <div class="Footer-text">

    <p>Copyright &copy; 2009 <?php echo $mainframe->getCfg( 'sitename' ); ?><br/>

All Rights Reserved</p>

</div>

 </div>

 <div class="Footer-background"></div>

</div>



    </div>

</div>

  <p class="page-footer">Template Designed by <a href="http://TroublefreeTemplates.com">TroublefreeTemplates.com</a></p>



</div>



</body> 

</html>