<?php
/**
 * コンテナクラス
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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainUserBaseWidgetContainer.php');

class admin_mainTaskaccessWidgetContainer extends admin_mainUserBaseWidgetContainer
{
	private $allTasks;
	private $allTaskArray;			// 変更可能なすべてのタスク
	private $enableTaskArray;		// 実行可能なタスク
	const CF_SYSTEM_MANAGER_ENABLE_TASK	= 'system_manager_enable_task';	// システム運用者が実行可能な管理画面タスク

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// パラメータ初期化
		$this->enableTaskArray = array();
		$this->allTaskArray = array(
										array(	'name' => 'ダッシュボード',		'value' => self::TASK_TOP),		// ダッシュボード(メッセージのみ)
										array(	'name' => 'ユーザ詳細',			'value' => self::TASK_USERLIST_DETAIL),		// ユーザ詳細
										array(	'name' => 'ログイン履歴',		'value' => self::TASK_LOGINHISTORY),		// ログイン履歴
										array(	'name' => '運用ログ一覧',		'value' => self::TASK_OPELOG),				// 運用ログ一覧
										array(	'name' => '運用ログ詳細',		'value' => self::TASK_OPELOG_DETAIL),		// 運用ログ詳細
										array(	'name' => 'アクセスログ一覧',	'value' => self::TASK_ACCESSLOG),		// アクセスログ一覧
										array(	'name' => 'アクセスログ詳細',	'value' => self::TASK_ACCESSLOG_DETAIL),		// アクセスログ詳細
										array(	'name' => '検索語ログ一覧',		'value' => self::TASK_SEARCHWORDLOG),		// 検索語ログ一覧
										array(	'name' => '検索語ログ詳細',		'value' => self::TASK_SEARCHWORDLOG_DETAIL),		// 検索語ログ詳細
										array(	'name' => '集計',				'value' => self::TASK_CALC),		// 集計
										array(	'name' => 'グラフ表示',			'value' => self::TASK_GRAPH),		// グラフ表示
										array(	'name' => 'Awstats',			'value' => self::TASK_AWSTATS),		// Awstats
										array(	'name' => 'ファイルブラウザ',	'value' => self::TASK_FILEBROWSE)		// ファイルブラウザ
									);
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
		return 'taskaccess.tmpl.html';
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
		$act = $request->trimValueOf('act');
		$taskList = explode(',', $request->trimValueOf('tasklist'));

		// チェックされているタスクを取得
		for ($i = 0; $i < count($taskList); $i++){
			// 項目がチェックされているかを取得
			$itemName = 'item' . $i . '_checked';
			$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
			if ($itemValue) $this->enableTaskArray[] = $taskList[$i];
		}
			
		if ($act == 'update'){		// 設定更新のとき
			$permitTask = implode(',', $this->enableTaskArray);

			$ret = $this->_db->updateSystemConfig(self::CF_SYSTEM_MANAGER_ENABLE_TASK, $permitTask);		// システム運用者が実行可能な管理画面タスク
			if ($ret){
				$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
			} else {
				$this->setAppErrorMsg('データ新に失敗しました');
			}
		}
		
		$permitTask = $this->_db->getSystemConfig(self::CF_SYSTEM_MANAGER_ENABLE_TASK);	// システム運用者が実行可能な管理画面タスク
		if (!empty($permitTask)) $this->enableTaskArray = explode(',', $permitTask);
		
		// タスク一覧作成
		$this->allTasks = array();
		$this->createTaskList();
		
		$this->tmpl->addVar("_widget", "task_list", implode($this->allTasks, ','));		// 表示中のタスク
	}
	/**
	 * タスク一覧作成
	 *
	 * @return なし
	 */
	function createTaskList()
	{
		for ($i = 0; $i < count($this->allTaskArray); $i++){
			$value = $this->allTaskArray[$i]['value'];
			$name = $this->allTaskArray[$i]['name'];
			
			$checked = '';
			if (in_array($value, $this->enableTaskArray)) $checked = 'checked';
			
			$row = array(
				'index'		=> $i,
				'value'		=> $value,
				'name'		=> $this->convertToDispString($name),
				'checked'	=> $checked									// 選択中かどうか
			);
			$this->tmpl->addVars('task_list', $row);
			$this->tmpl->parseTemplate('task_list', 'a');
			
			$this->allTasks[] = $value;		// 表示タスクを保存
		}
	}
}
?>
