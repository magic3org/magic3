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
 * @version    SVN: $Id: admin_mainUsercustomWidgetContainer.php 2235 2009-08-19 02:00:26Z fishbone $
 * @link       http://www.magic3.org
 */
//require_once($gEnvManager->getContainerPath()		. '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainUsercustomWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $sysDb;		// システムDBオブジェクト
	private $maxNo;	// 最大項目番号
	const KEY_HEAD = 'CUSTOM_KEY_';			// 置換キー
	const KEY_GROUP = 'user';				// 置換キーの所属グループ
	const VIEW_STR_LENGTH = 20;			// 表示文字数
	const ADD_STR = '...';				// 文字列を省略する場合の記号
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
		$this->sysDb = $this->gInstance->getSytemDbObject();
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
		return 'usercustom.tmpl.html';
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
		$defaultLang	= $this->gEnv->getDefaultLanguage();
		
		$this->id	= $request->trimValueOf('id');	// ID
		$addid		= $request->trimValueOf('addid');	// 追加ID
		$name	= $request->trimValueOf('item_name');	// 名前
		$value	= $request->valueOf('item_value');	// 値
		$act = $request->trimValueOf('act');
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'select'){		// 行選択のとき
			if (empty($this->id)){		// 新規追加モードにする
				$addid		= '';	// ID
				$name	= '';	// 名前
				$value	= '';	// 内容
			} else {
				$replaceNew = true;		// 設定データを取得
			}
		} else if ($act == 'update'){		// 行更新のとき
			// 入力チェック
			$this->checkInput($name, '表示名');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->sysDb->updateKeyValue($this->id, $value, $name, self::KEY_GROUP);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					
					//$this->id = $addid;		// 新規IDに更新
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else if ($act == 'add'){		// 新規追加のとき
			// 入力チェック
			$this->checkInput($name, '表示名');
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$ret = $this->sysDb->updateKeyValue($addid, $value, $name, self::KEY_GROUP);
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを追加しました');
					
					$this->id = $addid;		// 新規IDに更新
					$replaceNew = true;			// データを再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ追加に失敗しました');
				}
			}
		} else {		// 初期状態
			$this->id = '';			// 選択中の配送項目ID
			$addid		= '';	// ID
			$name	= '';	// 名前
			$value	= '';	// 内容
		}
		// 表示データ再取得
		if ($replaceNew){
			$value = $this->sysDb->getKeyValue($this->id, $name);
		}
		
		// 変換キーを取得
		$this->maxNo = 0;	// 最大項目番号
		$this->db->getAllKey(self::KEY_HEAD, self::KEY_GROUP, array($this, 'keyLoop'));
		
		if (empty($this->id)){		// IDが空のときは新規とする
			$this->maxNo++;
			$addid = self::KEY_HEAD . sprintf('%03d', $this->maxNo);
			$this->tmpl->addVar("_widget", "key", M3_TAG_START . $addid . M3_TAG_END);			// 選択項目のIDラベル
			$this->tmpl->addVar("_widget", "new_selected", 'checked');// ユーザIDが0のときは新規追加をチェック状態にする
			
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');// 新規登録ボタン表示
		} else {
			$this->tmpl->addVar("_widget", "key", M3_TAG_START . $this->id . M3_TAG_END);			// 選択項目のIDラベル
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 更新ボタン表示
		}
		$this->tmpl->addVar("_widget", "id", $this->id);			// ID
		$this->tmpl->addVar("_widget", "add_id", $addid);			// 追加ID
		$this->tmpl->addVar("_widget", "name", $name);		// 名前
		$this->tmpl->addVar("_widget", "value", $value);		// 値
	}
	/**
	 * 取得した変換キーをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function keyLoop($index, $fetchedRow, $param)
	{
		// 項目選択のラジオボタンの状態
		$selected = '';
		if ($fetchedRow['kv_id'] == $this->id){
			$selected = 'checked';
		}
		// 最大項目番号を取得
		$no = substr($fetchedRow['kv_id'], strlen(self::KEY_HEAD));
		$no = intval($no);
		if ($no > $this->maxNo) $this->maxNo = $no;
		
		$id = $fetchedRow['kv_id'];
		$key = M3_TAG_START . $id . M3_TAG_END;
		
		// 値
		$addValue = '';
		$srcValue = $fetchedRow['kv_value'];
		if (function_exists('mb_substr')){
			if (mb_strlen($srcValue) > self::VIEW_STR_LENGTH) $addValue = self::ADD_STR;
			$value = mb_substr($srcValue, 0, self::VIEW_STR_LENGTH) . $addValue;
		} else {
			if (strlen($srcValue) > self::VIEW_STR_LENGTH) $addValue = self::ADD_STR;
			$value = substr($srcValue, 0, self::VIEW_STR_LENGTH) . $addValue;
		}
		$row = array(
			'id' =>	$id,									
			'key'    => $key,
			'name'     => $this->convertToDispString($fetchedRow['kv_name']),			// 表示名
			'value'	=> $this->convertToDispString($value),					// 値
			'checked' => $selected												// 項目選択用ラジオボタン
		);
		$this->tmpl->addVars('key_list', $row);
		$this->tmpl->parseTemplate('key_list', 'a');
		return true;
	}
}
?>
