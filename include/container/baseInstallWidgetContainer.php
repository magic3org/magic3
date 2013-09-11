<?php
/**
 * ウィジェットインストールコンテナ作成用ベースクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: baseInstallWidgetContainer.php 3187 2010-06-06 06:35:07Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class BaseInstallWidgetContainer extends BaseWidgetContainer
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
	 * 起動マネージャから呼ばれる唯一のメソッド
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param int $install					インストール種別(0=インストール、1=アンインストール、2=アップグレード)
	 * @return								なし
	 */
	function process($request, $install)
	{
		// 管理者権限がなければ実行できない
		if (!$this->gEnv->isSystemAdmin()) return;
		
		// スクリプト実行前処理
		if (method_exists($this, '_preScript')) $this->_preScript($request, $install);
		
		// スクリプト実行処理
		if (method_exists($this, '_doScript')){
			// 初期化フラグがオフの場合のみスクリプト処理を実行
			$widgetId = $this->gEnv->getCurrentWidgetId();
			$ret = $this->_db->getWidgetInfo($widgetId, $row);
			if ($ret){
				$version = $row['wd_version'];		// ウィジェットのバージョン
				if ($row['wd_initialized']){		// 初期化完了済み(再初期化禁止)
					$this->setMsg(self::MSG_GUIDANCE, 'データの再初期化が禁止されているため、スクリプトは実行できません');
				} else {
					// 実行するスクリプトファイルを取得
					$scriptFiles = $this->_doScript($request, $install, $version);
					for ($i = 0; $i < count($scriptFiles); $i++){
						$scriptPath = $this->gEnv->getCurrentWidgetSqlPath() . '/' . $scriptFiles[$i];

						// スクリプト実行
						if ($this->gInstance->getDbManager()->execScriptWithConvert($scriptPath, $errors)){// 正常終了の場合
							$this->setMsg(self::MSG_GUIDANCE, 'スクリプト実行完了しました(ファイル名=' . $scriptFiles[$i] . ')');
						} else {
							$this->setMsg(self::MSG_APP_ERR, 'スクリプト実行に失敗しました(ファイル名=' . $scriptFiles[$i] . ')');
						}
						if (!empty($errors)){
							foreach ($errors as $error) {
								$this->setMsg(self::MSG_APP_ERR, $error);
							}
						}
					}
				}
			}
		}
		
		// スクリプト実行後処理
		if (method_exists($this, '_postScript')) $this->_postScript($request, $install);
		
		// 出力メッセージをメッセージマネージャに設定
		$this->addMsgToGlobal();
	}
	/**
	 * メッセージテーブルにグローバルメッセージを追加する
	 *
	 * @return 				なし
	 */
	function addMsgToGlobal()
	{
		$this->gInstance->getMessageManager()->addMessage($this->errorMessage, $this->warningMessage, $this->guideMessage);
	}
}
?>
