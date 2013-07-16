<?php
/**
 * 携帯index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_indexFrameContainer.php 2358 2009-09-26 06:17:00Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseFrameContainer.php');

class m_indexFrameContainer extends BaseFrameContainer
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
	 * バッファリングの準備
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function _prepareBuffer($request)
	{
		// 携帯の機種に応じて出力エンコーディングを設定する
		//$encode = 'SJIS';
		//$this->gEnv->setMobileEncoding($encode);
	}
	/**
	 * ビュー作成の前処理
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function _preBuffer($request)
	{
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
		return '';
	}
	/**
	 * コンテンツを変換
	 *
	 * @param string $src					変換元コンテンツ
	 * @return 								変換後コンテンツ
	 */
	function _convContents($src)
	{
		// 絵文字変換を行う
		$this->gInstance->getTextConvManager()->convEmoji($src, $dest);
		return $dest;
	}
}
?>
