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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainBaseWidgetContainer.php');

class admin_mainFilebrowserWidgetContainer extends admin_mainBaseWidgetContainer
{
//	private $openByDialog;	// CKEditorからの起動かどうか
	
	// ##### 注意 elFinder2.0-rc1はjQuery1.7以下でしか動かない #####
	// #####      elFinder2.0はjQuery1.8で動作可能             #####
//	const FILE_BROWSER_PATH			= '/elfinder-2.1/php/connector.php';		// ファイルブラウザのパス
//	const DIALOG_FIX_CSS 			= 'body { margin: 0; } #elfinder { border: none; } .elfinder-toolbar, .elfinder-statusbar { border-radius: 0 !important; }';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// Bootstrapの使用を強制キャンセル
//		$this->gPage->cancelBootstrap();
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
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
//		if ($openBy == 'dialog') $this->openByDialog = true;			// CKEditorから開いた場合
		
//		if ($this->openByDialog){	// CKEditorからの起動かどうか
//			return 'filebrowser_ckeditor.tmpl.html';
//		} else {
			return 'filebrowser.tmpl.html';
//		}
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
//		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
//		$connectorUrl = $this->getUrl($this->gEnv->getScriptsUrl() . self::FILE_BROWSER_PATH);
//		$this->tmpl->addVar('_widget', 'url', $connectorUrl);	// ファイルブラウザ接続先URL
		$this->tmpl->addVar('_widget', 'lang', $this->_langId);		// 表示言語
	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
/*	function _addCssFileToHead($request, &$param)
	{
		return array($this->getUrl($this->gEnv->getScriptsUrl() . self::FILEBROWSER_CSS_FILE),
					$this->getUrl($this->gEnv->getScriptsUrl() . self::FILEBROWSER_PLUS_CSS_FILE),
					$this->getUrl($this->gEnv->getAdminDefaultThemeUrl()),			// テンプレートの読み込み順調整のためダミーでデフォルトテンプレートを読み込ませる
					$this->getUrl($this->gEnv->getThemesUrl() . self::DEFAULT_THEME_CSS_FILE));		// 「smoothness」テンプレート
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
/*	function _addScriptFileToHead($request, &$param)
	{
		$scriptArray = array($this->getUrl($this->gEnv->getScriptsUrl() . self::FILEBROWSER_SCRIPT_FILE),
							$this->getUrl($this->gEnv->getScriptsUrl() . self::FILEBROWSER_LANG_FILE));
		return $scriptArray;
	}*/
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
//		if ($this->openByDialog){	// CKEditorからの起動かどうか
//			return self::DIALOG_FIX_CSS;
//		} else {
			return '';
//		}
	}
}
?>
