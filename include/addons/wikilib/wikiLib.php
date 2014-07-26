<?php
/**
 * Wikiコンテンツクラス
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
require_once(dirname(__FILE__) . '/wikiLibDb.php');

class wikiLib
{
	private $db;				// DB接続オブジェクト
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// DBオブジェクト作成
		$this->db = new wikiLibDb();
	}
	/**
	 * Wikiコンテンツをプレーンなテキストに変換
	 *
	 * @param string  $src	Wikiコンテンツソースデータ
	 * @return string		取得データ
	 */
	function convertToText($src)
	{
		return $src;
		
		if (!is_array($src)) $src = explode("\n", $src);
//$src = preg_split('/(?<=\n)/', $src);
//$src = str_replace("\r", '', $src);
//debug($src);
		// クラスが存在しない場合はライブラリを読み込む
		if (!class_exists('Body')){
			require_once(dirname(__FILE__) . '/htmlElement.php');
			require_once(dirname(__FILE__) . '/make_link.php');
			require_once(dirname(__FILE__) . '/html.php');
		}
		
		$body = new Body(1);
		$body->parse($src);
debug($body->toString());
		return $body->toString();

		//if (preg_match('/^\#([^\(]+)(?:\((.*)\))?/', $text, $matches) &&
//		return preg_replace('/^\#([^\(]+)(?:\((.*)\))?/', '', $src);
	}
}
?>
