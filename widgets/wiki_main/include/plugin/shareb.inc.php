<?php
/**
 * ソーシャルメディアシェアボタンプラグイン
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
function plugin_shareb_convert()
{
	global $gEnvManager;
	// パラメータエラーチェック
//	if (func_num_args() < 1) return false;
	
	$args = func_get_args();

	// 画面のURL
	$url = get_script_uri() . WikiParam::convQuery('?' . rawurlencode(WikiParam::getPage()));
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
		
	$body = '';
	
	// Facebookボタン
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
		$body .= '<ul class="social-button list-unstyled clearfix">';
	} else {
		$body .= '<ul class="social-button clearfix">';
	}
	$body .= '<li class="button-facebook"><div class="fb-share-button" data-href="' . $url . '" data-layout="box_count"></div></li>';
	
	// Twitterボタン
	$body .= '<li class="button-twitter"><a href="https://twitter.com/share" class="twitter-share-button" data-lang="ja" data-url="' . $url . '" data-count="vertical"></a></li>';

	// はてなブックマーク
	$body .= '<li class="button-hatena"><a href="http://b.hatena.ne.jp/entry/' . $url . '" class="hatena-bookmark-button" data-hatena-bookmark-layout="vertical-balloon" data-hatena-bookmark-lang="ja" title="このエントリーをはてなブックマークに追加"><img src="https://b.st-hatena.com/images/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border: none;" /></a></li>';
	
	// Google+
	$body .= '<li class="button-google"><div data-href="' . $url . '" class="g-plusone" data-size="tall"></div></li>';
	$body .= '</ul>';
		
	// Javascript読み込み
	plugin_shareb_addScript($type, $replaceData);
	
	return $body;
}
/**
 * Javascriptを追加
 *
 * @param string $type			スクリプトタイプ(「selectimage」=画像選択)
 * @param array  $replaceData	テンプレート置換データ。キーと値の連想配列。
 * @return						なし
 */
function plugin_shareb_addScript($type, $replaceData)
{
	global $gEnvManager;
	global $gPageManager;
	
	// 実行中のウィジェットを取得
	$widgetObj = $gEnvManager->getCurrentWidgetObj();
	
	$scriptBody = $widgetObj->getParsedTemplateData('plugin_shareb.tmpl.js', 'plugin_shareb_makeScript'/*コールバック関数*/, $replaceData);

	// Javascriptを追加
	$gPageManager->addHeadScript($scriptBody);
	
	// 非同期でJavascriptのURLを追加
//	$gPageManager->addHeadScriptFile('https://b.st-hatena.com/js/bookmark_button.js');
}
/**
 * Javascriptデータ作成処理コールバック
 *
 * @param object $tmpl			テンプレートオブジェクト
 * @param array  $replaceData	テンプレート置換データ。キーと値の連想配列。
 * @param						なし
 */
function plugin_shareb_makeScript($tmpl, $replaceData)
{
/*	foreach ($replaceData as $key => $value) {
		$tmpl->addVar("_tmpl", $key, $value);
	}*/
}
?>
