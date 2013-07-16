<?php
/**
 * ヘルプ変換クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: helpConv.php 3843 2010-11-19 08:27:05Z fishbone $
 * @link       http://www.magic3.org
 */
class HelpConv
{
	private $targetWidgetObj;		// 処理対象のウィジェット
	private $helpData = array();			// ヘルプデータ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * 処理対象のウィジェットを設定
	 *
	 * @param object $widgetObj	ウィジェットオブジェクト
	 * @return					なし
	 */
	function setWidget($widgetObj)
	{
		$this->targetWidgetObj = $widgetObj;
	}
	/**
	 * ヘルプ用の各言語対応のテキストを取得
	 *
	 * @param string $id		メッセージID
	 * @return string			取得テキスト
	 */
	function _($id)
	{
		if (isset($this->targetWidgetObj)){
			return $this->targetWidgetObj->_($id);
		} else {
			return '';
		}
	}
	/**
	 * ヘルプデータキーを取得
	 *
	 * @param array $helpData	ヘルプデータ
	 * @return array			ヘルプデータキー
	 */
	function getHelpKeys($helpData)
	{
		$this->helpData = $helpData;
		return array_keys($this->helpData);
	}
	/**
	 * ヘルプデータを取得
	 *
	 * @param string $key	ヘルプデータキー
	 * @return array		ヘルプデータ
	 */
	function getHelpData($key)
	{
		return $this->helpData[$key];
	}
}
?>
