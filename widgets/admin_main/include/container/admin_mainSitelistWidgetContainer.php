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
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_mainBaseWidgetContainer.php');

class admin_mainSitelistWidgetContainer extends admin_mainBaseWidgetContainer
{
	const HOME_DIR = '/home';
	const SITE_DEF_FILE = '/public_html/include/siteDef.php';
	const STSTUS_NOT_INSTALLED = 'インストール未実行';
	const STSTUS_ACTIVE = '運用中';
	const LINK_ADMIN_PAGE = '管理画面';
	const ACTIVE_ICON_FILE = '/images/system/active32.png';			// 運用中アイコン
	const NOT_INSTALLED_ICON_FILE = '/images/system/notice32.png';			// インストール未実行アイコン
	const WINDOW_ICON_FILE = '/images/system/window32.png';			// 管理画面アイコン
	
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
		$ret = is_dir(self::HOME_DIR);
		if ($ret === false){		// ディレクトリの参照ができない場合はアクセス不可
			return 'message.tmpl.html';
		} else {
			return 'sitelist.tmpl.html';
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
		// Apacheで運営されているバーチャルホストの情報を取得
		$vhostList = array();
		$siteCondition = shell_exec('httpd -S');
		preg_match_all('/^\s*port 80 namevhost\s*(.*?)\s*\((.*?):(\d+?)\).*$/m', $siteCondition, $matches, PREG_SET_ORDER);
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
		
		// マスターホストのディレクトリ名
		$masterHostId = basename(dirname($this->gEnv->getSystemRootPath()));
		
		// ディレクトリ一覧を取得
		$hostArray = array();
		$searchPath = self::HOME_DIR;
		if ($ret = @is_dir($searchPath)){
			$dir = dir($searchPath);
			while (($file = $dir->read()) !== false){
				$filePath = $searchPath . '/' . $file;
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
						
						// URL取得
						$url = '';
						$contents = file_get_contents($siteInfoFile);
						$key = 'M3_SYSTEM_ROOT_URL';
						if (preg_match("/^[ \t]*define\([ \t]*[\"']" . $key . "[\"'][ \t]*,[ \t]*[\"'](.*)[\"'][ \t]*\)/m", $contents, $matches)){
							$url = $matches[1];
						}
						$line['url'] = $url;
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
		usort($hostArray, create_function('$a,$b', 'return $a["date"] - $b["date"];'));
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
			if ($isSslAdminUrl){		// マスターホストの管理画面がSSLでのアクセスの場合は、管理対象のホストの管理画面もSSLアクセスとする
				$linkUrl = 'https://' . $line['hostname'] . '/' . M3_DIR_NAME_ADMIN . '/';
			} else {
				$linkUrl = 'http://' . $line['hostname'] . '/' . M3_DIR_NAME_ADMIN . '/';
			}
			$linkTag = '<a href="' . convertUrlToHtmlEntity($linkUrl) . '" target="_blank" rel="m3help" title="' . $titleStr . '">';
			$linkTag .= '<img src="' . $this->getUrl($iconUrl) . '" alt="' . $titleStr . '" /></a>';
			
			$row = array(
				'no'		=> $i + 1,
				'host'		=> $hostStr,	// ホスト名
				'status'	=> $statusTag,	// 状態
				'dir'		=> $this->convertToDispString($line['dir']),			// ディレクトリ名
				'date'		=> $this->convertToDispDate(date("Y/m/d H:i:s", $line['date'])),			// 作成日時
				'disksize'	=> $this->convertToDispString(convFromBytes($line['disksize'])),			// ディスク使用量
				'link'		=> $linkTag
			);
			$this->tmpl->addVars('sitelist', $row);
			$this->tmpl->parseTemplate('sitelist', 'a');
		}
	}
}
?>
