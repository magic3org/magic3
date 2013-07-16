<?php
/**
 * ヘルプマネージャー
 *
 * ヘルプ機能を管理する
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: helpManager.php 4964 2012-06-13 12:22:28Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class HelpManager extends Core
{
	private $db;						// DBオブジェクト
	private $currentWidgetId;			// 現在処理中のウィジェットID
	private $currentHelpKeys;			// 現在処理中のヘルプのキー
	private $helpObj;					// ヘルプオブジェクト
	const INFO_ICON_FILE = '/images/system/info.gif';			// ヘルプに付加されるアイコンファイル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
		
		$this->currentHelpKeys = array();			// 現在処理中のヘルプのキー
	}
	/**
	 * ヘルプ用データを読み込み、取得用のキー文字列を取得
	 *
	 * ヘルプ用データは、[ウィジェットID]/include/helpに格納
	 * ヘルプIDを使用した場合は「help_[ヘルプID].php」ファイルを読み込み、ヘルプIDが指定されない場合は共通データファイル「index.php」を読み込む。
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param bool $isAdd			追加モードかどうか
	 * @param string $helpId		ヘルプID
	 * @param object $widgetObj		ウィジェットオブジェクト
	 * @return array 				ヘルプデータ取得用キー
	 */
	function loadHelp($widgetId, $isAdd = false, $helpId = '', $widgetObj = NULL)
	{
		global $HELP;
		global $gEnvManager;
		
		// ウィジェットIDが異なるとき1度だけ読み込む
//		$keys = array();
		if ($widgetId != $this->currentWidgetId){
			if (empty($helpId)){		// 共通データ使用のとき
				if (!$isAdd) $HELP = array();		// データ初期化
				$helpFile = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/include/help/index.php';
				if (file_exists($helpFile)) require_once($helpFile);
				
				$this->helpObj = NULL;					// ヘルプオブジェクト
				//$keys = array_keys($HELP);
				$this->currentHelpKeys = array_keys($HELP);			// 現在処理中のヘルプのキー
			} else {
				$helpClass = 'help_' . $helpId;
				$helpFile = $gEnvManager->getWidgetsPath() . '/' . $widgetId . '/include/help/' . $helpClass . '.php';
				if (file_exists($helpFile)){
					require_once($helpFile);
					$this->helpObj = new $helpClass();					// ヘルプオブジェクト
					$this->helpObj->setWidget($widgetObj);				// ロケール変換用のウィジェットを設定
					$helpData = $this->helpObj->_setData();		// 設定されているヘルプデータを取得
					//$keys = $this->helpObj->getHelpKeys($helpData);
					$this->currentHelpKeys = $this->helpObj->getHelpKeys($helpData);			// 現在処理中のヘルプのキー
				} else {
					$this->helpObj = NULL;					// ヘルプオブジェクト
				}
			}
			$this->currentWidgetId = $widgetId;
		}
		//return $keys;
		return $this->currentHelpKeys;
	}
	/**
	 * ヘルプ用データを取得
	 *
	 * ヘルプ用データは、[ウィジェットID]/include/help/index.phpファイルに格納されている
	 *
	 * @param string $helpKey		ヘルプ取得用キー(メッセージID)
	 * @return string 				ヘルプ用データ文字列
	 */
	function getHelpText($helpKey)
	{
		global $HELP;
		global $gEnvManager;
		
		$data = '';
		if (isset($this->helpObj)){
			$helpData = $this->helpObj->getHelpData($helpKey);
			if (isset($helpData)){
				$title = $helpData['title'];// ヘルプタイトル
				$body = $helpData['body'];	// ヘルプ本文
			}
		} else {
			$title = $HELP[$helpKey]['title'];// ヘルプタイトル
			$body = $HELP[$helpKey]['body'];	// ヘルプ本文
		}
		if (!empty($title) || !empty($body)) $data = 'class="m3help" title="' . $title . '|' . $body . '"';
		return $data;
	}
	/**
	 * ヘルプ用データを作成
	 *
	 * @param string $title			ヘルプタイトル
	 * @param string $body			ヘルプ本文
	 * @return string 				ヘルプ用データ文字列
	 */
	function createHelpText($title, $body)
	{
		global $gEnvManager;
		
		$data = 'class="m3help" title="' . $title . '|' . $body . '"';
		return $data;
	}
	/**
	 * 説明用データを作成
	 *
	 * createHelpText()との違いは、表示までの時間が短い、表示位置はデフォルト表示。
	 *
	 * @param string $title			タイトル
	 * @param string $body			本文
	 * @return string 				用データ文字列
	 */
	/*function createTipText($title, $body)
	{
		global $gEnvManager;
		
		$icon = $gEnvManager->getRootUrl() . self::INFO_ICON_FILE;
		$data = 'title="cssheader=[help_head] cssbody=[help_body] header=[<img src=\'' . $icon . '\' style=\'vertical-align:middle\'>&nbsp;&nbsp;' . 
					$title . '] body=[' . $body .' ] delay=[500]" style="cursor:pointer"';
		return $data;
	}*/
}
?>
