<?php
/**
 * versionlistプラグイン
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2008 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: versionlist.inc.php 1104 2008-10-23 07:48:17Z fishbone $
 * @link       http://www.magic3.org
 */

function plugin_versionlist_action()
{
	global $_title_versionlist;

	if (PKWK_SAFE_MODE) die_message('PKWK_SAFE_MODE prohibits this');

	return array(
		'msg' => $_title_versionlist,
		'body' => plugin_versionlist_convert());
}

function plugin_versionlist_convert()
{
	global $gEnvManager;
	
	if (PKWK_SAFE_MODE) return ''; // Show nothing
	
	// 読み込みディレクトリ設定
	$readDir = array(	array(	'name' => 'プラグイン',
								'dir' => WikiConfig::getPluginDir()),
						array(	'name' => 'ライブラリ',
								'dir' => WikiConfig::getLibDir()),
						array(	'name' => 'DB',
								'dir' => WikiConfig::getDbDir()));

	// 一覧を作成
	$retValue = '';
	for ($i = 0; $i < count($readDir); $i++){
		$retValue .= plugin_versionlist__searchdir($readDir[$i]['name'], $readDir[$i]['dir']);
	}
	return $retValue;
}
/**
 * ディレクトリ内のファイル一覧を作成
 *
 * @param string $name			一覧のタイトル
 * @param string $searchPath	検索するディレクトリ
 * @return string				一覧のHTML
 */
function plugin_versionlist__searchdir($name, $searchPath)
{
	$comments = array();
	
	// ディレクトリ読み込み
	if (!$dir = @dir($searchPath)) return '';
	while($file = $dir->read())
	{
		if (!preg_match("/\.(php|lng|css|js)$/i",$file)) continue;

		$data = join('',file($searchPath.$file));
		$comment = array('file' => htmlspecialchars($file), 'rev' => '', 'date' => '');
		if (preg_match('/\$'.'Id: (.*) (\d+) (\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2})/', $data, $matches))
		{
			$comment['rev'] = htmlspecialchars($matches[2]);
			$comment['date'] = htmlspecialchars($matches[3]);
		}
		$comments[$searchPath.$file] = $comment;
	}
	$dir->close();

	// ファイルが存在しないときは終了
	if (count($comments) == 0) return '';

	ksort($comments);
	$retval = '';
	foreach ($comments as $comment)
	{
		$retval .= <<<EOD
  <tr>
   <td>{$comment['file']}</td>
   <td align="right">{$comment['rev']}</td>
   <td align="right">{$comment['date']}</td>
  </tr>
EOD;
	}
	
	$title = htmlspecialchars($name);
	$path = htmlspecialchars($searchPath);
	$retval = <<<EOD
<h4>$title</h4>
<p>$path</p>
<table border="1" width="100%">
 <thead>
  <tr>
   <th>filename</th>
   <th>revision</th>
   <th>date</th>
  </tr>
 </thead>
 <tbody>
$retval
 </tbody>
</table>
<br />
EOD;
	return $retval;
}
?>
