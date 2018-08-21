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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/blog_analyticsDb.php');

class admin_blog_mainAnalyticsWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $startDate;			// 集計期間(開始)
	private $endDate;			// 集計期間(終了)
	private $startYear;			// 集計期間(開始年)(集計タイプ月単位用)
	private $startMonth;		// 集計期間(開始月)(集計タイプ月単位用)
	private $calcTypeArray;		// 集計タイプ
	private $termTypeArray;		// 期間タイプ
	private $calcType;			// 選択中の集計タイプ
	private $termType;			// 選択中の期間タイプ
	private $yMaxTickArray;		// Y軸の最大値リスト
	private $weekTypeArray;		// 曜日表示名
	private $xTitleArray;		// X軸値
	private $yValueArray;		// Y軸値
	private $maxViewCount;				// コンテン参照数最大値
	private $graphDataKeyArray;			// グラフデータ取得用キー
	private $graphDataArray;			// グラフデータ(X軸値をキー、Y軸値を値とする連想配列)
	private $graphDataKeyFormat;		// グラフデータ用のキーフォーマット
	private $isContentViewData;			// アクセス解析データが存在するかどうか
	const TERM_TYPE_ALL = '_all';				// 全データ表示選択
	const DEFAULT_CALC_TYPE = 'day';			// デフォルトの集計タイプ
	const DEFAULT_TERM_TYPE = 'month';		// デフォルトの期間タイプ
	const DEFAULT_Y_TICK_VALUE = 100;		// デフォルトのY軸最大値
	const LIB_JQPLOT = 'jquery.jqplot';		// ライブラリID
	const DATE_FORMAT = 'Y-m-d';			// 日付表現のフォーマット
	const KEY_FORMAT_DAY = 'Y-m-d';			// グラフデータ取得用キーのフォーマット(日)
	const KEY_FORMAT_MONTH = 'Y-m';			// グラフデータ取得用キーのフォーマット(月)
	const KEY_FORMAT_HOUR = 'H';			// グラフデータ取得用キーのフォーマット(時間)
	const KEY_FORMAT_WEEK = 'D';			// グラフデータ取得用キーのフォーマット(週)
	const DEFAULT_LIST_COUNT = 20;			// リスト表示数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new blog_analyticsDb();
		
		// 初期値設定
		$this->maxViewCount = 0;				// コンテン参照数最大値
		$this->graphDataArray = array();	// グラフ用データ(X軸値をキー、Y軸値を値とする連想配列)
		
		// 集計タイプ
		$this->calcTypeArray = array(
										array(	'name' => '日単位',		'value' => 'day',	'default_term' => 'month'),
										array(	'name' => '月単位',		'value' => 'month',	'default_term' => 'year'),
										array(	'name' => '時間単位',	'value' => 'hour',	'default_term' => 'month'),
										array(	'name' => '曜日単位',	'value' => 'week',	'default_term' => 'month')
									);

		// 期間タイプ
		$this->termTypeArray = array(	array(	'name' => '1ヶ月',	'value' => 'month'),					// 日、月、時間の場合は30日。週の場合は4週間。
										array(	'name' => '3ヶ月',	'value' => '3month'),
										array(	'name' => '6ヶ月',	'value' => '6month'),
										array(	'name' => '1年',	'value' => 'year'),
										array(	'name' => 'すべて',	'value' => self::TERM_TYPE_ALL));
										
		// Y軸の最大値リスト
		$this->yMaxTickArray = array(1000000, 500000, 100000, 50000, 10000, 5000, 1000, 500, 100, 0);
		
		// 曜日表示名
		$this->weekTypeArray = array('日', '月', '火', '水', '木', '金', '土');
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
		return 'admin_analytics.tmpl.html';
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
		$this->calcType = $request->trimValueOf('item_calc_type');		// 集計方法
		if (empty($this->calcType)) $this->calcType = self::DEFAULT_CALC_TYPE;			// デフォルトの集計タイプ
		$this->termType = $this->getDefaultTermType($this->calcType);	// デフォルトの期間タイプを取得
		$this->startDate = $request->trimValueOf('startdate');
		$this->endDate = $request->trimValueOf('enddate');
		if (!empty($this->startDate)) $this->startDate = date(self::DATE_FORMAT, strtotime($this->startDate));
		if (!empty($this->endDate)) $this->endDate = date(self::DATE_FORMAT, strtotime($this->endDate));
		$backCalcType = $request->trimValueOf('back_calctype');
		
		// キーのフォーマットを取得
		switch ($this->calcType){
		case 'day':
			$this->graphDataKeyFormat = self::KEY_FORMAT_DAY;		// グラフデータ用のキーフォーマット
			break;
		case 'month':
			$this->graphDataKeyFormat = self::KEY_FORMAT_MONTH;		// グラフデータ用のキーフォーマット
			break;
		case 'hour':
			$this->graphDataKeyFormat = self::KEY_FORMAT_HOUR;		// グラフデータ用のキーフォーマット
			break;
		case 'week':
			$this->graphDataKeyFormat = self::KEY_FORMAT_WEEK;		// グラフデータ用のキーフォーマット
			break;
		}
		
		// ##### 集計期間を作成 #####
		// *** 日単位の場合のみ本日のデータを含んでグラフを作成する ***
		$baseDate = '';		// 基準日は空文字列(本日)で初期化する
		
		// 集計開始日を取得
		if (empty($this->startDate)){
			if ($this->calcType == 'day'){			// 日単位で集計の場合
				switch ($this->termType){		// 期間タイプ
				case 'month':
					$this->startDate = date(self::DATE_FORMAT, strtotime("$baseDate -30 day"));			// 1ヶ月前
					break;
				case '3month':
					$this->startDate = date(self::DATE_FORMAT, strtotime("$baseDate -90 day"));		// 3ヶ月前
					break;
				case '6month':
					$this->startDate = date(self::DATE_FORMAT, strtotime("$baseDate -180 day"));		// 6ヶ月前
					break;
				case 'year':
					$this->startDate = date(self::DATE_FORMAT, strtotime("$baseDate -1 year"));			// 1年前
					break;
				case self::TERM_TYPE_ALL:		// すべてのデータのとき
					$this->startDate = NULL;
					break;
				}
			} else if ($this->calcType == 'month'){			// 月単位で集計の場合
				// 本日を含まないデータでグラフを作成
				switch ($this->termType){		// 期間タイプ
				case 'year':
					$this->startYear = intval(date('Y')) -1;
					$this->startMonth = intval(date('n')) + 1;
					if ($this->startMonth > 12){
						$this->startYear++;
						$this->startMonth = 1;
					}
					$this->startDate = date(self::DATE_FORMAT, strtotime("$this->startYear-$this->startMonth-1"));			// 1年前
					break;
				case self::TERM_TYPE_ALL:		// すべてのデータのとき
					$this->startDate = NULL;
					break;
				}
			} else if ($this->calcType == 'hour'){			// 時間単位で集計の場合
				switch ($this->termType){		// 期間タイプ
				case 'month':
					$this->startDate = date(self::DATE_FORMAT, strtotime("$baseDate -28 day"));		// 4週間
					break;
				case self::TERM_TYPE_ALL:		// すべてのデータのとき
					$this->startDate = NULL;
					break;
				}
			} else if ($this->calcType == 'week'){				// 曜日単位で集計の場合
				switch ($this->termType){		// 期間タイプ
				case 'month':
					$this->startDate = date(self::DATE_FORMAT, strtotime("$baseDate -28 day"));		// 4週間
					break;
				case self::TERM_TYPE_ALL:		// すべてのデータのとき
					$this->startDate = NULL;
					break;
				}
			}
			// ##### 集計終了日 #####
			// 日単位で集計する場合は本日のデータも表示
			if ($this->calcType == 'day'){			// 日単位で集計の場合
				$this->endDate = date(self::DATE_FORMAT);		// 本日を含める
			} else {
				$this->endDate = date(self::DATE_FORMAT, strtotime("-1 day"));	// 前日
			}
		} else {
			// 開始日が設定されている場合は日数分取得
		}

		// ##### イベント処理 #####
		if ($act == 'changetype'){		// 集計タイプの変更のとき
		}
		
		// 集計タイプメニュー作成
		$this->createCalcTypeMenu();
		
		// ##### グラフ用データ作成 #####
		$this->createGraphData();
		
		// グラフ用データをスクリプト化
//		$graphDataXStr = '[' . implode(', ', array_map(create_function('$a','return "\'" . $a . "\'";'), $this->xTitleArray)) . ']';
		$graphDataXStr = '[' . implode(', ', array_map(function($a){ return "'" . $a . "'"; }, $this->xTitleArray)) . ']';
		$graphDataYStr = '[' . implode(', ', $this->yValueArray) . ']';
//		$graphDataKeyStr = '[' . implode(', ', array_map(create_function('$a','return "\'" . $a . "\'";'), $this->graphDataKeyArray)) . ']';
		$graphDataKeyStr = '[' . implode(', ', array_map(function($a){ return "'" . $a . "'"; }, $this->graphDataKeyArray)) . ']';

		// グラフY座標最大値取得
		$yMax = self::DEFAULT_Y_TICK_VALUE;
		for ($i = 0; $i < count($this->yMaxTickArray) -1; $i++){
			if ($this->maxViewCount >= $this->yMaxTickArray[$i + 1]){
				$yMax = $this->yMaxTickArray[$i];// Y座標最大値
				break;
			}
		}
		
		// ##### 上位コンテンツ一覧を作成 #####
		// 上位コンテンツを取得
		$this->db->getTopContentByDateRange(blog_mainCommonDef::VIEW_CONTENT_TYPE, $this->startDate, $this->endDate, self::DEFAULT_LIST_COUNT, 1/*先頭ページ*/, $this->_langId, array($this, 'contentListLoop'));
		if (!$this->isContentViewData){		// アクセス解析データが存在しない場合
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
			$this->setGuidanceMsg('アクセス解析データがありません');
		}
		
		// X軸タイトル作成
		array_unshift($this->xTitleArray, '総数');// 左端は総数のカラムを追加
		$xTitleCount = count($this->xTitleArray);
		for ($i = 0; $i < $xTitleCount; $i++){
			$row = array(
				'date'    => $this->convertToDispString($this->xTitleArray[$i])			// X軸タイトル
			);
			$this->tmpl->addVars('datelist', $row);
			$this->tmpl->parseTemplate('datelist', 'a');
		}
		
		// ライブラリパス
		$libDir = '';
		$libInfo = $this->gPage->getScriptLibInfo(self::LIB_JQPLOT);
		if (!empty($libInfo)) $libDir = $libInfo['dir'];
		$this->tmpl->addVar('_widget', 'lib_dir', $libDir);
		
		// 表示用期間
		if ($this->startDate == $this->endDate){		// 1日分のアクセス解析を表示する場合
			$termStr = '日付：' . $this->convertToDispDate($this->startDate);
			
			// 戻るボタン表示
			$this->tmpl->setAttribute('cancel_button', 'visibility', 'visible');
			
			// 戻り用集計タイプ
			$this->tmpl->addVar('_widget', 'calc_type', $backCalcType);		// 集計タイプ
		} else {
			$termStr = '期間：' . $this->convertToDispDate($this->startDate) . ' ～ ' . $this->convertToDispDate($this->endDate);
			
			// 戻り用集計タイプ
			$this->tmpl->addVar('_widget', 'calc_type', $this->calcType);		// 集計タイプ
		}
		
		// 値を埋め込む
		$this->tmpl->addVar('_widget', 'term', $termStr);		// 集計期間
		$this->tmpl->addVar('draw_graph', 'x_ticks', $graphDataXStr);		// グラフX軸タイトル
		$this->tmpl->addVar('draw_graph', 'y_values', $graphDataYStr);		// グラフY軸値
		$this->tmpl->addVar('draw_graph', 'keys', $graphDataKeyStr);		// グラフデータキー
		$this->tmpl->addVar('draw_graph', 'y_max', $yMax);		// グラフY座標最大値
	}
	/**
	 * 集計タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createCalcTypeMenu()
	{
		for ($i = 0; $i < count($this->calcTypeArray); $i++){
			$value = $this->calcTypeArray[$i]['value'];
			$name = $this->calcTypeArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// 集計タイプID
				'name'     => $name,			// 集計タイプ名
				'selected' => $this->convertToSelectedString($value, $this->calcType)			// 選択中かどうか
			);
			$this->tmpl->addVars('item_calc_type_list', $row);
			$this->tmpl->parseTemplate('item_calc_type_list', 'a');
		}
	}
	/**
	 * 集計タイプからデフォルトの期間タイプを取得
	 *
	 * @param string $calcType	集計タイプ
	 * @return string			期間タイプ
	 */
	function getDefaultTermType($calcType)
	{
		$defaultTerm = '';
		for ($i = 0; $i < count($this->calcTypeArray); $i++){
			$value = $this->calcTypeArray[$i]['value'];
			$name = $this->calcTypeArray[$i]['name'];
			if ($value == $calcType){
				$defaultTerm = $this->calcTypeArray[$i]['default_term'];
				break;
			}
		}
		return $defaultTerm;
	}
	/**
	 * グラフ用データ取得
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function contentViewCountLoop($index, $fetchedRow, $param)
	{
		// 参照数を取得
		switch ($this->calcType){
		case 'day':
			$graphDataKey = date($this->graphDataKeyFormat, strtotime($fetchedRow['day']));
			break;
		case 'month':
			$year = $fetchedRow['year'];
			$month = $fetchedRow['month'];
			$graphDataKey = date($this->graphDataKeyFormat, strtotime("$year-$month-1"));
			break;
		case 'hour':
			$graphDataKey = $fetchedRow['hour'];
			break;
		case 'week':
			$graphDataKey = $fetchedRow['week'];
			break;
		}

		$this->graphDataArray[$graphDataKey] = $fetchedRow['total'];	// グラフ用データ(X軸値をキー、Y軸値を値とする連想配列)

		$total = $fetchedRow['total'];
		if ($total > $this->maxViewCount) $this->maxViewCount = $total;				// コンテンツ参照数最大値
		
		return true;
	}
	/**
	 * コンテンツ一覧取得
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function contentListLoop($index, $fetchedRow, $param)
	{
		$contentId = $fetchedRow['vc_content_id'];
		$total = $fetchedRow['total'];
		$viewData = $this->getContentViewData($contentId);
		
		// ##### アクセス数一覧を作成 #####
		$this->tmpl->clearTemplate('countlist');
		
		// 先頭に総数を追加
		$row = array(
			'count'		=> $total
		);
		$this->tmpl->addVars('countlist', $row);
		$this->tmpl->parseTemplate('countlist', 'a');
			
		// 先頭以降のデータを追加
		$dateKeyCount = count($this->graphDataKeyArray);
		for ($i = 0; $i < $dateKeyCount; $i++){
			$dateKey = $this->graphDataKeyArray[$i];
			$accessCount = $viewData[$dateKey];
			if (!isset($accessCount)) $accessCount = 0;
			
			$row = array(
				'count'		=> $accessCount
			);
			$this->tmpl->addVars('countlist', $row);
			$this->tmpl->parseTemplate('countlist', 'a');
		}

		$row = array(
			'name' => $this->convertToDispString($fetchedRow['be_name'])		// 名前
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		$this->isContentViewData = true;		// アクセス解析データが存在する
		return true;
	}
	/**
	 * コンテンツごとのアクセス数データを取得
	 *
	 * @param int    $contentId			コンテンツID
	 * @return array					集計データ
	 */
	function getContentViewData($contentId)
	{
		$total = 0;
		$viewData = array();
		
		switch ($this->calcType){
		case 'day':
			$ret = $this->db->getContentViewCountByDate(blog_mainCommonDef::VIEW_CONTENT_TYPE, $contentId, $this->startDate, $this->endDate, $rows);
			if ($ret){
				$rowCount = count($rows);
				for ($i = 0; $i < $rowCount; $i++){
					$row = $rows[$i];
					$graphDataKey = date($this->graphDataKeyFormat, strtotime($row['day']));
					$dayTotal = $row['total'];
					$viewData[$graphDataKey] = $dayTotal;	// 日単位のアクセス数
					$total += $dayTotal;
				}
				$viewData['total'] = $total;		// 総アクセス数
			}
			break;
		case 'month':
			$ret = $this->db->getContentViewCountByMonth(blog_mainCommonDef::VIEW_CONTENT_TYPE, $contentId, $this->startDate, $this->endDate, $rows);
			if ($ret){
				$rowCount = count($rows);
				for ($i = 0; $i < $rowCount; $i++){
					$row = $rows[$i];
					$year = $row['year'];
					$month = $row['month'];
					$graphDataKey = date($this->graphDataKeyFormat, strtotime("$year-$month-1"));
					$dayTotal = $row['total'];
					$viewData[$graphDataKey] = $dayTotal;	// 日単位のアクセス数
					$total += $dayTotal;
				}
				$viewData['total'] = $total;		// 総アクセス数
			}
			break;
		case 'hour':
			$ret = $this->db->getContentViewCountByHour(blog_mainCommonDef::VIEW_CONTENT_TYPE, $contentId, $this->startDate, $this->endDate, $rows);
			if ($ret){
				$rowCount = count($rows);
				for ($i = 0; $i < $rowCount; $i++){
					$row = $rows[$i];
					$graphDataKey = $row['hour'];
					$dayTotal = $row['total'];
					$viewData[$graphDataKey] = $dayTotal;	// 日単位のアクセス数
					$total += $dayTotal;
				}
				$viewData['total'] = $total;		// 総アクセス数
			}
			break;
		case 'week':
			$ret = $this->db->getContentViewCountByWeek(blog_mainCommonDef::VIEW_CONTENT_TYPE, $contentId, $this->startDate, $this->endDate, $rows);
			if ($ret){
				$rowCount = count($rows);
				for ($i = 0; $i < $rowCount; $i++){
					$row = $rows[$i];
					$graphDataKey = $row['week'];
					$dayTotal = $row['total'];
					$viewData[$graphDataKey] = $dayTotal;	// 日単位のアクセス数
					$total += $dayTotal;
				}
				$viewData['total'] = $total;		// 総アクセス数
			}
			break;
		}
		return $viewData;
	}
	/**
	 * グラフ用データ作成
	 *
	 * @return					なし
	 */
	function createGraphData()
	{
		// ##### 集計グラフ作成 #####
		$this->xTitleArray = array();
		$this->yValueArray = array();
		$this->graphDataKeyArray = array();		// グラフデータ取得用キー
		
		// 集計グラフ用データ取得
		if ($this->calcType == 'day'){			// 日単位で集計の場合
			$this->db->getAllContentViewCountByDate(blog_mainCommonDef::VIEW_CONTENT_TYPE, $this->startDate, $this->endDate, array($this, 'contentViewCountLoop'));
			
			// X軸ラベル作成
			$dateTimestamp	= strtotime($this->startDate);
			$startTimestamp	= $dateTimestamp;
			$endTimestamp	= strtotime($this->endDate);
			$date = $this->startDate;
			$graphDataKey = date($this->graphDataKeyFormat, $dateTimestamp);
			while (true){
				if ($dateTimestamp > $endTimestamp) break;
				$this->graphDataKeyArray[] = $graphDataKey;			// グラフデータ取得用キー

				// グラフ用のデータ作成
				$this->xTitleArray[] = date('n/j', $dateTimestamp);		// X軸タイトル。表示フォーマットに変換。
				$value = $this->graphDataArray[$graphDataKey];
				if (isset($value)){
					$this->yValueArray[] = intval($value);
				} else {
					$this->yValueArray[] = 0;
				}
				$dateTimestamp = strtotime("$date 1 day");
				$date = date(self::DATE_FORMAT, $dateTimestamp);		// 次の日に更新
				$graphDataKey = date($this->graphDataKeyFormat, $dateTimestamp);
			}
			
			$this->tmpl->setAttribute('graph_clickable', 'visibility', 'visible');			// 1日分のグラフ画面への遷移を可能にする
		} else if ($this->calcType == 'month'){			// 月単位で集計の場合
			$this->db->getAllContentViewCountByMonth(blog_mainCommonDef::VIEW_CONTENT_TYPE, $this->startDate, $this->endDate, array($this, 'contentViewCountLoop'));

			// X軸ラベル作成
			$year = $this->startYear;			// 期間開始年
			$month = $this->startMonth;			// 期間開始月
			$dateTimestamp	= strtotime("$year-$month-1");
			$graphDataKey = date($this->graphDataKeyFormat, $dateTimestamp);

			for ($i = 0; $i < 12; $i++){
				$this->graphDataKeyArray[] = $graphDataKey;			// グラフデータ取得用キー

				// グラフ用のデータ作成
				$this->xTitleArray[] = date('n月', $dateTimestamp);		// X軸タイトル。表示フォーマットに変換。
				$value = $this->graphDataArray[$graphDataKey];
				if (isset($value)){
					$this->yValueArray[] = intval($value);
				} else {
					$this->yValueArray[] = 0;
				}

				// 次の月へ進む
				$month++;
				if ($month > 12){
					$month = 1;
					$year++;
				}
				$dateTimestamp	= strtotime("$year-$month-1");
				$graphDataKey = date($this->graphDataKeyFormat, $dateTimestamp);
			}
		} else if ($this->calcType == 'hour'){			// 時間単位で集計の場合
			$this->db->getAllContentViewCountByHour(blog_mainCommonDef::VIEW_CONTENT_TYPE, $this->startDate, $this->endDate, array($this, 'contentViewCountLoop'));

			// X軸ラベル作成
			for ($i = 0; $i < 24; $i++){
				$this->graphDataKeyArray[] = $i;			// グラフデータ取得用キー

				// グラフ用のデータ作成
				$this->xTitleArray[] = $i . '時';		// X軸タイトル(時間)
				$value = $this->graphDataArray[$i];
				if (isset($value)){
					$this->yValueArray[] = intval($value);
				} else {
					$this->yValueArray[] = 0;
				}
			}
		} else if ($this->calcType == 'week'){			// 曜日単位で集計の場合
			$this->db->getAllContentViewCountByWeek(blog_mainCommonDef::VIEW_CONTENT_TYPE, $this->startDate, $this->endDate, array($this, 'contentViewCountLoop'));

			// X軸ラベル作成
			for ($i = 0; $i < 7; $i++){
				$this->graphDataKeyArray[] = $i;			// グラフデータ取得用キー

				// グラフ用のデータ作成
				$this->xTitleArray[] = $this->weekTypeArray[$i];		// X軸タイトル(曜日)
				$value = $this->graphDataArray[$i];
				if (isset($value)){
					$this->yValueArray[] = intval($value);
				} else {
					$this->yValueArray[] = 0;
				}
			}
		}
	}
}
?>
