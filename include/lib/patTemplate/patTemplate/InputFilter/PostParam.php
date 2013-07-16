<?PHP
/**
 * 追加POST値タグ変換(patTemplateフィルター)
 *
 * 機能：Maigc3用の管理画面のためのPOST値の追加
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: PostParam.php 4115 2011-05-07 10:48:07Z fishbone $
 * @link       http://www.magic3.org
 */
class patTemplate_InputFilter_PostParam extends patTemplate_InputFilter
{
   /**
    * filter name
	*
	* @access	protected
	* @abstract
	* @var	string
	*/
	var	$_name	=	'PostParam';

   /**
	* compress the data
	*
	* @access	public
	* @param	string		data
	* @return	string		data without whitespace
	*/
	function apply( $data )
	{
		// 変換部作成
		$msgTag  = '<input type="hidden" name="_pdefserial" value="{_DEF_SERIAL}" />' . M3_NL;
		$msgTag .= '<input type="hidden" name="_pdefconfig" value="{_DEF_CONFIG}" />' . M3_NL;
		$msgTag .= '<input type="hidden" name="_backurl" value="{_BACK_URL}" />' . M3_NL;

		// <!--m3:PostParam-->タグを一度だけ変換する
		$data = preg_replace('/<!--[ \t].*m3:PostParam[ \t].*-->/', $msgTag, $data, 1);
		return $data;
	}
}
?>