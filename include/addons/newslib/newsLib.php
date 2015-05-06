<?php
/**
 * 新着情報クラス
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
	 * @param array  $eventParam	イベント処理用パラメータ
	 * @param string $msg   		メッセージ
	 * @param string $msgExt   		詳細メッセージ
	 * @return 						なし
	 */
	function _opelogEventHook($code, $eventParam, $msg, $msgExt)
	{
		global $gEnvManager;
		
		// コンテンツの追加、更新の場合は、新着情報を作成する
		switch ($code){
			case 2400:		// コンテンツ追加
			case 2401:		// コンテンツ更新
			// case 2402:	// コンテンツ削除
				$contentType	= $eventParam[M3_EVENT_HOOK_PARAM_CONTENT_TYPE];		// コンテンツタイプ
				$contentId		= $eventParam[M3_EVENT_HOOK_PARAM_CONTENT_ID];			// コンテンツID
				$updateDt		= $eventParam[M3_EVENT_HOOK_PARAM_UPDATE_DT];			// コンテンツ更新日時
				$url = $gEnvManager->getMacroPath($this->_createContentUrl($contentType, $contentId));// パスをマクロ形式に変換;
				$this->db->addNewsItem($contentType, $contentId, $this->configArray[self::FD_DEFAULT_MESSAGE], $url, $updateDt, $newSerial);
				break;
		}
	}
	/**
	 * コンテンツリンク用のURLを作成
	 *
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentId			コンテンツID
	 * @param string					パラメータ文字列
	 */
	private function _createContentUrl($contentType, $contentId)
	{
		global $gEnvManager;
		
		$deviceType = 0;
		switch ($deviceType){		// デバイスごとの処理
			case 0:		// PC
			default:
				$url = $gEnvManager->getDefaultPcUrl();
				break;
			case 1:		// 携帯
				$url = $gEnvManager->getDefaultMobileUrl();
				break;
			case 2:		// スマートフォン
				$url = $gEnvManager->getDefaultSmartphoneUrl();
				break;
		}
		
		$param = '';
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$param = M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				$param = M3_REQUEST_PARAM_PRODUCT_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				$param = M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				$param = M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$param = $contentId;
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				$param = M3_REQUEST_PARAM_ROOM_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
			case M3_VIEW_TYPE_EVENTENTRY:			// イベント予約
				$param = M3_REQUEST_PARAM_EVENT_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				$param = M3_REQUEST_PARAM_PHOTO_ID . '=' . $contentId;
				break;
			default:
				$param = '';
				break;
		}
		if (empty($param)){
			return '';
		} else {
			return $url . '?' . $param;
		}
	}
}
?>
