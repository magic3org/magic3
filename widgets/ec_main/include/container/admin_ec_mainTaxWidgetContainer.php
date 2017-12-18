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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_ec_mainBaseWidgetContainer.php');

class admin_ec_mainTaxWidgetContainer extends admin_ec_mainBaseWidgetContainer
{
	private $taxRateInfoArray;		// 税率リスト情報
	const TAX_RATE_SALES = 'rate_sales';			// 消費税率取得用
	
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
		return 'admin_tax.tmpl.html';
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
				
		// 税率リスト取得
		$taxRateRowCount = $request->trimValueOf('taxrate_rowcount');		// 税率リスト行数
		$taxRates	= $request->trimValueOf('item_rate');		// 税率
		$taxDates	= $request->trimValueOf('item_date');		// 開始日付
		$this->taxRateInfoArray = array();
		for ($i = 0; $i < $taxRateRowCount; $i++){
			// 税率の入力値がない場合は終了
			if (empty($taxRates[$i])) break;
			
			$newInfoObj = new stdClass;
			$newInfoObj->rate		= $taxRates[$i];
			$newInfoObj->date		= $taxDates[$i];
			$this->taxRateInfoArray[] = $newInfoObj;
		}

		$reloadData = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 更新のとき
			// 税率入力チェック
			$foreDate = '';
			for ($i = 0; $i < count($this->taxRateInfoArray); $i++){
				$infoObj = $this->taxRateInfoArray[$i];
				
				$isValid = $this->checkNumericF($infoObj->rate, '税率('. ($i + 2) . '行目)');
				if (!$isValid) break;
				$isValid = $this->checkDate($infoObj->date, '開始日付('. ($i + 2) . '行目)');
				if (!$isValid) break;
				
				// 開始日付のチェック
				if ($foreDate != ''){
					if (strtotime($infoObj->date) <= strtotime($foreDate)) $this->setUserErrorMsg('開始日付は前行 < 後行に設定してください');
				}
				$foreDate = $infoObj->date;
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 保存データ作成
				for ($i = 0; $i < count($this->taxRateInfoArray); $i++){
					$infoObj = $this->taxRateInfoArray[$i];
					$this->taxRateInfoArray[$i]->date = $this->convertToProperDate($infoObj->date);
				}
				
				$ret = self::$_mainDb->updateTaxRateList(self::TAX_RATE_SALES, $this->taxRateInfoArray);
				if ($ret){		// 更新成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$reloadData = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {
			$reloadData = true;
		}
		
		// 消費税デフォルト値取得
		$ret = self::$_mainDb->getDefaultTaxRate(self::TAX_RATE_SALES, $row);
		if ($ret) $rate = $row['tr_rate'];
		
		if ($reloadData){		// データの再読み込み
			// 消費税率リスト取得
			$this->taxRateInfoArray = array();
			self::$_mainDb->getTaxRateList(self::TAX_RATE_SALES, array($this, 'taxRateLoop'));
		}
		
		// 消費税リスト作成
		$this->createTaxRateList();
		if (count($this->taxRateInfoArray) == 0) $this->tmpl->setAttribute('sales_tax_rate_list', 'visibility', 'hidden');// データがないときは一覧を表示しない
		
		// 画面にデータ埋め込む
		$this->tmpl->addVar('_widget', 'rate', $this->convertToDispString($rate));
	}
	/**
	 * 消費税率をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function taxRateLoop($index, $fetchedRow, $param)
	{
		if ($index == 0) return true;		// デフォルト値は読み飛ばす
		
		$newInfoObj = new stdClass;
		$newInfoObj->rate		= $fetchedRow['tr_rate'];
		$newInfoObj->date		= $fetchedRow['tr_active_start_dt'];
		$this->taxRateInfoArray[] = $newInfoObj;
		return true;
	}
	/**
	 * 消費税率をテンプレートに設定する
	 *
	 * @return								なし
	 */
	function createTaxRateList()
	{
		for ($i = 0; $i < count($this->taxRateInfoArray); $i++){
			$infoObj = $this->taxRateInfoArray[$i];
			
			$row = array(
				'rate'     => $this->convertToDispString($infoObj->rate),			// 税率(%)
				'date'     => $this->convertToDispDate($infoObj->date)			// 開始日付
			);
			$this->tmpl->addVars('sales_tax_rate_list', $row);
			$this->tmpl->parseTemplate('sales_tax_rate_list', 'a');
		}
	}
}
?>
