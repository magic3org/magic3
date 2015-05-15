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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/evententry_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/evententry_mainDb.php');

class evententry_mainRegistWidgetContainer extends evententry_mainBaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $eventObj;			// イベント情報用取得オブジェクト
	private $showWidget;		// ウィジェットを表示するかどうか
	private $_contentParam;		// コンテンツ変換用	
	const EVENT_OBJ_ID = 'eventlib';		// イベント情報取得用オブジェクト
	const EYECATCH_IMAGE_SIZE = 40;		// アイキャッチ画像サイズ

//	const TARGET_WIDGET = 'blog_main';		// 呼び出しウィジェットID
	const DEFAULT_TITLE = 'イベント予約';		// デフォルトのウィジェットタイトル名
	
//	const BLOG_OBJ_ID = 'bloglib';		// ブログオブジェクトID
//	const CF_USE_MULTI_BLOG			= 'use_multi_blog';		// マルチブログ機能を使用するかどうか

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new evententry_mainDb();
		
		// イベント情報オブジェクト取得
		$this->eventObj = $this->gInstance->getObject(self::EVENT_OBJ_ID);
	}
	/**
	 * ウィジェット初期化
	 *
	 * 共通パラメータの初期化や、以下のパターンでウィジェット出力方法の変更を行う。
	 * ・組み込みの_setTemplate(),_assign()を使用
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string $task					処理タスク
	 * @return 								なし
	 */
	function _init($request, $task)
	{
		// ##### ウィジェットの表示制御 #####
		// イベントIDがない場合は非表示にする
		$eventId = $request->trimValueOf(M3_REQUEST_PARAM_EVENT_ID);
		if (empty($eventId)){
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// イベントが非公開の場合は表示しない
		$ret = $this->db->getEntry($this->_langId, $eventId, ''/*予約タイプ*/, $this->entryRow);
		if ($ret){
			// イベントの表示状態を取得
			$visible =$this->eventObj->isEntryVisible($this->entryRow);
			if (!$visible){
				$this->cancelParse();		// テンプレート変換処理中断
				return;
			}
		} else {			// 受付イベントがない場合
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}
		
		// イベント予約情報が非公開の場合は表示しない
		$this->entryStatus = $this->entryRow['et_status'];			// 予約情報の状態
		if ($this->entryStatus < 2){				// 	未設定(0),非公開(1)のときは非表示。受付中(2),受付停止(3),受付終了(4)のとき表示。
			$this->cancelParse();		// テンプレート変換処理中断
			return;
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
		return 'regist.tmpl.html';
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
		// 入力値取得
		$act = $request->trimValueOf('act');
		
		if ($act == 'regist'){		// 登録の場合
		} else if ($act == 'cancel'){		// 登録キャンセルの場合
		}
		
		// イベント予約画面作成
		$this->createSingle($request);
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return self::DEFAULT_TITLE;
	}
	/**
	 * イベント予約画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createSingle($request)
	{
		$entryId	= $this->entryRow['ee_id'];// 記事ID
		$title		= $this->entryRow['ee_name'];// タイトル
		$date		= $this->entryRow['ee_regist_dt'];// 日付
		$accessPointUrl = $this->gEnv->getDefaultUrl();
		// イベント情報追加分
		$summary	= $this->entryRow['ee_summary'];		// 要約
		$url		= $this->entryRow['ee_url'];		// URL
		$isAllDay	= $this->entryRow['ee_is_all_day'];			// 終日イベントかどうか
		$entryHtml	= $this->entryRow['et_html'];		// 説明
		
		// ##### コンテンツ作成用レイアウト取得 #####
		$layout = self::$_configArray[DEFAULT_LAYOUT_ENTRY_SINGLE];
		if (empty($layout)) $layout = evententry_mainCommonDef::DEFAULT_LAYOUT_ENTRY_SINGLE;
		
		// 記事へのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?'. M3_REQUEST_PARAM_EVENT_ID . '=' . $entryId, true/*リンク用*/);
		
		// タイトル作成
		$titleTag = '<h' . $this->itemTagLevel . '><a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '">' . $this->convertToDispString($title) . '</a></h' . $this->itemTagLevel . '>';
				
		// アイキャッチ画像
		$iconUrl = $this->eventObj->getEyecatchImageUrl($this->entryRow['ee_thumb_filename'], 's'/*sサイズ画像*/);
		if (empty($this->entryRow['ee_thumb_filename'])){
			$iconTitle = 'アイキャッチ画像未設定';
		} else {
			$iconTitle = 'アイキャッチ画像';
		}
	//	$imageTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::EYECATCH_IMAGE_SIZE . '" height="' . self::EYECATCH_IMAGE_SIZE . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		$imageTag = '<img src="' . $this->getUrl($iconUrl) . '" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// ##### 表示コンテンツ作成 #####
		// イベント開催期間
		$dateHtml = '';
		if ($this->entryRow['ee_end_dt'] == $this->gEnv->getInitValueOfTimestamp()){		// 開催開始日時のみ表示のとき
			if ($isAllDay){		// 終日イベントのとき
				$dateHtml = $this->convertToDispDate($this->entryRow['ee_start_dt']);
			} else {
				$dateHtml = $this->convertToDispDateTime($this->entryRow['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
			}
		} else {
			if ($isAllDay){		// 終日イベントのとき
				$dateHtml = $this->convertToDispDate($this->entryRow['ee_start_dt']) . evententry_mainCommonDef::DATE_RANGE_DELIMITER;
				$dateHtml .= $this->convertToDispDate($this->entryRow['ee_end_dt']);
			} else {
				$dateHtml = $this->convertToDispDateTime($this->entryRow['ee_start_dt'], 0/*ロングフォーマット*/, 10/*時分*/) . evententry_mainCommonDef::DATE_RANGE_DELIMITER;
				$dateHtml .= $this->convertToDispDateTime($this->entryRow['ee_end_dt'], 0/*ロングフォーマット*/, 10/*時分*/);
			}
		}
		
		// コンテンツレイアウトのプレマクロ変換(ブロック型マクロを変換してコンテンツマクロのみ残す)
		$contentParam = array(	
								// M3_TAG_MACRO_TITLE	=> $titleTag,
								M3_TAG_MACRO_IMAGE	=> $imageTag,
								M3_TAG_MACRO_DATE	=> $dateHtml,		// 開催期間
								M3_TAG_MACRO_BODY	=> $entryHtml		// 説明
							);
		$entryHtml = $this->createMacroContent($layout, $contentParam);
		
		// Magic3マクロ変換
		// あらかじめ「CT_」タグをすべて取得する?
		$contentInfo = array();
		$contentInfo[M3_TAG_MACRO_CONTENT_ID] = $entryId;			// コンテンツ置換キー(エントリーID)
		$contentInfo[M3_TAG_MACRO_CONTENT_URL] = $linkUrl;// コンテンツ置換キー(エントリーURL)
		$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $title;			// コンテンツ置換キー(タイトル)
		$contentInfo[M3_TAG_MACRO_CONTENT_SUMMARY] = $this->entryRow['ee_summary'];			// コンテンツ置換キー(要約)
//		$contentInfo[M3_TAG_MACRO_CONTENT_DATE] = $this->timestampToDate($this->entryRow['ee_start_dt']);		// コンテンツ置換キー(イベント開始日)
//		$contentInfo[M3_TAG_MACRO_CONTENT_TIME] = $this->timestampToTime($this->entryRow['ee_start_dt']);		// コンテンツ置換キー(イベント開始時間)
		
/*		// Magic3マクロ変換
		// あらかじめ「CT_」タグをすべて取得する?
		$contentInfo = array();
		$contentInfo[M3_TAG_MACRO_CONTENT_ID] = $this->entryRow['ee_id'];			// コンテンツ置換キー(エントリーID)
		$contentInfo[M3_TAG_MACRO_CONTENT_URL] = $this->getUrl($linkUrl);// コンテンツ置換キー(エントリーURL)
		$contentInfo[M3_TAG_MACRO_CONTENT_AUTHOR] = $this->entryRow['lu_name'];			// コンテンツ置換キー(著者)
		$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $this->entryRow['ee_name'];			// コンテンツ置換キー(タイトル)
		$contentInfo[M3_TAG_MACRO_CONTENT_DESCRIPTION] = $this->entryRow['ee_description'];			// コンテンツ置換キー(簡易説明)
		$contentInfo[M3_TAG_MACRO_CONTENT_IMAGE] = $this->getUrl($thumbUrl);		// コンテンツ置換キー(画像)
		$contentInfo[M3_TAG_MACRO_CONTENT_UPDATE_DT] = $this->entryRow['ee_create_dt'];		// コンテンツ置換キー(更新日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_REGIST_DT] = $this->entryRow['ee_regist_dt'];		// コンテンツ置換キー(登録日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_DATE] = $this->timestampToDate($this->entryRow['ee_regist_dt']);		// コンテンツ置換キー(登録日)
		$contentInfo[M3_TAG_MACRO_CONTENT_TIME] = $this->timestampToTime($this->entryRow['ee_regist_dt']);		// コンテンツ置換キー(登録時)
		$contentInfo[M3_TAG_MACRO_CONTENT_START_DT] = $this->entryRow['ee_active_start_dt'];		// コンテンツ置換キー(公開開始日時)
		$contentInfo[M3_TAG_MACRO_CONTENT_END_DT] = $this->entryRow['ee_active_end_dt'];		// コンテンツ置換キー(公開終了日時)
		// イベント情報追加分
		$contentInfo[M3_TAG_MACRO_CONTENT_PLACE]	= $this->getCurrentLangString($this->entryRow['ee_place']);// 開催場所
		$contentInfo[M3_TAG_MACRO_CONTENT_CONTACT]	= $this->getCurrentLangString($this->entryRow['ee_contact']);		// 連絡先
		$contentInfo[M3_TAG_MACRO_CONTENT_INFO_URL]		= $this->entryRow['ee_url'];		// その他の情報のURL
		*/
		
		$entryHtml = $this->convertM3ToHtml($entryHtml, true/*改行コーをbrタグに変換*/, $contentInfo);		// コンテンツマクロ変換
		
		// メイン領域を出力
		$this->tmpl->addVar("_widget", "entry", $entryHtml);
		
		// タイトル領域を出力
		if (!empty($title)){
			$this->startTitleTagLevel = 2;
			$this->tmpl->setAttribute('show_title', 'visibility', 'visible');		// 年月表示
			$this->tmpl->addVar("show_title", "title", '<h' . $this->startTitleTagLevel . '>' . $this->convertToDispString($title) . '</h' . $this->startTitleTagLevel . '>');
		}
	}
	/**
	 * コンテンツのプレマクロ変換
	 *
	 * @param string $layout		レイアウト
	 * @param array	$contentParam	コンテンツ作成用パラメータ
	 * @return string				作成コンテンツ
	 */
	function createMacroContent($layout, $contentParam)
	{
		$this->_contentParam = $contentParam;
		$dest = preg_replace_callback(M3_PATTERN_TAG_MACRO, array($this, '_replace_macro_callback'), $layout);
		return $dest;
	}
	/**
	 * コンテンツマクロ変換コールバック関数
	 * 変換される文字列はHTMLタグではないテキストで、変換後のテキストはHTMLタグ(改行)を含むか、HTMLエスケープしたテキスト
	 *
	 * @param array $matchData		検索マッチデータ
	 * @return string				変換後データ
	 */
    function _replace_macro_callback($matchData)
	{
		$destTag	= $matchData[0];		// マッチした文字列全体
		$typeTag	= $matchData[1];		// マクロキー
		$options	= $matchData[2];		// マクロオプション

		switch ($typeTag){
		case M3_TAG_MACRO_IMAGE:		// サムネール
		case M3_TAG_MACRO_DATE:		// 開催期間
		case M3_TAG_MACRO_BODY:		// 説明
			$destTag = $this->_contentParam[$typeTag];
			break;
		case M3_TAG_MACRO_BUTTON:		// ボタン
			// コンテンツマクロオプションを解析
			$optionParams = $this->gInstance->getTextConvManager()->parseMacroOption($options);

			// コンテンツマクロオプション処理
			$keys = array_keys($optionParams);
			for ($i = 0; $i < count($keys); $i++){
				$optionKey = $keys[$i];
				$optionValue = $optionParams[$optionKey];

				switch ($optionKey){
				case 'title':		// ボタンタイトル
					$title = $optionValue;
					break;
				}
			}
			$destTag = '<a class="button" href="' . $this->convertUrlToHtmlEntity($this->_contentParam[$typeTag]) . '">' . $this->convertToDispString($title) . '</a>';
			break;		
		}
		return $destTag;
	}
}
?>
