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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_custom_footerWidgetContainer.php 2271 2009-08-31 07:00:18Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class m_custom_footerWidgetContainer extends BaseWidgetContainer
{
	var $db;			// DB接続オブジェクト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
		return 'index.tmpl.html';
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
		$footContent = '';	// フッタコンテンツ
		
		$showFooter = $request->trimValueOf(M3_REQUEST_PARAM_SHOW_FOOTER);
		
		if ($showFooter != M3_REQUEST_VALUE_OFF){		// フッタを表示するとき
			$footContent .= '<hr />' . M3_NL;
			$footContent .= '<div align="center">(C) ' . date("Y") . ' Magic3.org</div>' . M3_NL;
			$footContent .= '<div align="center"><a href="http://www.magic3.org">Magic3 ' . M3_SYSTEM_VERSION . '</a> is licensed under the terms of the GNU General Public License.</div>' . M3_NL;
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$footContent	= $paramObj->footContent;			// フッタコンテンツ
			}
		}

		$this->tmpl->addVar("_widget", "content",	$footContent);
	}
}
?>
