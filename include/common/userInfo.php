<?php
/**
 * ユーザ情報クラス
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
class UserInfo
{
	public $userId;				// ユーザID
	public $account;			// ログインアカウント
	public $name;				// ユーザ名
	public $email;				// Eメール
	public $userType;			// ユーザタイプ
	public $adminWidget;		// アクセス可能ウィジェット(システム運用者の場合)。ウィジェットIDの配列
	public $userTypeOption;			// ユーザタイプオプション
	public $_recordSerial;			// 更新時に使用
	
	// ユーザタイプ
	//const USER_TYPE_GUEST				= -100;				// ゲストユーザ(ログインなし)
	const USER_TYPE_NOT_AUTHENTICATED	= -1;				// 未承認ユーザ
	const USER_TYPE_TMP					= 0;				// 仮ユーザ
	const USER_TYPE_NORMAL				= 10;				// 一般ユーザ
	const USER_TYPE_AUTHOR				= 20;				// 投稿ユーザ
	//const USER_TYPE_EDITOR				= 30;				// 編集ユーザ
	const USER_TYPE_MANAGER				= 50;				// システム運営者(ウィジェットの管理機能が使用可能)(このレベル以上が管理機能が使用できる)
	const USER_TYPE_SYS_ADMIN			= 100;				// システム管理者
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	
	/**
	 * システム管理者権限があるかどうか
	 *
	 * @return bool			true=システム管理者、false=システム管理者以外
	 */
	function isSystemAdmin()
	{
		if ($this->userType == self::USER_TYPE_SYS_ADMIN){
			return true;
		} else {
			return false;
		}
	}
}
?>
