<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_chachaWidgetContainer.php 3356 2010-07-08 13:40:58Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_chachaBaseWidgetContainer.php');

class m_chachaWidgetContainer extends m_chachaBaseWidgetContainer
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
	 * ディスパッチ処理(メインコンテナのみ実行)
	 *
     * HTTPリクエストの内容を見て処理をコンテナに振り分ける
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return bool 						このクラスの_setTemplate(), _assign()へ処理を継続するかどうかを返す。
	 *                                      true=処理を継続、false=処理を終了
	 */
	function _dispatch($request, &$param)
	{
		$memberId = $request->trimValueOf(self::URL_PARAM_MEMBER_ID);	// 会員ID
			
		// 実行処理を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)){
			// 会員IDを取得
			$memberId = $request->trimValueOf(self::URL_PARAM_MEMBER_ID);
			$messageId = $request->trimValueOf(self::URL_PARAM_MESSAGE_ID);		// メッセージID
			
			// 会員IDが設定されているときはマイページを表示
			if (empty($memberId)){
				if (empty($messageId)){
					$task = self::DEFAULT_TASK;
				} else {
					$task = self::TASK_THREAD;		// スレッド表示
				}
			} else {
				$task = self::TASK_MYPAGE;		// マイページ表示
			}
		}

		// アクセス権をチェック
		switch ($task){
			case self::TASK_THREAD:		// スレッド処理
			case self::TASK_READ:			// スレッド一覧画面
			case self::TASK_PROFILE:			// プロフィール入力画面
			case self::TASK_MYPAGE:			// マイページ画面
				// 携帯以外からのアクセスの場合はエラー
				if (empty($this->_mobileId)) return true;
				break;
		}

		// ユーザ登録されていない場合はユーザ登録へ
		if ($task == self::TASK_MYPAGE && empty($memberId)){			// マイページ画面
			$db = new chachaDb();		// DBオブジェクト作成
			$ret = $db->getMemberInfoByDeviceId($this->_mobileId, $row);
			if (!$ret){
				$nextPage = $this->gEnv->createCurrentPageUrlForMobile('task=' . self::TASK_PROFILE);
				$this->gPage->redirect($nextPage, true/*遷移時のダイアログ表示を抑止*/);
				return true;
			}
		}
		
		// ##### コンテナを起動 #####
		switch ($task){
			case self::TASK_TOP:			// トップ画面
			case self::TASK_THREAD:		// スレッド処理
			case self::TASK_READ:			// スレッド一覧画面
			case self::TASK_PROFILE:			// プロフィール入力画面
			case self::TASK_MYPAGE:			// マイページ画面
				$this->gLaunch->goSubWidget($task);
				return false;
			default:
				$this->SetMsg(self::MSG_APP_ERR, "画面が見つかりません");
				return true;
		}
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
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);

		// 要求画面によってテンプレートを変更
		switch ($task){
			default:
				return 'message.tmpl.html';
		}
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;

		// 要求画面によってテンプレートを変更
		switch ($task){
			case self::TASK_THREAD:		// スレッド処理
			case self::TASK_READ:			// スレッド一覧画面
			case self::TASK_PROFILE:			// プロフィール入力画面
			case self::TASK_MYPAGE:			// マイページ画面
				$this->setUserErrorMsg('携帯電話以外からは実行できません');
				$this->tmpl->addVar('_widget', 'top_url', $this->gEnv->createCurrentPageUrlForMobile(''));
				break;
			default:
				break;
		}
	}
}
?>
