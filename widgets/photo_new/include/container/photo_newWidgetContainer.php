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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/photo_newDb.php');

class photo_newWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $isExistsList;	// リスト項目が存在するかどうか
	private $defaultUrl;	// システムのデフォルトURL
	private $headRssFile;				// RSS情報
	private $showDate;						// 日付を表示するかどうか
	private $currentDate;				// 現在日付
	const DEFAULT_ITEM_COUNT = 12;		// デフォルトの表示項目数
	const DEFAULT_TITLE = 'フォトギャラリー最新画像';		// デフォルトのウィジェットタイトル名
	const RSS_ICON_FILE = '/images/system/rss14.png';		// RSSリンク用アイコン
	const CF_PHOTO_CATEGORY_PASSWORD	= 'photo_category_password';		// 画像カテゴリーのパスワード制限
	const THUMBNAIL_DIR = '/widgets/photo/image';		// 画像格納ディレクトリ
	const DEFAULT_VIEW_THUMB_SIZE = 72;		// サムネール表示サイズ
	const DATE_FORMAT = 'Y年 n月 j日';		// 日付フォーマット
	const TITLE_TAG_LEVEL = 4;
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new photo_newDb();
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
		// 設定値を取得
		$itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$useRss = 1;							// RSS配信を行うかどうか
		$this->showDate = 0;						// 日付を表示するかどうか
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$itemCount	= $paramObj->itemCount;
			$useRss		= $paramObj->useRss;// RSS配信を行うかどうか
			if (!isset($useRss)) $useRss = 1;
			$this->showDate	= $paramObj->showDate;		// 日付を表示するかどうか
			if (!isset($this->showDate)) $this->showDate = 0;
		}
		
		// 画像タイトルを取得
		$this->defaultUrl = $this->gEnv->getDefaultUrl();
		if (!$this->db->getConfig(self::CF_PHOTO_CATEGORY_PASSWORD)){			// カテゴリーパスワード制限がかかっているときは画像の表示不可
			$this->db->getPhotoItems($itemCount, $this->gEnv->getCurrentLanguage(), array($this, 'itemLoop'));
		}
			
/*		if (!$this->isExistsList){	// 画像がないときはメッセージを出力
			$this->tmpl->addVar("_widget", "message", '最新画像はありません');
			$this->tmpl->setAttribute('imagelist', 'visibility', 'hidden');// 一覧非表示
		}*/
		// 一覧データがない場合は非表示
		if ($this->isExistsList){
			if ($this->showDate){			// 日付表示ありのとき
				// 前の日付を表示
				$dateTag = '<h' . self::TITLE_TAG_LEVEL . '>' . $this->convertToDispString($this->currentDate) . '</h' . self::TITLE_TAG_LEVEL . '>';
				$dateRow = array(
					'date'		=> $dateTag			// 日付
				);
				$this->tmpl->addVars('date_list', $dateRow);
				$this->tmpl->parseTemplate('date_list', 'a');
			}
		} else {
			$this->tmpl->addVar("_widget", "message", '最新画像はありません');
			
			$this->tmpl->setAttribute('date_list', 'visibility', 'hidden');
		}
		
		// RSSの設定
		if (empty($useRss)){
			$this->tmpl->addVar("_widget", "rss_link", '');
			$this->headRssFile = array();
		} else {
			// RSS用リンク作成
			$iconTitle = self::DEFAULT_TITLE;
			$iconUrl = $this->gEnv->getRootUrl() . self::RSS_ICON_FILE;
			$rssLink = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			$linkUrl = $this->gPage->createRssCmdUrl($this->gEnv->getCurrentWidgetId());
			$rssLink = '<a href="' . convertUrlToHtmlEntity($this->getUrl($linkUrl)) . '">' . $rssLink . '</a>';
			$rssLink = '<div align="right">' . $rssLink . '</div>';		// 右寄せ
			$this->tmpl->addVar("_widget", "rss_link", $rssLink);
		
			// RSS情報を設定
			$this->headRssFile = array(
									'title' => $iconTitle,		// タイトル
									'href' => $this->getUrl($linkUrl)		// リンク先URL
								);				// RSS情報
		}
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
	 * RSS情報をHTMLヘッダ部に設定
	 *
	 * RSS情報をHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return array 						RSS情報データ。連想配列で「title」(タイトル)「href」(RSS配信用URL)を設定。出力しない場合は空配列を設定。
	 */
	function _addRssFileToHead($request, &$param)
	{
		return $this->headRssFile;
	}
	/**
	 * 取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function itemLoop($index, $fetchedRow, $param)
	{
		$photoId = $fetchedRow['ht_public_id'];		// フォトID
		$title = $fetchedRow['ht_name'];		// サムネール画像タイトル
		$date = date(self::DATE_FORMAT, strtotime($fetchedRow['ht_regist_dt']));
		$imageWidth = self::DEFAULT_VIEW_THUMB_SIZE;
		
		// 画像詳細へのリンク
		$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $photoId;
		$urlLink = $this->convertUrlToHtmlEntity($this->getUrl($url, true));

		// 画像URL
		$imageUrl = $this->gInstance->getImageManager()->getDefaultThumbUrl(M3_VIEW_TYPE_PHOTO, $photoId);
		
		$dispTitle = $this->convertToDispString($title);
		$imageTag = '<a href="' . $this->convertUrlToHtmlEntity($urlLink) . '"><img src="' . $this->convertUrlToHtmlEntity($imageUrl) . '" alt="' . $dispTitle . '" title="' . $dispTitle . '" style="width:' . $imageWidth . 'px;height:' . $imageWidth . 'px;' . $imageStyle . '" /></a>';

		if ($this->showDate){			// 日付表示ありのとき
			if (!isset($this->currentDate)){
				// 日付を更新
				$this->currentDate = $date;
			
				// バッファ更新
				$this->tmpl->clearTemplate('image_list');
			} else if ($date != $this->currentDate){
				$dateTag = '<h' . self::TITLE_TAG_LEVEL . '>' . $this->convertToDispString($this->currentDate) . '</h' . self::TITLE_TAG_LEVEL . '>';
				
				// 前の日付を表示
				$dateRow = array(
					'date'		=> 	$dateTag		// 日付
				);
				$this->tmpl->addVars('date_list', $dateRow);
				$this->tmpl->parseTemplate('date_list', 'a');
			
				// 日付を更新
				$this->currentDate = $date;
			
				// バッファ更新
				$this->tmpl->clearTemplate('image_list');
			}
		}
		
		$row = array(
			'image' => $imageTag			// アルバムのサムネール画像
		);
		$this->tmpl->addVars('image_list', $row);
		$this->tmpl->parseTemplate('image_list', 'a');
		
		$this->isExistsList = true;	// 画像があるかどうか
		return true;
	}
}
?>
