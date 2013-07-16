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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: quizkDb.php 1934 2009-05-28 11:09:45Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getDbPath() . '/baseDb.php');

class quizkDb extends BaseDb
{
	/**
	 * 設定値を取得
	 *
	 * @param string $key		キーとなる項目値
	 * @return string $value	値
	 */
	function getConfig($key)
	{
		$retValue = '';
		$queryStr = 'SELECT qc_value FROM quiz_config ';
		$queryStr .=  'WHERE qc_id  = ?';
		$ret = $this->selectRecord($queryStr, array($key), $row);
		if ($ret) $retValue = $row['qc_value'];
		return $retValue;
	}
	/**
	 * 設定値を更新
	 *
	 * @param string $key		キーとなる項目値
	 * @param string $value		値
	 * @return					true = 正常、false=異常
	 */
	function updateConfig($key, $value)
	{
		// トランザクションスタート
		$this->startTransaction();
		
		// データの確認
		$queryStr = 'SELECT qc_value FROM quiz_config ';
		$queryStr .=  'WHERE qc_id  = ?';
		$ret = $this->isRecordExists($queryStr, array($key));
		if ($ret){
			$queryStr = "UPDATE quiz_config SET qc_value = ? WHERE qc_id = ?";
			$this->execStatement($queryStr, array($value, $key));
		} else {
			$queryStr = "INSERT INTO quiz_config (qc_id, qc_value) VALUES (?, ?)";
			$this->execStatement($queryStr, array($key, $value));
		}
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 定義セットIDリスト取得
	 *
	 * @param function $callback	コールバック関数
	 * @return						なし
	 */
	function getAllSetId($callback)
	{
		$queryStr  = 'SELECT * FROM quiz_set_id ';
		$queryStr .=   'WHERE qs_deleted = false ';		// 未削除
		$queryStr .=   'ORDER BY qs_index';
		$this->selectLoop($queryStr, array(), $callback);
	}
	/**
	 * 定義セットID情報取得
	 *
	 * @param string	$id				定義セットID
	 * @param array  	$row			取得レコード
	 * @return							true=取得、false=取得せず
	 */
	function getSetId($id, &$row)
	{
		$queryStr  = 'SELECT * FROM quiz_set_id ';
		$queryStr .=   'WHERE qs_deleted = false ';		// 未削除
		$queryStr .=   'AND qs_id  = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		return $ret;
	}
	/**
	 * 定義セットIDを更新
	 *
	 * @param string	$id				定義セットID
	 * @param date		$totalDate		集計日付
	 * @return							true = 正常、false=異常
	 */
	function updateSetId($id, $totalDate)
	{
		// トランザクションスタート
		$this->startTransaction();
		
		// データの確認
		$queryStr = 'SELECT * FROM quiz_set_id ';
		$queryStr .=  'WHERE qs_id  = ? ';
		$ret = $this->selectRecord($queryStr, array($id), $row);
		if ($ret){
			$queryStr  = 'UPDATE quiz_set_id ';
			$queryStr .=   'SET qs_total_date = ? ';
			$queryStr .=   'WHERE qs_id = ?';
			$ret = $this->execStatement($queryStr, array($totalDate, $id));			
		} else {
			$queryStr = 'INSERT INTO quiz_set_id (';
			$queryStr .=  'qs_id, ';
			$queryStr .=  'qs_total_date ';
			$queryStr .=  ') VALUES (';
			$queryStr .=  '?, ?';
			$queryStr .=  ')';
			$ret = $this->execStatement($queryStr, array($id, $totalDate));
		}
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * すべてのクイズ定義を取得
	 *
	 * @param string $setId			パターンセットID
	 * @param function	$callback	コールバック関数
	 * @return 			なし
	 */
	function getAllItems($setId, $callback)
	{
		$queryStr  = 'SELECT * FROM quiz_item_def ';
		$queryStr .=   'WHERE qd_deleted = false ';		// 未削除
		$queryStr .=     'AND qd_set_id = ? ';
		$queryStr .=   'ORDER BY qd_id';
		$this->selectLoop($queryStr, array($setId), $callback);
	}
	/**
	 * クイズ定義を取得
	 *
	 * @param string $setId		パターンセットID
	 * @param string $id		クイズ項目ID
	 * @param array  $row		取得レコード
	 * @return bool				true=取得、false=取得せず
	 */
	function getItem($setId, $id, &$row)
	{
		$queryStr  = 'SELECT * FROM quiz_item_def ';
		$queryStr .=   'WHERE qd_deleted = false ';		// 未削除
		$queryStr .=     'AND qd_set_id = ? ';
		$queryStr .=     'AND qd_id = ? ';
		$ret = $this->selectRecord($queryStr, array($setId, $id), $row);
		return $ret;
	}
	/**
	 * すべてのクイズ定義を削除
	 *
	 * @param string $setId			パターンセットID
	 * @return						true=成功、false=失敗
	 */
	function deleteAllItems($setId)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクションスタート
		$startTran = false;			// この関数でトランザクションを開始したかどうか
		if (!$this->isInTransaction()){
			$this->startTransaction();
			$startTran = true;
		}
		
		// レコードを削除
		$queryStr  = 'UPDATE quiz_item_def ';
		$queryStr .=   'SET qd_deleted = true, ';	// 削除
		$queryStr .=     'qd_update_user_id = ?, ';
		$queryStr .=     'qd_update_dt = ? ';
		$queryStr .=   'WHERE qd_set_id = ? AND qd_deleted = false';
		$ret = $this->execStatement($queryStr, array($userId, $now, $setId));

		// トランザクション確定
		if ($startTran) $ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * クイズ定義項目が存在しているかチェック
	 *
	 * @param string $setId			定義セットID
	 * @param string $id			ID
	 * @return bool					true=存在する、false=存在しない
	 */
	function isExistsItem($setId, $id)
	{
		$queryStr = 'SELECT * FROM quiz_item_def ';
		$queryStr .=  'WHERE qd_deleted = false ';		// 未削除
		$queryStr .=     'AND qd_set_id = ? ';
		$queryStr .=     'AND qd_id = ? ';
		return $this->isRecordExists($queryStr, array($setId, $id));
	}
	/**
	 * 次の問題を取得
	 *
	 * @param string $setId			定義セットID
	 * @param string $mobileId		携帯ID
	 * @param array  	$row		取得レコード
	 * @return bool					true=取得、false=取得せず
	 */
	function getNextQuestion($setId, $mobileId, &$row)
	{
		$queryStr  = 'SELECT * FROM quiz_item_def LEFT JOIN quiz_user_post ON qd_set_id = qp_set_id AND qd_id = qp_question_id AND qd_type = 0 AND qp_mobile_id = ? ';
		$queryStr .=   'WHERE qd_deleted = false ';		// 未削除
		$queryStr .=     'AND qd_set_id = ? ';
		$queryStr .=     'AND qd_type = 0 ';		// 問題
		$queryStr .=     'AND qd_visible = true ';		// 表示
		$queryStr .=     'AND qp_mobile_id IS NULL ';
		$queryStr .=   'ORDER BY qd_index';
		$ret = $this->selectRecord($queryStr, array($mobileId, $setId), $row);
		return $ret;
	}
	/**
	 * 問題数を取得
	 *
	 * @param string $setId			定義セットID
	 * @param bool $visibleOnly		表示項目のみかどうか
	 * @return int					問題数
	 */
	function getQuestionCount($setId, $visibleOnly = true)
	{
		$queryStr = 'SELECT * FROM quiz_item_def ';
		$queryStr .=  'WHERE qd_deleted = false ';		// 未削除
		$queryStr .=     'AND qd_set_id = ? ';
		$queryStr .=     'AND qd_visible = true ';
		return $this->selectRecordCount($queryStr, array($setId));
	}
	/**
	 * クイズ回答を取得
	 *
	 * @param string $setId			定義セットID
	 * @param array $answerIdArray	回答の配列
	 * @param array  	$rows		取得レコード
	 * @return bool					true=取得、false=取得せず
	 */
	function getAnswers($setId, $answerIdArray, &$rows)
	{
		if (count($answerIdArray) == 0) return false;
		
		$answerStr = '';
		for ($i = 0; $i < count($answerIdArray); $i++){
			$answerStr .= '\'' . addslashes($answerIdArray[$i]) . '\',';
		}
		$answerStr = trim($answerStr, ',');
		
		// CASE文作成
		$caseStr = 'CASE qd_id ';
		for ($i = 0; $i < count($answerIdArray); $i++){
			$caseStr .= 'WHEN \'' . addslashes($answerIdArray[$i]) . '\' THEN ' . $i . ' ';
		}
		$caseStr .= 'END AS no';

		$queryStr  = 'SELECT *, ' . $caseStr . ' FROM quiz_item_def ';
		$queryStr .=   'WHERE qd_visible = true ';
		$queryStr .=     'AND qd_deleted = false ';		// 削除されていない
		$queryStr .=     'AND qd_type = 1 ';		// 回答
		$queryStr .=     'AND qd_id in (' . $answerStr . ') ';
		$queryStr .=     'AND qd_set_id = ? ';
		$queryStr .=   'ORDER BY no';
		$retValue = $this->selectRecords($queryStr, array($setId), $rows);
		return $retValue;
	}
	/**
	 * クイズ定義項目の更新
	 *
	 * @param string $setId			定義セットID
	 * @param string $id			項目ID
	 * @param int $type				項目タイプ
	 * @param int $index			項目順
	 * @param string $selAnswer		選択用回答
	 * @param string $answer		回答値
	 * @param string $title			タイトル
	 * @param string $content		内容
	 * @param bool $visible			表示制御
	 * @param int $newSerial		新規シリアル番号
	 * @return bool		 			true = 成功、false = 失敗
	 */
	function updateItem($setId, $id, $type, $index, $selAnswer, $answer, $title, $content, $visible, &$newSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		$userId = $this->gEnv->getCurrentUserId();	// 現在のユーザ
		
		// トランザクション開始
		//$this->startTransaction();
		
		// 指定のレコードの履歴インデックス取得
		$historyIndex = 0;		// 履歴番号
		$queryStr  = 'SELECT * FROM quiz_item_def ';
		$queryStr .=   'WHERE qd_set_id = ? ';
		$queryStr .=     'AND qd_id = ? ';
		$queryStr .=  'ORDER BY qd_history_index DESC ';
		$ret = $this->selectRecord($queryStr, array($setId, $id), $row);
		if ($ret){
			$historyIndex = $row['qd_history_index'] + 1;
		
			// レコードが削除されていない場合は削除
			if (!$row['qd_deleted']){
				// 古いレコードを削除
				$queryStr  = 'UPDATE quiz_item_def ';
				$queryStr .=   'SET qd_deleted = true, ';	// 削除
				$queryStr .=     'qd_update_user_id = ?, ';
				$queryStr .=     'qd_update_dt = ? ';
				$queryStr .=   'WHERE qd_serial = ?';
				$ret = $this->execStatement($queryStr, array($userId, $now, $row['qd_serial']));
				if (!$ret) return false;
			}
		}
		
		// 新規レコード追加
		$queryStr  = 'INSERT INTO quiz_item_def (';
		$queryStr .=   'qd_set_id, ';
		$queryStr .=   'qd_id, ';
		$queryStr .=   'qd_history_index, ';
		$queryStr .=   'qd_type, ';
		$queryStr .=   'qd_select_answer_id, ';
		$queryStr .=   'qd_answer_id, ';
		$queryStr .=   'qd_title, ';
		$queryStr .=   'qd_content, ';
		$queryStr .=   'qd_index, ';
		$queryStr .=   'qd_visible, ';
		$queryStr .=   'qd_create_user_id, ';
		$queryStr .=   'qd_create_dt ';
		$queryStr .=   ') VALUES ';
		$queryStr .=   '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$ret =$this->execStatement($queryStr, array($setId, $id, $historyIndex, $type, $selAnswer, $answer, $title, $content, $index, intval($visible), $userId, $now));
		if (!$ret) return false;
		
		// 新規のシリアル番号取得
		$queryStr = 'SELECT MAX(qd_serial) AS ns FROM quiz_item_def ';
		$ret = $this->selectRecord($queryStr, array(), $row);
		if ($ret) $newSerial = $row['ns'];
		
		// トランザクション確定
		//$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 回答データを追加
	 *
	 * @param string	$mobileId		携帯ID
	 * @param string	$setId			パターンセットID
	 * @param string	$id				回答ID
	 * @param string	$value			入力値
	 * @param bool      $result			回答結果
	 * @param int		$logSerial		アクセスログシリアル番号
	 * @return							true = 正常、false=異常
	 */
	function addPostData($mobileId, $setId, $id, $value, $result, $logSerial)
	{
		$now = date("Y/m/d H:i:s");	// 現在日時
		
		// トランザクションスタート
		$this->startTransaction();
		
		// 既に登録されている場合はエラー
		$queryStr = 'SELECT * FROM quiz_user_post ';
		$queryStr .=  'WHERE qp_mobile_id = ? ';
		$queryStr .=     'AND qp_set_id = ? ';
		$queryStr .=     'AND qp_question_id = ? ';
		$ret = $this->isRecordExists($queryStr, array($mobileId, $setId, $id));
		if ($ret){
			// トランザクション終了
			$ret = $this->endTransaction();
			return false;
		}
		
		$queryStr = 'INSERT INTO quiz_user_post (';
		$queryStr .=  'qp_mobile_id, ';
		$queryStr .=  'qp_set_id, ';
		$queryStr .=  'qp_question_id, ';
		$queryStr .=  'qp_answer_id, ';
		$queryStr .=  'qp_result, ';
		$queryStr .=  'qp_access_log_serial, ';
		$queryStr .=  'qp_dt ';
		$queryStr .=  ') VALUES (';
		$queryStr .=  '?, ?, ?, ?, ?, ?, ?';
		$queryStr .=  ')';
		$ret = $this->execStatement($queryStr, array($mobileId, $setId, $id, $value, intval($result), $logSerial, $now));
		
		// トランザクション終了
		$ret = $this->endTransaction();
		return $ret;
	}
	/**
	 * 回答状況を取得
	 *
	 * @param string $setId			定義セットID
	 * @param string $mobileId		携帯ID
	 * @param array  	$rows		取得レコード
	 * @return bool					true=取得、false=取得せず
	 */
	function getAnswerResult($setId, $mobileId, &$rows)
	{
		$queryStr  = 'SELECT * FROM quiz_item_def LEFT JOIN quiz_user_post ON qd_set_id = qp_set_id AND qd_id = qp_question_id AND qd_type = 0 AND qp_mobile_id = ? ';
		$queryStr .=   'WHERE qd_deleted = false ';		// 未削除
		$queryStr .=     'AND qd_set_id = ? ';
		$queryStr .=     'AND qd_type = 0 ';		// 問題
		$queryStr .=     'AND qd_visible = true ';		// 表示
		$queryStr .=     'AND qp_mobile_id IS NOT NULL ';
		$queryStr .=   'ORDER BY qd_index';
		$retValue = $this->selectRecords($queryStr, array($mobileId, $setId), $rows);
		return $retValue;
	}
}
?>
