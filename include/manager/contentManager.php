<?php
/**
 * コンテンツマネージャー
 *
 *  コンテンツのインポート、エクスポート等のデータ操作を行う
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class ContentManager extends _Core
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	
	/**
	 * テンプレートのページコンテンツをインポートする
	 *
	 * @param string $pageId		ページID
	 * @param string $pageSubId		ページサブID
	 * @param string $templateId	テンプレートID
	 * @return 		bool			true=成功、false=失敗
	 */
	function importPageContentFromTemplate($pageId, $pageSubId, $templateId)
	{
		
	}
}
?>
