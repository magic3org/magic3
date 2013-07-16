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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: c_updateinfoWidgetContainer.php 2692 2009-12-15 09:42:40Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/updateinfoDb.php');

class c_updateinfoWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
			
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new updateinfoDb();
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
		return '';		// テンプレートは使用しない
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
		$retValue = '0';		// 実行結果
		$act = $request->trimValueOf('act');
		$type = $request->trimValueOf('content_type');
		$serverId = $request->trimValueOf('server_id');		// サーバID
		$registDt = $request->trimValueOf('regist_dt');		// 登録情報を有効にする日時
		$name = $request->trimValueOf('content_name');		// コンテンツ名
		$link = $request->trimValueOf('content_link');		// コンテンツへのリンク
		$contentDt = $request->trimValueOf('content_dt');	// コンテンツ更新日時
		$message = $request->trimValueOf('message');		// 表示メッセージ
		$siteName = $request->trimValueOf('site_name');		// サイト名
		$siteLink = $request->trimValueOf('site_link');		// サイトリンク
		if ($act == 'regist'){			// データ登録のとき
			$ret = $this->db->addNews($type, $serverId, $registDt, $name, $link, $contentDt, $message, $siteName, $siteLink);
			if ($ret) $retValue = '1';/*正常終了*/
		}
		
		// 実行結果を返す
		$xmlstr = $this->gPage->getDefaultXmlDeclaration() . '<root></root>';
		$sxe = new SimpleXMLElement($xmlstr);
		$status = $sxe->addChild('status', $retValue);
		echo $sxe->asXML();
	}
}
?>
