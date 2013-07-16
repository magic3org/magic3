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
 * @version    SVN: $Id: exchange_classrateWidgetContainer.php 5437 2012-12-07 13:14:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');

class exchange_classrateWidgetContainer extends BaseIWidgetContainer
{
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
		
		// 入力値取得
		
		if ($act == 'calc'){		// 計算のとき
			// 定義値取得
			$priceTable	= $configObj->table;			// 料金テーブル
			
			// 可変データ取得
			$productTotal	= $optionObj->productTotal;			// 商品合計額
			
			// 送料計算用テーブル作成
			$priceArray = parseUserCustomParam($priceTable);	// 料金表を配列化
						
			// 送料計算
			$price = 0;
			$foreValue = 0;
			for ($i = 0; $i < count($priceArray); $i++){
				if (empty($priceArray[$i]->key) || ($foreValue <= $productTotal && $productTotal < $priceArray[$i]->key)) break;
				$foreValue = $priceArray[$i]->key;
			}
			if ($i < count($priceArray)) $price = $priceArray[$i]->value;
			$price = intval($price);		// 数値化

			// 計算結果オブジェクトに設定
			$resultObj->price = $price;
			$this->setResultObj($resultObj);
		} else if ($act == 'content'){		// 画面表示のとき
		}
	}
}
?>
