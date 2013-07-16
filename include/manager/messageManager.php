<?php
/**
 * メッセージ管理マネージャー
 *
 * 画面に出力するユーザ向けのメッセージをグローバルで管理する
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: messageManager.php 5728 2013-02-25 09:14:19Z fishbone $
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class MessageManager extends Core
{
	private $db;						// DBオブジェクト
	private $errorMessage    = array();		// アプリケーションのエラー
	private $warningMessage  = array();		// ユーザ操作の誤り
	private $guidanceMessage = array();		// ガイダンス
	private $_langStringArray = array();		// 読み込んだ言語テキスト
	private $langStringLoaded;		// 初期データを読み込んだかどうか
	private $loadedLang;			// データを読み込んだ言語ID
	private $loadedLocaleText = array();		// 各言語対応テキスト
	private $loadedGlobalLocaleText;		// 各言語対応テキスト(システム用)
	const MSG_SITE_IN_MAINTENANCE = 'msg_site_in_maintenance';			// サイトメンテナンス中メッセージ
	const MSG_ACCESS_DENY = 'msg_access_deny';							// アクセス不可メッセージ
	const MSG_PAGE_NOT_FOUND = 'msg_page_not_found';							// ページが見つからないメッセージ
	const DEFAULT_MSG_SITE_IN_MAINTENANCE = 'ただいまサイトのメンテナンス中です';// サイトメンテナンス中メッセージのデフォルト値
	const DEFAULT_MSG_ACCESS_DENY = 'アクセスできません';						// アクセス不可メッセージのデフォルト値
	const DEFAULT_MSG_PAGE_NOT_FOUND = 'ページが見つかりません';						// ページが見つからないメッセージのデフォルト値
	const DEFAULT_WORD_UNTITLED = '[未設定]';		// 用語未設定
	const LOCALE_TEXT_FILE_GETTEXT = '/php-gettext-1.0.10/gettext.php';			// 各言語対応テキスト処理用
	const LOCALE_TEXT_FILE_STREAMS = '/php-gettext-1.0.10/streams.php';			// 各言語対応テキスト処理用
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
	}
	/**
	 * メッセージを追加する
	 *
	 * @param array $errorMessage		エラーメッセージ
	 * @param array $warningMessage		ユーザ操作のエラー
	 * @param array $guidanceMessage	ガイダンス
	 * @return 				なし
	 */
	function addMessage($errorMessage, $warningMessage, $guidanceMessage)
	{
		$this->errorMessage		= array_merge($this->errorMessage, $errorMessage);		// アプリケーションエラー
		$this->warningMessage	= array_merge($this->warningMessage, $warningMessage);	// ユーザ操作のエラー
		$this->guidanceMessage	= array_merge($this->guidanceMessage, $guidanceMessage);	// ガイダンス
	}
	/**
	 * アプリケーションエラーメッセージを取得
	 *
	 * @return array	アプリケーションエラーメッセージ
	 */
	function getErrorMessage()
	{	
		return $this->errorMessage;
	}
	/**
	 * ユーザ操作のエラーメッセージを取得
	 *
	 * @return array	ユーザ操作のエラーメッセージ
	 */
	function getWarningMessage()
	{	
		return $this->warningMessage;
	}
	/**
	 * ガイダンスメッセージを取得
	 *
	 * @return array	ガイダンスメッセージ
	 */
	function getGuidanceMessage()
	{	
		return $this->guidanceMessage;
	}
	/**
	 * 言語テキスト定義をDBから取得
	 *
	 * @param string $lang	言語ID
	 * @return bool			true=取得実行、false=取得なし
	 */
	function _loadLangString($lang)
	{
		// 初期化終了で、言語に変更がない場合は終了
		if (!empty($this->langStringLoaded) && $this->loadedLang == $lang) return false;
		
		$this->_langStringArray = array();		// 読み込んだ言語テキスト

		// 言語定義を読み込み
		$ret = $this->db->getAllLangString($lang, $rows);
		if ($ret){
			// 取得データを連想配列にする
			$configCount = count($rows);
			for ($i = 0; $i < $configCount; $i++){
				$key = $rows[$i]['ls_id'];
				$value = $rows[$i]['ls_value'];
				$this->_langStringArray[$key] = $value;
			}
		} else {
			$this->gLog->error(__METHOD__, 'DBエラー発生: 言語定義(_language_string)が読み込めません。');
			return false;
		}
		$this->loadedLang = $lang;			// データを読み込んだ言語ID
		$this->langStringLoaded = true;
		return true;
	}
	/**
	 * 言語定義の再読み込みを指示
	 *
	 * @return 			なし
	 */
	function reloadMessage()
	{
		$this->langStringLoaded = false;
	}
	/**
	 * メッセージ定義値を取得
	 *
	 * @param string $key    キーとなる項目値
	 * @param string $lang	言語ID(空の場合は現在の言語ID)
	 * @return string		値
	 */
	function getMessage($key, $lang = '')
	{
		if (empty($lang)) $lang = $this->gEnv->getCurrentLanguage();
		
		// 初期データ読み込み
		$this->_loadLangString($lang);
		
		$value = $this->_langStringArray[$key];
		if (!isset($value)){
			// デフォルト値を取得
			switch ($key){
				case self::MSG_SITE_IN_MAINTENANCE:			// サイトメンテナンス中メッセージ
					$value = self::DEFAULT_MSG_SITE_IN_MAINTENANCE;// サイトメンテナンス中メッセージのデフォルト値
					break;
				case self::MSG_ACCESS_DENY:					// アクセス不可メッセージ
					$value = self::DEFAULT_MSG_ACCESS_DENY;			// アクセス不可メッセージのデフォルト値
					break;
				case self::MSG_PAGE_NOT_FOUND:				// ページが見つからないメッセージ
					$value = self::DEFAULT_MSG_PAGE_NOT_FOUND;			// ページが見つからないメッセージのデフォルト値
					break;
				default:
					$value = '';
					break;
			}
		}
		return $value;
	}
	/**
	 * メッセージ定義値を更新
	 *
	 * @param string $key	キーとなる項目値
	 * @param string $value	更新値
	 * @param string $lang	言語ID(空の場合は現在の言語ID)
	 * @return bool			true=更新成功、false=更新失敗
	 */
	function updateMessage($key, $value, $lang = '')
	{
		if (empty($lang)) $lang = $this->gEnv->getCurrentLanguage();
		
		$ret = $this->db->updateLangString($lang, $key, $value);
		return $ret;
	}
	/**
	 * ウィジェット単位の各言語対応のテキストをロード
	 *
	 * @param string $widgetId	ウィジェットID
	 * @param string $localeId	ロケールID
	 * @param string $filenameOption	ファイル名オプション
	 * @param string $type		取得タイプ
	 * @return bool			true=ロード成功、false=ロード失敗
	 */
	function loadLocaleText($widgetId, $localeId, $filenameOption = '', $type = 'default')
	{
		// テキスト処理ライブラリを読み込む
		require_once($this->gEnv->getLibPath() . self::LOCALE_TEXT_FILE_GETTEXT);
		require_once($this->gEnv->getLibPath() . self::LOCALE_TEXT_FILE_STREAMS);

		$filename = $localeId . '.mo';
		if (!empty($filenameOption)) $filename = $filenameOption . '.' . $filename;
		$file = $this->gEnv->getWidgetsPath() . '/' . $widgetId . '/' . M3_DIR_NAME_INCLUDE . '/' . M3_DIR_NAME_LOCALE . '/' . $filename;
		if (is_readable($file)){
			$input = new CachedFileReader($file);
			$this->loadedLocaleText[$type] = new gettext_reader($input);
			return true;
		} else {
			$this->loadedLocaleText[$type] = NULL;
			return false;
		}
	}
	/**
	 * ウィジェット単位の各言語対応のテキストを取得
	 *
	 * @param string $widgetId	ウィジェットID
	 * @param string $id	メッセージID
	 * @param string $type		取得タイプ
	 * @return string		メッセージIDに対応したテキスト
	 */
	function getLocaleText($widgetId, $id, $type = 'default')
	{
		$dest = $id;
		if (isset($this->loadedLocaleText[$type])){
			$dest = $this->loadedLocaleText[$type]->translate($id);
		}
		return $dest;
	}
	/**
	 * システム共通の各言語対応のテキストをロード
	 *
	 * @param string $localeId	ロケールID
	 * @return bool			true=ロード成功、false=ロード失敗
	 */
	function loadGlobalLocaleText($localeId)
	{
		// テキスト処理ライブラリを読み込む
		require_once($this->gEnv->getLibPath() . self::LOCALE_TEXT_FILE_GETTEXT);
		require_once($this->gEnv->getLibPath() . self::LOCALE_TEXT_FILE_STREAMS);

		$file = $this->gEnv->getIncludePath() . '/' . M3_DIR_NAME_LOCALE . '/' . $localeId . '.mo';
		if (is_readable($file)){
			$input = new CachedFileReader($file);
			$this->loadedGlobalLocaleText = new gettext_reader($input);
			return true;
		} else {
			$this->loadedGlobalLocaleText = NULL;
			return false;
		}
	}
	/**
	 * システム共通の各言語対応のテキストを取得
	 *
	 * @param string $id	メッセージID
	 * @return string		メッセージIDに対応したテキスト
	 */
	function getGlobalLocaleText($id)
	{
		$dest = $id;
		if (isset($this->loadedGlobalLocaleText)){
			$dest = $this->loadedGlobalLocaleText->translate($id);
		}
		return $dest;
	}
	/**
	 * 用語定義値を取得
	 *
	 * @param string $key    キーとなる項目値
	 * @param string $lang	言語ID(空の場合は現在の言語ID)
	 * @return string		値
	 */
	function getWord($key, $lang = '')
	{
		if (empty($lang)) $lang = $this->gEnv->getCurrentLanguage();
		
		// 初期データ読み込み
		$this->_loadLangString($lang);
		
		$value = $this->_langStringArray[$key];
		if (!isset($value)) $value = self::DEFAULT_WORD_UNTITLED;		// 用語未設定
		return $value;
	}
	/**
	 * Joomla!用定義値を取得
	 *
	 * @param string $key    キーとなる項目値
	 * @param string $lang	言語ID(空の場合は現在の言語ID)
	 * @return string		値
	 */
	function getJoomlaText($key, $lang = '')
	{
		if (empty($lang)) $lang = $this->gEnv->getCurrentLanguage();
		
		// 初期データ読み込み
		$this->_loadLangString($lang);
		
		$value = $this->_langStringArray[$key];
		if (!isset($value)) $value = '';
		return $value;
	}
	/**
	 * Joomla!用定義値を変更
	 *
	 * @param string $key	キーとなる項目値
	 * @param string $value	変更値
	 * @param string $lang	言語ID(空の場合は現在の言語ID)
	 * @return 				なし
	 */
	function replaceJoomlaText($key, $value, $lang = '')
	{
		if (empty($lang)) $lang = $this->gEnv->getCurrentLanguage();
		
		// 初期データ読み込み
		$this->_loadLangString($lang);
		
		$this->_langStringArray[$key] = $value;
	}
}
?>
