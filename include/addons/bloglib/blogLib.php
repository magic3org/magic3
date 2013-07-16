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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: blogLib.php 3629 2010-09-25 01:22:57Z fishbone $
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
}
?>
