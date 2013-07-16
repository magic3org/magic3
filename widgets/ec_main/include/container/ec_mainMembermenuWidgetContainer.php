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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ec_mainMembermenuWidgetContainer.php 5440 2012-12-08 09:37:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainMembermenuWidgetContainer extends ec_mainBaseWidgetContainer
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
		return 'membermenu.tmpl.html';
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
		// パスワード変更画面へのリンク
		$this->tmpl->addVar("_widget", "changepwd_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=changepwd', true));
		
		// 会員お知らせ画面へのリンク
		$this->tmpl->addVar("_widget", "member_notice_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=membernotice', true));
		
		// 会員情報画面へのリンク
		$this->tmpl->addVar("_widget", "member_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=memberinfo', true));
		
		// 購入履歴画面へのリンク
		$this->tmpl->addVar("_widget", "purchasehistory_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=purchasehistory', true));
		
		// 購入画面へのリンク
		$this->tmpl->addVar("_widget", "order_url", $this->getUrl($this->gEnv->createCurrentPageUrl() . '&task=cart', true));		// カートから遷移する
	}
}
?>
