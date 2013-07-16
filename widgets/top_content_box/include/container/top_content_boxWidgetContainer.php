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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: top_content_boxWidgetContainer.php 2446 2009-10-22 05:00:48Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/top_content_boxDb.php');

class top_content_boxWidgetContainer extends BaseWidgetContainer
{
	private $db;
	private $itemCount;					// リスト項目数
	private $isExistsList;				// リスト項目が存在するかどうか
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const CONTENT_TYPE = 'ct';		// 参照数カウント用
	const TARGET_WIDGET = 'default_content';		// 呼び出しウィジェットID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new top_content_boxDb();
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
		$now = date("Y/m/d H:i:s");	// 現在日時
		$this->itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->itemCount	= $paramObj->itemCount;
		}
			
		// ログインユーザでないときは、ユーザ制限のない項目だけ表示
		$all = false;
		if ($this->gEnv->isCurrentUserLogined()) $all = true;
		
		// 一覧を作成
		$this->db->getContentList(self::CONTENT_TYPE, $all, $now, array($this, 'itemsLoop'));
				
		// 画面にデータを埋め込む
		if ($this->isExistsList) $this->tmpl->setAttribute('itemlist', 'visibility', 'visible');
	}
	/**
	 * 取得したメニュー項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		// 表示項目数に達したときは終了
		if ($index >= $this->itemCount) return false;
		
		$serial = $fetchedRow['vc_serial'];
		$totalViewCount = $fetchedRow['total'];
		$name = $fetchedRow['cn_name'];

		// リンク先の作成
		$linkUrl = $this->createCmdUrlToWidget(self::TARGET_WIDGET, 'contentid=' . $fetchedRow['cn_id']);// コンテンツ表示ウィジェットへのリンク

		if (!empty($name)){
			$row = array(
				'total' => $totalViewCount,		// 閲覧数
				'link_url' => $this->convertUrlToHtmlEntity($this->getUrl($linkUrl, true)),		// リンク
				'name' => $this->convertToDispString($name)			// タイトル
			);
			$this->tmpl->addVars('itemlist', $row);
			$this->tmpl->parseTemplate('itemlist', 'a');
		
			$this->isExistsList = true;		// リスト項目が存在するかどうか
		}
		return true;
	}
}
?>
