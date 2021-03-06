<?php
/**
 * ウィジェットジョブコンテナ作成用ベースクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class BaseJobWidgetContainer extends BaseWidgetContainer
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
	 * @param RequestManager $request		HTTPリクエスト処理クラス(未使用)
	 * @return								なし
	 */
	function process($request)
	{
		// ディスパッチ処理
		if (method_exists($this, '_dispatch')){
			// 処理を継続しない場合は終了
			if (!$this->_dispatch()) return;
		}
		
		// ジョブ実行処理
		if (method_exists($this, '_execJob')) $this->_execJob();
	}
}
?>
