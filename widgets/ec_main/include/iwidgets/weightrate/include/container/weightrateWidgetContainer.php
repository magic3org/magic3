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
 * @version    SVN: $Id: weightrateWidgetContainer.php 5436 2012-12-07 09:55:12Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');
require_once($gEnvManager->getCurrentIWidgetDbPath() . '/weightrateDb.php');

class weightrateWidgetContainer extends BaseIWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $langId;	// 言語ID
	private $weight;	// 商品総重量
	
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
		return 'index.tmpl.html';
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
			
		// 入力値取得
		$time = $request->trimValueOf('iw_' . $id . '_demand_time');	// 希望日時
		
		if ($act == 'calc'){		// 計算のとき
			// 定義値取得
			$useMin = $configObj->useMin;		// 無料となる購入額を使用するかどうか
			$minPrice = $configObj->minPrice;	// 最小購入額
			$weightValues = $configObj->weightValues;// 重量
			$priceValues = $configObj->priceValues;	// 価格表
				
			// 可変データ取得
			$stateId = $optionObj->stateId;		// 配送先の都道府県IDを取得
			$productTotal	= $optionObj->productTotal;			// 商品合計額
			$cartId			= $optionObj->cartId;			// カートID

			// 商品の重量を求める
			$this->weight = 0;
			$this->db->getCartItemList($cartId, $this->langId, array($this, 'cartLoop'));
			
			// 送料計算
			$price = 0;
			$calcPrice = true;		// 送料計算を行うかどうか
			if ($useMin && $minPrice > 0){		// 購入最低額以上は無料のとき
				if ($productTotal >= $minPrice){
					$calcPrice = false;		// 送料計算を行うかどうか
				}
			}
			if ($calcPrice){		// 送料計算を行うとき
				// 計算テーブルを取得
				$table = $priceValues[$stateId];
				
				// 重量範囲を求める
				$foreWeight = 0;
				for ($i = 0; $i < count($weightValues); $i++){
					if (empty($weightValues[$i]) || ($foreWeight <= $this->weight && $this->weight < $weightValues[$i])) break;
					$foreWeight = $weightValues[$i];
				}
				if ($i < count($table)) $price = $table[$i];
				$price = intval($price);		// 数値化
			}

			// 計算結果オブジェクトに設定
			// *** ここで返した値が配送用データとしてDBに保存される ***
			$resultObj->price = $price;
			$resultObj->time = $time;		// 時間帯
			$this->setResultObj($resultObj);
		} else if ($act == 'content'){		// 画面作成の場合
			// 設定値取得
			$inputDate = $configObj->inputDate;		// 希望日時の入力許可
			
			// 呼び出し側の設定値を使用しない場合は、画面からの入力値を使用
			if ($init) $time = $optionObj->time;		// 希望時間

			// 配達希望日時の入力を許可するときは、フィールドを表示し、取得値を設定
			if ($inputDate){
				$this->tmpl->setAttribute('field_input', 'visibility', 'visible');
				$this->tmpl->addVar('field_input', 'demand_time',	$time);
			}
			// 配送方法IDを設定
			$this->tmpl->addVar('field_input', 'id',	$id);
		}
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function cartLoop($index, $fetchedRow, $request)
	{
		$id = $fetchedRow['si_product_id'];	// 商品ID
		
		// 商品情報を取得
		$ret = $this->db->getProductByProductId($id, $this->langId, $row);
		if ($ret){
			$delivWeight = $row['pt_weight'];		// 配送基準重量
			$quantity = $fetchedRow['si_quantity'];	// 商品数量
			$this->weight += $delivWeight * $quantity;
		}
		return true;
	}
}
?>
