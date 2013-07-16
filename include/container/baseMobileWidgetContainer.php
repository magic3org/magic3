<?php
/**
 * 携帯用ウィジェットベースクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: baseMobileWidgetContainer.php 3507 2010-08-18 11:04:35Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class BaseMobileWidgetContainer extends BaseWidgetContainer
{
	protected $agent;		// 携帯機器、機種判定用オブジェクト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->agent = $this->gInstance->getMobileAgent();
	}
	/**
	 * 指定URLへリダイレクト
	 *
	 * 画面を遷移させたとき、ドコモ携帯端末でダイアログ(サイトが移動しました(301))が出ないようにするオプションを付加。
	 *
	 * @param string $url			遷移先URL。未指定の場合は現在のスクリプト。URLでないときは、現在のスクリプトに付加。
	 * @return 						なし
	 */
	function redirect($url = '')
	{
		$this->gPage->redirect($url, true);
	}
}
?>
