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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainInitwizardBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainInitwizard_adminWidgetContainer extends admin_mainInitwizardBaseWidgetContainer
{
	const DEFAULT_ADMIN_USER_ID = 1;		// デフォルトの管理者ユーザID
	const DEFAULT_PASSWORD = '********';	// 設定済みを示すパスワード
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
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
		return 'initwizard_admin.tmpl.html';
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
		$this->serialNo = intval($request->trimValueOf('serial'));		// 選択項目のシリアル番号
		$name		= $request->trimValueOf('item_name');
		$account	= $request->trimValueOf('item_account');
		$password	= $request->trimValueOf('password');
		$email		= $request->trimValueOf('item_email');		// Eメール

		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			$this->checkInput($name,			'管理者名');		// 名前
			$this->checkLoginAccount($account,	'アカウント', true);// アカウント
			$this->checkMailAddress($email,		'Eメール', true);		// Eメール
			
			// アカウント重複チェック
			// 設定データを取得
			$ret = $this->db->getUserBySerial($this->serialNo, $row, $groupRows);
			if ($ret){
				if ($row['lu_account'] != $account && $this->_db->isExistsAccount($account)) $this->setMsg(self::MSG_USER_ERR, 'アカウントが重複しています');
			} else {
				$this->setMsg(self::MSG_APP_ERR, 'データ取得に失敗しました');
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// 追加項目
				$otherParams = array();
				$otherParams['lu_email'] = $email;		// Eメール
				$ret = $this->_db->updateLoginUser($this->serialNo, $name, $account, $password, $this->userType, $canLogin, $startDt, $endDt, $newSerial,
													null, null, $this->userGroupArray, $otherParams);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					// 運用ログ出力
					$ret = $this->db->getUserBySerial($newSerial, $row, $groupRows);
					if ($ret) $loginUserId = $row['lu_id'];
					$this->gOpeLog->writeUserInfo(__METHOD__, 'ユーザを更新しました。アカウント: ' . $account, 2100, 'userid=' . $loginUserId . ', username=' . $name);
					
					$this->serialNo = $newSerial;
					$reloadData = true;		// データの再読み込み
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {
			$reloadData = true;
		}
		if ($reloadData){		// データの再読み込み
			// 管理者の情報取得
			$ret = $this->_db->getLoginUserRecordById(self::DEFAULT_ADMIN_USER_ID, $row);
			if ($ret){
				$this->serialNo = $row['lu_serial'];
				$name = $row['lu_name'];
				$account = $row['lu_account'];
				$email = $row['lu_email'];		// Eメール
			}
		}
		$this->tmpl->addVar("_widget", "serial", $this->serialNo);
		$this->tmpl->addVar("_widget", "admin_name",	$this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "admin_account", $this->convertToDispString($account));
		$this->tmpl->addVar("_widget", "admin_email",	$this->convertToDispString($email));		// Eメール
		$this->tmpl->addVar("_widget", "admin_password", self::DEFAULT_PASSWORD);// 入力済みを示すパスワードの設定
		$this->tmpl->addVar("_widget", "admin_password2", self::DEFAULT_PASSWORD);// 入力済みを示すパスワードの設定
	}
}
?>
