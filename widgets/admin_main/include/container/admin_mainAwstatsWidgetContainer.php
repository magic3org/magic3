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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConditionBaseWidgetContainer.php');

class admin_mainAwstatsWidgetContainer extends admin_mainConditionBaseWidgetContainer
{
	const CF_LAST_DATE_CALC_PV	= 'last_date_calc_pv';	// ページビュー集計の最終更新日
	const DEFAULT_STR_NOT_CALC = '未集計';		// 未集計時の表示文字列
	const MAX_CALC_DAYS = 30;					// 最大集計日数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
			$ret = $this->gInstance->getAnalyzeManager()->updateAnalyticsData($messageArray);
			if ($ret){
				$this->setMsg(self::MSG_GUIDANCE, $messageArray[0]);
			} else {
				$this->setMsg(self::MSG_APP_ERR, $messageArray[0]);
			}
		} else if ($act == 'delall'){		// 集計データを削除するとき
		//	$ret = $this->db->updateStatus(self::CF_LAST_DATE_CALC_PV, '');
		} else {		// 初期状態
		}

		// 値を埋め込む
//		$lastDateCalcPv = $this->db->getStatus(self::CF_LAST_DATE_CALC_PV);		// ページビュー集計最終更新日
		if (empty($lastDateCalcPv)){
			$lastDateCalcPv = self::DEFAULT_STR_NOT_CALC;
		} else {
			$lastDateCalcPv = $this->convertToDispDate($lastDateCalcPv);		// 最終集計日
		}
		$this->tmpl->addVar("_widget", "lastdate_pv", $lastDateCalcPv);
	}
}
?>
