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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: commentWidgetContainer.php 6000 2013-05-14 12:28:33Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/commentBaseWidgetContainer.php');

class commentWidgetContainer extends commentBaseWidgetContainer
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// ウィジェットオブジェクト取得
		self::$_paramObj = $this->getWidgetParamObj();
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
		if (empty($task)) $task = self::DEFAULT_TASK;
		$blogId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ID);		// 所属ブログ
		$serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号

		// ##### アクセス制御 #####
		// 管理ウィンドウのときはブログIDが必須
		// 管理ウィンドウはマルチブログ時のみ使用
		switch ($task){
			case self::TASK_ENTRY:					// 記事編集画面(別ウィンドウ)
			case self::TASK_ENTRY_DETAIL:			// 記事編集画面詳細
			case self::TASK_COMMENT:		// ブログ記事コメント管理
			case self::TASK_COMMENT_DETAIL:		// ブログ記事コメント管理(詳細)
				if (empty($blogId)){	// ブログIDが指定されていない場合
					$this->SetMsg(self::MSG_APP_ERR, "アクセスできません");
					return true;
				}
				break;
		}
		self::$_canEditEntry = false;		// 記事編集権限
		
		// 設定値を取得
		if ($this->gEnv->isSystemManageUser()){			// システム運用可能ユーザのとき
			self::$_canEditEntry = true;		// 記事編集権限
		} else if ($this->gEnv->getCurrentUserType() >= UserInfo::USER_TYPE_AUTHOR){		// ユーザ権限(投稿者以上)のチェック
			// ブログライブラリオブジェクトからテンプレートを取得
			$blogId = '';
			$blogLibObj = $this->gInstance->getObject(self::BLOG_OBJ_ID);
			if (isset($blogLibObj)) $blogId = $blogLibObj->getBlogId();

			// ブログの所有者のときは編集権限あり
			$ret = self::$_mainDb->getBlogInfoById($blogId, $row);
			if ($ret && $this->gEnv->getCurrentUserId() == $row['bl_owner_id']){
				// シリアル番号が指定されている場合は編集権限をチェック
				if (empty($serialNo)){
					self::$_canEditEntry = true;		// 記事編集権限
				} else {
					switch ($task){
						case self::TASK_ENTRY:					// 記事編集画面(別ウィンドウ)
						case self::TASK_ENTRY_DETAIL:			// 記事編集画面詳細
							$ret = self::$_mainDb->isExistsEntryInBlogId($serialNo, $blogId);
							if ($ret) self::$_canEditEntry = true;		// 記事編集権限
							break;
						case self::TASK_COMMENT:		// ブログ記事コメント管理
						case self::TASK_COMMENT_DETAIL:		// ブログ記事コメント管理(詳細)
							$ret = self::$_mainDb->isExistsCommentInBlogId($serialNo, $blogId);
							if ($ret) self::$_canEditEntry = true;		// 記事編集権限
							break;
					}
				}
			}
		}

		// ##### コンテナを起動 #####
		$goWidget = false;		// サブウィジェットを実行するかどうか
		switch ($task){
			case self::TASK_TOP:			// トップ画面
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;				
			case self::TASK_ENTRY:					// 記事編集画面(別ウィンドウ)
			case self::TASK_ENTRY_DETAIL:			// 記事編集画面詳細
				if (self::$_canEditEntry){	// 記事が編集可能かどうか
					$task = self::TASK_ENTRY;
					$goWidget = true;		// サブウィジェットを実行するかどうか				
				} else {
					$this->SetMsg(self::MSG_APP_ERR, "アクセスできません");
					return true;
				}
				break;
			case self::TASK_COMMENT:		// ブログ記事コメント管理
			case self::TASK_COMMENT_DETAIL:		// ブログ記事コメント管理(詳細)
				if (self::$_canEditEntry){	// 記事が編集可能かどうか
					$task = self::TASK_COMMENT;
					$goWidget = true;		// サブウィジェットを実行するかどうか				
				} else {
					$this->SetMsg(self::MSG_APP_ERR, "アクセスできません");
					return true;
				}
				break;
		}
		if ($goWidget){		// サブウィジェットを実行するかどうか
			$this->gLaunch->goSubWidget($task);		// 一般機能で呼び出し
			return false;
		} else {
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
				break;
		}
	}
}
?>
