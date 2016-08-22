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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainMainteBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainPageinfoWidgetContainer extends admin_mainMainteBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $pageId;	// ページID
	private $pageSubId;	// ページサブID
	private $defaultSubId;	// デフォルトのページサブID
	private $contentType;
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $contentTypeArray;		// コンテンタイプ
	private $developMode;			// 開発モードかどうか
	const TITLE_PRE_ICON_HOME = '<i class="glyphicon glyphicon-home" rel="m3help" title="デフォルト"></i> ';		// タイトル付加用アイコン(デフォルトページ)
	const TITLE_PRE_ICON_LOCK = '<i class="glyphicon glyphicon-lock" rel="m3help" title="SSL"></i> ';		// タイトル付加用アイコン(SSL)
	const CF_PERMIT_DETAIL_CONFIG	= 'permit_detail_config';				// 開発モードかどうか
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
		
		// コンテンツタイプ
//		$this->contentTypeArray = array(	array(	'name' => '[指定なし]',					'value' => ''));
//		$this->contentTypeArray = array_merge($this->contentTypeArray, $this->gPage->getAllPageAttributeTypeInfo());			// すべてのページ属性を取得
		$this->contentTypeArray = $this->gPage->getAllPageAttributeTypeInfo();		// すべてのページ属性を取得
		
		$this->developMode = $this->gSystem->getSystemConfig(self::CF_PERMIT_DETAIL_CONFIG);	// 開発モード
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
		if ($task == 'pageinfo_detail'){		// 詳細画面
			return 'pageinfo_detail.tmpl.html';
		} else {			// 一覧画面
			return 'pageinfo.tmpl.html';
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
		if ($task == 'pageinfo_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
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
		// パラメータの取得
		$task = $request->trimValueOf('task');		// 処理区分
		$act = $request->trimValueOf('act');
		$this->pageId = $request->trimValueOf('pageid');		// ページID
		$this->pageSubId = $request->trimValueOf('pagesubid');// ページサブID
		
		// ページメインIDメニュー作成
		// 選択中のページIDを決定
		$this->db->getPageIdList(array($this, 'pageIdLoop'), 0/*ページID*/);
		
		// デフォルトのページサブIDを取得
		$this->defaultSubId = $this->_db->getDefaultPageSubId($this->pageId);
		
		// ページサブID一覧を作成
		$this->db->getPageSubIdList($this->pageId, ''/*言語なし*/, array($this, 'pageSubIdLoop'));
		
		$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');
		$this->pageId = $request->trimValueOf('pageid');		// ページID
		$this->pageSubId = $request->trimValueOf('pagesubid');// ページサブID
		$this->contentType = $request->trimValueOf('item_contenttype');// コンテンツタイプ
		$useSsl = ($request->trimValueOf('item_use_ssl') == 'on') ? 1 : 0;		// SSLを使用するかどうか
		$default = ($request->trimValueOf('item_default') == 'on') ? 1 : 0;		// デフォルトページかどうか
		$userLimited = ($request->trimValueOf('item_user_limited') == 'on') ? 1 : 0;		// ユーザ制限するかどうか
		$this->templateId = $request->trimValueOf('item_template_id');// テンプレート
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 更新のとき
			// 入力チェック
			$this->checkInput($this->pageId, 'ページID');
			$this->checkInput($this->pageSubId, 'ページサブID');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// デフォルトページの変更
				$ret = true;
				if ($default) $ret = $this->_db->updateDefaultPageSubId($this->pageId, $this->pageSubId);
				
				// ページ情報の更新
				if ($ret) $ret = $this->db->updatePageInfo($this->pageId, $this->pageSubId, $this->contentType,
														$this->templateId, ''/*サブテンプレートID*/, 0/*アクセス制御ユーザタイプ*/, $useSsl, $userLimited);

				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {		// 初期状態
			$replaceNew = true;			// データを再取得
		}
		// 表示データ再取得
		$name = '';
		$systemType = '';
		$deviceType = -1;
		if ($replaceNew){
			// アクセスポイント情報を取得
			$ret = $this->db->getPageIdRecord(0/*アクセスポイント*/, $this->pageId, $row);
			if ($ret){
				$accessPointName = $row['pg_name'];
			}
			
			$ret = $this->db->getPageInfo($this->pageId, $this->pageSubId, $row);
			if ($ret){
				$this->contentType = $row['pn_content_type'];
				$useSsl = $row['pn_use_ssl'];// SSLを使用するかどうか
				$userLimited = $row['pn_user_limited'];// ユーザ制限するかどうか
				$name = $row['pg_name'];
				$this->templateId = $row['pn_template_id'];			// テンプレートID
				$systemType = $row['pg_system_type'];		// システム用ページタイプ
				
				// 端末タイプを取得
				$ret = $this->db->getPageIdRecord(0/*ページID*/, $this->pageId, $row);
				if ($ret) $deviceType = $row['pg_device_type'];				// 端末タイプ
			}
		}

		// コンテンツタイプメニュー作成
		$this->createContentTypeMenu();
		
		// テンプレート選択メニュー作成
		$this->db->getAllTemplateList($deviceType, array($this, 'templateIdLoop'));
			
		// デフォルトのページサブIDを取得
		$isDefault = false;
		$this->defaultSubId = $this->_db->getDefaultPageSubId($this->pageId);
		if ($this->pageSubId == $this->defaultSubId) $isDefault = true;
		
		// システム用ページタイプの場合は更新を行わない
		$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
		if (empty($systemType)){
			$this->tmpl->addVar("_widget", "default_disabled", $this->convertToDisabledString($isDefault));		// デフォルトの場合はデフォルト値の変更不可
		} else {
			$this->tmpl->addVar("update_button", "update_disabled", 'disabled');		// 更新ボタン無効
			
			// その他の項目無効化
			$this->tmpl->addVar("_widget", "default_disabled", 'disabled');		// デフォルトページ
			$this->tmpl->addVar("_widget", "use_ssl_disabled", 'disabled');		// SSL
			$this->tmpl->addVar("_widget", "contenttype_disabled", 'disabled');		// ページ属性
			$this->tmpl->addVar("_widget", "template_id_disabled", 'disabled');		// テンプレート
			$this->tmpl->addVar("_widget", "user_limited_disabled", 'disabled');		// ユーザ制限
		}
		
		$this->tmpl->addVar("_widget", "use_ssl_checked", $this->convertToCheckedString($useSsl));		// SSLを使用するかどうか
		$this->tmpl->addVar("_widget", "user_limited_checked", $this->convertToCheckedString($userLimited));		// ユーザ制限するかどうか
		$this->tmpl->addVar("_widget", "access_point_name", $this->convertToDispString($accessPointName));			// アクセスポイント名
		$this->tmpl->addVar("_widget", "page_id", $this->pageId);			// ページID
		$this->tmpl->addVar("_widget", "page_subid", $this->pageSubId);		// ページサブID
		$this->tmpl->addVar("_widget", "name", $name);		// ページ名
		$this->tmpl->addVar("_widget", "default", $this->convertToCheckedString($isDefault));				// デフォルトのページかどうか
	}
	/**
	 * ページID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageIdLoop($index, $fetchedRow, $param)
	{
		// 開発モードのときはすべて表示、開発モードでないときはフロント画面用アクセスポイントのみ取得
		if (!$this->developMode && !$fetchedRow['pg_frontend']) return true;
		
		// デフォルトのページIDを取得
		if (empty($this->pageId)) $this->pageId = $fetchedRow['pg_id'];
		
		$selected = '';
		if ($fetchedRow['pg_id'] == $this->pageId){
			$selected = 'selected';
			
			// デフォルトのページサブIDを取得
			$this->defaultPageSubId = $fetchedRow['pg_default_sub_id'];		// デフォルトのページID
		}
		$name = $this->convertToDispString($fetchedRow['pg_name']);			// ページ名
		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['pg_id']),			// ページID
			'name'     => $name,			// ページ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('main_id_list', $row);
		$this->tmpl->parseTemplate('main_id_list', 'a');
		return true;
	}
	/**
	 * ページサブID、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageSubIdLoop($index, $fetchedRow, $param)
	{
		$pid = $fetchedRow['pg_id'];
		$value = $this->convertToDispString($pid);
		
		// 行の先頭にデフォルトかSSL使用のアイコンを付加。デフォルトアイコンが優先。
		// SSLを使用するかどうか
		$titleIcon = '';
		$ssl = '';
		if ($fetchedRow['pn_use_ssl']){
			$ssl = 'checked';
			$titleIcon = self::TITLE_PRE_ICON_LOCK;		// SSLアイコン
		}
		
		// デフォルトページ
		$default = '';
		if ($pid == $this->defaultSubId){
			$default = 'checked';
			$titleIcon = self::TITLE_PRE_ICON_HOME;		// デフォルトページアイコン
		}

		// 公開状況
		$public = '';
		if ($fetchedRow['pg_active']) $public = 'checked';
		
		// ユーザ制限するかどうか
		$userLimited = '';
		if ($fetchedRow['pn_user_limited']) $userLimited = 'checked';
		
		// ページ上のウィジェット数(共通属性のウィジェット除く)
		$refCount = $this->_db->getWidgetCountOnPage($this->pageId, $pid, true/*共通ウィジェット除く*/);
		
		// 有効状態のページを色分け
		$active = '';
		$activeDesc = '';
		if ($fetchedRow['pg_active']){
			$active = 'class="success"';
			$activeDesc = 'rel="m3help" title="有効なページ"';
		}
		
		$row = array(
			'index'    => $index,			// インデックス番号
			'title_icon'    => $titleIcon,			// デフォルト、SSLの表示アイコン
			'value'    => $value,			// ページID
			'name'     => $this->convertToDispString($fetchedRow['pg_name']),			// ページ名
			'content_type'     => $this->convertToDispString($fetchedRow['pn_content_type']),			// コンテンツタイプ
			'template'     => $this->convertToDispString($fetchedRow['pn_template_id']),			// テンプレート
			'public'	=> $public,			// ページ公開
			'ssl'		=> $ssl,			// SSLを使用するかどうか
			'user_limited'		=> $userLimited,			// ユーザ制限するかどうか
			'default'	=> $default,		// デフォルトのページサブID
			'ref_count'	=> $refCount,		// ページ上のウィジェット数
			'active'		=> $active,			// 有効な行をカラー表示
			'active_desc'	=> $activeDesc		// 有効行説明用
		);
		$this->tmpl->addVars('sub_id_list', $row);
		$this->tmpl->parseTemplate('sub_id_list', 'a');
		
		// 表示中項目のページサブIDを保存
		$this->serialArray[] = $value;
		return true;
	}
	/**
	 * コンテンツタイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createContentTypeMenu()
	{
		for ($i = 0; $i < count($this->contentTypeArray); $i++){
			$value = $this->contentTypeArray[$i]['value'];
			$name = $value . '(' . $this->contentTypeArray[$i]['name'] . ')';
			
			$selected = '';
			if ($value == $this->contentType) $selected = 'selected';
			
			$row = array(
				'value'    => $this->convertToDispString($value),			// ページID
				'name'     => $this->convertToDispString($name),			// ページ属性
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('content_type_list', $row);
			$this->tmpl->parseTemplate('content_type_list', 'a');
		}
	}
	/**
	 * テンプレート一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function templateIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['tm_id'] == $this->templateId){
			$selected = 'selected';
		}

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['tm_id']),			// テンプレートID
			'name'     => $this->convertToDispString($fetchedRow['tm_name']),			// テンプレート名名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('template_id_list', $row);
		$this->tmpl->parseTemplate('template_id_list', 'a');
		return true;
	}
}
?>
