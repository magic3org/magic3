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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');

class admin_blog_mainImageWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	private $tmpDir;		// 作業ディレクトリ
	const CREATE_EYECATCH_TAG_ID = 'createeyecatch';			// アイキャッチ画像作成ボタンタグID
	const ACT_CREATE_IMAGE	= 'createimage';	// 画像作成
	const ACT_GET_IMAGE		= 'getimage';		// 画像取得
	
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
		return 'admin_image.tmpl.html';
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
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		$act = $request->trimValueOf('act');
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == self::ACT_CREATE_IMAGE){		// 画像作成
			$this->createCropImage($request);
			return;
		} else if ($act == self::ACT_GET_IMAGE){			// 画像取得
			// Ajaxでの画像取得
			$this->getImageByType($request);
			return;
		} else {
			$replaceNew = true;		// データを再取得
			
			// 作業ディレクトリを削除
			$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
			rmDirectory($tmpDir);
		}
		
		$ret = self::$_mainDb->getEntryItem($entryId, $langId, $row);
		if ($ret){
			$html		= $row['be_html'];				// HTML
			$html2		= $row['be_html_ext'];			// HTML続き
			
			// 最大サイズのアイキャッチ画像を取得
			$eyecatchUrl = blog_mainCommonDef::getEyecatchImageUrl($row['be_thumb_filename'], self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE]);
			
			// アイキャッチ変更用ダイアログのデフォルト画像を取得
			if (empty($row['be_thumb_filename'])){
				$defaultEyecatchUrl = $eyecatchUrl;
			} else {		// 画像が作成されているとき
				// アイキャッチを作成したソース画像を取得
				$defaultEyecatchPath = $this->gInstance->getImageManager()->getFirstImagePath($html);
				if (empty($defaultEyecatchPath) && !empty($html2)) $defaultEyecatchPath = $this->gInstance->getImageManager()->getFirstImagePath($html2);		// 本文1に画像がないときは本文2を検索
				if (empty($defaultEyecatchPath)){		// 画像が見つからないとき
					$defaultEyecatchUrl = blog_mainCommonDef::getEyecatchImageUrl(''/*画像なし*/, self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE]);
				} else {
					$defaultEyecatchUrl = $this->gEnv->getUrlToPath($defaultEyecatchPath);		// URLに変換
				}
			}
			$defaultEyecatchUrl .= '?' . date('YmdHis');
		}
		
		// アイキャッチ画像変更ボタン
		$createEyecatchButton = $this->gDesign->createEditButton(''/*同画面*/, '画像を作成', self::CREATE_EYECATCH_TAG_ID);
		$this->tmpl->addVar("_widget", "create_eyecatch_button", $createEyecatchButton);
		$this->tmpl->addVar("_widget", "tagid_create_eyecatch", self::CREATE_EYECATCH_TAG_ID);		// 画像作成タグ
		
		$this->tmpl->addVar("_widget", "eyecatch_url", $this->convertUrlToHtmlEntity($this->getUrl($eyecatchUrl . '?' . date('YmdHis'))));
//		$this->tmpl->addVar("_widget", "default_eyecatch_url", $this->convertUrlToHtmlEntity($this->getUrl($defaultEyecatchUrl)));		// デフォルトのアイキャッチ画像
//		$this->tmpl->addVar("_widget", "sitelogo_updated", $updateStatus);
		$this->tmpl->addVar("_widget", "eyecatch_size", $imageSize . 'x' . $imageSize);
		$this->tmpl->addVar("_widget", "entry_id", $entryId);
		$this->tmpl->addVar("_widget", "target_widget", $this->gEnv->getCurrentWidgetId());// 変更画像送信用
	}
	/**
	 * クロップ画像を作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function createCropImage($request)
	{
		$entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		$type = $request->trimValueOf('type');		// 画像タイプ
		$src = $request->trimValueOf('src');	// 画像URL
		$x = $request->trimValueOf('x');
		$y = $request->trimValueOf('y');
		$w = $request->trimValueOf('w');
		$h = $request->trimValueOf('h');
		
		// 引数エラーチェック
		$entryId = intval($entryId);
		if (empty($entryId)) return false;
		
		// 作業ディレクトリを作成
		$tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
		
		// ソース画像パスを取得
		$srcPath = $this->gEnv->getAbsolutePath($this->gEnv->getDocumentRootUrl() . $src);
		
		// 画像ファイル名、フォーマット取得
		list($filenames, $formats) = $this->gInstance->getImageManager()->getSystemDefaultThumbFilename($entryId, 1/*クロップ画像のみ*/);
		
		// クロップ画像を作成
		for ($i = 0; $i < count($formats); $i++){
			$format = $formats[$i];
			
			// フォーマット情報を取得
			$ret = $this->gInstance->getImageManager()->parseImageFormat($format, $imageType, $imageAttr, $imageSize);
			if ($ret){
				// 画像作成
				$destPath = $tmpDir . DIRECTORY_SEPARATOR . $filenames[$i];
				$ret = $this->gInstance->getImageManager()->createCropImage($srcPath, $x, $y, $w, $h, $destPath, $imageSize, $imageSize);
				if (!$ret) return false;
			} else {
				break;
			}
		}
				// 画像参照用URL
		$imageUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$imageUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
		$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_IMAGE;
		$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_GET_IMAGE;
		$imageUrl .= '&' . M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $entryId;
//		$imageUrl .= '&type=' . $type;
		$imageUrl .= '&' . date('YmdHis');
		$this->gInstance->getAjaxManager()->addData('url', $imageUrl);
	}
	/**
	 * 最大画像を取得
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return					なし
	 */
	function getImageByType($request)
	{
		$entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		$type = $request->trimValueOf('type');		// 画像タイプ
		
		// 作業ディレクトリを取得
		$tmpDir = $this->gEnv->getTempDirBySession();
		
		// 画像ファイル名、フォーマット取得
		list($filenames, $formats) = $this->gInstance->getImageManager()->getSystemDefaultThumbFilename($entryId, 1/*クロップ画像のみ*/);
		
		$imagePath = '';
		$filename = $filenames[count($filenames) -1];
		if (!empty($filename)) $imagePath = $this->gEnv->getTempDirBySession() . '/' . $filename;
			
		// ページ作成処理中断
		$this->gPage->abortPage();

		if (is_readable($imagePath)){
			// 画像情報を取得
			$imageMimeType = '';
			$imageSize = @getimagesize($imagePath);
			if ($imageSize) $imageMimeType = $imageSize['mime'];	// ファイルタイプを取得
			
			// 画像MIMEタイプ設定
			if (!empty($imageMimeType)) header('Content-type: ' . $imageMimeType);
			
			// キャッシュの設定
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');// 過去の日付
			header('Cache-Control: no-store, no-cache, must-revalidate');// HTTP/1.1
			header('Cache-Control: post-check=0, pre-check=0');
			header('Pragma: no-cache');
		
			// 画像ファイル読み込み
			readfile($imagePath);
		} else {
			$this->gPage->showError(404);
		}
	
		// システム強制終了
		$this->gPage->exitSystem();
	}
}
?>
