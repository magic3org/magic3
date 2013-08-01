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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainResbrowseWidgetContainer.php 6132 2013-06-25 05:29:46Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainMainteBaseWidgetContainer.php');

class admin_mainResbrowseWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	//const FILE_BROWSER_PATH = '/editor/plugins/FileBrowser_Thumbnail/browser.html?Lang=%s&Connector=connectors/php/connector.php';		// ファイルブラウザのパス
	
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
		return 'resbrowse.tmpl.html';
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
/*		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		$browserPath = $this->gEnv->getScriptsUrl() . '/' . ScriptLibInfo::FCKEDITOR_DIRNAME . sprintf(self::FILE_BROWSER_PATH, $langId);
		$this->tmpl->addVar('_widget', 'url', $this->getUrl($browserPath));
		$this->tmpl->addVar('_widget', 'res_dir', $this->gEnv->getResourcePath());// リソースディレクトリ
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['label_res_dir'] = $this->_('Resource Directory:');		// リソースディレクトリ：
		$localeText['msg_browser_not_support'] = $this->_('Your browser does not support iframes.');		// ブラウザがiframeに対応していません
		$this->setLocaleText($localeText);
		*/
		$url = '?task=filebrowser&openby=iframe';
		$this->tmpl->addVar("_widget", "url", $url);
	}
}
?>
