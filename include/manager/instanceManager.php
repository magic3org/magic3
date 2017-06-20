<?php
/**
 * 共通インスタンス管理マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/db/systemDb.php');		// システムDBアクセスクラス
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');
/**
 * 共通インスタンス管理クラス
 *
 * 共通で使用するインスタンスオブジェクトの生成、破棄、取得を管理する
 */
class InstanceManager extends Core
{
    private $systemDb;			// システムDBオブジェクト
	private $userInfo;			// ユーザ情報オブジェクト
	private $addonDir;			// 追加クラスインストールディレクトリ
	private $addonArray;		// ロード済みの追加クラス
	private $addonInfoArray;	// アドオンクラス情報
	const USER_AGENT_SCRIPT = '/Net_UserAgent_Mobile-1.0.0/Net/UserAgent/Mobile.php';		// ユーザエージェント解析用スクリプト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->systemDb = new SystemDb();
		
		// 変数初期化
		//$this->addonArray = array();
	}
	/**
	 * システムDBオブジェクトを取得
	 */
	public function getSytemDbObject()
	{
		return $this->systemDb;
	}
	/**
	 * ユーザ情報設定
	 *
	 * @param UseInfo	$obj		ユーザ情報オブジェクト
	 */
	public function setUserInfo($obj)
	{
		$this->userInfo = $obj;
	}
	/**
	 * ユーザ情報取得
	 *
	 * @return UserInfo		ユーザ情報オブジェクト
	 */
	public function getUserInfo()
	{
		return $this->userInfo;
	}
	/**
	 * 携帯判定オブジェクト取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getMobileAgent()
	{
		global $gEnvManager;
		static $mobileAgent;// 携帯判定用オブジェクト
		
		if (!isset($mobileAgent)){
			require_once($gEnvManager->getLibPath() . self::USER_AGENT_SCRIPT);
			$mobileAgent = Net_UserAgent_Mobile::singleton();
		}
		return $mobileAgent;
	}
	/**
	 * オブジェクト取得
	 *
	 * @param string	$id			オブジェクト識別ID
	 * @return object				取得したオブジェクト
	 */
	public function getObject($id)
	{
		global $gEnvManager;
				
		// 追加クラス情報がないときは、追加クラス情報をロード
		if (!isset($this->addonArray)) $this->loadAddonClassInfo();

		$addonObj = $this->addonArray[$id];
		if (isset($addonObj) && is_string($addonObj)){		// クラス名のとき
			$loadClass = $addonObj;
			
			// クラスファイルパス
			$loadClassFile = $gEnvManager->getAddonsPath() . '/' . $id . '/' . $loadClass . '.php';
			
			// ファイル読み込み
			require_once($loadClassFile);
		
			// クラスオブジェクト格納
			$addonObj = $this->addonArray[$id] = new $loadClass();
		}
		return $addonObj;
	}
	/**
	 * アドオンオブジェクト取得
	 *
	 * @param string	$id			オブジェクト識別ID
	 * @return object				取得したオブジェクト
	 */
	public function getAddon($id)
	{
		return $this->getObject($id);
	}
	/**
	 * アドオンイベントフック処理を実行
	 *
	 * @param array			第1引数はイベントフックタイプ。それ以降はそのままイベントフックメソッドへ渡す。
	 * @return				なし
	 */
	public function callAddonEventHook()
	{
		$args =	func_get_args();
		$hookType = $args[0];		// イベントフックタイプ
		array_shift($args);
		
		// 追加クラス情報がないときは、追加クラス情報をロード
		if (!isset($this->addonArray)) $this->loadAddonClassInfo();
		
		// イベントフックが使用可能なアドオンをロードし、イベントフックを実行
		switch ($hookType){
			case M3_EVENT_HOOK_TYPE_OPELOG:		// 運用ログタイプ
				for ($i = 0; $i < count($this->addonInfoArray); $i++){
					$addonInfo = $this->addonInfoArray[$i];
					if ($addonInfo['ao_opelog_hook']){
						$addon = $this->getAddon($addonInfo['ao_id']);
						if (isset($addon)){
							if (method_exists($addon, 'getEventHook')){
								$method = $addon->getEventHook($hookType);
								if (is_callable($method)) call_user_func_array($method, $args);
							}
						}
					}
				}
				break;
		}
	}
	/**
	 * 追加クラス情報を取得
	 */
	public function loadAddonClassInfo()
	{
		global $gEnvManager;

		// 追加クラス情報初期化
		$this->addonArray = array();
		
		// DB接続不可のときは終了
		if (!$gEnvManager->canUseDb()) return;
		
		// 追加クラス格納ディレクトリを取得
		$this->addonDir = $gEnvManager->getAddonsPath();
		
		$ret = $this->systemDb->getAllAddons($rows);
		if ($ret){
			$this->addonInfoArray = array();// アドオンクラス情報
			for ($i = 0; $i < count($rows); $i++){
				$row = $rows[$i];
				
				// 追加クラスID
				$addonId = $row['ao_id'];
				// クラス名
				$loadClass = $row['ao_class_name'];
				// クラスファイルパス
				$loadClassFile = $this->addonDir . '/' . $addonId . '/' . $loadClass . '.php';
		
				// 追加クラスファイルがあるときは追加クラス情報を格納
				if (file_exists($loadClassFile)){
					$this->addonArray[$addonId] = $loadClass;
					
					// アドオンクラス情報追加
					$this->addonInfoArray[] = $row;
				} else {
					echo 'addon load error: id=' . $addonId;
				}
			}
		}
	}
	/**
	 * DB制御マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getDbManager()
	{
		static $dbManager;// DB制御マネージャーオブジェクト
		
		if (!isset($dbManager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/dbManager.php');
			$dbManager 		= new DbManager();
		}
		return $dbManager;
	}
	/**
	 * キャッシュ制御マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	/*static public function getCacheManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/cacheManager.php');
			$manager 		= new CacheManager();
		}
		return $manager;
	}*/
	/**
	 * Ajax操作マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getAjaxManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/ajaxManager.php');
			$manager 		= new AjaxManager();
		}
		return $manager;
	}
	/**
	 * 設定ファイル操作マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	/*static public function getConfigManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/configManager.php');
			$manager 		= new ConfigManager();
		}
		return $manager;
	}*/
	/**
	 * コマンド付きパラメータ管理マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getCmdParamManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/cmdParamManager.php');
			$manager 		= new CmdParamManager();
		}
		return $manager;
	}
	/**
	 * ヘルプマネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getHelpManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/helpManager.php');
			$manager 		= new HelpManager();
		}
		return $manager;
	}
	/**
	 * ファイル管理マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getFileManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/fileManager.php');
			$manager 		= new FileManager();
		}
		return $manager;
	}
	/**
	 * デザインマネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	/*static public function getDesignManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/designManager.php');
			$manager 		= new DesignManager();
		}
		return $manager;
	}*/
	/**
	 * 画面制御マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	/*static public function getDispManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/dispManager.php');
			$manager 		= new DispManager();
		}
		return $manager;
	}*/
	/**
	 * アクセス解析マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getAnalyzeManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/analyzeManager.php');
			$manager 		= new AnalyzeManager();
		}
		return $manager;
	}
	/**
	 * メールマネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getMailManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/mailManager.php');
			$manager 		= new MailManager();
		}
		return $manager;
	}
	/**
	 * メッセージ管理マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getMessageManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/messageManager.php');
			$manager 		= new MessageManager();
		}
		return $manager;
	}
	/**
	 * 各種テキスト変換マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getTextConvManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/textConvManager.php');
			$manager 		= new TextConvManager();
		}
		return $manager;
	}
	/**
	 * サーバ接続マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getConnectManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/connectManager.php');
			$manager 		= new ConnectManager();
		}
		return $manager;
	}
	/**
	 * 画像マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getImageManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/imageManager.php');
			$manager 		= new ImageManager();
		}
		return $manager;
	}
	/**
	 * インストールマネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getInstallManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/installManager.php');
			$manager 		= new InstallManager();
		}
		return $manager;
	}
	/**
	 * ユーザ環境マネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getUserEnvManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/userEnvManager.php');
			$manager 		= new UserEnvManager();
		}
		return $manager;
	}
	/**
	 * メニューマネージャー取得
	 *
	 * @return object			取得したオブジェクト
	 */
	static public function getMenuManager()
	{
		static $manager;// マネージャーオブジェクト
		
		if (!isset($manager)){
			require_once(M3_SYSTEM_INCLUDE_PATH . '/manager/menuManager.php');
			$manager 		= new MenuManager();
		}
		return $manager;
	}
}
?>
