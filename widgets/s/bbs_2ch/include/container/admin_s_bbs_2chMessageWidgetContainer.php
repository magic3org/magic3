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
 * @version    SVN: $Id: admin_s_bbs_2chMessageWidgetContainer.php 4851 2012-04-15 00:43:29Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_s_bbs_2chBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/s_bbs_2chDb.php');

class admin_s_bbs_2chMessageWidgetContainer extends admin_s_bbs_2chBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されているコンテンツシリアル番号
	private $isExistsContent;		// メッセージ項目が存在するかどうか
	const DEFAULT_LIST_COUNT = 30;			// 最大リスト表示数
	const LIST_MESSAGE_LENGTH = 50;			// 一覧に表示するメッセージの長さ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new s_bbs_2chDb();
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
		$filename = '';
		$task = $request->trimValueOf('task');
		switch ($task){
			case 'message':		// メッセージ管理
			default:
				$filename = 'admin_message.tmpl.html';
				break;
			case 'message_detail':		// メッセージ管理詳細
				$filename = 'admin_message_detail.tmpl.html';
				break;
		}
		return $filename;
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
	function _assign($request, &$param)
	{
		$task = $request->trimValueOf('task');
		switch ($task){
			case 'message':		// メッセージ管理
			default:
				$this->createList($request);
				break;
			case 'message_detail':		// メッセージ管理詳細
				$this->createDetail($request);
				break;
		}
	}
	/**
	 * コンテンツ一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		// ユーザ情報、表示言語
		$userId = $this->gEnv->getCurrentUserId();
		$langId = $this->gEnv->getDefaultLanguage();
		
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		$act = $request->trimValueOf('act');
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
				$ret = $this->db->delMessage($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'selpage'){			// ページ選択
		}
		// 一覧表示数
		$maxListCount = self::DEFAULT_LIST_COUNT;
		
		// メッセージ総数を取得
		$totalCount = $this->db->getMessageCount($this->_boardId);

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
		
		// メッセージ一覧を取得
		$this->db->getMessage($this->_boardId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
		if (!$this->isExistsContent) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// コンテンツ項目がないときは、一覧を表示しない
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
	}
	/**
	 * コンテンツ詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ユーザ情報、表示言語
		$langId = $this->gEnv->getDefaultLanguage();
		
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$name = $request->valueOf('item_name');
		$message = $request->valueOf('item_message');
		
		$reloadData = false;		// データの再読み込み		
		if ($act == 'delete'){		// 項目削除の場合
			if (empty($this->serialNo)){
				$this->setUserErrorMsg('削除項目が選択されていません');
			}
			// エラーなしの場合は、データを削除
			if ($this->getMsgCount() == 0){
				$ret = $this->db->delMessage(array($this->serialNo));
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// データ再取得
				$ret = $this->db->getMessageBySerial($this->serialNo, $row);
				if ($ret){
					//$name = $row['te_user_name'];		// ニックネーム
					$email = $row['te_email'];		// Eメール
				//	$message = $row['te_message'];				// メッセージ
					
					// データ更新
					$ret = $this->db->updateMessage($this->serialNo, $name, $email, $message);
				}
				
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
			}
		} else {
			$reloadData = true;		// データの再読み込み
		}
		if ($reloadData){		// データの再読み込み
			$ret = $this->db->getMessageBySerial($this->serialNo, $row);
			if ($ret){
				$no = $row['te_index'];
				$name = $row['te_user_name'];		// ニックネーム
				$title = $row['th_subject'];	// タイトル
				$message = $row['te_message'];				// メッセージ
				$regist_dt = $row['te_regist_dt'];
			} else {
				$this->serialNo = 0;
				$name = '';
				$message = '';
			}
		} else {
			// 表示のみのデータを再取得
			$ret = $this->db->getMessageBySerial($this->serialNo, $row);
			if ($ret){
				$no = $row['te_index'];
				$name = $row['te_user_name'];		// ニックネーム
				$title = $row['th_subject'];	// タイトル
				$message = $row['te_message'];				// メッセージ
				$regist_dt = $row['te_regist_dt'];
			}
		}
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar("_widget", "no", $no);				// 投稿番号
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));				// ニックネーム
		$this->tmpl->addVar("_widget", "title", $this->convertToDispString($title));			// タイトル
		$this->tmpl->addVar("_widget", "message", $this->convertToDispString($message));		// メッセージ
		$this->tmpl->addVar("_widget", "regist_dt", $this->convertToDispDateTime($regist_dt));	// 投稿日時
	
		// 選択中のシリアル番号を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		
		// ボタンの表示制御
		if (!empty($this->serialNo)){
			$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 「削除」ボタン
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');
		}
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
		$serial = $fetchedRow['te_serial'];
		
		// メッセージ
		$message = $fetchedRow['te_message'];
		$message = makeTruncStr($message, self::LIST_MESSAGE_LENGTH);// メッセージ長調整

		$row = array(
			'index' => $index,													// 項目番号
			'serial' => $serial,			// シリアル番号
			'no' => $this->convertToDispString($fetchedRow['te_index']),			// 投稿番号
			'title' => $this->convertToDispString($fetchedRow['th_subject']),		// タイトル
			'message' => $this->convertToDispString($message),		// メッセージ
			'name' => $this->convertToDispString($fetchedRow['te_user_name']),		// ニックネーム
			'update_dt' => $this->convertToDispDateTime($fetchedRow['te_regist_dt'])	// 投稿日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $serial;
		
		$this->isExistsContent = true;		// コンテンツ項目が存在するかどうか
		return true;
	}
}
?>
