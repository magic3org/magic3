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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_sample_inputWidgetContainer.php 1857 2009-05-06 09:23:04Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath()		. '/baseMobileWidgetContainer.php');

class m_sample_inputWidgetContainer extends BaseMobileWidgetContainer
{
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
		$msg = '';
		$input = $request->mobileTrimValueOf('msg');
		if (!empty($input)){
			$msg = '送信文字は[' . $input . ']です<br /><br />';
		}
		
		// 入力値を戻す
		$this->tmpl->addVar("_widget", "msg", $msg);
		$this->tmpl->addVar("_widget", "home_url", $this->gEnv->getDefaultMobileUrl(true));
		$url = $this->gEnv->createCurrentPageUrlForMobile();
		$this->tmpl->addVar("_widget", "url", $url);
		
		// 携帯キャリア、機種
		$this->tmpl->addVar("_widget", "carrier", $this->agent->getCarrierLongName());
		$this->tmpl->addVar("_widget", "type", $this->agent->getModel());
		$this->tmpl->addVar("_widget", "serial", $this->gEnv->getMobileId());
	}
}
?>
