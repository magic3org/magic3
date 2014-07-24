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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class wiki_updateDb extends BaseDb
{
	/**
	 * 最近の更新順にWikiコンテンツを取得
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param function	$callback			コールバック関数
	 * @return 			なし
	 */
	function getUpdatePages($limit, $page, $callback)
	{
		$type = '';		// ページタイプ
		
		// 回避ページ
		$escapePages = array(	'RecentChanges',	// Modified page list
								'RecentDeleted', 	// Removeed page list
								'InterWikiName',	// Set InterWiki definition here
								'MenuBar');       	// メニューバー

		$offset = $limit * ($page -1);
		if ($offset < 0) $offset = 0;
		
		$params = array();
		$queryStr = 'SELECT * FROM wiki_content ';
		$queryStr .=  'WHERE wc_deleted = false ';		// 削除されていない
		$queryStr .=    'AND wc_type = ? '; $params[] = $type;
		
		// 回避ページ
		for ($i = 0; $i < count($escapePages); $i++){
			$queryStr .=    'AND wc_id != ? '; $params[] = $escapePages[$i];
		}
		$keyword = addslashes(':');// 「:xxxxx」(設定ページ)の「'"\」文字をエスケープ
		$queryStr .=    'AND wc_id NOT LIKE \'' . $keyword . '%\' ';
					
		$queryStr .=  'ORDER BY wc_content_dt DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
		$this->selectLoop($queryStr, $params, $callback);
	}
}
?>
