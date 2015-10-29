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
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainTest_imageWidgetContainer extends admin_mainBaseWidgetContainer
{
	const DEFAULT_IMAGE_FILENAME_HEAD = 'default';		// デフォルトの画像ファイル名ヘッダ

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
		return 'test/test_image.tmpl.html';
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
		$format = 'sm=logo_80c.png';
		$ret = preg_match('/(.*?)\s*=\s*(.*?)_(\d+)(.*)\.(gif|png|jpg|jpeg|bmp)$/i', $format, $matches);
		if ($ret){
			$imageType = $matches[1];
			$name = $matches[2];
			$size = $matches[3];
			$type = $size . strtolower($matches[4]);
			$ext = strtolower($matches[5]);
		}
//		echo 'imagetype=[' . $imageType . '] name=[' . $name . '] size=[' . $size . '] type=[' . $type . '] ext=[' . $ext . ']';


		$this->createpImageList();
		
		$path = $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_SEARCH, 0/*PC用*/, ''/*ディレクトリ取得*/);
		$this->tmpl->addVar('_widget', 'path', $path);
	}
	/**
	 * 画像一覧作成
	 *
	 * @return なし
	 */
	function createpImageList()
	{
		$formats = $this->gInstance->getImageManager()->getSystemThumbFormat(1/*クロップ画像のみ*/);
		
		for ($i = 0; $i < count($formats); $i++){
			$format = $formats[$i];
			$imageUrl = $this->getImageUrl($format, $filename);
			
			$row = array(
				'image_url'			=> $imageUrl . '?' . date('YmdHis'),				// 画像URL
				'title'				=> $filename
			);
			$this->tmpl->addVars('image_list', $row);
			$this->tmpl->parseTemplate('image_list', 'a');
		}
	}
	/**
	 * 画像のURLを取得
	 *
	 * @param string $format		画像フォーマット
	 * @param string $filename		ファイル名
	 * @return string				URL
	 */
	function getImageUrl($format, &$filename = null)
	{
		$filename = $this->gInstance->getImageManager()->getThumbFilename(self::DEFAULT_IMAGE_FILENAME_HEAD, $format);
		$url = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_SEARCH, 0/*PC用*/, $filename);
		return $url;
	}
	/**
	 * アップロードファイルから各種画像を作成
	 *
	 * @param bool           $isSuccess		アップロード成功かどうか
	 * @param object         $resultObj		アップロード処理結果オブジェクト
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $filePath		アップロードされたファイル
	 * @param string         $destDir		アップロード先ディレクトリ
	 * @return								なし
	 */
	function uploadFile($isSuccess, &$resultObj, $request, $filePath, $destDir)
	{
		$type = $request->trimValueOf('type');		// 画像タイプ
		
		if ($isSuccess){		// ファイルアップロード成功のとき
			// 各種画像を作成
			switch ($type){
			case self::IMAGE_TYPE_ENTRY_IMAGE:			// 記事デフォルト画像
				$formats = $this->gInstance->getImageManager()->getSystemThumbFormat();
				$filenameBase = self::DEFAULT_IMAGE_FILENAME_HEAD;
				break;
			}

			$ret = $this->gInstance->getImageManager()->createImageByFormat($filePath, $formats, $destDir, $filenameBase, $destFilename);
			if ($ret){			// 画像作成成功の場合
				// 画像参照用URL
				$imageUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
				$imageUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
//				$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIG;
				$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_GET_IMAGE;
				$imageUrl .= '&type=' . $type . '&' . date('YmdHis');
				$resultObj['url'] = $imageUrl;
			} else {// エラーの場合
				$resultObj = array('error' => 'Could not create resized images.');
			}
		}
	}
}
?>
