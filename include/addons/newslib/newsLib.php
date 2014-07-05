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
	private $db;	// DB接続オブジェクト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new newsLibDb();
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
				break;
		}
	}
}
?>
