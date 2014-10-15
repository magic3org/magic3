<?php
/**
 * ヘルプリソースファイル
 * index.php
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
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_configsys extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## システム情報 ##########
		$helpData = array(
			'configsys_site_status' => array(	
				'title' =>	$this->_('Site Status'),	// サイトの状態
				'body' =>	$this->_('When it is closed, all access points show the maintenance page.')		// 非公開の場合、全アクセスポイントがメンテナンス画面に切り替わります
			),
			'configsys_ssl_url' => array(	
				'title' =>	$this->_('Root URL of Shared SSL'),	// 共有SSLのルートURL
				'body' =>	$this->_('If you use SSL on admin access point or general access point, you can set another url for SSL. If you set blank for the url, the system url which is changed http to https is used.')		// 管理画面または一般画面でSSLを使用する場合にSSL専用の別のURLが設定可能です。空に設定した場合はシステムのルートURLをhttpsに変更したURLが使用されます。
			)
		);
		return $helpData;
	}
/*
// ########## システム情報 ##########
$HELP['configsys']['title'] = 'システム基本設定';
$HELP['configsys']['body'] = 'システムの動作に関する基本設定を行います。';
$HELP['configsys_connect_server_url']['title'] = 'ポータルサーバのURL';
$HELP['configsys_connect_server_url']['body'] = 'ポータルサーバに接続する場合の接続先URLです。Magic3がインストールされているURLを指定します。';
*/
}
?>
