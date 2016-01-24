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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_ec_product_carouselWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $paramObj;		// ウィジェットデータオブジェクト
	const DEFAULT_ITEM_COUNT = 10;				// デフォルトの表示項目数
	
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
		// 入力値を取得
		$act = $request->trimValueOf('act');
		$viewCount		= $request->valueOf('view_count');			// 表示項目数
		
		if ($act == 'update'){		// 設定更新のとき
			// エラーチェック
			$this->checkNumeric($viewCount, '商品取得数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$this->paramObj = new stdClass;
				$this->paramObj->viewCount	= $viewCount;	// 表示項目数
				$ret = $this->updateWidgetParamObj($this->paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}				
			}
		} else {		// 初期表示の場合
			// デフォルト値設定
			$viewCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
			$this->paramObj = $this->getWidgetParamObj();
			if (!empty($this->paramObj)){
				$viewCount	= $this->paramObj->viewCount;	// 表示項目数
			}
		}
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "view_count",	$viewCount);// 表示項目数
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
	}
}
?>
