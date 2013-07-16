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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_event_mainConfigWidgetContainer.php 3975 2011-02-01 10:47:03Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_event_mainBaseWidgetContainer.php');

class admin_event_mainConfigWidgetContainer extends admin_event_mainBaseWidgetContainer
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
		return 'admin_config.tmpl.html';
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
		$entryViewCount = $request->trimValueOf('entry_view_count');		// 記事表示数
		$entryViewOrder = $request->trimValueOf('entry_view_order');		// 記事表示順
		$receiveComment = ($request->trimValueOf('receive_comment') == 'on') ? 1 : 0;		// コメントを受け付けるかどうか
		$topContents = $request->valueOf('top_contents');	// トップコンテンツ
		$maxCommentLength = $request->valueOf('max_comment_length');	// コメント最大文字数
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($entryViewCount, '記事表示順');
			$this->checkNumeric($maxCommentLength, 'コメント最大文字数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$isErr = false;
				
				if (!$isErr){
					if (!$this->_db->updateConfig(self::CF_ENTRY_VIEW_COUNT, $entryViewCount)) $isErr = true;// 記事表示数
				}
				if (!$isErr){
					if (!$this->_db->updateConfig(self::CF_ENTRY_VIEW_ORDER, $entryViewOrder)) $isErr = true;// 記事表示順
				}
				if (!$isErr){
					if (!$this->_db->updateConfig(self::CF_RECEIVE_COMMENT, $receiveComment)) $isErr = true;// コメントを受け付けるかどうか
				}
				if (!$isErr){
					if (!$this->_db->updateConfig(self::CF_MAX_COMMENT_LENGTH, $maxCommentLength)) $isErr = true;// コメント最大文字数
				}
				if (!$isErr){
					if (!$this->_db->updateConfig(self::CF_TOP_CONTENTS, $topContents)) $isErr = true;// トップコンテンツ
				}
				if ($isErr){
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				} else {
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				}
				// 値を再取得
				$entryViewCount	= $this->_db->getConfig(self::CF_ENTRY_VIEW_COUNT);// 記事表示数
				$entryViewOrder	= $this->_db->getConfig(self::CF_ENTRY_VIEW_ORDER);// 記事表示順
				$receiveComment	= $this->_db->getConfig(self::CF_RECEIVE_COMMENT);
				$maxCommentLength = $this->_db->getConfig(self::CF_MAX_COMMENT_LENGTH);	// コメント最大文字数
				$topContents = $this->_db->getConfig(self::CF_TOP_CONTENTS);// トップコンテンツ
			}
		} else {		// 初期表示の場合
			$entryViewCount	= $this->_db->getConfig(self::CF_ENTRY_VIEW_COUNT);// 記事表示数
			if (empty($entryViewCount)) $entryViewCount = self::DEFAULT_VIEW_COUNT;
			$entryViewOrder	= $this->_db->getConfig(self::CF_ENTRY_VIEW_ORDER);// 記事表示順
			$receiveComment	= $this->_db->getConfig(self::CF_RECEIVE_COMMENT);
			$maxCommentLength = $this->_db->getConfig(self::CF_MAX_COMMENT_LENGTH);	// コメント最大文字数
			if ($maxCommentLength == '') $maxCommentLength = self::DEFAULT_COMMENT_LENGTH;
			$topContents = $this->_db->getConfig(self::CF_TOP_CONTENTS);// トップコンテンツ
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "view_count", $entryViewCount);// 記事表示数
		if (empty($entryViewOrder)){	// 順方向
			$this->tmpl->addVar("_widget", "view_order_inc_selected", 'selected');// 記事表示順
		} else {
			$this->tmpl->addVar("_widget", "view_order_dec_selected", 'selected');// 記事表示順
		}
		$checked = '';
		if ($receiveComment) $checked = 'checked';
		$this->tmpl->addVar("_widget", "receive_comment", $checked);// コメントを受け付けるかどうか
		$this->tmpl->addVar("_widget", "max_comment_length", $maxCommentLength);// コメント最大文字数
		$this->tmpl->addVar("_widget", "top_contents", $topContents);		// トップコンテンツ
	}
}
?>
