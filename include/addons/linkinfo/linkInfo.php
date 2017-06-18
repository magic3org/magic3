<?php
/**
 * Eコマースメール連携クラス
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
require_once(dirname(__FILE__) . '/linkInfoDb.php');

class linkInfo
{
	private $db;	// DB接続オブジェクト
	private $langId;		// 言語
	private $pageList = array();		// ページリスト
	private $contentList = array();		// コンテンツリスト
	private $contentType;			// コンテンツタイプ
	private $contentTypeArray;		// 主要コンテンツタイプ
	private $accessPointType;	// アクセスポイント種別
	// URL作成用
	private $_urlParamOrder;					// URLパラメータの並び順
	private $_useHierPage;						// 階層化ページを使用するかどうか
	private $_isMultiDomain;						// マルチドメイン運用かどうか
	// タイトルリスト、プレビュー用定数
	const DEFAULT_CONTENT_COUNT = 300;		// コンテンツリスト取得数
	const CONTENT_LENGTH = 300;			// プレビュー用コンテンツサイズ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gEnvManager;
		global $gPageManager;
		
		// DBオブジェクト作成
		$this->db = new linkInfoDb();
		
/*		$this->contentTypeArray = array(	M3_VIEW_TYPE_CONTENT,				// 汎用コンテンツ
								M3_VIEW_TYPE_PRODUCT,				// 製品
								M3_VIEW_TYPE_BBS,					// BBS
								M3_VIEW_TYPE_BLOG,				// ブログ
								M3_VIEW_TYPE_WIKI,				// wiki
								M3_VIEW_TYPE_USER,				// ユーザ作成コンテンツ
								M3_VIEW_TYPE_EVENT,				// イベント
								M3_VIEW_TYPE_PHOTO);				// フォトギャラリー*/
		$this->contentTypeArray = $gPageManager->getMainContentTypes();
		$this->langId = $gEnvManager->getDefaultLanguage();
		$this->accessPointType = array(	array('', 'PC用「/」'),
										array('m', '携帯用「/m」'),
										array('s', 'スマートフォン用「/s」'));	// アクセスポイント種別
		// URLパラメータ並び順
		$this->_urlParamOrder = $gPageManager->getUrlParamOrder();
	}
	/**
	 * リンク情報を作成し、Ajaxデータとして出力
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function outputAjaxData($request)
	{
		global $gEnvManager;
		global $gInstanceManager;
		global $gPageManager;
		
		// ##### ウィジェット出力処理中断 ######
		$gPageManager->abortWidget();
		
		// 入力値を取得
		$accessPoint = $request->trimValueOf('accesspoint');
				
		switch ($accessPoint){
			case '':			// PC用
			default:
				$defaultPageId = $gEnvManager->getDefaultPageId();
				$accessPoint = '';		// アクセスポイント修正
				break;
			case 'm':			// 携帯用
				$defaultPageId = $gEnvManager->getDefaultMobilePageId();
				break;
			case 's':			// スマートフォン用
				$defaultPageId = $gEnvManager->getDefaultSmartphonePageId();
				break;
		}

		// ##### Ajaxによるリンク情報取得 #####
		$act = $request->trimValueOf('act');
		if ($act == 'getpage'){		// ページ情報取得
			$this->db->getPageSubIdListWithWidget($defaultPageId, array($this, 'pageSubIdLoop'));

			// ページ選択メニューデータ
			$this->pageList = array_merge(array(array('', '-- 未選択 --')), $this->pageList);
			$this->pageList[] = array('_root', '[トップページ]');
			$gInstanceManager->getAjaxManager()->addData('pagelist', $this->pageList);
		} else if ($act == 'getcontenttype'){		// コンテンツ種別取得
			$contentTypeList = $this->getContentTypeList($accessPoint);
			$gInstanceManager->getAjaxManager()->addData('contenttype', $contentTypeList);
		} else if ($act == 'getcontentlist'){		// コンテンツ一覧取得
			$this->contentType = $request->trimValueOf('contenttype');
			$pageNo = $request->trimIntValueOf('page', '1');
			
			// コンテンツタイプで一覧を取得
			$pageNo = 1;		// ページ番号
			switch ($this->contentType){
				case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
					// コンテンツタイプ
					switch ($accessPoint){
						case '':			// PC用
						default:
							$contentType = '';
							break;
						case 'm':			// 携帯用。コンテンツタイプが携帯専用とPC共通がある。
							$contentType = 'mobile';	// デフォルトの汎用コンテンツタイプ
							$contentTypeList = $this->getContentTypeList($accessPoint);
							if (count($contentTypeList) > 0) $contentType = $contentTypeList[0][2];
							break;
						case 's':			// スマートフォン用
							$contentType = 'smartphone';
							break;
					}
		
					$this->db->getContentList($contentType, $this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, 0/*降順*/, array($this, 'contentLoop'));
					break;
				case M3_VIEW_TYPE_PRODUCT:	// 製品
					$this->db->getProductList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
					break;
				case M3_VIEW_TYPE_BBS:	// BBS
					break;
				case M3_VIEW_TYPE_BLOG:	// ブログ
					$this->db->getEntryList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
					break;
				case M3_VIEW_TYPE_WIKI:	// Wiki
					$this->db->getWikiList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
					break;
				case M3_VIEW_TYPE_USER:	// ユーザ作成コンテンツ
					break;
				case M3_VIEW_TYPE_EVENT:	// イベント
					$this->db->getEventList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
					break;
				case M3_VIEW_TYPE_PHOTO:	// フォトギャラリー
					$this->db->getPhotoList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
					break;
			}

			if (!empty($this->contentList)) $this->contentList = array_merge(array(array('', '-- 未選択 --')), $this->contentList);
			$gInstanceManager->getAjaxManager()->addData('contentlist', $this->contentList);
		} else if ($act == 'getcontent'){		// コンテンツ取得
			$this->contentType = $request->trimValueOf('contenttype');
			$contentId = $request->trimValueOf('contentid');
//			$contentText = '';		// プレビュー用コンテンツ
			
			// プレビュー用コンテンツ取得
			list($contentTitle, $contentText) = $this->getContentInfo($accessPoint, $this->contentType, $contentId, $this->langId);
			$gInstanceManager->getAjaxManager()->addData('content', $contentText);
			
		} else if ($act == 'getaccesspoint'){		// アクセスポイント取得
			$gInstanceManager->getAjaxManager()->addData('accesspoint', $this->accessPointType);
		} else if ($act == 'gettitle'){		// リンク先のコンテンツタイトル取得(メニュー定義画面(menudef,smenudef)からの呼び出し用)
			$url = $request->trimValueOf('url');
			$path = $gEnvManager->getMacroPath($url);
			$contentTitle = '';
			if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
				$path = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END . '/', '', $path);
				
				// アクセスポイントを取得
				$accessPoint = '';		// PC
				if (strStartsWith($path, M3_DIR_NAME_MOBILE . '/')){
					$accessPoint = M3_DIR_NAME_MOBILE;		// 携帯
				} else if (strStartsWith($path, M3_DIR_NAME_SMARTPHONE . '/')){
					$accessPoint = M3_DIR_NAME_SMARTPHONE;		// スマートフォン
				}
				
				// コンテンツタイプ、コンテンツID取得
				list($tmp, $queryStr) = explode('?', $path);
				list($this->contentType, $contentId) = $this->getContentType($queryStr);
				
				// コンテンツを取得
				list($contentTitle, $contentText) = $this->getContentInfo($accessPoint, $this->contentType, $contentId, $this->langId);
			} else {		// 外部リンクの場合
			}
			$gInstanceManager->getAjaxManager()->addData('title', $contentTitle);
		}
	}
	/**
	 * ページサブIDを配列に格納
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageSubIdLoop($index, $fetchedRow, $param)
	{
		$contentType = $fetchedRow['pn_content_type'];
		$name = $fetchedRow['pg_id'];
		if (!empty($contentType)) $name .= ' [' . $contentType . ']';
		$name .= ' - ' . $fetchedRow['pg_name'];
		
		$this->pageList[] = array($fetchedRow['pg_id'], $name);
		return true;
	}
	/**
	 * コンテンツ名を配列に格納
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function contentLoop($index, $fetchedRow, $param)
	{
		$contentId = $fetchedRow['id'];
		if ($this->contentType == M3_VIEW_TYPE_WIKI){		// コンテンツがWikiの場合の処理
			$name = $fetchedRow['name'];
			if (preg_match('/^\:/', $name)) return true;		// 定義データの場合は読み飛ばす
		} else {
			$name = $fetchedRow['name'] . ' [' . $contentId . ']';		// コンテンツ名
		}
		$this->contentList[] = array($contentId, $name);
		return true;
	}
	/**
	 * コンテンツ種別情報を取得
	 *
	 * @param string $accessPoint	アクセスポイント(「」=PC、「m」=携帯、「s」=スマートフォン)
	 * @return array				コンテンツ種別情報
	 */
	function getContentTypeList($accessPoint)
	{
		global $gEnvManager;

		$contentTypeArray = array(array(), array(), array());
		$pageIdArray = array($gEnvManager->getDefaultPageId(), $gEnvManager->getDefaultMobilePageId(), $gEnvManager->getDefaultSmartphonePageId());

		// 画面に配置しているウィジェットの主要コンテンツタイプを取得
		$ret = $this->db->getEditWidgetOnPage($this->langId, $pageIdArray, $this->contentTypeArray, $rows);
		if ($ret){
			$rowCount = count($rows);
			for ($i = 0; $i < $rowCount; $i++){
				$row = $rows[$i];
				switch ($row['pd_id']){		// アクセスポイントごとに分ける
					case $pageIdArray[0]:
					default:
						$index = 0;
						break;
					case $pageIdArray[1]:
						$index = 1;
						break;
					case $pageIdArray[2]:
						$index = 2;
						break;
				}
				$contentTypeArray[$index][] = $row;
			}
		}

		$contentTypeList = array();
		for ($i = 0; $i < count($this->accessPointType); $i++){
			if ($this->accessPointType[$i][0] == $accessPoint) break;
		}
		if ($i == count($this->accessPointType)) return $contentTypeList;
		$contentType = $contentTypeArray[$i];

		for ($i = 0; $i < count($contentType); $i++){
			$contentTitle = $this->getCurrentLangString($contentType[$i]['wd_content_name']);
			if (empty($contentTitle)) $contentTitle = $contentType[$i]['ls_value'];
			$contentTypeList[] = array($contentType[$i]['wd_type'], $contentTitle, $contentType[$i]['wd_content_info']);
		}
		return $contentTypeList;
	}
	
	/**
	 * コンテンツプレビュー用のテキストを作成
	 *
	 * @param string $src	元のコンテンツ
	 * @return string		作成したテキスト
	 */
	function createContentText($src)
	{
		global $gEnvManager;
		global $gInstanceManager;
		
		$contentText = $gInstanceManager->getTextConvManager()->htmlToText($src);

		// アプリケーションルートを変換
		$rootUrl = $this->getUrl($gEnvManager->getRootUrl());
		$contentText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $rootUrl, $contentText);

		// 登録したキーワードを変換
		$gInstanceManager->getTextConvManager()->convByKeyValue($contentText, $contentText);

		// 検索結果用にテキストを詰める。改行、タブ、スペース削除。
		$contentText = str_replace(array("\r", "\n", "\t", " "), '', $contentText);

		// 文字列長を修正
		if (function_exists('mb_strimwidth')){
			$contentText = mb_strimwidth($contentText, 0, self::CONTENT_LENGTH, '…');
		} else {
			$contentText = substr($contentText, 0, self::CONTENT_LENGTH) . '...';
		}
		return $contentText;
	}
	
	/**
	 * コンテンツ詳細画面のURLを取得
	 *
	 * @param strint $accessPoint	アクセスポイント(空文字列=PC用,m=携帯用,s=スマートフォン用)
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @param string $langId		言語ID(デフォルト言語の場合は空文字列)
	 * @return string				URL。取得できない場合は空文字列。
	 */
	function getContentUrl($accessPoint, $contentType, $contentId, $langId = '')
	{
		global $gEnvManager;
		
		$url = '';
		
		switch ($accessPoint){
		case '':			// PC用
		default:
			$url = $gEnvManager->getDefaultUrl();
			break;
		case 'm':			// 携帯用
			$url = $gEnvManager->getDefaultMobileUrl();
			break;
		case 's':			// スマートフォン用
			$url = $gEnvManager->getDefaultSmartphoneUrl();
			break;
		}
		
		// コンテンツごとのパラメータ追加
		switch ($contentType){
		case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツの場合
			$url .= '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
			break;
		case M3_VIEW_TYPE_USER:		// ユーザ作成コンテンツの場合
			// コンテンツへのリンクを作成
			$url .= '?' . M3_REQUEST_PARAM_ROOM_ID . '=' . $contentId;
			break;
		case M3_VIEW_TYPE_BLOG:			// ブログコンテンツの場合
			$url .= '?' . M3_REQUEST_PARAM_BLOG_ENTRY_ID . '=' . $contentId;
			break;
		case M3_VIEW_TYPE_PRODUCT:			// 商品情報の場合
			$url .= '?' . M3_REQUEST_PARAM_PRODUCT_ID . '=' . $contentId;
			break;
		case M3_VIEW_TYPE_EVENT:			// イベント情報の場合
			$url .= '?' . M3_REQUEST_PARAM_EVENT_ID . '=' . $contentId;
			break;
		case M3_VIEW_TYPE_BBS:			// BBSスレッド情報の場合
			$url .= '?' . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $contentId;
			break;
		case M3_VIEW_TYPE_PHOTO:			// フォト情報の場合
			$url .= '?' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $contentId;
			break;
		case M3_VIEW_TYPE_WIKI:			// Wikiコンテンツの場合
			$url .= '?' . $contentId;
			break;
		default:
			break;
		}
		
		if (!empty($url) || !empty($langId)){
			if ($langId != $gEnvManager->getDefaultLanguage()) $url .= '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $langId;
		}
		return $url;
	}
	
	/**
	 * コンテンツプレビュー用のテキストとタイトルを取得
	 *
	 * @param strint $accessPoint	アクセスポイント
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @param string $langId		言語ID
	 * @return array				コンテンツタイトル、コンテンツテキストの配列
	 */
	function getContentInfo($accessPoint, $contentType, $contentId, $langId)
	{
		$contentText = '';
		$contentTitle = '';
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
				// コンテンツタイプ
				switch ($accessPoint){
					case '':			// PC用
					default:
						$contentType = '';
						break;
					case 'm':			// 携帯用
						$contentType = 'mobile';
						break;
					case 's':			// スマートフォン用
						$contentType = 'smartphone';
						break;
				}
				$ret = $this->db->getContent($contentType, $contentId, $langId, $row);
				if ($ret){
					$contentTitle = $row['cn_name'];
					$contentText = $this->createContentText($row['cn_html']);
				}
				break;
			case M3_VIEW_TYPE_PRODUCT:	// 製品
				$ret = $this->db->getProduct($contentId, $langId, $row);
				if ($ret){
					$contentTitle = $row['pt_name'];
					$contentText = $this->createContentText($row['pt_description']);
				}
				break;
			case M3_VIEW_TYPE_BBS:	// BBS
				break;
			case M3_VIEW_TYPE_BLOG:	// ブログ
				$ret = $this->db->getEntry($contentId, $langId, $row);
				if ($ret){
					$contentTitle = $row['be_name'];
					$contentText = $this->createContentText($row['be_html']);
				}
				break;
			case M3_VIEW_TYPE_WIKI:	// Wiki
				$contentTitle = $contentId;			// コンテンツIDを返す
				break;
			case M3_VIEW_TYPE_USER:	// ユーザ作成コンテンツ
				break;
			case M3_VIEW_TYPE_EVENT:	// イベント
				$ret = $this->db->getEvent($contentId, $langId, $row);
				if ($ret){
					$contentTitle = $row['ee_name'];
					$contentText = $this->createContentText($row['ee_html']);
				}
				break;
			case M3_VIEW_TYPE_PHOTO:	// フォトギャラリー
				$ret = $this->db->getPhoto($contentId, $langId, $row);
				if ($ret){
					$contentTitle = $row['ht_name'];
					$contentText = $this->createContentText($row['ht_description']);
				}
				break;
		}
		return array($contentTitle, $contentText);
	}
	/**
	 * コンテンツの公開状態を取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @param string $contentId		コンテンツID
	 * @return array				公開状態(0=非公開、1=公開)
	 */
	function getContentStatus($contentType, $contentId)
	{
		global $gEnvManager;
	
		$langId = $gEnvManager->getDefaultLanguage();
		$now = date("Y/m/d H:i:s");	// 現在日時
		$status = 0;		// 公開状態(非公開)
		
		switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
				$ret = $this->db->getContent(''/*PC用*/, $contentId, $langId, $row);
				if ($ret){
					$startDt = $row['cn_active_start_dt'];
					$endDt = $row['cn_active_end_dt'];
					if ($row['cn_visible']) $status = $this->_isActive($startDt, $endDt, $now);// 表示可能
				}
				break;
			case M3_VIEW_TYPE_PRODUCT:	// 製品
				$ret = $this->db->getProduct($contentId, $langId, $row);
				if ($ret){
					$status = $row['pt_visible'];
				}
				break;
			case M3_VIEW_TYPE_BBS:	// BBS
				break;
			case M3_VIEW_TYPE_BLOG:	// ブログ
				$ret = $this->db->getEntry($contentId, $langId, $row);
				if ($ret){
					$startDt = $row['be_active_start_dt'];
					$endDt = $row['be_active_end_dt'];
					if ($row['be_status'] == 2/*公開*/) $status = $this->_isActive($startDt, $endDt, $now);// 表示可能
				}
				break;
			case M3_VIEW_TYPE_WIKI:	// Wiki
				$status = 1;
				break;
			case M3_VIEW_TYPE_USER:	// ユーザ作成コンテンツ
				break;
			case M3_VIEW_TYPE_EVENT:	// イベント
				$ret = $this->db->getEvent($contentId, $langId, $row);
				if ($ret){
					$status = $row['ee_status'];
				}
				break;
			case M3_VIEW_TYPE_PHOTO:	// フォトギャラリー
				$ret = $this->db->getPhoto($contentId, $langId, $row);
				if ($ret){
					$startDt = $row['ht_active_start_dt'];
					$endDt = $row['ht_active_end_dt'];
					if ($row['ht_visible']) $status = $this->_isActive($startDt, $endDt, $now);// 表示可能
				}
				break;
		}
		return $status;
	}
	/**
	 * 期間から公開可能かチェック
	 *
	 * @param timestamp	$startDt		公開開始日時
	 * @param timestamp	$endDt			公開終了日時
	 * @param timestamp	$now			基準日時
	 * @return bool						true=公開可能、false=公開不可
	 */
	function _isActive($startDt, $endDt, $now)
	{
		global $gEnvManager;
		
		$isActive = false;		// 公開状態

		if ($startDt == $gEnvManager->getInitValueOfTimestamp() && $endDt == $gEnvManager->getInitValueOfTimestamp()){
			$isActive = true;		// 公開状態
		} else if ($startDt == $gEnvManager->getInitValueOfTimestamp()){
			if (strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		} else if ($endDt == $gEnvManager->getInitValueOfTimestamp()){
			if (strtotime($now) >= strtotime($startDt)) $isActive = true;		// 公開状態
		} else {
			if (strtotime($startDt) <= strtotime($now) && strtotime($now) < strtotime($endDt)) $isActive = true;		// 公開状態
		}
		return $isActive;
	}
	/**
	 * URLクエリー文字列からコンテンツタイプを取得
	 *
	 * @param string $queryStr	クエリー文字列
	 * @return array			コンテンツタイプとコンテンツIDの配列
	 */
	function getContentType($queryStr)
	{
		$contentType = '';
		$contentId = '';
		
		// URLクエリー文字列を解析
		parse_str($queryStr, $queryArray);
		if (count($queryArray) > 0){
			reset($queryArray);
			$firstKey = key($queryArray);
			$contentId = $queryArray[$firstKey];
			
			switch ($firstKey){
				case M3_REQUEST_PARAM_CONTENT_ID:		// 汎用コンテンツID
				case M3_REQUEST_PARAM_CONTENT_ID_SHORT:
					$contentType = M3_VIEW_TYPE_CONTENT;		// 汎用コンテンツ
					break;
				case M3_REQUEST_PARAM_PRODUCT_ID:		// 製品ID
				case M3_REQUEST_PARAM_PRODUCT_ID_SHORT:
					$contentType = M3_VIEW_TYPE_PRODUCT;		// 商品情報(Eコマース)
					break;
				case M3_REQUEST_PARAM_EVENT_ID:	// イベントID
				case M3_REQUEST_PARAM_EVENT_ID_SHORT:
					$contentType = M3_VIEW_TYPE_EVENT;		// イベント情報
					break;
				case M3_REQUEST_PARAM_PHOTO_ID:	// 画像ID
				case M3_REQUEST_PARAM_PHOTO_ID_SHORT:
					$contentType = M3_VIEW_TYPE_PHOTO;		// フォトギャラリー
					break;
				case M3_REQUEST_PARAM_BLOG_ENTRY_ID:	// ブログ記事ID
				case M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT:
					$contentType = M3_VIEW_TYPE_BLOG;		// ブログ
					break;
				default:
					$contentId = '';
			}
			
			if (empty($contentType)){		// コンテンツタイプが確定できないとき
				$queryArray = explode('&', $queryStr);// 「&」で分割
				for ($i = 0; $i < count($queryArray); $i++){
					$line = $queryArray[$i];
					$pos = strpos($line, '=');
					if ($pos === false){		// 「=」なしのパラメータはwikiパラメータとする
						$contentType = M3_VIEW_TYPE_WIKI;		// wiki
						$contentId = $line;			// コンテンツID
						break;
					}
				}
			}
		} else {		// URLトップの場合
		}
		return array($contentType, $contentId);
	}
	/**
	 * 多言語対応文字列から現在の言語の文字列を取得
	 *
	 * @param string $str		多言語対応文字列
	 * @return string			現在の言語文字列(存在しない場合はデフォルト言語文字列)
	 */
	function getCurrentLangString($str)
	{
		global $gEnvManager;
		
		$langs = $this->unserializeLangArray($str);
		$langStr = $langs[$gEnvManager->getCurrentLanguage()];
		if (isset($langStr)){
			return $langStr;
		} else {
			$langStr = $langs[$gEnvManager->getDefaultLanguage()];
			if (isset($langStr)){
				return $langStr;
			} else {
				return '';
			}
		}
	}
	/**
	 * 多言語対応文字列をテキストの連想配列に変換
	 *
	 * @param string $str		多言語対応文字列
	 * @return array			言語ごとの文字列の連想配列
	 */
	function unserializeLangArray($str)
	{
		global $gEnvManager;
		
		$langs = array();
		if (empty($str)) return $langs;
		
		$itemArray = explode(M3_TAG_START . M3_TAG_MACRO_SEPARATOR . M3_TAG_END, $str);		// セパレータ分割
		for ($i = 0; $i < count($itemArray); $i++){
			$line = $itemArray[$i];
			if (empty($line)) continue;
			$pos = strpos($line, M3_LANG_SEPARATOR);		// 言語ID取得
			if ($pos === false){		// 言語IDがないときはデフォルトの言語IDを使用
				$langId = $gEnvManager->getDefaultLanguage();
				$langStr = $line;
			} else {
				list($langId, $langStr) = explode(M3_LANG_SEPARATOR, $line, 2);
				if (empty($langId)) continue;
			}
			$langs[$langId] = $langStr;
		}
		return $langs;
	}
	/**
	 * URLを作成
	 *
	 * ・ページのSSL設定状況に応じて、SSL用URLに変換
	 *
	 * @param string $path				URL作成用のパス
	 * @param bool $isLink				作成するURLがリンクかどうか。リンクの場合はリンク先のページのSSLの状態に合わせる
	 * @param string,array $param		URLに付加するパラメータ
	 * @return string					作成したURL
	 */
	function getUrl($path, $isLink = false, $param = '')
	{
		global $gEnvManager;
		global $gPageManager;
		
		$destPath = '';
		$path = trim($path);
		
		// URLの示すファイルタイプを取得
		if ($gEnvManager->getUseSsl()){		// SSLを使用する場合
			// 現在のページがSSL使用設定になっているかどうかを取得
			$currentPageId = $gEnvManager->getCurrentPageId();
			$currentPageSubId = $gEnvManager->getCurrentPageSubId();
			$isSslPage = $gPageManager->isSslPage($currentPageId, $currentPageSubId);
			
			$baseUrl = $gEnvManager->getRootUrl();
			$sslBaseUrl = $gEnvManager->getSslRootUrl();		// SSL用URLが設定されている場合、設定値を使用
			
			// パスを解析
			if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
				$relativePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '', $path);
				if (empty($relativePath)){			// Magic3のルートURLの場合
					if ($isLink){		// リンクタイプのとき
						$destPath = $baseUrl;
					} else {		// リンクでないとき
						// 現在のページのSSLの状態に合わせる
						if ($isSslPage){
							$destPath = $sslBaseUrl;
						} else {
							$destPath = $baseUrl;
						}
					}
					// トップページへのリンクの場合とどう区別するか→トップページへのリンクの場合はフルパスで指定?
				} else {
					$destPath = $this->_createUrlByRelativePath($isSslPage, $baseUrl, $sslBaseUrl, $relativePath, $param, $isLink);
				}
			} else if (strncasecmp($path, 'http://', strlen('http://')) == 0 || strncasecmp($path, 'https://', strlen('https://')) == 0){				// 絶対パスURLのとき
				// パスを解析
				$relativePath = str_replace($baseUrl, '', $path);		// ルートURLからの相対パスを取得
				if (empty($relativePath)){			// Magic3のルートURLの場合
					if ($isLink){		// リンクタイプのとき
						$destPath = $baseUrl;
					} else {		// リンクでないとき
						// 現在のページのSSLの状態に合わせる
						if ($isSslPage){
							$destPath = $sslBaseUrl;
						} else {
							$destPath = $baseUrl;
						}
					}
					// トップページへのリンクの場合とどう区別するか→トップページへのリンクの場合はフルパスで指定?
				} else if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?')){		// ルートURL配下のとき
					$destPath = $this->_createUrlByRelativePath($isSslPage, $baseUrl, $sslBaseUrl, $relativePath, $param, $isLink);
				} else {		// ルートURL以外のURLのとき
					$destPath = $path;
				}
			} else {		// 相対パスのとき
			}
		} else {		// SSLを使用しない場合
			if ($this->_useHierPage){		// 階層化ページ使用のとき
				$createPath = true;		// パスを生成するかどうか
				$baseUrl = $gEnvManager->getRootUrl();
				$sslBaseUrl = $gEnvManager->getSslRootUrl();		// SSL用URLが設定されている場合、設定値を使用
				if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
					$relativePath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '', $path);
				} else if (strncasecmp($path, 'http://', strlen('http://')) == 0 || strncasecmp($path, 'https://', strlen('https://')) == 0){				// 絶対パスURLのとき
					$relativePath = str_replace($baseUrl, '', $path);		// ルートURLからの相対パスを取得
					if (strStartsWith($relativePath, '/') || strStartsWith($relativePath, '?')){		// ルートURL配下のとき
					} else {		// ルートURL以外のURLのとき
						$createPath = false;		// パスを生成するかどうか
					}
				}
				if ($createPath){		// パスを生成するかどうか
					$destPath = $this->_createUrlByRelativePath(false, $baseUrl, $sslBaseUrl, $relativePath, $param);
				} else {
					$destPath = $path;
				}
			} else {
				if (strStartsWith($path, M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END)){		// Magic3のルートURLマクロのとき
					$destPath = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $gEnvManager->getRootUrl(), $path);
				} else {
					$destPath = $path;
				}
			}
		}
		// マルチドメイン運用の場合はパスを修正
		if ($this->_isMultiDomain){
			if ($gEnvManager->getIsSmartphoneSite()){		// スマートフォンサイトの場合
				$domainUrl = $gEnvManager->getDefaultSmartphoneUrl(false/*ファイル名なし*/);
				$relativePath = str_replace($domainUrl . '/' . M3_DIR_NAME_SMARTPHONE, '', $destPath);
				if (strStartsWith($relativePath, '/')){
					$destPath = $domainUrl . $relativePath;
				} else {
					// メインのドメインの場合はアクセスポイント用ドメインに変換
					$relativePath = str_replace(M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_SMARTPHONE, '', $destPath);
					if (strStartsWith($relativePath, '/')) $destPath = $domainUrl . $relativePath;
				}
			} else if ($gEnvManager->getIsMobileSite()){		// 携帯サイトの場合
				$domainUrl = $gEnvManager->getDefaultMobileUrl(false, false/*ファイル名なし*/);
				$relativePath = str_replace($domainUrl . '/' . M3_DIR_NAME_MOBILE, '', $destPath);
				if (strStartsWith($relativePath, '/')){
					$destPath = $domainUrl . $relativePath;
				} else {
					// メインのドメインの場合はアクセスポイント用ドメインに変換
					$relativePath = str_replace(M3_SYSTEM_ROOT_URL . '/' . M3_DIR_NAME_MOBILE, '', $destPath);
					if (strStartsWith($relativePath, '/')) $destPath = $domainUrl . $relativePath;
				}
			}
		}
		return $destPath;
	}
	/**
	 * コンテンツ編集用のメインウィジェットを取得
	 *
	 * @param string $contentType	コンテンツタイプ
	 * @return string				ウィジェットID
	 */
	function getContentEditWidget($contentType)
	{
		$widgetId = '';
		
		// 画面に配置しているウィジェットの主要コンテンツタイプを取得
		$ret = $this->db->getContentEditWidget($contentType, 0/*デバイスタイプはPCに限定*/, $rows);
		if ($ret) $widgetId = $rows[0]['wd_id'];
		return $widgetId;
	}
	/**
	 * ジョブ起動用のメインウィジェットを取得
	 *
	 * @param string $jobType		ジョブタイプ
	 * @return string				ウィジェットID
	 */
	function getJobWidget($jobType)
	{
		switch ($jobType){
		case M3_JOB_TYPE_MAIL:		// メール配信は会員情報に付属
			$contentType = M3_VIEW_TYPE_MEMBER;
			break;
		default:
			$contentType = $jobType;
			break;
		}
		
		$widgetId = $this->getContentEditWidget($contentType);
		return $widgetId;
	}
	
	/**
	 * 相対パスからURLを作成
	 *
	 * @param bool $isSslPage		現在のページがSSL使用になっているかどうか
	 * @param string $baseUrl		ルートURL
	 * @param string $sslBaseUrl	SSL使用時のルートURL
	 * @param string $path			相対パス
	 * @param string,array $param		URLに付加するパラメータ
	 * @param bool $isLink				作成するURLがリンクかどうか。リンクの場合はリンク先のページのSSLの状態に合わせる
	 * @return string				作成したURLパラメータ
	 */
	function _createUrlByRelativePath($isSslPage, $baseUrl, $sslBaseUrl, $path, $param = '', $isLink = false)
	{
		$destPath = '';
		
		// ファイル名を取得
		$paramArray = array();
		list($filename, $query) = explode('?', basename($path));
		$saveFilename = $filename;		// ファイル名を退避
		if (empty($filename)) $filename = M3_FILENAME_INDEX;

		if (!empty($query)) parse_str($query, $paramArray);
		if (is_array($param)){
			$paramArray = array_merge($paramArray, $param);
		} else if (is_string($param) && !empty($param)){
			parse_str($param, $addArray);
			$paramArray = array_merge($paramArray, $addArray);
		}
		// ページIDを取得
		if (strEndsWith($filename, '.php')){			// PHPスクリプトのとき
			if ($isLink){		// リンクタイプのとき
				// ページIDを取得
				$pageId = basename($filename, '.php');
				$pageSubId = $paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID];
			
				// 目的のページのSSL設定状況を取得
				$isSslSelPage = $gPageManager->isSslPage($pageId, $pageSubId);
				if ($isSslSelPage){
					$destPath = $sslBaseUrl;
				} else {
					$destPath = $baseUrl;
				}
			} else {
				// 現在のページのSSLの状態に合わせる
				if ($isSslPage){
					//$destPath = $sslBaseUrl . $path;
					$destPath = $sslBaseUrl;
				} else {
					//$destPath = $baseUrl . $path;
					$destPath = $baseUrl;
				}
			}
			// 階層化パスで出力のとき
			if ($this->_useHierPage && $filename == M3_FILENAME_INDEX){
				$subId = $paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID];
				$dirName = dirname($path);
				if ($dirName == '/'){
					$destPath .= $dirName;
				} else {
					$destPath .= $dirName . '/';
				}
				if (!empty($subId)){
					$destPath .= $subId . '/';
					unset($paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID]);
				}
			} else {
				$dirName = dirname($path);
				if ($dirName == '/'){
					$destPath .= $dirName . $saveFilename;
				} else {
					$destPath .= $dirName . '/' . $saveFilename;
				}
			}
			// ページサブIDがデフォルト値のときはページサブIDを省略
			//if ($paramArray[M3_REQUEST_PARAM_PAGE_SUB_ID] == 
			$paramStr = $this->_createParamStr($paramArray);
			if (!empty($paramStr)) $destPath .= '?' . $paramStr;
		} else {
			// 現在のページのSSLの状態に合わせる
			if ($isSslPage){
				$destPath = $sslBaseUrl . $path;
			} else {
				$destPath = $baseUrl . $path;
			}
		}
		return $destPath;
	}
	/**
	 * URLパラメータ文字列作成
	 *
	 * @param array $paramArray			URL作成用のパス
	 * @return string					作成したURLパラメータ
	 */
	function _createParamStr($paramArray)
	{
		$destParam = '';
		if (count($paramArray) == 0) return $destParam;

		$sortParam = array();
		$keys = array_keys($paramArray);
		$keyCount = count($keys);
		for ($i = 0; $i < $keyCount; $i++){
			$key = $keys[$i];
			$value = $paramArray[$key];
			$orderNo = $this->_urlParamOrder[$key];
			if (!isset($orderNo)) $orderNo = 100;
			$sortParam[] = array('key' => $key, 'value' => $value, 'no' => $orderNo);
		}
		usort($sortParam, create_function('$a,$b', 'return $a["no"] - $b["no"];'));
		
		// 文字列を作成
		$sortCount = count($sortParam);
		for ($i = 0; $i < $sortCount; $i++){
			if ($i > 0) $destParam .= '&';
			$destParam .= rawurlencode($sortParam[$i]['key']) . '=' . rawurlencode($sortParam[$i]['value']);
		}
		return $destParam;
	}
}
?>
