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
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/blog_categoryDb.php');

class admin_blog_mainAnalyticsWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $serialNo;			// シリアル番号
	private $firstNo;			// 項目番号
	private $configType;		// 設定タイプ
	private $langId;			// 選択中の言語
	private $serialArray = array();		// 表示されている項目シリアル番号
	const DEFAULT_RES_TYPE = 0;	// デフォルトの設定タイプ(常設)
	const DEFAULT_CONFIG_ID = 0;	// デフォルトの設定ID
	const DEFAULT_LIST_COUNT = 20;			// 最大リスト表示数
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
//		$this->db = new blog_categoryDb();
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
		$task = $request->trimValueOf('task');
		if ($task == 'category_detail'){		// 詳細画面
			return 'admin_category_detail.tmpl.html';
		} else {
			return 'admin_category.tmpl.html';
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
		$task = $request->trimValueOf('task');
		if ($task == 'category_detail'){	// 詳細画面
			return $this->createDetail($request);
		} else {			// 一覧画面
			return $this->createList($request);
		}
	}
	/**
	 * 一覧画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createList($request)
	{
		$this->langId	= $this->gEnv->getCurrentLanguage();		// 表示言語を取得
		$act = $request->trimValueOf('act');
		
		if ($act == 'delete'){		// 項目削除の場合
			$listedItem = explode(',', $request->trimValueOf('seriallist'));
			$delItems = array();
			for ($i = 0; $i < count($listedItem); $i++){
				// 項目がチェックされているかを取得
				$itemName = 'item' . $i . '_selected';
				$itemValue = ($request->trimValueOf($itemName) == 'on') ? 1 : 0;
				
				if ($itemValue){		// チェック項目
					$delItems[] = $listedItem[$i];
				}
			}
			if (count($delItems) > 0){
				// カテゴリーが使用中かどうかチェック
				for ($i = 0; $i < count($delItems); $i++){
					// カテゴリーID取得
					$ret = $this->db->getCategoryBySerial($delItems[$i], $row);
					if ($ret){
						$ret = $this->db->isUsedCategory($row['bc_id']);		// カテゴリーIDを確認
						if ($ret){
							$this->setAppErrorMsg('使用中のカテゴリーは削除できません');
							break;
						}
					} else {
						$this->setAppErrorMsg('カテゴリー情報の取得に失敗しました');
						break;
					}
				}
				// エラーなしの場合は、データを削除
				if ($this->getMsgCount() == 0){
					$ret = $this->db->delCategoryBySerial($delItems);
					if ($ret){		// データ削除成功のとき
						$this->setGuidanceMsg('データを削除しました');
					} else {
						$this->setAppErrorMsg('データ削除に失敗しました');
					}
				}
			}
		}
		// #### カテゴリーリストを作成 ####
//		$this->db->getAllCategory(array($this, 'categoryListLoop'), $this->langId);// デフォルト言語で取得
		
		if (count($this->serialArray) > 0){
			$this->tmpl->addVar("_widget", "serial_list", implode($this->serialArray, ','));// 表示項目のシリアル番号を設定
		} else {
			$this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');// 項目がないときは、一覧を表示しない
		}
	}
}
?>
