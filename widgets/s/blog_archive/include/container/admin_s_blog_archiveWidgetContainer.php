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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_s_blog_archiveWidgetContainer.php 4752 2012-03-14 04:42:10Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_s_blog_archiveWidgetContainer extends BaseAdminWidgetContainer
{
	const DEFAULT_LIST_TITLE = 'ブログアーカイブ';			// デフォルトのリストタイトル
	
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
		return 'admin.tmpl.html';
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
		if ($act == 'update'){		// 設定更新のとき
			// 入力値を取得
			$title	= $request->valueOf('item_title');			// タイトル
			$theme		= $request->trimValueOf('item_theme');		// メニューのテーマ
			$insetList	= ($request->trimValueOf('item_inset_list') == 'on') ? 1 : 0;		// インセットリスト形式で表示するかどうか
		
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj->title	= $title;		// タイトル
				$paramObj->theme		= $theme;		// メニューのテーマ
				$paramObj->insetList	= $insetList;		// インセットリスト形式で表示するかどうか
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}				
			}
			$this->gPage->updateParentWindow();// 親ウィンドウを更新
		} else {		// 初期表示の場合
			// デフォルト値設定
			$title = self::DEFAULT_LIST_TITLE;	// タイトル
			$theme = 'c';		// メニューのテーマ
			$insetList = 1;		// インセットリスト形式で表示するかどうか
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$title	= $paramObj->title;			// タイトル
				$theme = $paramObj->theme;		// メニューのテーマ
				$insetList	= $paramObj->insetList;		// インセットリスト形式で表示するかどうか
			}
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "title",	$title);
		$this->tmpl->addVar("_widget", "theme",	$this->convertToDispString($theme));				// メニューのテーマ
		if (!empty($insetList)) $this->tmpl->addVar("_widget", "inset_list_checked",	'checked'); // インセットリスト形式で表示するかどうか
	}
}
?>
