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
	private $calcTypeArray;		// 集計タイプ
	private $termTypeArray;		// 期間タイプ
	private $calcType;			// 選択中の集計タイプ
	private $termType;			// 選択中の期間タイプ
	private $yTickValueArray;	// Y軸の最大値リスト
	private $maxViewCount;				// コンテン参照数最大値
	private $graphDataArray;	// グラフ用データ(X軸値をキー、Y軸値を値とする連想配列)
	const TERM_TYPE_ALL = '_all';				// 全データ表示選択
	const DEFAULT_CALC_TYPE = 'day';			// デフォルトの集計タイプ
	const DEFAULT_TERM_TYPE = 'month';		// デフォルトの期間タイプ
	const DEFAULT_Y_TICK_VALUE = 100;		// デフォルトのY軸最大値
	const LIB_JQPLOT = 'jquery.jqplot';		// ライブラリID
	const DATE_KEY_FORMAT = 'Y-m-d';		// 参照数管理用の日付キーのフォーマット
	
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
		$this->calcType	= self::DEFAULT_CALC_TYPE;			// 選択中の集計タイプ
		$this->termType	= self::DEFAULT_TERM_TYPE;			// 選択中の期間タイプ
		$this->maxViewCount = 0;				// コンテン参照数最大値
		$this->graphDataArray = array();	// グラフ用データ(X軸値をキー、Y軸値を値とする連想配列)
		
		// 集計タイプ
		$this->calcTypeArray = array(
										array(	'name' => '日単位',		'value' => 'day'),
										array(	'name' => '時間単位',	'value' => 'hour'),
										array(	'name' => '月単位',		'value' => 'month'),
										array(	'name' => '週単位',		'value' => 'week')
									);

		// 期間タイプ
		$this->termTypeArray = array(	array(	'name' => '1ヶ月',	'value' => 'month'),					// 日、月、時間の場合は30日。週の場合は4週間。
										array(	'name' => '3ヶ月',	'value' => '3month'),
										array(	'name' => '6ヶ月',	'value' => '6month'),
										array(	'name' => '1年',	'value' => '1year'),
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
		// 集計開始日を取得
		if (empty($startDate)){
			if ($this->calcType == 'week'){				// 週単位で集計の場合
				switch ($this->termType){		// 期間タイプ
					case 'month':
						$startDate = date(self::DATE_KEY_FORMAT, strtotime("$endDate -28 day"));			// 1ヶ月前
						break;
					case '3month':
						$startDate = date(self::DATE_KEY_FORMAT, strtotime("$endDate -86 day"));		// 3ヶ月前
						break;
					case '6month':
						$startDate = date(self::DATE_KEY_FORMAT, strtotime("$endDate -172 day"));		// 6ヶ月前
						break;
					case '1year':
						$startDate = date(self::DATE_KEY_FORMAT, strtotime("$endDate -1 year"));			// 1年前
						break;
					case self::TERM_TYPE_ALL:		// すべてのデータのとき
						$startDate = NULL;
						break;
				}
			} else {
				switch ($this->termType){		// 期間タイプ
					case 'month':
						$startDate = date(self::DATE_KEY_FORMAT, strtotime("$endDate -30 day"));			// 1ヶ月前
						break;
					case '3month':
						$startDate = date(self::DATE_KEY_FORMAT, strtotime("$endDate -90 day"));		// 3ヶ月前
						break;
					case '6month':
						$startDate = date(self::DATE_KEY_FORMAT, strtotime("$endDate -180 day"));		// 6ヶ月前
						break;
					case '1year':
						$startDate = date(self::DATE_KEY_FORMAT, strtotime("$endDate -1 year"));			// 1年前
						break;
					case self::TERM_TYPE_ALL:		// すべてのデータのとき
						$startDate = NULL;
						break;
				}
			}
			// 集計終了日
			$endDate = date(self::DATE_KEY_FORMAT);		// 本日を含める
		} else {
			// 開始日が設定されている場合は日数分取得
		}
		
		// 集計グラフ用データ取得
		$this->db->getContentViewCountByDate(blog_mainCommonDef::VIEW_CONTENT_TYPE, ''/*すべてのコンテンツ対象*/, $startDate, $endDate, array($this, 'contentViewCountLoop'));

		// 集計タイプメニュー作成
		$this->createCalcTypeMenu();
		
		// ##### 集計グラフ作成 #####
		// X軸ラベル作成
		$xTitleArray = array();
		$yValueArray = array();
		$date = $startDate;
		$endTimestamp = strtotime($endDate);
		while (true){
			$dateTimestamp = strtotime($date);
			if ($dateTimestamp > $endTimestamp) break;

			// グラフ用のデータ作成
			$xTitleArray[] = date('n/j', $dateTimestamp);		// X軸タイトル。表示フォーマットに変換。
			$value = $this->graphDataArray[$date];
			if (isset($value)){
				$yValueArray[] = intval($value);
			} else {
				$yValueArray[] = 0;
			}
			
			$date = date(self::DATE_KEY_FORMAT, strtotime("$date 1 day"));		// 次の日に更新
		}
		$xTitleArray = array_map(create_function('$a','return "\'" . $a . "\'";'), $xTitleArray);
		$graphDataXStr = '[' . implode(', ', $xTitleArray) . ']';
		$graphDataYStr = '[' . implode(', ', $yValueArray) . ']';

		// グラフY座標最大値取得
		$yMax = self::DEFAULT_Y_TICK_VALUE;
		for ($i = 0; $i < count($this->yTickValueArray) -1; $i++){
			if ($this->maxViewCount >= $this->yTickValueArray[$i + 1]){
				$yMax = $this->yTickValueArray[$i];// Y座標最大値
				break;
			}
		}
		
		// ライブラリパス
		$libDir = '';
		$libInfo = $this->gPage->getScriptLibInfo(self::LIB_JQPLOT);
		if (!empty($libInfo)) $libDir = $libInfo['dir'];
		$this->tmpl->addVar("_widget", "lib_dir", $libDir);
		
		// 値を埋め込む
		$this->tmpl->addVar("draw_graph", "x_ticks", $graphDataXStr);		// グラフX軸タイトル
		$this->tmpl->addVar("draw_graph", "y_values", $graphDataYStr);		// グラフY軸値
		$this->tmpl->addVar("draw_graph", "y_max", $yMax);		// グラフY座標最大値
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
		$date = date(self::DATE_KEY_FORMAT, strtotime($fetchedRow['vc_date']));
		$this->graphDataArray[$date] = $fetchedRow['total'];	// グラフ用データ(X軸値をキー、Y軸値を値とする連想配列)

		$total = $fetchedRow['total'];
		if ($total > $this->maxViewCount) $this->maxViewCount = $total;				// コンテンツ参照数最大値
		
		return true;
	}
}
?>
