<?php
/**
 * backupプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version

// Prohibit rendering old wiki texts (suppresses load, transfer rate, and security risk)
define('PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING', PKWK_SAFE_MODE || PKWK_OPTIMISE);

function plugin_backup_action()
{
	global $do_backup, $hr;
	global $_msg_backuplist, $_msg_diff, $_msg_nowdiff, $_msg_source, $_msg_backup;
	global $_msg_view, $_msg_goto, $_msg_deleted;
	global $_title_backupdiff, $_title_backupnowdiff, $_title_backupsource;
	global $_title_backup, $_title_pagebackuplist, $_title_backuplist;

	// ### パスワード認証フォーム表示 ###
	// 認証されている場合はスルーして関数以降を実行
	$retStatus = password_form();
	if (!empty($retStatus)) return $retStatus;
	
	if (! $do_backup) return;

	$page = WikiParam::getPage();
	if ($page == '') return array('msg'=>$_title_backuplist, 'body'=>plugin_backup_get_list_all());

	check_readable($page, true, true);
	$s_page = htmlspecialchars($page);
	$r_page = rawurlencode($page);

	$action = WikiParam::getVar('action');
	if ($action == 'delete') return plugin_backup_delete($page);

	$s_action = $r_action = '';
	if ($action != '') {
		$s_action = htmlspecialchars($action);
		$r_action = rawurlencode($action);
	}

	$value = WikiParam::getVar('age');
	$s_age  = ($value == '') ? 0 : intval($value);
	if ($s_age <= 0) return array( 'msg'=>$_title_pagebackuplist, 'body'=>plugin_backup_get_list($page));

	$script = get_script_uri();

	$body  = '<ul>' . "\n";
	//$body .= ' <li><a href="' . $script . '?cmd=backup">' . $_msg_backuplist . '</a></li>' ."\n";
	$body .= ' <li><a href="' . $script . WikiParam::convQuery('?cmd=backup') . '">' . $_msg_backuplist . '</a></li>' ."\n";

	//$href    = $script . '?cmd=backup&amp;page=' . $r_page . '&amp;age=' . $s_age;
	$href    = $script . WikiParam::convQuery('?cmd=backup&amp;page=' . $r_page . '&amp;age=' . $s_age);
	$is_page = WikiPage::isPage($page);

	if ($is_page && $action != 'diff')
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=diff">' . $_msg_diff . '</a>',
			$_msg_view) . '</li>' . "\n";

	if ($is_page && $action != 'nowdiff')
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=nowdiff">' . $_msg_nowdiff . '</a>',
			$_msg_view) . '</li>' . "\n";

	if ($action != 'source')
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'&amp;action=source">' . $_msg_source . '</a>',
			$_msg_view) . '</li>' . "\n";

	if (! PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING && $action)
		$body .= ' <li>' . str_replace('$1', '<a href="' . $href .
			'">' . $_msg_backup . '</a>',
			$_msg_view) . '</li>' . "\n";

	if ($is_page) {
		//$body .= ' <li>' . str_replace('$1', '<a href="' . $script . '?' . $r_page . '">' . $s_page . '</a>', $_msg_goto) . "\n";
		$body .= ' <li>' . str_replace('$1', '<a href="' . $script . WikiParam::convQuery('?' . $r_page) . '">' . $s_page . '</a>', $_msg_goto) . "\n";
	} else {
		$body .= ' <li>' . str_replace('$1', $s_page, $_msg_deleted) . "\n";
	}

	//$backups = get_backup($page);
	$backups = WikiPage::getPageBackupInfo($page);
	$backups_count = count($backups);
	if ($s_age > $backups_count) $s_age = $backups_count;

	if ($backups_count > 0) {
		//$body .= '  <ul>' . "\n";
		$body .= '  <ul class="wiki_list">' . "\n";
		foreach($backups as $age => $val) {
			$date = format_date($val['time'], TRUE);
			/*$body .= ($age == $s_age) ?
				'   <li><em>' . $age . ' ' . $date . '</em></li>' . "\n" :
				'   <li><a href="' . $script . '?cmd=backup&amp;action=' . $r_action . '&amp;page=' . $r_page . '&amp;age=' . $age . '">' . $age . ' ' . $date . '</a></li>' . "\n";*/
			$body .= ($age == $s_age) ?
				'   <li><em>' . $age . ' ' . $date . '</em></li>' . "\n" :
				'   <li><a href="' . $script . WikiParam::convQuery('?cmd=backup&amp;action=' . $r_action . '&amp;page=' . $r_page . '&amp;age=' . $age) . '">' . $age . ' ' . $date . '</a></li>' . "\n";
		}
		$body .= '  </ul>' . "\n";
	}
	$body .= ' </li>' . "\n";
	$body .= '</ul>'  . "\n";

	if ($action == 'diff') {
		$title = $_title_backupdiff;
		/*$old = ($s_age > 1) ? join('', $backups[$s_age - 1]['data']) : '';
		$cur = join('', $backups[$s_age]['data']);*/
		$old = ($s_age > 1) ? WikiPage::getPageBackup($page, $s_age - 1, true) : '';
		$cur = WikiPage::getPageBackup($page, $s_age, true);
		$body .= plugin_backup_diff(do_diff($old, $cur));
	} else if ($s_action == 'nowdiff') {
		$title = $_title_backupnowdiff;
		//$old = join('', $backups[$s_age]['data']);
		$old = WikiPage::getPageBackup($page, $s_age, true);
		$cur = join('', get_source($page));
		$body .= plugin_backup_diff(do_diff($old, $cur));
	} else if ($s_action == 'source') {
		$title = $_title_backupsource;
		//$body .= '<pre>' . htmlspecialchars(join('', $backups[$s_age]['data'])) . '</pre>' . "\n";
		$body .= '<pre class="wiki_pre">' . htmlspecialchars(WikiPage::getPageBackup($page, $s_age, true)) . '</pre>' . "\n";
	} else {
		if (PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING) {
			die_message('This feature is prohibited');
		} else {
			$title = $_title_backup;
			//$body .= $hr . "\n" . drop_submit(convert_html($backups[$s_age]['data']));
			$body .= $hr . "\n" . drop_submit(convert_html(WikiPage::getPageBackup($page, $s_age)));
		}
	}
	return array('msg'=>str_replace('$2', $s_age, $title), 'body'=>$body);
}

// Delete backup
function plugin_backup_delete($page)
{
	global $_title_backup_delete, $_title_pagebackuplist, $_msg_backup_deleted;
	global $_msg_backup_adminpass, $_btn_delete, $_msg_invalidpass;
	global $dummy_password;
	global $gEnvManager;
	
	//if (! _backup_file_exists($page))
	if (!WikiPage::isPageBackup($page)) return array('msg'=>$_title_pagebackuplist, 'body'=>plugin_backup_get_list($page)); // Say "is not found"

	$body = '';
	$pass = WikiParam::getVar('pass');
	if ($pass != ''){
		if (pkwk_login($pass)){
			_backup_delete($page);
			return array(
				'msg'  => $_title_backup_delete,
				'body' => str_replace('$1', make_pagelink($page), $_msg_backup_deleted)
			);
		} else {
			$body = '<p><strong>' . $_msg_invalidpass . '</strong></p>' . "\n";
		}
	}

	$postScript = get_script_uri() . WikiParam::convQuery("?");
	$s_page = htmlspecialchars($page);
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
//		$body .= '<p>' . $_msg_backup_adminpass . '</p>' . M3_NL;
		$body .= '<form action="' . $postScript . '" method="post" class="form form-inline" role="form">' . M3_NL;
		$body .= '<input type="hidden"   name="wcmd"    value="backup" />' . M3_NL;
		$body .= '<input type="hidden"   name="page"   value="' . $s_page . '" />' . M3_NL;
		$body .= '<input type="hidden"   name="action" value="delete" />' . M3_NL;
		$body .= '<input type="hidden"   name="pass" />' . M3_NL;
		$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
//		$body .= '<div class="form-group"><input type="password" class="form-control" name="password" size="12" /></div>' . M3_NL;
		$body .= '<input type="submit"   name="ok"     class="button btn" value="' . $_btn_delete . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
		$body .= '</form>' . M3_NL;
	} else {
//		$body .= '<p>' . $_msg_backup_adminpass . '</p>' . M3_NL;
		$body .= '<form action="' . $postScript . '" method="post" class="form">' . M3_NL;
		$body .= '<div>' . M3_NL;
		$body .= '<input type="hidden"   name="wcmd"    value="backup" />' . M3_NL;
		$body .= '<input type="hidden"   name="page"   value="' . $s_page . '" />' . M3_NL;
		$body .= '<input type="hidden"   name="action" value="delete" />' . M3_NL;
		$body .= '<input type="hidden"   name="pass" />' . M3_NL;
		$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
//		$body .= '<input type="password" name="password" size="12" />' . M3_NL;
		$body .= '<input type="submit"   name="ok"     class="button" value="' . $_btn_delete . '" onclick="this.form.pass.value = hex_md5(this.form.password.value);" />' . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '</form>' . M3_NL;
	}
	return	array('msg'=>$_title_backup_delete, 'body'=>$body);
}

function plugin_backup_diff($str)
{
	global $_msg_addline, $_msg_delline, $hr;
	$ul = <<<EOD
$hr
<ul>
 <li>$_msg_addline</li>
 <li>$_msg_delline</li>
</ul>
EOD;

	return $ul . '<pre class="wiki_pre">' . diff_style_to_css(htmlspecialchars($str)) . '</pre>' . "\n";
	//return $ul . '<pre>' . diff_style_to_css(htmlspecialchars($str)) . '</pre>' . "\n";
}

function plugin_backup_get_list($page)
{
	global $_msg_backuplist, $_msg_diff, $_msg_nowdiff, $_msg_source, $_msg_nobackup;
	global $_title_backup_delete;

	$script = get_script_uri();
	$postScript = $script . WikiParam::convQuery('?cmd=backup');
	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);
	$retval = array();
	$retval[0] = <<<EOD
<ul class="wiki_list">
 <li><a href="$postScript">$_msg_backuplist</a>
  <ul>
EOD;
	$retval[1] = "\n";
	$retval[2] = <<<EOD
  </ul>
 </li>
</ul>
EOD;
	
	$backups = WikiPage::isPageBackup($page) ? WikiPage::getPageBackupInfo($page) : array();
	if (empty($backups)) {
		$msg = str_replace('$1', make_pagelink($page), $_msg_nobackup);
		$retval[1] .= '   <li>' . $msg . '</li>' . "\n";
		return join('', $retval);
	}

	if (! PKWK_READONLY) {
		$retval[1] .= '   <li><a href="' . $script . WikiParam::convQuery('?cmd=backup&amp;action=delete&amp;page=' . $r_page) . '">';
		$retval[1] .= str_replace('$1', $s_page, $_title_backup_delete);
		$retval[1] .= '</a></li>' . "\n";
	}

	$query = '?cmd=backup&amp;page=' . $r_page . '&amp;age=';
	$_anchor_from = $_anchor_to   = '';
	foreach ($backups as $age=>$data) {
		if (! PLUGIN_BACKUP_DISABLE_BACKUP_RENDERING) {
			$_anchor_from = '<a href="' . $script . WikiParam::convQuery($query . $age) . '">';
			$_anchor_to   = '</a>';
		}
		$date = format_date($data['time'], TRUE);

		$url = $script . WikiParam::convQuery($query . $age);
		$retval[1] .= <<<EOD
   <li>$_anchor_from$age $date$_anchor_to
     [ <a href="$url&amp;action=diff">$_msg_diff</a>
     | <a href="$url&amp;action=nowdiff">$_msg_nowdiff</a>
     | <a href="$url&amp;action=source">$_msg_source</a>
     ]
   </li>
EOD;
	}

	return join('', $retval);
}

// List for all pages
function plugin_backup_get_list_all($withfilename = FALSE)
{
	global $cantedit;

	$pages = array_diff(WikiPage::getAllBackupPages(), $cantedit);

	if (empty($pages)) {
		return '';
	} else {
		return page_list($pages, 'backup', $withfilename);
	}
}
?>
