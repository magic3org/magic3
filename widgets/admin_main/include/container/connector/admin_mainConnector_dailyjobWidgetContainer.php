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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @link       http://magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_mainConnector_dailyjobWidgetContainer extends BaseAdminWidgetContainer
{
	const MAX_CALC_DAYS = 3;		// 集計最大日数
	const MSG_JOB_COMPLETED = '日次処理を実行しました。';
	const MSG_JOB_CANCELD = '日次処理をキャンセルしました。現在サーバ負荷が大きい状態(%d%%)です。';
	const MSG_ERR_JOB = '日次処理(アクセス解析の集計)に失敗しました。';
	const MAX_SERVER_LOAD_AVERAGE = 30;		// サーバの最大付加状況(%)
	
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
		return '';		// テンプレートは使用しない
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
		// サーバ負荷が高い場合は実行中止
		$avg = $this->_checkServerLoadAverage();
		if ($avg > 0){
			$this->gOpeLog->writeInfo(__METHOD__, sprintf(self::MSG_JOB_CANCELD, $avg), 1002);
			return;
		}
		
		// タイムアウトを停止
		$this->gPage->setNoTimeout();
		
		// ウィジェット出力処理中断
		$this->gPage->abortWidget();

		// アクセス解析の集計処理
		$messageArray = array();
		$ret = $this->gInstance->getAnalyzeManager()->updateAnalyticsData($messageArray, self::MAX_CALC_DAYS);
		if (!$ret){	// エラーの場合
			// ログを残す
			$this->gOpeLog->writeError(__METHOD__, self::MSG_ERR_JOB, 1100, implode(', ', $messageArray));
			return;
		}
		
		// 日次処理終了のログを残す
		$this->gOpeLog->writeInfo(__METHOD__, self::MSG_JOB_COMPLETED, 1002);
	}
	/**
	 * サーバの負荷状況をチェック
	 *
	 * @return int					負荷状況を%で返す。0の場合は問題なし。
	 */
	function _checkServerLoadAverage()
	{
		$load = sys_getloadavg();
		$coreCount = shell_exec('nproc');	// プロセッサ数取得
		
		for ($i = 0; $i < 3; $i++){
			$avg = $load[$i] / $coreCount * 100;
			
			// 最大負荷よりも大きい場合は負荷値を返す
			if ($avg > self::MAX_SERVER_LOAD_AVERAGE) return $avg;
		}
		
		return 0;
	}
}
?>
