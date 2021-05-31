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
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainConnector_dailyjobWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const MAX_CALC_DAYS = 3;		// 集計最大日数
	const MSG_JOB_COMPLETED = '日次処理終了しました。日時: %s';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
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
		// ##### ウィジェット出力処理中断 ######
		$this->gPage->abortWidget();

		// アクセス解析の集計処理
		$messageArray = array();
		$ret = $this->gInstance->getAnalyzeManager()->updateAnalyticsData($messageArray, self::MAX_CALC_DAYS);
		if (!$ret){	// エラーの場合
			// ログを残す
			$this->gOpeLog->writeError(__METHOD__, '日次処理(アクセス解析の集計)に失敗しました。', 1100, implode(', ', $messageArray));
			return;
		}
		
		// 日次処理終了のログを残す
		$this->gOpeLog->writeInfo(__METHOD__, sprintf(self::MSG_JOB_COMPLETED, date("Y/m/d H:i")), 1002);
	}
}
?>
