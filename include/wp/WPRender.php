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
	const DEFAULT_POSITION = 'sidebar-1';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * WordPressモジュール用コンテンツ取得
	 * 
	 * @param string $style			表示スタイル
	 * @param string $content		ウィジェット出力
	 * @param string $title			タイトル(空のときはタイトル非表示)
	 * @param array $attribs		その他タグ属性
	 * @param array $paramsOther	その他パラメータ
	 * @param array $pageDefParam	画面定義パラメータ
	 * @param int   $templateVer	テンプレートバージョン(0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
	 * @param string $widgetTag		ウィジェット識別用タグ
	 * @return string				モジュール出力
	 */
	public function getModuleContents($style, $content, $title = '', $attribs = array(), $paramsOther = array(), $pageDefParam = array(), $templateVer = 0, $widgetTag = '')
	{
		global $wp_registered_sidebars;

		// 現在のポジション取得
		$position = $pageDefParam['pd_position_id'];
		
		// 配置ブロック用のパラメータを取得
		$blockParam = $wp_registered_sidebars[$position];
		if (empty($blockParam)) $blockParam = $wp_registered_sidebars[self::DEFAULT_POSITION];			// 存在しない場合はデフォルトを取得

		// WordPress側から初期パラメータを取得できない場合は終了
		if (empty($blockParam)) return $content;
		
		// タイトルを追加
		if ($style == PageManager::WIDGET_STYLE_WORDPRESS){			// タイトルありの場合
			if (!empty($title)) $content = $blockParam['before_title'] . convertToHtmlEntity($title) . $blockParam['after_title'] . $content;
		}
		
		// 前後コンテンツ追加
//		$content = $pageDefParam['pd_top_content'] . $content . $pageDefParam['pd_bottom_content'];
		// 「もっと読む」ボタンを追加
//		if ($pageDefParam['pd_show_readmore']) $content = $this->addReadMore($content, $pageDefParam['pd_readmore_title'], $pageDefParam['pd_readmore_url']);
		
		// コンテンツ全体をウィジェット用タグで囲む
		$widgetClass = 'm3widget_' . $pageDefParam['pd_widget_id'];		// ウィジェットIDからクラス名作成
		if (!empty($paramsOther['moduleclass'])) $widgetClass .= ' ' . $paramsOther['moduleclass'];
		$blockParam['before_widget'] = sprintf($blockParam['before_widget'], $widgetTag, $widgetClass);		// ウィジェット識別用IDとウィジェットクラス名
		$content = $blockParam['before_widget'] . $content . $blockParam['after_widget'];
		 
		return $content;
	}
	/**
	 * WordPressコンポーネント用コンテンツ取得
	 * 
	 * @param string $style			表示スタイル
	 * @param string $content		ウィジェット出力
	 * @param string $title			タイトル(空のときはタイトル非表示)
	 * @param array $attribs		その他タグ属性
	 * @param array $paramsOther	その他パラメータ
	 * @param array $pageDefParam	画面定義パラメータ
	 * @param int   $templateVer	テンプレートバージョン(0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
	 * @param string $widgetTag		ウィジェット識別用タグ
	 * @return string				モジュール出力
	 */
	public function getComponentContents($style, $content, $title = '', $attribs = array(), $paramsOther = array(), $pageDefParam = array(), $templateVer = 0, $widgetTag = '')
	{
		global $gEnvManager;
		global $gContentApi;
		
		// コンポーネント生成用スクリプトパス取得
		$componentPath = $gEnvManager->getWpComponentPath();
		if (file_exists($componentPath)){
			// ウィジェットで生成されたデータでWordPressコンポーネント用のデータを更新
			$gContentApi->updateComponentContent($content);
		
			ob_clean();
			require($componentPath);		// 毎回実行する
			$content = ob_get_contents();
			ob_clean();
		} else {
			// コンポーネント生成用スクリプトがない場合はWordPressモジュール出力を使用
			$content = $this->getModuleContents($style, $content, $title, $attribs, $paramsOther, $pageDefParam, $templateVer, $widgetTag);
		}
		return $content;
	}
}
?>
