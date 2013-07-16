<?php
/**
 * RSSベースクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: baseRssContainer.php 2631 2009-12-06 11:04:06Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class BaseRssContainer extends BaseWidgetContainer
{
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
	 * @return								なし
	 */
	function process($request)
	{
		// ウィジェット単位のアクセス制御
		if (method_exists($this, '_checkAccess')){
			// アクセス不可のときはここで終了
			if (!$this->_checkAccess($request)) return;
		}
					
		// ディスパッチ処理
		if (method_exists($this, '_dispatch')){
			// 処理を継続しない場合は終了
			if (!$this->_dispatch($request, $param)) return;
		}
		if (method_exists($this, '_setTemplate')){
			// テンプレートファイル名を取得
			// $paramは、任意使用パラメータ
			$templateFile = $this->_setTemplate($request, $param);
			
			// テンプレートファイル名が空文字列のときは、テンプレートライブラリを使用しない
			if ($templateFile != ''){
				// テンプレートオブジェクト作成
				$this->__setTemplate();
				
				// テンプレートファイルを設定
				$this->tmpl->readTemplatesFromFile($templateFile);

				// エラーメッセージ組み込み
				$this->__assign();
			}
			// 各ウィジェットごとのテンプレート処理、テンプレートを使用しないときは出力処理(Ajax等)
			if (method_exists($this, '_preAssign')) $this->_preAssign($request, $param);
			
			// 各ウィジェットごとのテンプレート処理、テンプレートを使用しないときは出力処理(Ajax等)
			if (method_exists($this, '_assign')){
				$this->_assign($request, $param);
			}
				
			// 各ウィジェットごとのテンプレート処理、テンプレートを使用しないときは出力処理(Ajax等)
			if (method_exists($this, '_postAssign')) $this->_postAssign($request, $param);
			
			// RSSチャンネルデータ設定
			if (method_exists($this, '_setRssChannel')){
				$rssData = $this->_setRssChannel($request, $param);
				
				// ヘッダにRSSチャンネルデータを設定
				if (!empty($rssData)) $this->gPage->setRssChannel($rssData);
			}

			if ($templateFile != ''){
				// エラーメッセージ出力
				if ($this->displayMessage) $this->displayMsg();
	
				// HTML生成
				if (!$this->parseCancel) $this->__parse();
			}
		} else {	// メソッドが存在しないときはエラーメッセージを出力
			echo 'method not found: BaseWidgetContainer::_setTemplate()';
		}
	}
}
?>
