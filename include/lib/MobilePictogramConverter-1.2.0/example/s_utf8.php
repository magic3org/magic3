<?php
ini_set('error_reporting', E_ALL);
require_once '../MobilePictogramConverter.php';

header("Content-Type: text/html; charset=UTF-8");

/* バイナリコード */
$str    = "あ".pack("H*", "EE8081EE8082EE8083EE8084EE8085EE8086")."い";
$option = MPC_FROM_OPTION_RAW;
/* Web入力コード */
//$str    = 'あ$G!$G"い$E"$E#$E%$E#う';
//$option = MPC_FROM_OPTION_WEB;
/* 画像 */
//$str    = 'あ<img src="../img/s/18209.gif" alt="" border="0" width="15" height="15" /><img src="../img/s/18210.gif" alt="" border="0" width="15" height="15" />い<img src="../img/s/17698.gif" alt="" border="0" width="15" height="15" /><img src="../img/s/17699.gif" alt="" border="0" width="15" height="15" /><img src="../img/s/17701.gif" alt="" border="0" width="15" height="15" /><img src="../img/s/17699.gif" alt="" border="0" width="15" height="15" />う';
//$option = MPC_FROM_OPTION_IMG;

$mpc =& MobilePictogramConverter::factory($str, MPC_FROM_SOFTBANK, MPC_FROM_CHARSET_UTF8, $option);
if (is_object($mpc) == false) {
	die($mpc);
}
$mpc->setImagePath("../img/");
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>SoftBank絵文字からの変換 (UTF-8)</title>
</head>
<body>
<?php
/* ユーザーエージェントからの自動変換 */
echo "Auto :".$mpc->autoConvert()."<br />\r\n";
/* 絵文字削除 */
echo 'Delete : '.$mpc->Except().'<br />'."\r\n";
/* 文字列に含まれている絵文字の数 */
echo 'Count : '.$mpc->Count().'<br />'."\r\n";

/* モバイル表示用（Web入力コード） */
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_WEB).'<br />'."\r\n";
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_WEB).'<br />'."\r\n";
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_WEB).'<br />'."\r\n";

/* モバイル表示用（バイナリコード） */
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_RAW).'<br />'."\r\n";
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_RAW).'<br />'."\r\n";
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_RAW).'<br />'."\r\n";

/* PC表示用（画像） */
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_IMG).'<br />'."\r\n";
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_IMG).'<br />'."\r\n";
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_IMG).'<br />'."\r\n";
?>
</body>
</html>