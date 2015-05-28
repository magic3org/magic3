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

class member_mainDb extends BaseDb
{
	/**
	 * ユーザリスト取得
	 *
	 * @param int		$limit		取得する項目数
	 * @param int		$page		取得するページ(1～)
	 * @param array		$keywords	検索キーワード
	 * @param function  $callback	コールバック関数
	 * @return						なし
	 */
	function getMemberList($limit, $page, $keywords, $callback)
	{
		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr  = 'SELECT * FROM _login_user LEFT JOIN _login_log ON lu_id = ll_user_id ';
		$queryStr .=   'WHERE lu_deleted = false ';// 削除されていない
		$queryStr .=     'AND lu_user_type <= ? '; $params[] = UserInfo::USER_TYPE_NORMAL;			// 一般ユーザ以下のユーザのみ対象とする
		
		// タイトル、本文、ユーザ定義フィールドを検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (lu_account LIKE \'%' . $keyword . '%\' ';		// アカウント
				$queryStr .=    'OR lu_name LIKE \'%' . $keyword . '%\') ';			// 名前
			}
		}
		
		$queryStr .=   'ORDER BY lu_regist_dt DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
	/**
	 * ユーザ総数取得
	 *
	 * @param array		$keywords	検索キーワード
	 * @return int					総数
	 */
	function getMemberListCount($keywords)
	{
		$params = array();
		$queryStr  = 'SELECT * FROM _login_user LEFT JOIN _login_log ON lu_id = ll_user_id ';
		$queryStr .=   'WHERE lu_deleted = false ';// 削除されていない
		$queryStr .=     'AND lu_user_type <= ? '; $params[] = UserInfo::USER_TYPE_NORMAL;			// 一般ユーザ以下のユーザのみ対象とする
		
		// タイトル、本文、ユーザ定義フィールドを検索
		if (!empty($keywords)){
			for ($i = 0; $i < count($keywords); $i++){
				$keyword = addslashes($keywords[$i]);// 「'"\」文字をエスケープ
				$queryStr .=    'AND (lu_account LIKE \'%' . $keyword . '%\' ';		// アカウント
				$queryStr .=    'OR lu_name LIKE \'%' . $keyword . '%\') ';			// 名前
			}
		}
		
		return $this->selectRecordCount($queryStr, $params);
	}
}
?>
