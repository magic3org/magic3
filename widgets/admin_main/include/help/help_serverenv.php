<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_serverenv extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## サーバ環境 ##########
		$helpData = array(
			'serverenv_upload_filesize' => array(	
				'title' =>	$this->_('Allowed Upload File Size'),	// アップロード可能なファイルのサイズ
				'body' =>	$this->_('The parameters which related to upload file size in php.ini are configured memory_limit &gt;= post_max_size &gt;= upload_max_filesize.')		// php.iniの関係するパラメータをmemory_limit &gt;= post_max_size &gt;= upload_max_filesize に設定します。
			)
		);
		return $helpData;
	}
}
?>
