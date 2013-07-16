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
 * @version    SVN: $Id: admin_mainTopWidgetContainer.php 5796 2013-03-05 13:04:29Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainTopWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $outputHtml;			// HTML出力
	private $addScript = array();
	const DEFAULT_NAV_ID = 'admin_menu';		// メニューID
	const TOPPAGE_IMAGE_PATH = 'toppage_image_path';				// トップページ画像
	const CONTEXTMENU_SCRIPT_FILE = '/cotextmenu1.0.js';					// コンテキストメニュー用スクリプト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
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
		return 'top.tmpl.html';
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
		// ページがダッシュボードタイプのときはメニューを表示せずにメッセージのみ表示する
		if ($this->gPage->getContentType() == M3_VIEW_TYPE_DASHBOARD){
			// コンテキストメニューを作成(現在はダッシュボード画面のみ対応)
			$this->addScript = array($this->getUrl($this->gEnv->getCurrentWidgetScriptsUrl() . self::CONTEXTMENU_SCRIPT_FILE));
			return;
		}
		
		// トップレベル項目を取得
		$navId = self::DEFAULT_NAV_ID . '.' . $this->gEnv->getCurrentLanguage();
		if (!$this->db->getNavItems($navId, 0, $rows)){			// 現在の言語で取得できないときはデフォルト言語で取得
			$navId = self::DEFAULT_NAV_ID . '.' . $this->gEnv->getDefaultLanguage();
			if (!$this->db->getNavItems($navId, 0, $rows)){		// デフォルト言語で取得できないときは拡張子なしで取得
				$navId = self::DEFAULT_NAV_ID;
				$this->db->getNavItems($navId, 0, $rows);
			}
		}
			
		$menuInner = '';
		$menuInner .= '<tr valign="top"><td>';
		$topMenuCount = count($rows);
		for ($i = 0; $i < $topMenuCount; $i++){
			if ($rows[$i]['ni_view_control'] == 0){		// 改行以外のとき
				$topId = $rows[$i]['ni_id'];
			
				// サブレベル取得
				$this->db->getNavItems($navId, $topId, $subRows);
				// 初期表示画面
				if (count($subRows) > 0) $firstTask = $this->gEnv->getDefaultAdminUrl() . '?task=' . $subRows[0]['ni_task_id'];
				
				// メニュー外枠
				$menuInner .= '<div class="ui-widget m3toppage_menu">'. M3_NL;
				
				// ### タイトル部 ###
				// 「a」タグ
				// リンク先を作成。「_」で始まるタスクはリンクを作成しない
				$topLink = '#';
				$linkTask = $rows[$i]['ni_task_id'];
				if (strncmp($linkTask, '_', strlen('_')) != 0) $topLink = $this->gEnv->getDefaultAdminUrl() . '?task=' . $linkTask;	// 起動タスクパラメータを設定
			
				// ヘルプの作成
				$helpText = '';
				$title = $rows[$i]['ni_help_title'];
				if (!empty($title)){
					$helpText = $this->gInstance->getHelpManager()->createHelpText($title, $rows[$i]['ni_help_body']);
				}
			
				$menuInner .= str_repeat(' ', 4);
				//$menuInner .= '<a href="' . $topLink . '">' . '<span ' . $helpText . '>' . $this->convertToDispString($rows[$i]['ni_name']) . '</span></a>' . M3_NL;
				$menuInner .= '<div class="ui-state-default ui-priority-primary ui-corner-tl ui-corner-tr"><span ' . $helpText . '>' . 
								$this->convertToDispString($rows[$i]['ni_name']) . '</span></div>'. M3_NL;
				
				// 「ul」タグ
				$menuInner .= str_repeat(' ', 4);
				$menuInner .= '<ul class="ui-widget-content ui-corner-bl ui-corner-br">' . M3_NL;
			
				// 「li」タグ
				if (count($subRows) > 0){
					for ($l = 0; $l < count($subRows); $l++){
						// ヘルプの作成
						$helpText = '';
						$title = $subRows[$l]['ni_help_title'];
						if (!empty($title)){
							$helpText = $this->gInstance->getHelpManager()->createHelpText($title, $subRows[$l]['ni_help_body']);
						}
			
						$menuInner .= str_repeat(' ', 8);
						$menuInner .= '<li ';
						$menuInner .= '><a href="';
						$menuInner .= $this->gEnv->getDefaultAdminUrl() . '?task=' . $subRows[$l]['ni_task_id'];	// 起動タスクパラメータを設定
						if (!empty($subRows[$l]['ni_param'])){		// パラメータが存在するときはパラメータを追加
							$menuInner .= '&' . M3_REQUEST_PARAM_OPERATION_TODO . '=' . urlencode($subRows[$l]['ni_param']);
						}
						$menuInner .= '" ><span ' . $helpText . '>' . $this->convertToDispString($subRows[$l]['ni_name']) . '</span></a></li>' . M3_NL;
					}
				}
				$menuInner .= str_repeat(' ', 4);
				$menuInner .= '</ul>' . M3_NL;
				$menuInner .= '</div>' . M3_NL;		// メニュー外枠
			} else {		// 改行のとき
				$menuInner .= '</td><td>';
			}
		}

		$menuInner .= '</td></tr>';
		$this->tmpl->addVar("_widget", "items", $menuInner);
		
		// トップページ用画像
/*		$imageUrl = $this->db->getSystemConfig(self::TOPPAGE_IMAGE_PATH);
		if (!empty($imageUrl)){
			if (strStartsWith($imageUrl, '/')){		// 相対パス表記のとき
				$relativePath = $this->gEnv->getRelativePathToSystemRootUrl($this->gEnv->getDocumentRootUrl() . $imageUrl);
				$imagePath = $this->gEnv->getSystemRootPath() . $relativePath;
				$imageUrl = $this->gEnv->getRootUrl() . $relativePath;
			} else {		// マクロ表記のとき
				$imagePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getSystemRootPath(), $imageUrl);
				$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
			}
			if (file_exists($imagePath)){		// 画像ファイルが存在するとき
				$this->tmpl->addVar("showimage", "toppage_img", $imageUrl);
				$this->tmpl->setAttribute('showimage', 'visibility', 'visible');
			}
		}*/
		
		// 管理用URL設定
		$this->tmpl->addVar("_widget", "admin_url", $this->gEnv->getDefaultAdminUrl());
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['msg_logout'] = $this->_('Logout from system?');		// ログアウトしますか?
		$this->setLocaleText($localeText);
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
	function _addCssFileToHead($request, &$param)
	{
		return array($this->getUrl($this->gEnv->getAdminDefaultThemeUrl()));
	}
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
	function _addScriptFileToHead($request, &$param)
	{
		return $this->addScript;
	}
}
?>
