<?php
/**
 * attachプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 *
 * 変更履歴    2021/2/24 Pukiwiki v1.5.3の変更を反映
 */
// Copyright (C)
//   2003-2006 PukiWiki Developers Team
//   2002-2003 PANDA <panda@arino.jp> http://home.arino.jp/
//   2002      Y.MASUI <masui@hisec.co.jp> http://masui.net/pukiwiki/
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version

// 管理者だけが添付ファイルをアップロードできるようにする
//define('PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY', TRUE); // FALSE or TRUE

// 管理者だけが添付ファイルを削除できるようにする
//define('PLUGIN_ATTACH_DELETE_ADMIN_ONLY', TRUE); // FALSE or TRUE

// 管理者が添付ファイルを削除するときは、バックアップを作らない
// PLUGIN_ATTACH_DELETE_ADMIN_ONLY=TRUEのとき有効
//define('PLUGIN_ATTACH_DELETE_ADMIN_NOBACKUP', TRUE); // FALSE or TRUE

// ファイルのアクセス権
//define('PLUGIN_ATTACH_FILE_MODE', 0644);

// File icon image
define('PLUGIN_ATTACH_FILE_ICON', '<img src="' . IMAGE_DIR .  'file.png"' .
	' width="20" height="20" alt="file"' .
	' style="border-width:0px" />');

// mime-typeを記述したページ
define('PLUGIN_ATTACH_CONFIG_PAGE_MIME', 'plugin/attach/mime-type');

//-------- convert
function plugin_attach_convert()
{
	$page = WikiParam::getPage();

	$nolist = $noform = FALSE;
	if (func_num_args() > 0) {
		foreach (func_get_args() as $arg) {
			$arg = strtolower($arg);
			$nolist |= ($arg == 'nolist');
			$noform |= ($arg == 'noform');
		}
	}

	$ret = '';
	if (! $nolist) {
		$obj  = new AttachPages($page);
		$ret .= $obj->toString($page, TRUE);
	}
	if (! $noform) {
		$ret .= attach_form($page);
	}
	return $ret;
}

//-------- action
function plugin_attach_action()
{
	$pcmd = WikiParam::getVar('pcmd');
	$refer = WikiParam::getRefer();
	$pass = WikiParam::getVar('pass');
	$page = WikiParam::getPage();

/*
	if ($refer != '' && is_pagename($refer)) {
		if(in_array($pcmd, array('info', 'open', 'list'))) {
			check_readable($refer);
		} else {
			check_editable($refer);
		}
	}*/
	
	// ### パスワード認証フォーム表示 ###
	// info,open,listはノーチェックで通す
	if(!((in_array($pcmd, array('info', 'open')) && is_pagename($refer)) ||		// info,openの場合はリファラーが必要
		$pcmd == 'list')){
		// 認証されている場合はスルーして関数以降を実行
		$retStatus = password_form();
		if (!empty($retStatus)) return $retStatus;
	}

	// Dispatch
	if (isset($_FILES['attach_file'])) {
		// Upload
		return attach_upload($_FILES['attach_file'], $refer, $pass);
	} else {
		switch ($pcmd) {
			case 'info'     : return attach_info();
			case 'delete'   : return attach_delete();
			case 'open'     : return attach_open();
			case 'list'     : return attach_list();
			case 'freeze'   : return attach_freeze(TRUE);
			case 'unfreeze' : return attach_freeze(FALSE);
			case 'rename'   : return attach_rename();
			case 'upload'   : return attach_showform();
		}
		if ($page == '' || ! WikiPage::isPage($page)) {
			return attach_list();
		} else {
			return attach_showform();
		}
	}
}

/**
 * Wikiページ画面用の添付ファイルリストを作成
 *
 * @return			なし
 */
function attach_filelist()
{
	global $_attach_messages;

	$page = WikiParam::getPage();

	$obj = new AttachPages($page, 0);

	if (!isset($obj->pages[$page])){
		return '';
	} else {
		return $_attach_messages['msg_file'] . ': ' . $obj->toString($page, TRUE) . "\n";
	}
}

//-------- 実体
// ファイルアップロード
// $pass = NULL : パスワードが指定されていない
// $pass = TRUE : アップロード許可
function attach_upload($file, $page, $pass = NULL)
{
	global $_attach_messages, $notify, $notify_subject;
	global $gOpeLogManager;
	
	// Check query-string
	$query = 'plugin=attach&amp;pcmd=info&amp;refer=' . rawurlencode($page) . '&amp;file=' . rawurlencode($file['name']);

	if (PKWK_QUERY_STRING_MAX && strlen($query) > PKWK_QUERY_STRING_MAX) {
		pkwk_common_headers();
		echo('Query string (page name and/or file name) too long');
		exit;
	} else if (! WikiPage::isPage($page)) {
		die_message('No such page');
	} else if ($file['tmp_name'] == '' || ! is_uploaded_file($file['tmp_name'])) {
		// アップロードしたファイルがクライアントにセットした「max_file_size」を超えた場合は「error」コード2で返る
		if ($file['error'] == 2){	// 添付ファイルの最大サイズを超えた場合
			// 運用ログを残す
			$gOpeLogManager->writeUserError(__METHOD__, '[Wikiコンテンツ] 添付ファイルのサイズが最大値を超えたためアップロードできません。(ファイル名=' . $file['name'] . ')', 1100);
			
			return array(
				'result'=>FALSE,
				'msg'=>$_attach_messages['err_exceed']);
		} else {	// その他のエラーの場合
			return array(
				'result'=>FALSE,
				'msg'=>$_attach_messages['err_noparm']);
		}
	} else if ($file['size'] > WikiConfig::getUploadFilesize()){			// アップロードファイルのサイズチェック
		// 運用ログを残す
		$gOpeLogManager->writeUserError(__METHOD__, '[Wikiコンテンツ] 添付ファイルのサイズが最大値を超えたためアップロードできません。(ファイル名=' . $file['name'] . ')', 1100);
			
		return array(
			'result'=>FALSE,
			'msg'=>$_attach_messages['err_exceed']);
	} else if (!is_pagename($page) || ($pass !== TRUE && !is_editable($page))){		// 凍結されている場合はファイルアップロード不可
		return array(
			'result'=>FALSE,
			'msg'=>$_attach_messages['err_noparm']);
/*	} else if (PLUGIN_ATTACH_UPLOAD_ADMIN_ONLY && $pass !== TRUE && ($pass === NULL || ! pkwk_login($pass))) {
		return array(
			'result'=>FALSE,
			'msg'=>$_attach_messages['err_adminpass']);*/
	}

	$obj = new AttachFile($page, $file['name']);
	if ($obj->exist)
		return array('result'=>FALSE,
			'msg'=>$_attach_messages['err_exists']);

	// ファイルのパーミッション設定
	if (move_uploaded_file($file['tmp_name'], $obj->filename)) chmod($obj->filename, M3_SYSTEM_FILE_PERMISSION);
		//chmod($obj->filename, PLUGIN_ATTACH_FILE_MODE);

	//if (WikiPage::isPage($page)) touch(get_filename($page));
	// ページの更新日時を更新
	if (WikiPage::isPage($page)) WikiPage::updatePageTime($page);

	// ### 運用ログを残す ###
	$obj->outputOpeLog(1/*添付ファイルアップロード*/, $page);
		
	// パスワードは使用しない
/*
	$obj->getstatus();
	$obj->status['pass'] = ($pass !== TRUE && $pass !== NULL) ? md5($pass) : '';
	$obj->putstatus();*/

	if ($notify) {
		$footer['ACTION']   = 'File attached';
		$footer['FILENAME'] = $file['name'];
		$footer['FILESIZE'] = $file['size'];
		$footer['PAGE']     = $page;

		$footer['URI']      = get_script_uri() .
			//'?' . rawurlencode($page);
			// MD5 may heavy
			/*'?plugin=attach' .
				'&refer=' . rawurlencode($page) .
				'&file='  . rawurlencode($file['name']) .
				'&pcmd=info';*/
			WikiParam::convQuery('?plugin=attach' .
				'&refer=' . rawurlencode($page) .
				'&file='  . rawurlencode($file['name']) .
				'&pcmd=info', false);

		$footer['USER_AGENT']  = TRUE;
		$footer['REMOTE_ADDR'] = TRUE;

		pkwk_mail_notify($notify_subject, "\n", $footer) or
			die('pkwk_mail_notify(): Failed');
	}

	return array(
		'result'=>TRUE,
		'msg'=>$_attach_messages['msg_uploaded']);
}

// 詳細フォームを表示
function attach_info($err = '')
{
	//global $vars, $_attach_messages;
	global $_attach_messages;

	//foreach (array('refer', 'file', 'age') as $var)
	//	${$var} = isset($vars[$var]) ? $vars[$var] : '';

	//$obj = new AttachFile($refer, $file, $age);
	$refer = WikiParam::getRefer();
	$file = WikiParam::getVar('file');
	$age = WikiParam::getVar('age');
	
	$obj = new AttachFile($refer, $file, $age);
	return $obj->getstatus() ? $obj->info($err) : array('msg'=>$_attach_messages['err_notfound']);
}

// 削除
function attach_delete()
{
	//global $vars, $_attach_messages;
	global $_attach_messages;

	//foreach (array('refer', 'file', 'age', 'pass') as $var)
	//	${$var} = isset($vars[$var]) ? $vars[$var] : '';
	$refer = WikiParam::getRefer();
	$file = WikiParam::getVar('file');
	$age = WikiParam::getVar('age');
	$pass = WikiParam::getVar('pass');
	
	if (is_freeze($refer) || ! is_editable($refer))
		return array('msg'=>$_attach_messages['err_noparm']);

	$obj = new AttachFile($refer, $file, $age);
	if (! $obj->getstatus())
		return array('msg'=>$_attach_messages['err_notfound']);
		
	return $obj->delete($pass);
}

// 凍結
function attach_freeze($freeze)
{
	//global $vars, $_attach_messages;
	global $_attach_messages;

	//foreach (array('refer', 'file', 'age', 'pass') as $var) {
	//	${$var} = isset($vars[$var]) ? $vars[$var] : '';
	//}
	$refer = WikiParam::getRefer();
	$file = WikiParam::getVar('file');
	$age = WikiParam::getVar('age');
	$pass = WikiParam::getVar('pass');
	
	if (is_freeze($refer) || ! is_editable($refer)) {
		return array('msg'=>$_attach_messages['err_noparm']);
	} else {
		$obj = new AttachFile($refer, $file, $age);
		return $obj->getstatus() ?
			$obj->freeze($freeze, $pass) :
			array('msg'=>$_attach_messages['err_notfound']);
	}
}

// リネーム
function attach_rename()
{
	//global $vars, $_attach_messages;
	global $_attach_messages;

	/*foreach (array('refer', 'file', 'age', 'pass', 'newname') as $var) {
		${$var} = isset($vars[$var]) ? $vars[$var] : '';
	}*/
	$refer = WikiParam::getRefer();
	$file = WikiParam::getVar('file');
	$age = WikiParam::getVar('age');
	$pass = WikiParam::getVar('pass');
	$newname = WikiParam::getVar('newname');
	
	if (is_freeze($refer) || ! is_editable($refer)) {
		return array('msg'=>$_attach_messages['err_noparm']);
	}
	$obj = new AttachFile($refer, $file, $age);
	if (! $obj->getstatus())
		return array('msg'=>$_attach_messages['err_notfound']);

	return $obj->rename($pass, $newname);

}

// ダウンロード
function attach_open()
{
	//global $vars, $_attach_messages;
	global $_attach_messages;

	/*foreach (array('refer', 'file', 'age') as $var) {
		${$var} = isset($vars[$var]) ? $vars[$var] : '';
	}*/
	$refer = WikiParam::getRefer();
	$file = WikiParam::getVar('file');
	$age = WikiParam::getVar('age');

	$obj = new AttachFile($refer, $file, $age);
	return $obj->getstatus() ?
		$obj->open() :
		array('msg'=>$_attach_messages['err_notfound']);
}

// 一覧取得
function attach_list()
{
	//global $vars, $_attach_messages;
	global $_attach_messages;

	//$refer = isset($vars['refer']) ? $vars['refer'] : '';
	$refer = WikiParam::getRefer();

	$obj = new AttachPages($refer);

	$msg = $_attach_messages[($refer == '') ? 'msg_listall' : 'msg_listpage'];
	$body = ($refer == '' || isset($obj->pages[$refer])) ? $obj->toString($refer, FALSE) : $_attach_messages['err_noexist'];

	return array('msg'=>$msg, 'body'=>$body);
}

// アップロードフォームを表示 (action時)
function attach_showform()
{
	global $_attach_messages;
	
	$page = WikiParam::getPage();
	WikiParam::setRefer($page);
	$body = attach_form($page);
	
	return array('msg'=>$_attach_messages['msg_upload'], 'body'=>$body);
}

//-------- サービス
// mime-typeの決定
function attach_mime_content_type($filename, $displayname)
{
	$type = 'application/octet-stream'; // default

	if (! file_exists($filename)) return $type;
	$pathinfo = pathinfo($displayname);
	$ext0 = $pathinfo['extension'];
	if (preg_match('/^(gif|jpg|jpeg|png|swf)$/i', $ext0)) {
		$size = @getimagesize($filename);
		if (is_array($size)) {
			switch ($size[2]) {
				case 1: return 'image/gif';
				case 2: return 'image/jpeg';
				case 3: return 'image/png';
				case 4: return 'application/x-shockwave-flash';
			}
		}
	}
	// mime-type一覧表を取得
	$config = new Config(PLUGIN_ATTACH_CONFIG_PAGE_MIME);
	$table = $config->read() ? $config->get('mime-type') : array();
	unset($config); // メモリ節約
	foreach ($table as $row) {
		$_type = trim($row[0]);
		$exts = preg_split('/\s+|,/', trim($row[1]), -1, PREG_SPLIT_NO_EMPTY);
		foreach ($exts as $ext) {
			if (preg_match("/\.$ext$/i", $displayname)) return $_type;
		}
	}
	return $type;
}

// アップロードフォームの出力
function attach_form($page)
{
	global $script, $_attach_messages;
	global $dummy_password;
	global $gEnvManager;

	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	
	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);
	$linkList		= $script. WikiParam::convQuery("?plugin=attach&amp;pcmd=list&amp;refer=$r_page");
	$linkListAll	= $script. WikiParam::convQuery("?plugin=attach&amp;pcmd=list");
	
	// テンプレートタイプに合わせて出力を変更
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
		$navi = <<<EOD
  <p>
   [<a href="$linkList">{$_attach_messages['msg_list']}</a>]
   [<a href="$linkListAll">{$_attach_messages['msg_listall']}</a>]
  </p>
EOD;
	} else {
		$navi = <<<EOD
  <span>
   [<a href="$linkList">{$_attach_messages['msg_list']}</a>]
   [<a href="$linkListAll">{$_attach_messages['msg_listall']}</a>]
  </span><br />
EOD;
	}
/*
	$navi = <<<EOD
  <span>
   [<a href="$script?plugin=attach&amp;pcmd=list&amp;refer=$r_page">{$_attach_messages['msg_list']}</a>]
   [<a href="$script?plugin=attach&amp;pcmd=list">{$_attach_messages['msg_listall']}</a>]
  </span><br />
EOD;*/

	if (! ini_get('file_uploads')) return '#attach(): file_uploads disabled<br />' . $navi;
	if (! WikiPage::isPage($page))          return '#attach(): No such page<br />'          . $navi;

//	$msg_maxsize = sprintf($_attach_messages['msg_maxsize'], number_format($maxsize/1024) . 'KB');
	$maxsize = WikiConfig::getUploadFilesize();				// アップロードファイル最大サイズ
	$msg_maxsize = sprintf($_attach_messages['msg_maxsize'], WikiConfig::getUploadFilesize(true/*文字列表記*/));

	$postScript = $script . WikiParam::convQuery("?");
	
	// テンプレートタイプに合わせて出力を変更
	$body = '';
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap v3.0型テンプレートの場合
		$body .= '<form name="wiki_main" enctype="multipart/form-data" action="' . $postScript . '" method="post" class="form form-inline" role="form">' . M3_NL;
		$body .= '<input type="hidden" name="plugin" value="attach" />' . M3_NL;
		$body .= '<input type="hidden" name="pcmd"   value="post" />' . M3_NL;
		$body .= '<input type="hidden" name="refer"  value="' . $s_page . '" />' . M3_NL;
		$body .= '<input type="hidden" name="max_file_size" value="' . $maxsize . '" />' . M3_NL;
		$body .= '<input type="hidden" name="pass" />' . M3_NL;
		$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
		$body .= $navi;
		$body .= '<p>' . M3_NL;
		$body .= $msg_maxsize;
		$body .= '</p>' . M3_NL;
		$body .= '<div class="form-group">' . M3_NL;
		$body .= '<div class="input-group">' . M3_NL;
		$body .= '<span class="input-group-btn">' . M3_NL;
		$body .= '<span class="btn btn-primary btn-file">' . $_attach_messages['msg_select_file'] . '<input type="file" name="attach_file"></span>' . M3_NL;
		$body .= '</span>' . M3_NL;
		$body .= '<input type="text" class="form-control" readonly>' . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '<div>' . M3_NL;
		$body .= '<input type="submit" id="wiki_main_submit" class="button btn" value="' . $_attach_messages['btn_upload'] . '" />' . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '</form>' . M3_NL;
	} else if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
		$body .= $navi;
		$body .= '<p>' . M3_NL;
		$body .= $msg_maxsize;
		$body .= '</p>' . M3_NL;
		$body .= '<form name="wiki_main" enctype="multipart/form-data" action="' . $postScript . '" method="post" class="form form-inline">' . M3_NL;
		$body .= '<input type="hidden" name="plugin" value="attach" />' . M3_NL;
		$body .= '<input type="hidden" name="pcmd"   value="post" />' . M3_NL;
		$body .= '<input type="hidden" name="refer"  value="' . $s_page . '" />' . M3_NL;
		$body .= '<input type="hidden" name="max_file_size" value="' . $maxsize . '" />' . M3_NL;
		$body .= '<input type="hidden" name="pass" />' . M3_NL;
		$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
		$body .= '<div class="form-group">' . M3_NL;
		$body .= '<div class="input-group mr-2">' . M3_NL;
		$body .= '<div class="input-group-prepend">' . M3_NL;
		$body .= '<button class="button btn btn-file">' . $_attach_messages['msg_select_file'] . '<input type="file" name="attach_file"></button>' . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '<input type="text" class="form-control" readonly>' . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '<input type="submit" id="wiki_main_submit" class="button btn btn-success" value="' . $_attach_messages['btn_upload'] . '" />' . M3_NL;
		$body .= '</form>' . M3_NL;
	} else {
		$body .= '<form name="wiki_main" enctype="multipart/form-data" action="' . $postScript . '" method="post" class="form">' . M3_NL;
		$body .= '<div>' . M3_NL;
		$body .= '<input type="hidden" name="plugin" value="attach" />' . M3_NL;
		$body .= '<input type="hidden" name="pcmd"   value="post" />' . M3_NL;
		$body .= '<input type="hidden" name="refer"  value="' . $s_page . '" />' . M3_NL;
		$body .= '<input type="hidden" name="max_file_size" value="' . $maxsize . '" />' . M3_NL;
		$body .= '<input type="hidden" name="pass" />' . M3_NL;
		$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
		$body .= $navi;
		$body .= '<span>' . M3_NL;
		$body .= $msg_maxsize;
		$body .= '</span><br />' . M3_NL;
		$body .= '<label for="_p_attach_file">' . $_attach_messages['msg_file'] . ':</label> <input type="file" name="attach_file" id="_p_attach_file" />' . M3_NL;
		$body .= '<input type="submit" id="wiki_main_submit" class="button" value="' . $_attach_messages['btn_upload'] . '" />' . M3_NL;
		$body .= '</div>' . M3_NL;
		$body .= '</form>' . M3_NL;
	}
	
	// Javascript読み込み
	plugin_attach_addScript();
	
	return $body;
}
/**
 * Javascriptを追加
 *
 * @return						なし
 */
function plugin_attach_addScript()
{
	global $gEnvManager;
	global $gPageManager;
	
	// 実行中のウィジェットを取得
	$widgetObj = $gEnvManager->getCurrentWidgetObj();
	
	$scriptBody = $widgetObj->getParsedTemplateData('plugin/attach.tmpl.js');

	// Javascriptを追加
	$gPageManager->addHeadScript($scriptBody);
}
//-------- クラス
// ファイル
class AttachFile
{
	var $page, $file, $age, $basename, $filename;
	var $time = 0;
	var $size = 0;
	var $time_str = '';
	var $size_str = '';
	var $status = array('count'=>array(0), 'age'=>'', 'pass'=>'', 'freeze'=>FALSE);

	// 運用ログメッセージ
	const LOG_MSG_UPLOAD = 'Wikiコンテンツを更新(添付ファイルアップロード)しました。タイトル: %s; 添付ファイル名: %s';
	const LOG_MSG_RENAME = 'Wikiコンテンツを更新(添付ファイル名変更)しました。タイトル: %s; 添付ファイル名: %s → %s';
	const LOG_MSG_DELETE = 'Wikiコンテンツを更新(添付ファイル削除)しました。タイトル: %s; 添付ファイル名: %s';

	function __construct($page, $file, $age = 0)
	{
		$this->page = $page;
		$this->file = preg_replace('#^.*/#','',$file);
		$this->age  = is_numeric($age) ? $age : 0;

		$this->basename = UPLOAD_DIR . encode($page) . '_' . encode($this->file);
		$this->filename = $this->basename . ($age ? '.' . $age : '');
		$this->exist    = file_exists($this->filename);
		$this->time     = $this->exist ? filemtime($this->filename) - LOCALZONE : 0;
		$this->md5hash  = $this->exist ? md5_file($this->filename) : '';
	}

	// ファイル情報取得
	function getstatus()
	{
		if (! $this->exist) return FALSE;

		// 1レコード(行)のパターンは、「ファイル名」「元のファイル名」「ファイルハッシュキー」「世代番号」「アクセスカウント数」「凍結状態」をタブ区切りで格納
		$keyFilename = basename($this->filename);
		$lines = WikiPage::getPageUpload($this->page);
		foreach ($lines as $line){
			$lineData = rtrim($line);
			if (empty($lineData)) continue;
			
			list($filename, $originalFilename, $md5, $age, $count, $freeze) = explode("\t", $lineData);
			if ($filename == $keyFilename){
				$this->status['age'] = $age;
				$this->status['count'] = $count;
				$this->status['md5'] = $md5;
				$this->status['freeze'] = $freeze;
				
				$this->status['count'] = explode(',', $this->status['count']);		// 複数設定のときは配列にする
				break;
			}
		}
		
		$this->time_str = get_date('Y/m/d H:i:s', $this->time);
		$this->size     = filesize($this->filename);
		$this->size_str = sprintf('%01.1f', round($this->size/1024, 1)) . 'KB';
		$this->type     = attach_mime_content_type($this->filename, $this->file);

		return TRUE;
	}

	// ステータス保存
	function putstatus()
	{
		// 配列を文字列に変換
		$this->status['count'] = join(',', $this->status['count']);
		
		// 1レコード(行)のパターンは、「ファイル名」「元のファイル名」「ファイルハッシュキー」「世代番号」「アクセスカウント数」「凍結状態」をタブ区切りで格納
		$keyFilename = basename($this->filename);
		$lines = WikiPage::getPageUpload($this->page);
		for ($i = 0; $i < count($lines); $i++){
			$lineData = rtrim($lines[$i]);
			if (empty($lineData)) continue;
			
			list($filename, $originalFilename, $md5, $age, $count, $freeze) = explode("\t", $lineData);
			if ($filename == $keyFilename){
				array_splice($lines, $i, 1);
				break;
			}
		}
		// 新規データ追加
		$newLine = '';
		$newLine .= $keyFilename . "\t";
		$newLine .= $this->file . "\t";		// 元のファイル名
		$newLine .= (file_exists($this->filename) ? md5_file($this->filename) : '') . "\t";// ファイルハッシュ値再設定
		$newLine .= $this->status['age'] . "\t";
		$newLine .= $this->status['count'] . "\t";
		$newLine .= $this->status['freeze'] . "\n";
		$lines[] = $newLine;
		WikiPage::updatePageUpload($this->page, join('', $lines));
	}

	// 日付の比較関数
	static function datecomp($a, $b) {
		//return ($a->time == $b->time) ? 0 : (($a->time > $b->time) ? -1 : 1);
		return ($a->time == $b->time) ? 0 : (($a->time < $b->time) ? -1 : 1);		// 添付ファイルはアップロード日時昇順に並べる(Magic3仕様)
	}

	function toString($showicon, $showinfo)
	{
		global $script, $_attach_messages;
		global $gEnvManager;

		$editAuth = WikiConfig::isUserWithEditAuth();		// 編集権限があるかどうか
		
		$this->getstatus();
		$param  = '&amp;file=' . rawurlencode($this->file) . '&amp;refer=' . rawurlencode($this->page) .
			($this->age ? '&amp;age=' . $this->age : '');
		$title = $this->time_str . ' ' . $this->size_str;
		$label = ($showicon ? PLUGIN_ATTACH_FILE_ICON : '') . htmlspecialchars($this->file);
		if ($this->age) {
			$label .= ' (backup No.' . $this->age . ')';
		}
		$info = $count = '';
		$infoUrl = $script . WikiParam::convQuery("?plugin=attach&amp;pcmd=info$param");
		$openUrl = $script . WikiParam::convQuery("?plugin=attach&amp;pcmd=open$param");
		if ($showinfo) {
			// テンプレートタイプに合わせて出力を変更
//			$templateType = $gEnvManager->getCurrentTemplateType();
//			if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
				$_title = str_replace('$1', rawurlencode($this->file), $_attach_messages['msg_info']);
				
				// 添付ファイルの詳細情報はページ編集権限がある場合のみ表示→詳細情報は常に表示(仕様変更 2021/2/23)
				//if ($editAuth){
					$info = "\n[<a href=\"$infoUrl\" title=\"$_title\" rel=\"tooltip\" data-toggle=\"tooltip\">{$_attach_messages['btn_info']}</a>]\n";
				
					// ダウンロード数は制限する?
					$count = ($showicon && ! empty($this->status['count'][$this->age])) ? sprintf($_attach_messages['msg_count'], $this->status['count'][$this->age]) : '';
				//}
		}
		//return "<a href=\"$openUrl\" title=\"$title\" target=\"_blank\" rel=\"tooltip\" data-toggle=\"tooltip\">$label</a> $count $info";
//		return "<a href=\"$openUrl\" title=\"$title\" target=\"_blank\" rel=\"tooltip\" data-toggle=\"tooltip\" style=\"white-space: nowrap\">$label</a> $count $info";
		return "<a href=\"$openUrl\" title=\"$title\" target=\"_blank\" rel=\"tooltip\" data-toggle=\"tooltip\" style=\"word-break: break-all;\">$label</a> $count $info";
	}

	// 情報表示
	function info($err)
	{
		global $script, $_attach_messages;
		global $dummy_password;
		global $gEnvManager;
			
		// テンプレートタイプに合わせて出力を変更
		$templateType = $gEnvManager->getCurrentTemplateType();
		
		$r_page = rawurlencode($this->page);
		$s_page = htmlspecialchars($this->page);
		$s_file = htmlspecialchars($this->file);
		$s_err = ($err == '') ? '' : '<p style="font-weight:bold">' . $_attach_messages[$err] . '</p>';
		$msg_rename  = '';
		
		// ベースHタグレベル取得
		$baseHTagLevel = 2;
		$widgetObj = $gEnvManager->getCurrentWidgetObj();
		if (!empty($widgetObj)) $baseHTagLevel = $widgetObj->getHTagLevel();

		// テンプレートタイプに合わせて出力を変更
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap v3.0型テンプレートの場合
			if ($this->age) {
				$msg_delete  = '<div><div class="radio"><input type="radio" name="pcmd" id="_p_attach_delete" value="delete" />' .
					'<label for="_p_attach_delete">' .  $_attach_messages['msg_delete'] . '</label></div></div>';
			} else {
				if ($this->status['freeze']) {
					$msg_delete  = '';
				} else {
					$msg_delete = '<div><div class="radio"><input type="radio" name="pcmd" id="_p_attach_delete" value="delete" />' . '<label for="_p_attach_delete">' . $_attach_messages['msg_delete'];
					$msg_delete .= '</label></div></div>';

					$msg_rename  = '<div><div class="radio"><input type="radio" name="pcmd" id="_p_attach_rename" value="rename" />' .
						'<label for="_p_attach_rename">' .  $_attach_messages['msg_rename'] . '</label></div></div>' .
						'<div class="form-group"><label for="_p_attach_newname">' . $_attach_messages['msg_newname'] . ':</label> ' .
						'<input type="text" name="newname" id="_p_attach_newname" class="form-control" size="40" value="' . $this->file . '" /></div>';
				}
			}
		} else if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
			if ($this->age) {
				$msg_delete  = '<div class="form-check"><input type="radio" name="pcmd" id="_p_attach_delete" class="form-check-input" value="delete" />' .
					'<label for="_p_attach_delete" class="form-check-label">' .  $_attach_messages['msg_delete'] . '</label></div>';
			} else {
				if ($this->status['freeze']) {
					$msg_delete  = '';
				} else {
					$msg_delete = '<div class="form-check"><input type="radio" name="pcmd" id="_p_attach_delete" class="form-check-input" value="delete" />' . '<label for="_p_attach_delete" class="form-check-label">' . $_attach_messages['msg_delete'];
					$msg_delete .= '</label></div>';

					$msg_rename  = '<div class="form-check"><input type="radio" name="pcmd" id="_p_attach_rename" class="form-check-input" value="rename" />' .
						'<label for="_p_attach_rename" class="form-check-label">' .  $_attach_messages['msg_rename'] . '</label></div>' .
						'<div class="form-group form-inline"><label for="_p_attach_newname" class="mr-2">' . $_attach_messages['msg_newname'] . ':</label>' .
						'<input type="text" name="newname" id="_p_attach_newname" class="form-control" size="40" value="' . $this->file . '" /></div>';
				}
			}
		} else {
			if ($this->age) {
//				$msg_freezed = '';
				$msg_delete  = '<input type="radio" name="pcmd" id="_p_attach_delete" value="delete" />' .
					'<label for="_p_attach_delete">' .  $_attach_messages['msg_delete'] . '</label><br />';
//				$msg_freeze  = '';
			} else {
				if ($this->status['freeze']) {
//					$msg_freezed = "<dd>{$_attach_messages['msg_isfreeze']}</dd>";
					$msg_delete  = '';
//					$msg_freeze  = '<input type="radio" name="pcmd" id="_p_attach_unfreeze" value="unfreeze" />' .
						'<label for="_p_attach_unfreeze">' .  $_attach_messages['msg_unfreeze'] . '</label><br />';
				} else {
//					$msg_freezed = '';
					$msg_delete = '<input type="radio" name="pcmd" id="_p_attach_delete" value="delete" />' .
						'<label for="_p_attach_delete">' . $_attach_messages['msg_delete'];
					$msg_delete .= '</label><br />';
//					$msg_freeze  = '<input type="radio" name="pcmd" id="_p_attach_freeze" value="freeze" />' .
						'<label for="_p_attach_freeze">' .  $_attach_messages['msg_freeze'] . '</label><br />';

					$msg_rename  = '<input type="radio" name="pcmd" id="_p_attach_rename" value="rename" />' .
						'<label for="_p_attach_rename">' .  $_attach_messages['msg_rename'] . '</label><br />&nbsp;&nbsp;&nbsp;&nbsp;' .
						'<label for="_p_attach_newname">' . $_attach_messages['msg_newname'] .
						':</label> ' .
						'<input type="text" name="newname" id="_p_attach_newname" size="40" value="' .
						$this->file . '" /><br />';
				}
			}
		}
		$info = $this->toString(TRUE, FALSE);

		$postScript 	= $script . WikiParam::convQuery("?");
		$linkList		= $script . WikiParam::convQuery("?plugin=attach&amp;pcmd=list&amp;refer=$r_page");
		$linkListAll	= $script . WikiParam::convQuery("?plugin=attach&amp;pcmd=list");
		$msg			= sprintf($_attach_messages['msg_info'], htmlspecialchars($this->file));
		$body			= '';
		$filename		= basename($this->filename);
		
		// テンプレートタイプに合わせて出力を変更
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap v3.0型テンプレートの場合
			$body .= '<p>' . M3_NL;
			$body .= '[<a href="' . $linkList . '">' . $_attach_messages['msg_list'] . '</a>]' . M3_NL;
			$body .= '[<a href="' . $linkListAll . '">' . $_attach_messages['msg_listall'] . '</a>]' . M3_NL;
			$body .= '</p>' . M3_NL;
			$body .= '<dl class="wiki_list">' . M3_NL;
			$body .= '<dt>' . $info . '</dt>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_page'] . ': ' . make_pagelink($this->page) . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_filename'] . ': ' . $filename . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_filesize'] . ': ' . $this->size_str . ' (' . $this->size . ' bytes)</dd>' . M3_NL;
			$body .= '<dd>Content-type: ' . $this->type . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_date'] . ': ' . $this->time_str . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_dlcount'] . ': ' . $this->status['count'][$this->age] . '</dd>' . M3_NL;
			$body .= '</dl>' . M3_NL;
			if (WikiConfig::isUserWithEditAuth() && is_editable($this->page)){		// ユーザに編集権限があり、ページが編集可能な場合のみ添付ファイルの削除、ファイル名変更が可能
				$body .= '<hr />' . M3_NL;
				$body .= $s_err;
				$body .= '<form action="' . $postScript . '" method="post" class="form form-inline" role="form">' . M3_NL;
				$body .= '<input type="hidden" name="plugin" value="attach" />' . M3_NL;
				$body .= '<input type="hidden" name="refer" value="' . $s_page . '" />' . M3_NL;
				$body .= '<input type="hidden" name="file" value="' . $s_file . '" />' . M3_NL;
				$body .= '<input type="hidden" name="age" value="' . $this->age . '" />' . M3_NL;
				$body .= '<input type="hidden" name="pass" />' . M3_NL;
				$body .= $msg_delete;
				$body .= $msg_rename;
				$body .= '<br /><br />' . M3_NL;
				$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
				$body .= '<input type="submit" class="button btn" value="' . $_attach_messages['btn_submit'] . '" onclick="this.form.pass.value = hex_md5(this.form.password.value); this.form.password.value = \'\';" />' . M3_NL;
				$body .= '</form>' . M3_NL;
			}
		} else if ($templateType == M3_TEMPLATE_BOOTSTRAP_40){		// Bootstrap v4.0型テンプレートの場合
			$body .= '<p>' . M3_NL;
			$body .= '[<a href="' . $linkList . '">' . $_attach_messages['msg_list'] . '</a>]' . M3_NL;
			$body .= '[<a href="' . $linkListAll . '">' . $_attach_messages['msg_listall'] . '</a>]' . M3_NL;
			$body .= '</p>' . M3_NL;
			$body .= '<h' . ($baseHTagLevel +1) . '>' . $info . '</h' . ($baseHTagLevel +1) . '>' . M3_NL;
			//$body .= '<dl class="wiki_list">' . M3_NL;
			$body .= '<dl>' . M3_NL;
			//$body .= '<dt class="list-inline-item">' . $info . '</dt>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_page'] . ': ' . make_pagelink($this->page) . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_filename'] . ': ' . $filename . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_filesize'] . ': ' . $this->size_str . ' (' . $this->size . ' bytes)</dd>' . M3_NL;
			$body .= '<dd>Content-type: ' . $this->type . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_date'] . ': ' . $this->time_str . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_dlcount'] . ': ' . $this->status['count'][$this->age] . '</dd>' . M3_NL;
			$body .= '</dl>' . M3_NL;
			if (WikiConfig::isUserWithEditAuth() && is_editable($this->page)){		// ユーザに編集権限があり、ページが編集可能な場合のみ添付ファイルの削除、ファイル名変更が可能
				$body .= '<hr />' . M3_NL;
				$body .= $s_err;
				$body .= '<form action="' . $postScript . '" method="post" class="form">' . M3_NL;
				$body .= '<input type="hidden" name="plugin" value="attach" />' . M3_NL;
				$body .= '<input type="hidden" name="refer" value="' . $s_page . '" />' . M3_NL;
				$body .= '<input type="hidden" name="file" value="' . $s_file . '" />' . M3_NL;
				$body .= '<input type="hidden" name="age" value="' . $this->age . '" />' . M3_NL;
				$body .= '<input type="hidden" name="pass" />' . M3_NL;
				$body .= $msg_delete;
				$body .= $msg_rename;
				$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
				$body .= '<input type="submit" class="button btn" value="' . $_attach_messages['btn_submit'] . '" onclick="this.form.pass.value = hex_md5(this.form.password.value); this.form.password.value = \'\';" />' . M3_NL;
				$body .= '</form>' . M3_NL;
			}
		} else {
			$body .= '<p>' . M3_NL;
			$body .= '[<a href="' . $linkList . '">' . $_attach_messages['msg_list'] . '</a>]' . M3_NL;
			$body .= '[<a href="' . $linkListAll . '">' . $_attach_messages['msg_listall'] . '</a>]' . M3_NL;
			$body .= '</p>' . M3_NL;
			$body .= '<dl class="wiki_list">' . M3_NL;
			$body .= '<dt>' . $info . '</dt>' . M3_NL;
//			$body .= '<dd>' . $_attach_messages['msg_page'] . ': ' . $s_page . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_page'] . ': ' . make_pagelink($this->page) . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_filename'] . ': ' . $filename . '</dd>' . M3_NL;
//			$body .= '<dd>' . $_attach_messages['msg_md5hash'] . ': ' . $this->md5hash . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_filesize'] . ': ' . $this->size_str . ' (' . $this->size . ' bytes)</dd>' . M3_NL;
			$body .= '<dd>Content-type: ' . $this->type . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_date'] . ': ' . $this->time_str . '</dd>' . M3_NL;
			$body .= '<dd>' . $_attach_messages['msg_dlcount'] . ': ' . $this->status['count'][$this->age] . '</dd>' . M3_NL;
//			$body .= $msg_freezed;
			$body .= '</dl>' . M3_NL;
			if (WikiConfig::isUserWithEditAuth() && is_editable($this->page)){		// ユーザに編集権限があり、ページが編集可能な場合のみ添付ファイルの削除、ファイル名変更が可能
				$body .= '<hr />' . M3_NL;
				$body .= $s_err;
				$body .= '<form action="' . $postScript . '" method="post" class="form">' . M3_NL;
				$body .= '<div>' . M3_NL;
				$body .= '<input type="hidden" name="plugin" value="attach" />' . M3_NL;
				$body .= '<input type="hidden" name="refer" value="' . $s_page . '" />' . M3_NL;
				$body .= '<input type="hidden" name="file" value="' . $s_file . '" />' . M3_NL;
				$body .= '<input type="hidden" name="age" value="' . $this->age . '" />' . M3_NL;
				$body .= '<input type="hidden" name="pass" />' . M3_NL;
				$body .= $msg_delete;
//				$body .= $msg_freeze;
				$body .= $msg_rename;
				$body .= '<br />' . M3_NL;
	//			$body .= '<label for="_p_attach_password">' . $_attach_messages['msg_password'] . ':</label>' . M3_NL;
	//			$body .= '<input type="password" name="password" id="_p_attach_password" size="12" />' . M3_NL;
				$body .= '<input type="hidden" name="password" value="' . $dummy_password . '" />' . M3_NL;
				$body .= '<input type="submit" class="button" value="' . $_attach_messages['btn_submit'] . '" onclick="this.form.pass.value = hex_md5(this.form.password.value); this.form.password.value = \'\';" />' . M3_NL;
				$body .= '</div>' . M3_NL;
				$body .= '</form>' . M3_NL;
			}
		}
		return array('msg' => $msg, 'body' => $body);
	}

	function delete($pass)
	{
		global $_attach_messages, $notify, $notify_subject;

		if ($this->status['freeze']) return attach_info('msg_isfreeze');

/*		if (! pkwk_login($pass)) {
			if (PLUGIN_ATTACH_DELETE_ADMIN_ONLY || $this->age) {
				return attach_info('err_adminpass');
			}
		}*/

		/*
		// バックアップ
		if ($this->age || (PLUGIN_ATTACH_DELETE_ADMIN_ONLY && PLUGIN_ATTACH_DELETE_ADMIN_NOBACKUP)) {
			// 添付ファイル削除
			@unlink($this->filename);
		} else {
			do {
				$age = ++$this->status['age'];
			} while (file_exists($this->basename . '.' . $age));

			if (! rename($this->basename,$this->basename . '.' . $age)) {
				// 削除失敗 why?
				return array('msg'=>$_attach_messages['err_delete']);
			}

			$this->status['count'][$age] = $this->status['count'][0];
			$this->status['count'][0] = 0;
			$this->putstatus();
		}*/
		
		// ##### 添付ファイルの削除の場合、バックアップはどうする? #####
		// 添付ファイル削除
		@unlink($this->filename);

		// ページの更新日時を更新
		if (WikiPage::isPage($this->page)) WikiPage::updatePageTime($this->page);

		// ### 運用ログを残す ###
		$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_WIKI,
								M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->page,
								M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
		$this->_writeUserInfoEvent(__METHOD__, sprintf(self::LOG_MSG_DELETE, $this->page, $this->file), 2403, 'ID=' . $this->page, $eventParam);
				
		if ($notify) {
			$footer['ACTION']   = 'File deleted';
			$footer['FILENAME'] = $this->file;
			$footer['PAGE']     = $this->page;
			//$footer['URI']      = get_script_uri() . '?' . rawurlencode($this->page);
			$footer['URI']      = get_script_uri() . WikiParam::convQuery('?' . rawurlencode($this->page), false);
			$footer['USER_AGENT']  = TRUE;
			$footer['REMOTE_ADDR'] = TRUE;
			pkwk_mail_notify($notify_subject, "\n", $footer) or
				die('pkwk_mail_notify(): Failed');
		}

		return array('msg'=>$_attach_messages['msg_deleted']);
	}

	function rename($pass, $newname)
	{
		global $_attach_messages, $notify, $notify_subject;

		if ($this->status['freeze']) return attach_info('msg_isfreeze');

		$newbase = UPLOAD_DIR . encode($this->page) . '_' . encode($newname);
		if (file_exists($newbase)) {
			return array('msg'=>$_attach_messages['err_exists']);
		}

		// 添付ファイル名変更
		if (!rename($this->basename, $newbase)) {
			return array('msg'=>$_attach_messages['err_rename']);
		}
		
		// ### 運用ログを残す ###
		$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_WIKI,
								M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $this->page,
								M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
		$this->_writeUserInfoEvent(__METHOD__, sprintf(self::LOG_MSG_RENAME, $this->page, $this->file, $newname), 2403, 'ID=' . $this->page, $eventParam);
		
		return array('msg'=>$_attach_messages['msg_renamed']);
	}

	function freeze($freeze, $pass)
	{
		global $_attach_messages;

		if (! pkwk_login($pass)) return attach_info('err_adminpass');

		$this->getstatus();
		$this->status['freeze'] = $freeze;
		$this->putstatus();

		return array('msg'=>$_attach_messages[$freeze ? 'msg_freezed' : 'msg_unfreezed']);
	}

	function open()
	{
		global $gPageManager;

		$this->getstatus();
		$this->status['count'][$this->age]++;
		$this->putstatus();
		$filename = $this->file;

		// Care for Japanese-character-included file name
		if (LANG == 'ja') {
			switch(UA_NAME . '/' . UA_PROFILE){
			case 'Opera/default':
				// Care for using _auto-encode-detecting_ function
				$filename = mb_convert_encoding($filename, 'UTF-8', 'auto');
				break;
			case 'MSIE/default':
				$filename = mb_convert_encoding($filename, 'SJIS', 'auto');
				break;
			}
		}
		$filename = htmlspecialchars($filename);

		ini_set('default_charset', '');
		mb_http_output('pass');

		// ページ作成処理中断
		$gPageManager->abortPage();

		pkwk_common_headers();
		header('Content-Disposition: inline; filename="' . $filename . '"');
		header('Content-Length: ' . $this->size);
		header('Content-Type: '   . $this->type);
		@readfile($this->filename);
		//exit;
		
		// システム強制終了
		$gPageManager->exitSystem();
	}

	/**
	 * 運用ログ出力
	 *
	 * @param int    $type		メッセージタイプ(1=添付ファイルアップロード,2=添付ファイル名変更,3=添付ファイル削除)
	 * @param string $page   	Wikiページ名
	 * @return なし
	 */
	function outputOpeLog($type, $page)
	{
		$eventParam = array(	M3_EVENT_HOOK_PARAM_CONTENT_TYPE	=> M3_VIEW_TYPE_WIKI,
								M3_EVENT_HOOK_PARAM_CONTENT_ID		=> $page,
								M3_EVENT_HOOK_PARAM_UPDATE_DT		=> date("Y/m/d H:i:s"));
		$this->_writeUserInfoEvent(__METHOD__, sprintf(self::LOG_MSG_UPLOAD, $page, $this->file), 2403, 'ID=' . $page, $eventParam);
	}
	/**
	 * ユーザ操作運用ログ出力とイベント処理
	 *
	 * 以下の状況で運用ログメッセージを出力するためのインターフェイス
	 * ユーザの通常の操作で記録すべきもの
	 * 例) コンテンツの更新等
	 *
	 * @param object $method	呼び出し元クラスメソッド(通常は「__METHOD__」)
	 * @param string $msg   	メッセージ
	 * @param int    $code		メッセージコード
	 * @param string $msgExt   	詳細メッセージ
	 * @param array  $eventParam	イベント処理用パラメータ(ログに格納しない)
	 * @return なし
	 */
	function _writeUserInfoEvent($method, $msg, $code = 0, $msgExt = '', $eventParam = array())
	{
		global $gOpeLogManager;
		
		$gOpeLogManager->writeUserInfo($method, $msg, $code, $msgExt, '', '', false, $eventParam);
	}
}

// ファイルコンテナ
class AttachFiles
{
	var $page;
	var $files = array();

	function __construct($page)
	{
		$this->page = $page;
	}

	function add($file, $age)
	{
		$this->files[$file][$age] = new AttachFile($this->page, $file, $age);
	}

	// ファイル一覧を取得
	function toString($flat)
	{
		global $_title_cannotread;

		if (! check_readable($this->page, FALSE, FALSE)) {
			return str_replace('$1', make_pagelink($this->page), $_title_cannotread);
		} else if ($flat) {
			return $this->to_flat();
		}

		$ret = '';
		$files = array_keys($this->files);
		//sort($files);
		sort($files, SORT_STRING);

		foreach ($files as $file) {
			$_files = array();
			foreach (array_keys($this->files[$file]) as $age) {
				$_files[$age] = $this->files[$file][$age]->toString(FALSE, TRUE);
			}
			if (! isset($_files[0])) {
				$_files[0] = htmlspecialchars($file);
			}
			//ksort($_files);
			ksort($_files, SORT_NUMERIC);
			$_file = $_files[0];
			unset($_files[0]);
			$ret .= " <li>$_file\n";
			if (count($_files)) {
				$ret .= "<ul>\n<li>" . join("</li>\n<li>", $_files) . "</li>\n</ul>\n";
			}
			$ret .= " </li>\n";
		}
		return make_pagelink($this->page) . "\n<ul>\n$ret</ul>\n";
	}

	// ファイル一覧を取得(inline)
	function to_flat()
	{
		$ret = '';
		$files = array();
		foreach (array_keys($this->files) as $file) {
			if (isset($this->files[$file][0])) {
				$files[$file] = $this->files[$file][0];
			}
		}
		
		// ファイル一覧はアップロード日時昇順で並べる(Magic3仕様)
		uasort($files, array('AttachFile', 'datecomp'));

		foreach (array_keys($files) as $file) {
			$ret .= $files[$file]->toString(TRUE, TRUE) . ' ';
		}

		return $ret;
	}
}

// ページコンテナ
class AttachPages
{
	var $pages = array();

	function __construct($page = '', $age = NULL)
	{
		// アップロード用のディレクトリ内のファイルリストを取得
		$dir = opendir(UPLOAD_DIR) or
			die('directory ' . UPLOAD_DIR . ' is not exist or not readable.');

		$page_pattern = ($page == '') ? '(?:[0-9A-F]{2})+' : preg_quote(encode($page), '/');
		$age_pattern = ($age === NULL) ?
			'(?:\.([0-9]+))?' : ($age ?  "\.($age)" : '');
		$pattern = "/^({$page_pattern})_((?:[0-9A-F]{2})+){$age_pattern}$/";

		$matches = array();
		while ($file = readdir($dir)) {
			if (! preg_match($pattern, $file, $matches))
				continue;

			$_page = decode($matches[1]);
			$_file = decode($matches[2]);
			$_age  = isset($matches[3]) ? $matches[3] : 0;
			if (! isset($this->pages[$_page])) {
				$this->pages[$_page] = new AttachFiles($_page);
			}
			$this->pages[$_page]->add($_file, $_age);
		}
		closedir($dir);
	}

	function toString($page = '', $flat = FALSE)
	{
		if ($page != '') {
			if (! isset($this->pages[$page])) {
				return '';
			} else {
				return $this->pages[$page]->toString($flat);
			}
		}
		$ret = '';

		// ページ名ソート
		$pages = array_keys($this->pages);
		//sort($pages);
		sort($pages, SORT_STRING);

		foreach ($pages as $page) {
			if (check_non_list($page)) continue;
			$ret .= '<li>' . $this->pages[$page]->toString($flat) . '</li>' . "\n";
		}
		return "\n" . '<ul>' . "\n" . $ret . '</ul>' . "\n";
	}
}
?>
