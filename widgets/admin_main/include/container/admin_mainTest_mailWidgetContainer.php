<?php
/**
 * コンテナクラス
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
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');

class admin_mainTest_mailWidgetContainer extends admin_mainBaseWidgetContainer
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{	
		return 'test_mail.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$act = $request->trimValueOf('act');
		
		if ($act == 'send'){
		
			$fromAddress = $this->gEnv->getSiteEmail();	// 送信元アドレス
			$toAddress = $fromAddress;
			
			// 管理者に通知を行う場合はBCCアドレスを設定
			$bccAddress = $fromAddress;		// CCメール
			
			// メール件名、本文マクロ
			$mailParam = array();
			$mailParam['ACCOUNT'] = $account;
			$titleParam = array();
			$titleParam[M3_TAG_MACRO_SITE_NAME] = $this->gEnv->getSiteName();			// サイト名
			$titleParam[M3_TAG_MACRO_ACCOUNT]	= $account;							// ログインアカウント
			$ret = $this->gInstance->getMailManager()->sendFormMail(1/*自動送信*/, $this->gEnv->getCurrentWidgetId(), $toAddress, $fromAddress, '', $bccAddress, reg_userCommonDef::MAIL_TMPL_REGIST_USER_AUTO_COMPLETED, $mailParam,
			$this->setGuidanceMsg('送信しました');
		}
	}
}
?>
