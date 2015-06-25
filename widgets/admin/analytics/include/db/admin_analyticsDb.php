<?php
/**
 * アクセス解析用DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_analyticsDb.php 4889 2012-04-26 22:29:06Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class admin_analyticsDb extends BaseDb
{
	const MAX_URL_LENGTH = 180;					// URLの長さ最大値
	
	/**
	 * 最も古いアクセスログを取得
	 *
	 * @param array  	$row		取得レコード
	 * @param bool					true=成功、false=失敗
	 */
	function getOldAccessLog(&$row)
	{
//		$queryStr  = 'SELECT * FROM _access_log ';
/*		$queryStr  = 'SELECT al_dt FROM _access_log ';
		$queryStr .=   'ORDER BY al_serial';
		$ret = $this->selectRecord($queryStr, array(), $row);*/
		
		$serial = 0;
		$queryStr  = 'SELECT min(al_serial) as m FROM _access_log ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $serial = $row['m'];
		
		$queryStr  = 'SELECT * FROM _access_log ';
		$queryStr .=   'WHERE al_serial = ?';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * サイト解析状況を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateStatus($key, $value)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// トランザクションスタート
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		$queryStr  = 'SELECT as_value FROM _analyze_status ';
		$queryStr .=   'WHERE as_id = ? ';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret){
			$queryStr  = 'UPDATE _analyze_status ';
			$queryStr .=   'SET as_value = ?, ';
			$queryStr .=     'as_update_dt = ? ';
			$queryStr .=   'WHERE as_id = ? ';
			$ret = $this->execStatement($queryStr, array($value, $now, $key));			
		} else {
			$queryStr  = 'INSERT INTO _analyze_status (';
			$queryStr .=   'as_id, ';
			$queryStr .=   'as_value, ';
			$queryStr .=   'as_update_dt ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ?, ?';
			$queryStr .= ')';
			$ret = $this->execStatement($queryStr, array($key, $value, $now));	
		}
		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * サイト解析状況を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getStatus($key)
	{
		$retValue = '';
		$queryStr  = 'SELECT as_value FROM _analyze_status ';
		$queryStr .=   'WHERE as_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['as_value'];
		return $retValue;
	}
	/**
	 * 時間単位で一日分の集計処理を行う
	 *
	 * @param date		$date		集計する日付
	 * @param bool					true=成功、false=失敗
	 */
	public function calcDatePv($date)
	{
		// 一旦データをすべて削除
		$queryStr  = 'DELETE FROM _analyze_page_view ';
		$queryStr .=   'WHERE ap_date = ? ';
		$ret = $this->execStatement($queryStr, array($date));
		if (!$ret) return false;
		$queryStr  = 'DELETE FROM _analyze_daily_count ';
		$queryStr .=   'WHERE aa_date = ? ';
		$ret = $this->execStatement($queryStr, array($date));
		if (!$ret) return false;
		
		// ##### 時間単位で集計 #####
		for ($i = 0; $i < 24; $i++){
			// 時間範囲
			$startDt = $date . ' ' . $i . ':0:0';
			if ($i < 23){
				$endDt = $date . ' ' . ($i + 1) . ':0:0';
			} else {
				$endDt = date("Y/m/d", strtotime("$date 1 day")) . ' 0:0:0';		// 翌日
			}
		
			$params = array();
			$queryStr  = 'SELECT COUNT(*) AS total,al_uri,al_path FROM _access_log ';
			$queryStr .=   'WHERE (? <= al_dt AND al_dt < ?) ';
			$params[] = $startDt;
			$params[] = $endDt;
			$queryStr .=  'GROUP BY al_uri ';
			$queryStr .=  'ORDER BY total DESC';
			$ret = $this->selectRecords($queryStr, $params, $rows);

			// 集計データを登録
			if ($ret){			// データありのとき
				for ($j = 0; $j < count($rows); $j++){
					$total = $rows[$j]["total"];
					$path = $rows[$j]["al_path"];

					// URLの長さのチェック
					$rowUpdated = false;		// 更新したかどうか
					$url = $this->makeTruncStr($rows[$j]["al_uri"], self::MAX_URL_LENGTH);
					if ($url != $rows[$j]["al_uri"]){		// URLが長いときは省略形で登録
						$queryStr  = 'SELECT * FROM _analyze_page_view ';
						$queryStr .=   'WHERE ap_type = ? ';
						$queryStr .=     'AND ap_url = ? ';
						$queryStr .=     'AND ap_date = ? ';
						$queryStr .=     'AND ap_hour = ?';
						$ret = $this->selectRecord($queryStr, array(0/*すべてのデータ*/, $url, $date, $i), $row);
						if ($ret){
							$serial = $row['ap_serial'];
							$count = $row['ap_count'] + $total;
							
							$queryStr  = 'UPDATE _analyze_page_view ';
							$queryStr .=   'SET ap_count = ? ';
							$queryStr .=   'WHERE ap_serial = ? ';
							$ret = $this->execStatement($queryStr, array($count, $serial));
							if (!$ret) return false;		// エラー発生
							
							$rowUpdated = true;		// 更新したかどうか
						}
					}
					if (!$rowUpdated){			// データ更新していないとき
						$queryStr  = 'INSERT INTO _analyze_page_view (';
						$queryStr .=   'ap_type, ';
						$queryStr .=   'ap_url, ';
						$queryStr .=   'ap_date, ';
						$queryStr .=   'ap_hour, ';
						$queryStr .=   'ap_count, ';
						$queryStr .=   'ap_path ';
						$queryStr .= ') VALUES (';
						$queryStr .=   '?, ?, ?, ?, ?, ?';
						$queryStr .= ')';
						$ret = $this->execStatement($queryStr, array(0/*すべてのデータ*/, $url, $date, $i, $total, $path));
						if (!$ret) return false;		// エラー発生
					}
				}
			}
		}

		// ##### 訪問数を集計 #####
		// 時間範囲
		$startDt = $date . ' 0:0:0';
		$endDt = date("Y/m/d", strtotime("$date 1 day")) . ' 0:0:0';		// 翌日
	
		// 一日あたりURLごとの集計
		$params = array();
		$queryStr  = 'SELECT COUNT(DISTINCT al_session) AS total,al_uri,al_path FROM _access_log ';
		$queryStr .=   'WHERE (? <= al_dt AND al_dt < ?) ';
		$params[] = $startDt;
		$params[] = $endDt;
		$queryStr .=  'GROUP BY al_uri ';
		$queryStr .=  'ORDER BY total DESC';
		$ret = $this->selectRecords($queryStr, $params, $rows);
	
		// 集計データを登録
		if ($ret){			// データありのとき
			for ($j = 0; $j < count($rows); $j++){
				$total = $rows[$j]["total"];
				$path = $rows[$j]["al_path"];

				// URLの長さのチェック
				$rowUpdated = false;		// 更新したかどうか
				$url = $this->makeTruncStr($rows[$j]["al_uri"], self::MAX_URL_LENGTH);
				if ($url != $rows[$j]["al_uri"]){		// URLが長いときは省略形で登録
					$queryStr  = 'SELECT * FROM _analyze_daily_count ';
					$queryStr .=   'WHERE aa_type = ? ';
					$queryStr .=     'AND aa_url = ? ';
					$queryStr .=     'AND aa_date = ? ';
					$ret = $this->selectRecord($queryStr, array(0/*訪問数*/, $url, $date), $row);
					if ($ret){
						$serial = $row['aa_serial'];
						$count = $row['aa_count'] + $total;
						
						$queryStr  = 'UPDATE _analyze_daily_count ';
						$queryStr .=   'SET aa_count = ? ';
						$queryStr .=   'WHERE aa_serial = ? ';
						$ret = $this->execStatement($queryStr, array($count, $serial));
						if (!$ret) return false;		// エラー発生
						
						$rowUpdated = true;		// 更新したかどうか
					}
				}
				if (!$rowUpdated){			// データ更新していないとき
					$queryStr  = 'INSERT INTO _analyze_daily_count (';
					$queryStr .=   'aa_type, ';
					$queryStr .=   'aa_url, ';
					$queryStr .=   'aa_date, ';
					$queryStr .=   'aa_count, ';
					$queryStr .=   'aa_path ';
					$queryStr .= ') VALUES (';
					$queryStr .=   '?, ?, ?, ?, ?';
					$queryStr .= ')';
					$ret = $this->execStatement($queryStr, array(0/*訪問数*/, $url, $date, $total, $path));
					if (!$ret) return false;	// エラー発生
				}
			}
		}
		// 一日あたりアクセスポイントごとの集計
		$params = array();
		$queryStr  = 'SELECT COUNT(DISTINCT al_session) AS total,al_uri,al_path FROM _access_log ';
		$queryStr .=   'WHERE (? <= al_dt AND al_dt < ?) ';
		$params[] = $startDt;
		$params[] = $endDt;
		$queryStr .=  'GROUP BY al_path ';
		$queryStr .=  'ORDER BY total DESC';
		$ret = $this->selectRecords($queryStr, $params, $rows);

		// 集計データを登録
		if ($ret){			// データありのとき
			for ($j = 0; $j < count($rows); $j++){
				$total = $rows[$j]["total"];
				$path = $rows[$j]["al_path"];

				// FCKEditorからのアクセスは、アクセスポイントを使用しないので除く
				if (!empty($path)){
					$queryStr  = 'INSERT INTO _analyze_daily_count (';
					$queryStr .=   'aa_type, ';
					$queryStr .=   'aa_url, ';
					$queryStr .=   'aa_date, ';
					$queryStr .=   'aa_count, ';
					$queryStr .=   'aa_path ';
					$queryStr .= ') VALUES (';
					$queryStr .=   '?, ?, ?, ?, ?';
					$queryStr .= ')';
					$ret = $this->execStatement($queryStr, array(0/*訪問数*/, ''/*アクセスポイント指定*/, $date, $total, $path));
					if (!$ret) return false;	// エラー発生
				}
			}
		}
		// 一日あたりすべてのアクセスポイントの集計
		// MySQL v5.0.22でエラー発生(2010/12/3)
		// SQLエラーメッセージ(Mixing of GROUP columns (MIN(),MAX(),COUNT(),...) with no GROUP columns is illegal if there is no GROUP BY clause error code: 42000)
		// 「GROUP BY」を付けると回避可能
		$params = array();
		//$queryStr  = 'SELECT COUNT(DISTINCT al_session) AS total,al_uri,al_path FROM _access_log ';		// NG
		$queryStr  = 'SELECT COUNT(DISTINCT al_session) AS total FROM _access_log ';		// OK
		$queryStr .=   'WHERE (? <= al_dt AND al_dt < ?) ';
		$params[] = $startDt;
		$params[] = $endDt;
		$queryStr .=  'ORDER BY total DESC';
		$ret = $this->selectRecord($queryStr, $params, $row);

		// 集計データを登録
		if ($ret && $row["total"] > 0){			// データありのとき
			$total = $row["total"];

			$queryStr  = 'INSERT INTO _analyze_daily_count (';
			$queryStr .=   'aa_type, ';
			$queryStr .=   'aa_url, ';
			$queryStr .=   'aa_date, ';
			$queryStr .=   'aa_count, ';
			$queryStr .=   'aa_path ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ?, ?, ?, ?';
			$queryStr .= ')';
			$ret = $this->execStatement($queryStr, array(0/*訪問数*/, ''/*アクセスポイント指定*/, $date, $total, ''/*すべてのアクセスポイント*/));
			if (!$ret) return false;	// エラー発生
		}
		// ##### 訪問者数を集計 #####
		// 時間範囲
		$startDt = $date . ' 0:0:0';
		$endDt = date("Y/m/d", strtotime("$date 1 day")) . ' 0:0:0';		// 翌日
	
		// 1日あたりURLごとの集計
		$params = array();
		$queryStr  = 'SELECT COUNT(DISTINCT al_cookie_value) AS total,al_uri,al_path FROM _access_log ';
		$queryStr .=   'WHERE (? <= al_dt AND al_dt < ?) ';
		$params[] = $startDt;
		$params[] = $endDt;
		$queryStr .=  'GROUP BY al_uri ';
		$queryStr .=  'ORDER BY total DESC';
		$ret = $this->selectRecords($queryStr, $params, $rows);
	
		// 集計データを登録
		if ($ret){			// データありのとき
			for ($j = 0; $j < count($rows); $j++){
				$total = $rows[$j]["total"];
				$path = $rows[$j]["al_path"];

				// URLの長さのチェック
				$rowUpdated = false;		// 更新したかどうか
				$url = $this->makeTruncStr($rows[$j]["al_uri"], self::MAX_URL_LENGTH);
				if ($url != $rows[$j]["al_uri"]){		// URLが長いときは省略形で登録
					$queryStr  = 'SELECT * FROM _analyze_daily_count ';
					$queryStr .=   'WHERE aa_type = ? ';
					$queryStr .=     'AND aa_url = ? ';
					$queryStr .=     'AND aa_date = ? ';
					$ret = $this->selectRecord($queryStr, array(1/*訪問者数*/, $url, $date), $row);
					if ($ret){
						$serial = $row['aa_serial'];
						$count = $row['aa_count'] + $total;
						
						$queryStr  = 'UPDATE _analyze_daily_count ';
						$queryStr .=   'SET aa_count = ? ';
						$queryStr .=   'WHERE aa_serial = ? ';
						$ret = $this->execStatement($queryStr, array($count, $serial));
						if (!$ret) return false;		// エラー発生
						
						$rowUpdated = true;		// 更新したかどうか
					}
				}
				if (!$rowUpdated){			// データ更新していないとき
					$queryStr  = 'INSERT INTO _analyze_daily_count (';
					$queryStr .=   'aa_type, ';
					$queryStr .=   'aa_url, ';
					$queryStr .=   'aa_date, ';
					$queryStr .=   'aa_count, ';
					$queryStr .=   'aa_path ';
					$queryStr .= ') VALUES (';
					$queryStr .=   '?, ?, ?, ?, ?';
					$queryStr .= ')';
					$ret = $this->execStatement($queryStr, array(1/*訪問者数*/, $url, $date, $total, $path));
					if (!$ret) return false;	// エラー発生
				}
			}
		}
		
		// 1日あたりアクセスポイントごとの集計
		$params = array();
		$queryStr  = 'SELECT COUNT(DISTINCT al_cookie_value) AS total,al_uri,al_path FROM _access_log ';
		$queryStr .=   'WHERE (? <= al_dt AND al_dt < ?) ';
		$params[] = $startDt;
		$params[] = $endDt;
		$queryStr .=  'GROUP BY al_path ';
		$queryStr .=  'ORDER BY total DESC';
		$ret = $this->selectRecords($queryStr, $params, $rows);

		// 集計データを登録
		if ($ret){			// データありのとき
			for ($j = 0; $j < count($rows); $j++){
				$total = $rows[$j]["total"];
				$path = $rows[$j]["al_path"];

				// FCKEditorからのアクセスは、アクセスポイントを使用しないので除く
				if (!empty($path)){
					$queryStr  = 'INSERT INTO _analyze_daily_count (';
					$queryStr .=   'aa_type, ';
					$queryStr .=   'aa_url, ';
					$queryStr .=   'aa_date, ';
					$queryStr .=   'aa_count, ';
					$queryStr .=   'aa_path ';
					$queryStr .= ') VALUES (';
					$queryStr .=   '?, ?, ?, ?, ?';
					$queryStr .= ')';
					$ret = $this->execStatement($queryStr, array(1/*訪問者数*/, ''/*アクセスポイント指定*/, $date, $total, $path));
					if (!$ret) return false;	// エラー発生
				}
			}
		}
		
		// 1日あたりすべてのアクセスの集計
		$params = array();
		//$queryStr  = 'SELECT COUNT(DISTINCT al_cookie_value) AS total,al_uri,al_path FROM _access_log ';		// NG
		$queryStr  = 'SELECT COUNT(DISTINCT al_cookie_value) AS total FROM _access_log ';		// OK
		$queryStr .=   'WHERE (? <= al_dt AND al_dt < ?) ';
		$params[] = $startDt;
		$params[] = $endDt;
		$queryStr .=  'ORDER BY total DESC';
		$ret = $this->selectRecord($queryStr, $params, $row);
	
		// 集計データを登録
		if ($ret && $row["total"] > 0){			// データありのとき
			$total = $row["total"];

			$queryStr  = 'INSERT INTO _analyze_daily_count (';
			$queryStr .=   'aa_type, ';
			$queryStr .=   'aa_url, ';
			$queryStr .=   'aa_date, ';
			$queryStr .=   'aa_count, ';
			$queryStr .=   'aa_path ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ?, ?, ?, ?';
			$queryStr .= ')';
			$ret = $this->execStatement($queryStr, array(1/*訪問者数*/, ''/*アクセスポイント指定*/, $date, $total, ''/*すべてのアクセスポイント*/));
			if (!$ret) return false;	// エラー発生
		}
		return true;
	}
	/**
	 * ページビューデータを日付範囲で取得
	 *
	 * @param string	$path		アクセスポイント(NULL=すべてのデータ)
	 * @param date		$startDate	集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate	集計期間、終了日
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getPageViewByDate($path, $startDate, $endDate, $callback)
	{
		$params = array();
		$queryStr  = 'SELECT SUM(ap_count) AS total, ap_date FROM _analyze_page_view ';
		$queryStr .=   'WHERE ap_type = ? '; $params[] = 0;
		if (is_null($startDate)){
			$queryStr .=     'AND ap_date <= ? ';
		} else {
			$queryStr .=     'AND (? <= ap_date AND ap_date <= ?) ';
			$params[] = $startDate;
		}
		$params[] = $endDate;
		if (!is_null($path)){
			$queryStr .=  'AND ap_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'GROUP BY ap_date ';
		$queryStr .=  'ORDER BY ap_date';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 日次カウントデータを日付範囲で取得
	 *
	 * @param int       $type		データのタイプ(0=訪問数、1=訪問者数)
	 * @param string	$path		アクセスポイント(NULL=すべてのデータ)
	 * @param date		$startDate	集計期間、開始日(NULL=先頭からすべて)
	 * @param date		$endDate	集計期間、終了日
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getDailyCountByDate($type, $path, $startDate, $endDate, $callback)
	{
		$params = array();
		$queryStr  = 'SELECT SUM(aa_count) AS total, aa_date FROM _analyze_daily_count ';
		$queryStr .=   'WHERE aa_type = ? '; $params[] = $type;
		$queryStr .=     'AND aa_url = ? '; $params[] = '';			/*アクセスポイントごとの集計値*/
		if (is_null($startDate)){
			$queryStr .=     'AND aa_date <= ? ';
		} else {
			$queryStr .=     'AND (? <= aa_date AND aa_date <= ?) ';
			$params[] = $startDate;
		}
		$params[] = $endDate;
		if (is_null($path)){		// すべてのアクセスポイントの場合
			$queryStr .=  'AND aa_path = ? ';
			$params[] = '';
		} else {
			$queryStr .=  'AND aa_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'GROUP BY aa_date ';
		$queryStr .=  'ORDER BY aa_date';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 省略文字列を作成
	 *
	 * @param string  $str		変換元文字列
	 * @param int     $len		文字列長
	 * @return string			作成した文字列
	 */
	function makeTruncStr($str, $len)
	{
		if (strlen($str) > $len) $addStr = '...';
		$destStr = substr($str, 0, $len) . $addStr;
		return $destStr;
	}
	/**
	 * ページIDのリストを取得
	 *
	 * @param function $callback	コールバック関数
	 * @param int $type				リストの種別
	 * @param int $filter			データのフィルタリング(-1=PC携帯スマートフォンに関係なく取得、0=PC用のみ、1=携帯用のみ、2=スマートフォン用のみ)
	 * @return						なし
	 */
	function getPageIdList($callback, $type, $filter = -1)
	{
		$params = array($type);
		$queryStr = 'SELECT * FROM _page_id ';
		$queryStr .=  'WHERE pg_type = ? ';
		if ($filter != -1){
			// pg_device_typeは後で追加したため、pg_mobileを残しておく
			if ($filter != 1){			// 携帯以外のとき
				$queryStr .=    'AND pg_device_type = ? '; $params[] = $filter;
			}

			$queryStr .=  'AND pg_mobile = ? ';
			$mobile = 0;
			if ($filter == 1) $mobile = 1;			// 携帯のとき
			$params[] = $mobile;
		}
		$queryStr .=  'ORDER BY pg_priority';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 解析を行うアクセスポイントを取得
	 *
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getAnalyticsAccessPoint(&$rows)
	{
		$queryStr  = 'SELECT * FROM _page_id ';
		$queryStr .=   'WHERE pg_type = 0 ';			// アクセスポイント
		$queryStr .=     'AND pg_analytics = true ';	// アクセス解析あり
		$queryStr .=     'AND pg_visible = true ';		// 公開しているアクセスポイントのみ
		$queryStr .=     'AND pg_active = true ';		// アクセス許可しているアクセスポイントのみ
		$queryStr .=   'ORDER BY pg_priority';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
}
?>
