<?php
/**
 * index.php用共通定義クラス
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
 
class newsCommonDef
{
	static $_deviceType = 0;				// デバイスタイプ(PC)
	static $_viewContentType = 'news';		// コンテンツタイプ
	
	// デフォルト値
	const DF_VIEW_COUNT		= 100;		// 表示コメント数
	const DF_VIEW_DIRECTION	= 0;		// 昇順
	const DF_MAX_LENGTH		= 300;		// コメント文字数
	const DF_MAX_IMAGE_SIZE	= 200;		// 画像最大サイズ
	const DF_UPLOAD_MAX_BYTES	= 512000;		// アップロード画像最大バイトサイズ
	
	// DBフィールド名
	const FD_DEFAULT_MESSAGE	= 'default_message';		// デフォルトメッセージ
	const FD_DATE_FORMAT		= 'date_format';			// 日時フォーマット
	const FD_LAYOUT_LIST_ITEM	= 'layout_list_item';		// リスト項目レイアウト

	/**
	 * 新着情報定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	static function loadConfig($db)
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
		$ret = $db->getAllConfig($rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['nc_id'];
				$value = $rows[$i]['nc_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
}
?>
