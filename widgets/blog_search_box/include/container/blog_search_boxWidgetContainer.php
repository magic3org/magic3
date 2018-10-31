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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');

class blog_search_boxWidgetContainer extends BaseWidgetContainer
{
	const TARGET_WIDGET = 'blog_main';		// 呼び出しウィジェットID
	const THIS_WIDGET_ID = 'blog_search_box';		// ウィジェットID
	const DEFAULT_TITLE = 'ブログ検索';		// デフォルトのウィジェットタイトル名
	const WORDPRESS_WIDGET_CLASS = 'widget_search';			// WordPress用ウィジェットクラス名
	
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
		if ($this->_renderType == M3_RENDER_WORDPRESS){		// WordPressテンプレートの場合
			return '';			// テンプレートなし
		} else if ($this->_renderType == M3_RENDER_BOOTSTRAP){
			return 'index_bootstrap.tmpl.html';
		} else {
			return 'index.tmpl.html';
		}
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
		if ($this->_renderType == M3_RENDER_WORDPRESS){		// WordPressテンプレートの場合
			get_search_form();
			
			// ##### ウィジェットクラス名追加 #####
			$this->gEnv->setWpWidgetClass(self::WORDPRESS_WIDGET_CLASS);
			return;
		}
		
		$act = $request->trimValueOf('act');
		if ($act == 'blog_search'){			// ブログ検索のとき
			// キーワード取得
			$keyword = $request->trimValueOf('keyword');
			
			// ブログメインに検索結果を表示させる
			$url = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, self::THIS_WIDGET_ID, 'act=search&keyword=' . urlencode($keyword));
			$this->gPage->redirect($url);
		}
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
