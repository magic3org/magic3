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
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/blog_analyticsDb.php');

class admin_blog_mainAnalyticsWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $calcTypeArray;		// 集計タイプ
	private $termTypeArray;		// 期間タイプ
	private $calcType;			// 選択中の集計タイプ
	const TERM_TYPE_ALL = '_all';				// 全データ表示選択

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new blog_analyticsDb();
		
		// 横軸タイプ
		$this->calcTypeArray = array(
										array(	'name' => '日単位',		'value' => 'day'),
										array(	'name' => '時間単位',	'value' => 'hour'),
										array(	'name' => '月単位',		'value' => 'month'),
										array(	'name' => '週単位',		'value' => 'week')
									);

		// 期間タイプ
		$this->termTypeArray = array(	array(	'name' => '1ヶ月',	'value' => 'month'),					// 日、月、時間の場合は30日。週の場合は4週間。
										array(	'name' => '3ヶ月',	'value' => '3month'),
										array(	'name' => '6ヶ月',	'value' => '6month'),
										array(	'name' => '1年',	'value' => '1year'),
										array(	'name' => 'すべて',	'value' => self::TERM_TYPE_ALL));
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
		return 'admin_analytics.tmpl.html';
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
		// 集計タイプメニュー作成
		$this->createCalcTypeMenu();
	}
	/**
	 * 集計タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createCalcTypeMenu()
	{
		for ($i = 0; $i < count($this->calcTypeArray); $i++){
			$value = $this->calcTypeArray[$i]['value'];
			$name = $this->calcTypeArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->calcType) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 集計タイプID
				'name'     => $name,			// 集計タイプ名
				'selected' => $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('item_calc_type_list', $row);
			$this->tmpl->parseTemplate('item_calc_type_list', 'a');
		}
	}
}
?>
