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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_banner3ImageWidgetContainer.php 5868 2013-03-28 04:08:49Z fishbone $
 * @link       http://www.magic3.org
 */
//require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_banner3BaseWidgetContainer.php');
require_once($gEnvManager->getWidgetContainerPath('banner3') . '/admin_banner3BaseWidgetContainer.php');
//require_once($gEnvManager->getCurrentWidgetDbPath() . '/banner3Db.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class admin_banner3ImageWidgetContainer extends admin_banner3BaseWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている画像リンクシリアル番号
	private $idArray = array();			// 表示されている画像リンクID
	private $isExistsContent;		// コンテンツ項目が存在するかどうか
	private $itemTypeArray;		// バナー項目のすべての種類
	private $targetTypeArray;		// リンクターゲットのすべての種類
	private $itemId;			// 選択中の画像リンク項目
	private $itemType;			// バナー項目の種類
	private $targetType;			// リンクターゲットの種類
	private $selectedItems;		// 画像リンク選択用
	private $task;				// 処理タスク
	const IMAGE_LIST_COUNT = 10;		// 表示画像リンク数
	const IMAGE_ICON_FILE = '/images/system/image16.png';			// イメージアイコン
	const FLASH_ICON_FILE = '/images/system/flash16.png';		// Flashアイコン
	const ICON_SIZE = 16;		// アイコンのサイズ
	const MAX_URL_LENGTH = 30;		// 一覧のURLの最大長
	const MAX_NOTE_LENGTH = 30;		// 一覧のコメントの最大長
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 画像タイプ
		$this->itemTypeArray = array(	array(	'name' => '画像',	'value' => '0'),
										array(	'name' => 'Flash',	'value' => '1'));
		// リンクターゲットタイプ
		$this->targetTypeArray = array(	array(	'name' => '同じウィンドウ',		'value' => ''),
										array(	'name' => '新しいウィンドウ',	'value' => '_blank'));
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
		if ($task == 'image_detail'){		// 詳細画面
			return 'admin_image_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_image.tmpl.html';
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
		if ($task == 'image_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * 画像リンク一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		// 共通値
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->task = $request->trimValueOf('task');
		
		// ##### 検索条件 #####
		$pageNo = $request->trimValueOf('page');				// ページ番号
		if (empty($pageNo)) $pageNo = 1;
		$maxListCount = self::IMAGE_LIST_COUNT;		// 表示画像リンク数
		
		// 画像選択画面で使用
		$this->selectedItems = explode(',', $request->trimValueOf('items'));
		sort($this->selectedItems, SORT_NUMERIC);		// ID順にソート
		
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
				$ret = self::$_mainDb->delImage($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 総数を取得
		$totalCount = self::$_mainDb->getImageCount();

		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
		
		// #### 画像リンクリストを作成 ####
		self::$_mainDb->getImageList($maxListCount, $pageNo, array($this, 'imageListLoop'));
		if (!$this->isExistsContent) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 項目がないときは、一覧を表示しない
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "id_list", implode($this->idArray, ','));// 表示項目のIDを設定
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "task", $this->task);	// 処理タスク
		
		// ボタンの表示制御
		if ($this->task == 'image_select'){		// 画像リンク選択タスクのとき
			$this->tmpl->setAttribute('select_button', 'visibility', 'visible');// 「確定」ボタン
			
			// 選択中の画像リンク
			$itemsStr = $this->convertToDispString(implode($this->selectedItems, ','));
			$this->tmpl->addVar("_widget", "items", $itemsStr);	// 画像リンク選択項目
			$this->tmpl->addVar("select_button", "items_label", $itemsStr);	// 画像リンク選択項目
		} else {
			$this->tmpl->setAttribute('edit_button', 'visibility', 'visible');// 「新規」「削除」「編集」ボタン
			
			// ヘルプの追加
			$this->convertHelp('edit_button');
		}
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * コンテンツ詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		// 引き継ぎパラメータ
		$pageNo = $request->trimValueOf('page');				// ページ番号
		
		// 共通値
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->itemId = $request->trimValueOf('item_imageid');		// 選択中の画像リンク項目ID
		$name = $request->trimValueOf('item_name');
		$this->itemType = $request->trimValueOf('item_type');
		$this->targetType = $request->trimValueOf('item_target_type');			// リンクターゲットの種類
		$linkUrl = $request->trimValueOf('item_link_url');			// リンク先URL(デフォルト)
		$linkUrlArray = array();
		$linkUrlArray['s'] = $request->trimValueOf('item_link_url_s');		// リンク先URL(スマートフォン用)
		//$linkUrl_s = $request->trimValueOf('item_link_url_s');		// リンク先URL(スマートフォン用)
		$imageUrl = $request->trimValueOf('item_image_url');
		$image = $request->trimValueOf('item_image');
		$admin_note = $request->trimValueOf('item_admin_note');
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;			// 表示するかどうか
		$imageWidth = $request->trimValueOf('item_width');
		$imageHeight = $request->trimValueOf('item_height');
		$imageAlt = $request->trimValueOf('item_alt');
		$srcHtml = $request->valueOf('item_html');
		
		// Pタグを除去
		$srcHtml = $this->gInstance->getTextConvManager()->deleteTag($srcHtml, 'p');
		
		// その他の属性を作成
		$attr = '';
		$attrArray = array();
		if (!empty($this->targetType)) $attrArray[] = 'target=' . $this->targetType;
		if (!empty($attrArray)) $attr = implode(';', $attrArray);
				
		$reloadData = false;		// データの再読み込み
		if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			
			if (!empty($imageWidth) && !ValueCheck::isNumeric($imageWidth)){		// 数値かどうかのチェック
				$this->setAppErrorMsg('画像の幅は数字で指定してください');
			}
			if (!empty($imageHeight) && !ValueCheck::isNumeric($imageHeight)){		// 数値かどうかのチェック
				$this->setAppErrorMsg('画像の高さは数字で指定してください');
			}
					
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// パスをマクロ形式に変換
				if (!empty($imageUrl)) $imageUrl = $this->gEnv->getMacroPath($imageUrl);

				// リンク先を結合
				$dbLinkUrl = $this->makeLinkUrl($linkUrl, $linkUrlArray);
				
				$ret = self::$_mainDb->updateImage(0, $name, $this->itemType, $admin_note, $imageUrl, $dbLinkUrl, 
													$imageWidth, $imageHeight, $imageAlt, $srcHtml, $visible, $startdt, $enddt, $attr, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			
			if (!empty($imageWidth) && !ValueCheck::isNumeric($imageWidth)){		// 数値かどうかのチェック
				$this->setAppErrorMsg('画像の幅は数字で指定してください');
			}
			if (!empty($imageHeight) && !ValueCheck::isNumeric($imageHeight)){		// 数値かどうかのチェック
				$this->setAppErrorMsg('画像の高さは数字で指定してください');
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// パスをマクロ形式に変換
				if (!empty($imageUrl)) $imageUrl = $this->gEnv->getMacroPath($imageUrl);
				
				// リンク先を結合
				$dbLinkUrl = $this->makeLinkUrl($linkUrl, $linkUrlArray);
				
				$ret = self::$_mainDb->updateImage($this->serialNo, $name, $this->itemType, $admin_note, $imageUrl, $dbLinkUrl, 
													$imageWidth, $imageHeight, $imageAlt, $srcHtml, $visible, $startdt, $enddt, $attr, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}				
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = self::$_mainDb->delImage(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'select'){		// 画像リンクIDの選択の場合
			if (empty($this->itemId)){
				$this->serialNo = 0;
				
				// 入力値初期化
				$name = '';		// 名前
				$this->itemType = 0;
				$this->targetType = '';			// リンクターゲットの種類
				$linkUrl = '';				// リンク先(デフォルト)
				$linkUrlArray = array();	// リンク先(デフォルト以外)
				$imageUrl = '';		// 画像URL
				$admin_note = '';		// 管理者用備考
				$visible = 1;		// デフォルトは表示状態
				$imageWidth = '';
				$imageHeight = '';
				$imageAlt = '';
				$srcHtml = M3_TAG_START . M3_TAG_MACRO_ITEM . M3_TAG_END;			// テンプレートデフォルト値
			} else {
				// IDからシリアル番号を取得
				$ret = self::$_mainDb->getImageById($this->itemId, $row);
				if ($ret){
					$this->serialNo = $row['bi_serial'];
					$reloadData = true;
				}
			}
		} else {
			if (empty($this->serialNo)){		// シリアル番号が0のときは、新規追加モードにする
				// 入力値初期化
				$name = '';		// 名前
				$this->itemType = 0;
				$this->targetType = '';			// リンクターゲットの種類
				$linkUrl = '';		// リンク先(デフォルト)
				$imageUrl = '';		// 画像URL
				$linkUrlArray = array();	// リンク先(デフォルト以外)
				$admin_note = '';		// 管理者用備考
				$visible = 1;		// デフォルトは表示状態
				$imageWidth = '';
				$imageHeight = '';
				$imageAlt = '';
				$srcHtml = M3_TAG_START . M3_TAG_MACRO_ITEM . M3_TAG_END;			// テンプレートデフォルト値
			} else {
				$reloadData = true;
			}
		}
		if ($reloadData){		// データの再読み込み
			// 登録済みのバナー定義を取得
			$ret = self::$_mainDb->getImageBySerial($this->serialNo, $row);
			if ($ret){
				// 取得値を設定
				$this->itemId = $row['bi_id'];		// ID
				$name = $row['bi_name'];		// 名前
				$this->itemType = $row['bi_type'];		// 項目の種別
				$imageUrl = $row['bi_image_url'];		// 画像URL
				$admin_note = $row['bi_admin_note'];		// 管理者用備考
				$visible = $row['bi_visible'];		// 表示
				$updateUser = $this->convertToDispString($row['lu_name']);	// 更新者
				$updateDt = $this->convertToDispDateTime($row['bi_create_dt']);	// 更新日時
				$imageWidth = $row['bi_image_width'];		// 画像幅
				$imageHeight = $row['bi_image_height'];		// 画像高さ
				$imageAlt = $row['bi_image_alt'];		// 画像テキスト
				$srcHtml = $row['bi_html'];		// HTML
				$attr = $row['bi_attr'];		// その他の属性
				
				// リンク先解析
				$linkDbUrl = $row['bi_link_url'];		// リンク先
				default_bannerCommonDef::parseLinkUrl($linkDbUrl, $linkUrl, $linkUrlArray);
				
				// その他の属性を設定
				if (!empty($attr)){
					$attrArray = explode(';', $attr);
					for ($i = 0; $i < count($attrArray); $i++){
						list($key, $value) = explode('=', $attrArray[$i]);
						$key = trim($key);
						$value = trim($value);
						switch ($key){
							case 'target':
								$this->targetType = $value;			// リンクターゲットの種類
								break;
						}
					}
				}
			}
		}
		// 画像項目メニューを作成
		self::$_mainDb->getImageList(-1/* すべて取得 */, 0, array($this, 'imageIdListLoop'));
		
		// バナー項目タイプメニュー作成
		$this->createItemTypeMenu();
		
		// リンクターゲットメニュー作成
		$this->createTargetTypeMenu();
		
		// 画像のパスを修正
		if (!empty($imageUrl)){
			$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
		}
		
		// #### 更新、新規登録部をを作成 ####
		$this->tmpl->addVar("_widget", "serial", $this->convertToDispString($this->serialNo));
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// 名前
		$this->tmpl->addVar("_widget", "image_url", $this->convertToDispString($imageUrl));		// 画像イメージURL
		$this->tmpl->addVar("_widget", "link_url", $this->convertToDispString($linkUrl));		// リンク先(デフォルト)
		$this->tmpl->addVar("_widget", "link_url_s", $this->convertToDispString($linkUrlArray['s']));		// リンク先(スマートフォン)
		$this->tmpl->addVar("_widget", "admin_note", $this->convertToDispString($admin_note));		// 備考
		$visibleStr = '';
		if ($visible){	// 項目の表示
			$visibleStr = 'checked';
		}
		$this->tmpl->addVar("_widget", "visible", $visibleStr);		// 表示状態
		if (!empty($updateUser)) $this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($updateUser));	// 更新者
		if (!empty($updateDt)) $this->tmpl->addVar("_widget", "update_dt", $this->convertToDispDateTime($updateDt));	// 更新日時
		
		// バナー表示イメージの作成
		$destImg = '';
		if (!empty($imageUrl)){
			if ($this->itemType == 0){		// 画像ファイルの場合
				$destImg = '<img id="preview_img" src="' . $this->getUrl($imageUrl) . '" ';
				if (!empty($imageWidth) && $imageWidth > 0) $destImg .= 'width="' . $imageWidth . '"';
				if (!empty($imageHeight) && $imageHeight > 0) $destImg .= ' height="' . $imageHeight. '"';
				$destImg .= ' />';
			} else if ($this->itemType == 1){		// Flashファイルの場合
				$destImg = '<object id="preview_obj" data="' . $this->getUrl($imageUrl) . '" type="application/x-shockwave-flash"';
				if (!empty($imageWidth) && $imageWidth > 0) $destImg .= ' width="' . $imageWidth . '"';
				if (!empty($imageHeight) && $imageHeight > 0) $destImg .= ' height="' . $imageHeight . '"';
				$destImg .= '><param id="preview_param" name="movie" value="' . $this->getUrl($imageUrl) . '" /><param name="wmode" value="transparent" /></object>';
			}
		}
		$this->tmpl->addVar("_widget", "image", $destImg);
		$this->tmpl->addVar("_widget", "width", $imageWidth);
		$this->tmpl->addVar("_widget", "height", $imageHeight);
		$this->tmpl->addVar("_widget", "alt", $imageAlt);
		$this->tmpl->addVar("_widget", "html", $srcHtml);		// テンプレート
		$this->tmpl->addVar("_widget", "tag", M3_TAG_START . M3_TAG_MACRO_ITEM . M3_TAG_END);		// 埋め込みタグ
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->addVar("_widget", "id", $this->itemId);			// 画像リンク項目ID
			$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 「削除」ボタン
		}
		
		// 引き継ぎパラメータ
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		
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
	function imageListLoop($index, $fetchedRow, $param)
	{
		$serial = $this->convertToDispString($fetchedRow['bi_serial']);
		$visible = '';
		if ($fetchedRow['bi_visible']){	// 項目の表示
			$visible = 'checked';
		}
		// ファイル名取得
		$partArray = explode('/', $fetchedRow['bi_image_url']);
		if (count($partArray) > 0) $filename = $partArray[count($partArray)-1];
		
		// 閲覧数取得
		$viewCount = self::$_mainDb->getTotalViewCount($serial);
		
		// 項目タイプの設定
		$iconUrl = '';
		switch ($fetchedRow['bi_type']){
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
		
		// リンク先、備考
		$redirectUrl = default_bannerCommonDef::getLinkUrlByDevice($fetchedRow['bi_link_url']);
		$linkUrl = makeTruncStr($redirectUrl, self::MAX_URL_LENGTH);
		$note = makeTruncStr($fetchedRow['bi_admin_note'], self::MAX_NOTE_LENGTH);
	
		// 画像リンク選択タスクのときは、選択中の項目にチェックをつける
		$checked = '';
		if ($this->task == 'image_select'){		// 画像リンク選択タスクのとき
			if (in_array($fetchedRow['bi_id'], $this->selectedItems)) $checked = 'checked';
		}
		$row = array(
			'index' => $index,													// 項目番号
			'serial' => $serial,								// シリアル番号
			'id' => $this->convertToDispString($fetchedRow['bi_id']),			// ID
			'checked' => $checked,				// 項目のチェック状況
			'type_icon' => $iconTag,					// バナー項目タイプ
			'type' => $this->convertToDispString($fetchedRow['bi_type']),					// バナー項目タイプ
			'name' => $this->convertToDispString($fetchedRow['bi_name']),		// 名前
			'filename' => $filename,
			'url' => $this->getUrl($url),					// URL
			'link_url' => $this->convertToDispString($linkUrl),					// リンク先URL
			'width' => $this->convertToDispString($fetchedRow['bi_image_width']),					// 画像幅
			'height' => $this->convertToDispString($fetchedRow['bi_image_height']),					// 画像高さ
			'view_count' => $viewCount,								// 閲覧数
			'visible' => $visible,											// 項目の表示
			'note' => $this->convertToDispString($note),					// 備考
			'update_user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'update_dt' => $this->convertToDispDateTime($fetchedRow['bi_create_dt'])	// 更新日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['bi_serial'];
		$this->idArray[] = $fetchedRow['bi_id'];
		$this->isExistsContent = true;		// コンテンツ項目が存在するかどうか
		return true;
	}
	/**
	 * バナー項目タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createItemTypeMenu()
	{
		for ($i = 0; $i < count($this->itemTypeArray); $i++){
			$value = $this->itemTypeArray[$i]['value'];
			$name = $this->itemTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->itemType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_type_list', $row);
			$this->tmpl->parseTemplate('item_type_list', 'a');
		}
	}
	/**
	 * リンクターゲットメニュー作成
	 *
	 * @return なし
	 */
	function createTargetTypeMenu()
	{
		for ($i = 0; $i < count($this->targetTypeArray); $i++){
			$value = $this->targetTypeArray[$i]['value'];
			$name = $this->targetTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->targetType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_target_list', $row);
			$this->tmpl->parseTemplate('item_target_list', 'a');
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
	function imageIdListLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['bi_id'] == $this->itemId){
			$selected = 'selected';
		}
		
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['bi_id']),			// バナー項目ID
			'name'     => $this->convertToDispString($fetchedRow['bi_name']),			// 項目名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('imageid_list', $row);
		$this->tmpl->parseTemplate('imageid_list', 'a');
		return true;
	}
	/**
	 * DB格納用リンク先URLを作成
	 *
	 * @param string $url_default	リンク先URL(デフォルト)
	 * @param array $urls			リンク先URL(デフォルト以外)
	 * @return string				生成したリンク先URL
	 */
	function makeLinkUrl($url_default, $urls)
	{
		if (empty($url_default)) return '';
		
		$destUrl = $url_default;
		foreach ($urls as $key => $value){
			if (empty($key) || empty($value)) continue;
			$destUrl .= ';' . $key . '|' . $value;
		}
		return $destUrl;
	}
}
?>
