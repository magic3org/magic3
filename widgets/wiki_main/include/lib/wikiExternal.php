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
require_once(dirname(dirname(__FILE__)) .	'/container/wiki_mainCommonDef.php');
require_once(dirname(dirname(__FILE__)) .	'/db/wiki_mainDb.php');
// Magic3追加ファイル
require_once(dirname(__FILE__) . '/wikiConfig.php');
require_once(dirname(__FILE__) . '/wikiPage.php');
require_once(dirname(__FILE__) . '/wikiParam.php');
require_once(dirname(__FILE__) . '/wikiScript.php');
// PukiWikiファイル
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
	}
	/**
	 * 表示用データ作成
	 *
	 * @param string $src		Wikiコンテンツテキスト
	 * @return string			HTML
	 */
	function convertToHtml($src)
	{
		return convert_html($src);
	}
}
?>
