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
 * @version    SVN: $Id: admin_indexFrameContainer.php 4297 2011-09-06 03:00:32Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseFrameContainer.php');

class admin_indexFrameContainer extends BaseFrameContainer
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
	 * フレーム単位のアクセス制御
	 *
	 * 同フレーム(同.phpファイル)での共通のアクセス制御を行う
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function _checkAccess($request)
	{
		global $gEnvManager;
		global $gAccessManager;
		global $gPageManager;

		// 受け付けるコマンドを判断
		$ret = false;		// 戻り値リセット
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		if ($cmd == '' ||								// コマンドなし
			$cmd == M3_REQUEST_CMD_CHANGE_TEMPLATE ||	// テンプレート変更
			$cmd == M3_REQUEST_CMD_LOGIN ||				// ログイン
			$cmd == M3_REQUEST_CMD_LOGOUT){				// ログアウト
			$ret = true;
		} else if(	$cmd == M3_REQUEST_CMD_SHOW_POSITION ||		// 表示位置を表示するとき
					$cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET ||		// 表示位置を表示するとき(ウィジェット付き)
					$cmd == M3_REQUEST_CMD_SHOW_WIDGET ||		// ウィジェットの単体表示
					$cmd == M3_REQUEST_CMD_DO_WIDGET ||		// ウィジェット単体実行
					$cmd == M3_REQUEST_CMD_CONFIG_TEMPLATE ||		// テンプレートの設定
					$cmd == M3_REQUEST_CMD_GET_WIDGET_INFO ||	// ウィジェット各種情報取得(AJAX用)
					$cmd == M3_REQUEST_CMD_SHOW_PHPINFO){	// phpinfoの表示
			// 管理者権限がなければ実行できない
			if ($gEnvManager->isSystemAdmin()){
			//if ($this->gEnv->isSystemManageUser()){	// システム運用可能ユーザかどうか
				$ret = true;
			} else {
				// クッキーがないため権限を識別できない場合は、管理者キーをチェックする
				$ret = $gAccessManager->isValidAdminKey();
			}
		} else if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){		// ウィジェットの設定
			if ($this->gEnv->isSystemManageUser()){	// システム運用可能ユーザかどうか
				// システム運用者の場合はアクセス可能なウィジェットをチェック
				$widgetId = $request->trimValueOf(M3_REQUEST_PARAM_WIDGET_ID);
				$ret = $gEnvManager->canUseWidgetAdmin($widgetId);
			} else {
				// クッキーがないため権限を識別できない場合は、管理者キーをチェックする
				$ret = $gAccessManager->isValidAdminKey();
			}
		}

		// 管理機能アクセス可能なときはヘルプ出力する
		if ($ret) $gPageManager->setUseHelp(true);

		return $ret;
	}
	/**
	 * ビュー作成の前処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function _preBuffer($request)
	{
	}
	/**
	 * ビュー作成の後処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function _postBuffer($request)
	{
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return 								テンプレートを固定にしたい場合はテンプレート名を返す。
	 *										テンプレートが任意の場合(変更可能な場合)は空文字列を返す。
	 */
	function _setTemplate($request)
	{
		global $gSystemManager;
		
		// 受け付けるコマンドを判断
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		if ($cmd == M3_REQUEST_CMD_SHOW_POSITION ||		// 表示位置を表示するとき
			$cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// 表示位置を表示するとき(ウィジェット付き)
			return '';		// デフォルトのテンプレートを使用
		} else {
			return $gSystemManager->defaultAdminTemplateId();
		}
	}
}
?>
