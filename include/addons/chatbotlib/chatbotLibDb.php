<?php
/**
 * DBクラス
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
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class chatbotLibDb extends BaseDb
{
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
		global $gEnvManager;
		global $gAccessManager;
		
		$widgetId = $gEnvManager->getCurrentWidgetId();
		$clientId = $gAccessManager->getClientId();
		$logSerial = $gEnvManager->getCurrentAccessLogSerial();	// 現在のアクセスログシリアル番号
		
		// トランザクション開始
		$this->startTransaction();
		
		// データを追加
		$queryStr  = 'INSERT INTO chatbot_log ';
		$queryStr .=   '(cb_message, cb_bot_message, cb_dest_message, cb_type, cb_widget_id, cb_client_id, cb_access_log_serial, cb_dt) ';
		$queryStr .= 'VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?)';
		$this->execStatement($queryStr, array($message, $botMessage, $destMessage, $type, $widgetId, $clientId, $logSerial, $registDt));

		// トランザクション終了
		return $this->endTransaction();
	}
}
?>
