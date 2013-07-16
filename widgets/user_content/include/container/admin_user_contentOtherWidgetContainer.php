<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ユーザ作成コンテンツ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_user_contentOtherWidgetContainer.php 3566 2010-09-04 05:29:36Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_user_contentBaseWidgetContainer.php');

class admin_user_contentOtherWidgetContainer extends admin_user_contentBaseWidgetContainer
{
	private $paramObjArray;		// パラメータ保存配列
	
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
		return 'admin_other.tmpl.html';
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
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObjArray, true/*定義ID=0も取得*/);
		
		$defaultLang	= $this->gEnv->getDefaultLanguage();
		$act = $request->trimValueOf('act');

		$topHtml	= $request->valueOf('item_top_html');	// トップ表示用HTML
		$css	= $request->valueOf('item_css');		// タブ用CSS
		$useTab = ($request->trimValueOf('item_use_tab') == 'on') ? 1 : 0;		// タブを使用するかどうか
		$editBySameUserId = ($request->trimValueOf('item_edit_by_same_user_id') == 'on') ? 1 : 0;		// ルームIDと同じユーザIDのユーザに編集許可を与える

		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObjArray, $defConfigId, $paramObj);
				if ($ret){			// 既存データがある場合
					$paramObj->topHtml	= $topHtml;	// トップ表示用HTML
					$paramObj->css	= $css;			// タブ用CSS
					$paramObj->useTab = $useTab;	// タブを使用するかどうか
					$paramObj->editBySameUserId = $editBySameUserId;		// ルームIDと同じユーザIDのユーザに編集許可を与える
					
					// パラメータオブジェクトを更新
					$ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObjArray, $defConfigId, $paramObj);
				} else {
					$paramObj = new stdClass;		// オブジェクトがないときは作成
					$paramObj->topHtml	= $topHtml;	// トップ表示用HTML
					$paramObj->css	= $css;			// タブ用CSS
					$paramObj->useTab = $useTab;	// タブを使用するかどうか
					$paramObj->editBySameUserId = $editBySameUserId;		// ルームIDと同じユーザIDのユーザに編集許可を与える
				
					// パラメータオブジェクトを追加
					$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObjArray, $paramObj);
				}
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値設定
			$css = $this->getParsedTemplateData('default.tmpl.css', array($this, 'makeCss'));// デフォルト用のCSSを取得
			$useTab = 1;	// タブを使用するかどうか
			$editBySameUserId = 0;
			
			// 設定値を取得
			$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObjArray, $defConfigId, $paramObj);
			if ($ret){
				$topHtml	= $paramObj->topHtml;	// トップ表示用HTML
				$css = $paramObj->css;					// タブ用CSS
				if (!is_null($paramObj->useTab)) $useTab = $paramObj->useTab;	// タブを使用するかどうか
				$editBySameUserId = $paramObj->editBySameUserId;		// ルームIDと同じユーザIDのユーザに編集許可を与える
			}
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "top_html", $topHtml);	// トップ表示用HTML
		$this->tmpl->addVar("_widget", "css", $css);		// タブ用CSS
		if (!empty($useTab)) $this->tmpl->addVar("_widget", "use_tab", 'checked');		// タブを使用するかどうか
		if (!empty($editBySameUserId)) $this->tmpl->addVar("_widget", "edit_by_same_user_id", 'checked');		// ルームIDと同じユーザIDのユーザに編集許可を与える
		$this->tmpl->addVar("_widget", "group_id", $defConfigId);		// 現在のグループID
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObjArray);
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
