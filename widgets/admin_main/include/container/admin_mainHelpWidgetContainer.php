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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainHelpWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const BREADCRUMB_TITLE = 'ヘルプ';		// 画面タイトル名(パンくずリスト)
	const NAV_ID = 'helplink';				// ヘルプ項目取得用ナビゲーションID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
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
		return 'help.tmpl.html';
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

		// ヘルプ項目を取得
		$this->db->getNavItemsByLoop(self::NAV_ID, 0/*第1階層*/, array($this, 'itemListLoop'));
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// パンくずリストの作成
		$this->gPage->setAdminBreadcrumbDef(array(self::BREADCRUMB_TITLE));
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
		// 現在の言語に対応したテキストを取得
		$name = $this->getCurrentLangString($fetchedRow['ni_name']);
		$detail = $this->getCurrentLangString($fetchedRow['ni_help_body']);		// 説明
		
		// リンクを付加
		$itemTag  = '<a href="#">' . $this->convertToDispString($name) . '</a>';	//$this->gDesign->createAdminPageLink($this->convertToDispString($name), $fetchedRow['ni_url']);
		$itemTag .= '<div>';
		$itemTag .= $this->convertToDispString($detail) . ' ';
		$itemTag .= $this->gDesign->createAdminPageLink('<i class="glyphicon glyphicon-new-window"></i>', $fetchedRow['ni_url']);			// リンクを付加
		$itemTag .= '</div>';
		
		$row = array(
			'item' => $itemTag
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		return true;
	}
}
?>
