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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/blog_mainDb.php');

class admin_blog_mainBlogidWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	private $mainDb;	// DB接続オブジェクト
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();		// 表示されている項目シリアル番号
	private $templateId;	// テンプレートID
	private $subTemplateId;	// サブテンプレートID
	private $subTemplateInfo;		// サブテンプレート情報
	private $isExistsSubTemplate;		// サブテンプレートが存在するかどうか
	private $ownerId;	// 所有者ID
	private $limitedUserId;		// 制限ユーザID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->mainDb = new blog_mainDb();
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
		if ($task == 'blogid_detail'){		// 詳細画面
			return 'admin_blogid_detail.tmpl.html';
		} else {			// 一覧画面
			return 'admin_blogid.tmpl.html';
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
		if ($task == 'blogid_detail'){	// 詳細画面
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
		$act = $request->trimValueOf('act');
		
		if ($act == 'delete'){		// 項目削除の場合
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
				// ブログIDを取得
				$roomArray = array();
				for ($i = 0; $i < count($delItems); $i++){
					// シリアル番号からデータを取得
					$ret = $this->mainDb->getBlogInfoBySerial($delItems[$i], $row);
					if ($ret) $roomArray[] = $row['bl_id'];			// ブログ識別ID
				}
				
				// ブログを削除
				$ret = $this->mainDb->delBlogInfo($delItems);
				if ($ret){		// データ削除成功のとき
					$this->setGuidanceMsg('データを削除しました');
				} else {
					$this->setAppErrorMsg('データ削除に失敗しました');
				}
			}
		}
		
		// 一覧作成
		$this->mainDb->getAllBlogInfo(array($this, 'itemLoop'));
		
		if (count($this->serialArray) > 0){
			$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		} else {
			// 項目がないときは、一覧を表示しない
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
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
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$name	= $request->trimValueOf('item_name');	// 名前
		$id		= $request->trimValueOf('item_id');	// ブログ識別ID
		$this->templateId	= $request->trimValueOf('templateid');	// テンプレートID
		$this->subTemplateId = $request->trimValueOf('subtemplateid');	// サブテンプレートID
		$this->ownerId	= $request->trimValueOf('item_owner_id');	// 所有者ID
		$this->limitedUserId = $request->trimValueOf('item_limited_user_id');		// 制限ユーザID
		$index	= $request->trimValueOf('item_index');		// 表示順
		$visible = ($request->trimValueOf('item_visible') == 'on') ? 1 : 0;		// 表示するかどうか
		$userLimited = ($request->trimValueOf('item_user_limited') == 'on') ? 1 : 0;		// ユーザ制限するかどうか
		$metaTitle = $request->trimValueOf('item_meta_title');		// ページタイトル名
		$metaDesc = $request->trimValueOf('item_meta_desc');			// ページ要約
		$metaKeyword = $request->trimValueOf('item_meta_keyword');	// ページキーワード

		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkSingleByte($id, '識別ID');
			$this->checkNumeric($index, '表示順');
			
			// 同じIDがある場合はエラー
			if ($this->mainDb->getBlogInfoById($id, $row)) $this->setMsg(self::MSG_USER_ERR, '識別IDが重複しています');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 保存形式に変換
				if (!empty($this->limitedUserId)) $this->limitedUserId = blog_mainCommonDef::USER_ID_SEPARATOR . $this->limitedUserId . blog_mainCommonDef::USER_ID_SEPARATOR;
				
				$ret = $this->mainDb->updateBlogInfo(0/*新規*/, $id, $name, $index, $this->templateId, $this->subTemplateId, $visible, $userLimited,
													$metaTitle, $metaDesc, $metaKeyword, $this->ownerId, $this->limitedUserId, $newSerial);
				
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					$this->serialNo = $newSerial;		// シリアル番号を更新
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			$this->checkInput($name, '名前');
			$this->checkNumeric($index, '表示順');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 保存形式に変換
				if (!empty($this->limitedUserId)) $this->limitedUserId = blog_mainCommonDef::USER_ID_SEPARATOR . $this->limitedUserId . blog_mainCommonDef::USER_ID_SEPARATOR;
				
				$ret = $this->mainDb->updateBlogInfo($this->serialNo, $id, $name, $index, $this->templateId, $this->subTemplateId, $visible, $userLimited,
													$metaTitle, $metaDesc, $metaKeyword, $this->ownerId, $this->limitedUserId, $newSerial);
				
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				
					$this->serialNo = $newSerial;		// シリアル番号を更新
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'getsubtemplate'){		// サブテンプレート取得
			// ##### ウィジェット出力処理中断 ######
			$this->gPage->abortWidget();
			
			// デフォルトのサブテンプレートを取得
			$this->subTemplateId = $this->getDefaultSubTemplateId($this->templateId);
			
			$subTemplateMenu = $this->getParsedTemplateData('sub_template_menu.tmpl.html', array($this, 'createSubTemplateMenu'), $this->templateId);// サブテンプレートメニュー取得
			if (!$this->isExistsSubTemplate) $subTemplateMenu = '';		// サブテンプレートが存在しない場合は空で返す
			$this->gInstance->getAjaxManager()->addDataToBody($subTemplateMenu);
			return;
		} else {		// 初期状態
			// シリアル番号からデータを取得
			$ret = $this->mainDb->getBlogInfoBySerial($this->serialNo, $row);
			if ($ret) $id = $row['bl_id'];			// 識別ID
			
			if (empty($id)){		// 識別IDが空のときは新規とする
				$this->serialNo = 0;
				$id		= '';		// 識別ID
				$name	= '';	// 名前
				$index = $this->mainDb->getBlogInfoMaxIndex() + 1;	// 表示順
				$this->templateId	= '';	// テンプレートID
				$this->subTemplateId = '';	// サブテンプレートID
				$visible	= 1;		// 公開
				$userLimited = 0;		// ユーザ制限
				$metaTitle = '';		// ページタイトル名(METAタグ)
				$metaDesc = '';		// ページ要約(METAタグ)
				$metaKeyword = '';		// ページキーワード(METAタグ)
				$this->ownerId	= 0;	// 所有者ID
				$this->limitedUserId = '';		// 制限ユーザID
			} else {
				$replaceNew = true;			// データを再取得
			}
		}
		// 表示データ再取得
		if ($replaceNew){
			// ブログIDからデータを取得
			$ret = $this->mainDb->getBlogInfoById($id, $row);
			if ($ret){
				$this->serialNo = $row['bl_serial'];
				$name		= $row['bl_name'];
				$index = $row['bl_index'];	// 表示順
				$this->templateId	= $row['bl_template_id'];	// テンプレートID
				$this->subTemplateId = $row['bl_sub_template_id'];	// サブテンプレートID
				$visible	= $row['bl_visible'];		// 公開
				$userLimited = $row['bl_user_limited'];		// ユーザ制限
				$metaTitle = $row['bl_meta_title'];		// ページタイトル名(METAタグ)
				$metaDesc = $row['bl_meta_description'];		// ページ要約(METAタグ)
				$metaKeyword = $row['bl_meta_keywords'];		// ページキーワード(METAタグ)
				$this->ownerId			= $row['bl_owner_id'];	// 所有者ID
				$this->limitedUserId	= trim($row['bl_limited_user_id'], blog_mainCommonDef::USER_ID_SEPARATOR);		// 制限ユーザID
			}
		}
		// ブログ所有者ユーザ選択メニュー作成
		$this->mainDb->getUserList(UserInfo::USER_TYPE_AUTHOR, array($this, 'userIdLoop'));
		
		// アクセス制限用ユーザ選択メニュー作成
		$this->mainDb->getUserList(UserInfo::USER_TYPE_NORMAL, array($this, 'limitedUserIdLoop'));
		
		// テンプレート選択メニュー作成
		$this->mainDb->getAllTemplateList(0/*PC用テンプレート*/, array($this, 'templateIdLoop'));
		
		// サブテンプレート選択メニュー作成
		$this->createSubTemplateMenu($this->tmpl, $this->templateId);
				
		if (empty($this->serialNo)){		// シリアル番号が空のときは新規とする
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
			$this->tmpl->setAttribute('new_id_field', 'visibility', 'visible');// 新規ID入力フィールド表示
			
			$this->tmpl->addVar("new_id_field", "id", $id);		// 識別キー
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
			$this->tmpl->setAttribute('id_field', 'visibility', 'visible');// 固定IDフィールド表示
			
			$this->tmpl->addVar("id_field", "id", $id);		// 識別キー
		}
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "index", $index);		// 表示順
		$this->tmpl->addVar("_widget", "meta_title", $metaTitle);		// ページタイトル名(METAタグ)
		$this->tmpl->addVar("_widget", "meta_desc", $metaDesc);		// ページ要約(METAタグ)
		$this->tmpl->addVar("_widget", "meta_keyword", $metaKeyword);		// ページキーワード(METAタグ)
		$checked = '';
		if ($visible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "visible", $checked);// 項目表示、項目利用可否チェックボックス
		$checked = '';
		if ($userLimited) $checked = 'checked';
		$this->tmpl->addVar("_widget", "user_limited_checked", $checked);// ユーザ制限
		
		// プレビュー用URL
		if (!empty($id)){
			$previewUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_BLOG_ID . '=' . $id;
			$this->tmpl->addVar('_widget', 'preview_url', $this->getUrl($previewUrl));// プレビュー用URL(フロント画面)
		}
		
		// 選択中のシリアル番号を設定
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "target_widget", $this->gEnv->getCurrentWidgetId());// メニュー選択ウィンドウ表示用
	}
	/**
	 * 取得したブログ情報をテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['bl_id'];		// ブログID
		$visible = '';
		if ($fetchedRow['bl_visible']) $visible = 'checked';	// 項目の表示
		
		// 所有者
		$owner = '';
		$account = $fetchedRow['lu_account'];
		if (!empty($account)) $owner = $this->convertToDispString($fetchedRow['lu_name'] . ' / ' . $account);
		
		$row = array(
			'index' => $index,
			'serial' => $fetchedRow['bl_serial'],
			'id' =>	$this->convertToDispString($id),		// 識別ID
			'name'     => $this->convertToDispString($fetchedRow['bl_name']),			// 表示名
			'owner'     => $owner,			// 所有者
			'visible'	=> $visible					// 公開状況
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		// 表示中項目のシリアル番号を保存
		$this->serialArray[] = $fetchedRow['bl_serial'];
		return true;
	}
	/**
	 * ユーザ一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function userIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['lu_id'] == $this->ownerId) $selected = 'selected';

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['lu_id']),			// ユーザID
			'name'     => $this->convertToDispString($fetchedRow['lu_name'] . ' / ' . $fetchedRow['lu_account']),			// ユーザ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('user_list', $row);
		$this->tmpl->parseTemplate('user_list', 'a');
		return true;
	}
	/**
	 * アクセス制限用ユーザ一覧を作成
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function limitedUserIdLoop($index, $fetchedRow, $param)
	{
		$selected = '';
		if ($fetchedRow['lu_id'] == $this->limitedUserId) $selected = 'selected';

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['lu_id']),			// ユーザID
			'name'     => $this->convertToDispString($fetchedRow['lu_name'] . ' / ' . $fetchedRow['lu_account']),			// ユーザ名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('limited_user_list', $row);
		$this->tmpl->parseTemplate('limited_user_list', 'a');
		return true;
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
		if ($fetchedRow['tm_id'] == $this->templateId) $selected = 'selected';

		$row = array(
			'value'    => $this->convertToDispString($fetchedRow['tm_id']),			// テンプレートID
			'name'     => $this->convertToDispString($fetchedRow['tm_name']),			// テンプレート名名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('template_list', $row);
		$this->tmpl->parseTemplate('template_list', 'a');
		return true;
	}
	/**
	 * サブテンプレートメニュー作成
	 *
	 * @param object  $tmpl			テンプレートオブジェクト
	 * @param string  $templateId	テンプレートID
	 * @return						なし
	 */
	function createSubTemplateMenu($tmpl, $templateId)
	{
		if (empty($templateId)) return;
		
		// テンプレート情報取得
		$ret = self::$_mainDb->getTemplate($templateId, $row);
		if (!$ret) return;
		
		$generator	= $row['tm_generator'];
		$version	= $row['tm_version'];		// テンプレートバージョン
		if ($generator != M3_TEMPLATE_GENERATOR_THEMLER) return;		// Themler
		
		// テンプレート選択メニューを表示
		$tmpl->setAttribute('select_subtemplate', 'visibility', 'visible');
		
		$subTemplateInfoFile = $this->gEnv->getTemplatesPath() . '/' . $templateId . '/templates/list.php';
		if (is_readable($subTemplateInfoFile)){
			// サブテンプレート情報ファイル読み込み
			require_once($subTemplateInfoFile);
			
			// $templatesInfoにサブテンプレートの情報が設定されているので取得
			if (!isset($this->subTemplateInfo)) $this->subTemplateInfo = array();
			if (!empty($templatesInfo)) $this->subTemplateInfo = $templatesInfo;
			
			foreach ($this->subTemplateInfo as $key => $templateInfo){
				$subTemplateId = $templateInfo['fileName'];
				$type = $templateInfo['kind'];
				if (empty($subTemplateId)) continue;
				if ($type == 'error404') continue;		// エラーメッセージ表示用の404タイプのサブテンプレートは表示しない
				
				$selected = '';
				if ($subTemplateId == $this->subTemplateId) $selected = 'selected';		// サブテンプレートID
				
				$row = array(
					'value'    => $this->convertToDispString($subTemplateId),
					'name'     => $this->convertToDispString($templateInfo['defaultTemplateCaption'] . '(' . $templateInfo['fileName'] . ')'),
					'selected' => $selected														// 選択中かどうか
				);
				$tmpl->addVars('subtemplate_list', $row);
				$tmpl->parseTemplate('subtemplate_list', 'a');
				
				$this->isExistsSubTemplate = true;		// サブテンプレートが存在するかどうか
			}
		}
	}
	/**
	 * デフォルトのサブテンプレートIDを取得
	 *
	 * @param string  $templateId	テンプレートID
	 * @return string				サブテンプレートID
	 */
	function getDefaultSubTemplateId($templateId)
	{
		$subTemplateId = '';
		
		$ret = self::$_mainDb->getTemplate($templateId, $row);
		if (!$ret) return $subTemplateId;
		
		$generator	= $row['tm_generator'];
		$version	= $row['tm_version'];		// テンプレートバージョン
		switch ($generator){
		case M3_TEMPLATE_GENERATOR_THEMLER:		// Themler
			// デフォルトのサブテンプレートIDを取得
			$subTemplateInfoFile = $this->gEnv->getTemplatesPath() . '/' . $templateId . '/templates/list.php';
			if (is_readable($subTemplateInfoFile)){
				// サブテンプレート情報ファイル読み込み
				require_once($subTemplateInfoFile);
				
				// $templatesInfoにサブテンプレートの情報が設定されているので取得
				if (!isset($this->subTemplateInfo)) $this->subTemplateInfo = array();
				if (!empty($templatesInfo)) $this->subTemplateInfo = $templatesInfo;

				foreach ($this->subTemplateInfo as $key => $templateInfo){
					$id = $templateInfo['fileName'];
					$type = $templateInfo['kind'];
					if (empty($id)) continue;
					
					if ($type == 'default'){
						$subTemplateId = $id;
						break;
					}
				}
			}
			break;
		}
		return $subTemplateId;
	}
}
?>
