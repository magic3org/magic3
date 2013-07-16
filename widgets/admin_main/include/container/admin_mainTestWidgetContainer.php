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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_mainTestWidgetContainer.php 5881 2013-03-30 12:37:14Z fishbone $
 * @link       http://www.magic3.org
 */
//require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/admin_mainBaseWidgetContainer.php');
require_once($gEnvManager->getLibPath() .	'/tcpdf/config/lang/jpn.php');
require_once($gEnvManager->getLibPath() .	'/tcpdf/tcpdf.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_mainDb.php');

class admin_mainTestWidgetContainer extends admin_mainBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DB接続オブジェクト作成
		$this->db = new admin_mainDb();
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
		return 'test.tmpl.html';
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
		$limit = ini_get('upload_max_filesize') > ini_get('post_max_size') ? ini_get('post_max_size') : ini_get('upload_max_filesize');
echo $limit.' ';
		$limit = $limit < ini_get('memory_limit') ? ini_get('memory_limit') : $limit;
		echo $limit.' ';
		
/*
		// 契約番号(8桁) オンライン登録時に発行された契約番号を入力してください。
		$contract_code = "48346400";

		// 注文番号(注文毎にユニークな番号を割り当てます。ここでは仮に乱数を使用しています。)
		$order_number = rand(0,99999999);

		// 決済区分 (使用したい決済方法を指定してください。登録時に申し込まれていない決済方法は指定できません。)
//		$st_code = '10100-0000-00000';   // 指定方法はCGI設定マニュアルの「決済区分について」を参照してください。
		$st_code = '10000-0000-00000';		// クレジットカード決済のみ
//		$st_code = '00100-0000-00000';		// コンビニ決済のみ
//		$st_code = '10100-0000-00000';		// クレジットカード決済とコンビニ決済
		

		// 課金区分 (1:一回のみ 2～10:月次課金)
		// 月次課金について契約がない場合は利用できません。また、月次課金を設定した場合決済区分はクレジットカード決済のみとなります。
		$mission_code = 1;

		// 処理区分 (1:初回課金 2:登録済み課金 3:登録のみ 4:登録変更 8:月次課金解除 9:退会)
		// 月次課金をご利用にならない場合は1:初回課金をご利用ください。
		// 各処理区分のご利用に関してはCGI設定マニュアルの「処理区分について」を参照してください。
		$process_code = 1;

		// 追加情報 1,2  (入力は必須ではありません)
		$memo1 = "試験用オーダー情報";
		$memo2 = "";

		// 商品コード (商品毎に識別コードを指定してください。ここでは仮に固定の値を指定しています。)
		$item_code = "abc12345";

		// 商品名、価格
		$item_name = "12345";
		$item_price = 54321;

		$user_id = 'testuser';            // ユーザーID
		$user_name = 'テスト太郎';        // ユーザー氏名
		$user_mail_add = 'test@example.com';// メールアドレス

		$url = "http://beta.epsilon.jp/cgi-bin/order/receive_order3.cgi";
		$postParam = array();
		*/
/*		$postParam['contract_code'] = $contract_code;
		$postParam['user_id'] = $user_id;
		$postParam['user_name'] = mb_convert_encoding($user_name, "EUC-JP", "auto");
		$postParam['user_mail_add'] = $user_mail_add;
		$postParam['item_code'] = $item_code;
		$postParam['item_name'] = mb_convert_encoding($item_name, "EUC-JP", "auto");
		$postParam['order_number'] = $order_number;
		$postParam['st_code'] = $st_code;

		$postParam['mission_code'] = $mission_code;
		$postParam['item_price'] = $item_price;
		$postParam['process_code'] = $process_code;
		$postParam['memo1'] = $memo1;
		$postParam['memo2'] = $memo2;
		$postParam['xml'] = '1';*/
/*		$postParam['contract_code'] = $contract_code;
		$postParam['user_id'] = $user_id;
		$postParam['user_name'] = $user_name;
		$postParam['user_mail_add'] = $user_mail_add;
		$postParam['item_code'] = $item_code;
		$postParam['item_name'] = $item_name;
		$postParam['order_number'] = $order_number;
		$postParam['st_code'] = $st_code;
		$postParam['mission_code'] = $mission_code;
		$postParam['item_price'] = $item_price;
		$postParam['process_code'] = $process_code;
		$postParam['memo1'] = $memo1;
		$postParam['memo2'] = $memo2;
		$postParam['xml'] = '1';
  
		//$this->postData($url, $postParam, $resultArray);*/
	}
}
?>
