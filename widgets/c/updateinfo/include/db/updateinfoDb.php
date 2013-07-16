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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: updateinfoDb.php 2692 2009-12-15 09:42:40Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class updateinfoDb extends BaseDb
{
	/**
	 * 新着情報を追加
	 *
	 * @param string $type		コンテンツタイプ
	 * @param string $serverId	サーバID
	 * @param string $registDt	登録情報を有効にする日時
	 * @param string $name		コンテンツ名
	 * @param string $link		コンテンツへのリンク
	 * @param string $contentDt	コンテンツ更新日時
	 * @param string $message	表示メッセージ
	 * @param string $siteName	サイト名
	 * @param string $siteLink	サイトリンク
	 * @return					true=成功、false=失敗
	 */
	function addNews($type, $serverId, $registDt, $name, $link, $contentDt, $message, $siteName, $siteLink)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		$this->startTransaction();

		$queryStr = 'INSERT INTO news (';
		$queryStr .=  'nw_type, ';
		$queryStr .=  'nw_server_id, ';
		$queryStr .=  'nw_regist_dt, ';
		$queryStr .=  'nw_name, ';
		$queryStr .=  'nw_link, ';
		$queryStr .=  'nw_content_dt, ';
		$queryStr .=  'nw_message, ';
		$queryStr .=  'nw_site_name, ';
		$queryStr .=  'nw_site_link, ';
		$queryStr .=  'nw_update_user_id, ';
		$queryStr .=  'nw_update_dt ';
		$queryStr .=  ') VALUES (';
		$queryStr .=  '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
		$queryStr .=  ')';
		$this->execStatement($queryStr, array($type, $serverId, $registDt, $name, $link, $contentDt, $message, $siteName, $siteLink, $userId, $now));
				
		// トランザクション確定
		$ret = $this->endTransaction();
		return $ret;
	}
}
?>
