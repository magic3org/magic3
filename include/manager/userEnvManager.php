<?php
/**
 * ユーザ環境マネージャー
 *
 * ユーザ単位で必要とする情報へのアクセスを管理
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class UserEnvManager extends Core
{
	private $widgetId;					// 処理対象ウィジェット
	const WORK_DIR_EXPIRE_HOUR = 1;		// 作業ディレクトリ自動削除時間
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * ユーザ環境マネージャの使用宣言(このマネージャーを使用する場合は必ず呼び出す)
	 *
	 * @return			なし
	 */
	function prepare()
	{
		// ##### 不使用な作業ディレクトリを削除 #####
		$this->cleanupAllSessionWorkDir();
		
		// ##### 現在のウィジェットを取得 #####
		$this->widgetId = $this->gEnv->getCurrentWidgetId();
		if (empty($this->widgetId)) $this->gLog->error(__METHOD__, 'ユーザ環境マネージャー: ウィジェットID取得失敗');
	}
	/**
	 * 現在のウィジェットに割り当てたユーザ環境データを初期化
	 *
	 * @return			なし
	 */
	function widgetOpen()
	{
		$this->_widgetReset();
	}
	/**
	 * 現在のウィジェットに割り当てたユーザ環境データを破棄
	 *
	 * @return			なし
	 */
	function widgetClose()
	{
		$this->_widgetReset();
	}
	/**
	 * 作業ディレクトリにアップロードしたファイルの情報を追加
	 *
	 * @param object $fileInfo	ファイル情報オブジェクト
	 * @return bool				true=追加成功、false=追加失敗
	 */
	function widgetAddFileInfo($fileInfo)
	{
		$sessionParamObj = $this->_getWidgetSessionObj();		// セッション保存パラメータ
		
		if (empty($sessionParamObj)){
			// セッションデータが存在しない場合は失敗
			return false;
		} else {
			// セッションパラメータを更新
			$sessionParamObj->fileInfoArray[] = $fileInfo;
			$this->_setWidgetSessionObj($sessionParamObj);
			return true;
		}
	}
	/**
	 * セッション単位の作業用ディレクトリ作成
	 *
	 * @param bool  $createDir	ディレクトリが存在しない場合、作成するかどうか
	 * @return string			作業ディレクトリパス
	 */
	function getSessionWorkDir($createDir = false)
	{
		// ディレクトリを作成
		$workDir = $this->gEnv->getUserTempDirBySession($createDir/*ディレクトリ作成*/);
		
		return $workDir;
	}
	/**
	 * セッション単位の作業用ディレクトリを削除
	 *
	 * @return bool			true=削除を実行、false=削除対象なし
	 */
/*	function removeSessionWorkDir()
	{
		// ディレクトリを取得
		$workDir = $this->gEnv->getUserTempDirBySession();
		
		if (file_exists($workDir)){
			rmDirectory($workDir);
			return true;
		} else {
			return false;
		}
	}*/
	/**
	 * すべてのユーザ用の作業用ディレクトリに対して、一定期間以上経過したディレクトリを削除
	 *
	 * @return			なし
	 */
	function cleanupAllSessionWorkDir()
	{
		$expireTime = self::WORK_DIR_EXPIRE_HOUR * 60 * 60;			// 作業ディレクトリ自動削除時間
		
		// 一般ユーザ用の作業ディレクトリを取得
		$usersDir = $this->gEnv->getUserWorkDirPath();
		
		// サブディレクトリの更新日時をチェック
		if (is_dir($usersDir)){
			$dir = dir($usersDir);
			while (($file = $dir->read()) !== false){
				$filePath = $usersDir . '/' . $file;
				// ディレクトリかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_dir($filePath)){
					if (time() - filemtime($filePath) >= $expireTime){
						rmDirectory($filePath);
					}
				}
			}
			$dir->close();
		}
	}
	/**
	 * ウィジェット専用セッション値(オブジェクト)を設定
	 *
	 * ウィジェット専用セッションは同一ウィジェット内でのみ使用するセッションである。
	 *
	 * @param object $paramObj	パラメータオブジェクト。nullをセットした場合は削除。
	 * @return なし
	 */
	function _setWidgetSessionObj($paramObj)
	{
		$keyName = M3_SESSION_USER_ENV_WIDGET . $this->widgetId;
		if (is_null($paramObj)){
			$this->gRequest->unsetSessionValue($keyName);
		} else {
			$this->gRequest->setSessionValue($keyName, serialize($paramObj));
		}
	}
	/**
	 * ウィジェット専用セッション値(オブジェクト)を取得
	 *
	 * ウィジェット専用セッションは同一ウィジェット内でのみ使用するセッションである。
	 *
	 * @return object		ウィジェットオブジェクト。取得できないときはnull。
	 */
	function _getWidgetSessionObj()
	{
		$keyName = M3_SESSION_USER_ENV_WIDGET . $this->widgetId;
		$serializedObj = $this->gRequest->getSessionValue($keyName);
		if (empty($serializedObj)){
			return null;
		} else {
			return unserialize($serializedObj);
		}
	}
	/**
	 * ウィジェット専用パラメータ初期化
	 *
	 * @return			なし
	 */
	function _widgetReset()
	{
		// 作業ディレクトリにファイルが残っている場合は削除
		// セッションパラメータ取得
		$sessionParamObj = $this->_getWidgetSessionObj();		// セッション保存パラメータ
		if (!empty($sessionParamObj)){
			$fileInfoArray = $sessionParamObj->fileInfoArray;
			for ($i = 0; $i < count($fileInfoArray); $i++){
				$filePath = $fileInfoArray[$i]['path'];
				// ファイル削除
				if (file_exists($filePath)) unlink($filePath);
			}
		}
		
		// セッションパラメータを更新
		$sessionParamObj = new stdClass;		
		$sessionParamObj->fileInfoArray = array();		// 作業ディレクトリのアップロードファイルの情報
		$this->_setWidgetSessionObj($sessionParamObj);
	}
}
?>
