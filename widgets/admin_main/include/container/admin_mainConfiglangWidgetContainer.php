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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainConfiglangWidgetContainer.php 5124 2012-08-20 03:30:46Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConfigsystemBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainConfiglangWidgetContainer extends admin_mainConfigsystemBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $acceptLanguage;	// アクセス可能言語
	private $serialArray = array();		// 表示されている項目シリアル番号
	const CF_ACCEPT_LANGUAGE	= 'accept_language';	// アクセス可能言語
	const ICON_PATH = '/images/system/flag/';		// 言語アイコンパス
	const ITEM_HEAD_ACCEPT = 'item_accept';
	const ITEM_HEAD_AVAILABLE = 'item_available';
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_mainDb();
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
		return 'configlang.tmpl.html';
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
		return $this->createDetail($request);
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');

		if ($act == 'update'){		// 更新のとき
			// 入力チェック
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				$listedItem = explode(',', $request->trimValueOf('seriallist'));
				$selItems = array();
				$ret = true;
				for ($i = 0; $i < count($listedItem); $i++){
					// アクセス可能項目がチェックされているかを取得
					$itemName = self::ITEM_HEAD_ACCEPT . $i;
					$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
					if ($itemValue) $selItems[] = $listedItem[$i];	// チェック項目
					
					// メニューからの選択可否を更新
					$itemName = self::ITEM_HEAD_AVAILABLE . $i;
					$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
					$ret = $this->db->updateLangStatus($listedItem[$i], $itemValue);
					if (!$ret) break;
				}
				$langStr = '';
				if (!empty($selItems)) $langStr = implode(',', $selItems);
				
				// アクセス可能言語を更新
				if ($ret) $ret = $this->_db->updateSystemConfig(self::CF_ACCEPT_LANGUAGE, $langStr);
				
				if ($ret){		// データ追加成功のとき
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
			}
		} else {		// 初期状態
		}
		$this->acceptLanguage = $this->gSystem->getAcceptLanguage(true/*再取得*/);
		
		// 言語一覧を取得
		$this->db->getAllLang(array($this, 'langListLoop'));

		$this->tmpl->addVar("_widget", "serial_list", implode(',', $this->serialArray));// 表示項目のシリアル番号を設定
	}
	/**
	 * 言語データをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function langListLoop($index, $fetchedRow, $param)
	{
		$langId = $fetchedRow['ln_id'];		// 言語ID
		
		$name = $fetchedRow['ln_name'];
		if ($langId == $this->gEnv->getDefaultLanguage()) $name .= '(デフォルト)';
		
		// 言語アイコン
		$iconTitle = $fetchedRow['ln_name'];
		$iconUrl = $this->gEnv->getRootUrl() . self::ICON_PATH . $fetchedRow['ln_image_filename'];		// 画像ファイル
		$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// アクセス許可状態を取得
		$accept = '';
		if (in_array($langId, $this->acceptLanguage)) $accept = 'checked';
		
		$available = '';
		if ($fetchedRow['ln_available']) $available = 'checked';
		
		$row = array(
			'image'		=> $iconTag,				// 言語アイコン
			'index'		=> $index,
			'value'		=> $this->convertToDispString($langId),			// 言語値
			'name'		=> $this->convertToDispString($name),			// 言語名
			'accept'	=> $accept,										// アクセス許可状態
			'available'	=> $available									// 利用可(管理画面)
		);
		$this->tmpl->addVars('lang_list', $row);
		$this->tmpl->parseTemplate('lang_list', 'a');
		
		// 表示中項目のページサブIDを保存
		$this->serialArray[] = $langId;
		return true;
	}
}
?>
