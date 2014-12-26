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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_banner3BaseWidgetContainer.php 5859 2013-03-26 06:14:45Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getWidgetContainerPath('banner3') . '/default_bannerCommonDef.php');
require_once($gEnvManager->getWidgetDbPath('banner3') . '/banner3Db.php');

class admin_banner3BaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	// 画面
	const TASK_BANNER 		= 'banner';					// バナー設定
	const TASK_BANNER_LIST	= 'banner_list';			// バナー設定一覧
	const TASK_IMAGE 		= 'image';					// 画像一覧
	const TASK_IMAGE_DETAIL	= 'image_detail';			// 画像詳細
	const TASK_IMAGE_SELECT	= 'image_select';			// 画像選択
	const DEFAULT_TASK = 'banner';						// デフォルト画面
			
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 代替処理用のウィジェットIDを設定
		$this->setDefaultWidgetId(default_bannerCommonDef::BANNER_WIDGET_ID);
		
		// DBオブジェクト作成
		if (!isset(self::$_mainDb)) self::$_mainDb = new banner3Db();
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
		if ($openBy == 'simple' || $openBy == 'tabs' || $openBy == 'dialog') return;			// シンプルウィンドウまたはタブ表示、ダイアログのときはメニューを表示しない
				
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;		// デフォルト画面を設定
		
		// パンくずリストの定義データ作成
		$titles = array();
		switch ($task){
			case self::TASK_BANNER:					// バナー設定
				$titles[] = 'バナー管理';
				$titles[] = 'バナー設定';
				break;
			case self::TASK_BANNER_LIST:			// バナー設定一覧
				$titles[] = 'バナー管理';
				$titles[] = 'バナー設定';
				$titles[] = '設定一覧';
				break;
			case self::TASK_IMAGE:					// 画像一覧
				$titles[] = 'バナー管理';
				$titles[] = '画像一覧';
				break;
			case self::TASK_IMAGE_DETAIL:				// 画像詳細
				$titles[] = 'バナー管理';
				$titles[] = '画像一覧';
				$titles[] = '詳細';
				break;
			case self::TASK_IMAGE_SELECT:			// 画像選択
				break;
		}
		
		// メニューバーの定義データ作成
		$menu =	array(
					(Object)array(
						'name'		=> 'バナー管理',
						'task'		=> '',
						'url'		=> '',
						'tagid'		=> '',
						'active'	=> (
											$task == self::TASK_BANNER ||				// バナー設定
											$task == self::TASK_BANNER_LIST ||			// バナー設定一覧
											$task == self::TASK_IMAGE ||				// 画像一覧
											$task == self::TASK_IMAGE_DETAIL			// 画像詳細
										),
						'submenu'	=> array(
							(Object)array(
								'name'		=> 'バナー設定',
								'task'		=> self::TASK_BANNER,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_BANNER ||				// バナー設定
													$task == self::TASK_BANNER_LIST			// バナー設定一覧
												)
							),
							(Object)array(
								'name'		=> '画像一覧',
								'task'		=> self::TASK_IMAGE,
								'url'		=> '',
								'tagid'		=> '',
								'active'	=> (
													$task == self::TASK_IMAGE ||				// 画像一覧
													$task == self::TASK_IMAGE_DETAIL			// 画像詳細
												)
							)
						)
					)
				);

		// サブメニューバーを作成
		$this->setConfigMenubarDef($titles, $menu);
		
/*
		// パンくずリストを作成
		$createList = true;		// パンくずリストを作成するかどうか
		switch ($task){
			case 'banner':		// バナー管理
			case 'banner_list':		// バナー一覧管理
				$linkList = ' &gt;&gt; バナー管理';// パンくずリスト
				break;
			case 'image':		// 画像管理
			case 'image_detail':	// 画像詳細
				$linkList = ' &gt;&gt; 画像リンク管理';// パンくずリスト
				break;
			case 'image_select':	// 画像選択
				$createList = false;		// パンくずリストを作成するかどうか
				break;
		}

		if ($createList){				// パンくずリストを作成するとき
			// ####### 上段メニューの作成 #######
			$menuText = '<div id="configmenu-upper">' . M3_NL;
			$menuText .= '<ul>' . M3_NL;
		
			$current = '';
			$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き
		
			// バナー管理
			$current = '';
			$link = $this->getUrl($baseUrl . '&task=banner');
			if ($task == 'banner' ||
				$task == 'banner_list'){
				$current = 'id="current"';
			}
			$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>バナー管理</span></a></li>' . M3_NL;
		
			// 画像管理
			$current = '';
			$link = $this->getUrl($baseUrl . '&task=image');
			if ($task == 'image' ||		// 画像管理
				$task == 'image_detail'){		// 画像詳細
				$current = 'id="current"';
			}
			$menuText .= '<li ' . $current . '><a href="'. $this->convertUrlToHtmlEntity($link) .'"><span>画像リンク管理</span></a></li>' . M3_NL;
		
			// 上段メニュー終了
			$menuText .= '</ul>' . M3_NL;
			$menuText .= '</div>' . M3_NL;
		
			// 作成データの埋め込み
			$linkList = '<div id="configmenu-top"><label>' . 'バナー' . $linkList . '</div>';
			$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
			$this->tmpl->addVar("_widget", "menu_items", $outputText);
		} else {
			$this->tmpl->addVar("_widget", "menu_items", '');
		}
		*/
	}
}
?>
