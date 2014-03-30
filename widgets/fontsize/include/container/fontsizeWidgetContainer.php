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

class fontsizeWidgetContainer extends BaseWidgetContainer
{
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'フォントサイズ';			// デフォルトのウィジェットタイトル
	const DEFAULT_FONTRESIZE_CLASS = 'fontresize';			// フォントリサイズ領域を指定するためのクラス名
	const DEFAULT_SCRIPT_FILE = '/jquery.textresizer.min.js';		// scriptファイル
	
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
	/*
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			//$name = $targetObj->name;// 定義名
			//$tagId = $targetObj->tagId;		// 選択範囲のタグID
			
			// 表示データ埋め込み
			//$this->tmpl->setAttribute('show_html', 'visibility', 'visible');// 画像を表示
			//$this->tmpl->addVar("_widget", "tag_id",	$tagId);
			// 画面に書き戻す
			$this->tmpl->addVar("_widget", "class_name", $targetObj->targetClass);		// フォント変更対象クラス
		}*/
		// デフォルト値設定
		$targetClass = self::DEFAULT_FONTRESIZE_CLASS;
		
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$targetClass	= $paramObj->targetClass;			// フォント変更対象クラス
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "class_name", $targetClass);		// フォント変更対象クラス
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
	function _addScriptFileToHead($request, &$param)
	{
		$scriptArray = array($this->getUrl($this->gEnv->getCurrentWidgetScriptsUrl() . self::DEFAULT_SCRIPT_FILE));
		return $scriptArray;
	}
}
?>
