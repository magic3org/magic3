<?php
/**
 * 汎用HTML編集クラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: htmlEdit.php 2 2007-11-03 04:59:01Z fishbone $
 * @link       http://www.magic3.org
 */
class HtmlEdit
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * セレクトメニュー作成
	 *
	 * @param SelectMenuItem $itemArray		メニューに表示する項目(SelectMenuItemクラスの配列)
	 * @param array $tagName				タグ名
	 * @param array $tagAttribs				selectタグの属性
	 * @return 								メニューのHTMLテキスト
	 */
	public static function createSelectMenu($itemArray, $tagName, $tagAttribs)
	{
		$html = "\n<select name=\"$tagName\" $tagAttribs>";
		$n = count($itemArray);
		for ($i = 0; $i < $n; $i++){
			$t = $itemArray[$i]->name;
			$k = $itemArray[$i]->value;

			$selected = '';
			if ($itemArray[$i]->selected){
				$selected = " selected";
			}
			$html .= "\n\t<option value=\"".$k."\"$selected>" . $t . "</option>";
		}
		$html .= "\n</select>\n";
		return $html;
	}
	/**
	 * 改行コードをbrタグに変換
	 *
	 * @param string $src			変換するデータ
	 * @return string				変換後データ
	 */
	public static function convLineBreakToBr($src)
	{
		return preg_replace("/(\015\012)|(\015)|(\012)/","<br />", $src);
	}
}
/**
 * セレクトメニュー項目クラス
 */
class SelectMenuItem
{
	public $name = '';			// 画面上に表示されるタイトル
	public $value = '';			// 実際の値
	public $selected = false;	// 選択状態
}
?>
