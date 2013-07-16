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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_g_analyticsWidgetContainer.php 3719 2010-10-19 08:39:44Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class m_g_analyticsWidgetContainer extends BaseWidgetContainer
{
	private $account;		// Google AnalyticsのプロファイルのID
	const ACCOUNT_HEAD = 'MO-';		// 携帯用アカウントヘッダ
	
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
		return 'main.tmpl.html';
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
		$this->account = '';	// Google AnalyticsのプロファイルのID
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->account	= $paramObj->account;
		}

		// アカウント番号が空のときは出力しない
		if (empty($this->account)){
			$this->cancelParse();
		} else {
			$imageUrl = $this->googleAnalyticsGetImageUrl();
			$this->tmpl->addVar("_widget", "image",	$imageUrl);
		}
	}
	function googleAnalyticsGetImageUrl()
	{
		//global $GA_ACCOUNT, $GA_PIXEL;
		$GA_ACCOUNT = self::ACCOUNT_HEAD . $this->account;
		$GA_PIXEL = $this->gEnv->getCurrentWidgetRootUrl() . '/ga.php';
		
		$url = "";
		$url .= $GA_PIXEL . "?";
		$url .= "utmac=" . $GA_ACCOUNT;
		$url .= "&utmn=" . rand(0, 0x7fffffff);
		$referer = $_SERVER["HTTP_REFERER"];
		$query = $_SERVER["QUERY_STRING"];
		$path = $_SERVER["REQUEST_URI"];
		if (empty($referer)) {
		  $referer = "-";
		}
		$url .= "&utmr=" . urlencode($referer);
		if (!empty($path)) {
		  $url .= "&utmp=" . urlencode($path);
		}
		$url .= "&guid=ON";
		return str_replace("&", "&amp;", $url);
	}
}
?>
