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
 * @copyright  Copyright 2006-2018 Magic3 Project.
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
	public $userTypeOption;			// ユーザタイプオプション文字列
	public $userOptType;			// ユーザオプションタイプ(page_manager等)
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
	// ユーザオプションタイプ
	const USER_OPT_TYPE_PAGE_MANAGER	= 'page_manager';	// ページ運用者(システム運用者の制限ありユーザ)
	
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
	/**
	 * パーソナルモードユーザかどうか
	 *
	 * @return bool			true=パーソナルモードユーザ、false=パーソナルモードユーザではない
	 */
	function isPersonal()
	{
		$isPersonal = true;
		if ($this->userType == self::USER_TYPE_SYS_ADMIN){	// システム管理者の場合
			$isPersonal = false;
		} else if ($this->userType == self::USER_TYPE_MANAGER && empty($this->userOptType)){			// システム運用者でユーザタイプオプションがない場合
			$isPersonal = false;
		}
		return $isPersonal;
	}
	/**
	 * ユーザタイプオプション文字列を解析し、ユーザオプションタイプを取得
	 *
	 * @return string		ユーザオプションタイプ。取得できない場合は空文字列。
	 */
	static function parseUserTypeOption($option)
	{
		if (empty($option)) return '';
		
		$userOptionArray = explode(M3_USER_TYPE_OPTION_SEPARATOR, $option);
		for ($i = 0; $i < count($userOptionArray); $i++){
			$userOption = trim($userOptionArray[$i]);
			if (!empty($userOption)){
				// 「=」がない文字列の場合はユーザオプションタイプと認識
				$pos = strpos($userOption, '=');
				if ($pos === false) return $userOption;
			}
		}
		return '';
	}
}
?>
