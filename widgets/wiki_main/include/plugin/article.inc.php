<?php
/**
 * articleプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: article.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2002      Originally written by OKAWARA,Satoshi <kawara@dml.co.jp>
//             http://www.dml.co.jp/~kawara/pukiwiki/pukiwiki.php
//
// article: BBS-like plugin

 /*
 メッセージを変更したい場合はLANGUAGEファイルに下記の値を追加してからご使用ください
	$_btn_name    = 'お名前';
	$_btn_article = '記事の投稿';
	$_btn_subject = '題名: ';

 ※$_btn_nameはcommentプラグインで既に設定されている場合があります

 投稿内容の自動メール転送機能をご使用になりたい場合は
 -投稿内容のメール自動配信
 -投稿内容のメール自動配信先
 を設定の上、ご使用ください。

 */

define('PLUGIN_ARTICLE_COLS',	70); // テキストエリアのカラム数
define('PLUGIN_ARTICLE_ROWS',	 5); // テキストエリアの行数
define('PLUGIN_ARTICLE_NAME_COLS',	24); // 名前テキストエリアのカラム数
define('PLUGIN_ARTICLE_SUBJECT_COLS',	60); // 題名テキストエリアのカラム数
define('PLUGIN_ARTICLE_NAME_FORMAT',	'[[$name]]'); // 名前の挿入フォーマット
define('PLUGIN_ARTICLE_SUBJECT_FORMAT',	'**$subject'); // 題名の挿入フォーマット
// Bootstrap用
define('PLUGIN_ARTICLE_COLS_BOOTSTRAP',	40); // テキストエリアのカラム数

define('PLUGIN_ARTICLE_INS',	0); // 挿入する位置 1:欄の前 0:欄の後
define('PLUGIN_ARTICLE_COMMENT',	1); // 書き込みの下に一行コメントを入れる 1:入れる 0:入れない
define('PLUGIN_ARTICLE_AUTO_BR',	1); // 改行を自動的変換 1:する 0:しない

define('PLUGIN_ARTICLE_MAIL_AUTO_SEND',	0); // 投稿内容のメール自動配信 1:する 0:しない
define('PLUGIN_ARTICLE_MAIL_FROM',	''); // 投稿内容のメール送信時の送信者メールアドレス
define('PLUGIN_ARTICLE_MAIL_SUBJECT_PREFIX', "[someone's PukiWiki]"); // 投稿内容のメール送信時の題名

// 投稿内容のメール自動配信先
global $_plugin_article_mailto;
$_plugin_article_mailto = array (
	''
);

function plugin_article_action()
{
	//global $script, $post, $vars, $cols, $rows, $now;
	global $script, $cols, $rows, $now;
	global $_title_collided, $_msg_collided, $_title_updated;
	global $_plugin_article_mailto, $_no_subject, $_no_name;
	global $_msg_article_mail_sender, $_msg_article_mail_page;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	//if ($post['msg'] == '') return array('msg'=>'','body'=>'');
	if (WikiParam::getMsg() == '') return array('msg'=>'','body'=>'');

	$refer = WikiParam::getPostVar('refer');
	//$name = ($post['name'] == '') ? $_no_name : $post['name'];
	$name = (WikiParam::getPostVar('name') == '') ? $_no_name : WikiParam::getPostVar('name');
	$name = ($name == '') ? '' : str_replace('$name', $name, PLUGIN_ARTICLE_NAME_FORMAT);
	//$subject = ($post['subject'] == '') ? $_no_subject : $post['subject'];
	$subject = (WikiParam::getPostVar('subject') == '') ? $_no_subject : WikiParam::getPostVar('subject');
	$subject = ($subject == '') ? '' : str_replace('$subject', $subject, PLUGIN_ARTICLE_SUBJECT_FORMAT);
	$article  = $subject . "\n" . '>' . $name . ' (' . $now . ')~' . "\n" . '~' . "\n";

	//$msg = rtrim($post['msg']);
	$msg = rtrim(WikiParam::getMsg());
	if (PLUGIN_ARTICLE_AUTO_BR) {
		//改行の取り扱いはけっこう厄介。特にURLが絡んだときは…
		//コメント行、整形済み行には~をつけないように arino
		$msg = join("\n", preg_replace('/^(?!\/\/)(?!\s)(.*)$/', '$1~', explode("\n", $msg)));
	}
	$article .= $msg . "\n\n" . '//';

	if (PLUGIN_ARTICLE_COMMENT) $article .= "\n\n" . '#comment' . "\n";

	$postdata = '';
	//$postdata_old  = get_source($post['refer']);
	$postdata_old  = get_source($refer);
	$article_no = 0;

	foreach($postdata_old as $line) {
		if (! PLUGIN_ARTICLE_INS) $postdata .= $line;
		if (preg_match('/^#article/i', $line)) {
			//if ($article_no == $post['article_no'] && $post['msg'] != '')
			if ($article_no == WikiParam::getPostVar('article_no') && WikiParam::getMsg() != '')
				$postdata .= $article . "\n";
			++$article_no;
		}
		if (PLUGIN_ARTICLE_INS) $postdata .= $line;
	}

	$postdata_input = $article . "\n";
	$body = '';

	//if (md5(@join('', get_source($post['refer']))) != $post['digest']) {
	if (md5(get_source($refer, true)) != WikiParam::getPostVar('digest')) {
		$title = $_title_collided;

		$body = $_msg_collided . "\n";

/*		$s_refer    = htmlspecialchars($post['refer']);
		$s_digest   = htmlspecialchars($post['digest']);*/
		$s_refer    = htmlspecialchars($refer);
		$s_digest   = htmlspecialchars(WikiParam::getPostVar('digest'));
		$s_postdata = htmlspecialchars($postdata_input);
		$postScript = $script . WikiParam::convQuery('?cmd=preview');
		$body .= <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="refer" value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" class="wiki_edit" rows="$rows" cols="$cols">$s_postdata</textarea><br />
 </div>
</form>
EOD;
	} else {
		//page_write($post['refer'], trim($postdata));
		page_write($refer, trim($postdata));

		// 投稿内容のメール自動送信
		if (PLUGIN_ARTICLE_MAIL_AUTO_SEND) {
			$mailaddress = implode(',', $_plugin_article_mailto);
			$mailsubject = PLUGIN_ARTICLE_MAIL_SUBJECT_PREFIX . ' ' . str_replace('**', '', $subject);
		//	if ($post['name']) $mailsubject .= '/' . $post['name'];
			$name = WikiParam::getVar('name');
			if ($name != '') $mailsubject .= '/' . $name;
			$mailsubject = mb_encode_mimeheader($mailsubject);
/*
			$mailbody = $post['msg'];
			$mailbody .= "\n\n" . '---' . "\n";
			$mailbody .= $_msg_article_mail_sender . $post['name'] . ' (' . $now . ')' . "\n";
			$mailbody .= $_msg_article_mail_page . $post['refer'] . "\n";
			$mailbody .= '　 URL: ' . $script . '?' . rawurlencode($post['refer']) . "\n";
			$mailbody = mb_convert_encoding($mailbody, 'JIS');
			*/
			
			$mailbody = WikiParam::getMsg();
			$mailbody .= "\n\n" . '---' . "\n";
			$mailbody .= $_msg_article_mail_sender . $name . ' (' . $now . ')' . "\n";
			$mailbody .= $_msg_article_mail_page . $refer . "\n";
			$mailbody .= '　 URL: ' . $script . WikiParam::convQuery('?' . rawurlencode($refer)) . "\n";
			$mailbody = mb_convert_encoding($mailbody, 'JIS');

			$mailaddheader = 'From: ' . PLUGIN_ARTICLE_MAIL_FROM;

			mail($mailaddress, $mailsubject, $mailbody, $mailaddheader);
		}

		$title = $_title_updated;
	}
	$retvars['msg'] = $title;
	$retvars['body'] = $body;

/*	$post['page'] = $post['refer'];
	$vars['page'] = $post['refer'];*/
	WikiParam::setPage($refer);

	return $retvars;
}

function plugin_article_convert()
{
	//global $script, $vars, $digest;
	global $script;
	global $_btn_article, $_btn_name, $_btn_subject;
	global $gEnvManager;
	static $numbers = array();

	if (PKWK_READONLY) return ''; // Show nothing
	
	$page = WikiParam::getPage();
	if (!isset($numbers[$page])) $numbers[$page] = 0;
	//if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;

	//$article_no = $numbers[$vars['page']]++;
	$article_no = $numbers[$page]++;

	//$s_page   = htmlspecialchars($vars['page']);
	$s_page   = htmlspecialchars($page);
	//$s_digest = htmlspecialchars($digest);
	$s_digest = htmlspecialchars(WikiParam::getDigest());
	$name_cols = PLUGIN_ARTICLE_NAME_COLS;
	$subject_cols = PLUGIN_ARTICLE_SUBJECT_COLS;
	$article_rows = PLUGIN_ARTICLE_ROWS;
	$article_cols = PLUGIN_ARTICLE_COLS;
	$postScript = $script . WikiParam::convQuery('?');
	
	// テンプレートタイプに合わせて出力を変更
	$templateType = $gEnvManager->getCurrentTemplateType();
	if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
		$article_cols = PLUGIN_ARTICLE_COLS_BOOTSTRAP;
		$string = <<<EOD
<form action="$postScript" method="post" class="form form-inline" role="form">
  <input type="hidden" name="article_no" value="$article_no" />
  <input type="hidden" name="plugin" value="article" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" name="refer" value="$s_page" />
  <div><div class="form-group"><label for="_p_article_name_$article_no">$_btn_name
  <input type="text" class="form-control" name="name" id="_p_article_name_$article_no" maxlength="$name_cols" /></label></div></div>
  <div><div class="form-group"><label for="_p_article_subject_$article_no">$_btn_subject
  <input type="text" class="form-control" name="subject" id="_p_article_subject_$article_no" maxlength="$subject_cols" /></label></div></div>
  <div><textarea class="wiki_edit form-control" name="msg" rows="$article_rows" cols="$article_cols">\n</textarea></div>
  <input type="submit" class="button btn btn-default" name="article" value="$_btn_article" />
</form>
EOD;
	} else {
		$string = <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="article_no" value="$article_no" />
  <input type="hidden" name="plugin" value="article" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" name="refer" value="$s_page" />
  <label for="_p_article_name_$article_no">$_btn_name</label>
  <input type="text" name="name" id="_p_article_name_$article_no" size="$name_cols" /><br />
  <label for="_p_article_subject_$article_no">$_btn_subject</label>
  <input type="text" name="subject" id="_p_article_subject_$article_no" size="$subject_cols" /><br />
  <textarea name="msg" class="wiki_edit" rows="$article_rows" cols="$article_cols">\n</textarea><br />
  <input type="submit" class="button" name="article" value="$_btn_article" />
 </div>
</form>
EOD;
	}
	return $string;
}
?>
