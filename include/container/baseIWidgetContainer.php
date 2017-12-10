<?php
/**
 * ウィジェットコンテナ作成用ベースクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class BaseIWidgetContainer extends BaseWidgetContainer
{
	private $_paramObj;		// パラメータオブジェクト
	const OUTPUT_CONTENT = 'content';		// HTML出力
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * 起動マネージャから呼ばれる唯一のメソッド
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return								なし
	 */
	function process($request)
	{
		if (method_exists($this, '_setTemplate')){
			// 実行コマンド、パラメータを取得
			$id			= $this->gEnv->getCurrentIWidgetId();
			$configId	= $this->gEnv->getCurrentIWidgetConfigId();		// 定義ID
			$ret = $this->gInstance->getCmdParamManager()->getParam($id . M3_WIDGET_ID_SEPARATOR . $configId, $cmd, $obj, $optionObj);

			// テンプレートファイル名を取得
			// $paramは、任意使用パラメータ
			$templateFile = $this->_setTemplate($request, $cmd, $obj, $optionObj);

			// テンプレートファイル名が空文字列のときは、テンプレートライブラリを使用しない
			if (!empty($templateFile) || $cmd == self::OUTPUT_CONTENT){
				// テンプレートオブジェクト作成
				self::__setTemplate();
				
				// テンプレートファイルを設定
				$this->tmpl->readTemplatesFromFile($templateFile);

				// エラーメッセージ組み込み
				self::__assign();
			}
			
			// 各ウィジェットごとのテンプレート処理、テンプレートを使用しないときは出力処理(Ajax等)
			if (method_exists($this, '_assign')){
				$this->_assign($request, $cmd, $obj, $optionObj);
			}
				
			if (!empty($templateFile) || $cmd == self::OUTPUT_CONTENT){
				// エラーメッセージ出力
//				self::displayMsg();
	
				// HTML生成
				self::__parse();
			}
		} else {	// メソッドが存在しないときはエラーメッセージを出力
			echo 'method not found: BaseWidgetContainer::_setTemplate()';
		}
		// 出力メッセージをメッセージマネージャに設定
		$this->addMsgToGlobal();
	}
	/**
	 * テンプレートファイルの設定
	 */
	function __setTemplate()
	{
		// テンプレートオブジェクト作成
		$this->tmpl = new PatTemplate();
 
		// テンプレート読み込みディレクトリを設定
		//$this->tmpl->setRoot($this->gEnv->getCurrentWidgetTemplatePath());
		// ウィジェットIDとインナーウィジェットIDを取り出す
		$id = $this->gEnv->getCurrentIWidgetId();
		list($widgetId, $iWidgetId) = explode(M3_WIDGET_ID_SEPARATOR, $id);

		// テンプレートディレクトリ作成
		if (empty($widgetId)){		// ウィジェットIDが指定されていないときは共通ディレクトリ
		//$this->gEnv->getIWidgetsPath() . '/' . $iWidgetId . '/' . $containerClass . '.php';
		} else {
			$templatePath = $this->gEnv->getWidgetsPath() . '/' . $widgetId . '/include/iwidgets/' . $iWidgetId . '/include/template';
		}
		$this->tmpl->setRoot($templatePath);
		
		// エラーメッセージテンプレートを埋め込む
		$this->tmpl->applyInputFilter('ErrorMessage');
		
		// 機能付きタグを変換
		//$this->tmpl->applyInputFilter('FunctionTag');
		
		// コメントを削除
		//$this->tmpl->applyInputFilter('StripComments');
	}
	/**
	 * 出力用の変数に値を設定する
	 * このクラスでは、共通項目を設定
	 */
	function __assign()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}
	/**
	 * メッセージテーブルにグローバルメッセージを追加する
	 *
	 * @return 				なし
	 */
	function addMsgToGlobal()
	{
		$this->gInstance->getMessageManager()->addMessage($this->errorMessage, $this->warningMessage, $this->guideMessage);
	}
	/**
	 * 定義情報オブジェクトを更新
	 *
	 * @param object $obj		格納するウィジェットパラメータオブジェクト
	 * @return bool				true=更新成功、false=更新失敗
	 */
	function updateConfigObj($obj)
	{
		$id = $this->gEnv->getCurrentIWidgetId();
		$configId	= $this->gEnv->getCurrentIWidgetConfigId();		// 定義ID
		$ret = $this->gInstance->getCmdParamManager()->setParam($id . M3_WIDGET_ID_SEPARATOR . $configId, '', $obj);
		return $ret;
	}
	/**
	 * 結果オブジェクトを更新
	 *
	 * @param object $obj		格納するウィジェットパラメータオブジェクト
	 * @return bool				true=更新成功、false=更新失敗
	 */
	function setResultObj($obj)
	{
		$id = $this->gEnv->getCurrentIWidgetId();
		$configId	= $this->gEnv->getCurrentIWidgetConfigId();		// 定義ID
		$ret = $this->gInstance->getCmdParamManager()->setResult($id . M3_WIDGET_ID_SEPARATOR . $configId, $obj);
		return $ret;
	}
}
?>
