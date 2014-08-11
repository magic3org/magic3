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
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/slide_imageDb.php');

class slide_imageWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $paramObj;		// 定義取得用
	private $imageInfoArray = array();			// 画像情報
	private $cssId;			// タグのID
	private $css;
	const DEFAULT_CONFIG_ID = 0;
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new slide_imageDb();
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
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$name	= $targetObj->name;// 名前
			if (!empty($targetObj->imageInfo)) $this->imageInfoArray = $targetObj->imageInfo;			// 画像情報
			$this->css		= $targetObj->css;		// CSS
			$this->cssId	= $targetObj->cssId;	// CSS用のID
			$showTitle		= $targetObj->showTitle;		// タイトルを表示するかどうか
			$showPager		= $targetObj->showPager;			// ページ移動ボタンを表示するかどうか
			$showControl	= $targetObj->showControl;		// 前後移動ボタンを表示するかどうか
			$auto			= $targetObj->auto;		// 自動切り替えするかどうか
		}
		// サムネール表示を作成
		$this->createImageList();
		
		// 画面に値を埋め込む
		$this->tmpl->addVar("_widget", "css_id",	$this->cssId);		// CSS用ID
		$this->tmpl->addVar("_widget", "option_captions",	$showTitle ? 'true' : 'false');		// タイトルを表示するかどうか
		$this->tmpl->addVar("_widget", "option_pager",		$showPager ? 'true' : 'false');		// ページ移動ボタンを表示するかどうか
		$this->tmpl->addVar("_widget", "option_controls",	$showControl ? 'true' : 'false');		// 前後移動ボタンを表示するかどうか
		$this->tmpl->addVar("_widget", "option_auto",		$auto ? 'true' : 'false');		// 自動切り替えするかどうか
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
		return $this->css;
	}
	/**
	 * 画像情報一覧を作成
	 *
	 * @return なし						
	 */
	function createImageList()
	{
		$imageCount = count($this->imageInfoArray);
		for ($i = 0; $i < $imageCount; $i++){
			$infoObj = $this->imageInfoArray[$i];
			$name = $infoObj->name;// タイトル名
			
			// 画像URL
			$url = '';
			$relativeUrl = '';
			if (!empty($infoObj->url)){
				$url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $infoObj->url);
				$relativeUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, '', $infoObj->url);
			}

			// タイトルタグを作成
			$titleTag = '';
			if (!empty($name)) $titleTag = 'alt="' . $this->convertToDispString($name) . '" title="' . $this->convertToDispString($name) . '" ';
			
			$row = array(
				'url' => $this->convertUrlToHtmlEntity($this->getUrl($url)),			// 実画像URL
				'title' => $titleTag			// タイトル
			);
			$this->tmpl->addVars('image_list', $row);
			$this->tmpl->parseTemplate('image_list', 'a');
		}
	}
}
?>
