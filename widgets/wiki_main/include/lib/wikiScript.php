<?php
/**
 * Javascript管理クラス
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
class WikiScript
{
	private static $scriptBody;		// Javascript
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		self::$scriptBody = '';
	}
	/**
	 * オブジェクトを初期化
	 *
	 * @param object $db	DBオブジェクト
	 * @return				なし
	 */
	public static function init($db)
	{
	}
	/**
	 * スクリプト取得
	 *
	 * @return string		埋め込み用スクリプト
	 */
	public static function getScript()
	{
		$scriptTag  = '<script type="text/javascript">' . M3_NL . $script;
		$scriptTag .= '//<![CDATA[' . M3_NL;
		$scriptTag .= self::$scriptBody;
		$scriptTag = rtrim($scriptTag, M3_NL) . M3_NL;		// 改行が付加されていれば削除
		$scriptTag .= '//]]>' . M3_NL . $script;
		$scriptTag .= '</script>' . M3_NL . $script;
		return $scriptTag;
	}
	/**
	 * Javascriptを追加
	 *
	 * @param string $type			スクリプトタイプ(「selectimage」=画像選択)
	 * @param array  $replaceData	テンプレート置換データ。キーと値の連想配列。
	 * @return						なし
	 */
	public static function addScript($type, $replaceData)
	{
		global $gEnvManager;
		
		// 実行中のウィジェットを取得
		$widgetObj = $gEnvManager->getCurrentWidgetObj();
		
		switch ($type){
		case 'selectimage':
			self::$scriptBody .= $widgetObj->getParsedTemplateData('selectimage.tmpl.js', array('WikiScript', 'makeScript'), $replaceData);
			break;
		}
	}
	/**
	 * Javascriptデータ作成処理コールバック
	 *
	 * @param object $tmpl			テンプレートオブジェクト
	 * @param array  $replaceData	テンプレート置換データ。キーと値の連想配列。
	 * @param						なし
	 */
	public static function makeScript($tmpl, $replaceData)
	{
		foreach ($replaceData as $key => $value) {
			$tmpl->addVar("_tmpl", $key, $value);
		}
	}
}
?>
