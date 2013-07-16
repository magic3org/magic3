<?php
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

$document = null;
if (isset($this))
  $document = & $this;
$baseUrl = $this->baseurl;
$templateUrl = $this->baseurl . '/templates/' . $this->template;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
 <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
 <jdoc:include type="head" />
 <link rel="stylesheet" type="text/css" href="<?php echo $templateUrl; ?>/css/style.css" media="screen" />
 <!--[if IE]><link rel="stylesheet" href="<?php echo $templateUrl; ?>/css/iestyles.css" type="text/css" media="screen" /><![endif]-->
</head>
<body<?php global $gPageManager;echo $gPageManager->getBodyStyle(); ?>>
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
