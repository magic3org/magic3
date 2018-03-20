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
 * @copyright  Copyright 2006-2018 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
 
class default_contentCommonDef
{
	static $_contentType = '';	// コンテンツタイプ
	static $_deviceType = 0;	// デバイスタイプ
	static $_deviceTypeName = 'PC';	// デバイスタイプ名
	static $_viewContentType = 'ct';		// 参照数カウント用コンテンツタイプ(将来的にはcontentを使用)

	// DB定義値
	static $CF_USE_JQUERY			= 'use_jquery';		// jQueryスクリプトを作成するかどうか
	static $CF_USE_CONTENT_TEMPLATE	= 'use_content_template';		// コンテンツ単位のテンプレート設定を行うかどうか
	static $CF_USE_PASSWORD			= 'use_password';		// パスワードアクセス制御
	static $CF_PASSWORD_CONTENT		= 'password_content';			// パスワード画面コンテンツ
	static $CF_LAYOUT_VIEW_DETAIL	= 'layout_view_detail';			// コンテンツレイアウト(詳細表示)
	static $CF_OUTPUT_HEAD			= 'output_head';		// ヘッダ出力するかどうか
	static $CF_HEAD_VIEW_DETAIL		= 'head_view_detail';			// ヘッダ出力(詳細表示)
	const CF_AUTO_GENERATE_ATTACH_FILE_LIST = 'auto_generate_attach_file_list';			// 添付ファイルリストを自動作成
	
	const CONTENT_WIDGET_ID = 'default_content';		// デフォルトの汎用コンテンツ編集ウィジェット
	const ATTACH_FILE_DIR = '/etc/content';				// 添付ファイル格納ディレクトリ
	const DOWNLOAD_CONTENT_TYPE = '-file';				// ダウンロードするコンテンツのタイプ
	const DEFAULT_CONTENT_LAYOUT = '[#BODY#][#FILES#][#PAGES#][#LINKS#]';	// デフォルトのコンテンツレイアウト
	const DEFAULT_HEAD_VIEW_DETAIL = '<meta property="og:type" content="article" /><meta property="og:title" content="[#CT_TITLE#]" /><meta property="og:url" content="[#CT_URL#]" /><meta property="og:image" content="[#CT_IMAGE#]" /><meta property="og:description" content="[#CT_DESCRIPTION#]" /><meta property="og:site_name" content="[#SITE_NAME#]" />';	// デフォルトのヘッダ出力(詳細表示)
	
	/**
	 * 汎用コンテンツ定義値をDBから取得
	 *
	 * @param object $db	DBオブジェクト
	 * @return array		取得データ
	 */
	static function loadConfig($db)
	{
		$retVal = array();

		// 汎用コンテンツ定義を読み込み
		$ret = $db->getAllConfig(self::$_contentType, $rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['ng_id'];
				$value = $rows[$i]['ng_value'];
				$retVal[$key] = $value;
			}
		}
		return $retVal;
	}
	/**
	 * 添付ファイル格納ディレクトリ取得
	 *
	 * @return string		ディレクトリパス
	 */
	static function getAttachFileDir()
	{
		global $gEnvManager;
		$dir = $gEnvManager->getIncludePath() . self::ATTACH_FILE_DIR;
		if (!file_exists($dir)) mkdir($dir, M3_SYSTEM_DIR_PERMISSION, true/*再帰的*/);
		return $dir;
	}
	/**
	 * レイアウトからユーザ定義フィールドを取得
	 *
	 * @param string $src			変換するデータ
	 * @return array				フィールドID
	 */
/*	static function parseUserMacro($src)
	{
		$fields = array();
		$pattern = '/' . preg_quote(M3_TAG_START . M3_TAG_MACRO_USER_KEY) . '([A-Z0-9_]+):?(.*?)' . preg_quote(M3_TAG_END) . '/u';
		preg_match_all($pattern, $src, $matches, PREG_SET_ORDER);
		for ($i = 0; $i < count($matches); $i++){
			$key = M3_TAG_MACRO_USER_KEY . $matches[$i][1];
			$value = $matches[$i][2];
			if (!array_key_exists($key, $fields)) $fields[$key] = $value;
		}
		return $fields;
	}*/
	static function parseUserMacro($src)
	{
		global $gInstanceManager;
		static $fields;
		
		if (!isset($fields)) $fields = $gInstanceManager->getTextConvManager()->parseUserMacro($src);
		return $fields;
	}
}
?>
