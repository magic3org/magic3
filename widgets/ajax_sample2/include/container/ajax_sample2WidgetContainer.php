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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: ajax_sample2WidgetContainer.php 3351 2010-07-08 02:12:05Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class ajax_sample2WidgetContainer extends BaseWidgetContainer
{
	private $itemArray;
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 項目データ
		$this->itemArray = array(
									array(	'no' => '1', 'title' => 'title1',	'user' => '',	
											'thumbnail' => '1.png', 'message' => 'png image'),
									array(	'no' => '2', 'title' => 'title2',	'user' => '',	
											'thumbnail' => '2.png', 'message' => 'png image'),
									array(	'no' => '3', 'title' => 'title3',	'user' => '',	
											'thumbnail' => '3.png', 'message' => 'png image'),
									array(	'no' => '4', 'title' => 'title4',	'user' => '',	
											'thumbnail' => '4.gif', 'message' => 'gif image'),
									array(	'no' => '5', 'title' => 'title5',	'user' => '',	
											'thumbnail' => '5.gif', 'message' => 'gif image'),
									array(	'no' => '6', 'title' => 'title6',	'user' => '',	
											'thumbnail' => '6.gif', 'message' => 'gif image'),
									array(	'no' => '7', 'title' => 'title7',	'user' => '',	
											'thumbnail' => '7.png', 'message' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
									array(	'no' => '8', 'title' => 'title8',	'user' => '',	
											'thumbnail' => '8.png', 'message' => '88888'),
									array(	'no' => '9', 'title' => 'title9',	'user' => '',	
											'thumbnail' => '9.png', 'message' => '99999999'),
									array(	'no' => '10', 'title' => 'title10',	'user' => '',	
											'thumbnail' => '10.png', 'message' => '0000000000000000000000'),
									array(	'no' => '11', 'title' => 'title11',	'user' => '',	
											'thumbnail' => '11.png', 'message' => 'あいうえお,かきくけこ'),
									array(	'no' => '12', 'title' => 'title12',	'user' => '',	
											'thumbnail' => '12.png', 'message' => 'あいうえお,かきくけこ、[1234567890]'),
									array(	'no' => '13', 'title' => 'title13',	'user' => '',	
											'thumbnail' => '13.png', 'message' => 'あいうえお,かきくけこ'),
									array(	'no' => '14', 'title' => 'title14',	'user' => '',	
											'thumbnail' => '14.png', 'message' => 'あいうえお,かきくけこあいうえお,かきくけこあいうえお,かきくけこあいうえお,かきくけこ'),
									array(	'no' => '15', 'title' => 'title15',	'user' => '',	
											'thumbnail' => '15.png', 'message' => 'あいうえお,かきくけこ'),
									array(	'no' => '16', 'title' => 'title16',	'user' => '',	
											'thumbnail' => '16.png', 'message' => 'あいうえお,かきくけこ'),
									array(	'no' => '17', 'title' => 'title17',	'user' => '',	
											'thumbnail' => '17.png', 'message' => 'あいうえお,かきくけこ'),
									array(	'no' => '18', 'title' => 'title18',	'user' => '',	
											'thumbnail' => '18.png', 'message' => 'あいうえお,かきくけこ'),
									array(	'no' => '19', 'title' => 'title19',	'user' => '',	
											'thumbnail' => '19.png', 'message' => 'あいうえお,かきくけこ'),
									array(	'no' => '20', 'title' => 'title20',	'user' => '',	
											'thumbnail' => '20.png', 'message' => 'あいうえお,かきくけこ')
								);
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
		if ($request->trimValueOf('act') == 'getdata'){	// Ajaxインターフェイスでの対応
			$param = 'ajax';
			return '';		// テンプレートは使用しない
		} else {
			return 'index.tmpl.html';
		}
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @return								なし
	 */
	function _assign($request, &$param)
	{
		if ($param == 'ajax'){	// Ajaxインターフェイスでの対応の場合
			$no = $request->trimValueOf('no');		// 開始番号
			if ($no == '') $no = 1;
			$count = $request->trimValueOf('count');		// 取得数
			
			$imagePath = $this->getUrl($this->gEnv->getCurrentWidgetRootUrl() . '/images');
			$sendData = array();
			if ($no + $count -1 <= count($this->itemArray)){
				for ($i = 0; $i < $count; $i++){
					$line = $this->itemArray[$i + $no -1];
					
					// 画像パスを修正
					$line['thumbnail'] = $imagePath . '/' . $line['thumbnail'];
					$sendData[] = $line;
				}
			}
			
			$this->gInstance->getAjaxManager()->addData('message', "サーバからの応答\n現在の日時は" . date(" Y年m月d日 H：i：s"));
			$this->gInstance->getAjaxManager()->addData('items', $sendData);
		} else {	// HTML表示の場合
			$this->tmpl->addVar("_widget", "IMG_URL", $this->getUrl($this->gEnv->getCurrentWidgetRootUrl() . '/images'));
		}
	}
}
?>
