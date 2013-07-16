<?php
/**
 * DBクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: banner3Db.php 3116 2010-05-11 12:19:18Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class banner3Db extends BaseDb
{
	/**
	 * 画像リンクをシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getImageBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM bn_item LEFT JOIN _login_user ON bi_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE bi_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * 画像リンクをIDで取得
	 *
	 * @param string	$id					項目ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getImageById($id, &$row)
	{
		$queryStr  = 'SELECT * FROM bn_item LEFT JOIN _login_user ON bi_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE bi_id = ? AND bi_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * 画像リンク項目の新規追加
	 *
	 * @param string  $serial		シリアル番号
	 * @param string  $name			名前
	 * @param int     $type			項目タイプ
	 * @param string  $admin_note	備考
	 * @param string  $image		画像へのパス
	 * @param string  $link_url		遷移先URL
	 * @param string  $width		幅
	 * @param string  $height		高さ
	 * @param string  $alt			画像テキスト
	 * @param string  $html			HTML
	 * @param bool    $visible		表示状態
	 * @param timestamp  $startdt	表示開始
	 * @param timestamp  $enddt		表示終了
	 * @param string  $attr			その他の属性
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateImage($serial, $name, $type, $admin_note, $image, $link_url, $width, $height, $alt, $html, $visible, $startdt, $enddt, $attr, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		if (empty($startdt)) $startdt = $this->gEnv->getInitValueOfTimestamp();
		if (empty($enddt)) $enddt = $this->gEnv->getInitValueOfTimestamp();
				
		// トランザクション開始
		$this->startTransaction();
		
		$historyIndex = 0;
		if ($serial == 0){		// シリアル番号が0のときは、バナーIDを新規取得
			// バナーIDを決定する
			$queryStr = 'select max(bi_id) as bid from bn_item ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$banId = $row['bid'] + 1;
			} else {
				$banId = 1;
			}
		} else {
			// 前レコードの削除状態チェック
			$queryStr = 'SELECT * FROM bn_item ';
			$queryStr .=  'WHERE bi_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serial), $row);
			if ($ret){
				if ($row['bi_deleted']){		// レコードが削除状態であれば終了
					$this->endTransaction();
					return false;
				}
				$historyIndex = $row['bi_history_index'] + 1;
				$banId = $row['bi_id'];
			} else {	// 指定したシリアル番号のレコードが存在していなければ終了
				$this->endTransaction();
				return false;
			}
		}

		// 古いレコードを削除
		$queryStr  = 'UPDATE bn_item ';
		$queryStr .=   'SET bi_deleted = true, ';	// 削除
		$queryStr .=     'bi_update_user_id = ?, ';
		$queryStr .=     'bi_update_dt = ? ';
		$queryStr .=   'WHERE bi_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
			
		// データを追加
		$queryStr = 'INSERT INTO bn_item ';
		$queryStr .=  '(bi_id, bi_history_index, bi_name, bi_type, bi_admin_note, bi_image_url, bi_link_url, bi_image_width, bi_image_height, bi_image_alt, bi_html, bi_visible, bi_active_start_dt, bi_active_end_dt, bi_attr, bi_create_user_id, bi_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($banId, $historyIndex, $name, $type, $admin_note, $image, $link_url, $width, $height, $alt, $html, $visible, $startdt, $enddt, $attr, $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'select max(bi_serial) as bs from bn_item ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['bs'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画像リンク項目の削除
	 *
	 * @param int $serial			シリアル番号
	 * @return						true=成功、false=失敗
	 */
	function delImage($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM bn_item ';
			$queryStr .=   'WHERE bi_deleted = false ';		// 未削除
			$queryStr .=     'AND bi_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE bn_item ';
		$queryStr .=   'SET bi_deleted = true, ';	// 削除
		$queryStr .=     'bi_update_user_id = ?, ';
		$queryStr .=     'bi_update_dt = ? ';
		$queryStr .=   'WHERE bi_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画像リンク一覧を取得
	 *
	 * @param int		$limit				取得する項目数(-1=すべて取得)
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getImageList($limit, $page, $callback)
	{
		if ($limit == -1){
			$queryStr  = 'SELECT * FROM bn_item ';
			$queryStr .=   'WHERE bi_deleted = false ';
			$queryStr .=   'ORDER BY bi_id';
		} else {
			$offset = $limit * ($page -1);
			if ($offset < 0) $offset = 0;
		
			$queryStr  = 'SELECT * FROM bn_item ';
			$queryStr .=   'WHERE bi_deleted = false ';
			$queryStr .=   'ORDER BY bi_id limit ' . $limit . ' offset ' . $offset;
		}
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * 画像リンク数を取得
	 *
	 * @return int		項目数
	 */
	function getImageCount()
	{
		$queryStr  = 'SELECT * FROM bn_item ';
		$queryStr .=   'WHERE bi_deleted = false ';
		return $this->selectRecordCount($queryStr, array());
	}
	/**
	 * 画像リンク一覧をIDで取得
	 *
	 * @param array		$idArray			IDの配列
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getImageListById($idArray, $callback)
	{
		if (!is_array($idArray) || count($idArray) <= 0) return;
		
		$queryStr  = 'SELECT * FROM bn_item ';
		$queryStr .=   'WHERE bi_deleted = false ';
		$queryStr .=     'AND bi_id in (' . implode($idArray, ',') . ') ';
		$queryStr .=   'ORDER BY bi_id';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * バナー項目のビューカウント総数を取得
	 *
	 * @param  int $serial				バナー項目シリアル番号
	 * @return int						総数
	 */
	function getTotalViewCount($serial)
	{
		$count = 0;
		$params = array();
		$queryStr  = 'SELECT * FROM bn_item_view ';
		$queryStr .=   'WHERE bv_item_serial = ? ';	$params[] = $serial;
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * バナー表示ログの記録
	 *
	 * @param int    $bannerItemSerial		バナー項目シリアル番号
	 * @param int    $accessLogSerial    	アクセスログシリアル番号
	 * @return string						公開キー
	 */
	function viewBannerItemLog($bannerItemSerial, $accessLogSerial)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$max = 0;
		$queryStr = 'SELECT MAX(bv_serial) AS m FROM bn_item_view';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $max = $row['m'];
		
		// 公開キーの作成
		$key = md5($this->gRequest->trimServerValueOf('REMOTE_ADDR') . ($max + 1));
		
		$queryStr  = 'INSERT INTO bn_item_view (';
		$queryStr .=   'bv_public_key, ';
		$queryStr .=   'bv_item_serial, ';
		$queryStr .=   'bv_log_serial, ';
		$queryStr .=   'bv_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ?, ?, now()';
		$queryStr .= ')';
		$ret = $this->execStatement($queryStr, array($key, $bannerItemSerial, $accessLogSerial));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		if ($ret){
			return $key;
		} else {
			return '';
		}
	}
	/**
	 * バナークリックログの記録
	 *
	 * @param string    $stamp				公開ID
	 * @param string    $url   				リダイレクト先URL
	 * @param int       $accessLogSerial    アクセスログシリアル番号
	 * @return bool							true = 成功、false = 失敗
	 */
	function clickBannerItemLog($stamp, $url, $accessLogSerial)
	{	
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'INSERT INTO bn_item_access (';
		$queryStr .=   'ba_public_key, ';
		$queryStr .=   'ba_redirect_url, ';
		$queryStr .=   'ba_log_serial, ';
		$queryStr .=   'ba_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ?, ?, now()';
		$queryStr .= ')';
		$ret = $this->execStatement($queryStr, array($stamp, $url, $accessLogSerial));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * バナー定義一覧を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getBannerList($callback)
	{
		$queryStr = 'SELECT * FROM bn_def LEFT JOIN _login_user ON bd_update_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'ORDER BY bd_id';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * バナー定義の更新
	 *
	 * @param int     $bannerId		バナーID(0=新規追加、0以外=更新)
	 * @param string  $name			バナー名
	 * @param string  $bannerItem	バナー項目ID(「,」区切り)
	 * @param int     $dispType		バナー表示方法
	 * @param int     $dispDirect	バナー表示方向
	 * @param int     $dispCount	バナー項目表示数
	 * @param string  $html			テンプレートHTML
	 * @param string  $css			CSS
	 * @param string  $cssId		CSS用ID
	 * @param int     $newBannerId	新規バナーID
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateBanner($bannerId, $name, $bannerItem, $dispType, $dispDirect, $dispCount, $html, $css, $cssId, &$newBannerId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($bannerId)){
			// バナーIDを決定する
			$queryStr = 'SELECT MAX(bd_id) AS bid FROM bn_def ';
			$ret = $this->selectRecord($queryStr, array(), $row);
			if ($ret){
				$newBannerId = $row['bid'] + 1;
			} else {
				$newBannerId = 1;
			}

			// データを追加
			$queryStr = 'INSERT INTO bn_def ';
			$queryStr .=  '(bd_id, bd_name, bd_item_id, bd_disp_type, bd_disp_direction, bd_disp_item_count, bd_item_html, bd_css, bd_css_id, bd_update_user_id, bd_update_dt) ';
			$queryStr .=  'VALUES ';
			$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
			$this->execStatement($queryStr, array($newBannerId, $name, $bannerItem, $dispType, $dispDirect, $dispCount, $html, $css, $cssId, $userId, $now));
		} else {
			// データを更新
			$queryStr  = 'UPDATE bn_def ';
			$queryStr .=   'SET bd_name = ?, ';
			$queryStr .=     'bd_item_id = ?, ';
			$queryStr .=     'bd_disp_type = ?, ';
			$queryStr .=     'bd_disp_direction = ?, ';
			$queryStr .=     'bd_disp_item_count = ?, ';
			$queryStr .=     'bd_item_html = ?, ';
			$queryStr .=     'bd_css = ?, ';
			$queryStr .=     'bd_css_id = ?, ';
			$queryStr .=     'bd_update_user_id = ?, ';
			$queryStr .=     'bd_update_dt = ? ';
			$queryStr .=   'WHERE bd_id = ?';
			$this->execStatement($queryStr, array($name, $bannerItem, $dispType, $dispDirect, $dispCount, $html, $css, $cssId, $userId, $now, $bannerId));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * バナー定義の削除
	 *
	 * @param int $serial			シリアル番号
	 * @return						true=成功、false=失敗
	 */
	function delBanner($serial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		if (!is_array($serial) || count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM bn_def ';
			$queryStr .=   'WHERE bd_id = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコード削除
		$queryStr = "DELETE FROM bn_def ";
		$queryStr .=   'WHERE bd_id in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array());
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * バナー定義をバナーIDで取得
	 *
	 * @param string	$bannerId			バナーID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getBanner($bannerId, &$row)
	{
		$queryStr  = 'SELECT * FROM bn_def LEFT JOIN _login_user ON bd_update_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE bd_id = ? ';
		$ret = $this->selectRecord($queryStr, array($bannerId), $row);
		return $ret;
	}
	/**
	 * バナー定義の読み込みインデックスを更新
	 * (更新日時、更新者は更新しない)
	 *
	 * @param string	$bannerId			バナーID
	 * @param int       $index				インデックス番号
	 * @return bool							true = 成功、false = 失敗
	 */
	function updateBannerItemIndex($bannerId, $index)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// データを更新
		$queryStr  = 'UPDATE bn_def ';
		$queryStr .=   'SET bd_first_item_index = ? ';
		$queryStr .=   'WHERE bd_id = ?';
		$this->execStatement($queryStr, array($index, $bannerId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
