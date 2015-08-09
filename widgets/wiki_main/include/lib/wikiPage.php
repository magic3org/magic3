<?php
/**
 * Wikiページ作成、アクセスクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
//require_once($gEnvManager->getCurrentWidgetDbPath() .	'/wiki_mainDb.php');

class WikiPage
{
	private static $db;		// DBオブジェクト
	private static $footNote = array();
	private static $availablePages;			// 利用可能なすべてのページ
	const CACHE_ENTITY_DATA			= 'entities_dat';		// 判定用データ定義名
	const CACHE_RECENT_DATA			= 'recent_dat';			// 最終更新データ
	const CACHE_AUTOLINK_DATA		= 'autolink_dat';		// オートリンク用データ
	const CACHE_USER_ONLINE_DATA	= 'user_online_dat';		// 現在参照中のユーザ
	const CONFIG_INIT = 'initialized';		// 初期設定(初期データインストール等)が完了しているかどうか

	// Wikiコンテンツデータタイプ
	const CONTENT_TYPE_DIFF			= 'diff';		// Wikiコンテンツdiffデータ
	const CONTENT_TYPE_COUNT		= 'count';		// ページアクセスカウントデータ
	const CONTENT_TYPE_UPLOAD		= 'upload';		// 添付ファイル情報データ
	const CONTENT_TYPE_CACHE_REL	= 'cache_rel';			// キャッシュデータ(関連ページ)
	const CONTENT_TYPE_CACHE_REF	= 'cache_ref';			// キャッシュデータ(参照ページ)
//	const CONTENT_TYPE_TRACKBACK	= 'trackback';			// トラックバックデータ
	const CONTENT_TYPE_CACHE		= 'cache';				// 共通キャッシュデータ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * オブジェクトを初期化
	 *
	 * @param object $db	DBオブジェクト
	 * @return				なし
	 */
	public static function init($db)
	{
		self::$db = $db;
		
		// 利用可能なページIDをすべて取得
		//self::$availablePages = self::getPages();
		self::$availablePages = self::$db->getAvailablePages();
	}
	/**
	 * 利用可能なページ名を更新
	 *
	 * @return				なし
	 */
	public static function updateAvailablePages()
	{
		self::$availablePages = self::$db->getAvailablePages();
	}
	/**
	 * 初期データ読み込み
	 *
	 * @return bool			true=成功、false=失敗
	 */
	public static function readInitData()
	{
		global $gEnvManager;
		
		$status = false;		// 戻り値初期化
		
		// 初期化が完了しているかチェック
		$init = self::$db->getConfig(self::CONFIG_INIT);
		if (empty($init)){
			// 初期データディレクトリ内のページデータファイルをすべて読み込む
			$path = $gEnvManager->getCurrentWidgetIncludePath() . '/data';
			if (is_dir($path)){
				$dir = dir($path);
				while (($file = $dir->read()) !== false){
					$filePath = $path . '/' . $file;
					$pathParts = pathinfo($file);
					$ext = $pathParts['extension'];		// 拡張子
					// ファイルかどうかチェック
					if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath) &&
						strncmp($file, '_', 1) != 0 &&	// 「_」で始まる名前のファイルは読み込まない
						$ext == 'txt'){		// 拡張子が「.txt」のファイルだけを読み込む
						$name = decode(basename($file, '.txt'));
						if (!empty($name)){
							$ret = self::getPageFile($name, $data);		// ファイルから初期データを読み込む
							if ($ret) self::initPage($name, $data);
						}
					}
				}
				$dir->close();
			}
			$ret = self::$db->updateConfig(self::CONFIG_INIT, '1');
			if ($ret) $status = true;
			
			// ##### 利用可能なページ名を更新 #####
			self::updateAvailablePages();
		}
		return $status;
	}
	/**
	 * 初期化が完了しているかどうかをチェック
	 *
	 * @return bool				true=完了、false=未完了
	 */
	public static function isInit()
	{
		$init = self::$db->getConfig(self::CONFIG_INIT);
		if (empty($init)){
			return false;
		} else {
			return true;
		}
	}
	/**
	 * ページが存在するかどうかをチェック
	 *
	 * @param  string $name		Wiki名
	 * @return bool				true=存在する、false=存在しない
	 */
	public static function isPage($name)
	{
//		return self::$db->isExistsPage($name);
		return in_array($name, self::$availablePages);
	}
	/**
	 * ページデータが存在するかどうかをチェック(DBを確認)
	 *
	 * @param  string $name		Wiki名
	 * @return bool				true=存在する、false=存在しない
	 */
	public static function isExistsPage($name)
	{
		return self::$db->isExistsPage($name);
	}
	/**
	 * ページがロックされているかどうかをチェック
	 *
	 * @param  string $name		Wiki名
	 * @return bool				true=ロック状態、false=非ロック状態
	 */
	public static function isPageLocked($name)
	{
		$ret = self::$db->getPage($name, $row);
		if ($ret){
			if ($row['wc_locked']) return true;
		}
		return false;
	}
	/**
	 * ページを初期作成
	 *
	 * @param string $name		Wiki名
	 * @param string $data		初期データ
	 * @param bool   $updateAvailablePages		利用可能なページ一覧
	 * @return bool			true=成功、false=失敗
	 */
	public static function initPage($name, $data='', $updateAvailablePages = false)
	{
		// 引数エラーチェック
		if (empty($name)) return false;
		
		$ret = self::$db->updatePage($name, $data);
		
		// 利用可能なページ名を更新
		if ($updateAvailablePages) self::updateAvailablePages();

		return $ret;
	}
	/**
	 * ページの初期データファイルを読み込む
	 *
	 * @param  string $name		Wiki名
	 * @param  string $data		初期データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function getPageFile($name, &$data)
	{
		global $gEnvManager;
		
		$data = '';		// データ初期化
					
		// パラメータエラーチェック
		if (empty($name)) return false;
		
		$path = $gEnvManager->getCurrentWidgetIncludePath() . '/data/' . encode($name) . '.txt';
		if ($fData = file_get_contents($path)){		// ファイルが読み込めないときはファイルがないとする
			$data = $fData;
			return true;
		} else {
			return false;
		}
	}
	/**
	 * ページデータを取得
	 *
	 * @param string $name		ページ名
	 * @param bool $join		データ連結するかどうか
	 * @param int $serial		取得したデータレコードのシリアル番号
	 * @return string,array		取得データ
	 */
	public static function getPage($name, $join=false, &$serial=0)
	{
		$retVal = $join ? '' : array();

		$ret = self::$db->getPage($name, $row);
		if ($ret){
			if ($join){		// 文字列を返すとき
				$retVal = $row['wc_data'];
			} else {		// 行単位(改行コード含む)の配列にして返すとき
				$retVal = preg_split('/(?<=\n)/', $row['wc_data']);
			}
			$serial = $row['wc_serial'];
		}
		return $retVal;
	}
	/**
	 * ページデータを更新
	 *
	 * @param string $name		ページ名
	 * @param string $data		更新データ
	 * @param bool $keepTime	更新日時を維持するかどうか
	 * @param bool $updateAvailablePages		利用可能なページ一覧
	 * @return bool				true=成功、false=失敗
	 */
	public static function updatePage($name, $data, $keepTime = false, $updateAvailablePages = false)
	{
		// 引数エラーチェック
		if (empty($name)) return false;
		
		$type = '';		// ページタイプ
		$ret = self::$db->updatePage($name, $data, $type, $keepTime);
		
		// 関連データ更新
		if ($ret){
			// ##### 利用可能なページ名を更新 #####
			if ($updateAvailablePages) self::updateAvailablePages();
		}
		return $ret;
	}
	/**
	 * ページを削除
	 *
	 * @param string $name		ページ名
	 * @param bool   $updateAvailablePages		利用可能なページ一覧
	 * @return bool				true=成功、false=失敗
	 */
	public static function deletePage($name, $updateAvailablePages = false)
	{
		$type = '';		// ページタイプ
		$ret = self::$db->deletePage($name, $type);
		
		// 関連データも削除
		if ($ret){
			self::clearPageDiff($name);			// ページdiffデータ
			self::clearPageCount($name);		// ページカウント数
			self::clearPageUpload($name);		// アップロードファイル管理データ
			self::clearCacheRel($name);			// ページキャッシュデータ(関連ページ)
			self::clearCacheRef($name);			// ページキャッシュデータ(参照ページ)
//			self::clearPageTrackback($name);	// ページトラックバックデータ
			
			// ##### 利用可能なページ名を更新 #####
			if ($updateAvailablePages) self::updateAvailablePages();
		}
		return $ret;
	}
	/**
	 * ページを変更
	 *
	 * @param string $oldName		旧ページ名
	 * @param string $newName		新ページ名
	 * @param bool   $updateAvailablePages		利用可能なページ一覧
	 * @return bool					true=成功、false=失敗
	 */
	public static function renamePage($oldName, $newName, $updateAvailablePages = false)
	{
		$type = '';		// ページタイプ
		$ret = self::$db->renamePage($oldName, $newName, $type);
		
		// 関連データも削除
		if ($ret){
			self::clearPageDiff($oldName);			// ページdiffデータ
			self::clearPageCount($oldName);		// ページカウント数
			self::clearPageUpload($oldName);		// アップロードファイル管理データ
			self::clearCacheRel($oldName);			// ページキャッシュデータ(関連ページ)
			self::clearCacheRef($oldName);			// ページキャッシュデータ(参照ページ)
//			self::clearPageTrackback($oldName);	// ページトラックバックデータ
			
			// ##### 利用可能なページ名を更新 #####
			if ($updateAvailablePages) self::updateAvailablePages();
		}
		return $ret;
	}
	/**
	 * ページdiffデータを更新
	 *
	 * @param string $name		ページ名
	 * @param string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function updatePageDiff($name, $data)
	{
		// 引数エラーチェック
		if (empty($name)) return false;
		
		$ret = self::$db->updatePageOther($name, $data, self::CONTENT_TYPE_DIFF);
		return $ret;
	}
	/**
	 * ページdiffデータを取得
	 *
	 * @param string $name		ページ名
	 * @param bool $join		データ連結するかどうか
	 * @return string		取得データ
	 */
	public static function getPageDiff($name, $join=false)
	{
		$value = self::$db->getPageOther($name, self::CONTENT_TYPE_DIFF);
		if (!$join) $value = preg_split('/(?<=\n)/', $value);// 行単位(改行コード含む)の配列にして返すとき
		return $value;
	}
	/**
	 * ページdiffデータを削除
	 *
	 * @param string $name		ページ名
	 * @return bool				true=成功、false=失敗
	 */
	public static function clearPageDiff($name='')
	{
		return self::$db->clearPageOther($name, self::CONTENT_TYPE_DIFF);
	}
	/**
	 * ページアクセスカウントデータを更新
	 *
	 * @param string $name		ページ名
	 * @param  string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function updatePageCount($name, $data)
	{
		// 引数エラーチェック
		if (empty($name)) return false;
		
		$ret = self::$db->updatePageOther($name, $data, self::CONTENT_TYPE_COUNT);
		return $ret;
	}
	/**
	 * ページアクセスカウントデータを取得
	 *
	 * @param string $name		ページ名
	 * @param bool $join		データ連結するかどうか
	 * @return string		取得データ
	 */
	public static function getPageCount($name, $join=false)
	{
		$value = self::$db->getPageOther($name, self::CONTENT_TYPE_COUNT);
		if (!$join) $value = preg_split('/(?<=\n)/', $value);// 行単位(改行コード含む)の配列にして返すとき
		return $value;
	}
	/**
	 * ページアクセスカウントデータを削除
	 *
	 * @param string $name		ページ名
	 * @return bool				true=成功、false=失敗
	 */
	public static function clearPageCount($name='')
	{
		return self::$db->clearPageOther($name, self::CONTENT_TYPE_COUNT);
	}
	/**
	 * アップロードファイル管理データを更新
	 *
	 * @param string $name		ページ名
	 * @param  string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function updatePageUpload($name, $data)
	{
		// 引数エラーチェック
		if (empty($name)) return false;
		
		$ret = self::$db->updatePageOther($name, $data, self::CONTENT_TYPE_UPLOAD);
		return $ret;
	}
	/**
	 * アップロードファイル管理データを取得
	 *
	 * @param string $name		ページ名
	 * @param bool $join		データ連結するかどうか
	 * @return string		取得データ
	 */
	public static function getPageUpload($name, $join=false)
	{
		$value = self::$db->getPageOther($name, self::CONTENT_TYPE_UPLOAD);
		if (!$join) $value = preg_split('/(?<=\n)/', $value);// 行単位(改行コード含む)の配列にして返すとき
		return $value;
	}
	/**
	 * アップロードファイル管理データを削除
	 *
	 * @param string $name		ページ名
	 * @return bool				true=成功、false=失敗
	 */
	public static function clearPageUpload($name='')
	{
		return self::$db->clearPageOther($name, self::CONTENT_TYPE_UPLOAD);
	}
	/**
	 * ページキャッシュデータ(関連ページ)を更新
	 *
	 * @param string $name		ページ名
	 * @param  string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function updatePageCacheRel($name, $data)
	{
		// 引数エラーチェック
		if (empty($name)) return false;
		
		$ret = self::$db->updatePageOther($name, $data, self::CONTENT_TYPE_CACHE_REL);
		return $ret;
	}
	/**
	 * ページキャッシュデータ(関連ページ)を取得
	 *
	 * @param string $name		ページ名
	 * @param bool $join		データ連結するかどうか
	 * @return string		取得データ
	 */
	public static function getPageCacheRel($name, $join=false)
	{
		$value = self::$db->getPageOther($name, self::CONTENT_TYPE_CACHE_REL);
		if (!$join) $value = preg_split('/(?<=\n)/', $value);// 行単位(改行コード含む)の配列にして返すとき
		return $value;
	}
	/**
	 * ページキャッシュデータ(関連ページ)をすべて削除
	 *
	 * @param string $name		ページ名(空の場合はすべてのページが対象)
	 * @return bool				true=成功、false=失敗
	 */
	public static function clearCacheRel($name = '')
	{
		$ret = self::$db->clearPageOther($name, self::CONTENT_TYPE_CACHE_REL);
		return $ret;
	}
	/**
	 * ページキャッシュデータ(参照ページ)を更新
	 *
	 * @param string $name		ページ名
	 * @param  string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function updatePageCacheRef($name, $data)
	{
		// 引数エラーチェック
		if (empty($name)) return false;
		
		$ret = self::$db->updatePageOther($name, $data, self::CONTENT_TYPE_CACHE_REF);
		return $ret;
	}
	/**
	 * ページキャッシュデータ(参照ページ)を取得
	 *
	 * @param string $name		ページ名
	 * @param bool $join		データ連結するかどうか
	 * @return string		取得データ
	 */
	public static function getPageCacheRef($name, $join=false)
	{
		$value = self::$db->getPageOther($name, self::CONTENT_TYPE_CACHE_REF);
		if (!$join) $value = preg_split('/(?<=\n)/', $value);// 行単位(改行コード含む)の配列にして返すとき
		return $value;
	}
	/**
	 * ページキャッシュデータ(参照ページ)をクリア
	 *
	 * @param string $name		ページ名(空の場合はすべてのページが対象)
	 * @return bool				true=成功、false=失敗
	 */
	public static function clearCacheRef($name = '')
	{
		$ret = self::$db->clearPageOther($name, self::CONTENT_TYPE_CACHE_REF);
		return $ret;
	}
	/**
	 * ページトラックバックデータを更新
	 *
	 * @param string $name		ページ名
	 * @param  string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
/*	public static function updatePageTrackback($name, $data)
	{
		// 引数エラーチェック
		if (empty($name)) return false;
		
		$ret = self::$db->updatePageOther($name, $data, self::CONTENT_TYPE_TRACKBACK);
		return $ret;
	}*/
	/**
	 * ページトラックバックデータを取得
	 *
	 * @param string $name		ページ名
	 * @param bool $join		データ連結するかどうか
	 * @return string		取得データ
	 */
/*	public static function getPageTrackback($name, $join=false)
	{
		$value = self::$db->getPageOther($name, self::CONTENT_TYPE_TRACKBACK);
		if (!$join) $value = preg_split('/(?<=\n)/', $value);// 行単位(改行コード含む)の配列にして返すとき
		return $value;
	}*/
	/**
	 * ページトラックバックデータを削除
	 *
	 * @param string $name		ページ名
	 * @return bool				true=成功、false=失敗
	 */
/*	public static function clearPageTrackback($name='')
	{
		return self::$db->clearPageOther($name, self::CONTENT_TYPE_TRACKBACK);
	}*/
	/**
	 * 最終更新キャッシュデータを更新
	 *
	 * @param  string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function updateCacheRecentChanges($data)
	{
		$ret = self::$db->updatePageOther(self::CACHE_RECENT_DATA, $data, self::CONTENT_TYPE_CACHE);
		return $ret;
	}
	/**
	 * 最終更新キャッシュデータを取得
	 *
	 * @param bool $join		データ連結するかどうか
	 * @return string		取得データ
	 */
	public static function getCacheRecentChanges($join=false)
	{
		$value = self::$db->getPageOther(self::CACHE_RECENT_DATA, self::CONTENT_TYPE_CACHE);
		if (!$join) $value = preg_split('/(?<=\n)/', $value);// 行単位(改行コード含む)の配列にして返すとき
		return $value;
	}
	/**
	 * 自動リンクキャッシュデータを更新
	 *
	 * @param  string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function updateCacheAutolink($data)
	{
		$ret = self::$db->updatePageOther(self::CACHE_AUTOLINK_DATA, $data, self::CONTENT_TYPE_CACHE);
		return $ret;
	}
	/**
	 * 自動リンクキャッシュデータを取得
	 *
	 * @param bool $join		データ連結するかどうか
	 * @return string		取得データ
	 */
	public static function getCacheAutolink($join=false)
	{
		$value = self::$db->getPageOther(self::CACHE_AUTOLINK_DATA, self::CONTENT_TYPE_CACHE);
		if (!$join) $value = preg_split('/(?<=\n)/', $value);// 行単位(改行コード含む)の配列にして返すとき
		return $value;
	}
	/**
	 * 現在参照中のユーザデータを更新
	 *
	 * @param  string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function updateCacheUserOnline($data)
	{
		$ret = self::$db->updatePageOther(self::CACHE_USER_ONLINE_DATA, $data, self::CONTENT_TYPE_CACHE);
		return $ret;
	}
	/**
	 * 現在参照中のユーザデータを取得
	 *
	 * @param bool $join		データ連結するかどうか
	 * @return string			取得データ
	 */
	public static function getCacheUserOnline($join=false)
	{
		$value = self::$db->getPageOther(self::CACHE_USER_ONLINE_DATA, self::CONTENT_TYPE_CACHE);
		if (!$join) $value = preg_split('/(?<=\n)/', $value);// 行単位(改行コード含む)の配列にして返すとき
		return $value;
	}
	/**
	 * ページ名を取得
	 *
	 * @return array		ページ名
	 */
	public static function getPages()
	{
//		$retVal = self::$db->getAvailablePages();
//		return $retVal;
		return self::$availablePages;			// 利用可能なすべてのページ
	}
	/**
	 * 削除済みを含めたすべてのページ名を取得
	 *
	 * @return array		ページ名
	 */
	public static function getAllPages()
	{
		$retVal = self::$db->getAllPages();
		return $retVal;
	}
	/**
	 * バックアップのあるページ名を取得
	 *
	 * @return array		ページ名
	 */
	public static function getAllBackupPages()
	{
		$pages = array();
		$allPage = self::getPages();
		for ($i = 0; $i < count($allPage); $i++){
			$name = $allPage[$i];
			if (self::isPageBackup($name)) $pages[] = $name;
		}
		return $pages;
	}
	/**
	 * カウンタ付きのページのページ名を取得
	 *
	 * @return array		ページ名
	 */
	public static function getCountPages()
	{
		$retVal = self::$db->getAvailablePages(self::CONTENT_TYPE_COUNT);
		return $retVal;
	}
	/**
	 * トラックバックデータのページ名を取得
	 *
	 * @return array		ページ名
	 */
/*	public static function getTrackbackPages()
	{
		$retVal = self::$db->getAvailablePages(self::CONTENT_TYPE_TRACKBACK);
		return $retVal;
	}*/
	/**
	 * キャッシュデータ(関連ページ)が存在するページ名を取得
	 *
	 * @return array		ページ名
	 */
	public static function getPageCacheRelPages()
	{
		$retVal = self::$db->getAvailablePages(self::CONTENT_TYPE_CACHE_REL);
		return $retVal;
	}	
	/**
	 * キャッシュデータ(参照ページ)が存在するページ名を取得
	 *
	 * @return array		ページ名
	 */
	public static function getPageCacheRefPages()
	{
		$retVal = self::$db->getAvailablePages(self::CONTENT_TYPE_CACHE_REF);
		return $retVal;
	}
	/**
	 * ページ更新日時を取得
	 *
	 * @param string $name		ページ名
	 * @return int				UNIXタイムスタンプ
	 */
	public static function getPageTime($name)
	{
		$retVal = 0;
		$ret = self::$db->getPage($name, $row);
		if ($ret){
			$retVal = strtotime($row['wc_content_dt']) - LOCALZONE;
		}
		return $retVal;
	}
	/**
	 * ページ更新日時を更新
	 *
	 * @param string $name		ページ名
	 * @param timestamp $time	更新日時
	 * @return bool				true=成功、false=失敗
	 */
	public static function updatePageTime($name, $time=null)
	{
		// 引数エラーチェック
		if (empty($name)) return false;
		
		$ret = self::$db->updatePageTime($name, $time);
		return $ret;
	}
	/**
	 * ページのロックを制御
	 *
	 * @param string $name		ページ名
	 * @param bool $lock		true=ページロック、false=ページロック解除
	 * @return bool				true=成功、false=失敗
	 */
	public static function lockPage($name, $lock)
	{
		$ret = self::$db->lockPage($name, $lock);
		return $ret;
	}
	/**
	 * ページパックアップ情報を取得
	 *
	 * @param string $name		ページ名
	 * @param int $age			世代番号(1～)。「世代番号=履歴番号+1」で対応させる。-1のときは最新を含まないすべての世代を取得
	 * @param bool $onlyVisible	表示可能なバックアップだけを対象とするかどうか
	 * @return array			バックアップ情報
	 */
	public static function getPageBackupInfo($name, $age=-1, $onlyVisible = true)
	{
		$backupInfo = array();
		
		// 履歴番号を作成
		if ($age == -1){
			$history = $age;
		} else {
			$history = $age -1;
		}
		
		$ret = self::$db->getPageInfo($name, $history, $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				if ($rows[$i]['wc_deleted']){		// 最新以外のデータを取得
					if (($onlyVisible && $rows[$i]['wc_visible']) || !$onlyVisible){
						$backupInfo[$rows[$i]['wc_history_index'] + 1] = array(	'time'		=> strtotime($rows[$i]['wc_content_dt']) - LOCALZONE,	// 最終更新
																			'user'		=> $rows[$i]['wc_update_user_id'],
																			'visible'	=> $rows[$i]['wc_visible'],
																			'serial'	=> $rows[$i]['wc_serial']);
					}
				}
			}
		}
		return $backupInfo;
	}
	/**
	 * バックアップデータが存在するかどうかをチェック
	 *
	 * @param string $name		Wiki名
	 * @return bool				true=存在する、false=存在しない
	 */
	public static function isPageBackup($name)
	{
		$ret = self::$db->getPage($name, $row);
		if ($ret){
			if (intval($row['wc_history_index']) > 0) return true;
		}
		return false;
	}
	/**
	 * バックアップデータを取得
	 *
	 * @param string $name		ページ名
	 * @param bool $join		データ連結するかどうか
	 * @param int $serial		取得したデータレコードのシリアル番号
	 * @return string,array		取得データ
	 */
	public static function getPageBackup($name, $age, $join=false, &$serial=0)
	{
		$retVal = $join ? '' : array();

		// 履歴番号を作成
		$history = $age -1;

		$ret = self::$db->getPageWithHistory($name, $history, $row);
		if ($ret){
			if ($join){		// 文字列を返すとき
				$retVal = $row['wc_data'];
			} else {		// 行単位(改行コード含む)の配列にして返すとき
				$retVal = preg_split('/(?<=\n)/', $row['wc_data']);
			}
			$serial = $row['wc_serial'];
		}
		return $retVal;
	}
	/**
	 * バックアップデータの表示制御
	 *
	 * @param string $name		ページ名
	 * @param bool $visible		true=表示、false=非表示
	 * @return bool				true=成功、false=失敗
	 */
	public static function setPageBackupVisible($name, $visible)
	{
		$ret = self::$db->setOldPageVisible($name, $visible);
		return $ret;
	}
	/**
	 * 判定用データファイル(entities.dat)を読み込む
	 *
	 * @return string		取得データ
	 */
	public static function getEntityData()
	{
		global $gEnvManager;
		
		// DBをチェックする
		if (self::$db->isExistsPageOther(self::CACHE_ENTITY_DATA, self::CONTENT_TYPE_CACHE)){
			return self::$db->getPageOther(self::CACHE_ENTITY_DATA, self::CONTENT_TYPE_CACHE);
		} else {
			// DBに存在しない場合は初期化
			$path = $gEnvManager->getCurrentWidgetIncludePath() . '/data/entities.dat';
			if ($fData = file_get_contents($path)){
				if (self::$db->updatePageOther(self::CACHE_ENTITY_DATA, $fData, self::CONTENT_TYPE_CACHE)){
					return $fData;
				} else {
					return '';
				}
			} else {
				return '';
			}
		}
	}
	/**
	 * 判定用データを更新
	 *
	 * @param  string $data		更新データ
	 * @return bool				true=成功、false=失敗
	 */
	public static function updateEntityData($data)
	{
		$ret = self::$db->updatePageOther(self::CACHE_ENTITY_DATA, $data, self::CONTENT_TYPE_CACHE);
		return $ret;
	}
	/**
	 * 脚注文字列を取得
	 *
	 * @return array		行単位の脚注文字列
	 */
	public static function getFootNote()
	{
		return self::$footNote;
	}
	/**
	 * 脚注文字列を追加
	 *
	 * @param string $key		キー値
	 * @param string $value		設定値
	 * @return なし
	 */
	public static function addFootNote($key, $value)
	{
		self::$footNote[$key] = $value;
	}
}
?>
