<?php
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');
?>
<!DOCTYPE html>
<html>
<head>
<?php mosShowHead(); ?>
<link href="../templates/_install/css/default.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="container">
<?php m3AnchorWidget('_install'); ?>
</div>
</body>
</html>
