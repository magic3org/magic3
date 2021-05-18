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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/product_randomDb.php');

class admin_ec_product_randomWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $productStatusData;		// 商品ステータスデータ
	private $paramObj;		// ウィジェットデータオブジェクト
	const DEFAULT_ITEM_COUNT = 8;				// デフォルトの表示項目数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new product_randomDb();
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
		// デフォルト値
		$langId	= $this->gEnv->getDefaultLanguage();
		
		// 商品ステータスを取得
		$this->db->getAllProductStatus($langId, $this->productStatusData);
		
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			// 入力値を取得
			$viewCount		= $request->valueOf('view_count');			// 表示項目数
			$condition		= $request->valueOf('condition');			// 条件
			// 商品ステータス
			$statusArray = array();
			for ($i = 0; $i < count($this->productStatusData); $i++){
				$itemName = 'product_status' . $i;
				$itemValue		= $request->valueOf($itemName);
				if (!empty($itemValue)){		// 空以外の値を取得
					$statusArray[] = $itemValue;
				}
			}
			// エラーチェック
			$this->checkNumeric($viewCount, '商品取得数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$this->paramObj->viewCount	= $viewCount;	// 表示項目数
				$this->paramObj->condition	= $condition;	// 条件
				$this->paramObj->statusArray	= $statusArray;	// 商品ステータス
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
			$condition = 0;	// 条件
			$statusArray = array();	// 商品ステータス
			$this->paramObj = $this->getWidgetParamObj();
			if (!empty($this->paramObj)){
				$viewCount	= $this->paramObj->viewCount;	// 表示項目数
				$condition	= $this->paramObj->condition;	// 条件
				$statusArray = $this->paramObj->statusArray;	// 商品ステータス
			}
		}
		// 商品ステータス部作成
		$this->createSelectStatus();
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "view_count",	$viewCount);// 表示項目数
		if (empty($condition)){// 条件
			$this->tmpl->addVar("_widget", "cond_all",	'checked');
		} else {
			$this->tmpl->addVar("_widget", "cond_selected",	'checked');
		}
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
	/**
	 * ステータス選択部を作成
	 *
	 * @return なし						
	 */
	function createSelectStatus()
	{
		$statusArray = $this->paramObj->statusArray;
		
		for ($i = 0; $i < count($this->productStatusData); $i++){
			$value = $this->productStatusData[$i]['pa_id'];
			$selected = '';
			
			if (is_array($statusArray)){
				for ($j = 0; $j < count($statusArray); $j++){
					if ($statusArray[$j] == $value){
						$selected = 'checked';
						break;
					}
				}
			}
			$itemRow = array(		
				'index'		=> $i,			// 項目番号
				'value'		=> $value,			// ID
				'name'		=> $this->productStatusData[$i]['pa_name'],			// カテゴリー名
				'selected'	=> $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('product_status', $itemRow);
			$this->tmpl->parseTemplate('product_status', 'a');
		}
	}
}
?>
