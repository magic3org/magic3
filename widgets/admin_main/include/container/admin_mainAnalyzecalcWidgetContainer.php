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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConditionBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_analyzeDb.php');

class admin_mainAnalyzecalcWidgetContainer extends admin_mainConditionBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;	// システム情報取得用
	const CF_LAST_DATE_CALC_PV	= 'last_date_calc_pv';	// ページビュー集計の最終更新日
	const DEFAULT_STR_NOT_CALC = '未集計';		// 未集計時の表示文字列
//	const MAX_CALC_DAYS = 30;					// 最大集計日数
	const MAX_CALC_MONTHS = 12;					// 最大集計月数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_analyzeDb();
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
		return 'analyzecalc.tmpl.html';
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

		if ($act == 'calc'){		// 集計実行のとき
			$messageArray = array();
			$ret = $this->gInstance->getAnalyzeManager()->updateAnalyticsData($messageArray, self::MAX_CALC_MONTHS * 30/*12ヶ月分集計*/);
			if ($ret){
				$this->setMsg(self::MSG_GUIDANCE, $messageArray[0]);
			} else {
				$this->setMsg(self::MSG_APP_ERR, $messageArray[0]);
			}
		/*
			$ret = $this->db->getOldAccessLog($row);
			if ($ret){		// 集計対象のデータが存在するとき
				$startDate = date("Y/m/d", strtotime($row['al_dt']));

				$lastDate = $this->db->getStatus(self::CF_LAST_DATE_CALC_PV);

				// 集計開始日を求める
				if (!empty($lastDate)){
					$startDate = date("Y/m/d", strtotime("$lastDate 1 day"));		// 翌日
				}
				// 集計終了日を求める
				$endDate = date("Y/m/d", strtotime("-1 day"));	// 前日
				$endTime = strtotime($endDate);
			
				// 集計処理を行う
				$dayCount = 0;		// 集計日数
				$date = $startDate;
				while (true){
					if (strtotime($date) > $endTime){
						$this->setMsg(self::MSG_GUIDANCE, '集計完了しました');
						break;
					}
					// トランザクションスタート
					$this->db->startTransaction();

					$ret = $this->db->calcDatePv($date);
					
					// 集計日付を更新
					if ($ret) $ret = $this->db->updateStatus(self::CF_LAST_DATE_CALC_PV, $date);
					
					// トランザクション終了
					$this->db->endTransaction();

					// エラーの場合は終了
					if (!$ret){
						$this->setMsg(self::MSG_APP_ERR, 'エラーが発生しました');
						break;
					}
					
					// 集計日数を更新
					$dayCount++;
					if ($dayCount >= self::MAX_CALC_DAYS){
						$this->setMsg(self::MSG_GUIDANCE, self::MAX_CALC_DAYS . '日分の集計完了しました');
						break;
					}
					
					$date = date("Y/m/d", strtotime("$date 1 day"));
				}
			} else {				// 集計データがないとき
				$this->setMsg(self::MSG_GUIDANCE, '集計対象のアクセスログがありません');
			}
			*/
		} else if ($act == 'delall'){		// 集計データを削除するとき
			$ret = $this->db->updateStatus(self::CF_LAST_DATE_CALC_PV, '');
		} else {		// 初期状態
		}

		// 値を埋め込む
		$lastDateCalcPv = $this->db->getStatus(self::CF_LAST_DATE_CALC_PV);		// ページビュー集計最終更新日
		if (empty($lastDateCalcPv)){
			$lastDateCalcPv = self::DEFAULT_STR_NOT_CALC;
		} else {
			$lastDateCalcPv = $this->convertToDispDate($lastDateCalcPv);		// 最終集計日
		}
		$this->tmpl->addVar("_widget", "lastdate_pv", $lastDateCalcPv);
	}
}
?>
