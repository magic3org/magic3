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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/reg_userCommonDef.php');

class reg_userBaseWidgetContainer extends BaseWidgetContainer
{
	private $cssFilePath = array();			// CSSファイル
	protected $_authType;			// 承認タイプ
	const CSS_FILE = '/style.css';		// CSSファイルのパス
	const EMAIL_LOGIN_URL		= '&task=emaillogin&account=%s&pwd=%s';		// Eメールからのログイン用URL
	
	// 画面
	const TASK_REGIST			= 'regist';			// 会員登録
	const TASK_LOGIN			= 'login';			// ログイン
	const TASK_EMAIL_LOGIN		= 'emaillogin';		// Eメールからのログイン
	const TASK_SEND_PASSWORD	= 'sendpwd';		// パスワード送信
	const TASK_PROFILE			= 'profile';		// プロフィール画面(要ログイン)
	const TASK_CHANGE_PASSWORD	= 'changepwd';		// パスワード変更(要ログイン)
	const DEFAULT_TASK			= 'login';			// デフォルト画面
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// CSSファイルの追加
		if ($this->_renderType == M3_RENDER_BOOTSTRAP){			// Bootstrap型テンプレートのとき
		} else {
			$this->cssFilePath[] = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);		// CSSファイル
		}
	}
	/**
	 * ウィジェット初期化
	 *
	 * 共通パラメータの初期化や、以下のパターンでウィジェット出力方法の変更を行う。
	 * ・組み込みの_setTemplate(),_assign()を使用
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return 								なし
	 */
	function _preInit($request)
	{
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->_authType	= $paramObj->authType;			// 承認タイプ
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
