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
 * @version    SVN: $Id: admin_default_contentHistoryWidgetContainer.php 4970 2012-06-15 10:51:03Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('default_content') . '/admin_default_contentBaseWidgetContainer.php');

class admin_default_contentHistoryWidgetContainer extends admin_default_contentBaseWidgetContainer
{
	private $totalCount;		// 編集履歴総数
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
	const HISTORY_GET_ICON_FILE = '/images/system/history_get.png';		// 履歴データ取得用アイコン
	
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
		return 'admin_history.tmpl.html';
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
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$contentId = $request->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID);
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// 一覧表示数
		$maxListCount = self::DEFAULT_LIST_COUNT;
		
		// コンテンツ総数を取得
		$this->totalCount = self::$_mainDb->getContentHistoryCount(default_contentCommonDef::$_contentType, $contentId, $langId);

		// 表示するページ番号の修正
		$pageCount = (int)(($this->totalCount -1) / $maxListCount) + 1;		// 総ページ数
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
		
		// コンテンツ編集履歴を取得
		self::$_mainDb->getContentHistory(default_contentCommonDef::$_contentType, $contentId, $langId, $maxListCount, $pageNo, array($this, 'itemListLoop'));
		
		$this->tmpl->addVar("_widget", "page", $pageNo);
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
		$this->tmpl->addVar("_widget", "content_id", $contentId);
		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
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
		// 履歴番号
		$no = $fetchedRow['cn_history_index'];
		if ($no == $this->totalCount -1){
			$no = '最新';
		} else {
			$no++;
		}
		// 操作用ボタン
		$historyGetImg = $this->getUrl($this->gEnv->getRootUrl() . self::HISTORY_GET_ICON_FILE);		// 履歴データ取得用アイコン
		$historyGetStr = 'データを取得';
		$row = array(
			'no' => $this->convertToDispString($no),													// 履歴番号
			'serial' => $this->convertToDispString($fetchedRow['cn_serial']),			// シリアル番号
			'history_get_img' => $historyGetImg,											// 履歴データ取得用の画像
			'history_get_str' => $historyGetStr,											// 履歴データ取得用の文字列
			'user' => $this->convertToDispString($fetchedRow['lu_name']),	// 更新者
			'date' => $this->convertToDispDateTime($fetchedRow['cn_create_dt'])	// 更新日時
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中のコンテンツIDを保存
		$this->serialArray[] = $fetchedRow['cn_serial'];
		
		$this->isExistsContent = true;		// コンテンツ項目が存在するかどうか
		return true;
	}
}
?>
