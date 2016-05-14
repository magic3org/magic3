<?php
/**
 * アクセス解析マネージャー
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2016 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');
require_once(M3_SYSTEM_INCLUDE_PATH . '/db/analyticsDb.php');

class AnalyzeManager extends Core
{
	private $db;						// DBオブジェクト
	private $analyticsDb;
	const CF_LAST_DATE_CALC_PV	= 'last_date_calc_pv';	// ページビュー集計の最終更新日
	const MAX_CALC_DAYS = 30;					// 最大集計日数
	const CRAWLER_DETECT_SCRIPT_DIR = '/Crawler-Detect-1.2.2/';		// クローラー解析スクリプトディレクトリ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// システムDBオブジェクト取得
		$this->db = $this->gInstance->getSytemDbObject();
		$this->analyticsDb = new analyticsDb();
	}
	/**
	 * コンテンツのビューカウントを更新
	 *
	 * @param string    $typeId				コンテンツタイプ
	 * @param int     	$serial				コンテンツシリアル番号(0のときはコンテンツIDを使用)
	 * @param string	$day				日にち
	 * @param int		$hour				時間
	 * @param string    $contentId			コンテンツID
	 * @return bool							true=成功, false=失敗
	 */
	function updateContentViewCount($typeId, $serial, $day = null, $hour = null, $contentId = '')
	{
		$ret = $this->db->updateViewCount($typeId, $serial, $contentId, $day, $hour);
		return $ret;
	}
	/**
	 * コンテンツのビューカウントを取得
	 *
	 * @param string    $typeId				コンテンツタイプ
	 * @param int $serial					コンテンツシリアル番号(0のときはコンテンツIDを使用)
	 * @param string    $contentId			コンテンツID
	 * @return int							総参照数
	 */
	function getTotalContentViewCount($typeId, $serial, $contentId = '')
	{
		$count = $this->db->getTotalViewCount($typeId, $serial, $contentId);
		return $count;
	}
	/**
	 * コンテンツ参照を記録
	 *
	 * @param string    $typeId				コンテンツタイプ
	 * @param int     	$serial				コンテンツシリアル番号(0のときはコンテンツIDを使用)
	 * @param string    $contentId			コンテンツID
	 * @return bool							true=記録、false=記録しない
	 */
	function logContentView($typeId, $serial, $contentId = '')
	{
		static $day;
		static $hour;
		
		// パラメータエラーチェック
		$serial = intval($serial);
		if ($serial <= 0) return false;
		
		// コンテンツ参照がしない場合は終了
		if (!$this->canRegistContentView()) return false;

		if (!isset($day)) $day = date("Y/m/d");		// 日
		if (!isset($hour)) $hour = (int)date("H");		// 時間

		$ret = $this->db->updateViewCount($typeId, $serial, $contentId, $day, $hour);
		return $ret;
	}
	/**
	 * 検索キーワードログを記録
	 *
	 * @param string    $widgetId			ウィジェットID
	 * @param string    $keyword			検索キーワード
	 * @return bool							true=記録、false=記録しない
	 */
	function logSearchWord($widgetId, $keyword)
	{
		// 引数エラーチェック
		if (empty($keyword)) return false;
		
		// 端末タイプを判定
		$deviceType = 0;	// 端末をPCに初期化
		if ($this->gEnv->isMobile()){		// 携帯のとき
			$deviceType = 1;
		}		// スマートフォンのとき
		
		// クライアントIDを取得
		$cid = '';
		switch ($deviceType){
			case 0:			// PC
			case 2:			// スマートフォン
			default:
				$cid = $this->gAccess->getClientId();// クッキー値のクライアントID
				break;
			case 1:			// 携帯
				$cid = $this->gEnv->getMobileId();	// 携帯端末ID
				break;
		}
		
		// スペース区切りの場合はワードを分割
		// 全角英数を半角に、半角カナ全角ひらがなを全角カナに変換
		$basicWord = $keyword;
		if (function_exists('mb_convert_kana')) $basicWord = mb_convert_kana($basicWord, 'aKCV');
		$basicWord = strtolower($basicWord);		// 大文字を小文字に変換
		
		// アクセスパスを取得
		$path = $this->gEnv->getAccessPath();
		
		// 検索キーワードログ書き込み
		$this->db->writeSearchWordLog($deviceType, $cid, $widgetId, $keyword, $basicWord, $path);
		return true;
	}
	/**
	 * コンテンツダウンロードログを記録
	 *
	 * @param string    $contentType		コンテンツタイプ
	 * @param string    $contentId			コンテンツID
	 * @return bool							true=記録、false=記録しない
	 */
	function logContentDownload($contentType, $contentId)
	{
		// ダウンロードログ書き込み
		$ret = $this->db->writeDownloadLog($contentType, $contentId);
		return $ret;
	}
	/**
	 * ブラウザのタイプを取得
	 *
	 * @param string $agent		解析元の文字列。HTTP_USER_AGENTの値。
	 * @param string $version	ブラウザのバージョン
	 * @return string			ブラウザのタイプコード。不明のときは空文字列
	 */
	public function getBrowserType($agent, &$version)
	{
		require_once(M3_SYSTEM_LIB_PATH . '/Net_UserAgent_Mobile-1.0.0/Net/UserAgent/Mobile.php');
		$parser = Net_UserAgent_Mobile::singleton($agent);
		
		if ($parser->isNonMobile()){		// 携帯以外のとき
			require_once(M3_SYSTEM_LIB_PATH . '/UserAgentParser.php');
			$retObj = UserAgentParser::getBrowser($agent);
			if ($retObj === false){
				return '';
			} else {
				$version = $retObj['version'];
				return $retObj['id'];
			}
		} else {		// 携帯のとき
			if ($parser->isDoCoMo()){	// ドコモ端末のとき
				return 'DC';
			} else if ($parser->isEZweb()){	// au端末のとき
				return 'AU';
			} else if ($parser->isSoftBank()){	// ソフトバンク端末のとき
				return 'SB';
			} else {
				return '';
			}
		}
	}
	/**
	 * プラットフォーム(OSまたは携帯機種)のタイプを取得
	 *
	 * @param string $agent		解析元の文字列。HTTP_USER_AGENTの値。
	 * @return string			プラットフォームのタイプコード
	 */
	public function getPlatformType($agent)
	{
		require_once(M3_SYSTEM_LIB_PATH . '/Net_UserAgent_Mobile-1.0.0/Net/UserAgent/Mobile.php');
		$parser = Net_UserAgent_Mobile::singleton($agent);
		
		if ($parser->isNonMobile()){		// 携帯以外のとき
			require_once(M3_SYSTEM_LIB_PATH . '/UserAgentParser.php');
			$retObj = UserAgentParser::getOperatingSystem($agent);
			if ($retObj === false){
				return '';
			} else {
				return $retObj['id'];
			}
		} else {		// 携帯のとき
			return $parser->getModel();		// 機種を取得
		}
	}
	/**
	 * ブラウザの言語から国コードを取得
	 *
	 * @param string $lang		解析元の文字列。HTTP_ACCEPT_LANGUAGEの値
	 * @return string			国コード
	 */
	public function getBrowserCountryCode($lang)
	{
		$lang = $this->parseBrowserLanguage($lang);
		$tmpLang = explode(',', $lang);
		return $this->getCountryCodeFromBrowserLanguage($tmpLang[0]);
	}
	/**
	 * ブラウザの言語を取得
	 *
	 * @param string $lang		解析元の文字列。HTTP_ACCEPT_LANGUAGEの値 例)ja,en-us;q=0.7,en;q=0.3
	 * @return string			言語(カンマ区切り) 例)ja,en-us,en
	 */
	public function parseBrowserLanguage($lang)
	{
		$replacementPatterns = array(
				'/(\\\\.)/',     // quoted-pairs (RFC 3282)
				'/(\s+)/',       // CFWS white space
				'/(\([^)]*\))/', // CFWS comments
				'/(;q=[0-9.]+)/' // quality
			);

		$browserLang = strtolower(trim($lang));

		// language tags are case-insensitive per HTTP/1.1 s3.10 but the region may be capitalized per ISO3166-1;
		// underscores are not permitted per RFC 4646 or 4647 (which obsolete RFC 1766 and 3066),
		// but we guard against a bad user agent which naively uses its locale
		$browserLang = strtolower(str_replace('_', '-', $browserLang));

		// filters
		$browserLang = preg_replace($replacementPatterns, '', $browserLang);

		$browserLang = preg_replace('/((^|,)chrome:.*)/', '', $browserLang, 1); // Firefox bug
		$browserLang = preg_replace('/(,)(?:en-securid,)|(?:(^|,)en-securid(,|$))/', '\\1',	$browserLang, 1); // unregistered language tag

		$browserLang = str_replace('sr-sp', 'sr-rs', $browserLang); // unofficial (proposed) code in the wild
		return $browserLang;
	}
	/**
	 * ブラウザの言語から国コードを取得
	 *
	 * @param string $lang		解析元の文字列。HTTP_ACCEPT_LANGUAGEの値
	 * @return string			国コード
	 */
	public function getCountryCodeFromBrowserLanguage($browserLanguage)
	{
		global $COUNTRY_LIST, $LANGUAGE_TO_COUNTRY;

		require_once(M3_SYSTEM_INCLUDE_PATH . '/data/analyzeMap.php');// アクセス解析用マップ情報
		$validCountries = array_keys($COUNTRY_LIST);
		$langToCountry = array_keys($LANGUAGE_TO_COUNTRY);

		if (preg_match('/^([a-z]{2,3})(?:,|;|$)/', $browserLanguage, $matches)){
			// match language (without region) to infer the country of origin
			if(in_array($matches[1], $langToCountry)) return $LANGUAGE_TO_COUNTRY[$matches[1]];
		}
		if (!empty($validCountries) && preg_match_all("/[-]([a-z]{2})/", $browserLanguage, $matches, PREG_SET_ORDER)){
			foreach($matches as $parts){
				// match location; we don't make any inferences from the language
				if(in_array($parts[1], $validCountries)) return $parts[1];
			}
		}	
		return 'xx';
	}
	/**
	 * アクセスログからアクセス解析用のデータを作成
	 *
	 * @param array  	$message	エラーメッセージ
	 * @param int		$maxDay		最大集計日数
	 * @return bool					true=正常終了、false=異常終了
	 */
	function updateAnalyticsData(&$message = null, $maxDay = self::MAX_CALC_DAYS)
	{
		$ret = $this->analyticsDb->getOldAccessLog($row);
		if ($ret){		// 集計対象のデータが存在するとき
			$startDate = date("Y/m/d", strtotime($row['al_dt']));
			$lastDate = $this->analyticsDb->getStatus(self::CF_LAST_DATE_CALC_PV);

			// 集計開始日を求める
			if (!empty($lastDate)){
				$startDate = date("Y/m/d", strtotime("$lastDate 1 day"));		// 翌日
			}
			// 集計終了日を求める
			$endDate = date("Y/m/d", strtotime("-1 day"));	// 前日
			$endTime = strtotime($endDate);
		
			// 集計処理を行う
			$dayCount = 0;		// 集計日数
			$date = $startDate;
			while (true){
				if (strtotime($date) > $endTime){
					if (!is_null($message)) $message[] = '集計完了しました';
					break;
				}
				// トランザクションスタート
				$this->analyticsDb->startTransaction();

				$ret = $this->analyticsDb->calcDatePv($date);
				
				// 集計日付を更新
				if ($ret) $ret = $this->analyticsDb->updateStatus(self::CF_LAST_DATE_CALC_PV, $date);
				
				// トランザクション終了
				$this->analyticsDb->endTransaction();

				// エラーの場合は終了
				if (!$ret){
					if (!is_null($message)) $message[] = 'エラーが発生しました';
					break;
				}
				
				// 集計日数を更新
				$dayCount++;
				if ($dayCount >= $maxDay){
					if (!is_null($message)) $message[] = $maxDay . '日分の集計完了しました';
					break;
				}
				
				$date = date("Y/m/d", strtotime("$date 1 day"));
			}
		} else {				// 集計データがないとき
			if (!is_null($message)) $message[] = '集計対象のアクセスログがありません';
			
			$ret = true;		// 正常終了
		}
		return $ret;
	}
	/**
	 * アクセスログの即時アクセス解析
	 *
	 * @param int $logSerial		アクセスログシリアル番号
	 * @param string $cookieValue	アクセス管理用クッキー値
	 * @return bool					true=成功、false=失敗
	 */
	function realtimeAnalytics($logSerial, $cookieValue)
	{
		global $gRequestManager;
		global $gEnvManager;
		
		$uri		= $gRequestManager->trimServerValueOf('REQUEST_URI');
		$referer	= $gRequestManager->trimServerValueOf('HTTP_REFERER');
		$agent		= $gRequestManager->trimServerValueOf('HTTP_USER_AGENT');		// ユーザエージェント
		$language	= $gRequestManager->trimServerValueOf('HTTP_ACCEPT_LANGUAGE');	// クライアント認識可能言語
		
		// 最初のアクセスかどうか確認(アクセス管理用クッキー値が存在するかどうか)
		$isFirstAccess = false;		// 最初のアクセスかどうか
		$ret = $this->analyticsDb->isExistsCookieValueInAccessLog($cookieValue, $logSerial);
		if (!$ret) $isFirstAccess = true;
		
		// クローラーかどうかをチェック
/*		$isCrawler = false;
		if (empty($agent)){		// ユーザエージェントが設定されていないものはクローラーと見なす
			$isCrawler = true;
		} else {
			$crawlerArray = array('bot', 'crawl');
			foreach ($crawlerArray as $value){
				if (preg_match('/' . preg_quote($value, '/') . '/i', $agent)){
					$isCrawler = true;
					break;
				}
			}
		}*/
		require_once($gEnvManager->getLibPath() . self::CRAWLER_DETECT_SCRIPT_DIR . 'CrawlerDetect.php');
		require_once($gEnvManager->getLibPath() . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/AbstractProvider.php');
		require_once($gEnvManager->getLibPath() . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/Crawlers.php');
		require_once($gEnvManager->getLibPath() . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/Exclusions.php');
		require_once($gEnvManager->getLibPath() . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/Headers.php');
		
		$CrawlerDetect = new Jaybizzle\CrawlerDetect\CrawlerDetect;
		$isCrawler = false;
		if ($CrawlerDetect->isCrawler()) $isCrawler = true;
		
		// アクセスログを更新
		$ret = $this->analyticsDb->updateAccessLog($logSerial, $isFirstAccess, $isCrawler);
		
		return $ret;
	}
	/**
	 * コンテンツのアクセスログを記録するかどうかを取得
	 *
	 * @return bool			true=記録する、false=記録しない
	 */
	public function canRegistContentView()
	{
		global $gRequestManager;
		
		static $canRegist;
		
		if (!isset($canRegist)){
			if (!$this->gEnv->isSystemManageUser() &&			// システム運用者以上の場合はカウントしない
				!$gRequestManager->isCmdAccess()){				// cmd付きアクセスでない
				$canRegist = true;
			} else {
				$canRegist = false;
			}
		}
		return $canRegist;
	}
}
?>
