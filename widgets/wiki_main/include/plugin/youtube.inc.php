<?php
/**
 * YouTubeプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
function plugin_youtube_convert()
{
	// パラメータエラーチェック
	if (func_num_args() < 1) return false;
	
	$args = func_get_args();
	$movieId = $args[0];			// 動画ID
	$align = strtolower($args[1]);	// 動画位置(left=左寄せ,center=中央,right=右寄せ
	if (empty($align)) $align = 'left';
	$size = strtolower($args[2]);	// 動画サイズ(small(560x315),medium(640x360),large(835x480),extralarge(1280x720))
	if (empty($size)) $size = 'medium';
	
	$outerStyle = '';
	switch ($align){
	case 'left':
	case 'center':
	case 'right':
		$outerStyle .= $align . ';';
		break;
	default:
		$outerStyle .= 'left' . ';';
		break;
	}
	
	switch ($size){
	case 'small':
		$width = 560;
		$height = 315;
		break;
	case 'medium':
	default:
		$width = 640;
		$height = 360;
		break;
	case 'large':
		$width = 835;
		$height = 480;
		break;
	case 'extralarge':
		$width = 1280;
		$height = 720;
		break;
	}

	$body  = '<div class="youtube-wrap" style="text-align:' . $outerStyle . '"><iframe class="youtube" width="' . $width . '" height="' . $height . '" src="https://www.youtube.com/embed/' . $movieId . '" frameborder="0" allowfullscreen></iframe></div>';
	return $body;
}
?>
