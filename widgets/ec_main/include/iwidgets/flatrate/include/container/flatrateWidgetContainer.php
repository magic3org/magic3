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
 * @version    SVN: $Id: flatrateWidgetContainer.php 5436 2012-12-07 09:55:12Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');

class flatrateWidgetContainer extends BaseIWidgetContainer
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
		$time = $request->trimValueOf('iw_' . $id . '_demand_time');	// 希望日時
		
		if ($act == 'calc'){		// 計算のとき
			// 定義値取得
			$price	= $configObj->price;		// 定額料金
			$useMin = $configObj->useMin;		// 無料となる購入額を使用するかどうか
			$minPrice = $configObj->minPrice;	// 最小購入額

			// 可変データ取得
			$productTotal	= $optionObj->productTotal;			// 商品合計額

			// 送料計算
			if ($useMin == 1){		// 購入最低額以上は無料かどうか
				if ($productTotal >= $minPrice) $price = 0;
			}

			// 計算結果オブジェクトに設定
			// *** ここで返した値が配送用データとしてDBに保存される ***
			$resultObj->price = $price;
			$resultObj->time = $time;		// 時間帯
			$this->setResultObj($resultObj);
		} else if ($act == 'content'){		// 画面作成の場合
			// 定義値取得
			$inputDate = $configObj->inputDate;		// 希望日時の入力許可

			// 呼び出し側の設定値を使用しない場合は、画面からの入力値を使用
			if ($init) $time = $optionObj->time;		// 希望時間

			// 配達希望日時の入力を許可するときは、フィールドを表示し、取得値を設定
			if ($inputDate){
				$this->tmpl->setAttribute('field_input', 'visibility', 'visible');
				$this->tmpl->addVar('field_input', 'demand_time',	$time);
			}
			// ユニークIDを設定
			$this->tmpl->addVar('field_input', 'id',	$id);
		}
	}
}
?>
