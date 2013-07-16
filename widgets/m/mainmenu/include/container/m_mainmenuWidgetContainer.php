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
 * @version    SVN: $Id: m_mainmenuWidgetContainer.php 2201 2009-08-05 01:42:16Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/mainmenuDb.php');

class m_mainmenuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $outputText;	// 作成したメニュー
	private $menuType;	// メニュータイプ(0=テーブル、1=リスト)
	const MAIN_MENU_ID = 'mobile_menu';			// メニューID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new mainmenuDb();
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
		// メニュー情報を取得
		$ret = $this->db->getMenu(self::MAIN_MENU_ID, $this->gEnv->getCurrentLanguage(), $row);
		if (!$ret){// 現在の言語で作成できない場合はデフォルト言語で作成
			$ret = $this->db->getMenu(self::MAIN_MENU_ID, $this->gEnv->getDefaultLanguage(), $row);
		}
		if ($ret){
			$this->menuType = $row['me_type'];	// メニュータイプ
		}
		// メニューデータの作成
		$this->outputText = '';
		
		// メインメニューの項目取得
		// 現在の言語で作成
		$this->db->getMenuItems(array($this, 'itemsLoop'), self::MAIN_MENU_ID, $this->gEnv->getCurrentLanguage());
		if ($this->outputText == ''){// 現在の言語で作成できない場合はデフォルト言語で作成
			$this->db->getMenuItems(array($this, 'itemsLoop'), self::MAIN_MENU_ID, $this->gEnv->getDefaultLanguage());
		}

		// 作成したメニュー出力
		$this->tmpl->addVar("_widget", "MENU", $this->outputText);
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
		// リンクタイプに合わせてタグを生成
		$option = '';
		switch ($fetchedRow['mi_link_type']){
			case 0:			// 同ウィンドウで開くリンク
				break;
			case 1:			// 別ウィンドウで開くリンク
				$option = 'target="_blank"';
				break;
		}
		
		// 「mainlevel」「sublevel」「active_menu」クラス名を設定する
		$name = '';
		if ($fetchedRow['mi_show_name']){	// 名前を表示するとき
			$name = $fetchedRow['mi_name'];
		}
		// リンク先の作成
		$linkUrl = $fetchedRow['mi_link_url'];
		$linkUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $linkUrl);
		if ($fetchedRow['mi_enable']){		// 遷移可能なとき
			$link = '<a href="' . $linkUrl . '" class="mainlevel" ' . $option . '>' . $name . '</a>';
		} else {
			$link = $name;
		}
		
		$this->outputText .= $link . '<br />' . M3_NL;
		return true;
	}
}
?>
