<?php
defined('_JEXEC') or die('Restricted access'); // no direct access
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php';
$document = isset($this) ? $this : null;
$baseUrl = $this->baseurl;
$templateUrl = $this->baseurl . '/templates/' . $this->template;
artxComponentWrapper($document);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
 <head>
  <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<jdoc:include type="head" />
  <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" type="text/css" />

  <link rel="stylesheet" type="text/css" href="<?php echo $templateUrl; ?>/css/template.css" />
  <!--[if IE 6]><link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/template.ie6.css" type="text/css" media="screen" /><![endif]-->
  <!--[if IE 7]><link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/template.ie7.css" type="text/css" media="screen" /><![endif]-->
  <script type="text/javascript" src="<?php echo $templateUrl; ?>/script.js"></script>
 </head>
<body>
<div class="PageBackgroundGlare">
    <div class="PageBackgroundGlareImage"></div>
</div>
<div class="Main">
<div class="Sheet">
    <div class="Sheet-tl"></div>
    <div class="Sheet-tr"></div>
    <div class="Sheet-bl"></div>
    <div class="Sheet-br"></div>
    <div class="Sheet-tc"></div>
    <div class="Sheet-bc"></div>
    <div class="Sheet-cl"></div>
    <div class="Sheet-cr"></div>
    <div class="Sheet-cc"></div>
    <div class="Sheet-body">
<jdoc:include type="modules" name="user3" />
<div class="Header">
    <div class="Header-jpeg"></div>

</div>
<jdoc:include type="modules" name="banner1" style="xhtml" />
<?php echo artxPositions($document, array('top1', 'top2', 'top3'), 'artpost'); ?>
<div class="contentLayout">
<?php if (artxCountModules($document, 'left')) : ?>
<div class="sidebar1"><?php echo artxModules($document, 'left', 'artblock'); ?>
</div>
<?php endif; ?>
<div class="<?php echo artxGetContentCellStyle($document); ?>">

<?php
  echo artxModules($document, 'banner2', 'xhtml');
  if (artxCountModules($document, 'breadcrumb'))
    echo artxPost(null, artxModules($document, 'breadcrumb'));
  echo artxPositions($document, array('user1', 'user2'), 'artblock');
  echo artxModules($document, 'banner3', 'xhtml');
?>
<?php if (artxHasMessages()) : ?><div class="Post">
    <div class="Post-body">
<div class="Post-inner">
<div class="PostContent">

<jdoc:include type="message" />

</div>
<div class="cleared"></div>

</div>

    </div>
</div>
<?php endif; ?>
<jdoc:include type="component" />

<?php echo artxModules($document, 'banner4', 'xhtml'); ?>
<?php echo artxPositions($document, array('user4', 'user5'), 'artpost'); ?>
<?php echo artxModules($document, 'banner5', 'xhtml'); ?>
</div>
<?php if (artxCountModules($document, 'right')) : ?>
<div class="sidebar2"><?php echo artxModules($document, 'right', 'artblock'); ?>
</div>
<?php endif; ?>

</div>
<div class="cleared"></div>

<?php echo artxPositions($document, array('bottom1', 'bottom2', 'bottom3'), 'artblock'); ?>
<jdoc:include type="modules" name="banner6" style="xhtml" />
<div class="Footer">
 <div class="Footer-inner">
  <?php echo artxModules($document, 'syndicate'); ?>
  <div class="Footer-text">
  <?php if (artxCountModules($document, 'copyright') == 0): ?>
<p>Copyright &copy; 2009 ---.<br/>
All Rights Reserved.</p>

  <?php else: ?>
  <?php echo artxModules($document, 'copyright', 'xhtml'); ?>
  <?php endif; ?>
  </div>
 </div>
 <div class="Footer-background"></div>
</div>

    </div>
</div>
<div class="cleared"></div>
<p class="page-footer"><a href="http://www.pc-didi.at/?p=joomla_templates">Webdesign Tirol</a> designed by pc-didi.</p>
</div>

</body> 
</html>