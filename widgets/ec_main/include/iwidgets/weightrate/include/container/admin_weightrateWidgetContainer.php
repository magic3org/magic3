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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_weightrateWidgetContainer.php 5436 2012-12-07 09:55:12Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');
require_once($gEnvManager->getCurrentIWidgetDbPath() . '/weightrateDb.php');

class admin_weightrateWidgetContainer extends BaseIWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $stateArray;		// 都道府県データ
	private $colCount;		// 入力フィールドのカラム数
	private $weightValues;		// 入力値(重量)
	private $priceValues;		// 入力値(価格)
	const DEFAULT_FIELD_COUNT = 2;	// 料金入力部のカラム数
	const MIN_FIELD_COUNT = 2;	// 料金入力部の最小カラム数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト取得
		$this->db = new weightrateDb();
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $act			実行処理
	 * @param object         $configObj		定義情報オブジェクト
	 * @param object         $optionObj		可変パラメータオブジェクト
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, $act, $configObj, $optionObj)
	{	
		return 'admin.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $act			実行処理
	 * @param object         $configObj		定義情報オブジェクト
	 * @param object         $optionObj		可変パラメータオブジェクト
	 * @param								なし
	 */
	function _assign($request, $act, $configObj, $optionObj)
	{
		// 基本情報を取得
		$id		= $optionObj->id;		// ユニークなID(配送方法ID)
		$init	= $optionObj->init;		// データ初期化を行うかどうか
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得

		// 都道府県データを取得
		$ret = $this->db->getAllState('JPN', $this->langId, $this->stateArray);
		
		// 入力値取得
		$minPrice = $request->trimValueOf('iw_min_price');	// 最小購入額
		$inputDate = ($request->trimValueOf('iw_input_date') == 'on') ? 1 : 0;			// 配達希望日時の入力を許可するかどうか
		$useMin = ($request->trimValueOf('iw_use_min') == 'on') ? 1 : 0;		// 無料となる購入額を使用するかどうか
		$this->colCount = $request->trimValueOf('iw_col_count');	// カラム数
		$fieldCount = $request->trimValueOf('iw_field_count');	// フィールド数
		// ヘッダ部
		$this->weightValues = array();
		for ($i = 0; $i < $this->colCount -1; $i++){
			$value = $request->trimValueOf('iw_weight_' . $i);
			$this->weightValues[] = $value;
		}
		// 価格部
		$this->priceValues = array();
		for ($i = 0; $i < count($this->stateArray); $i++){
			$id = $this->stateArray[$i]['gz_id'];	// 地域ID
			$line = array();
			for ($j = 0; $j < $this->colCount; $j++){
				$inputId = $id . '_' . $j;
				$name = $this->stateArray[$i]['gz_name'];
				$value = $request->trimValueOf('iw_price_' . $inputId);
				$line[] = $value;
			}
			$this->priceValues[$id] = $line;			// 入力値を保存
		}
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値エラーチェック
			// ヘッダ部
			for ($i = 0; $i < count($this->weightValues); $i++){
				$value = $this->weightValues[$i];
				$name = '重量' . (intval($i) + 1);
				$this->checkNumeric($value, $name);
			}
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$max = 0;
				for ($i = 0; $i < count($this->weightValues); $i++){
					if ($max >= $this->weightValues[$i]){
						$msg = '重量' . (intval($i) + 1) . 'の値は' . '重量' . intval($i) . 'よりも大きく設定する必要があります';
						$this->setMsg(self::MSG_USER_ERR, $msg);
						break;
					} else {
						$max = $this->weightValues[$i];
					}
				}
			}
			// 価格入力部
			for ($i = 0; $i < count($this->stateArray); $i++){
				$id = $this->stateArray[$i]['gz_id'];	// 地域ID
				for ($j = 0; $j < $this->colCount; $j++){
					$inputId = $id . '_' . $j;
					$name = $this->stateArray[$i]['gz_name'] . (intval($j) + 1);
					$value = $request->trimValueOf('iw_price_' . $inputId);
					$this->checkNumeric($value, $name);
				}
			}
			$this->checkNumeric($minPrice, '最小購入額');
			$this->checkNumeric($fieldCount, 'フィールド数');
			if ($this->getMsgCount() == 0){			// エラーのないとき
				if (intval($fieldCount) < 2) $this->setMsg(self::MSG_USER_ERR, 'フィールド数は2以上を設定してください');
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 重量データの修正
				$this->weightValues[] = '0';
				
				$configObj->minPrice	= $minPrice;	// 最小購入額
				$configObj->inputDate = $inputDate;		// 希望日時の入力許可
				$configObj->useMin	= $useMin;		// 無料となる購入額を使用するかどうか
				$configObj->colCount = $fieldCount;		// カラム数
				$configObj->weightValues = $this->weightValues;// 重量
				$configObj->priceValues = $this->priceValues;	// 入力値(価格)
				$ret = $this->updateConfigObj($configObj);
				if (!$ret) $this->setMsg(self::MSG_APP_ERR, 'インナーウィジェットデータの更新に失敗しました');
				// ***** 正常に終了した場合はメッセージを残さない *****
			}
		} else if ($act == 'content'){		// 画面表示のとき
			if (!empty($init)){			// 初期表示のとき
				// 保存値取得
				if (empty($configObj)){		// 定義値がないとき(管理画面なので最初は定義値が存在しない)
					// デフォルト値設定
					$minPrice = 5000;	// 最小購入額
					$inputDate = 0;		// 希望日時の入力許可
					$useMin = 0;		// 無料となる購入額を使用するかどうか
					$this->colCount = self::DEFAULT_FIELD_COUNT;		// 入力フィールドの列数
					$this->weightValues = array(2000, 5000);		// 入力値(重量)
					$this->priceValues = array();		// 入力値(価格)
				} else {
					$minPrice = $configObj->minPrice;	// 最小購入額
					$inputDate = $configObj->inputDate;		// 希望日時の入力許可
					$useMin = $configObj->useMin;		// 無料となる購入額を使用するかどうか
					$this->colCount = $configObj->colCount;		// 入力フィールドの列数
					$this->weightValues = $configObj->weightValues;		// 入力値(重量)
					$this->priceValues = $configObj->priceValues;		// 入力値(価格)
				}
				$fieldCount = $this->colCount;		// 入力フィールド数
			}
			
			// 一覧を作成
			$this->createStateList();
			
			$this->tmpl->addVar("_widget", "min_price",	$minPrice);
			$this->tmpl->addVar("_widget", "col_count",	$this->colCount);		// カラム数
			$this->tmpl->addVar("_widget", "field_count",	$fieldCount);		// カラム数(入力用)
			if ($inputDate) $this->tmpl->addVar('_widget', 'input_date', 'checked');		// 配達希望日時が入力可能かどうか
			if ($useMin) $this->tmpl->addVar('_widget', 'use_min', 'checked');				// 無料となる購入額を使用するかどうか
		}
	}
	/**
	 * 都道府県一覧を作成
	 *
	 * @return 			なし
	 */
	function createStateList()
	{
		// ヘッダ部作成
		for ($i = 0; $i < $this->colCount -1; $i++){
			$weight = isset($this->weightValues[$i]) ? $this->weightValues[$i] : 0;
			$weightRow = array(
				'id'		=> $i,			// 入力フィールド名用
				'weight'	=> $weight			// 重量
			);
			$this->tmpl->addVars('weight_input_list', $weightRow);
			$this->tmpl->parseTemplate('weight_input_list', 'a');
		}
		
		// 本体作成
		for ($i = 0; $i < count($this->stateArray); $i++){
			$id = $this->stateArray[$i]['gz_id'];	// 地域ID
						
			// 価格入力欄を作成
			$this->tmpl->clearTemplate('price_input_list');
			for ($j = 0; $j < $this->colCount; $j++){
				$inputId = $id . '_' . $j;
				$price = isset($this->priceValues[$id][$j]) ? $this->priceValues[$id][$j] : 0;
				$priceRow = array(
					'id'		=> $inputId,			// 入力フィールド名用
					'price'		=> $price			// 価格
				);
				$this->tmpl->addVars('price_input_list', $priceRow);
				$this->tmpl->parseTemplate('price_input_list', 'a');
			}
			$name = $this->stateArray[$i]['gz_name'];
			$row = array(
				'id'    => $this->convertToDispString($id),			// 地域ID
				'name'     => $this->convertToDispString($name)		// 地域名
			);
			$this->tmpl->addVars('state_list', $row);
			$this->tmpl->parseTemplate('state_list', 'a');
		}
	}
}
?>
