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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainTempBaseWidgetContainer.php');

class admin_mainTempgeneratecssWidgetContainer extends admin_mainTempBaseWidgetContainer
{
	private $templateId;				// テンプレートID
	
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
		$task = $request->trimValueOf('task');
		if ($task == self::TASK_TEMPGENERATECSS_DETAIL){		// 詳細画面
			return 'tempgeneratecss_detail.tmpl.html';
		} else {			// 一覧画面
			return 'tempgeneratecss.tmpl.html';
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
		$act = $request->trimValueOf('act');
		$task = $request->trimValueOf('task');
		$this->templateId = $request->trimValueOf(M3_REQUEST_PARAM_TEMPLATE_ID);		// テンプレートIDを取得

		// パラメータチェック
		if (empty($this->templateId)){
			$this->setAppErrorMsg('テンプレートIDが設定されていません');
			return;
		}

		switch ($task){
			case self::TASK_TEMPGENERATECSS:	// テンプレートCSS生成
			default:
				$this->createList($request);
				break;
			case self::TASK_TEMPGENERATECSS_DETAIL:// テンプレートCSS生成(詳細)
//				$this->createDetail($request);
				break;
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// 画像格納ディレクトリを取得
		$this->imageBasePath = $this->gEnv->getTemplatesPath() . '/' . $this->templateId . self::DEFAULT_IMAGE_DIR;
		if (!is_dir($this->imageBasePath)){
			$this->setAppErrorMsg('画像ディレクトリが見つかりません。パス=' . $this->imageBasePath);
			return;
		}
		
		// ファイル一覧を作成
		$this->createFileList($path);
	}
	/**
	 * ファイル一覧を作成
	 *
	 * @param string $path		パス
	 * @return なし
	 */
	function createFileList($path)
	{
		// ファイル一覧取得
		$fileList = $this->getFileList($path);
				
		$index = 0;			// インデックス番号
		for ($i = $startNo -1; $i < $endNo; $i++){
			$filePath = $fileList[$i];
			$relativeFilePath = substr($filePath, strlen($this->imageBasePath));

			$filePathArray = explode('/', $filePath);		// pathinfo,basenameは日本語処理できないので日本語対応
			$file = end($filePathArray);		// ファイル名
			$size = '';				// ファイルサイズ
			$fileLink = '';
			$checkDisabled = '';		// チェックボックス使用制御
			$imageSizeStr = '';
			if (is_dir($filePath)){			// ディレクトリのとき
				// アイコン作成
				$iconUrl = $this->gEnv->getRootUrl() . self::FOLDER_ICON_FILE;
				$iconTitle = $this->convertToDispString($file);
				$pageUrl = '?task=' . self::TASK_TEMPIMAGE . '&' . M3_REQUEST_PARAM_TEMPLATE_ID . '=' . $this->templateId . '&path=' . $relativeFilePath;
				$iconTag = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($pageUrl)) . '">';
				$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
				$iconTag .= '</a>';
			
				$fileLink = '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($pageUrl)) . '">' . $this->convertToDispString($file) . '</a>';
				
				// ファイルまたはディレクトリがないときは削除可能
				$files = getFileList($filePath);
				if (count($files) > 0){
					$checkDisabled = 'disabled ';		// チェックボックス使用制御
				}
				$serial = -1;
			} else {		// ファイルのとき
				// 画像情報を取得
				$isImageFile = false;
				$imageSize = @getimagesize($filePath);
				if ($imageSize){
					$imageWidth = $imageSize[0];
					$imageHeight = $imageSize[1];
					$imageType = $imageSize[2];
					$imageMimeType = $imageSize['mime'];	// ファイルタイプを取得

					// 処理可能な画像ファイルタイプかどうか
					if (in_array($imageMimeType, $this->permitMimeType)){
						$isImageFile = true;
						$imageSizeStr = $imageWidth . 'x' . $imageHeight;
					}
				}

				// ファイル削除用チェックボックス
				if (!is_writable($filePath)) $checkDisabled = 'disabled ';		// チェックボックス使用制御
				
				// アイコン作成
				$iconTitle = $this->convertToDispString($file);
				if ($isImageFile){				// 画像ファイルの場合
					// 画像サイズが指定サイズより大きい場合はサムネール画像を作成
					if ($imageWidth > self::LIST_ICON_SIZE || $imageHeight > self::LIST_ICON_SIZE){
						$thumbPath = $this->cacheDir . $relativeFilePath;		// サムネール画像パス
						
						// サムネール画像が存在しない場合は作成
						if (file_exists($thumbPath)){
							$imageSize = @getimagesize($thumbPath);
							if ($imageSize){
								$imageWidth = $imageSize[0];
								$imageHeight = $imageSize[1];
								$imageType = $imageSize[2];
								$imageMimeType = $imageSize['mime'];	// ファイルタイプを取得
							}
							$iconUrl = $this->gEnv->getUrlToPath($thumbPath);
						} else {
							// ディレクトリ作成
							$ret = true;
							$thumbDir = dirname($thumbPath);
							if (!is_dir($thumbDir)) $ret = mkdir($thumbDir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
							
							if ($ret) $ret = $this->gInstance->getImageManager()->createImage($filePath, $thumbPath, self::LIST_ICON_SIZE, $imageType, $destSize);
							if ($ret){
								$imageWidth = $destSize['width'];
								$imageHeight = $destSize['height'];
								$iconUrl = $this->gEnv->getUrlToPath($thumbPath);
							} else {
								$this->setAppErrorMsg('サムネール画像が作成できません。画像ファイル=' . $filePath);
							
								$imageWidth = '';
								$imageHeight = '';
							}
						}
					} else {		// 画像サイズが範囲内の場合はそのまま表示
						$iconUrl = $this->gEnv->getUrlToPath($filePath);
					}
					
					$iconTag = '<a href="#" onclick="editItemByFilename(\'' . addslashes($file) . '\');return false;">';
					$iconTag .= '<img src="' . $this->getUrl($iconUrl) . '?' . date('YmdHis') . '" width="' . $imageWidth . '" height="' . $imageHeight . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
					$iconTag .= '</a>';
					
					$fileLink = '<a href="#" onclick="editItemByFilename(\'' . addslashes($file) . '\');return false;">' . $this->convertToDispString($file) . '</a>';
				} else {	// 画像ファイル以外のとき
					// 画像ファイル以外の場合は詳細画面へ遷移しない
					$iconUrl = $this->gEnv->getRootUrl() . self::FILE_ICON_FILE;
					$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';

					$fileLink = $this->convertToDispString($file);
				}
				
				$size = filesize($filePath);
			}
	
			// ファイル更新日時
			$updateDate = date('Y/m/d H:i:s', filemtime($filePath));
			
			$row = array(
				'serial'		=> $serial,
				'index'			=> $index,			// インデックス番号(チェックボックス識別)
				'icon'			=> $iconTag,		// アイコン
				'name'			=> $this->convertToDispString($file),			// ファイル名
				'filename'    	=> $fileLink,			// ファイル名
				'image_size'	=> $imageSizeStr,		// 画像サイズ
				'size'     		=> $size,			// ファイルサイズ
				'date'    		=> $updateDate,			// 更新日時
				'check_disabled'	=> $checkDisabled,		// チェックボックス使用制御
			);
			$this->tmpl->addVars('file_list', $row);
			$this->tmpl->parseTemplate('file_list', 'a');
			
			// インデックス番号を保存
			$this->fileArray[] = $this->convertToDispString($file);			// ファイル名
			$index++;
		}
	}
	/**
	 * ファイル一覧を作成
	 *
	 * @param string $path	ディレクトリパス
	 * @return array		ファイルパスのリスト
	 */
	function getFileList($path)
	{
		$fileList = array();
		
		// 引数エラーチェック
		if (!is_dir($path)) return $fileList;
		
		$dir = dir($path);
		while (($file = $dir->read()) !== false){
			$filePath = $path . DIRECTORY_SEPARATOR . $file;
			// カレントディレクトリかどうかチェック
			if ($file != '.' && $file != '..') $fileList[] = $filePath;
		}
		$dir->close();
		
		// アルファベット順にソート
		usort($fileList, array($this, 'sortOrderByAlphabet'));
		
		return $fileList;
	}
	/**
	 * ファイルをアルファベットで昇順にソートする。ディレクトリは先頭。
	 *
	 * @param string  	$path1			比較するパス1
	 * @param string	$path2			比較するパス2
	 * @return int						同じとき0、$path1が$path2より大きいとき1,$path1が$path2より小さいとき-1を返す
	 */
	function sortOrderByAlphabet($path1, $path2)
	{
		// ディレクトリは常に先頭に表示
		if (is_dir($path1)){			// ディレクトリのとき
			if (!is_dir($path2)) return -1; // ファイルのとき
		} else {
			if (is_dir($path2)) return 1;	// ディレクトリのとき
		}
		
		return strcasecmp($path1, $path2);
	}
}
?>
