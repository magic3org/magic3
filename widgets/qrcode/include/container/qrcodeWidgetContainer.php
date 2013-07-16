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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: qrcodeWidgetContainer.php 2263 2009-08-28 05:21:34Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() .			'/baseWidgetContainer.php');
 
class qrcodeWidgetContainer extends BaseWidgetContainer
{
	const DEFAULT_TITLE = 'QRコード';			// デフォルトのウィジェットタイトル
	
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
		// 設定値の取得
		$qrData = '';				// QRコード化するデータ
		$desc = '';				// 説明
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$qrData = $paramObj->qrData;						// QRコード化するデータ
			$desc = $paramObj->desc;				// 説明
		}
		
		$act = $request->trimValueOf('act');
		if ($act == 'genarate'){			// QRコード生成のとき
			require_once($this->gEnv->getLibPath() .	'/qr_img0.50g/php/qr_img.php');
		} else {
			// QRコード生成パスを設定
			$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
			$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
			$urlparam .= 'act=genarate&t=P&s=3&d=' . urlencode($qrData);
			
			$this->tmpl->addVar("_widget", "qr_path", $this->getUrl($this->gEnv->getDefaultUrl() . '?' . $urlparam));
			$this->tmpl->addVar("_widget", "desc", $desc);				// 説明
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
}
?>
