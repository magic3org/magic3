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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: youtube_playerWidgetContainer.php 3860 2010-11-24 07:34:24Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class youtube_playerWidgetContainer extends BaseWidgetContainer
{
	private $fieldInfoArray = array();			// 動画項目情報
	private $themeFilePath;		// テーマファイル
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_CSS_FILE = '/youtube-player.css';		// CSSファイル
	const DEFAULT_THEME_DIR = '/ui/themes/';				// jQueryUIテーマ格納ディレクトリ
	const THEME_CSS_FILE = 'jquery-ui.custom.css';		// テーマファイル
	
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
		$theme = $targetObj->theme;		// 配色用テーマ
		$width = $targetObj->width;		// 幅
		$height = $targetObj->height;		// 高さ
		if (!empty($targetObj->fieldInfo)) $this->fieldInfoArray = $targetObj->fieldInfo;			// お問い合わせフィールド情報

		// Javascript,cssファイルパスを設定
		$this->cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_CSS_FILE);		// CSSファイル
		$themeFile = $this->gEnv->getRootUrl() . self::DEFAULT_THEME_DIR . $theme . '/'. self::THEME_CSS_FILE;		// 管理画面用jQueryUIテーマ
		$this->themeFilePath = $this->getUrl($themeFile);			// jQuery UIテーマ
		
		// 表示オプションを設定
		$option = '';
		if (!empty($width)) $option .= 'width: ' . $width . ', ';
		if (!empty($height)) $option .= 'height: ' . $height . ', ';
		$this->tmpl->addVar("_widget", "option", $option);

		// 動画リスト作成
		$fieldCount = $this->createMovieList();
	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		return array($this->themeFilePath, $this->cssFilePath);		// jQueryUIテーマを先に読み込み
	}
	/**
	 * 動画リスト作成
	 *
	 * @return			なし
	 */
	function createMovieList()
	{
		$fieldCount = count($this->fieldInfoArray);
		for ($i = 0; $i < $fieldCount; $i++){
			$infoObj = $this->fieldInfoArray[$i];
			$name		= $infoObj->name;// 名前
			$movieid	= $infoObj->movieid;		// 動画ID
			
			// セパレータ
			$separator = '';
			if ($i < $fieldCount -1) $separator = ',';
			
			// 文字列はJavascript用のエスケープ処理を行う
			$row = array(
				'name'		=> addslashes($name),	// 名前
				'movie_id'	=> addslashes($movieid),	// 住所
				'separator' => $separator
			);
			$this->tmpl->addVars('field_list', $row);
			$this->tmpl->parseTemplate('field_list', 'a');
		}
	}
}
?>
