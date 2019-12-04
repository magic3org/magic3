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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainInitwizardBaseWidgetContainer.php');

class admin_mainInitwizard_contentWidgetContainer extends admin_mainInitwizardBaseWidgetContainer
{
	private $idArray = array();			// 表示するコンテンツ
	private $itemIndex;					// 項目番号
	private $pageIdArray;				// アクセスポイント
	private $mainContentType;			// 主要コンテンツタイプ
	private $mainFeatureType;			// 主要機能タイプ
	private $selectedContentType = array();				// 選択中のコンテンツタイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->mainContentType	= $this->gPage->getMainContentTypeInfo();			// 主要コンテンツタイプ
		$this->mainFeatureType	= $this->gPage->getMainFeatureTypeInfo();			// 主要機能タイプ
		$this->pageIdArray		= $this->gEnv->getAllDefaultPageId();		// アクセスポイント
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
		return 'initwizard_content.tmpl.html';
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
	
		$reloadData = false;		// データの再ロード
		if ($act == 'update'){		// 設定更新のとき
			$listedItem = explode(',', $request->trimValueOf('idlist'));
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// ウィジェットの配置情報を取得
				$oldContentType = $this->getSelectedContentType($widgetInfoRows);
				
				for ($i = 0; $i < count($listedItem); $i++){
					// 項目がチェックされているかを取得
					$itemName = 'item' . $i . '_selected';
					$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
					$contentType = $listedItem[$i];
					
					// ウィジェットカテゴリーメニューの表示制御。一旦非表示にする。
					switch ($contentType){
					case M3_VIEW_TYPE_BBS:			// BBS
					case M3_VIEW_TYPE_WIKI:			// Wiki
					case M3_VIEW_TYPE_EVENT:			// イベント情報
					case M3_VIEW_TYPE_PHOTO:			// フォトギャラリー
					case M3_VIEW_TYPE_MEMBER:			// 会員情報
						$this->_mainDb->updateWidgetCategoryVisible($contentType, false);
						break;
					case M3_VIEW_TYPE_PRODUCT:		// 製品
					case M3_VIEW_TYPE_COMMERCE:			// Eコマース
						$this->_mainDb->updateWidgetCategoryVisible(M3_VIEW_TYPE_COMMERCE, false);
						break;
					}
					
					// コンテンツの表示可否によってウィジェットを配置
					if ($itemValue){
						// 表示に変更された場合のみウィジェットを配置
						if (!in_array($contentType, $oldContentType)){
							// アクセスポイントごとの処理
							for ($j = 0; $j < count($this->pageIdArray); $j++){
								// アクセスポイントが有効の場合のみ処理を行う
								$isActive = $this->isActiveAccessPoint($j);
								if (!$isActive) continue;
								
								$pageId = $this->pageIdArray[$j];
								
								// コンテンツ属性からページサブIDを取得
								$pageSubId = $this->gPage->getPageSubIdByContentType($contentType, $pageId);
							
								// コンテンツに対する表示ウィジェットを取得
								$ret = $this->_mainDb->getViewWidgetListByDeviceType($contentType, $j/*デバイスタイプ*/, $rows);
								if ($ret){
									$widgetId = $rows[0]['wd_id'];
									
									// ウィジェットがなければウィジェットを配置
									$this->_db->addWidget($pageId, $pageSubId, 'main', $widgetId, 0/*インデックス*/);
								}
								
								// ページの有効状態を更新
								$this->updatePageActive($pageSubId, true);	// ページ有効
							}
						}
						
						// ウィジェットカテゴリーメニューの表示制御。チェックが入ったカテゴリーのみ表示させる。
						switch ($contentType){
						case M3_VIEW_TYPE_BBS:			// BBS
						case M3_VIEW_TYPE_WIKI:			// Wiki
						case M3_VIEW_TYPE_EVENT:			// イベント情報
						case M3_VIEW_TYPE_PHOTO:			// フォトギャラリー
						case M3_VIEW_TYPE_MEMBER:			// 会員情報
							$this->_mainDb->updateWidgetCategoryVisible($contentType, true);
							break;
						case M3_VIEW_TYPE_PRODUCT:		// 製品
						case M3_VIEW_TYPE_COMMERCE:			// Eコマース
							$this->_mainDb->updateWidgetCategoryVisible(M3_VIEW_TYPE_COMMERCE, true);
							break;
						}
					} else {
						// 変更状況に関わらず処理を行う
						// 非選択のコンテンツタイプのウィジェットはページから削除
//						if (in_array($contentType, $oldContentType)){
						for ($j = 0; $j < count($widgetInfoRows); $j++){
							// 指定のコンテンツタイプに対応するウィジェットを取得
							$widgetId = $widgetInfoRows[$j]['wd_id'];
							if ($contentType == $widgetInfoRows[$j]['wd_content_type']){
								// ウィジェットをページから削除
								$ret = $this->_mainDb->delPageDefByWidgetId($widgetId);
							}
						}
							
						// アクセスポイントごとの処理
						for ($j = 0; $j < count($this->pageIdArray); $j++){
							$pageId = $this->pageIdArray[$j];
							
							// コンテンツ属性からページサブIDを取得
							$pageSubId = $this->gPage->getPageSubIdByContentType($contentType, $pageId);
					
							// ページの有効状態を更新
							$this->updatePageActive($pageSubId, false);	// ページ無効
						}
//						}
					}
				}
				if (true){
					// 次の画面へ遷移
					$this->_redirectNextTask();
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');			// データ更新に失敗しました
				}
			}
		} else {
			$reloadData = true;
		}
		
		if ($reloadData){		// データ再取得のとき
		}
		
		// 使用しているコンテンツタイプを取得
		$this->selectedContentType = $this->getSelectedContentType($tmp);
		
		// コンテンツタイプ、機能タイプ一覧を作成
		$this->itemIndex = 0;					// 項目番号
		$this->createContentTypeList();
		$this->createFeatureTypeList();
		$this->tmpl->addVar("_widget", "id_list", implode(',', $this->idArray));// 表示項目のIDを設定
	}
	/**
	 * コンテンツタイプ一覧作成
	 *
	 * @return なし
	 */
	function createContentTypeList()
	{
		for ($i = 0; $i < count($this->mainContentType); $i++){
			$value = $this->mainContentType[$i]['value'];
			$name = $this->mainContentType[$i]['name'];
			
			$checked = '';
			if (in_array($value, $this->selectedContentType)) $checked = 'checked';
			$row = array(
				'index'		=> $this->itemIndex,
				'name'		=> $name,			// コンテンツ名
				'checked'	=> $checked
			);
			$this->tmpl->addVars('content_type_list', $row);
			$this->tmpl->parseTemplate('content_type_list', 'a');
			$this->itemIndex++;
			
			// 表示中項目のページサブIDを保存
			$this->idArray[] = $value;
		}
	}
	/**
	 * 機能タイプ一覧作成
	 *
	 * @return なし
	 */
	function createFeatureTypeList()
	{
		for ($i = 0; $i < count($this->mainFeatureType); $i++){
			$value = $this->mainFeatureType[$i]['value'];
			$name = $this->mainFeatureType[$i]['name'];
			
			$checked = '';
			if (in_array($value, $this->selectedContentType)) $checked = 'checked';
			$row = array(
				'index'		=> $this->itemIndex,
				'name'		=> $name,			// コンテンツ名
				'checked'	=> $checked
			);
			$this->tmpl->addVars('feature_type_list', $row);
			$this->tmpl->parseTemplate('feature_type_list', 'a');
			$this->itemIndex++;
			
			// 表示中項目のページサブIDを保存
			$this->idArray[] = $value;
		}
	}
	/**
	 * 使用中のコンテンツタイプを取得
	 *
	 * @param array $widgetInfoRows 配置されているウィジェットの情報
	 * @return array				使用されているコンテンツタイプ
	 */
	function getSelectedContentType(&$widgetInfoRows)
	{
		$selectedContentType = array();
		$menuItems = array(array(), array(), array());

		// 主要コンテンツタイプと主要機能タイプを連結
		$contentType = array_merge($this->gPage->getMainContentTypes(), $this->gPage->getMainFeatureTypes());
		$ret = $this->_mainDb->getContentWidgetOnPage($this->langId, $this->pageIdArray, $contentType, $rows);
		if ($ret){
			$widgetInfoRows = $rows;
			
			// コンテンツタイプを取得
			$usedContentType = array();
			$rowCount = count($rows);
			for ($i = 0; $i < $rowCount; $i++){
				if (!empty($rows[$i]['wd_content_type'])) $usedContentType[] = $rows[$i]['wd_content_type'];
			}
			// コンテンツタイプをユニークにする
			for ($i = 0; $i < count($contentType); $i++){
				$type = $contentType[$i];
				if (in_array($type, $usedContentType)) $selectedContentType[] = $type;
			}
		}
		return $selectedContentType;
	}
	/**
	 * ページの有効状態を更新
	 *
	 * @param string $pageSubId		ページサブID
	 * @param bool $active			ページが有効かどうか
	 * @return bool					true=成功、false=失敗
	 */
	function updatePageActive($pageSubId, $active)
	{
		// 使用ページにする
		$ret = $this->_mainDb->getPageIdRecord(1/*ページサブIDを指定*/, $pageSubId, $row);
		if ($ret){
			$ret = $this->_mainDb->updatePageId(1/*ページサブIDを指定*/, $pageSubId, $row['pg_name'], $row['pg_description'], $row['pg_priority'], $active, $row['pg_visible']);
		}
		return $ret;
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
}
?>
