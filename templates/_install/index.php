<?php
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php mosShowHead(); ?>
<!--<link href="../templates/_install/css/style.css" rel="stylesheet" type="text/css" />-->
<!--[if IE]><link rel="stylesheet" type="text/css" media="screen" href="../templates/_install/css/iestyles.css" /><![endif]-->
<!--[if lt IE 9]>
     <script src="../templates/_install/html5shiv.js"></script>
     <script src="../templates/_install/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
<?php m3AnchorWidget('_install'); ?>
</body>
</html>
