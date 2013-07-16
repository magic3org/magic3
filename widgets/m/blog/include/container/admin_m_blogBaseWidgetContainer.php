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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_m_blogBaseWidgetContainer.php 3836 2010-11-17 06:05:07Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blogDb.php');

class admin_m_blogBaseWidgetContainer extends BaseAdminWidgetContainer
{
	protected $_db;			// DB接続オブジェクト
	protected $_blogId;	// ブログID
	const DEFAULT_TOP_PAGE = 'config';		// デフォルトのトップページ
	const WIDGET_TITLE_NAME = 'ブログ(携帯)';				// ウィジェットタイトル名
	const CF_ENTRY_VIEW_COUNT		= 'm:entry_view_count';			// 記事表示数
	const CF_ENTRY_VIEW_ORDER		= 'm:entry_view_order';			// 記事表示方向
	const CF_TITLE_COLOR			= 'm:title_color';			// タイトルの背景色
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// サブウィジェット起動のときだけ初期処理実行
		if ($this->gEnv->getIsSubWidget()){
			// DBオブジェクト作成
			$this->_db = new blogDb();
		
			$this->_blogId = '';
		
			// ブログ定義を読み込む
			$this->_loadConfig($this->_blogId);
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
		// ウィンドウオープンタイプ取得
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		if (!empty($openBy)) $this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $openBy);
		
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TOP_PAGE;
		
		// パンくずリストを作成
		switch ($task){
			case 'config':		// 基本設定
				$linkList = ' &gt;&gt; 基本設定';// パンくずリスト
				break;
		}
		// ####### 上段メニューの作成 #######
		$menuText = '<div id="configmenu-upper">' . M3_NL;
		$menuText .= '<ul>' . M3_NL;
		$baseUrl = $this->getAdminUrlWithOptionParam(true);// 画面定義ID付き

		// その他設定
		$current = '';
		$link = $baseUrl . '&task=config';
		if ($task == 'config'){		
			$current = 'id="current"';
		}
		$menuText .= '<li ' . $current . '><a href="'. $this->getUrl($link, true) .'"><span>基本設定</span></a></li>' . M3_NL;
		
		// 上段メニュー終了
		$menuText .= '</ul>' . M3_NL;
		$menuText .= '</div>' . M3_NL;
		
		// 作成データの埋め込み
		$linkList = '<div id="configmenu-top"><label>' . self::WIDGET_TITLE_NAME . $linkList . '</div>';
		$outputText .= '<table width="90%"><tr><td>' . $linkList . $menuText . '</td></tr></table>' . M3_NL;
		$this->tmpl->addVar("_widget", "menu_items", $outputText);
	}
	/**
	 * ブログ定義値をDBから取得
	 *
	 * @param string $blogId	ブログID
	 * @return bool				true=取得成功、false=取得失敗
	 */
	function _loadConfig($blogId)
	{
		$this->_configArray = array();

		// BBS定義を読み込み
		$ret = $this->_db->getAllConfig($rows, $blogId);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['bg_id'];
				$value = $rows[$i]['bg_value'];
				$this->_configArray[$key] = $value;
			}
		}
		return $ret;
	}
}
?>
