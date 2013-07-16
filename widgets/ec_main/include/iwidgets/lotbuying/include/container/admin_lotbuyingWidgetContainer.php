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
 * @version    SVN: $Id: admin_lotbuyingWidgetContainer.php 5437 2012-12-07 13:14:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');
require_once($gEnvManager->getCurrentIWidgetDbPath() . '/lotbuyingDb.php');

class admin_lotbuyingWidgetContainer extends BaseIWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $productClass;		// 商品クラス
	private $productType;		// 商品タイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト取得
		$this->db = new lotbuyingDb();
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
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		// 基本情報を取得
		$id		= $optionObj->id;		// ユニークなID(配送方法ID)
		$init	= $optionObj->init;		// データ初期化を行うかどうか
		
		// 入力値を取得
		$this->productClass	= $request->trimValueOf('iw_product_class');		// 商品クラス
		$this->productType	= $request->trimValueOf('iw_product_type');		// 商品タイプ
		$count			= $request->trimValueOf('iw_count');			// 単位数
		$discountRate	= $request->trimValueOf('iw_discount_rate');	// 割引率

		if ($act == 'update'){		// 設定更新のとき
			// 入力エラーチェック
			$this->checkNumeric($count, '個数');
			$this->checkNumeric($discountRate, '割引率');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$configObj->productClass	= $this->productClass;		// 商品クラス
				$configObj->productType		= $this->productType;		// 商品タイプ
				$configObj->count			= $count;				// 単位数
				$configObj->discountRate 	= $discountRate;		// 割引率
				$ret = $this->updateConfigObj($configObj);
				if (!$ret) $this->setMsg(self::MSG_APP_ERR, 'インナーウィジェットデータの更新に失敗しました');
				// ***** 正常に終了した場合はメッセージを残さない *****
			}
		} else if ($act == 'content'){		// 画面表示のとき
			if (!empty($init)){			// 初期表示のとき
				if (empty($configObj)){		// 定義値がないとき(管理画面なので最初は定義値が存在しない)
					$this->productClass	= '-';		// 商品クラス(選択なし)
					$this->productType	= '-';		// 商品タイプ(選択なし)
					$count			= 10;		// 単位数
					$discountRate	= 10;		// 割引率
				} else {
					$this->productClass	= $configObj->productClass;		// 商品クラス
					$this->productType	= $configObj->productType;		// 商品タイプ
					$count			= $configObj->count;			// 単位数
					$discountRate	= $configObj->discountRate;		// 割引率
				}
			}

			// 画面にデータを埋め込む
			$this->tmpl->addVar("_widget", "count",	$count);
			$this->tmpl->addVar('_widget', 'discount_rate', $discountRate);
		}

		// 商品クラス選択メニュー作成
		$this->db->getAllProductClass($langId, array($this, 'productClassLoop'));
		
		// 商品タイプメニューを作成
		$this->db->getAllProductType($this->productClass, $langId, array($this, 'productTypeLoop'));
	}
	/**
	 * 商品クラスをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function productClassLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['pu_id'];
		$selected = '';
		if ($id == $this->productClass) $selected = 'selected';		// 選択中の商品クラス

		$row = array(
			'value'    => $this->convertToDispString($id),			// ID
			'name'     => $this->convertToDispString($fetchedRow['pu_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('product_class_list', $row);
		$this->tmpl->parseTemplate('product_class_list', 'a');
		return true;
	}
	/**
	 * 商品タイプをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function productTypeLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['py_id'];
		$selected = '';
		if ($id == $this->productType) $selected = 'selected';		// 選択中の商品タイプ

		$row = array(
			'value'    => $this->convertToDispString($id),			// ID
			'name'     => $this->convertToDispString($fetchedRow['py_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('product_type_list', $row);
		$this->tmpl->parseTemplate('product_type_list', 'a');
		return true;
	}
}
?>
