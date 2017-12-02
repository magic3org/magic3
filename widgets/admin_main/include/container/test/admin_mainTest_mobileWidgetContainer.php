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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainTest_mobileWidgetContainer extends admin_mainBaseWidgetContainer
{
	const DETECT_DEVICE_SCRIPT = '/Mobile-Detect-2.8.26/Mobile_Detect.php';		// デバイス判定用スクリプト
	
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
		return 'test/test_mobile.tmpl.html';
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
		echo '<h1>Mobile Test</h1>';
		echo self::DETECT_DEVICE_SCRIPT;
		
		require_once(M3_SYSTEM_LIB_PATH . self::DETECT_DEVICE_SCRIPT);
		
		$detect = new Mobile_Detect;
		
		echo '<h3>Current Device</h3>';
		if ($detect->isMobile()) echo 'Mobile OK<br />';
		if ($detect->isTablet()) echo 'Tablet OK<br />';
		if ($detect->isMobile() && !$detect->isTablet()){		// 小画面デバイスかどうか(モバイルかつタブレットではない)
			echo 'Small Device OK';
		} else {
			echo 'Not small Device';
		}
		
		$userAgents = array(
		'Mozilla/5.0 (Linux; Android 4.0.4; Desire HD Build/IMM76D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Mobile Safari/535.19',
		'BlackBerry7100i/4.1.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/103',
		'Mozilla/5.0 (Linux; Android 4.1.1; Nexus 7 Build/JRO03D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Safari/535.19',			// タブレット
		);
		foreach($userAgents as $userAgent){
 
			$detect->setUserAgent($userAgent);
 
			echo '<h3>' .$userAgent . '</h3>';
			if ($detect->isMobile()) echo 'Mobile OK<br />';
			if ($detect->isTablet()) echo 'Tablet OK<br />';
			if ($detect->isMobile() && !$detect->isTablet()){		// 小画面デバイスかどうか(モバイルかつタブレットではない)
				echo 'Small Device OK';
			} else {
				echo 'Not small Device';
			}
		}
	}
}
?>
