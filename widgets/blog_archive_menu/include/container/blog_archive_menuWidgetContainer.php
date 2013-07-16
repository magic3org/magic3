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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: blog_archive_menuWidgetContainer.php 5270 2012-10-04 12:19:21Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blog_archive_menuDb.php');

class blog_archive_menuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 言語
	const TARGET_WIDGET = 'blog_main';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'ブログアーカイブ';		// デフォルトのウィジェットタイトル名
		
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
		
		// #### カテゴリーリストを作成 ####
		$ret = $this->db->getAllEntry($this->langId, $rows);// デフォルト言語で取得
		if ($ret){
			$foreYear = 0;
			$foreMonth = 0;
			$entryCount = 0;		// 記事数
			$rowCount = count($rows);
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
			}
		}
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
