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
 * @version    SVN: $Id: custom_search_boxWidgetContainer.php 3596 2010-09-16 07:38:11Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class custom_search_boxWidgetContainer extends BaseWidgetContainer
{
	private $langId;		// 現在の言語
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'カスタム検索連携';			// デフォルトのウィジェットタイトル
	const MESSAGE_NO_INPUT	= 'キーワードを入力してください';
	const DEFAULT_SEARCH_ACT = 'custom_search';		// 検索実行処理
	const TARGET_WIDGET = 'custom_search';		// 呼び出しウィジェットID
	
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){		// 定義データが取得できないとき
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// 表示設定を取得
		$searchTextId = $targetObj->searchTextId;		// 検索用テキストフィールドのタグID
		$searchButtonId = $targetObj->searchButtonId;		// 検索用ボタンのタグID
		$searchResetId = $targetObj->searchResetId;		// 検索エリアリセットボタンのタグID
		$searchTemplate = $targetObj->searchTemplate;		// 検索用テンプレート
		$searchFormId = $this->gEnv->getCurrentWidgetId() . '_' . $configId . '_form';		// フォームのID
		
		// 入力値を取得
		$keyword = $request->trimValueOf('keyword');		// 検索キーワード
		$act = $request->trimValueOf('act');
		
		if ($act == self::DEFAULT_SEARCH_ACT){		// 検索実行
			// 検索キーワードが空以外の場合は、キーワードログを残す
			if (empty($keyword)){
				$message = self::MESSAGE_NO_INPUT;
			} else {
				// カスタム検索に検索結果を表示させる
				$url = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, $this->gEnv->getCurrentWidgetId(), 'act=custom_search&keyword=' . urlencode($keyword));
				$this->gPage->redirect($url);
			}
		}
		// メッセージを表示
		if (!empty($message)){
			$this->tmpl->setAttribute('message', 'visibility', 'visible');
			$this->tmpl->addVar("message", "message", $this->convertToDispString($message));
		}
		
		// 表示データ埋め込み
		$this->tmpl->addVar("_widget", "page_sub",	$this->gEnv->getCurrentPageSubId());		// ページサブID
		$this->tmpl->addVar("_widget", "html",	$searchTemplate);
		$this->tmpl->addVar("_widget", "search_text_id",	$searchTextId);		// 検索用テキストフィールドのタグID
		$this->tmpl->addVar("_widget", "search_button_id",	$searchButtonId);		// 検索用ボタンのタグID
		$this->tmpl->addVar("_widget", "search_reset_id",	$searchResetId);		// 検索エリアリセットボタンのタグID
		$this->tmpl->addVar("_widget", "search_form_id",	$searchFormId);		// 検索フォームのタグID
		$this->tmpl->addVar("_widget", "search_act",	self::DEFAULT_SEARCH_ACT);		// 検索実行処理
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
