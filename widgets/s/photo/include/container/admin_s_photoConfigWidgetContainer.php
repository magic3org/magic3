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
 * @version    SVN: $Id: admin_s_photoConfigWidgetContainer.php 4716 2012-02-26 02:19:15Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_s_photoBaseWidgetContainer.php');

class admin_s_photoConfigWidgetContainer extends admin_s_photoBaseWidgetContainer
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
		$titleLength = $request->trimValueOf('item_title_length');		// 画像タイトル文字数
		
		$imageSize	= $request->trimValueOf('item_image_size');		// 公開画像サイズ
		$thumbnailSize	= $request->trimValueOf('item_thumbnail_size');		// サムネール画像サイズ
		$defaultImageSize	= $request->trimValueOf('item_default_image_size');		// デフォルト公開画像サイズ
		$defaultThumbnailSize	= $request->trimValueOf('item_default_thumbnail_size');		// デフォルトサムネール画像サイズ
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($listViewCount, '画像一覧表示数');
			$this->checkNumeric($titleLength, '画像タイトル文字数');
			$this->checkNumeric($defaultImageSize, 'デフォルト公開画像サイズ');
			$this->checkNumeric($defaultThumbnailSize, 'デフォルトサムネール画像サイズ');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$isErr = false;
				
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(photoCommonDef::CF_PHOTO_LIST_ITEM_COUNT, $listViewCount)) $isErr = true;// 画像一覧表示数
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(photoCommonDef::CF_PHOTO_LIST_ORDER, $listViewOrder)) $isErr = true;// 画像一覧表示順
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(photoCommonDef::CF_PHOTO_LIST_SORT_KEY, $this->sortKey)) $isErr = true;// 画像一覧ソートキー
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(photoCommonDef::CF_PHOTO_TITLE_SHORT_LENGTH, $titleLength)) $isErr = true;// 画像タイトル文字数
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(photoCommonDef::CF_IMAGE_SIZE, $imageSize)) $isErr = true;	// 公開画像サイズ
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(photoCommonDef::CF_THUMBNAIL_SIZE, $thumbnailSize)) $isErr = true;	// サムネール画像サイズ
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(photoCommonDef::CF_DEFAULT_IMAGE_SIZE, $defaultImageSize)) $isErr = true;	// デフォルト公開画像サイズ
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(photoCommonDef::CF_DEFAULT_THUMBNAIL_SIZE, $defaultThumbnailSize)) $isErr = true;	// デフォルトサムネール画像サイズ
				}
				if ($isErr){
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				} else {
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				}
				// 値を再取得
				$listViewCount	= self::$_mainDb->getConfig(photoCommonDef::CF_PHOTO_LIST_ITEM_COUNT);		// 画像一覧表示数
				$listViewOrder	= self::$_mainDb->getConfig(photoCommonDef::CF_PHOTO_LIST_ORDER);			// 画像一覧表示順
				if (!in_array($listViewOrder, array('0', '1'))) $listViewOrder = photoCommonDef::DEFAULT_PHOTO_LIST_ORDER;		// デフォルトの画像一覧並び順(昇順)
				$this->sortKey	= self::$_mainDb->getConfig(photoCommonDef::CF_PHOTO_LIST_SORT_KEY);// 画像一覧ソートキー
				if (empty($this->sortKey)) $this->sortKey = photoCommonDef::DEFAULT_PHOTO_LIST_SORT_KEY;
				$titleLength	= self::$_mainDb->getConfig(photoCommonDef::CF_PHOTO_TITLE_SHORT_LENGTH);// 画像タイトル文字数
				$imageSize			= self::$_mainDb->getConfig(photoCommonDef::CF_IMAGE_SIZE);	// 公開画像サイズ
				$thumbnailSize		= self::$_mainDb->getConfig(photoCommonDef::CF_THUMBNAIL_SIZE);	// サムネール画像サイズ
				$defaultImageSize	= self::$_mainDb->getConfig(photoCommonDef::CF_DEFAULT_IMAGE_SIZE);		// デフォルト公開画像サイズ
				$defaultThumbnailSize	= self::$_mainDb->getConfig(photoCommonDef::CF_DEFAULT_THUMBNAIL_SIZE);		// デフォルトサムネール画像サイズ
			}
		} else {		// 初期表示の場合
			$listViewCount	= self::$_mainDb->getConfig(photoCommonDef::CF_PHOTO_LIST_ITEM_COUNT);// 画像一覧表示数
			if (intval($listViewCount) <= 0) $listViewCount = photoCommonDef::DEFAULT_PHOTO_LIST_VIEW_COUNT;
			$listViewOrder	= self::$_mainDb->getConfig(photoCommonDef::CF_PHOTO_LIST_ORDER);// 画像一覧表示順
			if (!in_array($listViewOrder, array('0', '1'))) $listViewOrder = photoCommonDef::DEFAULT_PHOTO_LIST_ORDER;		// デフォルトの画像一覧並び順(昇順)
			$this->sortKey	= self::$_mainDb->getConfig(photoCommonDef::CF_PHOTO_LIST_SORT_KEY);// 画像一覧ソートキー
			if (empty($this->sortKey)) $this->sortKey = photoCommonDef::DEFAULT_PHOTO_LIST_SORT_KEY;
			$titleLength	= self::$_mainDb->getConfig(photoCommonDef::CF_PHOTO_TITLE_SHORT_LENGTH);// 画像タイトル文字数
			if (intval($titleLength) <= 0) $titleLength = photoCommonDef::DEFAULT_PHOTO_TITLE_SHORT_LENGTH;
			$imageSize			= self::$_mainDb->getConfig(photoCommonDef::CF_IMAGE_SIZE);	// 公開画像サイズ
			if (empty($imageSize)) $imageSize = photoCommonDef::DEFAULT_IMAGE_SIZE;
			$thumbnailSize	= self::$_mainDb->getConfig(photoCommonDef::CF_THUMBNAIL_SIZE);	// サムネール画像サイズ
			if (empty($thumbnailSize)) $thumbnailSize = photoCommonDef::DEFAULT_THUMBNAIL_SIZE;
			$defaultImageSize	= self::$_mainDb->getConfig(photoCommonDef::CF_DEFAULT_IMAGE_SIZE);		// デフォルト公開画像サイズ
			if (empty($defaultImageSize)) $defaultImageSize = photoCommonDef::DEFAULT_IMAGE_SIZE;
			$defaultThumbnailSize	= self::$_mainDb->getConfig(photoCommonDef::CF_DEFAULT_THUMBNAIL_SIZE);		// デフォルトサムネール画像サイズ
			if (empty($defaultThumbnailSize)) $defaultThumbnailSize = photoCommonDef::DEFAULT_THUMBNAIL_SIZE;
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
		$this->tmpl->addVar("_widget", "title_length", $titleLength);// 画像タイトル文字数
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
