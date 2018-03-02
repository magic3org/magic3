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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainMainteBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_analyzeDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_tableDb.php');

class admin_mainDbaccesslogWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $analyzeDb;
	private $tableDb;
	const CF_LAST_DATE_CALC_PV	= 'last_date_calc_pv';	// ページビュー集計の最終更新日
	const DEFAULT_STR_NOT_CALC = '未集計';		// 未集計時の表示文字列
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
		$this->analyzeDb = new admin_analyzeDb();
		$this->tableDb = new admin_tableDb();
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
		return 'dbaccesslog.tmpl.html';
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

		if ($act == 'dellog'){		// アクセスログ削除処理のとき
			if ($ret){
				$this->setMsg(self::MSG_GUIDANCE, 'アクセスログを削除しました');
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'アクセスログ削除に失敗しました');
			}
		}
		
		// 最終集計日取得
		$lastDateCalcPv = $this->analyzeDb->getStatus(self::CF_LAST_DATE_CALC_PV);		// ページビュー集計最終更新日
		if (empty($lastDateCalcPv)){
			$lastDateCalcPv = self::DEFAULT_STR_NOT_CALC;
			
			$this->tmpl->addVar('_widget', 'del_log_button_disabled', 'disabled');		// ログ削除ボタンは使用不可
		} else {
			$lastDateCalcPv = $this->convertToDispDate($lastDateCalcPv);		// 最終集計日
		}
		$this->tmpl->addVar("_widget", "lastdate_pv", $lastDateCalcPv);
			
		// レコード数取得
		$rowCount = $this->tableDb->getTableDataListCount('_access_log');
		$this->tmpl->addVar('_widget', 'row_count', $this->convertToDispString($rowCount));
		
		// ディスク使用量取得
		$diskByte = $this->gInstance->getDbManager()->getTableDataSize('_access_log');
		$this->tmpl->addVar("_widget", "size_access_log", convFromBytes($diskByte));
	}
}
?>
