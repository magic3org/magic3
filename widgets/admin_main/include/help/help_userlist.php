<?php
/**
 * ヘルプリソースファイル
 * index.php
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: help_userlist.php 5892 2013-04-01 05:50:24Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCommonPath()	. '/helpConv.php' );

class help_userlist extends HelpConv
{
	/**
	 * ヘルプ用データを設定
	 *
	 * @return array 				ヘルプ用データ
	 */
	function _setData()
	{
		// ########## ウィジェット表示調整 ##########
		$helpData = array(
			'userlist' => array(	
				'title' =>	$this->_('User List'),	// ユーザ一覧
				'body' =>	$this->_('User listed in the system.')		// システムで一元管理するユーザの一覧です。
			),
			'userlist_check' => array(	
				'title' =>	$this->_('Checkbox to Select'),	// 選択用チェックボックス
				'body' =>	$this->_('Select items to edit or delete by using checkboxes.')		// 編集や削除を行う項目を選択します。
			),
			'userlist_account' => array(	
				'title' =>	$this->_('Login Account'),	// ログインアカウント
				'body' =>	$this->_('Account to login.<br />You can enter by email address format.')		// ログイン時に使用するアカウントです。<br />メールアドレス形式のアカウントも使用できます。
			),
			'userlist_pwd' => array(	
				'title' =>	$this->_('Password'),	// パスワード
				'body' =>	$this->_('Password to login.')		// ログイン時に使用するパスワードです。
			),
			'userlist_name' => array(	
				'title' =>	$this->_('Name'),	// 名前
				'body' =>	$this->_('User name displayed on the web page.')		// 画面上に表示されるユーザの名前です。
			),
			'userlist_email' => array(	
				'title' =>	$this->_('Email'),	// Eメール
				'body' =>	$this->_('Email address for user.<br />It is used for such as sending password.')		// ユーザが使用するEメールアドレスです。パスワード再送信等に使用します。
			),
			'userlist_usertype' => array(	
				'title' =>	$this->_('User Type'),	// ユーザ種別
				'body' =>	$this->_('User type in the system. <br /><strong>Administrator</strong> - It can use all administration functions.<br /><strong>Site Manager</strong> - It can use limited administration functions.<br /><strong>Author</strong> - It can create entry.<br /><strong>Normal</strong> - It can login the site.<br /><strong>Temporary</strong> - It is registered by itself, but it does not login yet. If it logins once, it changes to \'Normal\' user.<br /><strong>Not Authenticated</strong> - It requests to become user, but it does not have permissions.')		// ユーザのタイプです。<br />管理者 - すべての管理機能が使用可能です。<br />運営者 - 制限された管理機能が使用可能です。<br />投稿者 - サイトにログインでき、ブログ等の記事の投稿が可能です。<br />一般 - サイトにログインできます。<br />仮登録 - 一般機能からユーザ登録を行い、まだログインしていないユーザです。一度ログインすると一般ユーザに変更されます。<br />未承認 - 承認を申請し、まだ承認されていないユーザです。
			),
			'userlist_admin' => array(	
				'title' =>	$this->_('Administration Authority'),	// 管理権限
				'body' =>	$this->_('Permission to use administration functions.<br />The user can use functions if it checked.')		// 管理機能(管理画面)が使用可能かどうかの設定です。<br />チェックが入っていると管理機能が使用可能です。
			),
			'userlist_login' => array(	
				'title' =>	$this->_('Login Enable'),	// ログイン可
				'body' =>	$this->_('Permission to login.<br />The user can login if it checked.<br />Administrator can not be disable to login.')		// ログインを許可するかどうかの設定です。<br />チェックが入っているとログイン可能です。<br />誤操作防止のため、管理者はログイン不可にできません。
			),
			'userlist_login_count' => array(	
				'title' =>	$this->_('Login Count'),	// ログイン回数
				'body' =>	$this->_('Count of user login.')		// ログインした回数です。
			),
			'userlist_active_term' => array(	
				'title' =>	$this->_('Active Term'),	// 有効期間
				'body' =>	$this->_('Active term of the account. If you set blank, the account is no limited.')		// アカウントが有効な期間を設定します。空の場合は制限なしを示します。
			),
			'userlist_user_group' => array(	
				'title' =>	$this->_('User Group'),	// ユーザグループ
				'body' =>	$this->_('Group user belong to.')		// ユーザが所属するグループです。
			),
			'userlist_buttons' => array(	
				'title' =>	$this->_('Action Buttons'),	// 操作ボタン
				'body' =>	$this->_('<strong>New Button</strong> - Add new user.<br /><strong>Edit Button</strong> - Edit user configuration.<br />Select the user by using the left checkbox.<br /><strong>Delete Button</strong> - Delete user.<br />Select the user by using the left checkbox.')		// 新規ボタン - 新規ユーザを追加します。<br />編集ボタン - 選択されているユーザを編集します。<br />ユーザを選択するには、一覧の左端のチェックボックスにチェックを入れます。<br />削除ボタン - 選択されているユーザを削除します。<br />ユーザを選択するには、一覧の左端のチェックボックスにチェックを入れます。
			),
			'userlist_new_btn' => array(	
				'title' =>	$this->_('New Button'),	// 新規ボタン
				'body' =>	$this->_('Add new user.')		// 新規ユーザを追加します。
			),
			'userlist_edit_btn' => array(	
				'title' =>	$this->_('Edit Button'),	// 編集ボタン
				'body' =>	$this->_('Edit user configuration.<br />Select the user by using the left checkbox.')		// 選択されているユーザを編集します。<br />ユーザを選択するには、一覧の左端のチェックボックスにチェックを入れます。
			),
			'userlist_del_btn' => array(	
				'title' =>	$this->_('Delete Button'),	// 削除ボタン
				'body' =>	$this->_('Delete user.<br />Select the user by using the left checkbox.')		// 選択されているユーザを削除します。<br />ユーザを選択するには、一覧の左端のチェックボックスにチェックを入れます。
			),
			'userlist_ret_btn' => array(	
				'title' =>	$this->_('Go back Button'),	// 戻るボタン
				'body' =>	$this->_('Go back to user list.')		// ユーザ一覧へ戻ります。
			),
		);
		return $helpData;
	}
}
?>
