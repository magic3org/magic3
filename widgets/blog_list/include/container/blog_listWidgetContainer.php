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
 * @version    SVN: $Id: blog_listWidgetContainer.php 5269 2012-10-04 12:05:11Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/blog_listDb.php');

class blog_listWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 言語
	private $showWidget;		// ウィジェットを表示するかどうか
	const TARGET_WIDGET = 'blog_main';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'ブログリスト';		// デフォルトのウィジェットタイトル名
	const BLOG_OBJ_ID = 'bloglib';		// ブログオブジェクトID
	const CF_USE_MULTI_BLOG			= 'use_multi_blog';		// マルチブログ機能を使用するかどうか

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new blog_listDb();
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
		return 'main.tmpl.html';
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
		
		// ブログリストを作成
		$this->db->getAllBlog(array($this, 'blogListLoop'));
		
		// 表示データがない場合はウィジェットを表示しない
		if (empty($this->showWidget)) $this->cancelParse();// 出力抑止
		
		$blogLibObj = $this->gInstance->getObject(self::BLOG_OBJ_ID);
		if (isset($blogLibObj)){
			$value = $blogLibObj->getConfig(self::CF_USE_MULTI_BLOG);
			if (!$value) $this->SetMsg(self::MSG_APP_ERR, "マルチブログモードが選択されていません");
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
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function blogListLoop($index, $fetchedRow, $param)
	{
		// リンク先の作成
		$name = $fetchedRow['bl_name'];
		$linkUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ID . '=' . $fetchedRow['bl_id'];
		$row = array(
			'link_url' => $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true/*リンク用*/)),		// リンク
			'name' => $this->convertToDispString($name)			// タイトル
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		$this->showWidget = true;		// ウィジェットを表示
		return true;
	}
}
?>
