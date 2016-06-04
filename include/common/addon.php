<?php
/**
 * Magic3コアクラス
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class Addon extends Core
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
	 * ユーザ操作運用ログ出力とイベント処理
	 *
	 * 以下の状況で運用ログメッセージを出力するためのインターフェイス
	 * ユーザの通常の操作で記録すべきもの
	 * 例) コンテンツの更新等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param array  $eventParam	イベント処理用パラメータ(ログに格納しない)
	 * @return なし
	 */
	public function writeUserInfoEvent($method, $msg, $code = 0, $msgExt = '', $eventParam = array())
	{
		$this->gOpeLog->writeUserInfo($method, $msg, $code, $msgExt, '', '', false, $eventParam);
	}
}
?>
