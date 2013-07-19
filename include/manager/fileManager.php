<?php
/**
 * ファイル管理マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: fileManager.php 6156 2013-07-02 00:03:50Z fishbone $
 * @link       http://www.magic3.org
 */
class FileManager extends Core
{
	private $db;						// DBオブジェクト
		
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
	 * インストーラを退避
	 *
	 * @return bool				true=成功、false=失敗
	 */
	public function backupInstaller()
	{
		global $gEnvManager;
		
		// システムルート取得
		$sytemRoot = $gEnvManager->getSystemRootPath();
		
		$filename = $sytemRoot . '/admin/install.php';
		$backupFilename = $filename . '_backup';
		return rename($filename, $backupFilename);
	}
	/**
	 * インストーラを回復
	 *
	 * @return bool				true=成功、false=失敗
	 */
	public function recoverInstaller()
	{
		global $gEnvManager;
		
		// システムルート取得
		$sytemRoot = $gEnvManager->getSystemRootPath();
		
		$oldFilename = $sytemRoot . '/admin/install.php_backup';
		$newFilename = $sytemRoot . '/admin/install.php';
		
		// ファイルの存在チェック
		if (file_exists($newFilename)){
			return true;
		} else {
			return rename($oldFilename, $newFilename);
		}
	}
	/**
	 * インストーラファイルパスを取得
	 *
	 * @return string		ファイルパス
	 */
	public function getInstallerPath()
	{
		global $gEnvManager;
		
		// システムルート取得
		$sytemRoot = $gEnvManager->getSystemRootPath();
		
		$newFilename = $sytemRoot . '/admin/install.php';
		return $newFilename;
	}
	/**
	 * 添付ファイル情報を新規追加
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $fileId		ファイル識別ID(コンテンツタイプでユニーク)
	 * @param string $filePath		ファイルパス
	 * @param string $originalFilename	元のファイル名
	 * @param string $originalUrl		取得元のURL
	 * @return bool					true=成功、false=失敗
	 */
	public function addAttachFileInfo($contentType, $fileId, $filePath, $originalFilename, $originalUrl = '')
	{
		$ret = $this->db->addAttachFileInfo($contentType, $fileId, $filePath, $originalFilename, $originalUrl);
		return $ret;
	}
	/**
	 * 添付ファイル情報を更新
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @param int $oldContentSerial	旧コンテンツシリアル番号
	 * @param int $contentSerial	コンテンツシリアル番号
	 * @param array $fileInfo		ファイル情報
	 * @param string $dir			ファイル格納ディレクトリ
	 * @return bool					true=成功、false=失敗
	 */
	public function updateAttachFileInfo($contentType, $contentId, $oldContentSerial, $contentSerial, $fileInfo, $dir)
	{
		$ret = $this->db->updateAttachFileInfo($contentType, $contentId, $oldContentSerial, $contentSerial, $fileInfo, $dir);
		return $ret;
	}
	/**
	 * 添付ファイル情報を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param int $contentSerial	コンテンツシリアル番号
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	public function getAttachFileInfo($contentType, $contentSerial, &$rows)
	{
		$ret = $this->db->getAttachFileInfo($contentType, $contentSerial, $rows);
		return $ret;
	}
	/**
	 * ファイルIDから添付ファイル情報を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $fileId		ファイルID
	 * @param array  $row			レコード
	 * @param bool $assignedOnly	本登録済みファイルのみかどうか
	 * @return bool					取得あり = true, 取得なし= false
	 */
	public function getAttachFileInfoByFileId($contentType, $fileId, &$row, $assignedOnly = true)
	{
		$ret = $this->db->getAttachFileInfoByFileId($contentType, $fileId, $row, $assignedOnly);
		return $ret;
	}
	/**
	 * クライアントIDで仮登録の添付ファイル情報を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $clientId		クライアントID
	 * @param array  $rows			レコード
	 * @return bool					1行以上取得 = true, 取得なし= false
	 */
	public function getAttachFileInfoByClientId($contentType, $clientId, &$rows)
	{
		$ret = $this->db->getAttachFileInfoByClientId($contentType, $clientId, $rows);
		return $ret;
	}
	/**
	 * 添付ファイル情報を削除
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param int $contentSerial	コンテンツシリアル番号
	 * @param string $dir			ファイル格納ディレクトリ
	 * @return bool					true=成功、false=失敗
	 */
	public function delAttachFileInfo($contentType, $contentSerial, $dir)
	{
		$ret = $this->db->delAttachFileInfo($contentType, $contentSerial, $dir);
		return $ret;
	}
	/**
	 * 添付ファイル情報をコンテンツIDで削除
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @param string $dir			ファイル格納ディレクトリ
	 * @return bool					true=成功、false=失敗
	 */
	public function delAttachFileInfoByContentId($contentType, $contentId, $dir)
	{
		$ret = $this->db->delAttachFileInfoByContentId($contentType, $contentId, $dir);
		return $ret;
	}
	/**
	 * 仮登録の添付ファイル情報を削除
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $dir			ファイル格納ディレクトリ
	 * @param array  $fileIdArray	削除対象のファイルID
	 * @return bool					true=正常終了、false=異常終了
	 */
	public function cleanAttachFileInfo($contentType, $dir, $fileIdArray = null)
	{
		$ret = $this->db->cleanAttachFileInfo($contentType, $dir, $fileIdArray);
		return $ret;
	}
	/**
	 * ランダムなファイルIDを生成
	 *
	 * @return string		ファイルID
	 */
	public function createRandFileId()
	{
		global $gEnvManager;
		global $gAccessManager;
		
		return md5($gEnvManager->getRootUrl() . '-' . $gAccessManager->getAccessLogSerialNo() . '-' . rand());
	}
}
?>
