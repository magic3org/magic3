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
		$si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
		$base = 1024;
		$path = '/';

		//全体サイズ
		$total_bytes = disk_total_space($path);
		$class = min((int)log($total_bytes , $base) , count($si_prefix) - 1);
		echo "全体サイズ:" . sprintf('%1.2f' , $total_bytes / pow($base,$class)) . $si_prefix[$class] . "<br />";

		//空き容量
		$free_bytes = disk_free_space($path);
		$class = min((int)log($free_bytes , $base) , count($si_prefix) - 1);
		echo "空き容量:" . sprintf('%1.2f' , $free_bytes / pow($base,$class)) . $si_prefix[$class] . "<br />";

		//使用容量
		$used_bytes = $total_bytes - $free_bytes;
		$class = min((int)log($used_bytes , $base) , count($si_prefix) - 1);
		echo "使用容量:" . sprintf('%1.2f' , $used_bytes / pow($base,$class)) . $si_prefix[$class] . "<br />";

		//使用率
		echo "使用率:" . round($used_bytes / $total_bytes * 100, 2) . "%<br />";
	}
}
?>
