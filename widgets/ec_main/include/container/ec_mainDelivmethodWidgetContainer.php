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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainDelivmethodWidgetContainer extends ec_mainBaseWidgetContainer
{
	private $zipcode;	// 郵便番号
	private $stateId;	// 都道府県ID
	private $deliveryMethod;		// 配送方法
	private $deliveryMethodCount;	// 配送方法総数
	private $cartId;
	private $productTotal;		// 商品合計額
	private $productCount;		// 商品総数
	private $demandTime;	// 希望時間帯
	private $demandDt;		// 希望日
	private $replaceNew;		// データを再取得するかどうか
	const DEFAULT_COUNTRY_ID = 'JPN';	// デフォルト国ID
	
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
		return 'delivery_method.tmpl.html';
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
		$defaultCurrency = ec_mainCommonDef::DEFAULT_CURRENCY;		// 通貨

		// 初期データ取得
		// クッキー読み込み、カートIDを取得。カートの商品を集計する(配送料金の計算に必要)
		$this->cartId = $request->getCookieValue(M3_COOKIE_CART_ID);
		$this->productTotal = 0;		// 合計価格
		$this->productCount = 0;		// 商品総数
		$ret = $this->getTotalPrice($price, $count);
		if ($ret){
			$this->productTotal = $price;
			$this->productCount = $count;	// 商品総数
		} else {
			// カート内の商品の価格が変更されている場合はカート画面へ遷移
		}
		
		$act = $request->trimValueOf('act');
		$this->deliveryMethod = $request->trimValueOf('item_delivery_method');	// 配送方法
		
		$this->replaceNew = false;		// データを再取得するかどうか
		if ($act == 'regist'){			// 配送先仮登録
			$this->checkInput($this->deliveryMethod, 'お届け方法', 'お届け方法が選択されていません');
			
			// エラーなしの場合
			if ($this->getMsgCount() == 0){
				// 注文書を取得
				$ret = $this->_getOrderSheet($row);
				if ($ret){
					// 配送料計算に必要な変数を取得
					$this->zipcode = $row['oe_deliv_zipcode'];	// 郵便番号
					$this->stateId = $row['oe_deliv_state_id'];	// 都道府県

					// 配送料金を求める
					$currency_id = $defaultCurrency;
					$subtotal = $this->productTotal;		// 商品合計
					$deliv_fee = 0;		// 配送料
					$this->demandTime = '';	// 希望時間帯
					$this->demandDt = $this->gEnv->getInitValueOfTimestamp();		// 希望日
					$appoint_dt = $this->gEnv->getInitValueOfTimestamp();		// 予定納期
					if (self::$_orderDb->getDelivMethod($this->deliveryMethod, $this->_langId, 0/*デフォルトのセットID*/, $delivMethodRow)){
						$iWidgetId	= $delivMethodRow['do_iwidget_id'];	// インナーウィジェットID
						if (!empty($iWidgetId)){
							// パラメータをインナーウィジェットに設定し、計算結果を取得
							$optionParam = new stdClass;
							$optionParam->id = $delivMethodRow['do_id'];
							$optionParam->init = true;		// 初期データ取得
							$optionParam->userId = $this->_userId;					// ログインユーザID
							$optionParam->languageId = $this->_langId;		// 言語ID
							$optionParam->cartId = $this->cartId;					// 商品のカート
							$optionParam->productTotal = $subtotal;				// 商品総額
							$optionParam->productCount = $this->productCount;	// 商品総数
							$optionParam->zipcode = $this->zipcode;		// 配送先の郵便番号
							$optionParam->stateId = $this->stateId;		// 配送先の都道府県
							if ($this->calcIWidgetParam($iWidgetId, $delivMethodRow['do_id'], $delivMethodRow['do_param'], $optionParam, $resultObj)){
								if (isset($resultObj->price)) $deliv_fee = $resultObj->price;		// 配送料金
								if (isset($resultObj->date)) $this->demandDt = $resultObj->date;	// 希望日
								if (isset($resultObj->time)) $this->demandTime = $resultObj->time;	// 希望時間帯
							}
						}
					}
					$charge = 0;		// 手数料
					$discount = 0;		// 値引き額
					$total = $subtotal - $discount + $deliv_fee + $charge;		// 総支払額を求める
						
					$ret = self::$_orderDb->updateOrderSheet($this->_userId, $this->_langId, $this->_getClientId(),
						$row['oe_custm_id'], $row['oe_custm_name'], $row['oe_custm_name_kana'], $row['oe_custm_person'], $row['oe_custm_person_kana'],
						$row['oe_custm_zipcode'], $row['oe_custm_state_id'], $row['oe_custm_address1'], $row['oe_custm_address2'], $row['oe_custm_phone'], $row['oe_custm_fax'], $row['oe_custm_email'], $row['oe_custm_country_id'], 
						$row['oe_deliv_id'], $row['oe_deliv_name'], $row['oe_deliv_name_kana'], $row['oe_deliv_person'], $row['oe_deliv_person_kana'],
						$row['oe_deliv_zipcode'], $row['oe_deliv_state_id'], $row['oe_deliv_address1'], $row['oe_deliv_address2'], $row['oe_deliv_phone'], $row['oe_deliv_fax'], $row['oe_deliv_email'], $row['oe_deliv_country_id'],
						$row['oe_bill_id'], $row['oe_bill_name'], $row['oe_bill_name_kana'], $row['oe_bill_person'], $row['oe_bill_person_kana'], 
						$row['oe_bill_zipcode'], $row['oe_bill_state_id'], $row['oe_bill_address1'], $row['oe_bill_address2'], $row['oe_bill_phone'], $row['oe_bill_fax'], $row['oe_bill_email'], $row['oe_bill_country_id'],
						$this->deliveryMethod, $row['oe_pay_method_id'], $row['oe_card_type'], $row['oe_card_owner'], $row['oe_card_number'], $row['oe_card_expires'],
						$this->demandDt, $this->demandTime, $appoint_dt, $currency_id, $subtotal, $discount, $deliv_fee, $charge, $total);
					if ($ret){
						$this->replaceNew = true;		// データを再取得
						
						// エラーがなければ次の画面へ
						$nextPage = $this->gEnv->createCurrentPageUrl() . '&task=' . $this->_getOrderNextTask();
						$this->gPage->redirect($nextPage);
						return;
					}
				}
				$this->setAppErrorMsg('データ更新に失敗しました');
			}
		} else {		// 初期表示
			$this->replaceNew = true;		// データを再取得
			
			// 注文書を取得
			$ret = $this->_getOrderSheet($row);	// 注文情報を取得
			if ($ret){			// データが既に登録済みのとき
				$this->zipcode = $row['oe_deliv_zipcode'];
				$this->stateId = $row['oe_deliv_state_id'];
				$this->deliveryMethod = $row['oe_deliv_method_id'];			// 支払方法
				$this->demandTime = $row['oe_demand_time'];			// 希望時間帯
				$this->demandDt = $row['oe_demand_dt'];			// 希望日
			}
		}
		// 配送方法メニューを作成
		$this->deliveryMethodCount = self::$_orderDb->getAllDelivMethodCount($this->_langId);		// 配送方法総数
		self::$_orderDb->getAllDelivMethod($this->_langId, 0/*デフォルトのセットID*/, array($this, 'delivMethodLoop'));
		
		// 選択メッセージを表示
		if ($this->deliveryMethodCount > 1) $this->tmpl->setAttribute('select_message', 'visibility', 'visible');
		
		// 遷移先を設定
		$this->tmpl->addVar("_widget", "goback_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=' . $this->_getOrderNextTask(-1) . '&act=goback', true));		// 1つ前の画面
	}
	/**
	 * 取得した配送方法をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function delivMethodLoop($index, $fetchedRow, $param)
	{
		$isCurrent = false;			// 選択中の配送方法
		if ($fetchedRow['do_id'] == $this->deliveryMethod) $isCurrent = true;		// 選択中の配送方法
		
		$checked = '';
		if ($isCurrent) $checked = 'checked';

		// 入力コントロールタイプ
		$inputType = 'radio';
		if ($this->deliveryMethodCount <= 1) $inputType = 'hidden';			// 配送方法が選択できないとき
		
		// 出力を初期化
		$price = 0;
		$content = '';
		
		// 配送料金を求める
		$iWidgetId	= $fetchedRow['do_iwidget_id'];	// インナーウィジェットID
		if (!empty($iWidgetId)){
			// パラメータをインナーウィジェットに設定し、計算結果を取得
			$optionParam = new stdClass;
			$optionParam->id = $fetchedRow['do_id'];		// ユニークなID(配送方法ID)
			// データの更新方法を設定
			if ($this->replaceNew){		// データを再取得するかどうか
				$optionParam->init = true;		// 初期データ取得
			} else {
				$optionParam->init = false;		// 画面からの入力データを使用
			}
			$optionParam->userId = $this->_userId;					// ログインユーザID
			$optionParam->languageId = $this->_langId;		// 言語ID
			$optionParam->cartId = $this->cartId;					// 商品のカート
			$optionParam->productTotal = $this->productTotal;				// 商品総額
			$optionParam->productCount = $this->productCount;		// 商品総数
			$optionParam->zipcode = $this->zipcode;		// 配送先の郵便番号
			$optionParam->stateId = $this->stateId;		// 配送先の都道府県

			if ($isCurrent){		// 選択中の配送方法のとき
				$optionParam->time = $this->demandTime;		// 希望時間帯
				$optionParam->date = $this->demandDt;			// 希望日
			}
			if ($this->calcIWidgetParam($iWidgetId, $fetchedRow['do_id'], $fetchedRow['do_param'], $optionParam, $resultObj)){
				$price = $resultObj->price;		// 配送料金
			}
			// インナーウィジェットの画面を取得
			$this->setIWidgetParam($iWidgetId, $fetchedRow['do_id'], $fetchedRow['do_param'], $optionParam);// パラメータをインナーウィジェットに設定
			$content = $this->getIWidgetContent($iWidgetId, $fetchedRow['do_id']);	// 通常画面を取得
		}
		// 送料が0円のときは「無料」表示
		$unit = '円';
		if (empty($price)){
			$price = '';
			$unit = '無料';
		}
		$row = array(
			'type'		=> $inputType,			// 入力コントロールタイプ
			'value'		=> $this->convertToDispString($fetchedRow['do_id']),			// ID
			'name'		=> $this->convertToDispString($fetchedRow['do_name']),		// 表示名
			'desc'		=> $fetchedRow['do_description'],							// 説明(HTMLが含まれる)
			'price'		=> $price,							// 配送料金
			'unit'		=> $unit,							// 単位
			'def_content'		=> $content,		// ユーザ選択用コンテンツ
			'checked'	=> $checked														// 選択中かどうか
		);
		$this->tmpl->addVars('deliv_method_list', $row);
		$this->tmpl->parseTemplate('deliv_method_list', 'a');
		return true;
	}
}
?>
