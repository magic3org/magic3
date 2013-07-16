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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_admin_analyticsWidgetContainer.php 5807 2013-03-08 05:18:13Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_analyticsDb.php');

class admin_admin_analyticsWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $graphTypeArray;	// グラフタイプ
	private $termTypeArray;		// 期間タイプ
	private $graphType;			// グラフ種別
	private $path;				// アクセスパス
	private $termType;				// 期間タイプ
	const DEFAULT_ACCESS_PATH = 'index';		// デフォルトのアクセスパス(PC用アクセスポイント)
	const ACCESS_PATH_ALL = '_all';				// アクセスパスすべて選択
	const DEFAULT_TERM_TYPE = '30day';		// デフォルトの期間タイプ
	const TERM_TYPE_ALL = '_all';				// 全データ表示選択
	const DEFAULT_GRAPH_TYPE = 'pageview';		// デフォルトのグラフ種別
	const DEFAULT_GRAPH_WIDTH = 800;		// グラフ幅
	const DEFAULT_GRAPH_HEIGHT = 320;		// グラフ高さ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_analyticsDb();
		
		// グラフタイプ
		$this->graphTypeArray = array(	array(	'name' => 'ページビュー',	'value' => 'pageview'),
										array(	'name' => '訪問数',			'value' => 'visit'),
										array(	'name' => '訪問者数',		'value' => 'visitor'));
										
		// 期間タイプ
		$this->termTypeArray = array(	array(	'name' => '30日',	'value' => '30day'),
										array(	'name' => '3ヶ月',	'value' => '3month'),
										array(	'name' => '6ヶ月',	'value' => '6month'),
										array(	'name' => '1年',	'value' => '1year'),
										array(	'name' => 'すべて',	'value' => self::TERM_TYPE_ALL));
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
		return 'admin.tmpl.html';
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
		
		// 入力値を取得
		$this->path = $request->trimValueOf('item_path');		// アクセスパス
		$this->termType = $request->trimValueOf('item_term');				// 期間タイプ
		$graphWidth = $request->trimValueOf('item_graph_width');		// グラフ幅
		$graphHeight = $request->trimValueOf('item_graph_height');		// グラフ高さ
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			// 入力チェック
			$this->checkNumeric($graphWidth, 'グラフ幅');
			$this->checkNumeric($graphHeight, 'グラフ高さ');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->path = $this->path;				// アクセスパス
				$paramObj->termType = $this->termType;		// 期間タイプ
				$paramObj->graphWidth = $graphWidth;		// グラフ幅
				$paramObj->graphHeight = $graphHeight;		// グラフ高さ
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			$replaceNew = true;			// データ再取得
		}
		
		if ($replaceNew){		// データ再取得のとき
			$paramObj = $this->getWidgetParamObj();
			if (empty($paramObj)){		// 既存データなしのとき
				// デフォルト値設定
				$this->path = self::DEFAULT_ACCESS_PATH;
				$this->termType = self::DEFAULT_TERM_TYPE;
				$graphWidth = self::DEFAULT_GRAPH_WIDTH;		// グラフ幅
				$graphHeight = self::DEFAULT_GRAPH_HEIGHT;		// グラフ高さ
			} else {
				$this->path = $paramObj->path;				// アクセスパス
				$this->termType = $paramObj->termType;		// 期間タイプ
				$graphWidth = $paramObj->graphWidth;		// グラフ幅
				$graphHeight = $paramObj->graphHeight;		// グラフ高さ
			}
		}
		
		// アクセスポイントメニュー作成
		$this->createPathMenu();
		
		// 期間メニュー作成
		$this->createTermMenu();
		
		// 値を埋め込む
		$this->tmpl->addVar("_widget", "graph_width", $graphWidth);// グラフ幅
		$this->tmpl->addVar("_widget", "graph_height", $graphHeight);// グラフ高さ
	}
	/**
	 * アクセスパスメニュー作成
	 *
	 * @return								なし
	 */
	function createPathMenu()
	{
		$selected = '';
		if ($this->path == self::ACCESS_PATH_ALL){// アクセスパスすべて選択
			$selected = 'selected';
		}
		$row = array(
			'value'    => self::ACCESS_PATH_ALL,			// アクセスパス
			'name'     => 'すべて表示',			// 表示文字列
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('path_list', $row);
		$this->tmpl->parseTemplate('path_list', 'a');
		
		$this->db->getPageIdList(array($this, 'pageIdLoop'), 0/*ページID*/);
	}
	/**
	 * ページID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['pg_path'] == $this->path){
			$selected = 'selected';
		}
		$name = $this->convertToDispString($fetchedRow['pg_path']) . ' - ' . $this->convertToDispString($fetchedRow['pg_name']);			// ページ名
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_path']),			// アクセスパス
			'name'     => $name,			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('path_list', $row);
		$this->tmpl->parseTemplate('path_list', 'a');
		return true;
	}
	/**
	 * 期間タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createTermMenu()
	{
		for ($i = 0; $i < count($this->termTypeArray); $i++){
			$value = $this->termTypeArray[$i]['value'];
			$name = $this->termTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->termType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('term_list', $row);
			$this->tmpl->parseTemplate('term_list', 'a');
		}
	}
}
?>
