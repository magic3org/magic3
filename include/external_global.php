<?php
/**
 * 外部起動用グローバル定義ファイル
 *
 * 機能：CRONジョブの実行などの外部起動によりシステムを起動する。
 *       起動するジョブタイプのジョブ起動制御ファイルが存在する場合のみシステム起動まで進む。
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// ########## ジョブ起動制御ファイルによるスクリプト実行制御 ##########
// 実行ジョブタイプを取得
$execFilePath = $argv[0];		// 実行スクリプトファイルパス
$jobTypeId = basename(dirname($execFilePath));

// ジョブ起動制御ファイルをチェック
$jobFlagFile = dirname(__FILE__) . '/jobcontrol/' . $jobTypeId;
if (!file_exists($jobFlagFile)) exit(0);			// ジョブ監視ファイルがない場合は正常終了

// ########## Magic3アクセス制御(開始) ##########
require_once(dirname(__FILE__) . '/global.php');

if (!$gAccessManager->isExternalPermittedUser()){		// rootのみアクセスを許可
	echo 'Access error: access denied.';

	$gOpeLogManager->writeUserAccess(__METHOD__, '外部起動インターフェイスへの不正なアクセスを検出しました。root以外のユーザ。', 3001, 'アクセスをブロックしました。実行ファイル=' . $execFilePath);
	exit(0);
}
// ########## Magic3アクセス制御(終了) ##########
?>
