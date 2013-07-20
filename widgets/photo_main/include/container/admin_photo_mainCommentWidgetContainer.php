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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_photo_mainCommentWidgetContainer.php 4368 2011-09-30 02:27:33Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_photo_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/photo_commentDb.php');

class admin_photo_mainCommentWidgetContainer extends admin_photo_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const COMMENT_SIZE = 40;			// コメント内容の最大文字数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new photo_commentDb();
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
		if ($task == 'comment_detail'){		// 詳細画面
			return 'admin_comment_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_comment.tmpl.html';
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
		if ($task == 'comment_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * JavascriptファイルをHTMLヘッダ部に設定
	 *
	 * JavascriptファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						Javascriptファイル。出力しない場合は空文字列を設定。
	 */
/*	function _addScriptFileToHead($request, &$param)
	{
		$scriptArray = array($this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_SCRIPT_FILE),		// カレンダースクリプトファイル
							$this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_LANG_FILE),	// カレンダー言語ファイル
							$this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_SETUP_FILE));	// カレンダーセットアップファイル
		return $scriptArray;

	}*/
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
/*	function _addCssFileToHead($request, &$param)
	{
		return $this->getUrl($this->gEnv->getScriptsUrl() . self::CALENDAR_CSS_FILE);
	}*/
	/**
	 * 一覧画面作成
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ユーザ情報、表示言語
//		$defaultLangId = $this->gEnv->getDefaultLanguage();
		
		$act = $request->trimValueOf('act');
//		$this->langId = $request->trimValueOf('item_lang');				// 現在メニューで選択中の言語
//		if (empty($this->langId)) $this->langId = $defaultLangId;			// 言語が選択されていないときは、デフォルト言語を設定	
		
		// ##### 検索条件 #####
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// DBの保存設定値を取得
		$maxListCount = self::DEFAULT_LIST_COUNT;
/*		$serializedParam = $this->sysDb->getWidgetParam($this->gEnv->getCurrentWidgetId());
		if (!empty($serializedParam)){
			$dispInfo = unserialize($serializedParam);
			$maxListCount = $dispInfo->maxMemberListCountByAdmin;		// 会員リスト最大表示数
		}*/
/*
		$search_startDt = $request->trimValueOf('search_start');		// 検索範囲開始日付
		if (!empty($search_startDt)) $search_startDt = $this->convertToProperDate($search_startDt);
		$search_endDt = $request->trimValueOf('search_end');			// 検索範囲終了日付
		if (!empty($search_endDt)) $search_endDt = $this->convertToProperDate($search_endDt);
		$search_keyword = $request->trimValueOf('search_keyword');			// 検索キーワード
		*/

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
				$ret = $this->db->delCommentItem($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
/*		} else if ($act == 'search'){		// 検索のとき
			if (!empty($search_startDt) && !empty($search_endDt) && $search_startDt > $search_endDt){
				$this->setUserErrorMsg('期間の指定範囲にエラーがあります。');
			}
			$pageNo = 1;		// ページ番号初期化
			*/
		} else if ($act == 'selpage'){			// ページ選択
		}
		// ###### 一覧の取得条件を作成 ######
		if (!empty($search_endDt)) $endDt = $this->getNextDay($search_endDt);
		
		// 総数を取得
		$totalCount = $this->db->getCommentItemCount($search_startDt, $endDt, $search_keyword, $this->_langId);

		// 表示するページ番号の修正
		$pageCount = (int)(($totalCount -1) / $maxListCount) + 1;		// 総ページ数
		if ($pageNo < 1) $pageNo = 1;
		if ($pageNo > $pageCount) $pageNo = $pageCount;
		$this->firstNo = ($pageNo -1) * $maxListCount + 1;		// 先頭番号
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			for ($i = 1; $i <= $pageCount; $i++){
				if ($i == $pageNo){
					$link = '&nbsp;' . $i;
				} else {
					$link = '&nbsp;<a href="#" onclick="selpage(\'' . $i . '\');return false;">' . $i . '</a>';
				}
				$pageLink .= $link;
			}
		}
		
		// 記事項目リストを取得
		$this->db->searchCommentItems($maxListCount, $pageNo, $search_startDt, $endDt, $search_keyword, $this->_langId, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 投稿記事がないときは、一覧を表示しない
		
		// 検索結果
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		
		// 検索条件
		$this->tmpl->addVar("_widget", "search_start", $search_startDt);	// 開始日付
		$this->tmpl->addVar("_widget", "search_end", $search_endDt);	// 終了日付
		$this->tmpl->addVar("_widget", "search_keyword", $search_keyword);	// 検索キーワード

		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		$this->tmpl->addVar("_widget", "page", $pageNo);	// ページ番号
		$this->tmpl->addVar("_widget", "list_count", $maxListCount);	// 一覧表示項目数
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		if (empty($this->serialNo)) $this->serialNo = 0;
		$comment = $request->trimValueOf('item_comment');		// コメント

		$dataReload = false;		// データの再ロード
		if ($act == 'add'){		// 項目追加の場合
		/*
			// 入力チェック
			$this->checkInput($name, 'タイトル');
					
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				$ret = $this->db->addEntryItem(0, $this->langId, $name, $html, $status, $this->categoryArray, $regUserId, $regDt, $newSerial);
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					// シリアル番号更新
					$this->serialNo = $newSerial;
					$dataReload = true;		// データの再ロード
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}*/
		} else if ($act == 'update'){		// 項目更新の場合
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->db->updateCommentItem($this->serialNo, $comment);
				
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					$dataReload = true;		// データの再ロード
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->db->delCommentItem(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'deleteid'){		// ID項目削除の場合
		} else {	// 初期画面表示のとき
			$dataReload = true;		// データの再ロード
		}
		
		// 設定データを再取得
		if ($dataReload){		// データの再ロード
			$ret = $this->db->getCommentBySerial($this->serialNo, $row);
			if ($ret){
				$imageTitle = $row['ht_name'];				// 画像タイトル
				$imageCode = $row['ht_code'];				// 画像コード
				$rateValue = $row['hr_rate_value'];				// 評価値
				$comment = $row['hr_message'];				// コメント
				$reg_user = $row['regist_user_name'];				// 投稿者
				$reg_dt = $this->convertToDispDateTime($row['hr_regist_dt']);				// 投稿日時
				$update_user = $row['update_user_name'];		// 更新者
				$update_dt = $this->convertToDispDateTime($row['hr_update_dt']);		// 更新日時
			}
		}
		
		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);	// シリアル番号

		// 入力フィールドの設定、共通項目のデータ設定
		if (empty($this->serialNo)){		// 未登録データのとき
			// データ追加ボタン表示
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');
		} else {
			// データ更新、削除ボタン表示
			$this->tmpl->setAttribute('delete_button', 'visibility', 'visible');// デフォルト言語以外はデータ削除
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
		}

		// ### 入力値を再設定 ###
		$this->tmpl->addVar("_widget", "image_title", $this->convertToDispString($imageTitle));		// 画像タイトル
		$this->tmpl->addVar("_widget", "code", $this->convertToDispString($imageCode));				// 画像コード
		$this->tmpl->addVar("_widget", "rate", $this->convertToDispString($rateValue));		// 評価値
		$this->tmpl->addVar("_widget", "comment", $this->convertToDispString($comment));		// コメント
		$this->tmpl->addVar("_widget", "regist_user", $this->convertToDispString($reg_user));		// 投稿者
		$this->tmpl->addVar("_widget", "regist_dt", $reg_dt);	// 投稿日時
		$this->tmpl->addVar("_widget", "update_user", $this->convertToDispString($update_user));	// 更新者
		$this->tmpl->addVar("_widget", "update_dt", $update_dt);	// 更新日時
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
		// シリアル番号
		$serial = $fetchedRow['hr_serial'];
		
		// コメント内容
		$comment = makeTruncStr($fetchedRow['hr_message'], self::COMMENT_SIZE);

		$row = array(
			'index' => $index,		// 項目番号
			'no' => $index + 1,													// 行番号
			'serial' => $serial,			// シリアル番号
			'rate' => $this->convertToDispString($fetchedRow['hr_rate_value']),		// 評価値
			'content' => $this->convertToDispString($comment),		// コメント内容
			'code' => $this->convertToDispString($fetchedRow['ht_code']),		// 画像コード
			'reg_date' => $this->convertToDispDateTime($fetchedRow['hr_regist_dt'])	// 投稿日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
}
?>
