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
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainServeradminBaseWidgetContainer.php');

class admin_mainSitelistWidgetContainer extends admin_mainServeradminBaseWidgetContainer
{
	const HOME_DIR = '/home';
	const SITE_DEF_FILE = '/public_html/include/siteDef.php';
	const GLOBAL_FILE = '/public_html/include/global.php';
	const STSTUS_NOT_INSTALLED = 'インストール未実行';
	const STSTUS_ACTIVE = '運用中';
	const LINK_ADMIN_PAGE = '管理画面';
	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 運用中アイコン
	const NOT_INSTALLED_ICON_FILE = '/images/system/notice32.png';			// インストール未実行アイコン
	const WINDOW_ICON_FILE = '/images/system/window32.png';			// 管理画面アイコン
	const MAX_SITE_COUNT = 3;		// 管理サイト最大数
	
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
		$task = $request->trimValueOf('task');
		
		$ret = is_dir(self::HOME_DIR);
		if ($ret === false){		// ディレクトリの参照ができない場合はアクセス不可
			return 'message.tmpl.html';
		} else {
			if ($task == 'sitelist_detail'){		// 詳細画面
				return 'sitelist_detail.tmpl.html';
			} else {
				return 'sitelist.tmpl.html';
			}
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
		
		if ($task == 'sitelist_detail'){		// 詳細画面
			$this->createDetail($request);
		} else {
			$this->createList($request);
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
		// Apacheからバーチャルホスト情報を取得
		$vhostList = $this->_getVirtualHostInfo();
		
		// マスターホストのディレクトリ名
		$masterHostId = basename(dirname($this->gEnv->getSystemRootPath()));
		
		// ジョブの実行状況を表示
//		$isShownJobStatus = $this->_showJobStatus();
		
		// ディレクトリ一覧を取得
		$hostArray = array();
		$searchPath = self::HOME_DIR;
		if ($ret = @is_dir($searchPath)){
			$dir = dir($searchPath);
			while (($file = $dir->read()) !== false){
				$filePath = $searchPath . M3_DS . $file;
				$pathParts = pathinfo($file);
					
				// ディレクトリのときは、ドメイン名を取得
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_dir($filePath) && $file != $masterHostId){
					$line = array();
					$hostInfo = $vhostList[$file];
					if (!isset($hostInfo)) continue;		// バーチャルホストでないディレクトリは読み飛ばす
					
					if (!empty($hostInfo['hostname'])) $line['hostname'] = $hostInfo['hostname'];
					
					// ディレクトリ作成日をホスト作成日とする
					$line['date'] = filemtime($filePath);
					
					// 使用ディスク量
					$line['disksize'] = calcDirSize($filePath);
						
					$siteInfoFile = $filePath . self::SITE_DEF_FILE;
					if (file_exists($siteInfoFile)){
						$line['dir'] = $file;
//						$line['date'] = date("Y/m/d H:i:s", filemtime($siteInfoFile));
						$contents = file_get_contents($siteInfoFile);
						
						// URL取得
						$url = '';
						$key = 'M3_SYSTEM_ROOT_URL';
						if (preg_match("/^[ \t]*define\([ \t]*[\"']" . $key . "[\"'][ \t]*,[ \t]*[\"'](.*)[\"'][ \t]*\)/m", $contents, $matches)) $url = $matches[1];
						$line['url'] = $url;
						
						// 管理画面URL取得
						$adminUrl = '';
						$key = 'M3_SYSTEM_ADMIN_URL';
						if (preg_match("/^[ \t]*define\([ \t]*[\"']" . $key . "[\"'][ \t]*,[ \t]*[\"'](.*)[\"'][ \t]*\)/m", $contents, $matches)) $adminUrl = $matches[1];
						$line['admin_url'] = $adminUrl;
					}
					$hostArray[] = $line;
				}
			}
			$dir->close();
		}
		if ($ret === false){
			$this->SetMsg(self::MSG_APP_ERR, $this->_('Can not access the page.'));		// アクセスできません
			return;
		}
		$adminUrl = $this->gEnv->getAdminUrl();
		$isSslAdminUrl = false;			// 管理画面がSSLアクセスかどうか
		if (strStartsWith($adminUrl, 'https://')) $isSslAdminUrl = true;
		
		// 値を埋め込む
//		usort($hostArray, create_function('$a,$b', 'return $a["date"] - $b["date"];'));
		usort($hostArray, function($a, $b)
		{
			return $a['date'] - $b['date'];
		});
		
		for ($i = 0; $i < count($hostArray); $i++){
			$line = $hostArray[$i];
			$hostStr = $this->convertToDispString($line['hostname']);
//			if (empty($hostStr)) $hostStr = '<span class="error">' . self::MSG_SITE_NOT_INSTALLED . '</span>';
			
			// 運用状態アイコン
			if (empty($line['url'])){		// インストールが実行されていないとき
				$titleStr = self::STSTUS_NOT_INSTALLED;
				$iconUrl = $this->gEnv->getRootUrl() . self::NOT_INSTALLED_ICON_FILE;		// インストール未実行アイコン
			} else {
				$titleStr = self::STSTUS_ACTIVE;
				$iconUrl = $this->gEnv->getRootUrl() . self::ACTIVE_ICON_FILE;		// 運用中アイコン
			}
			$statusTag = '<img src="' . $this->getUrl($iconUrl) . '" alt="' . $titleStr . '" title="' . $titleStr . '" rel="m3help" />';
		
			// 管理画面アイコン
			$titleStr = self::LINK_ADMIN_PAGE;
			$iconUrl = $this->gEnv->getRootUrl() . self::WINDOW_ICON_FILE;		// 管理画面アイコン
			$adminUrl = $line['admin_url'];			// 管理画面用URL
			if (empty($adminUrl)){		// 管理画面用URLが設定されていない場合は、ドメイン名から作成
				$linkUrl = 'http://' . $line['hostname'] . M3_DS . M3_DIR_NAME_ADMIN . M3_DS;
			} else {
				$linkUrl = $adminUrl . M3_DS;
			}
			$linkTag = '<a href="' . convertUrlToHtmlEntity($linkUrl) . '" target="_blank" rel="m3help" title="' . $titleStr . '">';
			$linkTag .= '<img src="' . $this->getUrl($iconUrl) . '" alt="' . $titleStr . '" /></a>';
			
			$row = array(
				'no'		=> $i + 1,
				'id'		=> $this->convertToDispString($line['hostname']),	// ID(ホスト名)
				'host'		=> $hostStr,	// ホスト名
				'status'	=> $statusTag,	// 状態
				'dir'		=> $this->convertToDispString($line['dir']),			// ディレクトリ名
				'date'		=> $this->convertToDispDateTime(date("Y/m/d H:i:s", $line['date']), 0/*ロングフォーマット*/, 10/*時分*/),			// 作成日時
				'disksize'	=> $this->convertToDispString(convFromBytes($line['disksize'])),			// ディスク使用量
				'link'		=> $linkTag
			);
			$this->tmpl->addVars('sitelist', $row);
			$this->tmpl->parseTemplate('sitelist', 'a');
		}
		
		// 管理可能なホスト数の上限を超えている場合は新規ボタンを使用不可にする
		if (count($hostArray) >= self::MAX_SITE_COUNT) $this->tmpl->addVar('_widget', 'add_button_disabled', $this->convertToDisabledString(1));
	}
	/**
	 * 詳細画面作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param								なし
	 */
	function createDetail($request)
	{
		$act = $request->trimValueOf('act');
		$id = $request->trimValueOf('id');				// ID(ホスト名)
		$hostname = $request->trimValueOf('item_hostname');				// ホスト名
		
		// Apacheからバーチャルホスト情報を取得
		$vhostList = $this->_getVirtualHostInfo();

		// ジョブ実行ファイル
//		$cmdFile_create_site = $this->cmdPath . M3_DS . self::CMD_FILENAME_CREATE_SITE;		// サイト作成、コマンドファイル
//		$cmdFile_remove_site = $this->cmdPath . M3_DS . self::CMD_FILENAME_REMOVE_SITE;		// サイト削除、コマンドファイル
		
		// ジョブの実行状況を表示
//		$isShownJobStatus = $this->_showJobStatus();
		
		$reloadData = false;		// データを再取得するかどうか
		if ($act == 'add'){		// 新規追加のとき
			if (count($vhostList) > self::MAX_SITE_COUNT) $this->setUserErrorMsg('管理可能なホスト数を超えています');
			
			// 入力チェック
			$ret = $this->checkInput($hostname, 'ホスト名');		// ホスト名
			if ($ret && !preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', $hostname)) $this->setUserErrorMsg('不正なホスト名です');
			
			// 登録済みかどうかチェック
			if ($this->getMsgCount() == 0){
				foreach ($vhostList as $key => $vhost){
					if ($vhost['hostname'] == $hostname){
						$this->setUserErrorMsg('ホスト名はすでに登録されています');
						break;
					}
				}
			}
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// コマンドファイルにパラメータを書き込む
				$cmdContent = '';
				$email = $this->gEnv->getSiteEmail();
				if (!empty($email)) $cmdContent .= 'mailto=' . $email . "\n";
				$cmdContent .= 'hostname=' . $hostname . "\n";
				$ret = file_put_contents($this->cmdFile_create_site, $cmdContent, LOCK_EX/*排他的アクセス*/);
				if ($ret !== false){
					$id = $hostname;
					$reloadData = true;			// データを再取得

					$this->tmpl->setAttribute('show_process_dialog', 'visibility', 'visible');		// 処理結果監視
					$this->tmpl->addVar("_widget", "cmd_type", '&type=create_site');		// 実行コマンドタイプ
					$msgCompleted = 'サイト作成が完了しました';	// 処理完了メッセージ
					$msgError = 'サイト作成に失敗しました';		// 処理エラーメッセージ
				}
			}
		} else if ($act == 'delete'){		// 削除のとき
			// 入力チェック
			$ret = $this->checkInput($id, 'ID');		// ID(ホスト名)
			
			// エラーなしの場合は、データを更新
			if ($this->getMsgCount() == 0){
				// コマンドファイルにパラメータを書き込む
				$cmdContent = '';
				$email = $this->gEnv->getSiteEmail();
				if (!empty($email)) $cmdContent .= 'mailto=' . $email . "\n";
				$cmdContent .= 'hostname=' . $id . "\n";
				$ret = file_put_contents($this->cmdFile_remove_site, $cmdContent, LOCK_EX/*排他的アクセス*/);
				if ($ret !== false){
					$reloadData = true;			// データを再取得

					$this->tmpl->setAttribute('show_process_dialog', 'visibility', 'visible');		// 処理結果監視
					$this->tmpl->addVar("_widget", "cmd_type", '&type=remove_site');		// 実行コマンドタイプ
					$msgCompleted = 'サイト削除が完了しました';		// 処理完了メッセージ
					$msgError = 'サイト削除に失敗しました';			// 処理エラーメッセージ
				}
			}
		} else if ($act == 'getinfo'){		// 最新情報取得
			$type = $request->trimValueOf('type');			// 実行コマンドタイプ
			if ($type == 'create_site'){
				$cmdFile = $this->cmdFile_create_site;		// サイト作成、コマンドファイル
			} else if ($type == 'remove_site'){
				$cmdFile = $this->cmdFile_remove_site;		// サイト削除、コマンドファイル
			}
			if (file_exists($cmdFile)){
				$this->gInstance->getAjaxManager()->addData('code', '0');
			} else {			// インストールパッケージ更新完了のとき
				$this->gInstance->getAjaxManager()->addData('code', '1');
			}
			return;
		} else {		// 初期状態
			$reloadData = true;			// データを再取得
		}
		// 表示データ再取得
		if ($reloadData){
			// ホストID取得
			$hostId = '';
			foreach ($vhostList as $key => $vhost){
				if ($vhost['hostname'] == $id){
					$hostId = $key;
					$hostname = $id;
					break;
				}
			}
			if (!empty($hostId)){
				// ディレクトリ日付取得
				$siteDir = self::HOME_DIR . M3_DS . $hostId;
				if (file_exists($siteDir)){
					$createDt = date("Y/m/d H:i:s", filemtime($siteDir));
				
					// バージョン取得
					if (file_exists($siteDir . self::GLOBAL_FILE)){
						$key = 'M3_SYSTEM_VERSION';
						$contents = file_get_contents($siteDir . self::GLOBAL_FILE);
						if (preg_match("/^[ \t]*define\([ \t]*[\"']" . $key . "[\"'][ \t]*,[ \t]*[\"'](.*)[\"'][ \t]*\)/m", $contents, $matches)) $version = $matches[1];
					}
				}
			}
		}
		
		if (empty($id)){		// 新規追加のとき
			$this->tmpl->setAttribute('input_hostname', 'visibility', 'visible');	// ホスト名入力領域表示
			$this->tmpl->setAttribute('add_button', 'visibility', 'visible');		// 新規追加ボタン表示
			
			$this->tmpl->addVar("input_hostname", "hostname", $this->convertToDispString($hostname));			// ホスト名
			
			// ジョブメッセージが表示されているときはボタン使用不可
			$this->tmpl->addVar("add_button", "add_button_disabled", $this->convertToDisabledString($this->isShownJobStatus));
		} else {
			$this->tmpl->setAttribute('update_button', 'visibility', 'visible');// 削除ボタン表示
			$this->tmpl->addVar("_widget", "hostname", $this->convertToDispString($hostname));			// ホスト名
			
			// ジョブメッセージが表示されているときはボタン使用不可
			$this->tmpl->addVar("update_button", "del_button_disabled", $this->convertToDisabledString($this->isShownJobStatus));
		}
		$this->tmpl->addVar("_widget", "id", $this->convertToDispString($id));		// ID(ホスト名)
		$this->tmpl->addVar("_widget", "host_id", $this->convertToDispString($hostId));		// ホストID
		$this->tmpl->addVar("_widget", "date", $this->convertToDispDateTime($createDt, 0/*ロングフォーマット*/, 10/*時分*/));		// 作成日付
		$this->tmpl->addVar("_widget", "version", $this->convertToDispString($version));		// Magic3バージョン
		$this->tmpl->addVar("_widget", "msg_completed", $msgCompleted);		// 処理完了メッセージ
		$this->tmpl->addVar("_widget", "msg_error", $msgError);		// 処理エラーメッセージ
	}
	/**
	 * Apacheから運用中のバーチャルホスト情報を取得
	 *
	 * @return array		バーチャルホスト情報
	 */
	function _getVirtualHostInfo()
	{
		// Apacheで運営されているバーチャルホストの情報を取得
		$vhostList = array();
		$siteCondition = shell_exec('httpd -S');
//		preg_match_all('/^\s*port 80 namevhost\s*(.*?)\s*\((.*?):(\d+?)\).*$/m', $siteCondition, $matches, PREG_SET_ORDER);
		preg_match_all('/^\s*port 80 namevhost\s*(.*?)\s*\((.*?):(\d+)\).*$/m', $siteCondition, $matches, PREG_SET_ORDER);			// 2018/4/20 該当行数マッチ部分修正
		for ($i = 0; $i < count($matches); $i++){
			$hostName = $matches[$i][1];
			$configPath = $matches[$i][2];

			// ホストIDを取得
			$hostID = '';
			$fileContent = file_get_contents($configPath);
			$ret = preg_match('/^\s*DocumentRoot\s*\/home\/(.*?)\/.*$/m', $fileContent, $hostMatches);
			if ($ret) $hostID = $hostMatches[1];
			if (!empty($hostID)) $vhostList[$hostID] = array('hostname' => $hostName, 'config_path' => $configPath);
		}
		
		return $vhostList;
	}
}
?>
