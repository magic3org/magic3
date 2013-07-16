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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: s_jquery_headerWidgetContainer.php 4559 2012-01-03 16:26:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class s_jquery_headerWidgetContainer extends BaseWidgetContainer
{
	private $langId;		// 現在の言語
	private $initScript;	// 初期化用スクリプト
	private $autoBackButton;		// 自動的に戻るボタンを表示するかどうか
	private $headPreMobileScript;	// jQueryMobileの前に読み込む必要のあるスクリプト
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'jQueryページ専用ヘッダ';			// デフォルトのウィジェットタイトル
	const INIT_SCRIPT_FILE = '/init.js';					// メニュー初期化ファイル
	
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$name		= $targetObj->name;// 定義名
			$content	= $targetObj->content;		// タグ内容
			$this->autoBackButton = $targetObj->autoBackButton;		// 自動的に戻るボタンを表示するかどうか

			$act = $request->trimValueOf('act');
			if ($act == 'initscript'){			// 初期化スクリプト取得のとき
				// 初期化スクリプトを作成
				$initTemplate = $this->getParsedTemplateData('init.tmpl.js', array($this, 'makeInitScript'));// 初期化スクリプト
				
				// 標準のテンプレート変換をキャンセルし、直接出力
				$this->cancelParse();
				$this->gPage->setOutputByHtml(false);	// HTML出力をキャンセル
				echo $initTemplate;
			} else {
				// タイトルを変換
				$title = '';
				$titleArray = $this->gPage->getHeadSubTitle();
				if (count($titleArray) > 0){
					$title = $titleArray[count($titleArray) -1]['title'];			// 最後に追加されたタイトルを取得
				}
				// タイトルが設定されていないときはページ名を取得
				if (empty($title)){
					$line = $this->gPage->getPageInfo($this->gEnv->getCurrentPageId(), $this->gEnv->getCurrentPageSubId());
					if (!empty($line) && !empty($line['pn_name'])) $title = $line['pn_name'];
				}
				
				$keyTag = M3_TAG_START . M3_TAG_MACRO_TITLE . M3_TAG_END;
				$content = str_replace($keyTag, $this->convertToDispString($title), $content);
				
				// 初期化用スクリプトのURLを作成
				$this->initScript = $this->getUrl($this->createCmdUrlToCurrentWidget('act=initscript'));
//				$this->headPreMobileScript = $this->getParsedTemplateData('add_head.tmpl.js', array($this, 'makeAddScript'));	// jQueryMobileの前に読み込む必要のあるスクリプト

				// 表示データ埋め込み
				$this->tmpl->addVar("_widget", "content",	$content);
			}
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
	 * JavascriptファイルをHTMLヘッダ部に設定
	 *
	 * JavascriptファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						Javascriptファイル。出力しない場合は空文字列を設定。
	 */
	function _addPreMobileScriptFileToHead($request, &$param)
	{
		return $this->initScript;
	}
	/**
	 * テンプレートデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeInitScript($tmpl)
	{
		$valueStr = 'false';
		if (!empty($this->autoBackButton)) $valueStr = 'true';		// 自動的に戻るボタンを表示するかどうか
		$tmpl->addVar("_tmpl", "auto_back_button",	$valueStr);
	}
	/**
	 * JavascriptをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addPreMobileScriptToHead($request, &$param)
	{
		return $this->headPreMobileScript;
	}
	/**
	 * テンプレートデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeAddScript($tmpl)
	{
/*		$valueStr = 'false';
		if (!empty($this->autoBackButton)) $valueStr = 'true';		// 自動的に戻るボタンを表示するかどうか
		$tmpl->addVar("_tmpl", "auto_back_button",	$valueStr);*/
	}
}
?>
