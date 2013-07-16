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
 * @version    SVN: $Id: s_css_addWidgetContainer.php 5607 2013-02-07 08:48:39Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class s_css_addWidgetContainer extends BaseWidgetContainer
{
	private $paramObj;		// 定義取得用
	private $headCss;			// ヘッダ出力用CSS
	private $cssFiles;		// CSSファイル
	const DEFAULT_CONFIG_ID = 0;
	const CSS_DIR = '/resource/css';			// CSSファイル格納ディレクトリ
	
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
		//return 'index.tmpl.html';
		return '';
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
		
		// 初期値設定
		$files = array();
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$menuId		= $targetObj->menuId;	// メニューID
			$name		= $targetObj->name;// 定義名
			$this->headCss = $this->convertM3ToText($targetObj->css);	// 標準マクロ変換してCSSを作成
			$files 		= $targetObj->cssFiles;			// CSSファイル
			if (!isset($files)) $files = array();
		}
		
		// CSSファイルのパスを修正
		$this->cssFiles = array();
		$cssDir = $this->gEnv->getSystemRootPath() . self::CSS_DIR;		// CSSファイル読み込みディレクトリ
		$cssUrl = $this->gEnv->getRootUrl() . self::CSS_DIR;		// CSSファイル読み込みURL
		for ($i = 0; $i < count($files); $i++){
			$file = $cssDir . $files[$i];
			if (file_exists($file)){
				$fileUrl = $this->getUrl($cssUrl . $files[$i]);
				$this->cssFiles[] = $fileUrl;
			} else {		// ファイルが存在しないときはエラーメッセージ
				$this->gOpeLog->writeError(__METHOD__, 'CSSファイルが存在しません。(ファイル=' . $file . ')', 1100);
			}
		}
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
		return $this->headCss;
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
		return $this->cssFiles;
	}
}
?>
