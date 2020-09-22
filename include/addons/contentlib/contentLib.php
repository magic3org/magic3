<?php
/**
 * 汎用コンテンツ情報取得クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(dirname(__FILE__) . '/contentLibDb.php');

class contentLib
{
	private $db;	// DB接続オブジェクト
	private $templateId;	// テンプレートID
	private $subTemplateId;	// サブテンプレートID
	private $configArray;	// 汎用コンテンツ定義値
	const CF_USE_CONTENT_TEMPLATE	= 'use_content_template';		// コンテンツ単位のテンプレート設定を行うかどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new contentLibDb();
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
		$deviceType = $gEnvManager->getCurrentPageDeviceType();
	
		// コンテンツタイプを取得
		switch ($deviceType){
			case 1:		// 携帯
				$contentType = 'mobile';			// コンテンツタイプ
				break;
			case 2:		// スマートフォン
				$contentType = 'smartphone';			// コンテンツタイプ
				break;
			case 0:		// PC
			default:
				$contentType = '';
		}
		
		// DB定義値を取得
		$this->configArray = $this->_loadConfig($contentType, $this->db);
		
		// URLからコンテンツIDを取得
		$contentId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID);
		if (empty($contentId)) $contentId = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_CONTENT_ID_SHORT);		// 略式汎用コンテンツID
		if (!empty($contentId)){
			$ret = $this->db->getContent($contentType, $contentId, $langId, $row);
			if ($ret){
				if ($this->configArray[self::CF_USE_CONTENT_TEMPLATE]){
					$this->templateId = $row['cn_template_id'];
					$this->subTemplateId = $row['cn_sub_template_id'];	// サブテンプレートID
				}
			}
		}
		$init = true;		// 初期化完了
	}
	/**
	 * 汎用コンテンツ定義値をDBから取得
	 *
	 * @param string $contentType	コンテンツタイプ(空文字列=PC、mobile=携帯、smartphone=スマートフォン)
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	function _loadConfig($contentType, $db)
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
		$ret = $this->db->getAllConfig($contentType, $rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['ng_id'];
				$value = $rows[$i]['ng_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * URLパラメータからオプションのテンプレートを取得
	 *
	 * @return array					テンプレートID,サブテンプレートIDの配列
	 */
	function getOptionTemplate()
	{
		// 初期化
		$this->_initData();
		
		return array($this->templateId, $this->subTemplateId);
	}
	/**
	 * 汎用コンテンツ定義値を取得
	 *
	 * @param string $key			定義キー。空の場合は全データ取得
	 * @return array,string			汎用コンテンツ定義値
	 */
	function getConfig($key = '')
	{
		// 初期化
		$this->_initData();
		
		if (empty($key)){
			return $this->configArray;
		} else {
			return $this->configArray[$key];
		}
	}
	/**
	 * コンテンツ一覧の表示設定を取得
	 *
	 * @return array				表示項目数,ソート順(0=昇順,1=降順),サムネール表示可否の配列
	 */
	function getPublicContentViewConfig()
	{
	//	$itemCount = $this->getConfig(self::CF_ENTRY_VIEW_COUNT);
		$itemCount = 10;
		$showThumb = 0;
		return array($itemCount, 1, $showThumb);
	}
	/**
	 * 公開中の記事数を取得。アクセス制限も行う。
	 *
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @return int							項目数
	 */
	function getPublicContentCount($now, $startDt, $endDt, $keywords, $langId, $categoryId = null)
	{
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		$itemCount = $this->db->getPublicContentItemsCount($now, $startDt, $endDt, $keywords, $langId, $userId, $categoryId);
		return $itemCount;
	}
	/**
	 * 公開中のエントリー項目を取得。アクセス制限も行う。
	 *
	 * @param int		$limit				取得する項目数
	 * @param int		$page				取得するページ(1～)
	 * @param int,array	$contentId			コンテンツID(0のときは期間で取得)
	 * @param timestamp $now				現在日時(現在日時より未来の投稿日時の記事は取得しない)
	 * @param timestamp	$startDt			期間(開始日)
	 * @param timestamp	$endDt				期間(終了日)
	 * @param string,array	$keywords		検索キーワード
	 * @param string	$langId				言語
	 * @param int		$order				取得順(0=昇順,1=降順)
	 * @param function	$callback			コールバック関数
	 * @param int		$categoryId			カテゴリーID(nullのとき指定なし)
	 * @return 			なし
	 */
	function getPublicContentList($limit, $page, $contentId, $now, $startDt, $endDt, $keywords, $langId, $order, $callback, $categoryId = null)
	{
		global $gEnvManager;
		
		$userId = $gEnvManager->getCurrentUserId();
		$this->db->getPublicContentItems($limit, $page, $contentId, $now, $startDt, $endDt, $keywords, $langId, $order, $userId, $callback, $categoryId);
	}

	/**
	 * 汎用コンテンツを新規追加
	 *
	 * @param string  $name			コンテンツ名
	 * @param string  $html			HTML
	 * @param string  $metaTitle	METAタグ、タイトル
	 * @param string  $metaDesc		METAタグ、ページ要約
	 * @param string  $metaKeyword	METAタグ、検索用キーワード
	 * @param int     $newID		新規コンテンツID
	 * @return bool					true = 成功、false = 失敗
	 */
	function addContent($name, $html, $metaTitle, $metaDesc, $metaKeyword, &$newID)
	{
		global $gEnvManager;
		
		$langId = $gEnvManager->getDefaultLanguage();
		$contentType = '';
		$startDt = $endDt = $gEnvManager->getInitValueOfTimestamp();
		
		$ret = $this->db->addContentItem($contentType, 
											$langId, $name, ''/*説明*/, $html, 1/*表示*/, 0/*未使用(デフォルトかどうか)*/, 0/*ユーザ制限なし*/, ''/*外部参照キー*/, ''/*パスワードなし*/, 
											$metaTitle, $metaDesc, $metaKeyword, ''/*ヘッダ部その他*/, $startDt, $endDt, true/*コンテンツIDは再利用で取得*/, $newSerial);
		if (!$ret) return false;
		
		// コンテンツID取得
		$ret = $this->db->getContentBySerial($newSerial, $row);
		if ($ret) $newID = $row['cn_id'];		// コンテンツID
		return $ret;
	}
}
?>
