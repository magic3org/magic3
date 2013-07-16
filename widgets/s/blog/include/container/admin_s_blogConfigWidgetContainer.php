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
 * @version    SVN: $Id: admin_s_blogConfigWidgetContainer.php 4618 2012-01-26 04:28:58Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_s_blogBaseWidgetContainer.php');

class admin_s_blogConfigWidgetContainer extends admin_s_blogBaseWidgetContainer
{
	const DEFAULT_VIEW_COUNT	= 10;				// デフォルトの表示記事数
		
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
		$entryViewCount 	= $request->trimValueOf('entry_view_count');		// 記事表示数
		$entryViewOrder 	= $request->trimValueOf('entry_view_order');		// 記事表示順
		$topContent 		= $request->valueOf('top_content');	// トップコンテンツ
		$jqueryViewStyle	= ($request->trimValueOf('item_jquery_view_style') == 'on') ? 1 : 0;			// jQueryMobile用のフォーマットで表示するかどうか
		$imageMaxSize		= $request->trimValueOf('item_image_max_size');		// 画像の自動変換最大サイズ
		$useTitleListImage	= ($request->trimValueOf('item_use_title_list_image') == 'on') ? 1 : 0;			// タイトルリスト画像を使用するかどうか
		$titleListImageUrl 	= $request->trimValueOf('item_title_list_image_url');							// 画像へのパス
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($entryViewCount, '記事表示順');
			$this->checkNumeric($imageMaxSize, '自動変換画像サイズ(最大値)');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// パスをマクロ形式に変換
				if (!empty($titleListImageUrl)) $titleListImageUrl = $this->gEnv->getMacroPath($titleListImageUrl);
				
				$isErr = false;
				
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(s_blogCommonDef::CF_ENTRY_VIEW_COUNT, $entryViewCount)) $isErr = true;// 記事表示数
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(s_blogCommonDef::CF_ENTRY_VIEW_ORDER, $entryViewOrder)) $isErr = true;// 記事表示順
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(s_blogCommonDef::CF_AUTO_RESIZE_IMAGE_MAX_SIZE, $imageMaxSize)) $isErr = true;// 画像の自動変換最大サイズ
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(s_blogCommonDef::CF_TOP_CONTENT, $topContent)) $isErr = true;// トップコンテンツ
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(s_blogCommonDef::CF_JQUERY_VIEW_STYLE, $jqueryViewStyle)) $isErr = true;	// jQueryMobile用のフォーマットで表示するかどうか
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(s_blogCommonDef::CF_USE_TITLE_LIST_IMAGE, $useTitleListImage)) $isErr = true;// タイトルリスト画像を使用するかどうか
				}
				if (!$isErr){
					if (!self::$_mainDb->updateConfig(s_blogCommonDef::CF_TITLE_LIST_IMAGE, $titleListImageUrl)) $isErr = true;// タイトルリスト画像
				}
				if ($isErr){
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				} else {
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				}
				// 値を再取得
				$entryViewCount	= self::$_mainDb->getConfig(s_blogCommonDef::CF_ENTRY_VIEW_COUNT);// 記事表示数
				$entryViewOrder	= self::$_mainDb->getConfig(s_blogCommonDef::CF_ENTRY_VIEW_ORDER);// 記事表示順
				$imageMaxSize	= self::$_mainDb->getConfig(s_blogCommonDef::CF_AUTO_RESIZE_IMAGE_MAX_SIZE);// 画像の自動変換最大サイズ
				$topContent = self::$_mainDb->getConfig(s_blogCommonDef::CF_TOP_CONTENT);// トップコンテンツ
				$jqueryViewStyle = self::$_mainDb->getConfig(s_blogCommonDef::CF_JQUERY_VIEW_STYLE);	// jQueryMobile用のフォーマットで表示するかどうか
				$useTitleListImage = self::$_mainDb->getConfig(s_blogCommonDef::CF_USE_TITLE_LIST_IMAGE);	// タイトルリスト画像を使用するかどうか
				$titleListImageUrl = self::$_mainDb->getConfig(s_blogCommonDef::CF_TITLE_LIST_IMAGE);	// タイトルリスト画像
			}
		} else {		// 初期表示の場合
			$entryViewCount	= self::$_mainDb->getConfig(s_blogCommonDef::CF_ENTRY_VIEW_COUNT);// 記事表示数
			if (empty($entryViewCount)) $entryViewCount = self::DEFAULT_VIEW_COUNT;
			$entryViewOrder	= self::$_mainDb->getConfig(s_blogCommonDef::CF_ENTRY_VIEW_ORDER);// 記事表示順
			$topContent = self::$_mainDb->getConfig(s_blogCommonDef::CF_TOP_CONTENT);// トップコンテンツ
			$imageMaxSize	= self::$_mainDb->getConfig(s_blogCommonDef::CF_AUTO_RESIZE_IMAGE_MAX_SIZE);// 画像の自動変換最大サイズ
			$jqueryViewStyle = self::$_mainDb->getConfig(s_blogCommonDef::CF_JQUERY_VIEW_STYLE);	// jQueryMobile用のフォーマットで表示するかどうか
			$useTitleListImage = self::$_mainDb->getConfig(s_blogCommonDef::CF_USE_TITLE_LIST_IMAGE);	// タイトルリスト画像を使用するかどうか
			$titleListImageUrl = self::$_mainDb->getConfig(s_blogCommonDef::CF_TITLE_LIST_IMAGE);	// タイトルリスト画像
		}
		// 画像のパスを修正
		if (empty($titleListImageUrl)){		// 設定されていない場合はデフォルト画像
			$titleListImageUrl = $this->gEnv->getCurrentWidgetImagesUrl() . '/' . s_blogCommonDef::DEFAULT_TITLE_LIST_IMAGE;
			$titleListImageValue = '';
		} else {
			$titleListImageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $titleListImageUrl);
			$titleListImageValue = $titleListImageUrl;
		}

		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "view_count", $entryViewCount);// 記事表示数
		if (empty($entryViewOrder)){	// 順方向
			$this->tmpl->addVar("_widget", "view_order_inc_selected", 'selected');// 記事表示順
		} else {
			$this->tmpl->addVar("_widget", "view_order_dec_selected", 'selected');// 記事表示順
		}
		$this->tmpl->addVar("_widget", "image_max_size", $imageMaxSize);// 画像の自動変換最大サイズ
		$this->tmpl->addVar("_widget", "top_content", $topContent);		// マルチブログ時のトップコンテンツ
		$checked = '';
		if (!empty($jqueryViewStyle)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "jquery_view_style_checked", $checked);// jQueryMobile用のフォーマットで表示するかどうか
		$checked = '';
		if (!empty($useTitleListImage)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_title_list_image_checked", $checked);		// タイトルリスト画像を使用するかどうか
		$this->tmpl->addVar("_widget", "title_list_image_url",	$this->convertToDispString($titleListImageValue));		// タイトルリストデフォルト画像
		
		// プレビュー作成
		$destImg = '<img id="preview_img" src="' . $this->getUrl($titleListImageUrl) . '" />';
		$this->tmpl->addVar("_widget", "title_list_image", $destImg);
	}
}
?>
