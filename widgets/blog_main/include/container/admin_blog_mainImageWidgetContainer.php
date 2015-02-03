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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');

class admin_blog_mainImageWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	const CREATE_EYECATCH_TAG_ID = 'createeyecatch';			// アイキャッチ画像作成ボタンタグID
	
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
		return 'admin_image.tmpl.html';
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
		$userId		= $this->gEnv->getCurrentUserId();
		$langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		$entryId = $request->trimValueOf(M3_REQUEST_PARAM_BLOG_ENTRY_ID);
		
		$ret = self::$_mainDb->getEntryItem($entryId, $langId, $row);
		if ($ret){
			$html		= $row['be_html'];				// HTML
			$html2		= $row['be_html_ext'];			// HTML続き
			
			// 最大サイズのアイキャッチ画像を取得
			$eyecatchUrl = blog_mainCommonDef::getEyecatchImageUrl($row['be_thumb_filename'], self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE]);
			
			// アイキャッチ変更用ダイアログのデフォルト画像を取得
			if (empty($row['be_thumb_filename'])){
				$defaultEyecatchUrl = $eyecatchUrl;
			} else {		// 画像が作成されているとき
				// アイキャッチを作成したソース画像を取得
				$defaultEyecatchPath = $this->gInstance->getImageManager()->getFirstImagePath($html);
				if (empty($defaultEyecatchPath) && !empty($html2)) $defaultEyecatchPath = $this->gInstance->getImageManager()->getFirstImagePath($html2);		// 本文1に画像がないときは本文2を検索
				if (empty($defaultEyecatchPath)){		// 画像が見つからないとき
					$defaultEyecatchUrl = blog_mainCommonDef::getEyecatchImageUrl(''/*画像なし*/, self::$_configArray[blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE]);
				} else {
					$defaultEyecatchUrl = $this->gEnv->getUrlToPath($defaultEyecatchPath);		// URLに変換
				}
			}
			$eyecatchUrl .= '?' . date('YmdHis');
			$defaultEyecatchUrl .= '?' . date('YmdHis');
		}
		
		// アイキャッチ画像変更ボタン
		$createEyecatchButton = $this->gDesign->createEditButton(''/*同画面*/, '画像を作成', self::CREATE_EYECATCH_TAG_ID);
		$this->tmpl->addVar("_widget", "create_eyecatch_button", $createEyecatchButton);
		$this->tmpl->addVar("_widget", "tagid_create_eyecatch", self::CREATE_EYECATCH_TAG_ID);		// 画像作成タグ
		
		$this->tmpl->addVar("_widget", "eyecatch_url", $this->convertUrlToHtmlEntity($this->getUrl($eyecatchUrl)));
		$this->tmpl->addVar("_widget", "default_eyecatch_url", $this->convertUrlToHtmlEntity($this->getUrl($defaultEyecatchUrl)));		// デフォルトのアイキャッチ画像
//		$this->tmpl->addVar("_widget", "sitelogo_updated", $updateStatus);
		$this->tmpl->addVar("_widget", "eyecatch_size", $imageSize . 'x' . $imageSize);
		$this->tmpl->addVar("_widget", "entry_id", $entryId);
	}
}
?>
