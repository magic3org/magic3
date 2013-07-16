<?php
// 直接アクセスの防止
defined('M3_SYSTEM') or die('Access error: Direct access denied.');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<?php mosShowHead(); ?>
<link href="<?php echo $mosConfig_live_site; ?>/templates/_admin/css/style.css" rel="stylesheet" type="text/css" />
<!--[if IE]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $mosConfig_live_site;?>/templates/_admin/css/iestyles.css" /><![endif]-->
</head>
<body<?php mosBodyStyle(); ?>>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td valign="top"><?php mosLoadModules('top'); ?></td></tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
	<?php if (mosCountModules ('left')){ ?>
    <td style="vertical-align:top;"><?php mosLoadModules('left'); ?></td>
	<?php } ?>
    <td style="vertical-align:top;"><?php mosMainBody(); ?></td>
  </tr>
</table>
</body>
</html>
