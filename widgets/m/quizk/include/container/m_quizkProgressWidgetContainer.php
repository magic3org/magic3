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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_quizkProgressWidgetContainer.php 1933 2009-05-28 10:54:45Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_quizkBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/quizkDb.php');

class m_quizkProgressWidgetContainer extends m_quizkBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $setId;				// 定義セットID
	const CFG_DEFAULT_SET_ID_KEY = 'current_set_id';		// 現在の選択中のセットID取得用キー
	const CURRENT_TASK = 'progress';		// 現在の画面
	const NEXT_TASK = 'complete';		// 次の画面

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new quizkDb();
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{	
		return 'progress.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$this->setId = $this->db->getConfig(self::CFG_DEFAULT_SET_ID_KEY);		// パターンセットID
		$act = $request->trimValueOf('act');
		
		if ($act == 'answer'){		// 回答したとき
			$postSetId = $request->trimValueOf('sid');
			$questionId = $request->trimValueOf('qid');
			$answer = $request->trimValueOf('answer');

			// 回答状況をチェック
			$isErr = false;		// エラー発生状況
			$isRight = false;	// 正解かどうか
			$ret = $this->db->getItem($postSetId, $questionId, $row);
			if ($ret){
				if ($row['qd_type'] != 0) $isErr = true;		// エラー発生状況
				$answerId = $row['qd_answer_id'];
			} else {
				$isErr = true;		// エラー発生状況
			}
			if (!$isErr){
				if (strcmp($answerId, $answer) == 0) $isRight = true;	// 正解かどうか
				
				$logSerial = $this->gEnv->getCurrentAccessLogSerial();
				$ret = $this->db->addPostData($this->mobileId, $postSetId, $questionId, $answer, $isRight, $logSerial);
				if (!$ret) $isErr = true;
			}
			// 回答を表示
			if (!$isErr){
				$ret = $this->db->getItem($postSetId, $answerId, $row);
				if ($ret){
					// 選択結果を表示
					if ($isRight){
						$result = '正解';
					} else {
						$result = '不正解';
						$this->tmpl->setAttribute('result_msg', 'visibility', 'visible');
					}
					$this->tmpl->setAttribute('result_area', 'visibility', 'visible');
					$this->tmpl->addVar("result_area", "result", $result);
					$this->tmpl->addVar("result_area", "title", $row['qd_title']);
					$this->tmpl->addVar("result_area", "content", $row['qd_content']);
				} else {
					$isErr = true;
				}
			}
			// 次の問題へのリンクを作成
			if (!$isErr){
				$ret = $this->db->getNextQuestion($this->setId, $this->mobileId, $row);
				if ($ret){		// 問題が残っているとき
					$this->tmpl->addVar("result_area", "next_name", '次へ');
					$this->tmpl->addVar('result_area', 'next_url', $this->gEnv->createCurrentPageUrlForMobile('task=' . self::CURRENT_TASK));
				} else {
					$this->tmpl->addVar("result_area", "next_name", '終了');
					$this->tmpl->addVar('result_area', 'next_url', $this->gEnv->createCurrentPageUrlForMobile('task=' . self::NEXT_TASK));
				}					
			}
			
			if ($isErr){
				$message = sprintf(self::ERR_MESSAGE_FORMAT, 'エラーが発生しました');
				$this->tmpl->addVar("_widget", "message", $message);
			}
		} else {
			// 問題を取得
			$ret = $this->db->getNextQuestion($this->setId, $this->mobileId, $row);
			if ($ret){		// 問題が残っているとき
				$this->tmpl->setAttribute('question_area', 'visibility', 'visible');
			
				// クイズ問題を作成
				$this->createQuestion($row);
			} else {		// 次の問題がないとき
				$count = $this->db->getQuestionCount($this->setId);
				if ($count == 0){
					$message = '問題が登録されていません';
				} else {
					$message = '全問回答しました';
				}
				$this->tmpl->addVar("_widget", "message", $message);
				$this->tmpl->setAttribute('view_status_area', 'visibility', 'visible');
			}
		}
		$this->tmpl->addVar('question_area', 'act', 'answer');
		$this->tmpl->addVar('question_area', 'url', $this->gEnv->createCurrentPageUrlForMobile('task=' . self::CURRENT_TASK));
		$this->tmpl->addVar('_widget', 'top_url', $this->gEnv->createCurrentPageUrlForMobile(''));
		$this->tmpl->addVar('_widget', 'start_url', $this->gEnv->createCurrentPageUrlForMobile('task=' . self::NEXT_TASK));
	}
	/**
	 * クイズ問題、回答を作成
	 *
	 * @param array $row			クイズ問題項目レコード
	 * @return 						なし
	 */
	function createQuestion($row)
	{
		$answer = $row['qd_select_answer_id'];		// 回答ID
		$answerArray = array();
		if (!empty($answer)) $answerArray = explode(';', $answer);
		
		// 問題を作成
		$title = $this->convertToDispString($row['qd_title']);
		$content = $this->convertToDispString($row['qd_content']);
		$this->tmpl->addVar('question_area', 'title', $title);
		$this->tmpl->addVar('question_area', 'content', $content);
		$this->tmpl->addVar('question_area', 'sid', $this->setId);
		$this->tmpl->addVar('question_area', 'qid', $this->convertToDispString($row['qd_id']));
		
		if (count($answerArray) == 0) return;
		
		// 回答を作成
		$ret = $this->db->getAnswers($this->setId, $answerArray, $rows);
		if ($ret){
			$inputTag = '';
			for ($i = 0; $i < count($rows); $i++){
				$title = $this->convertToDispString($rows[$i]['qd_title']);
				$value = $this->convertToDispString($rows[$i]['qd_id']);
				$inputTag .= '<input type="radio" name="answer" value="' . $value . '" />' . $title . '<br />' . M3_NL;
			}
			$this->tmpl->addVar('question_area', 'answer', $inputTag);
		}
	}
}
?>
