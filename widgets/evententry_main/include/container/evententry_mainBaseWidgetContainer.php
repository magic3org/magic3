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
	protected static $_canEditEntry;	// 記事が編集可能かどうか
	protected $_pageUrl;		// 現在のページのURL
	protected $_baseUrl;		// ベースURL
	const DATE_RANGE_DELIMITER		= '～';				// 日時範囲用デリミター
	const CSS_FILE = '/style.css';		// CSSファイルのパス

	// 画面
	const TASK_REGIST		= 'regist';			// 参加登録画面
	const TASK_LOGIN		= 'login';			// ログイン画面
	const DEFAULT_TASK		= 'regist';
		
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
	 * テンプレートに前処理
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _preAssign($request, &$param)
	{
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。(注意)別ファイル出力はページ単位のURLなのでタスクに関係なく出力する必要がある。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		// CSSを作成
		$css = '';
//		if ($this->_useCalendar) $css = $this->getParsedTemplateData('calendar.tmpl.css');
		return $css;
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
		return $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::CSS_FILE);		// デフォルトのCSSファイル
	}
}
?>
