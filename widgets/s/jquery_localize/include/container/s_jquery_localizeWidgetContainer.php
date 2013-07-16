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
 * @version    SVN: $Id: s_jquery_localizeWidgetContainer.php 4541 2012-01-01 12:23:25Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class s_jquery_localizeWidgetContainer extends BaseWidgetContainer
{
//	private $langId;		// 現在の言語
//	private $initScript;	// 初期化用スクリプト
//	private $autoBackButton;		// 自動的に戻るボタンを表示するかどうか
//	const DEFAULT_CONFIG_ID = 0;
//	const DEFAULT_TITLE = 'jQueryページ専用ヘッダ';			// デフォルトのウィジェットタイトル
	const INIT_SCRIPT_FILE = '/init.js';					// メニュー初期化ファイル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
		return '';
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
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
/*	function _setTitle($request, &$param)
	{
		return self::DEFAULT_TITLE;
	}*/
	/**
	 * JavascriptファイルをHTMLヘッダ部に設定
	 *
	 * JavascriptファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						Javascriptファイル。出力しない場合は空文字列を設定。
	 */
	function _addPreMobileScriptFileToHead($request, &$param)
	{
		return $this->getUrl($this->gEnv->getCurrentWidgetScriptsUrl() . self::INIT_SCRIPT_FILE);
	}
	/**
	 * テンプレートデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
/*	function makeInitScript($tmpl)
	{
		$valueStr = 'false';
		if (!empty($this->autoBackButton)) $valueStr = 'true';		// 自動的に戻るボタンを表示するかどうか
		$tmpl->addVar("_tmpl", "auto_back_button",	$valueStr);
	}*/
}
?>
