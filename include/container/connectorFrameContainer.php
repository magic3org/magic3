<?php
/**
 * connector.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: connectorFrameContainer.php 4297 2011-09-06 03:00:32Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseFrameContainer.php');

class connectorFrameContainer extends BaseFrameContainer
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
		// 受け付けるコマンドを判断
		$ret = false;		// 戻り値リセット
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		if (empty($cmd)){
			// アクセス元をチェック
			$senderIp = $this->gRequest->trimServerValueOf('REMOTE_ADDR');
			$serverIp = $this->gRequest->trimServerValueOf('SERVER_ADDR');
			if ($senderIp == $serverIp){		// アクセス元と自サーバが同じとき
				$ret = true;
			} else {
				// サーバの登録状況をチェック
				//$ret = $this->_db->isExistsTenantServerIp($senderIp);
				// ブラックリストのIPはアクセス不可にする
				$ret = true;
			}
		} else if ($cmd == M3_REQUEST_CMD_DO_WIDGET){		// ウィジェット単体実行
			// 管理者権限がなければ実行できない
			if ($this->gEnv->isSystemManageUser()){	// システム運用可能ユーザかどうか
				$ret = true;
			} else {
				// クッキーがないため権限を識別できない場合は、管理者キーをチェックする
				$ret = $this->gAccess->isValidAdminKey();
			}
		}
		return $ret;
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
		return '_admin';
	}
}
?>
