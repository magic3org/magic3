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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/lang_changerDb.php');

class lang_changerWidgetContainer extends BaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $currentPageUrl;		// 現在のページ
	const DEFAULT_TITLE = '言語選択';		// デフォルトのウィジェットタイトル
	const ICON_PATH = '/images/system/flag/';		// 言語アイコンパス
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new lang_changerDb();
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
		//$this->currentPageUrl = $this->gEnv->createCurrentPageUrl();// 現在のページURL
		$this->currentPageUrl = $this->gEnv->getCurrentRequestUri();		// アクセスされたURL

		$acceptLang = $this->gSystem->getAcceptLanguage();

		// 言語一覧を取得
		if (count($acceptLang) > 0){
			$this->db->getLangs($acceptLang, array($this, 'langListLoop'));
		} else {
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
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
	 * 言語データをテンプレートに設定する
	 *
	 * @param int $index			行番号(0～)
	 * @param array $fetchedRow		フェッチ取得した行
	 * @param object $param			未使用
	 * @return bool					true=処理続行の場合、false=処理終了の場合
	 */
	function langListLoop($index, $fetchedRow, $param)
	{
		$langId = $fetchedRow['ln_id'];		// 言語ID
		$name = $fetchedRow['ln_name'];
		$title = $name . '(' . $fetchedRow['ln_name_en'] . ')';
		if (strEndsWith($this->currentPageUrl, '.php') || strEndsWith($this->currentPageUrl, '/')){		// クエリー文字列なしの場合
			$currentUrl = $this->currentPageUrl . '?' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $langId;
		} else {
			list($baseUrl, $query) = explode('?', $this->currentPageUrl);
			if (!empty($query)) parse_str($query, $paramArray);
			
			if (isset($paramArray[M3_REQUEST_PARAM_OPERATION_LANG])){
				$paramArray[M3_REQUEST_PARAM_OPERATION_LANG] = $langId;
				
				// クエリー文字列を作成
				$query = '';
				$keys = array_keys($paramArray);
				$keyCount = count($keys);
				for ($i = 0; $i < $keyCount; $i++){
					$key = $keys[$i];
					$value = $paramArray[$key];
					if ($i > 0) $query .= '&';
					$query .= $key . '=' . $value;
				}
				$currentUrl = $baseUrl . '?' . $query;
			} else {
				$currentUrl = $this->currentPageUrl . '&' . M3_REQUEST_PARAM_OPERATION_LANG . '=' . $langId;
			}
		}
		
		// 言語アイコン
		$iconTitle = $name;
		$iconUrl = $this->gEnv->getRootUrl() . self::ICON_PATH . $fetchedRow['ln_image_filename'];		// 画像ファイル
		$iconTag = '<img src="' . $this->getUrl($iconUrl) . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		$linkUrl = $this->convertUrlToHtmlEntity($this->getUrl($currentUrl, true));
		$imageTag = '<a href="' . $linkUrl . '">' . $iconTag . '</a>&nbsp;&nbsp;';
			
		$row = array(
			'image'		=> $imageTag,
			'name'		=> $this->convertToDispString($title),			// 言語名
			'link_url'	=> $linkUrl										// リンク先
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		return true;
	}
}
?>
