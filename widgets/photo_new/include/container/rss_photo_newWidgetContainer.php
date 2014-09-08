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
require_once($gEnvManager->getContainerPath() . '/baseRssContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/photo_newDb.php');

class rss_photo_newWidgetContainer extends BaseRssContainer
{
	private $db;
	private $isExistsList;				// リスト項目が存在するかどうか
	private $rssChannel;				// RSSチャンネル部出力データ
	private $rssSeqUrl = array();					// 項目の並び
	private $defaultUrl;	// システムのデフォルトURL
	const DEFAULT_ITEM_COUNT = 12;		// デフォルトの表示項目数
	const DEFAULT_TITLE = 'フォトギャラリー最新画像';			// デフォルトのウィジェットタイトル
	const DEFAULT_DESC = '最新のフォトギャラリー画像が取得できます。';
	const CF_PHOTO_CATEGORY_PASSWORD	= 'photo_category_password';		// 画像カテゴリーのパスワード制限
	const THUMBNAIL_DIR = '/widgets/photo/image';		// 画像格納ディレクトリ
	const DEFAULT_IMAGE_EXT = 'jpg';			// 画像ファイルのデフォルト拡張子
	const DEFAULT_THUMBNAIL_SIZE = 128;		// サムネール画像サイズ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
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
		return 'rss_index.tmpl.html';
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
		$langId = $this->gEnv->getCurrentLanguage();
		
		// 設定値を取得
		$itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$useRss = 1;							// RSS配信を行うかどうか
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$itemCount	= $paramObj->itemCount;
			$useRss		= $paramObj->useRss;// RSS配信を行うかどうか
			if (!isset($useRss)) $useRss = 1;
		}
		// RSS配信を行わないときは終了
		if (empty($useRss)){
			// ページ作成処理中断
			$this->gPage->abortPage();
			
			// システム強制終了
			$this->gPage->exitSystem();
		}

		// 一覧を作成
		$this->defaultUrl = $this->gEnv->getDefaultUrl();
		if (!$this->db->getConfig(self::CF_PHOTO_CATEGORY_PASSWORD)){			// カテゴリーパスワード制限がかかっているときは画像の表示不可
			$this->db->getPhotoItems($itemCount, $langId, array($this, 'itemLoop'));
		}
		if (!$this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 一覧非表示
		
		// RSSチャンネル部出力データ作成
		$linkUrl = $this->getUrl($this->gPage->createRssCmdUrl($this->gEnv->getCurrentWidgetId()));
		$this->rssChannel = array(	'title' => self::DEFAULT_TITLE,		// タイトル
									'link' => $linkUrl,					// RSS配信用URL
									'description' => self::DEFAULT_DESC,// 説明
									'seq' => $this->rssSeqUrl);			// 項目の並び
	}
	/**
	 * RSSのチャンネル部出力
	 *
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ
	 * @return array 						設定データ
	 */
	function _setRssChannel($request, &$param)
	{
		return $this->rssChannel;
	}
	/**
	 * 取得したメニュー項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemLoop($index, $fetchedRow)
	{
		$photoId = $fetchedRow['ht_public_id'];		// フォトID
		$title = $fetchedRow['ht_name'];		// サムネール画像タイトル
		
		// 画像詳細へのリンク
		$url = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $photoId;
		$linkUrl = $this->getUrl($url, true);
		
		// 画像URL
		//$imageUrl = $this->getUrl($this->gEnv->getResourceUrl() . self::THUMBNAIL_DIR . '/' . $photoId . '_' . self::DEFAULT_THUMBNAIL_SIZE . '.' . self::DEFAULT_IMAGE_EXT);
		$imageUrl = $this->getUrl($this->gInstance->getImageManager()->getDefaultThumbUrl(M3_VIEW_TYPE_PHOTO, $photoId));
		$imageTag = '<img src="' . $this->convertUrlToHtmlEntity($imageUrl) . '" />';
		
		$row = array(
			'link_url'	=> $this->convertUrlToHtmlEntity($linkUrl),		// リンク
			'image_url' => $this->convertUrlToHtmlEntity($imageUrl),		// 画像URL
			'name'		=> $this->convertToDispString($title),			// タイトル
			'date'		=> getW3CDate($fetchedRow['ht_regist_dt']),		// 投稿日時
			'image_tag' => $imageTag	// サムネール画像
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
	
		// RSS用
		$this->rssSeqUrl[] = $linkUrl;					// 項目の並び
		
		$this->isExistsList = true;		// リスト項目が存在するかどうか
		return true;
	}
}
?>
