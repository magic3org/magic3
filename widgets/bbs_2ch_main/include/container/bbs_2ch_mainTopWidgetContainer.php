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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: bbs_2ch_mainTopWidgetContainer.php 4055 2011-04-01 07:15:28Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/bbs_2ch_mainBaseWidgetContainer.php');

class bbs_2ch_mainTopWidgetContainer extends bbs_2ch_mainBaseWidgetContainer
{
	private $threadCount;	// スレッド表示数
	private $threadId;		// スレッドID
	private $threadIdArray = array();	// 表示するスレッドID
	private $threadInfoArray = array();	// 表示中のスレッドの情報
	private $headTitle;		// METAタグタイトル
	private $headDesc;		// METAタグ要約
	private $headKeyword;	// METAタグキーワード
	private $headCss;			// ヘッダ追加CSS
	private $threadStyle;	// スレッド部表示スタイル
	private $subjectColor;	// 件名文字色
	private $nameColor;		// 登録者名文字色
	private $createEmailLink;	// Eメールのリンクを作成するかどうか
	private $isExistsMessage;	// メッセージが存在するかどうか
	private $ticket;		// POST確認用ハッシュキー
	private $bbsName;		// 入力再設定用(名前)
	private $bbsEmail;		// 入力再設定用(Eメールアドレス)
	private $bbsMessage;		// 入力再設定用(投稿メッセージ)
	const DEFAULT_MENU_NAME = 'menu';		// スレッドメニューの名前
	
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
		$act = $request->trimValueOf('act');
		return 'main.tmpl.html';
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
		$act = $request->trimValueOf('act');
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		$this->threadId = $request->trimValueOf(M3_REQUEST_PARAM_BBS_THREAD_ID);	// スレッドID
		$message = $request->valueOf('bbs_message');// 投稿メッセージ
		$name = $request->trimValueOf('bbs_name');// 名前
		$email = $request->trimValueOf('bbs_email');// Eメールアドレス
		
		if ($act == 'add'){		// 投稿追加のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 入力項目のエラーチェック
				$this->checkMessageInput($this->_boardId, $this->threadId, $name, $email, $message);
			
				// エラーなしの場合は、データを更新
				if ($this->getMsgCount() == 0){
					// 新規投稿文の追加
					if (strcasecmp($email, self::DEFAULT_EMAIL_NOT_UPDATE) == 0){		// 日付更新を行わないとき
						$updateDt = false;
					} else {
						$updateDt = true;		// 日付を更新
					}
					$ret = $this->_db->addMessage($this->_boardId, $this->threadId, $name, $email, $message, $updateDt, $newSerial);

					if ($ret){		// データ追加成功のとき
						//$this->setMsg(self::MSG_GUIDANCE, 'スレッドを作成しました');
						$this->setMsg(self::MSG_GUIDANCE, '書きこみが終わりました。');
					
						// 入力項目を使用不可に設定
						$this->tmpl->addVar("_widget", "name_disabled", 'disabled ');
						$this->tmpl->addVar("_widget", "email_disabled", 'disabled ');
						$this->tmpl->addVar("_widget", "subject_disabled", 'disabled ');
						$this->tmpl->addVar("_widget", "message_disabled", 'disabled ');
						$this->tmpl->addVar("_widget", "button_disabled", 'disabled ');
						
						// スレッドが最大数に達したときはメッセージを追加
						$ret = $this->_db->getThreadInfo($this->_boardId, $this->threadId, $row);
						if ($ret){
							$resCount = $this->_configArray[self::CF_THREAD_RES];
							if ($row['th_message_count'] >= $resCount){
								$adminName = $this->_configArray[self::CF_ADMIN_NAME];	// サイト運営者名
								if (empty($adminName)) $adminName = self::DEFAULT_ADMIN_NAME;
								$overMessage = $this->_configArray[self::CF_THREAD_END_MESSAGE];	// デフォルトのレス上限メッセージ
								if (empty($overMessage)) $overMessage = self::DEFAULT_THREAD_END_MESSAGE;
								$overMessage = str_replace(M3_TAG_START . self::MACRO_RES_MAX_NO . M3_TAG_END, $resCount, $overMessage);// レス上限数を埋め込む
								$ret = $this->_db->addMessage($this->_boardId, $this->threadId, $adminName, ''/*Eメール*/, $overMessage, $updateDt, $newSerial);
							}
						}
					} else {
						//$this->setMsg(self::MSG_APP_ERR, 'スレッドを作成に失敗しました');
						$this->setMsg(self::MSG_APP_ERR, '書きこみに失敗しました。');
					}
				} else {
					// 入力データを再設定
					$this->bbsName = $name;		// 入力再設定用(名前)
					$this->bbsEmail = $email;		// 入力再設定用(Eメールアドレス)
					$this->bbsMessage = $message;		// 入力再設定用(投稿メッセージ)
				}
			} else {
				$this->setMsg(self::MSG_APP_ERR, '不正な投稿により、書きこみに失敗しました。');
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		}
		// ハッシュキー作成
		$this->ticket = md5(time() . $this->gAccess->getAccessLogSerialNo());
		$request->setSessionValue(M3_SESSION_POST_TICKET, $this->ticket);		// セッションに保存
		
		// 表示スタイル作成
		$this->createEmailLink = $this->_configArray[self::CF_SHOW_EMAIL];		// Eメールのリンクを作成するかどうか
		$threadColor = $this->_configArray[self::CF_THREAD_COLOR];		// スレッド表示部背景色
		$this->threadStyle .= 'background-color:' . $threadColor . ';';
		//$this->tmpl->addVar("_widget", "thread_style", $this->threadStyle);
		$makeThreadColor = $this->_configArray[self::CF_MAKE_THREAD_COLOR];		// スレッド作成部背景色
		$makeThreadStyle .= 'background-color:' . $makeThreadColor . ';';
		$this->subjectColor = $this->_configArray[self::CF_SUBJECT_COLOR];		// 件名文字色
		$this->nameColor = $this->_configArray[self::CF_NAME_COLOR];		// 登録者名文字色
		
		// ファイルアップロード領域
		if (!empty($this->_configArray[self::CF_FILE_UPLOAD])){		// ファイルアップロード許可のとき
			$this->tmpl->setAttribute('file_upload', 'visibility', 'visible');// ファイルアップロード領域追加
		}
		
		// スレッドメニュー作成
		$this->threadCount = $this->_configArray[self::CF_THREAD_COUNT];
		$this->_db->getThread(array($this, 'itemsLoop'), $this->_boardId, $this->_configArray[self::CF_MENU_THREAD_COUNT]/*メニューに表示するスレッド最大数*/);
		
		// 投稿文を作成
		// メッセージの最小インデックス番号を求める
		$resCount = $this->_configArray[self::CF_RES_COUNT];	// トップ画面に表示するレス最大数
		$minIndexArray = array();
		for ($i = 0; $i < count($this->threadIdArray); $i++){
			$minIndex = 2;			// 2番目のメッセージ以降
			if ($this->threadInfoArray[$i]['th_message_count'] > $resCount + 1) $minIndex = $this->threadInfoArray[$i]['th_message_count'] - $resCount + 1;
			$minIndexArray[] = $minIndex;
		}
		$this->_db->getThreadMessage(array($this, 'messagesLoop'), $this->_boardId, $this->threadIdArray, $minIndexArray);
		
		// スレッドが存在しないときはタグを非表示にする
		if (!$this->isExistsMessage) $this->tmpl->setAttribute('thread_list', 'visibility', 'hidden');
		
		// 掲示板規則部
		$this->tmpl->addVar("_widget", "make_thread_style", $makeThreadStyle);
		$this->tmpl->addVar("_widget", "menu_name", self::DEFAULT_MENU_NAME);// スレッドメニューの名前
		$this->tmpl->addVar("_widget", "menu_anchor", '#' . self::DEFAULT_MENU_NAME);// スレッドメニューへのアンカーリンク
		if ($this->isExistsMessage){
			$firstAnchorTag = '<a href="#1">▼</a>';
		} else {
			$firstAnchorTag = '▼';
		}
		$this->tmpl->addVar("_widget", "first_anchor", $firstAnchorTag);		// 最初のスレッドへのアンカーリンク
		
		// 画面下部メッセージ
		$bottomMessage = $this->_configArray[self::CF_BOTTOM_MESSAGE];
		if (is_null($bottomMessage)) $bottomMessage = self::DEFAULT_BOTTOM_MESSAGE;
		$this->tmpl->addVar("_widget", "bottom_message", $bottomMessage);
		
		// その他
		$this->tmpl->addVar("_widget", "ad", "");		// 広告バナー等
	}
	/**
	 * 取得したスレッド件名をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		// トップ画面に表示するスレッド最大数
		$no = $index + 1;
		$subject = $fetchedRow['th_subject'] . ' (' . $fetchedRow['th_message_count'] . ')';
		$threadLinkUrl = $this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $fetchedRow['th_id'] . '&' . M3_REQUEST_PARAM_LIST_NO . '=l' . self::DEFAULT_LAST_MESSAGE_COUNT;
		if ($index < $this->threadCount){
			$subject = $no . ':</a> <a href="#' . $no . '">' . $this->convertToDispString($subject);
			
			// スレッドIDを追加
			$this->threadIdArray[] = $fetchedRow['th_id'];
			
			// スレッド情報を追加
			$this->threadInfoArray[] = $fetchedRow;
		} else {
			$subject = $no . ': ' . $this->convertToDispString($subject);
		}
		
		$row = array(
			'url' => $this->convertUrlToHtmlEntity($this->getUrl($threadLinkUrl, true)),	// スレッドリンク先
			'subject' => $subject		// スレッド件名
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		return true;
	}
	/**
	 * 取得したスレッドメッセージをテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function messagesLoop($index, $fetchedRow)
	{
		static $threadIndex = 0;
		
		$threadId = $fetchedRow['te_thread_id'];
		$messageCount = $this->threadInfoArray[$threadIndex]['th_message_count'];		// メッセージ総数
		$no = $fetchedRow['te_index'];			// メッセージインデックス番号

		// メッセージが最大を超えたときは終了
		if ($no > $messageCount) return true;
		
		$findLastMessage = false;
		if ($no == $messageCount) $findLastMessage = true;// 最後のメッセージかどうか
		
		// ##### メッセージ部分を作成 #####
		// 登録者名作成
		$name = $this->convertToDispString($fetchedRow['te_user_name']);		// ユーザ名
		if (empty($name)) $name = $this->_configArray[self::CF_NONAME_NAME];				// 名前未設定時の表示名
		$email = $this->convertToDispString($fetchedRow['te_email']);			// Eメール
		if (!empty($email) && !empty($this->createEmailLink)){			// Eメールリンク作成のとき
			$name = '<a href="mailto:' . $email . '"><b>' . $name . ' </b></a>';
		} else {
			$name = '<font color="' . $this->nameColor . '"><b>' . $name . ' </b></font>';
		}
		
		// 日付作成
		$weekDay = array('日', '月', '火', '水', '木', '金', '土');
		$timestamp = strtotime($fetchedRow['te_regist_dt']);
		$weekNo = intval(date('w', $timestamp));
		$date = date('Y/m/d(' . $weekDay[$weekNo] . ') H:i:s', $timestamp);
		
		// 投稿文
		$message = $this->convertToDispString($fetchedRow['te_message']);// 文字エスケープ処理
		$message = $this->convertToPreviewText($message);			// 改行をBRタグに変換
		if ($fetchedRow['te_index'] == 1) $message .= '<br />';		// トップのメッセージは改行を追加
		
		// メッセージ変換
		$message = $this->convDispMessage($message, $threadId);
		
		// 削除メッセージのとき
		if ($fetchedRow['te_deleted']){
			$name = '参照不可';
			$message = '参照できません。';
		}
		
		$row = array(
			'no'		=> $no,		// メッセージインデックス番号
			'name'		=> $name,	// 投稿者名
			'date'		=> $date,	// 投稿日付
			'message'		=> $message		// 投稿文
		);
		$this->tmpl->addVars('message_list', $row);
		$this->tmpl->parseTemplate('message_list', 'a');
		
		// ##### スレッドごとの出力 #####
		if ($findLastMessage){		// 最後のメッセージの場合
			$threadInfo = $this->threadInfoArray[$threadIndex];
			$threadUrl = $this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId;
			$thread50Url = $this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId . '&' . M3_REQUEST_PARAM_LIST_NO . '=l' . self::DEFAULT_LAST_MESSAGE_COUNT;
			$thread100Url = $this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId . '&' . M3_REQUEST_PARAM_LIST_NO . '=' . self::DEFAULT_MESSAGE_COUNT_PER_PAGE;
			$reloadUrl = $this->_currentPageUrl;
			
			// アンカーリンク作成
			if ($threadIndex == 0){
				$foreAnchor = '#' . count($this->threadIdArray);
				if (count($this->threadIdArray) == 1){
					$nextAnchor = '#1';
				} else {
					$nextAnchor = '#' . ($threadIndex + 2);
				}
			} else if ($threadIndex < count($this->threadIdArray) -1){
				$foreAnchor = '#' . $threadIndex;
				$nextAnchor = '#' . ($threadIndex + 2);
			} else {
				$foreAnchor = '#' . $threadIndex;
				$nextAnchor = '#1';
			}
			
			// ファイルアップロード領域の表示
			$enctype = 'application/x-www-form-urlencoded';
			if (!empty($this->_configArray[self::CF_FILE_UPLOAD])){		// ファイルアップロード許可のとき
				$enctype = 'multipart/form-data';
			}
			// 入力データを再設定
			$bbsName = '';		// 入力再設定用(名前)
			$bbsEmail = '';		// 入力再設定用(Eメールアドレス)
			$bbsMessage = '';		// 入力再設定用(投稿メッセージ)
			if ($this->threadId == $threadId){
				if (!empty($this->bbsName)) $bbsName = $this->bbsName;		// 入力再設定用(名前)
				if (!empty($this->bbsEmail)) $bbsEmail = $this->bbsEmail;		// 入力再設定用(Eメールアドレス)
				if (!empty($this->bbsMessage)) $bbsMessage = $this->bbsMessage;		// 入力再設定用(投稿メッセージ)
			}
			$row = array(
				'bbs_name'		=> $this->convertToDispString($bbsName),	// 入力再設定用(名前)
				'bbs_email'		=> $this->convertToDispString($bbsEmail),	// 入力再設定用(Eメールアドレス)
				'bbs_message'	=> $this->convertToDispString($bbsMessage),	// 入力再設定用(投稿メッセージ)
				'enctype' 		=> $enctype,		// ファイルアップロード領域
				'index'			=> $threadIndex + 1,				// スレッドインデックス番号
				'menu_anchor'	=> '#' . self::DEFAULT_MENU_NAME,	// スレッドメニューへのアンカーリンク
				'fore_anchor'	=> $foreAnchor,		// 前スレッドリンク
				'next_anchor'	=> $nextAnchor,	// 次スレッドリンク
				'message_count' => $threadInfo['th_message_count'],			// メッセージ総数
				'thread_id'		=> $threadId,		// スレッドID
				'subject'		=> $this->convertToDispString($threadInfo['th_subject']),				// スレッド件名
				'subject_color' => $this->subjectColor,		// 件名表示色
				'thread_style'	=> $this->threadStyle,		// スレッド表示スタイル
				'thread_url'	=> $this->convertUrlToHtmlEntity($this->getUrl($threadUrl, true)),				// スレッド全表示URL
				'thread50_url'	=> $this->convertUrlToHtmlEntity($this->getUrl($thread50Url, true)),				// スレッド最新50表示URL
				'thread100_url'	=> $this->convertUrlToHtmlEntity($this->getUrl($thread100Url, true)),				// スレッド100まで表示URL
				'reload_url'	=> $this->convertUrlToHtmlEntity($this->getUrl($reloadUrl, true)),					// 画面再表示
				'ticket'		=> $this->ticket		// POST確認用ハッシュキー
			);
			$this->tmpl->addVars('thread_list', $row);
			$this->tmpl->parseTemplate('thread_list', 'a');
			
			$this->tmpl->clearTemplate('message_list');		// メッセージを一旦クリア
			$threadIndex++;
		}
		// メッセージが存在するかどうか
		$this->isExistsMessage = true;
		return true;
	}
}
?>
