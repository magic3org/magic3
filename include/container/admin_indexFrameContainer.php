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
			//if ($gEnvManager->isSystemAdmin()){
			if ($this->gEnv->isSystemManageUser()) $ret = true;	// システム運用可能ユーザかどうか(2018/8/5変更)
		} else if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){		// ウィジェットの設定
			// ### trueを返すとウィジェット設定画面が表示され、falseを返すとログイン画面が表示される。                               ###
			// ### ログイン画面の場合、グローバルメッセージが設定されている場合はログイン画面の代わりにエラーメッセージが表示される。###
			$ret = false;			// アクセス不可に初期化
			if ($this->gEnv->isSystemAdmin()){		// システム管理者の場合
				$ret = true;
			} else if ($this->gEnv->isSystemManager($optionType)){				// システム運用者の場合
				$widgetId = $request->trimValueOf(M3_REQUEST_PARAM_WIDGET_ID);
				
				// ウィジェットが配置済みかどうかチェック。配置されていなければウィジェット設定画面へのアクセスは不可。
				$canAccess = $this->_db->canAccessWidget($widgetId);
				if ($canAccess){
					// パーソナルモードでの起動の場合は、ウィジェットがパーソナルモード対応かどうかチェック
					if ($this->gPage->isPersonalMode()){
						if ($this->_db->getWidgetInfo($widgetId, $row)){
							if ($row['wd_personal_mode']) $ret = true;			// パーソナルモード対応であればアクセス可能
						}
					} else {
						// パーソナルモードでなければすべてのウィジェット設定画面にアクセス可能
						$ret = true;
					}
				}

				// システム運用者でアクセス権がない場合はログイン画面の代わりにグローバルエラーメッセージ出力
				if (!$ret) $this->gInstance->getMessageManager()->addErrorMessage('アクセス権限がありません');
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
