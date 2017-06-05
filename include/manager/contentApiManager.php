<?php
/**
 * コンテンツAPIマネージャー
 *
 * 主コンテンツ取得API
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class ContentApiManager extends Core
{
	private $contentType;			// コンテンツタイプ
	const CF_DEFAULT_CONTENT_TYPE = 'default_content_type';		// デフォルトコンテンツタイプ取得用
	const DEFAULT_CONTENT_TYPE = 'blog';		// デフォルトコンテンツタイプのデフォルト値
	// アドオンオブジェクト作成用
	const ADDON_OBJ_ID_CONTENT	= 'contentlib';
	const ADDON_OBJ_ID_BLOG		= 'bloglib';
	const ADDON_OBJ_ID_PRODUCT	= 'eclib';

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gPageManager;
		global $gSystemManager;
		
		// 親クラスを呼び出す
		parent::__construct();
		
		// コンテンツタイプを取得
		$this->contentType = $gSystemManager->getSystemConfig(self::CF_DEFAULT_CONTENT_TYPE);// デフォルトコンテンツタイプ
		if (empty($this->contentType)) $this->contentType = self::DEFAULT_CONTENT_TYPE;
			
		// 現在のページにコンテンツタイプがある場合は取得
		$contentType = $gPageManager->getContentType();
		if (!empty($contentType)){
			// メインコンテンツタイプのみ対象とする
			$mainContentTypes = $gPageManager->getMainContentTypes();
			if (in_array($contentType, $mainContentTypes)) $this->contentType = $contentType;
		}
	}
	/**
	 * 対象のコンテンツタイプのアドオンオブジェクトを取得
	 *
	 * @return object 		アドオンオブジェクト
	 */
	function _getAddonObj()
	{
		global $gInstanceManager;
		
		switch ($this->contentType){
			case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
				$addonObj = $gInstanceManager->getObject(self::ADDON_OBJ_ID_CONTENT);
//				$this->db->getContentList($contentType, $this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, 0/*降順*/, array($this, 'contentLoop'));
				break;
			case M3_VIEW_TYPE_PRODUCT:	// 製品
				$addonObj = $gInstanceManager->getObject(self::ADDON_OBJ_ID_PRODUCT);
//				$this->db->getProductList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
				break;
			case M3_VIEW_TYPE_BBS:	// BBS
				break;
			case M3_VIEW_TYPE_BLOG:	// ブログ
				$addonObj = $gInstanceManager->getObject(self::ADDON_OBJ_ID_BLOG);
//				$this->db->getEntryList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
				break;
			case M3_VIEW_TYPE_WIKI:	// Wiki
				break;
			case M3_VIEW_TYPE_USER:	// ユーザ作成コンテンツ
				break;
			case M3_VIEW_TYPE_EVENT:	// イベント
				break;
			case M3_VIEW_TYPE_PHOTO:	// フォトギャラリー
				break;
		}
		
		return $addonObj;
	}
	/**
	 * 一覧を取得
	 *
	 * @param string	$langId				言語
	 * @param int		$limit				取得する項目数
	 * @param int		$pageNo				取得するページ番号(1～)
	 * @param array     $rows				レコード
	 * @return bool							取得 = true, 取得なし= false
	 */
	function getDefaultList($langId, $limit, $pageNo, &$rows)
	{
		$addonObj = $this->_getAddonObj();
		$retValue = $addonObj->getList($langId, $limit, $pageNo, $rows);
		return $retValue;
	}
}
?>
