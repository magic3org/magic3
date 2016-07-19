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

class UserManager extends Core
{
	private $db;						// DBオブジェクト
	const WORK_DIR_EXPIRE_HOUR = 1;		// 作業ディレクトリ自動削除時間
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
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
	function removeSessionWorkDir()
	{
		// ディレクトリを取得
		$workDir = $this->gEnv->getUserTempDirBySession();
		
		if (file_exists($workDir)){
			rmDirectory($workDir);
			return true;
		} else {
			return false;
		}
	}
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
}
?>
