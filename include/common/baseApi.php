<?php
/**
 * ペースApiクラス
 *
 * コンテナクラス以外にインターフェイスを提供する
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class BaseApi extends Core
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	
	/**
	 * URLを作成
	 *
	 * ・ページのSSL設定状況に応じて、SSL用URLに変換
	 *
	 * @param string $path				URL作成用のパス
	 * @param bool $isLink				作成するURLがリンクかどうか。リンクの場合はリンク先のページのSSLの状態に合わせる
	 * @param string,array $param		URLに付加するパラメータ
	 * @return string					作成したURL
	 */
	function getUrl($path, $isLink = false, $param = '')
	{
		global $gEnvManager;
		global $gPageManager;
		
		$destPath = '';
		$path = trim($path);
		
		// URLの示すファイルタイプを取得
		if ($gEnvManager->getUseSsl()){		// SSLを使用する場合
			// 現在のページがSSL使用設定になっているかどうかを取得
			$currentPageId = $gEnvManager->getCurrentPageId();
			$currentPageSubId = $gEnvManager->getCurrentPageSubId();
			$isSslPage = $gPageManager->isSslPage($currentPageId, $currentPageSubId);
			
			$baseUrl = $gEnvManager->getRootUrl();
			$sslBaseUrl = $gEnvManager->getSslRootUrl();		// SSL用URLが設定されている場合、設定値を使用
			
			// パスを解析
			if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
				$relativePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '', $path);
				if (empty($relativePath)){			// Magic3のルートURLの場合
					if ($isLink){		// リンクタイプのとき
						$destPath = $baseUrl;
					} else {		// リンクでないとき
						// 現在のページのSSLの状態に合わせる
						if ($isSslPage){
							$destPath = $sslBaseUrl;
						} else {
							$destPath = $baseUrl;
						}
					}
					// トップページへのリンクの場合とどう区別するか→トップページへのリンクの場合はフルパスで指定?
				} else {
					$destPath = $this->_createUrlByRelativePath($isSslPage, $baseUrl, $sslBaseUrl, $relativePath, $param, $isLink);
				}
			} else if (strncasecmp($path, 'http://', strlen('http://')) == 0 || strncasecmp($path, 'https://', strlen('https://')) == 0){				// 絶対パスURLのとき
				// パスを解析
				$relativePath = str_replace($baseUrl, '', $path);		// ルートURLからの相対パスを取得
				if (empty($relativePath)){			// Magic3のルートURLの場合
					if ($isLink){		// リンクタイプのとき
						$destPath = $baseUrl;
					} else {		// リンクでないとき
						// 現在のページのSSLの状態に合わせる
						if ($isSslPage){
							$destPath = $sslBaseUrl;
						} else {
							$destPath = $baseUrl;
						}
					}
					// トップページへのリンクの場合とどう区別するか→トップページへのリンクの場合はフルパスで指定?
				} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?')){		// ルートURL配下のとき
					$destPath = $this->_createUrlByRelativePath($isSslPage, $baseUrl, $sslBaseUrl, $relativePath, $param, $isLink);
				} else {		// ルートURL以外のURLのとき
					$destPath = $path;
				}
			} else {		// 相対パスのとき
			}
		} else {		// SSLを使用しない場合
			if ($this->_useHierPage){		// 階層化ページ使用のとき
				$createPath = true;		// パスを生成するかどうか
				$baseUrl = $gEnvManager->getRootUrl();
				$sslBaseUrl = $gEnvManager->getSslRootUrl();		// SSL用URLが設定されている場合、設定値を使用
				if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
					$relativePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '', $path);
				} else if (strncasecmp($path, 'http://', strlen('http://')) == 0 || strncasecmp($path, 'https://', strlen('https://')) == 0){				// 絶対パスURLのとき
					$relativePath = str_replace($baseUrl, '', $path);		// ルートURLからの相対パスを取得
					if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?')){		// ルートURL配下のとき
					} else {		// ルートURL以外のURLのとき
						$createPath = false;		// パスを生成するかどうか
					}
				}
				if ($createPath){		// パスを生成するかどうか
					$destPath = $this->_createUrlByRelativePath(false, $baseUrl, $sslBaseUrl, $relativePath, $param);
				} else {
					$destPath = $path;
				}
			} else {
				if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
					$destPath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $gEnvManager->getRootUrl(), $path);
				} else {
					$destPath = $path;
				}
			}
		}
		// マルチドメイン運用の場合はパスを修正
		if ($this->_isMultiDomain){
			if ($gEnvManager->getIsSmartphoneSite()){		// スマートフォンサイトの場合
				$domainUrl = $gEnvManager->getDefaultSmartphoneUrl(false/*ファイル名なし*/);
				$relativePath = str_replace($domainUrl . '/' . M3_DIR_NAME_SMARTPHONE, '', $destPath);
				if (strStartsWith($relativePath, '/')){
					$destPath = $domainUrl . $relativePath;
				} else {
					// メインのドメインの場合はアクセスポイント用ドメインに変換
					$relativePath = str_replace(M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_SMARTPHONE, '', $destPath);
					if (strStartsWith($relativePath, '/')) $destPath = $domainUrl . $relativePath;
				}
			}
		}
		return $destPath;
	}
	/**
	 * 多言語対応文字列から現在の言語の文字列を取得
	 *
	 * @param string $str		多言語対応文字列
	 * @return string			現在の言語文字列(存在しない場合はデフォルト言語文字列)
	 */
	function getCurrentLangString($str)
	{
		$langs = $this->unserializeLangArray($str);
		$langStr = $langs[$this->gEnv->getCurrentLanguage()];
		if (isset($langStr)){
			return $langStr;
		} else {
			$langStr = $langs[$this->gEnv->getDefaultLanguage()];
			if (isset($langStr)){
				return $langStr;
			} else {
				return '';
			}
		}
	}
	/**
	 * 多言語対応文字列をテキストの連想配列に変換
	 *
	 * @param string $str		多言語対応文字列
	 * @return array			言語ごとの文字列の連想配列
	 */
	function unserializeLangArray($str)
	{
		$langs = array();
		if (empty($str)) return $langs;
		
		$itemArray = explode(M3_TAG_START . M3_TAG_MACRO_SEPARATOR . M3_TAG_END, $str);		// セパレータ分割
		for ($i = 0; $i < count($itemArray); $i++){
			$line = $itemArray[$i];
			if (empty($line)) continue;
			$pos = strpos($line, M3_LANG_SEPARATOR);		// 言語ID取得
			if ($pos === false){		// 言語IDがないときはデフォルトの言語IDを使用
				$langId = $this->gEnv->getDefaultLanguage();
				$langStr = $line;
			} else {
				list($langId, $langStr) = explode(M3_LANG_SEPARATOR, $line, 2);
				if (empty($langId)) continue;
			}
			$langs[$langId] = $langStr;
		}
		return $langs;
	}
	/**
	 * 表示用文字列に変換
	 *
	 * @param string $src 		変換元文字列
	 * @param bool $keepTags	HTMLタグを変換するかどうか
	 * @return string			変換後文字列
	 */
	function convertToDispString($src, $keepTags = false)
	{
		// 変換文字「&<>"」
		return convertToHtmlEntity($src, $keepTags);
	}
	/**
	 * URLをエンティティ文字に変換
	 */
	function convertUrlToHtmlEntity($src)
	{
		// 変換文字「&<>"'」
		return convertUrlToHtmlEntity($src);
	}
	/**
	 * URLがルートを指しているかどうか取得
	 *
	 * @param string $url	チェック対象のURL。空の場合は現在のURL
	 * @return bool			true=ルート、false=ルート以外
	 */
	function isRootUrl($url = '')
	{
		if (empty($url)) $url = $this->gEnv->getCurrentRequestUri();
		
		// マクロURLに変換
		$url = $this->gEnv->getMacroPath($url);
		
		list($tmp, $query) = explode('?', $url);
		if (!empty($query)) list($query, $tmp) = explode('#', $query);
		if (empty($query)){				// クエリ文字列がないことが条件。「#」はあっても良い。
			// パスを解析
			$relativePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '', $url);		// ルートURLからの相対パスを取得
			if (empty($relativePath)){			// Magic3のルートURLの場合
				return true;
			} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '/' . M3_FILENAME_INDEX)){		// ルートURL配下のとき
				return true;
			}
		}
		return false;
	}
	/**
	 * 年月日(yyyy/mm/dd)から前日を求める
	 *
	 * @param string $strYMD		年月日
	 * @return string				前日
	 */
	function getForeDay($srcstr)
	{
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\-]/', $srcstr);
		return date("Y/m/d", mktime(0, 0, 0, $mm, $dd - 1, $yyyy));
	}
	/**
	 * 年月日(yyyy/mm/dd)から翌日を求める
	 *
	 * @param string $strYMD		年月日(時刻付きも可)
	 * @return string				翌日
	 */
	function getNextDay($srcstr)
	{
		list($yyyy, $mm, $dd) = preg_split('/[\/\.\- ]/', $srcstr);
		return date("Y/m/d", mktime(0, 0, 0, $mm, $dd + 1, $yyyy));
	}
	/**
	 * 年月日(yyyy/mm/dd)から前月を求める
	 *
	 * @param string $strYMD		年月日
	 * @return string				前月
	 */
	function getForeMonth($srcstr)
	{
		list($yyyy, $mm) = preg_split('/[\/\.\-]/', $srcstr);
		return date("Y/m", mktime(0, 0, 0, $mm -1, 1, $yyyy));
	}
	/**
	 * 年月日(yyyy/mm/dd)から翌月を求める
	 *
	 * @param string $strYMD		年月日
	 * @return string				翌月
	 */
	function getNextMonth($srcstr)
	{
		list($yyyy, $mm) = preg_split('/[\/\.\-]/', $srcstr);
		return date("Y/m", mktime(0, 0, 0, $mm +1, 1, $yyyy));
	}
}
?>
