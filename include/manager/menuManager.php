<?php
/**
 * メニューマネージャー
 *
 *  メニューに関する情報の仲介をする
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class MenuManager extends Core
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
	 * メニュー項目を取得
	 *
	 * @param string $menuId		メニュー識別ID
	 * @param string $parentId		親項目ID(0～)
	 * @param string $langId		言語ID
	 * @param timestamp $now		現在日時
	 * @param array  $rows			取得レコード
	 * @return						true=取得、false=取得せず
	 */
	function getChildMenuItems($menuId, $parentId, $langId, $now, &$rows)
	{
		$ret = $this->_db->getChildMenuItems($menuId, $parentId, $langId, $now, $rows);
		return $ret;
	}
}
?>
