<?php
/**
 * Eコマースメール連携クラス
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
require_once($gEnvManager->getCommonPath() . '/addon.php');
require_once(dirname(__FILE__) . '/blogLibDb.php');

class blogLib extends Addon
{
	private $db;	// DB接続オブジェクト
	private $blogId = '';	// ブログID
	private $templateId = '';	// テンプレートID
	const CF_ENTRY_VIEW_COUNT		= 'entry_view_count';			// 記事表示数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new blogLibDb();
		
		// ##### ブログ記事予約更新処理 #####
		$this->updateEntryBySchedule();
	}
	/**
	 * 初期化
	 *
	 * @return なし
	 */
	function _initData()
	{
		global $gEnvManager;
		global $gRequestManager;
		static $init = false;
		
		if ($init) return;
		
		$langId = $gEnvManager->getDefaultLanguage();
	
		// 記事IDからブログID、テンプレートIDを取得
		$entryId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		if (empty($entryId)) $entryId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT);		// 略式ブログ記事ID
		if (!empty($entryId)){
			$ret = $this->db->getEntryItem($entryId, $langId, $row);
			if ($ret){
				$this->templateId = $row['bl_template_id'];
				$this->blogId = $row['bl_id'];;	// ブログID
			}
		} else {
			// ブログIDからテンプレートIDを取得
			$blogId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);
			if (empty($blogId)) $blogId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_BLOG_ID_SHORT);		// 略式ブログID
			if (!empty($blogId)){
				$ret = $this->db->getBlogInfoById($blogId, $row);
				if ($ret){
					$this->templateId = $row['bl_template_id'];
					$this->blogId = $row['bl_id'];;	// ブログID
				}
			}
		}
		
		$init = true;		// 初期化完了
	}
	/**
	 * URLパラメータからオプションのテンプレートを取得
	 *
	 * @return string						テンプレートID
	 */
	function getOptionTemplate()
	{
		// 初期化
		$this->_initData();
		
		return $this->templateId;
	}
	/**
	 * 現在のブログIDを取得
	 *
	 * @return string					ブログID
	 */
	function getBlogId()
	{
		// 初期化
		$this->_initData();
		
		return $this->blogId;
	}
	/**
	 * ブログ定義値を取得
	 *
	 * @param string $id				定義ID
	 * @return string					定義値
	 */
	function getConfig($id)
	{
		static $configArray;
		
		// ブログ定義を読み込む
		if (!isset($configArray)) $configArray = $this->loadConfig($this->db);
		
		return isset($configArray[$id]) ? $configArray[$id] : '';
	}
	/**
	 * ブログ定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	function loadConfig($db)
	{
		$retVal = array();

		// ブログ定義値を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['bg_id'];
				$value = $rows[$i]['bg_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * ブログ記事予約更新処理
	 *
	 * @return							なし
	 */
	function updateEntryBySchedule()
	{
		$ret = $this->db->getEntryScheduleInActive(array($this, 'updateByScheduleLoop'));
	}
	/**
	 * 予約更新処理を実行
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function updateByScheduleLoop($index, $fetchedRow, $param)
	{
		$entryId = $fetchedRow['be_id'];		// 記事ID
		$langId = $fetchedRow['be_language_id'];
		$name = $fetchedRow['be_name'];			// 記事タイトル

		// 更新対象のブログ記事を取得
		$statusStr = '';
		$ret = $this->db->getEntryItem($entryId, $langId, $row);
		if ($ret){
			$serialNo = $row['be_serial'];
			$name = $row['be_name'];		// コンテンツ名前
			$updateDt = $row['be_create_dt'];		// 作成日時
			
			// 公開状態
			switch ($row['be_status']){
				case 1:	$statusStr = '編集中';	break;
				case 2:	$statusStr = '公開';	break;
				case 3:	$statusStr = '非公開';	break;
			}
		}
		
		// 変更値を設定
		$updateParams = array();
		$updateParams['be_html'] = $fetchedRow['be_html'];					// 記事内容1
		$updateParams['be_html_ext'] = $fetchedRow['be_html_ext'];			// 記事内容2
		$updateParams['be_master_serial'] = $fetchedRow['be_serial'];		// 作成元レコードのシリアル番号
		// その他の項目は入力値がある場合のみ更新
		
		// ブログ記事を更新
		$ret = $this->db->updateEntryItemBySchedule($serialNo, $updateParams, $newSerial, $oldRecord);
		if ($ret){
			// ブログ記事更新成功の場合は、予約記事の状態を更新
			$ret = $this->db->updateScheduleEntryStatus($fetchedRow['be_serial'], 3/*終了*/);
			
			// ##### 運用ログを残す #####
			$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_BLOG,
									M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $entryId,
									M3_EVENT_HOOK_PARAM_UPDATE_DT		=> $updateDt);
			$this->writeUserInfoEvent(__METHOD__, 'ブログ記事を予約更新(' . $statusStr . ')しました。タイトル: ' . $name, 2401, 'ID=' . $entryId, $eventParam);
		}
		return true;
	}
	/**
	 * 記事一覧の表示項目数を取得
	 *
	 * @return int				表示項目数
	 */
	function getPublicContentViewCount()
	{
		$itemCount = $this->getConfig(self::CF_ENTRY_VIEW_COUNT);
		return $itemCount;
	}
	/**
	 * 公開中の記事数を取得。アクセス制限も行う。
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @return int							項目数
	 */
	function getPublicContentCount($now, $startDt, $endDt, $keywords, $langId, $categoryId = null, $blogId = null)
	{
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		$itemCount = $this->db->getPublicEntryItemsCount($now, $startDt, $endDt, $keywords, $langId, $userId, $categoryId, $blogId);
		return $itemCount;
	}
	/**
	 * 公開中の記事を取得。アクセス制限も行う。
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param int,array	$entryId			エントリーID(0のときは期間で取得)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @return 			なし
	 */
	function getPublicContentList($limit, $page, $entryId, $now, $startDt, $endDt, $keywords, $langId, $order, $callback, $categoryId = null, $blogId = null)
	{
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		$this->db->getPublicEntryItems($limit, $page, $entryId, $now, $startDt, $endDt, $keywords, $langId, $order, $userId, $callback, $categoryId, $blogId);
	}
	/**
	 * 公開中のエントリーの前後のエントリー項目を取得
	 *
	 * @param int       $type				前後記事のタイプ(0=前方,1=後方)
	 * @param timestamp $regDate			基準となる記事の登録日時
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param array		$keywords			検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @param string	$blogId				ブログID(nullのとき指定なし)
	 * @return array 						記事のレコード。取得なしの場合はfalseを返す。
	 */
	function getPublicPrevNextEntry($type, $regDate, $now, $startDt, $endDt, $keywords, $langId, $order, $categoryId = null, $blogId = null)
	{
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		$row = $this->db->getPublicPrevNextEntry($type, $regDate, $now, $startDt, $endDt, $keywords, $langId, $order, $userId, $categoryId, $blogId);
		return $row;
	}
}
?>
