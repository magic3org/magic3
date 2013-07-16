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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_reserve_mainOtherWidgetContainer.php 486 2008-04-09 05:26:03Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .			'/admin_reserve_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/reserve_mainDb.php');

class admin_reserve_mainOtherWidgetContainer extends admin_reserve_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const DEFAULT_CONFIG_ID = 0;		// デフォルト設定ID
	const UNIT_INTERVAL_MINUTE = 'unit_interval_minute';	// 単位時間(分)
	const MAX_COUNT_PER_UNIT = 'max_count_per_unit';		// 1単位あたりの最大登録数
					
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new reserve_mainDb();
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
		return 'admin_other.tmpl.html';
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
		global $gEnvManager;
		
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			$unitIntervalMinute = $request->trimValueOf('interval_minute');			// 単位時間(分)
			$maxCountPerUnit = $request->trimValueOf('max_count_unit');			// 1単位あたりの最大登録数
			
			$this->checkNumeric($unitIntervalMinute, '単位時間(分)');
			$this->checkNumeric($maxCountPerUnit, '1単位あたりの最大登録数');
			
			// 入力値のエラーチェック
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$isErr = false;
				if (!$isErr){
					if (!$this->db->updateReserveConfig(self::DEFAULT_CONFIG_ID, self::UNIT_INTERVAL_MINUTE, $unitIntervalMinute)) $isErr = true;
				}	
				if (!$isErr){
					if (!$this->db->updateReserveConfig(self::DEFAULT_CONFIG_ID, self::MAX_COUNT_PER_UNIT, $maxCountPerUnit)) $isErr = true;
				}
				if ($isErr){
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				} else {
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				}				
			}
		} else {		// 初期表示の場合
			$unitIntervalMinute	= $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::UNIT_INTERVAL_MINUTE);
			$maxCountPerUnit	= $this->db->getReserveConfig(self::DEFAULT_CONFIG_ID, self::MAX_COUNT_PER_UNIT);
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "interval_minute", $unitIntervalMinute);		// 単位時間(分)
		$this->tmpl->addVar("_widget", "max_count_unit", $maxCountPerUnit);		// 1単位あたりの最大登録数
	}
}
?>
