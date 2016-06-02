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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_analyticsDb.php');

class admin_analyticsWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $termTypeArray;		// 期間タイプ
	private $graphType;			// グラフ種別
	private $path;				// アクセスパス
	private $pathArray;				// アクセスパス
	private $termType;				// 期間タイプ
	private $completedDate;			// 処理終了日付
	private $graphDataStr;		// グラフデータ
	private $maxPv = 0;				// 日次のページビュー最大値
	private $yTickValueArray;		// Y軸の最大値
	const CF_LAST_DATE_CALC_PV	= 'last_date_calc_pv';	// ページビュー集計の最終更新日
	const DEFAULT_STR_NOT_CALC = '未集計';		// 未集計時の表示文字列
	const DEFAULT_ACCESS_PATH = 'index';		// デフォルトのアクセスパス(PC用アクセスポイント)
	const ACCESS_PATH_ALL = '_all';				// アクセスパスすべて選択
	const DEFAULT_TERM_TYPE = '30day';		// デフォルトの期間タイプ
	const TERM_TYPE_ALL = '_all';				// 全データ表示選択
	const DEFAULT_GRAPH_TYPE = 'pageview';		// デフォルトのグラフ種別
	const DEFAULT_GRAPH_WIDTH = 800;		// グラフ幅
	const DEFAULT_GRAPH_HEIGHT = 280;		// グラフ高さ
	const LIB_JQPLOT = 'jquery.jqplot';		// ライブラリID
	const LINE_DATA_HEAD = 'line';			// ラインデータ変数名ヘッダ
			
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
		$this->termTypeArray = array(	array(	'name' => '10日',	'value' => '10day'),
										array(	'name' => '30日',	'value' => '30day'),
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
		return 'index.tmpl.html';
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
		// グラフ用のパラメータを取得
		$this->graphType = self::DEFAULT_GRAPH_TYPE;// グラフ種別
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
		// アクセスポイントパスを取得
		$this->pathArray = array();
		$ret = $this->db->getAnalyticsAccessPoint($rows);
		if ($ret){
			for ($i = 0; $i < count($rows); $i++){
				$this->pathArray[] = $rows[$i]['pg_path'];				// アクセスパス
			}
		}
		
		$act = $request->trimValueOf('act');
		if ($act == 'analytics_update'){		// 設定更新(再計算)のとき
			$messageArray = array();
			$ret = $this->gInstance->getAnalyzeManager()->updateAnalyticsData($messageArray);
			if ($ret){
				$this->setMsg(self::MSG_GUIDANCE, $messageArray[0]);
			} else {
				$this->setMsg(self::MSG_APP_ERR, $messageArray[0]);
			}
		}
		
		$showGraph = false;		// グラフを表示するかどうか
		$lineDataScript = '';	// ラインデータスクリプト
		$lineParam = '';		// ラインパラメータ
		
		// 出力期間を取得
		$endDate = $this->db->getStatus(self::CF_LAST_DATE_CALC_PV);
		if (empty($endDate)){		// 集計処理が一度も行われていないとき
			// 空のグラフを作成
			$yMax = 100;
			$lineDataScript .= M3_INDENT_SPACE . 'var ' . self::LINE_DATA_HEAD . ' = [[]];';		// ラインデータ
			$lineParam .= self::LINE_DATA_HEAD;
			$showGraph = true;		// グラフを表示
		} else {
			// データの先頭の日付を求める
			$ret = $this->db->getOldAccessLog($row);
			if ($ret){		// 集計対象のデータが存在するとき
				$logStartDate = date("Y-m-d", strtotime($row['al_dt']));
			}
			switch ($this->termType){
				case '10day':
					$startDate = date("Y-m-d", strtotime("$endDate -10 day"));			// 10日前
					break;
				case '30day':
					$startDate = date("Y-m-d", strtotime("$endDate -30 day"));			// 30日前
					break;
				case '3month':
					$startDate = date("Y-m-1", strtotime("$endDate -3 month"));		// 3ヶ月前
					break;
				case '6month':
					$startDate = date("Y-m-1", strtotime("$endDate -6 month"));		// 6ヶ月前
					break;
				case '1year':
					$startDate = date("Y-m-1", strtotime("$endDate -1 year"));			// 1年前
					break;
				case self::TERM_TYPE_ALL:		// すべてのデータのとき
					$startDate = NULL;
					break;
			}
			if (!is_null($startDate)){
				// 先頭の日付を修正
				if (strtotime($startDate) < strtotime($logStartDate)) $startDate = $logStartDate;
			}
				
			// グラフデータ作成
			$this->maxPv = 0;
			for ($i = 0; $i < count($this->pathArray); $i++){
				// パスパラメータ作成
				$pathParam = $this->pathArray[$i];
			
				// データの先頭日付を修正
				$this->graphDataStr = '';		// グラフデータ
				if (is_null($startDate)){
					$this->completedDate = '';
				} else {
					$this->completedDate = date("Y-m-d", strtotime("$startDate -1 day"));// 処理終了日付を前日に設定
				}

				// ##### グラフ種別に応じてデータ取得 #####
				switch ($this->graphType){
					case 'pageview':		//ページビュー
						// データ取得
						$this->db->getPageViewByDate($pathParam, $startDate, $endDate, array($this, 'pageViewDataLoop'));
						break;
//					case 'visit':			// 訪問数
//						$this->db->getDailyCountByDate(0/*訪問数*/, $pathParam, $startDate, $endDate, array($this, 'pageViewDataLoop'));
//						break;
//					case 'visitor':			// 訪問者数
//						$this->db->getDailyCountByDate(1/*訪問者数*/, $pathParam, $startDate, $endDate, array($this, 'pageViewDataLoop'));
//						break;
				}
				// 集計終了日までのデータを作成
				$nextStartDate = date("Y-m-d", strtotime("$this->completedDate 1 day"));		// 処理終了日翌日
				$this->createGraphData($nextStartDate, $endDate);
					
				$this->graphDataStr = trim($this->graphDataStr, ',');		// グラフデータ
				if (!empty($this->graphDataStr)) $showGraph = true;		// グラフを表示
				$lineDataScript .= M3_INDENT_SPACE . 'var ' . self::LINE_DATA_HEAD . $i . ' = [' . $this->graphDataStr . '];' . M3_NL;
				$lineParam .= self::LINE_DATA_HEAD . $i . ',';	// ラインパラメータ
			}
			// グラフY座標最大値取得
			for ($i = 0; $i < count($this->yTickValueArray) -1; $i++){
				if ($this->maxPv >= $this->yTickValueArray[$i + 1]){
					$yMax = $this->yTickValueArray[$i];// Y座標最大値
					break;
				}
			}
		}
		// グラフ表示制御
		if (!$showGraph){	// グラフ非表示の場合
			$this->setMsg(self::MSG_GUIDANCE, '集計済みのデータがありません');
			$this->tmpl->setAttribute('show_graph', 'visibility', 'hidden');		// グラフ非表示
			$this->tmpl->setAttribute('draw_graph', 'visibility', 'hidden');		// グラフ非表示
		}
		
		// グラフの開始終了期間
		if (empty($startDate)){
			$termStart = date("Y-m-d", strtotime("-30 day"));
		} else {
			$termStart = $startDate;
		}
/*		if (empty($this->completedDate)){
			$termEnd = date("Y-m-d", strtotime("-1 day"));
		} else {
			$termEnd = $this->completedDate;
		}*/
		if (empty($endDate)){
			$termEnd = date("Y-m-d", strtotime("-1 day"));
		} else {
			$termEnd = $endDate;
		}
		
		// 集計終了日表示用テキスト作成
		if (empty($endDate)){
			$lastData = self::DEFAULT_STR_NOT_CALC;
		} else {
			$lastData = $this->convertToDispDate($endDate);		// 最終集計日
		}
		// 値を埋め込む
		$this->tmpl->addVar("draw_graph", "term_start", $termStart);// グラフ期間開始
		$this->tmpl->addVar("draw_graph", "term_end", $termEnd);// グラフ期間終了
		$this->tmpl->addVar("draw_graph", "line_data", $lineDataScript);	// ラインデータスクリプト
		$this->tmpl->addVar("draw_graph", "line_param", trim($lineParam, ','));	// ラインパラメータ
		$this->tmpl->addVar("draw_graph", "y_max", $yMax);		// グラフY座標最大値
		$this->tmpl->addVar("_widget", "date", $lastData);// 最終集計日
		$this->tmpl->addVar("show_graph", "graph_width", $graphWidth);// グラフ幅
		$this->tmpl->addVar("show_graph", "graph_height", $graphHeight);// グラフ高さ
		
		// ライブラリパス
		$libDir = '';
		$libInfo = $this->gPage->getScriptLibInfo(self::LIB_JQPLOT);
		if (!empty($libInfo)) $libDir = $libInfo['dir'];
		$this->tmpl->addVar("_widget", "lib_dir", $libDir);
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
		$endDate = date("Y-m-d", strtotime("$pvDate -1 day"));		// 1日前
		$total = $fetchedRow['total'];
		if ($total > $this->maxPv) $this->maxPv = $total;				// 日次のページビュー最大値
		
		// 0データの期間埋める
		if (!is_null($this->completedDate)){
			$date = date("Y-m-d", strtotime("$this->completedDate 1 day"));

			while (true){
				if (strtotime($date) > strtotime($endDate)) break;

				// グラフ用のデータ作成
				$this->graphDataStr .= '[\'' . $date . '\',0],';
				$date = date("Y-m-d", strtotime("$date 1 day"));
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
			$date = date("Y-m-d", strtotime("$date 1 day"));
		}
	}
}
?>
