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
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/_installBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/_installDb.php');

class _installCheckdbWidgetContainer extends _installBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	const FIRST_VER = 2016073101;			// バージョンアップ可能なDBのバージョン

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new _installDB();
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
		return 'checkdb.tmpl.html';
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
	function _assign($request, &$param)
	{
		$act = $request->trimValueOf('act');
		$updateType = $request->trimValueOf('update_type');
		if (empty($updateType)) $updateType = 'update';

		// バージョンアップが可能かどうかチェック
		$canVerUp = false;

		// DB接続可能なときはDBのバージョンを取得
		$status = $this->_db->getDisplayErrMessage();	// 出力状態を取得
		$currentVer = $this->_db->getSystemConfig(M3_TB_FIELD_DB_VERSION);	// 接続先が不明でここで落ちている(調査中)
		$this->_db->displayErrMessage($status);		// 抑止解除
		if (empty($currentVer)) $currentVer = 0;
		if ($currentVer >= self::FIRST_VER) $canVerUp = true;

		if ($canVerUp){			// DBバージョンアップ可能なとき
			if ($updateType == 'init'){
				$this->tmpl->addVar("_widget", "init_selected", 'selected');		// 初期化選択
				$msg = '<b><font color="red">' . $this->_('NOTICE&nbsp;&nbsp;All the existing data cleared.') . '</font></b><br />';		// 注意&nbsp;&nbsp;データはすべて消去されます
				$msg .= $this->_('Clear all the data, and update system and database.');// DBの内容をすべてクリアしてから、システムをバージョンアップします
			} else {
				$this->tmpl->addVar("_widget", "update_selected", 'selected');		// バージョンアップ選択
				$msg = $this->_('Keep existing data, and update system and database.');// DBの内容を保持したまま、システムをバージョンアップします
			}
			$this->tmpl->addVar("_widget", "comment", $msg);		// メッセージを設定
		} else {
			if (empty($currentVer)){		// 新規インストールのとき
				// DB構築画面へ遷移
				$this->gPage->redirectInInstall('?task=initdb&from=inputparam' . '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $this->gEnv->getCurrentLanguage());	// ここで遷移している(調査中)
			} else {
				$msg = 'このDBのバージョン(' . $currentVer . ')はバージョンアップ対象外です<br />バージョンアップ機能はシステムバージョン1.7.0以降のDBが対象です';
				$this->tmpl->addVar("_widget", "message", $msg);
			}
			$this->tmpl->addVar("_widget", "init_selected", 'selected');		// 初期化選択
			$this->tmpl->addVar("_widget", "update_type_disabled", 'disabled');		// 選択無効
		}
		
		// テキストをローカライズ
		$localeText = array();
		$localeText['title_select_versionup_type'] = $this->_('Select Versionup Type');	// バージョンアップ方法の選択
		$localeText['msg_select_versionup_type'] = $this->_('Select Versionup Type for Database.');// DBのバージョンアップ方法を選択してください
		$localeText['label_versionup_type'] = $this->_('Versionup Type');// バージョンアップ方法
		$localeText['label_desc'] = $this->_('Details');// [説明]
		$localeText['label_type_clean_install'] = $this->_('Clean All Data, and Rebuild Database');// 既存データをすべて消去して、DBを再構築
		$localeText['label_type_update_install'] = $this->_('Keep Existing Data, and Update Database');// 既存データを残して、DBをバージョンアップ
		$this->setLocaleText($localeText);
	}
}
?>
