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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');
require_once($gEnvManager->getIncludePath() . '/common/userInfo.php');		// ユーザ情報クラス

class admin_mainDb extends BaseDb
{	
	private $now;		// 現在日時
	private $userId;		// ログイン中のユーザ
	private $maxNo;		// 最大管理番号
//	const CF_DEFAULT_TEMPLATE			= 'default_template';			// システム定義値取得用キー(PC用デフォルトテンプレート)
//	const CF_DEFAULT_TEMPLATE_MOBILE	= 'mobile_default_template';	// システム定義値取得用キー(携帯用デフォルトテンプレート)
	
	// 取得値
	const CAN_DETAIL_CONFIG = 'permit_detail_config';				// 詳細設定が可能かどうか
		
	/**
	 * システム定義値を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getSystemConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT sc_value FROM _system_config ';
		$queryStr .=  'WHERE sc_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['sc_value'];
		return $retValue;
	}
	/**
	 * システム定義値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateSystemConfig($key, $value)
	{
		// トランザクションスタート
		$this->startTransaction();
		
		$queryStr = 'SELECT sc_value FROM _system_config ';
		$queryStr .=  'WHERE sc_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret){
			$queryStr  = 'UPDATE _system_config ';
			$queryStr .=   'SET sc_value = ? ';
			$queryStr .=   'WHERE sc_id = ?';
			$ret = $this->execStatement($queryStr, array($value, $key));			
		} else {
			$queryStr = 'INSERT INTO _system_config (';
			$queryStr .=  'sc_id, ';
			$queryStr .=  'sc_value ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?';
			$queryStr .=  ')';
			$ret = $this->execStatement($queryStr, array($key, $value));	
		}
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * サイト定義値を更新
	 *
	 * @param string $lang		言語
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @param int $user			ユーザID
	 * @return					true = 正常、false=異常
	 */
	function updateSiteDef($lang, $key, $value)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のレコードの履歴インデックス取得
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM _site_def ';
		$queryStr .=   'WHERE sd_id = ? ';
		$queryStr .=     'AND sd_language_id = ? ';
		$queryStr .=  'ORDER BY sd_history_index desc ';
		$ret = $this->selectRecord($queryStr, array($key, $lang), $row);
		if ($ret){
			$historyIndex = $row['sd_history_index'] + 1;
		
			// レコードが削除されていない場合は削除
			if (!$row['sd_deleted']){
				// 古いレコードを削除
				$queryStr  = 'UPDATE _site_def ';
				$queryStr .=   'SET sd_deleted = true, ';	// 削除
				$queryStr .=     'sd_update_user_id = ?, ';
				$queryStr .=     'sd_update_dt = ? ';
				$queryStr .=   'WHERE sd_serial = ?';
				$ret = $this->execStatement($queryStr, array($user, $now, $row['sd_serial']));
				if (!$ret) return false;
			}
		}
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO _site_def ';
		$queryStr .=  '(';
		$queryStr .=  'sd_id, ';
		$queryStr .=  'sd_language_id, ';
		$queryStr .=  'sd_history_index, ';
		$queryStr .=  'sd_value, ';
		$queryStr .=  'sd_create_user_id, ';
		$queryStr .=  'sd_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($key, $lang, $historyIndex, $value, $user, $now));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * サイト定義値を取得
	 *
	 * @param string $lang		言語
	 * @param string $key		キーとなる項目値
	 * @return string			値
	 */
	function getSiteDef($lang, $key)
	{
		$queryStr  = 'SELECT * FROM _site_def ';
		$queryStr .=   'WHERE sd_deleted = false ';
		$queryStr .=     'AND sd_id = ? ';
		$queryStr .=     'AND sd_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($key, $lang), $row);
		if ($ret){
			return $row['sd_value'];
		} else {
			return '';
		}
	}
	/**
	 * システムの詳細設定が可能かどうか
	 *
	 * @return bool					true=可能、false=不可
	 */
	function canDetailConfig()
	{
		$retValue = $this->getSystemConfig(self::CAN_DETAIL_CONFIG);
		return $retValue;
	}
	/**
	 * ウィジェットリスト取得
	 *
	 * @param int      $type		ウィジェットのタイプ(-1=管理用、0=PC用、1=携帯用、2=スマートフォン)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllWidgetList($type, $callback)
	{
		// wd_device_typeは後で追加したため、wd_mobileを残しておく
//		$queryStr  = 'SELECT * FROM _widgets ';
		$queryStr  = 'SELECT DISTINCT wd_serial,wd_id,wd_name,wd_description,wd_license_type,wd_release_dt,wd_available,wd_active,wd_editable,wd_has_admin,wd_version,wd_latest_version,wd_required_version,pd_widget_id ';
		$queryStr .=   'FROM _widgets LEFT JOIN _page_def ON wd_id = pd_widget_id ';
		$queryStr .=   'WHERE wd_deleted = false ';// 削除されていない
		$params = array();
		switch ($type){
			case -1:		// 管理用
				$queryStr .=    'AND wd_admin = true ';		// 管理用
				$queryStr .=    'AND wd_device_type = 0 ';	// PC画面
				break;
			case 0:		// PC用
			case 2:		// スマートフォン用
			default:
				$queryStr .=    'AND wd_admin = false ';		// 管理用以外
				$queryStr .=    'AND wd_mobile = false ';		// 携帯用以外
				$queryStr .=    'AND wd_device_type = ? '; $params[] = $type;
				break;
			case 1:		// 携帯用のとき
				$queryStr .=    'AND wd_admin = false ';		// 管理用以外
				$queryStr .=    'AND wd_mobile = true ';		// 携帯用
				break;
		}
		$queryStr .=  'ORDER BY wd_id';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 管理メニュー項目用ウィジェットリスト(管理画面ありでPC携帯両方)取得
	 *
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAvailableWidgetListForEditMenu($callback)
	{
		$queryStr  = 'select * from _widgets ';
		$queryStr .=   'where wd_deleted = false ';// 削除されていない
		$queryStr .=     'and wd_available = true ';		// メニューから選択可能なもの
		$queryStr .=     'and wd_has_admin = true ';		// 管理機能あり
		$queryStr .=   'order by wd_sort_order,wd_id';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * ウィジェットタイプとデバイスタイプからウィジェットリストを取得
	 *
	 * @param string $widgetType	ウィジェットタイプ
	 * @param int $deviceType		デバイスタイプ
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	function getViewWidgetListByDeviceType($widgetType, $deviceType, &$rows)
	{
		$queryStr  = 'SELECT * FROM _widgets ';
		$queryStr .=   'WHERE wd_deleted = false ';	// 削除されていない
	//	$queryStr .=     'AND wd_type = ? ';		// ウィジェットタイプ
		$queryStr .=     'AND wd_content_type = ? ';		// 表示コンテンツタイプ
		$queryStr .=     'AND wd_device_type = ? ';		// デバイスタイプ
		$queryStr .=   'ORDER BY wd_priority';
		$retValue = $this->selectRecords($queryStr, array($widgetType, $deviceType), $rows);
		return $retValue;
	}
	/**
	 * ウィジェットIDリスト取得
	 *
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getAllWidgetIdList(&$rows)
	{
		$queryStr = 'select * from _widgets ';
		$queryStr .=  'where wd_deleted = false ';// 削除されていない
		$queryStr .=  'order by wd_id';
		
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * ウィジェットIDの存在チェック
	 *
	 * @param string  $id			ウィジェットID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsWidgetId($id)
	{
		$queryStr = 'SELECT * FROM _widgets ';
		$queryStr .=  'WHERE wd_deleted = false ';// 削除されていない
		$queryStr .=  'AND wd_id = ? ';
		return $this->isRecordExists($queryStr, array($id));
	}
	/**
	 * ウィジェットの追加
	 *
	 * @param string  $id				ウィジェットID
	 * @param string  $name				ウィジェット名
	 * @param int     $deviceType	端末タイプ(0=PC用、1=携帯用、2=スマートフォン)
	 * @param bool    $readScripts		スクリプトディレクトリを自動読み込みするかどうか
	 * @param bool    $readCss			cssディレクトリを自動読み込みするかどうか
	 * @param bool    $hasAdmin			管理画面があるかどうか
	 * @return							なし
	 */
	function addNewWidget($id, $name, $deviceType = 0, $readScripts = false, $readCss = false, $hasAdmin = false)
	{
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
		$historyIndex = 0;
		$mobile = 0;				// 携帯端末かどうか
		if ($deviceType == 1) $mobile = 1;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 同じIDが登録済みかどうかチェック
		$queryStr = 'select * from _widgets ';
		$queryStr .=  'where wd_id = ? ';
		$queryStr .=  'order by wd_history_index desc ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			if (!$row['wd_deleted']){		// レコードが削除されていなければ、削除
				// 古いレコードを削除
				$queryStr  = 'UPDATE _widgets ';
				$queryStr .=   'SET wd_deleted = true, ';	// 削除
				$queryStr .=     'wd_update_user_id = ?, ';
				$queryStr .=     'wd_update_dt = ? ';
				$queryStr .=   'WHERE wd_serial = ?';
				$this->execStatement($queryStr, array($userId, $now, $row['wd_serial']));
			}
			$historyIndex = $row['wd_history_index'] + 1;
		}

		$queryStr = 'INSERT INTO _widgets ';
		$queryStr .=  '(wd_id, wd_history_index, wd_name, wd_device_type, wd_mobile, wd_read_scripts, wd_read_css, wd_has_admin, wd_create_user_id, wd_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, now())';
		$this->execStatement($queryStr, array($id, $historyIndex, $name, intval($deviceType), intval($mobile), intval($readScripts), intval($readCss), intval($hasAdmin), $userId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
	}
	/**
	 * ウィジェットの更新
	 *
	 * @param int $serial			シリアル番号
	 * @param array $updateParams	更新パラメータ
	 * @return bool					true=成功、false=失敗
	 */
	function updateWidget($serial, $updateParams)
	{
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
		$updateFields = array();	// 更新するフィールド名
		$boolFields = array();		// boolタイプのフィールド名
		$updateFields[] = 'wd_language';			// 対応言語ID(「,」区切りで複数指定可)
		$updateFields[] = 'wd_name';				// ウィジェット名称
		$updateFields[] = 'wd_type';				// ウィジェット種別(content=コンテンツ表示)
		$updateFields[] = 'wd_content_type';		// 必要とするページのコンテンツ種別
		$updateFields[] = 'wd_device_type';			// 端末タイプ(0=PC、1=携帯、2=スマートフォン)
		$updateFields[] = 'wd_version';				// バージョン文字列
		$updateFields[] = 'wd_fingerprint';			// ソースコードレベルでウィジェットを識別するためのID
		$updateFields[] = 'wd_group_id';			// ウィジェットグループ(管理用)
		$updateFields[] = 'wd_compatible_id';		// 互換ウィジェットID
		$updateFields[] = 'wd_parent_id';			// 親ウィジェットID(ファイル名)
		$updateFields[] = 'wd_joomla_class';		// Joomla!テンプレート用のクラス名
		$updateFields[] = 'wd_suffix';				// HTMLタグのクラス名に付けるサフィックス文字列
		$updateFields[] = 'wd_params';				// 各種パラメータ
		$updateFields[] = 'wd_author';				// 作者名
		$updateFields[] = 'wd_copyright';			// 著作権
		$updateFields[] = 'wd_license';				// ライセンス
		$updateFields[] = 'wd_license_type';		// ライセンスタイプ(0=オープンソース、1=商用)
		$updateFields[] = 'wd_official_level';		// 公認レベル(0=非公認、1=準公認、10=正規公認)
		$updateFields[] = 'wd_status';				// 状態(0=通常,1=テスト中,-1=廃止予定,-10=廃止)
		$updateFields[] = 'wd_cache_type';			// キャッシュタイプ(0=不可、1=可、2=非ログイン時可, 3=ページキャッシュのみ可)
		$updateFields[] = 'wd_cache_lifetime';		// キャッシュの保持時間(分)
		$updateFields[] = 'wd_view_control_type';	// 表示出力の制御タイプ(-1=固定、0=可変、1=ウィジェットパラメータ可変、2=URLパラメータ可変)
		$updateFields[] = 'wd_description';			// 説明
		$updateFields[] = 'wd_url';					// 取得先URL
		$updateFields[] = 'wd_add_script_lib';		// 追加する共通スクリプトライブラリ(ライブラリ名で指定、「,」区切りで複数指定可)
		$updateFields[] = 'wd_add_scripts';			// 追加スクリプトファイル(相対パス表記、「,」区切りで複数指定可)
		$updateFields[] = 'wd_add_css';				// 追加CSSファイル(相対パス表記、「,」区切りで複数指定可)
		$updateFields[] = 'wd_add_script_lib_a';	// (管理機能用)追加する共通スクリプトライブラリ(ライブラリ名で指定、「,」区切りで複数指定可)
		$updateFields[] = 'wd_add_scripts_a';		// (管理機能用)追加スクリプトファイル(相対パス表記、「,」区切りで複数指定可)
		$updateFields[] = 'wd_add_css_a';			// (管理機能用)追加CSSファイル(相対パス表記、「,」区切りで複数指定可)
		$updateFields[] = 'wd_admin'; $boolFields[] = 'wd_admin';			// 管理用ウィジェットかどうか
		$updateFields[] = 'wd_mobile'; $boolFields[] = 'wd_mobile'; 	// 携帯対応かどうか
		$updateFields[] = 'wd_show_name'; $boolFields[] = 'wd_show_name';			// ウィジェット名称を表示するかどうか
		$updateFields[] = 'wd_read_scripts'; $boolFields[] = 'wd_read_scripts';		// スクリプトディレクトリを自動読み込みするかどうか
		$updateFields[] = 'wd_read_css'; $boolFields[] = 'wd_read_css';			// cssディレクトリを自動読み込みするかどうか
		$updateFields[] = 'wd_use_ajax'; $boolFields[] = 'wd_use_ajax';			// Ajax共通ライブラリを読み込むかどうか
    	$updateFields[] = 'wd_active'; $boolFields[] = 'wd_use_ajax';			// 一般ユーザが実行可能かどうか
    	$updateFields[] = 'wd_available'; $boolFields[] = 'wd_use_ajax';		// メニューから選択可能かどうか
		$updateFields[] = 'wd_editable'; $boolFields[] = 'wd_editable';			// データ編集可能かどうか
		$updateFields[] = 'wd_edit_content'; $boolFields[] = 'wd_edit_content';	// 主要コンテンツ編集可能かどうか
		$updateFields[] = 'wd_has_admin'; $boolFields[] = 'wd_has_admin';			// 管理画面があるかどうか
		$updateFields[] = 'wd_has_log'; $boolFields[] = 'wd_has_log';				// ログ参照画面があるかどうか
		$updateFields[] = 'wd_enable_operation'; $boolFields[] = 'wd_enable_operation';	// 単体起動可能かどうか
		$updateFields[] = 'wd_use_instance_def'; $boolFields[] = 'wd_use_instance_def';	// インスタンス定義が必要かどうか
		$updateFields[] = 'wd_initialized'; $boolFields[] = 'wd_initialized';			// 初期化完了かどうか
		$updateFields[] = 'wd_use_cache'; $boolFields[] = 'wd_use_cache';			// キャッシュ機能を使用するかどうか
		$updateFields[] = 'wd_has_rss'; $boolFields[] = 'wd_has_rss';				// RSS機能があるかどうか
		$updateFields[] = 'wd_sort_order';			// ソート順
		$updateFields[] = 'wd_launch_index';		// 遅延実行制御が必要な場合の実行順(0=未設定、0以上=実行順)
		$updateFields[] = 'wd_release_dt';		// リリース日時
		$updateFields[] = 'wd_install_dt';			// インストール日時
		$updateFields[] = 'wd_index_file';			// 起動クラスのファイル名
		$updateFields[] = 'wd_index_class';			// 起動クラス名
		$updateFields[] = 'wd_admin_file';			// 管理機能起動クラスのファイル名
		$updateFields[] = 'wd_admin_class';			// 管理機能起動クラス名
		$updateFields[] = 'wd_db';					// 対応DB種(mysql,pgsql等を「,」区切りで指定)
		$updateFields[] = 'wd_table_access_type';	// テーブルのアクセス範囲(0=テーブル未使用、1=共通テーブルのみ、2=独自テーブル)
		$updateFields[] = 'wd_content_name';		// コンテンツ名称(メニュー表示用)
		$updateFields[] = 'wd_content_info';		// コンテンツ情報
		$updateFields[] = 'wd_priority';			// 優先度
		$updateFields[] = 'wd_category_id';			// 所属カテゴリー
		$updateFields[] = 'wd_type_option';			// ウィジェット種別オプション(nav=ナビゲーションメニュー)
		$updateFields[] = 'wd_template_type';		// 対応するテンプレートタイプ(「,」区切りで指定。値=bootstrap,jquerymobile)
		$updateFields[] = 'wd_latest_version';		// 最新バージョンのバージョン文字列
 
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM _widgets ';
		$queryStr .=   'WHERE wd_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['wd_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['wd_history_index'] + 1;
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _widgets ';
		$queryStr .=   'SET wd_deleted = true, ';	// 削除
		$queryStr .=     'wd_update_user_id = ?, ';
		$queryStr .=     'wd_update_dt = ? ';
		$queryStr .=   'WHERE wd_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
		// ##### データ更新処理 #####
		// 更新対象外を除く
		$unsetParams = array('wd_id', 'wd_history_index', 'wd_create_user_id', 'wd_create_dt');
		for ($i = 0; $i < count($unsetParams); $i++){
			unset($updateParams[$unsetParams[$i]]);
		}
		$keys = array_keys($updateParams);// キーを取得
		
		// クエリー作成
		$queryStr  = 'INSERT INTO _widgets (';
		$queryStr .=   'wd_id, ';
		$queryStr .=   'wd_history_index, ';
		$valueStr = '(?, ?, ';
		$values = array($row['wd_id'], $historyIndex);
		// 呼び出しパラメータから取得値を連結
		for ($i = 0; $i < count($keys); $i++){
			$fieldName = $keys[$i];
			$queryStr .= $fieldName . ', ';
			$valueStr .= '?, ';
			if (in_array($fieldName, $boolFields)){
				$values[] = intval($updateParams[$fieldName]);
			} else {
				$values[] = $updateParams[$fieldName];
			}
		}
		
		// 更新値を設定
		for ($i = 0; $i < count($updateFields); $i++){
			$fieldName = $updateFields[$i];
			if (!in_array($fieldName, $keys)){		// フィールドがないとき
				$queryStr .= $fieldName . ', ';
				$valueStr .= '?, ';
				if (in_array($fieldName, $boolFields)){
					$values[] = intval($row[$fieldName]);
				} else {
					$values[] = $row[$fieldName];
				}
			}
		}

		// レコードを追加
		$queryStr .= 'wd_create_user_id, wd_create_dt) ';
		$valueStr .= '?, ?)';
		$values = array_merge($values, array($userId, $now));
		$queryStr .=  'VALUES ';
		$queryStr .=  $valueStr;
		$this->execStatement($queryStr, $values);

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットの削除
	 *
	 * @param string  $serial		シリアル番号
	 * @return						true=成功、false=失敗
	 */
	function deleteWidget($serial)
	{
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$queryStr  = 'select * from _widgets ';
		$queryStr .=   'WHERE wd_serial = ? ';
		$queryStr .=    'and wd_deleted = false';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if (!$ret){		// 登録レコードがないとき
			$this->endTransaction();
			return false;
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _widgets ';
		$queryStr .=   'SET wd_deleted = true, ';	// 削除
		$queryStr .=     'wd_update_user_id = ?, ';
		$queryStr .=     'wd_update_dt = ? ';
		$queryStr .=   'WHERE wd_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;		
	}
	/**
	 * ウィジェットの取得
	 *
	 * @param string  $serial		シリアル番号
	 * @param array $row			取得データ
	 * @return						true=正常、false=異常
	 */
	function getWidget($serial, &$row)
	{
		$queryStr  = 'select * from _widgets ';
		$queryStr .=   'where wd_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * ウィジェットの初期化状態を取得
	 *
	 * @param string  $id				ウィジェットID
	 * @return bool						true=初期済み、false=未初期化
	 */
	function isWidgetInitialized($id)
	{
		$queryStr = 'SELECT * from _widgets ';
		$queryStr .=  'WHERE wd_deleted = false ';// 削除されていない
		$queryStr .=  'AND wd_id = ?';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret && $row['wd_initialized']){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * ウィジェットの初期化状態を更新
	 *
	 * @param string  $id				ウィジェットID
	 * @param bool $init				初期化状態
	 * @return bool						true=成功、false=失敗
	 */
	function updateIsWidgetInitialized($id, $init)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr = 'SELECT * from _widgets ';
		$queryStr .=  'WHERE wd_deleted = false ';// 削除されていない
		$queryStr .=  'AND wd_id = ?';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if (!$ret){		// 登録レコードがないとき
			$this->endTransaction();
			return false;
		}
		
		// レコードを更新
		$queryStr  = 'UPDATE _widgets ';
		$queryStr .=   'SET wd_initialized = ?, ';	// 初期化状態
		$queryStr .=     'wd_update_user_id = ?, ';
		$queryStr .=     'wd_update_dt = ? ';
		$queryStr .=   'WHERE wd_serial = ?';
		$this->execStatement($queryStr, array(intval($init), $userId, $now, $row['wd_serial']));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットのバージョン情報を更新
	 *
	 * @param string  $id				ウィジェットID
	 * @param bool $verStr				バージョン文字列
	 * @return bool						true=成功、false=失敗
	 */
	function updateWidgetVerInfo($id, $verStr)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// レコードを更新
		$queryStr  = 'UPDATE _widgets ';
		$queryStr .=   'SET wd_latest_version = ?, ';	// 最新バージョン
		$queryStr .=     'wd_update_user_id = ?, ';
		$queryStr .=     'wd_update_dt = ? ';
		$queryStr .=   'WHERE wd_id = ? ';
		$queryStr .=     'AND wd_deleted = false ';
		$this->execStatement($queryStr, array($verStr, $userId, $now, $id));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * フロント画面のアクセスポイントのリストを取得
	 *
	 * @param function $callback			コールバック関数
	 * @param bool $activeOnly				有効なアクセスポイントのメニューIDのみを取得するかどうか
	 * @return								なし
	 */
	function getAccessPointList($callback, $activeOnly = false)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM _page_id ';
		$queryStr .= 'WHERE pg_type = 0 ';			// アクセスポイント
		$queryStr .=   'AND pg_frontend = true ';		// フロント画面用
		if ($activeOnly) $queryStr .=  'AND pg_active = true ';	// 有効
		$queryStr .= 'ORDER BY pg_priority';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * ページIDのリストを取得
	 *
	 * @param function $callback	コールバック関数
	 * @param int $type				リストの種別(0=ページID、1=ページサブID)
	 * @param int $filter			データのフィルタリング(-1=PC携帯スマートフォンに関係なく取得、0=PC用のみ、1=携帯用のみ、2=スマートフォン用のみ)
	 * @param bool $availableOnly	true=メニュー表示可能項目のみ取得、false=すべて取得
	 * @return						なし
	 */
	function getPageIdList($callback, $type, $filter = -1, $availableOnly = false)
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
//		if ($availableOnly) $queryStr .=    'AND pg_available = true ';		// メニューから選択可能項目のみ取得
		if ($availableOnly) $queryStr .=    'AND pg_active = true ';		// 有効ページのみメニュー用に取得
		$queryStr .=  'ORDER BY pg_priority';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * ページIDのリストを取得
	 *
	 * @param int $type				リストの種別(0=ページメインID,1=ページサブID)
	 * @param string $pageId		ページID
	 * @param array $row			取得データ
	 * @return bool					true=成功、false=失敗
	 */
	function getPageIdRecord($type, $pageId, &$row)
	{
		$queryStr = 'SELECT * FROM _page_id ';
		$queryStr .=  'WHERE pg_type = ? ';
		$queryStr .=  'AND pg_id = ?';
		return $this->selectRecord($queryStr, array($type, $pageId), $row);
	}
	/**
	 * ページIDのリストを取得
	 *
	 * @param int $type				リストの種別(0=ページメインID,1=ページサブID)
	 * @param array $row			取得データ
	 * @param bool $availableOnly	true=メニュー表示可能項目のみ取得、false=すべて取得
	 * @return bool					true=成功、false=失敗
	 */
	function getPageIdRecords($type, &$row, $availableOnly = false)
	{
		$queryStr 	= 'SELECT * FROM _page_id ';
		$queryStr .=  	'WHERE pg_type = ? ';
		//if ($availableOnly) $queryStr .=    'AND pg_available = true ';		// メニューから選択可能項目のみ取得
		if ($availableOnly) $queryStr .=    'AND pg_active = true ';		// 有効ページのみメニュー用に取得
		$queryStr .=  'ORDER BY pg_priority';
		return $this->selectRecords($queryStr, array($type), $row);
	}
	/**
	 * サブページIDのリストを取得
	 *
	 * @param string $pageId		ページID
	 * @param string $langId		言語ID
	 * @param function $callback	コールバック関数
	 * @param bool $availableOnly	true=メニュー表示可能項目のみ取得、false=すべて取得
	 * @return						なし
	 */
	function getPageSubIdList($pageId, $langId, $callback, $availableOnly = false)
	{
		//$queryStr = 'SELECT * FROM _page_info RIGHT JOIN _page_id ON pn_sub_id = pg_id AND pg_type = 1 AND pn_deleted = false AND pn_id = ? ';
		$queryStr = 'SELECT * FROM _page_info RIGHT JOIN _page_id ON pn_sub_id = pg_id AND pg_type = 1 AND pn_deleted = false AND pn_id = ? AND pn_language_id = ? ';// 2010/2/23更新
		$queryStr .=  'WHERE ((pn_deleted IS NULL ';
		$queryStr .=    'AND pg_type = 1) ';		// サブページID
		$queryStr .=    'OR pn_deleted = false) ';
		//if ($availableOnly) $queryStr .=    'AND pg_available = true ';		// メニューから選択可能項目のみ取得
		if ($availableOnly) $queryStr .=    'AND pg_active = true ';		// 有効ページのみメニュー用に取得
		$queryStr .=  'ORDER BY pg_priority';
		$this->selectLoop($queryStr, array($pageId, $langId), $callback);
	}
	/**
	 * ウィジェットが配置されているページサブIDのリストを取得
	 *
	 * @param string $pageId		ページID
	 * @param function $callback	コールバック関数
	 * @param int    $setId			定義セットID
	 * @return						なし
	 */
	function getPageSubIdListWithWidget($pageId, $callback, $setId = 0)
	{
		$queryStr  = 'SELECT DISTINCT pg_id, pg_name, pn_content_type FROM _page_def LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .= 'LEFT JOIN _page_info ON pd_id = pn_id AND pd_sub_id = pn_sub_id AND pn_deleted = false AND pn_language_id = \'\' ';
		$queryStr .=   'WHERE pd_id = ? ';
		$queryStr .=     'AND pd_sub_id != \'\' ';	// 共通でないウィジェットが配置されている
		$queryStr .=     'AND pd_set_id = ? ';
		$queryStr .=     'AND pg_visible = true ';	// 外部公開可能なページ
		$queryStr .=     'AND pg_active = true ';	// 外部公開可能なページ
		$queryStr .=   'ORDER BY pg_priority';
		$this->selectLoop($queryStr, array($pageId, $setId), $callback);
	}
	/**
	 * ページ情報の取得
	 *
	 * 注意)ページ情報(_page_info)部が空レコードの場合あり
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param array $row			取得データ
	 * @param string $langId		言語ID
	 * @return bool					true=正常、false=異常
	 */
	function getPageInfo($pageId, $pageSubId, &$row, $langId = '')
	{
		$queryStr = 'SELECT * FROM _page_info RIGHT JOIN _page_id ON pn_sub_id = pg_id AND pg_type = 1 AND pn_deleted = false AND pn_id = ? AND pn_language_id = ? ';// 2010/2/23更新
		$queryStr .=  'WHERE (pn_deleted IS NULL ';
		$queryStr .=    'AND pg_type = 1 AND pg_id = ?) ';		// サブページID
		$queryStr .=    'OR (pn_deleted = false ';
		$queryStr .=    'AND pn_sub_id = ?) ';		// サブページID
		$ret = $this->selectRecord($queryStr, array($pageId, $langId, $pageSubId, $pageSubId), $row);
		return $ret;
	}
	/**
	 * ページIDでページ情報を取得
	 *
	 * @param string $pageId		ページID
	 * @param string $langId		言語ID
	 * @param array $rows			取得データ
	 * @return bool					true=正常、false=異常
	 */
	function getPageInfoByPageId($pageId, $langId, &$rows)
	{
		$queryStr = 'SELECT * FROM _page_info RIGHT JOIN _page_id ON pn_sub_id = pg_id AND pg_type = 1 AND pn_deleted = false AND pn_id = ? AND pn_language_id = ? ';// 2010/2/23更新
		$queryStr .=  'WHERE (pn_deleted IS NULL ';
		$queryStr .=    'AND pg_type = 1) ';		// サブページID
		$queryStr .=    'OR pn_deleted = false ';
		$queryStr .=  'ORDER BY pg_priority';
		$ret = $this->selectRecords($queryStr, array($pageId, $langId), $rows);
		return $ret;
	}
	/**
	 * ページ情報を更新(ページ情報管理用)
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $contentType	コンテンツタイプ
	 * @param string $template		テンプレートID
	 * @param string $subTemplateId	サブテンプレートID
	 * @param int $authType			アクセス制御タイプ(0=管理者のみ、1=制限なし、2=ログインユーザ)
	 * @param bool $ssl				SSLを使用するかどうか
	 * @param bool $userLimited		ユーザ制限するかどうか
	 * @return					true = 正常、false=異常
	 */
	function updatePageInfo($pageId, $pageSubId, $contentType = '', $template = '', $subTemplateId = '', $authType = 0, $ssl = false, $userLimited = false)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$metaTitle = '';
		$metaDesc = '';
		$metaKeyword = '';
		
		// トランザクション開始
		$this->startTransaction();
		
		// コンテンツタイプが指定されている場合は、他のデータのコンテンツタイプをクリア
		if (!empty($contentType)){
			$queryStr  = 'UPDATE _page_info ';
			$queryStr .=   'SET pn_content_type = \'\', ';		// コンテンツタイプをクリア
			$queryStr .=     'pn_update_user_id = ?, ';
			$queryStr .=     'pn_update_dt = ? ';
			$queryStr .=   'WHERE pn_deleted = false ';
			$queryStr .=     'AND pn_id = ? ';					// ページID
			$queryStr .=     'AND pn_content_type = ? ';		// コンテンツタイプ
			$queryStr .=     'AND pn_language_id = ? ';		// 言語ID(2010/2/23追加)
			$this->execStatement($queryStr, array($user, $now, $pageId, $contentType, ''/*言語なし*/));
		}
		
		// 指定のレコードの履歴インデックス取得
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM _page_info ';
		$queryStr .=   'WHERE pn_id = ? ';
		$queryStr .=     'AND pn_sub_id = ? ';
		$queryStr .=     'AND pn_language_id = ? ';		// 言語ID(2010/2/23追加)
		$queryStr .=  'ORDER BY pn_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($pageId, $pageSubId, ''/*言語なし*/), $row);
		if ($ret){
			$historyIndex = $row['pn_history_index'] + 1;
			$metaTitle = $row['pn_meta_title'];
			$metaDesc = $row['pn_meta_description'];
			$metaKeyword = $row['pn_meta_keywords'];
		
			// レコードが削除されていない場合は削除
			if (!$row['pn_deleted']){
				// 古いレコードを削除
				$queryStr  = 'UPDATE _page_info ';
				$queryStr .=   'SET pn_deleted = true, ';	// 削除
				$queryStr .=     'pn_update_user_id = ?, ';
				$queryStr .=     'pn_update_dt = ? ';
				$queryStr .=   'WHERE pn_serial = ?';
				$ret = $this->execStatement($queryStr, array($user, $now, $row['pn_serial']));
				if (!$ret){
					$this->endTransaction();
					return false;
				}
			}
		}
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO _page_info ';
		$queryStr .=  '(';
		$queryStr .=  'pn_id, ';
		$queryStr .=  'pn_sub_id, ';
		$queryStr .=  'pn_language_id, ';// 言語ID(2010/2/23追加)
		$queryStr .=  'pn_history_index, ';
		$queryStr .=  'pn_template_id, ';
		$queryStr .=  'pn_sub_template_id, ';
		$queryStr .=  'pn_meta_title, ';
		$queryStr .=  'pn_meta_description, ';
		$queryStr .=  'pn_meta_keywords, ';
		$queryStr .=  'pn_content_type, ';
		$queryStr .=  'pn_auth_type, ';
		$queryStr .=  'pn_use_ssl, ';
		$queryStr .=  'pn_user_limited, ';
		$queryStr .=  'pn_create_user_id, ';
		$queryStr .=  'pn_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($pageId, $pageSubId, ''/*言語なし*/, $historyIndex, $template, $subTemplateId, $metaTitle, $metaDesc, $metaKeyword, $contentType, $authType, intval($ssl), intval($userLimited), $user, $now));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ページヘッダ情報を更新
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $langId		言語ID
	 * @param string $metaTitle		METAタグ、タイトル
	 * @param string $metaDesc		METAタグ、ページ要約
	 * @param string $metaKeyword	METAタグ、検索用キーワード
	 * @param string $headOthers	HEADタグ、その他
	 * @return					true = 正常、false=異常
	 */
	function updatePageHead($pageId, $pageSubId, $langId, $metaTitle='', $metaDesc='', $metaKeyword='', $headOthers='')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$template = '';			// テンプレートID
		$contentType = '';
		$authType = 0;
		$ssl = false;
		$userLimited = false;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のレコードの履歴インデックス取得
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM _page_info ';
		$queryStr .=   'WHERE pn_id = ? ';
		$queryStr .=     'AND pn_sub_id = ? ';
		$queryStr .=     'AND pn_language_id = ? ';		// 言語ID(2010/2/23追加)
		$queryStr .=  'ORDER BY pn_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($pageId, $pageSubId, $langId), $row);
		if ($ret){
			$historyIndex = $row['pn_history_index'] + 1;
			$template = $row['pn_template_id'];			// テンプレートID
			$contentType = $row['pn_content_type'];
			$authType = $row['pn_auth_type'];
			$ssl = $row['pn_use_ssl'];
			$userLimited = $row['pn_user_limited'];
			
			// レコードが削除されていない場合は削除
			if (!$row['pn_deleted']){
				// 古いレコードを削除
				$queryStr  = 'UPDATE _page_info ';
				$queryStr .=   'SET pn_deleted = true, ';	// 削除
				$queryStr .=     'pn_update_user_id = ?, ';
				$queryStr .=     'pn_update_dt = ? ';
				$queryStr .=   'WHERE pn_serial = ?';
				$ret = $this->execStatement($queryStr, array($user, $now, $row['pn_serial']));
				if (!$ret){
					$this->endTransaction();
					return false;
				}
			}
		}
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO _page_info ';
		$queryStr .=  '(';
		$queryStr .=  'pn_id, ';
		$queryStr .=  'pn_sub_id, ';
		$queryStr .=  'pn_language_id, ';// 言語ID(2010/2/23追加)
		$queryStr .=  'pn_history_index, ';
		$queryStr .=  'pn_template_id, ';
		$queryStr .=  'pn_meta_title, ';
		$queryStr .=  'pn_meta_description, ';
		$queryStr .=  'pn_meta_keywords, ';
		$queryStr .=  'pn_head_others, ';
		$queryStr .=  'pn_content_type, ';
		$queryStr .=  'pn_auth_type, ';
		$queryStr .=  'pn_use_ssl, ';
		$queryStr .=  'pn_user_limited, ';
		$queryStr .=  'pn_create_user_id, ';
		$queryStr .=  'pn_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($pageId, $pageSubId, $langId, $historyIndex, $template, $metaTitle, $metaDesc, $metaKeyword, $headOthers, $contentType, $authType, intval($ssl), intval($userLimited), $user, $now));
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ページIDの更新
	 *
	 * @param int $type				ページタイプ(0=ページID,1=ページサブID)
	 * @param string  $id			ID
	 * @param string  $name			名前
	 * @param string  $desc			説明
	 * @param int  $priority		優先度
	 * @param bool  $active			有効かどうか
	 * @param bool  $visible		公開ページかどうか
	 * @return						true=成功、false=失敗
	 */
	function updatePageId($type, $id, $name, $desc, $priority, $active, $visible = null)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$ret = $this->isExistsPageId($type, $id);
		if ($ret){		// データが存在する場合
			// 既存値を取得
			$ret = $this->getPageIdRecord($type, $id, $row);
			if ($ret){
				if (is_null($visible)) $visible = $row['pg_visible'];
			}
			
			// 既存項目を更新
			$queryStr  = 'UPDATE _page_id ';
			$queryStr .=   'SET ';
			$queryStr .=     'pg_name = ?, ';
			$queryStr .=     'pg_description = ?, ';
			$queryStr .=     'pg_priority = ?, ';
			$queryStr .=     'pg_active = ?, ';
			$queryStr .=     'pg_visible = ?, ';
			$queryStr .=     'pg_function_type = ? ';			// システム用機能タイプ
			$queryStr .=   'WHERE pg_id = ? ';
			$queryStr .=     'AND pg_type = ? ';
			$this->execStatement($queryStr, array($name, $desc, $priority, intval($active), intval($visible), $row['pg_function_type'], $id, $type));
		} else {
			if (is_null($visible)) $visible = true;
			
			// 新規レコードを追加
			$queryStr  = 'INSERT INTO _page_id (';
			$queryStr .=   'pg_id, ';
			$queryStr .=   'pg_type, ';
			$queryStr .=   'pg_name, ';
			$queryStr .=   'pg_description, ';
			$queryStr .=   'pg_priority, ';
			$queryStr .=   'pg_active, ';
			$queryStr .=   'pg_visible ';
			$queryStr .= ') VALUES (';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?) ';
			$this->execStatement($queryStr, array($id, $type, $name, $desc, $priority, intval($active), intval($visible)));
		}

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ページIDの有効状態のみ変更
	 *
	 * @param int $type				ページタイプ(0=ページID,1=ページサブID)
	 * @param string  $id			ID
	 * @param bool  $active			有効かどうか
	 * @return						true=成功、false=失敗
	 */
	function updatePageIdActive($type, $id, $active)
	{
		// 既存値を取得
		$ret = $this->getPageIdRecord($type, $id, $row);
		if ($ret){
			// トランザクション開始
			$this->startTransaction();
		
			// 既存項目を更新
			$queryStr  = 'UPDATE _page_id ';
			$queryStr .=   'SET ';
			$queryStr .=     'pg_name = ?, ';
			$queryStr .=     'pg_description = ?, ';
			$queryStr .=     'pg_priority = ?, ';
			$queryStr .=     'pg_active = ?, ';
			$queryStr .=     'pg_visible = ?, ';
			$queryStr .=     'pg_function_type = ? ';			// システム用機能タイプ
			$queryStr .=   'WHERE pg_id = ? ';
			$queryStr .=     'AND pg_type = ? ';
			$this->execStatement($queryStr, array($row['pg_name'], $row['pg_description'], $row['pg_priority'], intval($active), intval($row['pg_visible']), $row['pg_function_type'], $id, $type));
			
			// トランザクション確定
			$ret = $this->endTransaction();
			return $ret;
		} else {
			return false;
		}
	}
	/**
	 * ページIDの公開状態のみ変更
	 *
	 * @param int $type				ページタイプ(0=ページID,1=ページサブID)
	 * @param string  $id			ID
	 * @param bool  $visible		公開かどうか
	 * @return						true=成功、false=失敗
	 */
	function updatePageIdVisible($type, $id, $visible)
	{
		// 既存値を取得
		$ret = $this->getPageIdRecord($type, $id, $row);
		if ($ret){
			// トランザクション開始
			$this->startTransaction();
		
			// 既存項目を更新
			$queryStr  = 'UPDATE _page_id ';
			$queryStr .=   'SET ';
			$queryStr .=     'pg_name = ?, ';
			$queryStr .=     'pg_description = ?, ';
			$queryStr .=     'pg_priority = ?, ';
			$queryStr .=     'pg_active = ?, ';
			$queryStr .=     'pg_visible = ?, ';
			$queryStr .=     'pg_function_type = ? ';			// システム用機能タイプ
			$queryStr .=   'WHERE pg_id = ? ';
			$queryStr .=     'AND pg_type = ? ';
			$this->execStatement($queryStr, array($row['pg_name'], $row['pg_description'], $row['pg_priority'], intval($row['pg_active']), intval($visible), $row['pg_function_type'], $id, $type));
			
			// トランザクション確定
			$ret = $this->endTransaction();
			return $ret;
		} else {
			return false;
		}
	}
	/**
	 * ページIDの存在チェック
	 *
	 * @param int $type				リストの種別
	 * @param string  $id			ID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsPageId($type, $id)
	{
		$queryStr = 'SELECT * FROM _page_id ';
		$queryStr .=  'WHERE pg_type = ? ';
		$queryStr .=  'AND pg_id = ? ';
		return $this->isRecordExists($queryStr, array($type, $id));
	}
	/**
	 * ページIDの削除
	 *
	 * @param int $type				リストの種別
	 * @param string  $id			ID
	 * @return bool					true=存在する、false=存在しない
	 */
	function deletePageId($type, $id)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr = 'DELETE FROM _page_id ';
		$queryStr .=  'WHERE pg_type = ? ';
		$queryStr .=  'AND pg_id = ? ';
		$this->execStatement($queryStr, array($type, $id));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ページポジションのリストを取得
	 *
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getPagePositionList($callback)
	{
		$queryStr = 'select * from _template_position ';
		$queryStr .=  'order by tp_sort_order';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * ページ定義のリスト取得
	 *
	 * @param function $callback	コールバック関数
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $position		表示位置。空文字列のときはすべて取得。
	 * @param int    $setId			定義セットID
	 * @return						なし
	 */
	function getPageDefList($callback, $pageId, $pageSubId, $position = '', $setId = 0)
	{
		$queryStr  = 'select * from _page_def left join _widgets on pd_widget_id = wd_id and wd_deleted = false ';
		$queryStr .=   'where pd_id = ? ';
		$queryStr .=     'and (pd_sub_id = ? or pd_sub_id = \'\') ';	// 空の場合は共通項目
		$queryStr .=     'and pd_set_id = ? ';
		if (empty($position)){
			$queryStr .=   'order by pd_position_id, pd_index';
			$this->selectLoop($queryStr, array($pageId, $pageSubId, $setId), $callback);
		} else {
			$queryStr .=     'and pd_position_id = ? ';
			$queryStr .=   'order by pd_position_id, pd_index';
			$this->selectLoop($queryStr, array($pageId, $pageSubId, $setId, $position), $callback);
		}
	}
	/**
	 * ページ定義があるか確認
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $position	表示位置
	 * @param int $index		表示インデックス
	 * @param int    $setId			定義セットID
	 * @return bool				true=存在する、false=存在しない
	 */
	 /*
	function isPageDefExists($pageId, $pageSubId, $position, $index, $setId = 0)
	{
		$queryStr  = 'select * from _page_def ';
		$queryStr .=   'where pd_id = ? ';
		$queryStr .=     'and pd_sub_id = ? ';
		$queryStr .=     'and pd_position_id = ? ';
		$queryStr .=     'and pd_index = ? ';
		return $this->isRecordExists($queryStr, array($pageId, $pageSubId, $position, $index));
	}*/
	/**
	 * ページ定義を取得
	 *
	 * @param int $serialNo			シリアルNo
	 * @param array  $rows			更新データ
	 * @return bool				true=存在する、false=存在しない
	 */
	function getPageDef($serialNo, &$row)
	{
		$queryStr  = 'SELECT * FROM _page_def LEFT JOIN _widgets on pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'WHERE pd_serial = ?';
		return $this->selectRecord($queryStr, array($serialNo), $row);
	}
	/**
	 * ページ定義項目の更新
	 *
	 * @param int $serialNo			シリアルNo(0のとき新規追加)
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $position		表示位置
	 * @param int $index			表示インデックスNo
	 * @param string $widgetId		ウィジェットID
	 * @param int $configId			定義ID
	 * @param string $suffix		サフィックス
	 * @param string $style			css
	 * @param bool $visible			表示状態
	 * @param int    $setId			定義セットID
	 * @return						true=成功、false=失敗
	 */
	function updatePageDef($serialNo, $pageId, $pageSubId, $position, $index, $widgetId, $configId, $suffix, $style, $visible, $setId = 0)
	{
		// 更新ユーザ、日時設定
		$this->now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$editable = 1;		// 編集可能
		
		// トランザクション開始
		$this->startTransaction();

		if ($serialNo == 0){		// 新規追加
			// 新規データを追加
			$queryStr  = 'INSERT INTO _page_def (';
			$queryStr .=   'pd_id, ';
			$queryStr .=   'pd_sub_id, ';
			$queryStr .=   'pd_set_id, ';
			$queryStr .=   'pd_position_id, ';
			$queryStr .=   'pd_index, ';
			$queryStr .=   'pd_widget_id, ';
			$queryStr .=   'pd_config_id, ';
			$queryStr .=   'pd_suffix, ';
			$queryStr .=   'pd_style, ';
			$queryStr .=   'pd_visible, ';
			$queryStr .=   'pd_editable, ';
			$queryStr .=   'pd_update_user_id, ';
			$queryStr .=   'pd_update_dt) ';
			$queryStr .= 'VALUES (';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?, ';
			$queryStr .=   '?)';
			$this->execStatement($queryStr, array($pageId, $pageSubId, $setId, $position, $index, 
								$widgetId, $configId, $suffix, $style, $visible, $editable, $userId, $this->now));
		} else {			// 更新
			$queryStr  = 'select * from _page_def ';
			$queryStr .=   'where pd_serial = ? ';
			$ret = $this->selectRecord($queryStr, array($serialNo), $row);
			if ($ret){
				if ($row['pd_id'] != $pageId || $row['pd_sub_id'] != $pageSubId){			// 表示ページが変更された
					// 新規に追加して、古いレコードを削除
					// 新規データを追加
					$queryStr  = 'INSERT INTO _page_def (';
					$queryStr .=   'pd_id, ';
					$queryStr .=   'pd_sub_id, ';
					$queryStr .=   'pd_set_id, ';
					$queryStr .=   'pd_position_id, ';
					$queryStr .=   'pd_index, ';
					$queryStr .=   'pd_widget_id, ';
					$queryStr .=   'pd_config_id, ';
					$queryStr .=   'pd_suffix, ';
					$queryStr .=   'pd_style, ';
					$queryStr .=   'pd_visible, ';
					$queryStr .=   'pd_editable, ';
					$queryStr .=   'pd_update_user_id, ';
					$queryStr .=   'pd_update_dt) ';
					$queryStr .= 'VALUES (';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?, ';
					$queryStr .=   '?)';
					$this->execStatement($queryStr, array($pageId, $pageSubId, $row['pd_set_id'], $position, $index, 
										$widgetId, $configId, $suffix, $style, $visible, $editable, $userId, $this->now));
										
					// 旧データ削除
					$queryStr  = 'DELETE FROM _page_def WHERE pd_serial = ?';
					$this->execStatement($queryStr, array($serialNo));
				} else {
					// 既存項目を更新
					$queryStr  = 'UPDATE _page_def ';
					$queryStr .=   'SET ';
					$queryStr .=     'pd_position_id = ?, ';
					$queryStr .=     'pd_index = ?, ';
					$queryStr .=     'pd_widget_id = ?, ';
					$queryStr .=     'pd_config_id = ?, ';
					$queryStr .=     'pd_suffix = ?, ';
					$queryStr .=     'pd_style = ?, ';
					$queryStr .=     'pd_visible = ?, ';
					$queryStr .=     'pd_editable = ?, ';
					$queryStr .=     'pd_update_user_id = ?, ';
					$queryStr .=     'pd_update_dt = ? ';
					$queryStr .=   'WHERE pd_serial = ? ';
					$this->execStatement($queryStr, array($position, $index, 
										$widgetId, $configId, $suffix, $style, $visible, $editable, $userId, $this->now, $serialNo));
				}
			}
		}

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ページ定義項目のスタイル値の変更
	 *
	 * @param int $serialNo			シリアルNo
	 * @param string $style			スタイル値
	 * @param string $title			タイトル
	 * @param bool $titleVisible	タイトルを表示するかどうか
	 * @param bool $useRender		Joomla!の描画処理を使用するかどうか
	 * @param string $topContent	補助コンテンツ(上)
	 * @param string $bottomContent	補助コンテンツ(下)
	 * @param bool $showReadmore	もっと読むボタンを表示するかどうか
	 * @param string $readmoreTitle	もっと読むボタンタイトル
	 * @param string $readmoreUrl	もっと読むリンク先URL
	 * @param string $serializedParam	その他のパラメータ
	 * @param string $exportCss		外部出力用CSS
	 * @return						true=成功、false=失敗
	 */
/*	function updatePageDefInfo($serialNo, $style, $title, $titleVisible, $useRender, $topContent, $bottomContent, $showReadmore, $readmoreTitle, $readmoreUrl, $serializedParam, $exportCss)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ

		// トランザクション開始
		$this->startTransaction();
		
		// 既存項目を更新
		$queryStr  = 'UPDATE _page_def ';
		$queryStr .=   'SET ';
		$queryStr .=     'pd_style = ?, ';
		$queryStr .=     'pd_title = ?, ';
		$queryStr .=     'pd_title_visible = ?, ';
		$queryStr .=     'pd_use_render = ?, ';
		$queryStr .=     'pd_top_content = ?, ';
		$queryStr .=     'pd_bottom_content = ?, ';
		$queryStr .=     'pd_show_readmore = ?, ';
		$queryStr .=     'pd_readmore_title = ?, ';
		$queryStr .=     'pd_readmore_url = ?, ';
		$queryStr .=     'pd_param = ?, ';
		$queryStr .=     'pd_css = ?, ';
		$queryStr .=     'pd_update_user_id = ?, ';
		$queryStr .=     'pd_update_dt = ? ';
		$queryStr .=   'WHERE pd_serial = ? ';
		$ret = $this->execStatement($queryStr, array($style, $title, intval($titleVisible), intval($useRender), $topContent, $bottomContent, intval($showReadmore), $readmoreTitle, $readmoreUrl, $serializedParam, $exportCss, $user, $now, $serialNo));

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}*/
	/**
	 * ページ定義レコードを更新
	 *
	 * @param int $serialNo			シリアルNo
	 * @param array $updateData		更新データ(キー=フィールド名、値=更新値の配列)
	 * @return						true=成功、false=失敗
	 */
	function updatePageDefRecord($serialNo, $updateData)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$user = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$param = array();
		$keys = array_keys($updateData);// キーを取得
		
		// トランザクション開始
		$this->startTransaction();
		
		// 既存項目を更新
		$queryStr  = 'UPDATE _page_def ';
		$queryStr .=   'SET ';
		for ($i = 0; $i < count($keys); $i++){
			$queryStr .= $keys[$i] . ' = ?, ';
			$param[] = $updateData[$keys[$i]];
		}
		$queryStr .=     'pd_update_user_id = ?, ';
		$queryStr .=     'pd_update_dt = ? ';
		$queryStr .=   'WHERE pd_serial = ? ';
		$ret = $this->execStatement($queryStr, array_merge($param, array($user, $now, $serialNo)));

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットの共通属性を変更
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param int $serial		シリアル番号
	 * @param int    $shared	共通属性
	 * @return bool				true=成功、false=失敗
	 */
	function toggleSharedWidget($pageId, $pageSubId, $serial, $shared)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 現在の値取得
		$queryStr  = 'SELECT * FROM _page_def ';
		$queryStr .=   'WHERE pd_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){
			if (empty($shared)){		// 単独ウィジェットのとき
				$newPageSubId = $pageSubId;
			} else {
				$newPageSubId = '';
			}
			
			// 既存項目を更新
			$queryStr  = 'UPDATE _page_def ';
			$queryStr .=   'SET ';
			$queryStr .=     'pd_sub_id = ?, ';
			$queryStr .=     'pd_update_user_id = ?, ';
			$queryStr .=     'pd_update_dt = ? ';
			$queryStr .=   'WHERE pd_serial = ? ';
			$this->execStatement($queryStr, array($newPageSubId, $userId, $now, $serial));
		} else {
			$this->endTransaction();
			return false;
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ページ定義項目の削除
	 *
	 * @param int $serialNo			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delPageDef($serialNo)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'DELETE FROM _page_def WHERE pd_serial = ?';
		$this->execStatement($queryStr, array($serialNo));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ウィジェットIDでページ定義項目の削除
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param int    $setId				定義セットID
	 * @return						true=成功、false=失敗
	 */
	function delPageDefByWidgetId($widgetId, $setId = 0)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'DELETE FROM _page_def ';
		$queryStr .= 'WHERE pd_widget_id = ? ';
		$queryStr .=   'AND pd_set_id = ? ';
		$this->execStatement($queryStr, array($widgetId, $setId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ページ定義項目をすべて削除
	 *
	 * @param string  $pageId			ページID
	 * @param string  $pageSubId		ページサブID
	 * @param string  $position			表示ポジション
	 * @param bool $withCommon			共通項目も削除するかどうか
	 * @param int    $setId			定義セットID
	 * @return						true=成功、false=失敗
	 */
	function delPageDefAll($pageId, $pageSubId, $position, $withCommon, $setId = 0)
	{
		// トランザクション開始
		$this->startTransaction();
		
		if (empty($position)){
			$queryStr  = 'DELETE FROM _page_def ';
			$queryStr .=   'WHERE pd_id = ? ';
			if ($withCommon){
				$queryStr .=     'and (pd_sub_id = ? or pd_sub_id = \'\') ';	// 空の場合は共通項目
			} else {
				$queryStr .=     'and pd_sub_id = ? ';	// 空の場合は共通項目
			}
			$queryStr .=     'and pd_set_id = ? ';
			$this->execStatement($queryStr, array($pageId, $pageSubId, $setId));
		} else {
			$queryStr  = 'DELETE FROM _page_def ';
			$queryStr .=   'WHERE pd_id = ? ';
			if ($withCommon){
				$queryStr .=     'and (pd_sub_id = ? or pd_sub_id = \'\') ';	// 空の場合は共通項目
			} else {
				$queryStr .=     'and pd_sub_id = ? ';	// 空の場合は共通項目
			}
			$queryStr .=     'and pd_position_id = ? ';
			$queryStr .=     'and pd_set_id = ? ';
			$this->execStatement($queryStr, array($pageId, $pageSubId, $position, $setId));
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 指定したページ上の共通以外のページ定義項目をすべて削除
	 *
	 * @param string  $pageSubId		ページサブID
	 * @return						true=成功、false=失敗
	 */
	function delPageDefAllNonCommon($pageSubId)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// フロント画面のアクセスポイントを取得
		$queryStr  = 'SELECT * FROM _page_id ';
		$queryStr .=   'WHERE pg_type = 0 ';
		$queryStr .=     'AND pg_frontend = true';
		$ret = $this->selectRecords($queryStr, array(), $rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$pageId = $rows[$i]['pg_id'];
				$queryStr  = 'DELETE FROM _page_def ';
				$queryStr .=   'WHERE pd_id = ? ';
				$queryStr .=     'AND pd_sub_id = ? ';
				$this->execStatement($queryStr, array($pageId, $pageSubId));
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ユーザリスト取得
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllUserList($limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$queryStr = 'SELECT * FROM _login_user LEFT JOIN _login_log on lu_id = ll_user_id ';
		$queryStr .=  'WHERE lu_deleted = false ';// 削除されていない
		$queryStr .=  'ORDER BY lu_user_type, lu_account limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * ユーザ総数取得
	 *
	 * @return int					総数
	 */
	function getAllUserListCount()
	{
		$queryStr = 'select * from _login_user ';
		$queryStr .=  'where lu_deleted = false ';// 削除されていない
		return $this->selectRecordCount($queryStr, array());
	}
	/**
	 * ログイン状況取得
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getUserLoginStatusList($limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$queryStr = 'SELECT lu_id,lu_account,lu_name,lu_user_type,lu_user_status,ll_login_count,ll_access_log_serial,ll_pre_login_dt,ll_last_login_dt, ';
		$queryStr .=    'CASE WHEN ll_last_login_dt IS NULL THEN 1 ELSE 0 ';
		$queryStr .=    'END AS ord ';
		$queryStr .=    'FROM _login_user LEFT JOIN _login_log on lu_id = ll_user_id ';
		$queryStr .=  'WHERE lu_deleted = false ';// 削除されていない
		$queryStr .=  'ORDER BY ord, ll_last_login_dt desc limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * ログイン状況数取得
	 *
	 * @return int					総数
	 */
	function getUserLoginStatusListCount()
	{
		$queryStr = 'SELECT * FROM _login_user LEFT JOIN _login_log on lu_id = ll_user_id ';
		$queryStr .=  'WHERE lu_deleted = false ';// 削除されていない
		return $this->selectRecordCount($queryStr, array());
	}
	/**
	 * ユーザの削除
	 *
	 * @param array $serial			シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delUserBySerial($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM _login_user ';
			$queryStr .=   'WHERE lu_deleted = false ';		// 未削除
			$queryStr .=     'AND lu_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE _login_user ';
		$queryStr .=   'SET lu_deleted = true, ';	// 削除
		$queryStr .=     'lu_update_user_id = ?, ';
		$queryStr .=     'lu_update_dt = ? ';
		$queryStr .=   'WHERE lu_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ユーザ情報をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @param array     $groupRows			ユーザグループ
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getUserBySerial($serial, &$row, &$groupRows)
	{
		$queryStr  = 'SELECT * FROM _login_user ';
		$queryStr .=   'WHERE lu_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		
		// ユーザグループを取得
		if ($ret){
			$queryStr  = 'SELECT * FROM _user_with_group LEFT JOIN _user_group ON uw_group_id = ug_id AND ug_deleted = false ';
			$queryStr .=   'WHERE uw_user_serial = ? ';
			$queryStr .=  'ORDER BY uw_index ';
			$this->selectRecords($queryStr, array($serial), $groupRows);
		}
		return $ret;
	}
	/**
	 * ユーザ情報をユーザIDで取得
	 *
	 * @param string	$id			ユーザID
	 * @param array     $row		レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getUserById($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _login_user ';
		$queryStr .=   'WHERE lu_id = ? ';
		$queryStr .=     'AND lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * ユーザ情報をアカウントで取得
	 *
	 * @param string	$account			アカウント
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getUserByAccount($account, &$row)
	{
		$queryStr  = 'SELECT * FROM _login_user ';
		$queryStr .=   'WHERE lu_account = ? ';
		$queryStr .=    'AND lu_deleted = false';
		$ret = $this->selectRecord($queryStr, array($account), $row);
		return $ret;
	}
	/**
	 * テンプレート情報の取得
	 *
	 * @param string  $id			テンプレートID
	 * @return						true=正常、false=異常
	 */
	function getTemplate($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _templates ';
		$queryStr .=   'WHERE tm_id = ? ';
		$queryStr .=   'AND tm_deleted = false';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * テンプレートリスト取得
	 *
	 * @param int      $type			テンプレートのタイプ(0=PC用、1=携帯用、2=スマートフォン)
	 * @param function $callback		コールバック関数
	 * @param bool     $availableOnly	利用可能なテンプレートに制限するかどうか
	 * @return							なし
	 */
	function getAllTemplateList($type, $callback, $availableOnly = true)
	{
		// tm_device_typeは後で追加したため、tm_mobileを残しておく
		$queryStr = 'SELECT * FROM _templates ';
		$queryStr .=  'WHERE tm_deleted = false ';// 削除されていない
		if ($availableOnly) $queryStr .=    'AND tm_available = true ';		// 利用可能
		$params = array();
		switch ($type){
			case 0:		// PC用テンプレート
			case 2:		// スマートフォン用テンプレート
			default:
				$queryStr .=    'AND tm_mobile = false ';		// 携帯用以外
				$queryStr .=    'AND tm_device_type = ? '; $params[] = $type;
				break;
			case 1:		// 携帯用のとき
				$queryStr .=    'AND tm_mobile = true ';		// 携帯用
				break;
		}
		$queryStr .=  'ORDER BY tm_id';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * テンプレートIDリスト取得
	 *
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getAllTemplateIdList(&$rows)
	{
		$queryStr = 'SELECT * FROM _templates ';
		$queryStr .=  'WHERE tm_deleted = false ';// 削除されていない
		$queryStr .=  'ORDER BY tm_id';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * 携帯用テンプレートIDリスト取得
	 *
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getAllMobileTemplateIdList(&$rows)
	{
		$queryStr = 'SELECT * FROM _templates ';
		$queryStr .=  'WHERE tm_deleted = false ';// 削除されていない
		$queryStr .=  'AND tm_mobile = true ';// 携帯
		$queryStr .=  'ORDER BY tm_id';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * テンプレートの追加
	 *
	 * @param string  $id			テンプレートID
	 * @param string  $name			テンプレート名
	 * @param int     $type			テンプレートのタイプ(1=Joomla!v1.5テンプレート,2=Joomla!v2.5テンプレート,10=Bootstrap v3.0テンプレート)
	 * @param int     $deviceType	端末タイプ(0=PC用、1=携帯用、2=スマートフォン)
	 * @param int     $cleanType	クリーン処理タイプ
	 * @param string  $generator	テンプレート作成アプリケーション
	 * @param string  $version		テンプレートバージョン
	 * @return						なし
	 */
	function addNewTemplate($id, $name, $type, $deviceType = 0, $cleanType = 0, $generator = '', $version = '')
	{
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		$now = date("Y/m/d H:i:s");	// 現在日時
		$historyIndex = 0;
		$mobile = 0;				// 携帯端末かどうか
		if ($deviceType == 1) $mobile = 1;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 同じIDが登録済みかどうかチェック
		$queryStr = 'SELECT * FROM _templates ';
		$queryStr .=  'WHERE tm_id = ? ';
		$queryStr .=  'ORDER BY tm_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			if (!$row['tm_deleted']){		// レコードが削除されていなければ、削除
				// 古いレコードを削除
				$queryStr  = 'UPDATE _templates ';
				$queryStr .=   'SET tm_deleted = true, ';	// 削除
				$queryStr .=     'tm_update_user_id = ?, ';
				$queryStr .=     'tm_update_dt = ? ';
				$queryStr .=   'WHERE tm_serial = ?';
				$this->execStatement($queryStr, array($userId, $now, $row['tm_serial']));			
			}
			$historyIndex = $row['tm_history_index'] + 1;
		}
		// Bootstrapを使用するかどうか
		$useBootstrap = false;
		if ($type >= 10) $useBootstrap = true;
		
		$queryStr = 'INSERT INTO _templates ';
		$queryStr .=  '(tm_id, tm_history_index, tm_name, tm_type, tm_device_type, tm_mobile, tm_clean_type, tm_use_bootstrap, tm_generator, tm_version, tm_create_dt, tm_create_user_id) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $historyIndex, $name, $type, $deviceType, $mobile, $cleanType, intval($useBootstrap), $generator, $version, $now, $userId));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * テンプレートの更新
	 *
	 * @param string $templateId	テンプレートID
	 * @param string  $name			ウィジェット名
	 * @return						true=成功、false=失敗
	 */
	function updateTemplate($templateId, $name)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		$this->startTransaction();
		
		// 既存データを取得
		$historyIndex = 0;		// 履歴番号
		$queryStr = 'SELECT * FROM _templates ';
		$queryStr .=  'WHERE tm_id = ? ';
		$queryStr .=  'ORDER BY tm_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($templateId), $row);
		if ($ret){
			if ($row['tm_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			} else {		// レコードが削除されていなければ、削除
				// 古いレコードを削除
				$queryStr  = 'UPDATE _templates ';
				$queryStr .=   'SET tm_deleted = true, ';	// 削除
				$queryStr .=     'tm_update_user_id = ?, ';
				$queryStr .=     'tm_update_dt = ? ';
				$queryStr .=   'WHERE tm_serial = ?';
				$this->execStatement($queryStr, array($userId, $now, $row['tm_serial']));			
			}
			$historyIndex = $row['tm_history_index'] + 1;
		} else {
			$this->endTransaction();
			return false;
		}
		
		// 新規レコード追加
		$queryStr  = 'INSERT INTO _templates (';
		$queryStr .=   'tm_id, ';
		$queryStr .=   'tm_history_index, ';
		$queryStr .=   'tm_name, ';
		$queryStr .=   'tm_type, ';
		$queryStr .=   'tm_device_type, ';
		$queryStr .=   'tm_mobile, ';
		$queryStr .=   'tm_clean_type, ';
		$queryStr .=   'tm_available, ';
		$queryStr .=   'tm_create_user_id, ';
		$queryStr .=   'tm_create_dt ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?)';
		$this->execStatement($queryStr, array($row['tm_id'], $historyIndex, $name, $row['tm_type'], intval($row['tm_device_type']), intval($row['tm_mobile']), $row['tm_clean_type'], intval($row['tm_available']), $userId, $now));

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * テンプレートの削除
	 *
	 * @param string $templateId	テンプレートID
	 * @return						true=成功、false=失敗
	 */
	function deleteTemplate($templateId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 既存データを取得
		$queryStr = 'SELECT * FROM _templates ';
		$queryStr .=  'WHERE tm_deleted = false ';
		$queryStr .=  'AND tm_id = ? ';
		$ret = $this->selectRecord($queryStr, array($templateId), $row);
		if (!$ret){
			$this->endTransaction();
			return false;
		}
		// レコードを削除
		$queryStr  = 'UPDATE _templates ';
		$queryStr .=   'SET tm_deleted = true, ';	// 削除
		$queryStr .=     'tm_update_user_id = ?, ';
		$queryStr .=     'tm_update_dt = ? ';
		$queryStr .=   'WHERE tm_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $row['tm_serial']));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;		
	}
	/**
	 * 言語状態を更新
	 *
	 * @param string $id		言語ID
	 * @param bool	 $available	利用可
	 * @return					true = 正常、false=異常
	 */
	function updateLangStatus($id, $available)
	{
		// トランザクションスタート
		$this->startTransaction();

		$queryStr  = 'UPDATE _language ';
		$queryStr .=   'SET ln_available = ? ';
		$queryStr .=   'WHERE ln_id = ?';
		$ret = $this->execStatement($queryStr, array(intval($available), $id));

		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * すべての言語を取得
	 *
	 * @param function	$callback			コールバック関数
	 * @return			true=取得、false=取得せず
	 */
	function getAllLang($callback)
	{
		$queryStr = 'SELECT * FROM _language ORDER BY ln_priority';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * 指定言語を取得
	 *
	 * @param array		$langArray	取得言語のID
	 * @param function	$callback	コールバック関数
	 * @return			true=取得、false=取得せず
	 */
	function getLangs($langArray, $callback)
	{
		$id = '';
		for ($i = 0; $i < count($langArray); $i++){
			$id .= '\'' . addslashes($langArray[$i]) . '\',';
		}
		$id = rtrim($id, ',');
		$queryStr  = 'SELECT * FROM _language ';
		$queryStr .=   'WHERE ln_id in (' . $id . ') ';
		$queryStr .= 'ORDER BY ln_priority';
		$this->selectLoop($queryStr, array(), $callback, null);
	}
	/**
	 * 利用可能な言語を取得
	 *
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getAvailableLang(&$rows)
	{
		$queryStr  = 'SELECT * FROM _language ';
		$queryStr .=   'WHERE ln_available = true ';
		$queryStr .=   'ORDER BY ln_priority';
		$retValue = $this->selectRecords($queryStr, array(), $rows);
		return $retValue;
	}
	/**
	 * メニュー項目の表示状態を更新
	 *
	 * @param string $groupId	更新対象グループID
	 * @param bool $visible		表示状態
	 * @return					true = 正常、false=異常
	 */
	function updateMenuVisible($groupId, $visible)
	{
		$sql = "UPDATE _nav_item SET ni_visible = ? WHERE ni_group_id = ?";
		$params = array($visible, $groupId);
		return $this->execStatement($sql, $params);
	}
	/**
	 * トップ画面表示項目を取得
	 *
	 * @param string $navId			ナビゲーションバー識別ID
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	/*function getTopPageItems($navId, &$rows)
	{
		$queryStr  = 'SELECT * FROM _nav_item ';
		$queryStr .=   'WHERE ni_nav_id = ? ';
		$queryStr .=     'AND ni_top_page_index != 0 ';		// 0以外を表示
		$queryStr .=     'AND ni_visible = true ';
		$queryStr .=   'ORDER BY ni_top_page_index';
		$retValue = $this->selectRecords($queryStr, array($navId), $rows);
		return $retValue;
	}*/
	/**
	 * ナビゲーションバー項目を取得
	 *
	 * @param string $navId			ナビゲーションバー識別ID
	 * @param string $parentId		親項目ID
	 * @param function	$callback	コールバック関数
	 * @return 			なし
	 */
	function getNavItemsByLoop($navId, $parentId, $callback)
	{
		$queryStr  = 'SELECT * FROM _nav_item ';
		$queryStr .=   'WHERE ni_nav_id = ? ';
		$queryStr .=     'AND ni_parent_id = ? ';
		$queryStr .=     'AND ni_visible = true ';
		$queryStr .=   'ORDER BY ni_index';
		$this->selectLoop($queryStr, array($navId, $parentId), $callback);
	}
	/**
	 * ナビゲーションバー項目を取得
	 *
	 * @param string $navId			ナビゲーションバー識別ID
	 * @param string $parentId		親項目ID
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getNavItems($navId, $parentId, &$rows)
	{
		$queryStr  = 'SELECT * FROM _nav_item ';
		$queryStr .=   'WHERE ni_nav_id = ? ';
		$queryStr .=     'AND ni_parent_id = ? ';
		$queryStr .=     'AND ni_visible = true ';
		$queryStr .=   'ORDER BY ni_index';
		
		$retValue = $this->selectRecords($queryStr, array($navId, $parentId), $rows);
		return $retValue;
	}
	/**
	 * ナビゲーションバー項目をすべて取得
	 *
	 * @param string 	$navId			ナビゲーションバー識別ID
	 * @param function	$callback		コールバック関数
	 * @return 			なし
	 */
	function getNavItemsAll($navId, $callback)
	{
		$queryStr  = 'SELECT * FROM _nav_item ';
		$queryStr .=   'WHERE ni_nav_id = ? ';
		$queryStr .=   'ORDER BY ni_id';
		$this->selectLoop($queryStr, array($navId), $callback, null);
	}
	/**
	 * ナビゲーションバー項目をすべて取得
	 *
	 * @param string 	$navId			ナビゲーションバー識別ID
	 * @param array  	$rows			取得レコード
	 * @return 			なし
	 */
	function getNavItemsAllRecords($navId, &$rows)
	{
		$queryStr  = 'SELECT * FROM _nav_item ';
		$queryStr .=   'WHERE ni_nav_id = ? ';
		$queryStr .=   'ORDER BY ni_id';
		$retValue = $this->selectRecords($queryStr, array($navId), $rows);
		return $retValue;
	}
	/**
	 * ナビゲーションバー項目を削除
	 *
	 * @param string $menuId		メニュー識別ID
	 * @return						true=成功、false=失敗
	 */
	function delNavItems($menuId)
	{
		$sql = "DELETE FROM _nav_item WHERE ni_nav_id = ?";
		$params = array($menuId);
		$this->execStatement($sql, $params);
	}
	/**
	 * ナビゲーションバー項目の最大IDを取得
	 *
	 * @return int			最大ID
	 */
	function getNavItemsMaxId()
	{
		$max = 0;
		$queryStr = 'SELECT max(ni_id) as mid FROM _nav_item ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $max = $row['mid'];
		return $max;
	}
	/**
	 * ナビゲーションバー項目キー存在チェック
	 *
	 * @param string $menuId		メニュー識別ID
	 * @param string $taskId		タスクID
	 * @param string $param			追加パラメータ
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsNavItemKey($menuId, $taskId, $param)
	{
		$queryStr = 'SELECT * FROM _nav_item ';
		$queryStr .=  'WHERE ni_nav_id = ? ';
		$queryStr .=  'AND ni_task_id = ? ';
		$queryStr .=  'AND ni_param = ? ';
		return $this->isRecordExists($queryStr, array($menuId, $taskId, $param));
	}
	/**
	 * ナビゲーションバー項目を更新
	 *
	 * @param string $navId			ナビゲーションバー識別ID
	 * @param int $id				項目ID
	 * @param int $parentId			項目親項目ID
	 * @param int $index			インデックス番号起動
	 * @param string $taskId		タスクID
	 * @param string $param			追加パラメータ
	 * @param int $control			改行指示(0=改行しない、1=改行)
	 * @param string $name			項目名
	 * @param string $helpTitle		ヘルプタイトル
	 * @param string $helpBody		ヘルプ本体
	 * @return						true=成功、false=失敗
	 */
	function addNavItems($navId, $id, $parentId, $index, $taskId, $param, $control, $name, $helpTitle, $helpBody)
	{
		// 新規レコード追加
		$groupId = '';
		$queryStr  = 'INSERT INTO _nav_item (';
		$queryStr .=   'ni_id, ';
		$queryStr .=   'ni_parent_id, ';
		$queryStr .=   'ni_index, ';
		$queryStr .=   'ni_nav_id, ';
		$queryStr .=   'ni_task_id, ';
		$queryStr .=   'ni_param, ';
		$queryStr .=   'ni_group_id, ';
		$queryStr .=   'ni_view_control, ';
		$queryStr .=   'ni_name, ';
		$queryStr .=   'ni_help_title, ';
		$queryStr .=   'ni_help_body ';
		$queryStr .= ') VALUES (';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?, ';
		$queryStr .=   '?)';
		$ret = $this->execStatement($queryStr, array($id, $parentId, $index, $navId, $taskId, $param, $groupId, $control, $name, $helpTitle, $helpBody));
		return $ret;
	}
	/**
	 * ナビゲーションバー項目を取得(タスク指定)
	 *
	 * @param string $navId			ナビゲーションバー識別ID
	 * @param string $taskId		タスクID
	 * @param array  $row			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getNavItemsByTask($navId, $taskId, &$row)
	{
		$queryStr  = 'SELECT * FROM _nav_item ';
		$queryStr .=   'WHERE ni_nav_id = ? ';
		$queryStr .=     'AND ni_task_id = ? ';
//		$queryStr .=     'AND ni_visible = true ';
		$retValue = $this->selectRecord($queryStr, array($navId, $taskId), $row);
		return $retValue;
	}
	/**
	 * メニュー項目のタスクを更新
	 *
	 * @param string $itemId	メニュー項目ID
	 * @param bool $taskId		タスク
	 * @return					true = 正常、false=異常
	 */
	function updateNavItemMenuType($itemId, $taskId)
	{
		$sql = 'UPDATE _nav_item SET ni_task_id = ? WHERE ni_id = ?';
		$params = array($taskId, $itemId);
		$retValue =$this->execStatement($sql, $params);
		return $retValue;
	}
	/**
	 * メニュー項目の表示制御
	 *
	 * @param string $itemId	メニュー項目ID
	 * @param bool $visible		表示非表示
	 * @return					true = 正常、false=異常
	 */
	function updateNavItemVisible($itemId, $visible)
	{
		$queryStr  = 'UPDATE _nav_item ';
		$queryStr .=   'SET ni_visible = ? ';
		$queryStr .= 'WHERE ni_id = ?';
		$params = array(intval($visible), $itemId);
		$retValue = $this->execStatement($queryStr, $params);
		return $retValue;
	}
	/**
	 * 変換キーテーブルを取得
	 *
	 * @param string	$key				キー文字列
	 * @param string	$group				グループID
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllKey($key, $group, $callback)
	{
		$queryStr = 'SELECT * FROM _key_value ';
		$queryStr .=  'WHERE kv_deleted = false ';
		$queryStr .=    'AND kv_id LIKE \'' . $key . '%\' ';
		$queryStr .=    'AND kv_group_id = ? ';
		$queryStr .=  'ORDER BY kv_id';
		$this->selectLoop($queryStr, array($group), $callback, null);
	}
	/**
	 * 運用ログ取得
	 *
	 * @param int		$level		取得ログのレベル(0すべて、1=注意以上、10=要確認)
	 * @param int		$status		取得するデータの状況(0=すべて、1=未参照のみ、2=参照済みのみ)
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param function	$callback	コールバック関数
	 * @return						なし
	 */
	function getOpeLogList($level, $status, $limit, $page, $callback)
	{
		// メッセージ種別
		// 通常メッセージ: info=情報,warn=警告,user_info=ユーザ操作
		// 参照必須メッセージ: error=通常エラー,fatal=致命的エラー,user_err=ユーザ操作エラー,user_access=不正アクセス,user_data=不正データ
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$queryStr = 'SELECT * FROM _operation_log LEFT JOIN _operation_type ON ol_type = ot_id ';
		$queryStr .= 'LEFT JOIN _access_log ON ol_access_log_serial = al_serial ';
		
		// 必須参照項目のみに限定
		$params = array();
		$addWhere = '';
		if ($level > 0){
			$addWhere .= 'WHERE ot_level >= ? ';
			$params[] = $level;
		}
		// 参照状況を制限
		if ($status == 1){		// 未参照
			if (empty($addWhere)){
				$addWhere .= 'WHERE ';
			} else {
				$addWhere .= 'AND ';
			}
			$addWhere .= 'ol_checked = false ';
		} else if ($status == 2){	// 参照済み
			if (empty($addWhere)){
				$addWhere .= 'WHERE ';
			} else {
				$addWhere .= 'AND ';
			}
			$addWhere .= 'ol_checked = true ';
		}
		$queryStr .= $addWhere;
		$queryStr .=  'ORDER BY ol_serial DESC limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 運用ログ総数取得
	 *
	 * @param int		$level		取得ログのレベル(0すべて、1=参照必須)
	 * @param int		$status		取得するデータの状況(0=すべて、1=未参照のみ、2=参照済みのみ)
	 * @return int					総数
	 */
	function getOpeLogCount($level, $status)
	{
		$queryStr = 'SELECT * FROM _operation_log LEFT JOIN _operation_type ON ol_type = ot_id ';
		
		// 必須参照項目のみに限定
		$params = array();
		$addWhere = '';
		if ($level > 0){
			$addWhere .= 'WHERE ot_level >= ? ';
			$params[] = $level;
		}
		// 参照状況を制限
		if ($status == 1){		// 未参照
			if (empty($addWhere)){
				$addWhere .= 'WHERE ';
			} else {
				$addWhere .= 'AND ';
			}
			$addWhere .= 'ol_checked = false ';
		} else if ($status == 2){	// 参照済み
			if (empty($addWhere)){
				$addWhere .= 'WHERE ';
			} else {
				$addWhere .= 'AND ';
			}
			$addWhere .= 'ol_checked = true ';
		}
		$queryStr .= $addWhere;
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * メッセージコードから運用ログ取得
	 *
	 * @param int,array	$messageCode	メッセージコード。配列の場合はORで取得。
	 * @param string	$searchOption	検索付加オプション
	 * @param int		$limit			取得する項目数
	 * @param int		$page			取得するページ(1～)
	 * @param function	$callback		コールバック関数
	 * @return							なし
	 */
	function getOpeLogListByMessageCode($messageCode, $searchOption, $limit, $page, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		if (!is_array($messageCode)) $messageCode = array($messageCode);
		
		$params = array();
		$queryStr  = 'SELECT * FROM _operation_log LEFT JOIN _operation_type ON ol_type = ot_id ';
		$queryStr .=   'LEFT JOIN _access_log ON ol_access_log_serial = al_serial ';
		$queryStr .=   'WHERE ol_search_option = ? '; $params[] = $searchOption;
		$queryStr .=     'AND (';
		for ($i = 0; $i < count($messageCode); $i++){
			if ($i > 0) $queryStr .= 'OR ';
			$queryStr .=     'ol_message_code = ? '; $params[] = $messageCode[$i];
		}
		$queryStr .=     ') ';
		$queryStr .=   'ORDER BY ol_serial DESC limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * メッセージコードから運用ログ総数取得
	 *
	 * @param int,array		$messageCode	メッセージコード。配列の場合はORで取得。
	 * @param string		$searchOption	検索付加オプション
	 * @return int							総数
	 */
	function getOpeLogCountByMessageCode($messageCode, $searchOption)
	{
		if (!is_array($messageCode)) $messageCode = array($messageCode);
		
		$params = array();
		$queryStr  = 'SELECT * FROM _operation_log LEFT JOIN _operation_type ON ol_type = ot_id ';
		$queryStr .=   'WHERE ol_search_option = ? '; $params[] = $searchOption;
		$queryStr .=     'AND (';
		for ($i = 0; $i < count($messageCode); $i++){
			if ($i > 0) $queryStr .= 'OR ';
			$queryStr .=     'ol_message_code = ? '; $params[] = $messageCode[$i];
		}
		$queryStr .=     ') ';
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 運用ログの取得
	 *
	 * @param string  $serial		シリアル番号
	 * @return						true=正常、false=異常
	 */
	function getOpeLog($serial, &$row)
	{
		$queryStr = 'SELECT * FROM _operation_log LEFT JOIN _operation_type ON ol_type = ot_id ';
		$queryStr .=   'WHERE ol_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * 運用ログの確認状況を更新
	 *
	 * @param string  $serial		シリアル番号
	 * @param bool    $checked		確認状況
	 * @return						true=正常、false=異常
	 */
	function updateOpeLogChecked($serial, $checked)
	{
		$queryStr = "UPDATE _operation_log SET ol_checked = ? WHERE ol_serial = ?";
		$params = array(intval($checked), $serial);
		return $this->execStatement($queryStr, $params);
	}
	/**
	 * アクセスログ取得
	 *
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param string	$path		アクセスパス
	 * @param function	$callback	コールバック関数
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @return						なし
	 */
	function getAccessLogList($limit, $page, $path, $callback, $startDt, $endDt)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM _access_log LEFT JOIN _login_user on al_user_id = lu_id ';
		if (!is_null($path)){
			$queryStr .=  'WHERE al_path = ? ';
			$params[] = $path;
		}
		// 日付範囲
		if (!empty($startDt)){
			if (count($params) > 0){
				$queryStr .=    'AND ? <= al_dt ';
			} else {
				$queryStr .=    'WHERE ? <= al_dt ';
			}
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			if (count($params) > 0){
				$queryStr .=    'AND al_dt < ? ';
			} else {
				$queryStr .=    'WHERE al_dt < ? ';
			}
			$params[] = $endDt;
		}
		$queryStr .=  'ORDER BY al_serial DESC limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * アクセスログ総数取得
	 *
	 * @param string	$path		アクセスパス
	 * @param timestamp	$startDt	期間(開始日)
	 * @param timestamp	$endDt		期間(終了日)
	 * @return int					総数
	 */
	function getAccessLogCount($path, $startDt, $endDt)
	{
		$params = array();
		$queryStr = 'SELECT * FROM _access_log ';
		if (!is_null($path)){
			$queryStr .=  'WHERE al_path = ? ';
			$params[] = $path;
		}
		// 日付範囲
		if (!empty($startDt)){
			if (count($params) > 0){
				$queryStr .=    'AND ? <= al_dt ';
			} else {
				$queryStr .=    'WHERE ? <= al_dt ';
			}
			$params[] = $startDt;
		}
		if (!empty($endDt)){
			if (count($params) > 0){
				$queryStr .=    'AND al_dt < ? ';
			} else {
				$queryStr .=    'WHERE al_dt < ? ';
			}
			$params[] = $endDt;
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * アクセスログの取得
	 *
	 * @param string  $serial		シリアル番号
	 * @return						true=正常、false=異常
	 */
	function getAccessLog($serial, &$row)
	{
		$queryStr = 'SELECT * FROM _access_log LEFT JOIN _login_user on al_user_id = lu_id ';
		$queryStr .=   'WHERE al_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * 検索語ログ取得
	 *
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param string	$path		アクセスパス
	 * @param function	$callback	コールバック関数
	 * @return						なし
	 */
	function getSearchWordLogList($limit, $page, $path, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT * FROM _search_word  ';
		$queryStr .=   'LEFT JOIN _access_log ON sw_access_log_serial = al_serial ';
		$queryStr .=   'LEFT JOIN _login_user on al_user_id = lu_id ';
		if (!is_null($path)){
			$queryStr .=  'WHERE sw_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'ORDER BY sw_serial DESC limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 検索語ログ総数取得
	 *
	 * @param string	$path		アクセスパス
	 * @return int					総数
	 */
	function getSearchWordLogCount($path)
	{
		$params = array();
		$queryStr = 'SELECT * FROM _search_word ';
		if (!is_null($path)){
			$queryStr .=  'WHERE sw_path = ? ';
			$params[] = $path;
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 比較語から検索語ログ取得
	 *
	 * @param string    $word		比較語
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param string	$path		アクセスパス
	 * @param function	$callback	コールバック関数
	 * @return						なし
	 */
	function getSearchWordLogListByWord($word, $limit, $page, $path, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT * FROM _search_word  ';
		$queryStr .=   'LEFT JOIN _access_log ON sw_access_log_serial = al_serial ';
		$queryStr .=   'LEFT JOIN _login_user on al_user_id = lu_id ';
		$queryStr .=   'WHERE sw_basic_word = ? '; $params[] = $word;
		if (!is_null($path)){
			$queryStr .=  'AND sw_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'ORDER BY sw_serial DESC limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 比較語から検索語ログ総数取得
	 *
	 * @param string    $word		比較語
	 * @param string	$path		アクセスパス
	 * @return int					総数
	 */
	function getSearchWordLogCountByWord($word, $path)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM _search_word ';
		$queryStr .=   'WHERE sw_basic_word = ? '; $params[] = $word;
		if (!is_null($path)){
			$queryStr .=  'AND sw_path = ? ';
			$params[] = $path;
		}
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 検索語検索数リスト取得
	 *
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param string	$path		アクセスパス
	 * @param function	$callback	コールバック関数
	 * @return						なし
	 */
	function getSearchWordSumList($limit, $page, $path, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT count(*) AS ct, sw_basic_word FROM _search_word ';
		if (!is_null($path)){
			$queryStr .=  'WHERE sw_path = ? ';
			$params[] = $path;
		}
		$queryStr .=  'GROUP BY sw_basic_word ';
		$queryStr .=  'ORDER BY ct DESC limit ' . $limit . ' offset ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * 検索語検索数総数取得
	 *
	 * @param string	$path		アクセスパス
	 * @return int					総数
	 */
	function getSearchWordSumCount($path)
	{
		$params = array();
		$queryStr  = 'SELECT count(*) AS ct FROM _search_word ';
		if (!is_null($path)){
			$queryStr .=  'WHERE sw_path = ? ';
			$params[] = $path;
		}
		$queryStr .=   'GROUP BY sw_basic_word';
		return $this->selectRecordCount($queryStr, $params);
	}
	/**
	 * 検索語ログの取得
	 *
	 * @param int  $serial		シリアル番号
	 * @return						true=正常、false=異常
	 */
	function getSearchWordLog($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM _search_word  ';
		$queryStr .=   'LEFT JOIN _access_log ON sw_access_log_serial = al_serial ';
		$queryStr .=   'LEFT JOIN _login_user on al_user_id = lu_id ';
		$queryStr .=   'WHERE sw_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * 比較語から検索語ログの取得
	 *
	 * @param string  $word		比較語
	 * @return					true=正常、false=異常
	 */
	function getSearchWordLogByCompareWord($word, &$row)
	{
		$queryStr  = 'SELECT * FROM _search_word  ';
		$queryStr .=   'LEFT JOIN _access_log ON sw_access_log_serial = al_serial ';
		$queryStr .=   'LEFT JOIN _login_user on al_user_id = lu_id ';
		$queryStr .=   'WHERE sw_basic_word = ? ';
		$queryStr .=   'ORDER BY sw_serial DESC ';
		$ret = $this->selectRecord($queryStr, array($word), $row);
		return $ret;
	}
	/**
	 * メニュー項目の追加
	 *
	 * @param string  $menuId		メニューID
	 * @param string  $parentId		親メニュー項目ID
	 * @param string  $name			メニュー名
	 * @param string  $title		タイトル(HTML可)
	 * @param string  $desc			説明
	 * @param int     $index		インデックス番号(0のときは最大値を設定)
	 * @param int     $type			項目タイプ
	 * @param int     $linkType		リンクタイプ
	 * @param string  $url			URL
	 * @param bool    $visible		表示状態
	 * @param bool $userLimited		ユーザ制限するかどうか
	 * @param int     $newId		新規ID
	 * @param string  $contentType	リンク先コンテンツタイプ
	 * @param string  $contentId	リンク先コンテンツID
	 * @return bool					true = 成功、false = 失敗
	 */
	function addMenuItem($menuId, $parentId, $name, $title, $desc, $index, $type, $linkType, $url, $visible, $userLimited, &$newId, $contentType = '', $contentId = '')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// IDを求める
		$id = 1;
		$queryStr = 'SELECT max(md_id) as ms FROM _menu_def ';
		$ret = $this->selectRecord($queryStr, array(), $maxRow);
		if ($ret) $id = $maxRow['ms'] + 1;
			
		// インデックスが0のときは、最大値を格納
		if (empty($index)){
			$index = 1;
			$queryStr = 'SELECT max(md_index) as ms FROM _menu_def ';
			$queryStr .=  'WHERE md_menu_id = ? ';
			$ret = $this->selectRecord($queryStr, array($menuId), $maxRow);
			if ($ret) $index = $maxRow['ms'] + 1;
		}
		$queryStr = 'INSERT INTO _menu_def ';
		$queryStr .=  '(md_id, md_parent_id, md_index, md_menu_id, md_name, md_title, md_description, md_type, md_link_type, md_link_url, md_visible, md_user_limited, md_content_type, md_content_id, md_update_user_id, md_update_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $parentId, $index, $menuId, $name, $title, $desc, $type, $linkType, $url, intval($visible), intval($userLimited), $contentType, $contentId, $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'select max(md_id) as ns from _menu_def ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newId = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目の更新
	 *
	 * @param int     $id			メニュー項目ID
	 * @param string  $name			メニュー名
	 * @param string  $title		タイトル(HTML可)
	 * @param string  $desc			説明
	 * @param int     $type			項目タイプ
	 * @param int     $linkType		リンクタイプ
	 * @param string  $url			URL
	 * @param bool    $visible		表示状態
	 * @param bool $userLimited		ユーザ制限するかどうか
	 * @param string  $contentType	リンク先コンテンツタイプ
	 * @param string  $contentId	リンク先コンテンツID
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateMenuItem($id, $name, $title, $desc, $type, $linkType, $url, $visible, $userLimited, $contentType = '', $contentId = '')
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();

		$params = array();
		$queryStr = 'UPDATE _menu_def ';
		$queryStr .=  'SET md_name = ?, ';			$params[] = $name;
		$queryStr .=    'md_title = ?, ';			$params[] = $title;
		$queryStr .=    'md_description = ?, ';		$params[] = $desc;
		$queryStr .=    'md_type = ?, ';			$params[] = $type;
		$queryStr .=    'md_link_type = ?, ';		$params[] = $linkType;
		$queryStr .=    'md_link_url = ?, ';		$params[] = $url;
		$queryStr .=    'md_visible = ?, ';			$params[] = intval($visible);
		$queryStr .=    'md_user_limited = ?, ';			$params[] = intval($userLimited);
		$queryStr .=    'md_content_type = ?, ';	$params[] = $contentType;
		$queryStr .=    'md_content_id = ?, ';		$params[] = $contentId;
		$queryStr .=    'md_update_user_id = ?, ';	$params[] = $userId;
		$queryStr .=    'md_update_dt = ? ';		$params[] = $now;
		$queryStr .=  'WHERE md_id = ? ';			$params[] = $id;
		$this->execStatement($queryStr, $params);

		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目の削除
	 *
	 * @param string $id			複数シリアルNoをカンマ区切り
	 * @return						true=成功、false=失敗
	 */
	function delMenuItems($id)
	{
		// トランザクション開始
		$this->startTransaction();
		
		// レコードを削除
		$queryStr  = 'DELETE FROM _menu_def ';
		$queryStr .=   'WHERE md_id in (' . $id . ') ';
		$this->execStatement($queryStr, array());
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目をIDで取得
	 *
	 * @param int     $id			メニュー項目ID
	 * @param array   $row			レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getMenuItem($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * メニュー項目を取得
	 *
	 * @param string $menuId		メニュー識別ID
	 * @param string $parentId		親項目ID
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getChildMenuItems($menuId, $parentId, &$rows)
	{
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_menu_id = ? ';
		$queryStr .=     'AND md_parent_id = ? ';
		$queryStr .=   'ORDER BY md_index';
		$retValue = $this->selectRecords($queryStr, array($menuId, $parentId), $rows);
		return $retValue;
	}
	/**
	 * メニューの項目を取得(管理用)
	 *
	 * @param string $menuId		メニューID
	 * @param function $callback	コールバック関数
	 * @return 						なし
	 */
	function getAllMenuItems($menuId, $callback)
	{
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_menu_id = ? ';
		$queryStr .=   'ORDER BY md_parent_id, md_index';
		$this->selectLoop($queryStr, array($menuId), $callback);
	}
	/**
	 * メニュー項目順序を変更
	 *
	 * @param string $menuId		メニュー識別ID
	 * @param int $parentId			親項目ID
	 * @param int $id				項目ID
	 * @param int $pos				新規の位置
	 * @return						true=成功、false=失敗
	 */
	function reorderMenuItem($menuId, $parentId, $id, $pos)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_menu_id = ? ';
		$queryStr .=     'AND md_parent_id = ? ';
		$queryStr .=   'ORDER BY md_index';
		$retValue = $this->selectRecords($queryStr, array($menuId, $parentId), $rows);
		
		// 同階層内かどうかチェック
		$insPos = $pos;			// 項目挿入位置
		for ($i = 0; $i < count($rows); $i++){
			if ($id == $rows[$i]['md_id']){
				//if ($i < $pos) $insPos++;		// 2011/8/22 simpleTreeからjsTreeに変更のため仕様変更
				break;
			}
		}
		$index = 0;
		for ($i = 0; $i < $insPos; $i++){
			$itemId = $rows[$i]['md_id'];
			if ($itemId != $id){
				$queryStr  = 'UPDATE _menu_def ';
				$queryStr .=   'SET md_index = ?, ';	// インデックス
				$queryStr .=     'md_update_user_id = ?, ';
				$queryStr .=     'md_update_dt = ? ';
				$queryStr .=   'WHERE md_id = ?';
				$ret = $this->execStatement($queryStr, array($index, $userId, $now, $itemId));
				$index++;
			}
		}
		$queryStr  = 'UPDATE _menu_def ';
		$queryStr .=   'SET md_index = ?, ';	// インデックス
		$queryStr .=     'md_parent_id = ?, ';
		$queryStr .=     'md_update_user_id = ?, ';
		$queryStr .=     'md_update_dt = ? ';
		$queryStr .=   'WHERE md_id = ?';
		$ret = $this->execStatement($queryStr, array($index, $parentId, $userId, $now, $id));
		$index++;
		for ($i = $insPos; $i < count($rows); $i++){
			$itemId = $rows[$i]['md_id'];
			if ($itemId != $id){
				$queryStr  = 'UPDATE _menu_def ';
				$queryStr .=   'SET md_index = ?, ';	// インデックス
				$queryStr .=     'md_update_user_id = ?, ';
				$queryStr .=     'md_update_dt = ? ';
				$queryStr .=   'WHERE md_id = ?';
				$ret = $this->execStatement($queryStr, array($index, $userId, $now, $itemId));
				$index++;
			}
		}
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニュー項目の表示順を変更する
	 *
	 * @param string  $menuId			メニューID
	 * @param int $parentId				親項目ID
	 * @param bool $visibleOnly			表示項目だけかどうか
	 * @param array $menuItemNoArray	並び順
	 * @return bool					true = 成功、false = 失敗
	 */
	function orderMenuItems($menuId, $parentId, $visibleOnly, $menuItemNoArray)
	{
		// メニュー項目をすべて取得
		$queryStr  = 'SELECT * FROM _menu_def ';
		$queryStr .=   'WHERE md_menu_id = ? ';
		$queryStr .=     'AND md_parent_id = ? ';
		if ($visibleOnly) $queryStr .= 'AND md_visible = true ';		// 表示中の項目
		$queryStr .=   'ORDER BY md_index';
		$ret = $this->selectRecords($queryStr, array($menuId, $parentId), $rows);
		if (!$ret) return false;
	
		// メニュー数をチェックし、異なっている場合はエラー
		$menuItemCount = count($rows);
		if ($menuItemCount != count($menuItemNoArray)) return false;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();

		for ($i = 0; $i < $menuItemCount; $i++){
			$id = $rows[$menuItemNoArray[$i]]['md_id'];
			$index = $rows[$i]['md_index'];

			// 既存項目を更新
			$queryStr  = 'UPDATE _menu_def ';
			$queryStr .=   'SET ';
			$queryStr .=     'md_index = ?, ';
			$queryStr .=     'md_update_user_id = ?, ';
			$queryStr .=     'md_update_dt = ? ';
			$queryStr .=   'WHERE md_id = ? ';
			$this->execStatement($queryStr, array($index, $userId, $now, $id));
		}
										
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * コンテンツ項目一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param string	$contentType		コンテンツタイプ
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllContents($lang, $contentType, $callback)
	{
		$queryStr = 'SELECT * FROM content ';
		$queryStr .=  'WHERE cn_type = ? ';
		$queryStr .=    'AND cn_language_id = ? ';
//		$queryStr .=    'AND cn_visible = true ';		// 画面に表示可能
		$queryStr .=    'AND cn_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY cn_id';
		$this->selectLoop($queryStr, array($contentType, $lang), $callback, null);
	}
	/**
	 * 汎用コンテンツを外部キーで取得
	 *
	 * @param string	$contentType		コンテンツタイプ
	 * @param string	$langId				言語ID
	 * @param string	$key				外部キー
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getContentByKey($contentType, $langId, $key, &$row)
	{
		$queryStr  = 'SELECT * FROM content ';
		$queryStr .=   'WHERE cn_deleted = false ';	// 削除されていない
		$queryStr .=     'AND cn_type = ? ';
		$queryStr .=     'AND cn_language_id = ? ';
		$queryStr .=     'AND cn_key = ? ';
		$queryStr .=   'ORDER BY cn_id';
		$ret = $this->selectRecord($queryStr, array($contentType, $langId, $key), $row);
		return $ret;
	}
	/**
	 * コンテンツ項目をシリアル番号で取得
	 *
	 * @param string	$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getContentBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM content LEFT JOIN _login_user ON cn_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE cn_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * メニューIDのリストを取得
	 *
	 * @param int $deviceType				端末タイプ(-1=すべて、0=PC、1=携帯、2=スマートフォン)
	 * @param function $callback			コールバック関数
	 * @param bool $getWidgetMenu			ウィジェット専用メニューを取得するかどうか
	 * @param bool $activeAccessPointOnly	有効なアクセスポイントのメニューIDのみを取得するかどうか
	 * @return								なし
	 */
	function getMenuIdList($deviceType, $callback, $getWidgetMenu = false, $activeAccessPointOnly = false)
	{
		$addWhere = '';
		$params = array();
		$queryStr  = 'SELECT * FROM _menu_id ';
		$queryStr .=   'LEFT JOIN _page_id ON mn_device_type = pg_device_type AND pg_type = 0 AND pg_frontend = true ';
		if ($deviceType != -1){
			$addWhere = 'WHERE mn_device_type = ? ';
			$params[] = $deviceType;
		}

		if (!$getWidgetMenu){
			if (empty($addWhere)){
				$addWhere .= 'WHERE ';
			} else {
				$addWhere .= 'AND ';
			}
			$addWhere .=  'mn_widget_id = \'\' ';
		}

		if ($activeAccessPointOnly){
			if (empty($addWhere)){
				$addWhere .= 'WHERE ';
			} else {
				$addWhere .= 'AND ';
			}
			$addWhere .=  'pg_active = true ';
		}
		
		$queryStr .= $addWhere;
		$queryStr .= 'ORDER BY pg_priority, mn_sort_order';
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * メニューIDのレコードを取得
	 *
	 * @param string  $id			メニューID
	 * @param array   $row			レコード
	 * @return bool					取得 = true, 取得なし= false
	 */
	function getMenuId($id, &$row)
	{
		$retValue = '';
		$queryStr = 'SELECT * FROM _menu_id ';
		$queryStr .=  'WHERE mn_id  = ?';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * メニューIDのレコードを更新
	 *
	 * @param string $id			メニューID(存在しない場合は新規追加)
	 * @param string $name			名前
	 * @param int    $order			ソート順
	 * @param ing    $deviceType	端末タイプ
	 * @param string $widget		対象ウィジェット
	 * @return bool			true=更新成功、false=更新失敗
	 */
	function updateMenuId($id, $name, $order, $deviceType, $widget)
	{
		// トランザクション開始
		$this->startTransaction();
		
		$queryStr = 'SELECT * FROM _menu_id ';
		$queryStr .=  'WHERE mn_id  = ?';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			$queryStr  = 'UPDATE _menu_id ';
			$queryStr .=   'SET mn_name = ?, ';
			$queryStr .=     'mn_sort_order = ?, ';
			$queryStr .=     'mn_device_type = ?, ';
			$queryStr .=     'mn_widget_id = ? ';
			$queryStr .=   'WHERE mn_id = ?';
			$ret = $this->execStatement($queryStr, array($name, $order, $deviceType, $widget, $id));			
		} else {
			$queryStr = 'INSERT INTO _menu_id (';
			$queryStr .=  'mn_id, ';
			$queryStr .=  'mn_name, ';
			$queryStr .=  'mn_sort_order, ';
			$queryStr .=  'mn_device_type, ';
			$queryStr .=  'mn_widget_id ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?, ?, ?, ?';
			$queryStr .=  ')';
			$ret = $this->execStatement($queryStr, array($id, $name, $order, $deviceType, $widget));	
		}
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニューIDの削除
	 *
	 * @param array $serial			メニューIDの配列
	 * @return						true=成功、false=失敗
	 */
	function delMenuId($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$delId = '';
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM _menu_id ';
			$queryStr .=   'WHERE mn_id = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
			$delId .= '\'' . addslashes($serial[$i]) . '\',';
		}
		$delId = rtrim($delId, ',');
		
		// データ削除
		$queryStr  = 'DELETE FROM _menu_id ';
		$queryStr .=   'WHERE mn_id in (' . $delId . ') ';
		$this->execStatement($queryStr, array());
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * メニューIDの存在チェック
	 *
	 * @param string  $id			メニューID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsMenuId($id)
	{
		$queryStr = 'SELECT * FROM _menu_id ';
		$queryStr .=  'WHERE mn_id = ? ';
		return $this->isRecordExists($queryStr, array($id));
	}
	/**
	 * ユーザグループ一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getAllUserGroup($lang, $callback)
	{
		$queryStr = 'SELECT * FROM _user_group LEFT JOIN _login_user ON ug_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE ug_language_id = ? ';
		$queryStr .=    'AND ug_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY ug_sort_order';
		$this->selectLoop($queryStr, array($lang), $callback);
	}
	/**
	 * ユーザグループ一覧を取得
	 *
	 * @param string	$lang				言語
	 * @param array		$rows				取得データ
	 * @return bool							true=取得、false=取得せず
	 */
	function getAllUserGroupRows($lang, &$rows)
	{
		$queryStr = 'SELECT * FROM _user_group LEFT JOIN _login_user ON ug_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE ug_language_id = ? ';
		$queryStr .=    'AND ug_deleted = false ';		// 削除されていない
		$queryStr .=  'ORDER BY ug_sort_order';
		$retValue = $this->selectRecords($queryStr, array($lang), $rows);
		return $retValue;
	}
	/**
	 * ユーザグループをシリアル番号で削除
	 *
	 * @param array   $serial		シリアルNo
	 * @return						true=成功、false=失敗
	 */
	function delUserGroupBySerial($serial)
	{
		// 引数のエラーチェック
		if (!is_array($serial)) return false;
		if (count($serial) <= 0) return true;
		
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		for ($i = 0; $i < count($serial); $i++){
			$queryStr  = 'SELECT * FROM _user_group ';
			$queryStr .=   'WHERE ug_deleted = false ';		// 未削除
			$queryStr .=     'AND ug_serial = ? ';
			$ret = $this->isRecordExists($queryStr, array($serial[$i]));
			// 存在しない場合は、既に削除されたとして終了
			if (!$ret){
				$this->endTransaction();
				return false;
			}
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE _user_group ';
		$queryStr .=   'SET ug_deleted = true, ';	// 削除
		$queryStr .=     'ug_update_user_id = ?, ';
		$queryStr .=     'ug_update_dt = ? ';
		$queryStr .=   'WHERE ug_serial in (' . implode($serial, ',') . ') ';
		$this->execStatement($queryStr, array($userId, $now));
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ユーザグループをシリアル番号で取得
	 *
	 * @param int		$serial				シリアル番号
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getUserGroupBySerial($serial, &$row)
	{
		$queryStr  = 'SELECT * FROM _user_group LEFT JOIN _login_user ON ug_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=   'WHERE ug_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		return $ret;
	}
	/**
	 * ユーザグループを識別IDで取得
	 *
	 * @param string	$id					識別ID
	 * @param array     $row				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getUserGroupById($id, &$row)
	{
		$queryStr  = 'SELECT * FROM _user_group LEFT JOIN _login_user ON ug_create_user_id = lu_id AND lu_deleted = false ';
		$queryStr .=  'WHERE ug_deleted = false ';
		$queryStr .=  'AND ug_id = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * ユーザグループの最大表示順を取得
	 *
	 * @param string	$lang		言語
	 * @return int					最大表示順
	 */
	function getUserGroupMaxIndex($lang)
	{
		$queryStr = 'SELECT max(ug_sort_order) as mi FROM _user_group ';
		$queryStr .=  'WHERE ug_deleted = false ';
		$queryStr .=  'AND ug_language_id = ? ';
		$ret = $this->selectRecord($queryStr, array($lang), $row);
		if ($ret){
			$index = $row['mi'];
		} else {
			$index = 0;
		}
		return $index;
	}
	/**
	 * ユーザグループの新規追加
	 *
	 * @param int	  $id			識別ID
	 * @param string  $lang			言語ID
	 * @param string  $name			名前
	 * @param int     $index		表示順
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function addUserGroup($id, $lang, $name, $index, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();
		
		// ユーザグループが存在していないかチェック
		$ret = $this->getUserGroupById($id, $row);
		if ($ret){
			$this->endTransaction();
			return false;
		}
		
		// データを追加
		$queryStr  = 'INSERT INTO _user_group ';
		$queryStr .=   '(ug_id, ug_language_id, ug_name, ug_sort_order, ug_create_user_id, ug_create_dt) ';
		$queryStr .=   'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($id, $lang, $name, $index, $userId, $now));
		
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ug_serial) AS ns FROM _user_group ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
			
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * ユーザグループの更新
	 *
	 * @param int     $serial		シリアル番号
	 * @param string  $name			名前
	 * @param int     $index		表示順
	 * @param int     $newSerial	新規シリアル番号
	 * @return bool					true = 成功、false = 失敗
	 */
	function updateUserGroup($serial, $name, $index, &$newSerial)
	{	
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
				
		// トランザクション開始
		$this->startTransaction();
		
		// 指定のシリアルNoのレコードが削除状態でないかチェック
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM _user_group ';
		$queryStr .=   'WHERE ug_serial = ? ';
		$ret = $this->selectRecord($queryStr, array($serial), $row);
		if ($ret){		// 既に登録レコードがあるとき
			if ($row['ug_deleted']){		// レコードが削除されていれば終了
				$this->endTransaction();
				return false;
			}
			$historyIndex = $row['ug_history_index'] + 1;
		} else {		// 存在しない場合は終了
			$this->endTransaction();
			return false;
		}
		
		// 古いレコードを削除
		$queryStr  = 'UPDATE _user_group ';
		$queryStr .=   'SET ug_deleted = true, ';	// 削除
		$queryStr .=     'ug_update_user_id = ?, ';
		$queryStr .=     'ug_update_dt = ? ';
		$queryStr .=   'WHERE ug_serial = ?';
		$this->execStatement($queryStr, array($userId, $now, $serial));
		
		// 新規レコード追加
		$queryStr = 'INSERT INTO _user_group ';
		$queryStr .=  '(ug_id, ug_language_id, ug_history_index, ug_name, ug_sort_order, ug_create_user_id, ug_create_dt) ';
		$queryStr .=  'VALUES ';
		$queryStr .=  '(?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($row['ug_id'], $row['ug_language_id'], $historyIndex, $name, $index, $userId, $now));

		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(ug_serial) AS ns FROM _user_group ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 画面配置している主要コンテンツ編集ウィジェットを取得
	 *
	 * @param string $langId			言語ID
	 * @param array $pageIdArray		ページID
	 * @param array $contentTypeArray    コンテンツタイプ
	 * @param array  $rows				取得レコード
	 * @param int    $setId				定義セットID
	 * @return							true=取得、false=取得せず
	 */
	function getEditWidgetOnPage($langId, $pageIdArray, $contentTypeArray, &$rows, $setId = 0)
	{
		// CASE文作成
		$caseStr = 'CASE pd_id ';
		$pageStr = '';
		for ($i = 0; $i < count($pageIdArray); $i++){
			$caseStr .= 'WHEN \'' . $pageIdArray[$i] . '\' THEN ' . $i . ' ';
			$pageStr .= '\'' . $pageIdArray[$i] . '\', ';
		}
		$caseStr .= 'END AS pageno, ';
		$pageStr = rtrim($pageStr, ', ');
		
		$caseStr .= 'CASE wd_type ';
		$contentStr = '';
		for ($i = 0; $i < count($contentTypeArray); $i++){
			$caseStr .= 'WHEN \'' . $contentTypeArray[$i] . '\' THEN ' . $i . ' ';
			$contentStr .= '\'' . $contentTypeArray[$i] . '\', ';
		}
		$caseStr .= 'ELSE 100 ';		// デフォルトでないメインコンテンツ編集ウィジェットは後にする
		$caseStr .= 'END AS contentno';
		$contentStr = rtrim($contentStr, ', ');
		
		$queryStr  = 'SELECT DISTINCT pd_id, wd_id, wd_name, wd_type, wd_content_info, wd_content_name, ls_value, ' . $caseStr . ' FROM _page_def ';
		$queryStr .=   'LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .=   'LEFT JOIN _language_string ON wd_type = ls_id AND ls_type = 2 AND ls_language_id = ? ';	// コンテンツ種別名
		$queryStr .= 'WHERE pd_set_id = ? ';
		$queryStr .=   'AND pd_id in (' . $pageStr . ') ';
		//$queryStr .=   'AND pd_visible = true ';			// ウィジェットは表示中に限定しない
		$queryStr .=   'AND wd_deleted = false ';			// ウィジェットは削除されていない
		$queryStr .=   'AND wd_active = true ';				// 一般ユーザが実行可能かどうか
		$queryStr .=   'AND (pd_sub_id = \'\' OR pg_active = true) ';		// グローバル属性ウィジェットか公開中のページ上のウィジェット
//		$queryStr .=   'AND wd_edit_content = true ';			// ##### メインウィジェットに限定しない #####
		$queryStr .=   'AND wd_type in (' . $contentStr . ') ';	// ##### パラメータのコンテンツタイプに限定 #####
//		$queryStr .=   'AND wd_type != \'\' ';
//		$queryStr .=   'AND wd_use_instance_def = false ';		// インスタンス定義を使用しないウィジェットをメインコンテンツ編集ウィジェットとする
		$queryStr .= 'ORDER BY pageno, contentno';
		$retValue = $this->selectRecords($queryStr, array($langId, $setId), $rows);
		return $retValue;
	}
	/**
	 * 画面配置している主要コンテンツウィジェット、主要機能ウィジェットを取得
	 *
	 * @param string $langId			言語ID
	 * @param array $pageIdArray		ページID
	 * @param array $contentTypeArray    コンテンツタイプ
	 * @param array  $rows				取得レコード
	 * @param int    $setId				定義セットID
	 * @return							true=取得、false=取得せず
	 */
	function getContentWidgetOnPage($langId, $pageIdArray, $contentTypeArray, &$rows, $setId = 0)
	{
		// CASE文作成
		$caseStr = 'CASE pd_id ';
		$pageStr = '';
		for ($i = 0; $i < count($pageIdArray); $i++){
			$caseStr .= 'WHEN \'' . $pageIdArray[$i] . '\' THEN ' . $i . ' ';
			$pageStr .= '\'' . $pageIdArray[$i] . '\', ';
		}
		$caseStr .= 'END AS pageno, ';
		$pageStr = rtrim($pageStr, ', ');
		
		$caseStr .= 'CASE wd_content_type ';
		$contentStr = '';
		for ($i = 0; $i < count($contentTypeArray); $i++){
			$caseStr .= 'WHEN \'' . $contentTypeArray[$i] . '\' THEN ' . $i . ' ';
			$contentStr .= '\'' . $contentTypeArray[$i] . '\', ';
		}
		$caseStr .= 'ELSE 100 ';		// 指定外
		$caseStr .= 'END AS contentno ';
		$contentStr = rtrim($contentStr, ', ');
		
		$queryStr  = 'SELECT DISTINCT pd_id, wd_id, wd_name, wd_content_type, wd_content_info, wd_content_name, ls_value, ' . $caseStr . ' FROM _page_def ';
		$queryStr .=   'LEFT JOIN _widgets ON pd_widget_id = wd_id AND wd_deleted = false ';
		$queryStr .=   'LEFT JOIN _page_id ON pd_sub_id = pg_id AND pg_type = 1 ';// ページサブID
		$queryStr .=   'LEFT JOIN _language_string ON wd_type = ls_id AND ls_type = 2 AND ls_language_id = ? ';	// コンテンツ種別名
		$queryStr .= 'WHERE pd_set_id = ? ';
		$queryStr .=   'AND pd_id in (' . $pageStr . ') ';
		//$queryStr .=   'AND pd_visible = true ';			// ウィジェットは表示中に限定しない
		$queryStr .=   'AND wd_deleted = false ';			// ウィジェットは削除されていない
		$queryStr .=   'AND wd_active = true ';				// 一般ユーザが実行可能かどうか
		$queryStr .=   'AND (pd_sub_id = \'\' OR pg_active = true) ';		// グローバル属性ウィジェットか公開中のページ上のウィジェット
//		$queryStr .=   'AND wd_edit_content = true ';			// ##### メインウィジェットに限定しない #####
		$queryStr .=   'AND wd_content_type in (' . $contentStr . ') ';	// コンテンツタイプに主要コンテンツ、主要機能がある場合
//		$queryStr .=   'AND wd_type != \'\' ';
//		$queryStr .=   'AND wd_use_instance_def = false ';		// インスタンス定義を使用しないウィジェットをメインコンテンツ編集ウィジェットとする
		$queryStr .= 'ORDER BY pageno, contentno';
		$retValue = $this->selectRecords($queryStr, array($langId, $setId), $rows);
		return $retValue;
	}
}
?>
