<?php
/**
 * Joomla用定義ファイル
 *
 * 機能：Joomlaの定義を管理
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: mosDef.php 3974 2011-01-31 07:56:03Z fishbone $
 * @link       http://www.magic3.org
 */
// Joomla用定義
define( '_VALID_MOS', 1 );
DEFINE('_DATE_FORMAT_LC',"%A, %d %B %Y"); //Uses PHP's strftime Command Format
DEFINE('_ISO','charset=utf-8');

// Joomla用グローバル変数
global $gEnvManager;
$mosConfig_absolute_path = $gEnvManager->getJoomlaRootPath();
$mosConfig_live_site = $gEnvManager->getRootUrlByCurrentPage();
$mosConfig_sitename = $gEnvManager->getSiteName();// サイト名称
$mosConfig_favicon = 'favicon.ico';
$mosConfig_sef = '0';
$cur_template = $gEnvManager->getCurrentTemplateId();

// グローバルオブジェクト
class mosMainFrame {
	function getTemplate(){
		global $cur_template;
		return $cur_template;
	}
	// ##### 以下、Joomla!v1.5用に追加 #####
	function getMessageQueue()
	{
		return array();
	}
	function getCfg($name)
	{
		global $gEnvManager;
		global $gPageManager;
		
		$value = '';
		switch ($name){
			case 'sitename':
				$value = $gEnvManager->getSiteName();// サイト名称
				break;
/*			case 'MetaDesc':
				$value = $gPageManager->getHeadDescription();		// サイトの説明
				break;
			case 'MetaRights':
				$value = $gEnvManager->getSiteCopyRight();		// 著作権
				break;
			case 'MetaKeys':
				$value = $gPageManager->getHeadKeywords();		// キーワード
				break;
				*/
		}
		return $value;
	}
}
$mainframe = new mosMainFrame();
?>
