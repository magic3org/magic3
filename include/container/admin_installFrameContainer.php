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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_installFrameContainer.php 3770 2010-11-05 04:07:38Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseFrameContainer.php');

class admin_installFrameContainer extends BaseFrameContainer
{
	const DEFAULT_LANG = 'ja';		// デフォルトのインストール言語
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		global $gPageManager;
		
		// 親クラスを呼び出す
		parent::__construct();
		
		$gPageManager->useBootstrap();			// Bootstrapを使用
		$gPageManager->setHtml5();			// HTML5で出力するかどうか
	}
	/**
	 * フレーム単位のアクセス制御
	 *
	 * 同フレーム(同.phpファイル)での共通のアクセス制御を行う
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function _checkAccess($request)
	{
		return true;
	}
	/**
	 * ビュー作成の前処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function _preBuffer($request)
	{
		// インストール処理言語の設定
		$lang = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);
		if (empty($lang)) $lang = self::DEFAULT_LANG;
		$this->gEnv->setCurrentLanguage($lang);
	}
	/**
	 * ビュー作成の後処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function _postBuffer($request)
	{
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return 								テンプレートを固定にしたい場合はテンプレート名を返す。
	 *										テンプレートが任意の場合(変更可能な場合)は空文字列を返す。
	 */
	function _setTemplate($request)
	{	
		return '_install';
	}
}
?>
