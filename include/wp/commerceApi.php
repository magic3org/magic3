<?php
/**
 * EコマースAPI
 *
 * Eコマースデータにアクセス
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/baseApi.php');

class CommerceApi extends BaseApi
{
	private $db;				// システムDBオブジェクト
	private $ecObj;					// EC共通ライブラリオブジェクト
	private $langId;				// コンテンツの言語
	private $userId;				// 現在のユーザID
	private $delivMethodRows;		// 配送方法レコード
	const ADDON_OBJ_COMMERCE = 'eclib';		// EコマースアドオンオブジェクトID
	const DELIVERY_METHOD_CLASS = 'WCShippingMethod';			// 配送方法クラス名(共通)
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクトを取得
		$this->db = $this->gInstance->getSytemDbObject();
		
		// Eコマースアドオンオブジェクト取得
		$this->ecObj = $this->gInstance->getObject(self::ADDON_OBJ_COMMERCE);
		
		$this->langId = $this->gEnv->getCurrentLanguage();				// コンテンツの言語
		$this->userId = $this->gEnv->getCurrentUserId();				// 現在のユーザID
	}
	/**
	 * 配送方法の取得
	 *
	 * @return array		配送方法レコード
	 */
	function _getDeliveryMethodRows()
	{
		if (!isset($this->delivMethodRows)){
			$this->delivMethodRows = array();
			$status = $this->ecObj->getActiveDelivMethodRows($this->langId, $rows);
			if ($status) $this->delivMethodRows = $rows;
		}
		return $this->delivMethodRows;
	}
	/**
	 * 配送方法の総数取得
	 *
	 * @return int     				配送方法数
	 */
	function getDeliveryMethodCount()
	{
		$rows = $this->_getDeliveryMethodRows();
		return count($rows);
	}
	/**
	 * 配送方法クラスを取得
	 *
	 * @return array     				配送方法、配送クラスの連想配列
	 */
	function getDeliveryMethodClass()
	{
		$methodArray = array();
		$rows = $this->_getDeliveryMethodRows();
		$rowCount = count($rows);
		for ($i = 0; $i < $rowCount; $i++){
			$row = $rows[$i];
			$methodArray[$row['do_id']] = self::DELIVERY_METHOD_CLASS/*配送クラス名(共通)*/;
		}
		return $methodArray;
	}
	/**
	 * 配送方法の初期化パラメータを取得
	 *
	 * @return array     				配送方法,配送クラス,初期化パラメータ,タイトルの配列
	 */
	function getDeliveryMethodInitParam()
	{
		$methodArray = array();
		$rows = $this->_getDeliveryMethodRows();
		$rowCount = count($rows);
		for ($i = 0; $i < $rowCount; $i++){
			$row = $rows[$i];
			
			// IWidgetパラメータを解析
			$support = array();
			if (!empty($row['iw_params'])){
				$lines = explode(';', $row['iw_params']);
				for ($i = 0; $i < count($lines); $i++){
					$keyValue = explode('=', $lines[$i]);
					$key = strtolower(trim($keyValue[0]));
					$value = strtolower(trim($keyValue[1]));
					if ($key == 'wc_support'){
						$support = explode(',', $value);
					}
				}
			}
			$methodArray[] = array($row['do_id'], self::DELIVERY_METHOD_CLASS/*配送クラス名(共通)*/, $support, $row['do_name']);
		}
		return $methodArray;
	}
	/**
	 * 配送方法の変更のチェック用のハッシュを取得
	 *
	 * @return string	ハッシュ文字列
	 */
	function getDeliveryMethodHash()
	{
		$rows = $this->_getDeliveryMethodRows();
		return md5(json_encode($rows));
	}
	/**
	 * 配送料を集計
	 *
	 * @param string $methodId		配送方法ID
	 * @param array $package		カート内容
	 * @return float				集計額
	 */
	function calcDeliveryCost($methodId, $package)
	{
		$cost = 0;
		
		$rows = $this->_getDeliveryMethodRows();
		for ($i = 0; $i < count($rows); $i++){
			$row = $rows[$i];
			if ($methodId == $row['do_id']){		// 配送方法ID
				// 配送料金を求める
				$iWidgetId	= $row['do_iwidget_id'];	// インナーウィジェットID
				if (!empty($iWidgetId)){
					// パラメータをインナーウィジェットに設定し、計算結果を取得
					$optionParam = new stdClass;
					$optionParam->id = $row['do_id'];		// ユニークなID(配送方法ID)
					// データの更新方法を設定
	//				if ($this->replaceNew){		// データを再取得するかどうか
						$optionParam->init = true;		// 初期データ取得
	//				} else {
	//					$optionParam->init = false;		// 画面からの入力データを使用
	//				}
					$optionParam->userId = $this->userId;					// ログインユーザID
					$optionParam->languageId = $this->langId;		// 言語ID
					$optionParam->cartId = $this->cartId;					// 商品のカート
			//		$optionParam->productTotal = $this->productTotal;				// 商品総額
					$optionParam->productTotal = $package['contents_cost'];// 商品総額
			//		$optionParam->productCount = $this->productCount;		// 商品総数
					$optionParam->productCount =$this->get_package_item_qty($package);// 商品総数
					$optionParam->zipcode = $this->zipcode;		// 配送先の郵便番号
					$optionParam->stateId = $this->stateId;		// 配送先の都道府県
					if ($this->calcIWidgetParam($iWidgetId, $row['do_id'], $row['do_param'], $optionParam, $resultObj)){
						$cost = $resultObj->price;		// 配送料金
					}

					// インナーウィジェットの画面を取得
					$this->setIWidgetParam($iWidgetId, $row['do_id'], $row['do_param'], $optionParam);// パラメータをインナーウィジェットに設定
					$content = $this->getIWidgetContent($iWidgetId, $row['do_id']);	// 通常画面を取得
				}
				break;
			}
		}
		return $cost;
	}
	/**
	 * インナーウィジェットを出力を取得
	 *
	 * @param string $id		ウィジェットID+インナーウィジェットID
	 * @param string $configId	インナーウィジェット定義ID
	 * @param bool $isAdmin		管理者機能(adminディレクトリ以下)かどうか
	 * @return string 			出力内容
	 */
	function getIWidgetContent($id, $configId, $isAdmin = false)
	{
		$ret = $this->gPage->commandIWidget(10/*コンテンツ取得*/, $id, $configId, $paramObj, $optionParamObj, $resultObj, $content, $isAdmin);
		return $content;
	}
	/**
	 * インナーウィジェットにパラメータを設定
	 *
	 * @param string $id				ウィジェットID+インナーウィジェットID
	 * @param string $configId			インナーウィジェット定義ID
	 * @param string $param				シリアル化したパラメータオブジェクト
	 * @param object $optionParamObj	追加パラメータオブジェクト
	 * @param bool $isAdmin				管理者機能(adminディレクトリ以下)かどうか
	 * @return string 					出力内容
	 */
	function setIWidgetParam($id, $configId, $param, $optionParamObj, $isAdmin = false)
	{
		$paramObj = unserialize($param);			// パラメータをオブジェクト化
		$ret = $this->gPage->commandIWidget(0/*パラメータ設定*/, $id, $configId, $paramObj, $optionParamObj, $resultObj, $content, $isAdmin);
		return $ret;
	}
	/**
	 * インナーウィジェットにパラメータを設定し計算処理を行う
	 *
	 * @param string $id				ウィジェットID+インナーウィジェットID
	 * @param string $configId			インナーウィジェット定義ID
	 * @param string $param				シリアル化したパラメータオブジェクト
	 * @param object $optionParamObj	追加パラメータオブジェクト
	 * @param bool $isAdmin				管理者機能(adminディレクトリ以下)かどうか
	 * @return string 					出力内容
	 */
	function calcIWidgetParam($id, $configId, $param, &$optionParamObj, &$resultObj, $isAdmin = false)
	{
		$paramObj = unserialize($param);			// パラメータをオブジェクト化
		$ret = $this->gPage->commandIWidget(2/*計算*/, $id, $configId, $paramObj, $optionParamObj, $resultObj, $content, $isAdmin);
		return $ret;
	}
	/**
	 * Get items in package.
	 * @param  array $package
	 * @return int
	 */
	public function get_package_item_qty( $package ) {
		$total_quantity = 0;
		foreach ( $package['contents'] as $item_id => $values ) {
			if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
				$total_quantity += $values['quantity'];
			}
		}
		return $total_quantity;
	}
}
?>
