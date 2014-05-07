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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainBaseWidgetContainer.php');

class admin_mainServerinfoWidgetContainer extends admin_mainBaseWidgetContainer
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
		return 'serverinfo.tmpl.html';
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
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
		$base = 1024;
		$path = '/';

		//全体サイズ
		$totalBytes = disk_total_space($path);
		$class = min((int)log($totalBytes , $base) , count($units) - 1);
		$totalStr = sprintf('%1.2f' , $totalBytes / pow($base, $class)) . $units[$class];

		//空き容量
		$freeBytes = disk_free_space($path);
		$class = min((int)log($freeBytes , $base) , count($units) - 1);
		$freeStr = sprintf('%1.2f' , $freeBytes / pow($base, $class)) . $units[$class];

		//使用容量
		$usedBytes = $totalBytes - $freeBytes;
		$class = min((int)log($usedBytes , $base) , count($units) - 1);
		$usedStr = sprintf('%1.2f' , $usedBytes / pow($base, $class)) . $units[$class];

		//使用率
		$usedRateStr = round($usedBytes / $totalBytes * 100, 2) . '%';
		
		// 値を埋め込む
		$this->tmpl->addVar('_widget', 'total_size',	$this->convertToDispString($totalStr));
		$this->tmpl->addVar('_widget', 'free_size',		$this->convertToDispString($freeStr));
		$this->tmpl->addVar('_widget', 'used_size',		$this->convertToDispString($usedStr));
		$this->tmpl->addVar('_widget', 'used_rate',		$this->convertToDispString($usedRateStr));
	}
}
?>
