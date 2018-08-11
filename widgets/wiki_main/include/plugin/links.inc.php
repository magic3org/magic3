<?php
/**
 * linksプラグイン
 *
 * 機能：リンク情報を更新
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: links.inc.php 1601 2009-03-21 05:51:06Z fishbone $
 * @link       http://www.magic3.org
 */
function plugin_links_init()
{
	$messages = array(
		'_links_messages'=>array(
			'title_update'  => 'キャッシュ更新',
			'msg_adminpass' => '管理者パスワード',
			'btn_submit'    => '実行',
			'msg_done'      => 'キャッシュの更新が完了しました。',
			'msg_usage'     => "
* 処理内容

:キャッシュを更新|
全てのページをスキャンし、あるページがどのページからリンクされているかを調査して、キャッシュに記録します。

* 注意
実行には数分かかる場合もあります。実行ボタンを押したあと、しばらくお待ちください。

* 実行
管理者パスワードを入力して、[実行]ボタンをクリックしてください。
"
		)
	);
	set_plugin_messages($messages);
}

function plugin_links_action()
{
	global $script;
	global $_links_messages;
	global $gEnvManager;
	
	if (PKWK_SAFE_MODE || PKWK_READONLY) die_message('PKWK_READONLY prohibits this');

	$msg = $body = '';
	$action = WikiParam::getVar('action');
	if ($action == '' || !pkwk_login(WikiParam::getPostVar('pass'))) {
		$msg   = $_links_messages['title_update'];
		$postScript = $script . WikiParam::convQuery("?");
		$body  = convert_html($_links_messages['msg_usage']);
		
		// テンプレートタイプに合わせて出力を変更
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$body .= <<<EOD
<form action="$postScript" method="post" class="form form-inline" role="form">
 <div>
  <input type="hidden" name="plugin" value="links" />
  <input type="hidden" name="action" value="update" />
  <input type="hidden" name="pass" />
  <div class="form-group"><label for="_p_links_adminpass">{$_links_messages['msg_adminpass']}</label>
  <input type="password" class="form-control" name="password" id="_p_links_adminpass" size="12" /></div>
  <input type="submit" class="button btn" value="{$_links_messages['btn_submit']}" onclick="this.form.pass.value = hex_md5(this.form.password.value); this.form.password.value = '';" />
 </div>
</form>
EOD;
		} else {
			$body .= <<<EOD
<form action="$postScript" method="post" class="form">
 <div>
  <input type="hidden" name="plugin" value="links" />
  <input type="hidden" name="action" value="update" />
  <input type="hidden" name="pass" />
  <label for="_p_links_adminpass">{$_links_messages['msg_adminpass']}</label>
  <input type="password" name="password" id="_p_links_adminpass" size="12" />
  <input type="submit" class="button" value="{$_links_messages['btn_submit']}" onclick="this.form.pass.value = hex_md5(this.form.password.value); this.form.password.value = '';" />
 </div>
</form>
EOD;
		}
	} else if ($action == 'update'){
		links_init();
		$msg  = $_links_messages['title_update'];
		$body = $_links_messages['msg_done'    ];
	} else {
		$msg  = $_links_messages['title_update'];
		$body = $_links_messages['err_invalid' ];
	}
	return array('msg'=>$msg, 'body'=>$body);
}
?>
