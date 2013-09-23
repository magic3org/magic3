<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blog_archive_menuDb.php');

class blog_archive_menuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 言語
	private $isExistsListItem;	// 一覧表示項目があるかどうか
	const DEFAULT_CONFIG_ID = 0;
	const TARGET_WIDGET = 'blog_main';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'ブログアーカイブ';		// デフォルトのウィジェットタイトル名
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new blog_archive_menuDb();
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
		return 'menu.tmpl.html';
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
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// デフォルト値設定
		$itemCount	= self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$archiveType	= 0;		// アーカイブタイプ
		$sortOrder	= 0;		// ソート順
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$itemCount	= $targetObj->itemCount;
			$archiveType	= $targetObj->archiveType;		// アーカイブタイプ
			$sortOrder	= $targetObj->sortOrder;		// ソート順
		}
		
		// #### アーカイブリストを作成 ####
		$listItemCount = 0;			// 表示項目数
		$ret = $this->db->getAllEntry($this->langId, $sortOrder, $rows);// デフォルト言語で取得
		if ($ret){
			$foreYear = 0;
			$foreMonth = 0;
			$entryCount = 0;		// 記事数
			$rowCount = count($rows);
			
			if (empty($archiveType)){		// 月別アーカイブのとき
				for ($i = 0; $i < $rowCount; $i++){
					// 記事の投稿日を取得
					$this->timestampToYearMonthDay($rows[$i]['be_regist_dt'], $year, $month, $day);
		
					if ($year == $foreYear && $month == $foreMonth){		// 年月が変わらないとき
						$entryCount++;		// 記事数
					} else {		// 年月が変更のとき
						// メニュー項目を作成
						if ($entryCount > 0){		// 記事数が0以上のとき
							$name = $foreYear . '年' . $foreMonth . '月(' . $entryCount . ')';
							$linkUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'act=view&year=' . $foreYear . '&month=' . $foreMonth);
							$row = array(
								'link_url' => $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)),		// リンク
								'name' => $this->convertToDispString($name)			// タイトル
							);
							$this->tmpl->addVars('itemlist', $row);
							$this->tmpl->parseTemplate('itemlist', 'a');
						
							$listItemCount++;			// 表示項目数
							if ($itemCount > 0 && $listItemCount >= $itemCount){		// 表示項目数の最大を超えたかどうか
								$entryCount = 0;		// メニュー項目追加を終了
								break;
							}
						}
					
						// データを初期化
						$foreYear = $year;
						$foreMonth = $month;
						$entryCount = 1;	// 記事数
					}
				}
				// メニュー項目を作成
				if ($entryCount > 0){		// 記事数が0以上のとき
					$name = $foreYear . '年' . $foreMonth . '月(' . $entryCount . ')';
					$linkUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'act=view&year=' . $foreYear . '&month=' . $foreMonth);
					$row = array(
						'link_url' => $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)),		// リンク
						'name' => $this->convertToDispString($name)			// タイトル
					);
					$this->tmpl->addVars('itemlist', $row);
					$this->tmpl->parseTemplate('itemlist', 'a');
				
					$listItemCount++;			// 表示項目数
				}
			} else {			// 年別アーカイブのとき
				for ($i = 0; $i < $rowCount; $i++){
					// 記事の投稿日を取得
					$this->timestampToYearMonthDay($rows[$i]['be_regist_dt'], $year, $month, $day);
		
					if ($year == $foreYear){		// 月が変わらないとき
						$entryCount++;		// 記事数
					} else {		// 年が変更のとき
						// メニュー項目を作成
						if ($entryCount > 0){		// 記事数が0以上のとき
							$name = $foreYear . '年(' . $entryCount . ')';
							$linkUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'act=view&year=' . $foreYear);
							$row = array(
								'link_url' => $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)),		// リンク
								'name' => $this->convertToDispString($name)			// タイトル
							);
							$this->tmpl->addVars('itemlist', $row);
							$this->tmpl->parseTemplate('itemlist', 'a');
						
							$listItemCount++;			// 表示項目数
							if ($itemCount > 0 && $listItemCount >= $itemCount){		// 表示項目数の最大を超えたかどうか
								$entryCount = 0;		// メニュー項目追加を終了
								break;
							}
						}
					
						// データを初期化
						$foreYear = $year;
						$entryCount = 1;	// 記事数
					}
				}
				// メニュー項目を作成
				if ($entryCount > 0){		// 記事数が0以上のとき
					$name = $foreYear . '年(' . $entryCount . ')';
					$linkUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'act=view&year=' . $foreYear);
					$row = array(
						'link_url' => $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)),		// リンク
						'name' => $this->convertToDispString($name)			// タイトル
					);
					$this->tmpl->addVars('itemlist', $row);
					$this->tmpl->parseTemplate('itemlist', 'a');
				
					$listItemCount++;			// 表示項目数
				}
			}
		}
		if ($listItemCount <= 0) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 一覧非表示
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return self::DEFAULT_TITLE;
	}
}
?>
