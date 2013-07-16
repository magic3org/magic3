<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    ユーザ作成コンテンツ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: user_contentTopWidgetContainer.php 3679 2010-10-08 03:02:35Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/user_contentBaseWidgetContainer.php');

class user_contentTopWidgetContainer extends user_contentBaseWidgetContainer
{
	private $langId;		// 言語ID
	private $roomId;		// ルームID
	private $contentArray = array();	// 現在のルームに対応したコンテンツ情報
	private $isTabCreated;	// タブが作成されたかどうか
	private $isSystemManageUser;	// システム運用可能ユーザかどうか
	private $currentDay;		// 現在日
	private $currentHour;		// 現在時間
	const CONTENT_TYPE = 'uc';		// ユーザ作成コンテンツ参照数カウント用
	const ICON_SIZE = 16;		// アイコンのサイズ
	const EDIT_ICON_FILE = '/images/system/page_edit.png';		// 編集アイコン
	
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
		$act = $request->trimValueOf('act');
		if (version_compare(M3_SYSTEM_VERSION, '1.15.0') >= 0){			// jQuery v1.4対応版
			return 'new/main.tmpl.html';
		} else {
			return 'main.tmpl.html';
		}
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
		$this->langId = $this->gEnv->getDefaultLanguage();		// デフォルト言語
		$this->isSystemManageUser = $this->gEnv->isSystemManageUser();	// システム運用可能ユーザかどうか
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間
		$act = $request->trimValueOf('act');
		$this->roomId = $request->trimValueOf(M3_REQUEST_PARAM_ROOM_ID);
		if (empty($this->roomId)) $this->roomId = $request->trimValueOf(M3_REQUEST_PARAM_ROOM_ID_SHORT);		// 略式ルームID
		
		// ルームIDが空のときはデフォルトのHTMLを表示
		if (empty($this->roomId)){
			$this->tmpl->addVar("_widget", "top_html", self::$_paramObj->topHtml);// トップ表示用HTML
			
			// タブを非表示にする
			$this->tmpl->setAttribute('tab_area', 'visibility', 'hidden');
			return;
		}
		
		// 公開可能なルームかどうかチェック
		$ret = $this->_localDb->getRoomById($this->roomId, $row);
		if (!$ret || ($ret && !$row['ur_visible'])){
			$this->SetMsg(self::MSG_APP_ERR, "該当するデータがありません");
			
			// タブを非表示にする
			$this->tmpl->setAttribute('tab_area', 'visibility', 'hidden');
			return;
		}

		// ルームIDに対応したコンテンツをすべて取得
		$this->contentArray = $this->getAllContent($this->roomId, $this->langId);

		// タブの作成
		$this->isTabCreated = false;
		$groupId = $row['ur_group_id'];
		$this->_localDb->getAllVisibleTabs($this->langId, $groupId, array($this, 'itemsLoop'));
		if (!$this->isTabCreated){
			$this->cancelParse();// テンプレート変換処理中断
			return;
		}

		// ビューカウントを更新
		if (!$this->isSystemManageUser){		// システム運用者以上の場合はカウントしない
			$this->gInstance->getAnalyzeManager()->updateContentViewCount(self::CONTENT_TYPE, 0/*コンテンツIDで指定*/, $this->currentDay, $this->currentHour, $this->roomId);
		}
		
		// タブの表示制御
		$useTab = 1;		// タブを使用するかどうか
		if (!is_null(self::$_paramObj->useTab)) $useTab = self::$_paramObj->useTab;
		if (empty($useTab)){		// タブが非表示のとき
			$this->tmpl->setAttribute('create_tab', 'visibility', 'hidden');
			$this->tmpl->setAttribute('tablist_area', 'visibility', 'hidden');
		}

		// コンテンツ編集可能ユーザの場合は編集用ボタンを表示
		if (!empty(self::$_canEditContent)){
			// 編集用画面へのURL作成
			$urlparam  = M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET . '&';
			$urlparam .= M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId() .'&';
			$urlparam .= 'openby=simple&task=' . self::TASK_CONTENT . '&' . M3_REQUEST_PARAM_ROOM_ID . '=' . $this->roomId;
			$editUrl = $this->gEnv->getDefaultUrl() . '?' . $urlparam;
			
			// 設定画面表示用のスクリプトを埋め込む
			$this->tmpl->setAttribute('admin_script', 'visibility', 'visible');
			$this->tmpl->addVar("admin_script", "edit_url", $this->getUrl($editUrl));
			
			// 編集ボタンを作成
			$iconUrl = $this->gEnv->getRootUrl() . self::EDIT_ICON_FILE;		// 編集アイコン
			$iconTitle = '編集';
			$editImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
			$buttonList = '<span style="line-height:0;"><a href="#" onclick="editRoom();">' . $editImg . '</a></span><br />';
			$buttonList = '<div style="text-align:right;position:absolute;top:10px;z-index:10;width:100%;">' . $buttonList . '</div>';
			$this->tmpl->addVar("tab_area", "button_list", $buttonList);
		}
	}
	/**
	 * タブ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$row = array(
			'name' => $this->convertToDispString($fetchedRow['ub_name']),		// タブタイトル
			'href' => '#' . $fetchedRow['ub_id']		// タブ参照先
		);
		$this->tmpl->addVars('tablist', $row);
		$this->tmpl->parseTemplate('tablist', 'a');
		
		// タブ内容を作成
		$content = $fetchedRow['ub_template_html'];
		
		// コンテンツ項目IDを取得
		$useItemArray = array();
		$useItem = $fetchedRow['ub_use_item_id'];
		if (!empty($useItem)){
			$useItemArray = explode(',', $useItem);
		}

		// タブにコンテンツデータを埋め込む
		for ($i = 0; $i < count($useItemArray); $i++){
			$itemId = $useItemArray[$i];
			$keyTag = M3_TAG_START . M3_TAG_MACRO_ITEM_KEY . $itemId . M3_TAG_END;// 埋め込みタグ

			$contentInfo = $this->contentArray[$itemId];
			if (isset($contentInfo)){	
				$contentData = $contentInfo['uc_data'];
				
				// テキスト、数値のときは文字エスケープ処理
				if ($contentInfo['ui_type'] != 0){
					$contentData = $this->convertToDispString($contentData);
				}
			} else {
				$contentData = '';
			}
			// 埋め込みタグ変換
			$content = str_replace($keyTag, $contentData, $content);
		}

		$row = array(
			'panel_id' => $fetchedRow['ub_id'],		// タブID
			'content' => $content		// タブ内容
		);
		$this->tmpl->addVars('panellist', $row);
		$this->tmpl->parseTemplate('panellist', 'a');
		
		// タブ情報が取得できた
		$this->isTabCreated = true;
		return true;
	}
	/**
	 * すべてのコンテンツ項目を取得
	 *
	 * @return array			コンテンツ項目IDをキーにしたコンテンツ項目レコードの連想配列
	 */
	function getAllContent($roomId, $langId)
	{
		$destArray = array();
		
		$ret = $this->_localDb->getAllContentsByRoomId($roomId, $langId, $rows);
		if ($ret){
			$count = count($rows);
			for ($i = 0; $i < $count; $i++){
				$key = $rows[$i]['uc_id'];
				$destArray[$key] = $rows[$i];
			}
		}
		return $destArray;
	}
}
?>
