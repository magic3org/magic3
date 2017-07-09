<?php
/**
 * WordPressウィジェットHTML作成クラス
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
	
class WPRender
{

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * Joomlaモジュール用コンテンツ取得
	 * 
	 * @param string $style			表示スタイル
	 * @param string $content		ウィジェット出力
	 * @param string $title			タイトル(空のときはタイトル非表示)
	 * @param array $attribs		その他タグ属性
	 * @param array $paramsOther	その他パラメータ
	 * @param array $pageDefParam	画面定義パラメータ
	 * @param int   $templateVer	テンプレートバージョン(0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
	 * @return string				モジュール出力
	 */
	public function getModuleContents($style, $content, $title = '', $attribs = array(), $paramsOther = array(), $pageDefParam = array(), $templateVer = 0)
	{
		global $gEnvManager;



		// 前後コンテンツ追加
		$content = $pageDefParam['pd_top_content'] . $content . $pageDefParam['pd_bottom_content'];
		// 「もっと読む」ボタンを追加
		if ($pageDefParam['pd_show_readmore']) $content = $this->addReadMore($content, $pageDefParam['pd_readmore_title'], $pageDefParam['pd_readmore_url']);
		
		// ウィジェットの内枠(コンテンツ外枠)を設定
//		$content = '<div class="' . self::WIDGET_INNER_CLASS . '">' . $content . '</div>';
		
		// 指定された表示スタイルでウィジェットを出力
		$chromeMethod = 'modChrome_' . $style;
		if (function_exists($chromeMethod))
		{
			$module = new stdClass;
			//$module->style = $attribs['style'];
			$module->content = $content;
			if (!empty($title)){
				$module->showtitle = 1;
//				$module->title = htmlentities($title, ENT_COMPAT, M3_HTML_CHARSET);
				$module->title = convertToHtmlEntity($title);
			}
			
			// Joomla!2.5テンプレート用追加設定(2012/4/4 追加)
			$GLOBALS['artx_settings'] = array('block' => array('has_header' => true));
			
			ob_clean();
			$chromeMethod($module, $params, $attribs);
			$contents = ob_get_contents();
			ob_clean();
		}
		return $contents;
	}

}
?>
