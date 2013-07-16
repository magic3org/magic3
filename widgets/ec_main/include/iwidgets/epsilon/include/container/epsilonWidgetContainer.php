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
 * @version    SVN: $Id: epsilonWidgetContainer.php 5437 2012-12-07 13:14:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentIWidgetContainerPath() . '/epsilonCommonDef.php');
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');
require_once($gEnvManager->getCurrentIWidgetDbPath() . '/epsilonDb.php');

class epsilonWidgetContainer extends BaseIWidgetContainer
{
	private $db;			// DBオブジェクト
	private $ecObj;			// 価格計算用オブジェクト
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const USER_ID_HEAD = 'user-';				// ユーザID作成用ヘッダ
	const PRODUCT_NAME_FORMAT	= '%s(%s)';		// 商品名表示フォーマット
	const PRODUCT_CODE_FORMAT	= '%s-%s';		// 商品コード表示フォーマット
	const PRODUCT_CLASS_DEFAULT	= '';		// 商品クラス
	const PRODUCT_CLASS_PHOTO	= 'photo';		// 商品クラス
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 価格計算用オブジェクト取得
		$this->ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
		
		// DBオブジェクト取得
		$this->db = new epsilonDb();
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
		return '';
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
		$isErr = false;			// エラーが発生したかどうか
		
		// 基本情報を取得
		$id		= $optionObj->id;		// ユニークなID
		$init	= $optionObj->init;		// データ初期化を行うかどうか
		$operation = $optionObj->operation;		// 操作種別
		
		if ($act == 'calc'){		// 計算のとき
			if ($operation == 'cancel_order'){			// キャンセル処理のとき
				// 可変データ取得
				$userId			= $optionObj->userId;			// ログインユーザID
				$langId			= $optionObj->langId;
				$cartId			= $optionObj->cartId;					// 商品のカート
				$orderSheetRow	= $optionObj->orderSheetRow;		// 注文書データ
				
				// イプシロン用注文番号をMagic3用注文番号に変換
				$orderNo = $request->trimValueOf('order_number');
				$orderNoArray = str_split($orderNo, 8);
				$orderNo = $orderNoArray[0] . '-' . $orderNoArray[1];
				$ret = $this->db->getOrderRecord($userId, $orderNo, $row);

				// 戻りデータ設定
				if ($ret){
					$resultObj->retcode = 1;		// 実行結果(成功)
					$resultObj->orderId = $row['or_id'];		// 注文ID
				} else {
					$resultObj->retcode = 0;		// 実行結果(失敗)
				}
				$this->setResultObj($resultObj);
			} else if ($operation == 'complete_order'){			// 決済完了のとき
				// 可変データ取得
				$userId			= $optionObj->userId;			// ログインユーザID
				$langId			= $optionObj->langId;
				$cartId			= $optionObj->cartId;					// 商品のカート
				$orderSheetRow	= $optionObj->orderSheetRow;		// 注文書データ
				
				// イプシロン用注文番号をMagic3用注文番号に変換
				$orderNo = $request->trimValueOf('order_number');
				$orderNoArray = str_split($orderNo, 8);
				$orderNo = $orderNoArray[0] . '-' . $orderNoArray[1];
				$ret = $this->db->getOrderRecord($userId, $orderNo, $row);

				// イプシロン実行結果
				$result = $request->trimValueOf('result');				// 実行結果(0=失敗、1=成功)
				$transCode = $request->trimValueOf('trans_code');		// トランザクションコード
				$userId = $request->trimValueOf('user_id');				// ユーザID
				
				// 戻りデータ設定
				if ($ret && $result == 1){
					$resultObj->retcode = 1;		// 実行結果(成功)
					$resultObj->orderId = $row['or_id'];		// 注文ID
					$resultObj->note = 'トランザクションコード=' . $transCode . ',イプシロン登録ユーザID=' . $userId;		// 補足情報
				} else {
					$resultObj->retcode = 0;		// 実行結果(失敗)
				}
				$this->setResultObj($resultObj);
			} else {		// 注文登録
				// 定義値取得
				$connectMode	= $configObj->connectMode;	// 接続モード
				$contractCode	= $configObj->contractCode;		// 契約番号
				if ($connectMode == epsilonCommonDef::PRODUCTION_MODE){		// 本番モードのとき
					$url = $configObj->url;		// 本番サーバURL
					$isTestMode = false;
				} else {
					$url = epsilonCommonDef::TEST_URL;
					$isTestMode = true;			// テストモードで実行
				}
			
				// 可変データ取得
				$userId			= $optionObj->userId;			// ログインユーザID
				$langId			= $optionObj->langId;
				$cartId			= $optionObj->cartId;					// 商品のカート
				$orderSheetRow	= $optionObj->orderSheetRow;		// 注文書データ
	//			$orderId		= $optionObj->orderId;			// 注文ID
				$orderNo		= $optionObj->orderNo;				// 注文番号
				$orderNo		= str_replace('-', '', $orderNo);		// イプシロンでは「-」が使用できないので削除
				if (empty($optionObj->userId)){					// ユーザIDがない場合はクライアントIDを使用
					$userId = self::USER_ID_HEAD . $orderSheetRow['oe_client_id'];
				} else {
					$userId = self::USER_ID_HEAD . $optionObj->userId;
				}
			
				// 注文書データ取得
				$userName = $orderSheetRow['oe_bill_name'];		// ユーザ名(請求先)
				$userEmail = $orderSheetRow['oe_bill_email'];		// ユーザEメール(請求先)
				if (empty($userEmail)) $userEmail = $this->gEnv->getSiteEmail();		// メールアドレスが設定されていない場合はサイト管理者のメールアドレスを設定
				$total		= intval($orderSheetRow['oe_total']);		// 支払総額
				
				// 受注ヘッダを取得
	/*			$ret = $this->db->getOrder($orderId, $row);
				if ($ret){
					$userName = $row['or_bill_name'];		// ユーザ名(請求先)
					$userEmail = $row['or_bill_email'];		// ユーザEメール(請求先)
					if (empty($userEmail)) $userEmail = $this->gEnv->getSiteEmail();		// メールアドレスが設定されていない場合はサイト管理者のメールアドレスを設定
					$total		= intval($row['or_total']);		// 支払総額
				} else {
					$errMsg = '注文情報ヘッダを取得できません。注文ID=' . $orderId;
					$isErr = true;			// エラーが発生したかどうか
				}*/

				// 注文商品を取得
	/*			$ret = $this->db->getFirstOrderDetail($orderId, $langId, $row);
				if ($ret){
					$productName = $row['od_product_name'];		// 商品名
					$productCode = $row['od_product_code'];		// 商品コード
				} else {
					$errMsg = '注文情報詳細を取得できません。注文ID=' . $orderId;
					$isErr = true;			// エラーが発生したかどうか
				}*/
				// カートから最初の商品を取得
				$productClassInCart = $this->ecObj->db->getProductClassInCart($cartId, $langId);
				if (count($productClassInCart) > 0){
					$productClass = $productClassInCart[0];
					$ret = $this->ecObj->db->getCartItemByIndex($cartId, $langId, $productClass, 0, $cartItemRow);
					if ($ret){
						switch ($productClass){
							case self::PRODUCT_CLASS_PHOTO:		// フォトギャラリー画像のとき
								$photoId			= $cartItemRow['ht_public_id'];		// 公開画像ID
								$title				= $cartItemRow['ht_name'];		// サムネール画像タイトル
								$productTypeName	= $cartItemRow['py_name'];		// 商品タイプ名
								$productTypeCode	= $cartItemRow['py_code'];		// 商品タイプコード

								// 表示用の商品名、商品コード作成
								$productName = sprintf(self::PRODUCT_NAME_FORMAT, $productTypeName, $title);		// 商品名
								$productCode = sprintf(self::PRODUCT_CODE_FORMAT, $photoId, $productTypeCode);		// 商品コード
								break;
							case self::PRODUCT_CLASS_DEFAULT:	// 一般商品のとき
								// 表示用の商品名、商品コード作成
								$productName	= $cartItemRow['pt_name'];		// 商品名
								$productCode	= $cartItemRow['pt_code'];		// 商品コード
								break;
						}
					}
				}
			
				// 決済区分 (使用したい決済方法を指定してください。登録時に申し込まれていない決済方法は指定できません。)
		//		$st_code = '10100-0000-00000';   // 指定方法はCGI設定マニュアルの「決済区分について」を参照してください。
				$st_code = '10000-0000-00000';		// クレジットカード決済のみ
		//		$st_code = '00100-0000-00000';		// コンビニ決済のみ
		//		$st_code = '10100-0000-00000';		// クレジットカード決済とコンビニ決済
		
				// 課金区分 (1:一回のみ 2～10:月次課金)
				// 月次課金について契約がない場合は利用できません。また、月次課金を設定した場合決済区分はクレジットカード決済のみとなります。
				$mission_code = 1;

				// 処理区分 (1:初回課金 2:登録済み課金 3:登録のみ 4:登録変更 8:月次課金解除 9:退会)
				// 月次課金をご利用にならない場合は1:初回課金をご利用ください。
				// 各処理区分のご利用に関してはCGI設定マニュアルの「処理区分について」を参照してください。
				$process_code = 1;

				$postParam = array();
				$postParam['contract_code']	= $contractCode;		// 契約番号
				$postParam['user_id']		= $userId;				// ユーザID
				$postParam['user_name']		= $userName;
				$postParam['user_mail_add'] = $userEmail;
				$postParam['item_code']		= $productCode;
				$postParam['item_name']		= $productName;
				$postParam['order_number']	= $orderNo;			// 注文番号
				$postParam['st_code']		= $st_code;
				$postParam['mission_code']	= $mission_code;
				$postParam['item_price']	= $total;			// 支払総額
				$postParam['process_code']	= $process_code;
				$postParam['memo1']			= '本番用オーダー情報';	// 追加情報1
				$postParam['memo2']			= '';					// 追加情報2
				$postParam['xml']			= '1';
  
	  			// 決済サーバ接続
				if ($isErr){			// エラー発生の場合
					$addMsg = '';
					if ($isTestMode) $addMsg = '[テスト]';
					$this->gOpeLog->writeError(__METHOD__, $addMsg . 'イプシロン決済サーバ間通信エラー(要因=注文情報取得エラー)', 1100, $errMsg);
					$ret = false;
				} else {
					$ret = epsilonCommonDef::postData($url, $postParam, $resultArray, $isTestMode);
				}
			
				// 戻りデータ設定
				if ($ret){
					$resultObj->retcode = 1;		// 実行結果(成功)
					$resultObj->redirectUrl = $resultArray['redirect'];			// リダイレクト先
				} else {
					$resultObj->retcode = 0;		// 実行結果(失敗)
				}
				$this->setResultObj($resultObj);
			}
		} else if ($act == 'content'){		// 画面表示のとき
		}
	}
}
?>
