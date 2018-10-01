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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_wiki_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCommonPath()		. '/archive.php');

class admin_wiki_mainPageWidgetContainer extends admin_wiki_mainBaseWidgetContainer
{
	private $wikiLibObj;		// Wikiコンテンツオブジェクト
	private $serialNo;			// シリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $builtinPages;		// 自動生成されるWikiページ
	private $sortKeyType;			// ソートキータイプ
	private $sortKey;		// ソートキー
	private $sortDirection;		// ソート方向
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	const ICON_SIZE = 32;		// アイコンのサイズ
	const SORT_ICON_SIZE = 10;		// ソートアイコンサイズ
	const LOCK_ICON_FILE = '/images/system/lock32.png';			// ロック状態アイコン
	const UNLOCK_ICON_FILE = '/images/system/unlock32_inactive.png';		// アンロック状態アイコン
	const PREVIEW_ICON_FILE = '/images/system/window32.png';		// プレビュー用アイコン
	const SORT_UP_ICON_FILE = '/images/system/arrow_up10.png';		// ソート降順アイコン
	const SORT_DOWN_ICON_FILE = '/images/system/arrow_down10.png';		// ソート昇順アイコン
	const WIKI_OBJ_ID = 'wikilib';			// Wikiコンテンツオブジェクト
	const DEFAULT_SORT_KEY = 'id';		// デフォルトのソートキー
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();

		$this->wikiLibObj = $this->gInstance->getObject(self::WIKI_OBJ_ID);// Wikiコンテンツオブジェクト取得

		// パラメータ初期化
		$this->maxListCount = self::DEFAULT_LIST_COUNT;
		$this->builtinPages	= $this->wikiLibObj->getBuiltinPages();			// 自動生成されるWikiページ
		$this->sortKeyType = array('id'/*WikiページID*/, 'date'/*更新日時*/, 'locked'/*ページロック状態*/);
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
		if ($task == 'page_detail'){		// 詳細画面
			return 'admin_page_detail.tmpl.html';
		} else {
			return 'admin_page.tmpl.html';
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
		if ($task == 'page_detail'){	// 詳細画面
//			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
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
		$act = $request->trimValueOf('act');
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		$sort = $request->trimValueOf('sort');		// ソート順
		
		// ソート順
		list($this->sortKey, $this->sortDirection) = explode('-', $sort);
		if (!in_array($this->sortKey, $this->sortKeyType) || !in_array($this->sortDirection, array('0', '1'))){
			$this->sortKey = self::DEFAULT_SORT_KEY;		// デフォルトのソートキー
			$this->sortDirection = '1';	// 昇順
		}
		
		if ($act == 'delete'){		// 項目削除の場合
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
				// 指定のWikiページを削除
				for ($i = 0; $i < count($delItems); $i++){
					$ret = self::$_mainDb->getPageBySerial($delItems[$i], $row);
					if ($ret) page_write($row['wc_id'], '');
				}
				
				// リンク情報再構築
				$this->wikiLibObj->initLinks();
//				$ret = $this->db->delCategoryBySerial($delItems);
//				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
//				} else {
//					$this->setAppErrorMsg('データ削除に失敗しました');
//				}
			}
		} else if ($act == 'upload'){		// ファイルアップロードの場合
			$overwritePage = $request->trimCheckedValueOf('item_overwrite');		// ページを上書きするかどうか

			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])){
				$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得

				// ファイル名の解析
				$pathParts = pathinfo($uploadFilename);
				$ext = $pathParts['extension'];		// 拡張子
				$filename = basename($uploadFilename, '.' . $ext);		// 拡張子をはずす
				$ext = strtolower($ext);			

				// 拡張子のチェック
				if ($ext != 'txt' && $ext != 'zip'){
					$this->setAppErrorMsg("対応外のファイルタイプです\n読み込み可能なページファイルの形式は、単一のtxtファイル(UTF-8またはEUC-JP)またはtxtファイル(UTF-8のみ)を格納したディレクトリのzip圧縮ファイルです。");
				}
				
				// テンポラリディレクトリの書き込み権限をチェック
				if (!is_writable($this->gEnv->getWorkDirPath())){
					$this->setAppErrorMsg('一時ディレクトリに書き込み権限がありません。ディレクトリ：' . $this->gEnv->getWorkDirPath());
				}
				
				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					// ファイルを保存するサーバディレクトリを指定
					$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
		
					// アップされたテンポラリファイルを保存ディレクトリにコピー
					$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
					if ($ret){
						if ($ext == 'txt'){
							// ファイル名のチェック
							$page = @decode($filename);
							if (empty($page)) $this->setAppErrorMsg('対応外のファイルです');
					
							if ($this->getMsgCount() == 0){		// エラーが発生していないとき
								// ### 単一ファイルの場合は日本語コード自動変換あり ###
								// ページ名、ページファイルをUTF-8に変換
								list($page, $fileData) = self::convertPageFile($page, $tmpFile);
				
								// 「:」で始まるシステム用ページは作成不可
								$ret = true;
								if (strncmp($page, ':', 1) == 0){
									$this->setAppErrorMsg('ページ名が不正です。ページ=' . $page);
									$ret = false;
								} else if (WikiPage::isPage($page)){			// 既にページが存在しているか確認
									if ($overwritePage){		// 上書きの場合
										$ret = WikiPage::updatePage($page, $fileData, false/*更新日時を更新*/, true/*ページ一覧更新*/);
										if (!$ret) $this->setAppErrorMsg('ページの更新に失敗しました。ページ=' . $page);
									} else {
										$this->setAppErrorMsg('ページが存在しています。ページ=' . $page);
										$ret = false;
									}
								} else {			// ページが存在しない場合
									// ページ新規作成
									$ret = WikiPage::initPage($page, $fileData);
									if (!$ret) $this->setAppErrorMsg('ページの作成に失敗しました。ページ=' . $page);
								}
						
								if ($ret){
									$this->setGuidanceMsg('ページを読み込みました。ページ=' . $page);
							
									// 運用ログを残す
									$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_WIKI,
															M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $page,
															M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
									if ($overwritePage){			// 更新の場合
										_writeUserInfoEvent(__METHOD__, sprintf(LOG_MSG_UPDATE_CONTENT, $page), 2401, 'ID=' . $page, $eventParam);
									} else {			// 新規の場合
										_writeUserInfoEvent(__METHOD__, sprintf(LOG_MSG_ADD_CONTENT, $page), 2400, 'ID=' . $page, $eventParam);
									}
								}
							}
						} else if ($ext == 'zip'){
							// 解凍先ディレクトリ取得
							$extDir = $this->gEnv->getTempDir();
						
							// ファイルを解凍
							$archiver = new Archive();
							$ret = $archiver->extract($tmpFile, $extDir, $ext);
							if ($ret){
								// 作成されたファイルを取得
								$fileList = getFileList($extDir);
								if (count($fileList) == 1 && is_dir($extDir . '/' . $fileList[0])){		// 単一ディレクトリのとき
									$srcDir = $extDir . '/' . $fileList[0];
								} else {
									// 設定ファイルを取得
									$srcDir = $extDir;
								}
								
								// 格納ファイル名取得
								$targetFiles = array();			// 処理対象ファイル
								$srcFiles = getFileList($srcDir, true/*ファイルのみ*/);
								for ($i = 0; $i < count($srcFiles); $i++){
									$filename = $srcFiles[$i];

									// ファイル名の解析
									$pathParts = pathinfo($filename);
									$ext = $pathParts['extension'];		// 拡張子
									$basename = basename($filename, '.' . $ext);		// 拡張子をはずす
									$ext = strtolower($ext);
									
									// 拡張子が「txt」でエンコードされているファイルのみ取得
									if ($ext == 'txt'){
										$page = @decode($basename);
										if (!empty($page) && strncmp($page, ':', 1) != 0) $targetFiles[$page] = $filename;// 「:」で始まるシステム用ページは不可
									}
								}
								
								// ページの日本語コードをチェック
								$ret = true;
								$errPages = array();		// エラーありのファイル
								foreach ($targetFiles as $page => $filename){
									$path = $srcDir . '/' . $filename;
									$ret = self::checkPageFile($page, $path);
									if (!$ret) $errPages[] = $page;
								}
								if (!empty($errPages)) $this->setAppErrorMsg('ページファイルから日本語コードEUC-JPを検出しました。ページ=' . implode(',', $errPages));
								
								// ページが既に登録されていないかチェック
								if ($ret && !$overwritePage){			// 上書きしない場合
									$existsPages = WikiPage::getPages();		// 既に登録されているページ
									
									$pages = array_keys($targetFiles);
									for ($i = 0; $i < count($pages); $i++){
										$page = $pages[$i];
										if (in_array($page, $pages)) $this->setAppErrorMsg('ページが既に登録されています。ページ=' . $page);
									}
								}
							
								if ($this->getMsgCount() == 0){		// エラーが発生していないとき
									$completePages = array();		// 登録完了のファイル
									foreach ($targetFiles as $page => $filename){
										$path = $srcDir . '/' . $filename;
										$fileData = file_get_contents($path);		// ファイル読み込み
										
										$ret = true;
										if (WikiPage::isPage($page)){			// 既にページが存在しているか確認
											if ($overwritePage){		// 上書きの場合
												$ret = WikiPage::updatePage($page, $fileData, false/*更新日時を更新*/, true/*ページ一覧更新*/);
												if (!$ret) $this->setAppErrorMsg('ページの更新に失敗しました。ページ=' . $page);
											} else {
												$this->setAppErrorMsg('ページが存在しています。ページ=' . $page);
												$ret = false;
											}
										} else {			// ページが存在しない場合
											// ページ新規作成
											$ret = WikiPage::initPage($page, $fileData);
											if (!$ret) $this->setAppErrorMsg('ページの作成に失敗しました。ページ=' . $page);
										}
					
										if ($ret){
											// 登録したページを追加
											$completePages[] = $page;
											
											// 運用ログを残す
											$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_WIKI,
																	M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $page,
																	M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
											if ($overwritePage){			// 更新の場合
												_writeUserInfoEvent(__METHOD__, sprintf(LOG_MSG_UPDATE_CONTENT, $page), 2401, 'ID=' . $page, $eventParam);
											} else {			// 新規の場合
												_writeUserInfoEvent(__METHOD__, sprintf(LOG_MSG_ADD_CONTENT, $page), 2400, 'ID=' . $page, $eventParam);
											}
										}
									}
									if (!empty($completePages)) $this->setGuidanceMsg('ページを読み込みました。ページ=' . implode(',', $completePages));
								}
							}
							// 解凍用ディレクトリを削除
							if (file_exists($extDir)) rmDirectory($extDir);
						}
					} else {
						$this->setAppErrorMsg('ファイルのアップロードに失敗しました');
					}
					// テンポラリファイル削除
					unlink($tmpFile);
				}
			} else {
				$msg = 'アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります - ' . $this->gSystem->getMaxFileSizeForUpload() . 'バイト)';
				$this->setAppErrorMsg($msg);
			}
		}
		// #### Wikiページリストを作成 ####
		// 総数を取得
		$totalCount = self::$_mainDb->getAvailablePageListCount();

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $this->maxListCount);
		
		// ページングリンク作成
		$sort = '';		// ソート値
		if (!empty($this->sortKey)) $sort = '&sort=' . $this->sortKey . '-' . $this->sortDirection;
//		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');

		$currentBaseUrl = $this->_baseUrl . $sort;
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, $currentBaseUrl/*リンク作成用*/);

		// ページリストを取得
//		self::$_mainDb->getAvailablePageList($this->maxListCount, $pageNo, array($this, 'itemListLoop'));
		self::$_mainDb->getAvailablePageList($this->maxListCount, $pageNo, $this->sortKey, $this->sortDirection, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 表示データないときは、一覧を表示しない
		
		// ソート用データ設定
		if (empty($this->sortDirection)){
			$iconUrl = $this->getUrl($this->gEnv->getRootUrl() . self::SORT_UP_ICON_FILE);	// ソート降順アイコン
			$iconTitle = '降順';
		} else {
			$iconUrl = $this->getUrl($this->gEnv->getRootUrl() . self::SORT_DOWN_ICON_FILE);	// ソート昇順アイコン
			$iconTitle = '昇順';
		}
		$style = 'style="' . 'width:' . self::SORT_ICON_SIZE . 'px;height:' . self::SORT_ICON_SIZE . 'px;"';
		$sortImage = '<img src="' . $iconUrl . '" title="' . $iconTitle . '" alt="' . $iconTitle . '" rel="m3help" ' . $style . ' />';
		
		switch ($this->sortKey){
			case 'id':		// WikiページID
				$this->tmpl->addVar('_widget', 'direct_icon_id', $sortImage);
				break;
			case 'date':		// 更新日時
				$this->tmpl->addVar('_widget', 'direct_icon_date', $sortImage);
				break;
			case 'locked':		// ロック状態
				$this->tmpl->addVar('_widget', 'direct_icon_locked', $sortImage);
				break;
		}
		if ($this->sortKey == 'id' && !empty($this->sortDirection)){
			$this->tmpl->addVar('_widget', 'sort_id', 'id-0');
		} else {
			$this->tmpl->addVar('_widget', 'sort_id', 'id-1');
		}
		if ($this->sortKey == 'date' && !empty($this->sortDirection)){
			$this->tmpl->addVar('_widget', 'sort_date', 'date-0');
		} else {
			$this->tmpl->addVar('_widget', 'sort_date', 'date-1');
		}
		if ($this->sortKey == 'locked' && !empty($this->sortDirection)){
			$this->tmpl->addVar('_widget', 'sort_locked', 'locked-0');
		} else {
			$this->tmpl->addVar('_widget', 'sort_locked', 'locked-1');
		}
		$this->tmpl->addVar('_widget', 'sort', $this->sortKey . '-' . $this->sortDirection);

		// 一覧用項目
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		
		// その他の項目
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function createDetail($request)
	{
		$userId = $this->gEnv->getCurrentUserId();
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号

		$name	= $request->trimValueOf('item_name');		// カテゴリー名称
		$index	= $request->trimValueOf('item_index');		// 表示順
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;			// 表示するかどうか
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 項目追加の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($index, '表示順');
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
//				$ret = $this->db->addCategory(0, $this->langId, $name, 0, $index, $visible, $userId, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$replaceNew = true;			// データを再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($index, '表示順');		
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateCategory($this->serialNo, $name, 0, $index, $visible, $userId, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					// 登録済みのカテゴリーを取得
					$this->serialNo = $newSerial;
					$replaceNew = true;			// データを再取得
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			$ret = $this->db->delCategoryBySerial(array($this->serialNo));
			if ($ret){		// データ削除成功のとき
				$this->setGuidanceMsg('データを削除しました');
			} else {
				$this->setAppErrorMsg('データ削除に失敗しました');
			}
		} else {	// 初期表示
			// 入力値初期化
			if (empty($this->serialNo)){		// シリアル番号
				$name = '';		// 名前
//				$index = $this->db->getMaxIndex($this->langId) + 1;	// 表示順
				$visible = 1;	// 表示状態
			} else {
				$replaceNew = true;			// データを再取得
			}
		}
		// データを再取得のとき
		if ($replaceNew){
			$ret = $this->db->getCategoryBySerial($this->serialNo, $row);
			if ($ret){
				// 取得値を設定
				$id = $row['bc_id'];		// ID
//				$this->langId = $row['bc_language_id'];		// 言語ID
				$name = $row['bc_name'];		// 名前
				$index = $row['bc_sort_order'];	// 表示順
				$visible = $row['bc_visible'];	// 表示状態
				$updateUser = $this->convertToDispString($row['lu_name']);	// 更新者
				$updateDt = $this->convertToDispDateTime($row['bc_create_dt']);	// 更新日時
			}
		}
		// #### 更新、新規登録部をを作成 ####
		if (empty($this->serialNo)){		// シリアル番号のときは新規とする
			$this->tmpl->addVar("_widget", "id", '新規');
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->addVar("_widget", "id", $id);
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
		}
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "index", $index);		// 表示順
		
		$visibleStr = '';
		if ($visible){	// 項目の表示
			$visibleStr = 'checked';
		}
		$this->tmpl->addVar("_widget", "visible", $visibleStr);		// 表示状態
		if (!empty($updateUser)) $this->tmpl->addVar("_widget", "update_user", $updateUser);	// 更新者
		if (!empty($updateDt)) $this->tmpl->addVar("_widget", "update_dt", $updateDt);	// 更新日時
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemListLoop($index, $fetchedRow, $param)
	{
		$serial		= $fetchedRow['wc_serial'];// シリアル番号
		$id			= $fetchedRow['wc_id'];			// WikiページID
		$date		= $fetchedRow['wc_content_dt'];	// 更新日時
		$isLocked	= $fetchedRow['wc_locked'];		// ロック状態
		
		$idTag = $this->convertToDispString($id);
		if (in_array($id, $this->builtinPages)) $idTag = '<strong>' . $idTag . '</strong>';
		
		// Wikiページ状態
		if ($isLocked){
			$iconUrl = $this->gEnv->getRootUrl() . self::LOCK_ICON_FILE;			// ロック状態アイコン
			$iconTitle = 'ロック';
		} else {
			$iconUrl = $this->gEnv->getRootUrl() . self::UNLOCK_ICON_FILE;		// アンロック状態アイコン
			$iconTitle = 'アンロック';
		}
		$statusImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" rel="m3help" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
	
		// 参照数
//		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(wiki_mainCommonDef::$_viewContentType, $serial);
		$updateViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(wiki_mainCommonDef::$_viewContentType, $serial);	// 更新後からの参照数
		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(wiki_mainCommonDef::$_viewContentType, 0, $id);	// 新規作成からの参照数
		$viewCountStr = $updateViewCount;
		if ($totalViewCount > $updateViewCount) $viewCountStr .= '(' . $totalViewCount . ')';		// 新規作成からの参照数がない旧仕様に対応
		
		// 添付ファイル数
		$attachCount = '';
		require_once(WikiConfig::getPluginDir() . 'attach.inc.php');
		$obj = new AttachPages($id);			// 現状では世代管理なし
		if (isset($obj->pages[$id])) $attachCount = count($obj->pages[$id]->files);
	
		// プレビュー用URL
		$previewUrl = $this->gEnv->getDefaultUrl() . WikiParam::convQuery("?" . rawurlencode($id), false/*URLエンコードしない*/);
		$previewUrl .= '&' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_PREVIEW;// プレビュー用URL
		$previewImg = $this->getUrl($this->gEnv->getRootUrl() . self::PREVIEW_ICON_FILE);
		$previewStr = 'プレビュー';
		
		$row = array(
			'index'			=> $index,		// 項目番号
			'serial'		=> $this->convertToDispString($serial),	// シリアル番号
			'id'			=> $idTag,		// WikiページID
			'status'		=> $statusImg,		// Wikiページ状態
//			'view_count'	=> $totalViewCount,									// 参照数
			'view_count' => $this->convertToDispString($viewCountStr),			// 参照数
			'attach_count'	=> $attachCount,									// 添付ファイル数
			'user'			=> $this->convertToDispString($fetchedRow['lu_name']),		// 更新者
			'date'			=> $this->convertToDispDateTime($date, 0/*ロングフォーマット*/, 10/*時分*/),		// 更新日時
			'preview_url'	=> $previewUrl,											// プレビュー用のURL
			'preview_img'	=> $previewImg,											// プレビュー用の画像
			'preview_str'	=> $previewStr									// プレビュー文字列
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
	/**
	 * ページ名、ページファイルをUTF-8に変換
	 *
	 * @param string $page		ページ名
	 * @param string $path		ページファイルパス
	 * @return array			ページ名とファイル内容が返る
	 */
	function convertPageFile($page, $path)
	{
		// ファイルの内容から文字コードを判断
		$fileData = file_get_contents($path);
		$encoding = mb_detect_encoding($fileData, 'ASCII,UTF-8,EUC-JP');
		if (empty($encoding)) $encoding = M3_ENCODING;

		// ページデータをUTF-8に変換
		if ($encoding != M3_ENCODING){
			$fileData = mb_convert_encoding($fileData, M3_ENCODING, $encoding);
			
			if ($encoding == 'ASCII'){		// 1バイトコードとして判定されている場合はページ名のみ再度判定
				$encoding = mb_detect_encoding($page, 'ASCII,UTF-8,EUC-JP');
				$page = mb_convert_encoding($page, M3_ENCODING, $encoding);
			}
		}
		return array($page, $fileData);
	}
	/**
	 * ページ名、ファイル内容の日本語コードをチェック
	 *
	 * @param string $page		ページ名
	 * @param string $path		ページファイルパス
	 * @return bool				true=問題なし、false=変換が必要
	 */
	function checkPageFile($page, $path)
	{
		// ページ名の日本語コードをチェック
		$encoding = mb_detect_encoding($page, 'ASCII,UTF-8,EUC-JP');
		if ($encoding == 'EUC-JP') return false;
				
		// ファイルの内容から文字コードを判断
		$fileData = file_get_contents($path);
		$encoding = mb_detect_encoding($fileData, 'ASCII,UTF-8,EUC-JP');
		if ($encoding == 'EUC-JP') return false;
		
		return true;
	}
}
?>
