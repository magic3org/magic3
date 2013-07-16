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
 * @version    SVN: $Id: s_blog_categoryWidgetContainer.php 4749 2012-03-13 03:12:29Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/s_blog_categoryDb.php');

class s_blog_categoryWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 言語
	const TARGET_WIDGET = 's/blog';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'ブログカテゴリー';		// デフォルトのウィジェットタイトル名
	const DEFAULT_LIST_TITLE = 'ブログカテゴリー';			// デフォルトのリストタイトル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new s_blog_categoryDb();
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
		// デフォルト値取得
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$title = self::DEFAULT_LIST_TITLE;	// タイトル
		$theme = 'c';		// メニューのテーマ
		$insetList = 1;		// インセットリスト形式で表示するかどうか
		
		// パラメータオブジェクトを取得
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){		// 定義データが取得できたとき
			$title		= $paramObj->title;// リストタイトル
			$theme		= $paramObj->theme;		// メニューのテーマ
			$insetList	= $paramObj->insetList;		// インセットリスト形式で表示するかどうか
		}
		
		// #### カテゴリーリストを作成 ####
		$this->db->getAllCategory(array($this, 'categoryListLoop'), $this->langId);// デフォルト言語で取得
		
		// 表示データ埋め込み
		if (empty($title)){
			$this->tmpl->setAttribute('listtitle', 'visibility', 'hidden');
		} else {
			$this->tmpl->addVar("listtitle", "title",	$this->convertToDispString($title));
		}
		$listOption = '';
		if (!empty($insetList)){		// インセットリスト形式で表示するかどうか
			$listOption .= ' data-inset="true"';
		}
		if (!empty($theme)){
			$listOption .= ' data-theme="' . $theme . '"';
		}
		$this->tmpl->addVar("_widget", "list_option",	$listOption);
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
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function categoryListLoop($index, $fetchedRow, $param)
	{
		$total = $fetchedRow['total'];		// 記事数
		
		// リンク先の作成
		$name = $fetchedRow['bc_name'];
		$linkUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, M3_REQUEST_PARAM_CATEGORY_ID . '=' . $fetchedRow['bc_id']);
		
		$row = array(
			'link_url'	=> $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)),		// リンク
			'name'		=> $this->convertToDispString($name),			// タイトル
			'total'		=> $total			// 記事数
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		return true;
	}
}
?>
