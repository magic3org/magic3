<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: default_contentBaseWidgetContainer.php 5134 2012-08-23 05:57:47Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getWidgetContainerPath('default_content') . '/default_contentCommonDef.php');
require_once($gEnvManager->getWidgetDbPath('default_content') . '/default_contentDb.php');

class default_contentBaseWidgetContainer extends BaseWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// 汎用コンテンツ定義値
	protected $_langId;			// 現在の言語
	protected $_userId;			// 現在のユーザ
	protected $_isMultiLang;			// 多言語対応画面かどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 代替処理用のウィジェットIDを設定
		$this->setDefaultWidgetId(default_contentCommonDef::CONTENT_WIDGET_ID);
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new default_contentDb();
		
		// 汎用コンテンツ定義を読み込む
		if (!isset(self::$_configArray)) self::$_configArray = default_contentCommonDef::loadConfig(self::$_mainDb);
		
		$this->_langId = $this->gEnv->getCurrentLanguage();			// 現在の言語
		$this->_userId = $this->gEnv->getCurrentUserId();		// 現在のユーザ
		$this->_isMultiLang = $this->gEnv->isMultiLanguageSite();			// 多言語対応画面かどうか
	}
}
?>
