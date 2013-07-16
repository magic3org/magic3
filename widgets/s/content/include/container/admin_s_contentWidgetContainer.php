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
 * @version    SVN: $Id: admin_s_contentWidgetContainer.php 5048 2012-07-20 09:59:14Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('default_content') . '/admin_default_contentWidgetContainer.php');

class admin_s_contentWidgetContainer extends admin_default_contentWidgetContainer
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// ##### 定義値を変更 #####
		// コンテンツタイプ、デバイスを設定
		default_contentCommonDef::$_contentType		= 'smartphone';			// コンテンツタイプ
		default_contentCommonDef::$_deviceType		= 2;						// デバイスタイプ(スマートフォン)
		default_contentCommonDef::$_deviceTypeName	= 'スマートフォン';	// デバイスタイプ名
		default_contentCommonDef::$_viewContentType = 's:content';		// 参照数カウント用コンテンツタイプ(スマートフォン用汎用コンテンツ)
		// DB定義値
//		default_contentCommonDef::$CF_USE_PASSWORD		= 's:use_password';		// パスワードアクセス制御
//		default_contentCommonDef::$CF_PASSWORD_CONTENT	= 's:password_content';			// パスワード画面コンテンツ
		
		// 親クラスを呼び出す
		parent::__construct();
	}
}
?>
