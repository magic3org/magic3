<?php
/**
 * 汎用コンテンツ情報取得クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: contentLib.php 5897 2013-04-02 01:12:56Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(dirname(__FILE__) . '/contentLibDb.php');

class contentLib
{
	private $db;	// DB接続オブジェクト
	private $templateId;	// テンプレートID
	private $configArray;	// 汎用コンテンツ定義値
	const CF_USE_CONTENT_TEMPLATE	= 'use_content_template';		// コンテンツ単位のテンプレート設定を行うかどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new contentLibDb();
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
		$deviceType = $gEnvManager->getCurrentPageDeviceType();
	
		// コンテンツタイプを取得
		switch ($deviceType){
			case 1:		// 携帯
				$contentType = 'mobile';			// コンテンツタイプ
				break;
			case 2:		// スマートフォン
				$contentType = 'smartphone';			// コンテンツタイプ
				break;
			case 0:		// PC
			default:
				$contentType = '';
		}
		
		// DB定義値を取得
		$this->configArray = $this->_loadConfig($contentType, $this->db);
		
		// URLからコンテンツIDを取得
		$contentId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID);
		if (empty($contentId)) $contentId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID_SHORT);		// 略式汎用コンテンツID
		if (!empty($contentId)){
			$ret = $this->db->getContent($contentType, $contentId, $langId, $row);
			if ($ret){
				if ($this->configArray[self::CF_USE_CONTENT_TEMPLATE]) $this->templateId = $row['cn_template_id'];
			}
		}
		$init = true;		// 初期化完了
	}
	/**
	 * 汎用コンテンツ定義値をDBから取得
	 *
	 * @param string $contentType	コンテンツタイプ(空文字列=PC、mobile=携帯、smartphone=スマートフォン)
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	function _loadConfig($contentType, $db)
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
		$ret = $this->db->getAllConfig($contentType, $rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['ng_id'];
				$value = $rows[$i]['ng_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * URLパラメータからオプションのテンプレートを取得
	 *
	 * @return string						テンプレートID
	 */
	function getTemplate()
	{
		// 初期化
		$this->_initData();
		
		return $this->templateId;
	}
	/**
	 * 汎用コンテンツ定義値を取得
	 *
	 * @param string $key			定義キー。空の場合は全データ取得
	 * @return array,string			汎用コンテンツ定義値
	 */
	function getConfig($key = '')
	{
		// 初期化
		$this->_initData();
		
		if (empty($key)){
			return $this->configArray;
		} else {
			return $this->configArray[$key];
		}
	}
}
?>
