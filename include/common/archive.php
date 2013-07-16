<?php
/**
 * アーカイブ処理クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: archive.php 1782 2009-04-22 06:54:03Z fishbone $
 * @link       http://www.magic3.org
 */
class Archive
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * アーカイブ
	 *
	 * @param string $srcPath				アーカイブファイルパス
	 * @param string $destDir				解凍先ディレクトリ
	 * @param string $type					アーカイブ処理タイプ
	 * @return bool							true=成功、false=失敗
	 */
	public function extract($srcPath, $destDir, $type = '')
	{
		// ファイル名の解析
	//	$pathParts = pathinfo($srcPath);
	//	$ext = strtolower($pathParts['extension']);		// 拡張子
	
		$ret = false;
		switch ($type){
			case 'zip':
				$archiver = $this->getArchiver($type);
				$ret = $archiver->extract($srcPath, $destDir);
				break;
			case 'tar':
				break;
			case 'tgz':
			case 'gz':
			case 'gzip':
				break;
		}
		return $ret;
	}
	/**
	 * 各種アーカイブ処理クラス取得
	 *
	 * @param string $type					アーカイブタイプ
	 * @return bool							true=成功、false=失敗
	 */
	private function getArchiver($type)
	{
		global $gEnvManager;
		static $archiver;

		if (!isset($archiver)) $archiver = array();

		if (!isset($archiver[$type])){
			// アーカイブ処理クラス取得
			$class = ucfirst($type) . 'Archiver';

			if (!class_exists($class)){
				//$path = dirname(__FILE__).DS.'archive'.$type).'.php';
				$path = $gEnvManager->getCommonPath() . '/archive/' . $type . 'Archiver.php';
				if (file_exists($path)){
					require_once($path);
				} else {
					return null;
				}
			}
			$archiver[$type] = new $class();
		}
		return $archiver[$type];
	}
}
?>
