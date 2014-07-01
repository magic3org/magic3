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
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/whatsnewDb.php');

class admin_whatsnewWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;
	private $paramObj;		// パラメータ保存用オブジェクト
	const CONTENT_TYPE = 'news';			// コンテンツタイプ
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new whatsnewDb();
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
		$task = $request->trimValueOf('task');
		if ($task == 'list'){		// 一覧画面
			return 'admin_list.tmpl.html';
		} else {			// 一覧画面
			return 'admin.tmpl.html';
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
		$task = $request->trimValueOf('task');
		if ($task == 'list'){		// 一覧画面
			return $this->createList($request);
		} else {			// 詳細設定画面
			return $this->createDetail($request);
		}
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$userId		= $this->gEnv->getCurrentUserId();
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		
		// ページ定義IDとページ定義のレコードシリアル番号
		$defConfigId = $this->getPageDefConfigId($request);				// 定義ID取得
		$defSerial = $this->getPageDefSerial($request);		// ページ定義のレコードシリアル番号
		$value = $request->trimValueOf('defconfig');		// ページ定義の定義ID
		if (!empty($value)) $defConfigId = $value;
		$value = $request->trimValueOf('defserial');		// ページ定義のレコードシリアル番号
		if (!empty($value)) $defSerial = $value;
		
		$name = $request->trimValueOf('item_name');
		$html = $request->valueOf('item_html');		// ヘッダコンテンツ
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// パラメータオブジェクトを取得
		$this->paramObj = $this->getWidgetParamObj();
		
		if (empty($act)){// 初期起動時
			$name = '';
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$html = '';		// コンテンツ
		} else if ($act == 'add'){// 新規追加
			// 入力チェック
			$this->checkInput($name, '名前');

			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i];
				if ($name == $targetObj->name){		// 定義名
					$this->setUserErrorMsg('名前が重複しています');
					break;
				}
			}
			
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// コンテンツ登録
				$contentId = 0;// コンテンツID新規追加
				$default = 0;
				$key = '';
				$ret = $this->db->updateContentItem(self::CONTENT_TYPE, $contentId, $this->langId, $name, $html, 1/*コンテンツ表示*/, $default, $key, $userId, $newContentId, $newSerial);
				if ($ret) $contentId = $newContentId;// コンテンツID更新
				
				// 最大の定義ID取得
				$newConfigId = 1;
				if (count($this->paramObj) > 0){
					$targetObj = $this->paramObj[count($this->paramObj) -1];
					$newConfigId = $targetObj->id + 1;
				}
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->name	= $name;// 表示名
				$newObj->id	= $newConfigId;// 定義ID
				$newObj->contentId = $contentId;		// コンテンツID
				$this->paramObj[] = $newObj;
			
				// ウィジェットパラメータオブジェクト更新
				$ret = $this->updateWidgetParamObj($this->paramObj);
				
				// 画面定義更新
				if ($ret && !empty($defSerial)){
					$ret = $this->_db->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $newConfigId, $name);
				}
				if ($ret){
					$this->setGuidanceMsg('データを追加しました');
					
					if (!empty($defSerial)) $defConfigId = $newConfigId;		// 定義定義IDを更新
					$this->configId = $newConfigId;		// 定義定義IDを更新
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'update'){		// 項目更新の場合
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// コンテンツIDを取得
				$contentId = 0;
				for ($i = 0; $i < count($this->paramObj); $i++){
					$targetObj = $this->paramObj[$i];
					$id = $targetObj->id;// 定義ID
					$name = $targetObj->name;// 定義名
					if ($id == $this->configId){
						$contentId = $targetObj->contentId;
						break;
					}
				}
				// ウィジェットパラメータオブジェクトを更新
				$ret = $this->updateWidgetParamObj($this->paramObj);
				
				// コンテンツを更新
				$default = 0;
				$key = '';
				$ret = $this->db->updateContentItem(self::CONTENT_TYPE, $contentId, $this->langId, $name, $html, 1/*コンテンツ表示*/, $default, $key, $userId, $newContentId, $newSerial);
				
				if (empty($defConfigId) && !empty($defSerial)){		// 画面定義の定義IDが設定されていないときは設定
					// 画面定義更新
					if ($ret) $ret = $this->_db->updateWidgetConfigId($this->gEnv->getCurrentWidgetId(), $defSerial, $this->configId, $name);
				}
				if ($ret){
					$this->setGuidanceMsg('データを更新しました');
					
					if (empty($defConfigId) && !empty($defSerial)){		// 画面定義の定義IDが設定されていないときは設定
						$defConfigId = $this->configId;		// 定義定義IDを更新
					}
				} else {
					$this->setAppErrorMsg('データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow($defSerial);// 親ウィンドウを更新
			}
		} else if ($act == 'select'){	// 定義IDを変更
		}

		// お知らせ選択メニュー作成
		$this->createNewsListMenu();

		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			$name = $this->createDefaultName();			// デフォルト登録項目名
			$html = '';
			$this->serialNo = 0;
		} else {
			// 定義からウィジェットパラメータを検索して、定義データを取得
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i];
				$id = $targetObj->id;// 定義ID
				if ($id == $this->configId) break;
			}
			if ($i < count($this->paramObj)){		// 該当項目があるとき
				// コンテンツを取得
				$contentId = $targetObj->contentId;
				$ret = $this->db->getContentByContentId(self::CONTENT_TYPE, $contentId, $this->langId, $row);
				if ($ret){
					$html = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $row['cn_html']);// アプリケーションルートを変換
					$this->serialNo = $row['cn_serial'];
					
					$name = $targetObj->name;// 名前
					
					// 新規作成でないときは、メニューを変更不可にする
					if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
				}
			}
		}
		
		// ### 入力値を再設定 ###
		$this->tmpl->addVar("item_name_visible", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "content", $html);		// コンテンツ
		
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定

		// 画面定義用の情報を戻す
		$this->tmpl->addVar("_widget", "def_serial", $defSerial);	// ページ定義のレコードシリアル番号
		$this->tmpl->addVar("_widget", "def_config", $defConfigId);	// ページ定義の定義ID
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
		} else {
			$this->tmpl->setAttribute('del_button', 'visibility', 'visible');// 「削除」ボタン
		}
	}
	/**
	 * お知らせ選択用メニュー
	 *
	 * @return なし						
	 */
	function createNewsListMenu()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$targetObj = $this->paramObj[$i];
			$id = $targetObj->id;// 定義ID
			$name = $targetObj->name;// 定義名
			$selected = '';
			if ($this->configId == $id) $selected = 'selected';
			$row = array(
				'name' => $name,		// 名前
				'value' => $id,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('news_list', $row);
			$this->tmpl->parseTemplate('news_list', 'a');
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		
		// ページ定義IDとページ定義のレコードシリアル番号
		$defConfigId = $this->getPageDefConfigId($request);				// 定義ID取得
		$defSerial = $this->getPageDefSerial($request);		// ページ定義のレコードシリアル番号
		$value = $request->trimValueOf('defconfig');		// ページ定義の定義ID
		if (!empty($value)) $defConfigId = $value;
		$value = $request->trimValueOf('defserial');		// ページ定義のレコードシリアル番号
		if (!empty($value)) $defSerial = $value;

		// パラメータオブジェクトを取得
		$this->paramObj = $this->getWidgetParamObj();
		
		if ($act == 'delete'){		// メニュー項目の削除
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				// 更新オブジェクト作成
				$newParamObj = array();

				for ($i = 0; $i < count($this->paramObj); $i++){
					$targetObj = $this->paramObj[$i];
					$id = $targetObj->id;// 定義ID
					if (!in_array($id, $delItems)){		// 削除対象でないときは追加
						$newParamObj[] = $targetObj;
					}
				}
				
				// ウィジェットパラメータオブジェクト更新
				$ret = $this->updateWidgetParamObj($newParamObj);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
					$this->paramObj = $newParamObj;
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		// お知らせ選択リスト作成
		$this->createNewsList();
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		
		// 画面定義用の情報を戻す
		$this->tmpl->addVar("_widget", "def_serial", $defSerial);	// ページ定義のレコードシリアル番号
		$this->tmpl->addVar("_widget", "def_config", $defConfigId);	// ページ定義の定義ID
	}
	/**
	 * お知らせ一覧用リスト
	 *
	 * @return なし						
	 */
	function createNewsList()
	{
		for ($i = 0; $i < count($this->paramObj); $i++){
			$targetObj = $this->paramObj[$i];
			$id = $targetObj->id;// 定義ID
			$name = $targetObj->name;// 定義名
			
			$defCount = 0;
			if (!empty($id)){
				$defCount = $this->_db->getPageDefCount($this->gEnv->getCurrentWidgetId(), $id);
			}
			$operationDisagled = '';
			if ($defCount > 0) $operationDisagled = 'disabled';
			$row = array(
				'index' => $i,
				'ope_disabled' => $operationDisagled,			// 選択可能かどうか
				'name' => $this->convertToDispString($name),		// 名前
				'def_count' => $defCount							// 使用数
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
			
			// シリアル番号を保存
			$this->serialArray[] = $id;
		}
	}
	/**
	 * デフォルトの名前を取得
	 *
	 * @return string	デフォルト名						
	 */
	function createDefaultName()
	{
		$name = self::DEFAULT_NAME_HEAD;
		for ($j = 1; $j < 100; $j++){
			$name = self::DEFAULT_NAME_HEAD . $j;
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i];
				if ($name == $targetObj->name){		// 定義名
					break;
				}
			}
			// 重複なしのときは終了
			if ($i == count($this->paramObj)) break;
		}
		return $name;
	}
}
?>
