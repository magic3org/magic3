<?php
/**
 * 汎用データチェッククラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: valueCheck.php 2 2007-11-03 04:59:01Z fishbone $
 * @link       http://www.magic3.org
 */

class ValueCheck
{
	/**
	 * 引数のデータが数値であるかチェック
	 *
	 * @param  $value		チェックするデータ(単一データまたは配列データ)
	 * @return bool			true=すべて数値、false=数値以外が存在する
	 */
	public static function isNumeric($value)
	{
		if (empty($value)) return false;
		
		if (is_array($value)){	// 配列のとき
			$count = count($value);
			for ($i = 0; $i < $count; $i++){
				if (!is_numeric($value[$i])){
					return false;
				}
			}
			return true;
		} else {
			if (is_numeric($value)){
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 * 引数のデータが整数であるかチェック
	 *
	 * @param  $value		チェックするデータ(単一データまたは配列データ)
	 * @return bool			true=すべて数値、false=数値以外が存在する
	 */
	public static function isInt($value)
	{
		if (empty($value)) return false;
		
		if (is_array($value)){	// 配列のとき
			$count = count($value);
			for ($i = 0; $i < $count; $i++){
				if (!is_int($value[$i])){
					return false;
				}
			}
			return true;
		} else {
			if (is_int($value)){
				return true;
			} else {
				return false;
			}
		}
	}	
}
?>
