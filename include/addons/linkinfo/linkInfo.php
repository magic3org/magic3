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
 * @copyright  Copyright 2006-2014 Magic3 Project.
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
	const DEFAULT_CONTENT_COUNT = 300;		// コンテンツリスト取得数
	const CONTENT_LENGTH = 300;			// プレビュー用コンテンツサイズ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new linkInfoDb();
		
		$this->contentTypeArray = array(	M3_VIEW_TYPE_CONTENT,				// 汎用コンテンツ
								M3_VIEW_TYPE_PRODUCT,				// 製品
								M3_VIEW_TYPE_BBS,					// BBS
								M3_VIEW_TYPE_BLOG,				// ブログ
								M3_VIEW_TYPE_WIKI,				// wiki
								M3_VIEW_TYPE_USER,				// ユーザ作成コンテンツ
								M3_VIEW_TYPE_EVENT,				// イベント
								M3_VIEW_TYPE_PHOTO);				// フォトギャラリー
		$this->langId = $this->gEnv->getDefaultLanguage();
		$this->accessPointType = array(	array('', 'PC用「/」'),
										array('m', '携帯用「/m」'),
										array('s', 'スマートフォン用「/s」'));	// アクセスポイント種別
	}
	/**
	 * リンク情報を作成し、Ajaxデータとして出力
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function outputAjaxData($request)
	{
		// 入力値を取得
		$accessPoint = $request->trimValueOf('accesspoint');
				
		switch ($accessPoint){
			case '':			// PC用
			default:
				$defaultPageId = $this->gEnv->getDefaultPageId();
				$accessPoint = '';		// アクセスポイント修正
				break;
			case 'm':			// 携帯用
				$defaultPageId = $this->gEnv->getDefaultMobilePageId();
				break;
			case 's':			// スマートフォン用
				$defaultPageId = $this->gEnv->getDefaultSmartphonePageId();
				break;
		}

		// ##### Ajaxによるリンク情報取得 #####
		$act = $request->trimValueOf('act');
		if ($act == 'getpage'){		// ページ情報取得
			$this->db->getPageSubIdListWithWidget($defaultPageId, array($this, 'pageSubIdLoop'));

			// ページ選択メニューデータ
			$this->pageList = array_merge(array(array('', '-- 未選択 --')), $this->pageList);
			$this->pageList[] = array('_root', '[トップページ]');
			$this->gInstance->getAjaxManager()->addData('pagelist', $this->pageList);
		} else if ($act == 'getcontenttype'){		// コンテンツ種別取得
			$contentTypeList = $this->getContentTypeList($accessPoint);
			$this->gInstance->getAjaxManager()->addData('contenttype', $contentTypeList);
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
					break;
			}

			if (!empty($this->contentList)) $this->contentList = array_merge(array(array('', '-- 未選択 --')), $this->contentList);
			$this->gInstance->getAjaxManager()->addData('contentlist', $this->contentList);
		} else if ($act == 'getcontent'){		// コンテンツ取得
			$this->contentType = $request->trimValueOf('contenttype');
			$contentId = $request->trimValueOf('contentid');
//			$contentText = '';		// プレビュー用コンテンツ
			
			// プレビュー用コンテンツ取得
			list($contentTitle, $contentText) = $this->getContentInfo($accessPoint, $this->contentType, $contentId, $this->langId);
			$this->gInstance->getAjaxManager()->addData('content', $contentText);
			
		} else if ($act == 'getaccesspoint'){		// アクセスポイント取得
			$this->gInstance->getAjaxManager()->addData('accesspoint', $this->accessPointType);
		} else if ($act == 'gettitle'){		// リンク先のコンテンツタイトル取得(メニュー定義画面(menudef,smenudef)からの呼び出し用)
			$url = $request->trimValueOf('url');
			$path = $this->gEnv->getMacroPath($url);
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
			$this->gInstance->getAjaxManager()->addData('title', $contentTitle);
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
		$contentTypeArray = array(array(), array(), array());
		$pageIdArray = array($this->gEnv->getDefaultPageId(), $this->gEnv->getDefaultMobilePageId(), $this->gEnv->getDefaultSmartphonePageId());
		
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
		$contentText = $this->gInstance->getTextConvManager()->htmlToText($src);

		// アプリケーションルートを変換
		$rootUrl = $this->getUrl($this->gEnv->getRootUrl());
		$contentText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $rootUrl, $contentText);

		// 登録したキーワードを変換
		$this->gInstance->getTextConvManager()->convByKeyValue($contentText, $contentText);

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
				break;
		}
		return array($contentTitle, $contentText);
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
}
?>
