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
require_once($gEnvManager->getCommonPath() .	'/gitRepo.php');

class admin_mainServerinfoWidgetContainer extends admin_mainServeradminBaseWidgetContainer
{
	const MAGIC3_SRC_VER_FILE = '/var/magic3/src_version';
	const MAGIC3_SHELL_CREATEDOMAIN = '/root/tools/createdomain.sh';		// ドメイン作成用シェルプログラム
	const WATCH_JOB_STATUS_FILE = 'STATUS';		// ジョブ状態確認用ファイル
	const DIALOG_ID_SSL = 'uploadModal';			// SSL認証書アップロード用ダイアログのID
	const DEFAULT_SSL_FILENAME = 'SSL_CRT';		// SSL認証書デフォルトファイル名
	
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
		return 'serverinfo.tmpl.html';
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
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
		$base = 1024;
		$path = '/';
//		$cmdFile_update_install_package = $this->cmdPath . M3_DS . self::CMD_FILENAME_UPDATE_INSTALL_PACKAGE;		// インストールパッケージの更新、コマンド実行ファイル
//		$cmdFile_update_ssl				= $this->cmdPath . M3_DS . self::CMD_FILENAME_UPDATE_SSL;		// SSL認証書の更新、コマンド実行ファイル
		
		// ジョブの実行状況を表示
//		$isShownJobStatus = $this->_showJobStatus();

		// マスターホストのディレクトリ名
		$masterHostId = basename(dirname($this->gEnv->getSystemRootPath()));
		if (basename(dirname(dirname($this->gEnv->getSystemRootPath()))) != 'home') $masterHostId = 'なし';		// homeディレクトリ以外の場合はホストIDなし
		
		$act = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_ACT);
		$status	= $request->trimValueOf('status');
		if ($act == 'getnewsrc'){		// 最新インストールパッケージ取得のとき
			// コマンドファイルにパラメータを書き込む
			$cmdContent = '';
			$email = $this->gEnv->getSiteEmail();
			if (!empty($email)) $cmdContent .= 'mailto=' . $email . "\n";
			$ret = file_put_contents($this->cmdFile_update_install_package, $cmdContent, LOCK_EX/*排他的アクセス*/);
			if ($ret !== false){
				$this->tmpl->setAttribute('show_process_dialog', 'visibility', 'visible');		// 処理結果監視
				
				// ジョブタイプ設定
				$this->tmpl->addVar('show_process_dialog', 'type',	self::JOB_TYPE_UPDATE_INSTALL_PACKAGE);// インストールパッケージ取得ジョブ
			}
		} else if ($act == 'getinfo'){		// 最新情報取得
			// 処理タイプ
			$type	= $request->trimValueOf('type');
			
			switch ($type){
			case self::JOB_TYPE_UPDATE_INSTALL_PACKAGE:	// インストールパッケージ取得ジョブ
				if (file_exists($this->cmdFile_update_install_package)){
					$this->gInstance->getAjaxManager()->addData('code', '0');
				} else {			// インストールパッケージ更新完了のとき
					$this->gInstance->getAjaxManager()->addData('code', '1');
				}
				break;
			case self::JOB_TYPE_UPDATE_SSL:				// SSL認証書の更新ジョブ
				if (file_exists($this->cmdFile_update_ssl)){
					$this->gInstance->getAjaxManager()->addData('code', '0');
				} else {			// インストールパッケージ更新完了のとき
					$this->gInstance->getAjaxManager()->addData('code', '1');
				}
				break;
			}
			return;
		} else if ($act == 'upload'){		// ファイルアップロードの場合
			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])){
				$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得
				
				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					// 作業ディレクトリ作成
					$tmpDir = $this->gEnv->getTempDirBySession(true/*ディレクトリ作成*/);		// セッション単位の作業ディレクトリを取得
					$tmpFile = $tmpDir . M3_DS . self::DEFAULT_SSL_FILENAME;
					
					// アップされたテンポラリファイルを保存ディレクトリにコピー
					$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
					if ($ret){
						// ファイル内容の確認
						$fileContent = file_get_contents($tmpFile);
						$parsedCert = openssl_x509_parse($fileContent);
						$expireDt = $parsedCert['validTo_time_t'];
						$sslDomain = $parsedCert['subject']['CN'];		// ドメイン名

						if (time() <= $expireDt){
							$expireDt = date("Y/m/d H:i:s", $expireDt);
							$expireDtTag = '<span class="available">' . $this->convertToDispDateTime($expireDt) . '</span>';
						} else {
							$expireDt = date("Y/m/d H:i:s", $expireDt);
							$expireDtTag = '<span class="stopped">' . $this->convertToDispDateTime($expireDt) . '</span>';
						}
						$sslUpdateInfo = '<br />=>&nbsp' . $expireDtTag . '&emsp;ドメイン名：' . $this->convertToDispString($sslDomain);
						
						$status = '1';			// 画面状態(SSL更新表示)
						$msg = 'この内容で証明書を更新する場合は、更新ボタンを押してください';
						$this->setInfoMsg($msg);
					} else {
						$msg = 'ファイルのアップロードに失敗しました';
						$this->setAppErrorMsg($msg);
					}
					// テンポラリファイル削除
					//unlink($tmpFile);
				}
			} else {
				$msg = 'アップロードファイルが見つかりません';
				$this->setAppErrorMsg($msg);
			}
		} else if ($act == 'updatessl' && $status == '1'){		// SSL認証書を更新のとき
			// 一時ファイルのSSL認証書取得
			$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
			$tmpFile = $tmpDir . M3_DS . self::DEFAULT_SSL_FILENAME;
			
			// ドメイン名取得
			$fileContent = file_get_contents($tmpFile);
			$parsedCert = openssl_x509_parse($fileContent);
			$sslDomain = $parsedCert['subject']['CN'];		// ドメイン名
			$sslDomain = ltrim($sslDomain, '*.');
			
			// SSL認証書を保存
			$ret = mvFileToDir($tmpDir, array(self::DEFAULT_SSL_FILENAME), $this->cmdPath . M3_DS . self::JOB_OPTION_FILE_DIR);
			if ($ret){
				// コマンドファイルにパラメータを書き込む
				$cmdContent = '';
				$email = $this->gEnv->getSiteEmail();
				if (!empty($email)) $cmdContent .= 'mailto=' . $email . "\n";
			
				// ドメイン名
				$cmdContent .= 'domain=' . $sslDomain . "\n";
				
				// SSL認証書ファイル
				$sslFile = self::JOB_OPTION_FILE_DIR . M3_DS . self::DEFAULT_SSL_FILENAME;
				$cmdContent .= 'file=' . $sslFile . "\n";
			
				$ret = file_put_contents($this->cmdFile_update_ssl, $cmdContent, LOCK_EX/*排他的アクセス*/);
				if ($ret !== false){
					$this->tmpl->setAttribute('show_process_dialog', 'visibility', 'visible');		// 処理結果監視
					
					// ジョブタイプ設定
					$this->tmpl->addVar('show_process_dialog', 'type',	self::JOB_TYPE_UPDATE_SSL);// SSL認証書の更新ジョブ
				}
			} else {
				$msg = 'エラーが発生しました';
				$this->setAppErrorMsg($msg);
			}
			
			// 作業ディレクトリを削除
			rmDirectory($tmpDir);
			
			// 処理状態をリセット
			$status = '';
		} else {
			// 作業ディレクトリを削除
			$tmpDir = $this->gEnv->getTempDirBySession();		// セッション単位の作業ディレクトリを取得
			rmDirectory($tmpDir);
		}

		//全体サイズ
		$totalBytes = @disk_total_space($path);
		if ($totalBytes === false){
			$this->SetMsg(self::MSG_APP_ERR, $this->_('Can not access the page.'));		// アクセスできません
			return;
		}
		$class = min((int)log($totalBytes , $base) , count($units) - 1);
		$totalStr = sprintf('%1.2f' , $totalBytes / pow($base, $class)) . $units[$class];

		//空き容量
		$freeBytes = disk_free_space($path);
		$class = min((int)log($freeBytes , $base) , count($units) - 1);
		$freeStr = sprintf('%1.2f' , $freeBytes / pow($base, $class)) . $units[$class];

		//使用容量
		$usedBytes = $totalBytes - $freeBytes;
		$class = min((int)log($usedBytes , $base) , count($units) - 1);
		$usedStr = sprintf('%1.2f' , $usedBytes / pow($base, $class)) . $units[$class];

		//使用率
		$usedRateStr = round($usedBytes / $totalBytes * 100, 2) . '%';
		
		// Magic3のソースバージョン
		$repo = new GitRepo('magic3org', 'magic3');
		$latestVersion = $repo->getLatestVersionStrByTag();
		if (file_exists(self::MAGIC3_SRC_VER_FILE)){
			$srcVer = file_get_contents(self::MAGIC3_SRC_VER_FILE);
			$srcVer = trim($srcVer);
			
			// 最新バージョンの場合はインストール不可
			$this->tmpl->addVar("_widget", "update_src_button_disabled", $this->convertToDisabledString((!empty($srcVer) && version_compare($srcVer, $latestVersion) == 0) || $this->isShownJobStatus));
			
			// 最新バージョン表示用
			$versionInfoStr = '<span class="available">(最新版 ' . $latestVersion . ')</span>';
		} else {
			$srcVer = '未取得';
			
			// ジョブ監視していない場合はソース取得ボタンを使用不可にする
//			if (!file_exists(self::MAGIC3_SHELL_CREATEDOMAIN)){		// apacheユーザから/root/tools以下は参照できない
//				$this->tmpl->addVar("_widget", "update_src_button_disabled", $this->convertToDisabledString(1));
//			}
			if (!file_exists($this->cmdPath . M3_DS . self::WATCH_JOB_STATUS_FILE)){
				$this->tmpl->addVar("_widget", "update_src_button_disabled", $this->convertToDisabledString(1));
			}
		}
		
		// ジョブ監視状況
		$watchJobStatus = '<span class="stopped">停止</span>';
		if (file_exists($this->cmdPath . M3_DS . self::WATCH_JOB_STATUS_FILE)){
			// 10分以内にジョブが実行されている場合は稼動にする
			$time = filemtime($this->cmdPath . M3_DS . self::WATCH_JOB_STATUS_FILE);
			if (time() - $time < 60 * 10) $watchJobStatus = '<span class="running">稼動中</span>';
		}
		
		// SSL期限
		$expireDt = $this->_getSslExpireDt($this->gEnv->getRootUrl(), $sslDomain);
		if (empty($expireDt)){
			$expireDtTag = '未取得';
			$sslDomainTag = '';
		} else {
			if (time() <= $expireDt){
				$expireDt = date("Y/m/d H:i:s", $expireDt);
				$expireDtTag = '<span class="available">' . $this->convertToDispDateTime($expireDt) . '</span>';
			} else {
				$expireDt = date("Y/m/d H:i:s", $expireDt);
				$expireDtTag = '<span class="stopped">' . $this->convertToDispDateTime($expireDt) . '</span>';
			}
			$sslDomainTag = '&emsp;ドメイン名：' . $this->convertToDispString($sslDomain);
		}
		
		// ボタンの表示制御
		if (empty($status)){
			$this->tmpl->setAttribute('show_ssl_upload', 'visibility', 'visible');		// 「SSL認証書をアップロード」ボタン
		} else {
			$this->tmpl->setAttribute('show_ssl_update', 'visibility', 'visible');		// 「SSL認証書を更新」ボタン
		}
		// SSL認証書アップロード用ダイアログ
		$eventAttr = 'onclick="uploadCheck();"';
		$this->tmpl->addVar('_widget', 'ssl_dialog',	$this->gDesign->createFileUploadDialogHtml(self::DIALOG_ID_SSL, ''/*OKボタンのIDなし*/, $eventAttr));		// SSL認証書アップロード用ダイアログ
		
		// 値を埋め込む
		$this->tmpl->addVar('_widget', 'status',	$this->convertToDispString($status));			// 画面状態
		$this->tmpl->addVar('_widget', 'site_url',	$this->convertToDispString($this->gEnv->getAdminUrl()));
		$this->tmpl->addVar('_widget', 'host_id',	$this->convertToDispString($masterHostId));			// ホストID
		$this->tmpl->addVar('_widget', 'total_size',	$this->convertToDispString($totalStr));
		$this->tmpl->addVar('_widget', 'free_size',		$this->convertToDispString($freeStr));
		$this->tmpl->addVar('_widget', 'used_size',		$this->convertToDispString($usedStr));
		$this->tmpl->addVar('_widget', 'used_rate',		$this->convertToDispString($usedRateStr));
		$this->tmpl->addVar('_widget', 'watch_job_status',		$watchJobStatus);
		$this->tmpl->addVar('_widget', 'src_version',	$this->convertToDispString($srcVer) . $versionInfoStr);
		$this->tmpl->addVar('_widget', 'ssl_expire_dt',	$expireDtTag);
		$this->tmpl->addVar('_widget', 'domain_name',	$sslDomainTag);
		$this->tmpl->addVar('_widget', 'ssl_update_info',	$sslUpdateInfo);
		$this->tmpl->addVar('show_ssl_upload', 'ssl_dialog_id',	self::DIALOG_ID_SSL);			// SSL認証書アップロード用ダイアログのタグID
	}
	/**
	 * SSLの期限を取得
	 *
	 * @param string $url		SSL証明書を取得するURL
	 * @param string $sslDomain	SSLの対象となるドメイン名
	 * @return int				UNIXタイムスタンプ。取得できない場合は0。
	 */
	function _getSslExpireDt($url, &$sslDomain)
	{
		$arr = parse_url($url);
		if ($arr == false)  return '';
		
		$hostname = $arr['host'];

		$stream_context = stream_context_create(array(
			'ssl' => array('capture_peer_cert' => true)
		));
		$fp = @stream_socket_client(
			'ssl://' . $hostname . ':443',
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$stream_context
		);
		if (!$fp) return 0;		// 取得不可の場合は終了
		
		$cont = stream_context_get_params($fp);
		$parsed = openssl_x509_parse($cont['options']['ssl']['peer_certificate']);
//		$expireDt = date("Y/m/d H:i:s", $parsed['validTo_time_t']);
		$expireDt = $parsed['validTo_time_t'];
		$sslDomain = $parsed['subject']['CN'];		// ドメイン名
		
		// ファイルポインタ閉じる
		fclose($fp);
		
		// ドメイン名のチェック
		if (strStartsWith($sslDomain, '*.')){		// ワイルドカードSSLの場合
			if (!strEndsWith($hostname, substr($sslDomain, 1))) $expireDt = 0;
		} else {
			if (!strEndsWith($hostname, '.' . $sslDomain)) $expireDt = 0;
		}
		return $expireDt;
	}
}
?>
