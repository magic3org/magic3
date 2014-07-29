<?php
/**
 * デザインマネージャー
 *
 * 共通的な画面デザインを管理する
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class DesignManager extends Core
{
	private $db;						// DBオブジェクト
	private $defaultMenuParam;			// デフォルトメニュー用パラメータ
	private $iconExts = array('png', 'gif');
	const DEFAULT_MENU_PARAM_KEY = 'default_menu_param';		// designテーブルのフィールド名
//	const DEFAULT_MENU_PARAM_INIT_VALUE = 'class="moduletable" width="100%" border="0" cellpadding="0" cellspacing="1"';	// デフォルトメニューのtagのパラメータデフォルト値
	const DEFAULT_MENU_PARAM_INIT_VALUE = 'class="moduletable"';	// デフォルトメニューのtagのパラメータデフォルト値
//	const DEFAULT_MENU_PARAM_INIT_VALUE = 'class="module_menu"';	// デフォルトメニューのtagのパラメータデフォルト値
	const J10_DEFAULT_CONTENT_HEAD_CLASS = 'class="contentheading"';		// Joomla!1.0テンプレート用のコンテンツヘッダCSSクラス
	const CF_CONFIG_WINDOW_STYLE		= 'config_window_style';	// 設定画面のウィンドウスタイル取得用キー
	const DEFAULT_CONFIG_WINDOW_STYLE	= 'toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1000,height=900';// 設定画面のウィンドウスタイルデフォルト値
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
	}
	/**
	 * デフォルトウィジェットテーブルのパラメータを取得
	 *
	 * @param int $menuType			タイプ(0=テーブルタグ形式、1=リンクタグ形式)
	 * @return string 				デフォルトメニューで使用するtableタグのタグ属性を取得
	 */
	function getDefaultWidgetTableParam($menuType = 0)
	{
		if (empty($this->defaultMenuParam)){
			$value = $this->db->getDesignConfig(DEFAULT_MENU_PARAM_KEY);
			if (empty($value)){
				$this->defaultMenuParam = self::DEFAULT_MENU_PARAM_INIT_VALUE;
			} else {
				$this->defaultMenuParam = $value;
			}
		}
		return $this->defaultMenuParam;
	}
	/**
	 * コンテンツヘッダ部のCSSクラス文字列を取得
	 *
	 * @return string 				CSSクラス文字列
	 */
	function getDefaultContentHeadClassString()
	{
		// テンプレートタイプを取得
		$classStr = '';
		$templateType = $this->gEnv->getCurrentTemplateType();
		switch ($templateType){
			case 0:
				$classStr = self::J10_DEFAULT_CONTENT_HEAD_CLASS;		// Joomla!1.0テンプレート用のコンテンツヘッダCSSクラス
				break;
			case 1:
				break;
		}
		return $classStr;
	}
	/**
	 * ウィジェットアイコンを取得
	 *
	 * ウィジェット用のアイコンのURLを取得する。
	 * ウィジェットのアイコンが存在しない場合はデフォルトのアイコンのURLを返す。
	 *
	 * @param string $widgetId		ウィジェットID
	 * @param int $size				アイコンサイズ
	 * @return string 				ウィジェットのアイコンへのURL
	 */
	function getWidgetIconUrl($widgetId, $size)
	{
		// サイズ指定で取得
		for ($i = 0; $i < count($this->iconExts); $i++){
			$iconName = 'icon' . $size . '.' . $this->iconExts[$i];
			// ファイルが存在するかチェック
			$iconPath = $this->gEnv->getWidgetsPath() . '/' . $widgetId . '/images/' . $iconName;
			if (file_exists($iconPath)) return $this->gEnv->getWidgetsUrl() . '/' . $widgetId . '/images/' . $iconName;
		}
		// 指定サイズがない場合はウィジェットのデフォルトアイコンを取得
		for ($i = 0; $i < count($this->iconExts); $i++){
			$iconName = 'icon.' . $this->iconExts[$i];
			// ファイルが存在するかチェック
			$iconPath = $this->gEnv->getWidgetsPath() . '/' . $widgetId . '/images/' . $iconName;
			if (file_exists($iconPath)) return $this->gEnv->getWidgetsUrl() . '/' . $widgetId . '/images/' . $iconName;
		}
		// 見つからない場合はシステムからデフォルトアイコンを取得
		return $this->gEnv->getRootUrl() . '/images/wicon'. $size . '.png';
	}
	/**
	 * ウィジェット出力の前後に出力するHTMLを取得
	 *
	 * @param bool $isPrefix		true=ウィジェット出力の前出力、false=ウィジェット出力の後出力
	 * @return string 				取得HTML
	 */
	function getAdditionalWidgetOutput($isPrefix)
	{
		if ($isPrefix){		// 前出力
			return '';
		} else {// 後出力
			//return '<br>';
			return '';
		}
	}
	/**
	 * 設定画面のウィンドウスタイルを取得
	 *
	 * @return string 			ウィンドウスタイル文字列
	 */
	function getConfigWindowStyle()
	{
		$value = $this->gSystem->getSystemConfig(self::CF_CONFIG_WINDOW_STYLE);
		if (empty($value)) $value = self::DEFAULT_CONFIG_WINDOW_STYLE;
		return $value;
	}
	/**
	 * Bootstrapメッセージ用CSSクラス取得
	 *
	 * @param string $type		メッセージタイプ(danger,error,warning,info,success)
	 * @param string $preTag	前タグ
	 * @param string $preTag	後タグ
	 * @return array 			クラス名
	 */
	function getBootstrapMessageClass($type, &$preTag = null, &$postTag = null)
	{
		$extClass = array();
		
		switch ($type){
			case 'danger':
				$extClass[] = 'alert';
				$extClass[] = 'alert-danger';
				break;
			case 'error':
				$extClass[] = 'alert';
				$extClass[] = 'alert-error';
				break;
			case 'warning':
				$extClass[] = 'alert';
				$extClass[] = 'alert-warning';
				break;
			case 'info':
				$extClass[] = 'alert';
				$extClass[] = 'alert-info';
				break;
			case 'success':
				$extClass[] = 'alert';
				$extClass[] = 'alert-success';
				break;
		}
		// メッセージ幅
		$extClass[] = 'col-lg-6';
		$extClass[] = 'col-lg-offset-3';
	
		// 前後タグ
		if (isset($preTag)) $preTag = '<div class="row">';
		if (isset($postTag)) $postTag = '</div>';
		return $extClass;
	}
	/**
	 * ページリンク作成(Artisteer4.1対応)
	 *
	 * @param int $pageNo			ページ番号(1～)。
	 * @param int $pageCount		総項目数
	 * @param int $linkCount		最大リンク数
	 * @param string $baseUrl		リンク用のベースURL
	 * @param string $urlParams		オプションのURLパラメータ
	 * @param int $style			0=Artisteerスタイル、1=括弧スタイル、2=Bootstrap型、-1=管理画面
	 * @param string $clickEvent	リンククリックイベント用スクリプト
	 * @return string				リンクHTML
	 */
	function createPageLink($pageNo, $pageCount, $linkCount, $baseUrl, $urlParams = '', $style = 0, $clickEvent = '')
	{
		// パラメータ修正
		if (!empty($urlParams) && !strStartsWith($urlParams, '&')) $urlParams = '&' . $urlParams;
		
		// ページング用リンク作成
		$pageLink = '';
		if ($pageCount > 1){	// ページが2ページ以上のときリンクを作成
			// ページ数1から$linkCountまでのリンクを作成
			$maxPageCount = $pageCount < $linkCount ? $pageCount : $linkCount;
			for ($i = 1; $i <= $maxPageCount; $i++){
				if ($i == $pageNo){
					switch ($style){
						case 2:			// Bootstrap型のとき
							$link = '<li class="active"><a href="#">' . $i . '<span class="sr-only">(current)</span></a></li>';
							break;
						default:
							$link = '&nbsp;<span class="active">' . $i . '</span>';
							break;
					}
				} else {
					$linkUrl = '';
					$clickScript = '';
					if (empty($clickEvent)){
						$linkUrl = $baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . $i . $urlParams;
					} else {
						$clickScript = str_replace('$1', $i, $clickEvent);
					}
					switch ($style){
						case 2:			// Bootstrap型のとき
							$link = '<li>' . $this->_createLink($i, convertUrlToHtmlEntity($linkUrl), $clickScript) . '</li>';
							break;
						default:
							$link = '&nbsp;' . $this->_createLink($i, convertUrlToHtmlEntity($linkUrl), $clickScript);
							break;
					}
				}
				$pageLink .= $link;
			}
			// 残りは「...」表示
			if ($pageCount > $linkCount) $pageLink .= '&nbsp;...';
		}
		if ($pageNo > 1){		// 前ページがあるとき
			$linkUrl = '';
			$clickScript = '';
			if (empty($clickEvent)){
				$linkUrl = $baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . ($pageNo -1) . $urlParams;
			} else {
				$clickScript = str_replace('$1', $pageNo -1, $clickEvent);
			}
			switch ($style){
				case 2:			// Bootstrap型のとき
					$link = '<li>' . $this->_createLink('&laquo;', convertUrlToHtmlEntity($linkUrl), $clickScript) . '</li>';
					break;
				default:
					$link = $this->_createLink('&laquo; 前へ', convertUrlToHtmlEntity($linkUrl), $clickScript);
					break;
			}
			$pageLink = $link . $pageLink;
		}
		if ($pageNo < $pageCount){		// 次ページがあるとき
			$linkUrl = '';
			$clickScript = '';
			if (empty($clickEvent)){
				$linkUrl = $baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . ($pageNo +1) . $urlParams;
			} else {
				$clickScript = str_replace('$1', $pageNo +1, $clickEvent);
			}
			switch ($style){
				case 2:			// Bootstrap型のとき
					$link = '<li>' . $this->_createLink('&raquo;', convertUrlToHtmlEntity($linkUrl), $clickScript) . '</li>';
					break;
				default:
					$link = '&nbsp;' . $this->_createLink('次へ &raquo;', convertUrlToHtmlEntity($linkUrl), $clickScript);
					break;
			}
			$pageLink .= $link;
		}
		if (!empty($pageLink)){
			switch ($style){
				case 2:			// Bootstrap型のとき
					$pageLink = '<ul class="pagination">' . $pageLink . '</ul>';
					break;
				default:
					$pageLink = '<div class="art-pager">' . $pageLink . '</div>';
					break;
			}
		}
		return $pageLink;
	}
	/**
	 * Aタグリンク作成
	 *
	 * @param string $name				リンクされる文字列
	 * @param string $url				URL
	 * @param string $clickEvent		クリックイベント用JavaScript。イベントが設定されている場合はイベントを優先。
	 * @return string 					タグ文字列
	 */
	function _createLink($name, $url, $clickEvent = '')
	{
		$destTag = '';
		if (empty($clickEvent)){
			$destTag = '<a href="' . $url . '" >' . $name . '</a>';
		} else {
			$destTag = '<a href="javascript:void(0)" onclick="' . $clickEvent . '" >' . $name . '</a>';
		}
		return $destTag;
	}
	/**
	 * 管理画面用ナビゲーションタブを作成
	 *
	 * @param array $tabDef				タブの定義
	 * @param string $activeTask		選択状態のタスク
	 * @param bool $withBreadcrumb		パンくずリストを付加するかどうか
	 * @param string $breadcrumbTitle	パンくずリストのトップタイトル
	 * @return string 					タブのHTML
	 */
	function createConfigNavTab($tabDef, $activeTask = '', $withBreadcrumb = false, $breadcrumbTitle = '')
	{
		$tabDefCount = count($tabDef);
		if ($tabDefCount <= 0) return '';

		$tabHtml = '<ul id="m3navtab" class="nav nav-tabs">';
		for ($i = 0; $i < $tabDefCount; $i++){
			$tabItem = $tabDef[$i];
			$name = $tabItem->name;
			$url = $tabItem->url;
			$active = '';
			if ($tabItem->active) $active = ' class="active"';
			$tabHtml .= '<li' . $active . '>';
			if (empty($url)){
				$tabHtml .= convertToHtmlEntity($name);
			} else {
				$tabHtml .= '<a href="' . convertUrlToHtmlEntity($url) . '" data-toggle="tab">' . convertToHtmlEntity($name) . '</a>';
			}
			$tabHtml .= '</li>';
		}
		$tabHtml .= '</ul>';
		return $tabHtml;
/*
<ul class="nav nav-tabs" id="myTab">
  <li class="active"><a href="#home" data-toggle="tab">Home</a></li>
  <li><a href="#profile" data-toggle="tab">Profile</a></li>
  <li><a href="#messages" data-toggle="tab" class="clickable">Messages(click!)</a></li>

<li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
      Dropdown <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
  <li><a href="#profile" >sample1</a></li>
  <li><a href="#messages" >sample2</a></li>
    </ul>
</li>
  <li><a href="#settings" data-toggle="tab">Settings</a></li>
</ul>
*/
	}
}
?>
