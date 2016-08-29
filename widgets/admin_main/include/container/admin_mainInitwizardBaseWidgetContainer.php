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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainInitwizardBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	protected $_mainDb;			// DB接続オブジェクト
	protected $_taskArray;		// 管理下のタスク
	protected $_taskTitleArray;		// 管理下のタスク名
	protected $_prevTask;
	protected $_nextTask;
	const BREADCRUMB_TITLE = '管理画面カスタムウィザード';		// 画面タイトル名(パンくずリスト)
	const TASK_START		= 'initwizard';
	const TASK_SITE			= 'initwizard_site';		// サイト情報
	const TASK_ADMIN		= 'initwizard_admin';		// システム管理者
	const TASK_ACCESS_POINT	= 'initwizard_accesspoint';		// アクセスポイント
	const TASK_MENU			= 'initwizard_menu';		// メニュー
	const TASK_CONTENT		= 'initwizard_content';		// コンテンツ
	const TASK_PAGE1		= 'initwizard_page1';		// ページ1
	const TASK_PAGE2		= 'initwizard_page2';		// ページ2
	const TASK_END			= 'initwizard_end';		// 処理終了
	const TASK_TITLE_SITE			= 'サイト';		// サイト情報
	const TASK_TITLE_ADMIN			= '管理者';		// 管理者
	const TASK_TITLE_ACCESS_POINT	= 'アクセスポイント';
	const TASK_TITLE_MENU			= 'メニュー';
	const TASK_TITLE_CONTENT		= 'コンテンツ';
	const TASK_TITLE_PAGE1			= 'ページ1';
	const TASK_TITLE_PAGE2			= 'ページ2';
	const TASK_TITLE_END			= '完了';		// 完了
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->_mainDb = new admin_mainDb();
		
		$this->_taskArray		= array(self::TASK_SITE, self::TASK_ADMIN, self::TASK_ACCESS_POINT, self::TASK_MENU,
										self::TASK_CONTENT, self::TASK_PAGE1, self::TASK_PAGE2, self::TASK_END);		// 管理下のタスク
		$this->_taskTitleArray	= array(self::TASK_TITLE_SITE, self::TASK_TITLE_ADMIN, self::TASK_TITLE_ACCESS_POINT, self::TASK_TITLE_MENU,
										self::TASK_TITLE_CONTENT, self::TASK_TITLE_PAGE1, self::TASK_TITLE_PAGE2, self::TASK_TITLE_END);
	}
	/**
	 * テンプレート前処理
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _preAssign($request, &$param)
	{
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		
		// 前後のタスクを取得
		$this->_prevTask = '';
		$this->_nextTask = '';
		if ($task == self::TASK_END){		// 初期化終了
			// 前後タスクなし
		} else {
			$taskCount = count($this->_taskArray);
			for ($i = 0; $i < $taskCount; $i++){
				if ($task == $this->_taskArray[$i]){
					if ($i > 0) $this->_prevTask = $this->_taskArray[$i -1];
					if ($i < $taskCount -1) $this->_nextTask = $this->_taskArray[$i +1];
					break;
				}
			}
		}
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		$baseUrl = $this->gEnv->getDefaultAdminUrl();
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		
		// メニュー作成
		$menuHtml = '';
		for ($i = 0; $i < count($this->_taskArray); $i++){
			$url = $baseUrl . '?task=' . $this->_taskArray[$i];
			$attr = '';
			if ($task == $this->_taskArray[$i]) $attr = ' class="active"';
			$menuHtml .= '<li' . $attr . '><a href="' . $url . '">' . $this->convertToDispString($this->_taskTitleArray[$i]) . '</a></li>';
		}
//		$menuHtml = '<ul class="nav nav-tabs">' . $menuHtml . '</ul>';
		$menuHtml = '<ul class="nav nav-wizard">' . $menuHtml . '</ul>';
		$this->tmpl->addVar("_widget", "menu_items", $menuHtml);
		
		// 前後エントリー移動ボタン
		if (!empty($this->_prevTask)){
			$this->tmpl->setAttribute('show_prev_button', 'visibility', 'visible');
			$this->tmpl->addVar('show_prev_button', 'task', $this->_prevTask);
		}
		if (!empty($this->_nextTask)){
			$this->tmpl->setAttribute('show_next_button', 'visibility', 'visible');
			$this->tmpl->addVar('show_next_button', 'task', '');
		}
		
		// パンくずリストの作成
		$this->gPage->setAdminBreadcrumbDef(array(self::BREADCRUMB_TITLE));
	}
	/**
	 * 次のタスクがある場合は画面遷移
	 *
	 * @return なし
	 */
	function _redirectNextTask()
	{
		if (!empty($this->_nextTask)){
			$nextPage = $this->gEnv->getDefaultAdminUrl() . '?task=' . $this->_nextTask;
			$this->gPage->redirect($nextPage);
		}
	}
}
?>
