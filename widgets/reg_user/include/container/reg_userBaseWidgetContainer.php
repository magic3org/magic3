<?php
/**
 * index.php用コンテナクラス
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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class reg_userBaseWidgetContainer extends BaseWidgetContainer
{
//	private $_userId;			// 現在のユーザ
	private $cssFilePath = array();			// CSSファイル
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	const EMAIL_LOGIN_URL		= '&task=emaillogin&account=%s&pwd=%s';		// Eメールからのログイン用URL
	// 画面
	const TASK_PROFILE = 'profile';			// プロフィール画面
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
//		$this->_userId = $this->gEnv->getCurrentUserId();
		
		// CSSファイルの追加
		if ($this->_renderType == M3_RENDER_BOOTSTRAP){			// Bootstrap型テンプレートのとき
		} else {
			$this->cssFilePath[] = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);		// CSSファイル
		}
	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		//return $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);
		return $this->cssFilePath;
	}
	/**
	 * Eメール送信元を取得
	 *
	 * @return string		送信元アドレス
	 */
	function getFromAddress()
	{
		$address = $this->gEnv->getSiteEmail();// サイトのメールアドレス
		return $address;
	}
}
?>
