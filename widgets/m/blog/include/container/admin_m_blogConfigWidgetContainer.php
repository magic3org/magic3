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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_m_blogConfigWidgetContainer.php 3836 2010-11-17 06:05:07Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_m_blogBaseWidgetContainer.php');

class admin_m_blogConfigWidgetContainer extends admin_m_blogBaseWidgetContainer
{
	const DEFAULT_VIEW_COUNT	= 3;				// デフォルトの表示記事数
	
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
		$defaultLang	= $this->gEnv->getDefaultLanguage();
		
		$act = $request->trimValueOf('act');
		$entryViewCount = $request->trimValueOf('item_entry_view_count');		// 記事表示数
		$entryViewOrder = $request->trimValueOf('item_entry_view_order');		// 記事表示順
		$titleColor = $request->trimValueOf('item_title_color');					// タイトルの背景色
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($entryViewCount, '記事表示順');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$isErr = false;
				
				if (!$isErr){
					if (!$this->_db->updateConfig(self::CF_ENTRY_VIEW_COUNT, $entryViewCount)) $isErr = true;// 記事表示数
				}
				if (!$isErr){
					if (!$this->_db->updateConfig(self::CF_ENTRY_VIEW_ORDER, $entryViewOrder)) $isErr = true;// 記事表示順
				}
				if (!$isErr){
					if (!$this->_db->updateConfig(self::CF_TITLE_COLOR, $titleColor)) $isErr = true;// タイトルの背景色
				}

				if ($isErr){
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				} else {
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					// ブログ定義を読み込む
					$this->_loadConfig($this->_blogId);
					$reloadData = true;		// データの再読み込み
				}
			}
		} else {		// 初期表示の場合
			$reloadData = true;		// データの再読み込み
		}
		// データ再取得
		if ($reloadData){
			$entryViewCount	= $this->_configArray[self::CF_ENTRY_VIEW_COUNT];// 記事表示数
			$entryViewOrder	= $this->_configArray[self::CF_ENTRY_VIEW_ORDER];// 記事表示順
			$titleColor = $this->_configArray[self::CF_TITLE_COLOR];		// タイトルの背景色
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "view_count", $entryViewCount);// 記事表示数
		if (empty($entryViewOrder)){	// 順方向
			$this->tmpl->addVar("_widget", "view_order_inc_selected", 'selected');// 記事表示順
		} else {
			$this->tmpl->addVar("_widget", "view_order_dec_selected", 'selected');// 記事表示順
		}
		$this->tmpl->addVar("_widget", "title_color", $titleColor);// タイトルの背景色
	}
}
?>
