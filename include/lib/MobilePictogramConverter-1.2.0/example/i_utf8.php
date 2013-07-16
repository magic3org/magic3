<?php
ini_set('error_reporting', E_ALL);
require_once '../MobilePictogramConverter.php';

header('Content-Type: text/html; charset=UTF-8');

/* バイナリコード */
$str    = 'あいうえおかき';
$option = MPC_FROM_OPTION_RAW;
/* Web入力コード */
//$str    = 'あ&#63647;&#63648;い&#63649;&#63650;う&#63651;&#xE70C;え&#xE70D;&#xE70E;お&#xE70F;&#xE710;';
//$option = MPC_FROM_OPTION_WEB;
/* 画像 */
//$str    = 'あ<img src="../img/i/63647.gif" alt="" border="0" width="12" height="12" /><img src="../img/i/63648.gif" alt="" border="0" width="12" height="12" />い<img src="../img/i/63649.gif" alt="" border="0" width="12" height="12" /><img src="../img/i/63650.gif" alt="" border="0" width="12" height="12" />う<img src="../img/i/63651.gif" alt="" border="0" width="12" height="12" /><img src="../img/i/63921.gif" alt="" border="0" width="12" height="12" />え<img src="../img/i/63922.gif" alt="" border="0" width="12" height="12" /><img src="../img/i/63923.gif" alt="" border="0" width="12" height="12" />お<img src="../img/i/63924.gif" alt="" border="0" width="12" height="12" /><img src="../img/i/63925.gif" alt="" border="0" width="12" height="12" />';
//$option = MPC_FROM_OPTION_IMG;

$mpc =& MobilePictogramConverter::factory($str, MPC_FROM_FOMA, MPC_FROM_CHARSET_UTF8, $option);
if (is_object($mpc) == false) {
	die($mpc);
}
$mpc->setImagePath('../img/');
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>i-mode絵文字からの変換 (UTF-8)</title>
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
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_IMG).'<br />'."\r\n";
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_WEB).'<br />'."\r\n";
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_WEB).'<br />'."\r\n";

/* モバイル表示用（バイナリコード） */
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_RAW).'<br />'."\r\n";
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_RAW).'<br />'."\r\n";
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_RAW).'<br />'."\r\n";

/* PC表示用（画像） */
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_IMG).'<br />'."\r\n";
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_IMG).'<br />'."\r\n";
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_IMG).'<br />'."\r\n";
?>
</body>
</html>