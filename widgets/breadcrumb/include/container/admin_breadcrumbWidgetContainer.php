<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    パンくずリスト
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2012-2015 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_breadcrumbWidgetContainer extends BaseAdminWidgetContainer
{
	private $permitMimeType;			// アップロードを許可する画像タイプ
	const DEFAULT_ARROW_IMAGE_FILE = '/images/arrow.png';		// パンくずリスト用画像
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->permitMimeType = array(	image_type_to_mime_type(IMAGETYPE_GIF),
										image_type_to_mime_type(IMAGETYPE_JPEG),
										image_type_to_mime_type(IMAGETYPE_PNG),
										image_type_to_mime_type(IMAGETYPE_BMP));			// アップロードを許可する画像タイプ
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
		$act = $request->trimValueOf('act');
		
		// 入力値を取得
		$visibleOnRoot = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// トップページでリスト表示するかどうか
		$useHiddenMenu = ($request->trimValueOf('item_use_hidden_menu') == 'on') ? 1 : 0;		// 非表示のメニューウィジェットの定義を使用する
		$separatorImgPath = $request->trimValueOf('item_separator_image_path');		// 区切り画像パス
			
		if ($act == 'update'){		// 設定更新のとき
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->visibleOnRoot	= $visibleOnRoot;
				$paramObj->useHiddenMenu	= $useHiddenMenu;
				$paramObj->separatorImgPath	= $separatorImgPath;		// 区切り画像パス
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == 'upload'){		// 画像アップロードのとき
			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])){
				// テンポラリディレクトリの書き込み権限をチェック
				if (!is_writable($this->gEnv->getWorkDirPath())){
					$msg = '一時ディレクトリに書き込み権限がありません。ディレクトリ：' . $this->gEnv->getWorkDirPath();
					$this->setAppErrorMsg($msg);
				}
				
				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					// ファイルを保存するサーバディレクトリを指定
					$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
		
					// アップされたテンポラリファイルを保存ディレクトリにコピー
					$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
					if ($ret){
						// ファイルの内容のチェック
						$imageSize = @getimagesize($tmpFile);// 画像情報を取得
						if ($imageSize){
							$imageWidth = $imageSize[0];
							$imageHeight = $imageSize[1];
							$imageType = $imageSize[2];
							$imageMimeType = $imageSize['mime'];	// ファイルタイプを取得

							// 受付可能なファイルタイプかどうか
							if (!in_array($imageMimeType, $this->permitMimeType)){
								$msg = 'アップロード画像のタイプが不正です。';
								$this->setAppErrorMsg($msg);
							}
						} else {
							$msg = 'アップロード画像が不正です。';
							$this->setAppErrorMsg($msg);
						}
				
						if ($this->getMsgCount() == 0){		// エラーが発生していないとき
							// 画像をコピー
							$newImgPath = $this->gEnv->getResourcePath() . DIRECTORY_SEPARATOR . 'widgets/' . $this->gEnv->getCurrentWidgetId() . self::DEFAULT_ARROW_IMAGE_FILE;
							$newImgDir = dirname($newImgPath);
							if (!file_exists($newImgDir)) mkdir($newImgDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);		// ディレクトリ作成
							$ret = copy($tmpFile, $newImgPath);
							if ($ret){
								$separatorImgPath = str_replace($this->gEnv->getSystemRootPath(), '', $newImgPath);		// 区切り画像パス
								
								$msg = '画像を変更しました';
								$this->setGuidanceMsg($msg);
							}
						}
					} else {
						$msg = 'ファイルのアップロードに失敗しました';
						$this->setAppErrorMsg($msg);
					}
					// テンポラリファイル削除
					unlink($tmpFile);
				}
			} else {
				$msg = sprintf($this->_('Uploded file not found. (detail: The file may be over maximum size to be allowed to upload. Size %s bytes.'), $this->gSystem->getMaxFileSizeForUpload());	// アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります。%dバイト)
				$this->setAppErrorMsg($msg);
			}
		} else {		// 初期表示の場合
			// デフォルト値設定
			$visibleOnRoot = 1;				// トップページでリスト表示するかどうか
			$useHiddenMenu = 0;		// 非表示のメニューウィジェットの定義を使用する
			$separatorImgPath = '';		// 区切り画像パス
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$visibleOnRoot	= $paramObj->visibleOnRoot;
				$useHiddenMenu	= $paramObj->useHiddenMenu;
				$separatorImgPath = $paramObj->separatorImgPath;		// 区切り画像パス
			}
		}

		// 区切り画像
		if (empty($separatorImgPath)){
			$separatorImgUrl = $this->gEnv->getCurrentWidgetRootUrl() . self::DEFAULT_ARROW_IMAGE_FILE . '?' . date('YmdHis');		// デフォルトの画像
		} else {
			$separatorImgUrl = $this->gEnv->getRootUrl() . $separatorImgPath . '?' . date('YmdHis');		// ユーザ指定の画像
		}
		$separatorImg = '<img src="' . $this->convertUrlToHtmlEntity($this->getUrl($separatorImgUrl)) . '" />';
		$this->tmpl->addVar("_widget", "separator_image", $separatorImg);
		
		// 画面にデータを埋め込む
		$checked = '';
		if ($visibleOnRoot) $checked = 'checked';
		$this->tmpl->addVar("_widget", "visible", $checked);
		$checked = '';
		if ($useHiddenMenu) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_hidden_menu", $checked);
		$this->tmpl->addVar("_widget", "separator_image_path", $separatorImgPath);
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
}
?>
