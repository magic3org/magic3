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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @link       http://magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_mainConnectorBaseWidgetContainer extends BaseAdminWidgetContainer
{
	const MAX_SERVER_LOAD_AVERAGE = 30;		// サーバの最大付加状況(%)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * サーバの負荷状況をチェック
	 *
	 * @return int					負荷状況を%で返す。0の場合は問題なし。
	 */
	function _checkServerLoadAverage()
	{
		$load = sys_getloadavg();
		$coreCount = shell_exec('nproc');	// プロセッサ数取得
		
		for ($i = 0; $i < 3; $i++){
			$avg = $load[$i] / $coreCount * 100;
			
			// 最大負荷よりも大きい場合は負荷値を返す
			if ($avg > self::MAX_SERVER_LOAD_AVERAGE) return $avg;
		}
		
		return 0;
	}
}
?>
