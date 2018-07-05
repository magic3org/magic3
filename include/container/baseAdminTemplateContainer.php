<?php
/**
 * テンプレート管理画面コンテナ作成用ベースクラス
 *
 * テンプレートの管理画面を作成するためのベースクラス
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
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class BaseAdminTemplateContainer extends BaseAdminWidgetContainer
{
//	const DEFAULT_WIDGET_TYPE = 'admin';		// ウィジェットタイプ
//	const TASK_CONFIG_LIST = 'list';			// 設定一覧
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// データ初期化
//		$this->_widgetType = self::DEFAULT_WIDGET_TYPE;						// ウィジェットタイプ
	}
	/**
	 * 出力用の変数に値を設定する
	 * このクラスでは、共通項目を設定
	 */
	function __assign()
	{

	}
	/**
	 * テンプレートファイルの設定
	 * 
	 * @param bool $useSystemTemplate		システムの標準テンプレートを使用するかどうか
	 * @return 								なし
	 */
	function __setTemplate($useSystemTemplate = false)
	{
		// テンプレートオブジェクト作成
		$this->tmpl = new PatTemplate();
 
		// ##### テンプレート読み込みディレクトリ #####
		$dirArray = array();

		// テンプレート管理画面のテンプレートディレクトリを追加
		$templateId = $this->gRequest->trimValueOf(M3_REQUEST_PARAM_TEMPLATE_ID);// テンプレートID
		$dir = $this->gEnv->getTemplatesPath() . '/' . $templateId . '/include/template';
		if (file_exists($dir)) $dirArray[] = $dir;

		$this->tmpl->setRoot($dirArray);
		
		// エラーメッセージテンプレートを埋め込む
		$this->tmpl->applyInputFilter('ErrorMessage');
		
		// 機能付きタグを変換
		//$this->tmpl->applyInputFilter('FunctionTag');
		
		// コメントを削除
		//$this->tmpl->applyInputFilter('StripComments');
		
		// フォームチェック機能を使用するか、システム管理権限がある場合は、管理画面用パラメータを埋め込む
//		if ($this->_useFormCheck || $gEnvManager->isSystemManageUser()) $this->tmpl->applyInputFilter('PostParam');
		$this->tmpl->applyInputFilter('PostParam');
	}
}
?>
