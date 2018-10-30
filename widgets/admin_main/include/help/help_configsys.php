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
 * @copyright  Copyright 2006-2018 Magic3 Project.
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
				'body' =>	$this->_('If you use SSL on admin access point or front access point, you can set another url for SSL. If you set blank for the url, the system url which is changed http to https is used.')		// 管理画面またはフロント画面でSSLを使用する場合にSSL専用の別のURLが設定可能です。空に設定した場合はシステムのルートURLをhttpsに変更したURLが使用されます。
			),
			'configsys_access_point_in_public' => array(
				'title' =>	'アクセスポイント公開状況',
				'body' =>	'アクセスポイントごとの公開状況を設定します。「公開」にチェックが入っていない場合はアクセス不可のメッセージ画面が表示されます。'
			),
			'configsys_admin_mode' => array(
				'title' =>	'管理画面モード',
				'body' =>	'管理画面の運用モードを変更して、メインメニューに表示される項目を変更します。この画面を再表示させるにはダッシュボード画面上でESCキーを押した後に表示されるメニューからアクセスします。'
			),
			'configsys_access_point' => array(	
				'title' =>	'アクセスポイント',
				'body' =>	'使用するアクセスポイントを有効にします。'
			),
			'configsys_network' => array(
				'title' =>	'ネットワーク',	// ネットワーク
				'body' =>	'ネットワークに関するシステムの運用形態を設定します。<br />●イントラネット運用 ー Googleマップ等の外部インターネットサービスを使用しない場合はチェックを入れます。'
			),
			'configsys_admin_page' => array(
				'title' =>	'管理画面',
				'body' =>	'利用形態に合わせて管理画面の表示方法を変更します。<br />●マルチデバイス最適化 ― 管理画面をスマートフォン等の画面サイズが小さいデバイス用の表示に自動切り替えします。<br />●詳細設定表示(開発モード) ― システム開発時に使用します。'
			),
			'configsys_front_page' => array(	
				'title' =>	'フロント画面',
				'body' =>	'フロント画面の設定です。<br />●jQueryバージョン ― 使用するjQueryのバージョンを指定します。テンプレート側で組み込みでjQueryを読み込みシステム内部のjQueryを読み込まない場合は「外部のjQueryを使用」にチェックを入れます。<br />●システム画面テンプレート ― アクセス不可等のメッセージを表示する画面で使用するテンプレートです。'
			),
			'configsys_menu' => array(
				'title' =>	'メニュー',
				'body' =>	'フロントで表示するメニューの単階層、多階層の区別を設定します。管理画面がメニュー階層に合わせて変更されます。'
			),
			'configsys_upload' => array(
				'title' =>	'アップロード',
				'body' =>	'画像ファイルのアップロードについて設定します。<br />「画像のリサイズ」にチェックを入れると範囲内に収まるように画像を自動的にリサイズします。'
			)
		);
		return $helpData;
	}
}
?>
