<?php
/**
 * テンプレートコンテナ作成用ベースクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: baseTemplateContainer.php 1653 2009-03-27 05:24:28Z fishbone $
 * @link       http://www.magic3.org
 */
// テンプレートライブラリ読み込み
require_once($gEnvManager->getLibPath() . '/patTemplate/patTemplate.php');
require_once($gEnvManager->getLibPath() . '/patTemplate/patError.php');
require_once($gEnvManager->getLibPath() . '/patTemplate/patErrorManager.php');

class BaseTemplateContainer
{
	protected $tmpl;		// テンプレートオブジェクト
	private $errorMessage    = array();		// アプリケーションのエラー
	private $warningMessage  = array();		// ユーザ操作の誤り
	private $guideMessage = array();		// ガイダンス
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
	}

	/**
	 * 起動マネージャから呼ばれる唯一のメソッド
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function process($request)
	{
		// サブクラスの前処理を実行
		$this->_preBuffer($request);
		
		if (method_exists($this, '_setTemplate')){
			$templateFile = $this->_setTemplate();
			
			// テンプレートファイル名が空文字列のときは、テンプレートライブラリを使用しない
			if ($templateFile != ''){
				// 値を取得
				//$mode = self::valueOf('_mode');
				$mode = $request->valueOf('_mode');
						
				// テンプレートオブジェクト作成
				self::__setTemplate();
				
				// テンプレートファイルを設定
				$this->tmpl->readTemplatesFromFile($templateFile);

				// エラーメッセージ組み込み
				self::__assign();

				// 各ウィジェットごとののテンプレート処理
				if (method_exists($this, '_assign')) $this->_assign($mode);

				// エラーメッセージ出力
				//self::displayMsg();
	
				// HTML生成
				self::__parse();
			}
		} else {	// メソッドが存在しないときはエラーメッセージを出力
			echo 'method not found: BaseTemplateContainer::_setTemplate()';
		}

		// サブクラスの後処理の呼び出し
		$this->_postBuffer($request);
	}

	/**
	 * テンプレートの設定
	 */
	private function __setTemplate()
	{
		// テンプレートオブジェクト作成
		$this->tmpl = new PatTemplate();
 
		// テンプレート読み込みディレクトリ
		global $gEnvManager;
		$this->tmpl->setRoot($gEnvManager->getTemplatesPath());
		
		// エラーメッセージテンプレートを埋め込む
		$this->tmpl->applyInputFilter('ErrorMessage');
		
		// 機能付きタグを変換
		//$this->tmpl->applyInputFilter('FunctionTag');
		
		// コメントを削除
		//$this->tmpl->applyInputFilter('StripComments');
		
		// テンプレートファイルを設定
		//$this->tmpl->readTemplatesFromFile($filename);
	}
	
	/**
	 * 出力用の変数に値を設定する
	 *
	 * このクラスでは、共通項目を設定
	 */
	private function __assign()
	{
		// テンプレートに値を設定
		$now = date("Y/m/d H:i:s");
		$this->tmpl->addVar("_page", "DATE", "created $now");
		
		// リソース読み込みディレクトリを設定
		//$this->tmpl->addVar("_page", "RES", M3_SYSTEM_RES_DIR);
		//$this->tmpl->addVar("_page_body", "RES", M3_SYSTEM_RES_DIR);
		
		// post名を設定
		//$this->tmpl->addVar("_page", "POST_NAME", M3_COMPONENT_POST_NAME);
		//$this->tmpl->addVar("_page_body", "POST_NAME", M3_COMPONENT_POST_NAME);
	}

	/**
	 * 出力データ作成
	 */
	private function __parse()
	{
		echo $this->tmpl->getParsedTemplate('_page');
	}
}
?>
