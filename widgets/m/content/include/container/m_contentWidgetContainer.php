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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_contentWidgetContainer.php 3749 2010-10-27 12:09:56Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/contentDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class m_contentWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $_contentCreated;	// コンテンツが取得できたかどうか
	private $currentDay;		// 現在日
	private $currentHour;		// 現在時間
	private $headTitle;		// HTMLヘッダタイトル
	const CONTENT_TYPE = 'mobile';			// コンテンツタイプ
	const VIEW_CONTENT_TYPE = 'mc';			// 参照数カウント用
	const DEFAULT_SEARCH_LIST_COUNT = 10;			// 最大リスト表示数
	const MESSAGE_NO_CONTENT		= 'コンテンツが見つかりません';
	const CONTENT_SIZE = 100;			// 検索結果コンテンツの文字列最大長
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new contentDb();
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
		$act = $request->trimValueOf('act');
		if ($act == 'search'){
			return 'search.tmpl.html';
		} else {
			return 'main.tmpl.html';
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
		// 現在日時を取得
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
		$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL

		// ログインユーザでないときは、ユーザ制限のない項目だけ表示
		$all = false;
		if ($this->gEnv->isCurrentUserLogined()) $all = true;
		
		$act = $request->trimValueOf('act');
		$keyword = $request->mobileTrimValueOf('keyword');
		$contentid = $request->trimValueOf('contentid');
		
		if ($act == 'search'){			// 検索
			$itemCount = self::DEFAULT_SEARCH_LIST_COUNT;		// 取得数
			
			// キーワード検索のとき
			if (empty($keyword)){
				$msg = '検索キーワードが入力されていません';
				$this->headTitle = 'コンテンツ検索';
			} else {
				$this->db->searchContentByKeyword(self::CONTENT_TYPE, $itemCount, 1, $keyword, $this->gEnv->getCurrentLanguage(), $all, array($this, 'searchItemsLoop'));
				$this->headTitle = 'コンテンツ検索[' . $keyword . ']';
				if (!$this->isExistsViewData) $msg = self::MESSAGE_NO_CONTENT;
			}
			$this->tmpl->addVar("_widget", "keyword", $keyword);
			if (!empty($msg)){
				$this->tmpl->setAttribute('message', 'visibility', 'visible');// メッセージ表示
				$this->tmpl->addVar("message", "msg", $msg);
			}
		} else if (empty($contentid)){	// コンテンツIDがないときはデフォルトデータを取得
			$this->db->getContentItems(self::CONTENT_TYPE, array($this, 'itemsLoop'), null, $this->gEnv->getCurrentLanguage());
			if (!$this->_contentCreated){		// コンテンツが取得できなかったときはデフォルト言語で取得
				$this->db->getContentItems(self::CONTENT_TYPE, array($this, 'itemsLoop'), null, $this->gEnv->getDefaultLanguage());
			}
		} else {
			// データエラーチェック
			$contentIdArray = explode(',', $contentid);
			if (ValueCheck::isNumeric($contentIdArray)){		// すべて数値であるかチェック
				$this->db->getContentItems(self::CONTENT_TYPE, array($this, 'itemsLoop'), $contentIdArray, $this->gEnv->getCurrentLanguage());
				if (!$this->_contentCreated){		// コンテンツが取得できなかったときはデフォルト言語で取得
					$this->db->getContentItems(self::CONTENT_TYPE, array($this, 'itemsLoop'), $contentIdArray, $this->gEnv->getDefaultLanguage());
				}
			} else {
				$this->setAppErrorMsg('IDにエラー値があります');
			}
		}
		// HTMLサブタイトルを設定
		if (!empty($this->headTitle)) $this->gPage->setHeadSubTitle($this->headTitle);
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
		// ビューカウントを更新
		if (!$this->gEnv->isSystemManageUser()){		// システム運用者以上の場合はカウントしない
			$this->gInstance->getAnalyzeManager()->updateContentViewCount(self::VIEW_CONTENT_TYPE, $fetchedRow['cn_serial'], $this->currentDay, $this->currentHour);
		}

		// タイトルを設定
		$title = $fetchedRow['cn_name'];
		if (empty($this->headTitle)) $this->headTitle = $title;
		
		// HTMLを出力
		// 出力内容は特にエラーチェックしない
		$contentText = $fetchedRow['cn_html'];
		$contentText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $contentText);// アプリケーションルートを変換
		
		// 登録したキーワードを変換
		$this->gInstance->getTextConvManager()->convByKeyValue($contentText, $contentText, true/*改行コーをbrタグに変換*/);
		
		// 携帯用HTMLをきれいにする
		$contentText = $this->gInstance->getTextConvManager()->cleanMobileTag($contentText);
		
		// 表示属性を取得
		$showTitle = 0;
		$titleBgColor = '';
		$contentId = $fetchedRow['cn_id'];
		$paramObj = $this->getWidgetParamObjByConfigId($contentId);
		if (!empty($paramObj)){
			$showTitle = $paramObj->showTitle;		// タイトルの表示
			$titleBgColor = $paramObj->titleBgColor;		// タイトルバックグランドカラー
		}
		// タイトルの表示
		$titleStr = '';
		if ($showTitle){
			$titleStr = '<div align="center" style="text-align:center;';
			if (!empty($titleBgColor)) $titleStr .= 'background-color:' . $titleBgColor . ';';// タイトルバックグランドカラー
			$titleStr .= '">' . $this->convertToDispString($title) . '</div>';
		}
		$row = array(
			'title' => $titleStr,
			'content' => $contentText	// コンテンツ
		);
		$this->tmpl->addVars('contentlist', $row);
		$this->tmpl->parseTemplate('contentlist', 'a');
		
		// コンテンツが取得できた
		$this->_contentCreated = true;
		return true;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function searchItemsLoop($index, $fetchedRow)
	{
		// タイトルを設定
		$title = $fetchedRow['cn_name'];

		// 記事へのリンクを生成
		$linkUrl = $this->currentPageUrl . '&contentid=' . $fetchedRow['cn_id'];
		$link = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '" >' . $title . '</a>';

		// テキストに変換
		//$contentText = strip_tags($fetchedRow['cn_html']);
		$contentText = $this->gInstance->getTextConvManager()->htmlToText($fetchedRow['cn_html']);
		
		// アプリケーションルートを変換
		$contentText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $contentText);
		
		// 登録したキーワードを変換
		$this->gInstance->getTextConvManager()->convByKeyValue($contentText, $contentText);
		
		// Magic3タグ削除(絵文字タグ削除)
		$contentText = $this->gInstance->getTextConvManager()->deleteM3Tag($contentText);
		
		// 検索結果用にテキストを詰める。改行、タブ、スペース削除。
		$contentText = str_replace(array("\r", "\n", "\t", " "), '', $contentText);
		
		// 文字列長を修正
		if (function_exists('mb_strimwidth')){
			$contentText = mb_strimwidth($contentText, 0, self::CONTENT_SIZE, '…');
		} else {
			$contentText = substr($contentText, 0, self::CONTENT_SIZE) . '...';
		}

		$row = array(
			'title' => $link,			// リンク付きタイトル
			'content' => $this->convertToDispString($contentText)	// コンテンツ
		);
		$this->tmpl->addVars('contentlist', $row);
		$this->tmpl->parseTemplate('contentlist', 'a');
		$this->isExistsViewData = true;				// 表示データがあるかどうか
		return true;
	}
}
?>
