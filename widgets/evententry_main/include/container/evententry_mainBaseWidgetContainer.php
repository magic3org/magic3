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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/evententry_mainCommonDef.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/evententry_mainDb.php');

class evententry_mainBaseWidgetContainer extends BaseWidgetContainer
{
	protected static $_mainDb;			// DB接続オブジェクト
	protected static $_configArray;		// イベント定義値
	protected static $_paramObj;		// ウィジェットパラメータオブジェクト
	protected $_pageUrl;		// 現在のページのURL
	protected $_baseUrl;		// ベースURL

	const DEFAULT_TITLE = 'イベント予約';		// デフォルトのウィジェットタイトル名
	
	// 画面
	const TASK_REQUEST		= 'request';			// 参加登録画面
	const TASK_LOGIN		= 'login';			// ログイン画面
	const DEFAULT_TASK		= 'top';			// トップ画面
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// サブウィジェット起動のときだけ初期処理実行
		if ($this->gEnv->getIsSubWidget()){
			// DBオブジェクト作成
			if (!isset(self::$_mainDb)) self::$_mainDb = new evententry_mainDb();
		
			// イベント定義を読み込む
			if (!isset(self::$_configArray)) self::$_configArray = evententry_mainCommonDef::loadConfig(self::$_mainDb);
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
		// URLパラメータ取得
		$this->addOptionUrlParam(M3_REQUEST_PARAM_OPEN_BY, $this->_openBy);		// ウィンドウオープンタイプ
		
		// 共通パラメータ初期化
		$this->_pageUrl = $this->gEnv->createCurrentPageUrl();		// 現在のページのURL
		$this->_baseUrl = $this->getUrlWithOptionParam();			// ベースURL(オプション付き)
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
}
?>
