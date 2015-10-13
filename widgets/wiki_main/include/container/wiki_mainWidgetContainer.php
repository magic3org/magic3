<?php
/**
 * index.php用コンテナクラス
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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/wiki_mainDb.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/wiki_mainCommonDef.php');
// Magic3追加ファイル
require_once($gEnvManager->getCurrentWidgetLibPath() . '/wikiConfig.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/wikiPage.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/wikiParam.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/wikiScript.php');
// PukiWikiファイル
require_once($gEnvManager->getCurrentWidgetLibPath() . '/func.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/file.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/plugin.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/html.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/backup.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/convert_html.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/make_link.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/diff.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/config.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/link.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/auth.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/proxy.php');

class wiki_mainWidgetContainer extends BaseWidgetContainer
{
	private $langId;		// 現在の言語
	private $resLang = array();			// テキスト取得用
	private $resImage = array();		// 画像取得用
	private $resLink = array();			// リンク取得用
	private $isPage;					// ページが存在するかどうか
	private $isRead;					// ページ表示かどうか
	private $isFreeze;					// ページが凍結中かどうか
	private $relatedContents;			// 関連ページ表示用
	private $attachContents;			// 添付ファイル表示用
	private $lastModified;				// 最終更新表示用
	private $widgetTitle;				// ウィジェットタイトル
	private $cssFilePath;				// CSSファイル
	private $_contentParam;				// コンテンツ変換用
	const DEFAULT_CSS_FILE = '/default.css';				// CSSファイル
	const DEFAULT_BOOTSTRAP_CSS_FILE = '/default_bootstrap.css';		// Bootstrap用CSSファイル
	const INIT_SCRIPT = 'init_script.tmpl.js';				// Wiki初期化スクリプト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト取得
		$db = wiki_mainCommonDef::getDb();

		// クラス初期化
		WikiConfig::init($db);
		WikiPage::init($db);		// Wikiページ管理クラス
		WikiParam::init($db);		// URLパラメータ管理クラス
		
		// セッションオブジェクトを初期化
		WikiConfig::setSessionObj($this->getWidgetSessionObj());
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
		return 'index.tmpl.html';
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
		// PukiWiki用グローバル変数		// add for Magic3 by naoki on 2008/9/28
		global $vars;
		global $nofollow;
		global $gEnvManager;
		global $gPageManager;
		global $gDesignManager;
		
		// CSSファイルの設定
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$this->cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_BOOTSTRAP_CSS_FILE);		// CSSファイル
		} else {
			$this->cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_CSS_FILE);		// CSSファイル
		}
		
		// 初期化用Javascript
		$script = $this->getParsedTemplateData(self::INIT_SCRIPT, array($this, 'makeScript'));
		$gPageManager->addHeadScript($script);
			
		// 初期設定が完了していなときは、ページ初期データ読み込み
		$init = false;
		if (!WikiPage::isInit()){		// 初期化未実行のとき
			set_time_limit(0);			// タイムアウトを解除
			$init = WikiPage::readInitData();
		}
		
		$this->langId = $this->gEnv->getCurrentLanguage();
		$wikiLibDir = $this->gEnv->getCurrentWidgetLibPath();
		
		// Defaults
		$notify = 0;

		// ##### コマンド,プラグイン,ページIDの取得 #####
		// コマンド、プラグインが設定されていない場合は、クエリー文字列をInterWikiNameとする
		$cmd = WikiParam::getCmd();
		$plugin = WikiParam::getPlugin();
		if (empty($cmd) && empty($plugin)){			// Wikiページ名で画面を表示の場合
			WikiParam::setCmd('read');
	
			$arg = WikiParam::getUnbraketArg();
			if ($arg == '') $arg = WikiConfig::getDefaultPage();
			WikiParam::setPage($arg);
		}
		// グローバル変数に値を格納
		$vars['page'] = WikiParam::getPage();
		
		// ##### ページID設定後、各種パラメータの初期化 #####
		global $gEnvManager;
		require_once($wikiLibDir . '/init.php');

		// Load optional libraries
		if ($notify) {
			require_once($wikiLibDir . '/mail.php'); // Mail notification
		}
/*		if ($referer) {
			// Referer functionality uses trackback functions
			// without functional reason now
			require_once($wikiLibDir . '/trackback.php'); // TrackBack
		}*/
		// 初期データをインストールしたときは、リンク再構築
		if ($init) links_init();
		
		$retvars = array();
		$is_cmd = FALSE;
		$cmd = WikiParam::getCmd();		// 再取得
//		$plugin = WikiParam::getPlugin();
		if (!empty($cmd)) {
			$is_cmd  = TRUE;
			$plugin = $cmd;
		}
		// プラグイン、コマンドの実行。プラグインでグローバル変数$varsを使用。
		if ($plugin != '') {
			if (exist_plugin_action($plugin)) {
				// Found and exec
				$retvars = do_plugin_action($plugin);
				if ($retvars === FALSE) return;

				if ($is_cmd) {
					$base = WikiParam::getPage();
				} else {
					$base = WikiParam::getRefer();
				}
			} else {
				// Not found
				$msg = 'plugin=' . htmlspecialchars($plugin) . ' is not implemented.';
				$retvars = array('msg'=>$msg,'body'=>$msg);
				$base = WikiConfig::getDefaultPage();
			}
		}
		$pageTitle  = make_search($base);
		
		// msgパラメータからタイトルを作成
		if (isset($retvars['msg']) && $retvars['msg'] != '') {		// プラグイン実行の戻り値がある場合
			$pageTitle  = str_replace('$1', make_pagelink($base),  $retvars['msg']);// バックリンクではなくて通常のリンクに変更 by magic3
		} else {
			// ページが編集不可の場合はロック中マークを付加
			if (WikiConfig::isUserWithEditAuth() && !is_editable($base)) $pageTitle .= '<span class="locked"><i class="glyphicon glyphicon-lock" title="ページロック状態" rel="tooltip" data-toggle="tooltip"></i></span>';
		}
		
		if (isset($retvars['body']) && $retvars['body'] != ''){
			$body = $retvars['body'];
		} else {			// Wikiページ表示の場合
			if ($base == '' || !is_page($base)){
				$base = WikiConfig::getDefaultPage();
				//$headTitle = htmlspecialchars(strip_bracket($base));
				$pageTitle  = make_search($base);		// 目的のページの場合はバックリンクを設定
			}

			// グローバル変数に値を格納
			$vars['cmd']  = 'read';
			$vars['page'] = $base;
			WikiParam::setCmd('read');
			WikiParam::setPage($base);
			$body  = convert_html(get_source($base, false, $serial));

			// ビューカウントを更新
			if ($serial != 0 && !$this->gEnv->isSystemManageUser()){		// システム運用者以上の場合はカウントしない
				$this->gInstance->getAnalyzeManager()->updateContentViewCount(wiki_mainCommonDef::$_viewContentType, $serial);
			}
//			if ($referer) ref_save($base);
		}
		// ##### METAタグ追加 #####
		if ($nofollow) $gPageManager->addHeadOthers($gDesignManager->getMetaTag(0/*検索エンジン登録拒否*/));		// 検索エンジンのアクセス制御
		
		// ##### タイトルを設定 #####
		// ウィジェットタイトル作成
		$this->widgetTitle = strip_tags($pageTitle);

		// HTMLサブタイトルを設定
		$this->gPage->setHeadSubTitle($this->widgetTitle);
			
		// ### ページデータ作成 ###
		list($body, $notes) = $this->createViewData($request, $body);
		
		// 「{」「}」で囲まれた文字が変換されないようにする(patTemplate用の設定)
		$regexp = '/{([^a-z]+)}/U';
		$body = preg_replace($regexp, '\\{\\1\\}', $body);
		
		// Javascript追加
		$this->tmpl->addVar("_widget", "script", WikiScript::getScript());		// Javascript

		// ##### ページ作成 #####
		$layout = WikiConfig::getConfig(wiki_mainCommonDef::CF_LAYOUT_MAIN);
		if (empty($layout)) $layout = wiki_mainCommonDef::DEFAULT_LAYOUT_MAIN;	// レイアウト
		
		// ページのパーマリンクを取得
		$permaLink = '';
		if ($cmd == 'read'){
			$page = WikiParam::getPage();
			$r_page   = rawurlencode($page);
			$pageHref = $this->gEnv->getDefaultUrl() . WikiParam::convQuery("?$r_page");
			$pageUrl = $this->gEnv->getDefaultUrl() . htmlspecialchars(WikiParam::convQuery("?$r_page", false));
			$permaLink = "<small><a href=\"$pageHref\">$pageUrl</a></small>";
		}
		
		// ツールバー
		$toolbar = $this->createToolbar();		// 編集権限ありまたは常時ツールバーを表示の場合
				
		// コンテンツレイアウトのプレマクロ変換(ブロック型マクロを変換してコンテンツマクロのみ残す)
		$contentParam = array(
								M3_TAG_MACRO_TITLE		=> $pageTitle,							// ページタイトル
								M3_TAG_MACRO_URL		=> $permaLink,							// ページパーマリンク
								M3_TAG_MACRO_BODY		=> array($body, $notes),			// ページコンテンツ
								M3_TAG_MACRO_FILES		=> $this->attachContents,				// 添付ファイル
								M3_TAG_MACRO_UPDATES	=> $this->lastModified,					// 更新情報
								M3_TAG_MACRO_LINKS		=> $this->relatedContents,				// 関連ページ
								M3_TAG_MACRO_TOOLBAR	=> $toolbar					// ツールバー
							);
		$content = $this->createMacroContent($layout, $contentParam);
		$this->tmpl->addVar("_widget", "content", $content);
		
		// ### セッションオブジェクトをセッションに保存 ###
		$sessionObj = WikiConfig::getSessionObj();
		$this->setWidgetSessionObj($sessionObj);
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return $this->widgetTitle;
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
		return $this->cssFilePath;
	}
	/**
	 * JavascriptライブラリをHTMLヘッダ部に設定
	 *
	 * JavascriptライブラリをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string,array 				Javascriptライブラリ。出力しない場合は空文字列を設定。
	 */
	function _addScriptLibToHead($request, &$param)
	{
		if (WikiConfig::isUserWithEditAuth()){		// 編集権限ありのとき
			return array( ScriptLibInfo::LIB_CKEDITOR, ScriptLibInfo::LIB_ELFINDER );
		} else {
			return '';
		}
	}
	/**
	 * 表示用データ作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $body			本体HTML
	 * @return 			なし
	 */
	function createViewData($request, $body)
	{
		global $related_link;		// 関連ページを表示するかどうか。プラグインでOFFにする場合あり。
		global $attach_link;
		global $note_hr;
		global $search_word_color;		// 検索語ハイライトを行うかどうか
		global $_msg_word, $hr;

		$word = $request->valueOf('word');
		$page = WikiParam::getPage();
		$r_page = rawurlencode($page);
		$script = WikiParam::getScript();

		// リンク作成
		$this->resLink['add']      = $script . WikiParam::convQuery("?cmd=add&amp;page=$r_page");
		$this->resLink['backup']   = $script . WikiParam::convQuery("?cmd=backup&amp;page=$r_page");
		$this->resLink['copy']     = $script . WikiParam::convQuery("?plugin=template&amp;refer=$r_page");
		$this->resLink['diff']     = $script . WikiParam::convQuery("?cmd=diff&amp;page=$r_page");
		$this->resLink['edit']     = $script . WikiParam::convQuery("?cmd=edit&amp;page=$r_page");
		$this->resLink['filelist'] = $script . WikiParam::convQuery("?cmd=filelist");
		$this->resLink['freeze']   = $script . WikiParam::convQuery("?cmd=freeze&amp;page=$r_page");
		$this->resLink['help']     = $script . WikiParam::convQuery("?" . rawurlencode(WikiConfig::getHelpPage()));
		$this->resLink['list']     = $script . WikiParam::convQuery("?cmd=list");
		$this->resLink['new']      = $script . WikiParam::convQuery("?plugin=newpage&amp;refer=$r_page");
		$this->resLink['rdf']      = $script . WikiParam::convQuery("?cmd=rss&amp;ver=1.0");
		$this->resLink['recent']   = $script . WikiParam::convQuery("?" . rawurlencode(WikiConfig::getWhatsnewPage()));
//		$this->resLink['refer']    = $script . WikiParam::convQuery("?plugin=referer&amp;page=$r_page");
		$this->resLink['reload']   = $script . WikiParam::convQuery("?$r_page");
		$this->resLink['rename']   = $script . WikiParam::convQuery("?plugin=rename&amp;refer=$r_page");
		$this->resLink['rss']      = $script . WikiParam::convQuery("?cmd=rss");
		$this->resLink['rss10']    = $script . WikiParam::convQuery("?cmd=rss&amp;ver=1.0"); // Same as 'rdf'
		$this->resLink['rss20']    = $script . WikiParam::convQuery("?cmd=rss&amp;ver=2.0");
		$this->resLink['search']   = $script . WikiParam::convQuery("?cmd=search");
		$this->resLink['top']      = $script . WikiParam::convQuery("?" . rawurlencode(WikiConfig::getDefaultPage()));
		$this->resLink['unfreeze'] = $script . WikiParam::convQuery("?cmd=unfreeze&amp;page=$r_page");
		$this->resLink['upload']   = $script . WikiParam::convQuery("?plugin=attach&amp;pcmd=upload&amp;page=$r_page");

		// Set toolbar-specific images
		$this->resImage['reload']   = 'reload.png';
		$this->resImage['new']      = 'new.png';
		$this->resImage['edit']     = 'edit.png';
		$this->resImage['freeze']   = 'freeze.png';
		$this->resImage['unfreeze'] = 'unfreeze.png';
		$this->resImage['diff']     = 'diff.png';
		$this->resImage['upload']   = 'file.png';
		$this->resImage['copy']     = 'copy.png';
		$this->resImage['rename']   = 'rename.png';
		$this->resImage['top']      = 'top.png';
		$this->resImage['list']     = 'list.png';
		$this->resImage['search']   = 'search.png';
		$this->resImage['recent']   = 'recentchanges.png';
		$this->resImage['backup']   = 'backup.png';
		$this->resImage['help']     = 'help.png';
		$this->resImage['rss']      = 'rss.png';
		$this->resImage['rss10']    = $this->resImage['rss'];
		$this->resImage['rss20']    = 'rss20.png';
		$this->resImage['rdf']      = 'rdf.png';

		// Init flags
		$this->isPage = (is_pagename($page) && ! arg_check('backup') && $page != WikiConfig::getWhatsnewPage());
		$this->isRead = (arg_check('read') && is_page($page));
		$this->isFreeze = is_freeze($page);

		// 添付ファイル
		if (WikiConfig::isShowPageAttachFiles()) $this->attachContents = ($attach_link && $this->isRead && exist_plugin_action('attach')) ? attach_filelist() : '';

		// 最終更新
		if (WikiConfig::isShowPageLastModified()) $this->lastModified = $this->isRead ?  format_date(get_filetime($page)) . ' ' . get_pg_passage($page, FALSE) : '';
		
		// 関連ページ
		if (WikiConfig::isShowPageRelated()) $this->relatedContents = ($related_link && $this->isRead) ? make_related($page) : '';

		// 注釈
		$footNote = WikiPage::getFootNote();
		ksort($footNote, SORT_NUMERIC);
		$notes = ! empty($footNote) ? $note_hr . join("\n", $footNote) : '';

		// Search words
//		if ($search_word_color && isset($vars['word'])){
		if ($search_word_color && $word != ''){
			//$body = '<div class="small">' . $_msg_word . htmlspecialchars($vars['word']) . '</div>' . $hr . "\n" . $body;
			$body = '<div>' . $_msg_word . htmlspecialchars($word) . '</div>' . $hr . "\n" . $body;

			// BugTrack2/106: Only variables can be passed by reference from PHP 5.0.5
			// with array_splice(), array_flip()
	//		$words = preg_split('/\s+/', $vars['word'], -1, PREG_SPLIT_NO_EMPTY);
			$words = preg_split('/\s+/', $word, -1, PREG_SPLIT_NO_EMPTY);
			$words = array_splice($words, 0, 10); // Max: 10 words
			$words = array_flip($words);

			$keys = array();
			foreach ($words as $word => $id) $keys[$word] = strlen($word);
			arsort($keys, SORT_NUMERIC);	// 長いワードから並べる
			$keys = get_search_words(array_keys($keys), TRUE);

			$id = 0;
			foreach ($keys as $key=>$pattern) {
				$s_key    = htmlspecialchars($key);
				$pattern  = '/' .
					'<textarea[^>]*>.*?<\/textarea>' .	// Ignore textareas
					'|' . '<[^>]*>' .			// Ignore tags
					'|' . '&[^;]+;' .			// Ignore entities
					'|' . '(' . $pattern . ')' .		// $matches[1]: Regex for a search word
					'/sS';
				$decorate_Nth_word = create_function(
					'$matches',
					'return (isset($matches[1])) ? ' .
						'\'<strong class="word word' .
							$id .
						'">\' . $matches[1] . \'</strong>\' : ' .
						'$matches[0];'
				);
				$body  = preg_replace_callback($pattern, $decorate_Nth_word, $body);
				$notes = preg_replace_callback($pattern, $decorate_Nth_word, $notes);
				++$id;
			}
		}

		$longtaketime = getmicrotime() - MUTIME;
		$taketime     = sprintf('%01.03f', $longtaketime);
		return array($body, $notes);
	}
	/**
	 * ツールバー作成
	 *
	 * @return string			ツールバーHTML
	 */
	function createToolbar()
	{
		// 編集権限ありまたは常時ツールバーを表示、以外の場合は作成しない
		if (!WikiConfig::isUserWithEditAuth() && !WikiConfig::isShowToolbarForAllUser()) return '';
		
		$pageEditable = WikiConfig::isPageEditable();		// ページ編集可能かどうか
		
		$toolbar = $this->createToolbarButton('top');
		if ($this->isPage){
			$toolbar .= '&nbsp;';
			if ($pageEditable){
				if (WikiConfig::isUserWithFreezeAuth()){			// 解凍・凍結権限ありの場合
					$toolbar .= $this->createToolbarButton('edit');
				} else if (!$this->isFreeze){		 // 解凍・凍結権限なしの場合は、凍結されている場合は編集ボタン非表示
					$toolbar .= $this->createToolbarButton('edit');
				}
				
			//	if ($this->isRead && WikiConfig::isPageFreeze() && WikiConfig::getFreezeButtonVisibility()){	// 凍結・解凍ボタンを表示する場合
				if ($this->isRead && WikiConfig::isUserWithFreezeAuth()){			// 解凍・凍結権限ありの場合
					if ($this->isFreeze){
						$toolbar .= $this->createToolbarButton('unfreeze');
					} else {
						$toolbar .= $this->createToolbarButton('freeze');
					}
				}
			}
			$toolbar .= $this->createToolbarButton('diff');
			if (WikiConfig::isPageBackup()){
				$toolbar .= $this->createToolbarButton('backup');
			}
			if ($pageEditable){
				if ((bool)ini_get('file_uploads') && !$this->isFreeze){				// 凍結されていない場合のみファイル添付可能
					$toolbar .= $this->createToolbarButton('upload');
				}
				$toolbar .= $this->createToolbarButton('copy');
				if (!$this->isFreeze){				// 凍結されていない場合のみファイル名変更可能
					$toolbar .= $this->createToolbarButton('rename');
				}
			}
			$toolbar .= $this->createToolbarButton('reload');
		}
		$toolbar .= '&nbsp;';
		if ($pageEditable){
			$toolbar .= $this->createToolbarButton('new');
		}
		$toolbar .= $this->createToolbarButton('list');
		$toolbar .= $this->createToolbarButton('search');
		$toolbar .= $this->createToolbarButton('recent');
		$toolbar .= '&nbsp;';
		$toolbar .= $this->createToolbarButton('help');
//		$toolbar .= '&nbsp;';
//		$toolbar .= $this->createToolbarButton('rss10', 36, 14);
		return $toolbar;
	}
	/**
	 * ツールバーボタン作成
	 *
	 * @param string $key		取得キー
	 * @param int    $width		幅
	 * @param int    $height	高さ
	 * @return string			ツールバーボタンHTML
	 */
	function createToolbarButton($key, $width = 20, $height = 20)
	{
		global $_LANG;
		global $gEnvManager;
		
		$lang	= $_LANG['skin'];
		$link	= $this->resLink;
		$image	= $this->resImage;
		if (! isset($lang[$key]) ) { $button = 'LANG NOT FOUND';  return $button; }
		if (! isset($link[$key]) ) { $button = 'LINK NOT FOUND';  return $button; }
		if (! isset($image[$key])) { $button = 'IMAGE NOT FOUND'; return $button; }

		// テンプレートタイプに合わせて出力を変更
		// Lightbox回避用のクラス付加
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$button = '<a href="' . $link[$key] . '">' .
				'<img src="' . IMAGE_DIR . $image[$key] . '" class="no-lightbox" width="' . $width . '" height="' . $height . '" alt="' . $lang[$key] . '" title="' . $lang[$key] . '" rel="tooltip" data-toggle="tooltip" />' .
				'</a>';
		} else {
			$button = '<a href="' . $link[$key] . '">' .
				'<img src="' . IMAGE_DIR . $image[$key] . '" class="no-lightbox" width="' . $width . '" height="' . $height . '" alt="' . $lang[$key] . '" title="' . $lang[$key] . '" />' .
				'</a>';
		}
		return $button;
	}
	/**
	 * Script作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeScript($tmpl)
	{
		global $gEnvManager;
		
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			if (WikiConfig::isUserWithEditAuth()){		// 編集権限ありのとき
				$tmpl->setAttribute('fileselect', 'visibility', 'visible');// ファイル選択UI作成
			}
		}
	}
	/**
	 * コンテンツのプレマクロ変換
	 *
	 * @param string $layout		レイアウト
	 * @param array	$contentParam	コンテンツ作成用パラメータ
	 * @return string				作成コンテンツ
	 */
	function createMacroContent($layout, $contentParam)
	{
		$this->_contentParam = $contentParam;
		$dest = preg_replace_callback(M3_PATTERN_TAG_MACRO, array($this, '_replace_macro_callback'), $layout);
		return $dest;
	}
	/**
	 * コンテンツマクロ変換コールバック関数
	 * 変換される文字列はHTMLタグではないテキストで、変換後のテキストはHTMLタグ(改行)を含むか、HTMLエスケープしたテキスト
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_macro_callback($matchData)
	{
		$destTag	= $matchData[0];		// マッチした文字列全体
		$typeTag	= $matchData[1];		// マクロキー
		$options	= $matchData[2];		// マクロオプション
		
		switch ($typeTag){
		case M3_TAG_MACRO_TITLE:		// ページタイトル
			// 置換データがない場合は空文字列を返す
			if (empty($this->_contentParam[$typeTag])) return '';
		
			if (WikiConfig::isShowPageTitle()){
				$hTagLevel = $this->getHTagLevel();			// コンテンツ内のヘッダタイトルのタグレベル
				//$destTag = '<h1 class="contentheading">' . $this->_contentParam[$typeTag] . '</h1>';
				$destTag = '<h' . $hTagLevel . ' class="contentheading">' . $this->_contentParam[$typeTag] . '</h' . $hTagLevel . '>';
			} else {
				$destTag = '';
			}
			break;
		case M3_TAG_MACRO_URL:		// ページURL
			// 置換データがない場合は空文字列を返す
			if (empty($this->_contentParam[$typeTag])) return '';
			
			if (WikiConfig::isShowPageUrl()){
				$destTag = $this->_contentParam[$typeTag];
			} else {
				$destTag = '';
			}
			break;
		case M3_TAG_MACRO_BODY:		// ページコンテンツ
			list($body, $notes) = $this->_contentParam[$typeTag];
			
			$destTag = '';
			if (!empty($body)) $destTag .= '<section><div class="content">' . $body . '</div></section>';
			if (!empty($notes)) $destTag .= '<footer><div class="note">' . $notes . '</div></footer>';
			break;
		case M3_TAG_MACRO_FILES:		// 添付ファイル
		case M3_TAG_MACRO_UPDATES:		// 最終更新
		case M3_TAG_MACRO_LINKS:		// 関連ページ
			// コンテンツマクロオプションを解析
			$optionParams = $this->gInstance->getTextConvManager()->parseMacroOption($options);

			// コンテンツマクロオプション処理
			$keys = array_keys($optionParams);
			for ($i = 0; $i < count($keys); $i++){
				$optionKey = $keys[$i];
				$optionValue = $optionParams[$optionKey];

				switch ($optionKey){
				case 'pretag':		// 前方出力タグ
					$preTagSrc = $optionValue;
					break;
				case 'posttag':		// 後方出力タグ
					$postTagSrc = $optionValue;
					break;
				}
			}

			// 置換データがない場合は後のデータを取得
			if (empty($this->_contentParam[$typeTag])){
				list($tmp, $preTag) = explode('|', $preTagSrc);
				list($tmp, $postTag) = explode('|', $postTagSrc);
			} else {
				list($preTag, $tmp) = explode('|', $preTagSrc);
				list($postTag, $tmp) = explode('|', $postTagSrc);
			}
			
			$destTag = '';
			if (!empty($preTag)) $destTag .= convert_html($preTag);			// Wiki記法をHTMLタグに直す
			$destTag .= '<div>' . $this->_contentParam[$typeTag] . '</div>';
			if (!empty($postTag)) $destTag .= convert_html($postTag);			// Wiki記法をHTMLタグに直す
			break;
		case M3_TAG_MACRO_TOOLBAR:			// ツールバー
			// 置換データがない場合は空文字列を返す
			if (empty($this->_contentParam[$typeTag])) return '';
			
			$destTag = '<div class="toolbar breadcrumb">' . $this->_contentParam[$typeTag] . '</div>';
			break;
		}
		return $destTag;
	}
}
?>
