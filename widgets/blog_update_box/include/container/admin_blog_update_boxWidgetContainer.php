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
 * @version    SVN: $Id: admin_blog_update_boxWidgetContainer.php 3918 2011-01-01 03:20:22Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_blog_update_boxWidgetContainer extends BaseAdminWidgetContainer
{
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	
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
			$itemCount	= $request->valueOf('item_count');			// 表示項目数
			$useRss = ($request->trimValueOf('item_use_rss') == 'on') ? 1 : 0;		// RSS配信を行うかどうか
			$optionPassage = ($request->trimValueOf('item_option_passage') == 'on') ? 1 : 0;		// 表示オプション(経過日時)
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj->itemCount	= $itemCount;
				$paramObj->useRss	= $useRss;
				$paramObj->optionPassage	= $optionPassage;		// 表示オプション(経過日時)
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値設定
			$itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
			$useRss = 1;							// RSS配信を行うかどうか
			$optionPassage = 0;						// 表示オプション(経過日時)
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$itemCount	= $paramObj->itemCount;
				$useRss		= $paramObj->useRss;// RSS配信を行うかどうか
				if (!isset($useRss)) $useRss = 1;
				$optionPassage	= $paramObj->optionPassage;		// 表示オプション(経過日時)
				if (!isset($optionPassage)) $optionPassage = 0;
			}
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "item_count",	$itemCount);
		$checked = '';
		if ($useRss) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_rss",	$checked);// RSS配信を行うかどうか
		$checked = '';
		if ($optionPassage) $checked = 'checked';
		$this->tmpl->addVar("_widget", "option_passage",	$checked);// 表示オプション(経過日時)
	}
}
?>
