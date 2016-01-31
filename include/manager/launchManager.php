<?php
/**
 * 起動制御マネージャー
 *
 * コンテナ(フレームコンテナ、ウィジェットコンテナ、ウィジェット内サブコンテナ)の起動を行う。
 * このマネージャーでユーザのアクセス制御は行わない。
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class LaunchManager extends Core
{
	private $loadPath;								// クラス検索ロード用のパス
	const DEFAULT_RSS_CLASS_PREFIX = 'rss_';		// RSS実行用クラスの先頭文字列
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		$this->loadPath = array();								// クラス検索ロード用のパス
	}

	/**
	 * プログラムを実行
	 *
	 * @param string $filepath		呼び出し元ファイルのフルパス。通常は「__FILE__」。OSによってパスの表現が違うので注意。
	 */
	function go($filepath = '')
	{
		global $gEnvManager;
		global $gRequestManager;

		// ルートから$filepathへの相対パスで「FrameContainer.php」の先頭につける
		// サフィックスを作成する
		// 例) 相対パスが「admin/index.php」のとき「admin_index」
		if ($filepath == ''){
			$basename = basename($_SERVER["PHP_SELF"], '.php');
		} else {
			// ルートまでのパスを削除
			$path = str_replace(M3_SYSTEM_ROOT_PATH, '', $filepath);
			$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
			$path = trim($path, '/');
			//$pathArray = split('/', $path);
			$pathArray = explode('/', $path);
			$basename = '';
			for ($i = 0; $i < count($pathArray); $i++){
				if ($i == 0){
					$basename .= $pathArray[$i];
				} else {
					//$basename .= ucfirst($pathArray[$i]);
					$basename .= ('_' . $pathArray[$i]);
				}
			}
			$basename = basename($basename, '.php');
		}
		
		// PC用URLかどうかを設定(管理画面はPC用URLとしない)
		$isPcSite = true;
		if (strStartsWith($basename, 'admin_')) $isPcSite = false;
		$gEnvManager->setIsPcSite($isPcSite);
		
		// サーバ接続かどうかを設定
		if ($basename == basename(M3_FILENAME_SERVER_CONNECTOR, '.php')) $gEnvManager->setIsServerConnector(true);
		
		// ページIDを設定
		$gEnvManager->setCurrentPageId($basename);

		// アクセスポイントを設定
		$gEnvManager->setAccessPath(str_replace('_', '/', $basename));
		
		// ファイル名から、コンテナクラスファイル取り込み
		require_once($gEnvManager->getContainerPath() . '/' . $basename . 'FrameContainer.php');
		
		// コンテナクラスを起動
		$class = $basename . 'FrameContainer';
		$mainContainer = new $class();
		$mainContainer->process($gRequestManager);
	}
	/**
	 * ウィジェットプログラムを実行
	 *
	 * @param string $filepath		呼び出し元ファイルのフルパス。通常は「__FILE__」。OSによってパスの表現が違うので注意。
	 */
	function goWidget($filepath)
	{
		global $gEnvManager;
		global $gRequestManager;
		static $pathArray = array();		// 呼び出し元を保存する

		// 実行コマンドを取得
		$cmd = $gRequestManager->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		
		$basename = basename($filepath, '.php');
		$widgetId = $gEnvManager->getCurrentWidgetId();// ウィジェットID
		
		// このメソッドにアクセスしたウィジェットのパスをみて、管理画面へのアクセスかどうかを判断
		$pathArray = explode(DIRECTORY_SEPARATOR, $filepath);
		$pathCount = count($pathArray);
		$accessAdmin = false;
		
		// ウィジェットの種別を設定
		$gEnvManager->setIsSubWidget(false);			// 通常ウィジェットで起動
		
		if ($gEnvManager->getIsMobileSite()){		// 携帯サイトへのアクセスのとき
			// 管理画面へのアクセスかどうかチェック
			if ($pathArray[$pathCount -2] == 'admin' && $pathArray[$pathCount -3] == $widgetId){
				$accessAdmin = true;
			}
			// 携帯用ウィジェットのウィジェットIDは、「m/xxxxxx」の形式
			// ウィジェットIDを変換
			$widgetId = str_replace('/', '_', $widgetId);
			
			// コンテナクラス名作成
			if ($cmd == M3_REQUEST_CMD_RSS){		// RSS配信のとき
				$containerClass = self::DEFAULT_RSS_CLASS_PREFIX . $widgetId . 'WidgetContainer';		// デフォルトで起動するコンテナクラス名「ウィジェットID + WidgetContainer」
				$containerPath = $gEnvManager->getCurrentWidgetContainerPath() . '/' . $containerClass . '.php';
			} else if ($accessAdmin){
				$containerClass = 'admin_' . $widgetId . 'WidgetContainer';		// デフォルトで起動するコンテナクラス名「admin_ + ウィジェットID + WidgetContainer」
			} else {
				$containerClass = $widgetId . 'WidgetContainer';		// デフォルトで起動するコンテナクラス名「ウィジェットID + WidgetContainer」
				$containerPath = $gEnvManager->getCurrentWidgetContainerPath() . '/' . $containerClass . '.php';
			}
			if (file_exists($containerPath)){
				require_once($containerPath);
			} else {
				echo 'file not found error: ' . $containerPath;
			}
			// コンテナクラスを起動
			$widgetContainer = new $containerClass();
			$gEnvManager->setCurrentWidgetObj($widgetContainer);				// 実行するウィジェットコンテナオブジェクトを登録
			$widgetContainer->process($gRequestManager);
			$gEnvManager->setCurrentWidgetObj(null);
		} else {			// PC用の画面からのアクセスまたは管理画面へのアクセス
			// インナーウィジェットのチェック
			$isIWidget = false;
			if ($pathArray[$pathCount -4] == 'iwidgets' && $pathArray[$pathCount -2] == 'admin'){		// インナーウィジェット管理者画面
				$isIWidget = true;
				$widgetId = $pathArray[$pathCount -3];		// インナーウィジェットID
				$accessAdmin = true;
			} else if ($pathArray[$pathCount -3] == 'iwidgets'){	// インナーウィジェット通常画面
				$isIWidget = true;
				$widgetId = $pathArray[$pathCount -2];// インナーウィジェットID
			} else if ($pathArray[$pathCount -3] == $widgetId && $pathArray[$pathCount -2] == 'admin'){		// PC用ウィジェット管理画面
				$accessAdmin = true;
			} else if ($pathArray[$pathCount -4] . '/' . $pathArray[$pathCount -3] == $widgetId && $pathArray[$pathCount -2] == 'admin'){		// 携帯用ウィジェット管理画面
				$accessAdmin = true;
			}
			// コンテナクラス名作成
			if ($cmd == M3_REQUEST_CMD_RSS){		// RSS配信のとき
				$containerClass = self::DEFAULT_RSS_CLASS_PREFIX . $widgetId . 'WidgetContainer';		// デフォルトで起動するコンテナクラス名「ウィジェットID + WidgetContainer」
			} else if ($accessAdmin){
				$containerClass = 'admin_' . $widgetId . 'WidgetContainer';		// デフォルトで起動するコンテナクラス名「admin_ + ウィジェットID + WidgetContainer」
			} else {
				$containerClass = $widgetId . 'WidgetContainer';		// デフォルトで起動するコンテナクラス名「ウィジェットID + WidgetContainer」
			}
			// コンテナクラス名修正
			$containerClass = str_replace('/', '_', $containerClass);
					
			// コンテナクラスが既にロードされているときはエラー
			// 同じウィジェットが2回以上実行される可能性があるので、ウィジェットIDが同じであればエラーとしない
			if (class_exists($containerClass)){
				// 既に起動済みのウィジェットかどうかチェック
				//if (in_array($filepath, $pathArray)){
					// 同じウィジェットの場合は起動
					$widgetContainer = new $containerClass();
					$gEnvManager->setCurrentWidgetObj($widgetContainer);				// 実行するウィジェットコンテナオブジェクトを登録
					$widgetContainer->process($gRequestManager);
					$gEnvManager->setCurrentWidgetObj(null);
	//			} else {
	//				// 同じウィジェットが起動されていないときは、クラス名のバッテイングでエラー
	//				echo 'class redefined error: ' . $containerClass;
	//			}
			} else {
				// ウィジェットのコンテナクラスファイルを読み込み
				if ($isIWidget){		// インナーウィジェットの場合
					if ($accessAdmin){
						$containerPath = dirname(dirname($filepath)) . '/include/container/' . $containerClass . '.php';
					} else {
						$containerPath = dirname($filepath) . '/include/container/' . $containerClass . '.php';
					}
				} else {
					$containerPath = $gEnvManager->getCurrentWidgetContainerPath() . '/' . $containerClass . '.php';
				}
				if (file_exists($containerPath)){
					require_once($containerPath);
				} else {
					echo 'file not found error: ' . $containerPath;
				}
				// コンテナクラスを起動
				$widgetContainer = new $containerClass();
				$gEnvManager->setCurrentWidgetObj($widgetContainer);				// 実行するウィジェットコンテナオブジェクトを登録
				$widgetContainer->process($gRequestManager);
				$gEnvManager->setCurrentWidgetObj(null);
			}
			// 呼び出し元ファイルパスの保存
			$pathArray[] = $filepath;
		}
	}
	/**
	 * ウィジェットプログラム(サブ)を実行
	 *
	 * @param string $task		タスク名
	 * @param bool $isAdmin		管理者機能(adminディレクトリ以下)かどうか
	 * @param string $defaultWidgetId	カレントウィジェットの実行クラスが取得できない場合のデフォルトウィジェットID
	 * @return なし
	 */
	function goSubWidget($task, $isAdmin = false, $defaultWidgetId = '')
	{
		global $gEnvManager;
		global $gRequestManager;
		
		// ウィジェットの種別を設定
		$gEnvManager->setIsSubWidget(true);			// サブウィジェットで起動
		
		// コンテナクラス名作成
		// フォーマット: [ウィジェットID][タスク名]WidgetContainer
		$widgetId = $gEnvManager->getCurrentWidgetId();// ウィジェットID
		$containerClass = '';
		if ($isAdmin) $containerClass .= 'admin_';
		$containerClass .= $widgetId . ucfirst($task) . 'WidgetContainer';

		// コンテナクラス名修正
		$containerClass = str_replace('/', '_', $containerClass);
		
		// コンテナクラスが既にロードされているときはエラー
		if (class_exists($containerClass)){
			echo 'class redefined error2: ' . $containerClass;
		} else {
			// コンテナクラスファイル取り込み
			$containerPath = $gEnvManager->getCurrentWidgetContainerPath() . '/' . $containerClass . '.php';	// カレントウィジェットのコンテナクラス
			if (file_exists($containerPath)){
				require_once($containerPath);
				
				// コンテナクラスを起動
				$widgetContainer = new $containerClass();
				$gEnvManager->setCurrentWidgetObj($widgetContainer);				// 実行するウィジェットコンテナオブジェクトを登録
				$widgetContainer->process($gRequestManager);
				$gEnvManager->setCurrentWidgetObj(null);
			} else if (!empty($this->loadPath)){		// クラス検索用パスが設定されているとき
				require_once($containerClass . '.php');
				
				// コンテナクラスを起動
				$widgetContainer = new $containerClass();
				$gEnvManager->setCurrentWidgetObj($widgetContainer);				// 実行するウィジェットコンテナオブジェクトを登録
				$widgetContainer->process($gRequestManager);
				$gEnvManager->setCurrentWidgetObj(null);
			} else {
				if (empty($defaultWidgetId)){
					echo 'file not found error: ' . $containerPath;
				} else {		// デフォルトのウィジェットIDが指定されている場合はデフォルトウィジェットIDで実行
					// コンテナクラス名作成
					$containerClass = '';
					if ($isAdmin) $containerClass .= 'admin_';
					$containerClass .= $defaultWidgetId . ucfirst($task) . 'WidgetContainer';

					// コンテナクラス名修正
					$containerClass = str_replace('/', '_', $containerClass);
					
					// コンテナクラスが既にロードされているときはエラー
					if (class_exists($containerClass)){
						echo 'class redefined error3: ' . $containerClass;
					} else {
						// コンテナクラスファイル取り込み
						$containerPath = $gEnvManager->getWidgetsPath() . '/' . $defaultWidgetId . '/include/container/' . $containerClass . '.php';
						if (file_exists($containerPath)){
							require_once($containerPath);
				
							// コンテナクラスを起動
							$widgetContainer = new $containerClass();
							$gEnvManager->setCurrentWidgetObj($widgetContainer);				// 実行するウィジェットコンテナオブジェクトを登録
							$widgetContainer->process($gRequestManager);
							$gEnvManager->setCurrentWidgetObj(null);
						} else {
							echo 'file not found error: ' . $containerPath;
						}
					}
				}
			}
		}
	}
	/**
	 * ウィジェットインストーラ、アンインストーラを実行
	 *
	 * @param int $install					インストール種別(0=インストール、1=アンインストール、2=アップデート)
	 */
	function goInstallWidget($install)
	{
		global $gEnvManager;
		global $gRequestManager;
		
		// コンテナクラス名作成
		$widgetId = $gEnvManager->getCurrentWidgetId();// ウィジェットID
		$containerClass = 'admin_';
		$containerClass .= $widgetId . 'InstallWidgetContainer';

		// コンテナクラス名修正
		$containerClass = str_replace('/', '_', $containerClass);
		
		// コンテナクラスが既にロードされているときはエラー
		if (class_exists($containerClass)){
			echo 'class redefined error4: ' . $containerClass;
		} else {
			// コンテナクラスファイル取り込み
			$containerPath = $gEnvManager->getCurrentWidgetContainerPath() . '/' . $containerClass . '.php';
			if (file_exists($containerPath)){
				require_once($containerPath);
			} else {		// インストーラが存在しないときは終了
				return;
			}
			// コンテナクラスを起動
			$widgetContainer = new $containerClass();
			$gEnvManager->setCurrentWidgetObj($widgetContainer);				// 実行するウィジェットコンテナオブジェクトを登録
			$widgetContainer->process($gRequestManager, $install);
			$gEnvManager->setCurrentWidgetObj(null);
		}
	}
	/**
	 * ジョブプログラムを実行
	 *
	 * @param string $filepath		呼び出し元ファイルのフルパス。通常は「__FILE__」。OSによってパスの表現が違うので注意。
	 */
	function goJob($filepath)
	{
		global $gEnvManager;
		global $gRequestManager;
		global $gPageManager;
		
		// ##### ジョブタイプから実行するウィジェットIDを取得 #####
		$jobType = basename(dirname($filepath));
		$widgetId = $gPageManager->getWidgetIdByJobType($jobType);
		if (empty($widgetId)) return;

		// ##### ウィジェットのジョブを実行 #####
		// コンテナクラス名作成
		$containerClass = 'admin_';
		$containerClass .= $widgetId . 'JobWidgetContainer';

		// コンテナクラス名修正
		$containerClass = str_replace('/', '_', $containerClass);
		
		// コンテナクラスが既にロードされているときはエラー
		if (class_exists($containerClass)){
			echo 'class redefined error5: ' . $containerClass;
		} else {
			// コンテナクラスファイル取り込み
			$containerPath = $gEnvManager->getCurrentWidgetContainerPath() . '/' . $containerClass . '.php';
			if (file_exists($containerPath)){
				require_once($containerPath);
			} else {		// ジョブ実行クラスが存在しないときは終了
				return;
			}
			// コンテナクラスを起動
			$widgetContainer = new $containerClass();
			$gEnvManager->setCurrentWidgetObj($widgetContainer);				// 実行するウィジェットコンテナオブジェクトを登録
			$widgetContainer->process($gRequestManager);
			$gEnvManager->setCurrentWidgetObj(null);
		}
	}
	/**
	 * ウィジェットジョブプログラムを実行
	 *
	 * @param string $filepath		呼び出し元ファイルのフルパス。通常は「__FILE__」。OSによってパスの表現が違うので注意。
	 */
	function goWidgetJob($filepath)
	{
	echo 'widget....';
	}
	/**
	 * 携帯用プログラムを実行
	 *
	 * @param string $filepath		呼び出し元ファイルのフルパス。通常は「__FILE__」。OSによってパスの表現が違うので注意。
	 * @return 						なし
	 */
	function goMobile($filepath = '')
	{
		$this->_goDevice(1/*携帯用*/, $filepath);
	}
	/**
	 * スマートフォン用プログラムを実行
	 *
	 * @param string $filepath		呼び出し元ファイルのフルパス。通常は「__FILE__」。OSによってパスの表現が違うので注意。
	 * @return 						なし
	 */
	function goSmartphone($filepath = '')
	{
		$this->_goDevice(2/*スマートフォン用*/, $filepath);
	}
	/**
	 * 各種デバイス用プログラムを実行
	 *
	 * @param int $type				1=携帯用プログラム、2=スマートフォン用プログラム
	 * @param string $filepath		呼び出し元ファイルのフルパス。通常は「__FILE__」。OSによってパスの表現が違うので注意。
	 * @return 						なし
	 */
	function _goDevice($type, $filepath)
	{
		global $gEnvManager;
		global $gRequestManager;

		switch ($type){
			case 1:
				// 携帯用URLへのアクセスを設定
				$gEnvManager->setIsMobileSite(true);
				break;
			case 2:
				// スマートフォン用URLのアクセスを設定
				$gEnvManager->setIsSmartphoneSite(true);
				break;
		}
		
		// ルートから$filepathへの相対パスで「FrameContainer.php」の先頭につける
		// サフィックスを作成する
		// 例) 相対パスが「admin/index.php」のとき「admin_index」
		if ($filepath == ''){
			$basename = basename($_SERVER["PHP_SELF"], '.php');
		} else {
			// ルートまでのパスを削除
			$path = str_replace(M3_SYSTEM_ROOT_PATH, '', $filepath);
			$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
			$path = trim($path, '/');
			$pathArray = explode('/', $path);
			$basename = '';
			for ($i = 0; $i < count($pathArray); $i++){
				if ($i == 0){
					$basename .= $pathArray[$i];
				} else {
					//$basename .= ucfirst($pathArray[$i]);
					$basename .= ('_' . $pathArray[$i]);
				}
			}
			$basename = basename($basename, '.php');
		}
		
		// ページIDを設定
		$gEnvManager->setCurrentPageId($basename);

		// アクセスポイントを設定
		$gEnvManager->setAccessPath(str_replace('_', '/', $basename));
		
		// ファイル名から、コンテナクラスファイル取り込み
		require_once($gEnvManager->getContainerPath() . '/' . $basename . 'FrameContainer.php');
		
		// コンテナクラスを起動
		$class = $basename . 'FrameContainer';
		$mainContainer = new $class();
		$mainContainer->process($gRequestManager);
	}
	/**
	 * クラス検索ロード用のパスを追加
	 *
	 * @param string $path		追加パス
	 * @return 					なし
	 */
	function addLoadPath($path)
	{
		if (!empty($path) && !in_array($path, $this->loadPath)){
			set_include_path(get_include_path() . PATH_SEPARATOR . $path);
			$this->loadPath[] = $path;
		}
	}
}
?>
