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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() .			'/baseWidgetContainer.php');
 
class skywayWidgetContainer extends BaseWidgetContainer
{
	const DEFAULT_TITLE = 'SkyWayサンプル';			// デフォルトのウィジェットタイトル
	const SKYWAY_CALL = 'skyway_call';				// SKYWAY返答メールフォーマット
	
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
		return 'index.tmpl.html';
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
		// 設定値の取得
		$apiKey = '';				// APIキー
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$apiKey = $paramObj->apiKey;						// APIキー
		}
		
		$act = $request->trimValueOf('act');
		if ($act == 'sendmail'){			// 管理者をコールする場合
			// ##### ウィジェット出力処理中断 ######
			$this->gPage->abortWidget();

			$peerid = $request->trimValueOf('peerid');
			
			// 管理者にコールを通知
			$address = $this->gEnv->getSiteEmail();// 送信元が取得できないときは、システムのデフォルトメールアドレスを使用

			$url = $this->gPage->getDefaultPageUrlByWidget($this->gEnv->getCurrentWidgetId());		// このウィジェットのあるページURL
			if (!empty($peerid)) $url .=  '&peerid=' . $peerid;
			$mailParam = array();
			$mailParam['URL']		= $this->getUrl($url, true);
			$ret = $this->gInstance->getMailManager()->sendFormMail(2/*手動送信*/, $this->gEnv->getCurrentWidgetId(), $address, $address, '', '', self::SKYWAY_CALL, $mailParam);
			
			// フロントへ返す値を設定
			$this->gInstance->getAjaxManager()->addData('result', $ret);		// メール送信結果
			return;
		} else {		// 初期表示
			// URLのパラメータにPeerIDがある場合のみ画面に埋め込む
			$peerid = $request->trimValueOfGet('peerid');
			if (!empty($peerid)) $this->tmpl->addVar("_widget", "peer_id", $peerid);
		}
		$this->tmpl->addVar("_widget", "api_key", $apiKey);				// APIキー
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return self::DEFAULT_TITLE;
	}
	/**
	 * JavascriptファイルをHTMLヘッダ部に設定
	 *
	 * JavascriptファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						Javascriptファイル。出力しない場合は空文字列を設定。
	 */
	function _addScriptFileToHead($request, &$param)
	{
		$scriptUrl = '//cdn.webrtc.ecl.ntt.com/skyway-latest.js';
		return $scriptUrl;
	}
}
?>
