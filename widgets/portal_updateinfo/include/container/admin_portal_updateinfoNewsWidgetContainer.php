<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ポータル用コンテンツ更新情報
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2009 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_portal_updateinfoNewsWidgetContainer.php 2724 2009-12-21 07:41:16Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_portal_updateinfoBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/portal_updateinfoDb.php');

class admin_portal_updateinfoNewsWidgetContainer extends admin_portal_updateinfoBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $lang;		// 現在の選択言語
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $isExistsList;		// リスト項目が存在するかどうか
	const CONTENT_TYPE = 'content';		// 取得コンテンツタイプ
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new portal_updateinfoDb();
		$this->sysDb = $this->gInstance->getSytemDbObject();
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
		if ($task == 'news_detail'){		// 詳細画面
			return 'admin_news_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_news.tmpl.html';
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
		if ($task == 'news_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
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
		$this->langId = $this->gEnv->getDefaultLanguage();
		$this->itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->itemCount	= $paramObj->itemCount;
		}
		
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
				$ret = $this->db->delUpdateInfoItem($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		} else if ($act == 'selpage'){			// ページ選択
		}
		// 一覧を作成
		$this->db->getUpdateInfoList(self::CONTENT_TYPE, intval($this->itemCount), array($this, 'itemsLoop'));

		// 非表示項目を設定
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
		// 一覧項目がないときは、一覧を表示しない
		if (!$this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
	}
	/**
	 * 取得したメニュー項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$name = $fetchedRow['nw_name'];
		$linkUrl = $fetchedRow['nw_link'];		// コンテンツへのリンク
		$message = $fetchedRow['nw_message'];
		$siteLink = $fetchedRow['nw_site_link'];
		$siteName = $fetchedRow['nw_site_name'];
		
		if (!empty($name)){
			$row = array(
				'index' => $index,										// 項目番号
				'link_url' => $this->convertUrlToHtmlEntity($linkUrl),		// リンク
				'name' => $this->convertToDispString($name),			// タイトル
				'message' => $this->convertToDispString($message),		// メッセージ
				'site_link' => $this->convertUrlToHtmlEntity($siteLink),	// サイトへのリンク
				'site_name' => $this->convertToDispString($siteName),		// サイト名
				'reg_date' => $this->convertToDispDateTime($fetchedRow['nw_regist_dt'])		// 登録日時
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
		
			// 表示中の項目のシリアル番号を保存
			$this->serialArray[] = $fetchedRow['nw_serial'];
		
			$this->isExistsList = true;		// リスト項目が存在するかどうか
		}
		return true;
	}
}
?>
