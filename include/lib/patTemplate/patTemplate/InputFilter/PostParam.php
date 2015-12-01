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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
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
		global $gEnvManager;
		
		$paramTag = '';
		
		// 現在のウィジェットオブジェクトを取得
		$widgetObj = $gEnvManager->getCurrentWidgetObj();
		if (isset($widgetObj)){
			// 非表示INPUTタグ情報を取得
			$tagInfo = $widgetObj->_getHiddenTagInfo();
			if (!empty($tagInfo)){
				foreach($tagInfo as $name => $value){
					$paramTag .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />' . M3_NL;
				}
			}
		}
		
		if ($gEnvManager->isAdminDirAccess() && $gEnvManager->isSystemManageUser()){		// 管理画面へのアクセス、システム管理権限ありの場合
			// 変換部作成
			$paramTag .= '<input type="hidden" name="_pdefserial" value="{_DEF_SERIAL}" />' . M3_NL;
			$paramTag .= '<input type="hidden" name="_pdefconfig" value="{_DEF_CONFIG}" />' . M3_NL;
			$paramTag .= '<input type="hidden" name="_backurl" value="{_BACK_URL}" />' . M3_NL;
		}
		$paramTag .= '<input type="hidden" name="_formid" value="{_FORM_ID}" />' . M3_NL;
		
		// Firefoxの自動入力の問題を回避(2015/12/1)
		if (($gEnvManager->isAdminDirAccess() && $gEnvManager->isSystemManageUser()) ||		// 管理画面にログインしている場合
			(isset($widgetObj) && $widgetObj->getConfigMode())){								// ウィジェットが設定入力モードの場合(一般画面)
			$paramTag .= '<input type="text" name="_account_dummy" class="noeditcheck" style="display:none;" />' . M3_NL;
			$paramTag .= '<input type="password" name="_password_dummy" class="noeditcheck" style="display:none;" />' . M3_NL;
		}
		
		// <!--m3:PostParam-->タグを一度だけ変換する
		$data = preg_replace('/<!--[ \t].*m3:PostParam[ \t].*-->/', $paramTag, $data, 1);
		return $data;
	}
}
?>