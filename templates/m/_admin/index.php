<?php
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');
?>
<?php m3MobileDocType(); ?>
<html>
<head>
<?php mosShowHead(); ?>
</head>
<body bgcolor="#ffffff">
<div><?php mosLoadModules('top'); ?></div>
<div><?php mosLoadModules('center'); ?></div>
<div><?php mosMainBody(); ?></div>
<div><?php mosLoadModules('footer'); ?></div>
</body>
</html>
