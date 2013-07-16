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
 * @version    SVN: $Id: admin_staterateWidgetContainer.php 5436 2012-12-07 09:55:12Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');
require_once($gEnvManager->getCurrentIWidgetDbPath() . '/staterateDb.php');

class admin_staterateWidgetContainer extends BaseIWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $stateArray;		// 都道府県データ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト取得
		$this->db = new staterateDb();
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
		$priceArray = array();
		for ($i = 0; $i < count($this->stateArray); $i++){
			$id = $this->stateArray[$i]['gz_id'];
			$name = $this->stateArray[$i]['gz_name'];
			$value = $request->trimValueOf('iw_price_' . $id);
			$priceArray[$id] = $value;			// 入力値を保存
		}
		$minPrice = $request->trimValueOf('iw_min_price');	// 最小購入額
		$inputDate = ($request->trimValueOf('iw_input_date') == 'on') ? 1 : 0;			// 配達希望日時の入力を許可するかどうか
		$useMin = ($request->trimValueOf('iw_use_min') == 'on') ? 1 : 0;		// 無料となる購入額を使用するかどうか
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値エラーチェック
			for ($i = 0; $i < count($this->stateArray); $i++){
				$id = $this->stateArray[$i]['gz_id'];
				$name = $this->stateArray[$i]['gz_name'];
				$value = $request->trimValueOf('iw_price_' . $id);
				$this->checkNumeric($value, $name);
			}
			$this->checkNumeric($minPrice, '最小購入額');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$configObj->table	= $priceArray;		// 料金表
				$configObj->minPrice	= $minPrice;	// 最小購入額
				$configObj->inputDate = $inputDate;		// 希望日時の入力許可
				$configObj->useMin	= $useMin;		// 無料となる購入額を使用するかどうか
				$ret = $this->updateConfigObj($configObj);
				if (!$ret) $this->setMsg(self::MSG_APP_ERR, 'インナーウィジェットデータの更新に失敗しました');
				// ***** 正常に終了した場合はメッセージを残さない *****
			}
		} else if ($act == 'content'){		// 画面表示のとき
			if (!empty($init)){			// 初期表示のとき
				// 保存値取得
				if (empty($configObj)){		// 定義値がないとき(管理画面なので最初は定義値が存在しない)
					// デフォルト値設定
					$priceArray	= array();			// 都道府県ごとの送料
					$minPrice = 5000;	// 最小購入額
					$inputDate = 0;		// 希望日時の入力許可
					$useMin = 0;		// 無料となる購入額を使用するかどうか
				} else {
					$priceArray	= $configObj->table;			// 都道府県ごとの送料
					$minPrice = $configObj->minPrice;	// 最小購入額
					$inputDate = $configObj->inputDate;		// 希望日時の入力許可
					$useMin = $configObj->useMin;		// 無料となる購入額を使用するかどうか
				}
			}
			
			// 都道府県一覧を作成
			$this->createStateList($priceArray);
		
			$this->tmpl->addVar("_widget", "min_price",	$minPrice);
			if ($inputDate) $this->tmpl->addVar('_widget', 'input_date', 'checked');		// 配達希望日時が入力可能かどうか
			if ($useMin) $this->tmpl->addVar('_widget', 'use_min', 'checked');				// 無料となる購入額を使用するかどうか
		}
	}
	/**
	 * 都道府県一覧を作成
	 *
	 * @param array  	$srcRows			取得行
	 * @return 			なし
	 */
	function createStateList($srcRows)
	{
		for ($i = 0; $i < count($this->stateArray); $i++){
			$id = $this->stateArray[$i]['gz_id'];
			$name = $this->stateArray[$i]['gz_name'];
			$price = isset($srcRows[$id]) ? $srcRows[$id] : 0;
			$row = array(
				'id'    => $this->convertToDispString($id),			// 地域ID
				'name'     => $this->convertToDispString($name),		// 地域名
				'price' => $price														// 価格
			);
			$this->tmpl->addVars('state_list', $row);
			$this->tmpl->parseTemplate('state_list', 'a');
		}
	}
}
?>
