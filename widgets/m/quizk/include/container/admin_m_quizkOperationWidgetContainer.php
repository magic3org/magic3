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
 * @version    SVN: $Id: admin_m_quizkOperationWidgetContainer.php 1923 2009-05-25 11:43:17Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_m_quizkBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/quizkDb.php');

class admin_m_quizkOperationWidgetContainer extends admin_m_quizkBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $defaultSetId;		// 現在選択中のセットID
	const CFG_DEFAULT_SET_ID_KEY = 'current_set_id';		// 現在の選択中のセットID取得用キー
	const CFG_FIELD_PATH_KEY = 'field_path';				// 上位のフィールド階層固定の場合のパス
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new quizkDb();
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
		return 'admin_operation.tmpl.html';
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
		$this->defaultSetId = $request->trimValueOf('item_setid');		// 定義セットID
		$path = $request->trimValueOf('item_path');		// 上位のフィールド階層固定の場合のパス
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			$isErr = false;
			if (!$isErr){
				if (!$this->db->updateConfig(self::CFG_DEFAULT_SET_ID_KEY, $this->defaultSetId)) $isErr = true;
			}
			if (!$isErr){
				if (!$this->db->updateConfig(self::CFG_FIELD_PATH_KEY, $path)) $isErr = true;
			}
			
			if ($isErr){
				$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
			} else {
				$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
			}
			// 値を再取得
			$this->defaultSetId = $this->db->getConfig(self::CFG_DEFAULT_SET_ID_KEY);		// 定義セットID
			$path = $this->db->getConfig(self::CFG_FIELD_PATH_KEY);		// 上位のフィールド階層固定の場合のパス
		} else {
			// 値を取得
			$this->defaultSetId = $this->db->getConfig(self::CFG_DEFAULT_SET_ID_KEY);		// 定義セットID
			$path = $this->db->getConfig(self::CFG_FIELD_PATH_KEY);		// 上位のフィールド階層固定の場合のパス
		}
		// パターンセットメニュー作成
		$this->db->getAllSetId(array($this, 'setIdListLoop'));
		
		$this->tmpl->addVar("_widget", "path", $path);
	}
	/**
	 * パターンセットIDリスト、取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function setIdListLoop($index, $fetchedRow, $param)
	{
		$id = $fetchedRow['qs_id'];
		$name = $fetchedRow['qs_name'];
		
		$selected = '';
		if ($id == $this->defaultSetId) $selected = 'selected';
		$row = array(
			'value'    => $this->convertToDispString($id),			// セットID
			'name'     => $this->convertToDispString($name),			// セットID名
			'selected' => $selected														// 選択中かどうか
		);
		$this->tmpl->addVars('set_id_list', $row);
		$this->tmpl->parseTemplate('set_id_list', 'a');
		return true;
	}
}
?>
