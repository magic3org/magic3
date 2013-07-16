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
 * @version    SVN: $Id: m_blog_searchWidgetContainer.php 3508 2010-08-18 11:05:42Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseMobileWidgetContainer.php');
//require_once($gEnvManager->getCommonPath()			. '/htmlEdit.php');

class m_blog_searchWidgetContainer extends BaseMobileWidgetContainer
{
	const TARGET_WIDGET = 'm/blog';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'ブログ検索';		// デフォルトのウィジェットタイトル名
	
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
		$act = $request->trimValueOf('act');
		if ($act == 'blog_search'){			// ブログ検索のとき
			// キーワード取得
			$keyword = $request->mobileTrimValueOf('keyword');		// 一旦内部コードへ変換
			$keyword = $request->convMobileText($keyword);			// 再度携帯用コードへ変換
			
			// ブログメインに検索結果を表示させる
			$url = $this->gPage->createWidgetCmdUrl(self::TARGET_WIDGET, $this->gEnv->getCurrentWidgetId(), 'act=search&keyword=' . urlencode($keyword));
			$this->redirect($url);
		}
		// パラメータ埋め込み
		$this->tmpl->addVar('_widget', 'url', $this->gEnv->createCurrentPageUrlForMobile());
		$this->tmpl->addVar('_widget', 'act', 'blog_search');
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
