<?php
defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/functions.php';

Designer::load("Designer_Shortcodes");

$content = $this->getBuffer('component');

$content = getCustomComponentContent($this->getBuffer('component'), 'common');
$content = DesignerShortcodes::process($content);
$this->setBuffer($content, 'component');

?>
<!DOCTYPE html>
<html dir="ltr" lang="<?php echo $this->language; ?>">
<head>
 <script src="<?php echo $this->baseurl . '/templates/' . $this->template; ?>/jquery.js"></script>
 <jdoc:include type="head" />
 <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" />
 <link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" />
 <?php if ('1' == JRequest::getVar('print')) : ?>
  <link rel="stylesheet" href="<?php echo $this->baseurl . '/templates/' . $this->template; ?>/css/print.css" />
  <?php else : ?>
  <link rel="stylesheet" href="<?php echo $this->baseurl . '/templates/' . $this->template; ?>/css/bootstrap.css" />
  <link rel="stylesheet" href="<?php echo $this->baseurl . '/templates/' . $this->template; ?>/css/template.css" />
  <?php endif; ?>
</head>
<body class="contentpane">
 <jdoc:include type="message" />
 <jdoc:include type="component" />
</body>
</html>