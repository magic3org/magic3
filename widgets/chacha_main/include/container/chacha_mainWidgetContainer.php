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
 * @version    SVN: $Id: chacha_mainWidgetContainer.php 3344 2010-07-06 11:27:55Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/chacha_mainBaseWidgetContainer.php');

class chacha_mainWidgetContainer extends chacha_mainBaseWidgetContainer
{
	const DEFAULT_TASK = 'top';				// デフォルトの画面
	
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
		// 実行処理を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)){
			// 会員ID,メッセージIDを取得
			$memberId = $request->trimValueOf(self::URL_PARAM_MEMBER_ID);		// 会員ID
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
		} else if ($task == self::TASK_NEW_THREAD){		// スレッド新規作成
			$task = self::TASK_THREAD;
		}

		// ##### コンテナを起動 #####
		switch ($task){
			case self::TASK_TOP:			// トップ画面
			case self::TASK_SUBJECT:		// スレッド件名
			case self::TASK_THREAD:		// スレッド処理
			case self::TASK_READ:		// スレッド表示
			case self::TASK_PROFILE:			// プロフィール表示
			case self::TASK_MYPAGE:			// マイページ表示
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
		return 'message.tmpl.html';
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

		// 要求画面によってテンプレートを変更
		switch ($task){
			case self::TASK_TOP:			// トップ画面
			default:
				// メッセージを表示
				//$this->tmpl->addVar("_widget", "login_url", $this->gEnv->createCurrentPageUrl() . '&task=login');		// ログイン用URL
				break;
		}
	}
}
?>
