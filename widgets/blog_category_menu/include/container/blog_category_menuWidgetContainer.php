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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blog_category_menuDb.php');

class blog_category_menuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $isExistsList;	// 一覧表示項目があるかどうか
	const TARGET_WIDGET = 'blog_main';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'ブログカテゴリー';		// デフォルトのウィジェットタイトル名
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new blog_category_menuDb();
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
		return 'index.tmpl.html';
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
		// #### カテゴリーリストを作成 ####
		$this->db->getAllCategory(array($this, 'categoryListLoop'), $this->_langId);// デフォルト言語で取得
		if (!$this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 一覧非表示
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
		$categoryId = $fetchedRow['bc_id'];
		$name = $fetchedRow['bc_name'];
		
		// リンク先の作成
		$linkUrl = $this->gPage->createContentPageUrl(M3_VIEW_TYPE_BLOG, M3_REQUEST_PARAM_CATEGORY_ID . '=' . $categoryId);// カテゴリー画面へのURLを作成
		
		// クラス名
		$classAttr = '';
		if ($this->_renderType == M3_RENDER_WORDPRESS){		// WordPressテンプレートの場合
			$classAttr = 'class="cat-item cat-item-' . $categoryId . '"';
		}
		
		$row = array(
			'link_url'	=> $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)),		// リンク
			'name'		=> $this->convertToDispString($name),			// タイトル
			'class'		=> $classAttr									// クラス名
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		$this->isExistsList = true;	// 一覧表示項目があるかどうか
		return true;
	}
}
?>
