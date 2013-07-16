<?php
/**
 * 共通定義クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: epsilonCommonDef.php 5437 2012-12-07 13:14:59Z fishbone $
 * @link       http://www.magic3.org
 */
class epsilonCommonDef
{
	const TEST_URL = 'http://beta.epsilon.jp/cgi-bin/order/receive_order3.cgi';		// 接続テスト用URL
	const PRODUCTION_MODE = 'production';		// 接続モード(本番)
	
	/**
	 * 決済サーバと通信
	 *
	 * @param string $url			送信先URL(空のときはデフォルトのポータルサーバURL)
	 * @param array $postParam		Post送信データ
	 * @param array $resultArray	受信データ
	 * @param bool $isTest			テストモードかどうか
	 * @return bool					true=成功、false=失敗
	 */
	static public function postData($url, $params, &$resultArray, $isTest = false)
	{
		global $gOpeLogManager;
		
		// 入力パラメータコード変換
		$postParams = array();
		foreach ($params as $key => $value){
			$postParams[$key] = mb_convert_encoding($value, 'EUC-JP', 'UTF-8');
		}
		
		// データ送信
		$ret = false;		// 送信状況
		$msgDetail = '';	// 詳細メッセージ
		$resultArray = array();		// 戻り値
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, M3_SYSTEM_NAME . '/' . M3_SYSTEM_VERSION);		// ユーザエージェント
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
		$output = curl_exec($ch);
		if ($output === false){
			$errMsg = curl_error($ch);
		} else {
			// 取得したデータを解析
			try {
				$output = str_replace("x-sjis-cp932", "SJIS-win", $output);
				$xmlObj = @new SimpleXMLElement($output);
				
				foreach ($xmlObj->result as $resultKey => $resultValue){
		    		list($resultAttrKey, $resultAttrValue) = each($resultValue);
					foreach ($resultAttrValue as $key => $value) $resultArray[$key] = mb_convert_encoding(urldecode($value), 'UTF-8', 'SJIS-win');
				}

				if ($resultArray['result'] == '1'){
					$ret = true;		// 送信状況
				} else {
					$codeMsg = '';
					if ($resultArray['result'] == '0') $codeMsg = '(失敗)';
					$errMsg = 'サーバからの実行結果が不正(' . $resultArray['result'] . $codeMsg . ')';
				}
			} catch (Exception $e){
				$errMsg = 'サーバからの戻りデータが不正';
			}
		}
		curl_close($ch);

		// 通信状況をログに残す
		$msgDetail = '送信先URL=' . $url . ', 送信データ(';
		$keys = array_keys($params);// キーを取得
		for ($i = 0; $i < count($keys); $i++){
			if ($i > 0) $msgDetail .= ', ';
			$key = $keys[$i];
			$value = $params[$key];
			$msgDetail .= $key . '=' . $value;
		}
		$msgDetail .= '), 受信データ(';
		
		$keys = array_keys($resultArray);// キーを取得
		for ($i = 0; $i < count($keys); $i++){
			if ($i > 0) $msgDetail .= ', ';
			$key = $keys[$i];
			$value = $resultArray[$key];
			$msgDetail .= $key . '=' . $value;
		}
		$msgDetail .= ')';
		
		$addMsg = '';
		if ($isTest) $addMsg = '[テスト]';
		if ($ret){
			//$this->gOpeLog->writeInfo(__METHOD__, $addMsg . 'イプシロン決済サーバと通信しました。', 1000, $msgDetail);
			$gOpeLogManager->writeInfo(__METHOD__, $addMsg . 'イプシロン決済サーバと通信しました。', 1000, $msgDetail);
		} else {		// エラーの場合
			//$this->gOpeLog->writeError(__METHOD__, $addMsg . 'イプシロン決済サーバ間通信エラー(要因=' . $errMsg . ')', 1100, $msgDetail);
			$gOpeLogManager->writeError(__METHOD__, $addMsg . 'イプシロン決済サーバ間通信エラー(要因=' . $errMsg . ')', 1100, $msgDetail);
		}
		return $ret;
	}
}
?>
