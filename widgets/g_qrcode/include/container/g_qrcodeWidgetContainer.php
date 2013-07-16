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
 * @version    SVN: $Id: g_qrcodeWidgetContainer.php 1803 2009-04-25 05:32:05Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class g_qrcodeWidgetContainer extends BaseWidgetContainer
{
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_QR_CODE_SIZE = 150;		// デフォルトのQRコードサイズ
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
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$paramObj = $this->getWidgetParamObj();
		
		// 指定定義IDのデータを取得
		$name = '';
		for ($i = 0; $i < count($paramObj); $i++){
			$targetObj = $paramObj[$i];
			$id = $targetObj->id;// 定義ID
			if ($id == $configId){
				$name = $targetObj->name;// 定義名
				$qrData	= $targetObj->qrData;		// QRコード化するデータ
				$desc	= $targetObj->desc;		// 説明
				$width = $targetObj->width;		// QRコードサイズ
				if (empty($width)) $width = self::DEFAULT_QR_CODE_SIZE;
				break;
			}
		}
		if ($i < count($paramObj)){		// 該当する定義IDのデータが取得できたとき
			$this->tmpl->setAttribute('show_html', 'visibility', 'visible');
		
			// 説明の設定
			if (!empty($desc)){
				$descString = '<tr><td>' . $desc . '</td></tr>';
				$this->tmpl->addVar("show_html", "desc", $descString);
			}
			// 表示データ埋め込み
			$qrData = urlencode($qrData);
			$this->tmpl->addVar("show_html", "qr_data",	$qrData);
			$size = $width . 'x' . $width;
			$this->tmpl->addVar("show_html", "size",	$size);
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
