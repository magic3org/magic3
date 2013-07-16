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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: featured_contentWidgetContainer.php 5550 2013-01-14 12:35:04Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/featured_contentDb.php');

class featured_contentWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $showReadMore;		// 「続きを読む」ボタンを表示
	private $readMoreTitle;		// 「続きを読む」ボタンタイトル
	private $showCreateDate;		// 表示項目(作成日)
	private $showModifiedDate;		// 表示項目(更新日)
	private $showPublishedDate;		// 表示項目(公開日)
	private $fieldInfoArray = array();			// 動画項目情報
	private $_contentCreated;	// コンテンツが取得できたかどうか
	private $viewItemsData = array();			// Joomla!ビュー用データ
	const DEFAULT_CONFIG_ID = 0;
	const CONTENT_TYPE = '';	// コンテンツタイプ
	const VIEW_CONTENT_TYPE = 'ct';		// 参照数カウント用コンテンツタイプ(将来的にはcontentを使用)
	const DEFAULT_READ_MORE = 'もっと読む';		// 「続きを読む」ボタンタイトル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new featured_contentDb();
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
		return 'index.tmpl.html';
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
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
	
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){// 定義データが取得できないときは終了
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// デフォルト値設定
		$inputEnabled = true;			// 入力の許可状態
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$this->readMoreTitle	= self::DEFAULT_READ_MORE;		// 「続きを読む」ボタンタイトル
		
		$this->showReadMore = $targetObj->showReadMore;		// 「続きを読む」ボタンを表示
		if (!empty($targetObj->readMoreTitle)) $this->readMoreTitle	= $targetObj->readMoreTitle;		// 「続きを読む」ボタンタイトル
		$leadContentCount	= $targetObj->leadContentCount;						// 先頭のコンテンツ数
		$columnContentCount	= $targetObj->columnContentCount;						// カラム部のコンテンツ数
		$columnCount		= $targetObj->columnCount;						// カラム数
		$this->showCreateDate		= $targetObj->showCreateDate;		// 表示項目(作成日)
		$this->showModifiedDate	= $targetObj->showModifiedDate;		// 表示項目(更新日)
		$this->showPublishedDate	= $targetObj->showPublishedDate;		// 表示項目(公開日)
		if (!empty($targetObj->fieldInfo)) $this->fieldInfoArray = $targetObj->fieldInfo;			// フィールド情報

		// ログインユーザでないときは、ユーザ制限のない項目だけ表示
		$all = false;
		if ($this->gEnv->isCurrentUserLogined()) $all = true;
		
		// データエラーチェック
		$contentIdArray = array();
		for ($i = 0; $i < count($this->fieldInfoArray); $i++){
			$contentId = $this->fieldInfoArray[$i]->contentId;		// コンテンツID
			$contentIdArray[] = $contentId;
		}
		
		//$contentIdArray = explode(',', $contentId);
		$this->db->getContentItems(self::CONTENT_TYPE, array($this, 'itemsLoop'), $contentIdArray, $this->langId, $all);
		if (!$this->_contentCreated){		// コンテンツが取得できなかったときはデフォルト言語で取得
			$this->db->getContentItems(self::CONTENT_TYPE, array($this, 'itemsLoop'), $contentIdArray, $this->gEnv->getDefaultLanguage(), $all);
		}

		// 「featured」用のJoomla!パラメータを設定
		$this->gEnv->setCurrentWidgetJoomlaParam(array('moduleclass_sfx' => 'featured'));
		
		// Joomla!ビュー用データを設定
		$viewData = array();
		$viewData['Items'] = $this->viewItemsData;
		// Magic3追加分
		$viewData['leadContentCount']	= $leadContentCount;			// 先頭のコンテンツ数
		$viewData['columnContentCount']	= $columnContentCount;			// カラム部のコンテンツ数
		$viewData['columnCount']		= $columnCount;					// カラム数
		$viewData['readMoreTitle']		= $this->readMoreTitle;		// 「続きを読む」ボタンタイトル
		$this->gEnv->setJoomlaViewData($viewData);
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$contentId = $fetchedRow['cn_id'];
		$contentName = $fetchedRow['cn_name'];
		
		// ページタイトルの設定
		if (empty($this->pageTitle)) $this->pageTitle = $contentName;		// 画面タイトル、パンくずリスト用タイトル
		
		// コンテンツ編集権限がある場合はボタンを表示
		$buttonList = '';
		if (!empty($this->showEdit) && $this->isSystemManageUser){
			$iconUrl = $this->gEnv->getRootUrl() . self::EDIT_ICON_FILE;		// 編集アイコン
			$iconTitle = '編集';
			$editImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . 
						'" alt="' . $iconTitle . '" title="' . $iconTitle . '" style="border:none;" />';
			$buttonList = '<a href="javascript:void(0);" onclick="editContent(' . $contentId . ');">' . $editImg . '</a>';
			switch (default_contentCommonDef::$_deviceType){		// デバイスごとの処理
				case 0:		// PC
				default:
					$buttonList = '<div style="text-align:right;position:absolute;top:' . $this->editIconPos . 'px;z-index:10;width:100%;">' . $buttonList . '</div>';
					break;
				case 1:		// 携帯
				case 2:		// スマートフォン
					$buttonList = '<div style="text-align:right;position:absolute;top:' . $this->editIconPos . 'px;right:5px;z-index:10;width:100%;">' . $buttonList . '</div>';
					break;
			}
			$this->editIconPos += self::EDIT_ICON_NEXT_POS;			// 編集アイコンの位置を更新
		}
		
		// コンテンツの出力形式を設定
		$this->inputPassword = false;			// パスワード入力かどうか
		if ($this->usePassword && !empty($fetchedRow['cn_password'])){			// パスワードによるコンテンツ閲覧制限、かつ、パスワードが設定されている
			// 認証状況をチェック
			if (!is_array($this->sessionParamObj->authContentId) || !in_array($contentId, $this->sessionParamObj->authContentId)) $this->inputPassword = true;			// パスワード入力かどうか
		}
		
		// ##### 表示コンテンツ作成 #####
		if ($this->inputPassword){			// パスワード入力のとき
			$this->tmpl->addVar('contentlist', 'type', 'input_password');		// パスワード入力画面
			
			$formName = self::PASSWORD_FORM_NAME . ($this->passwordFormCount + 1);		// パスワードチェック用フォーム名
			$funcName = str_replace('_', '', $formName);
			$this->passwordFormCount++;		// パスワード入力フォーム数
			
			$row = array(
				'func_name'	=> $funcName,	// パスワードチェック用関数
				'form_name'	=> $formName	// パスワードチェック用フォーム名
			);
			$this->tmpl->addVars('form_list', $row);
			$this->tmpl->parseTemplate('form_list', 'a');
			
			$contentText = self::$_configArray[default_contentCommonDef::$CF_PASSWORD_CONTENT];		// パスワード画面コンテンツ
		} else {
			// ビューカウントを更新
//			if (!$this->isSystemManageUser){		// システム運用者以上の場合はカウントしない
//				$this->gInstance->getAnalyzeManager()->updateContentViewCount(self::VIEW_CONTENT_TYPE, $fetchedRow['cn_serial'], $this->currentDay, $this->currentHour);
//			}
		
			// コンテンツタイトルの出力設定
//			if (empty($this->showTitle)) $this->tmpl->addVar('contentlist', 'type', 'hide_title');
			
			$formName = '';
			$funcName = '';
			
			$contentText = $fetchedRow['cn_html'];
			
			$accessPointUrl = $this->gEnv->getDefaultUrl();;		// コンテンツアクセスポイント
			$contentUrl		= $this->getUrl($accessPointUrl . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $fetchedRow['cn_id']);		// コンテンツへのリンク
			
			$isMoreContentExists = false;		// 続きのコンテンツがあるかどうか
			if (!empty($this->showReadMore)){		//「続きを読む」ボタンを表示のとき
				$contentArray = explode(M3_TAG_START . M3_TAG_MACRO_CONTENT_BREAK . M3_TAG_END, $contentText, 2);
				$contentText = $contentArray[0];
				
				if (count($contentArray) >= 2) $isMoreContentExists = true;		// 続きのコンテンツがあるかどうか
			}
/*			
			// ユーザ定義フィールド値取得
			// 埋め込む文字列はHTMLエスケープする
			$contentLayout = self::$_configArray[default_contentCommonDef::$CF_LAYOUT_VIEW_DETAIL];
			$fieldInfoArray = default_contentCommonDef::parseUserMacro($contentLayout);
			$fieldValueArray = $this->unserializeArray($fetchedRow['cn_option_fields']);
			$userFields = array();
			$fieldKeys = array_keys($fieldInfoArray);
			for ($i = 0; $i < count($fieldKeys); $i++){
				$key = $fieldKeys[$i];
				$value = $fieldValueArray[$key];
				$userFields[$key] = isset($value) ? $this->convertToDispString($value) : '';
			}
			
			// カレント言語がデフォルト言語と異なる場合はデフォルト言語の添付ファイルを取得
			$isDefaltContent = false;	// デフォルト言語のコンテンツを取得したかどうか
			if ($this->_isMultiLang && $this->_langId != $this->gEnv->getDefaultLanguage()){
				$ret = self::$_mainDb->getContentByContentId(default_contentCommonDef::$_contentType, $contentId, $this->gEnv->getDefaultLanguage(), $defaltContentRow);
				if ($ret) $isDefaltContent = true;
			}
			// コンテンツのサムネールを取得
			$thumbUrl = '';
			$thumbFilename = $fetchedRow['cn_thumb_filename'];
			if ($isDefaltContent) $thumbFilename = $defaltContentRow['cn_thumb_filename'];
			if (!empty($thumbFilename)){
				$thumbFilenameArray = explode(';', $thumbFilename);
				$thumbUrl = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_CONTENT, default_contentCommonDef::$_deviceType, $thumbFilenameArray[count($thumbFilenameArray) -1]);
			}

			// 添付ファイルダウンロード用リンク
			$attachFileTag = '';
			$attachContentSerial = $fetchedRow['cn_serial'];
			if ($isDefaltContent) $attachContentSerial = $defaltContentRow['cn_serial'];
			$ret = $this->gInstance->getFileManager()->getAttachFileInfo(default_contentCommonDef::$_viewContentType, $attachContentSerial, $attachFileRows);
			if ($ret){
				$optionAttr = '';		// 追加属性
				if ($this->jqueryMobileFormat) $optionAttr = 'rel="external"';			// jQueryMobile用のフォーマットで出力するかどうか
				
				$attachFileTag .= '<ul>';
				for ($i = 0; $i < count($attachFileRows); $i++){
					$fileTitle = $attachFileRows[$i]['af_title'];
					if (empty($fileTitle)) $fileTitle = $attachFileRows[$i]['af_filename'];
					
					// ダウンロード用のリンク
					$downloadUrl  = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET;
					$downloadUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
					$downloadUrl .= '&fileid=' . $attachFileRows[$i]['af_file_id'];
						
					$attachFileTag .= '<li>' . $this->convertToDispString($fileTitle);
					$attachFileTag .= '<a href="' . $this->convertUrlToHtmlEntity($this->getUrl($downloadUrl)) . '" ' . $optionAttr . '>';
					$attachFileTag .= '<img src="' . $this->getUrl($this->gEnv->getRootUrl() . self::DOWNLOAD_ICON_FILE) . '" width="' . self::DOWNLOAD_ICON_SIZE . '" height="' . self::DOWNLOAD_ICON_SIZE . '" title="ダウンロード" alt="ダウンロード" style="border:none;margin:0;padding:0;vertical-align:text-top;" />';
					$attachFileTag .= '</a></li>';
				}
				$attachFileTag .= '</ul>';
			}
			
			// 関連コンテンツリンク
			$relatedContentTag = '';	// 関連コンテンツリンク
			$relatedContent = $fetchedRow['cn_related_content'];
			if ($isDefaltContent) $relatedContent = $defaltContentRow['cn_related_content'];
			if (!empty($relatedContent)){
				$contentIdArray = array_map('trim', explode(',', $relatedContent));
				$ret = self::$_mainDb->getContentItemsById(default_contentCommonDef::$_contentType, $contentIdArray, $this->_langId, $rows);
				if ($ret){
					$relatedContentTag .= '<ul>';
					for ($i = 0; $i < count($rows); $i++){
						$relatedUrl = $accessPointUrl . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $rows[$i]['cn_id'];	// 関連コンテンツリンク先
						$relatedContentTag .= '<li><a href="' . $this->convertUrlToHtmlEntity($this->getUrl($relatedUrl)) . '">' . $this->convertToDispString($rows[$i]['cn_name']);
						$relatedContentTag .= '</a></li>';
					}
					$relatedContentTag .= '</ul>';
				}
			}
*/
			// コンテンツレイアウトに埋め込む
//			$contentParam = array_merge($userFields, array('BODY' => $contentText, 'FILES' => $attachFileTag, 'PAGES' => '', 'LINKS' => $relatedContentTag));
//			$contentText = $this->createDetailContent($contentParam);
			
			// Magic3マクロ変換
			// あらかじめ「CT_」タグをすべて取得する?
			$contentInfo = array();
			$contentInfo[M3_TAG_MACRO_CONTENT_BREAK] = '';		// コンテンツ置換キー(コンテンツ区切り)
			$contentInfo[M3_TAG_MACRO_CONTENT_ID] = $fetchedRow['cn_id'];			// コンテンツ置換キー(コンテンツID)
			$contentInfo[M3_TAG_MACRO_CONTENT_URL] = $contentUrl;			// コンテンツ置換キー(コンテンツURL)
			$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $contentName;			// コンテンツ置換キー(タイトル)
			$contentInfo[M3_TAG_MACRO_CONTENT_DESCRIPTION] = $fetchedRow['cn_description'];			// コンテンツ置換キー(簡易説明)
			$contentInfo[M3_TAG_MACRO_CONTENT_IMAGE] = $this->getUrl($thumbUrl);		// コンテンツ置換キー(画像)
			$contentInfo[M3_TAG_MACRO_CONTENT_UPDATE_DT] = $fetchedRow['cn_create_dt'];		// コンテンツ置換キー(更新日時)
			$contentInfo[M3_TAG_MACRO_CONTENT_START_DT] = $fetchedRow['cn_active_start_dt'];		// コンテンツ置換キー(公開開始日時)
			$contentInfo[M3_TAG_MACRO_CONTENT_END_DT] = $fetchedRow['cn_active_end_dt'];		// コンテンツ置換キー(公開終了日時)
			$contentText = $this->convertM3ToHtml($contentText, true/*改行コードをbrタグに変換*/, $contentInfo);
			
			// ##### HTMLヘッダ処理 #####
			// METAタグを設定
			if (!empty($this->headTitle) && !strEndsWith($this->headTitle, ',')) $this->headTitle .= ',';
			if (!empty($this->headDesc) && !strEndsWith($this->headDesc, ',')) $this->headDesc .= ',';
			if (!empty($this->headKeyword) && !strEndsWith($this->headKeyword, ',')) $this->headKeyword .= ',';
			$this->headTitle .= $fetchedRow['cn_meta_title'];
			$this->headDesc .= $fetchedRow['cn_meta_description'];
			$this->headKeyword .= $fetchedRow['cn_meta_keywords'];
		}
		
		$headClassStr = $this->gDesign->getDefaultContentHeadClassString();			// コンテンツヘッダ用CSSクラス
		
		$row = array(
			'class' => $headClassStr,		// コンテンツヘッダ用CSSクラス
			'title' => $this->convertToDispString($contentName),
			'content' => $contentText,	// コンテンツ
			'content_id' => $contentId,
			'func_name'	=> $funcName,	// パスワードチェック用関数
			'form_name'	=> $formName,	// パスワードチェック用フォーム名
			'button_list' => $buttonList	// 記事編集ボタン
		);
		$this->tmpl->addVars('contentlist', $row);
		$this->tmpl->parseTemplate('contentlist', 'a');
		
		// Joomla!ビュー用データ作成
		$viewItem = new stdClass;
		$viewItem->url		= $contentUrl;		// コンテンツへのリンク(Magic3拡張)
		$viewItem->id		= $contentId;	// コンテンツID
		$viewItem->title	= $contentName;	// コンテンツ名
		$viewItem->introtext	= $contentText;	// コンテンツ内容
		$viewItem->text = $viewItem->introtext;	// コンテンツ内容(Joomla!1.5テンプレート用)
		$viewItem->state	= 1;			// 表示モード(0=新着,1=表示済み)
		if (!empty($this->showReadMore) && $isMoreContentExists) $viewItem->readmore	= $this->readMoreTitle;			// 続きがある場合は「もっと読む」ボタンタイトルを設定

		// 以下は表示する項目のみ値を設定する
		if (!empty($this->showCreateDate)){		// 表示項目(作成日)
		}
		if (!empty($this->showModifiedDate)){		// 表示項目(更新日)
			$viewItem->modified	= $fetchedRow['cn_create_dt'];		// コンテンツ更新日時
		}
		if (!empty($this->showPublishedDate)){		// 表示項目(公開日)
			if ($fetchedRow['cn_active_start_dt'] != $this->gEnv->getInitValueOfTimestamp()) $viewItem->published	= $fetchedRow['cn_active_start_dt'];		// コンテンツ公開日時
		}
		$this->viewItemsData[] = $viewItem;			// Joomla!ビュー用データ
		
		// コンテンツが取得できた
		$this->_contentCreated = true;
		return true;
	}
}
?>
