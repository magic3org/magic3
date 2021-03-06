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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class g_analytics2WidgetContainer extends BaseWidgetContainer
{
	private $trackingId;		// Google AnalyticsのトラッキングID
	
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
		$this->trackingId = '';	// Google AnalyticsのトラッキングID
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->trackingId	= $paramObj->trackingId;
		}

		// Google Analyticsトラッキングコード作成
		// トラッキングIDが空のときは出力しない
		$script = '';
		if (!empty($this->trackingId)) $script = $this->getParsedTemplateData('default.tmpl.js', array($this, 'makeScript'));
		
		// トラッキングコードをHEADタグの先頭に追加
		if (!empty($script)) $this->gPage->setHeadFirstTag($script);
	}
	/**
	 * Javascriptデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeScript($tmpl)
	{
		$tmpl->addVar("_tmpl", "tracking_id",	$this->trackingId);		// Google AnalyticsのトラッキングID
	}
}
?>
