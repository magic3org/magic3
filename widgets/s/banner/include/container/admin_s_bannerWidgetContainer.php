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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_s_bannerWidgetContainer.php 5867 2013-03-28 04:04:02Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('banner3') . '/admin_banner3WidgetContainer.php');

class admin_s_bannerWidgetContainer extends admin_banner3WidgetContainer
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		default_bannerCommonDef::$_deviceType		= 2;						// デバイスタイプ(スマートフォン)
		default_bannerCommonDef::$_deviceTypeName	= 'スマートフォン';	// デバイスタイプ名
		
		// 親クラスを呼び出す
		parent::__construct();
	}
}
?>
