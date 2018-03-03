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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConditionBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_analyzeDb.php');

class admin_mainAnalyzegraphWidgetContainer extends admin_mainConditionBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $analyzeDb;
	private $graphTypeArray;	// グラフタイプ
	private $termTypeArray;		// 期間タイプ
	private $graphType;			// グラフ種別
	private $path;				// アクセスパス
	private $termType;				// 期間タイプ
	private $completedDate;			// 処理終了日付
	private $graphDataStr;		// グラフデータ
	private $maxPv = 0;				// 日次のページビュー最大値
	private $yTickValueArray;		// Y軸の最大値
	private $startNo;			// 先頭行番号
	const CF_LAST_DATE_CALC_PV	= 'last_date_calc_pv';	// ページビュー集計の最終更新日
	const DEFAULT_STR_NOT_CALC = '未集計';		// 未集計時の表示文字列
	const DEFAULT_ACCESS_PATH = 'index';		// デフォルトのアクセスパス(PC用アクセスポイント)
	const ACCESS_PATH_ALL = '_all';				// アクセスパスすべて選択
	const DEFAULT_TERM_TYPE = '30day';		// デフォルトの期間タイプ
	const TERM_TYPE_ALL = '_all';				// 全データ表示選択
	const DEFAULT_GRAPH_TYPE = 'pageview';		// デフォルトのグラフ種別
	const DEFAULT_LIST_COUNT = 10;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 10;			// リンクページ数
	const LIB_JQPLOT = 'jquery.jqplot';		// ライブラリID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_analyzeDb();
		
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
		// Y軸の最大値
		$this->yTickValueArray = array(1000000, 500000, 100000, 50000, 10000, 5000, 1000, 500, 100, 0);
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
		$task = $request->trimValueOf('task');
		
		// ローカライズ処理
		$localeText = array();
		$localeText['label_range'] = $this->_('Range:');		// 範囲：
		$this->setLocaleText($localeText);
		
		return 'analyzegraph.tmpl.html';
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
		// 初期値設定
		$maxListCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
		
		$act = $request->trimValueOf('act');
		$this->graphType = $request->trimValueOf('graphtype');			// グラフ種別
		if (empty($this->graphType)) $this->graphType = self::DEFAULT_GRAPH_TYPE;
		$this->path = $request->trimValueOf('path');		// アクセスパス
		if (empty($this->path)) $this->path = self::DEFAULT_ACCESS_PATH;
		$this->termType = $request->trimValueOf('term');				// 期間タイプ
		if (empty($this->termType)) $this->termType = self::DEFAULT_TERM_TYPE;
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');			// ページ番号
		
		// 出力期間を取得
		$endDate = $this->db->getStatus(self::CF_LAST_DATE_CALC_PV);
		if (empty($endDate)){
			$this->setMsg(self::MSG_GUIDANCE, '集計済みのデータがありません。集計処理を行ってください。');
			$this->tmpl->setAttribute('show_graph', 'visibility', 'hidden');		// グラフ非表示
			$this->tmpl->setAttribute('draw_graph', 'visibility', 'hidden');		// グラフ非表示
		} else {
/*			// データの先頭の日付を求める
			$ret = $this->db->getOldAccessLog($row);
			if ($ret){		// 集計対象のデータが存在するとき
				$logStartDate = date("Y/m/d", strtotime($row['al_dt']));
			}*/
			// サイト解析ページビューの先頭のデータを取得
			$ret = $this->db->getOldPageView($row);
			if ($ret){
				$logStartDate = date("Y-m-d", strtotime($row['ap_date']));
			} else {			// 未集計の場合はアクセスログの先頭から日付を取得
				$ret = $this->db->getOldAccessLog($row);
				if ($ret) $logStartDate = date("Y-m-d", strtotime($row['al_dt']));
			}
			
			switch ($this->termType){
				case '30day':
					$startDate = date("Y/m/d", strtotime("$endDate -30 day"));			// 30日前
					break;
				case '3month':
					$startDate = date("Y/m/1", strtotime("$endDate -3 month"));		// 3ヶ月前
					break;
				case '6month':
					$startDate = date("Y/m/1", strtotime("$endDate -6 month"));		// 6ヶ月前
					break;
				case '1year':
					$startDate = date("Y/m/1", strtotime("$endDate -1 year"));			// 1年前
					break;
				case self::TERM_TYPE_ALL:		// すべてのデータのとき
					$startDate = NULL;
					break;
			}
			// データの先頭日付を修正
			$this->graphDataStr = '';		// グラフデータ
			if (is_null($startDate)){
				$this->completedDate = '';
			} else {
				// 先頭の日付を修正
				if (strtotime($startDate) < strtotime($logStartDate)) $startDate = $logStartDate;
				
				$this->completedDate = date("Y/m/d", strtotime("$startDate -1 day"));// 処理終了日付を前日に設定
			}
			
			// パスパラメータ作成
			$pathParam = $this->path;
			if ($pathParam == self::ACCESS_PATH_ALL){
				$pathParam = NULL;
			}
			
			// ##### グラフ種別に応じてデータ取得 #####
			switch ($this->graphType){
				case 'pageview':		//ページビュー
					// データ取得
					$this->db->getPageViewByDate($pathParam, $startDate, $endDate, array($this, 'pageViewDataLoop'));
					break;
				case 'visit':			// 訪問数
					$this->db->getDailyCountByDate(0/*訪問数*/, $pathParam, $startDate, $endDate, array($this, 'pageViewDataLoop'));
					break;
				case 'visitor':			// 訪問者数
					$this->db->getDailyCountByDate(1/*訪問者数*/, $pathParam, $startDate, $endDate, array($this, 'pageViewDataLoop'));
					break;
			}
			// 集計終了日までのデータを作成
			$graphStartDate = date("Y/m/d", strtotime("$this->completedDate 1 day"));		// 処理終了日翌日
			$this->createGraphData($graphStartDate, $endDate);
		
			// ##### グラフデータ埋め込み #####
			$this->graphDataStr = trim($this->graphDataStr, ',');		// グラフデータ
			if (empty($this->graphDataStr)){
				$this->setMsg(self::MSG_GUIDANCE, '集計済みのデータがありません');
				$this->tmpl->setAttribute('show_graph', 'visibility', 'hidden');		// グラフ非表示
				$this->tmpl->setAttribute('draw_graph', 'visibility', 'hidden');		// グラフ非表示
			} else {
				// グラフの開始終了期間
				if (empty($startDate)){
					$termStart = date("Y-m-d", strtotime("-30 day"));
				} else {
					$termStart = $startDate;
				}
				if (empty($endDate)){
					$termEnd = date("Y-m-d", strtotime("-1 day"));
				} else {
					$termEnd = date("Y-m-d", strtotime("$endDate"));
				}
				$this->tmpl->addVar("draw_graph", "term_start", $termStart);// グラフ期間開始
				$this->tmpl->addVar("draw_graph", "term_end", $termEnd);// グラフ期間終了
			
				// グラフデータ作成
				for ($i = 0; $i < count($this->yTickValueArray) -1; $i++){
					if ($this->maxPv >= $this->yTickValueArray[$i + 1]){
						$yMax = $this->yTickValueArray[$i];// Y座標最大値
						break;
					}
				}
				
				// グラフデータを設定
				$this->tmpl->addVar("draw_graph", "y_max", $yMax);
				$this->tmpl->addVar("draw_graph", "line1_data", $this->graphDataStr);
			}
		}
		// グラフ種別メニュー作成
		$this->createGraphTypeMenu();
		
		// アクセスポイントメニュー作成
		$this->createPathMenu();
		
		// 期間メニュー作成
		$this->createTermMenu();
		
		// ########## 上位URL一覧 ##########
		// 総数を取得
		if (empty($endDate)){		// 集計済みデータがないとき
			$totalCount = 0;
		} else {
			switch ($this->graphType){
				case 'pageview':		//ページビュー
					$totalCount = $this->db->getUrlListCountByPageView($pathParam, $startDate, $endDate);
					break;
				case 'visit':			// 訪問数
					$totalCount = $this->db->getUrlListCountByDailyCount(0/*訪問数*/, $pathParam, $startDate, $endDate);
					break;
				case 'visitor':			// 訪問者数
					$totalCount = $this->db->getUrlListCountByDailyCount(1/*訪問者数*/, $pathParam, $startDate, $endDate);
					break;
			}
		}

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $maxListCount);
		$this->startNo = ($pageNo -1) * $maxListCount +1;		// 先頭の行番号
		
		// 表示するページ番号の修正
/*		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$startNo = ($pageNo -1) * $maxListCount +1;		// 先頭の行番号
		$endNo = $pageNo * $maxListCount > $totalCount ? $totalCount : $pageNo * $maxListCount;// 最後の行番号
		$this->startNo = $startNo;			// 先頭の項目番号
		*/
		
		// ページング用リンク作成
/*		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i > self::LINK_PAGE_COUNT) break;			// 最大ページ数以上のときは終了
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="#" onclick="selectPage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}*/
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selectPage($1);return false;');

		$this->tmpl->addVar("_widget", "page_link", $pageLink);
//		$this->tmpl->addVar("_widget", "total_count", sprintf($this->_('%d Total'), $totalCount));
//		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
//		$this->tmpl->addVar("_widget", "view_count", $maxListCount);	// 最大表示項目数
		
		// 一覧作成
		if (!empty($endDate)){		// 集計済みデータがあるとき
			switch ($this->graphType){
				case 'pageview':		//ページビュー
					$this->db->getUrlListByPageView($pathParam, $startDate, $endDate, $maxListCount, $pageNo, array($this, 'pageViewLoop'));
					break;
				case 'visit':			// 訪問数
					$this->db->getUrlListByDailyCount(0/*訪問数*/, $pathParam, $startDate, $endDate, $maxListCount, $pageNo, array($this, 'pageViewLoop'));
					break;
				case 'visitor':			// 訪問者数
					$this->db->getUrlListByDailyCount(1/*訪問者数*/, $pathParam, $startDate, $endDate, $maxListCount, $pageNo, array($this, 'pageViewLoop'));
					break;
			}
		}
		
		// ライブラリパス
		$libDir = '';
		$libInfo = $this->gPage->getScriptLibInfo(self::LIB_JQPLOT);
		if (!empty($libInfo)) $libDir = $libInfo['dir'];
		$this->tmpl->addVar("_widget", "lib_dir", $libDir);
	}
	/**
	 * グラフ種別メニュー作成
	 *
	 * @return なし
	 */
	function createGraphTypeMenu()
	{
		for ($i = 0; $i < count($this->graphTypeArray); $i++){
			$value = $this->graphTypeArray[$i]['value'];
			$name = $this->graphTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->graphType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// グラフ種別ID
				'name'     => $name,			// グラフ種別
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('graphtype_list', $row);
			$this->tmpl->parseTemplate('graphtype_list', 'a');
		}
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
			'name'     => 'すべて',			// 表示文字列
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('path_list', $row);
		$this->tmpl->parseTemplate('path_list', 'a');
		
		$this->_mainDb->getPageIdList(array($this, 'pageIdLoop'), 0/*ページID*/);
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
		$name = $this->convertToDispString($fetchedRow['pg_name']);			// アクセスポイント名
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
	/**
	 * ページビューデータを取得
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageViewDataLoop($index, $fetchedRow, $param)
	{
		// 日付を取得
		switch ($this->graphType){
			case 'pageview':		//ページビュー
				$pvDate = $fetchedRow['ap_date'];
				break;
			case 'visit':			// 訪問数
			case 'visitor':			// 訪問者数
				$pvDate = $fetchedRow['aa_date'];
				break;
		}
		$endDate = date("Y/m/d", strtotime("$pvDate -1 day"));		// 1日前
		$total = $fetchedRow['total'];
		if ($total > $this->maxPv) $this->maxPv = $total;				// 日次のページビュー最大値
		
		// 0データの期間埋める
		if (!is_null($this->completedDate)){
			$date = date("Y/m/d", strtotime("$this->completedDate 1 day"));

			while (true){
				if (strtotime($date) > strtotime($endDate)) break;

				// グラフ用のデータ作成
				$this->graphDataStr .= '[\'' . $date . '\',0],';
				$date = date("Y/m/d", strtotime("$date 1 day"));
			}
		}
		
		// グラフ用のデータ作成
		$this->graphDataStr .= '[\'' . $pvDate . '\',' . $total . '],';

		// 処理終了日付を更新
		$this->completedDate = $pvDate;
		return true;
	}
	/**
	 * 集計終了日までのグラフデータを作成する
	 *
	 * @param date   $startDate		開始日
	 * @param date   $endDate		終了日
	 * @return						なし
	 */
	function createGraphData($startDate, $endDate)
	{
		$date = $startDate;
		while (true){
			if (strtotime($date) > strtotime($endDate)) break;

			// グラフ用のデータ作成
			$this->graphDataStr .= '[\'' . $date . '\',0],';
			$date = date("Y/m/d", strtotime("$date 1 day"));
		}
	}
	/**
	 * 上位URLアクセス数の一覧取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageViewLoop($index, $fetchedRow, $param)
	{
		// 先頭の項目番号
		$no = $this->startNo + $index;
		
		$row = array(
			'no' => $no,													// 行番号
			'url' => $this->convertToDispString($fetchedRow['url']),			// URL
			'preview_url' => $fetchedRow['url'],							// プレビュー画面用URL
			'count' => $fetchedRow['total']									// アクセス数
		);
		$this->tmpl->addVars('urllist', $row);
		$this->tmpl->parseTemplate('urllist', 'a');
		return true;
	}
}
?>
