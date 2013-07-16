<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ユーザ作成コンテンツ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: user_contentBaseWidgetContainer.php 3011 2010-04-08 04:06:37Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/user_contentDb.php');

class user_contentBaseWidgetContainer extends BaseWidgetContainer
{
	protected $_localDb;			// DB接続オブジェクト
	protected $_headCss;		// ヘッダに設定するCSS
	protected static $_canEditContent;	// コンテンツが編集可能かどうか
	protected static $_paramObj;		// ウィジェットパラメータオブジェクト
	
	// 画面
	const TASK_TOP = 'top';			// トップ画面
	const TASK_CONTENT = 'content';			// コンテンツ編集画面
	const TASK_CONTENT_DETAIL = 'content_detail';			// コンテンツ編集画面詳細
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->_localDb = new user_contentDb();
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
		// タブ用CSSを取得
		if (!empty(self::$_paramObj)){
			$this->_headCss = self::$_paramObj->css;					// タブ用CSS
		}
		
		// ヘッダ用CSSが作成されていないときは、デフォルトのCSSを取得
		if (empty($this->_headCss)){
			$this->_headCss = $this->getParsedTemplateData('default.tmpl.css', array($this, 'makeCss'));// デフォルト用のCSSを取得
		}
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		return $this->_headCss;
	}
	/**
	 * BBS定義値をDBから取得
	 *
	 * @return bool			true=取得成功、false=取得失敗
	 */
	function _loadConfig()
	{
		$this->_configArray = array();

		// BBS定義を読み込み
		$ret = $this->_localDb->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['tg_id'];
				$value = $rows[$i]['tg_value'];
				$this->_configArray[$key] = $value;
			}
		}
		return $ret;
	}
	/**
	 * CSSデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeCss($tmpl)
	{
		$tmpl->addVar("_tmpl", "widget_url",	$this->gEnv->getCurrentWidgetRootUrl());		// ウィジェットのURL
	}
}
?>
