<?php
/**
 * Wiki外部アクセス用クラス
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
require_once(dirname(dirname(__FILE__)) .	'/db/wiki_mainDb.php');
require_once(dirname(dirname(__FILE__)) .	'/container/wiki_mainCommonDef.php');
// Magic3追加ファイル
require_once(dirname(__FILE__) . '/wikiConfig.php');
require_once(dirname(__FILE__) . '/wikiPage.php');
require_once(dirname(__FILE__) . '/wikiParam.php');
require_once(dirname(__FILE__) . '/wikiScript.php');
// PukiWikiファイル
require_once(dirname(__FILE__) . '/func.php');
require_once(dirname(__FILE__) . '/file.php');
require_once(dirname(__FILE__) . '/plugin.php');
require_once(dirname(__FILE__) . '/html.php');
require_once(dirname(__FILE__) . '/backup.php');
require_once(dirname(__FILE__) . '/convert_html.php');
require_once(dirname(__FILE__) . '/make_link.php');
require_once(dirname(__FILE__) . '/diff.php');
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/link.php');
require_once(dirname(__FILE__) . '/auth.php');
require_once(dirname(__FILE__) . '/proxy.php');

class wikiExternal
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト取得
		$db = wiki_mainCommonDef::getDb();

		// クラス初期化
		WikiConfig::init($db);
		WikiPage::init($db);		// Wikiページ管理クラス
		WikiParam::init($db);		// URLパラメータ管理クラス
		
		// 初期化。WikiページID取得等
		
		require_once(dirname(__FILE__) . '/init.php');
	}
	/**
	 * 表示用データ作成
	 *
	 * @param string $src		Wikiコンテンツテキスト
	 * @param string  $pageId	WikiページID
	 * @return string			HTML
	 */
	function convertToHtml($src, $pageId = '')
	{
		global $vars;
		
		// ページ指定でWikiコンテンツ作成の場合はパラメータを初期化
		if (!empty($pageId)){
			// 一旦退避
			$saveVarCmd = $vars['cmd'];
			$saveVarPage = $vars['page'];
			$saveCmd = WikiParam::getCmd();
			$savePage = WikiParam::getPage();
			
			// 一時的に変更
			$vars['cmd']  = 'read';
			$vars['page'] = $pageId;
			WikiParam::setCmd('read');
			WikiParam::setPage($pageId);
		}
		$dest = convert_html($src);
		
		// ページ指定でWikiコンテンツ作成の場合はパラメータをリセット
		if (!empty($pageId)){
			// 値を戻す
/*			$vars['cmd']  = '';
			$vars['page'] = '';
			WikiParam::setCmd('');
			WikiParam::setPage('');*/
			$vars['cmd']  = $saveVarCmd;
			$vars['page'] = $saveVarPage;
			WikiParam::setCmd($saveCmd);
			WikiParam::setPage($savePage);
		}
		return $dest;
	}
}
?>
