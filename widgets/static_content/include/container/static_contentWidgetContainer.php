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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/static_contentDb.php');

class static_contentWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 現在の言語
	private $isSystemManageUser;	// システム運用可能ユーザかどうか
	private $editIconPos;			// 編集アイコンの位置
	private $title;			// ウィジェットタイトル
	const DEFAULT_CONFIG_ID = 0;
	const CONTENT_TYPE = '';			// コンテンツタイプ
	const VIEW_CONTENT_TYPE = 'ct';		// 参照数カウント用
	const DEFAULT_TITLE = 'コンテンツ';			// デフォルトのウィジェットタイトル
	const ICON_SIZE = 16;		// アイコンのサイズ
	const EDIT_ICON_FILE = '/images/system/page_edit.png';		// 編集アイコン
	const NEW_ICON_FILE = '/images/system/page_add.png';		// 新規アイコン
	const EDIT_ICON_MIN_POS = 10;			// 編集アイコンの位置
	const EDIT_ICON_NEXT_POS = 20;			// 編集アイコンの位置
	const CONTENT_WIDGET_ID = 'default_content';			// コンテンツ編集ウィジェット
	const DEFAULT_READ_MORE = 'もっと読む';		// 「続きを読む」ボタンタイトル
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->editIconPos = self::EDIT_ICON_MIN_POS;			// 編集アイコンの位置
		
		// DBオブジェクト作成
		$this->db = new static_contentDb();
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
		return 'main.tmpl.html';
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
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->langId = $this->gEnv->getCurrentLanguage();
		$this->isSystemManageUser = $this->gEnv->isSystemManageUser();	// システム運用可能ユーザかどうか
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$showWidget = false;		// ウィジェットを表示するかどうか
		$buttonType = 0;			// 編集ボタンタイプ
		$contentId = 0;
		$showReadMore = 0;		// 「続きを読む」ボタンを表示
		$readMoreTitle	= self::DEFAULT_READ_MORE;		// 「続きを読む」ボタンタイトル
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$name = $targetObj->name;// 定義名
			$contentId	= $targetObj->contentId;		// コンテンツID
			$showReadMore = $targetObj->showReadMore;		// 「続きを読む」ボタンを表示
			if (!empty($targetObj->readMoreTitle)) $readMoreTitle	= $targetObj->readMoreTitle;		// 「続きを読む」ボタンタイトル
			
			// コンテンツを取得
			$ret = $this->db->getContentByContentId(self::CONTENT_TYPE, $contentId, $this->langId, $now, $row);
			if ($ret){
				$this->title = $row['cn_name'];			// ウィジェットタイトル
				
				$contentData = $row['cn_html'];
				if (!empty($showReadMore)){		//「続きを読む」ボタンを表示のとき
					$contentArray = explode(M3_TAG_START . M3_TAG_MACRO_CONTENT_BREAK . M3_TAG_END, $contentData, 2);
					$contentData = $contentArray[0];
					
					// 「続きを読む」ボタンを表示
					$contentUrl = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $contentId;
					$this->tmpl->setAttribute('show_read_more', 'visibility', 'visible');
					$this->tmpl->addVar("show_read_more", "read_more_title", $this->convertToDispString($readMoreTitle));
					$this->tmpl->addVar("show_read_more", "content_url", $this->getUrl($contentUrl));
				}
				
				$contentInfo = array();
				$contentInfo[M3_TAG_MACRO_CONTENT_BREAK] = '';		// コンテンツ置換キー(コンテンツ区切り)
				$contentInfo[M3_TAG_MACRO_CONTENT_TITLE] = $this->title;			// コンテンツ置換キー(タイトル)
				$contentInfo[M3_TAG_MACRO_CONTENT_UPDATE_DT] = $row['cn_create_dt'];		// コンテンツ置換キー(更新日時)
				$contentText = $this->convertM3ToHtml($contentData, true/*改行コーをbrタグに変換*/, $contentInfo);

				// コンテンツを設定
				$this->tmpl->addVar("_widget", "content", $contentText);
			
				// ログインユーザに表示が制限されている場合は非表示
				if ($this->gEnv->isCurrentUserLogined() || !$row['cn_user_limited']) $showWidget = true;

				$buttonType = 1;			// 編集ボタンタイプ(編集)
				//$contentId = $row['cn_id'];
			}
		}
		// ウィジェットの表示
		if ($showWidget){
			if ($this->isSystemManageUser){		// システム運用者の場合
				// 設定画面表示用のスクリプトを埋め込む
				$editUrl = $this->gEnv->getDefaultAdminUrl() . '?cmd=configwidget&openby=simple&widget=' . self::CONTENT_WIDGET_ID . '&task=content_detail';
				$this->tmpl->setAttribute('admin_script', 'visibility', 'visible');
				$this->tmpl->addVar("admin_script", "edit_url", $editUrl);
			
				// 編集ボタンを作成
				if ($buttonType == 1){		// 編集ボタン
					$buttonList = '';
					$iconUrl = $this->gEnv->getRootUrl() . self::EDIT_ICON_FILE;		// 編集アイコン
					$iconTitle = '編集';
					$editImg = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . 
								'" alt="' . $iconTitle . '" title="' . $iconTitle . '" style="border:none;" />';
					$buttonList = '<a href="javascript:void(0);" onclick="editContent(' . $contentId . ');">' . $editImg . '</a>';
					$buttonList = '<div style="text-align:right;position:absolute;top:' . $this->editIconPos . 'px;z-index:10;width:100%;">' . $buttonList . '</div>';
					$this->editIconPos += self::EDIT_ICON_NEXT_POS;			// 編集アイコンの位置を更新
				} else {		// 新規ボタン
				}
				
				// 編集ボタンを表示
				$this->tmpl->addVar("_widget", "button_list", $buttonList);
			} else {		// システム運用者以上の場合はカウントしない
				// ビューカウントを更新
				$currentDay = date("Y/m/d");		// 日
				$currentHour = (int)date("H");		// 時間
				$this->gInstance->getAnalyzeManager()->updateContentViewCount(self::VIEW_CONTENT_TYPE, $row['cn_serial'], $currentDay, $currentHour);
			}
		} else {
			// 出力抑止
			$this->cancelParse();
		}
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		$title = self::DEFAULT_TITLE;
		if (!empty($this->title)) $title = $this->title;
		return $title;
	}
}
?>
