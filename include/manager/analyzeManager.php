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
 * @copyright  Copyright 2006-2017 Magic3 Project.
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
	const NOT_FOUND_BROWSER_IMAGE = 'noimage.png';			// ブラウザアイコンが見つからなかった場合のアイコン
	const CF_LAST_DATE_CALC_PV	= 'last_date_calc_pv';	// ページビュー集計の最終更新日
	const MAX_CALC_DAYS = 30;					// 最大集計日数
//	const CRAWLER_DETECT_SCRIPT_DIR = '/Crawler-Detect-1.2.20/';		// クローラー解析スクリプトディレクトリ
	const CRAWLER_DETECT_SCRIPT_DIR = '/Crawler-Detect-1.2.40/';		// クローラー解析スクリプトディレクトリ
	const USER_AGENT_SCRIPT = '/Net_UserAgent_Mobile-1.0.0/Net/UserAgent/Mobile.php';		// ユーザエージェント解析用スクリプト
	const BROWSER_DETECT_SCRIPT = '/PhpUserAgent-0.5.2/UserAgentParser.php';		// ブラウザ判定スクリプト
	const PLATFORM_DETECT_SCRIPT_DIR = '/php-browser-detector-6.0.5/';		// プラットフォーム判定スクリプト
	
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
	 * @param int     	$serial				コンテンツシリアル番号
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
		
		// コンテンツ参照ない場合は終了
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
	 * (注意)クローラーがシュミレートしている場合はクローラーと判定する
	 *
	 * @param string $agent		解析元の文字列。HTTP_USER_AGENTの値。
	 * @return array			ブラウザ情報
	 */
	public function getBrowserType($agent)
	{
/*
//$agent = 'DoCoMo/2.0 N905i(c100;TB;W24H16) (compatible; Googlebot-Mobile/2.1; +http://www.google.com/bot.html)';		// google 携帯シュミレート
$agent = 'Mozilla/5.0 (compatible; Steeler/3.5; http://www.tkl.iis.u-tokyo.ac.jp/~crawler/)';		// クローラー
//
//$agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)';
$agent = 'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko';
$agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393';		// edge
$agent = 'Mozilla/5.0 (iPad; CPU OS 10_0_1 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Mobile/14A403 Safari/602.1';		// ipad
$agent = 'Mozilla/5.0 (Linux; U; Android 1.6; ja-jp; Docomo HT-03A Build/DRD08) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1';	// android
$agent = 'SoftBank/1.0/831SH/SHJ003/SN123456789012345 Browser/NetFront/3.5 Profile/MIDP-2.0 Configuration/CLDC-1.1';	// SoftBank携帯

$agent = 'Mozilla/5.0 (PlayStation Vita 3.01) AppleWebKit/536.26 (KHTML, like Gecko) Silk/3.2';
$agent = 'SAMSUNG-GT-C3303/C3303DDJG4 NetFront/3.5 Profile/MIDP-2.0 Configuration/CLDC-1.1';
//$agent = 'Mozilla/5.0 (Linux; U; Android 4.0.3; ja-jp; SC-02C Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';		// Android標準
//$agent = 'Mozilla/5.0 (Linux; Android 4.0.3; SC-02C Build/IML74K) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.58 Mobile Safari/537.31';		// Android Chrome
$agent = 'Mozilla/4.0 (PS2; PlayStation BB Navigator 1.0) NetFront/3.0';
$agent = 'Opera/9.80 (Android; Opera Mini/6.5.27452/27.1324; U; ja) Presto/2.8.119 Version/11.10';
$agent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.122 Safari/537.36 Vivaldi/1.4.589.29';		// Vivaldi
$agent = 'Mozilla/5.0 (PlayStation 4 1.70) AppleWebKit/536.26 (KHTML, like Gecko)';			// Playstation4
$agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36';
$agent = 'Mozilla/5.0 (Mobile; Windows Phone 8.1; Android 4.0; ARM; Trident/7.0; Touch; rv:11.0; IEMobile/11.0; NOKIA; Lumia 1320) like iPhone OS 7_0_3 Mac OS X AppleWebKit/537 (KHTML, like Gecko) Mobile Safari/537';	// Windows Phone(new) -> NG
$agent = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; FujitsuToshibaMobileCommun; IS12T; KDDI)';	// Windows Phone(old)
$agent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36 OPR/42.0.2393.94';		// Opera Next
// ***** 検出失敗(2016/10/19) *****
$agent = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 10.0; WOW64; Trident/7.0; Sleipnir6/6.2.1; SleipnirSiteUpdates/6.2.1)';		// Sleipnir RSS取得用
$agent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36 Sleipnir/6.2.1';		// Sleipnir
*/
		$resultObj = array();

		// クローラーかどうかのチェック
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'CrawlerDetect.php');
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/AbstractProvider.php');
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/Crawlers.php');
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/Exclusions.php');
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/Headers.php');
		
		$crawlerDetect = new Jaybizzle\CrawlerDetect\CrawlerDetect;
		if ($crawlerDetect->isCrawler($agent)){		// クローラー検出の場合
			$crawlerName = $crawlerDetect->getMatches();
			$resultObj['name'] = strval($crawlerName);
			$resultObj['browser'] = strtolower($crawlerName);
//				$resultObj['version'] = strtolower($infoObj['version']);
			$crawlerIcon = $this->_getBrowserIconFile($resultObj['browser']);
			if (empty($crawlerIcon)) $crawlerIcon = $this->_getBrowserIconFile('crawler');		// クローラーデフォルトアイコン
			$resultObj['icon'] = $crawlerIcon;
		} else {
			// 携帯かどうかのチェック
			require_once(M3_SYSTEM_LIB_PATH . self::USER_AGENT_SCRIPT);
			
			$parser = Net_UserAgent_Mobile::singleton($agent);
			
			if ($parser->isNonMobile()){		// 携帯以外のとき
				// ブラウザを解析
				require_once(M3_SYSTEM_LIB_PATH . self::BROWSER_DETECT_SCRIPT);
				
				$infoObj = parse_user_agent($agent);
				$resultObj['name'] = strval($infoObj['browser']);
				$resultObj['platform'] = strtolower($infoObj['platform']);
				$resultObj['browser'] = strtolower($infoObj['browser']);
				$resultObj['version'] = strtolower($infoObj['version']);
				$resultObj['icon'] = $this->_getBrowserIconFile($resultObj['browser']);
				if (empty($resultObj['icon'])) $resultObj['icon'] = self::NOT_FOUND_BROWSER_IMAGE;			// ブラウザアイコンが見つからなかった場合
				/*
				require_once(M3_SYSTEM_LIB_PATH . '/UserAgentParser.php');
				$retObj = UserAgentParser::getBrowser($agent);
//				if ($retObj === false){
//					return '';
//				} else {
//					$version = $retObj['version'];
//					return $retObj['id'];
//				}
*/
			} else {		// 携帯(ガラケー)のとき
				if ($parser->isDoCoMo()){	// ドコモ端末のとき
					$resultObj['name'] = 'DoCoMo';
					$resultObj['icon'] = $this->_getBrowserIconFile('docomo');
				} else if ($parser->isEZweb()){	// au端末のとき
					$resultObj['name'] = 'au';
					$resultObj['icon'] = $this->_getBrowserIconFile('au');
				} else if ($parser->isSoftBank()){	// ソフトバンク端末のとき
					$resultObj['name'] = 'SoftBank';
					$resultObj['icon'] = $this->_getBrowserIconFile('softbank');
				} else if ($parser->isWillcom()){	// WILLCOM端末のとき
					$resultObj['name'] = 'WILLCOM';
					$resultObj['icon'] = $this->_getBrowserIconFile('willcom');
				}
			}
		}
		return $resultObj;
	}
	/**
	 * プラットフォーム(OSまたは携帯機種)のタイプを取得
	 *
	 * @param string $agent		解析元の文字列。HTTP_USER_AGENTの値。
	 * @return array			プラットフォーム情報
	 */
	public function getPlatformType($agent)
	{
/*		require_once(M3_SYSTEM_LIB_PATH . self::USER_AGENT_SCRIPT);
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
		}*/
		$resultObj = array();
		
		require_once(M3_SYSTEM_LIB_PATH . self::PLATFORM_DETECT_SCRIPT_DIR . 'DetectorInterface.php');
		require_once(M3_SYSTEM_LIB_PATH . self::PLATFORM_DETECT_SCRIPT_DIR . 'BrowserDetector.php');
		require_once(M3_SYSTEM_LIB_PATH . self::PLATFORM_DETECT_SCRIPT_DIR . 'UserAgent.php');
		require_once(M3_SYSTEM_LIB_PATH . self::PLATFORM_DETECT_SCRIPT_DIR . 'OsDetector.php');
		require_once(M3_SYSTEM_LIB_PATH . self::PLATFORM_DETECT_SCRIPT_DIR . 'Os.php');

		$osInfo = new Sinergi\BrowserDetector\Os($agent);
		$resultObj['name'] = strval($osInfo->getName());
		$resultObj['platform'] = strtolower($resultObj['name']);
		$resultObj['version_name'] = strval($osInfo->getVersion());
		$resultObj['version'] = strtolower($resultObj['version_name']);
		$resultObj['icon'] = $this->_getOsIconFile($resultObj['platform']);
		return $resultObj;
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
//		global $gEnvManager;
		
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
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'CrawlerDetect.php');
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/AbstractProvider.php');
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/Crawlers.php');
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/Exclusions.php');
		require_once(M3_SYSTEM_LIB_PATH . self::CRAWLER_DETECT_SCRIPT_DIR . 'Fixtures/Headers.php');
		
		$crawlerDetect = new Jaybizzle\CrawlerDetect\CrawlerDetect;
		$isCrawler = false;
		if ($crawlerDetect->isCrawler()) $isCrawler = true;
		
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
	/**
	 * ブラウザアイコンファイル名を取得
	 *
	 * @param string  $type		ブラウザ種別
	 * @return string			ファイル名(該当なしの場合は空文字列)
	 */
	public function _getBrowserIconFile($type)
	{
		// ブラウザアイコンファイル名
		static $browserIconFile = array(
			'opera'							=> 'opera.png',
			'opera next'					=> 'operanext.png',
			'silk'							=> 'silk.png',
			'msie'							=> 'ie.png',
			'microsoft internet explorer'	=> 'ie.png',
			'internet explorer'				=> 'ie.png',
			'edge'							=> 'edge.png',
			'android browser'				=> 'android.png',
			'netscape6'						=> 'netscape.png',
			'netscape'						=> 'netscape.png',
			'netfront'						=> 'netfront.gif',
			'galeon'						=> 'galeon.png',
			'phoenix'						=> 'phoenix.png',
			'firefox'						=> 'firefox.png',
			'mozilla firebird'				=> 'firebird.png',
			'firebird'						=> 'firebird.png',
			'seamonkey'						=> 'seamonkey.png',
			'camino'						=> 'camino.png',
			'safari'						=> 'safari.png',
			'chrome'						=> 'chrome.gif',
			'k-meleon'						=> 'k-meleon.png',
			'mozilla'						=> 'mozilla.gif',
			'konqueror'						=> 'konqueror.png',
			'blackberry'					=> '',
			'icab'							=> 'icab.png',
			'lynx'							=> '',
			'links'							=> '',
			'ncsa mosaic'					=> '',
			'amaya'							=> '',
			'omniweb'						=> 'omniweb.png',
			'hotjava'						=> '',
			'browsex'						=> '',
			'amigavoyager'					=> '',
			'amiga-aweb'					=> '',
			'ibrowse'						=> '',
			'arora'							=> '',
			'epiphany'						=> 'epiphany.png',
			'flock'							=> 'flock.png',
			'sleipnir'						=> 'sleipnir.gif',
			'lunascape'						=> 'lunascape.gif',
			'shiira'						=> 'shiira.gif',
			'swift'							=> 'swift.png',
			'wamcom.org'					=> '',
			'playstation portable'			=> 'playstation.gif',
			'scej psp browser'				=> '',	// ワイプアウトピュア
			'w3m'							=> '',
			'netcaptor'						=> 'netcaptor.gif',
			'webtv'							=> 'webtv.gif',
			'vivaldi'						=> 'vivaldi.png',
			
			// ダウンローダ
			'freshreader'					=> '',
			'pockey'						=> '',		// GetHTMLW
			'wwwc'							=> '',
			'wwwd'							=> '',
			'flashget'						=> '',
			'download ninja'				=> '',	// ダウンロードNinja
			'webauto'						=> '',
			'arachmo'						=> '',
			'wget'							=> '',
			
			// RSSリーダー
			'simplepie'						=> 'simplepie.png',
			
			// クローラー
			'googlebot'					=> 'google.gif',	// Google
			'googlebot-mobile'			=> 'google.gif',	// Google-Mobile
			'mediapartners-google'		=> 'google.gif',	// Google
			'msnbot'					=> 'msn.gif',	// MSN
			'msnbot-media'				=> 'msn.gif',	// MSN
			'yahooseeker'				=> 'yahoo.gif',	// YahooSeeker
			'slurp'						=> 'yahoo.gif',	// Yahoo!
			'yahoo! de slurp'			=> 'yahoo.gif',	// Yahoo!
			'yahoo! slurp'				=> 'yahoo.gif',	// Yahoo!
			'zyborg'					=> '',	// InfoSeek
			'infoseek'					=> '',	// InfoSeek
			'slurp.so/goo; slurp'		=> 'goo.gif',	// goo
			'mogimogi'					=> 'goo.gif',	// goo
			'moget'						=> 'goo.gif',	// goo
			'ichiro'					=> 'goo.gif',	// goo
			'baiduspider'				=> 'baidu.png',	// 百度
			'baiduspider+'				=> 'baidu.png',	// 百度
			'sogou web spider'			=> '',			// 搜狗
			'asahina-antenna'			=> '',			// 朝日奈アンテナ
			'hatena antenna'			=> 'hatena.gif',	// はてなアンテナ
			'yeti'						=> 'naver.gif',	// Naver(韓国)
			'icc-crawler'				=> 'nict.gif',	// 独立行政法人情報通信研究機構
			'dotbot'					=> 'dotbot.gif',	// Dotbot
			'speedy spider'				=> 'entireweb.png',	// Entireweb
			'turnitinbot'				=> 'turnitinbot.png',	// TurnitinBot
			'bingbot'					=> 'bing.png',	// Bing
			'bingpreview'				=> 'bing.png',	// Bing
			'yacybot'					=> 'yacy.png',	// YaCy
			'mj12bot'					=> 'mj12bot.png',
			'ahrefsbot'					=> 'ahrefs.png',
			'semrushbot'				=> 'semrush.png',
			'scrapy'					=> 'scrapy.png',
			'deusu'						=> 'deusu.png',
			
			// クローラーその他
			'msproxy'					=> '',	// ProxyServer
			'spacebison'				=> '',	// Proxomitron
			'bookmark renewal'			=> '',	// Bookまーく
			'hatenascreenshot'			=> '',	// はてなスクリーンショット
			'monazilla'					=> '',
			'crawler'					=> 'crawler.png',			// クローラー該当なしの場合

			// 携帯
			'docomo'					=> 'docomo.gif',		// ドコモ
			'au'						=> 'au.gif',		// au
			'softbank'					=> 'softbank.gif',		// ソフトバンク
			'willcom'					=> 'willcom.gif',		// WILLCOM
		);
		$filename = $browserIconFile[$type];
		if (!isset($filename)) $filename = '';
		return $filename;
	}
	/**
	 * OSアイコンファイル名を取得
	 *
	 * @param string  $type		OS種別
	 * @return string			ファイル名(該当なしの場合は空文字列)
	 */
	public function _getOsIconFile($type)
	{
		// OSアイコンファイル名
		static $osIconFile = array(
/*			'IPD' => '',	// iPod
			'IPH' => '',	// iPhone
			'WII' => '',	// Nintendo Wii
			'PSP' => '',	// PlayStation Portable
			'PS3' => '',	// PlayStation 3
			'AND' => '',	// Android
			'POS' => '',	// PalmOS
			'BLB' => '',	// BlackBerry
			'WI7' => 'winvista.gif',	// Windows NT 6.1, Windows 7
			'WVI' => 'winvista.gif',	// Windows NT 6.0, Windows Vista
			'WS3' => 'win.gif',	// Windows NT 5.2, Windows Server 2003
			'WXP' => 'win.gif',	// Windows NT 5.1, Windows XP
			'W98' => 'win98.gif',	// Windows 98
			'W2K' => 'win.gif',	// Windows NT 5.0, Windows 2000
			'WNT' => 'win98.gif',	// Windows NT 4.0
			'WME' => 'win98.gif',	// Win 9x 4.90, Windows ME
			'W95' => 'win98.gif',	// Windows 95
			'MAC' => '', 	// Mac PowerPC
			'LIN' => '',	// Linux
			*/
			'windows'		=> 'windows.png',
			'windows phone' => 'windowsphone.gif',
			'os x'			=> 'osx.png',
			'ios'			=> 'ios.png',
			'android'		=> 'android.png',
			'chrome os'		=> 'chromeos.gif',
			'linux'			=> 'linux.gif',
			'symbos'		=> '',
			'nokia'			=> '',
			'blackberry'	=> '',
			'freebsd'		=> 'bsd.gif',
			'openbsd'		=> 'openbsd.gif',
			'netbsd'		=> 'bsd.gif',
			'opensolaris'	=> '',
			'sunos'			=> 'sun.gif',
			'os2'			=> 'os2.gif',
			'beos'			=> ''
		);
		$filename = $osIconFile[$type];
		if (!isset($filename)) $filename = '';
		return $filename;
	}
}
?>
