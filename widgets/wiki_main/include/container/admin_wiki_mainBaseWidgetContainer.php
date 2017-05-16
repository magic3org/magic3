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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/wiki_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/wiki_mainDb.php');

class admin_wiki_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected $_baseUrl;			// 管理画面のベースURL
	
	// 画面
	const TASK_PAGE			= 'page';				// Wikiページ管理(一覧)
	const TASK_PAGE_DETAIL	= 'page_detail';		// Wikiページ管理(詳細)
	const TASK_CONFIG		= 'config';				// 基本設定
	const DEFAULT_TASK		= 'page';				// デフォルト画面
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new wiki_mainDb();
	}
	/**
	 * テンプレートに前処理
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _preAssign($request, &$param)
	{
		$this->_openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($this->_openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $this->_openBy);

		// 管理画面ペースURL取得
		$this->_baseUrl = $this->getAdminUrlWithOptionParam();
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
		// ウィンドウオープンタイプ取得
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
		if ($openBy == 'simple' || $openBy == 'tabs') return;			// シンプルウィンドウまたはタブ表示のときはメニューを表示しない
	
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;		// デフォルト画面を設定
		
		// パンくずリストの定義データ作成
		$titles = array();
		switch ($task){
			case self::TASK_PAGE:					// Wikiページ管理(一覧)
				$titles[] = 'Wiki管理';
				$titles[] = 'Wikiページ一覧';
				break;
			case self::TASK_PAGE_DETAIL:			// Wikiページ管理(詳細)
				$titles[] = 'Wiki管理';
				$titles[] = 'Wikiページ一覧';
				$titles[] = '詳細';
				break;
			case self::TASK_CONFIG:				// 基本設定
				$titles[] = '基本';
				break;
		}
		
		// メニューバーの定義データ作成
		$menu =	array(
					(Object)array(
						'name'		=> 'Wiki管理',
						'task'		=> '',
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_PAGE ||				// Wikiページ管理(一覧)
											$task == self::TASK_PAGE_DETAIL			// Wikiページ管理(詳細)
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> 'Wikiページ一覧',
								'task'		=> self::TASK_PAGE,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_PAGE ||				// Wikiページ管理(一覧)
													$task == self::TASK_PAGE_DETAIL			// Wikiページ管理(詳細)
												)
							)
						)
					),
					(Object)array(
						'name'		=> '基本',
						'task'		=> self::TASK_CONFIG,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_CONFIG 	// 基本設定
										)
					)
				);

		// サブメニューバーを作成
		$this->setConfigMenubarDef($titles, $menu);
	}
}
?>
