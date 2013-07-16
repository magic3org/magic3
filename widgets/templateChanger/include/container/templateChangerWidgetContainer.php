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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: templateChangerWidgetContainer.php 2248 2009-08-24 05:22:21Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/templateChangerDb.php');
require_once($gEnvManager->getCommonPath() . '/htmlEdit.php');

class templateChangerWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $selectMenuArray = array();
	private $currentTemplate;	// 選択中のテンプレート
	const DEFAULT_TITLE = 'テンプレートチェンジャー';		// デフォルトのウィジェットタイトル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new templateChangerDb();
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
		$this->currentTemplate = $this->gEnv->getCurrentTemplateId();// 選択中のテンプレート
		
		// すべてのテンプレートを取得
		$this->db->getAllTemplateList(array($this, 'selectMenuLoop'));

		// テンプレート選択用メニュー作成
		$html = HtmlEdit::createSelectMenu($this->selectMenuArray, M3_SYSTEM_TAG_CHANGE_TEMPLATE, "class=\"button\" onchange=\"showprevimage();\" style=\"width:140px;\"");

		$imagePath = $this->getUrl($this->gEnv->getCurrentTemplateUrl() . '/template_thumbnail.png');
		$this->tmpl->addVar("_widget", "TMPL_IMAGE", $imagePath);							// プレビュー画像
		$this->tmpl->addVar("_widget", "CUR_TMPL", $this->gEnv->getCurrentTemplateId());
		$this->tmpl->addVar("_widget", "CHANGE_TEMPLATE", M3_REQUEST_CMD_CHANGE_TEMPLATE);	// テンプレート変更処理
		$this->tmpl->addVar("_widget", "SEL_BUTTON_LABEL", '選択');						// 選択ボタンラベル
		$this->tmpl->addVar("_widget", "SEL_LIST", $html);								// テンプレート選択メニュー
		$this->tmpl->addVar("_widget", "SEL_TEMPLATE", M3_SYSTEM_TAG_CHANGE_TEMPLATE);		// テンプレート選択タグ
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
	 * メニューを作成
	 *
	 * @param int    $index			行番号
	 * @param array  $fetchedRow	取得行
	 * @param object $param			未使用
	 * @return bool					trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function selectMenuLoop($index, $fetchedRow, $param)
	{
		$menuItems = new SelectMenuItem();
		$menuItems->name = strlen($fetchedRow['tm_name']) ? $fetchedRow['tm_name'] : $fetchedRow['tm_id'];
		$menuItems->value = $fetchedRow['tm_id'];
		if ($menuItems->value == $this->currentTemplate) $menuItems->selected = true;
		$this->selectMenuArray[] = $menuItems;
		return true;
	}
}
?>
