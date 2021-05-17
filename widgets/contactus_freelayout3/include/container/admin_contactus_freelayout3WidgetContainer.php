<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    フリーレイアウトお問い合わせ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2009-2021 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_contactus_freelayout3WidgetContainer extends BaseAdminWidgetContainer
{
	private $serialNo;		// 選択中の項目のシリアル番号
	private $serialArray = array();			// 表示中のシリアル番号
	private $langId;		// 選択言語
	private $configId;		// 定義ID
	private $paramObj;		// パラメータ保存用オブジェクト
	private $typeArray;		// 項目タイプ
	private $langArray;		// 言語メニュー用
	private $fieldInfoArray = array();			// お問い合わせ項目情報
	private $confirmButtonId;		// 確認ボタンのタグID
	private $sendButtonId;		// 送信ボタンのタグID
	private $cancelButtonId;		// 送信キャンセルボタンのタグID
	private $resetButtonId;		// エリアリセットボタンのタグID
	private $css;				// 入力エリア作成用CSS
	private $script;			// 入力エリア作成用JavaScript
	const DEFAULT_NAME_HEAD = '名称未設定';			// デフォルトの設定名
	const DEFAULT_STR_REQUIRED = '<font color="red">*必須</font>';		// 「必須」表示用テキスト
	const DEFAULT_USER_EMAIL_SUBJECT = '送信内容ご確認(自動送信メール)';
	const DEFAULT_USER_EMAIL_FORMAT = "以下の内容でお問い合わせを送信しました。\n\n[#BODY#]";
	const UPLOAD_MAX_SIZE = '2M';		// アップロード最大ファイルサイズ(バイト)
	const UPLOAD_MAX_COUNT = 5;			// アップロードファイル最大数
	const UPLOAD_FILE_EXTENSION = 'png,gif,jpg,jpeg';		// アップロード可能なファイルの拡張子
	const OPEN_PANEL_ICON_FILE = '/images/system/plus32.png';		// 拡張エリア表示用アイコン
	const CLOSE_PANEL_ICON_FILE = '/images/system/minus32.png';		// 拡張エリア非表示用アイコン
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// お問い合わせ項目タイプ
		$this->typeArray = array(	array(	'name' => 'テキストボックス',			'value' => 'text'),
									array(	'name' => 'テキストボックス(Eメール)',	'value' => 'email'),
									array(	'name' => 'テキストボックス(計算)',		'value' => 'calc'),
									array(	'name' => 'テキストエリア',				'value' => 'textarea'),
									array(	'name' => 'セレクトメニュー',			'value' => 'select'),
									array(	'name' => 'チェックボックス',			'value' => 'checkbox'),
									array(	'name' => 'ラジオボタン',				'value' => 'radio'),
									array(	'name' => 'ファイルアップローダ',		'value' => 'file')
								);
		
		// 言語メニュータイプ
		$this->langArray = array(
									array(	'name' => '日本語',	'value' => 'ja'),
									array(	'name' => '英語',	'value' => 'en')
								);
	}
	/**
	 * ウィジェット初期化
	 *
	 * 共通パラメータの初期化や、以下のパターンでウィジェット出力方法の変更を行う。
	 * ・組み込みの_setTemplate(),_assign()を使用
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return 								なし
	 */
	function _init($request)
	{
		$task = $request->trimValueOf('task');
		if ($task == 'list'){		// 一覧画面
			// 通常のテンプレート処理を組み込みのテンプレート処理に変更。_setTemplate()、_assign()はキャンセル。
			$this->replaceAssignTemplate(self::ASSIGN_TEMPLATE_BASIC_CONFIG_LIST);		// 設定一覧(基本)
		}
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
		return 'admin.tmpl.html';
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
		return $this->createDetail($request);
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		// ページ定義IDとページ定義のレコードシリアル番号を取得
		$this->startPageDefParam($defSerial, $defConfigId, $this->paramObj);
		
		$userId		= $this->gEnv->getCurrentUserId();
		$act = $request->trimValueOf('act');
		$this->serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$this->configId = $request->trimValueOf('item_id');		// 定義ID
		if (empty($this->configId)) $this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
		
		// 入力値を取得
		$name	= $request->trimValueOf('item_name');			// 定義名
		$this->langId	= $request->trimValueOf('item_lang');		// メッセージ表示言語を取得
		if (empty($this->langId)) $this->langId = $this->gEnv->getDefaultLanguage();			// デフォルト言語
		$pageTitle = $request->trimValueOf('item_page_title');			// 画面タイトル
		$baseTemplate = $request->valueOf('item_html');		// 入力エリア作成用ベーステンプレート
		$this->css	= $request->valueOf('item_css');		// 入力エリア作成用CSS
		$this->script	= $request->valueOf('item_script');		// 入力エリア作成用JavaScript
		$this->confirmButtonId = $request->trimValueOf('item_confirm_button');		// 確認ボタンのタグID
		$this->sendButtonId = $request->trimValueOf('item_send_button');		// 送信ボタンのタグID
		$this->cancelButtonId = $request->trimValueOf('item_cancel_button');		// 送信キャンセルボタンのタグID
		$this->resetButtonId = $request->trimValueOf('item_reset_button');		// エリアリセットボタンのタグID
		$fieldCount = intval($request->trimValueOf('fieldcount'));		// お問い合わせ項目数
		$titles = $request->trimValueOf('item_title');		// お問い合わせ項目タイトル
		$descs = $request->trimValueOf('item_desc');		// お問い合わせ項目説明
		$types	= $request->trimValueOf('item_type');		// お問い合わせ項目タイプ
		$defs = $request->trimValueOf('item_def');		// お問い合わせ項目定義
		$values = $request->trimValueOf('required');		// お問い合わせ項目必須入力
		$requireds = array();
		if (strlen($values) > 0) $requireds = explode(',', $values);
		$values = $request->trimValueOf('disabled');		// 編集不可
		$disableds = array();
		if (strlen($values) > 0) $disableds = explode(',', $values);
		$values = $request->trimValueOf('titlevisible');		// お問い合わせ項目タイトル表示制御
		$titleVisibles = array();
		if (strlen($values) > 0) $titleVisibles = explode(',', $values);
		$values = $request->trimValueOf('alphabet');		// 入力制限半角英字
		$alphabets = array();
		if (strlen($values) > 0) $alphabets = explode(',', $values);
		$values = $request->trimValueOf('number');		// 入力制限半角数値
		$numbers = array();
		if (strlen($values) > 0) $numbers = explode(',', $values);
		$defaults = $request->trimValueOf('item_default');		// お問い合わせ項目デフォルト値
		$fieldIds = $request->trimValueOf('item_field_id');		// お問い合わせ項目フィールドID
		$calcs = $request->trimValueOf('item_calc');		// お問い合わせ項目計算式
		$emailSubject = $request->trimValueOf('item_email_subject');		// メールタイトル
		$emailReceiver = trim($request->valueOf('item_email_receiver'));	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
		$sendUserEmail = ($request->trimValueOf('item_send_user_email') == 'on') ? 1 : 0;	// 入力ユーザ向けメールを送信するかどうか
		$userEmailReply = $request->trimValueOf('item_user_email_reply');					// 入力ユーザ向けメール返信先メールアドレス
		$userEmailSubject = $request->trimValueOf('item_user_email_subject');				// 入力ユーザ向けメールタイトル
		$userEmailFormat = $request->trimValueOf('item_user_email_format');					// 入力ユーザ向けメール本文フォーマット
		$useArtisteer = ($request->trimValueOf('item_use_artisteer') == 'on') ? 1 : 0;					// Artisteer対応デザイン
		$uploadMaxSize = $request->trimValueOf('item_upload_max_size');		// アップロードファイル最大サイズ(バイト)
		$uploadMaxCount = $request->trimValueOf('item_upload_max_count');		// アップロードファイル最大数
		$uploadFileExtension = $request->trimValueOf('item_upload_file_extension');		// アップロード可能なファイルの拡張子
		$uploadArea = $request->valueOf('item_upload_area');		// ファイルアップロードエリア
		$msgConfirm = $request->trimValueOf('item_msg_confirm');		// 確認画面メッセージ
		$msgComplete = $request->trimValueOf('item_msg_complete');		// 完了画面メッセージ
		$contentComplete = $request->valueOf('item_content_complete');		// 完了画面コンテンツ
		$requiredLabel = $request->valueOf('item_required_label');		// 必須入力ラベル
		$accessKey = $request->trimValueOf('item_access_key');		// 発行アクセスキー
		$hideInComplete	= $request->trimCheckedValueOf('item_hide_in_complete');			// お問い合わせ項目を送信完了画面で隠すかどうか
		
		// 入力データを取得
		$this->fieldInfoArray = array();
		for ($i = 0; $i < $fieldCount; $i++){
			$newInfoObj = new stdClass;
			$newInfoObj->title	= $titles[$i];
			$newInfoObj->desc	= $descs[$i];
			$newInfoObj->type	= $types[$i];
			$newInfoObj->def	= $defs[$i];
			$newInfoObj->required	= $requireds[$i];
			$newInfoObj->disabled	= $disableds[$i];
			$newInfoObj->titleVisible	= $titleVisibles[$i];
			$newInfoObj->alphabet	= $alphabets[$i];
			$newInfoObj->number		= $numbers[$i];
			$newInfoObj->default	= $defaults[$i];
			$newInfoObj->fieldId	= $fieldIds[$i];
			$newInfoObj->calc		= $calcs[$i];
			$this->fieldInfoArray[] = $newInfoObj;
		}
				
		// Pタグを除去
		$baseTemplate = $this->gInstance->getTextConvManager()->deleteTag($baseTemplate, 'p');
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'add'){// 新規追加
			// 入力値のエラーチェック
			for ($i = 0; $i < $fieldCount; $i++){
				if (empty($titles[$i])){
					$this->setUserErrorMsg('タイトルが入力されていません');
					break;
				}
			}
			// フィールドIDのチェック
			for ($i = 0; $i < $fieldCount; $i++){
				if (!empty($fieldIds[$i])){
					if (preg_match("/[^a-z]/", $fieldIds[$i])){
						$this->setUserErrorMsg('フィールドIDは英小文字が使用可能です');
						break;
					}
				}
			}
			// 計算式のチェック
			for ($i = 0; $i < $fieldCount; $i++){
				if (!empty($calcs[$i])){
					if (preg_match("/[^0-9a-z-\+\*\/()]/", $calcs[$i])){
						$this->setUserErrorMsg('計算式はフィールドID、演算子「+-*/」、括弧「()」、数値が使用可能です');
						break;
					}
				}
			}
								
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					$this->setUserErrorMsg('名前が重複しています');
					break;
				}
			}
			
			// 確認メール用の設定のチェック
			if (!empty($sendUserEmail)){
				$this->checkInput($userEmailSubject, '確認メール件名');
				$this->checkInput($userEmailFormat, '確認メール本文');
			}

			$this->checkNumeric($uploadMaxCount, 'アップロード最大ファイル最数');
			$this->checkSingleByte($uploadMaxSize, 'アップロード最大ファイルサイズ');
			$this->checkInput($uploadFileExtension, 'アップロード可能なファイルの拡張子');
		
			// 発行アクセスキー
			$this->checkSingleByte($accessKey, '発行アクセスキー', true);
		
			// エラーなしの場合は、データを登録
			if ($this->getMsgCount() == 0){
				// ファイルアップロードエリアが空のときはデフォルトを取得
				if (empty($uploadArea)) $uploadArea = $this->gDesign->createDragDropFileUploadHtml();
				
				// ファイルアップロードエリアをマクロ表現に変換
				$uploadArea = $this->gInstance->getTextConvManager()->convToContentMacro($uploadArea);
				
				// データ修正
				$uploadFileExtension = implode(',', array_map('trim', explode(',', $uploadFileExtension)));
				
				// 必須入力ラベル
				if (empty($requiredLabel)) $requiredLabel = self::DEFAULT_STR_REQUIRED;
				
				// 追加オブジェクト作成
				$newObj = new stdClass;
				$newObj->name		= $name;// 表示名
				$newObj->langId		= $this->langId;		// メッセージ表示言語を取得
				$newObj->pageTitle = $pageTitle;			// 画面タイトル
				$newObj->baseTemplate = $baseTemplate;		// 入力エリア作成用ベーステンプレート
				$newObj->css	= $this->css;					// 入力エリア用CSS
				$newObj->script	= $this->script;			// 入力エリア作成用JavaScript
				$newObj->confirmButtonId = $this->confirmButtonId;		// 確認ボタンのタグID
				$newObj->sendButtonId	= $this->sendButtonId;		// 送信ボタンのタグID
				$newObj->cancelButtonId	= $this->cancelButtonId;		// 送信キャンセルボタンのタグID
				$newObj->resetButtonId	= $this->resetButtonId;		// エリアリセットボタンのタグID
				$newObj->emailSubject = $emailSubject;		// メールタイトル
				$newObj->emailReceiver = $emailReceiver;	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
				$newObj->sendUserEmail = $sendUserEmail;	// 入力ユーザ向けメールを送信するかどうか
				$newObj->userEmailReply = $userEmailReply;					// 入力ユーザ向けメール返信先メールアドレス
				$newObj->userEmailSubject = $userEmailSubject;				// 入力ユーザ向けメールタイトル
				$newObj->userEmailFormat = $userEmailFormat;				// 入力ユーザ向けメール本文フォーマット
				$newObj->useArtisteer = $useArtisteer;					// Artisteer対応デザイン
				$newObj->uploadMaxCount = $uploadMaxCount;		// アップロードファイル最大数
				$newObj->uploadMaxSize = $uploadMaxSize;			// アップロードファイル最大サイズ(バイト)
				$newObj->uploadFileExtension = $uploadFileExtension;	// アップロード可能なファイルの拡張子
				$newObj->uploadArea = $uploadArea;		// ファイルアップロードエリア
				$newObj->fieldInfo	= $this->fieldInfoArray;		// フィールド定義
				$newObj->msgConfirm = $msgConfirm;		// 確認画面メッセージ
				$newObj->msgComplete = $msgComplete;		// 完了画面メッセージ
				$newObj->contentComplete = $contentComplete;		// 完了画面コンテンツ
				$newObj->requiredLabel = $requiredLabel;		// 必須入力ラベル
				$newObj->accessKey = $accessKey;		// 発行アクセスキー
				$newObj->hideInComplete	= $hideInComplete;			// お問い合わせ項目を送信完了画面で隠すかどうか
				
				$ret = $this->addPageDefParam($defSerial, $defConfigId, $this->paramObj, $newObj);
				if ($ret){
					// ##### アクセスキー情報を登録 #####
					$this->gAccess->unegistAllSessionAccessKey($defConfigId);		// 一旦すべて削除
					if (!empty($accessKey)) $this->gAccess->registSessionAccessKey($accessKey, $defConfigId, 1/*発行*/);
					
					$this->setGuidanceMsg('データを追加しました');
					
					$this->configId = $defConfigId;		// 定義定義IDを更新
					$replaceNew = true;			// データ再取得
				} else {
					$this->setAppErrorMsg('データ追加に失敗しました');
				}
			}
		} else if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			for ($i = 0; $i < $fieldCount; $i++){
				if (empty($titles[$i])){
					$this->setUserErrorMsg('タイトルが入力されていません');
					break;
				}
			}
			// フィールドIDのチェック
			for ($i = 0; $i < $fieldCount; $i++){
				if (!empty($fieldIds[$i])){
					if (preg_match("/[^a-z]/", $fieldIds[$i])){
						$this->setUserErrorMsg('フィールドIDは英小文字が使用可能です');
						break;
					}
				}
			}
			// 計算式のチェック
			for ($i = 0; $i < $fieldCount; $i++){
				if (!empty($calcs[$i])){
					if (preg_match("/[^0-9a-z-\+\*\/()]/", $calcs[$i])){
						$this->setUserErrorMsg('計算式はフィールドID、演算子「+-*/」、括弧「()」、数値が使用可能です');
						break;
					}
				}
			}
			
			// 確認メール用の設定のチェック
			if (!empty($sendUserEmail)){
				$this->checkInput($userEmailSubject, '確認メール件名');
				$this->checkInput($userEmailFormat, '確認メール本文');
			}

			$this->checkNumeric($uploadMaxCount, 'アップロード最大ファイル最数');
			$this->checkSingleByte($uploadMaxSize, 'アップロード最大ファイルサイズ');
			$this->checkInput($uploadFileExtension, 'アップロード可能なファイルの拡張子');
			
			// 発行アクセスキー
			$this->checkSingleByte($accessKey, '発行アクセスキー', true);
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// ファイルアップロードエリアが空のときはデフォルトを取得
				if (empty($uploadArea)) $uploadArea = $this->gDesign->createDragDropFileUploadHtml();
				
				// ファイルアップロードエリアをマクロ表現に変換
				$uploadArea = $this->gInstance->getTextConvManager()->convToContentMacro($uploadArea);
				
				// データ修正
				$uploadFileExtension = implode(',', array_map('trim', explode(',', $uploadFileExtension)));
				
				// 必須入力ラベル
				if (empty($requiredLabel)) $requiredLabel = self::DEFAULT_STR_REQUIRED;
				
				// 現在の設定値を取得
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ウィジェットオブジェクト更新
					$targetObj->langId			= $this->langId;		// メッセージ表示言語を取得
					$targetObj->pageTitle		= $pageTitle;			// 画面タイトル
					$targetObj->baseTemplate	= $baseTemplate;		// 入力エリア作成用ベーステンプレート
					$targetObj->css				= $this->css;					// 入力エリア作成用CSS
					$targetObj->script			= $this->script;			// 入力エリア作成用JavaScript
					$targetObj->confirmButtonId = $this->confirmButtonId;		// 確認ボタンのタグID
					$targetObj->sendButtonId	= $this->sendButtonId;		// 送信ボタンのタグID
					$targetObj->cancelButtonId	= $this->cancelButtonId;		// 送信キャンセルボタンのタグID
					$targetObj->resetButtonId	= $this->resetButtonId;		// エリアリセットボタンのタグID
					$targetObj->emailSubject = $emailSubject;		// メールタイトル
					$targetObj->emailReceiver = $emailReceiver;	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
					$targetObj->sendUserEmail = $sendUserEmail;	// 入力ユーザ向けメールを送信するかどうか
					$targetObj->userEmailSubject = $userEmailSubject;				// 入力ユーザ向けメールタイトル
					$targetObj->userEmailReply = $userEmailReply;					// 入力ユーザ向けメール返信先メールアドレス
					$targetObj->userEmailFormat = $userEmailFormat;				// 入力ユーザ向けメール本文フォーマット
					$targetObj->useArtisteer = $useArtisteer;					// Artisteer対応デザイン
					$targetObj->uploadMaxCount = $uploadMaxCount;		// アップロードファイル最大数
					$targetObj->uploadMaxSize = $uploadMaxSize;			// アップロードファイル最大サイズ(バイト)
					$targetObj->uploadFileExtension = $uploadFileExtension;	// アップロード可能なファイルの拡張子
					$targetObj->uploadArea = $uploadArea;		// ファイルアップロードエリア
					$targetObj->fieldInfo	= $this->fieldInfoArray;		// フィールド定義
					$targetObj->msgConfirm = $msgConfirm;		// 確認画面メッセージ
					$targetObj->msgComplete = $msgComplete;		// 完了画面メッセージ
					$targetObj->contentComplete = $contentComplete;		// 完了画面コンテンツ
					$targetObj->requiredLabel = $requiredLabel;		// 必須入力ラベル
					$targetObj->accessKey = $accessKey;		// 発行アクセスキー
					$targetObj->hideInComplete	= $hideInComplete;			// お問い合わせ項目を送信完了画面で隠すかどうか
				}
				
				// 設定値を更新
				if ($ret) $ret = $this->updatePageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					// ##### アクセスキー情報を登録 #####
					$this->gAccess->unegistAllSessionAccessKey($defConfigId);		// 一旦すべて削除
					if (!empty($accessKey)) $this->gAccess->registSessionAccessKey($accessKey, $defConfigId, 1/*発行*/);
					
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'select'){	// 定義IDを変更
			$replaceNew = true;			// データ再取得
		} else {	// 初期起動時、または上記以外の場合
			// デフォルト値設定
			$this->configId = $defConfigId;		// 呼び出しウィンドウから引き継いだ定義ID
			$replaceNew = true;			// データ再取得
		}
		// 設定項目選択メニュー作成
		$this->createItemMenu();
				
		// 表示用データを取得
		if (empty($this->configId)){		// 新規登録の場合
			$this->tmpl->setAttribute('item_name_visible', 'visibility', 'visible');// 名前入力フィールド表示
			if ($replaceNew){		// データ再取得時
//				$name = $this->createDefaultName();			// デフォルト登録項目名
				$name = $this->createConfigDefaultName();			// デフォルト登録項目名
				$this->langId = $this->gEnv->getDefaultLanguage();		// メッセージ表示言語を取得
				$pageTitle = '';			// 画面タイトル
				$this->css = $this->getParsedTemplateData('default.tmpl.css', array($this, 'makeCss'));// デフォルト用のCSSを取得
				$this->script = '';			// 入力エリア作成用JavaScript
				$emailSubject = '';		// メールタイトル
				$emailReceiver = '';	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
				$sendUserEmail = 0;	// 入力ユーザ向けメールを送信するかどうか
				$userEmailReply = '';					// 入力ユーザ向けメール返信先メールアドレス
				$userEmailSubject = self::DEFAULT_USER_EMAIL_SUBJECT;				// 入力ユーザ向けメールタイトル
				$userEmailFormat = self::DEFAULT_USER_EMAIL_FORMAT;				// 入力ユーザ向けメール本文フォーマット
				$useArtisteer = 0;					// Artisteer対応デザイン
				$uploadMaxCount = self::UPLOAD_MAX_COUNT;		// アップロードファイル最大数
				$uploadMaxSize = self::UPLOAD_MAX_SIZE;		// アップロードファイル最大サイズ(バイト)
				$uploadFileExtension = self::UPLOAD_FILE_EXTENSION;		// アップロード可能なファイルの拡張子
				$uploadArea = $this->gDesign->createDragDropFileUploadHtml();		// ファイルアップロードエリア
				$this->fieldInfoArray = array();			// お問い合わせ項目情報
				$msgConfirm = '';		// 確認画面メッセージ
				$msgComplete = '';		// 完了画面メッセージ
				$contentComplete = '';		// 完了画面コンテンツ
				$requiredLabel = self::DEFAULT_STR_REQUIRED;		// 必須入力ラベル
				$accessKey = '';		// 発行アクセスキー
				$hideInComplete	= 0;			// お問い合わせ項目を送信完了画面で隠すかどうか
				
				// デフォルトのテンプレート作成
				$tagHead = $this->createTagIdHead();
				$this->confirmButtonId = $tagHead . '_confirm';		// 確認ボタンのタグID
				$this->sendButtonId = $tagHead . '_send';		// 送信用ボタンのタグID
				$this->cancelButtonId	= $tagHead . '_cancel';		// 送信キャンセルボタンのタグID
				$this->resetButtonId = $tagHead . '_reset';		// エリアリセットボタンのタグID
				$baseTemplate = $this->getParsedTemplateData('default.tmpl.html', array($this, 'makeBaseTemplate'));// デフォルトの入力エリア作成用ベーステンプレート
			}
			$this->serialNo = 0;
		} else {
			if ($replaceNew){// データ再取得時
				$ret = $this->getPageDefParam($defSerial, $defConfigId, $this->paramObj, $this->configId, $targetObj);
				if ($ret){
					$name	= $targetObj->name;// 名前
					$this->langId = $targetObj->langId;		// メッセージ表示言語を取得
					$pageTitle = $targetObj->pageTitle;			// 画面タイトル
					$baseTemplate = $targetObj->baseTemplate;		// 入力エリア作成用ベーステンプレート
					$this->css		= $targetObj->css;					// 入力エリア作成用CSS
					$this->script 	= $targetObj->script;			// 入力エリア作成用JavaScript
					$this->confirmButtonId = $targetObj->confirmButtonId;		// 確認ボタンのタグID
					$this->sendButtonId = $targetObj->sendButtonId;		// 送信ボタンのタグID
					$this->cancelButtonId = $targetObj->cancelButtonId;		// 送信キャンセルボタンのタグID
					$this->resetButtonId = $targetObj->resetButtonId;		// エリアリセットボタンのタグID
					$emailSubject = $targetObj->emailSubject;		// メールタイトル
					$emailReceiver = $targetObj->emailReceiver;	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
					$sendUserEmail = $targetObj->sendUserEmail;	// 入力ユーザ向けメールを送信するかどうか
					$userEmailReply = $targetObj->userEmailReply;					// 入力ユーザ向けメール返信先メールアドレス
					$userEmailSubject = $targetObj->userEmailSubject;				// 入力ユーザ向けメールタイトル
					$userEmailFormat = $targetObj->userEmailFormat;				// 入力ユーザ向けメール本文フォーマット
					$useArtisteer = $targetObj->useArtisteer;					// Artisteer対応デザイン
					$uploadMaxCount = $targetObj->uploadMaxCount;
					if (!isset($uploadMaxCount)) $uploadMaxCount = self::UPLOAD_MAX_COUNT;		// アップロードファイル最大数
					$uploadMaxSize = $targetObj->uploadMaxSize;
					if (!isset($uploadMaxSize)) $uploadMaxSize = self::UPLOAD_MAX_SIZE;		// アップロードファイル最大サイズ(バイト)
					$uploadFileExtension = $targetObj->uploadFileExtension;
					if (!isset($uploadFileExtension)) $uploadFileExtension = self::UPLOAD_FILE_EXTENSION;		// アップロード可能なファイルの拡張子
					$uploadArea = $targetObj->uploadArea;
					if (empty($uploadArea)) $uploadArea = $this->gDesign->createDragDropFileUploadHtml();		// ファイルアップロードエリア
					$uploadArea = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $uploadArea);				// アプリケーションルートを変換
					if (!empty($targetObj->fieldInfo)) $this->fieldInfoArray = $targetObj->fieldInfo;			// お問い合わせ項目情報
					$msgConfirm = $targetObj->msgConfirm;		// 確認画面メッセージ
					$msgComplete = $targetObj->msgComplete;		// 完了画面メッセージ
					$contentComplete = $targetObj->contentComplete;		// 完了画面コンテンツ
					$requiredLabel = $targetObj->requiredLabel;		// 必須入力ラベル
					if (empty($requiredLabel)) $requiredLabel = self::DEFAULT_STR_REQUIRED;
					$accessKey = $targetObj->accessKey;		// 発行アクセスキー
					$hideInComplete	= $targetObj->hideInComplete;			// お問い合わせ項目を送信完了画面で隠すかどうか
				}
			}
			$this->serialNo = $this->configId;
				
			// 新規作成でないときは、メニューを変更不可にする(画面作成から呼ばれている場合のみ)
			if (!empty($defConfigId) && !empty($defSerial)) $this->tmpl->addVar("_widget", "id_disabled", 'disabled');
		}
		
		// 言語選択メニュー作成
		$this->createLangMenu();
		
		// 追加用タイプメニュー作成
		$this->createTypeMenu();
		
		// お問い合わせ項目一覧作成
		$this->createFieldList();
		if (empty($this->fieldInfoArray)) $this->tmpl->setAttribute('field_list', 'visibility', 'hidden');// お問い合わせ項目情報一覧
		
		// 画面にデータを埋め込む
		if (!empty($this->configId)) $this->tmpl->addVar("_widget", "id", $this->configId);		// 定義ID
		$this->tmpl->addVar("item_name_visible", "name",	$this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "page_title",	$this->convertToDispString($pageTitle));			// 画面タイトル
		$this->tmpl->addVar("_widget", "html",	$baseTemplate);		// 入力エリア作成用ベーステンプレート
		$this->tmpl->addVar("_widget", "css",	$this->convertToDispString($this->css));		// 入力エリア作成用CSS
		$this->tmpl->addVar("_widget", "script",	$this->convertToDispString($this->script));		// 入力エリア作成用JavaScript
		$this->tmpl->addVar("_widget", "confirm_button",	$this->confirmButtonId);		// 確認ボタンのタグID
		$this->tmpl->addVar("_widget", "send_button",	$this->sendButtonId);		// 送信ボタンのタグID
		$this->tmpl->addVar("_widget", "cancel_button",	$this->cancelButtonId);		// 送信キャンセルボタンのタグID
		$this->tmpl->addVar("_widget", "reset_button",	$this->resetButtonId);		// エリアリセットボタンのタグID
		$tagStr = $this->confirmButtonId . '(確認ボタンのID), ' . $this->sendButtonId . '(送信ボタンのID), ' . 
						$this->cancelButtonId . '(送信キャンセルボタンのID), ' . $this->resetButtonId . '(リセットボタンのID)';
		$this->tmpl->addVar("_widget", "tag_id_str", $tagStr);// タグIDの表示
		$this->tmpl->addVar("_widget", "email_subject",	$emailSubject);		// メールタイトル
		$this->tmpl->addVar("_widget", "email_receiver",	$emailReceiver);	// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
		$visibleStr = '';
		if (!empty($sendUserEmail)) $visibleStr = 'checked';	
		$this->tmpl->addVar("_widget", "send_user_email", $visibleStr);						// 入力ユーザ向けメールを送信するかどうか
		$this->tmpl->addVar("_widget", "user_email_reply",	$userEmailReply);		// 入力ユーザ向けメール返信先メールアドレス
		$this->tmpl->addVar("_widget", "user_email_subject",	$userEmailSubject);		// 入力ユーザ向けメールタイトル
		$this->tmpl->addVar("_widget", "user_email_format",	$userEmailFormat);		// 入力ユーザ向けメール本文フォーマット
		$checked = '';
		if (!empty($useArtisteer)) $checked = 'checked'; 					
		$this->tmpl->addVar("_widget", "use_artisteer",	$checked);// Artisteer対応デザイン
		$this->tmpl->addVar("_widget", "upload_max_count",	$this->convertToDispString($uploadMaxCount));			// アップロードファイル最大数
		$this->tmpl->addVar("_widget", "upload_max_size",	$this->convertToDispString($uploadMaxSize));			// アップロードファイル最大サイズ(バイト)
		$this->tmpl->addVar("_widget", "upload_file_extension",	$this->convertToDispString($uploadFileExtension));		// アップロード可能なファイルの拡張子
		$this->tmpl->addVar("_widget", "upload_area",	$uploadArea);		// ファイルアップロードエリア
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);// 選択中のシリアル番号、IDを設定
		$this->tmpl->addVar('_widget', 'tag_start', M3_TAG_START . M3_TAG_MACRO_ITEM_KEY);		// 置換タグ(前)
		$this->tmpl->addVar('_widget', 'tag_end', M3_TAG_END);		// 置換タグ(後)
		$this->tmpl->addVar('_widget', 'msg_confirm', $this->convertToDispString($msgConfirm));		// 確認画面メッセージ
		$this->tmpl->addVar('_widget', 'msg_complete', $this->convertToDispString($msgComplete));		// 完了画面メッセージ
		$this->tmpl->addVar('_widget', 'content_complete', $contentComplete);		// 完了画面メッセージ
		$this->tmpl->addVar('_widget', 'required_label', $this->convertToDispString($requiredLabel));		// 必須入力ラベル
		$this->tmpl->addVar('_widget', 'access_key', $this->convertToDispString($accessKey));		// 発行アクセスキー
		$this->tmpl->addVar("_widget", "hide_in_complete_checked", $this->convertToCheckedString($hideInComplete));			// お問い合わせ項目を送信完了画面で隠すかどうか
		
		// 項目追加処理内のオプション領域制御
		$iconUrl = $this->gEnv->getRootUrl() . self::OPEN_PANEL_ICON_FILE;		// 拡張エリア表示用アイコン
		$iconTitle = 'オプションを表示';
		$openButton = '<a href="javascript:void(0);" class="button_open btn btn-sm btn-warning" role="button" rel="m3help" data-container="body" title="' . $iconTitle . '"><i class="glyphicon glyphicon-plus"></i></a>';
		$this->tmpl->addVar('_widget', 'open_button', $openButton);
		$iconUrl = $this->gEnv->getRootUrl() . self::CLOSE_PANEL_ICON_FILE;		// 拡張エリア非表示用アイコン
		$iconTitle = 'オプションを非表示';
		$closeButton = '<a href="javascript:void(0);" class="button_close btn btn-sm btn-warning" role="button" rel="m3help" data-container="body" title="' . $iconTitle . '" style="display:none;"><i class="glyphicon glyphicon-minus"></i></a>';
		$this->tmpl->addVar('_widget', 'close_button', $closeButton);
		
		// ボタンの表示制御
		if (empty($this->serialNo)){		// 新規追加項目を選択しているとき
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 「新規追加」ボタン
			
			// プレビューボタン作成
			$this->tmpl->addVar("_widget", "preview_disabled", 'disabled ');// 「プレビュー」ボタン
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 「更新」ボタン
			
			// このウィジェットがマップされているページサブIDを取得
			$subPageId = $this->gPage->getPageSubIdByWidget($this->gEnv->getDefaultPageId(), $this->gEnv->getCurrentWidgetId(), $defConfigId);
			$previewUrl = $this->gEnv->getDefaultUrl();
			if (!empty($subPageId)) $previewUrl .= '?sub=' . $subPageId;
			$this->tmpl->addVar("_widget", "preview_url", $this->getUrl($previewUrl));
		}
		
		// ページ定義IDとページ定義のレコードシリアル番号を更新
		$this->endPageDefParam($defSerial, $defConfigId, $this->paramObj);
	}
	/**
	 * 選択用メニューを作成
	 *
	 * @return なし						
	 */
	function createItemMenu()
	{
		if (!is_array($this->paramObj)) return;
		
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id = $this->paramObj[$i]->id;// 定義ID
			$targetObj = $this->paramObj[$i]->object;
			$name = $targetObj->name;// 定義名
			$selected = '';
			if ($this->configId == $id) $selected = 'selected';

			$row = array(
				'name' => $name,		// 名前
				'value' => $id,		// 定義ID
				'selected' => $selected	// 選択中の項目かどうか
			);
			$this->tmpl->addVars('title_list', $row);
			$this->tmpl->parseTemplate('title_list', 'a');
		}
	}
	/**
	 * お問い合わせ項目一覧を作成
	 *
	 * @return なし						
	 */
	function createFieldList()
	{
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$infoObj = $this->fieldInfoArray[$i];
			$title = $infoObj->title;// タイトル名
			$desc = $infoObj->desc;		// 説明
			$type = $infoObj->type;		// 項目タイプ
			$def = $infoObj->def;		// 項目定義
			$requiredCheck = '';
			if (!empty($infoObj->required)) $requiredCheck = 'checked';
			$disabledCheck = '';							// 編集不可
			if (!empty($infoObj->disabled)) $disabledCheck = 'checked';
			$titleVisibleCheck = '';
			if (!empty($infoObj->titleVisible)) $titleVisibleCheck = 'checked';
			$alphabetCheck = '';
			if (!empty($infoObj->alphabet)) $alphabetCheck = 'checked';
			$numberCheck = '';
			if (!empty($infoObj->number)) $numberCheck = 'checked';
			$default	= $infoObj->default;		// デフォルト値
			$fieldId	= $infoObj->fieldId;		// フィールドID
			$calc		= $infoObj->calc;			// 計算式
			
			// 行を作成
			$this->tmpl->clearTemplate('type_list2');
			
			for ($j = 0; $j < count($this->typeArray); $j++){
				$value = $this->typeArray[$j]['value'];
				$name = $this->typeArray[$j]['name'];

				$selected = '';
				if ($value == $type) $selected = 'selected';

				$tableLine = array(
					'value'    => $value,			// タイプ値
					'name'     => $this->convertToDispString($name),			// タイプ名
					'selected' => $selected			// 選択中かどうか
				);
				$this->tmpl->addVars('type_list2', $tableLine);
				$this->tmpl->parseTemplate('type_list2', 'a');
			}
			
			// オプション領域
			$iconUrl = $this->gEnv->getRootUrl() . self::OPEN_PANEL_ICON_FILE;		// 拡張エリア表示用アイコン
			$iconTitle = 'オプションを表示';
			$openButton = '<a href="javascript:void(0);" class="button_open btn btn-sm btn-warning" role="button" rel="m3help" data-container="body" title="' . $iconTitle . '"><i class="glyphicon glyphicon-plus"></i></a>';
			$iconUrl = $this->gEnv->getRootUrl() . self::CLOSE_PANEL_ICON_FILE;		// 拡張エリア非表示用アイコン
			$iconTitle = 'オプションを非表示';
			$closeButton = '<a href="javascript:void(0);" class="button_close btn btn-sm btn-warning" role="button" rel="m3help" data-container="body" title="' . $iconTitle . '" style="display:none;"><i class="glyphicon glyphicon-minus"></i></a>';
		
			$row = array(
				'title' => $this->convertToDispString($title),	// タイトル名
				'desc' => $this->convertToDispString($desc),	// 説明
				'def' => $this->convertToDispString($def),		// 定義情報
				'required' => $requiredCheck,							// 必須入力
				'disabled' => $disabledCheck,							// 編集不可
				'title_visible' => $titleVisibleCheck,			// タイトル表示制御
				'alphabet' => $alphabetCheck,			// 入力制限半角英字
				'number' => $numberCheck,			// 入力制限半角数値
				'default' => $this->convertToDispString($default),	// デフォルト値
				'field_id' => $this->convertToDispString($fieldId),		// フィールドID
				'calc' => $this->convertToDispString($calc),			// 計算式
				'root_url' => $this->convertToDispString($this->getUrl($this->gEnv->getRootUrl())),
				'open_button' => $openButton,
				'close_button' => $closeButton
			);
			$this->tmpl->addVars('field_list', $row);
			$this->tmpl->parseTemplate('field_list', 'a');
		}
	}
	/**
	 * デフォルトの名前を取得
	 *
	 * @return string	デフォルト名						
	 */
/*	function createDefaultName()
	{
		$name = self::DEFAULT_NAME_HEAD;
		for ($j = 1; $j < 100; $j++){
			$name = self::DEFAULT_NAME_HEAD . $j;
			// 設定名の重複チェック
			for ($i = 0; $i < count($this->paramObj); $i++){
				$targetObj = $this->paramObj[$i]->object;
				if ($name == $targetObj->name){		// 定義名
					break;
				}
			}
			// 重複なしのときは終了
			if ($i == count($this->paramObj)) break;
		}
		return $name;
	}*/
	/**
	 * タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createTypeMenu()
	{
		for ($i = 0; $i < count($this->typeArray); $i++){
			$value = $this->typeArray[$i]['value'];
			$name = $this->typeArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('type_list1', $row);
			$this->tmpl->parseTemplate('type_list1', 'a');
		}
	}
	/**
	 * 言語選択メニューを作成
	 *
	 * @return なし
	 */
	function createLangMenu()
	{
		for ($i = 0; $i < count($this->langArray); $i++){
			$value = $this->langArray[$i]['value'];
			$name = $this->langArray[$i]['name'];
			
			$row = array(
				'value'    => $value,			// タイプ値
				'name'     => $this->convertToDispString($name),			// タイプ名
				'selected' => $this->convertToSelectedString($value, $this->langId)			// 選択中かどうか
			);
			$this->tmpl->addVars('lang_list', $row);
			$this->tmpl->parseTemplate('lang_list', 'a');
		}
	}
	/**
	 * テンプレートデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeBaseTemplate($tmpl)
	{
		$tmpl->addVar("_tmpl", "widget_url",	$this->gEnv->getCurrentWidgetRootUrl());		// ウィジェットのURL
		$tmpl->addVar("_tmpl", "confirm_button_id",	$this->confirmButtonId);	// 確認用ボタンのタグID
		$tmpl->addVar("_tmpl", "send_button_id",	$this->sendButtonId);		// 送信用ボタンのタグID
		$tmpl->addVar("_tmpl", "cancel_button_id",	$this->cancelButtonId);		// 送信キャンセル用ボタンのタグID
		$tmpl->addVar("_tmpl", "reset_button_id",	$this->resetButtonId);		// エリアリセットボタンのタグID
	}
	/**
	 * CSSデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeCss($tmpl)
	{
	}
	/**
	 * inputタグID用のヘッダ文字列を作成
	 *
	 * @return string	ID
	 */
	function createTagIdHead()
	{
		return $this->gEnv->getCurrentWidgetId() . '_' . $this->getTempConfigId($this->paramObj);
	}
}
?>
