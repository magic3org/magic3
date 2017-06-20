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
			} else if ($gEnvManager->getIsMobileSite()){		// 携帯サイトの場合
				$domainUrl = $gEnvManager->getDefaultMobileUrl(false, false/*ファイル名なし*/);
				$relativePath = str_replace($domainUrl . '/' . M3_DIR_NAME_MOBILE, '', $destPath);
				if (strStartsWith($relativePath, '/')){
					$destPath = $domainUrl . $relativePath;
				} else {
					// メインのドメインの場合はアクセスポイント用ドメインに変換
					$relativePath = str_replace(M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_MOBILE, '', $destPath);
					if (strStartsWith($relativePath, '/')) $destPath = $domainUrl . $relativePath;
				}
			}
		}
		return $destPath;
	}
}
?>
