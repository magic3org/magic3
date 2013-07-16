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
    <div class="Sheet-cc"></div>
    <div class="Sheet-body">
<div class="Header">
    <div class="Header-jpeg"></div>
<div class="logo">
 <h1 id="name-text" class="logo-name"><a href="<?php echo $this->baseurl ?>/">Template</a></h1>
 <div id="slogan-text" class="logo-text">by Webstyles</div>
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
  <div class="Footer-text"><p>Copyright &copy; 2009 ---.<br/>
All Rights Reserved.</p>
</div>
 </div>
 <div class="Footer-background"></div>
</div>

    </div>
</div>
 <p class="page-footer">Powered by <a href="http://www.joomla.org">Joomla</a>! Designed by webstyles <a href="http://www.webstyles-chinese.info/">webdesign</a> and sponsored by <a href="http://www.trauringoase.de">Trauringe</a> and more...</p>
</div>

</body> 
</html>