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
/***************************************************************************************************
### 複製元クラス admin_blog_mainScheduleWidgetContainer ###
複製元クラスからadmin_blog_mainScheduleWidgetContainerクラスを生成する
変更行
　・親クラスファイルの読み込み(require_once)
　・クラス名定義
****************************************************************************************************/
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');

class admin_blog_mainScheduleWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	private $serialArray = array();		// 表示されている項目シリアル番号
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const LINK_PAGE_COUNT		= 20;			// リンクページ数
	
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
		return 'admin_schedule.tmpl.html';
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
		$langId	= $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);		// 編集言語を取得
		if (empty($langId)) $langId = $this->_langId;
		$entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 一覧表示数
		$maxListCount = self::DEFAULT_LIST_COUNT;
		
		// 総数を取得
		$totalCount = self::$_mainDb->getEntryScheduleCount($entryId, $langId);

		// ページング計算
		$this->calcPageLink($pageNo, $totalCount, $maxListCount);
		
		// ページングリンク作成
		$pageLink = $this->createPageLink($pageNo, self::LINK_PAGE_COUNT, ''/*リンク作成用(未使用)*/, 'selpage($1);return false;');
		
		// コンテンツ編集履歴を取得
		self::$_mainDb->getEntrySchedule($entryId, $langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
		if (count($this->serialArray) <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 投稿記事がないときは、一覧を表示しない
		
		// ページ遷移(Pagination)用
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "total_count", $totalCount);
		
		$this->tmpl->addVar("_widget", "page", $pageNo);
		$this->tmpl->addVar("_widget", "entry_id", $entryId);
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
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

		$row = array(
			'no' => $this->convertToDispString($index + 1),								// 項目番号
			'serial' => $this->convertToDispString($fetchedRow['be_serial']),			// シリアル番号
			'user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'date' => $this->convertToDispDateTime($fetchedRow['be_create_dt'])	// 更新日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['be_serial'];
		return true;
	}
}
?>
