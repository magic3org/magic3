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
	private $calcTypeArray;		// 集計タイプ
	private $termTypeArray;		// 期間タイプ
	private $calcType;			// 選択中の集計タイプ
	private $termType;			// 選択中の期間タイプ
	private $yTickValueArray;	// Y軸の最大値リスト
	private $maxViewCount;				// コンテン参照数最大値
	private $graphDataKeyArray;		// グラフデータ取得用キー
	private $graphDataArray;	// グラフデータ(X軸値をキー、Y軸値を値とする連想配列)
	private $graphDataKeyFormat;		// グラフデータ用のキーフォーマット
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
//		$this->calcType	= self::DEFAULT_CALC_TYPE;			// 選択中の集計タイプ
//		$this->termType	= self::DEFAULT_TERM_TYPE;			// 選択中の期間タイプ
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
		
		if ($act == 'changetype'){		// 集計タイプの変更のとき
		}
		
		// 集計開始日を取得
		if (empty($this->startDate)){
			if ($this->calcType == 'day'){			// 日単位で集計の場合
				switch ($this->termType){		// 期間タイプ
					case 'month':
						$this->startDate = date(self::DATE_FORMAT, strtotime("$this->endDate -30 day"));			// 1ヶ月前
						break;
					case '3month':
						$this->startDate = date(self::DATE_FORMAT, strtotime("$this->endDate -90 day"));		// 3ヶ月前
						break;
					case '6month':
						$this->startDate = date(self::DATE_FORMAT, strtotime("$this->endDate -180 day"));		// 6ヶ月前
						break;
					case 'year':
						$this->startDate = date(self::DATE_FORMAT, strtotime("$this->endDate -1 year"));			// 1年前
						break;
					case self::TERM_TYPE_ALL:		// すべてのデータのとき
						$this->startDate = NULL;
						break;
				}
				
				$this->graphDataKeyFormat = self::KEY_FORMAT_DAY;		// グラフデータ用のキーフォーマット
				
			} else if ($this->calcType == 'month'){			// 月単位で集計の場合
				switch ($this->termType){		// 期間タイプ
					case 'year':
						$startMonth = intval(date('n')) + 1;
						$startYear = intval(date('Y')) -1;
						if ($startMonth > 12){
							$startMonth = 1;
							$startYear++;
						}
						$this->startDate = date(self::DATE_FORMAT, strtotime("$startYear-$startMonth-1"));			// 1年前
						break;
					case self::TERM_TYPE_ALL:		// すべてのデータのとき
						$this->startDate = NULL;
						break;
				}
				
				$this->graphDataKeyFormat = self::KEY_FORMAT_MONTH;		// グラフデータ用のキーフォーマット
				
			} else if ($this->calcType == 'week'){				// 曜日単位で集計の場合
				switch ($this->termType){		// 期間タイプ
					case 'month':
						$this->startDate = date(self::DATE_FORMAT, strtotime("$this->endDate -28 day"));			// 1ヶ月前
						break;
					case '3month':
						$this->startDate = date(self::DATE_FORMAT, strtotime("$this->endDate -86 day"));		// 3ヶ月前
						break;
					case '6month':
						$this->startDate = date(self::DATE_FORMAT, strtotime("$this->endDate -172 day"));		// 6ヶ月前
						break;
					case 'year':
						$this->startDate = date(self::DATE_FORMAT, strtotime("$this->endDate -1 year"));			// 1年前
						break;
					case self::TERM_TYPE_ALL:		// すべてのデータのとき
						$this->startDate = NULL;
						break;
				}
				
				$this->graphDataKeyFormat = self::KEY_FORMAT_WEEK;		// グラフデータ用のキーフォーマット
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

		// 集計タイプメニュー作成
		$this->createCalcTypeMenu();
		
		// ##### 集計グラフ作成 #####
		// 集計グラフ用データ取得
		$this->db->getAllContentViewCountByDate(blog_mainCommonDef::VIEW_CONTENT_TYPE, $this->startDate, $this->endDate, array($this, 'contentViewCountLoop'));
		
		// X軸ラベル作成
		$xTitleArray = array();
		$yValueArray = array();
		$this->graphDataKeyArray = array();		// グラフデータ取得用キー
		$dateTimestamp	= strtotime($this->startDate);
		$startTimestamp	= $dateTimestamp;
		$endTimestamp	= strtotime($this->endDate);
		$date = $this->startDate;
		$graphDataKey = date($this->graphDataKeyFormat, $dateTimestamp);
		while (true){
			if ($dateTimestamp > $endTimestamp) break;
			$this->graphDataKeyArray[] = $graphDataKey;			// グラフデータ取得用キー

			// グラフ用のデータ作成
			$xTitleArray[] = date('n/j', $dateTimestamp);		// X軸タイトル。表示フォーマットに変換。
			$value = $this->graphDataArray[$graphDataKey];
			if (isset($value)){
				$yValueArray[] = intval($value);
			} else {
				$yValueArray[] = 0;
			}
			$dateTimestamp = strtotime("$date 1 day");
			$date = date(self::DATE_FORMAT, $dateTimestamp);		// 次の日に更新
			$graphDataKey = date($this->graphDataKeyFormat, $dateTimestamp);
		}
		$graphDataXStr = '[' . implode(', ', array_map(create_function('$a','return "\'" . $a . "\'";'), $xTitleArray)) . ']';
		$graphDataYStr = '[' . implode(', ', $yValueArray) . ']';

		// グラフY座標最大値取得
		$yMax = self::DEFAULT_Y_TICK_VALUE;
		for ($i = 0; $i < count($this->yTickValueArray) -1; $i++){
			if ($this->maxViewCount >= $this->yTickValueArray[$i + 1]){
				$yMax = $this->yTickValueArray[$i];// Y座標最大値
				break;
			}
		}
		
		// ##### 上位コンテンツ一覧を作成 #####
		// 上位コンテンツを取得
		$this->db->getTopContentByDateRange(blog_mainCommonDef::VIEW_CONTENT_TYPE, $this->startDate, $this->endDate, self::DEFAULT_LIST_COUNT, 1/*先頭ページ*/, $this->_langId, array($this, 'contentListLoop'));
		
		// X軸タイトル作成
		array_unshift($xTitleArray, '総数');// 左端は総数のカラムを追加
		$xTitleCount = count($xTitleArray);
		for ($i = 0; $i < $xTitleCount; $i++){
			$row = array(
				'date'    => $this->convertToDispString($xTitleArray[$i])			// X軸タイトル
			);
			$this->tmpl->addVars('datelist', $row);
			$this->tmpl->parseTemplate('datelist', 'a');
		}
		
		// ライブラリパス
		$libDir = '';
		$libInfo = $this->gPage->getScriptLibInfo(self::LIB_JQPLOT);
		if (!empty($libInfo)) $libDir = $libInfo['dir'];
		$this->tmpl->addVar('_widget', 'lib_dir', $libDir);
		
		// 値を埋め込む
		$this->tmpl->addVar('_widget', 'start_date', $this->convertToDispDate($this->startDate));		// 集計期間(開始)
		$this->tmpl->addVar('_widget', 'end_date', $this->convertToDispDate($this->endDate));		// 集計期間(終了)
		$this->tmpl->addVar('draw_graph', 'x_ticks', $graphDataXStr);		// グラフX軸タイトル
		$this->tmpl->addVar('draw_graph', 'y_values', $graphDataYStr);		// グラフY軸値
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
		//$date = date(self::DATE_FORMAT, strtotime($fetchedRow['vc_date']));
		$date = date($this->graphDataKeyFormat, strtotime($fetchedRow['vc_date']));
		$this->graphDataArray[$date] = $fetchedRow['total'];	// グラフ用データ(X軸値をキー、Y軸値を値とする連想配列)

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
		$ret = $this->db->getContentViewCountByDate(blog_mainCommonDef::VIEW_CONTENT_TYPE, $contentId, $this->startDate, $this->endDate, $rows);
		if ($ret){
			$rowCount = count($rows);
			for ($i = 0; $i < $rowCount; $i++){
				$row = $rows[$i];
				$date = date(self::DATE_FORMAT, strtotime($row['vc_date']));
				$dayTotal = $row['total'];
				$viewData[$date] = $dayTotal;	// 日単位のアクセス数
				$total += $dayTotal;
			}
			$viewData['total'] = $total;		// 総アクセス数
		}
		return $viewData;
	}
}
?>
