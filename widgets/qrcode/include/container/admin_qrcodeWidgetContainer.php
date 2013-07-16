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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_qrcodeWidgetContainer.php 5168 2012-09-06 01:35:37Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() .			'/baseAdminWidgetContainer.php');

class admin_qrcodeWidgetContainer extends BaseAdminWidgetContainer
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
		return 'admin.tmpl.html';
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
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			$qrData = $request->trimValueOf('qr_data');			// QRコード化するデータ
			$desc = $request->trimValueOf('desc');				// 説明

			// 入力値のエラーチェック
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->qrData = $qrData;						// QRコード化するデータ
				$paramObj->desc = $desc;				// 説明
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値の設定
			$qrData = $this->gEnv->getDefaultMobileUrl();			// QRコード化するデータ、デフォルト値は携帯サイトURL
			$desc = '';				// 説明
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$qrData = $paramObj->qrData;			// QRコード化するデータ
				$desc = $paramObj->desc;				// 説明
			}
		}
		$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
		$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
		$generateUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam . 'act=genarate&t=P&s=3&d=' . '[urlエンコードしたデータ]';			// QRコード生成URL
			
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "qr_data", $qrData);			// QRコード化するデータ
		$this->tmpl->addVar("_widget", "desc", $desc);				// 説明
		$this->tmpl->addVar("_widget", "gurl", $this->getUrl($generateUrl));		// QRコード生成URL
	}
}
?>
