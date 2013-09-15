<?php
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');
?>
<!DOCTYPE html>
<html>
<head>
<?php mosShowHead(); ?>
<link href="../templates/_install/css/style.css" rel="stylesheet" type="text/css" />
<!--[if lt IE 9]>
     <script src="../templates/_install/html5shiv.js"></script>
     <script src="../templates/_install/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
<?php m3AnchorWidget('_install'); ?>
</div>
</body>
</html>
