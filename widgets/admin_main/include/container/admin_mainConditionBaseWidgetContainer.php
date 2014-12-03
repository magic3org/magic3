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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainConditionBaseWidgetContainer extends admin_mainBaseWidgetContainer
{
	protected $_mainDb;
	protected $_openBy;				// ウィンドウオープンタイプ
	const BREADCRUMB_TITLE	= '運用状況';		// パンくずリストトップタイトル
	// 画面
	const TASK_OPELOG	= 'opelog';			// 運用ログ一覧
	const TASK_OPELOG_DETAIL 	= 'opelog_detail';		// 運用ログ詳細
	const TASK_ACCESSLOG		= 'accesslog';				// アクセスログ一覧
	const TASK_ACCESSLOG_DETAIL	= 'accesslog_detail';		// アクセスログ詳細
	const TASK_SEARCHWORDLOG	= 'searchwordlog';				// 検索語ログ一覧
	const TASK_SEARCHWORDLOG_DETAIL	= 'searchwordlog_detail';		// 検索語ログ詳細
	const TASK_CALC		= 'analyzecalc';		// 集計
	const TASK_GRAPH	= 'analyzegraph';		// グラフ表示
	const TASK_AWSTATS		= 'awstats';		// Awstats表示
	const DEFAULT_TOP_PAGE = 'accesslog';		// デフォルトのトップ画面
	// DB定義値
	const CF_AWSTATS_DATA_PATH = 'awstats_data_path';		// Awstatsデータパス
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->_mainDb = new admin_mainDb();
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
		if ($this->_openBy == 'simple') return;			// シンプルウィンドウのときはメニューを表示しない
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TOP_PAGE;
		
		// パンくずリストの作成
		$titles = array();
		$titles[] = self::BREADCRUMB_TITLE;
		switch ($task){
			case self::TASK_OPELOG:			// 運用ログ一覧
				$titles[] = 'ログ';
				$titles[] = '運用ログ';
				break;
			case self::TASK_OPELOG_DETAIL:		// 運用ログ詳細
				$titles[] = 'ログ';
				$titles[] = '運用ログ';
				$titles[] = '詳細';
				break;
			case self::TASK_ACCESSLOG:				// アクセスログ一覧
				$titles[] = 'ログ';
				$titles[] = 'アクセスログ';
				break;
			case self::TASK_ACCESSLOG_DETAIL:		// アクセスログ詳細
				$titles[] = 'ログ';
				$titles[] = 'アクセスログ';
				$titles[] = '詳細';
				break;
			case self::TASK_SEARCHWORDLOG:				// 検索語ログ一覧
				$titles[] = 'ログ';
				$titles[] = '検索キーワード';
				break;
			case self::TASK_SEARCHWORDLOG_DETAIL:		// 検索語ログ詳細
				$titles[] = 'ログ';
				$titles[] = '検索キーワード';
				$titles[] = '詳細';
				break;
			case self::TASK_GRAPH:	// グラフ表示
				$titles[] = 'アクセス数';
				$titles[] = 'グラフ表示';
				break;
			case self::TASK_CALC:	// 集計
				$titles[] = 'アクセス数';
				$titles[] = '集計';
				break;
			case self::TASK_AWSTATS:		// Awstats表示
				$titles[] = 'アクセス解析';
				$titles[] = 'Awstats';
				break;
		}
		$this->gPage->setAdminBreadcrumbDef($titles);
		
		// メニューバーの作成
		$navbarDef = new stdClass;
//		$navbarDef->title = $this->gEnv->getCurrentWidgetTitle();		// ウィジェット名
		$navbarDef->baseurl = $this->getAdminUrlWithOptionParam();
		$navbarDef->help	= '';// ヘルプ文字列
		$navbarDef->menu =	array(
								(Object)array(
									'name'		=> 'ログ',
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> (
														$task == self::TASK_ACCESSLOG ||				// アクセスログ一覧
														$task == self::TASK_ACCESSLOG_DETAIL ||			// アクセスログ詳細
														$task == self::TASK_OPELOG ||					// 運用ログ一覧
														$task == self::TASK_OPELOG_DETAIL ||			// 運用ログ詳細
														$task == self::TASK_SEARCHWORDLOG ||			// 検索語ログ一覧
														$task == self::TASK_SEARCHWORDLOG_DETAIL		// 検索語ログ詳細
													),
									'submenu'	=> array(
										(Object)array(
											'name'		=> 'アクセスログ',
											'task'		=> self::TASK_ACCESSLOG,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_ACCESSLOG ||				// アクセスログ一覧
																$task == self::TASK_ACCESSLOG_DETAIL			// アクセスログ詳細
															)
										),
										(Object)array(
											'name'		=> '運用ログ',
											'task'		=> self::TASK_OPELOG,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_OPELOG ||			// 運用ログ一覧
																$task == self::TASK_OPELOG_DETAIL		// 運用ログ詳細
															)
										),
										(Object)array(
											'name'		=> '検索キーワード',
											'task'		=> self::TASK_SEARCHWORDLOG,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_SEARCHWORDLOG ||				// 検索語ログ一覧
																$task == self::TASK_SEARCHWORDLOG_DETAIL			// 検索語ログ詳細
															)
										)
									)
								),
								(Object)array(
									'name'		=> 'アクセス数',
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> (
														$task == self::TASK_GRAPH ||	// グラフ表示
														$task == self::TASK_CALC			// 集計
													),
									'submenu'	=> array(
										(Object)array(
											'name'		=> 'グラフ表示',
											'task'		=> self::TASK_GRAPH,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_GRAPH	// グラフ表示
															)
										),
										(Object)array(
											'name'		=> '集計',
											'task'		=> self::TASK_CALC,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_CALC			// 集計
															)
										)
									)
								),
								(Object)array(
									'name'		=> 'アクセス解析',
									'task'		=> '',
									'url'		=> '',
									'tagid'		=> '',
									'active'	=> (
														$task == self::TASK_AWSTATS		// Awstats表示
													),
									'submenu'	=> array(
										(Object)array(
											'name'		=> 'Awstats',
											'task'		=> self::TASK_AWSTATS,
											'url'		=> '',
											'tagid'		=> '',
											'active'	=> (
																$task == self::TASK_AWSTATS
															),
											'disabled'	=> (	!$this->isExistsAwstats()	)
										)
									)
								)
							);
		$this->gPage->setAdminSubNavbarDef($navbarDef);
	}
	/**
	 * Awstatsの作成データが参照できるかどうか
	 *
	 * @return bool		true=参照可、false=参照不可
	 */
	function isExistsAwstats()
	{
		$awstatsDataPath = $this->getAwstatsPath();
		if (is_dir($awstatsDataPath)){
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Awstatsの作成データのパスを取得
	 *
	 * @return string		パス
	 */
	function getAwstatsPath()
	{
		$path = $this->gSystem->getSystemConfig(self::CF_AWSTATS_DATA_PATH);
		if (empty($path)) return '';
		
		$awstatsDataPath = rel2abs($path, $this->gEnv->getSystemRootPath());
		return $awstatsDataPath;
	}
	/**
	 * Awstatsの作成データのURLを取得
	 *
	 * @return string		URL
	 */
	function getAwstatsUrl()
	{
		$path = $this->gSystem->getSystemConfig(self::CF_AWSTATS_DATA_PATH);
		if (empty($path)) return '';
		
		$awstatsDataPath = rel2abs($path, $this->gEnv->getRootUrl());
		return $awstatsDataPath;
	}
}
?>
