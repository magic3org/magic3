<?php
/**
 * Eコマースメール連携クラス
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
require_once(dirname(__FILE__) . '/chatbotLibDb.php');

class chatbotLib
{
	private $db;	// DB接続オブジェクト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new chatbotLibDb();
	}
	/**
	 * チャットボット通話を記録
	 *
	 * @param string $type			チャットボットタイプ(luis=Microsoft LUIS,repl=DOCOMO Repl-AI)
	 * @param string $message		対話入力メッセージ
	 * @param string $botMessage	チャットボット応対メッセージ
	 * @param string $destMessage	サーバ処理済み応対メッセージ
	 * @param timestamp $registDt	登録日時
	 * @return bool					true=成功、false=失敗
	 */
	public function writeChatLog($type, $message, $botMessage, $destMessage, $registDt)
	{
		// DBに登録
		$ret = $this->db->writeChatLog($type, $message, $botMessage, $destMessage, $registDt);
		return $ret;
	}
}
?>
