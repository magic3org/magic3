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
 * @copyright  Copyright 2006-2014 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainConditionBaseWidgetContainer.php');

class admin_mainAwstatsWidgetContainer extends admin_mainConditionBaseWidgetContainer
{
	private $awstatsPath;		// Awstatsデータディレクトリパス
	private $awstatsUrl;		// AwstatsデータディレクトリURL
	
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
		return 'awstats.tmpl.html';
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
		$this->awstatsPath = $this->getAwstatsPath();		// Awstatsデータディレクトリパス
		$this->awstatsUrl = $this->getAwstatsUrl();		// AwstatsデータディレクトリURL
		
		// 集計ファイルを取得
		list($yearFileArray, $monthFileArray) = $this->getYearMonthFile($this->awstatsPath);
		
		// 年月データ一覧を作成
		$this->createYearMonthList($yearFileArray, $monthFileArray);
		
		// 値を埋め込む
		$this->tmpl->addVar("_widget", "last_index", $this->getAwstatsUrl());
	}
	/**
	 * 年月ごとの集計ファイルを取得(昇順)
	 *
	 * @param string $path		検索ディレクトリ
	 * @return array			月別ファイル一覧と年別ファイルの一覧の配列
	 */
	function getYearMonthFile($path)
	{
		$yearArray = array();
		$monthArray = array();
		
		$dir = dir($path);
		while (($file = $dir->read()) !== false){
			$filePath = $path . '/' . $file;
			// ディレクトリかどうかチェック
			if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)){
				$ret = preg_match("/^([0-9]{4})([0-9]{0,2})\.html$/", $file, $match);
				if ($ret){
					if (empty($match[2])){
						$yearArray[] = $match[0];
					} else {
						$monthArray[] = $match[0];
					}
				}

			}
		}
		$dir->close();
		
		sort($yearArray);
		sort($monthArray);
		return array($yearArray, $monthArray);
	}
	/**
	 * 年月一覧を作成
	 *
	 * @param array $yearFileArray			年データファイル名
	 * @param array $monthFileArray			月データファイル名
	 * @return なし						
	 */
	function createYearMonthList($yearFileArray, $monthFileArray)
	{
		if (count($monthFileArray) > 0){
			preg_match("/^([0-9]{4})([0-9]{0,2})\.html$/", $monthFileArray[0], $match);
			$startYear = intval($match[1]);
			$startMonth = intval($match[2]);
			
			preg_match("/^([0-9]{4})([0-9]{0,2})\.html$/", $monthFileArray[count($monthFileArray) -1], $match);
			$endYear = intval($match[1]);
			$endMonth = intval($match[2]);
		}
		
		for ($i = $endYear; $i >= $startYear; $i--){
			// 月データの出力
			$this->tmpl->clearTemplate('month_list');
			for ($j = 1; $j <= 12; $j++){
				// ファイルの存在チェック
				$filename = $i . sprintf('%02d', $j) . '.html';
				$monthDataFile = $this->awstatsPath . '/' . $filename;
				$monthTag = $j . '月';
				if (file_exists($monthDataFile)){
					$monthDataUrl = $this->awstatsUrl . '/' . $filename;
					$monthTag = '<a href="' . $monthDataUrl . '" target="_blank">' . $monthTag . '</a>';
				}
				$monthRow = array(
					'month'	=>	$monthTag										// 月
				);
				$this->tmpl->addVars('month_list', $monthRow);
				$this->tmpl->parseTemplate('month_list', 'a');
			}
			// ファイルの存在チェック
			$filename = $i . '.html';
			$yearDataFile = $this->awstatsPath . '/' . $filename;
			$yearTag = $i . '年';
			if (file_exists($yearDataFile)){
				$yearDataUrl = $this->awstatsUrl . '/' . $filename;		// AwstatsデータディレクトリURL
				$yearTag = '<a href="' . $yearDataUrl . '" target="_blank">' . $yearTag . '</a>';
			}
			$yearRow = array(
					'year'		=> 	$yearTag		// 年
			);
			$this->tmpl->addVars('year_list', $yearRow);
			$this->tmpl->parseTemplate('year_list', 'a');
		}
	}
}
?>
