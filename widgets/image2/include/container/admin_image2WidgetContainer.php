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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_image2WidgetContainer.php 2268 2009-08-31 03:29:18Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/image2Db.php');

class admin_image2WidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new image2Db();
		$this->sysDb = $this->gInstance->getSytemDbObject();
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
		if ($task == 'list'){		// 一覧画面
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
		if ($task == 'list'){		// 一覧画面
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
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// 定義名
		$imageUrl 	= $request->trimValueOf('item_image_url');							// 画像へのパス
		$linkUrl	= $request->trimValueOf('item_link_url');			// リンク先
		$align		= $request->trimValueOf('item_align');			// 表示位置
		$bgcolor = $request->trimValueOf('item_bgcolor');		// 画像バックグランドカラー
		$width	= $request->trimValueOf('item_width');		// 画像の幅
		$height	= $request->trimValueOf('item_height');		// 画像の高さ
		$margin = $request->trimValueOf('item_margin');		// 画像マージン
		$widthType	= $request->trimValueOf('item_widthtype');		// 画像の幅単位
		$heightType	= $request->trimValueOf('item_heighttype');		// 画像の高さ単位
		$posx	= $request->trimValueOf('item_posx');		// x座標
		$posy	= $request->trimValueOf('item_posy');		// y座標
		$posxType	= $request->trimValueOf('item_posxtype');		// x座標単位
		$posyType	= $request->trimValueOf('item_posytype');		// y座標単位
		$posType	= $request->trimValueOf('item_postype');		// 座標指定方法
		$usePos	= ($request->trimValueOf('item_use_pos') == 'on') ? 1 : 0;			// 座標指定を可能とするかどうか
		$useLink	= ($request->trimValueOf('item_use_link') == 'on') ? 1 : 0;			// 画像にリンクを付けるかどうか
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($width, '画像の幅', true);
			$this->checkNumeric($height, '画像の高さ', true);
			$this->checkNumeric($margin, '画像マージン', true);
			
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
				// パスをマクロ形式に変換
				if (!empty($imageUrl)) $imageUrl = $this->gEnv->getMacroPath($imageUrl);
				
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->name	= $name;// 表示名
				$newObj->imageUrl 	= $imageUrl;							// 画像へのパス
				$newObj->linkUrl	= $linkUrl;			// リンク先
				$newObj->align		= $align;			// 表示位置
				$newObj->bgcolor 	= $bgcolor;		// 画像バックグランドカラー
				$newObj->width		= $width;		// 画像の幅
				$newObj->height		= $height;		// 画像の高さ
				$newObj->margin		= $margin;		// 画像マージン
				$newObj->widthType	= $widthType;		// 画像の幅単位
				$newObj->heightType	= $heightType;		// 画像の高さ単位
				$newObj->posx		= $posx;		// x座標
				$newObj->posy		= $posy;		// y座標
				$newObj->posxType	= $posxType;		// x座標単位
				$newObj->posyType	= $posyType;		// y座標単位
				$newObj->posType	= $posType;		// 座標指定方法(相対座標)
				$newObj->usePos		= $usePos;			// 座標指定を可能とするかどうか
				$newObj->useLink	= $useLink;			// 画像にリンクを付けるかどうか
				
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					$this->configId = $defConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($width, '画像の幅', true);
			$this->checkNumeric($height, '画像の高さ', true);
			$this->checkNumeric($margin, '画像マージン', true);
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// パスをマクロ形式に変換
				if (!empty($imageUrl)) $imageUrl = $this->gEnv->getMacroPath($imageUrl);
				
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->imageUrl = $imageUrl;							// 画像へのパス
					$targetObj->linkUrl = $linkUrl;			// リンク先
					$targetObj->align	= $align;			// 表示位置
					$targetObj->bgcolor = $bgcolor;		// 画像バックグランドカラー
					$targetObj->width = $width;		// 画像の幅
					$targetObj->height = $height;		// 画像の高さ
					$targetObj->margin	= $margin;		// 画像マージン
					$targetObj->widthType = $widthType;		// 画像の幅単位
					$targetObj->heightType = $heightType;		// 画像の高さ単位
					$targetObj->posx = $posx;		// x座標
					$targetObj->posy = $posy;		// y座標
					$targetObj->posxType = $posxType;		// x座標単位
					$targetObj->posyType = $posyType;		// y座標単位
					$targetObj->posType = $posType;		// 座標指定方法(相対座標)
					$targetObj->imageType = $imageType;		// 画像のタイプ(0=メニューから選択、1=直接指定)
					$targetObj->usePos = $usePos;			// 座標指定を可能とするかどうか
					$targetObj->useLink = $useLink;			// 画像にリンクを付けるかどうか
				}
				
				// 設定値を更新
				if ($ret) $ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		} else {	// 初期起動時、または上記以外の場合
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		}
		// 設定項目選択メニュー作成
		$this->createItemMenu();
				
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
				$name = $this->createDefaultName();			// デフォルト登録項目名
				$imageUrl 	= '';							// 画像へのパス
				$linkUrl	= '';			// リンク先
				$align		= '';			// 表示位置
				$bgcolor 	= '';		// 画像バックグランドカラー
				$width		= 0;		// 画像の幅
				$height		= 0;		// 画像の高さ
				$margin		= 0;		// 画像マージン
				$widthType	= 0;		// 画像の幅単位
				$heightType	= 0;		// 画像の高さ単位
				$posx		= 0;		// x座標
				$posy		= 0;		// y座標
				$posxType	= 0;		// x座標単位
				$posyType	= 0;		// y座標単位
				$posType	= 'relative';		// 座標指定方法(相対座標)
				$usePos		= 0;			// 座標指定を可能とするかどうか
				$useLink	= 0;			// 画像にリンクを付けるかどうか
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name		= $targetObj->name;// 名前
					$imageUrl	= $targetObj->imageUrl;							// 画像へのパス
					$linkUrl	= $targetObj->linkUrl;			// リンク先
					$align		= $targetObj->align;			// 表示位置
					$bgcolor	= $targetObj->bgcolor;		// 画像バックグランドカラー
					$width		= $targetObj->width;		// 画像の幅
					$height		= $targetObj->height;		// 画像の高さ
					$margin		= $targetObj->margin;		// 画像マージン
					$widthType	= $targetObj->widthType;		// 画像の幅単位
					$heightType	= $targetObj->heightType;		// 画像の高さ単位
					$posx		= $targetObj->posx;		// x座標
					$posy		= $targetObj->posy;		// y座標
					$posxType	= $targetObj->posxType;		// x座標単位
					$posyType	= $targetObj->posyType;		// y座標単位
					$posType	= $targetObj->posType;		// 座標指定方法(相対座標)
					$usePos		= $targetObj->usePos;			// 座標指定を可能とするかどうか
					$useLink	= $targetObj->useLink;			// 画像にリンクを付けるかどうか
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}

		// 画像のパスを修正
		if (!empty($imageUrl)){
			$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
		}
		
		// 画面にデータを埋め込む
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("item_name_visible", "name",	$name);
		$this->tmpl->addVar("_widget", "link_url",	$this->getUrl($linkUrl));// リンク先
		$this->tmpl->addVar("_widget", "bgcolor", $bgcolor);
		$this->tmpl->addVar("_widget", "width",	$width);
		$this->tmpl->addVar("_widget", "height",	$height);
		$this->tmpl->addVar("_widget", "margin",	$margin);
		$this->tmpl->addVar("_widget", "posx",	$posx);// x座標
		$this->tmpl->addVar("_widget", "posy",	$posy);// y座標
		
		// 高さ、幅の単位
		if (empty($widthType)){		// ヘッダの幅単位
			$this->tmpl->addVar("_widget", "width0_selected",	'selected');
		} else {
			$this->tmpl->addVar("_widget", "width1_selected",	'selected');
		}
		if (empty($heightType)){		// ヘッダの高さ単位
			$this->tmpl->addVar("_widget", "height0_selected",	'selected');
		} else {
			$this->tmpl->addVar("_widget", "height1_selected",	'selected');
		}
		if (empty($posxType)){		// x座標単位
			$this->tmpl->addVar("_widget", "posx0_selected",	'selected');
		} else {
			$this->tmpl->addVar("_widget", "posx1_selected",	'selected');
		}
		if (empty($posyType)){		// y座標単位
			$this->tmpl->addVar("_widget", "posy0_selected",	'selected');
		} else {
			$this->tmpl->addVar("_widget", "posy1_selected",	'selected');
		}
		if ($posType == 'relative'){		// 座標の指定方法
			$this->tmpl->addVar("_widget", "postype0_selected",	'selected');
		} else if ($posType == 'absolute'){
			$this->tmpl->addVar("_widget", "postype1_selected",	'selected');
		}
		if ($align == ''){		// 表示位置
			$this->tmpl->addVar("_widget", "align0_selected",	'selected');
		} else if ($align == 'left'){
			$this->tmpl->addVar("_widget", "align1_selected",	'selected');
		} else if ($align == 'center'){
			$this->tmpl->addVar("_widget", "align2_selected",	'selected');
		} else if ($align == 'right'){
			$this->tmpl->addVar("_widget", "align3_selected",	'selected');
		}
		$this->tmpl->addVar("_widget", "image_url",	$imageUrl);
		if ($usePos) $this->tmpl->addVar('_widget', 'use_pos',	'checked');	// 座標指定を可能とするかどうか
		if ($useLink) $this->tmpl->addVar('_widget', 'use_link',	'checked');			// 画像にリンクを付けるかどうか
		
		// プレビュー作成
		$destImg = '';
		if (!empty($imageUrl)){
			$destImg = '<img id="preview_img" src="' . $this->getUrl($imageUrl) . '"';
			if (!empty($width) && $width > 0){
				$destImg .= ' width="' . $width;
				if (!empty($widthType)) $destImg .= '%';
				$destImg .= '"';
			}
			if (!empty($height) && $height > 0){
				$destImg .= ' height="' . $height;
				if (!empty($heightType)) $destImg .= '%';
				$destImg .= '"';
			}
			$destImg .= ' />';
		}
		$this->tmpl->addVar("_widget", "image", $destImg);
		
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		
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
	 * 選択用メニューを作成
	 *
	 * @return なし						
	 */
	function createItemMenu()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id = $this->paramObj[$i]->id;// 定義ID
			$targetObj = $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			$selected = '';
			if ($this->configId == $id) $selected = 'selected';

			$row = array(
				'name' => $name,		// 名前
				'value' => $id,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('title_list', $row);
			$this->tmpl->parseTemplate('title_list', 'a');
		}
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
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					break;
				}
			}
			// 重複なしのときは終了
			if ($i == count($this->paramObj)) break;
		}
		return $name;
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
				$ret = $this->delPageDefParam($defSerial, $defConfigId, $this->paramObj, $delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// 定義一覧作成
		$this->createItemList();
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar('_widget', 'admin_url', $this->gEnv->getDefaultAdminUrl());// 管理者URL
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * 定義一覧作成
	 *
	 * @return なし						
	 */
	function createItemList()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id			= $this->paramObj[$i]->id;// 定義ID
			$targetObj	= $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			$width		= $targetObj->width;		// 画像の幅
			$height		= $targetObj->height;		// 画像の高さ
			$widthType	= $targetObj->widthType;		// 画像の幅単位
			$heightType	= $targetObj->heightType;		// 画像の高さ単位
		
			// ファイル名取得
			$filename = '';
			$partArray = explode('/', $targetObj->imageUrl);
			if (count($partArray) > 0) $filename = $partArray[count($partArray)-1];
		
			// 使用数
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->sysDb->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			
			// 画像サイズ
			$imgWidth = '';
			$imgHeight = '';
			if (!empty($width) && $width > 0){
				$imgWidth = $width;
				if (!empty($widthType)) $imgWidth .= '%';
			}
			if (!empty($height) && $height > 0){
				$imgHeight = $height;
				if (!empty($heightType)) $imgHeight .= '%';
			}
			
			// 画像URL
			$url = $targetObj->imageUrl;
			if (!empty($url)) $url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);
			
			$row = array(
				'index' => $i,
				'id' => $id,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'filename' => $this->convertToDispString($filename),	// ファイル名
				'def_count' => $defCount,							// 使用数
				'width' => $imgWidth,					// 画像幅
				'height' => $imgHeight,					// 画像高さ
				'url' => $this->getUrl($url)					// URL
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
}
?>
