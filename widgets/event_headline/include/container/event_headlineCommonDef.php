<?php
/**
 * index.php用共通定義クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
 
class event_headlineCommonDef
{
	// ##### 定義値 #####
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const DEFAULT_IMAGE_TYPE = '80c.jpg';		// デフォルトの画像タイプ
	
	// ##### デフォルト値 #####
	const DEFAULT_EVENT_ITEM_LAYOUT = '<div style="float:left;">[#IMAGE#]</div><div class="clearfix"><div>[#TITLE#]([#CT_DATE#] [#CT_TIME#])</div><div>[#CT_SUMMARY#]</div></div>';	// イベント項目レイアウト
}
?>
