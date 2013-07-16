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
 * @version    SVN: $Id: ec_mainAgreememberWidgetContainer.php 5440 2012-12-08 09:37:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/ec_mainBaseWidgetContainer.php');

class ec_mainAgreememberWidgetContainer extends ec_mainBaseWidgetContainer
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
		return 'agreemember.tmpl.html';
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
		if ($act == 'agree'){			// 会員規約に同意のとき
			// セッションの会員規約同意状態を更新
			$this->setWidgetSession(photo_shopCommonDef::SK_AGREE_MEMBER, 1);
			
			// 会員登録画面へ遷移
			$regmemberPage = $this->gEnv->createCurrentPageUrl() . '&task=regmember';
			$this->gPage->redirect($regmemberPage);
			return;
		}
		// 会員規約を取得
		$content = '';
		if (self::$_mainDb->getContentByKey(photo_shopCommonDef::AGREE_MEMBER_TEXT_KEY, $this->_langId, $row)){
			$content = $row['cn_html'];
		}
		
		// 登録したキーワードを変換
		$this->gInstance->getTextConvManager()->convByKeyValue($content, $content, true/*改行コーをbrタグに変換*/);
		
		$this->tmpl->addVar("_widget", "content", $content);		// 会員規約
		$this->tmpl->addVar("_widget", "cancel_url", $this->getUrl($this->gEnv->getRootUrl(), true));		// キャンセル用URL(トップページ)
	}
}
?>
