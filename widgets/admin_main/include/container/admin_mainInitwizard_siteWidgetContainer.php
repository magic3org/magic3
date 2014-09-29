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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainInitwizardBaseWidgetContainer.php');

class admin_mainInitwizard_siteWidgetContainer extends admin_mainInitwizardBaseWidgetContainer
{
	// DB定義値
	const CF_SITE_IN_PUBLIC = 'site_in_public';			// サイト公開状況
	
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
		return 'initwizard_site.tmpl.html';
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
		// デフォルト値取得
		$this->langId		= $this->gEnv->getDefaultLanguage();
		
		$act = $request->trimValueOf('act');
		$siteName			= $request->trimValueOf('site_name');		// サイト名称
		$siteEmail			= trim($request->valueOf('site_email'));		// サイトEメール
		$siteDescription 	= $request->trimValueOf('site_description');		// サイト要約
		$siteKeyword		= $request->trimValueOf('site_keyword');		// サイトキーワード
		$siteOpen 			= $request->trimCheckedValueOf('site_open');		// サイト公開状態
		
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
			$ret = $this->_mainDb->updateSiteDef($this->langId, M3_TB_FIELD_SITE_NAME, $siteName);		// サイト名
			if ($ret) $ret = $this->_mainDb->updateSiteDef($this->langId, M3_TB_FIELD_SITE_TITLE, $siteName);	// 画面タイトル
			if ($ret) $ret = $this->_mainDb->updateSiteDef($this->langId, M3_TB_FIELD_SITE_EMAIL, $siteEmail);	// Eメール
			if ($ret) $ret = $this->_mainDb->updateSiteDef($this->langId, M3_TB_FIELD_SITE_DESCRIPTION, $siteDescription);		// サイト説明
			if ($ret) $ret = $this->_mainDb->updateSiteDef($this->langId, M3_TB_FIELD_SITE_KEYWORDS, $siteKeyword);		// 検索キーワード
			if ($ret) $ret = $this->_mainDb->updateSystemConfig(self::CF_SITE_IN_PUBLIC, $siteOpen);	// サイト公開状態
			if ($ret){
				// 次の画面へ遷移
				$this->_redirectNextTask();
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');			// データ更新に失敗しました
			}
		} else {
			$reloadData = true;
		}
		
		if ($reloadData){		// データ再取得のとき
			$siteName			= $this->_mainDb->getSiteDef($this->langId, M3_TB_FIELD_SITE_NAME);		// サイト名
			$siteEmail			= $this->_mainDb->getSiteDef($this->langId, M3_TB_FIELD_SITE_EMAIL);
			$siteDescription	= $this->_mainDb->getSiteDef($this->langId, M3_TB_FIELD_SITE_DESCRIPTION);		// サイト要約
			$siteKeyword		= $this->_mainDb->getSiteDef($this->langId, M3_TB_FIELD_SITE_KEYWORDS);		// サイトキーワード
			$siteOpen 			= $this->gSystem->siteInPublic();			// サイト公開状態
		}

		$this->tmpl->addVar("_widget", "site_name",			$this->convertToDispString($siteName));		// サイト名
		$this->tmpl->addVar("_widget", "site_email",		$this->convertToDispString($siteEmail));
		$this->tmpl->addVar("_widget", "site_description",	$this->convertToDispString($siteDescription));
		$this->tmpl->addVar("_widget", "site_keyword",		$this->convertToDispString($siteKeyword));
		$this->tmpl->addVar("_widget", "site_open_checked",	$this->convertToCheckedString($siteOpen));		// サイト公開状態
	}
}
?>
