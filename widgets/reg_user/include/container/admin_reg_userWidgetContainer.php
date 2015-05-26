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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/reg_userCommonDef.php');

class admin_reg_userWidgetContainer extends BaseAdminWidgetContainer
{
	private $authType;				// 承認タイプ
	private $authTypeArray;			// 承認タイプ選択用
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 処理タイプ選択用
		$this->authTypeArray = array(	array(	'name' => 'ユーザの再ログインで自動承認',		'value' => 'auto'),
										array(	'name' => '管理者による手動承認',				'value' => 'admin'));
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
		$this->authType = $request->trimValueOf('item_auth_type');		// 承認タイプ
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			// 入力値エラーチェック
			$this->checkInput($this->authType, 'ユーザ承認', 'ユーザ承認が選択されていません');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->authType	= $this->authType;		// 承認タイプ
				$ret = $this->updateWidgetParamObj($paramObj);
				
				if ($ret) 		$ret = $this->gInstance->getMailManager()->updateMailForm($formId, $this->_langId, $content, $subject);
				
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
			$this->authType = '';
			
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$this->authType	= $paramObj->authType;			// 承認タイプ
			}
		}
		
		// 承認タイプ選択肢作成
		$this->createAuthType();
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
	 * 承認タイプ選択肢作成
	 *
	 * @return なし
	 */
	function createAuthType()
	{
		for ($i = 0; $i < count($this->authTypeArray); $i++){
			$value = $this->authTypeArray[$i]['value'];
			$name = $this->authTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->authType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('authtype_list', $row);
			$this->tmpl->parseTemplate('authtype_list', 'a');
		}
	}
}
?>
