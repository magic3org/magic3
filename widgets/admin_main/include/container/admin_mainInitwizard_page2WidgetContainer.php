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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainInitwizardBaseWidgetContainer.php');

class admin_mainInitwizard_page2WidgetContainer extends admin_mainInitwizardBaseWidgetContainer
{
	private $deviceType;		// デバイスタイプ(0=PC,1=携帯,2=スマートフォン)
	private $defaultPageId;			// デフォルトのページサブID
	
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
		return 'initwizard_page2.tmpl.html';
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
		// アクセスポイントの使用状況を取得
		$siteOpenPc			= $this->isActiveAccessPoint(0/*PC*/);			// PC用サイトの公開状況
		$siteOpenSmartphone = $this->isActiveAccessPoint(2/*スマートフォン*/);	// スマートフォン用サイトの公開状況
		
		$act = $request->trimValueOf('act');
		$pageDefaultPc = $request->trimValueOf('page_default_pc');
		$pageDefaultSmartphone = $request->trimValueOf('page_default_smartphone');
		
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
			$ret = true;
			if ($siteOpenPc){
				$this->_db->updateDefaultPageSubId($this->gEnv->getDefaultPageId(), $pageDefaultPc);
			}
			if ($siteOpenSmartphone){
				$this->_db->updateDefaultPageSubId($this->gEnv->getDefaultSmartphonePageId(), $pageDefaultSmartphone);
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
			
		// デフォルトページ選択メニューの作成
		$isFirstAccessPoint = true;
		if ($siteOpenPc){
			// デフォルトのページID取得
			$this->deviceType = 0;
			$this->defaultPageId = $this->gEnv->getDefaultPageSubIdByPageId($this->gEnv->getDefaultPageId());
			
			$this->_mainDb->getPageIdList(array($this, 'pageLoop'), 1/*ページサブIDを指定*/);
			
			$this->tmpl->setAttribute('page_pc', 'visibility', 'visible');		// PC用アクセスポイント
			if (!$isFirstAccessPoint) $this->tmpl->addVar("page_pc", "offset_class", 'col-lg-offset-3');
			$isFirstAccessPoint = false;
		}
		if ($siteOpenSmartphone){
			// デフォルトのページID取得
			$this->deviceType = 2;
			$this->defaultPageId = $this->gEnv->getDefaultPageSubIdByPageId($this->gEnv->getDefaultSmartphonePageId());
			
			$this->_mainDb->getPageIdList(array($this, 'pageLoop'), 1/*ページサブIDを指定*/);
			
			$this->tmpl->setAttribute('page_smartphone', 'visibility', 'visible');		// スマートフォン用アクセスポイント
			if (!$isFirstAccessPoint) $this->tmpl->addVar("page_smartphone", "offset_class", 'col-lg-offset-3');
			$isFirstAccessPoint = false;
		}
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
	 * ページサブID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageLoop($index, $fetchedRow, $param)
	{
		$value = $fetchedRow['pg_id'];
		
		// 有効かつ公開でないページの場合は追加しない
		if (!$fetchedRow['pg_active'] || !$fetchedRow['pg_visible']) return true;
		
		$selected = '';
		if ($value == $this->defaultPageId) $selected = 'selected';
			
		$row = array(
			'value'		=> $this->convertToDispString($value),			// ページID
			'name'		=> $this->convertToDispString($fetchedRow['pg_name']),			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		
		switch ($this->deviceType){
			case 0:		// PC
				$this->tmpl->addVars('page_list_pc', $row);
				$this->tmpl->parseTemplate('page_list_pc', 'a');
				break;
			case 2:		// スマートフォン
				$this->tmpl->addVars('page_list_smartphone', $row);
				$this->tmpl->parseTemplate('page_list_smartphone', 'a');
				break;
		}
		
		// 表示中項目のページサブIDを保存
		$this->idArray[] = $value;
		return true;
	}
}
?>
