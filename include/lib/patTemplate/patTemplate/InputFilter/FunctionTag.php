<?PHP
/**
 * 機能付きタグ変換(patTemplateフィルター)
 *
 * 機能：機能付きのタグを変換する
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2007 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: FunctionTag.php 2 2007-11-03 04:59:01Z fishbone $
 * @link       http://www.magic3.org
 */
class patTemplate_InputFilter_FunctionTag extends patTemplate_InputFilter
{
   /**
    * filter name
	*
	* @access	protected
	* @abstract
	* @var	string
	*/
	var	$_name	=	'FunctionTag';

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
		$msgTag_ImageTooltip  = '<div id="image_tooltip" style="display:none;">' . M3_NL;
		$msgTag_ImageTooltip .= '<img id="image_tooltip_i" />' . M3_NL;
		$msgTag_ImageTooltip .= '</div>' . M3_NL;
		
		// <!--m3:ImageTooltip-->タグを一度だけ変換する
		$data = preg_replace('/<!--[ \t].*m3:ImageTooltip[ \t].*-->/', $msgTag_ImageTooltip, $data, 1);
		return $data;
	}
}
?>