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
	const MSG_SITE_NOT_INSTALLED = 'インストール未実行';
	
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
					if (!empty($hostInfo['hostname'])) $line['host'] = $hostInfo['hostname'];
						
					$siteInfoFile = $filePath . self::SITE_DEF_FILE;
					if (file_exists($siteInfoFile)){
						$line['dir'] = $file;
						$line['date'] = date("Y/m/d H:i:s", filemtime($siteInfoFile));
						
/*						// ホスト名取得
						$host = '';
						$contents = file_get_contents($siteInfoFile);
						$key = 'M3_SYSTEM_ROOT_URL';
						if (preg_match("/^[ \t]*define\([ \t]*[\"']" . $key . "[\"'][ \t]*,[ \t]*[\"'](.*)[\"'][ \t]*\)/m", $contents, $matches)){
							$params = parse_url($matches[1]);
							$host = $params['host'];
						}
						$line['host'] = $host;*/
						
						// 使用ディスク量
						$line['disksize'] = calcDirSize($filePath);
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
		// 値を埋め込む
		for ($i = 0; $i < count($hostArray); $i++){
			$line = $hostArray[$i];
			$hostStr = $this->convertToDispString($line['host']);
			if (empty($hostStr)) $hostStr = '<span class="error">' . self::MSG_SITE_NOT_INSTALLED . '</span>';
			$row = array(
				'no'		=> $i + 1,
				'host'		=> $hostStr,	// ホスト名
				'dir'		=> $this->convertToDispString($line['dir']),			// ディレクトリ名
				'date'		=> $this->convertToDispDate($line['date']),			// インストール日時
				'disksize'	=> $this->convertToDispString(convFromBytes($line['disksize'])),			// ディスク使用量
			);
			$this->tmpl->addVars('sitelist', $row);
			$this->tmpl->parseTemplate('sitelist', 'a');
		}
	}
}
?>
