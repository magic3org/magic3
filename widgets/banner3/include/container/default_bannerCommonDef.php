<?php
/**
 * index.php用共通定義クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
 
class default_bannerCommonDef
{
	static $_deviceType = 0;	// デバイスタイプ
	static $_deviceTypeName = 'PC';	// デバイスタイプ名

	const BANNER_WIDGET_ID = 'banner3';		// デフォルトのバナー管理ウィジェット
	
	/**
	 * 指定デバイス向けのURLを取得
	 *
	 * @param string $srcStr		解析元文字列
	 * @return string				URL
	 */
	static function getLinkUrlByDevice($srcStr)
	{
		// デフォルトのリンクURLを取得
		self::parseLinkUrl($srcStr, $defaultUrl, $urlArray);
		$url = $defaultUrl;			// リダイレクト先URL
		if (self::$_deviceType == 2 && !empty($urlArray['s'])) $url = $urlArray['s'];			// スマートフォンの場合
		return $url;
	}
	/**
	 * リンク先URLを解析する
	 *
	 * @param string $srcStr		解析元文字列
	 * @param string $url_default	リンク先URL(デフォルト)
	 * @param array $urls			リンク先URL(デフォルト以外)
	 * @return bool					true=成功、false=失敗
	 */
	static function parseLinkUrl($srcStr, &$url_default, &$urls)
	{
		// 初期化
		$url_default = '';
		$urls = array();
		
		$urlArray = explode(';', $srcStr);
		for ($i = 0; $i < count($urlArray); $i++){
			$lineStr = trim($urlArray[$i]);
			if (empty($lineStr)) continue;
			
			$pos = strpos($lineStr, '|');
			if ($pos === false){
				$url_default = $lineStr;
			} else {
				list($deviceType, $url) = explode('|', $lineStr);
				$deviceType = trim($deviceType);
				$urls[$deviceType] = trim($url);
			}
		}
		return true;
	}
}
?>
