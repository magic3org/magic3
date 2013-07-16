<?php
ini_set('error_reporting', E_ALL);
require_once '../MobilePictogramConverter.php';

header('Content-Type: text/html; charset=UTF-8');

/* バイナリコード */
$str    = pack('H*', 'EEBD89EF83B3EEB2A1EEB685EEB5A8EEB2A3EEB2A2EEB299EF83B4EF83B5EEB294EEB297EEB5ADEEB29FEEBD8BEF83B6EEB296EEBD8C').'あasdいうえお';
$option = MPC_FROM_OPTION_RAW;
/* Web入力コード */
//$str    = 'あf<img localsrc="1">い<img localsrc="2">う<img localsrc="3">え<img localsrc="4">お';
//$option = MPC_FROM_OPTION_WEB;
/* 画像 */
//$str    = 'あs<img src="../img/e/1.gif" alt="" border="0" />い<img src="../img/e/2.gif" alt="" border="0" />う<img src="../img/e/3.gif" alt="" border="0" />え<img src="../img/e/4.gif" alt="" border="0" />お';
//$option = MPC_FROM_OPTION_IMG;

$mpc =& MobilePictogramConverter::factory($str, MPC_FROM_EZWEB, MPC_FROM_CHARSET_UTF8, $option);
if (is_object($mpc) == false) {
	die($mpc);
}
$mpc->setImagePath('../img/');
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>EZweb絵文字からの変換 (UTF-8)</title>
</head>
<body>
<?php
/* ユーザーエージェントからの自動変換 */
echo 'Auto :'.$mpc->autoConvert().'<br />'."\r\n";
/* 絵文字削除 */
echo 'Delete : '.$mpc->Except().'<br />'."\r\n";
/* 文字列に含まれている絵文字の数 */
echo 'Count : '.$mpc->Count().'<br />'."\r\n";

/* モバイル表示用（Web入力コード） */
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_WEB).'<br />'."\r\n";
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_WEB).'<br />'."\r\n";
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_WEB).'<br />'."\r\n";

/* モバイル表示用（バイナリコード） */
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_RAW).'<br />'."\r\n";
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_RAW).'<br />'."\r\n";
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_RAW).'<br />'."\r\n";

/* PC表示用（画像） */
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_IMG).'<br />'."\r\n";
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_IMG).'<br />'."\r\n";
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_IMG).'<br />'."\r\n";
?>
</body>
</html>