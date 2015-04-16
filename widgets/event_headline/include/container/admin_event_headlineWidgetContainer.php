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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/event_headlineCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/event_headlineDb.php');

class admin_event_headlineWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sortOrder;		// ソート順
	private $sortOrderArray;		// ソート順
	private $imageType;		// 選択中の画像タイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new event_headlineDb();
		
		$this->sortOrderArray = array(	array(	'name' => '昇順',	'value' => '0'),
										array(	'name' => '降順',	'value' => '1'));		// ソート順
	}
	/**
	 * ウィジェット初期化
	 *
	 * 共通パラメータの初期化や、以下のパターンでウィジェット出力方法の変更を行う。
	 * ・組み込みの_setTemplate(),_assign()を使用
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return 								なし
	 */
	function _init($request)
	{
		$task = $request->trimValueOf('task');
		if ($task == 'list'){		// 一覧画面
			// 通常のテンプレート処理を組み込みのテンプレート処理に変更。_setTemplate()、_assign()はキャンセル。
			$this->replaceAssignTemplate(self::ASSIGN_TEMPLATE_BASIC_CONFIG_LIST);		// 設定一覧(基本)
		}
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
		return 'admin.tmpl.html';
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
		return $this->createDetail($request);
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得
		$name				= $request->trimValueOf('item_name');			// 定義名
		$itemCount			= $request->trimValueOf('item_count');			// 表示項目数
		$useBaseDay 		= $request->trimCheckedValueOf('item_use_base_day');		// 基準日を使用するかどうか
		$dayCount			= $request->trimIntValueOf('item_day_count', '0');			// 基準日からの日数
		$showImage			= $request->trimCheckedValueOf('item_show_image');		// 画像を表示するかどうか
		$this->imageType	= $request->trimValueOf('item_image_type');				// 画像タイプ
		$imageWidth			= $request->trimIntValueOf('item_image_width', '0');			// 画像幅(空文字列をOKとする)
		$imageHeight		= $request->trimIntValueOf('item_image_height', '0');			// 画像高さ(空文字列をOKとする)
		$useRss				= $request->trimCheckedValueOf('item_use_rss');		// RSS配信を行うかどうか
		$this->sortOrder	= $request->trimValueOf('item_sort_order');		// ソート順
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($itemCount, '表示項目数');
			$this->checkSingleByte($dayCount, '日数');		// 基準日からの日数
			$this->checkNumeric($itemCount, '項目数');
			$this->checkNumeric($imageWidth, '画像幅');
			$this->checkNumeric($imageHeight, '画像高さ');
			
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					$this->setUserErrorMsg('名前が重複しています');
					break;
				}
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->name			= $name;// 表示名
				$newObj->itemCount		= $itemCount;
				$newObj->sortOrder		= $this->sortOrder;		// ソート順
				$newObj->useBaseDay		= $useBaseDay;		// 基準日を使用するかどうか
				$newObj->dayCount		= $dayCount;			// 基準日からの日数
				$newObj->showImage		= $showImage;		// 画像を表示するかどうか
				$newObj->imageType		= $this->imageType;				// 画像タイプ
				$newObj->imageWidth		= intval($imageWidth);			// 画像幅
				$newObj->imageHeight	= intval($imageHeight);			// 画像高さ
				$newObj->useRss			= $useRss;
				
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$this->configId = $defConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($itemCount, '表示項目数');
			$this->checkSingleByte($dayCount, '日数');		// 基準日からの日数
			$this->checkNumeric($itemCount, '項目数');
			$this->checkNumeric($imageWidth, '画像幅');
			$this->checkNumeric($imageHeight, '画像高さ');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->itemCount		= $itemCount;
					$targetObj->sortOrder		= $this->sortOrder;		// ソート順
					$targetObj->useBaseDay		= $useBaseDay;		// 基準日を使用するかどうか
					$targetObj->dayCount		= $dayCount;			// 基準日からの日数
					$targetObj->showImage		= $showImage;		// 画像を表示するかどうか
					$targetObj->imageType		= $this->imageType;				// 画像タイプ
					$targetObj->imageWidth		= intval($imageWidth);			// 画像幅
					$targetObj->imageHeight		= intval($imageHeight);			// 画像高さ
					$targetObj->useRss			= $useRss;
				}
				
				// 設定値を更新
				if ($ret) $ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		} else {	// 初期起動時、または上記以外の場合
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		}
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				$name				= $this->createConfigDefaultName();			// デフォルトの設定名を作成
				$itemCount			= event_headlineCommonDef::DEFAULT_ITEM_COUNT;	// 表示項目数
				$this->sortOrder	= '0';		// ソート順
				$useBaseDay			= '0';		// 基準日を使用するかどうか
				$dayCount			= 0;			// 基準日からの日数
				$showImage			= 0;		// 画像を表示するかどうか
				$this->imageType	= event_headlineCommonDef::DEFAULT_IMAGE_TYPE;				// 画像タイプ
				$imageWidth			= 0;			// 画像幅
				$imageHeight		= 0;			// 画像高さ
				$useRss				= 1;							// RSS配信を行うかどうか
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name				= $targetObj->name;	// 名前
					$itemCount			= $targetObj->itemCount;
					$this->sortOrder	= $targetObj->sortOrder;		// ソート順
					$useBaseDay			= $targetObj->useBaseDay;		// 基準日を使用するかどうか
					$dayCount			= $targetObj->dayCount;			// 基準日からの日数
					$showImage			= $targetObj->showImage;		// 画像を表示するかどうか
					$this->imageType	= $targetObj->imageType;				// 画像タイプ
					$imageWidth			= intval($targetObj->imageWidth);			// 画像幅
					$imageHeight		= intval($targetObj->imageHeight);			// 画像高さ
					$useRss				= $targetObj->useRss;// RSS配信を行うかどうか
					if (!isset($useRss)) $useRss = 1;
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}

		// 設定項目選択メニュー作成
		$this->createConfigNameMenu($this->configId);
		
		// ソート順メニュー
		$this->createSortOrderMenu();
		
		// 画像タイプ選択メニュー作成
		$this->createpImageTypeList();
		
		// 画面にデータを埋め込む
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("item_name_visible", "name",	$name);
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
		$this->tmpl->addVar("_widget", "item_count",			$this->convertToDispString($itemCount));
		$this->tmpl->addVar("_widget", "use_base_day",			$this->convertToCheckedString($useBaseDay));		// 基準日を使用するかどうか
		$this->tmpl->addVar("_widget", "day_count",			$this->convertToDispString($dayCount));		// 基準日からの日数
		$this->tmpl->addVar("_widget", "show_image_checked",	$this->convertToCheckedString($showImage));// 画像を表示するかどうか
		$imageWidth = empty($imageWidth) ? '' : $imageWidth;		// 0の場合は空文字列を設定
		$imageHeight = empty($imageHeight) ? '' : $imageHeight;		// 0の場合は空文字列を設定
		$this->tmpl->addVar("_widget", "image_width",			$this->convertToDispString($imageWidth));// 画像幅
		$this->tmpl->addVar("_widget", "image_height",			$this->convertToDispString($imageHeight));// 画像高さ
		$this->tmpl->addVar("_widget", "use_rss",				$this->convertToCheckedString($useRss));			// RSS配信を行うかどうか
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
		}
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * ソート順選択メニュー作成
	 *
	 * @return なし
	 */
	function createSortOrderMenu()
	{
		for ($i = 0; $i < count($this->sortOrderArray); $i++){
			$value = $this->sortOrderArray[$i]['value'];
			$name = $this->sortOrderArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $this->convertToSelectedString($value, $this->sortOrder)			// 選択中かどうか
			);
			$this->tmpl->addVars('sort_order', $row);
			$this->tmpl->parseTemplate('sort_order', 'a');
		}
	}
	/**
	 * 画像タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createpImageTypeList()
	{
		$formats = $this->gInstance->getImageManager()->getSystemThumbFormat(1/*クロップ画像のみ*/);
		
		for ($i = 0; $i < count($formats); $i++){
			$id = $formats[$i];
			$name = $id;
			
			$selected = '';
			if ($id == $this->imageType) $selected = 'selected';

			$row = array(
				'value'			=> $this->convertToDispString($id),				// 値
				'name'			=> $this->convertToDispString($name),			// 名前
				'selected'		=> $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('image_type_list', $row);
			$this->tmpl->parseTemplate('image_type_list', 'a');
		}
	}
}
?>
