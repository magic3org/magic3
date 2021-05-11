<?php
/**
 * サイト定義ファイル
 *
 * 機能：システムを稼動するために最小限必要な設定ファイル。サイト固有の情報を設定する。
 *       すべてのソースコードの中で、唯一このファイルのみがシステムから編集される。
 *       インストール時にインストーラが設定を行うが、ユーザが直接編集することも可能。
 *       すべての項目を設定する必要がある。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: siteDef.php 1508 2009-02-10 10:06:04Z fishbone $
 * @link       http://www.magic3.org
 */
// #################### 設定必須項目 ####################
// ***************** URLの設定 *****************
// システムのルートへのURLをここで設定する
// 設定しない場合は、global.phpでデフォルトが設定される
// デフォルト値: http://サーバ名/ルートディレクトリ名
// 設定例)
//define('M3_SYSTEM_ROOT_URL',	'http://magic3.org/magic3');
define('M3_SYSTEM_ROOT_URL', '');

// ***************** DB接続設定 *****************
// 設定例)
// define('M3_DB_CONNECT_DSN',	'mysql:host=localhost;dbname=testdb');				// ローカルの「testdb」に接続(MySQL)
// define('M3_DB_CONNECT_DSN',	'mysql:host=127.0.0.1;port=3306;dbname=testdb');	// ポートを指定して「testdb」に接続(MySQL)
// define('M3_DB_CONNECT_DSN',	'pgsql:host=localhost;dbname=testdb');				// ローカルの「testdb」に接続(PostgreSQL)
define('M3_DB_CONNECT_DSN',			'');
define('M3_DB_CONNECT_USER',		'');	// 接続ユーザ
define('M3_DB_CONNECT_PASSWORD',	'');	// パスワード
?>
