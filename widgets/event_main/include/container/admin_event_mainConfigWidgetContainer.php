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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_event_mainBaseWidgetContainer.php');

class admin_event_mainConfigWidgetContainer extends admin_event_mainBaseWidgetContainer
{
	private $tmpDir;		// 作業ディレクトリ
	const IMAGE_TYPE_ENTRY_IMAGE = 'entryimage';			// 画像タイプ(記事デフォルト画像)
	const ACT_UPLOAD_IMAGE	= 'uploadimage';			// 画像アップロード
	const ACT_GET_IMAGE		= 'getimage';		// 画像取得
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 作業ディレクトリを取得
		$this->tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリパスを取得
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
		$entryViewCount = $request->trimValueOf('entry_view_count');		// 記事表示数
		$entryViewOrder = $request->trimValueOf('entry_view_order');		// 記事表示順
		$categoryCount = $request->trimValueOf('category_count');		// カテゴリ数
		$useCalendar	= $request->trimCheckedValueOf('item_use_calendar');	// カレンダーを使用するかどうか
		$topContents = $request->valueOf('top_contents');	// トップコンテンツ
		$layoutEntrySingle	= $request->valueOf('item_layout_entry_single');					// コンテンツレイアウト(記事詳細)
		$layoutEntryList	= $request->valueOf('item_layout_entry_list');					// コンテンツレイアウト(記事一覧)
		$outputHead			= $request->trimCheckedValueOf('item_output_head');		// ヘッダ出力するかどうか
		$headViewDetail		= $request->valueOf('item_head_view_detail');					// ヘッダ出力(詳細表示)
		$useWidgetTitle 	= $request->trimCheckedValueOf('item_use_widget_title');		// ウィジェットタイトルを使用するかどうか
		$titleDefault		= $request->trimValueOf('item_title_default');		// デフォルトタイトル
		$titleList			= $request->trimValueOf('item_title_list');		// 一覧タイトル
		$titleSearchList	= $request->trimValueOf('item_title_search_list');		// 検索結果タイトル
		$titleNoEntry		= $request->trimValueOf('item_title_no_entry');		// 記事なし時タイトル
		$messageNoEntry		= $request->trimValueOf('item_message_no_entry');		// 記事が登録されていないメッセージ
		$messageFindNoEntry = $request->trimValueOf('item_message_find_no_entry');		// 記事が見つからないメッセージ
		$msgNoEntryInFuture = $request->trimValueOf('item_msg_no_entry_in_future');	// 予定イベントなし時メッセージ
		$titleTagLevel		= $request->trimIntValueOf('item_title_tag_level', event_mainCommonDef::DEFAULT_TITLE_TAG_LEVEL);		// タイトルタグレベル
		$imageType			= $request->trimValueOf('type');		// 画像タイプ
		$updatedEntryImage	= $request->trimValueOf('updated_entryimage');		// 記事デフォルト画像更新フラグ
		
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($entryViewCount, '記事表示順');
			
			// 記事デフォルト画像のエラーチェック
			if (!empty($updatedEntryImage)){
				list($entryImageFilenameArray, $tmpArray) = $this->gInstance->getImageManager()->getSystemThumbFilename('0'/*デフォルト画像*/);
				for ($i = 0; $i < count($entryImageFilenameArray); $i++){
					$path = $this->tmpDir . DIRECTORY_SEPARATOR . $entryImageFilenameArray[$i];
					if (!file_exists($path)){
						$this->setAppErrorMsg('記事デフォルト画像が正常にアップロードされていません');
						break;
					}
				}
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 空の場合はデフォルト値を設定
				if (empty($titleList)) $titleList = event_mainCommonDef::DEFAULT_TITLE_LIST;				// 一覧タイトル
				if (empty($titleSearchList)) $titleSearchList = event_mainCommonDef::DEFAULT_TITLE_SEARCH_LIST;// 検索結果タイトル
				if (empty($titleNoEntry)) $titleNoEntry = event_mainCommonDef::DEFAULT_TITLE_NO_ENTRY;	// 記事なし時タイトル
				if (empty($messageNoEntry)) $messageNoEntry = event_mainCommonDef::DEFAULT_MESSAGE_NO_ENTRY;// 記事が登録されていないメッセージ
				if (empty($messageFindNoEntry)) $messageFindNoEntry = event_mainCommonDef::DEFAULT_MESSAGE_FIND_NO_ENTRY;	// 記事が見つからないメッセージ
				
				$ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_ENTRY_VIEW_COUNT, $entryViewCount); // 記事表示数
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_ENTRY_VIEW_ORDER, $entryViewOrder);// 記事表示順
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_CATEGORY_COUNT, $categoryCount);		// カテゴリ数
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_USE_CALENDAR, $useCalendar);		// カレンダーを使用するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_TOP_CONTENTS, $topContents);// トップコンテンツ
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE, $layoutEntrySingle);		// コンテンツレイアウト(記事詳細)
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_LAYOUT_ENTRY_LIST, $layoutEntryList);		// コンテンツレイアウト(記事一覧)
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_OUTPUT_HEAD, $outputHead);		// ヘッダ出力するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_HEAD_VIEW_DETAIL, $headViewDetail);	// ヘッダ出力(詳細表示)
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_USE_WIDGET_TITLE, $useWidgetTitle);		// ウィジェットタイトルを使用するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_TITLE_DEFAULT, $titleDefault);		// デフォルトタイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_TITLE_LIST, $titleList);			// 一覧タイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_TITLE_SEARCH_LIST, $titleSearchList);		// 検索結果タイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_TITLE_NO_ENTRY, $titleNoEntry);		// 記事なし時タイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_MESSAGE_NO_ENTRY, $messageNoEntry);		// 記事が登録されていないメッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_MSG_NO_ENTRY_IN_FUTURE, $msgNoEntryInFuture);	// 予定イベントなし時メッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_MESSAGE_FIND_NO_ENTRY, $messageFindNoEntry);		// 記事が見つからないメッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_TITLE_TAG_LEVEL, $titleTagLevel);		// タイトルタグレベル
				
				// 画像の移動
				if ($ret && !empty($updatedEntryImage)){		// 画像更新の場合
					$ret = mvFileToDir($this->tmpDir, $entryImageFilenameArray, $this->gInstance->getImageManager()->getSystemThumbPath(M3_VIEW_TYPE_EVENT, event_mainCommonDef::$_deviceType,
								''/*ディレクトリ取得*/));
					if ($ret){
						$ret = self::$_mainDb->updateConfig(event_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE, implode(';', $entryImageFilenameArray));
					}
				}
				
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$reloadData = true;		// データの再ロード
					
					// 作業ディレクトリを削除
					rmDirectory($this->tmpDir);
					
					// 親ウィンドウを更新
					$this->gPage->updateParentWindow();
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == self::ACT_UPLOAD_IMAGE){		// 画像アップロード
			// 作業ディレクトリを作成
			$this->tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
			
			// Ajaxでのファイルアップロード処理
			$this->ajaxUploadFile($request, array($this, 'uploadFile'), $this->tmpDir);
		} else if ($act == self::ACT_GET_IMAGE){			// 画像取得
			// Ajaxでの画像取得
			$this->getImageByType($imageType);
		} else {		// 初期表示の場合
			$reloadData = true;		// データの再ロード
			
			// 作業ディレクトリを削除
			rmDirectory($this->tmpDir);
		}
		// データ再取得
		if ($reloadData){
			$entryViewCount	= self::$_mainDb->getConfig(event_mainCommonDef::CF_ENTRY_VIEW_COUNT);// 記事表示数
			if (empty($entryViewCount)) $entryViewCount = event_mainCommonDef::DEFAULT_VIEW_COUNT;
			$entryViewOrder	= self::$_mainDb->getConfig(event_mainCommonDef::CF_ENTRY_VIEW_ORDER);// 記事表示順
			$categoryCount	= self::$_mainDb->getConfig(event_mainCommonDef::CF_CATEGORY_COUNT);// カテゴリ数
			if (empty($categoryCount)) $categoryCount = event_mainCommonDef::DEFAULT_CATEGORY_COUNT;
			$useCalendar	= self::$_mainDb->getConfig(event_mainCommonDef::CF_USE_CALENDAR);	// カレンダーを使用するかどうか
			$topContents = self::$_mainDb->getConfig(event_mainCommonDef::CF_TOP_CONTENTS);// トップコンテンツ
			$layoutEntrySingle = self::$_mainDb->getConfig(event_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE);		// コンテンツレイアウト(記事詳細)
			if (empty($layoutEntrySingle)) $layoutEntrySingle = event_mainCommonDef::DEFAULT_LAYOUT_ENTRY_SINGLE;
			$layoutEntryList = self::$_mainDb->getConfig(event_mainCommonDef::CF_LAYOUT_ENTRY_LIST);		// コンテンツレイアウト(記事一覧)
			if (empty($layoutEntryList)) $layoutEntryList = event_mainCommonDef::DEFAULT_LAYOUT_ENTRY_LIST;
			$outputHead = self::$_mainDb->getConfig(event_mainCommonDef::CF_OUTPUT_HEAD);		// ヘッダ出力するかどうか
			$headViewDetail = self::$_mainDb->getConfig(event_mainCommonDef::CF_HEAD_VIEW_DETAIL);		// ヘッダ出力(詳細表示)
			if (empty($headViewDetail)) $headViewDetail = event_mainCommonDef::DEFAULT_HEAD_VIEW_DETAIL;
			$useWidgetTitle = self::$_mainDb->getConfig(event_mainCommonDef::CF_USE_WIDGET_TITLE);		// ウィジェットタイトルを使用するかどうか
			$titleDefault = self::$_mainDb->getConfig(event_mainCommonDef::CF_TITLE_DEFAULT);		// デフォルトタイトル
			$titleList = self::$_mainDb->getConfig(event_mainCommonDef::CF_TITLE_LIST);		// 一覧タイトル
			if (empty($titleList)) $titleList = event_mainCommonDef::DEFAULT_TITLE_LIST;
			$titleSearchList = self::$_mainDb->getConfig(event_mainCommonDef::CF_TITLE_SEARCH_LIST);		// 検索結果タイトル
			if (empty($titleSearchList)) $titleSearchList = event_mainCommonDef::DEFAULT_TITLE_SEARCH_LIST;
			$titleNoEntry = self::$_mainDb->getConfig(event_mainCommonDef::CF_TITLE_NO_ENTRY);		// 記事なし時タイトル
			if (empty($titleNoEntry)) $titleNoEntry = event_mainCommonDef::DEFAULT_TITLE_NO_ENTRY;
			$messageNoEntry = self::$_mainDb->getConfig(event_mainCommonDef::CF_MESSAGE_NO_ENTRY);		// 記事が登録されていないメッセージ
			if (empty($messageNoEntry)) $messageNoEntry = event_mainCommonDef::DEFAULT_MESSAGE_NO_ENTRY;
			$messageFindNoEntry = self::$_mainDb->getConfig(event_mainCommonDef::CF_MESSAGE_FIND_NO_ENTRY);		// 記事が見つからないメッセージ
			if (empty($messageFindNoEntry)) $messageFindNoEntry = event_mainCommonDef::DEFAULT_MESSAGE_FIND_NO_ENTRY;
			$msgNoEntryInFuture = self::$_mainDb->getConfig(event_mainCommonDef::CF_MSG_NO_ENTRY_IN_FUTURE);	// 予定イベントなし時メッセージ
			if (empty($msgNoEntryInFuture)) $msgNoEntryInFuture = event_mainCommonDef::DEFAULT_MSG_NO_ENTRY_IN_FUTURE;
			$titleTagLevel = self::$_mainDb->getConfig(event_mainCommonDef::CF_TITLE_TAG_LEVEL);		// タイトルタグレベル
			if (empty($titleTagLevel)) $titleTagLevel = event_mainCommonDef::DEFAULT_TITLE_TAG_LEVEL;	
		}
		
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "view_count", $entryViewCount);// 記事表示数
		if (empty($entryViewOrder)){	// 順方向
			$this->tmpl->addVar("_widget", "view_order_inc_selected", 'selected');// 記事表示順
		} else {
			$this->tmpl->addVar("_widget", "view_order_dec_selected", 'selected');// 記事表示順
		}
		
		// アップロード実行用URL
		$uploadUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
		$uploadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIG;
		$uploadUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_UPLOAD_IMAGE;
		$this->tmpl->addVar("_widget", "upload_url_entryimage", $this->getUrl($uploadUrl . '&type=' . self::IMAGE_TYPE_ENTRY_IMAGE));		// 記事デフォルト画像
		
		// ##### 画像の表示 #####
		// アップロードされているファイルがある場合は、アップロード画像を表示
		// 記事デフォルト画像
		$imageUrl = '';
		$updateStatus = '0';			// 画像更新状態
		$entryImageFilename = $this->getDefaultEntryImageFilename();		// 記事デフォルト画像名取得
		if (!empty($entryImageFilename)){
			$imageUrl = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_EVENT, event_mainCommonDef::$_deviceType, $entryImageFilename) . '?' . date('YmdHis');
		}
		$this->tmpl->addVar("_widget", "entryimage_url", $this->convertUrlToHtmlEntity($this->getUrl($imageUrl)));			// 記事デフォルト画像
		$this->tmpl->addVar("_widget", "updated_entryimage", $updateStatus);
		
		$this->tmpl->addVar("_widget", "category_count", $categoryCount);// カテゴリ数
		$this->tmpl->addVar("_widget", "use_calendar", $this->convertToCheckedString($useCalendar));// カレンダーを使用するかどうか
		$this->tmpl->addVar("_widget", "top_contents", $this->convertToDispString($topContents));		// トップコンテンツ
		$this->tmpl->addVar("_widget", "layout_entry_single", $layoutEntrySingle);		// コンテンツレイアウト(記事詳細)
		$this->tmpl->addVar("_widget", "layout_entry_list", $layoutEntryList);		// コンテンツレイアウト(記事一覧)
		$this->tmpl->addVar("_widget", "output_head_checked", $this->convertToCheckedString($outputHead));		// ヘッダ出力するかどうか
		$this->tmpl->addVar("_widget", "head_view_detail", $headViewDetail);		// ヘッダ出力(詳細表示)
		$this->tmpl->addVar("_widget", "use_widget_title",	$this->convertToCheckedString($useWidgetTitle));// ウィジェットタイトルを使用するかどうか
		$this->tmpl->addVar("_widget", "title_default", $titleDefault);		// デフォルトタイトル
		$this->tmpl->addVar("_widget", "title_list", $titleList);		// 一覧タイトル
		$this->tmpl->addVar("_widget", "title_search_list", $titleSearchList);		// 検索結果タイトル
		$this->tmpl->addVar("_widget", "title_no_entry", $titleNoEntry);		// 記事なし時タイトル
		$this->tmpl->addVar("_widget", "message_no_entry", $messageNoEntry);		// 記事が登録されていないメッセージ
		$this->tmpl->addVar("_widget", "message_find_no_entry", $messageFindNoEntry);		// 記事が見つからないメッセージ
		$this->tmpl->addVar("_widget", "msg_no_entry_in_future", $this->convertToDispString($msgNoEntryInFuture));		// 予定イベントなし時メッセージ
		$this->tmpl->addVar("_widget", "title_tag_level", $titleTagLevel);		// タイトルタグレベル
		$this->tmpl->addVar("_widget", "upload_area", $this->gDesign->createDragDropFileUploadHtml());		// 画像アップロードエリア
	}
	/**
	 * 最大画像を取得
	 *
	 * @param string $type		画像タイプ
	 * @return					なし
	 */
	function getImageByType($type)
	{
		// 画像パス作成
		switch ($type){
		case self::IMAGE_TYPE_ENTRY_IMAGE:			// 記事デフォルト画像
			$filename = $this->getDefaultEntryImageFilename();		// 記事デフォルト画像名取得
			break;
		}
		$imagePath = '';
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
				$filenameBase = '0';
				break;
			}

			$ret = $this->gInstance->getImageManager()->createImageByFormat($filePath, $formats, $destDir, $filenameBase, $destFilename);
			if ($ret){			// 画像作成成功の場合
				// 画像参照用URL
				$imageUrl = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET;	// ウィジェット設定画面
				$imageUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();	// ウィジェットID
				$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::TASK_CONFIG;
				$imageUrl .= '&' . M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_GET_IMAGE;
				$imageUrl .= '&type=' . $type . '&' . date('YmdHis');
				$resultObj['url'] = $imageUrl;
			} else {// エラーの場合
				$resultObj = array('error' => 'Could not create resized images.');
			}
		}
	}
	/**
	 * 記事デフォルト画像名を取得
	 *
	 * @return string		ファイル名
	 */
	function getDefaultEntryImageFilename()
	{
		$filename = '';
		$value = self::$_mainDb->getConfig(event_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE);
		if (!empty($value)){
			$filenameArray = explode(';', $value);
			$filename = $filenameArray[count($filenameArray) -1];
		}
		return $filename;
	}
}
?>
