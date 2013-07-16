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
 * @version    SVN: $Id: admin_product_lotbuyingWidgetContainer.php 5437 2012-12-07 13:14:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');
require_once($gEnvManager->getCurrentIWidgetDbPath() . '/product_lotbuyingDb.php');

class admin_product_lotbuyingWidgetContainer extends BaseIWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $productClass;		// 商品クラス
	const DEFAULT_RATE = '10:10;20:20;30:30';				// 割引率表
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト取得
		$this->db = new product_lotbuyingDb();
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
		$productId			= $request->trimValueOf('iw_product_id');			// 商品ID
		$rateTable			= $request->trimValueOf('iw_rate_table');			// 割引率表

		if ($act == 'update'){		// 設定更新のとき
			// 入力エラーチェック
			$this->checkInput($productId,			'商品ID');
			$this->checkInput($rateTable,			'割引率');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$configObj->productClass	= $this->productClass;		// 商品クラス
				$configObj->productId		= $productId;				// 商品ID
				$configObj->rateTable 		= $rateTable;				// 割引率表
				$ret = $this->updateConfigObj($configObj);
				if (!$ret) $this->setMsg(self::MSG_APP_ERR, 'インナーウィジェットデータの更新に失敗しました');
				// ***** 正常に終了した場合はメッセージを残さない *****
			}
		} else if ($act == 'content'){		// 画面表示のとき
			if (!empty($init)){			// 初期表示のとき
				if (empty($configObj)){		// 定義値がないとき(管理画面なので最初は定義値が存在しない)
					$this->productClass	= '-';		// 商品クラス(選択なし)
					$productId			= '';				// 商品ID
					$rateTable			= self::DEFAULT_RATE;				// 割引率表
				} else {
					$this->productClass	= $configObj->productClass;		// 商品クラス
					$productId			= $configObj->productId;			// 商品ID
					$rateTable			= $configObj->rateTable;		// 割引率表
				}
			}

			// 画面にデータを埋め込む
			$this->tmpl->addVar('_widget', 'product_id',	$productId);		// 商品ID
			$this->tmpl->addVar('_widget', 'rate_table', $rateTable);			// 割引率表
		}

		// 商品クラス選択メニュー作成
		$this->db->getAllProductClass($langId, array($this, 'productClassLoop'));
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
}
?>
