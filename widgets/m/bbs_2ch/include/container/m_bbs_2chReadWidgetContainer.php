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
 * @version    SVN: $Id: m_bbs_2chReadWidgetContainer.php 4039 2011-03-21 05:37:18Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_bbs_2chBaseWidgetContainer.php');

class m_bbs_2chReadWidgetContainer extends m_bbs_2chBaseWidgetContainer
{
	private $threadStyle;	// スレッド部表示スタイル
	private $subjectColor;	// 件名文字色
	private $nameColor;		// 登録者名文字色
	private $createEmailLink;	// Eメールのリンクを作成するかどうか
	private $isExistsMessage;	// メッセージが存在するかどうか
	
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
		return 'thread.tmpl.html';
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
		$threadId = $request->trimValueOf(M3_REQUEST_PARAM_BBS_THREAD_ID);	// スレッドID
		$list = $request->trimValueOf(M3_REQUEST_PARAM_LIST_NO);			// 取得メッセージ
		$no = intval($request->trimValueOf(M3_REQUEST_PARAM_ITEM_NO));				// 取得メッセージ
		$message = $request->mobileTrimValueOf('bbs_message');// 投稿メッセージ
		$name = $request->mobileTrimValueOf('bbs_name');// 名前
		$email = $request->trimValueOf('bbs_email');// Eメールアドレス
		
		if ($act == 'add'){		// 投稿追加のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 入力項目のエラーチェック
				$this->checkMessageInput($this->_boardId, $threadId, $name, $email, $message);
			
				// エラーなしの場合は、データを更新
				if ($this->getMsgCount() == 0){
					// 新規投稿文の追加
					if (strcasecmp($email, self::DEFAULT_EMAIL_NOT_UPDATE) == 0){		// 日付更新を行わないとき
						$updateDt = false;
					} else {
						$updateDt = true;		// 日付を更新
					}
					$ret = $this->_db->addMessage($this->_boardId, $threadId, $name, $email, $message, $updateDt, $newSerial);

					if ($ret){		// データ追加成功のとき
						//$this->setMsg(self::MSG_GUIDANCE, 'スレッドを作成しました');
						$this->setMsg(self::MSG_GUIDANCE, '書きこみが終わりました。');
					
						// 入力項目を使用不可に設定
						$this->tmpl->addVar("thread_area", "name_disabled", 'disabled ');
						$this->tmpl->addVar("thread_area", "email_disabled", 'disabled ');
						$this->tmpl->addVar("thread_area", "message_disabled", 'disabled ');
						$this->tmpl->addVar("thread_area", "button_disabled", 'disabled ');
						
						// スレッドが最大数に達したときはメッセージを追加
						$ret = $this->_db->getThreadInfo($this->_boardId, $threadId, $row);
						if ($ret){
							$resCount = $this->_configArray[self::CF_THREAD_RES];
							if ($row['th_message_count'] >= $resCount){
								$adminName = $this->_configArray[self::CF_ADMIN_NAME];	// サイト運営者名
								if (empty($adminName)) $adminName = self::DEFAULT_ADMIN_NAME;
								$overMessage = $this->_configArray[self::CF_THREAD_END_MESSAGE];	// デフォルトのレス上限メッセージ
								if (empty($overMessage)) $overMessage = self::DEFAULT_THREAD_END_MESSAGE;
								$overMessage = str_replace(M3_TAG_START . self::MACRO_RES_MAX_NO . M3_TAG_END, $resCount, $overMessage);// レス上限数を埋め込む
								$ret = $this->_db->addMessage($this->_boardId, $threadId, $adminName, ''/*Eメール*/, $overMessage, $updateDt, $newSerial);
							}
						}
					} else {
						//$this->setMsg(self::MSG_APP_ERR, 'スレッドを作成に失敗しました');
						$this->setMsg(self::MSG_APP_ERR, '書きこみに失敗しました。');
					}
				} else {
					// 入力データを再設定
					$this->tmpl->addVar("thread_area", "bbs_name", $this->convertToDispString($name));
					$this->tmpl->addVar("thread_area", "bbs_email", $this->convertToDispString($email));
					$this->tmpl->addVar("thread_area", "bbs_message", $this->convertToDispString($message));
				}
			} else {
				$this->setMsg(self::MSG_APP_ERR, '不正な投稿により、書きこみに失敗しました。');
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		}
		
		// 表示スタイル作成
		$this->createEmailLink = $this->_configArray[self::CF_SHOW_EMAIL];		// Eメールのリンクを作成するかどうか
		$threadColor = $this->_configArray[self::CF_THREAD_COLOR];		// スレッド表示部背景色
		$this->threadStyle .= 'background-color:' . $threadColor . ';';
		$this->subjectColor = $this->_configArray[self::CF_SUBJECT_COLOR];		// 件名文字色
		$this->nameColor = $this->_configArray[self::CF_NAME_COLOR];		// 登録者名文字色
		
		// スレッドIDが正しいかチェック
		$ret = $this->_db->getThreadInfo($this->_boardId, $threadId, $row);
		if ($ret){
			$offset = 0;		// データ取得開始位置
			$limit = 0;		// 取得数、すべて取得
			
			// 表示範囲を取得
			if (empty($list) && !empty($no)){		// メッセージNoで取得のとき
				$offset = $no -1;
				if ($offset < 0) $offset = 0;
				$limit = 1;
			} else {		// 一覧で取得のとき
				if (strStartsWith($list, 'l')){		// 最新から取得の場合
					$messageCount = intval(substr($list, 1));
					if ($messageCount <= 0) $messageCount = 1;
					$offset = $row['th_message_count'] - $messageCount;
					if ($offset < 0) $offset = 0;
					$limit = $messageCount;
				}
			}
			// 投稿文を作成
			$this->_db->getThreadMessageByRange(array($this, 'itemsLoop'), $this->_boardId, $threadId, $limit, $offset);
			
			// リンク作成
			$threadUrl = $this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId;
			$threadNewUrl = $this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId . '&' . M3_REQUEST_PARAM_LIST_NO . '=l1';
			$thread50Url = $this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId . '&' . M3_REQUEST_PARAM_LIST_NO . '=l' . self::DEFAULT_LAST_MESSAGE_COUNT;
			$thread100Url = $this->_currentPageUrl . '&' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId . '&' . M3_REQUEST_PARAM_LIST_NO . '=' . self::DEFAULT_MESSAGE_COUNT_PER_PAGE;
			$reloadUrl = $this->_currentPageUrl;
			
			// 画面にデータを埋め込む
			$this->tmpl->addVar("thread_area", "bbs_url", $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrl(), true)));
			$this->tmpl->addVar("thread_area", "thread_url", $this->convertUrlToHtmlEntity($this->getUrl($threadUrl, true)));// スレッド全表示URL
			$this->tmpl->addVar("thread_area", "threadNew_url", $this->convertUrlToHtmlEntity($this->getUrl($threadNewUrl, true)));// スレッド最新表示URL
			$this->tmpl->addVar("thread_area", "thread50_url", $this->convertUrlToHtmlEntity($this->getUrl($thread50Url, true)));// スレッド最新50表示URL
			$this->tmpl->addVar("thread_area", "thread100_url", $this->convertUrlToHtmlEntity($this->getUrl($thread100Url, true)));// スレッド100まで表示URL
			$this->tmpl->addVar("thread_area", "reload_url", $this->convertUrlToHtmlEntity($this->getUrl($reloadUrl, true)));// 画面再表示
			$this->tmpl->addVar("thread_area", "thread_id", $threadId);
			$this->tmpl->addVar("thread_area", "subject", $this->convertToDispString($row['th_subject']));				// スレッド件名
			$this->tmpl->addVar("thread_area", "subject_color", $this->subjectColor);		// 件名表示色
			
			// スレッドが存在しないときはタグを非表示にする
			if (!$this->isExistsMessage){
				$this->setUserErrorMsg('ＥＲＲＯＲ：メッセージが存在しません！');
				
				$this->tmpl->setAttribute('message_list', 'visibility', 'hidden');
			}
			// ハッシュキー作成
			$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
			$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			$this->tmpl->addVar("thread_area", "ticket", $postTicket);				// 画面に書き出し
		} else {
			$this->setUserErrorMsg('ＥＲＲＯＲ：スレッドＩＤが不正です！');
			
			// スレッド表示部を非表示にする
			$this->tmpl->setAttribute('thread_area', 'visibility', 'hidden');
		}

		// 送信先
		$this->tmpl->addVar("thread_area", "post_url", $this->_currentPageUrl);
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("thread_area", "thread_style", $this->threadStyle);
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$no = $fetchedRow['te_index'];			// メッセージインデックス番号
		
		// ##### メッセージ部分を作成 #####
		// 登録者名作成
		$name = $this->convertToDispString($fetchedRow['te_user_name']);		// ユーザ名
		if (empty($name)) $name = $this->_configArray[self::CF_NONAME_NAME];				// 名前未設定時の表示名
		$email = $this->convertToDispString($fetchedRow['te_email']);			// Eメール
		if (!empty($email) && !empty($this->createEmailLink)){			// Eメールリンク作成のとき
			$name = '<a href="mailto:' . $email . '">' . $name . '</a>';
		} else {
			$name = '<span style="color:' . $this->nameColor . ';">' . $name . '</span>';
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
		$message = $this->convDispMessage($message, $fetchedRow['te_thread_id']);
		
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
		
		// メッセージが存在するかどうか
		$this->isExistsMessage = true;
		return true;
	}
}
?>
