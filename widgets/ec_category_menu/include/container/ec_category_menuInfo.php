<?php
/**
 * カテゴリーメニュー設定クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ec_category_menuInfo.php 2 2007-11-03 04:59:01Z fishbone $
 * @link       http://www.magic3.org
 */
class ec_category_menuInfo
{
	public $imageFilename;				// 画像ファイル名
	public $levelCount;					// メニュー表示階層
	public $fontColor1;					// フォントカラー1
	public $fontColor2;					// フォントカラー2
	public $fontColor3;					// フォントカラー3
	public $fontColor4;					// フォントカラー4
	public $useImageMenu;				// 画像メニューを使うかどうか
	public $title;			// メニュータイトル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
}
?>
