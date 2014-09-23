<?php
/**
 * Eコマースメール連携クラス
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
require_once(dirname(__FILE__) . '/blogLibDb.php');

class blogLib
{
	private $db;	// DB接続オブジェクト
	private $blogId = '';	// ブログID
	private $templateId = '';	// テンプレートID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new blogLibDb();
	}
	/**
	 * 初期化
	 *
	 * @return なし
	 */
	function _initData()
	{
		global $gEnvManager;
		global $gRequestManager;
		static $init = false;
		
		if ($init) return;
		
		$langId = $gEnvManager->getDefaultLanguage();
	
		// 記事IDからブログID、テンプレートIDを取得
		$entryId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		if (empty($entryId)) $entryId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT);		// 略式ブログ記事ID
		if (!empty($entryId)){
			$ret = $this->db->getEntryItem($entryId, $langId, $row);
			if ($ret){
				$this->templateId = $row['bl_template_id'];
				$this->blogId = $row['bl_id'];;	// ブログID
			}
		} else {
			// ブログIDからテンプレートIDを取得
			$blogId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);
			if (empty($blogId)) $blogId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ID_SHORT);		// 略式ブログID
			if (!empty($blogId)){
				$ret = $this->db->getBlogInfoById($blogId, $row);
				if ($ret){
					$this->templateId = $row['bl_template_id'];
					$this->blogId = $row['bl_id'];;	// ブログID
				}
			}
		}
		$init = true;		// 初期化完了
	}
	/**
	 * URLパラメータからオプションのテンプレートを取得
	 *
	 * @return string						テンプレートID
	 */
	function getOptionTemplate()
	{
		// 初期化
		$this->_initData();
		
		return $this->templateId;
	}
	/**
	 * 現在のブログIDを取得
	 *
	 * @return string					ブログID
	 */
	function getBlogId()
	{
		// 初期化
		$this->_initData();
		
		return $this->blogId;
	}
	/**
	 * ブログ定義値を取得
	 *
	 * @param string $id				定義ID
	 * @return string					定義値
	 */
	function getConfig($id)
	{
		static $configArray;
		
		// ブログ定義を読み込む
		if (!isset($configArray)) $configArray = $this->loadConfig($this->db);
		
		return isset($configArray[$id]) ? $configArray[$id] : '';
	}
	/**
	 * ブログ定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	static function loadConfig($db)
	{
		$retVal = array();

		// ブログ定義値を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['bg_id'];
				$value = $rows[$i]['bg_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
}
?>
