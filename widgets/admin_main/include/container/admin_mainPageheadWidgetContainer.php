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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConfigbasicBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainPageheadWidgetContainer extends admin_mainConfigbasicBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $pageId;	// ページID
	private $pageSubId;	// ページサブID
	private $langId;		// 選択中の言語
	private $serialArray = array();		// 表示されている項目シリアル番号
	const MAX_DESC_LENGTH = 40;					// 一覧の説明フィールドの最大文字列長
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
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
		if ($task == 'pagehead_detail'){		// 詳細画面
			return 'pagehead_detail.tmpl.html';
		} else {			// 一覧画面
			return 'pagehead.tmpl.html';
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
		if ($task == 'pagehead_detail'){	// 詳細画面
			return $this->createDetail($request);
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
		//$this->langId = $this->gEnv->getDefaultLanguage();		// デフォルトの言語を使用
		$this->langId = '';		// デフォルトの言語を使用
		
		// パラメータの取得
		$task = $request->trimValueOf('task');		// 処理区分
		$act = $request->trimValueOf('act');
		$this->pageId = $request->trimValueOf('pageid');		// ページID
		$this->pageSubId = $request->trimValueOf('pagesubid');// ページサブID
		
		// アクセスポイントメニュー作成
		$this->db->getPageIdList(array($this, 'pageIdLoop'), 0/*ページID*/);

		// ページサブID一覧を作成
		$this->db->getPageSubIdList($this->pageId, $this->langId, array($this, 'pageSubIdLoop'), true/*メニューから選択可項目のみ*/);
		
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
		//$this->langId = $this->gEnv->getDefaultLanguage();		// デフォルトの言語を使用
		$this->langId = '';		// デフォルトの言語を使用
		
		$act = $request->trimValueOf('act');
		$this->pageId = $request->trimValueOf('pageid');		// ページID
		$this->pageSubId = $request->trimValueOf('pagesubid');// ページサブID
		$metaTitle = $request->trimValueOf('item_title');		// ページタイトル名
		$metaDesc = $request->trimValueOf('item_desc');			// ページ要約
		$metaKeyword = $request->trimValueOf('item_keyword');	// ページキーワード
		$headOthers = $request->valueOf('item_others');	// ページその他
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 更新のとき
			// 入力チェック
			$this->checkInput($this->pageId, 'ページID');
			$this->checkInput($this->pageSubId, 'ページサブID');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// ページヘッダ情報の更新
				$ret = $this->db->updatePageHead($this->pageId, $this->pageSubId, $this->langId, $metaTitle, $metaDesc, $metaKeyword, $headOthers);

				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {		// 初期状態
			$replaceNew = true;			// データを再取得
		}
		// 表示データ再取得
		$name = '';
		if ($replaceNew){
			// アクセスポイント情報を取得
			$ret = $this->db->getPageIdRecord(0/*アクセスポイント*/, $this->pageId, $row);
			if ($ret){
				$accessPointName = $row['pg_name'];
			}
		
			// 言語指定が必要
			$ret = $this->db->getPageInfo($this->pageId, $this->pageSubId, $row);
			if ($ret){
				$name = $row['pg_name'];
				$metaTitle = $row['pn_meta_title'];		// ページタイトル名
				$metaDesc = $row['pn_meta_description'];		// ページ要約
				$metaKeyword = $row['pn_meta_keywords'];		// ページキーワード
				$headOthers = $row['pn_head_others'];	// ページその他
			}
		}
		
		$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
		$this->tmpl->addVar("_widget", "access_point_name", $this->convertToDispString($accessPointName));			// アクセスポイント名
		$this->tmpl->addVar("_widget", "page_id", $this->convertToDispString($this->pageId));			// ページID
		$this->tmpl->addVar("_widget", "page_subid", $this->convertToDispString($this->pageSubId));		// ページサブID
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));		// ページ名
		$this->tmpl->addVar("_widget", "title", $this->convertToDispString($metaTitle));		// ページタイトル名
		$this->tmpl->addVar("_widget", "desc", $this->convertToDispString($metaDesc));			// ページ要約
		$this->tmpl->addVar("_widget", "keyword", $this->convertToDispString($metaKeyword));	// ページキーワード
		$this->tmpl->addVar("_widget", "others", $this->convertToDispString($headOthers));	// ページその他
	}
	/**
	 * ページID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageIdLoop($index, $fetchedRow, $param)
	{
		// フロント画面用アクセスポイントのみ取得
		if (!$fetchedRow['pg_frontend']) return true;
		
		// 現在有効なアクセスポイントのみ取得
		$deviceType = $fetchedRow['pg_device_type'];		// デバイスタイプ
		$isActiveSite = $this->gSystem->getSiteActiveStatus($deviceType);
		if (!$isActiveSite) return true;
		
		// デフォルトのページIDを取得
		if (empty($this->pageId)) $this->pageId = $fetchedRow['pg_id'];
		
		$selected = '';
		if ($fetchedRow['pg_id'] == $this->pageId){
			$selected = 'selected';
			
			// デフォルトのページサブIDを取得
			$this->defaultPageSubId = $fetchedRow['pg_default_sub_id'];		// デフォルトのページID
		}
//		$name = $this->convertToDispString($fetchedRow['pg_id']) . ' - ' . $this->convertToDispString($fetchedRow['pg_name']);			// ページ名
		$name = $this->convertToDispString($fetchedRow['pg_name']);			// アクセスポイント名

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_id']),			// ページID
			'name'     => $name,			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('main_id_list', $row);
		$this->tmpl->parseTemplate('main_id_list', 'a');
		return true;
	}
	/**
	 * ページサブID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageSubIdLoop($index, $fetchedRow, $param)
	{
		$pid = $fetchedRow['pg_id'];
		$value = $this->convertToDispString($pid);
		
		// 公開状況
		$public = '';
		if ($fetchedRow['pg_active']) $public = 'checked';
		
		$desc = makeTruncStr($fetchedRow['pn_meta_description'], self::MAX_DESC_LENGTH);
		$row = array(
			'index'    => $index,			// インデックス番号
			'value'    => $value,			// ページID
			'name'     => $this->convertToDispString($fetchedRow['pg_name']),			// ページ名
			'title'     => $this->convertToDispString($fetchedRow['pn_meta_title']),			// ページタイトル
			'desc'     => $this->convertToDispString($desc)			// ページ要約
		);
		$this->tmpl->addVars('sub_id_list', $row);
		$this->tmpl->parseTemplate('sub_id_list', 'a');
		
		// 表示中項目のページサブIDを保存
		$this->serialArray[] = $value;
		return true;
	}
}
?>
