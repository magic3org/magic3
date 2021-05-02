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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class DesignManager extends _Core
{
	private $_getUrlCallback;		// URL変換(getUrl())用コールバック関数
	private $db;						// DBオブジェクト
	private $defaultMenuParam;			// デフォルトメニュー用パラメータ
	private $pageLinkInfo;				// ページリンク情報
	private $iconExts = array('png', 'gif');
	const DEFAULT_MENU_PARAM_KEY = 'default_menu_param';		// designテーブルのフィールド名
//	const DEFAULT_MENU_PARAM_INIT_VALUE = 'class="moduletable" width="100%" border="0" cellpadding="0" cellspacing="1"';	// デフォルトメニューのtagのパラメータデフォルト値
	const DEFAULT_MENU_PARAM_INIT_VALUE = 'class="moduletable"';	// デフォルトメニューのtagのパラメータデフォルト値
//	const DEFAULT_MENU_PARAM_INIT_VALUE = 'class="module_menu"';	// デフォルトメニューのtagのパラメータデフォルト値
	const J10_DEFAULT_CONTENT_HEAD_CLASS = 'class="contentheading"';		// Joomla!1.0テンプレート用のコンテンツヘッダCSSクラス
	const CF_CONFIG_WINDOW_STYLE		= 'config_window_style';	// 設定画面のウィンドウスタイル取得用キー
	const DEFAULT_CONFIG_WINDOW_STYLE	= 'toolbar=no,menubar=no,location=no,status=no,scrollbars=yes,resizable=yes,width=1000,height=900';// 設定画面のウィンドウスタイルデフォルト値
	const UPLOAD_ICON_FILE = '/images/system/upload_box32.png';		// アップロードボックスアイコン
	const CALENDAR_ICON_FILE = '/images/system/calendar.png';		// カレンダーアイコン
//	const CLOSE_ICON_FILE = '/images/system/close32.png';		// ウィンドウ閉じるアイコン
//	const NAV_ITEM_ICON_SIZE = 32;								// ナビゲーションメニューアイコンサイズ
	const SUB_MENUBAR_HEIGHT = 50;			// サブメニューバーの高さ
	const DEFAULT_META_NO_INDEX = '<meta name="robots" content="noindex,nofollow" />';		// METAタグ(検索エンジン登録拒否)
	
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
	 * URL変換(getUrl())用コールバック関数を設定
	 *
	 * @param  function  $func		コールバック関数
	 * @return 						なし
	 */
	function _setGetUrlCallback($func)
	{
		$this->_getUrlCallback = $func;
	}
	/**
	 * ウィジェットコンテナクラスのgetUrl()を使用してURLを作成
	 *
	 * @param string $path				URL作成用のパス
	 * @param bool $isLink				作成するURLがリンクかどうか。リンクの場合はリンク先のページのSSLの状態に合わせる(未使用)
	 * @param string,array $param		URLに付加するパラメータ
	 * @return string					変換後URL
	 */
	function getUrl($path, $isLink = false, $param = '')
	{
		$destUrl = call_user_func($this->_getUrlCallback, $path, $isLink, $param);
		return $destUrl;
	}
	/**
	 * システム標準のMetaタグを取得
	 *
	 * @param int $type				タグのタイプ(0=検索エンジンに登録しない)
	 * @return string 				タグ
	 */
	function getMetaTag($type = 0)
	{
		$tag = '';
		
		switch ($type){
		case 0:
			$tag = self::DEFAULT_META_NO_INDEX;		// 検索エンジン登録拒否
			break;
		}
		return $tag;
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
		return $this->gEnv->getRootUrl() . '/images/system/wicon'. $size . '.png';
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
	 * @param int $templateType	テンプレートタイプ(99=レイアウト用テンプレート,0=デフォルトテンプレート(Joomla!v1.0),1=Joomla!v1.5,2=Joomla!v2.5,10=Bootstrap v3.0,11=Bootstrap v4.0,20=jQuery Mobile,100=WordPress)
	 * @return array 			クラス名
	 */
	function getBootstrapMessageClass($type, &$preTag = null, &$postTag = null, $templateType = 0)
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
		switch ($templateType){
		case 10:		// Bootstrap v3.0
			$extClass[] = 'col-lg-6';
			$extClass[] = 'col-lg-offset-3';
			break;
		case 11:		// Bootstrap v4.0
			$extClass[] = 'col-lg-6';
			$extClass[] = 'offset-lg-3';
			break;
		}
	
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
		// ページリンク情報初期化
		$this->pageLinkInfo = array();
		
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
						case -1:		// 管理画面
							//$link = '<li class="active"><a href="#">' . $i . '<span class="sr-only">(current)</span></a></li>';
							//$link = '<li class="page-item active"><a href="#" class="page-link">' . $i . '<span class="sr-only">(current)</span></a></li>';
							$link = '<li class="page-item active" aria-current="page"><span class="page-link">' . $i . '</span></li>';
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
						case -1:		// 管理画面
							//$link = '<li>' . $this->_createLink($i, $linkUrl, $clickScript) . '</li>';
							$link = '<li class="page-item">' . $this->_createLink($i, $linkUrl, $clickScript, 'class="page-link"') . '</li>';
							break;
						default:
							$link = '&nbsp;' . $this->_createLink($i, $linkUrl, $clickScript);
							break;
					}
				}
				$pageLink .= $link;
			}
			// 残りは「...」表示
			if ($pageCount > $linkCount){
				switch ($style){
					case 2:			// Bootstrap型のとき
					case -1:		// 管理画面
						//$pageLink .= '<li class="disabled"><a href="#">…</a></li>';
						$pageLink .= '<li class="page-item disabled"><a href="#" class="page-link">…</a></li>';
						break;
					default:
						$pageLink .= '&nbsp;...';
						break;
				}
			}
			
			// ### Joomla!新型テンプレート用のページ遷移ナビゲーションデータを作成 ###
			$this->pageLinkInfo['format']	= $baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=$1' . $urlParams;
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
				case -1:		// 管理画面
					//$link = '<li>' . $this->_createLink('&laquo;', $linkUrl, $clickScript) . '</li>';
					$link = '<li class="page-item">' . $this->_createLink('&laquo;', $linkUrl, $clickScript, 'class="page-link"') . '</li>';
					break;
				default:
					$link = $this->_createLink('&laquo; 前へ', $linkUrl, $clickScript);
					break;
			}
			$pageLink = $link . $pageLink;
			
			// ### Joomla!新型テンプレート用のページ遷移ナビゲーションデータを作成 ###
			$this->pageLinkInfo['start']	= array( 'link' => $baseUrl . $urlParams );
			$this->pageLinkInfo['previous']	= array( 'link' => $baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . ($pageNo -1) . $urlParams );
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
				case -1:		// 管理画面
					//$link = '<li>' . $this->_createLink('&raquo;', $linkUrl, $clickScript) . '</li>';
					$link = '<li class="page-item">' . $this->_createLink('&raquo;', $linkUrl, $clickScript, 'class="page-link"') . '</li>';
					break;
				default:
					$link = '&nbsp;' . $this->_createLink('次へ &raquo;', $linkUrl, $clickScript);
					break;
			}
			$pageLink .= $link;
			
			// ### Joomla!新型テンプレート用のページ遷移ナビゲーションデータを作成 ###
			$this->pageLinkInfo['next']		= array( 'link' => $baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . ($pageNo +1) . $urlParams );
			$this->pageLinkInfo['end']		= array( 'link' => $baseUrl . '&' . M3_REQUEST_PARAM_PAGE_NO . '=' . $pageCount . $urlParams );
		}
		if (!empty($pageLink)){
			switch ($style){
				case 2:			// Bootstrap型のとき
				case -1:		// 管理画面
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
	 * ページリンク情報取得
	 *
	 * @return array			ページリンク情報
	 */
	function getPageLinkInfo()
	{
		return $this->pageLinkInfo;
	}
	/**
	 * 管理画面遷移用タグ作成
	 *
	 * @param string $src		リンク対象の文字列またはタグ
	 * @param string $url		リンク先URL
	 * @return string			リンクHTML
	 */
	function createAdminPageLink($src, $url)
	{
		// ウィジェット設定画面の場合は別画面で表示
		$attr = '';		// 別ウィンドウで画面を開くかどうか
		$pos = strpos($url, M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_CONFIG_WIDGET);
		if ($pos !== false) $attr = 'target="_blank"';
		
		// 絶対パスに直す
		if (strncasecmp($url, 'http://', strlen('http://')) != 0 && strncasecmp($url, 'https://', strlen('https://')) != 0) $url = $this->gEnv->getDefaultAdminUrl() . '?' . $url;
		
		// リンクタグを作成
		$linkTag = $this->_createLink($src, $url, ''/*クリックイベントなし*/, $attr);
		return $linkTag;
	}
	/**
	 * Aタグリンク作成
	 *
	 * @param string $name				リンクされる文字列またはタグ
	 * @param string $url				URL
	 * @param string $clickEvent		クリックイベント用JavaScript。イベントが設定されている場合はイベントを優先。
	 * @param string $attr				追加属性
	 * @return string 					タグ文字列
	 */
	function _createLink($name, $url, $clickEvent = '', $attr = '')
	{
		$destTag = '';
		if (empty($clickEvent)){
			$destTag = '<a href="' . convertUrlToHtmlEntity($url) . '" ' . $attr . ' >' . $name . '</a>';
		} else {
			$destTag = '<a href="javascript:void(0)" onclick="' . $clickEvent . '" ' . $attr . ' >' . $name . '</a>';
		}
		return $destTag;
	}
	/**
	 * 文字列からリンク対象とリンク先URLを取得
	 *
	 * @param string $str		解析文字列
	 * @return array			リンク対象語文字列とリンク先URL
	 */
	function _parseLinkString($str)
	{
		$destStr = '';
		$url = '';
		
		// リンク元文字列を取得
		$pos = strpos($str, '|');
		if ($pos === false){
			$destStr = $str;
		} else {
			list($destStr, $url) = explode('|', $str, 2);
		}
			
		return array($destStr, $url);
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
	}
	/**
	 * リンクフォーマットテキスト(「|」でURLを連結)からリンクタグを作成
	 *
	 * @param string $src			フォーマットテキスト
	 * @param bool $htmlEscape		HTMLエスケープするかどうか。falseの場合はリンク元文字列のみ返す。
	 * @return string 				リンクタグ。リンクが設定されていない場合はHTMLエスケープ済みの文字列を返す。
	 */
	function createLinkFromLinkFomatText($src, $htmlEscape = true)
	{
		list($linkStr, $url) = $this->_parseLinkString($src);
		$linkStr = trim($linkStr);
		$url = trim($url);
		if ($htmlEscape){
			if (empty($url)){
				$destTag = convertToHtmlEntity($linkStr);
			} else {
				$destTag = '<a href="' . convertUrlToHtmlEntity($url) . '" >' . convertToHtmlEntity($linkStr) . '</a>';
			}
		} else {
			$destTag = $linkStr;
		}
		return $destTag;
	}
	/**
	 * ドラッグ&ドロップファイルアップロード用タグを作成
	 *
	 * @return string 				アップロード用HTML
	 */
	function createDragDropFileUploadHtml()
	{
		$iconUrl = call_user_func($this->_getUrlCallback, $this->gEnv->getRootUrl() . self::UPLOAD_ICON_FILE);
		$html = '<h4 style="display:table;margin-left:auto;margin-right:auto;"><img src="' . $iconUrl . '" style="border:none;margin:0;padding:0;" /><span style="display:table-cell;vertical-align:middle;">ファイルアップロード</span></h4><p style="text-align:center;">ここにファイルをドロップ、またはクリック</p>';
		return $html;
	}
	/**
	 * アップロード用ダイアログのタグを作成
	 *
	 * @param string $id					ダイアログタグID
	 * @param string $uploadButtonTagId		アップロードボタンのタグのID
	 * @param string $uploadButtonAttr		アップロードボタンのその他の追加属性
	 * @param string $title					ダイアログタイトル
	 * @param string $message				ダイアログメッセージ
	 * @param string $uploadButtonLabel		アップロードボタンのラベル
	 * @param string $cancelButtonLabel		キャンセルボタンのラベル
	 * @param string $formName				フォーム名
	 * @param int $maxFileSize				最大ファイルサイズ(0のときはデフォルト値)
	 * @return string 						アップロード用HTML
	 */
	function createFileUploadDialogHtml($id, $uploadButtonTagId = '', $uploadButtonAttr = '', $title = 'ファイルアップロード', $message = 'ファイルを選択してください。', $uploadButtonLabel = 'アップロード', $cancelButtonLabel = 'キャンセル', $formName = 'upload', $maxFileSize = 0)
	{
		global $gSystemManager;
		
		if (empty($maxFileSize)) $maxFileSize = $gSystemManager->getMaxFileSizeForUpload(true/*数値のバイト数*/);
		
		$uploadButtonIdAttr = '';
		if (!empty($uploadButtonTagId)) $uploadButtonIdAttr = ' id="' . $uploadButtonTagId . '"';
		$uploadButtonOtherAttr = '';
		if (!empty($uploadButtonAttr)) $uploadButtonOtherAttr .= ' ' . $uploadButtonAttr;
		
		$html  = '<div id="' . $id . '" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">';
		$html .= '<div class="modal-dialog">';
		$html .= '<div class="modal-content">';
		$html .= '<div class="modal-header">';
		$html .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
		$html .= '<h4 class="modal-title" id="uploadModalLabel">' . convertToHtmlEntity($title) . '</h4>';
		$html .= '</div>';
		$html .= '<div class="modal-body">';
		$html .= '<p>' . convertToHtmlEntity($message) . '</p>';
		$html .= '<form enctype="multipart/form-data" method="post" name="' . $formName . '">';
		$html .= '<input type="hidden" name="act" />';
		$html .= '<input type="hidden" name="MAX_FILE_SIZE" value="' . $maxFileSize . '" />';
		$html .= '<input type="hidden" name="item_type" />';
		$html .= '<div class="input-group">';
		$html .= '<span class="input-group-addon btn-file"><i class="glyphicon glyphicon-folder-open"></i><input type="file" name="upfile"></span>';
		$html .= '<input type="text" class="form-control">';
		$html .= '</div>';
		$html .= '</form>';
		$html .= '</div>';
		$html .= '<div class="modal-footer">';
		$html .= '<button type="button" class="btn btn-default" data-dismiss="modal">' . convertToHtmlEntity($cancelButtonLabel) . '</button>';
		$html .= '<button type="button"' . $uploadButtonIdAttr . ' class="btn btn-success"' . $uploadButtonOtherAttr . '>' . convertToHtmlEntity($uploadButtonLabel) . '</button>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
	/**
	 * 管理画面用パンくずリストを作成
	 *
	 * @param array $def		パンくずリストの定義
	 * @param array $help		ヘルプ(title,bodyの連想配列)
	 * @return string 			パンくずリストのHTML
	 */
	function createAdminBreadcrumb($def, $help = array())
	{
		$destHtml = '<div class="m3breadcrumbbar">';
		$destHtml .= '<ol class="breadcrumb">';
		for ($i = 0; $i < count($def); $i++){
			$name = $def[$i];
			$destHtml .= '<li>' . convertToHtmlEntity($name) . '</li>';
		}
		$destHtml .= '</ol>';
		
		// ヘルプが設定されている場合は追加
		if (!empty($help)){
			$title = $help['title'];
			if (!empty($title)){
				$helpText = $this->gInstance->getHelpManager()->createHelpText($title, $help['body']);
				$destHtml .= '<span ' . $helpText . '><i class="glyphicon glyphicon-question-sign text-warning"></i></span>';
			}
		}
		$destHtml .= '</div>';
		return $destHtml;
	}
	/**
	 * サブメニューバー作成(ウィジェット設定画面専用)
	 *
	 * @param object $navbarDef			メニューバー定義
	 * @return string 					サブメニューバーのHTML
	 */
	function createSubMenubar($navbarDef)
	{
		// タイトル作成
	//	$titleTag = $this->createSubMenubarTitleTag($navbarDef, 1/*ウィジェット設定画面用アイコン*/);
		$titleTag = $this->createSubMenubarTitleTag($navbarDef, $navbarDef->type/*画面用アイコン*/);
		
		// メニュー作成
		$menuTag = $this->createSubMenubarMenuTag($navbarDef);
		
		// メニューバー作成
		$destHtml = '<nav class="navbar-inverse navbar-fixed-top secondlevel">';
		$destHtml .= '<div class="navbar-header"><button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#subnavbar-collapse"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>' . $titleTag . '</div>';
		$destHtml .= '<div class="collapse navbar-collapse" id="subnavbar-collapse">' . $menuTag . '</div></nav>';
		return $destHtml;
	}
	/**
	 * サブメニューバーのタイトルタグ作成
	 *
	 * @param object $navbarDef			メニューバー定義
	 * @param int $iconType				アイコンタイプ(0=なし、1=ウィジェット設定画面、2=システム画面(共通設定画面等)、3=テンプレート設定画面)
	 * @return string 					サブメニューバーのHTML
	 */
	function createSubMenubarTitleTag($navbarDef, $iconType = 0)
	{
		// タイトル作成
		$titleTag = '';
		if (!empty($navbarDef->title)){
			$title = convertToHtmlEntity($navbarDef->title);
			$iconTag = '';
			switch ($iconType){
				case 1:		// ウィジェット設定画面
					//$iconTag = '<i class="glyphicon glyphicon-cog"></i> ';
					$iconTag = '<i class="fas fa-cog"></i> ';
					break;
				case 2:		// 共通設定画面
					//$iconTag = '<i class="glyphicon glyphicon-tasks"></i> ';
					$iconTag = '<i class="fas fa-window-maximize"></i> ';
					break;
				case 3:		// テンプレート設定画面
					//$iconTag = '<i class="glyphicon glyphicon-wrench"></i> ';
					$iconTag = '<i class="fas fa-wrench"></i> ';
					break;
			}
			if (empty($navbarDef->help)){
				$title = $iconTag . $title;
			} else {
				if (empty($iconTag)){
					$title = '<span ' . $navbarDef->help . '>' . $title . '</span>';
				} else {
					$title = '<span ' . $navbarDef->help . '>' . $iconTag . '</span>' . $title;
				}
			}
//			$titleTag = '<div class="navbar-text title">' . $title . '</div>';
			$titleTag = '<div class="navbar-brand">' . $title . '</div>';
		}
		return $titleTag;
	}
	/**
	 * サブメニューバーのメニュータグ作成
	 *
	 * @param object $navbarDef			メニューバー定義
	 * @return string 					サブメニューバーのHTML
	 */
	function createSubMenubarMenuTag($navbarDef)
	{
		global $gRequestManager;
		
		$isSmallDeviceOptimize = $this->gEnv->isSmallDeviceAdmin();				// 管理画面の小画面デバイス最適化を行うかどうか
		
		// メニュー作成
		$menuTag = '';
		$baseUrl = $navbarDef->baseurl;
		$menu = $navbarDef->menu;
		//$menuItemCount = count($menu);
		$menuItemCount = empty($menu) ? 0 : count($menu);
		for ($i = 0; $i < $menuItemCount; $i++){
			$menuItem = $menu[$i];
			$name	= $menuItem->name;
			$tagId	= $menuItem->tagid;
			$active = $menuItem->active;
			$disabled	= $menuItem->disabled;
			$visible	= isset($menuItem->visible) ? $menuItem->visible : true;
			$task	= $menuItem->task;
			$url	= $menuItem->url;
			$help	= $menuItem->help;
			$subMenu = $menuItem->submenu;
			
			if (!$visible) continue;			// 非表示の場合はタグ出力しない
			
			if (empty($subMenu)){		// サブメニューを持たない場合
				if ($isSmallDeviceOptimize){	// 管理画面の小画面デバイス最適化を行う場合
					$buttonType = '';
				} else {
					if ($active){
						$buttonType = 'btn-primary';
					} else {
						$buttonType = 'btn-success';
					}
				}
				if ($disabled) $buttonType .= ' disabled';		// 使用可否
				$tagIdAttr = '';		// タグID
				if (!empty($tagId)) $tagIdAttr = ' id="' . $tagId . '"';
				
				// タスクまたはURLが設定されている場合はリンクを設定
				$event = '';
				$linkUrl = '';			// リンク先 
				if (!empty($task)) $linkUrl = createUrl($baseUrl, 'task=' . $task);
				if (empty($linkUrl)) $linkUrl = $url;
				if (!empty($linkUrl)) $event = ' onclick="window.location=\'' . $linkUrl . '\';"';
				if ($isSmallDeviceOptimize){	// 管理画面の小画面デバイス最適化を行う場合
					$button = '<button type="button"' . $tagIdAttr . ' class="' . $buttonType . '"' . $event . '>' . convertToHtmlEntity($name) . '</button>';
				} else {
					$button = '<button type="button"' . $tagIdAttr . ' class="btn navbar-btn ' . $buttonType . '"' . $event . '>' . convertToHtmlEntity($name) . '</button>';
				}
				if (!empty($help)) $button = '<span ' . $help . '>' . $button . '</span>';
				$menuTag .= '<li>' . $button . '</li>';
			} else {		// サブメニューがある場合
				// アクティブな項目があるかチェック
				$subMenuTag = '';
				for ($j = 0; $j < count($subMenu); $j++){
					$subMenuItem = $subMenu[$j];
					$subName	= $subMenuItem->name;
					$subTagId	= $subMenuItem->tagid;
					$subActive	= $subMenuItem->active;
					$subDisabled	= $subMenuItem->disabled;
					$subVisible	= isset($subMenuItem->visible) ? $subMenuItem->visible : true;
					$task		= $subMenuItem->task;
					$url		= $subMenuItem->url;
					
					if (!$subVisible) continue;			// 非表示の場合はタグ出力しない
					
					$linkUrl = '';			// リンク先 
					if (!empty($task)) $linkUrl = createUrl($baseUrl, 'task=' . $task);
					if (empty($linkUrl)) $linkUrl = $url;
					if (empty($linkUrl)) $linkUrl = '#';
					$classActive = '';
					if ($subDisabled){		// 使用可否
						$classActive = ' class="disabled"';
						$linkUrl = '#';
					} else if ($subActive){
						$classActive = ' class="active"';
						$active = true;			// 親の階層もアクティブにする
					}
					$tagIdAttr = '';		// タグID
					if (!empty($subTagId)) $tagIdAttr = ' id="' . $subTagId . '"';
				
				//	$subMenuTag .= '<li' . $tagIdAttr . $classActive . '><a href="' . convertUrlToHtmlEntity($this->getUrl($linkUrl)) . '">' . convertToHtmlEntity($subName) . '</a></li>';
					$subMenuTag .= '<li' . $classActive . '><a' . $tagIdAttr . ' href="' . convertUrlToHtmlEntity($this->getUrl($linkUrl)) . '">' . convertToHtmlEntity($subName) . '</a></li>';
				}
				$subMenuTag = '<ul class="dropdown-menu" role="menu">' . $subMenuTag . '</ul>';

				$tagIdAttr = '';		// タグID
				if (!empty($tagId)) $tagIdAttr = ' id="' . $tagId . '"';
				if ($isSmallDeviceOptimize){	// 管理画面の小画面デバイス最適化を行う場合
					$buttonType = '';
				} else {
	 				if ($active){
						$buttonType = 'btn-primary';
					} else {
						$buttonType = 'btn-success';
					}
				}
				if ($isSmallDeviceOptimize){	// 管理画面の小画面デバイス最適化を行う場合
					$menuTag .= '<li><a' . $tagIdAttr . ' class="' . $buttonType . '" data-toggle="dropdown" href="#" >' . convertToHtmlEntity($name) . ' <span class="caret"></span></a>' . $subMenuTag . '</li>';
				} else {
					$menuTag .= '<li><a' . $tagIdAttr . ' class="btn navbar-btn ' . $buttonType . '" data-toggle="dropdown" href="#" >' . convertToHtmlEntity($name) . ' <span class="caret"></span></a>' . $subMenuTag . '</li>';
				}
			}
		}
		if (!empty($menuTag)){
			// 小画面デバイス最適化の場合はメニューの最後に「閉じる」項目を付加
			if ($isSmallDeviceOptimize){	// 管理画面の小画面デバイス最適化を行う場合
				// ウィンドウが別画面起動の場合のみ項目付加
				$openBy = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
				if ($openBy == 'other'){
					$name = '閉じる';
					$iconTitle = '閉じる';
//					$iconUrl = $this->gEnv->getRootUrl() . self::CLOSE_ICON_FILE;
//					$imageSize = self::NAV_ITEM_ICON_SIZE;
//					$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . $imageSize . '" height="' . $imageSize . '" alt="' . $iconTitle . '" />';
					$menuTag .= '<li class="visible-xs"><a href="#" onclick="window.close();"><i class="fas fa-window-close fa-2x text-danger" style="background: radial-gradient(white 50%, transparent 50%);"></i> ' . convertToHtmlEntity($name) . '</a></li>';
				}
			}
			$menuTag = '<ul class="nav navbar-nav">' . $menuTag . '</ul>';
		}
		return $menuTag;
	}
	/**
	 * サブメニューバーの高さを取得
	 *
	 * @return int				高さ
	 */
	function getSubMenubarHeight()
	{
		return self::SUB_MENUBAR_HEIGHT;
	}
	/**
	 * 編集ボタンを作成
	 *
	 * @param string $url		リンク先(リンク先がない場合は空文字列)
	 * @param string $title		ツールチップ用文字列
	 * @param string $tagId		タグのID
	 * @param string $attr		その他の追加属性
	 * @param bool $disabled	ボタンの使用可否
	 * @param string $btnClass	ボタンのカラークラス
	 * @return string 			ボタンのタグ
	 */
	function createEditButton($url, $title = '', $tagId = '', $attr = '', $disabled = false, $btnClass = 'btn-warning')
	{
		if (empty($url)){
			$urlAttr = ' href="javascript:void(0);"';
		} else {
			$urlAttr = ' href="' . convertUrlToHtmlEntity($this->getUrl($url)) . '"';
		}
		$idAttr = '';
		if (!empty($tagId)) $idAttr = ' id="' . $tagId . '"';
		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';
		if (!empty($attr)) $otherAttr .= ' ' . $attr;
		$tagClass = 'btn btn-sm ' . $btnClass;
		if ($disabled) $tagClass .= ' disabled';
		$buttonTag = '<a' . $idAttr . $urlAttr . ' class="' . $tagClass . '" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon glyphicon-edit"></i></a>';
		return $buttonTag;
	}
	/**
	 * プレビューボタンを作成
	 *
	 * @param string $url		リンク先(リンク先がない場合は空文字列)
	 * @param string $title		ツールチップ用文字列
	 * @param string $tagId		タグのID
	 * @param string $attr		その他の追加属性
	 * @param bool $disabled	ボタンの使用可否
	 * @param string $btnClass	ボタンのカラークラス
	 * @return string 			ボタンのタグ
	 */
	function createPreviewButton($url, $title = '', $tagId = '', $attr = '', $disabled = false, $btnClass = 'btn-default')
	{
		if (empty($url)){
			$urlAttr = ' href="javascript:void(0);"';
		} else {
			$urlAttr = ' href="' . convertUrlToHtmlEntity($this->getUrl($url)) . '"';
		}
		$idAttr = '';
		if (!empty($tagId)) $idAttr = ' id="' . $tagId . '"';
		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';
		if (!empty($attr)) $otherAttr .= ' ' . $attr;
		$tagClass = 'btn btn-sm ' . $btnClass;
		if ($disabled) $tagClass .= ' disabled';
		$buttonTag = '<a' . $idAttr . $urlAttr . ' class="' . $tagClass . '" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon glyphicon-new-window"></i></a>';
		return $buttonTag;
	}
	/**
	 * 画像プレビューボタンを作成
	 *
	 * @param string $url		リンク先(リンク先がない場合は空文字列)
	 * @param string $title		ツールチップ用文字列
	 * @param string $tagId		タグのID
	 * @param string $attr		その他の追加属性
	 * @param bool $disabled	ボタンの使用可否
	 * @param string $btnClass	ボタンのカラークラス
	 * @return string 			ボタンのタグ
	 */
	function createPreviewImageButton($url, $title = '', $tagId = '', $attr = '', $disabled = false, $btnClass = 'btn-default')
	{
		if (empty($url)){
			$urlAttr = ' href="javascript:void(0);"';
		} else {
			$urlAttr = ' href="' . convertUrlToHtmlEntity($this->getUrl($url)) . '"';
		}
		$idAttr = '';
		if (!empty($tagId)) $idAttr = ' id="' . $tagId . '"';
		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';
		if (!empty($attr)) $otherAttr .= ' ' . $attr;
		$tagClass = 'btn btn-sm ' . $btnClass;
		if ($disabled) $tagClass .= ' disabled';
		$buttonTag = '<a' . $idAttr . $urlAttr . ' class="' . $tagClass . '" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon glyphicon-picture"></i></a>';
		return $buttonTag;
	}
	/**
	 * ゴミ箱ボタンを作成
	 *
	 * @param string $url		リンク先(リンク先がない場合は空文字列)
	 * @param string $title		ツールチップ用文字列
	 * @param string $tagId		タグのID
	 * @param string $attr		その他の追加属性
	 * @param bool $disabled	ボタンの使用可否
	 * @param string $btnClass	ボタンのカラークラス
	 * @return string 			ボタンのタグ
	 */
	function createTrashButton($url, $title = '', $tagId = '', $attr = '', $disabled = false, $btnClass = 'btn-default')
	{
		if (empty($url)){
			$urlAttr = ' href="javascript:void(0);"';
		} else {
			$urlAttr = ' href="' . convertUrlToHtmlEntity($this->getUrl($url)) . '"';
		}
		$idAttr = '';
		if (!empty($tagId)) $idAttr = ' id="' . $tagId . '"';
		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';
		if (!empty($attr)) $otherAttr .= ' ' . $attr;
		$tagClass = 'btn btn-sm ' . $btnClass;
		if ($disabled) $tagClass .= ' disabled';
		$buttonTag = '<a' . $idAttr . $urlAttr . ' class="' . $tagClass . '" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon glyphicon-trash"></i></a>';
		return $buttonTag;
	}
	/**
	 * ダウンロードボタンを作成
	 *
	 * @param string $url		リンク先(リンク先がない場合は空文字列)
	 * @param string $title		ツールチップ用文字列
	 * @param string $tagId		タグのID
	 * @param string $attr		その他の追加属性
	 * @param bool $disabled	ボタンの使用可否
	 * @param string $btnClass	ボタンのカラークラス
	 * @return string 			ボタンのタグ
	 */
	function createDownloadButton($url, $title = '', $tagId = '', $attr = '', $disabled = false, $btnClass = 'btn-default')
	{
		if (empty($url)){
			$urlAttr = ' href="javascript:void(0);"';
		} else {
			$urlAttr = ' href="' . convertUrlToHtmlEntity($this->getUrl($url)) . '"';
		}
		$idAttr = '';
		if (!empty($tagId)) $idAttr = ' id="' . $tagId . '"';
		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';
		if (!empty($attr)) $otherAttr .= ' ' . $attr;
		$tagClass = 'btn btn-sm ' . $btnClass;
		if ($disabled) $tagClass .= ' disabled';
		$buttonTag = '<a' . $idAttr . $urlAttr . ' class="' . $tagClass . '" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon glyphicon-cloud-download"></i></a>';
		return $buttonTag;
	}
	/**
	 * アップロードボタンを作成
	 *
	 * @param string $url		リンク先(リンク先がない場合は空文字列)
	 * @param string $title		ツールチップ用文字列
	 * @param string $tagId		タグのID
	 * @param string $attr		その他の追加属性
	 * @param bool $disabled	ボタンの使用可否
	 * @param string $btnClass	ボタンのカラークラス
	 * @return string 			ボタンのタグ
	 */
	function createUploadButton($url, $title = '', $tagId = '', $attr = '', $disabled = false, $btnClass = 'btn-default')
	{
		if (empty($url)){
			$urlAttr = ' href="javascript:void(0);"';
		} else {
			$urlAttr = ' href="' . convertUrlToHtmlEntity($this->getUrl($url)) . '"';
		}
		$idAttr = '';
		if (!empty($tagId)) $idAttr = ' id="' . $tagId . '"';
		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';
		if (!empty($attr)) $otherAttr .= ' ' . $attr;
		$tagClass = 'btn btn-sm ' . $btnClass;
		if ($disabled) $tagClass .= ' disabled';
		$buttonTag = '<a' . $idAttr . $urlAttr . ' class="' . $tagClass . '" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon glyphicon-cloud-upload"></i></a>';
		return $buttonTag;
	}
	/**
	 * 期間入力ボタンを作成
	 *
	 * @param string $url		リンク先(リンク先がない場合は空文字列)
	 * @param string $title		ツールチップ用文字列
	 * @param string $tagId		タグのID
	 * @param string $attr		その他の追加属性
	 * @param bool $disabled	ボタンの使用可否
	 * @param string $btnClass	ボタンのカラークラス
	 * @return string 			ボタンのタグ
	 */
	function createTermButton($url, $title = '', $tagId = '', $attr = '', $disabled = false, $btnClass = 'btn-warning')
	{
		if (empty($url)){
			$urlAttr = ' href="javascript:void(0);"';
		} else {
			$urlAttr = ' href="' . convertUrlToHtmlEntity($this->getUrl($url)) . '"';
		}
		$idAttr = '';
		if (!empty($tagId)) $idAttr = ' id="' . $tagId . '"';
		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';
		if (!empty($attr)) $otherAttr .= ' ' . $attr;
		$tagClass = 'btn btn-sm ' . $btnClass;
		if ($disabled) $tagClass .= ' disabled';
		$buttonTag = '<a' . $idAttr . $urlAttr . ' class="' . $tagClass . '" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon glyphicon-time"></i></a>';
		return $buttonTag;
	}
	/**
	 * 検索ボタンを作成
	 *
	 * @param string $url		リンク先(リンク先がない場合は空文字列)
	 * @param string $title		ツールチップ用文字列
	 * @param string $tagId		タグのID
	 * @param string $attr		その他の追加属性
	 * @param bool $disabled	ボタンの使用可否
	 * @param string $btnClass	ボタンのカラークラス
	 * @return string 			ボタンのタグ
	 */
	function createSearchButton($url, $title = '', $tagId = '', $attr = '', $disabled = false, $btnClass = 'btn-warning')
	{
		if (empty($url)){
			$urlAttr = ' href="javascript:void(0);"';
		} else {
			$urlAttr = ' href="' . convertUrlToHtmlEntity($this->getUrl($url)) . '"';
		}
		$idAttr = '';
		if (!empty($tagId)) $idAttr = ' id="' . $tagId . '"';
		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';
		if (!empty($attr)) $otherAttr .= ' ' . $attr;
		$tagClass = 'btn btn-sm ' . $btnClass;
		if ($disabled) $tagClass .= ' disabled';
		$buttonTag = '<a' . $idAttr . $urlAttr . ' class="' . $tagClass . '" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon glyphicon-search"></i></a>';
		return $buttonTag;
	}
	/**
	 * オプション表示ボタンを作成
	 *
	 * @param int    $buttonType	ボタンタイプ(0=表示用ボタン,1=非表示用ボタン)
	 * @param string $url			リンク先(リンク先がない場合は空文字列)
	 * @param string $title			ツールチップ用文字列
	 * @param string $tagId			タグのID
	 * @param string $attr			その他の追加属性
	 * @param bool $disabled		ボタンの使用可否
	 * @param string $btnClass		ボタンのカラークラス
	 * @return string 				ボタンのタグ
	 */
	function createOptionButton($buttonType, $url, $title = '', $tagId = '', $attr = '', $disabled = false, $btnClass = 'btn-warning')
	{
		if (empty($url)){
			$urlAttr = ' href="javascript:void(0);"';
		} else {
			$urlAttr = ' href="' . convertUrlToHtmlEntity($this->getUrl($url)) . '"';
		}
		$idAttr = '';
		if (!empty($tagId)) $idAttr = ' id="' . $tagId . '"';
		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';
		if (!empty($attr)) $otherAttr .= ' ' . $attr;
		$tagClass = 'btn btn-sm ' . $btnClass;
		if ($disabled) $tagClass .= ' disabled';
		
		// アイコンタイプ
		$iconType = 'glyphicon-plus';
		if ($buttonType == 1) $iconType = 'glyphicon-minus';
		
		$buttonTag = '<a' . $idAttr . $urlAttr . ' class="' . $tagClass . '" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon ' . $iconType . '"></i></a>';
		return $buttonTag;
	}
	/**
	 * 情報リンクを作成
	 *
	 * @param string $url		リンク先(リンク先がない場合は空文字列)
	 * @param string $title		ツールチップ用文字列
	 * @param string $tagId		タグのID
	 * @param string $attr		その他の追加属性
	 * @param string $btnClass	ボタンのカラークラス
	 * @return string 			ボタンのタグ
	 */
	function createInfoLink($url, $title = '', $btnClass = 'btn-default')
	{
		if (empty($url)){
			$url = 'javascript:void(0);';
		} else {
			$url = convertUrlToHtmlEntity($this->getUrl($url));
		}

		$otherAttr = '';
		if (!empty($title)) $otherAttr .= ' rel="m3help" title="' . $title . '"';

		$buttonTag = '<a href="' . $url . '" target="_blank" role="button" data-container="body"' . $otherAttr . '><i class="glyphicon glyphicon-info-sign"></i></a>';
		return $buttonTag;
	}
	/**
	 * エラー情報アイコンを作成
	 *
	 * @param string $message	エラー情報メッセージ
	 * @return string 			アイコンのタグ
	 */
	function createErrorInfoIcon($message = '')
	{
		$iconTag = '<span class="fa-stack fa-xs text-danger m3errorinfo" rel="m3help" data-placement="bottom" title="' . $message . '"><i class="fas fa-circle fa-stack-2x"></i><i class="fas fa-exclamation fa-stack-1x fa-inverse"></i></span>';
		return $iconTag;
	}
	/**
	 * カレンダーによる期間入力フィールドを作成
	 *
	 * @param string $startDateId		開始日タグID,タグ名
	 * @param string $startTimeId		開始時間タグID,タグ名
	 * @param string $endDateId			終了日タグID,タグ名
	 * @param string $endTimeId			終了時間タグID,タグ名
	 * @param string $startDateValue	開始日
	 * @param string $startTimeValue	開始時間
	 * @param string $endDateValue		終了日
	 * @param string $endTimeValue		終了時間
	 * @param string $startDateLabel	開始日ラベル
	 * @param string $startTimeLabel	開始時間ラベル
	 * @param string $endDateLabel		終了日ラベル
	 * @param string $endTimeLabeld		終了時間ラベル
	 * @param string $calendarLabel		カレンダーラベル
	 * @param string $startDateButtonId	開始日用カレンダー起動ボタンタグID
	 * @param string $endDateButtonId	終了日用カレンダー起動ボタンタグID
	 * @return string 					タグ
	 */
	function createCalendarRangeControl($startDateId, $startTimeId, $endDateId, $endTimeId, 
										$startDateValue, $startTimeValue, $endDateValue, $endTimeValue, 
										$startDateLabel, $startTimeLabel, $endDateLabel, $endTimeLabel, $calendarLabel, $startDateButtonId, $endDateButtonId)
	{
		$dateIconUrl = call_user_func($this->_getUrlCallback, $this->gEnv->getRootUrl() . self::CALENDAR_ICON_FILE);		// カレンダーアイコン
				
		$tag  = '<div class="form-control-static col-sm-1 m3config_item" >' . convertToHtmlEntity($startDateLabel) . '</div>';
		$tag .= '<div class="col-sm-2 m3config_item" style="width:130px;"><input type="text" class="form-control spacer_bottom" id="' . $startDateId . '" name="' . $startDateId . '" value="' . convertToHtmlEntity($startDateValue) . '" maxlength="10" /></div>';
		$tag .= '<div class="form-control-static col-sm-1" style="width:25px;padding-left:3px;"><a href="#" id="' . $startDateButtonId . '"><img src="' . $dateIconUrl . '" alt="' . convertToHtmlEntity($calendarLabel) . '" title="' . convertToHtmlEntity($calendarLabel) . '" rel="m3help" /></a></div>';
		$tag .= '<div class="form-control-static col-sm-1 m3config_item" style="width:50px;">' . convertToHtmlEntity($startTimeLabel) . '</div>';
		$tag .= '<div class="col-sm-2 m3config_item" style="width:110px;"><input type="text" class="form-control spacer_bottom" id="' . $startTimeId . '" name="' . $startTimeId . '" value="' . convertToHtmlEntity($startTimeValue) . '" maxlength="10" /></div>';
		$tag .= '<div class="form-control-static col-sm-1" style="width:10px;padding-left:3px;margin-right:5px;">～</div>';
        $tag .= '<div class="form-control-static col-sm-1 m3config_item" >' . convertToHtmlEntity($endDateLabel) . '</div>';
		$tag .= '<div class="col-sm-2 m3config_item" style="width:130px;"><input type="text" class="form-control" id="' . $endDateId . '" name="' . $endDateId . '" value="' . convertToHtmlEntity($endDateValue) . '" maxlength="10" /></div>';
		$tag .= '<div class="form-control-static col-sm-1" style="width:25px;padding-left:3px;"><a href="#" id="' . $endDateButtonId . '"><img src="' . $dateIconUrl . '" alt="' . convertToHtmlEntity($calendarLabel) . '" title="' . convertToHtmlEntity($calendarLabel) . '" rel="m3help" /></a></div>';
		$tag .= '<div class="form-control-static col-sm-1 m3config_item" style="width:50px;">' . convertToHtmlEntity($endTimeLabel) . '</div>';
		$tag .= '<div class="col-sm-2 m3config_item" style="width:110px;"><input type="text" class="form-control" id="' . $endTimeId . '" name="' . $endTimeId . '" value="' . convertToHtmlEntity($endTimeValue) . '" maxlength="10" /></div>';
		return $tag;
	}
	/**
	 * ユーザアバターを作成
	 *
	 * @param string $userName			ユーザ名
	 * @param string $avatar			アバター画像名
	 * @param string $linkUrl			リンク先
	 * @param int $size					画像サイズ
	 * @return string 					アバターHTML
	 */
	function createUserAvatar($userName, $avatar, $size, $linkUrl = '')
	{
		$avatarUrl = $this->gInstance->getImageManager()->getAvatarUrl($avatar);
		$imageSize = intval($size);
		$iconTitle = convertUrlToHtmlEntity($userName);
		$iconTag = '<img class="avatar" src="' . $this->getUrl($avatarUrl) . '" width="' . $imageSize . '" height="' . $imageSize . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" rel="m3help" />';
		if (!empty($linkUrl)) $iconTag ='<a href="' . convertUrlToHtmlEntity($this->getUrl($linkUrl)) . '">' . $iconTag . '</a>';
		return $iconTag;
	}
}
?>
