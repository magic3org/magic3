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
 * @version    SVN: $Id: admin_fontsizeWidgetContainer.php 2266 2009-08-28 08:25:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_fontsizeWidgetContainer extends BaseAdminWidgetContainer
{
	const DEFAULT_FONTRESIZE_CLASS = 'fontresize';			// フォントリサイズ領域を指定するためのクラス名

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
		$targetClass = $request->trimValueOf('item_class_name');		// フォント変更対象クラス
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			// 入力値エラーチェック
			$this->checkInput($targetClass, '対象クラス名');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj->targetClass	= $targetClass;		// フォント変更対象クラス
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;		// データを再取得するかどうか
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			$replaceNew = true;		// データを再取得するかどうか
		}
		if ($replaceNew){
			// デフォルト値設定
			$targetClass = self::DEFAULT_FONTRESIZE_CLASS;
			
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$targetClass	= $paramObj->targetClass;			// フォント変更対象クラス
			}
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "class_name", $targetClass);		// フォント変更対象クラス
	}
}
?>
