<?php
/**
 * キャッシュ処理マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: cacheManager.php 3456 2010-08-06 04:55:15Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class CacheManager extends Core
{
	private $db;						// DBオブジェクト
	private $usePageCache;				// ページキャッシュを使用するかどうか
	private $useWidgetCache;				// ウィジェットキャッシュを使用するかどうか
	private $isCacheOff;					// 強制キャッシュオフフラグ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gInstanceManager;
		
		// システムDBオブジェクト取得
		$this->db = $gInstanceManager->getSytemDbObject();
		
		// キャッシュ可能な画面の条件
		// ・単純なURLで表現でき、GETで取得可能な画面
		// ・task付きのURLでは、トップ画面(taskなし)のみ表示可能とする
		$this->safeParam = array(
			M3_REQUEST_PARAM_PAGE_SUB_ID,			// ページサブID
			//M3_REQUEST_PARAM_PAGE_CONTENT_ID,				// ページコンテンツID
			//M3_REQUEST_PARAM_WIDGET_ID,			// ウィジェットID
			//M3_REQUEST_PARAM_TEMPLATE_ID,		// テンプレートID
			//M3_REQUEST_PARAM_URL,				// リンク先等のURL
			//M3_REQUEST_PARAM_STAMP,			// 公開発行ID
			//M3_REQUEST_PARAM_OPTION,				// 通信オプション
			//M3_REQUEST_PARAM_OPERATION_COMMAND,				// 実行処理
			//M3_REQUEST_PARAM_OPERATION_WIKI_COMMAND,			// Wikiコマンド実行
			//M3_REQUEST_PARAM_OPERATION_TASK,			// ウィジェット間共通処理
			//M3_REQUEST_PARAM_OPERATION_ACT,				// クライアントからの実行処理
			//M3_REQUEST_PARAM_OPERATION_LANG,			// 言語指定表示
			//M3_REQUEST_PARAM_OPERATION_TODO,			// 指定ウィジェットに実行させる処理
			//M3_REQUEST_PARAM_FROM,			// メッセージの送信元ウィジェットID
			//M3_REQUEST_PARAM_VIEW_STYLE,			// 表示スタイル
			//M3_REQUEST_PARAM_FORWARD,			// 画面遷移用パラメータ
			//M3_REQUEST_PARAM_OPEN_BY,			// ウィンドウの開き方
			//M3_REQUEST_PARAM_SHOW_HEADER,			// ヘッダ部表示制御
			//M3_REQUEST_PARAM_SHOW_FOOTER,			// フッタ部表示制御
			//M3_REQUEST_PARAM_PAGE_DEF_SERIAL,		// ページ定義のレコードシリアル番号(設定画面起動時)
			//M3_REQUEST_PARAM_PAGE_DEF_CONFIG_ID,		// ページ定義のウィジェット定義ID(設定画面起動時)
			M3_REQUEST_PARAM_CONTENT_ID,		// コンテンツID
			M3_REQUEST_PARAM_CONTENT_ID_SHORT,				// コンテンツID(略式)
			M3_REQUEST_PARAM_PRODUCT_ID,		// 製品ID
			M3_REQUEST_PARAM_PRODUCT_ID_SHORT,				// 製品ID(略式)
			M3_REQUEST_PARAM_BLOG_ENTRY_ID,			// ブログ記事ID
			M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT,				// ブログ記事ID(略式)
			M3_REQUEST_PARAM_BBS_ID,		// 掲示板投稿記事ID
			M3_REQUEST_PARAM_BBS_ID_SHORT				// 掲示板投稿記事ID(略式)
		);
	}
	/**
	 * キャッシュ機能を強制的にオフにする
	 *
	 * @return 				なし
	 */
	function cacheOff()
	{
		$this->usePageCache = false;				// ページキャッシュを使用するかどうか
		$this->useWidgetCache = false;				// ウィジェットキャッシュを使用するかどうか
		$this->isCacheOff = true;					// 強制キャッシュオフフラグ
	}
	/**
	 * キャッシュ機能を初期化
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return 
	 */
	function initCache($request)
	{
		global $gEnvManager;
		
		// 強制キャッシュオフのときは終了
		if ($this->isCacheOff) return;
				
		$this->usePageCache = false;				// ページキャッシュを使用するかどうか
		$this->useWidgetCache = false;				// ウィジェットキャッシュを使用するかどうか

		// ログイン時は、ページキャッシュを使用しない。ウィジェットキャッシュのみ使用。
		if ($this->canUseCache($request)){		// キャッシュ機能が使用可能
			// ログインをチェック
			if (!$gEnvManager->isCurrentUserLogined()) $this->usePageCache = true;
			
			// ウィジェットキャッシュ機能をオンにする
			$this->useWidgetCache = true;
		}
	}
	/**
	 * 画面のキャッシュデータを取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return string   					キャッシュデータ。キャッシュデータがないときは空文字列。
	 */
	function getPageCache($request)
	{
		global $gEnvManager;
		global $gSystemManager;
		
		$cacheData = '';

		// ページキャッシュ機能が使用できるかどうか
		if (!$this->usePageCache) return $cacheData;
		
		$pageId = $gEnvManager->getCurrentPageId();
		$pageSubId = $gEnvManager->getCurrentPageSubId();
		
		// キャッシュデータを取得
		$url = $gEnvManager->getCurrentRequestUri();
		$ret = $this->db->getCacheData('', $url, $row);
		if ($ret){
			// ページIDをチェック。ページIDがマッチしないときは、キャッシュを削除
			if ($row['ca_page_id'] == $pageId && $row['ca_page_sub_id'] == $pageSubId){
				// キャッシュの更新時間が0のときはキャッシュデータを更新しない
				$lifetime = $gSystemManager->pageCacheLifetime();	// 単位分
				if ($lifetime > 0){
					if (time() - strtotime($row['ca_update_dt']) <= $lifetime * 60){		// キャッシュ保持時間内のとき
						$cacheData = $row['ca_html'];
					}
				} else {
					$cacheData = $row['ca_html'];
				}
			}
		}
		return $cacheData;
	}
	/**
	 * 画面キャッシュデータを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string $cacheData				キャッシュデータ
	 * @return bool   						true=成功、false=失敗
	 */
	function setPageCache($request, $cacheData)
	{
		global $gEnvManager;

		// ページキャッシュ機能が使用できるかどうか
		if (!$this->usePageCache) return false;
		
		// URLのチェック
		$url = $gEnvManager->getCurrentRequestUri();
		if (!$this->isProperCacheUrl($request)) return false;
		/*$queryArray = $request->getQueryArray();
		if (count($queryArray) > 0){
			$keys = array_keys($queryArray);
			for ($i = 0; $i < count($keys); $i++){
				// 受け付け可能なパラメータでないときは終了
				if (!in_array($keys[$i], $this->safeParam)) return false;
			}
		}*/
		
		// キャッシュ可能なページかチェック
		$pageId = $gEnvManager->getCurrentPageId();
		$pageSubId = $gEnvManager->getCurrentPageSubId();
		$ret = $this->db->getPageDefOnPage($pageId, $pageSubId, $rows);
		$defCount = count($rows);
		for ($i = 0; $i < $defCount; $i++){
			if ($rows[$i]['wd_launch_index'] > 0) return false;		// 遅延実行ウィジェットはキャッシュできない
			if ($rows[$i]['wd_cache_type'] <= 0) return false;		// キャッシュ不可の場合は終了
		}
		
		$ret = $this->db->updateCacheData('', $url, $pageId, $pageSubId, $cacheData);
		return $ret;
	}
	/**
	 * ウィジェットのキャッシュデータを取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param Array $infoRow				ウィジェット情報
	 * @param string  $metaTitle		METAタグ、タイトル
	 * @param string  $metaDesc			METAタグ、ページ要約
	 * @param string  $metaKeyword		METAタグ、検索用キーワード
	 * @return string   					キャッシュデータ。キャッシュデータがないときは空文字列。
	 */
	function getWidgetCache($request, $infoRow, &$metaTitle, &$metaDesc, &$metaKeyword)
	{
		global $gEnvManager;
		global $gSystemManager;
		
		$cacheData = '';

		// ウィジェットキャッシュ機能が使用できるかどうか
		if (!$this->useWidgetCache) return $cacheData;

		$url = $gEnvManager->getCurrentRequestUri();
		$pageId = $gEnvManager->getCurrentPageId();
		$pageSubId = $gEnvManager->getCurrentPageSubId();
		
		$ret = $this->canUseWidgetCache($request, $infoRow);
		if ($ret){		// キャッシュ使用可能なとき
			$widgetId = $infoRow['wd_id'];
			$configId = $infoRow['pd_config_id'];
			$lifetime = $infoRow['wd_cache_lifetime'];// キャッシュ保存時間(単位分)
				
			switch ($infoRow['wd_view_control_type']){
				case -1:			// 表示が固定のとき
					$configId = 0;		// インスタンス定義を使用しないときはパラメータID=0のパラメータを使用
				case 1:			// ウィジェットパラメータで表示制御されるとき
					// ウィジェットパラメータを取得
					$ret = $this->db->getWidgetCache($widgetId, $configId, $row);
					if ($ret){
						if ($row['wp_cache_update_dt'] != $gEnvManager->getInitValueOfTimestamp()){		// キャッシュデータがあるとき
							// キャッシュの更新時間が0のときはキャッシュデータを更新しない
							if ($lifetime > 0){
								if (time() - strtotime($row['wp_cache_update_dt']) <= $lifetime * 60){		// キャッシュ保持時間内のとき
									$cacheData		= $row['wp_cache_html'];
									$metaTitle		= $row['wp_meta_title'];
									$metaDesc		= $row['wp_meta_description'];
									$metaKeyword 	= $row['wp_meta_keywords'];
								}
							} else {
								$cacheData		= $row['wp_cache_html'];
								$metaTitle		= $row['wp_meta_title'];
								$metaDesc		= $row['wp_meta_description'];
								$metaKeyword 	= $row['wp_meta_keywords'];
							}
						}
					}
					break;
				case 2:			// URLパラメータで表示制御されるとき
					$ret = $this->db->getCacheData($widgetId, $url, $row);
					if ($ret){
						// ページIDをチェック。ページIDがマッチしないときは、キャッシュを削除
						if ($row['ca_page_id'] == $pageId && $row['ca_page_sub_id'] == $pageSubId){
							if ($lifetime > 0){
								if (time() - strtotime($row['ca_update_dt']) <= $lifetime * 60){		// キャッシュ保持時間内のとき
									$cacheData		= $row['ca_html'];
									$metaTitle		= $row['ca_meta_title'];
									$metaDesc		= $row['ca_meta_description'];
									$metaKeyword 	= $row['ca_meta_keywords'];
								}
							} else {
								$cacheData = $row['ca_html'];
								$metaTitle		= $row['ca_meta_title'];
								$metaDesc		= $row['ca_meta_description'];
								$metaKeyword 	= $row['ca_meta_keywords'];
							}
						}
					}
					break;
			}
		}
		return $cacheData;
	}
	/**
	 * ウィジェットのキャッシュデータを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param Array   $infoRow				ウィジェット情報
	 * @param string  $cacheData			キャッシュデータ
	 * @param string  $metaTitle			METAタグ、タイトル
	 * @param string  $metaDesc				METAタグ、ページ要約
	 * @param string  $metaKeyword			METAタグ、検索用キーワード
	 * @return bool   						true=成功、false=失敗
	 */
	function setWidgetCache($request, $infoRow, $cacheData, $metaTitle = '', $metaDesc = '', $metaKeyword = '')
	{
		global $gEnvManager;

		// ウィジェットキャッシュ機能が使用できるかどうか
		if (!$this->useWidgetCache) return false;
		
		$ret = $this->canUseWidgetCache($request, $infoRow);
		if ($ret){		// キャッシュ使用可能なとき
			$widgetId = $infoRow['wd_id'];
			$configId = $infoRow['pd_config_id'];
			$lifetime = $infoRow['wd_cache_lifetime'];// キャッシュ保存時間(単位分)
				
			switch ($infoRow['wd_view_control_type']){
				case 0:				// 表示が可変のとき
				default:
					$ret = false;
					break;
				case -1:			// 表示が固定のとき
					$configId = 0;		// インスタンス定義を使用しないときはパラメータID=0のパラメータを使用
				case 1:			// ウィジェットパラメータで表示制御されるとき
					// ウィジェットキャッシュを更新
					$ret = $this->db->updateWidgetCache($widgetId, $configId, $cacheData, $metaTitle, $metaDesc, $metaKeyword);
					break;
				case 2:			// URLパラメータで表示制御されるとき
					// URLのチェック
					$url = $gEnvManager->getCurrentRequestUri();
					if (!$this->isProperCacheUrl($request)) return false;
					
					$pageId = $gEnvManager->getCurrentPageId();
					$pageSubId = $gEnvManager->getCurrentPageSubId();
					$ret = $this->db->updateCacheData($widgetId, $url, $pageId, $pageSubId, $cacheData, $metaTitle, $metaDesc, $metaKeyword);
					break;
			}
		}
		return $ret;
	}
	/**
	 * キャッシュ機能が使用できるかどうか
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return bool   						true=可能、false=不可
	 */
	function canUseCache($request)
	{
		global $gEnvManager;
		global $gSystemManager;
		
		// キャッシュ処理を行わないときは終了
		if (!$gSystemManager->usePageCache()) return false;
		
		// システム運用者はキャッシュできない
		if ($gEnvManager->isSystemManageUser()) return false;
		
		// 管理画面はキャッシュ機能は使用できない
		if ($gEnvManager->isAdminDirAccess()) return false;
		
		// 携帯はSJIS出力なので非対応
		if ($gEnvManager->getIsMobileSite()) return false;
		
		// GET以外はキャッシュしない
		$method = strtoupper($request->trimServerValueOf('REQUEST_METHOD'));	// アクセスメソッド
		if ($method != 'GET') return false;
		
		return true;
	}
	/**
	 * ウィジェットキャッシュ機能が使用できるかどうか
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param Array $infoRow				ウィジェット情報
	 * @return bool   						true=可能、false=不可
	 */
	function canUseWidgetCache($request, $infoRow)
	{
		global $gEnvManager;
		
		// 遅延実行ウィジェットはキャッシュできない
		if ($infoRow['wd_launch_index'] > 0) return false;
		
		// キャッシュタイプをチェック
		$retStatus = false;
		switch ($infoRow['wd_cache_type']){
			case 0:			// キャッシュ不可のとき
			case 3:			// ページキャッシュのみ可のとき(ウィジェットからHTMLヘッダ部にCSS,JavaScript等を追加している場合)
			default:
				break;
			case 1:			// キャッシュ可能なとき
				$retStatus = true;
				break;
			case 2:			// 非ログイン時のみキャッシュ可能なとき
				// ログインをチェック
				if (!$gEnvManager->isCurrentUserLogined()) $retStatus = true;
				break;
		}
		return $retStatus;
	}
	/**
	 * キャッシュ可能なURLかどうか
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return bool   						true=可能、false=不可
	 */
	function isProperCacheUrl($request)
	{
		global $gEnvManager;
		
		$url = $gEnvManager->getCurrentRequestUri();
		
		$queryArray = $request->getQueryArray();
		if (count($queryArray) > 0){
			$keys = array_keys($queryArray);
			for ($i = 0; $i < count($keys); $i++){
				// 受け付け可能なパラメータでないときは終了
				if (!in_array($keys[$i], $this->safeParam)) return false;
			}
		}
		return true;
	}
	/**
	 * キャッシュデータを削除
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @return bool   						true=完了、false=不可
	 */
	function clearPageCache($pageId, $pageSubId)
	{
		$ret = $this->db->deletePageCacheData($pageId, $pageSubId);
		return $ret;
	}
	/**
	 * すべてのキャッシュデータを削除
	 *
	 * @return bool   						true=完了、false=不可
	 */
	function clearAllCache()
	{
		// ページキャッシュを削除
		$ret = $this->db->deletePageCacheData('', '');
		
		// ウィジェットキャッシュを削除
		$ret = $this->db->deleteWidgetCache();
		return true;
	}
	/**
	 * ウィジェットタイプ指定でキャッシュデータを削除
	 *
	 * @param string $widgetType	ウィジェットタイプ
	 * @return bool   				true=完了、false=不可
	 */
	function clearCacheByWidgetType($widgetType)
	{
		// ウィジェットタイプに該当するウィジェットIDを取得
		$ret = $this->db->getWidgetListByType($widgetType, $rows);
		if ($ret){
			// ウィジェットごとにキャッシュを削除
			for ($i = 0; $i < count($rows); $i++){
				$this->clearCacheByWidgetId($rows[$i]['wd_id']);
			}
		}
		return true;
	}
	/**
	 * ウィジェットIDとURLパラメータ指定でキャッシュデータを削除
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param array $param			URLパラメータ(空のときはウィジェット定義ID=0を対象とする)
	 * @return bool   				true=完了、false=不可
	 */
	function clearCacheByWidgetId($widgetId, $param = array())
	{
		if (empty($param)){
			// ##### ページキャッシュ、ウィジェットキャッシュを削除 #####
			// 定義ID=0が配置されているページのキャッシュを削除
			$this->clearCacheByWidgetConfigId($widgetId, 0);
		} else {
			// ##### ページキャッシュを削除 #####
			// ウィジェットを指定してキャッシュを削除
			$ret = $this->db->getCacheDataByWidgetId($widgetId, $rows);
			if ($ret){
				// URLパラメータがマッチしたレコードを削除
				$serialArray = array();
				/*if (empty($param)){
					for ($i = 0; $i < count($rows); $i++){
						$serialArray[] = $rows[$i]['ca_serial'];
					}
				} else {*/
					for ($i = 0; $i < count($rows); $i++){
						$url = $rows[$i]['ca_url'];
						$parsedUrl = parse_url($url);
						if (!empty($parsedUrl['query'])){
							// パラメータがマッチしたレコードは削除対象とする
							$lines = explode('&', $parsedUrl['query']);
							for ($j = 0; $j < count($lines); $j++){
								if (in_array($lines[$j], $param)) break;
							}
							if ($j < count($lines)) $serialArray[] = $rows[$i]['ca_serial'];
						}
					}
				//}
				$ret = $this->db->deletePageCacheDataBySerial($serialArray);
			}
		
			// ページキャッシュを削除
			$ret = $this->db->getCacheDataByWidgetId('', $rows);
			if ($ret){
				// URLパラメータがマッチしたレコードを削除
				$serialArray = array();
				/*if (empty($param)){
					for ($i = 0; $i < count($rows); $i++){
						$serialArray[] = $rows[$i]['ca_serial'];
					}
				} else {*/
					for ($i = 0; $i < count($rows); $i++){
						$url = $rows[$i]['ca_url'];
						$parsedUrl = parse_url($url);
						if (!empty($parsedUrl['query'])){
							// パラメータがマッチしたレコードは削除対象とする
							$lines = explode('&', $parsedUrl['query']);
							for ($j = 0; $j < count($lines); $j++){
								if (in_array($lines[$j], $param)) break;
							}
							if ($j < count($lines)) $serialArray[] = $rows[$i]['ca_serial'];
						}
					}
				//}
				$ret = $this->db->deletePageCacheDataBySerial($serialArray);
			}
		}
		return $ret;
	}
	/**
	 * ウィジェットIDとウィジェット定義ID指定でキャッシュデータを削除
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param string $configId		ウィジェット定義ID
	 * @return bool   				true=完了、false=不可
	 */
	function clearCacheByWidgetConfigId($widgetId, $configId)
	{
		// ##### ページキャッシュを削除 #####
		// ページID,ページサブIDを取得
		$ret = $this->db->getPageDefByWidgetConfigId($widgetId, $configId, $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$pageId = $rows[$i]['pd_id'];
				$pageSubId = $rows[$i]['pd_sub_id'];
				
				// キャッシュをクリア
				$this->clearPageCache($pageId, $pageSubId);
			}
		}
		
		// ##### ウィジェットキャッシュを削除 #####
		$ret = $this->db->deleteWidgetCache($widgetId, $configId);
		return $ret;
	}
}
?>
