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
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
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
    private $jsonData;		// レスポンスヘッダ用JSONデータ
	private $bodyData;		// レスポンスボディ用データ
	
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
	 * @return						なし
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
	 * レスポンスボディ用テキストデータで出力する値を追加
	 *
	 * @param string value			値項目
	 * @return						なし
	 */
	function addDataToBody($value='')
	{
		global $gPageManager;
		
		$this->bodyData = $value;

		// 送信に設定
		$this->_sendJsonToClient = true;
		
		// ページマネージャーに出力形式を指定
		$gPageManager->setOutputAjaxResponseBody(true);
	}
	/**
	 * レスポンスボディ用テキストデータが設定されているかどうか
	 *
	 * @return bool			true=設定データあり、false=設定データなし
	 */
/*	function isExistsResposeBodyData()
	{
		if (empty($this->bodyData)){
			return false;
		} else {
			return true;
		}
	}*/
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
		// 送信不可であれば何もしないで終了
		if (!$this->_sendJsonToClient) return;
		
		$retData = json_encode($this->jsonData);
		if (!empty($retData)) header('X-JSON: ' . '(' . $retData . ')');

		// レスポンスボディ用データがある場合は出力
		if (!empty($this->bodyData)){
			echo $this->bodyData;
		}
	}
}
?>
