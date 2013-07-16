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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: s_photoslideWidgetContainer.php 4868 2012-04-20 10:05:13Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath()	. '/s_photoslideDb.php');

class s_photoslideWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $css;			// 追加CSS
	private $visible;		// 表示するかどうか
	private $dispType;		// 画像表示方法
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_IMAGE_TYPE = 'directory';		// デフォルトの画像タイプ
	const PHOTO_IMAGE_DIR		= '/widgets/photo/image';		// フォトギャラリー公開画像ディレクトリ
	const DEFAULT_PHOTO_IMAGE_EXT	= 'jpg';	// フォトギャラリー公開画像ファイル拡張子
	const CF_PHOTO_CATEGORY_PASSWORD	= 'photo_category_password';		// 画像カテゴリーのパスワード制限
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new s_photoslideDb();
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
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
	
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){// 定義データが取得できないときは終了
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		}

		// デフォルト値取得
		
		// 設定値取得
		$name		= $targetObj->name;// 定義名
		$imageType	= $targetObj->imageType;// 表示画像タイプ
		$dir		= $targetObj->dir;		// 画像の読み込みディレクトリ
		$cssId		= $targetObj->cssId;					// CSS用ID
		$this->css	= $targetObj->css;		// 追加CSS
		$this->dispType = $targetObj->dispType;	// 表示方法
		$effect		= $targetObj->effect;			// エフェクト
		$speed		= $targetObj->speed;
		$imageCount		= $targetObj->imageCount;	// 画像取得数
		$sortOrder		= $targetObj->sortOrder;		// 画像並び順
		$this->sortKey	= $targetObj->sortKey;		// 画像ソートキー

		// 画像一覧作成
		switch ($imageType){
			case 'directory':
				$this->createImageList($dir);
				break;
			case 'photo':
				if (!$this->db->getConfig(self::CF_PHOTO_CATEGORY_PASSWORD)){			// カテゴリーパスワード制限がかかっているときは画像の表示不可
					$this->db->getPhotoItems($imageCount, $langId, $this->sortKey, $sortOrder, array($this, 'itemLoop'));
				}
				break;
		}
		
		// エフェクト設定を作成
		$effectStr = $this->createEffect($effect, $speed);
		$this->tmpl->addVar('_widget', 'effect', $effectStr);
		$this->tmpl->addVar('_widget', 'css_id', $cssId);
		$this->visible = true;
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		if ($this->visible){
			return $this->css;
		} else {
			return '';
		}
	}
	/**
	 * エフェクトの設定を作成
	 *
	 * @param string $effect		エフェクト
	 * @param string $speed			speedパラメータ
	 * @return string				エフェクト文字列
	 */
	function createEffect($effect, $speed)
	{
		$effectStr = '';
		if (!empty($effect)) $effectStr .= 'fx: \'' . $effect . '\'';
		if (!empty($speed)){
			if (!empty($effectStr)) $effectStr .= ',';
			$effectStr .= 'speed: \'' . $speed . '\'';
		}
		return $effectStr;
	}
	/**
	 * スライドショー用画像一覧を作成
	 *
	 * @param string $dir		画像のあるディレクトリ
	 * @return なし							
	 */
	function createImageList($dir)
	{
		// 画像ディレクトリを読み込み
		$searchPath = $this->gEnv->getSystemRootPath() . $dir;		// 画像検索パス
		$urlPath	= $this->gEnv->getRootUrl() . $dir;
		
		// ファイル一覧取得
		$files = $this->getFiles($searchPath);
		
		// 表示方法によって並べ替え
		switch ($this->dispType){
			case 0:
				sort($files);
				break;
			case 1:
				shuffle($files);
				break;
		}
		
		for ($i = 0; $i < count($files); $i++){
			$imageUrl = $urlPath . '/' . $files[$i];
			$imageTag = '<img src="' . $this->convertUrlToHtmlEntity($this->getUrl($imageUrl)) . '" />';
			$row = array(
				'image_tag'    => $imageTag			// 画像タグ
			);
			$this->tmpl->addVars('image_list', $row);
			$this->tmpl->parseTemplate('image_list', 'a');
		}
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
		
		// 画像詳細へのリンク
		$url = $this->gEnv->getDefaultSmartphoneUrl() . '?' . M3_REQUEST_PARAM_PHOTO_ID . '=' . $photoId;
		$urlLink = $this->getUrl($url, true);

		// 画像URL
		$imageUrl = $this->getUrl($this->getPhotoImageUrl($photoId));
		
		$dispTitle = $this->convertToDispString($title);
		$imageTag = '<a href="' . $this->convertUrlToHtmlEntity($urlLink) . '" data-ajax="false"><img src="' . $this->convertUrlToHtmlEntity($imageUrl) . '" alt="' . $dispTitle . '" title="' . $dispTitle . '" /></a>';
		$row = array(
			'image_tag' => $imageTag			// アルバムのサムネール画像
		);
		$this->tmpl->addVars('image_list', $row);
		$this->tmpl->parseTemplate('image_list', 'a');
		return true;
	}
	/**
	 * 指定ディレクトリのファイル一覧を取得
	 *
	 * @param string $path		読み込みディレクトリ
	 * @return array			ファイル名一覧
	 */
	function getFiles($path)
	{
		$filenames = array();
		if (is_dir($path)){
			$dir = dir($path);
			while (($file = $dir->read()) !== false){
				$filePath = $path . '/' . $file;
				// ファイルかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)
					&& strncmp($file, '_', 1) != 0){		// 「_」で始まる名前のファイルは読み込まない
					$filenames[] = $file;
				}
			}
			$dir->close();
		}
		return $filenames;
	}
	/**
	 * フォトギャラリー画像のURLを取得
	 *
	 * @param string $photoId		画像ID
	 * @return string				画像URL
	 */
	function getPhotoImageUrl($photoId)
	{
		return $this->gEnv->getResourceUrl() . self::PHOTO_IMAGE_DIR . '/' . $photoId . '.' . self::DEFAULT_PHOTO_IMAGE_EXT;
	}
}
?>
