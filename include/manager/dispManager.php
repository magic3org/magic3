<?php
/**
 * 画面制御マネージャー
 *
 *  クライアントに依存する表示設定を管理
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: dispManager.php 3008 2010-04-07 11:06:15Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');		// Magic3コアクラス

class DispManager extends Core
{
	private $adminParam;		// 管理者用パラメータ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->adminParam = new stdClass;
		$this->adminParam->config = array();
	}
	/**
	 * 画面設定取得
	 *
	 * @return 		なし
	 */
	public function load()
	{
		// 管理者のときは、管理者用の画面設定を読み込み
		if ($this->gEnv->isSystemAdmin()){
			$value = $this->gRequest->getCookieValue(M3_COOKIE_DISP_ID);
			if (!empty($value)){
				$this->adminParam = unserialize($value);
			}
			
			// 管理者名取得
			$this->adminParam->userName = $this->gEnv->getCurrentUserName();
		} else {
		}
	}
	/**
	 * 画面設定保存
	 *
	 * @return 		なし
	 */
	public function save()
	{
		// 管理者のときは、管理者用の画面設定を読み込み
		if ($this->gEnv->isSystemAdmin()){
			if (!empty($this->adminParam)){
				$this->gRequest->setCookieValue(M3_COOKIE_DISP_ID, serialize($this->adminParam));
			}
		} else {
		}
	}
	/**
	 * 管理者用の画面設定を取得
	 *
	 * @param string $key		設定名
	 * @return string			設定値
	 */
	public function getAdminConfig($key)
	{
		$key = strval($key);
		return isset($this->adminParam->config[$key]) ? $this->adminParam->config[$key] : '';
	}
	/**
	 * 管理者用の画面設定を更新
	 *
	 * @param string $key		設定名
	 * @param string $value		値
	 * @return なし
	 */
	public function setAdminConfig($key, $value)
	{
		$this->adminParam->config[strval($key)] = strval($value);
	}
}
?>
