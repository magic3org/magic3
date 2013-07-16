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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_s_bbs_2chOtherWidgetContainer.php 4851 2012-04-15 00:43:29Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_s_bbs_2chBaseWidgetContainer.php');

class admin_s_bbs_2chOtherWidgetContainer extends admin_s_bbs_2chBaseWidgetContainer
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
		return 'admin_other.tmpl.html';
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
		$defaultLang	= $this->gEnv->getDefaultLanguage();
		$act = $request->trimValueOf('act');
		
		$bbsTitle = $request->trimValueOf('bbs_title');					// 掲示板タイトル
		$topLink = $request->trimValueOf('title_link');				// トップ画像のリンク先
		$topImage = $request->trimValueOf('top_image');				// トップ画像
		$bgImage = $request->trimValueOf('bg_image');				// 背景画像
		$bbsGuide = $request->valueOf('bbs_guide');				// 掲示板規則(HTML許可)
		$bottomMessage = $request->valueOf('bottom_message');				// トップ画面下部メッセージ(HTML許可)
		$textColor = $request->valueOf('text_color');				// 文字色
		$bgColor = $request->valueOf('bg_color');				// 背景色
		//$titleColor = $request->valueOf('title_color');				// タイトルカラー
		$menuColor = $request->valueOf('menu_color');				// メニュー背景色
		$threadColor = $request->valueOf('thread_color');			// スレッド表示部背景色
		$makeThreadColor = $request->valueOf('makethread_color');	// スレッド作成部背景色
		$linkColor = $request->valueOf('link_color');	// リンク色
		$alinkColor = $request->valueOf('alink_color');	// リンク色(アクティブ)
		$vlinkColor = $request->valueOf('vlink_color');	// リンク色(アクセス済み)
		$subjectColor = $request->valueOf('subject_color');	// 件名文字色
		$nameColor = $request->valueOf('name_color');	// 投稿者名文字色
		$errMessageColor = $request->valueOf('err_message_color');	// エラーメッセージ文字色
		$subjectLength = $request->valueOf('subject_length');	// 件名最大長
		$nameLength = $request->valueOf('name_length');	// 投稿者名最大長
		$emailLength = $request->valueOf('email_length');	// emailアドレス最大長
		$messageLength = $request->valueOf('message_length');	// 投稿文最大長
		$lineLength = $request->valueOf('line_length');	// 投稿文行長
		$lineCount = $request->valueOf('line_count');	// 投稿文行数
		$resAnchorLinkCount = $request->valueOf('res_anchor_link_count');	// レスアンカーリンク数
		$threadCount = $request->valueOf('thread_count');	// トップ画面のスレッド最大数
		$resCount = $request->valueOf('res_count');	// トップ画面のレス最大数
		$threadRes = $request->valueOf('thread_res');	// 投稿可能なレス数の上限
		$menuThreadCount = $request->valueOf('menu_thread_count');	// メニューのスレッド最大数
		$nonameName = $request->valueOf('noname_name');	// 名前未設定時の表示名
		$adminName = $request->valueOf('admin_name');	// サイト運営者名
		$threadEndMessage = $request->valueOf('thread_end_message');	// スレッド終了メッセージ
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// パスの修正
				if (!empty($topImage)) $topImage = $this->gEnv->getMacroPath($topImage);
				if (!empty($bgImage)) $bgImage = $this->gEnv->getMacroPath($bgImage);
							
				$ret = $this->_db->updateConfig(self::CF_BBS_TITLE, $bbsTitle);		// 掲示板タイトル
				if ($ret) $this->_db->updateConfig(self::CF_TOP_LINK, $topLink);	// トップ画像のリンク先
				if ($ret) $this->_db->updateConfig(self::CF_TOP_IMAGE, $topImage);	// トップ画像
				if ($ret) $this->_db->updateConfig(self::CF_BG_IMAGE, $bgImage);	// 背景画像
				if ($ret) $this->_db->updateConfig(self::CF_BBS_GUIDE, $bbsGuide);	// 掲示板規則
				if ($ret) $this->_db->updateConfig(self::CF_BOTTOM_MESSAGE, $bottomMessage);	// トップ画面下部メッセージ
				if ($ret) $this->_db->updateConfig(self::CF_TEXT_COLOR, $textColor);	// 文字色
				if ($ret) $this->_db->updateConfig(self::CF_BG_COLOR, $bgColor);	// 背景色
				//if ($ret) $this->_db->updateConfig(self::CF_TITLE_COLOR, $titleColor);	// タイトルカラー
				if ($ret) $this->_db->updateConfig(self::CF_MENU_COLOR, $menuColor);	// メニュー背景色
				if ($ret) $this->_db->updateConfig(self::CF_THREAD_COLOR, $threadColor);	// スレッド表示部背景色
				if ($ret) $this->_db->updateConfig(self::CF_MAKE_THREAD_COLOR, $makeThreadColor);	// スレッド作成部背景色
				if ($ret) $this->_db->updateConfig(self::CF_LINK_COLOR, $linkColor);	// リンク色
				if ($ret) $this->_db->updateConfig(self::CF_ALINK_COLOR, $alinkColor);	// リンク色(アクティブ)
				if ($ret) $this->_db->updateConfig(self::CF_VLINK_COLOR, $vlinkColor);	// リンク色(アクセス済み)
				if ($ret) $this->_db->updateConfig(self::CF_SUBJECT_COLOR, $subjectColor);	// 件名文字色
				if ($ret) $this->_db->updateConfig(self::CF_NAME_COLOR, $nameColor);	// 投稿者名文字色
				if ($ret) $this->_db->updateConfig(self::CF_ERR_MESSAGE_COLOR, $errMessageColor);	// エラーメッセージ文字色
				if ($ret) $this->_db->updateConfig(self::CF_SUBJECT_LENGTH, $subjectLength);	// 件名最大長
				if ($ret) $this->_db->updateConfig(self::CF_NAME_LENGTH, $nameLength);	// 投稿者名最大長
				if ($ret) $this->_db->updateConfig(self::CF_EMAIL_LENGTH, $emailLength);	// emailアドレス最大長
				if ($ret) $this->_db->updateConfig(self::CF_MESSAGE_LENGTH, $messageLength);	// 投稿文最大長
				if ($ret) $this->_db->updateConfig(self::CF_LINE_LENGTH, $lineLength);	// 投稿文行長
				if ($ret) $this->_db->updateConfig(self::CF_LINE_COUNT, $lineCount);	// 投稿文行数
				if ($ret) $this->_db->updateConfig(self::CF_RES_ANCHOR_LINK_COUNT, $resAnchorLinkCount);	// レスアンカーリンク数
				if ($ret) $this->_db->updateConfig(self::CF_THREAD_COUNT, $threadCount);	// トップ画面のスレッド最大数
				if ($ret) $this->_db->updateConfig(self::CF_RES_COUNT, $resCount);	// トップ画面のレス最大数
				if ($ret) $this->_db->updateConfig(self::CF_THREAD_RES, $threadRes);	// 投稿可能なレス数の上限
				if ($ret) $this->_db->updateConfig(self::CF_MENU_THREAD_COUNT, $menuThreadCount);	// メニューのスレッド最大数
				if ($ret) $this->_db->updateConfig(self::CF_NONAME_NAME, $nonameName);	// 名前未設定時の表示名
				if ($ret) $this->_db->updateConfig(self::CF_ADMIN_NAME, $adminName);	// サイト運営者名
				if ($ret) $this->_db->updateConfig(self::CF_THREAD_END_MESSAGE, $threadEndMessage);		// スレッド終了メッセージ
				
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					// BBS定義を再読み込み
					$this->_loadConfig();
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			$reloadData = true;		// データの再読み込み
		}
		if ($reloadData){
			$bbsTitle = $this->_configArray[self::CF_BBS_TITLE];		// 掲示板タイトル
			$topLink = $this->_configArray[self::CF_TOP_LINK];					// トップ画像のリンク先
			$topImage = $this->_configArray[self::CF_TOP_IMAGE];		// トップ画像
			$bgImage = $this->_configArray[self::CF_BG_IMAGE];					// 背景画像
			$bbsGuide = $this->_configArray[self::CF_BBS_GUIDE];	// 掲示板規則
			$bottomMessage = $this->_configArray[self::CF_BOTTOM_MESSAGE];	// トップ画面下部メッセージ
			if (is_null($bottomMessage)) $bottomMessage = self::DEFAULT_BOTTOM_MESSAGE;
			$textColor = $this->_configArray[self::CF_TEXT_COLOR];	// 文字色
			$bgColor = $this->_configArray[self::CF_BG_COLOR];	// 背景色
			//$titleColor = $this->_configArray[self::CF_TITLE_COLOR];	// タイトルカラー
			$menuColor = $this->_configArray[self::CF_MENU_COLOR];	// メニュー背景色
			$threadColor = $this->_configArray[self::CF_THREAD_COLOR];	// スレッド表示部背景色
			$makeThreadColor = $this->_configArray[self::CF_MAKE_THREAD_COLOR];	// スレッド作成部背景色
			$linkColor = $this->_configArray[self::CF_LINK_COLOR];	// リンク色
			$alinkColor = $this->_configArray[self::CF_ALINK_COLOR];	// リンク色(アクティブ)
			$vlinkColor = $this->_configArray[self::CF_VLINK_COLOR];	// リンク色(アクセス済み)
			$subjectColor = $this->_configArray[self::CF_SUBJECT_COLOR];	// 件名文字色
			$nameColor = $this->_configArray[self::CF_NAME_COLOR];	// 投稿者名文字色
			$errMessageColor = $this->_configArray[self::CF_ERR_MESSAGE_COLOR];	// エラーメッセージ文字色
			$subjectLength = $this->_configArray[self::CF_SUBJECT_LENGTH];	// 件名最大長
			$nameLength = $this->_configArray[self::CF_NAME_LENGTH];	// 投稿者名最大長
			$emailLength = $this->_configArray[self::CF_EMAIL_LENGTH];	// emailアドレス最大長
			$messageLength = $this->_configArray[self::CF_MESSAGE_LENGTH];	// 投稿文最大長
			$lineLength = $this->_configArray[self::CF_LINE_LENGTH];	// 投稿文行長
			$lineCount = $this->_configArray[self::CF_LINE_COUNT];	// 投稿文行数
			$resAnchorLinkCount = $this->_configArray[self::CF_RES_ANCHOR_LINK_COUNT];	// レスアンカーリンク数
			$threadCount = $this->_configArray[self::CF_THREAD_COUNT];	// トップ画面のスレッド最大数
			$resCount = $this->_configArray[self::CF_RES_COUNT];	// トップ画面のレス最大数
			$threadRes = $this->_configArray[self::CF_THREAD_RES];	// 投稿可能なレス数の上限
			$menuThreadCount = $this->_configArray[self::CF_MENU_THREAD_COUNT];	// メニューのスレッド最大数
			$nonameName = $this->_configArray[self::CF_NONAME_NAME];	// 名前未設定時の表示名
			$adminName = $this->_configArray[self::CF_ADMIN_NAME];	// サイト運営者名
			if (empty($adminName)) $adminName = self::DEFAULT_ADMIN_NAME;
			$threadEndMessage = $this->_configArray[self::CF_THREAD_END_MESSAGE];		// スレッド終了メッセージ
			if (empty($threadEndMessage)) $threadEndMessage = self::DEFAULT_THREAD_END_MESSAGE;
			
			// パスの修正
			$topImage = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $topImage);
			$bgImage = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $bgImage);
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "bbs_title", $bbsTitle);		// 掲示板タイトル
		$this->tmpl->addVar("_widget", "top_link", $topLink);		// トップ画像のリンク先
		$this->tmpl->addVar("_widget", "top_image", $topImage);		// トップ画像
		$this->tmpl->addVar("_widget", "bg_image", $bgImage);		// 背景画像
		$this->tmpl->addVar("_widget", "bbs_guide", $bbsGuide);		// 掲示板規則
		$this->tmpl->addVar("_widget", "bottom_message", $bottomMessage);	// トップ画面下部メッセージ
		$this->tmpl->addVar("_widget", "text_color", $textColor);		// 文字色
		$this->tmpl->addVar("_widget", "bg_color", $bgColor);		// 背景色
		//$this->tmpl->addVar("_widget", "title_color", $titleColor);		// タイトルカラー
		$this->tmpl->addVar("_widget", "menu_color", $menuColor);		// メニュー背景色
		$this->tmpl->addVar("_widget", "thread_color", $threadColor);		// スレッド表示部背景色
		$this->tmpl->addVar("_widget", "makethread_color", $makeThreadColor);		// スレッド作成部背景色
		$this->tmpl->addVar("_widget", "link_color", $linkColor);	// リンク色
		$this->tmpl->addVar("_widget", "alink_color", $alinkColor);	// リンク色(アクティブ)
		$this->tmpl->addVar("_widget", "vlink_color", $vlinkColor);	// リンク色(アクセス済み)
		$this->tmpl->addVar("_widget", "subject_color", $subjectColor);	// 件名文字色
		$this->tmpl->addVar("_widget", "name_color", $nameColor);	// 投稿者名文字色
		$this->tmpl->addVar("_widget", "err_message_color", $errMessageColor);	// エラーメッセージ文字色
		$this->tmpl->addVar("_widget", "subject_length", $subjectLength);	// 件名最大長
		$this->tmpl->addVar("_widget", "name_length", $nameLength);	// 投稿者名最大長
		$this->tmpl->addVar("_widget", "email_length", $emailLength);	// emailアドレス最大長
		$this->tmpl->addVar("_widget", "message_length", $messageLength);	// 投稿文最大長
		$this->tmpl->addVar("_widget", "line_length", $lineLength);	// 投稿文行長
		$this->tmpl->addVar("_widget", "line_count", $lineCount);	// 投稿文行数
		$this->tmpl->addVar("_widget", "res_anchor_link_count", $resAnchorLinkCount);	// レスアンカーリンク数
		$this->tmpl->addVar("_widget", "thread_count", $threadCount);	// トップ画面のスレッド最大数
		$this->tmpl->addVar("_widget", "res_count", $resCount);	// トップ画面のレス最大数
		$this->tmpl->addVar("_widget", "thread_res", $threadRes);	// 投稿可能なレス数の上限
		$this->tmpl->addVar("_widget", "menu_thread_count", $menuThreadCount);	// メニューのスレッド最大数
		$this->tmpl->addVar("_widget", "noname_name", $nonameName);	// 名前未設定時の表示名
		$this->tmpl->addVar("_widget", "admin_name", $adminName);	// サイト運営者名
		$this->tmpl->addVar("_widget", "thread_end_message", $threadEndMessage);		// スレッド終了メッセージ
	}
}
?>
