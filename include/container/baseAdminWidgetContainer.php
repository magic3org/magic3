<?php
/**
 * ウィジェットコンテナ作成用ベースクラス
 *
 * 管理画面用のウィジェットを作成するためのベースクラスで、BaseWidgetContainerとは以下の点で異なる
 * ・ヘルプシステムが使用可能になる
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class BaseAdminWidgetContainer extends BaseWidgetContainer
{
	const DEFAULT_WIDGET_TYPE = 'admin';		// ウィジェットタイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// データ初期化
		$this->_widgetType = self::DEFAULT_WIDGET_TYPE;						// ウィジェットタイプ
	}
	/**
	 * 出力用の変数に値を設定する
	 * このクラスでは、共通項目を設定
	 */
	function __assign()
	{
		// テンプレートに値を設定
		$now = date("Y/m/d H:i:s");
		
		// システム用変数のデフォルト変換
		$rootUrl = $this->gEnv->getRootUrl();				// ルートURL
		$scriptsUrl = $this->gEnv->getScriptsUrl();		// スクリプト格納URL
		$currentWidgetUrl = $this->gEnv->getCurrentWidgetRootUrl();		// 現在のウィジェット格納URL
		if ($this->gEnv->getUseSslAdmin()){			// 管理画面にSSLを使用する場合
			//$rootUrl = str_replace('http://', 'https://', $rootUrl);
			//$scriptsUrl = str_replace('http://', 'https://', $scriptsUrl);
			//$currentWidgetUrl = str_replace('http://', 'https://', $currentWidgetUrl);
			$rootUrl = $this->gEnv->getSslRootUrl();
			$scriptsUrl = $this->gEnv->getSslScriptsUrl();
			$currentWidgetUrl = $this->gEnv->getCurrentWidgetSslRootUrl();
		}
		//$this->tmpl->addVar("_widget", "_DATE", "created $now");
		$this->tmpl->addVar("_widget", "_ROOT_URL", $rootUrl);
		$this->tmpl->addVar("_widget", "_SCRIPTS_URL", $scriptsUrl);		// 共通スクリプトディレクトリを設定
		$this->tmpl->addVar("_widget", "_WIDGET_URL", $currentWidgetUrl);	// 現在のウィジェット格納URL
		$this->tmpl->addVar("_widget", "_CONFIG_WINDOW_STYLE", $this->gDesign->getConfigWindowStyle());	// 設定画面のウィンドウスタイル

		// ヘルプを設定
		if ($this->gPage->getUseHelp()){		// ヘルプ表示を行う場合
			// ヘルプメソッドが指定されている場合は、ヘルプクラスを使用
			$outputHelp = false;		// ヘルプ出力を行ったかどうか
			$helpKeys = array();
			if (method_exists($this, '_setHelp')){
				$helpId = $this->_setHelp($request, $param);
				$helpKeys = $this->gInstance->getHelpManager()->loadHelp($this->gEnv->getCurrentWidgetId(), false/*新規モード*/, $helpId, $this);
			}

			// ヘルプIDが設定されていない場合は共通ファイルを読み込む
			if (empty($helpKeys)){
				// 代替ウィジェットが設定されている場合は代替ウィジェットのヘルプを読み込む
				if (!empty($this->_defaultWidgetId)) $helpKeys = $this->gInstance->getHelpManager()->loadHelp($this->_defaultWidgetId);

				// カレントウィジェットのヘルプを読み込む
				$isAdd = false;		// 追加モード
				if (!empty($this->_defaultWidgetId)) $isAdd = true;		// 追加モード
				$helpKeys = $this->gInstance->getHelpManager()->loadHelp($this->gEnv->getCurrentWidgetId(), $isAdd);
			}

			// テンプレート上のヘルプタグを変換
			for ($i = 0; $i < count($helpKeys); $i++){
				$key = $helpKeys[$i];
				$helpText = $this->gInstance->getHelpManager()->getHelpText($key);
				$this->tmpl->addGlobalVar(self::HELP_HEAD . $key, $helpText);		// グローバルパラメータを文字列変換
			}
		}
			
		// デバッグ出力があるときは表示
		if ($this->gEnv->getSystemDebugOut() && method_exists($this,'_debugString')){
			$debugStr = $this->_debugString();
			if (strlen($debugStr) > 0){
				$this->tmpl->addVar("_widget", "_DEBUG", $debugStr);
			}
		}
	}
	/**
	 * URLに追加設定するパラメータ
	 *
	 * @param string $key	キー
	 * @param string $value	値
	 * @return 				なし
	 */
	function addOptionUrlParam($key, $value)
	{
//		$param = array($key, $value);
//		array_push($this->optionUrlParam, $param);
		$this->optionUrlParam[$key] = $value;
	}
	/**
	 * パラメータ付きの管理画面用のURLを取得
	 *
	 * @param bool $withPageDef	ページ定義パラメータを追加するかどうか
	 * @return string			パラメータ付きURL
	 */
	function getAdminUrlWithOptionParam($withPageDef = false)
	{
		global $gRequestManager;
		
		$cmd = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		
		if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){			// ウィジェットの設定画面の場合
			$url = $this->gEnv->getDefaultAdminUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET . 
						'&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
			if ($withPageDef){
				$configId = $this->gRequest->trimValueOf(M3_REQUEST_PARAM_PAGE_DEF_CONFIG_ID);
				if (!empty($configId)) $url .= '&' . M3_REQUEST_PARAM_PAGE_DEF_CONFIG_ID . '=' . $configId;		// 定義ID
				$defSerial = $this->gRequest->trimValueOf(M3_REQUEST_PARAM_PAGE_DEF_SERIAL);
				if (!empty($defSerial)) $url .= '&' . M3_REQUEST_PARAM_PAGE_DEF_SERIAL . '=' . $defSerial;				// 画面定義シリアル番号
			}
		} else {
			$url = $this->gEnv->getDefaultAdminUrl();
		}
		// その他のパラメータ
		$url = createUrl($url, $this->optionUrlParam);
/*		foreach ($this->optionUrlParam as $value){
			$url .= '&' . $value[0] . '=' . $value[1];
		}*/
		return $url;
	}
	/**
	 * URLを作成
	 *
	 * ・ページのSSL設定状況に応じて、SSL用URLに変換
	 *
	 * @param string $path				URL作成用のパス
	 * @param bool $isLink				作成するURLがリンクかどうか。リンクの場合はリンク先のページのSSLの状態に合わせる(未使用)
	 * @param string,array $param		URLに付加するパラメータ
	 * @return string					作成したURL
	 */
	function getUrl($path, $isLink = false, $param = '')
	{
		$destPath = '';
		$path = trim($path);

		if ($isLink){		// リンクの場合はページに合わせてSSLを設定する
			$isAdminUrl = $this->gEnv->isAdminUrlAccess($path);
			if (!$isAdminUrl){
				$destPath = parent::getUrl($path, $isLink, $param);
				return $destPath;
			}
		}
		
		// URLの示すファイルタイプを取得
		if ($this->gEnv->getUseSslAdmin()){		// SSLを使用する場合
			$baseUrl = $this->gEnv->getRootUrl();
			$sslBaseUrl = $this->gEnv->getSslRootUrl();

			// パスを解析
			if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
				//$path = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $baseUrl, $path);
				//$destPath = str_replace('http://', 'https://', $path);
				$destPath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $sslBaseUrl, $path);
			} else if (strncasecmp($path, 'http://', strlen('http://')) == 0 || strncasecmp($path, 'https://', strlen('https://')) == 0){				// 絶対パスURLのとき
				//$path = str_replace('http://', 'https://', $path);		// 一旦httpsに統一
				$path = $this->gEnv->getSslUrl($path);				// SSL用のURLに変換
				$relativePath = str_replace($sslBaseUrl, '', $path);		// ルートURLからの相対パスを取得
				if (empty($relativePath)){			// Magic3のルートURLの場合
					$destPath = $sslBaseUrl;
				} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?')){		// ルートURL配下のとき
					$destPath = $this->_createAdminUrlByRelativePath($sslBaseUrl, $relativePath, $param);
				} else {		// ルートURL以外のURLのとき
					$destPath = $path;
				}
			} else {		// 相対パスの場合
			}
		} else {		// SSLを使用しない場合
			if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
				$destPath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $path);
			} else {
				$destPath = $path;
			}
		}
		return $destPath;
	}
	/**
	 * 相対パスからURLを作成
	 *
	 * @param string $sslBaseUrl	SSL使用時のルートURL
	 * @param string $path			相対パス
	 * @param string,array $param		URLに付加するパラメータ
	 * @return string				作成したURLパラメータ
	 */
	function _createAdminUrlByRelativePath($sslBaseUrl, $path, $param = '')
	{
		$destPath = '';
		
		// ファイル名を取得
		$paramArray = array();
		list($filename, $query) = explode('?', basename($path));
		$saveFilename = $filename;		// ファイル名を退避
		if (empty($filename)) $filename = M3_FILENAME_INDEX;

		if (!empty($query)) parse_str($query, $paramArray);
		if (is_array($param)){
			$paramArray = array_merge($paramArray, $param);
		} else if (is_string($param) && !empty($param)){
			parse_str($param, $addArray);
			$paramArray = array_merge($paramArray, $addArray);
		}
		// ページIDを取得
		if (strEndsWith($filename, '.php')){			// PHPスクリプトのとき
			$destPath = $sslBaseUrl;
			//$destPath .= dirname($path) . $saveFilename;
			$dirName = dirname($path);
			if ($dirName == '/'){
				$destPath .= $dirName . $saveFilename;
			} else {
				$destPath .= $dirName . '/' . $saveFilename;
			}
			$paramStr = $this->_createParamStr($paramArray);
			if (!empty($paramStr)) $destPath .= '?' . $paramStr;
		} else {
			$destPath = $sslBaseUrl . $path;
		}
		return $destPath;
	}
}
?>
