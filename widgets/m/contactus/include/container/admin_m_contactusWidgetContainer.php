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
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_m_contactusWidgetContainer.php 867 2008-07-28 09:04:54Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() .			'/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/contactus_mainDb.php');

class admin_m_contactusWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const DEFAULT_SEND_MESSAGE = 1;		// メール送信機能を使用するかどうか(デフォルト使用)
					
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new contactus_mainDb();
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
		global $gEnvManager;
		global $gPageManager;
		
		$defaultLang	= $gEnvManager->getDefaultLanguage();
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			$sendMessage = ($request->trimValueOf('send_message') == 'on') ? 1 : 0;		// メール送信機能を使用するかどうか
			//$emailReceiver = $request->trimValueOf('email_receiver');			// メール受信者
			$emailReceiver = trim($request->valueOf('email_receiver'));			// メール受信者(aaaa<xxx@xxx.xxx>形式が可能)
			$companyVisible = ($request->trimValueOf('company_visible') == 'on') ? 1 : 0;		// 会社名入力フィールドの表示
			$addressVisible = ($request->trimValueOf('address_visible') == 'on') ? 1 : 0;		// 住所入力フィールドの表示
			$telVisible = ($request->trimValueOf('tel_visible') == 'on') ? 1 : 0;		// 電話番号入力フィールドの表示
			// 入力値のエラーチェック
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj->sendMessage = $sendMessage;			// メール送信機能を使用するかどうか
				$paramObj->emailReceiver = $emailReceiver;		// メール受信者
				$paramObj->companyVisible = $companyVisible;	// 会社名入力フィールドの表示
				$paramObj->addressVisible = $addressVisible;	// 住所入力フィールドの表示
				$paramObj->telVisible = $telVisible;	// 電話番号入力フィールドの表示
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$gPageManager->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値の設定
			$sendMessage = self::DEFAULT_SEND_MESSAGE;			// メール送信機能を使用するかどうか
			$emailReceiver = '';		// メール受信者
			$companyVisible = 0;	// 会社名入力フィールドの表示
			$addressVisible = 0;	// 住所入力フィールドの表示
			$telVisible = 0;		// 電話番号入力フィールドの表示
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$sendMessage = $paramObj->sendMessage;			// メール送信機能を使用するかどうか
				$emailReceiver = $paramObj->emailReceiver;		// メール受信者
				$companyVisible = $paramObj->companyVisible;	// 会社名入力フィールドの表示
				$addressVisible = $paramObj->addressVisible;	// 住所入力フィールドの表示
				$telVisible = $paramObj->telVisible;		// 電話番号入力フィールドの表示
			}
		}
		// 画面に書き戻す
		$checked = '';
		if ($sendMessage) $checked = 'checked';
		$this->tmpl->addVar("_widget", "send_message", $checked);
		$this->tmpl->addVar("_widget", "email_receiver", $emailReceiver);		// メール受信者
		$checked = '';
		if ($companyVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "company_visible", $checked);// 会社名入力フィールドの表示
		$checked = '';
		if ($addressVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "address_visible", $checked);// 住所入力フィールドの表示
		$checked = '';
		if ($telVisible) $checked = 'checked';
		$this->tmpl->addVar("_widget", "tel_visible", $checked);// 電話番号入力フィールドの表示
	}
}
?>
