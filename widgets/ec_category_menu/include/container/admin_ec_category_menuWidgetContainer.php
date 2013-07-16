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
 * @version    SVN: $Id: admin_ec_category_menuWidgetContainer.php 2255 2009-08-26 14:53:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/ec_category_menuInfo.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/ec_category_menuDb.php');

class admin_ec_category_menuWidgetContainer extends BaseAdminWidgetContainer
{
	var $db;	// DB接続オブジェクト
	const DEFAULT_IMG_FILENAME = 'menu1.png';		// デフォルトファイル名
	const DEFAULT_LEVEL_COUNT = 3;		// デフォルト表示階層
	const THIS_WIDGET_ID = 'ec_category_menu';		// ウィジェットID
	const IMAGE_DIR = 'images';				// 画像ディレクトリ名
	const DEFAULT_MENU_TITLE = '商品カテゴリー';	// デフォルトメニュータイトル
			
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new ec_category_menuDb();
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
		return 'admin_menu.tmpl.html';
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
		global $gEnvManager;
		global $gInstanceManager;

		$userInfo		= $gEnvManager->getCurrentUserInfo();
		$filename = $request->trimValueOf('item_image');		// 画像ファイル名
		if (empty($filename)) $filename = self::DEFAULT_IMG_FILENAME;		// デフォルトファイル名	
		$levelCount = $request->trimValueOf('item_level_count');		// メニュー表示階層
		if (empty($levelCount)) $levelCount = self::DEFAULT_LEVEL_COUNT;		// デフォルト表示階層
		$color1 = $request->trimValueOf('item_color1');		// 文字色1
		$color2 = $request->trimValueOf('item_color2');		// 文字色2
		$color3 = $request->trimValueOf('item_color3');		// 文字色3
		$color4 = $request->trimValueOf('item_color4');		// 文字色4
		$imageMenu = ($request->trimValueOf('item_image_menu') == 'on') ? 1 : 0;	// 画像メニュー
		$title = $request->trimValueOf('item_title');		// メニュータイトル
		if (empty($title)) $title = self::DEFAULT_MENU_TITLE;			// メニュータイトル
				
		// 処理分岐
		$act = $request->trimValueOf('act');
		if ($act == 'select'){		// 画像選択の場合
		} else if ($act == 'update'){		// 項目更新の場合
			$menuInfo = new ec_category_menuInfo();
			$menuInfo->imageFilename = $filename;
			$menuInfo->levelCount = $levelCount;					// メニュー表示階層
			$menuInfo->fontColor1 = $color1;
			$menuInfo->fontColor2 = $color2;
			$menuInfo->fontColor3 = $color3;
			$menuInfo->fontColor4 = $color4;
			$menuInfo->useImageMenu = $imageMenu;		// 画像メニューを使用するかどうか
			$menuInfo->title = $title;			// メニュータイトル
			
			// システムDBでメニュー情報を保存
			$ret = $gInstanceManager->getSytemDbObject()->updateWidgetParam(self::THIS_WIDGET_ID, serialize($menuInfo), $userInfo->userId);
			if ($ret){		// データ更新成功のとき
				$this->setGuidanceMsg('データを更新しました');
			} else {
				$this->setAppErrorMsg('データ更新に失敗しました');
			}
		} else if ($act == 'upload'){		// ファイルアップロードの場合
			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])) {
				$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得
				
				// ファイルを保存するサーバディレクトリを指定
				//$file = tempnam("/tmp","upload_");
				//$save_dir = '/tmp/';
				$imageFilePath = $gEnvManager->getCurrentWidgetRootPath() . '/' . self::IMAGE_DIR. '/' . $uploadFilename;
		
				// アップされたテンポラリファイルを保存ディレクトリにコピー
				$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $imageFilePath);

				// ファイル削除
				//unlink($imageFilePath);
				if ($ret){
					$msg = 'ファイルのアップロードが完了しました(ファイル名：' . $uploadFilename . ')';
					$this->setGuidanceMsg($msg);
				} else {
					$this->setAppErrorMsg('ファイルのアップロードに失敗しました');
				}
			}
		} else {
			// ファイル名設定値を取得
			$serializedParam = $gInstanceManager->getSytemDbObject()->getWidgetParam(self::THIS_WIDGET_ID);
			if (!empty($serializedParam)){
				$menuInfo = unserialize($serializedParam);
				$filename = $menuInfo->imageFilename;
				if (!empty($menuInfo->imageFilename)) $filename = $menuInfo->imageFilename;
				if (!empty($menuInfo->levelCount)) $levelCount = $menuInfo->levelCount;
				if (!empty($menuInfo->fontColor1)) $color1 = $menuInfo->fontColor1;
				if (!empty($menuInfo->fontColor2)) $color2 = $menuInfo->fontColor2;
				if (!empty($menuInfo->fontColor3)) $color3 = $menuInfo->fontColor3;
				if (!empty($menuInfo->fontColor4)) $color4 = $menuInfo->fontColor4;
				if (!empty($menuInfo->useImageMenu)) $imageMenu = $menuInfo->useImageMenu;		// 画像メニューを使用するかどうか
				if (!empty($menuInfo->title)) $title = $menuInfo->title;			// メニュータイトル
			}
		}
		
		// メニュー画像メニューを作成
		// 画像ディレクトリチェック
		$searchPath = $gEnvManager->getCurrentWidgetRootPath() . '/' . self::IMAGE_DIR;
		if (is_dir($searchPath)){
			$dir = dir($searchPath);
			while (($file = $dir->read()) !== false){
				$filePath = $searchPath . '/' . $file;
				// ファイルかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)
					&& strncmp($file, '_', 1) != 0){		// 「_」で始まる名前のファイルは読み込まない
					
					$selected = '';
					if ($file == $filename){
						$selected = 'selected';
					}
					$row = array(
						'value'    => $file,			// ファイル名
						'name'     => $file,			// ファイル名
						'selected' => $selected														// 選択中かどうか
					);
					$this->tmpl->addVars('image_file_list', $row);
					$this->tmpl->parseTemplate('image_file_list', 'a');
				}
			}
			$dir->close();
		}
		// パラメータの埋め込み
		$this->tmpl->addVar("_widget", "level_count", $levelCount);		// メニュー階層
		$this->tmpl->addVar("_widget", "filename", $filename);		// メニュー画像
		$this->tmpl->addVar("_widget", "color1", $color1);		// 文字色1
		$this->tmpl->addVar("_widget", "color2", $color2);		// 文字色2
		$this->tmpl->addVar("_widget", "color3", $color3);		// 文字色3
		$this->tmpl->addVar("_widget", "color4", $color4);		// 文字色4
		$imageMenuStr = '';
		if ($imageMenu){
			$imageMenuStr = 'checked';
		}		
		$this->tmpl->addVar("_widget", "image_menu", $imageMenuStr);		// 画像メニューを使用するかどうか
		$this->tmpl->addVar("_widget", "title", $title);		// タイトル
		
		// プレビューイメージの設定
		$preview = $gEnvManager->getCurrentWidgetRootUrl() . '/' . self::IMAGE_DIR . '/' . $filename;
		$this->tmpl->addVar("_widget", "image", $this->getUrl($preview));
	}
}
?>
