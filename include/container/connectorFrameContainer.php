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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @link       http://magic3.org
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
			// ##### 自動起動処理(日次処理,月次処理)用のインターフェイス #####
			// アクセス元をチェック
			$senderIp = $this->gRequest->trimServerValueOf('REMOTE_ADDR');
			$serverIp = $this->gRequest->trimServerValueOf('SERVER_ADDR');
			if ($senderIp == $serverIp){		// アクセス元と自サーバが同じとき
				$ret = true;
			} else {
				// サーバの登録状況をチェック
				//$ret = $this->_db->isExistsTenantServerIp($senderIp);
				// ブラックリストのIPはアクセス不可にする
			}
		} else if ($cmd == M3_REQUEST_CMD_DO_WIDGET){		// ウィジェット単体実行
			// 管理者権限がなければ実行できない
			if ($this->gEnv->isSystemManageUser()) $ret = true;	// システム運用可能ユーザかどうか
		}
		return $ret;
	}
}
?>
