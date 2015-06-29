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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainInitwizardBaseWidgetContainer.php');

class admin_mainInitwizard_accesspointWidgetContainer extends admin_mainInitwizardBaseWidgetContainer
{
	const MENU_ID = 'admin_menu';		// メニュー変換対象メニューバーID
	
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
		return 'initwizard_accesspoint.tmpl.html';
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
		$siteOpenPc			= $request->trimCheckedValueOf('site_open_pc');			// PC用サイトの公開状況
		$siteOpenSmartphone = $request->trimCheckedValueOf('site_open_smartphone');	// スマートフォン用サイトの公開状況
		$siteOpenMobile		= $request->trimCheckedValueOf('site_open_mobile');	// 携帯用サイトの公開状況
	
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
			$ret = $this->updateActiveAccessPoint(0/*PC*/, $siteOpenPc);
			$this->setMenuItemVisible(0/*PC*/, $siteOpenPc);
			if ($ret){
				$ret = $this->updateActiveAccessPoint(2/*スマートフォン*/, $siteOpenSmartphone);
				$this->setMenuItemVisible(2/*スマートフォン*/, $siteOpenSmartphone);
			}
			if ($ret){
				$ret = $this->updateActiveAccessPoint(1/*携帯*/, $siteOpenMobile);
				$this->setMenuItemVisible(1/*携帯*/, $siteOpenMobile);
			}
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
			$siteOpenPc			= $this->isActiveAccessPoint(0/*PC*/);			// PC用サイトの公開状況
			$siteOpenSmartphone = $this->isActiveAccessPoint(2/*スマートフォン*/);	// スマートフォン用サイトの公開状況
			$siteOpenMobile		= $this->isActiveAccessPoint(1/*携帯*/);	// 携帯用サイトの公開状況
		}

		$this->tmpl->addVar("_widget", "site_open_pc_checked",			$this->convertToCheckedString($siteOpenPc));
		$this->tmpl->addVar("_widget", "site_open_smartphone_checked",	$this->convertToCheckedString($siteOpenSmartphone));
		$this->tmpl->addVar("_widget", "site_open_mobile_checked",		$this->convertToCheckedString($siteOpenMobile));
		$this->tmpl->addVar("_widget", "url_pc",			$this->convertToDispString($this->gEnv->getDefaultUrl()));
		$this->tmpl->addVar("_widget", "url_smartphone",	$this->convertToDispString($this->gEnv->getDefaultSmartphoneUrl()));
		$this->tmpl->addVar("_widget", "url_mobile",		$this->convertToDispString($this->gEnv->getDefaultMobileUrl()));
	}
	/**
	 * アクセスポイントが有効かどうか
	 *
	 * @param int   $deviceType デバイスタイプ(0=PC,1=携帯,2=スマートフォン)
	 * @return bool 			true=有効、false=無効
	 */
	function isActiveAccessPoint($deviceType)
	{
		// ページID作成
		switch ($deviceType){
			case 0:		// PC
				$pageId = 'index';
				break;
			case 1:		// 携帯
				$pageId = M3_DIR_NAME_MOBILE . '_index';
				break;
			case 2:		// スマートフォン
				$pageId = M3_DIR_NAME_SMARTPHONE . '_index';
				break;
		}
		
		$isActive = false;
		$ret = $this->_mainDb->getPageIdRecord(0/*アクセスポイント*/, $pageId, $row);
		if ($ret){
			$isActive = $row['pg_active'];
		}
		return $isActive;
	}
	/**
	 * アクセスポイントが有効状態を更新
	 *
	 * @param int   $deviceType デバイスタイプ(0=PC,1=携帯,2=スマートフォン)
	 * @param bool  $status		有効状態
	 * @return bool 			true=成功、false=失敗
	 */
	function updateActiveAccessPoint($deviceType, $status)
	{
		// ページID作成
		switch ($deviceType){
			case 0:		// PC
				$pageId = 'index';
				break;
			case 1:		// 携帯
				$pageId = M3_DIR_NAME_MOBILE . '_index';
				break;
			case 2:		// スマートフォン
				$pageId = M3_DIR_NAME_SMARTPHONE . '_index';
				break;
		}
		
		$ret = $this->_mainDb->getPageIdRecord(0/*アクセスポイント*/, $pageId, $row);
		if ($ret){
			$ret = $this->_mainDb->updatePageId(0/*アクセスポイント*/, $pageId, $row['pg_name'], $row['pg_description'], $row['pg_priority'], $status, $row['pg_available']);
		}
		return $ret;
	}
	/**
	 * 管理メニュー項目の表示制御
	 *
	 * @param int   $deviceType デバイスタイプ(0=PC,1=携帯,2=スマートフォン)
	 * @param bool  $visible	項目の表示非表示
	 * @return bool				変更できたかどうか
	 */
	function setMenuItemVisible($deviceType, $visible)
	{
		// 対象タスク
		switch ($deviceType){
			case 0:		// PC
			default:
				$taskId = 'pagedef';
				break;
			case 1:		// 携帯
				$taskId = 'pagedef_mobile';
				break;
			case 2:		// スマートフォン
				$taskId = 'pagedef_smartphone';
				break;
		}
		
		$ret = $this->_mainDb->getNavItemsByTask(self::MENU_ID, $taskId, $row);
		if ($ret) $ret = $this->_mainDb->updateNavItemVisible($row['ni_id'], $visible);
		return $ret;
	}
}
?>
