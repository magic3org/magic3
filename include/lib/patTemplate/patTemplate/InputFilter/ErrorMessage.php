<?PHP
/**
 * エラーメッセージタグ変換(patTemplateフィルター)
 *
 * 機能：Maigc3メッセージ出力タグを変換する
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
class patTemplate_InputFilter_ErrorMessage extends patTemplate_InputFilter
{
   /**
    * filter name
	*
	* @access	protected
	* @abstract
	* @var	string
	*/
	var	$_name	=	'ErrorMessage';

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
		$msgTag  = '<patTemplate:tmpl name="_messages" visibility="hidden">{PRE_TAG}<div class="m3messages{CLASS}"{ATTR}>' . M3_NL;
		$msgTag .= '<patTemplate:tmpl name="_danger_message" visibility="hidden">{PRE_TAG}<div class="danger-message{CLASS}"{ATTR}>{MESSAGE}</div>{POST_TAG}</patTemplate:tmpl>' . M3_NL;
		$msgTag .= '<patTemplate:tmpl name="_error_message" visibility="hidden">{PRE_TAG}<div class="error-message{CLASS}"{ATTR}>{MESSAGE}</div>{POST_TAG}</patTemplate:tmpl>' . M3_NL;
		$msgTag .= '<patTemplate:tmpl name="_warning_message" visibility="hidden">{PRE_TAG}<div class="warning-message{CLASS}"{ATTR}>{MESSAGE}</div>{POST_TAG}</patTemplate:tmpl>' . M3_NL;
		$msgTag .= '<patTemplate:tmpl name="_info_message" visibility="hidden">{PRE_TAG}<div class="info-message{CLASS}"{ATTR}>{MESSAGE}</div>{POST_TAG}</patTemplate:tmpl>' . M3_NL;
		$msgTag .= '<patTemplate:tmpl name="_guide_message" visibility="hidden">{PRE_TAG}<div class="guide-message{CLASS}"{ATTR}>{MESSAGE}</div>{POST_TAG}</patTemplate:tmpl>' . M3_NL;
		$msgTag .= '<patTemplate:tmpl name="_success_message" visibility="hidden">{PRE_TAG}<div class="success-message{CLASS}"{ATTR}>{MESSAGE}</div>{POST_TAG}</patTemplate:tmpl>' . M3_NL;
		$msgTag .= '</div>{POST_TAG}</patTemplate:tmpl>' . M3_NL;
		
		// <!--m3:ErrorMessage-->タグを一度だけ変換する
		$data = preg_replace('/<!--[ \t].*m3:ErrorMessage[ \t].*-->/', $msgTag, $data, 1);
		return $data;
	}
}
?>