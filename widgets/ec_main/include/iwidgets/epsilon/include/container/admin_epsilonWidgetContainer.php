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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_epsilonWidgetContainer.php 5437 2012-12-07 13:14:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentIWidgetContainerPath() . '/epsilonCommonDef.php');
require_once($gEnvManager->getContainerPath() . '/baseIWidgetContainer.php');

class admin_epsilonWidgetContainer extends BaseIWidgetContainer
{
	const CONFIRM_TASK = 'confirm';		// 確認画面
	const ERROR_TASK = 'error';		// エラー画面
	const ACT_CANCEL = 'cancel';	// キャンセル処理実行
	const ACT_COMPLETE = 'complete';	// 決済完了
	
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
	 * @param string         $act			実行処理
	 * @param object         $configObj		定義情報オブジェクト
	 * @param object         $optionObj		可変パラメータオブジェクト
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, $act, $configObj, $optionObj)
	{	
		return 'admin.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string         $act			実行処理
	 * @param object         $configObj		定義情報オブジェクト
	 * @param object         $optionObj		可変パラメータオブジェクト
	 * @param								なし
	 */
	function _assign($request, $act, $configObj, $optionObj)
	{
		// 基本情報を取得
		$id		= $optionObj->id;		// ユニークなID(配送方法ID)
		$init	= $optionObj->init;		// データ初期化を行うかどうか
		
		// 入力値を取得
		$connectMode	= $request->trimValueOf('iw_connect_mode');			// 接続モード
		$contractCode	= $request->trimValueOf('iw_contract_code');			// 契約番号
		$url			= $request->trimValueOf('iw_url');			// 本番サーバURL

		if ($act == 'update'){		// 設定更新のとき
			// 入力エラーチェック
			$this->checkInput($contractCode, '契約番号');
			if ($connectMode == epsilonCommonDef::PRODUCTION_MODE){		// 本番サーバが選択されているとき
				$this->checkUrl($url, '本番用URL');
			}
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$configObj->connectMode		= $connectMode;	// 接続モード
				$configObj->contractCode	= $contractCode;		// 契約番号
				$configObj->url				= $url;		// 本番サーバURL
				$ret = $this->updateConfigObj($configObj);
				if (!$ret) $this->setMsg(self::MSG_APP_ERR, 'インナーウィジェットデータの更新に失敗しました');
				// ***** 正常に終了した場合はメッセージを残さない *****
			}
		} else if ($act == 'content'){		// 画面表示のとき
			// 設定値を取得
			if (!empty($init)){			// 初期表示のとき
				if (empty($configObj)){		// 定義値がないとき(管理画面なので最初は定義値が存在しない)
					$connectMode	= 'test';	// 接続モード
					$contractCode	= '';		// 契約番号
					$url			= '';		// 本番サーバURL
				} else {
					$connectMode	= $configObj->connectMode;	// 接続モード
					$contractCode	= $configObj->contractCode;		// 契約番号
					$url			= $configObj->url;		// 本番サーバURL
				}
			}
			
			// インナーウィジェットのactを取得し、実行結果を表示
			$iwidgetAct = $request->trimValueOf('iw_act');
			if ($iwidgetAct == 'testconnect'){
				$ret = $this->testConnect($contractCode);
				if ($ret){		// サーバ接続成功
					$msg = '<b><font color="green">サーバ接続に成功しました</font></b>';	// テーブル作成正常
				} else {
					$msg = '<b><font color="red">サーバ接続に失敗しました</font></b>';			// テーブル作成エラー
				}
				$this->tmpl->addVar("_widget", "test_result", $msg);
			}
			
			// 画面にデータを埋め込む
			if ($connectMode == epsilonCommonDef::PRODUCTION_MODE){		// 本番モードのとき
				$this->tmpl->addVar('_widget', 'production_checked', 'checked');		// 本番環境
			} else {
				$this->tmpl->addVar('_widget', 'test_checked', 'checked');		// テスト環境
			}
			$this->tmpl->addVar("_widget", "test_url",	$this->convertToDispString(epsilonCommonDef::TEST_URL));
			$this->tmpl->addVar("_widget", "contract_code",	$contractCode);		// 契約コード
			$this->tmpl->addVar("_widget", "url",	$url);		// 本番サーバURL
			if (empty($contractCode)) $this->tmpl->addVar("_widget", "test_disabled",	"disabled");		// 「接続テスト」ボタン

			// イプシロン設定情報
			$subPageId = $this->gPage->getPageSubIdByWidget($this->gEnv->getDefaultPageId(), $this->gEnv->getCurrentWidgetId());
			$confirmUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . 
								M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $subPageId . '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::CONFIRM_TASK . '&' . 
								M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_COMPLETE, true);
			$gobackUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . 
								M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $subPageId . '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::CONFIRM_TASK . '&' . 
								M3_REQUEST_PARAM_OPERATION_ACT . '=' . self::ACT_CANCEL, true);
			$errorUrl = $this->getUrl($this->gEnv->getDefaultUrl() . '?' . 
								M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $subPageId . '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=' . self::ERROR_TASK, true);
			$this->tmpl->addVar("_widget", "host_ip",	$this->gRequest->trimServerValueOf('SERVER_ADDR'));		// 決済サーバに接続するサーバのIP
			$this->tmpl->addVar("_widget", "complete_url",	$this->convertToDispString($confirmUrl));
			$this->tmpl->addVar("_widget", "goback_url",	$this->convertToDispString($gobackUrl));
			$this->tmpl->addVar("_widget", "error_url",		$this->convertToDispString($errorUrl));
		}
	}
	/**
	 * 決済サーバとテスト通信
	 *
	 * @param string $contractCode	契約番号
	 * @return bool					true=成功、false=失敗
	 */
	public function testConnect($contractCode)
	{
		// 契約番号(8桁)
		$contract_code = $contractCode;

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

		$postParam = array();
		$postParam['contract_code'] = $contract_code;
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
  
		//$ret = $this->postData(epsilonCommonDef::TEST_URL, $postParam, $resultArray, true/*テストモード*/);
		$ret = epsilonCommonDef::postData(epsilonCommonDef::TEST_URL, $postParam, $resultArray, true/*テストモード*/);
		return $ret;
	}
}
?>
