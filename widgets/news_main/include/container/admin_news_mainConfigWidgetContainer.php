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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getWidgetContainerPath('news_main') . '/admin_news_mainBaseWidgetContainer.php');

class admin_news_mainConfigWidgetContainer extends admin_news_mainBaseWidgetContainer
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
		return 'admin_config.tmpl.html';
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
		$defaultMessage	= $request->trimValueOf('item_default_message');		// デフォルトメッセージ
		$dateFormat		= $request->trimValueOf('item_date_format');			// 日時フォーマット
		$layoutListItem = $request->trimValueOf('item_layout_list_item');		// リスト項目レイアウト
		$msgFilterActiveContent	= $request->trimCheckedValueOf('item_msg_filter_active_content');		// メッセージ取得フィルター(公開コンテンツのみ取得)
		
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$ret = self::$_mainDb->updateConfig(newsCommonDef::FD_DEFAULT_MESSAGE, $defaultMessage); // デフォルトメッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(newsCommonDef::FD_DATE_FORMAT, $dateFormat);// 日時フォーマット
				if ($ret) $ret = self::$_mainDb->updateConfig(newsCommonDef::FD_LAYOUT_LIST_ITEM, $layoutListItem);		// リスト項目レイアウト
				if ($ret) $ret = self::$_mainDb->updateConfig(newsCommonDef::FD_MSG_FILTER_ACTIVE_CONTENT, $msgFilterActiveContent);	// メッセージ取得フィルター(公開コンテンツのみ取得)

				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$reloadData = true;		// データの再ロード
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {		// 初期表示の場合
			$reloadData = true;		// データの再ロード
		}
		// データ再取得
		if ($reloadData){
			$defaultMessage	= self::$_mainDb->getConfig(newsCommonDef::FD_DEFAULT_MESSAGE);// デフォルトメッセージ
			$dateFormat		= self::$_mainDb->getConfig(newsCommonDef::FD_DATE_FORMAT);// 日時フォーマット
			$layoutListItem	= self::$_mainDb->getConfig(newsCommonDef::FD_LAYOUT_LIST_ITEM);// リスト項目レイアウト
			$msgFilterActiveContent	= self::$_mainDb->getConfig(newsCommonDef::FD_MSG_FILTER_ACTIVE_CONTENT);	// メッセージ取得フィルター(公開コンテンツのみ取得)
		}
		
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "default_message",	$this->convertToDispString($defaultMessage));// デフォルトメッセージ
		$this->tmpl->addVar("_widget", "date_format",		$this->convertToDispString($dateFormat));// 日時フォーマット
		$this->tmpl->addVar("_widget", "layout_list_item",	$this->convertToDispString($layoutListItem));// リスト項目レイアウト
		$this->tmpl->addVar("_widget", "msg_filter_active_content_checked", $this->convertToCheckedString($msgFilterActiveContent));	// メッセージ取得フィルター(公開コンテンツのみ取得)
	}
}
?>
