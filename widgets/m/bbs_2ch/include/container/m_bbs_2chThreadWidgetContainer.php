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
 * @version    SVN: $Id: m_bbs_2chThreadWidgetContainer.php 4038 2011-03-20 12:21:33Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_bbs_2chBaseWidgetContainer.php');

class m_bbs_2chThreadWidgetContainer extends m_bbs_2chBaseWidgetContainer
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
		$task = $request->trimValueOf('task');
		if ($task == self::TASK_NEW_THREAD){		// 新規スレッド作成画面
			return 'newthread.tmpl.html';
		} else {			// スレッド一覧画面
			return 'thread.tmpl.html';
		}
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
		$task = $request->trimValueOf('task');
		if ($task == self::TASK_NEW_THREAD){		// 新規スレッド作成画面
			return $this->createNewThread($request);
		} else {			// スレッド一覧画面
			return $this->createThreadList($request);
		}
	}
	/**
	 * 新規スレッド投稿画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createNewThread($request)
	{
		$act = $request->trimValueOf('act');
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		$subject = $request->mobileTrimValueOf('bbs_subject');// 投稿件名
		$message = $request->mobileTrimValueOf('bbs_message');// 投稿メッセージ
		$name = $request->mobileTrimValueOf('bbs_name');// 名前
		$email = $request->trimValueOf('bbs_email');// Eメールアドレス
		
		$isCompleted = false;		// 登録完了かどうか
		if ($act == 'add'){		// 新規追加のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 入力チェック
				$this->checkInput($subject, 'タイトル', 'ＥＲＲＯＲ：タイトルが存在しません！');
				if (function_exists('mb_strlen')){
					$length = mb_strlen($subject);
				} else {
					$length = strlen($subject);
				}
				if ($length > $this->_configArray[self::CF_SUBJECT_LENGTH]) $this->setUserErrorMsg('ＥＲＲＯＲ：タイトルが長すぎます！(最大文字数' . $this->_configArray[self::CF_SUBJECT_LENGTH] . ')');
			
				// その他の入力項目のエラーチェック
				$this->checkMessageInput($this->_boardId, -1/*スレッドIDチェックなし*/, $name, $email, $message);
			
				// エラーなしの場合は、データを更新
				if ($this->getMsgCount() == 0){
					// スレッドID作成
					$threadId = md5(time() . $this->gRequest->trimServerValueOf('REMOTE_ADDR'));
				
					// 新規スレッドの追加
					$ret = $this->_db->addNewThread($this->_boardId, $threadId, $subject, $name, $email, $message);

					if ($ret){		// データ追加成功のとき
						//$this->setMsg(self::MSG_GUIDANCE, 'スレッドを作成しました');
						$this->setMsg(self::MSG_GUIDANCE, '書きこみが終わりました。');
						$isCompleted = true;			// 投稿完了
					} else {
						//$this->setMsg(self::MSG_APP_ERR, 'スレッドを作成に失敗しました');
						$this->setMsg(self::MSG_APP_ERR, '書きこみに失敗しました。');
					}
				}
			} else {
				$this->setMsg(self::MSG_APP_ERR, '不正な投稿により、書きこみに失敗しました。');
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		}

		// 入力データを再設定
		if ($isCompleted){		// 投稿完了のとき
			$this->tmpl->setAttribute('result_area', 'visibility', 'visible');// 投稿エリアを表示
			$this->tmpl->addVar("result_area", "subject", $this->convertToDispString($subject));
			$this->tmpl->addVar("result_area", "message", $this->convertToDispString($message));
			$this->tmpl->addVar("result_area", "name", $this->convertToDispString($name));
			$this->tmpl->addVar("result_area", "email", $this->convertToDispString($email));
		} else {
			$this->tmpl->setAttribute('input_area', 'visibility', 'visible');// 投稿エリアを表示
			$this->tmpl->addVar("input_area", "subject", $this->convertToDispString($subject));
			$this->tmpl->addVar("input_area", "message", $this->convertToDispString($message));
		}
		
		$makeThreadColor = $this->_configArray[self::CF_MAKE_THREAD_COLOR];		// スレッド作成部背景色
		$makeThreadStyle .= 'background-color:' . $makeThreadColor . ';';
		$this->tmpl->addVar("_widget", "make_thread_style", $makeThreadStyle);
		
		$enctype = 'application/x-www-form-urlencoded';
		if (!empty($this->_configArray[self::CF_FILE_UPLOAD])){		// ファイルアップロード許可のとき
			$enctype = 'multipart/form-data';
			$this->tmpl->setAttribute('file_upload', 'visibility', 'visible');// ファイルアップロード領域追加
		}
		$this->tmpl->addVar("_widget", "enctype", $enctype);
		
		// ハッシュキー作成
		$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
		$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
		$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
		
		// 送信先
		$this->tmpl->addVar("_widget", "post_url", $this->_currentPageUrl);
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
		return true;
	}
}
?>
