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
 * @copyright  Copyright 2006-2020 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_messageDb.php');

class admin_messageWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialArray = array();		// ログシリアル番号
	const DEFAULT_LOG_LEVEL = -1;		// デフォルトのログレベル
	const DEFAULT_LIST_COUNT = 5;			// 最大メッセージ表示数
	const INFO_ICON_FILE = '/images/system/info16.png';			// 情報アイコン
	const NOTICE_ICON_FILE = '/images/system/notice16.png';		// 注意アイコン
	const ERROR_ICON_FILE = '/images/system/error16.png';		// エラーアイコン
	const ACTION_ICON_FILE = '/images/system/action16.png';		// 操作要求アイコン
	const GUIDE_ICON_FILE = '/images/system/guide16.png';		// ガイダンスアイコン
	const ICON_SIZE = 16;		// アイコンのサイズ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_messageDb();
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
		return 'index.tmpl.html';
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
		$act = $request->trimValueOf('act');
		$serialNo = $request->trimValueOf('serial');		// 選択項目のシリアル番号
		$checked = $request->trimValueOf('checked');		// 確認済みかどうか
		if ($act == 'update_message'){			// メッセージの確認状況の更新
			$this->db->updateOpeLogChecked($serialNo, $checked);
		}
		$this->createList($request);
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$logLevel = self::DEFAULT_LOG_LEVEL;		// ログ表示レベル
		$listCount = self::DEFAULT_LIST_COUNT;		// 取得数

		// 表示条件
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){		// 既存データなしのとき
			$listCount = $paramObj->listCount;		// 取得数
			if (!isset($listCount)) $listCount = self::DEFAULT_LIST_COUNT;		// 取得数
		}

		// 運用ログを取得
		$this->db->getOpeLogList($logLevel, 1/*未確認*/, $listCount, 1/*ページ番号*/, array($this, 'logListLoop'));
		if (count($this->serialArray) == 0) $this->tmpl->setAttribute('loglist', 'visibility', 'hidden');		// ログがないときは非表示
	}
	/**
	 * 運用ログ一覧取得したデータをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function logListLoop($index, $fetchedRow, $param)
	{
		$serial = $fetchedRow['ol_serial'];
		
		// メッセージレベルの設定
		$iconUrl = '';
		$title = '';
		$alertType = '';
		switch ($fetchedRow['ot_level']){
			case -1:		// ガイダンス
				$iconUrl = $this->gEnv->getRootUrl() . self::GUIDE_ICON_FILE;
				$title = 'ガイダンス';
				$alertType = 'alert-info';			// アラートタイプ
				break;			
			case 0:		// 情報
				$iconUrl = $this->gEnv->getRootUrl() . self::INFO_ICON_FILE;
				$title = '情報';
				$alertType = 'alert-info';			// アラートタイプ
				break;
			case 1:		// 操作要求アイコン
				$iconUrl = $this->gEnv->getRootUrl() . self::ACTION_ICON_FILE;
				$title = '要操作';
				$alertType = 'alert-warning';			// アラートタイプ
				break;
			case 2:		// 注意
				$iconUrl = $this->gEnv->getRootUrl() . self::NOTICE_ICON_FILE;
				$title = '注意';
				$alertType = 'alert-warning';			// アラートタイプ
				break;
			case 10:	// 要確認
				$iconUrl = $this->gEnv->getRootUrl() . self::ERROR_ICON_FILE;
				$title = '要確認';
				$alertType = 'alert-danger';			// アラートタイプ
				break;
			default:
				break;
		}
		$iconTitle = $fetchedRow['ot_name'];
		$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" width="' . self::ICON_SIZE . '" height="' . self::ICON_SIZE . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		$accessLog = '';
		if (!empty($fetchedRow['ol_access_log_serial'])) $accessLog = $this->convertToDispString($fetchedRow['ol_access_log_serial']);
		
		// 操作画面リンク
		if (!empty($fetchedRow['ol_link'])){
			$iconTag = $this->gDesign->createAdminPageLink($iconTag, $fetchedRow['ol_link']);
			
			// メッセージのリンク先
			$linkTag = $this->gDesign->createAdminPageLink('<i class="glyphicon glyphicon-new-window"></i>', $fetchedRow['ol_link']);
		/*
			$linkUrl = $this->gEnv->getDefaultAdminUrl() . '?' . $fetchedRow['ol_link'];
			$iconTag = '<a href="'. $this->getUrl($linkUrl) .'">' . $iconTag . '</a>';
			
			// メッセージのリンク先
			$linkTag = '<a href="' . $linkUrl . '"><i class="glyphicon glyphicon-new-window"></i></a>';
			*/
		}

		$row = array(
			'serial'		=> $this->convertToDispString($serial),			// シリアル番号
			'alert_type'	=> $alertType,		// アラートタイプ
			'type'			=> $iconTag,			// メッセージタイプを示すアイコン
			'title'			=> $this->convertToDispString($title),		// メッセージ
			'message'		=> $this->convertToDispString($fetchedRow['ol_message']),		// メッセージ
			'link'			=> $linkTag
		);
		$this->tmpl->addVars('loglist', $row);
		$this->tmpl->parseTemplate('loglist', 'a');
		
		// 表示中のログシリアル番号を保存
		$this->serialArray[] = $serial;
		return true;
	}
}
?>
