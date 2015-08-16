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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_wiki_mainBaseWidgetContainer.php');

class admin_wiki_mainPageWidgetContainer extends admin_wiki_mainBaseWidgetContainer
{
	private $wikiLibObj;		// Wikiコンテンツオブジェクト
	private $serialNo;			// シリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $builtinPages;		// 自動生成されるWikiページ
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 5;			// リンクページ数
	const ICON_SIZE = 32;		// アイコンのサイズ
	const LOCK_ICON_FILE = '/images/system/lock32.png';			// ロック状態アイコン
	const UNLOCK_ICON_FILE = '/images/system/unlock32_inactive.png';		// アンロック状態アイコン
	const WIKI_OBJ_ID = 'wikilib';			// Wikiコンテンツオブジェクト
	
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
			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])){
				$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得

				// ファイル名の解析
				$pathParts = pathinfo($uploadFilename);
				$ext = $pathParts['extension'];		// 拡張子
				$filename = basename($uploadFilename, '.' . $ext);		// 拡張子をはずす
				$ext = strtolower($ext);			

				// 拡張子のチェック
				if ($ext != 'txt'){
					$this->setAppErrorMsg('対応外のファイルタイプです');
				} else {
					// ファイル名のチェック
					$pageName = @decode($filename);
					if (empty($pageName)) $this->setAppErrorMsg('対応外のファイルです');
				}
				
				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					// ファイルを保存するサーバディレクトリを指定
					$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
		
					// アップされたテンポラリファイルを保存ディレクトリにコピー
					$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
					if ($ret){
						// ファイルの内容から文字コードを判断
						$fileData = file_get_contents($tmpFile);
						$encoding = mb_detect_encoding($fileData, 'UTF-8,EUC-JP,JIS');
						if (empty($encoding)) $encoding = M3_ENCODING;

						// ページデータをUTF-8に変換
						if ($encoding != M3_ENCODING){
							$fileData = mb_convert_encoding($fileData, M3_ENCODING, $encoding);
							$pageName = mb_convert_encoding($pageName, M3_ENCODING, $encoding);
						}
						
						// 既にページが存在しているか確認
						$ret = true;
						if (is_page($pageName)){
							$this->setAppErrorMsg('ページが存在しています。ページ=' . $pageName);
							$ret = false;
						}
						
						// WikiデータをDBに格納
						if ($ret) $ret = WikiPage::initPage($pageName, $fileData);
						
						if ($ret) $this->setGuidanceMsg('ページを読み込みました。ページ=' . $pageName);
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
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
		
		// イベントリストを取得
		self::$_mainDb->getAvailablePageList($this->maxListCount, $pageNo, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 表示データないときは、一覧を表示しない
		
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
	 * @param								なし
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
	
		// 総参照数
		$totalViewCount = $this->gInstance->getAnalyzeManager()->getTotalContentViewCount(wiki_mainCommonDef::$_viewContentType, $serial);
		
		// 添付ファイル数
		$attachCount = '';
		require_once(WikiConfig::getPluginDir() . 'attach.inc.php');
		$obj = new AttachPages($id);			// 現状では世代管理なし
		if (isset($obj->pages[$id])) $attachCount = count($obj->pages[$id]->files);
	
		$row = array(
			'index'			=> $index,		// 項目番号
			'serial'		=> $this->convertToDispString($serial),	// シリアル番号
			'id'			=> $idTag,		// WikiページID
			'status'		=> $statusImg,		// Wikiページ状態
			'view_count'	=> $totalViewCount,									// 総参照数
			'attach_count'	=> $attachCount,									// 添付ファイル数
			'user'			=> $this->convertToDispString($fetchedRow['lu_name']),		// 更新者
			'date'			=> $this->convertToDispDateTime($date, 0/*ロングフォーマット*/, 10/*時分*/),		// 更新日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
}
?>
