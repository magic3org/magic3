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
require_once($gEnvManager->getWidgetContainerPath('default_content') . '/admin_default_contentBaseWidgetContainer.php');

class admin_default_contentOtherWidgetContainer extends admin_default_contentBaseWidgetContainer
{
	const DEFAULT_MESSAGE_DENY = 'コンテンツを表示できません';		// アクセス不可の場合のメッセージ
			
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
		return 'admin_other.tmpl.html';
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
		$defaultLang	= $this->gEnv->getDefaultLanguage();
		$act = $request->trimValueOf('act');
		
		$showTitle	= ($request->trimValueOf('item_show_title') == 'on') ? 1 : 0;			// コンテンツタイトルを表示するかどうか
		$showMessageDeny = $request->trimValueOf('item_show_message_deny');					// アクセス不可の場合にメッセージを表示するかどうか
		$messageDeny = $request->trimValueOf('item_message_deny');							// アクセス不可の場合のメッセージ
		$showEdit	= ($request->trimValueOf('item_show_edit') == 'on') ? 1 : 0;			// 編集ボタンを表示するかどうか
		
		$useJQuery	= ($request->trimValueOf('item_use_jquery') == 'on') ? 1 : 0;			// jQueryスクリプトを作成するかどうか
		$useContentTemplate	= ($request->trimValueOf('item_use_content_template') == 'on') ? 1 : 0;			// コンテンツ単位のテンプレート設定を行うかどうか
		$usePassword	= ($request->trimValueOf('item_use_password') == 'on') ? 1 : 0;		// パスワードを使用するかどうか
		$passwordContent = $request->valueOf('item_password_content');					// パスワード入力画面用コンテンツ
		$layoutViewDetail = $request->valueOf('item_layout_view_detail');					// コンテンツレイアウト(詳細表示)
		$outputHead	= ($request->trimValueOf('item_output_head') == 'on') ? 1 : 0;		// ヘッダ出力するかどうか
		$headViewDetail = $request->valueOf('item_head_view_detail');					// ヘッダ出力(詳細表示)
		$autoGenerateAttachFileList = $request->trimValueOf('item_auto_generate_attach_file_list');	// 添付ファイルリストを自動作成
		
		// デバイスタイプごとの処理
		if (default_contentCommonDef::$_deviceType == 2){		// スマートフォンの場合
			$jQueryMobileFormat	= ($request->trimValueOf('item_jquery_mobile_format') == 'on') ? 1 : 0;			// jQueryMobile用のフォーマットで出力するかどうか
		}
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->showTitle	= $showTitle;			// コンテンツタイトルを表示するかどうか
				$paramObj->showMessageDeny = $showMessageDeny;					// アクセス不可の場合にメッセージを表示するかどうか
				$paramObj->messageDeny = $messageDeny;							// アクセス不可の場合のメッセージ
				$paramObj->showEdit	= $showEdit;			// 編集ボタンを表示するかどうか
				
				// デバイスタイプごとの処理
				if (default_contentCommonDef::$_deviceType == 2){		// スマートフォンの場合
					$paramObj->jQueryMobileFormat = $jQueryMobileFormat;			// jQueryMobile用のフォーマットで出力するかどうか
				}
				$ret = $this->updateWidgetParamObj($paramObj);
				
				if (empty($layoutViewDetail)) $layoutViewDetail = default_contentCommonDef::DEFAULT_CONTENT_LAYOUT;
				if (empty($headViewDetail)) $headViewDetail = default_contentCommonDef::DEFAULT_HEAD_VIEW_DETAIL;
				
				if ($ret) $ret = self::$_mainDb->updateConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_USE_JQUERY, $useJQuery);		// jQueryスクリプトを作成するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_USE_CONTENT_TEMPLATE, $useContentTemplate);// コンテンツ単位のテンプレート設定を行うかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_USE_PASSWORD, $usePassword);		// パスワードを使用するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_PASSWORD_CONTENT, $passwordContent);		// パスワード入力画面用コンテンツ
				if ($ret) $ret = self::$_mainDb->updateConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_LAYOUT_VIEW_DETAIL, $layoutViewDetail);	// コンテンツレイアウト(詳細表示)
				if ($ret) $ret = self::$_mainDb->updateConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_OUTPUT_HEAD, $outputHead);		// ヘッダ出力するかどうか																						
				if ($ret) $ret = self::$_mainDb->updateConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_HEAD_VIEW_DETAIL, $headViewDetail);	// ヘッダ出力(詳細表示)
				if ($ret) $ret = self::$_mainDb->updateConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::CF_AUTO_GENERATE_ATTACH_FILE_LIST, $autoGenerateAttachFileList);// 添付ファイルリストを自動作成
				
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値設定
			$showTitle = 0;			// コンテンツタイトルを表示するかどうか
			$showMessageDeny = 1;					// アクセス不可の場合にメッセージを表示するかどうか
			$messageDeny = self::DEFAULT_MESSAGE_DENY;							// アクセス不可の場合のメッセージ
			$showEdit = 1;			// 編集ボタンを表示するかどうか
			$useJQuery = 0;		// jQueryスクリプトを作成するかどうか
			$useContentTemplate = 0;// コンテンツ単位のテンプレート設定を行うかどうか
			$usePassword = 0;		// パスワードを使用するかどうか
			$passwordContent = '';		// パスワード入力画面用コンテンツ
			$layoutViewDetail = default_contentCommonDef::DEFAULT_CONTENT_LAYOUT;					// コンテンツレイアウト(詳細表示)
			$outputHead = 0;		// ヘッダ出力するかどうか
			$headViewDetail = default_contentCommonDef::DEFAULT_HEAD_VIEW_DETAIL;					// ヘッダ出力(詳細表示)
			
			// デバイスタイプごとの処理
			if (default_contentCommonDef::$_deviceType == 2){		// スマートフォンの場合
				$jQueryMobileFormat = 1;			// jQueryMobile用のフォーマットで出力するかどうか
			}
			
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$showTitle = $paramObj->showTitle;			// コンテンツタイトルを表示するかどうか
				$showMessageDeny = $paramObj->showMessageDeny;					// アクセス不可の場合にメッセージを表示するかどうか
				$messageDeny = $paramObj->messageDeny;							// アクセス不可の場合のメッセージ
				$showEdit = $paramObj->showEdit;			// 編集ボタンを表示するかどうか
				
				// デバイスタイプごとの処理
				if (default_contentCommonDef::$_deviceType == 2){		// スマートフォンの場合
					$jQueryMobileFormat = $paramObj->jQueryMobileFormat;			// jQueryMobile用のフォーマットで出力するかどうか
				}
			}
			
			$useJQuery = self::$_mainDb->getConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_USE_JQUERY);		// jQueryスクリプトを作成するかどうか
			$useContentTemplate = self::$_mainDb->getConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_USE_CONTENT_TEMPLATE);// コンテンツ単位のテンプレート設定を行うかどうか
			$usePassword = self::$_mainDb->getConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_USE_PASSWORD);		// パスワードを使用するかどうか
			$passwordContent = self::$_mainDb->getConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_PASSWORD_CONTENT);		// パスワード入力画面用コンテンツ
			$layoutViewDetail = self::$_mainDb->getConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_LAYOUT_VIEW_DETAIL);		// コンテンツレイアウト(詳細表示)
			$outputHead = self::$_mainDb->getConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_OUTPUT_HEAD);		// ヘッダ出力するかどうか
			$headViewDetail = self::$_mainDb->getConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::$CF_HEAD_VIEW_DETAIL);		// ヘッダ出力(詳細表示)
			$autoGenerateAttachFileList = self::$_mainDb->getConfig(default_contentCommonDef::$_contentType, default_contentCommonDef::CF_AUTO_GENERATE_ATTACH_FILE_LIST);		// 添付ファイルリストを自動作成
		}
		
		// 画面に書き戻す
		$checked = '';
		if (!empty($showTitle)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_title", $checked);		// コンテンツタイトルを表示するかどうか
		$checked = '';
		if (!empty($showMessageDeny)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_message_deny", $checked);		// アクセス不可の場合にメッセージを表示するかどうか
		$this->tmpl->addVar("_widget", "message_deny", $messageDeny);		// アクセス不可の場合のメッセージ
		$checked = '';
		if (!empty($showEdit)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "show_edit", $checked);		// 編集ボタンを表示するかどうか
		
		$checked = '';
		if (!empty($useJQuery)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_jquery", $checked);		// jQueryスクリプトを作成するかどうか
		$checked = '';
		if (!empty($useContentTemplate)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_content_template", $checked);// コンテンツ単位のテンプレート設定を行うかどうか
		$checked = '';
		if (!empty($usePassword)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_password", $checked);		// パスワードを使用するかどうか
		$this->tmpl->addVar("_widget", "password_content", $passwordContent);		// パスワード入力画面用コンテンツ
		$this->tmpl->addVar("_widget", "layout_view_detail", $layoutViewDetail);		// コンテンツレイアウト(詳細表示)
		$checked = '';
		if (!empty($outputHead)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "output_head_checked", $checked);		// ヘッダ出力するかどうか
		$this->tmpl->addVar("_widget", "head_view_detail", $headViewDetail);		// ヘッダ出力(詳細表示)
		if (empty($autoGenerateAttachFileList)){		// 添付ファイルリストを自動作成するかどうか
			$this->tmpl->addVar('_widget', 'auto_generate_list_user_checked', 'checked');		// ユーザが作成
		} else {
			$this->tmpl->addVar('_widget', 'auto_generate_list_auto_checked', 'checked');		// 自動生成
		}
		
		// デバイスタイプごとの画面作成
		switch (default_contentCommonDef::$_deviceType){
			case 0:		// PC
				break;
			case 1:		// 携帯
				break;
			case 2:		// スマートフォン
				$this->tmpl->setAttribute('option_smartphone', 'visibility', 'visible');
				
				$checked = '';
				if (!empty($jQueryMobileFormat)) $checked = 'checked';
				$this->tmpl->addVar("option_smartphone", "jquery_mobile_format_checked", $checked);		// jQueryMobile用のフォーマットで出力するかどうか
				break;
		}
	}
}
?>
