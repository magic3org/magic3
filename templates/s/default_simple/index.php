<?php
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" >
<head>
<jdoc:include type="head" />
</head>
<body>
<div><jdoc:include type="modules" name="top" style="xhtml" /></div>
<div><jdoc:include type="modules" name="center" style="xhtml" /></div>
<div><jdoc:include type="component" /></div>
<div><jdoc:include type="modules" name="footer" style="xhtml" /></div>
</body>
</html>
