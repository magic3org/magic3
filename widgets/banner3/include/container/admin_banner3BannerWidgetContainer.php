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
require_once($gEnvManager->getWidgetContainerPath('banner3') . '/admin_banner3BaseWidgetContainer.php');

class admin_banner3BannerWidgetContainer extends admin_banner3BaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $css;			// メニュー用CSS
	private $cssId;			// CSS用ID
	private $dispType;		// 画像リンク表示方法
	private $dispTypeDef;		// 画像リンク表示方法定義
	private $bannerNameArray;	// バナー定義名保存用
	private $act;				// 実行act
	private $selectedItems;		// 画像選択用
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const IMAGE_ICON_FILE = '/images/system/image16.png';			// イメージアイコン
	const FLASH_ICON_FILE = '/images/system/flash16.png';		// Flashアイコン
	const ICON_SIZE = 16;		// アイコンのサイズ
	const CHANGE_IMAGE_TAG_ID = 'changeimage';			// 画像変更ボタンタグID
	const MAX_URL_LENGTH = 30;		// 一覧のURLの最大長
	const IMAGE_LIST_COUNT = 10;		// 表示画像数
	const LINK_PAGE_COUNT		= 20;			// リンクページ数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 表示順
		$this->dispTypeDef = array(	array(	'name' => '順次',		'value' => '0'),
									array(	'name' => 'ランダム',	'value' => '1'));
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
		if ($task == 'banner_list'){		// 一覧画面
			return 'admin_list.tmpl.html';
		} else {			// 一覧画面
			return 'admin.tmpl.html';
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
		if ($task == 'banner_list'){		// 一覧画面
			return $this->createList($request);
		} else {			// 詳細設定画面
			return $this->createDetail($request);
		}
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

		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		$act = $request->trimValueOf('act');
		
		$name = $request->trimValueOf('item_name');
		$bannerItem = $request->trimValueOf('item_banner');
		$this->dispType = $request->trimValueOf('item_disptype');	// 表示方法
		$dispDirect = $request->trimValueOf('item_dispdirect');	// 表示方向
		$dispCount = $request->trimValueOf('item_dispcount');	// 表示項目数
		$html = $request->trimValueOf('item_html');	// 画像リンクテンプレート
		$this->css	= $request->trimValueOf('item_css');		// 表示用CSS
		$this->cssId	= $request->trimValueOf('item_css_id');		// CSS用ID
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($dispCount, '表示項目数');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = self::$_mainDb->updateBanner(0/*新規追加*/, $name, $bannerItem, $this->dispType, $dispDirect, $dispCount, $html, $this->css, $this->cssId, $newConfigId);
				
				// 画面定義更新
				if ($ret && !empty($defSerial)){
					$ret = $this->_db->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $newConfigId, $name);
				}
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					if (!empty($defSerial)) $defConfigId = $newConfigId;		// 定義定義IDを更新
					$this->configId = $newConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力チェック
			$this->checkNumeric($dispCount, '表示項目数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 名前を取得
				$ret = self::$_mainDb->getBanner($this->configId, $row);
				if ($ret) $name = $row['bd_name'];		// 名前
				
				$ret = self::$_mainDb->updateBanner($this->configId, $name, $bannerItem, $this->dispType, $dispDirect, $dispCount, $html, $this->css, $this->cssId, $newConfigId);
				
				if (empty($defConfigId) && !empty($defSerial)){		// 画面定義の定義IDが設定されていないときは設定
					// 画面定義更新
					if ($ret) $ret = $this->_db->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $this->configId, $name);
				}
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					if (empty($defConfigId) && !empty($defSerial)){		// 画面定義の定義IDが設定されていないときは設定
						$defConfigId = $this->configId;		// 定義定義IDを更新
					}
					
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		} else if ($act == 'update_preview'){	// プレビュー再表示
		} else if ($act == 'getimagelist'){		// 画像一覧取得
			$this->act = $act;				// 実行act

			$imageList = $this->getParsedTemplateData('default_imagelist.tmpl.html', array($this, 'makeImageList'), $request);// 画像一覧作成
			$this->gInstance->getAjaxManager()->addData('html', $imageList);
			return;
		} else {			// 初期起動時
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		}

		// 設定項目選択メニュー作成
		$this->bannerNameArray = array();
		self::$_mainDb->getBannerList(array($this, 'bannerListLoop'));
		
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// 新規取得時
				$name = $this->createDefaultName();			// デフォルト登録項目名
				$bannerItem = '';	// 画像リンク項目ID
				$this->dispType = 0;	// 表示方法
				$dispDirect = 0;	// 表示方向
				$dispCount = 1;	// 表示項目数
				$this->cssId = $this->createDefaultCssId();	// CSS用ID
				$this->css = $this->getParsedTemplateData('default.tmpl.css', array($this, 'makeCss'));// デフォルト用のCSSを取得
			}
		} else {
			if ($replaceNew){
				// 登録済みのバナー定義を取得
				$ret = self::$_mainDb->getBanner($this->configId, $row);
				if ($ret){
					// 取得値を設定
					$name = $row['bd_name'];		// 名前
					$bannerItem	= $this->convertToDispString($row['bd_item_id']);	// バナー項目ID
					$dispCount	= $this->convertToDispString($row['bd_disp_item_count']);	// 表示項目数
					$this->dispType	= $row['bd_disp_type'];				// 表示方法
					$dispDirect	= $row['bd_disp_direction'];		// 表示方向
					$html		= $row['bd_item_html'];	// 画像リンクテンプレート
					$this->cssId	= $row['bd_css_id'];		// CSS用ID
					$this->css	= $row['bd_css'];		// 表示用CSS
					$updateUser = $this->convertToDispString($row['lu_name']);// 更新者
					$updateDt = $this->convertToDispDateTime($row['bd_update_dt']);// 更新日時
				}
			}
			
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		$this->serialNo = $this->configId;
		
		// 画像リンク表示順選択メニュー作成
		$this->createDispTypeMenu();
		
		// 画像リンクプレビューを作成
		if (!empty($bannerItem)) self::$_mainDb->getImageListById(explode(',', $bannerItem), array($this, 'imageListLoop'));
		$this->setListTemplateVisibility('itemlist');	// 一覧部の表示制御
		//if (!$this->isExistsContent) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 項目がないときは、一覧を表示しない
		
		// プレビュー用のCSSを作成
		$this->headCss = str_replace(M3_TAG_START . M3_TAG_MACRO_WIDGET_URL . M3_TAG_END, $this->gEnv->getCurrentWidgetRootUrl(), $this->css);
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$selected = '';
		if ($dispDirect == 0) $selected = 'selected';
		$this->tmpl->addVar("_widget", "direct_v_selecter", $selected);	// 縦表示
		$selected = '';
		if ($dispDirect == 1) $selected = 'selected';
		$this->tmpl->addVar("_widget", "direct_h_selecter", $selected);	// 横表示
		$this->tmpl->addVar("_widget", "banner_item", $bannerItem);	// バナー項目ID
		$this->tmpl->addVar("_widget", "disp_count", $dispCount);	// 表示項目数
		$this->tmpl->addVar("_widget", "target_widget", $this->gEnv->getCurrentWidgetId());// 画像選択ダイアログ用
		//if (!empty($updateUser)) $this->tmpl->addVar("_widget", "update_user", $updateUser);	// 更新者
		//if (!empty($updateDt)) $this->tmpl->addVar("_widget", "update_dt", $updateDt);	// 更新日時
		
		// 画像変更ボタン
		$changeImageButton = $this->gDesign->createEditButton(''/*同画面*/, ''/*ツールチップなし*/, self::CHANGE_IMAGE_TAG_ID);
		$this->tmpl->addVar("_widget", "change_image_button", $changeImageButton);
		$this->tmpl->addVar("_widget", "tagid_change_image", self::CHANGE_IMAGE_TAG_ID);		// 画像変更タグ
		
		$this->tmpl->addVar("_widget", "css_id",	$this->cssId);	// CSS用ID
		$this->tmpl->addVar("_widget", "css",	$this->css);
		$this->tmpl->addVar("_widget", "preview",	$previewHtml);
		
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		//$this->tmpl->addVar("_widget", "widget_url", $this->gEnv->getCurrentWidgetRootUrl());	// ウィジェットのルートディレクトリ
		//$this->tmpl->addVar("_widget", "root_url", $this->gEnv->getRootUrl());
		
		// ボタンの表示制御
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
			
			// ヘルプの追加
			$this->convertHelp('update_button');
		}
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		return $this->headCss;
	}
	/**
	 * デフォルトの名前を取得
	 *
	 * @return string	デフォルト名						
	 */
	function createDefaultName()
	{
		$name = self::DEFAULT_NAME_HEAD;
		for ($j = 1; $j < 100; $j++){
			$name = self::DEFAULT_NAME_HEAD . $j;
			// 設定名の重複チェック
			if (!in_array($name, $this->bannerNameArray)) break;
		}
		return $name;
	}
	/**
	 * CSS用のデフォルトのIDを取得
	 *
	 * @return string	ID						
	 */
	function createDefaultCssId()
	{
		//return $this->gEnv->getCurrentWidgetId() . '_' . $this->getTempConfigId($this->paramObj);
		return $this->gEnv->getCurrentWidgetId() . '_' . self::$_mainDb->getNextBannerId();
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		
		if ($act == 'delete'){		// メニュー項目の削除
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
				$ret = self::$_mainDb->delBanner($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}

		// バナーリストを取得
		self::$_mainDb->getBannerList(array($this, 'itemListLoop'));
		$this->setListTemplateVisibility('itemlist');	// 一覧部の表示制御
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar('_widget', 'admin_url', $this->gEnv->getDefaultAdminUrl());// 管理者URL
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['bd_id'];

		// 使用数取得
		$defCount = 0;
		if (!empty($id)){
			$defCount = $this->_db->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
		}
		$operationDisagled = '';
		if ($defCount > 0) $operationDisagled = 'disabled';
			
		// 画像プレビュー用ボタンを作成
//		$buttonTag = $this->gDesign->createPreviewImageButton(''/*同画面*/, 'プレビュー', '');
		//createPreviewImageButton($url, $title = '', $tagId = '', $btnClass = '');
		
		$row = array(
			'index'					=> $index,													// インデックス番号
			'id'					=> $this->convertToDispString($id),														// ID
			'name'					=> $this->convertToDispString($fetchedRow['bd_name']),		// 名前
			'image_item'			=> $this->convertToDispString($fetchedRow['bd_item_id']),		// バナー項目ID
			'ope_disabled'			=> $operationDisagled,			// 選択可能かどうか
			'def_count'				=> $defCount							// 使用数
//			'preview_image_button'	=> $buttonTag					// 画像プレビューボタン
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// シリアル番号を保存
		$this->serialArray[] = $id;
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
	function bannerListLoop($index, $fetchedRow, $param)
	{
		$value = $fetchedRow['bd_id'];		// 定義ID
		$name = $fetchedRow['bd_name'];		// 名前
		
		$selected = '';
		if ($value == $this->configId) $selected = 'selected';
		
		$row = array(
			'value'    => $this->convertToDispString($value),			// 値
			'name'     => $this->convertToDispString($name),			// 名前
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('title_list', $row);
		$this->tmpl->parseTemplate('title_list', 'a');
		
		$this->bannerNameArray[] = $fetchedRow['bd_name'];
		return true;
	}
	/**
	 * CSSデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeCss($tmpl)
	{
		// メニュータイプが縦型のときは縦型用のCSSを追加
		/*if (!empty($this->menuType)){
			$tmpl->setAttribute('add_vertical', 'visibility', 'visible');
			$tmpl->addVar('add_vertical', 'id', '#' . $this->cssId);
		}*/
		$tmpl->addVar('_tmpl', 'id', '#' . $this->cssId);
	}
	/**
	 * 表示順選択メニュー作成
	 *
	 * @return なし
	 */
	function createDispTypeMenu()
	{
		for ($i = 0; $i < count($this->dispTypeDef); $i++){
			$value = $this->dispTypeDef[$i]['value'];
			$name = $this->dispTypeDef[$i]['name'];
			
			$selected = '';
			if ($value == $this->dispType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('disp_type_list', $row);
			$this->tmpl->parseTemplate('disp_type_list', 'a');
		}
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
/*	function imageListLoop($index, $fetchedRow, $param)
	{
		$serial = $this->convertToDispString($fetchedRow['bi_serial']);
		$visible = '';
		if ($fetchedRow['bi_visible']){	// 項目の表示
			$visible = 'checked';
		}
		$itemType = $fetchedRow['bi_type'];		// 画像タイプ
		
		// ファイル名取得
		$partArray = explode('/', $fetchedRow['bi_image_url']);
		if (count($partArray) > 0) $filename = $partArray[count($partArray)-1];
		
		// 閲覧数取得
		$viewCount = self::$_mainDb->getTotalViewCount($serial);
		
		// 項目タイプの設定
		$iconUrl = '';
		switch ($itemType){
			case 0:		// 画像ファイル
				$iconTitle = '画像ファイル';
				$iconUrl = $this->gEnv->getRootUrl() . self::IMAGE_ICON_FILE;// イメージアイコン
				break;
			case 1:		// Flashファイル
				$iconTitle = 'Flashファイル';
				$iconUrl = $this->gEnv->getRootUrl() . self::FLASH_ICON_FILE;// Flashアイコン
				break;
			default:
				break;
		}
		$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
	
		// 画像URL
		$url = $fetchedRow['bi_image_url'];
		if (!empty($url)) $url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);
	
		// バナー表示イメージの作成
		$imageUrl = $fetchedRow['bi_image_url'];
		if (!empty($imageUrl)) $imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
		$imageWidth = $fetchedRow['bi_image_width'];
		$imageHeight = $fetchedRow['bi_image_height'];
		$destImg = '';
		if (!empty($imageUrl)){
			if ($itemType == 0){		// 画像ファイルの場合
				$destImg = '<img id="preview_img" src="' . $this->getUrl($imageUrl) . '" ';
				if (!empty($imageWidth) && $imageWidth > 0) $destImg .= 'width="' . $imageWidth . '"';
				if (!empty($imageHeight) && $imageHeight > 0) $destImg .= ' height="' . $imageHeight. '"';
				$destImg .= ' />';
			} else if ($itemType == 1){		// Flashファイルの場合
				$destImg = '<object id="preview_obj" data="' . $this->getUrl($imageUrl) . '" type="application/x-shockwave-flash"';
				if (!empty($imageWidth) && $imageWidth > 0) $destImg .= ' width="' . $imageWidth . '"';
				if (!empty($imageHeight) && $imageHeight > 0) $destImg .= ' height="' . $imageHeight . '"';
				$destImg .= '><param id="preview_param" name="movie" value="' . $this->getUrl($imageUrl) . '" /><param name="wmode" value="transparent" /></object>';
			}
		}
		
		// リンク先URL
		$redirectUrl = default_bannerCommonDef::getLinkUrlByDevice($fetchedRow['bi_link_url']);
		
		$row = array(
			'index' => $index,													// 項目番号
			'serial' => $serial,								// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['bi_id']),			// ID
			'type_icon' => $iconTag,					// バナー項目タイプ
			'type' => $this->convertToDispString($fetchedRow['bi_type']),					// バナー項目タイプ
			'name' => $this->convertToDispString($fetchedRow['bi_name']),		// 名前
			'filename' => $filename,
			'url' => $this->getUrl($url),					// URL
			'link_url' => $this->convertToDispString($redirectUrl),					// リンク先URL
			'view_count' => $viewCount,								// 閲覧数
			'visible' => $visible,											// 項目の表示
			'note' => $this->convertToDispString($fetchedRow['bi_admin_note']),					// 備考
			'image' => $destImg,							// プレビュー画像
			'update_user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'update_dt' => $this->convertToDispDateTime($fetchedRow['bi_create_dt'])	// 更新日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		//$this->serialArray[] = $fetchedRow['bi_serial'];
		//$this->idArray[] = $fetchedRow['bi_id'];
		$this->isExistsContent = true;		// コンテンツ項目が存在するかどうか
		return true;
	}*/
	/**
	 * 画像一覧データ作成処理コールバック
	 *
	 * @param object	$tmpl			テンプレートオブジェクト
	 * @param object	$request		任意パラメータ(HTTPリクエストオブジェクト)
	 * @param							なし
	 */
	function makeImageList($tmpl, $request)
	{
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 画像選択画面で使用
		$this->selectedItems = explode(',', $request->trimValueOf('items'));
		sort($this->selectedItems, SORT_NUMERIC);		// ID順にソート
			
		// 総数を取得
		$totalCount = self::$_mainDb->getImageCount();

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, self::IMAGE_LIST_COUNT);
		
		// #### 画像リストを作成 ####
		self::$_mainDb->getImageList(self::IMAGE_LIST_COUNT, $pageNo, array($this, 'imageListLoop'), $tmpl);
		$this->setListTemplateVisibility('itemlist');	// 一覧部の表示制御
		//if (!$this->isExistsContent) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 項目がないときは、一覧を表示しない
		
		// ページングリンク作成
		$currentBaseUrl = '';		// POST用のリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $currentBaseUrl, 'selpage($1);return false;');
		$tmpl->addVar("_tmpl", "page_link", $pageLink);
		
		// 画像選択項目
		$itemsStr = $this->convertToDispString(implode($this->selectedItems, ','));
		$tmpl->addVar("_tmpl", "items", $itemsStr);	// 画像選択項目
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $tmpl			テンプレートオブジェクト(画像選択データ用)
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function imageListLoop($index, $fetchedRow, $tmpl)
	{
		$serial = $this->convertToDispString($fetchedRow['bi_serial']);
		$id = $fetchedRow['bi_id'];
		$name = $fetchedRow['bi_name'];
		$type = $fetchedRow['bi_type'];
		$width = $fetchedRow['bi_image_width'];
		$height = $fetchedRow['bi_image_height'];
		
		$visible = '';
		if ($fetchedRow['bi_visible']){	// 項目の表示
			$visible = 'checked';
		}
		// ファイル名取得
		$partArray = explode('/', $fetchedRow['bi_image_url']);
		if (count($partArray) > 0) $filename = $partArray[count($partArray)-1];
		
		// 画像URL
		$url = $fetchedRow['bi_image_url'];
		if (!empty($url)) $url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);
		
		// リンク先
		$linkUrl = default_bannerCommonDef::getLinkUrlByDevice($fetchedRow['bi_link_url']);
		$linkUrlShort = makeTruncStr($linkUrl, self::MAX_URL_LENGTH);
	
		// 画像プレビュー用ボタンを作成
		$eventAttr = 'onclick="showPreview(\''. $id . '\', \'' . $name . '\', \'' . $type . '\', \'' . $this->getUrl($url) . '\', \'' . $width .'\', \'' . $height . '\', \'' . $linkUrl . '\');"';
		$previewButtonTag = $this->gDesign->createPreviewImageButton(''/*同画面*/, 'プレビュー', ''/*タグID*/, $eventAttr/*クリックイベント時処理*/);

		// バナー表示イメージの作成
//		$imageUrl = $fetchedRow['bi_image_url'];
//		if (!empty($imageUrl)) $imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
//		$imageWidth = $fetchedRow['bi_image_width'];
//		$imageHeight = $fetchedRow['bi_image_height'];
		$destImg = '';
		if (!empty($url)){
			if ($itemType == 0){		// 画像ファイルの場合
				$destImg = '<img id="preview_img" src="' . $this->getUrl($url) . '" ';
				if (!empty($width) && $width > 0) $destImg .= 'width="' . $width . '"';
				if (!empty($height) && $height > 0) $destImg .= ' height="' . $height. '"';
				$destImg .= ' />';
			} else if ($itemType == 1){		// Flashファイルの場合
				$destImg = '<object id="preview_obj" data="' . $this->getUrl($url) . '" type="application/x-shockwave-flash"';
				if (!empty($width) && $width > 0) $destImg .= ' width="' . $width . '"';
				if (!empty($height) && $height > 0) $destImg .= ' height="' . $height . '"';
				$destImg .= '><param id="preview_param" name="movie" value="' . $this->getUrl($url) . '" /><param name="wmode" value="transparent" /></object>';
			}
		}
		
		// 画像選択タスクのときは、選択中の項目にチェックをつける
		$checked = '';
		if ($this->act == 'getimagelist'){		// 画像選択用データの場合
			if (in_array($id, $this->selectedItems)) $checked = 'checked';
		}
		$row = array(
			'index' => $index,													// 項目番号
			'serial' => $serial,								// シリアル番号
			'id' => $this->convertToDispString($id),			// ID
			'checked' => $checked,				// 項目のチェック状況
//			'type_icon' => $iconTag,					// バナー項目タイプ
//			'type' => $this->convertToDispString($type),					// バナー項目タイプ
			'name' => $this->convertToDispString($name),		// 名前
			'filename' => $filename,
//			'url' => $this->getUrl($url),					// URL
			'link_url' => $this->convertToDispString($linkUrl),					// リンク先URL
			'link_url_short' => $this->convertToDispString($linkUrlShort),					// リンク先URL
			'width' => $this->convertToDispString($width),					// 画像幅
			'height' => $this->convertToDispString($height),					// 画像高さ
			'visible' => $visible,											// 項目の表示
			'preview_image_button'	=> $previewButtonTag,					// 画像プレビューボタン
			'image' => $destImg							// プレビュー画像
		);
		
		if ($this->act == 'getimagelist'){		// 画像選択用データの場合
			$tmpl->addVars('itemlist', $row);
			$tmpl->parseTemplate('itemlist', 'a');
		} else {
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
		}
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['bi_serial'];
		$this->idArray[] = $id;
		$this->isExistsContent = true;		// コンテンツ項目が存在するかどうか
		return true;
	}
}
?>
