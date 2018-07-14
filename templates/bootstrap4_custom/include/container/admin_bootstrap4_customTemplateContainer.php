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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminTemplateContainer.php');

class admin_bootstrap4_customTemplateContainer extends BaseAdminTemplateContainer
{
	private $graphType;			// グラフ種別
	private $path;				// アクセスパス
	private $termType;				// 期間タイプ

	private $templatePath;		// テンプレートのパス
	private $isCssCdn;			// CSSがCDNかどうか
	private $cssData;			// CSSフォイルのパス(「/」で開始)またはCDNタグ
	const CSS_DIR = '/upload/css';		// CSSファイルディレクトリ
	
	const DEFAULT_GRAPH_WIDTH = 800;		// グラフ幅
	const DEFAULT_GRAPH_HEIGHT = 280;		// グラフ高さ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 初期値設定
		$this->templatePath = $this->gEnv->getTemplatesPath() . '/' . $this->_templateId;		// テンプレートのパス
		$this->isCssCdn = false;			// CSSがCDNかどうか
		$this->cssData = '';

		// テンプレートカスタマイズ情報取得
		$ret = $this->_db->getTemplate($this->_templateId, $row);
		if ($ret){
			$optionParams = $row['tm_custom_params'];
			if (empty($optionParams)){
				$templateCustomObj = array();
			} else {
				$templateCustomObj = unserialize($optionParams);		// 連想配列に変換
			}
			$cssData = $templateCustomObj['head_css_data'];
			if (!empty($cssData)){
				$this->cssData = $cssData;
				if (!strStartsWith($this->cssData, '/')) $isCssCdn = true;		// 相対パスでないとき
			}
		}
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
	 * テンプレートの後処理
	 *
	 * テンプレートのデータ埋め込み(_assign())の後に実行される。
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
		// 初期値取得
		
		$act = $request->trimValueOf('act');
		
		// 入力値を取得
		$this->path = $request->trimValueOf('item_path');		// アクセスパス
		$this->termType = $request->trimValueOf('item_term');				// 期間タイプ
		$graphWidth = $request->trimValueOf('item_graph_width');		// グラフ幅
		$graphHeight = $request->trimValueOf('item_graph_height');		// グラフ高さ
		
		$replaceNew = false;		// データを再取得するかどうか
		if ($act == 'update'){		// 設定更新のとき
			// 入力チェック
			$this->checkNumeric($graphWidth, 'グラフ幅');
			$this->checkNumeric($graphHeight, 'グラフ高さ');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->path = $this->path;				// アクセスパス
				$paramObj->termType = $this->termType;		// 期間タイプ
				$paramObj->graphWidth = $graphWidth;		// グラフ幅
				$paramObj->graphHeight = $graphHeight;		// グラフ高さ
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
					$replaceNew = true;			// データ再取得
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			$replaceNew = true;			// データ再取得
		}
		
		if ($replaceNew){		// データ再取得のとき
			$paramObj = $this->getWidgetParamObj();
			if (empty($paramObj)){		// 既存データなしのとき
				// デフォルト値設定
				$graphWidth = self::DEFAULT_GRAPH_WIDTH;		// グラフ幅
				$graphHeight = self::DEFAULT_GRAPH_HEIGHT;		// グラフ高さ
			} else {
				$this->path = $paramObj->path;				// アクセスパス
				$this->termType = $paramObj->termType;		// 期間タイプ
				$graphWidth = $paramObj->graphWidth;		// グラフ幅
				$graphHeight = $paramObj->graphHeight;		// グラフ高さ
			}
		}
		
		// CSSファイル選択メニュー作成
		$ret = $this->createCssFileMenu();
		if (!$ret) 

		// アップロードボタン
		$eventAttr = 'data-toggle="modal" data-target="#uploadModal"';		// ファイル選択ダイアログ起動
		$UploadButtonTag = $this->gDesign->createUploadButton(''/*同画面*/, 'アップロード', ''/*タグID*/, $eventAttr/*追加属性*/);
		$this->tmpl->addVar('_widget', 'upload_button', $UploadButtonTag);
		
		// 値を埋め込む
		$this->tmpl->addVar("_widget", "graph_width", $graphWidth);// グラフ幅
		$this->tmpl->addVar("_widget", "graph_height", $graphHeight);// グラフ高さ
	}
	/**
	 * CSSファイル選択メニュー作成
	 *
	 * @return bool			true=ファイルあり、false=ファイルなし
	 */
	function createCssFileMenu()
	{
		$cssDir = $this->templatePath . '/' . self::CSS_DIR;
		if (!is_dir ($cssDir)) return false;
		
		for ($i = 0; $i < count($this->termTypeArray); $i++){
			$value = $this->termTypeArray[$i]['value'];
			$name = $this->termTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->termType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// ページID
				'name'     => $name,			// ページ名
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('term_list', $row);
			$this->tmpl->parseTemplate('term_list', 'a');
		}
	}
}
?>
