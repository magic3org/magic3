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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_ec_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/ec_mainOrderDb.php');
require_once($gEnvManager->getLibPath() .	'/tcpdf/config/lang/jpn.php');
require_once($gEnvManager->getLibPath() .	'/tcpdf/tcpdf.php');

class admin_ec_mainOrderWidgetContainer extends admin_ec_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;			// シリアル番号
	private $orderRow;			// 受注内容
	private $currency;			// 通貨
	private $orderStatus;		// 受注ステータス
	private $custmState;		// 顧客都道府県
	private $delivState;		// 配送都道府県
	private $billState;		// 請求都道府県
	private $deliveryMethod;		// 配送方法
	private $payMethod;			// 支払い方法
	private $search_status;		// 検索ステータス
	private $serialArray = array();			// シリアル番号の配列
	private $updateContentAccess;		// コンテンツアクセス権の設定かどうか
	private $productClass;		// 商品クラス
	private $productType;		// 商品タイプ
	private $contentIdArray;		// コンテンツID
	private $cancelStock;		// 在庫キャンセル処理を行うかどうか
	
	const STANDARD_PRICE = 'selling';		// 通常価格
	const ORDER_STATUS_CLOSE = 900;			// 受注処理終了
	const PRICE_OBJ_ID = "eclib";		// 価格計算オブジェクトID
	const DEFAULT_COUNTRY_ID = 'JPN';	// デフォルト国ID
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const TARGET_WIDGET = 'photo_main';		// 画像の詳細情報
	const DEFAULT_DELIV_SHEET_TITLE = '納品書';	// 納品書(PDF)のデフォルトタイトル名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_mainOrderDb();
		
		// 価格計算用オブジェクト取得
		$this->ecObj = $this->gInstance->getObject(self::PRICE_OBJ_ID);
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
		$task = $request->trimValueOf('task');
		if ($task == 'order_detail'){		// 詳細画面
			return 'admin_order_detail.tmpl.html';
		} else {
			return 'admin_order.tmpl.html';
		}
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
		$task = $request->trimValueOf('task');
		if ($task == 'order_detail'){		// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ##### 検索条件 #####
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		// DBの保存設定値を取得
		$maxListCount = self::DEFAULT_LIST_COUNT;				// 表示項目数
//		$this->search_status = $request->trimValueOf('status');		// ステータス
//		$keyword = $request->trimValueOf('keyword');			// 検索キーワード
		$search_keyword = $request->trimValueOf('search_keyword');			// 検索キーワード
		$this->search_status = $request->trimValueOf('search_status');		// ステータス
		
		$act = $request->trimValueOf('act');
		if ($act == 'search'){		// 検索のとき
			$pageNo = 1;		// ページ番号初期化
		} else if ($act == 'delete'){		// 項目削除の場合
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				for ($i = 0; $i < count($delItems); $i++){
					$ret = $this->db->delOrder($delItems[$i], $this->_userId);
					if (!$ret) break;
				}
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		// ###### 検索条件を作成 ######
		// ステータス
		if ($this->search_status == 0){		// 終了、キャンセル以外
			$statusMin = 0;
			$statusMax = self::ORDER_STATUS_CLOSE -1;
		} else {
			$statusMin = 0;
			$statusMax = 0;
		}
		
		// 総数を取得
		$totalCount = $this->db->searchOrderHeaderCount($statusMin, $statusMax);
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数

		// 表示するページ番号の修正
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;

		// 受注リストを表示
		$this->db->searchOrderHeader($statusMin, $statusMax, $maxListCount, ($pageNo -1) * $maxListCount, array($this, 'orderListLoop'));
		if (empty($this->serialArray)) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 受注データがないときは、一覧を表示しない
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				$linkUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET . 
				'&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() . '&task=order&status=' . $this->search_status . '&page=' . $i;
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="' . $this->getUrl($linkUrl, true) . '" >' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		// 検出順を作成
		$startNo = ($pageNo -1) * $maxListCount +1;
		$endNo = $pageNo * $maxListCount > $totalCount ? $totalCount : $pageNo * $maxListCount;
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		$this->tmpl->addVar("search_range", "start_no", $startNo);
		$this->tmpl->addVar("search_range", "end_no", $endNo);
		if ($totalCount > 0) $this->tmpl->setAttribute('search_range', 'visibility', 'visible');// 検出範囲を表示
		
		// ##### 検索エリア作成 #####
		// 検索ボタン作成
		$eventAttr = 'onclick="showSearchArea();"';
		$searchButtonTag = $this->gDesign->createSearchButton(''/*同画面*/, '受注を検索'/*ボタンタイトル*/, ''/*タグID*/, $eventAttr/*クリックイベント時処理*/);
		$this->tmpl->addVar("_widget", "search_area_button", $searchButtonTag);
		
		if ($this->search_status == 0){		// 終了、キャンセル以外
			$this->tmpl->addVar("_widget", "status_active_selected", 'selected');
		} else {
			$this->tmpl->addVar("_widget", "status_all_selected", 'selected');
		}
		
		// 埋め込みパラメータの設定
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示中の項目のシリアル番号設定
		$this->tmpl->addVar("_widget", "admin_url", $this->getUrl($this->gEnv->getDefaultAdminUrl()));
		$this->tmpl->addVar('_widget', 'widget_id', $this->gEnv->getCurrentWidgetId());
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ユーザ情報、表示言語
		$this->now = date("Y/m/d H:i:s");	// 現在日時
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');			// 選択項目のシリアル番号
		
		// 受注詳細への直接アクセスのときは、シリアル番号を再設定
		$orderNo = $request->trimValueOf('orderno');			// 受注詳細を直接参照する場合の受注No
		if (!empty($orderNo)){
			$ret = $this->db->getOrderByOrderNo($orderNo, $row);
			if ($ret) $this->serialNo = $row['or_serial'];
		}
		
		// 入力項目
		$name = $request->trimValueOf('item_name');		// 顧客名
		$nameKana = $request->trimValueOf('item_name_kana');		// 顧客名(カナ)
		$custm_zipcode = $request->trimValueOf('item_custm_zipcode');		// 顧客郵便番号
		$custm_address = $request->trimValueOf('item_custm_address');		// 顧客住所
		$custm_address2 = $request->trimValueOf('item_custm_address2');		// 顧客住所2
		$custm_phone = $request->trimValueOf('item_custm_phone');		// 顧客電話番号
		$custm_fax = $request->trimValueOf('item_custm_fax');		// 顧客FAX
		$custm_email = $request->trimValueOf('item_custm_email');		// 顧客Eメール
		
		$deliv_name = $request->trimValueOf('item_deliv_name');		// 配送先名
		$deliv_name_kana = $request->trimValueOf('item_deliv_name_kana');		// 配送先名(カナ)
		$deliv_zipcode = $request->trimValueOf('item_deliv_zipcode');		// 配送先郵便番号
		$deliv_address = $request->trimValueOf('item_deliv_address');		// 配送先住所
		$deliv_address2 = $request->trimValueOf('item_deliv_address2');		// 配送先住所2
		$deliv_phone = $request->trimValueOf('item_deliv_phone');		// 配送先電話番号
		$deliv_fax = $request->trimValueOf('item_deliv_fax');		// 配送先FAX
		$deliv_email = $request->trimValueOf('item_deliv_email');		// 配送先Eメール
		$bill_name = $request->trimValueOf('item_bill_name');		// 請求先名
		$bill_name_kana = $request->trimValueOf('item_bill_name_kana');		// 請求先名(カナ)
		$bill_zipcode = $request->trimValueOf('item_bill_zipcode');		// 請求先郵便番号
		$bill_address = $request->trimValueOf('item_bill_address');		// 請求先住所
		$bill_address2 = $request->trimValueOf('item_bill_address2');		// 請求先住所2
		$bill_phone = $request->trimValueOf('item_bill_phone');		// 請求先電話番号
		$bill_fax = $request->trimValueOf('item_bill_fax');		// 請求先FAX
		$bill_email = $request->trimValueOf('item_bill_email');		// 請求先Eメール
		$this->deliveryMethod = $request->trimValueOf('item_delivery_method');		// 選択中の配送方法
		$this->payMethod = $request->trimValueOf('item_payment_method');			// 選択中の支払い方法
		$this->custmState = $request->trimValueOf('item_custm_state');		// 顧客都道府県
		$this->delivState = $request->trimValueOf('item_deliv_state');		// 配送都道府県
		$this->billState = $request->trimValueOf('item_bill_state');		// 請求都道府県

		$this->orderStatus = $request->trimValueOf('item_order_status');		// 受注ステータス
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name,				'顧客名');		
//			$this->checkInput($nameKana,			'顧客名(カナ)');
			$this->checkSingleByte($custm_zipcode,	'顧客郵便番号', true);
			$this->checkNumeric($this->custmState,	'顧客都道府県', true);
//			$this->checkInput($custm_address,		'顧客住所');	
			$this->checkSingleByte($custm_phone,	'顧客電話番号', true);
			$this->checkSingleByte($custm_fax,		'顧客FAX', true);
			$this->checkMailAddress($custm_email,	'顧客Eメール', true);
			
//			$this->checkInput($deliv_name,			'配送先名');		
//			$this->checkInput($deliv_name_kana,		'配送先名(カナ)');
			$this->checkSingleByte($deliv_zipcode,	'配送先郵便番号', true);
			$this->checkNumeric($this->delivState,	'配送先都道府県', true);
//			$this->checkInput($deliv_address,		'配送先住所');	
			$this->checkSingleByte($deliv_phone,	'配送先電話番号', true);
			$this->checkSingleByte($deliv_fax,		'配送先FAX', true);
			$this->checkMailAddress($deliv_email,	'配送先Eメール', true);
			
			$this->checkInput($bill_name,			'請求先名');		
//			$this->checkInput($bill_name_kana,		'請求先名(カナ)');
			$this->checkSingleByte($bill_zipcode,	'請求先郵便番号', true);
			$this->checkNumeric($this->billState,	'請求先都道府県', true);
//			$this->checkInput($bill_address,		'請求先住所');	
			$this->checkSingleByte($bill_phone,		'請求先電話番号', true);
			$this->checkSingleByte($bill_fax,		'請求先FAX', true);
			$this->checkMailAddress($bill_email,	'請求先Eメール', true);

			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// データ取得
				$ret = $this->db->getOrderBySerial($this->serialNo, $row);
				if ($ret){
					$enableUpdate = true;		// データ更新可能かどうか
					
					// 入金日付がない場合は一旦入金済みを実行した後終了可能
					if ($this->orderStatus == ec_mainCommonDef::ORDER_STATUS_CLOSE && $row['or_pay_dt'] == $this->gEnv->getInitValueOfTimestamp()){
						$this->setAppErrorMsg('入金日時が設定されていません。一旦「受注ステータス」を「入金済み」に更新してから「終了」に変更してください。');
						$enableUpdate = false;		// データ更新不可
					}
			
					if ($enableUpdate){
						$payUpdate = false;		// 入金状況を更新するかどうか
						if ($row['or_order_status'] != ec_mainCommonDef::ORDER_STATUS_PAYMENT_COMPLETED && 
							$this->orderStatus == ec_mainCommonDef::ORDER_STATUS_PAYMENT_COMPLETED) $payUpdate = true;
					
						// 受注状態が「入金済み」に設定のときは支払い日時を更新
						$payDt = $row['or_pay_dt'];
						if ($payUpdate) $payDt = $this->now;

						// トランザクションスタート
						$this->db->startTransaction();
			
						// 受注ヘッダ作成
						//$ret = $this->db->updateOrder($this->serialNo, $this->_userId, $this->_langId, $row['or_order_no'],
						$ret = $this->db->updateOrder($this->serialNo, $row['or_user_id'], $row['or_language_id'], $row['or_order_no'],
							$row['or_custm_id'], $name, $nameKana, $row['or_custm_person'], $row['or_custm_person_kana'],
							$custm_zipcode, $this->custmState, $custm_address, $custm_address2, $custm_phone, $custm_fax, $custm_email, $row['or_custm_country_id'], 
							$row['or_deliv_id'], $deliv_name, $deliv_name_kana, $row['or_deliv_person'], $row['or_deliv_person_kana'],
							$deliv_zipcode, $this->delivState, $deliv_address, $deliv_address2, $deliv_phone, $deliv_fax, $deliv_email, $row['or_deliv_country_id'],
							$row['or_bill_id'], $bill_name, $bill_name_kana, $row['or_bill_person'], $row['or_bill_person_kana'], 
							$bill_zipcode, $this->billState, $bill_address, $bill_address2, $bill_phone, $bill_fax, $bill_email, $row['or_bill_country_id'],
							$this->deliveryMethod, $this->payMethod, $row['or_card_type'], $row['or_card_owner'], $row['or_card_number'], $row['or_card_expires'],
							$row['or_demand_dt'], $row['or_demand_time'], $row['or_appoint_dt'], $row['or_currency_id'], $row['or_subtotal'], $row['or_discount'], $row['or_deliv_fee'], $row['or_charge'], $row['or_total'],
							$this->orderStatus, $row['or_estimate_dt'], $row['or_regist_dt'], $row['or_order_dt'], $row['or_deliv_dt'], $row['or_close_dt'],
							$this->_userId, $this->now, $newOrderId, $newSerial, $row['or_discount_desc'], $payDt);
						
						// キャンセルの場合は在庫を戻す
						if ($this->_getConfig(ec_mainCommonDef::CF_E_AUTO_STOCK) && $this->orderStatus == ec_mainCommonDef::ORDER_STATUS_CANCEL){
							$this->cancelStock = true;		// 在庫キャンセル処理を行うかどうか
							$this->db->getOrderDetailList($row['or_id'], $this->_langId, array($this, '_defaultOrderItemLoop'));
							$this->cancelStock = false;
						}
					
						// トランザクション終了
						$ret = $this->db->endTransaction();
						if ($ret){
							$this->setGuidanceMsg('データを更新しました');
					
							// 登録済みの受注情報を再取得
							$this->serialNo = $newSerial;
							$replaceNew = true;
						
							// 受注状態が「入金済み」に設定のときは購入完了処理
							if ($payUpdate) $this->setDownloadContentAccess($row['or_user_id']/*画像購入ユーザ*/, $row['or_id']);
						}
					}
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->db->delOrder($this->serialNo, $this->_userId);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'downloaddelivsheet'){		// 納品書ダウンロード
			// 受注内容を取得
			$ret = $this->db->getOrderBySerial($this->serialNo, $this->orderRow);
			
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
			$pdf->SetCreator('Magic3 ' . M3_SYSTEM_VERSION);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->AddPage();
			$pdf->SetFont('ipagp', '', 12);		// IPA Pゴシックフォント
			$pdf->SetMargins(20, 10, true);
			$sheet = $this->getParsedTemplateData('deliv_sheet.tmpl.html', array($this, 'createSheet'));
			$pdf->writeHTML($sheet, true, 0, true, 0);

			// ##### PDFを出力 #####
			$downloadFilename = self::DEFAULT_DELIV_SHEET_TITLE . '.pdf';					// ダウンロード時のファイル名
			$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
			$pdf->Output($tmpFile, 'F');		// ローカルファイルに保存

			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// ダウンロード処理
			$ret = $this->gPage->downloadFile($tmpFile, $downloadFilename, true/*実行後ファイル削除*/);
			
			// システム強制終了
			$this->gPage->exitSystem();
		} else {	// 初期表示
			if (empty($this->serialNo)){		// 新規登録のとき
				// 入力値初期化
				$name = '';		// 顧客名
				$nameKana = '';		// 顧客名(カナ)
				$custm_zipcode = '';		// 顧客郵便番号
				$custm_address = '';		// 顧客住所
				$custm_address2 = '';		// 顧客住所2
				$custm_phone = '';		// 顧客電話番号
				$custm_fax = '';		// 顧客FAX
				$custm_email = '';		// 顧客Eメール
				$deliv_name = '';		// 配送先名
				$deliv_name_kana = '';		// 配送先名(カナ)
				$deliv_zipcode = '';		// 配送先郵便番号
				$deliv_address = '';		// 配送先住所
				$deliv_address2 = '';		// 配送先住所2
				$deliv_phone = '';		// 配送先電話番号
				$deliv_fax = '';		// 配送先FAX
				$deliv_email = '';		// 配送先Eメール
				$bill_name = '';		// 請求先名
				$bill_name_kana = '';		// 請求先名(カナ)
				$bill_zipcode = '';		// 請求先郵便番号
				$bill_address = '';		// 請求先住所
				$bill_address2 = '';		// 請求先住所2
				$bill_phone = '';		// 請求先電話番号
				$bill_fax = '';		// 請求先FAX
				$bill_email = '';		// 請求先Eメール
				$this->deliveryMethod = '';		// 選択中の配送方法
				$this->payMethod = '';			// 選択中の支払い方法
				$this->custmState = 0;		// 顧客都道府県
				$this->delivState = 0;		// 配送都道府県
				$this->billState = 0;		// 請求都道府県
				$this->orderStatus = 0;		// 受注ステータス
			} else {
				$replaceNew = true;		// データ取得
			}
		}
		if ($replaceNew){
			// データ再取得
			$ret = $this->db->getOrderBySerial($this->serialNo, $row);
			if ($ret){
				// 取得値を設定
				$this->_langId = $row['or_language_id'];		// 受注時の言語
				$orderNo = $row['or_order_no'];		// 受注番号
				
				$name = $row['or_custm_name'];		// 顧客名
				$nameKana = $row['or_custm_name_kana'];		// 顧客名(カナ)
				$custm_zipcode = $row['or_custm_zipcode'];		// 顧客郵便番号
				$custm_address = $row['or_custm_address1'];		// 顧客住所
				$custm_address2 = $row['or_custm_address2'];		// 顧客住所2
				$custm_phone = $row['or_custm_phone'];		// 顧客電話番号
				$custm_fax = $row['or_custm_fax'];		// 顧客FAX
				$custm_email = $row['or_custm_email'];		// 顧客Eメール
				
				$deliv_name = $row['or_deliv_name'];		// 配送先名
				$deliv_name_kana = $row['or_deliv_name_kana'];		// 配送先名(カナ)
				$deliv_zipcode = $row['or_deliv_zipcode'];		// 配送先郵便番号
				$deliv_address = $row['or_deliv_address1'];		// 配送先住所
				$deliv_address2 = $row['or_deliv_address2'];		// 配送先住所2
				$deliv_phone = $row['or_deliv_phone'];		// 配送先電話番号
				$deliv_fax = $row['or_deliv_fax'];		// 配送先FAX
				$deliv_email = $row['or_deliv_email'];		// 配送先Eメール
				$bill_name = $row['or_bill_name'];		// 請求先名
				$bill_name_kana = $row['or_bill_name_kana'];		// 請求先名(カナ)
				$bill_zipcode = $row['or_bill_zipcode'];		// 請求先郵便番号
				$bill_address = $row['or_bill_address1'];		// 請求先住所
				$bill_address2 = $row['or_bill_address2'];		// 請求先住所2
				$bill_phone = $row['or_bill_phone'];		// 請求先電話番号
				$bill_fax = $row['or_bill_fax'];		// 請求先FAX
				$bill_email = $row['or_bill_email'];		// 請求先Eメール
				$this->deliveryMethod = $row['or_deliv_method_id'];		// 選択中の配送方法
				$this->payMethod = $row['or_pay_method_id'];			// 選択中の支払い方法
				$this->custmState = $row['or_custm_state_id'];		// 顧客都道府県
				$this->delivState = $row['or_deliv_state_id'];		// 配送都道府県
				$this->billState = $row['or_bill_state_id'];		// 請求都道府県

				$this->orderStatus = $row['or_order_status'];		// 受注ステータス
				$this->currency	= $row['or_currency_id'];	// 通貨
				$subtotal = $row['or_subtotal'];		// 商品総額
				$discount = $row['or_discount'];		// 値引き額
				$delivFee = $row['or_deliv_fee'];		// 配送料
				$charge = $row['or_charge'];		// 手数料
				$total = $row['or_total'];		// 総額
				
				$demand_dt = $row['or_demand_dt'];	// 希望日
				$demand_time = $row['or_demand_time'];	// 希望時間帯
				if ($demand_dt != $this->gEnv->getInitValueOfDate() || !empty($demand_time)){
					$delivNote = '配達希望日：' . $this->convertToDispDate($demand_dt) . '&nbsp;' . $this->convertToDispString($demand_time);
				}
				$discountDesc = $row['or_discount_desc'];		// 値引き説明
				
				$updateUser = $row['lu_name'];	// 更新者
				$updateDt = $row['or_create_dt'];	// 更新日時
				
				// 受注商品を取得
				$this->db->getOrderDetailList($row['or_id'], $this->_langId, array($this, '_defaultOrderItemLoop'));
			}
		}
		
		// 選択中の受注IDを設定
		if (empty($this->serialNo)){		// シリアル番号が空のときは新規とする
			$this->tmpl->addVar("_widget", "order_no", '新規');
			
			$this->tmpl->addVar("_widget", "new_selected", 'checked');// 新規追加をチェック状態にする
		} else {
			$this->tmpl->addVar("_widget", "order_no", $orderNo);
		}

		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// 顧客名
		$this->tmpl->addVar("_widget", "name_kana", $this->convertToDispString($nameKana));		// 顧客名(カナ)
		$this->tmpl->addVar("_widget", "custm_email", $this->convertToDispString($custm_email));		// Eメールアドレス
		$this->tmpl->addVar("_widget", "custm_zipcode", $this->convertToDispString($custm_zipcode));		// 顧客郵便番号
		$this->tmpl->addVar("_widget", "custm_address", $this->convertToDispString($custm_address));		// 顧客住所
		$this->tmpl->addVar("_widget", "custm_address2", $this->convertToDispString($custm_address2));		// 顧客住所2
		$this->tmpl->addVar("_widget", "custm_phone", $this->convertToDispString($custm_phone));		// 顧客電話番号
		$this->tmpl->addVar("_widget", "custm_fax", $this->convertToDispString($custm_fax));		// 顧客FAX
		$this->tmpl->addVar("_widget", "custm_email", $this->convertToDispString($custm_email));		// 顧客Eメール
		$this->tmpl->addVar("_widget", "regist_dt", $this->convertToDispDateTime($row['or_regist_dt']));		// 受注日時
		$this->tmpl->addVar("_widget", "pay_dt", $this->convertToDispDateTime($row['or_pay_dt']));		// 入金日時
		$this->tmpl->addVar("_widget", "deliv_note", $delivNote);		// 配送希望日
		
		$this->tmpl->addVar("_widget", "deliv_name", $this->convertToDispString($deliv_name));		// 配送先名		
		$this->tmpl->addVar("_widget", "deliv_name_kana", $this->convertToDispString($deliv_name_kana));		// 配送先名(カナ)	
		$this->tmpl->addVar("_widget", "deliv_zipcode", $this->convertToDispString($deliv_zipcode));		// 配送先郵便番号
		$this->tmpl->addVar("_widget", "deliv_address", $this->convertToDispString($deliv_address));		// 配送先住所
		$this->tmpl->addVar("_widget", "deliv_address2", $this->convertToDispString($deliv_address2));		// 配送先住所2
		$this->tmpl->addVar("_widget", "deliv_phone", $this->convertToDispString($deliv_phone));		// 配送先電話番号
		$this->tmpl->addVar("_widget", "deliv_fax", $this->convertToDispString($deliv_fax));		// 配送先FAX
		$this->tmpl->addVar("_widget", "deliv_email", $this->convertToDispString($deliv_email));		// 配送先Eメール
		$this->tmpl->addVar("_widget", "bill_name", $this->convertToDispString($bill_name));		// 請求先名		
		$this->tmpl->addVar("_widget", "bill_name_kana", $this->convertToDispString($bill_name_kana));		// 請求先名(カナ)
		$this->tmpl->addVar("_widget", "bill_zipcode", $this->convertToDispString($bill_zipcode));		// 請求先郵便番号
		$this->tmpl->addVar("_widget", "bill_address", $this->convertToDispString($bill_address));		// 請求先住所
		$this->tmpl->addVar("_widget", "bill_address2", $this->convertToDispString($bill_address2));		// 請求先住所2
		$this->tmpl->addVar("_widget", "bill_phone", $this->convertToDispString($bill_phone));		// 請求先電話番号
		$this->tmpl->addVar("_widget", "bill_fax", $this->convertToDispString($bill_fax));		// 請求先FAX
		$this->tmpl->addVar("_widget", "bill_email", $this->convertToDispString($bill_email));		// 請求先Eメール
		
		$subtotalStr = $this->ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $subtotal);
		$this->tmpl->addVar("_widget", "subtotal", $subtotalStr);		// 商品総額
		if ($discount > 0){
			$this->tmpl->addVar("_widget", "discount", '-' . $this->ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $discount));		// 値引き額
			$this->tmpl->addVar("_widget", "discount_desc", $this->convertToDispString($discountDesc));
		} else {
			$this->tmpl->addVar("_widget", "discount", "0");		// 値引き額
		}
		$delivFeeStr = $this->ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $delivFee);
		$this->tmpl->addVar("_widget", "delivery_fee", $delivFeeStr);		// 送料
		$chargeStr = $this->ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $charge);
		$this->tmpl->addVar("_widget", "charge", $chargeStr);		// 手数料
		$totalStr = $this->ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $total);
		$this->tmpl->addVar("_widget", "total", $totalStr);		// 総額
		
		if (!empty($updateUser)) $this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($updateUser));	// 更新者
		if (!empty($updateDt)) $this->tmpl->addVar("_widget", "update_dt", $this->convertToDispDateTime($updateDt));	// 更新日時

		// 受注ステータスメニューを作成
		$this->db->getAllOrderStatus($this->_langId, array($this, 'orderStatusLoop'));
		
		// 顧客都道府県を設定
		$this->db->getAllState('JPN', $this->_langId, array($this, 'customStateLoop'));
		
		// 配送先都道府県を設定
		$this->db->getAllState('JPN', $this->_langId, array($this, 'delivStateLoop'));
		
		// 請求先都道府県を設定
		$this->db->getAllState('JPN', $this->_langId, array($this, 'billStateLoop'));
		
		// 配送方法メニューを作成
		$this->db->getAllDelivMethod($this->_langId, 0/*デフォルトのセットID*/, array($this, 'delivMethodLoop'));
				
		// 支払い方法メニューを作成
		$this->db->getAllPaymentMethod($this->_langId, array($this, 'paymentMethodLoop'));
			
		// ボタンの設定
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			// データ更新、削除ボタン表示
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
		}
		// 埋め込みパラメータの設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "admin_url", $this->getUrl($this->gEnv->getDefaultAdminUrl()));
		$this->tmpl->addVar('_widget', 'main_widget_id', self::TARGET_WIDGET);
//		$this->tmpl->addVar("_widget", "productid_key", M3_REQUEST_PARAM_PRODUCT_ID);		// 商品IDキー
		//$this->tmpl->addVar('_widget', 'product_widget_id', self::TARGET_PRODUCT_WIDGET);		// 商品表示用
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function orderListLoop($index, $fetchedRow, $param)
	{
		// 項目選択のラジオボタンの状態
		$selected = '';
		if ($fetchedRow['or_serial'] == $this->serialNo){
			$selected = 'checked';
		}

		// 購入金額
		$this->ecObj->setCurrencyType($this->currency, $this->_langId);		// 通貨設定
		$this->ecObj->getPriceWithoutTax($fetchedRow['or_total'], $dispPrice);		// 表示文字列作成
		
		// 受注ステータス
		$orderStatus = $fetchedRow['or_order_status'];
		$ret = $this->db->getOrderStatusName($orderStatus, $this->_langId, $row);
		if ($ret){
			if ($orderStatus < 400){
				$status = '<font color="red">' . $row['os_name'] . '</font>';		
			} else {// 配送済み、終了、キャンセル
				$status = '<font color="green">' . $row['os_name'] . '</font>';
			}
		}
			
		// 配送日時
		$delibDt = '';
		if ($fetchedRow['or_deliv_dt'] != $this->gEnv->getInitValueOfTimestamp()) $delibDt = $this->convertToDispDateTime($fetchedRow['or_deliv_dt'], 0, 10/*時分表示*/);
		
		// 会員ID
		$memberId = intval($fetchedRow['or_custm_id']);
		if ($memberId < 0) $memberId = $memberId * (-1);
		
		// 会員情報へのリンク
		$nameLink = $this->convertToDispString($fetchedRow['or_custm_name']);
		if (!empty($memberId)){
			$nameLink = '<a href="#" onclick="selMember(' . $memberId . ');">' . $nameLink . '</a>';
		}
		$row = array(
			'index' => $index,													// 行番号
			'serial' => $this->convertToDispString($fetchedRow['or_serial']),	// シリアル番号
			'id' => $memberId,			// 会員ID
			'order_no' => $this->convertToDispString($fetchedRow['or_order_no']),		// 受注番号
			'name' => $nameLink,		// 顧客名
			'total' => $dispPrice,		// 購入額
			'status' => $status,		// 受注ステータス
			'lang' => $lang,													// 対応言語
			'regist_dt' => $this->convertToDispDateTime($fetchedRow['or_regist_dt'], 0, 10/*時分表示*/),	// 受付日時
			'deliv_dt' => $delibDt,	// 配送日時
			'update_dt' => $this->convertToDispDateTime($fetchedRow['or_create_dt'], 0, 10/*時分表示*/),	// 更新日時
			'selected' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中の項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['or_serial'];
		return true;
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function _defaultOrderItemLoop($index, $fetchedRow, $param)
	{
		static $itemIndex = 0;
		
		$priceAvailable = true;	// 価格が有効であるかどうか
		$productClass = $fetchedRow['od_product_class'];		// 商品クラス
		$productType = $fetchedRow['od_product_type_id'];		// 商品タイプ
		$productId = $fetchedRow['od_product_id'];				// 商品ID
		$prePrice = $this->convertToDispString($fetchedRow['cu_symbol']);		// 価格表示用
		$postPrice = $this->convertToDispString($fetchedRow['cu_post_symbol']);	// 価格表示用

		switch ($productClass){
			case ec_mainCommonDef::PRODUCT_CLASS_PHOTO:		// フォトギャラリー画像のとき
				$photoId = $fetchedRow['ht_public_id'];		// 公開画像ID
				$title = $fetchedRow['ht_name'];		// サムネール画像タイトル
				$checkValue = $photoId;					// 項目チェック値
				$productTypeName = $fetchedRow['py_name'];		// 商品タイプ名
				$productTypeCode = $fetchedRow['py_code'];		// 商品タイプコード

				// 表示用の商品名、商品コード作成
				$productName = $fetchedRow['od_product_name'];		// 商品名
				$productCode = $fetchedRow['od_product_code'];		// 商品コード
				
				// 商品の状態
				if (!$fetchedRow['ht_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 画像価格情報を取得
				$ret = self::$_mainDb->getPhotoInfoWithPrice($productId, $productClass, $productType, ec_mainCommonDef::STANDARD_PRICE, $this->_langId, $row);
				
				// 画像詳細へのリンク
				$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $photoId;
		
				// 画像URL
				$imageUrl = $this->gEnv->getResourceUrl() . ec_mainCommonDef::THUMBNAIL_DIR . '/' . $photoId . '_' . ec_mainCommonDef::DEFAULT_THUMBNAIL_SIZE . '.' . ec_mainCommonDef::DEFAULT_IMAGE_EXT;
				break;
			case ec_mainCommonDef::PRODUCT_CLASS_DEFAULT:	// 一般商品のとき
				$title = $fetchedRow['pt_name'];		// サムネール画像タイトル
				$checkValue = $productId;					// 項目チェック値
				
				// 表示用の商品名、商品コード作成
				$productName = $fetchedRow['od_product_name'];		// 商品名
				$productCode = $fetchedRow['od_product_code'];		// 商品コード
				
				// 商品の状態
				if (!$fetchedRow['pt_visible']) $priceAvailable = false;		// 商品が表示不可のときは価格を無効とする
				
				// 商品価格情報を取得
				$ret = self::$_mainDb->getProductByProductId($productId, $this->_langId, $row, $imageRows);
				
				// 商品詳細へのリンク
				$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $productId;

				// 画像URL
				$imageArray = $this->_getImage($imageRows, ec_mainCommonDef::PRODUCT_IMAGE_SMALL);// 商品画像小
				$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageArray['im_url']);
				
				// 在庫キャンセル処理
				if ($this->cancelStock){
					$newStockCount = intval($row['pe_stock_count']) + $fetchedRow['od_quantity'];
					$updateParam = array('pe_stock_count' => $newStockCount);
					$this->db->updateProductRecord($productId, $this->_langId, $updateParam);
				}
				break;
		}
		
		if ($ret){
			// 価格を取得
			$price = $row['pp_price'];	// 価格
			$currency = $row['pp_currency_id'];	// 通貨
			$taxType = ec_mainCommonDef::TAX_TYPE;					// 税種別

			// 価格作成
			$this->ecObj->setCurrencyType($currency, $this->_langId);		// 通貨設定
			$this->ecObj->setTaxType($taxType, $this->_langId);		// 税種別設定
			$unitPrice = $this->ecObj->getPriceWithTax($price, $dispUnitPrice);	// 税込み価格取得
			$dispUnitPriceNoSign = $dispUnitPrice;
			$dispUnitPrice = $prePrice . $dispUnitPrice . $postPrice;
		} else {
			$priceAvailable = false;
		}
		
		// ##### カートの内容のチェック #####
		// 価格が変更のときは、価格を無効にする
		$quantity = $fetchedRow['od_quantity'];
		$subtotal = $fetchedRow['od_total'];

		$oldCurrency = $fetchedRow['cu_id'];
		if ($unitPrice * $quantity != $subtotal) $priceAvailable = false;
		if ($oldCurrency != $currency) $priceAvailable = false;

		// 価格の有効判断
		if (!$fetchedRow['si_available']) $priceAvailable = false;
		
		// 小計価格作成
		$this->ecObj->setCurrencyType($oldCurrency, $this->_langId);		// 通貨設定
		$this->ecObj->getPriceWithoutTax($subtotal, $dispPrice);				// 税込み価格取得

		// 小計価格表示文字列
		$priceStatus = '';
		if (!$priceAvailable) $priceStatus = '<span style="color:#ff0000;">(無効)</span>';
		$dispPriceNoSign = $dispPrice;
		$dispPrice = $prePrice . $dispPrice . $postPrice;
		
		// 商品詳細へのリンク
		$urlLink = $this->convertUrlToHtmlEntity($this->getUrl($url, true));
		$nameLink = '<a href="' . $urlLink . '">' . $this->convertToDispString($productName) . '</a>';
		
		// サムネール
		$photoImage = '<a href="' . $urlLink . '"><img src="' . $this->getUrl($imageUrl) . '" width="' . ec_mainCommonDef::CART_ICON_SIZE . '" height="' . ec_mainCommonDef::CART_ICON_SIZE . 
								'" title="' . $this->convertToDispString($title) . '" alt="' . $this->convertToDispString($title) . '" style="border:none;" /></a>';
		
		if (!$this->cancelStock && empty($this->updateContentAccess)){		// コンテンツアクセス権の設定でないとき
			$row = array(
				'no' => $itemIndex + 1,
				'product_class' => $productClass,		// 商品クラス
				'name' => $nameLink,
				'name_nolink' => $this->convertToDispString($productName),
				'code' => $this->convertToDispString($productCode),		// 商品コード
				'check_value' => $checkValue,		// チェック値
				'image' => $photoImage,			// サムネール
				'unit_price' => $dispUnitPrice,			// 税込み単価
				'unit_price_nosign' => $dispUnitPriceNoSign,			// 税込み単価(記号なし)
				'price' => $dispPrice,					// 小計
				'price_nosign' => $dispPriceNoSign,					// 小計(記号なし)
				'price_status' => $priceStatus,			// 小計の状態
				'quantity' => $quantity
			);
			$this->tmpl->addVars('productlist', $row);
			$this->tmpl->parseTemplate('productlist', 'a');
		}
		
		// 指定のコンテンツIDを取得
		if ($productClass == $this->productClass && $productType == $this->productType){
			$this->contentIdArray[] = $productId;		// コンテンツID
		}
		$itemIndex++;
		return true;
	}
	/**
	 * 取得した配送先都道府県をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function customStateLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['gz_id'] == $this->custmState){		// 選択中の都道府県
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['gz_id']),			// ID
			'name'     => $this->convertToDispString($fetchedRow['gz_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('custm_state_list', $row);
		$this->tmpl->parseTemplate('custm_state_list', 'a');
		return true;
	}
	/**
	 * 取得した配送先都道府県をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function delivStateLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['gz_id'] == $this->delivState){		// 選択中の都道府県
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['gz_id']),			// ID
			'name'     => $this->convertToDispString($fetchedRow['gz_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('deliv_state_list', $row);
		$this->tmpl->parseTemplate('deliv_state_list', 'a');
		return true;
	}
	/**
	 * 取得した請求先都道府県をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function billStateLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['gz_id'] == $this->billState){		// 選択中の都道府県
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['gz_id']),			// ID
			'name'     => $this->convertToDispString($fetchedRow['gz_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('bill_state_list', $row);
		$this->tmpl->parseTemplate('bill_state_list', 'a');
		return true;
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
		//$checked = '';
		$selected = '';
		if ($fetchedRow['do_id'] == $this->deliveryMethod){		// 選択中の配送方法
			//$checked = 'checked';
			$selected = 'selected';
		}

		// 配送料金を求める
		$iWidgetId	= $fetchedRow['do_iwidget_id'];	// インナーウィジェットID
		if (!empty($iWidgetId)){
			// パラメータをインナーウィジェットに設定し、計算結果を取得
			if ($this->calcIWidgetParam($iWidgetId, $fetchedRow['do_id'], $fetchedRow['do_param'], $optionParam, $resultObj)){
				$price = $resultObj->price;		// 配送料金
			}
		}
					
		$row = array(
			'value'		=> $this->convertToDispString($fetchedRow['do_id']),			// ID
			'name'		=> $this->convertToDispString($fetchedRow['do_name']),		// 表示名
			'desc'		=> $fetchedRow['do_description'],							// 説明
			'price'		=> $price,							// 配送料金
			'def_content'		=> $content,		// ユーザ選択用コンテンツ
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('deliv_method_list', $row);
		$this->tmpl->parseTemplate('deliv_method_list', 'a');
		return true;
	}
	/**
	 * 取得した支払い方法をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function paymentMethodLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['po_id'] == $this->payMethod){		// 選択中の支払い方法
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['po_id']),			// ID
			'name'     => $this->convertToDispString($fetchedRow['po_name']),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('payment_method_list', $row);
		$this->tmpl->parseTemplate('payment_method_list', 'a');
		return true;
	}
	/**
	 * 受注ステータスをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function orderStatusLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['os_id'] == $this->orderStatus){		// 受注ステータス
			$selected = 'selected';
		}
		$optionStr = '';
		if ($fetchedRow['os_id'] == ec_mainCommonDef::ORDER_STATUS_CANCEL ||
			$fetchedRow['os_id'] == ec_mainCommonDef::ORDER_STATUS_PAYMENT_COMPLETED){
			$optionStr = '*';
		}
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['os_id']),			// ID
			'name'     => $this->convertToDispString($fetchedRow['os_name'] . $optionStr),			// 表示名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('order_status_list', $row);
		$this->tmpl->parseTemplate('order_status_list', 'a');
		return true;
	}
	/**
	 * 画像取得
	 *
	 * @param array  	$srcRows			画像リスト
	 * @param string	$imageType			画像タイプ
	 * @return array						取得した行
	 */
	function _getImage($srcRows, $sizeType)
	{
		for ($i = 0; $i < count($srcRows); $i++){
			if ($srcRows[$i]['im_size_id'] == $sizeType){
				return $srcRows[$i];
			}
		}
		return array();
	}
	/**
	 * ダウンロードコンテンツのアクセス権を設定
	 *
	 * @param int  	$userId				ユーザID
	 * @param int	$orderId			注文ID
	 * @return bool						true=成功、false=失敗
	 */
	function setDownloadContentAccess($userId, $orderId)
	{
		// ダウンロードコンテンツのIDを取得
		$this->updateContentAccess = true;		// コンテンツアクセス権の設定かどうか
		$this->productClass	= ec_mainCommonDef::PRODUCT_CLASS_PHOTO;		// 商品クラス(フォトギャラリー画像)
		$this->productType	= ec_mainCommonDef::PRODUCT_TYPE_DOWNLOAD;		// 商品タイプ(ダウンロード画像)
		$this->contentIdArray = array();		// コンテンツID
		$this->db->getOrderDetailList($orderId, $this->gEnv->getCurrentLanguage(), array($this, '_defaultOrderItemLoop'));
		$this->updateContentAccess = false;		// コンテンツアクセス権の設定かどうか
		
		// コンテンツのアクセス権を更新
		$ret = self::$_mainDb->updateContentAccess($userId, M3_VIEW_TYPE_PHOTO, $this->contentIdArray);
		return $ret;
	}
	/**
	 * PDFデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @return								なし
	 */
	function createSheet($tmpl)
	{
		// 受注内容が取得できない場合は終了
		if (empty($this->orderRow)) return;
		
		$this->currency	= $this->orderRow['or_currency_id'];	// 通貨
		$subtotal = $this->orderRow['or_subtotal'];		// 商品総額
		$discount = $this->orderRow['or_discount'];		// 値引き額
		$delivFee = $this->orderRow['or_deliv_fee'];		// 配送料
		$charge = $this->orderRow['or_charge'];		// 手数料
		$total = $this->orderRow['or_total'];		// 総額
		$discountDesc = $this->orderRow['or_discount_desc'];		// 値引き説明
		
		$tmpl->addVar('_tmpl', 'sheet_no', $this->orderRow['or_order_no']);		// 受注番号
		$tmpl->addVar('_tmpl', 'date', date('Y年 m月 d日'));				// 納品日
		
		// 納品先
		$deliv_name = $this->orderRow['or_deliv_name'];		// 配送先名
		$deliv_name_kana = $this->orderRow['or_deliv_name_kana'];		// 配送先名(カナ)
		$deliv_zipcode = $this->orderRow['or_deliv_zipcode'];		// 配送先郵便番号
		$deliv_address = $this->orderRow['or_deliv_address1'];		// 配送先住所
		$deliv_address2 = $this->orderRow['or_deliv_address2'];		// 配送先住所2
		$deliv_phone = $this->orderRow['or_deliv_phone'];		// 配送先電話番号
		
		$delivAddress = $this->convertToDispString($deliv_name). ' 様<br />';
		if (!empty($deliv_zipcode)) $delivAddress .= $this->convertToDispString($deliv_zipcode) . '<br />';
		if (!empty($deliv_address)) $delivAddress .= $this->convertToDispString($deliv_address) . '<br />';
		if (!empty($deliv_address2)) $delivAddress .= $this->convertToDispString($deliv_address2) . '<br />';
		if (!empty($deliv_phone)) $delivAddress .= 'tel. ' . $this->convertToDispString($deliv_phone) . '<br />';
		$tmpl->addVar('_tmpl', 'deliv_address', $delivAddress);
		
		// 送信元
		$shopName		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_NAME);		// ショップ名
		$shopOwner		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_OWNER);		// ショップオーナー名
		$shopZipcode	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_ZIPCODE);		// ショップ郵便番号
		$shopAddress	= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_ADDRESS);		// ショップ住所
		$shopPhone		= self::$_mainDb->getConfig(ec_mainCommonDef::CF_E_SHOP_PHONE);		// ショップ電話番号

		$fromAddress = '';
		if (!empty($shopName)) $fromAddress .= $this->convertToDispString($shopName) . '<br />';
		if (!empty($shopOwner)) $fromAddress .= $this->convertToDispString($shopOwner) . '<br />';
		if (!empty($shopZipcode)) $fromAddress .= $this->convertToDispString($shopZipcode) . '<br />';
		if (!empty($shopAddress)) $fromAddress .= $this->convertToPreviewText($this->convertToDispString($shopAddress)) . '<br />';
		if (!empty($shopPhone)) $fromAddress .= 'tel. ' . $this->convertToDispString($shopPhone) . '<br />';
		$tmpl->addVar('_tmpl', 'from_address', $fromAddress);
		
		if ($discount > 0){
			$tmpl->setAttribute('show_discount', 'visibility', 'visible');
			$tmpl->addVar("show_discount", "discount", '-' . $this->ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $discount));		// 値引き額
			$tmpl->addVar("show_discount", "discount_desc", $this->convertToDispString($discountDesc));
		}
		if ($delivFee > 0){
			$delivFeeStr = $this->ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $delivFee);
			$tmpl->setAttribute('show_delivery_fee', 'visibility', 'visible');
			$tmpl->addVar("show_delivery_fee", "delivery_fee", $delivFeeStr);		// 送料
		}
		if ($charge > 0){
			$chargeStr = $this->ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $charge);
			$tmpl->setAttribute('show_charge', 'visibility', 'visible');
			$tmpl->addVar("show_charge", "charge", $chargeStr);		// 手数料
		}
		$totalStr = $this->ecObj->convertByCurrencyFormat($this->currency, $this->_langId, $total);
		$tmpl->addVar("_tmpl", "total", $totalStr);		// 総額
		
		// 受注商品を取得
		$this->tmpl = $tmpl;		// 出力テンプレート変更
		$this->db->getOrderDetailList($this->orderRow['or_id'], $this->_langId, array($this, '_defaultOrderItemLoop'));
	}
}
?>
