<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: chacha_mainReadWidgetContainer.php 3362 2010-07-10 03:08:21Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/chacha_mainBaseWidgetContainer.php');

class chacha_mainReadWidgetContainer extends chacha_mainBaseWidgetContainer
{
	private $messageCount;			// メッセージ数
	private $isExistsMessage;	// メッセージが存在するかどうか
	private $isExistsNextPage;	// 次のページがあるかどうか
	
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
		return 'thread_read.tmpl.html';
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
		$pageNo = $request->trimIntValueOf(M3_REQUEST_PARAM_PAGE_NO, '1');				// ページ番号
		
		// クライアントIDを取得
		$clientId = '';
		if ($this->gEnv->canUseCookie()){		// クッキー使用可能なとき
			$clientId = $this->gAccess->getClientId();
		}
		
		// 会員IDを取得
		$clientMemberId = '';			// 現在の端末の会員ID
		$ret = $this->_db->getMemberInfoByDeviceId($clientId, $row);
		if ($ret) $clientMemberId = $row['mb_id'];
		
		// 表示メッセージ取得
		// 1つ多く呼び出し、次のページがあるか確認
		$this->messageCount = $this->_configArray[self::CF_MESSAGE_COUNT_MYPAGE];		// 最大項目数
		$this->_db->getThread($this->_boardId, $this->messageCount, $pageNo, array($this, 'itemsLoop'), true);
	
		// 個別のスタイル設定
		$innerStyle = self::CSS_BLOG_INNER_STYLE;
		//$innerColor = $this->_configArray[self::CF_PROFILE_COLOR];		// プロフィール背景色
		if (empty($innerColor)) $innerColor = $this->_configArray[self::CF_INNER_BG_COLOR];		// デフォルトの内枠背景色
		if (!empty($innerColor)) $innerStyle .= 'background-color:' . $innerColor . ';';
		$this->tmpl->addVar("_widget", "inner_style", $innerStyle);
		
		// 登録用リンクを作成。会員として認識できないときはプロフィール画面へ、認識できる場合はマイページ画面へ遷移。
		$this->tmpl->setAttribute('top_link_area', 'visibility', 'visible');
		if (empty($clientMemberId)){
			$rigistLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_PROFILE, true));
			$registName = 'ユーザ登録';
		} else {
			$rigistLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_MYPAGE . '&' . self::URL_PARAM_MEMBER_ID . '=' . $clientMemberId, true));
			$registName = '投稿';
		}
		$this->tmpl->addVar("top_link_area", "regist_url", $rigistLink);
		$this->tmpl->addVar("top_link_area", "regist_name", $registName);
		
		// スレッドが存在しないときは一覧を非表示にする
		if (!$this->isExistsMessage){
			$this->setGuidanceMsg('メッセージが投稿されていません');
			
			$this->tmpl->setAttribute('message_list', 'visibility', 'hidden');
		}
		
		// ページ遷移用リンク
		$pageLink = '';
		if ($pageNo > 1){			// 前のページがあるとき
			$foreLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_READ . '&page=' . ($pageNo -1), true));
			$pageLink .= '<a href="' . $foreLink . '"><b>前へ</b></a>';
		}
		if ($this->isExistsNextPage){	// 次のページがあるとき
			if (!empty($pageLink)) $pageLink .= '&nbsp;&nbsp;';
			$nextLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_READ . '&page=' . ($pageNo +1), true));
			$pageLink .= '<a href="' . $nextLink . '"><b>次へ</b></a>';
		}
		$this->tmpl->addVar("_widget", "page_link", $pageLink);
	}
	/**
	 * 取得したメッセージ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		// 最大表示数を超えたときは終了
		if ($index >= $this->messageCount){
			$this->isExistsNextPage = true;				// 次のページあり
			return false;
		}
		
		// メッセージ用のデータを取得
		$no = $fetchedRow['mt_update_no'];		// スレッド更新番号
		$memberId = $fetchedRow['mb_id'];		// 会員ID
		$memberName = $fetchedRow['mb_name'];	// 会員名
		$messageId = $fetchedRow['mm_thread_id'];
		$messageCount = $fetchedRow['mt_message_count'] -1;		// メッセージ数
		if ($messageCount <= 0) $messageCount = '';				// 返信がないときはメッセージ数を表示しない
		$mypageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId, true));
		$messageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MESSAGE_ID . '=' . $messageId, true));
		
		// 名前作成
		$nameTag = '<a href="' . $mypageLink . '">' . $this->convertToDispString($memberName) . '</a>';
		
		// 日付作成
		$weekDay = array('日', '月', '火', '水', '木', '金', '土');
		$timestamp = strtotime($fetchedRow['mm_regist_dt']);
		$weekNo = intval(date('w', $timestamp));
		$date = date('Y/m/d(' . $weekDay[$weekNo] . ') H:i:s', $timestamp);
		
		// 投稿文
		$message = $this->convertToDispString($fetchedRow['mm_message']);// 文字エスケープ処理
		$message = $this->_convertToPreviewTextWithSpace($message);			// 改行をスペースに変換
		$message = $this->convDispMessage($message, $fetchedRow['mm_thread_id']);// メッセージ内のリンクを作成
		
		// メッセージID
		$messageIdTag = '<a href="' . $messageLink  . '">#' . $messageId . '</a>';
		
		// 削除メッセージのとき
		/*if ($fetchedRow['mm_deleted']){
			$message = '参照できません。';
		}*/
		// アバター画像を設定
		$avatarImageUrl = $this->getAvatarUrl($memberId);
		$avatarTag = '<img src="' . $this->getUrl($avatarImageUrl) . '" width="' . self::AVATAR_SIZE . '" height="' . self::AVATAR_SIZE .'" />';
		$avatarTag = '<a href="' . $mypageLink . '">' . $avatarTag . '</a>';
			
		$row = array(
			'no'		=> $no,					// 項目番号
			'id'		=> $messageIdTag,		// メッセージID
			'message_count'		=> $messageCount,		// メッセージ数
			'name'		=> $nameTag,			// 会員名
			'date'		=> $date,			// 投稿日付
			'message'	=> $message,		// 投稿文
			'avatar'	=> $avatarTag	// アバター画像
		);
		$this->tmpl->addVars('message_list', $row);
		$this->tmpl->parseTemplate('message_list', 'a');
		
		// メッセージが存在するかどうか
		$this->isExistsMessage = true;
		return true;
	}
}
?>
