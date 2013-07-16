<?php
/**
 * コマンド付きパラメータ管理マネージャー
 *
 * 画面に出力するユーザ向けのメッセージをグローバルで管理する
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: cmdParamManager.php 5295 2012-10-19 01:27:10Z fishbone $
 * @link       http://www.magic3.org
 */
class CmdParamManager
{
	private $params = array();		// コマンド、パラメータ保存用
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
	}
	/**
	 * コマンド付きパラメータを設定
	 *
	 * @param string $id			識別用ID
	 * @param string $cmd 			設定する実行コマンド
	 * @param object $obj 			設定するパラメータオブジェクト
	 * @param object $optionObj 	設定するオプションパラメータオブジェクト
	 * @return bool					true=成功、false=失敗
	 */
	function setParam($id, $cmd, &$obj = NULL, &$optionObj = NULL)
	{
		if (isset($this->params[$id])) $value = $this->params[$id];
		if (!isset($value)) $value = new stdClass;
		$value->cmd = $cmd;
		if ($obj != NULL) $value->param = $obj;
		if ($optionObj != NULL) $value->option = $optionObj;
		$this->params[$id] = $value;
		return true;
	}
	/**
	 * コマンド付きパラメータを取得
	 *
	 * @param string $id	識別用ID
	 * @param string $cmd 	取得する実行コマンド
	 * @param object $obj 	取得するパラメータオブジェクト
	 * @param object $optionObj 	取得するオプションパラメータオブジェクト
	 * @return bool			true=成功、false=失敗
	 */
	function getParam($id, &$cmd, &$obj, &$optionObj)
	{
		if (isset($this->params[$id])){
			$value = $this->params[$id];
			$cmd = $value->cmd;
			$obj = $value->param;
			$optionObj = $value->option;
			return true;
		} else {
			return false;
		}
	}
	/**
	 * オプションパラメータオブジェクトを設定
	 *
	 * @param string $id	識別用ID
	 * @param object $obj 	設定するオプションパラメータオブジェクト
	 * @return bool			true=成功、false=失敗
	 */
	function setOptionParam($id, $obj)
	{
		if (isset($this->params[$id])){
			$value = $this->params[$id];
			$value->option = $obj;
			$this->params[$id] = $value;
			return true;
		} else {
			return false;
		}
	}
	/**
	 * オプションパラメータオブジェクトを取得
	 *
	 * @param string $id	識別用ID
	 * @param object $obj 	取得するオプションパラメータオブジェクト
	 * @return bool			true=成功、false=失敗
	 */
	function getOptionParam($id, &$obj)
	{
		if (isset($this->params[$id])){
			$value = $this->params[$id];
			$obj = $value->option;
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 結果オブジェクトを設定
	 *
	 * @param string $id	識別用ID
	 * @param object $obj 	設定する結果オブジェクト
	 * @return bool			true=成功、false=失敗
	 */
	function setResult($id, $obj)
	{
		if (isset($this->params[$id])){
			$value = $this->params[$id];
			$value->result = $obj;
			$this->params[$id] = $value;
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 結果オブジェクトを取得
	 *
	 * @param string $id	識別用ID
	 * @param object $obj 	取得する結果オブジェクト
	 * @return bool			true=成功、false=失敗
	 */
	function getResult($id, &$obj)
	{
		if (isset($this->params[$id])){
			$value = $this->params[$id];
			$obj = $value->result;
			return true;
		} else {
			return false;
		}
	}
}
?>
