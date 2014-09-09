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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_photo_mainBaseWidgetContainer.php');

class admin_photo_mainConfigWidgetContainer extends admin_photo_mainBaseWidgetContainer
{
	private $sortKeyTypeArray;		// ソートキータイプ
	private $sortKey;				// ソートキー
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// ソートキー選択用
		$this->sortKeyTypeArray = array(	array(	'name' => '画像表示順',		'value' => 'index'),
											array(	'name' => '日付',			'value' => 'date'),
											array(	'name' => '評価',			'value' => 'rate'),
											array(	'name' => '参照数',			'value' => 'ref'));
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
		return 'admin_config.tmpl.html';
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
		$act = $request->trimValueOf('act');
		$listViewCount = $request->trimValueOf('item_view_count');		// 画像一覧表示数
		$listViewOrder = $request->trimValueOf('item_view_order');		// 画像一覧表示順
		$this->sortKey = $request->trimValueOf('item_sort_key');				// ソートキー
		$categoryCount = $request->trimValueOf('item_category_count');		// カテゴリ数
		$titleLength = $request->trimValueOf('item_title_length');		// 画像タイトル文字数
		$protectCopyright = ($request->trimValueOf('item_protect_copyright') == 'on') ? 1 : 0;		// 著作権保護
		$thumbnailCrop = ($request->trimValueOf('item_thumbnail_crop') == 'on') ? 1 : 0;		// 切り取りサムネール
		$thumbBgColor = $request->trimValueOf('item_bg_color');		// サムネール背景色
		$categoryPassword = ($request->trimValueOf('item_category_password') == 'on') ? 1 : 0;		// 画像カテゴリーパスワード制限
		$onlineShop = ($request->trimValueOf('item_online_shop') == 'on') ? 1 : 0;		// オンラインショップ連携
		$layoutViewDetail = $request->valueOf('item_layout_view_detail');					// コンテンツレイアウト(詳細表示)
		$outputHead	= ($request->trimValueOf('item_output_head') == 'on') ? 1 : 0;		// ヘッダ出力するかどうか
		$headViewDetail = $request->valueOf('item_head_view_detail');					// ヘッダ出力(詳細表示)
		
		$usePhotoDate	= ($request->trimValueOf('item_use_photo_date') == 'on') ? 1 : 0;				// 画像情報(撮影日)を使用
		$usePhotoLocation	= ($request->trimValueOf('item_use_photo_location') == 'on') ? 1 : 0;			// 画像情報(撮影場所)を使用
		$usePhotoCamera	= ($request->trimValueOf('item_use_photo_camera') == 'on') ? 1 : 0;			// 画像情報(カメラ)を使用
		$usePhotoDescription = ($request->trimValueOf('item_use_photo_description') == 'on') ? 1 : 0;	// 画像情報(説明)を使用
		$usePhotoKeyword	= ($request->trimValueOf('item_use_photo_keyword') == 'on') ? 1 : 0;			// 画像情報(検索キーワード)を使用
		$usePhotoCategory	= ($request->trimValueOf('item_use_photo_category') == 'on') ? 1 : 0;		// 画像情報(カテゴリー)を使用
		$usePhotoRate	= ($request->trimValueOf('item_use_photo_rate') == 'on') ? 1 : 0;				// 画像情報(評価)を使用
		$htmlPhotoDescription	= ($request->trimValueOf('item_html_photo_description') == 'on') ? 1 : 0;	// HTML形式の画像情報(説明)
		
		$imageSize	= $request->trimValueOf('item_image_size');		// 公開画像サイズ
		$thumbnailSize	= $request->trimValueOf('item_thumbnail_size');		// サムネール画像サイズ
		$defaultImageSize	= $request->trimValueOf('item_default_image_size');		// デフォルト公開画像サイズ
		$defaultThumbnailSize	= $request->trimValueOf('item_default_thumbnail_size');		// デフォルトサムネール画像サイズ
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($listViewCount, '画像一覧表示数');
			$this->checkNumeric($categoryCount, '画像カテゴリー数');
			$this->checkNumeric($titleLength, '画像タイトル文字数');
			$this->checkNumeric($defaultImageSize, 'デフォルト公開画像サイズ');
			$this->checkNumeric($defaultThumbnailSize, 'デフォルトサムネール画像サイズ');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$isErr = false;
				
				// 値が空の場合はデフォルト値を設定
				if (empty($layoutViewDetail)) $layoutViewDetail = photo_mainCommonDef::DEFAULT_LAYOUT_VIEW_DETAIL;
				if (empty($headViewDetail)) $headViewDetail = photo_mainCommonDef::DEFAULT_HEAD_VIEW_DETAIL;
				
				$updateValues = array(	photo_mainCommonDef::CF_PHOTO_LIST_ITEM_COUNT	=> $listViewCount,		// 画像一覧表示数
										photo_mainCommonDef::CF_PHOTO_LIST_ORDER		=> $listViewOrder,		// 画像一覧表示順
										photo_mainCommonDef::CF_PHOTO_LIST_SORT_KEY		=> $this->sortKey,		// 画像一覧ソートキー
										photo_mainCommonDef::CF_IMAGE_CATEGORY_COUNT	=> $categoryCount,		// 画像カテゴリー数
										photo_mainCommonDef::CF_PHOTO_TITLE_SHORT_LENGTH	=> $titleLength,	// 画像タイトル文字数
										photo_mainCommonDef::CF_IMAGE_PROTECT_COPYRIGHT	=> $protectCopyright,	// 著作権保護
										photo_mainCommonDef::CF_THUMBNAIL_CROP			=> $thumbnailCrop,		// 切り取りサムネール
										photo_mainCommonDef::CF_THUMBNAIL_BG_COLOR		=> $thumbBgColor,		// サムネール背景色
										photo_mainCommonDef::CF_PHOTO_CATEGORY_PASSWORD	=> $categoryPassword,	// 画像カテゴリーパスワード制限
										photo_mainCommonDef::CF_ONLINE_SHOP				=> $onlineShop,			// オンラインショップ機能
										photo_mainCommonDef::CF_LAYOUT_VIEW_DETAIL		=> $layoutViewDetail,	// コンテンツレイアウト(詳細表示)
										photo_mainCommonDef::CF_OUTPUT_HEAD				=> $outputHead,			// ヘッダ出力するかどうか
										photo_mainCommonDef::CF_HEAD_VIEW_DETAIL		=> $headViewDetail,		// ヘッダ出力(詳細表示)
										
										photo_mainCommonDef::CF_USE_PHOTO_DATE			=> $usePhotoDate,				// 画像情報(撮影日)を使用
										photo_mainCommonDef::CF_USE_PHOTO_LOCATION		=> $usePhotoLocation,			// 画像情報(撮影場所)を使用
										photo_mainCommonDef::CF_USE_PHOTO_CAMERA		=> $usePhotoCamera,			// 画像情報(カメラ)を使用
										photo_mainCommonDef::CF_USE_PHOTO_DESCRIPTION	=> $usePhotoDescription,	// 画像情報(説明)を使用
										photo_mainCommonDef::CF_USE_PHOTO_KEYWORD		=> $usePhotoKeyword,			// 画像情報(検索キーワード)を使用
										photo_mainCommonDef::CF_USE_PHOTO_CATEGORY		=> $usePhotoCategory,		// 画像情報(カテゴリー)を使用
										photo_mainCommonDef::CF_USE_PHOTO_RATE			=> $usePhotoRate,				// 画像情報(評価)を使用
										photo_mainCommonDef::CF_HTML_PHOTO_DESCRIPTION	=> $htmlPhotoDescription,	// HTML形式の画像情報(説明)
										
										photo_mainCommonDef::CF_IMAGE_SIZE				=> $imageSize,	// 公開画像サイズ
										photo_mainCommonDef::CF_THUMBNAIL_SIZE			=> $thumbnailSize,	// サムネール画像サイズ
										photo_mainCommonDef::CF_DEFAULT_IMAGE_SIZE		=> $defaultImageSize,	// デフォルト公開画像サイズ
										photo_mainCommonDef::CF_DEFAULT_THUMBNAIL_SIZE	=> $defaultThumbnailSize);	// デフォルトサムネール画像サイズ
				$ret = $this->_updateConfig($updateValues);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました(項目=' . $this->_getErrMessage() . ')');
				}
				// 値を再取得
				$listViewCount	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_LIST_ITEM_COUNT);		// 画像一覧表示数
				$listViewOrder	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_LIST_ORDER);			// 画像一覧表示順
				if (!in_array($listViewOrder, array('0', '1'))) $listViewOrder = photo_mainCommonDef::DEFAULT_PHOTO_LIST_ORDER;		// デフォルトの画像一覧並び順(昇順)
				$this->sortKey	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_LIST_SORT_KEY);// 画像一覧ソートキー
				if (empty($this->sortKey)) $this->sortKey = photo_mainCommonDef::DEFAULT_PHOTO_LIST_SORT_KEY;
				$categoryCount	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_IMAGE_CATEGORY_COUNT);		// 画像カテゴリー数
				$titleLength	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_TITLE_SHORT_LENGTH);// 画像タイトル文字数
				$protectCopyright	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_IMAGE_PROTECT_COPYRIGHT);// 著作権保護
				$thumbnailCrop	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_THUMBNAIL_CROP);// 切り取りサムネール
				$thumbBgColor	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_THUMBNAIL_BG_COLOR);		// サムネール背景色
				$categoryPassword	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_CATEGORY_PASSWORD);		// 画像カテゴリーパスワード制限
				$onlineShop	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_ONLINE_SHOP);		// オンラインショップ機能
				$layoutViewDetail = self::$_mainDb->getConfig(photo_mainCommonDef::CF_LAYOUT_VIEW_DETAIL);					// コンテンツレイアウト(詳細表示)
				$outputHead = self::$_mainDb->getConfig(photo_mainCommonDef::CF_OUTPUT_HEAD);		// ヘッダ出力するかどうか
				$headViewDetail = self::$_mainDb->getConfig(photo_mainCommonDef::CF_HEAD_VIEW_DETAIL);		// ヘッダ出力(詳細表示)
			
				$usePhotoDate	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_DATE);				// 画像情報(撮影日)を使用
				$usePhotoLocation	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_LOCATION);			// 画像情報(撮影場所)を使用
				$usePhotoCamera	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_CAMERA);			// 画像情報(カメラ)を使用
				$usePhotoDescription = self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_DESCRIPTION);	// 画像情報(説明)を使用
				$usePhotoKeyword	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_KEYWORD);			// 画像情報(検索キーワード)を使用
				$usePhotoCategory	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_CATEGORY);		// 画像情報(カテゴリー)を使用
				$usePhotoRate	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_RATE);				// 画像情報(評価)を使用
				$htmlPhotoDescription	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_HTML_PHOTO_DESCRIPTION);	// HTML形式の画像情報(説明)
		
				$imageSize			= self::$_mainDb->getConfig(photo_mainCommonDef::CF_IMAGE_SIZE);	// 公開画像サイズ
				$thumbnailSize		= self::$_mainDb->getConfig(photo_mainCommonDef::CF_THUMBNAIL_SIZE);	// サムネール画像サイズ
				$defaultImageSize	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_DEFAULT_IMAGE_SIZE);		// デフォルト公開画像サイズ
				$defaultThumbnailSize	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_DEFAULT_THUMBNAIL_SIZE);		// デフォルトサムネール画像サイズ
			}
		} else {		// 初期表示の場合
			$listViewCount	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_LIST_ITEM_COUNT);// 画像一覧表示数
			if (intval($listViewCount) <= 0) $listViewCount = photo_mainCommonDef::DEFAULT_PHOTO_LIST_VIEW_COUNT;
			$listViewOrder	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_LIST_ORDER);// 画像一覧表示順
			if (!in_array($listViewOrder, array('0', '1'))) $listViewOrder = photo_mainCommonDef::DEFAULT_PHOTO_LIST_ORDER;		// デフォルトの画像一覧並び順(昇順)
			$this->sortKey	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_LIST_SORT_KEY);// 画像一覧ソートキー
			if (empty($this->sortKey)) $this->sortKey = photo_mainCommonDef::DEFAULT_PHOTO_LIST_SORT_KEY;
			$categoryCount	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_IMAGE_CATEGORY_COUNT);// 画像カテゴリー数
			if (intval($categoryCount) <= 0) $categoryCount = photo_mainCommonDef::DEFAULT_CATEGORY_COUNT;
			$titleLength	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_TITLE_SHORT_LENGTH);// 画像タイトル文字数
			if (intval($titleLength) <= 0) $titleLength = photo_mainCommonDef::DEFAULT_PHOTO_TITLE_SHORT_LENGTH;
			$protectCopyright	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_IMAGE_PROTECT_COPYRIGHT);// 画像著作権保護
			$thumbnailCrop	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_THUMBNAIL_CROP);// 切り取りサムネール
			$thumbBgColor	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_THUMBNAIL_BG_COLOR);		// サムネール背景色
			$categoryPassword	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_PHOTO_CATEGORY_PASSWORD);		// 画像カテゴリーパスワード制限
			$onlineShop	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_ONLINE_SHOP);		// オンラインショップ機能
			$layoutViewDetail = self::$_mainDb->getConfig(photo_mainCommonDef::CF_LAYOUT_VIEW_DETAIL);		// コンテンツレイアウト(詳細表示)
			$outputHead = self::$_mainDb->getConfig(photo_mainCommonDef::CF_OUTPUT_HEAD);		// ヘッダ出力するかどうか
			$headViewDetail = self::$_mainDb->getConfig(photo_mainCommonDef::CF_HEAD_VIEW_DETAIL);		// ヘッダ出力(詳細表示)
				
			$usePhotoDate	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_DATE);				// 画像情報(撮影日)を使用
			$usePhotoLocation	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_LOCATION);			// 画像情報(撮影場所)を使用
			$usePhotoCamera	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_CAMERA);			// 画像情報(カメラ)を使用
			$usePhotoDescription = self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_DESCRIPTION);	// 画像情報(説明)を使用
			$usePhotoKeyword	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_KEYWORD);			// 画像情報(検索キーワード)を使用
			$usePhotoCategory	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_CATEGORY);		// 画像情報(カテゴリー)を使用
			$usePhotoRate	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_USE_PHOTO_RATE);				// 画像情報(評価)を使用
			$htmlPhotoDescription	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_HTML_PHOTO_DESCRIPTION);	// HTML形式の画像情報(説明)
				
			$imageSize			= self::$_mainDb->getConfig(photo_mainCommonDef::CF_IMAGE_SIZE);	// 公開画像サイズ
			if (empty($imageSize)) $imageSize = photo_mainCommonDef::DEFAULT_IMAGE_SIZE;
			$thumbnailSize	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_THUMBNAIL_SIZE);	// サムネール画像サイズ
			if (empty($thumbnailSize)) $thumbnailSize = photo_mainCommonDef::DEFAULT_THUMBNAIL_SIZE;
			$defaultImageSize	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_DEFAULT_IMAGE_SIZE);		// デフォルト公開画像サイズ
			if (empty($defaultImageSize)) $defaultImageSize = photo_mainCommonDef::DEFAULT_IMAGE_SIZE;
			$defaultThumbnailSize	= self::$_mainDb->getConfig(photo_mainCommonDef::CF_DEFAULT_THUMBNAIL_SIZE);		// デフォルトサムネール画像サイズ
			if (empty($defaultThumbnailSize)) $defaultThumbnailSize = photo_mainCommonDef::DEFAULT_THUMBNAIL_SIZE;
		}
		// ソートキー選択メニュー作成
		$this->createSortKeyMenu();
		
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "view_count", $listViewCount);// 画像一覧表示数
		if (empty($listViewOrder)){	// 順方向
			$this->tmpl->addVar("_widget", "view_order_dec_selected", 'selected');// 降順
		} else {
			$this->tmpl->addVar("_widget", "view_order_inc_selected", 'selected');// 昇順
		}
		$this->tmpl->addVar("_widget", "category_count", $categoryCount);// 画像カテゴリー数
		$this->tmpl->addVar("_widget", "title_length", $titleLength);// 画像タイトル文字数
		$checked = '';
		if (!empty($protectCopyright)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "protect_copyright", $checked);// 画像著作権保護
		$checked = '';
		if (!empty($thumbnailCrop)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "thumbnail_crop_checked", $checked);// 切り取りサムネール
		$this->tmpl->addVar("_widget", "bg_color", $thumbBgColor);// サムネール背景色
		$checked = '';
		if (!empty($categoryPassword)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "category_password_checked", $checked);// 画像カテゴリーパスワード制限
		$checked = '';
		if (!empty($onlineShop)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "online_shop_checked", $checked);// オンラインショップ連携
		$this->tmpl->addVar("_widget", "layout_view_detail", $layoutViewDetail);		// コンテンツレイアウト(詳細表示)
		$this->tmpl->addVar("_widget", "output_head_checked", $outputHead ? 'checked' : '');		// ヘッダ出力するかどうか
		$this->tmpl->addVar("_widget", "head_view_detail", $headViewDetail);		// ヘッダ出力(詳細表示)
		
		$this->tmpl->addVar("_widget", "use_photo_date_checked", $usePhotoDate ? 'checked' : '');				// 画像情報(撮影日)を使用
		$this->tmpl->addVar("_widget", "use_photo_location_checked", $usePhotoLocation ? 'checked' : '');			// 画像情報(撮影場所)を使用
		$this->tmpl->addVar("_widget", "use_photo_camera_checked", $usePhotoCamera ? 'checked' : '');			// 画像情報(カメラ)を使用
		$this->tmpl->addVar("_widget", "use_photo_description_checked", $usePhotoDescription ? 'checked' : '');	// 画像情報(説明)を使用
		$this->tmpl->addVar("_widget", "use_photo_keyword_checked", $usePhotoKeyword ? 'checked' : '');			// 画像情報(検索キーワード)を使用
		$this->tmpl->addVar("_widget", "use_photo_category_checked", $usePhotoCategory ? 'checked' : '');		// 画像情報(カテゴリー)を使用
		$this->tmpl->addVar("_widget", "use_photo_rate_checked", $usePhotoRate ? 'checked' : '');				// 画像情報(評価)を使用
		$this->tmpl->addVar("_widget", "html_photo_description_checked", $htmlPhotoDescription ? 'checked' : '');	// HTML形式の画像情報(説明)
		
		$this->tmpl->addVar("_widget", "image_size", $imageSize);// 公開画像サイズ
		$this->tmpl->addVar("_widget", "thumbnail_size", $thumbnailSize);// サムネール画像サイズ
		$this->tmpl->addVar("_widget", "default_image_size", $defaultImageSize);			// デフォルト公開画像サイズ
		$this->tmpl->addVar("_widget", "default_thumbnail_size", $defaultThumbnailSize);	// デフォルトサムネール画像サイズ
	}
	/**
	 * ソートキー選択メニュー作成
	 *
	 * @return なし
	 */
	function createSortKeyMenu()
	{
		for ($i = 0; $i < count($this->sortKeyTypeArray); $i++){
			$value = $this->sortKeyTypeArray[$i]['value'];
			$name = $this->sortKeyTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->sortKey) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ソートキーID
				'name'     => $name,			// 名前
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('item_sort_key_type_list', $row);
			$this->tmpl->parseTemplate('item_sort_key_type_list', 'a');
		}
	}
}
?>
