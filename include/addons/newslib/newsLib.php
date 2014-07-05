<?php
/**
 * Eコマースメール連携クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(dirname(__FILE__) . '/newsLibDb.php');

class newsLib
{
	private $db;				// DB接続オブジェクト
	private $configArray;		// 新着情報定義値
	
	// DBフィールド名
	const FD_DEFAULT_MESSAGE	= 'default_message';		// デフォルトメッセージ
	const FD_DATE_FORMAT		= 'date_format';			// 日時フォーマット
	const FD_LAYOUT_LIST_ITEM	= 'layout_list_item';		// リスト項目レイアウト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new newsLibDb();
		
		$this->configArray = $this->loadConfig($this->db);
	}
	/**
	 * 新着情報定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	function loadConfig($db)
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['nc_id'];
				$value = $rows[$i]['nc_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * イベントフックメソッドを取得
	 *
	 * @param string $hookType		イベントフックタイプ
	 * @return callable				フックメソッド
	 */
	function getEventHook($hookType)
	{
		$method = null;

		switch ($hookType){
			case M3_EVENT_HOOK_TYPE_OPELOG:
				$method = array($this, '_opelogEventHook');
				break;
		}
		return $method;
	}
	/**
	 * 運用ログイベントフック
	 *
	 * @param int    $code			メッセージコード
	 * @param array  $detailParam	詳細パラメータ
	 * @param string $msg   		メッセージ
	 * @param string $msgExt   		詳細メッセージ
	 * @return 						なし
	 */
	function _opelogEventHook($code, $detailParam, $msg, $msgExt)
	{
		// コンテンツの追加、更新の場合は、新着情報を作成する
		switch ($code){
			case 2400:		// コンテンツ追加
			case 2401:		// コンテンツ更新
			// case 2402:	// コンテンツ削除
				$contentType = $detailParam[M3_EVENT_HOOK_PARAM_CONTENT_TYPE];
				$contentId = $detailParam[M3_EVENT_HOOK_PARAM_CONTENT_ID];
				$regDt = $detailParam[M3_EVENT_HOOK_PARAM_REGIST_DT];
				$url = '';
				$this->db->addNewsItem($contentType, $contentId, $this->configArray[FD_DEFAULT_MESSAGE], $url, $regDt, $newSerial);
				break;
		}
	}
}
?>
