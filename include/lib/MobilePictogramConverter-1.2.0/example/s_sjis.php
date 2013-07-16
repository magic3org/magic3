<?php
ini_set('error_reporting', E_ALL);
require_once '../MobilePictogramConverter.php';

header("Content-Type: text/html; charset=Shift_JIS");

/* ƒoƒCƒiƒŠƒR[ƒh */
$str    = '‚ '.pack('H*', 'FBA1').'‚¢'.pack('H*', 'FBD1').'‚¤';
$option = MPC_FROM_OPTION_RAW;
/* Web“ü—ÍƒR[ƒh */
//$str    = '‚ $G!$G"‚¢$E"$E#$E%$E#‚¤';
//$option = MPC_FROM_OPTION_WEB;
/* ‰æ‘œ */
//$str    = '‚ <img src="../img/s/18209.gif" alt="" border="0" width="15" height="15" /><img src="../img/s/18210.gif" alt="" border="0" width="15" height="15" />‚¢<img src="../img/s/17698.gif" alt="" border="0" width="15" height="15" /><img src="../img/s/17699.gif" alt="" border="0" width="15" height="15" /><img src="../img/s/17701.gif" alt="" border="0" width="15" height="15" /><img src="../img/s/17699.gif" alt="" border="0" width="15" height="15" />‚¤';
//$option = MPC_FROM_OPTION_IMG;

$mpc =& MobilePictogramConverter::factory($str, MPC_FROM_SOFTBANK, MPC_FROM_CHARSET_SJIS, $option);
if (is_object($mpc) == false) {
	die($mpc);
}
$mpc->setImagePath("../img/");
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=Shift_JIS">
<title>SoftBankŠG•¶Žš‚©‚ç‚Ì•ÏŠ· (SJIS)</title>
</head>
<body>
<?php
/* ƒ†[ƒU[ƒG[ƒWƒFƒ“ƒg‚©‚ç‚ÌŽ©“®•ÏŠ· */
echo "Auto :".$mpc->autoConvert()."<br />\r\n";
/* ŠG•¶Žšíœ */
echo 'Delete : '.$mpc->Except().'<br />'."\r\n";
/* •¶Žš—ñ‚ÉŠÜ‚Ü‚ê‚Ä‚¢‚éŠG•¶Žš‚Ì” */
echo 'Count : '.$mpc->Count().'<br />'."\r\n";

/* ƒ‚ƒoƒCƒ‹•\Ž¦—piWeb“ü—ÍƒR[ƒhj */
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_WEB).'<br />'."\r\n";
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_WEB).'<br />'."\r\n";
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_WEB).'<br />'."\r\n";

/* ƒ‚ƒoƒCƒ‹•\Ž¦—piƒoƒCƒiƒŠƒR[ƒhj */
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_RAW).'<br />'."\r\n";
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_RAW).'<br />'."\r\n";
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_RAW).'<br />'."\r\n";

/* PC•\Ž¦—pi‰æ‘œj */
//echo 'SoftBank : '.$mpc->Convert(MPC_TO_SOFTBANK, MPC_TO_OPTION_IMG).'<br />'."\r\n";
//echo 'FOMA : '    .$mpc->Convert(MPC_TO_FOMA    , MPC_TO_OPTION_IMG).'<br />'."\r\n";
//echo 'EZweb : '   .$mpc->Convert(MPC_TO_EZWEB   , MPC_TO_OPTION_IMG).'<br />'."\r\n";
?>
</body>
</html>