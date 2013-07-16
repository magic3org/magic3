<?php
/**
 * test_session.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: test_session.php 1643 2009-03-26 05:45:04Z fishbone $
 * @link       http://www.magic3.org
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head><title>セッションテスト</title>
</head>
<body>
<script type="text/javascript">
<!--
function reload(){
	document.main.submit();
	return true;
}
// -->
</script>
<form method="post" name="main">
<div align="center">
<table border="0" cellpadding="0" cellspacing="0">
    <tr><td>
<?php
session_start();
if (isset($_SESSION['count'])) {
	echo '<h2>アクセス回数： ' . $_SESSION['count'] . '</h2>';
} else {
	$_SESSION['count'] = 1;
	echo '<h2>アクセス回数： 初回</h2>';
}
$_SESSION['count']++;
?>
    </td></tr>
    <tr><td align="right"><input type="button" class="button" onclick="reload();" value="更新" />
	</td></tr>
</table>
</div>
</form>
</body>
</html>
