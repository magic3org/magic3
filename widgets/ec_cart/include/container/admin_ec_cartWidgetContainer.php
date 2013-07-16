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
 * @version    SVN: $Id: admin_ec_cartWidgetContainer.php 5418 2012-11-30 03:10:50Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_ec_cartWidgetContainer extends BaseAdminWidgetContainer
{
	const DEFAULT_TITLE_LENGTH = 10;		// デフォルトのタイトル文字数
	
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
			// 入力値を取得
	//		$isTargetPhoto = ($request->trimValueOf('item_target_photo') == 'on') ? 1 : 0;				// フォトギャラリー画像
	//		$isTargetProduct = ($request->trimValueOf('item_target_product') == 'on') ? 1 : 0;			// 商品
			$titleLength	= $request->trimValueOf('item_title_length');			// タイトル文字数
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
			//	$paramObj->isTargetPhoto = $isTargetPhoto;				// フォトギャラリー画像
		//		$paramObj->isTargetProduct = $isTargetProduct;				// 商品
				$paramObj->titleLength	= $titleLength;		// タイトル文字数
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値設定
			$titleLength = self::DEFAULT_TITLE_LENGTH;	// タイトル文字数
			$paramObj = $this->getWidgetParamObj();
			if (empty($paramObj)){
		//		$isTargetPhoto = 1;				// フォトギャラリー画像
		//		$isTargetProduct = 1;			// 商品
			} else {
		//		$isTargetPhoto = $paramObj->isTargetPhoto;				// フォトギャラリー画像
		//		$isTargetProduct = $paramObj->isTargetProduct;			// 商品
				$titleLength	= $paramObj->titleLength;
			}
		}
		
		// 画面にデータを埋め込む
	//	if (!empty($isTargetPhoto)) $this->tmpl->addVar('_widget', 'target_photo_checked', 'checked');		// フォトギャラリー画像
	//	if (!empty($isTargetProduct)) $this->tmpl->addVar('_widget', 'target_product_checked', 'checked');	// 商品
		$this->tmpl->addVar("_widget", "title_length",	$titleLength);// タイトル文字数
	}
}
?>
