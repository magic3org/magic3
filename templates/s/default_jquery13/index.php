<?php
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');

$document = $this;
$templateUrl = $document->baseurl . '/templates/' . $document->template;
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" >
<head>
<jdoc:include type="head" />
<link rel="stylesheet" type="text/css" href="<?php echo $templateUrl; ?>/jquery.mobile.theme-1.3.2.min.css" media="screen" />
</head>
<body>
<div data-role="page">
<div><jdoc:include type="modules" name="top" style="none" /></div>
<div><jdoc:include type="modules" name="center" style="none" /></div>
<div><jdoc:include type="modules" name="main" style="none" /></div>
<div><jdoc:include type="modules" name="footer" style="none" /></div>
</div>
</body>
</html>
