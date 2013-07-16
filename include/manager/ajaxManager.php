<?php
/**
 * Ajax操作マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ajaxManager.php 5009 2012-07-03 13:55:38Z fishbone $
 * @link       http://www.magic3.org
 */
class AjaxManager
{
	private $_sendJsonToClient = false;			// JSONデータをクライアントに送信するかどうか。初期状態では送信しない。データがセットされて送信状態になる
    /**
     * M3-JSONヘッダーへ出力する値を配列でもちます
     * $jsonData['retcode']		// レスポンス戻り値
     * $jsonData['data']		// JSON実データ
     */
    var $jsonData;
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// データ初期化
		$this->jsonData				= array();
		$this->jsonData['retcode']	= 0;
		$this->jsonData['data']		= array();
	}
	/**
	 * JSON型データで出力する値を追加
	 *
	 * $key,$valueのセットで呼び出すか、または、連想配列を$keyにセットして呼び出す。
	 *
	 * @param string key			キー項目
	 * @param string value			値項目
	 */
	function addData($key, $value='')
	{
		if (is_array($key)){
			foreach($key as $k => $v){
				$this->jsonData['data'][$k] = $v;
			}
		} else {
			$this->jsonData['data'][$key] = $value;
		}
		// 送信に設定
		$this->_sendJsonToClient = true;
	}
	/**
	 * JSON型データで出力する値をすべて消去
	 */
	function clearData()
	{
		$this->jsonData['data'] = array();
	}
	/**
	 * Ajax通信のクライアント戻り値を設定
	 *
	 * @param int $value			クライアント戻り値
	 */
	function setReturnCode($value)
	{
		$this->jsonData['retcode'] = $value;
		
		// 送信に設定
		$this->_sendJsonToClient = true;
	}
	/**
	 * JSONデータをヘッダに追加するかどうかの制御
	 *
	 * @param bool $status			true=JSONデータ送信、false=JSONデータ送信しない
	 */
	function setSendJsonToClient($status)
	{
		$this->_sendJsonToClient = $status;
	}	 
	/**
	 * JSONデータをHTTPヘッダに出力
	 *
	 * メンバ変数のJSONデータをHTTPヘッダに出力する
	 *
	 * @param 			なし
	 */
	function header()
	{
		static $json;		// JSONデータ作成オブジェクト

		// 送信不可であれば何もしないで終了
		if (!$this->_sendJsonToClient) return;
		
		if (!isset($json)){
			require_once(M3_SYSTEM_LIB_PATH . '/JSON.php');
			$json = new Services_JSON();
		}
		$retData = $json->encode($this->jsonData);
		if (!empty($retData)) header('X-JSON: ' . '(' . $retData . ')');
	}
	/**
	 * JSONデータを作成
	 *
	 * @param object $obj			JSONデータに変換するデータ
	 * @return string				JSON型文字列
	 */
	function createJsonString($obj)
	{
		require_once(M3_SYSTEM_LIB_PATH . '/JSON.php');
		$json = new Services_JSON();
		return $json->encode($obj);
	}
}
?>
