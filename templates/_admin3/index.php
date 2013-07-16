<?php
defined('_JEXEC') or die;

/**
 * Template for Joomla! CMS, created with Artisteer.
 * See readme.txt for more details on how to use the template.
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'functions.php';

// Create alias for $this object reference:
$document = $this;

// Shortcut for template base url:
$templateUrl = $document->baseurl . '/templates/' . $document->template;

//Artx::load("Artx_Page");

// Initialize $view:
//$view = $this->artx = new ArtxPage($this);

// Decorate component with Artisteer style:
//$view->componentWrapper();

//JHtml::_('behavior.framework', true);

?>
<!DOCTYPE html>
<html dir="ltr" lang="<?php echo $document->language; ?>">
<head>
    <jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/style.css" media="screen">
    <!--[if IE]><link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/iestyles.css" media="screen"><![endif]-->
	<!--[if lt IE 9]><script src="<?php echo $templateUrl; ?>/html5shiv.js"></script><![endif]-->
    <!-- Created by Artisteer v4.1.0.59688 -->
</head>
<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td valign="top"><jdoc:include type="modules" name="top" style="artstyle" artstyle="art-nostyle" /></td></tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
<?php if ($document->countModules('left')): ?>
    <td style="vertical-align:top;width:20%;"><jdoc:include type="modules" name="left" style="artstyle" artstyle="art-block" /></td>
<?php endif; ?>
    <td style="vertical-align:top;"><jdoc:include type="component" /></td>
<?php if ($document->countModules('right')): ?>
    <td style="vertical-align:top;width:20%;"><jdoc:include type="modules" name="right" style="artstyle" artstyle="art-block" /></td>
<?php endif; ?>
  </tr>
</table>
</body>
</html>