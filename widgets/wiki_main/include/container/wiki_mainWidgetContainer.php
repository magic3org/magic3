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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/wiki_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/wiki_mainDb.php');
// Magic3追加ファイル
require_once($gEnvManager->getCurrentWidgetLibPath() . '/wikiConfig.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/wikiPage.php');
require_once($gEnvManager->getCurrentWidgetLibPath() . '/wikiParam.php');
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
	private $db;	// DB接続オブジェクト
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
	const CONTENT_TYPE = 'wk';		// 参照数カウント用
	const DEFAULT_CSS_FILE = '/default.css';				// CSSファイル
	const DEFAULT_BOOTSTRAP_CSS_FILE = '/default_bootstrap.css';		// Bootstrap用CSSファイル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new wiki_mainDb();

		// クラス初期化
		WikiConfig::init($this->db);
		WikiPage::init($this->db);		// Wikiページ管理クラス
		WikiParam::init($this->db);		// URLパラメータ管理クラス
		
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
		global $gEnvManager;
		global $gPageManager;
		
		// CSSファイルの設定
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$this->cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_BOOTSTRAP_CSS_FILE);		// CSSファイル
			
			// Javaスクリプトを実行
			$script = $this->getParsedTemplateData('bootstrap.tmpl.js');
			$gPageManager->addHeadScript($script);
		} else {
			$this->cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_CSS_FILE);		// CSSファイル
		}
		
		// 初期設定が完了していなときは、初期データ読み込み
		$init = false;
		if (!WikiPage::isInit()){		// 初期化未実行のとき
			set_time_limit(0);			// タイムアウトを解除
			$init = WikiPage::readInitData();
		}
		
		$this->langId = $this->gEnv->getCurrentLanguage();
		$wikiLibDir = $this->gEnv->getCurrentWidgetLibPath();
		
		// Defaults
		$notify = 0;

		// Load *.ini.php files and init PukiWiki
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
		
		// コマンド、プラグインが設定されていない場合は、クエリー文字列をInterWikiNameとする
		$cmd = WikiParam::getCmd();
		$plugin = WikiParam::getPlugin();
		if (empty($cmd) && empty($plugin)){		
			WikiParam::setCmd('read');
	
			$arg = WikiParam::getUnbraketArg();
			if ($arg == '') $arg = WikiConfig::getDefaultPage();
			WikiParam::setPage($arg);
		}
		// グローバル変数に値を格納
		$vars['page'] = WikiParam::getPage();
			
		$retvars = array();
		$is_cmd = FALSE;
		$cmd = WikiParam::getCmd();
		$plugin = WikiParam::getPlugin();
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
		//$headTitle = htmlspecialchars(strip_bracket($base));	// HTMLヘッダタイトル
		$pageTitle  = make_search($base);
		
		// msgパラメータからタイトルを作成
		if (isset($retvars['msg']) && $retvars['msg'] != '') {		// プラグイン実行の戻り値がある場合
			//$headTitle = str_replace('$1', $headTitle, $retvars['msg']);
			//$pageTitle  = str_replace('$1', $pageTitle,  $retvars['msg']);
			$pageTitle  = str_replace('$1', make_pagelink($base),  $retvars['msg']);// バックリンクではなくて通常のリンクに変更 by magic3
		}
		if (isset($retvars['body']) && $retvars['body'] != '') {
			$body = $retvars['body'];
		} else {
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
				$this->gInstance->getAnalyzeManager()->updateContentViewCount(self::CONTENT_TYPE, $serial);
			}

//			if ($trackback) $body .= tb_get_rdf($base); // Add TrackBack-Ping URI
//			if ($referer) ref_save($base);
		}
		// ##### タイトルを設定 #####
		// ウィジェットタイトル作成
		$this->widgetTitle = strip_tags($pageTitle);

		// HTMLサブタイトルを設定
		$this->gPage->setHeadSubTitle($this->widgetTitle);
			
		// 表示データ作成
		list($body, $notes) = $this->createViewData($body);
		
		// 「{」「}」で囲まれた文字が変換されないようにする(patTemplate用の設定)
		$regexp = '/{([^a-z]+)}/U';
		$body = preg_replace($regexp, '\\{\\1\\}', $body);
		
		$toolbar = $this->createToolbar();// ツールバー作成
		
		// テンプレートに出力
		$this->tmpl->addVar("_widget", "content", $body);	// メインコンテンツ
		$this->tmpl->addVar("_widget", "note", $notes);		// 追記
		
		// ##### ページ構成 #####
		// ツールバー表示制御
//		if ($this->gEnv->isSystemManageUser() || WikiConfig::isShowToolbarForAllUser()){		// システム運用者以上は常に表示
		if (WikiConfig::isUserWithEditAuth() || WikiConfig::isShowToolbarForAllUser()){		// 編集権限ありまたは常時ツールバーを表示の場合
			$this->tmpl->setAttribute('show_toolbar', 'visibility', 'visible');
			$this->tmpl->addVar("show_toolbar", "toolbar", $toolbar);		// 操作ツールバー
		}
		// タイトル表示制御
		if (WikiConfig::isShowPageTitle()){
			$this->tmpl->setAttribute('show_title', 'visibility', 'visible');
			$this->tmpl->addVar("show_title", "title", $pageTitle);	// ページタイトル
			
			// Wikiページ表示のときは、リンク用URLを付加
			if (WikiParam::getCmd() == 'read'){
				$page = WikiParam::getPage();
				$r_page   = rawurlencode($page);
				$pageHref = $this->gEnv->getDefaultUrl() . WikiParam::convQuery("?$r_page");
				$pageUrl = $this->gEnv->getDefaultUrl() . htmlspecialchars(WikiParam::convQuery("?$r_page", false));
				$permaLink = "<div class=\"wiki_small_title\"><a href=\"$pageHref\">$pageUrl</a></div>";
				$this->tmpl->addVar("show_title", "title_small", $permaLink);	// リンク用URL
			}
		}
		// 添付ファイルの表示
		if (WikiConfig::isShowPageAttachFiles() && !empty($this->attachContents)){
			$this->tmpl->setAttribute('show_page_attach', 'visibility', 'visible');
			$this->tmpl->addVar("show_page_attach", "content", $this->attachContents);
		}
		// ##### その他のページの情報 #####
		$pageInfo = false;
		// 最終更新の表示
		if (WikiConfig::isShowPageLastModified() && !empty($this->lastModified)){
			$this->tmpl->setAttribute('show_last_modified', 'visibility', 'visible');
			$this->tmpl->addVar("show_last_modified", "content", $this->lastModified);
			$pageInfo = true;
		}
		// 関連ページの表示
		if (WikiConfig::isShowPageRelated() && !empty($this->relatedContents)){
			$this->tmpl->setAttribute('show_page_related', 'visibility', 'visible');
			$this->tmpl->addVar("show_page_related", "content", $this->relatedContents);
			$pageInfo = true;
		}
		if ($pageInfo){
			$this->tmpl->setAttribute('show_page_info', 'visibility', 'visible');
		}
		
		// セッションオブジェクトをセッションに保存
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
	 * 表示用データ作成
	 *
	 * @param string $body		本体HTML
	 * @return 			なし
	 */
	function createViewData($body)
	{
		global $related_link;
		global $attach_link;

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
/*		if ($trackback) {
			$tb_id = tb_get_id($page);
			$this->resLink['trackback'] = $script . WikiParam::convQuery("?plugin=tb&amp;__mode=view&amp;tb_id=$tb_id");
		}*/
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

		// Last modification date (string) of the page
		//$lastmodified = $this->isRead ?  format_date(get_filetime($page)) . ' ' . get_pg_passage($page, FALSE) : '';
		$this->lastModified = $this->isRead ?  format_date(get_filetime($page)) . ' ' . get_pg_passage($page, FALSE) : '';

		// List of attached files to the page
		//$attaches = ($attach_link && $this->isRead && exist_plugin_action('attach')) ? attach_filelist() : '';
		$this->attachContents = ($attach_link && $this->isRead && exist_plugin_action('attach')) ? attach_filelist() : '';

		// List of related pages
		//$related  = ($related_link && $this->isRead) ? make_related($page) : '';
		$this->relatedContents = ($related_link && $this->isRead) ? make_related($page) : '';

		// List of footnotes
		$footNote = WikiPage::getFootNote();
		ksort($footNote, SORT_NUMERIC);
		$notes = ! empty($footNote) ? $note_hr . join("\n", $footNote) : '';
		//ksort($foot_explain, SORT_NUMERIC);
		//$notes = ! empty($foot_explain) ? $note_hr . join("\n", $foot_explain) : '';
		
		// Tags will be inserted into <head></head>
		$head_tag = ! empty($head_tags) ? join("\n", $head_tags) ."\n" : '';

		// Search words
		if ($search_word_color && isset($vars['word'])) {
			$body = '<div class="small">' . $_msg_word . htmlspecialchars($vars['word']) .
				'</div>' . $hr . "\n" . $body;

			// BugTrack2/106: Only variables can be passed by reference from PHP 5.0.5
			// with array_splice(), array_flip()
			$words = preg_split('/\s+/', $vars['word'], -1, PREG_SPLIT_NO_EMPTY);
			$words = array_splice($words, 0, 10); // Max: 10 words
			$words = array_flip($words);

			$keys = array();
			foreach ($words as $word=>$id) $keys[$word] = strlen($word);
			arsort($keys, SORT_NUMERIC);
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
						'\'<strong class="word' .
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
		$pageEditable = WikiConfig::isPageEditable();		// ページ編集可能かどうか
		
		$toolbar = $this->createToolbarButton('top');
		if ($this->isPage){
			$toolbar .= '&nbsp;';
			if ($pageEditable){
				$toolbar .= $this->createToolbarButton('edit');
				if ($this->isRead && WikiConfig::isPageFreeze()){
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
				if ((bool)ini_get('file_uploads')){
					$toolbar .= $this->createToolbarButton('upload');
				}
				$toolbar .= $this->createToolbarButton('copy');
				$toolbar .= $this->createToolbarButton('rename');
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
		$toolbar .= '&nbsp;';
		$toolbar .= $this->createToolbarButton('rss10', 36, 14);
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
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$button = '<a href="' . $link[$key] . '">' .
				'<img src="' . IMAGE_DIR . $image[$key] . '" width="' . $width . '" height="' . $height . '" ' .
					'alt="' . $lang[$key] . '" title="' . $lang[$key] . '" rel="tooltip" data-toggle="tooltip" />' .
				'</a>';
		} else {
			$button = '<a href="' . $link[$key] . '">' .
				'<img src="' . IMAGE_DIR . $image[$key] . '" width="' . $width . '" height="' . $height . '" ' .
					'alt="' . $lang[$key] . '" title="' . $lang[$key] . '" />' .
				'</a>';
		}
		return $button;
	}
}
?>
