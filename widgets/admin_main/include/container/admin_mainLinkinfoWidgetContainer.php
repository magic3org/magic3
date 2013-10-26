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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_contentDb.php');

class admin_mainLinkinfoWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $langId;		// 言語
	private $db;	// DB接続オブジェクト
	private $contentDb;		// DB接続オブジェクト
	private $deviceType;		// デバイスタイプ(0=PC、1=携帯、2=スマートフォン)
	private $pageList = array();		// ページリスト
	private $contentList = array();		// コンテンツリスト
	private $contentType;		// 主要コンテンツタイプ
	const DEFAULT_CONTENT_COUNT = 300;		// コンテンツリスト取得数
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
		$this->contentDb = new admin_contentDb();
		
		$this->contentType = array(	M3_VIEW_TYPE_CONTENT,				// 汎用コンテンツ
								M3_VIEW_TYPE_PRODUCT,				// 製品
								M3_VIEW_TYPE_BBS,					// BBS
								M3_VIEW_TYPE_BLOG,				// ブログ
								M3_VIEW_TYPE_WIKI,				// wiki
								M3_VIEW_TYPE_USER,				// ユーザ作成コンテンツ
								M3_VIEW_TYPE_EVENT,				// イベント
								M3_VIEW_TYPE_PHOTO);				// フォトギャラリー
		$this->langId = $this->gEnv->getDefaultLanguage();
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
		return '';
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
		// 入力値を取得
		$accessPoint = $request->trimIntValueOf('accesspoint', '0');		// アクセスポイント(0=PC,1=携帯,2=スマートフォン)
		$accessPoint = intval($accessPoint);
		if (!in_array($accessPoint, array(0, 1, 2))) $accessPoint = 0;
		
		switch ($accessPoint){
			case 0:			// PC用
			default:
				$defaultPageId = $this->gEnv->getDefaultPageId();
				break;
			case 1:			// 携帯用
				$defaultPageId = $this->gEnv->getDefaultMobilePageId();
				break;
			case 2:			// スマートフォン用
				$defaultPageId = $this->gEnv->getDefaultSmartphonePageId();
				break;
		}

		// ##### Ajaxによるリンク情報取得 #####
		$act = $request->trimValueOf('act');
		if ($act == 'getpage'){		// ページ情報取得
			$this->db->getPageSubIdListWithWidget($defaultPageId, array($this, 'pageSubIdLoop'));

			// ページ選択メニューデータ
			$this->pageList = array_merge(array(array('', '-- 未選択 --')), $this->pageList);
			$this->pageList[] = array('_root', '[トップページ]');
			$this->gInstance->getAjaxManager()->addData('pagelist', $this->pageList);
		} else if ($act == 'getcontenttype'){		// コンテンツ種別取得
			$contentTypeList = $this->getContentTypeList($accessPoint);
			$this->gInstance->getAjaxManager()->addData('contenttype', $contentTypeList);
		} else if ($act == 'getcontentlist'){		// コンテンツ一覧取得
			$pageSubId = $request->trimValueOf('subid');			// ページサブID	####### 注意 処理ページが換わってしまうので、システムの「sub」パラメータとは重ならないようにする #######

			// ページ属性取得
			$ret = $this->db->getPageInfo($defaultPageId, $pageSubId, $row);
			if ($ret) $contentType = $row['pn_content_type'];
			
			// コンテンツタイプで一覧を取得
			$pageNo = 1;		// ページ番号
			switch ($contentType){
				case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
					// コンテンツタイプ
					switch ($accessPoint){
						case 0:			// PC用
						default:
							$contentType = '';
							break;
						case 1:			// 携帯用
							$contentType = 'mobile';
							break;
						case 2:			// スマートフォン用
							$contentType = 'smartphone';
							break;
					}
		
					$this->contentDb->getContentList($contentType, $this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, 0/*デフォルトソートキー*/, 0/*昇順*/, array($this, 'contentLoop'));
					break;
				case M3_VIEW_TYPE_PRODUCT:	// 製品
					break;
				case M3_VIEW_TYPE_BBS:	// BBS
					break;
				case M3_VIEW_TYPE_BLOG:	// ブログ
					$this->contentDb->getEntryList($this->langId, self::DEFAULT_CONTENT_COUNT, $pageNo, array($this, 'contentLoop'));
					break;
				case M3_VIEW_TYPE_WIKI:	// Wiki
					break;
				case M3_VIEW_TYPE_USER:	// ユーザ作成コンテンツ
					break;
				case M3_VIEW_TYPE_EVENT:	// イベント
					break;
				case M3_VIEW_TYPE_PHOTO:	// フォトギャラリー
					break;
			}
			
			if (!empty($this->contentList)) $this->contentList = array_merge(array(array('', '-- 未選択 --')), $this->contentList);
			$this->gInstance->getAjaxManager()->addData('contentlist', $this->contentList);
		} else if ($act == 'getaccesspoint'){		// アクセスポイント取得
			$accessPointList = array(	array('0', 'PC用「/」'),
										array('1', '携帯用「/m」'),
										array('2', 'スマートフォン用「/s」'));
			$this->gInstance->getAjaxManager()->addData('accesspoint', $accessPointList);
		}
	}
	/**
	 * ページサブIDを配列に格納
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function pageSubIdLoop($index, $fetchedRow, $param)
	{
		$contentType = $fetchedRow['pn_content_type'];
		$name = $fetchedRow['pg_id'];
		if (!empty($contentType)) $name .= ' [' . $contentType . ']';
		$name .= ' - ' . $fetchedRow['pg_name'];
		
		$this->pageList[] = array($fetchedRow['pg_id'], $name);
		return true;
	}
	/**
	 * コンテンツを配列に格納
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function contentLoop($index, $fetchedRow, $param)
	{
		$contentId = $fetchedRow['id'];
		$name = $fetchedRow['name'] . ' [' . $contentId . ']';		// コンテンツ名
		
		$this->contentList[] = array($contentId, $name);
		return true;
	}
	/**
	 * コンテンツ種別情報を取得
	 *
	 * @param int $accessPoint	アクセスポイント(0=PC、1=携帯、2=スマートフォン)
	 * @return array			コンテンツ種別情報
	 */
	function getContentTypeList($accessPoint)
	{
		$contentTypeArray = array(array(), array(), array());
		$pageIdArray = array($this->gEnv->getDefaultPageId(), $this->gEnv->getDefaultMobilePageId(), $this->gEnv->getDefaultSmartphonePageId());
		
		// 画面に配置しているウィジェットの主要コンテンツタイプを取得
		$ret = $this->db->getEditWidgetOnPage($this->langId, $pageIdArray, $this->contentType, $rows);
		if ($ret){
			$rowCount = count($rows);
			for ($i = 0; $i < $rowCount; $i++){
				$row = $rows[$i];
				switch ($row['pd_id']){		// アクセスポイントごとに分ける
					case $pageIdArray[0]:
					default:
						$index = 0;
						break;
					case $pageIdArray[1]:
						$index = 1;
						break;
					case $pageIdArray[2]:
						$index = 2;
						break;
				}
				$contentTypeArray[$index][] = $row;
			}
		}
		$contentTypeList = array();
		$contentType = $contentTypeArray[$accessPoint];
		for ($i = 0; $i < count($contentType); $i++){
			$contentTypeList[] = array($contentType[$i]['wd_type'], $contentType[$i]['ls_value']);
		}
		return $contentTypeList;
	}
}
?>
