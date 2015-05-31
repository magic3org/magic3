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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/member_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/member_mainDb.php');

class admin_member_mainBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// 会員情報定義値
	protected static $_task;			// 現在の画面
	protected $_baseUrl;			// 管理画面のベースURL
	
	// 画面
	const TASK_CONFIG			= 'config';				// 基本設定
	const TASK_MEMBER			= 'member';				// 会員情報一覧
	const TASK_MEMBER_DETAIL 	= 'member_detail';		// 会員情報詳細
	const DEFAULT_TASK			= 'config';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new member_mainDb();
		
		// DB定義を読み込む
//		if (!isset(self::$_configArray)) self::$_configArray = member_mainCommonDef::loadConfig(self::$_mainDb);
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
		$this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $this->_openBy);		// ウィンドウオープンタイプ
		
		// 管理画面ペースURL取得
		$this->_baseUrl = $this->getAdminUrlWithOptionParam(true);		// ページ定義パラメータ付加
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
		$task = self::$_task;			// 現在の画面を取得
		
		// パンくずリストの定義データ作成
		$titles = array();
		switch ($task){
			case self::TASK_MEMBER:		// 会員情報一覧
				$titles[] = '会員情報管理';
				$titles[] = '会員一覧';
				break;
			case self::TASK_MEMBER_DETAIL:		// 会員情報詳細
				$titles[] = '会員情報管理';
				$titles[] = '会員一覧';
				$titles[] = '詳細';
				break;
			case self::TASK_CONFIG:		// 基本設定
				$titles[] = '基本';
				$titles[] = '基本設定';
				break;
		}
		
		// メニューバーの定義データ作成
		$menu =	array(
					(Object)array(
						'name'		=> '会員情報管理',
						'task'		=> self::TASK_MEMBER,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_MEMBER ||			// 会員情報一覧
											$task == self::TASK_MEMBER_DETAIL		// 会員情報詳細
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> '会員一覧',
								'task'		=> self::TASK_MEMBER,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_MEMBER ||			// 会員情報一覧
													$task == self::TASK_MEMBER_DETAIL		// 会員情報詳細
												)
							)
						)
					)
/*					(Object)array(
						'name'		=> '基本',
						'task'		=> self::TASK_CONFIG,
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_CONFIG
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> '基本設定',
								'task'		=> self::TASK_CONFIG,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_CONFIG					// 基本設定
												)
							)
						)
					)*/
				);

		// サブメニューバーを作成
		$this->setConfigMenubarDef($titles, $menu);
	}
}
?>
