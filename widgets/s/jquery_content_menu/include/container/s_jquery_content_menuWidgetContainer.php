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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: s_jquery_content_menuWidgetContainer.php 4562 2012-01-04 02:07:17Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/s_jquery_content_menuDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class s_jquery_content_menuWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 現在の言語
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'jQueryページ専用コンテンツメニュー';			// デフォルトのウィジェットタイトル
	const CONTENT_TYPE = 'smartphone';			// コンテンツタイプ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new s_jquery_content_menuDb();
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$name		= $targetObj->name;// 定義名
			$title		= $targetObj->title;			// リストタイトル
			$contentId	= $targetObj->contentId;		// コンテンツID
			$theme 		= $targetObj->theme;		// メニューのテーマ
			$insetList	= $targetObj->insetList;		// インセットリスト形式で表示するかどうか
			
			// 初期値設定
			$now = date("Y/m/d H:i:s");	// 現在日時
			$all = false;
			if ($this->gEnv->isCurrentUserLogined()) $all = true;// ログインユーザでないときは、ユーザ制限のない項目だけ表示
		
			// コンテンツへのリンク作成
			$contentIdArray = explode(',', $contentId);
			if (ValueCheck::isNumeric($contentIdArray)){		// すべて数値であるかチェック
				$this->db->getContentItems(self::CONTENT_TYPE, array($this, 'itemsLoop'), $contentIdArray, $this->langId, $all, $now);
			}
			
			// 表示データ埋め込み
			if (empty($title)){
				$this->tmpl->setAttribute('listtitle', 'visibility', 'hidden');
			} else {
				$this->tmpl->addVar("listtitle", "title",	$this->convertToDispString($title));
			}
			$listOption = '';
			if (!empty($insetList)){		// インセットリスト形式で表示するかどうか
				$listOption .= ' data-inset="true"';
			}
			if (!empty($theme)){
				$listOption .= ' data-theme="' . $theme . '"';
			}
			$this->tmpl->addVar("_widget", "list_option",	$listOption);
			$this->tmpl->addVar("_widget", "content",	$content);
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
		return self::DEFAULT_TITLE;
	}
	/**
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		// コンテンツへのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultSmartphoneUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $fetchedRow['cn_id']);
		
		$row = array(
			'name' => $this->convertToDispString($fetchedRow['cn_name']),
			'url' => $this->convertUrlToHtmlEntity($linkUrl)	// コンテンツへのリンク
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		return true;
	}
}
?>
