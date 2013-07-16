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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: help_portal.php 4223 2011-07-08 03:56:33Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_portal extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## Magic3ポータル情報 ##########
		$helpData = array(
			'portal_portal_info' => array(	
				'title' =>	$this->_('Portal Information'),				// ポータル情報
				'body' =>	$this->_('Server information as portal.')		// ポータルサーバの情報です。
			),
			'portal_siteinfo' => array(	
				'title' =>	$this->_('Site Information'),					// サイト情報
				'body' =>	$this->_('Site information registered at Portal Server.')		// ポータルサーバへ登録するサイト情報です。
			),
			'portal_sitename' => array(
				'title' =>	$this->_('Site Name'),			// サイト名
				'body' =>	$this->_('Input site name on Site Information.')		// 「サイト情報」からサイトの名前を設定します。
			),
		);
		return $helpData;
	}
}
?>
