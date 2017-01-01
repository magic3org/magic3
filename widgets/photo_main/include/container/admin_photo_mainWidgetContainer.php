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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_photo_mainBaseWidgetContainer.php');

class admin_photo_mainWidgetContainer extends admin_photo_mainBaseWidgetContainer
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
		// 表示画面を決定
		$task = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_TASK);
		if (empty($task)) $task = self::DEFAULT_TASK;

		// ##### アクセス制御 #####
		if (self::$_isLimitedUser){		// 使用限定ユーザの場合
			switch ($task){
				case self::TASK_IMAGEBROWSE:		// 画像管理
				case self::TASK_IMAGEBROWSE_DETAIL:		// 画像管理(詳細)
				case self::TASK_IMAGEBROWSE_DIRECT:		// 画像取得
					// 画像ID、シリアル番号がある場合はアクセス権をチェック
					$photoId = $request->trimValueOf(M3_REQUEST_PARAM_PHOTO_ID);			// 画像ID
					if (!empty($photoId)){
						$ret = self::$_mainDb->getPhotoInfo($photoId, $this->gEnv->getCurrentLanguage(), $row);
						if ($ret){
							if ($row['ht_owner_id'] == $this->gEnv->getCurrentUserId()) break;
						}
						$this->SetMsg(self::MSG_APP_ERR, "アクセスできません");
						return true;
					}
					$serialNo = $request->trimValueOf(M3_REQUEST_PARAM_SERIAL_NO);			// シリアル番号
					if (!empty($serialNo)){
						$ret = self::$_mainDb->getPhotoInfoBySerial($serialNo, $row);
						if ($ret){
							if ($row['ht_owner_id'] == $this->gEnv->getCurrentUserId()) break;
						}
						$this->SetMsg(self::MSG_APP_ERR, "アクセスできません");
						return true;
					}
					break;
				default:
					$this->SetMsg(self::MSG_APP_ERR, "アクセスできません");
					return true;
			}
		}
		
		// コンテナを起動
		$goWidget = false;		// サブウィジェットを実行するかどうか
		switch ($task){
			case self::TASK_IMAGEBROWSE:		// 画像管理
			case self::TASK_IMAGEBROWSE_DETAIL:		// 画像管理(詳細)
			case self::TASK_IMAGEBROWSE_DIRECT:		// 画像取得
				$task = self::TASK_IMAGEBROWSE;
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case self::TASK_AUTHOER:				// 画像管理者管理
			case self::TASK_AUTHER_DETAIL:		// 画像管理者管理(詳細)
				$task = self::TASK_AUTHOER;
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case self::TASK_CATEGORY:			// カテゴリー
			case self::TASK_CATEGORY_DETAIL:		// カテゴリー(詳細)
				$task = self::TASK_CATEGORY;
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case self::TASK_COMMENT:		// 画像コメント管理
			case self::TASK_COMMENT_DETAIL:		// 画像コメント管理(詳細)
				$task = self::TASK_COMMENT;
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case self::TASK_SEARCH:		// 検索条件設定
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			case self::TASK_CONFIG:		// その他設定
				$goWidget = true;		// サブウィジェットを実行するかどうか
				break;
			default:
				break;
		}
		if ($goWidget){		// サブウィジェットを実行するかどうか
			$this->gLaunch->goSubWidget($task, true);		// 管理者機能で呼び出し
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
	}
}
?>
