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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_remotecontentWidgetContainer extends BaseWidgetContainer
{
	const DEFAULT_TITLE = 'リモート表示コンテンツ';		// デフォルトのウィジェットタイトル名
		
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
//		if ($this->useBootstrap){
//			return 'index_bs.tmpl.html';
//		} else {
			return 'index.tmpl.html';
//		}
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

		// 画面に埋め込む
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "login_count", $loginCount);
		$this->tmpl->addVar("_widget", "avatar_image", $iconTag);
		// 前回ログイン日時。年が同じ場合は省略。
		if (intval(date('Y', strtotime($loginDt))) == intval(date('Y'))){
			$this->tmpl->addVar("_widget", "login_dt", $this->convertToDispDateTime($loginDt, 11/*年省略,0なし年月*/, 10/*時分表示*/));
		} else {
			$this->tmpl->addVar("_widget", "login_dt", $this->convertToDispDateTime($loginDt, 0, 10/*時分表示*/));
		}
		
		$this->tmpl->addVar("_widget", "user_detail_url", $this->convertUrlToHtmlEntity($userDetailUrl));	// ユーザ詳細画面URL
		$this->tmpl->addVar("_widget", "login_status_url", $this->convertUrlToHtmlEntity($loginStatusUrl));	// ログイン状況画面URL
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
