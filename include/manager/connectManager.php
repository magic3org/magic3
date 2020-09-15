<?php
/**
 * サーバ接続マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: connectManager.php 5114 2012-08-16 01:04:11Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class ConnectManager extends _Core
{
	private $db;						// DBオブジェクト
	const CF_CONNECT_SERVER_URL = 'default_connect_server_url';			// ポータル接続先URL
	const CF_PORTAL_SERVER_URL = 'portal_server_url';		// ポータルサーバURL
	const CF_SERVER_ID = 'server_id';		// サーバID
	const DEFAULT_TO_WIDGET_ID = 'c/updateinfo';		// デフォルトの送信先ウィジェット
	const DEFAULT_PORTAL_TO_WIDGET_ID = 'c/portal';		// デフォルトのポータルの送信先ウィジェット
//	const DEFAULT_SITE_NAME = 'サイト名未設定';		// サイト名未設定の場合のデフォルトサイト名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
			
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
	}
	/**
	 * コンテンツ更新情報をサーバに登録
	 *
	 * 登録した更新内容はログテーブルに残す
	 *
	 * @param int $type				コンテンツタイプ(なし=汎用コンテンツ)
	 * @param string $url			送信先URL(空のときはデフォルトの登録用URL)
	 * @param string $toWidgetId	送信先ウィジェット(空のときはデフォルトの登録用ウィジェット)
	 * @param string $fromWidgetId	送信元ウィジェットID
	 * @param string $message		送信メッセージ
	 * @param string $contentTitle	コンテンツタイトル
	 * @param string $contentLink	コンテンツへのリンク
	 * @param timestamp $contentDt	コンテンツの更新日時
	 * @param timestamp $registDt	登録情報を有効にする日時
	 * @return bool					true=成功、false=失敗
	 */
	public function registUpdateInfo($type, $url, $toWidgetId, $fromWidgetId, $message, $contentTitle, $contentLink, $contentDt, $registDt='')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// 自サーバが非公開状態のときは送信しない
		if (!$this->gSystem->siteInPublic()) return false;
		
		// 接続先が未指定のときは、接続先を取得
		if (empty($url)) $url = $this->gSystem->getSystemConfig(self::CF_CONNECT_SERVER_URL);// ポータル接続先URL
		
		// 接続先が指定されていないときは終了
		if (empty($url)) return false;
		
		// サーバIDが設定されていないときは終了
		$serverId = $this->db->getSystemConfig(self::CF_SERVER_ID);
		if (empty($serverId)) return false;
		
		// コンテンツタイプ設定
		if (empty($type)) $type = M3_VIEW_TYPE_CONTENT;		// デフォルトは汎用コンテンツ
		
		// 送信先ウィジェット設定
		if (empty($toWidgetId)) $toWidgetId = self::DEFAULT_TO_WIDGET_ID;
		
		// 送信URL作成
		list($url, $query) = explode('?', $url);
		if (!strEndsWith($url, '.php')){
			if (!strEndsWith($url, '/')) $url .= '/';
			$url .= M3_FILENAME_SERVER_CONNECTOR;		// サーバ接続用コネクター
		}
		// 送信先ウィジェットを指定
		if (empty($query)){
			$query = M3_REQUEST_PARAM_WIDGET_ID . '=' . urlencode($toWidgetId);
		}
		$url .= '?' . $query;

		// 登録情報を有効にする日時
		if (empty($registDt)) $registDt = $now;
		
		// サイト名
		$siteName =$this->gEnv->getSiteName();
//		if (empty($siteName)) $siteName = self::DEFAULT_SITE_NAME;
		
		// 送信データ作成
		$params = array('server_id'		=> $serverId,		// サーバID
						'act'		=> 'regist',		// データ登録
						'regist_dt'	=> $registDt,		// 情報を登録する日時
						'content_type'	=> $type,		// コンテンツタイプ
						'content_name'	=> $contentTitle,		// コンテンツ名
						'content_link'	=> $contentLink,		// コンテンツリンク先
						'content_dt'	=> $contentDt,		// コンテンツ更新日時
						'message'	=> $message,		// 送信メッセージ
						'site_name'	=> $siteName,		// 送信元サイト名
						'site_link'	=> $this->gEnv->getRootUrl());		// 送信元サイトへのリンク
		
		// データ送信
		$ret = false;		// 送信状況
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, M3_SYSTEM_NAME . '/' . M3_SYSTEM_VERSION);		// ユーザエージェント
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$output = curl_exec($ch);
		if ($output === false){
			$errMsg = curl_error($ch);
			$msgDetail = 'コンテンツタイトル=' . $contentTitle;
		} else {
			// 取得したデータを解析
			try {
				$xml = new SimpleXMLElement($output);
				$status = M3_RESPONSE_PARAM_STATUS;
				if ($xml->status == 1){
					$ret = true;		// 送信状況
				} else {
					$errMsg = 'サーバからの戻りコードが不正(' . $xml->status . ')';
					$msgDetail = 'コンテンツタイトル=' . $contentTitle;
				}
			} catch (Exception $e){
				$errMsg = 'サーバからの戻りデータが不正';
				$msgDetail = 'コンテンツタイトル=' . $contentTitle . ',エラーデータ=' . $output;
			}
		}
		curl_close($ch);

		// 送信成功したときは、ログに残す
		if ($ret){
			$msgDetail = 'サーバURL=' . $url;
			$this->gOpeLog->writeInfo(__METHOD__, 'コンテンツ更新情報をサーバへアップしました。(コンテンツタイトル=' . $contentTitle . ')', 1000, $msgDetail);
		} else {		// エラーの場合
			$this->gOpeLog->writeError(__METHOD__, 'コンテンツ更新情報送信エラー(要因=' . $errMsg . ')', 1100, $msgDetail);
		}
		return $ret;
	}
	/**
	 * ポータルサーバと通信
	 *
	 * @param array $postParam		Post送信データ
	 * @param string $url			送信先URL(空のときはデフォルトのポータルサーバURL)
	 * @param string $toWidgetId	送信先ウィジェット(空のときはデフォルトの登録用ウィジェット)
	 * @param object $xmlObj		受信データSimpleXMLElementオブジェクト
	 * @return bool					true=成功、false=失敗
	 */
	public function sendToPortal($postParam, &$xmlObj, $url = '', $toWidgetId = '')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// ##### パラメータチェック #####
		// 自サーバが非公開状態のときは送信しない
		if (!$this->gSystem->siteInPublic()) return false;
		
		// 接続先が未指定のときは、接続先を取得
		if (empty($url)) $url = $this->gSystem->getSystemConfig(self::CF_PORTAL_SERVER_URL);// ポータルサーバURL
		
		// 接続先が指定されていないときは終了
		if (empty($url)) return false;
		
		// サーバIDが設定されていないときは終了
		$serverId = $this->db->getSystemConfig(self::CF_SERVER_ID);
		if (empty($serverId)) return false;
		
		// ##### 送信データ設定 #####
		// 送信先ウィジェット設定
		if (empty($toWidgetId)) $toWidgetId = self::DEFAULT_PORTAL_TO_WIDGET_ID;
		
		// 送信URL作成
		list($url, $query) = explode('?', $url);
		if (!strEndsWith($url, '.php')){
			if (!strEndsWith($url, '/')) $url .= '/';
			$url .= M3_FILENAME_SERVER_CONNECTOR;		// サーバ接続用コネクター
		}
		// 送信先ウィジェットを指定
		if (empty($query)){
			$query = M3_REQUEST_PARAM_WIDGET_ID . '=' . urlencode($toWidgetId);
		}
		$url .= '?' . $query;

		// 送信データ作成
		$params = array_merge(array('server_id'		=> $serverId), $postParam);		// サーバID
		
		// データ送信
		$ret = false;		// 送信状況
		$msgDetail = '';	// 詳細メッセージ
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, M3_SYSTEM_NAME . '/' . M3_SYSTEM_VERSION);		// ユーザエージェント
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$output = curl_exec($ch);
		if ($output === false){
			$errMsg = curl_error($ch);
		} else {
			// 取得したデータを解析
			try {
				$xmlObj = @new SimpleXMLElement($output);
				$status = M3_RESPONSE_PARAM_STATUS;
				if ($xmlObj->status == 1){
					$ret = true;		// 送信状況
				} else {
					$errMsg = 'サーバからの戻りコードが不正(' . $xmlObj->status . ')';
				}
			} catch (Exception $e){
				$errMsg = 'サーバからの戻りデータが不正';
			}
		}
		curl_close($ch);

		// 通信状況をログに残す
		$msgDetail = 'サーバURL=' . $url . ', 送信データ(';
		$keys = array_keys($params);// キーを取得
		for ($i = 0; $i < count($keys); $i++){
			if ($i > 0) $msgDetail .= ', ';
			$key = $keys[$i];
			$value = $params[$key];
			$msgDetail .= $key . '=' . $value;
		}
		$msgDetail .= ')';
		
		if ($ret){
			$this->gOpeLog->writeInfo(__METHOD__, 'ポータルサーバと通信しました。', 1000, $msgDetail);
		} else {		// エラーの場合
			$this->gOpeLog->writeError(__METHOD__, 'ポータルサーバ間通信エラー(要因=' . $errMsg . ')', 1100, $msgDetail);
		}
		return $ret;
	}
}
?>
