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
 * @version    SVN: $Id: s_jquery_initWidgetContainer.php 4648 2012-02-01 14:23:42Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class s_jquery_initWidgetContainer extends BaseWidgetContainer
{
	private $initScript;	// 初期化用スクリプトファイル
	private $script;		// 追加スクリプト
	const DEFAULT_CONFIG_ID = 0;
	
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
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (empty($targetObj)){// 定義データが取得できないときは終了
			$this->cancelParse();		// テンプレート変換処理中断
			return;
		} else {
			// 値取得
			$this->script = $targetObj->script;		// スクリプト
				
			$act = $request->trimValueOf('act');
			if ($act == 'initscript'){			// 初期化スクリプト取得のとき
				// 初期化スクリプトを作成
				$initTemplate = $this->getParsedTemplateData('init.tmpl.js', array($this, 'makeInitScript'));// 初期化スクリプト
				
				// 標準のテンプレート変換をキャンセルし、直接出力
				$this->cancelParse();
				$this->gPage->setOutputByHtml(false);	// HTML出力をキャンセル
				echo $initTemplate;
			} else {
				// 初期化用スクリプトのURLを作成
				if (!empty($this->script)) $this->initScript = $this->getUrl($this->createCmdUrlToCurrentWidget('act=initscript'));
			}
		}
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
		$tmpl->addVar("_tmpl", "script",	$this->script);
	}
}
?>
