<?php
/**
 * 新着情報クラス
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
require_once(dirname(__FILE__) . '/searchLibDb.php');

class searchLib
{
	private $db;				// DB接続オブジェクト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new searchLibDb();
	}
	/**
	 * コンテンツを検索
	 *
	 * @param string $contentType		コンテンツタイプ
	 * @param string $contentId			コンテンツID
	 * @param string					パラメータ文字列
	 */
	private function _searchContent($contentType, $contentId)
	{
		$param = '';
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:				// 汎用コンテンツ
				$param = M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_PRODUCT:				// 商品情報(Eコマース)
				$param = M3_REQUEST_PARAM_PRODUCT_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_BBS:					// BBS
				$param = M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_BLOG:				// ブログ
				$param = M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_WIKI:				// wiki
				$param = $contentId;
				break;
			case M3_VIEW_TYPE_USER:				// ユーザ作成コンテンツ
				$param = M3_REQUEST_PARAM_ROOM_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_EVENT:				// イベント情報
				$param = M3_REQUEST_PARAM_EVENT_ID . '=' . $contentId;
				break;
			case M3_VIEW_TYPE_PHOTO:				// フォトギャラリー
				$param = M3_REQUEST_PARAM_PHOTO_ID . '=' . $contentId;
				break;
		}
		return $url . '?' . $param;
	}
}
?>
