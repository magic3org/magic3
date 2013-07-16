<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_chacha_mainOtherWidgetContainer.php 3238 2010-06-14 05:05:09Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_chacha_mainBaseWidgetContainer.php');

class admin_chacha_mainOtherWidgetContainer extends admin_chacha_mainBaseWidgetContainer
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
		return 'admin_other.tmpl.html';
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
		$defaultLang	= $this->gEnv->getDefaultLanguage();
		$act = $request->trimValueOf('act');
		
		$textColor = $request->trimValueOf('text_color');				// 文字色
		$bgColor = $request->trimValueOf('bg_color');				// 背景色
		$innerBgColor = $request->trimValueOf('inner_bg_color');		// 内枠背景色
		$profileColor = $request->trimValueOf('profile_color');		// プロフィール背景色
		$errMessageColor = $request->trimValueOf('err_message_color');	// エラーメッセージ文字色
		$messageLength = $request->trimValueOf('message_length');	// 投稿文最大長
		$topContents = $request->valueOf('top_contents');	// トップコンテンツ
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$ret = true;
				if ($ret) $this->_db->updateConfig(self::CF_TEXT_COLOR, $textColor, $this->_boardId);	// 文字色
				if ($ret) $this->_db->updateConfig(self::CF_BG_COLOR, $bgColor, $this->_boardId);	// 背景色
				if ($ret) $this->_db->updateConfig(self::CF_INNER_BG_COLOR, $innerBgColor, $this->_boardId);	// 内枠背景色
				if ($ret) $this->_db->updateConfig(self::CF_PROFILE_COLOR, $profileColor, $this->_boardId);	// プロフィール背景色
				if ($ret) $this->_db->updateConfig(self::CF_ERR_MESSAGE_COLOR, $errMessageColor, $this->_boardId);	// エラーメッセージ文字色
				if ($ret) $this->_db->updateConfig(self::CF_MESSAGE_LENGTH, $messageLength, $this->_boardId);	// 投稿文最大長
				if ($ret) $this->_db->updateConfig(self::CF_TOP_CONTENTS, $topContents, $this->_boardId);	// トップコンテンツ
				
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			$reloadData = true;		// データの再読み込み
		}
		if ($reloadData){
			$textColor = $this->_configArray[self::CF_TEXT_COLOR];	// 文字色
			$bgColor = $this->_configArray[self::CF_BG_COLOR];	// 背景色
			$innerBgColor = $this->_configArray[self::CF_INNER_BG_COLOR];	// 内枠背景色
			$profileColor = $this->_configArray[self::CF_PROFILE_COLOR];	// プロフィール背景色
			$errMessageColor = $this->_configArray[self::CF_ERR_MESSAGE_COLOR];	// エラーメッセージ文字色
			$messageLength = $this->_configArray[self::CF_MESSAGE_LENGTH];	// 投稿文最大長
			$topContents = $this->_configArray[self::CF_TOP_CONTENTS];	// トップコンテンツ
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "text_color", $textColor);		// 文字色
		$this->tmpl->addVar("_widget", "bg_color", $bgColor);		// 背景色
		$this->tmpl->addVar("_widget", "inner_bg_color", $innerBgColor);		// 内枠背景色
		$this->tmpl->addVar("_widget", "profile_color", $profileColor);		// プロフィール背景色
		$this->tmpl->addVar("_widget", "err_message_color", $errMessageColor);	// エラーメッセージ文字色
		$this->tmpl->addVar("_widget", "message_length", $messageLength);	// 投稿文最大長
		$this->tmpl->addVar("_widget", "top_contents", $topContents);	// トップコンテンツ
	}
}
?>
