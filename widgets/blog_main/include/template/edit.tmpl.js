/**
 * Javascript作成用テンプレート(patTemplate)
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
<patTemplate:tmpl name="_tmpl">
<patTemplate:tmpl name="admin_script" visibility="hidden">
function showConfig(){
	m3ShowStandardWindow("{CONFIG_URL}");
	return false;
}
function editEntry(serial){
	m3ShowStandardWindow("{EDIT_URL}&serial=" + serial);
	return false;
}
</patTemplate:tmpl>
<patTemplate:tmpl name="edit_script" visibility="hidden">
function editEntry(serial){
	m3_showConfigWindow("{EDIT_URL}&serial=" + serial);
	return false;
}
</patTemplate:tmpl>
</patTemplate:tmpl>
