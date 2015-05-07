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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/evententry_attachmentDb.php');

class evententry_attachmentWidgetContainer extends BaseWidgetContainer
{
	private $db;
	private $itemCount;					// リスト項目数
	private $isExistsList;				// リスト項目が存在するかどうか
	private $currentDate;				// 現在日付
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const DEFAULT_TITLE = 'イベント予約';			// デフォルトのウィジェットタイトル
	const DATE_FORMAT = 'Y年 n月 j日';		// 日付フォーマット
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new evententry_attachmentDb();
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
		$langId = $this->gEnv->getDefaultLanguage();
		
		$this->itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$this->itemCount	= $paramObj->itemCount;
		}
		
		// 一覧を作成
		$this->db->getUpdatePages($this->itemCount, 1/*1ページ目*/, array($this, 'itemsLoop'));
			
		// 一覧データがない場合は非表示
		if ($this->isExistsList){
			// 前の日付を表示
			$dateRow = array(
				'date'		=> $this->convertToDispString($this->currentDate)			// 日付
			);
			$this->tmpl->addVars('date_list', $dateRow);
			$this->tmpl->parseTemplate('date_list', 'a');
		} else {
			$this->tmpl->setAttribute('date_list', 'visibility', 'hidden');
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
	 * 取得したメニュー項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		$name = $fetchedRow['wc_id'];
		$date = date(self::DATE_FORMAT, strtotime($fetchedRow['wc_content_dt']));
		
		// リンク先の作成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . $fetchedRow['wc_id'], true);

		if (!isset($this->currentDate)){
			// 日付を更新
			$this->currentDate = $date;
			
			// バッファ更新
			$this->tmpl->clearTemplate('item_list');
		} else if ($date != $this->currentDate){
			// 前の日付を表示
			$dateRow = array(
				'date'		=> $this->convertToDispString($this->currentDate)			// 日付
			);
			$this->tmpl->addVars('date_list', $dateRow);
			$this->tmpl->parseTemplate('date_list', 'a');
			
			// 日付を更新
			$this->currentDate = $date;
			
			// バッファ更新
			$this->tmpl->clearTemplate('item_list');
		}
		$row = array(
			'link_url'	=> $this->convertUrlToHtmlEntity($linkUrl),		// リンク
			'name'		=> $this->convertToDispString($name)			// タイトル
		);
		$this->tmpl->addVars('item_list', $row);
		$this->tmpl->parseTemplate('item_list', 'a');
		
		$this->isExistsList = true;		// リスト項目が存在するかどうか
		return true;
	}
}
?>
