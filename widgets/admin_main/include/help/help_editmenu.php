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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: help_editmenu.php 3894 2010-12-16 02:06:58Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()				. '/helpConv.php' );

class help_editmenu extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## 管理メニュー編集編集 ##########
		$helpData = array(
			'editmenu_config_others_btn' => array(	
				'title' =>	$this->_('Others Button'),	// その他ボタン
				'body' =>	$this->_('Configure others.')		// その他の設定を行います。
			),
			'editmenu_ret_btn' => array(	
				'title' =>	$this->_('Go back Button'),	// 戻るボタン
				'body' =>	$this->_('Go back to edit administration menu.')		// 管理メニュー編集へ戻ります。
			),
			'editmenu_top_image' => array(	
				'title' =>	$this->_('Image'),	// 画像
				'body' =>	$this->_('Image on administration menu page.')		// 管理メニュー上に表示する画像です。
			),
		);
		return $helpData;
	}
}
?>
