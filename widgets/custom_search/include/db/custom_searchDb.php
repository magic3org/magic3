<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    カスタム検索
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010-2013 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class custom_searchDb extends BaseDb
{
	/**
	 * 汎用コンテンツ定義値を取得をすべて取得
	 *
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getAllConfig(&$rows)
	{
		$contentType = '';
		$queryStr  = 'SELECT * FROM content_config ';
		$queryStr .=   'WHERE ng_type = ? ';
		$queryStr .=   'ORDER BY ng_index';
		$retValue = $this->selectRecords($queryStr, array($contentType), $rows);
		return $retValue;
	}
	/**
	 * すべてのカテゴリ種別を取得
	 *
	 * @param string  $langId		言語ID
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getAllCategory($langId, &$rows)
	{
		// カテゴリ情報を取得
		$queryStr  = 'SELECT * FROM user_content_category ';
		$queryStr .=   'WHERE ua_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ua_item_id = ? ';
		$queryStr .=     'AND ua_language_id = ? ';
		$queryStr .=   'ORDER BY ua_index';
		$retValue = $this->selectRecords($queryStr, array(''/*カテゴリ情報のみ*/, $langId), $rows);
		return $retValue;
	}
	/**
	 * メニュー作成用のカテゴリ一覧を取得
	 *
	 * @param string  $langId			言語ID
	 * @param array   $categoryArray	取得するカテゴリID
	 * @param array  $rows				取得レコード
	 * @return							true=取得、false=取得せず
	 */
	function getAllCategoryForMenu($langId, $categoryArray, &$rows)
	{
		if (count($categoryArray) <= 0) return false;
		
		// CASE文作成
		$categoryId = '';
		$caseStr = 'CASE ua_id ';
		for ($i = 0; $i < count($categoryArray); $i++){
			$id = '\'' . addslashes($categoryArray[$i]) . '\'';
			$caseStr .= 'WHEN ' . $id . ' THEN ' . $i . ' ';
			$categoryId .= $id . ',';
		}
		$caseStr .= 'END AS no,';
		$categoryId = rtrim($categoryId, ',');
		// タイトルを最後にする
		$caseStr .=   'CASE ua_item_id ';
		$caseStr .=     'WHEN \'\' THEN 1 ';
		$caseStr .=     'ELSE 0 ';
		$caseStr .=   'END AS type ';
		
		$queryStr  = 'SELECT *, ' . $caseStr . ' FROM user_content_category ';
		$queryStr .=   'WHERE ua_deleted = false ';		// 削除されていない
		$queryStr .=     'AND ua_language_id = ? ';
		$queryStr .=     'AND ua_id in (' . $categoryId . ') ';
		$queryStr .=   'ORDER BY no, type, ua_index';
		$retValue = $this->selectRecords($queryStr, array($langId), $rows);
		return $retValue;
	}
	/**
	 * コンテンツの項目数またはコンテンツを取得(表示用)
	 *
	 * @param int		$limit				取得する項目数(0=項目数取得、0以外=レコードを取得)
	 * @param int		$page				取得するページ(1～)
	 * @param array		$keywords			検索キーワード
	 * @param array     $categoryInfo		絞り込み用カテゴリ
	 * @param string	$langId				言語
	 * @param bool		$isAll				すべてのデータを取得するか、ユーザ制限のないデータを取得するかを指定
	 * @param bool		$isTargetContent	汎用コンテンツを検索対象とするかどうか
	 * @param bool		$isTargetUser		ユーザ作成コンテンツを検索対象とするかどうか
	 * @param bool		$isTargetBlog		ブログ記事を検索対象とするかどうか
	 * @param bool		$isTargetProduct		商品情報を検索対象とするかどうか
	 * @param bool		$isTargetEvent		イベント情報を検索対象とするかどうか
	 * @param bool		$isTargetBbs		BBSを検索対象とするかどうか
	 * @param bool		$isTargetPhoto		フォトギャラリーを検索対象とするかどうか
	 * @param bool		$contentUsePassword	汎用コンテンツのパスワード閲覧制限するかどうか
	 * @param function	$callback			コールバック関数
	 * @return int,bool						$limitが0のときintで項目数、$limitが0以外のときはbool(true=1行以上レコード取得、false=レコードなし)
	 */
	function searchContentsByKeyword($limit, $page, $keywords, $categoryInfo, $langId, $isAll, $isTargetContent, $isTargetUser, $isTargetBlog, 
									$isTargetProduct, $isTargetEvent, $isTargetBbs, $isTargetPhoto, $contentUsePassword, $callback = NULL)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		$initDt = $this->gEnv->getInitValueOfTimestamp();		// 日時初期化値
		$now = date("Y/m/d H:i:s");	// 現在日時
		$params = array();		// パラメータ初期化
		$queryStr = '';
		
		// ##### 汎用コンテンツの検索条件 #####
		if (!empty($isTargetContent)){
			$contentType = '';
			$queryStr .= 'SELECT DISTINCT \'content\' AS type, cn_id AS id, cn_name AS name, cn_create_dt AS dt, 0 AS group_id ';
			$queryStr .= 'FROM content ';
			$queryStr .=  'WHERE cn_visible = true ';
			$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
			$queryStr .=    'AND cn_type = ? ';$params[] = $contentType;
			$queryStr .=    'AND cn_language_id = ? ';$params[] = $langId;
			if (!$all) $queryStr .=    'AND cn_user_limited = false ';		// ユーザ制限のないデータ
			if ($contentUsePassword) $queryStr .=    'AND cn_password = \'\' ';	// パスワード閲覧制限する場合はパスワードが設定されているコンテンツを検索しない
			
			// タイトルと記事を検索
			if (!empty($keywords)){
				for ($i = 0; $i < count($keywords); $i++){
					$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
					$queryStr .=    'AND (cn_name LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR cn_html LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR cn_description LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR cn_option_fields LIKE \'%' . $keyword . '%\') ';	// ユーザ定義フィールド
				}
			}
		
			// 公開期間を指定
			$queryStr .=    'AND (cn_active_start_dt = ? OR (cn_active_start_dt != ? AND cn_active_start_dt <= ?)) ';
			$queryStr .=    'AND (cn_active_end_dt = ? OR (cn_active_end_dt != ? AND cn_active_end_dt > ?)) ';
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
		}
		// ##### ユーザ作成コンテンツの検索条件 #####
		if (!empty($isTargetUser)){
			// カテゴリ条件から検索対象のルームを取得
			$roomArray = array();
			if (!empty($categoryInfo)){
				$allParams = array();
				$allQueryStr = '';
				$keys = array_keys($categoryInfo);
				$categoryCount = count($keys);
				for ($i = 0; $i < $categoryCount; $i++){
					$qStr  = 'SELECT um_room_id FROM user_content_room_category ';
					$pms = array();
				
					$key = $keys[$i];
					$item = $categoryInfo[$key];
					if (is_array($item)){		// 複数項目の場合
						$qStr .=    'WHERE um_category_id = ? AND (';
						$pms[] = $key;
						$itemCount = count($item);
						for ($j = 0; $j < $itemCount; $j++){
							$qStr .=    'um_category_item_id = ?';
							if ($j < $itemCount -1) $qStr .=    ' OR ';
							$pms[] = $item[$j];
						}
						$qStr .=    ') ';
					} else {
						$qStr .=    'WHERE um_category_id = ? AND um_category_item_id = ? ';
						$pms[] = $key;
						$pms[] = $item;
					}
				
					$allParams = array_merge($pms, $allParams);
					if ($i == 0){
						$allQueryStr = $qStr;
					} else {
						$allQueryStr = $qStr . ' AND um_room_id IN (' . $allQueryStr . ')';
					}
				}
				$retValue = $this->selectRecords($allQueryStr, $allParams, $rows);
				if ($retValue){
					$rowCount = count($rows);
					for ($i = 0; $i < $rowCount; $i++){
						$roomArray[] = $rows[$i]['um_room_id'];
					}
				}
			}

			if (!empty($queryStr)) $queryStr .= ' UNION ';
			$queryStr .= 'SELECT DISTINCT \'user\' AS type, uc_room_id AS id, ur_name AS name, ur_content_update_dt AS dt, ur_group_id AS group_id ';
			$queryStr .= 'FROM user_content LEFT JOIN user_content_room ON uc_room_id = ur_id AND ur_deleted = false ';
			$queryStr .= 'WHERE uc_language_id = ? ';	$params[] = $langId;
			$queryStr .=   'AND uc_deleted = false ';		// 削除されていない
			$queryStr .=   'AND uc_visible = true ';		// 公開中
			$queryStr .=   'AND ur_visible = true ';		// ルーム公開中

			// 検索対象のルームを制限
			if (!empty($categoryInfo)){		// カテゴリ制限のとき
				if (count($roomArray) == 0){
					$idStr = '\'/dummy/\'';				// ダミーデータを設定
				} else {
					$idStr = '';
					for ($i = 0; $i < count($roomArray); $i++){
						$idStr .= '\'' . addslashes($roomArray[$i]) . '\',';
					}
					$idStr = rtrim($idStr, ',');
				}
				$queryStr .=  'AND uc_room_id in (' . $idStr . ') ';
			}

			// コンテンツを検索
			if (!empty($keywords)){
				for ($i = 0; $i < count($keywords); $i++){
					$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
					$queryStr .=    'AND (uc_data LIKE \'%' . $keyword . '%\' ';		// コンテンツ実データ
					$queryStr .=    'OR uc_room_id LIKE \'%' . $keyword . '%\' ';			// ルームID
					$queryStr .=    'OR ur_name LIKE \'%' . $keyword . '%\') ';			// ルーム名
				}
			}
		
			// 公開期間を指定
			$queryStr .=    'AND (uc_active_start_dt = ? OR (uc_active_start_dt != ? AND uc_active_start_dt <= ?)) ';
			$queryStr .=    'AND (uc_active_end_dt = ? OR (uc_active_end_dt != ? AND uc_active_end_dt > ?)) ';
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
		}
		// ##### ブログの検索条件 #####
		if (!empty($isTargetBlog)){
			if (!empty($queryStr)) $queryStr .= ' UNION ';
			$queryStr .= 'SELECT DISTINCT \'blog\' AS type, be_id AS id, be_name AS name, be_regist_dt AS dt, 0 AS group_id ';
			$queryStr .= 'FROM blog_entry ';
			$queryStr .=  'WHERE be_language_id = ? ';	$params[] = $langId;
			$queryStr .=    'AND be_deleted = false ';		// 削除されていない
			$queryStr .=    'AND be_regist_dt <= ? ';	$params[] = $now;	// 投稿日時が現在日時よりも過去のものを取得

			// タイトルと記事を検索
			if (!empty($keywords)){
				for ($i = 0; $i < count($keywords); $i++){
					$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
					$queryStr .=    'AND (be_name LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR be_html LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR be_html_ext LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR be_description LIKE \'%' . $keyword . '%\') ';
				}
			}
		
			// 公開期間を指定
			$queryStr .=    'AND (be_active_start_dt = ? OR (be_active_start_dt != ? AND be_active_start_dt <= ?)) ';
			$queryStr .=    'AND (be_active_end_dt = ? OR (be_active_end_dt != ? AND be_active_end_dt > ?)) ';
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
			$params[] = $initDt;
			$params[] = $initDt;
			$params[] = $now;
		}
		// ##### 商品情報の検索条件 #####
		if (!empty($isTargetProduct)){
			if (!empty($queryStr)) $queryStr .= ' UNION ';
			
			$queryStr .= 'SELECT DISTINCT \'product\' AS type, pt_id AS id, pt_name AS name, pt_create_dt AS dt, 0 AS group_id ';
			$queryStr .= 'FROM product ';
			$queryStr .= 'WHERE pt_language_id = ? '; $params[] = $langId;
			$queryStr .=   'AND pt_deleted = false ';		// 削除されていない
			$queryStr .=   'AND pt_visible = true ';		// 表示可能な商品
		
			// 商品名、商品コード、説明を検索
			if (!empty($keywords)){
				for ($i = 0; $i < count($keywords); $i++){
					$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
					$queryStr .=    'AND (pt_name LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR pt_code LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR pt_description LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR pt_description_short LIKE \'%' . $keyword . '%\') ';
				}
			}
		}
		// ##### イベント情報の検索条件 #####
		if (!empty($isTargetEvent)){
			if (!empty($queryStr)) $queryStr .= ' UNION ';
			$queryStr .= 'SELECT DISTINCT \'event\' AS type, ee_id AS id, ee_name AS name, ee_start_dt AS dt, 0 AS group_id ';
			$queryStr .= 'FROM event_entry ';
			$queryStr .=   'WHERE ee_language_id = ? ';	$params[] = $langId;
			$queryStr .=     'AND ee_deleted = false ';		// 削除されていない
			
			// 名前、予定、結果、概要、管理者用備考、場所、連絡先を検索
			if (!empty($keywords)){
				for ($i = 0; $i < count($keywords); $i++){
					$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
					$queryStr .=    'AND (ee_name LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR ee_html LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR ee_html_ext LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR ee_summary LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR ee_admin_note LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR ee_place LIKE \'%' . $keyword . '%\' ';
					$queryStr .=    'OR ee_contact LIKE \'%' . $keyword . '%\') ';
				}
			}
		}
		// ##### BBSの検索条件 #####
		if (!empty($isTargetBbs)){
			if (!empty($queryStr)) $queryStr .= ' UNION ';
			$queryStr .= 'SELECT DISTINCT \'bbs\' AS type, th_id AS id, th_subject AS name, th_dt AS dt, 0 AS group_id ';
			$queryStr .= 'FROM bbs_2ch_thread_message LEFT JOIN bbs_2ch_thread ON te_thread_id = th_id AND th_deleted = false ';
			$queryStr .=   'WHERE te_deleted = false ';		// 削除されていない
		
			// 「'"\」文字をエスケープ
			if (!empty($keywords)){
				for ($i = 0; $i < count($keywords); $i++){
					$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
					$queryStr .=     'AND (te_user_name LIKE \'%' . $keyword . '%\' ';
					$queryStr .=     'OR te_email LIKE \'%' . $keyword . '%\' ';
					$queryStr .=     'OR te_message LIKE \'%' . $keyword . '%\') ';
				}
			}
		}
		// ##### フォト情報の検索条件 #####
		if (!empty($isTargetPhoto)){
			if (!empty($queryStr)) $queryStr .= ' UNION ';
			$queryStr .= 'SELECT DISTINCT \'photo\' AS type, ht_public_id AS id, ht_name AS name, ht_regist_dt AS dt, 0 AS group_id ';
			$queryStr .= 'FROM photo LEFT JOIN _login_user ON ht_owner_id = lu_id AND lu_deleted = false ';
			$queryStr .=   'WHERE ht_deleted = false ';		// 未削除
			$queryStr .=     'AND ht_visible = true ';		// 公開中
			
			if (!empty($keywords)){
				for ($i = 0; $i < count($keywords); $i++){
					$keyword = addslashes($keywords[$i]);		// 「'"\」文字をエスケープ
					$queryStr .=    'AND (ht_public_id LIKE \'%' . $keyword . '%\' ';		// 公開用画像ID
					$queryStr .=    'OR ht_name LIKE \'%' . $keyword . '%\' ';		// 画像タイトル
					$queryStr .=    'OR ht_camera LIKE \'%' . $keyword . '%\' ';		// カメラ
					$queryStr .=    'OR ht_location LIKE \'%' . $keyword . '%\' ';		// 撮影場所
					$queryStr .=    'OR ht_description LIKE \'%' . $keyword . '%\' ';		// 説明
					$queryStr .=    'OR ht_summary LIKE \'%' . $keyword . '%\' ';		// 概要
					$queryStr .=    'OR ht_keyword LIKE \'%' . $keyword . '%\' ';		// キーワード
					$queryStr .=    'OR lu_name LIKE \'%' . $keyword . '%\') ';	// 撮影者
				}
			}
		}
		if (empty($limit)){			// 項目数取得の場合
			$ret = $this->selectRecordCount($queryStr, $params);
		} else {
			// コンテンツ更新の最新のデータから取得
			$queryStr .=  'ORDER BY dt desc limit ' . $limit . ' offset ' . $offset;
			$ret = $this->selectLoop($queryStr, $params, $callback);
		}
		return $ret;
	}
	/**
	 * デフォルトのタブ定義を取得
	 *
	 * @param string	$lang				言語
	 * @param int	    $groupId			グループID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getDefaultTab($lang, $groupId, &$row)
	{
		$queryStr = 'SELECT * FROM user_content_tab ';
		$queryStr .=  'WHERE ub_deleted = false ';		// 削除されていない
		$queryStr .=    'AND ub_visible = true ';		// 表示中
		$queryStr .=    'AND ub_language_id = ? ';
		$queryStr .=    'AND ub_group_id = ? ';
		$queryStr .=  'ORDER BY ub_index';
		$ret = $this->selectRecord($queryStr, array($lang, $groupId), $row);
		return $ret;
	}
	/**
	 * ルームIDですべてのコンテンツを取得
	 *
	 * @param string  $roomId		ルームID
	 * @param string  $langId		言語ID
	 * @param array   $rows			取得レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getAllContentsByRoomId($roomId, $langId, &$rows)
	{
		if (empty($roomId) || empty($langId)) return false;
		
		$queryStr  = 'SELECT * FROM user_content LEFT JOIN user_content_item ON uc_id = ui_id AND ui_deleted = false ';
		$queryStr .=   'WHERE uc_deleted = false ';	// 削除されていない
		$queryStr .=     'AND uc_room_id = ? ';
		$queryStr .=     'AND uc_language_id = ? ';
		$retValue = $this->selectRecords($queryStr, array($roomId, $langId), $rows);
		return $retValue;
	}
	/**
	 * コンテンツ項目をコンテンツIDで取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$contentId			コンテンツID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getContentByContentId($contentType, $contentId, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM content ';
		$queryStr .=   'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=    'AND cn_type = ? ';
		$queryStr .=   'AND cn_id = ? ';
		$queryStr .=   'AND cn_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($contentType, $contentId, $langId), $row);
		return $ret;
	}
	/**
	 * ブログ記事をエントリーIDで取得
	 *
	 * @param string	$id					エントリーID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEntryByEntryId($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM blog_entry ';
		$queryStr .=   'WHERE be_deleted = false ';	// 削除されていない
		$queryStr .=   'AND be_id = ? ';
		$queryStr .=   'AND be_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * 商品を商品ID、言語IDで取得
	 *
	 * @param int		$id					商品ID
	 * @param string	$langId				言語ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getProductByProductId($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM product LEFT JOIN product_record ON pt_id = pe_product_id AND pt_language_id = pe_language_id ';
		$queryStr .=   'WHERE pt_deleted = false ';	// 削除されていない
		$queryStr .=    'AND pt_visible = true ';		// 表示可能な商品
		$queryStr .=    'AND pt_id = ? ';
		$queryStr .=    'AND pt_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * イベント情報を取得
	 *
	 * @param int		$id					イベントID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getEvent($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM event_entry ';
		$queryStr .=   'WHERE ee_deleted = false ';	// 削除されていない
		$queryStr .=   'AND ee_id = ? ';
		$queryStr .=   'AND ee_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
	/**
	 * BBSスレッドを取得
	 *
	 * @param array     $threadId			スレッドID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getBbsThread($threadId, &$row)
	{
		$queryStr  = 'SELECT * FROM bbs_2ch_thread_message ';
		$queryStr .=   'WHERE te_thread_id = ? ';
		$queryStr .=     'AND te_index = 1 ';
		$ret = $this->selectRecord($queryStr, array($threadId), $row);
		return $ret;
	}
	/**
	 * フォト情報を取得
	 *
	 * @param int		$id					公開画像ID
	 * @param string	$langId				言語
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getPhoto($id, $langId, &$row)
	{
		$queryStr  = 'SELECT * FROM photo LEFT JOIN _login_user ON ht_owner_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ht_deleted = false ';
		$queryStr .=     'AND ht_public_id = ? ';
		$queryStr .=     'AND ht_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id, $langId), $row);
		return $ret;
	}
}
?>
