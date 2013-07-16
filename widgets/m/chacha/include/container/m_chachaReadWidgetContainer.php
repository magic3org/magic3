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
 * @version    SVN: $Id: m_chachaReadWidgetContainer.php 3363 2010-07-10 05:12:31Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_chachaBaseWidgetContainer.php');

class m_chachaReadWidgetContainer extends m_chachaBaseWidgetContainer
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
		if (empty($pageNo)) $pageNo = 1;
		
		// クライアントIDを取得
		$clientId = $this->_mobileId;
		
		// 会員IDを取得
		$clientMemberId = '';			// 現在の端末の会員ID
		$ret = $this->_db->getMemberInfoByDeviceId($clientId, $row);
		if ($ret) $clientMemberId = $row['mb_id'];
		
		// 表示メッセージ取得
		// 1つ多く呼び出し、次のページがあるか確認
		$this->messageCount = $this->_configArray[self::CF_MESSAGE_COUNT_MYPAGE];		// 最大項目数
		$this->_db->getThread($this->_boardId, $this->messageCount, $pageNo, array($this, 'itemsLoop'), true);
	
		// 登録用リンクを作成。会員として認識できないときはプロフィール画面へ、認識できる場合はマイページ画面へ遷移。
		if (empty($clientMemberId)){
			//$registLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_PROFILE, true));
			$registLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile('task=' . self::TASK_PROFILE)));
			$registName = 'ﾕｰｻﾞ登録';
		} else {
			//$registLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_MYPAGE . '&' . self::URL_PARAM_MEMBER_ID . '=' . $clientMemberId, true));
			$registLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile('task=' . self::TASK_MYPAGE . '&' . self::URL_PARAM_MEMBER_ID . '=' . $clientMemberId)));
			$registName = '投稿';
		}
		$this->tmpl->addVar("_widget", "regist_url", $registLink);
		$this->tmpl->addVar("_widget", "regist_name", $registName);
		$this->tmpl->addVar("_widget", "current_url", $this->gEnv->getCurrentRequestUri());
		
		// スレッドが存在しないときは一覧を非表示にする
		if (!$this->isExistsMessage){
			$this->setGuidanceMsg('ﾒｯｾｰｼﾞが投稿されていません');
			
			$this->tmpl->setAttribute('message_list', 'visibility', 'hidden');
		}
			
		// ページ遷移用リンク
		$pageLink = '';
		if ($pageNo > 1){			// 前のページがあるとき
			//$foreLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_READ . '&page=' . ($pageNo -1), true));
			$foreLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile('task=' . self::TASK_READ . '&page=' . ($pageNo -1), false/*セッションID削除*/)));
			$pageLink .= '<a href="' . $foreLink . '" accesskey="1">前へ[1]</a>';
		}
		if ($this->isExistsNextPage){	// 次のページがあるとき
			if (!empty($pageLink)) $pageLink .= '&nbsp;&nbsp;';
			//$nextLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_READ . '&page=' . ($pageNo +1), true));
			$nextLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile('task=' . self::TASK_READ . '&page=' . ($pageNo +1), false/*セッションID削除*/)));
			$pageLink .= '<a href="' . $nextLink . '" accesskey="2">次へ[2]</a>';
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
		$mypageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId));
		$messageLink = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MESSAGE_ID . '=' . $messageId));
		
		// 名前作成
		$nameTag = '<a href="' . $mypageLink . '">' . $this->convertToDispString($memberName) . '</a>';
		
		// 日付作成
		$weekDay = array('日', '月', '火', '水', '木', '金', '土');
		$timestamp = strtotime($fetchedRow['mm_regist_dt']);
		$weekNo = intval(date('w', $timestamp));
		//$date = date('Y/m/d(' . $weekDay[$weekNo] . ') H:i:s', $timestamp);
		$date = date('m/d(' . $weekDay[$weekNo] . ') H:i:s', $timestamp);
		
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
//		$avatarTag = '<a href="' . $mypageLink . '">' . $avatarTag . '</a>';
		$row = array(
			'no'		=> $no,					// 項目番号
			'id'		=> $messageIdTag,		// メッセージID
			'message_count'	=> $messageCount,	// メッセージ数
			'name'		=> $nameTag,			// 会員名
			'date'		=> $date,			// 投稿日付
			'message'	=> $message,		// 投稿文
			'avatar'	=> $avatarTag,	// アバター画像
			'spacer'	=> $this->_spacer	// スペーサ
		);
		$this->tmpl->addVars('message_list', $row);
		$this->tmpl->parseTemplate('message_list', 'a');
		
		// メッセージが存在するかどうか
		$this->isExistsMessage = true;
		return true;
	}
}
?>
