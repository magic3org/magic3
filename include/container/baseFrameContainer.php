<?php
/**
 * フレームコンテナ作成用ベースクラス
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
require_once(M3_SYSTEM_INCLUDE_PATH . '/db/systemDb.php');		// システムDBアクセスクラス
require_once(M3_SYSTEM_INCLUDE_PATH . '/common/core.php');

class BaseFrameContainer extends Core
{
	protected $_db;	// DB接続オブジェクト
	private $joomlaBufArray = array();			// Joomla!データ受け渡し用
	const SYSTEM_TEMPLATE = '_system';		// システム画面用テンプレート
	const M_ADMIN_TEMPLATE = 'm/_admin';	// 携帯用管理画面テンプレート
	const ERR_MESSAGE_ACCESS_DENY = 'Access denied.';		// ウィジェットアクセスエラーのメッセージ
	const SITE_ACCESS_EXCEPTION_IP = 'site_access_exception_ip';		// アクセス制御、例外とするIP
	const CONFIG_KEY_MSG_TEMPLATE = 'msg_template';			// メッセージ用テンプレート取得キー
//	const CF_MOBILE_AUTO_REDIRECT = 'mobile_auto_redirect';		// 携帯の自動遷移
	const TEMPLATE_GENERATOR_THEMLER = 'themler';			// テンプレート作成アプリケーション(Themler)
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト取得
		$this->_db = $this->gInstance->getSytemDbObject();
	}
	/**
	 * 起動マネージャから呼ばれる唯一のメソッド
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 */
	function process($request)
	{
		// パラメータを取得
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);		// 実行コマンドを取得
				
		// インストール画面への制御は、install.phpファイルの作成、削除で制御する
		// 最小限の設定が行われていない場合,DBに接続できない場合は、インストール画面へ
		if (!defined('M3_STATE_IN_INSTALL')){
			if (($this->gEnv->canUseDb() && $this->gSystem->canInitSystem()) ||		// システム初期化モードのとき
				!$this->gConfig->isConfigured()){									// 設定ファイルに設定がないとき(初回インストール)
				
				// 旧システムがある場合はadminディレクトリまでアクセスしてインストーラを実行。
				$isRedirect = false;
				if ($this->_isExistsOldSystemDir()){		// ディレクトリアクセス権がない場合も旧システムが存在するものとしてリダイレクトを行う
					if ($this->gEnv->isAdminDirAccess()) $isRedirect = true;
				} else {
					$isRedirect = true;
				}
				
				if ($isRedirect){
					// インストーラファイルがない場合は回復
					$this->gInstance->getFileManager()->recoverInstaller();

					$this->gPage->redirectToInstall();
				} else {
					// サイト非公開(システムメンテナンス)表示
					$this->gPage->showError(503);
				}
				return;
			} else if ($this->gConfig->isConfigured() && !$this->gEnv->canUseDb()){		// DB接続失敗のとき
				if ($this->gEnv->isAdminDirAccess()){		// 管理画面の場合のみインストーラ起動
					// インストーラファイルがない場合は回復
					$this->gInstance->getFileManager()->recoverInstaller();

					$this->gPage->redirectToInstall();
				} else {
					// サーバ内部エラーメッセージ表示
					$this->gPage->showError(500);
				}
				return;
			}
		}
		
		// 開始ログ出力
		//$this->gLog->info(__METHOD__, 'フレーム作成開始');

		// ページ作成開始
		// セッション変数読み込み。サブページIDの設定。
		$this->gPage->startPage($request);
		
		// パラメータ取得
		$isSystemAdmin = $this->gEnv->isSystemAdmin();		// 管理者権限があるかどうか
		$isSystemManageUser = $this->gEnv->isSystemManageUser();		// システム運用可能かどうか
			
		if (!defined('M3_STATE_IN_INSTALL')){		// インストールモード以外のとき
			// ############## ユーザごとの設定の読み込み ###################
			// 引数での言語設定取得、言語変更可能な場合は変更
			// 言語の優先順は、URLの言語設定、クッキーの言語設定の順
			if (!$this->gEnv->isAdminDirAccess()){		// 管理画面以外の場合
				if ($this->gEnv->getCanChangeLang() && $this->gEnv->isMultiLanguageSite()){	// 言語変更可能で多言語対応サイトのとき
					$lang = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_LANG);
					if (empty($lang)){
						// 空の場合はクッキーから言語値を取得
						$lang = $request->getCookieValue(M3_COOKIE_LANG);
					}

					// アクセス可能な言語な場合は変更
					if (in_array($lang, $this->gSystem->getAcceptLanguage())){
						$this->gEnv->setCurrentLanguage($lang);
						
						// クッキーに言語を保存
						$request->setCookieValue(M3_COOKIE_LANG, $lang);
					} else {		// アクセス不可の場合はクッキーを削除
						// クッキーを削除
						$request->setCookieValue(M3_COOKIE_LANG, '', -1);
					}
				} else {
					// クッキーを削除
					$request->setCookieValue(M3_COOKIE_LANG, '', -1);
				}
				// 言語に依存する情報を取り込む
				$this->gPage->loadLang();
			}
			// ################### URLアクセス制御 ######################
			// 非公開URLへは管理権限がないとアクセスできない
			$canAccess = true;		// アクセス可能かどうか
			$isErrorAccess = false;		// 不正アクセスかどうか
			$toAdminType = 0;		// 管理画面の遷移タイプ(0=アクセス不可、1=ログイン画面、2=サイト非公開画面, 3=存在しないページ)
			$errMessage = '';	// エラーメッセージ
			$messageDetail = '';	// 詳細メッセージ
			
			// ページID,ページサブID以外のURLパラメータをチェック。ページマネージャーでの処理(startPage())の結果を反映。
			$ret = $this->gPage->isSystemPage();		// システム制御ページへ遷移するかどうか
			if ($ret){
				// ページが見つかりません画面へ遷移
				$canAccess = false;
				$toAdminType = 4;
			}
			
			// ページID,ページサブIDからアクセス権をチェック
			if ($canAccess){
				$isPublicUrl = $this->gPage->canAccessUrl($isActivePage, $errCode);
				if (!$isPublicUrl && !$isSystemManageUser){// システム運用可能ユーザかどうか
					$canAccess = false;
					$isErrorAccess = true;		// 不正アクセスかどうか
					$errMessage = 'ユーザに公開されていないページへのアクセス。';
				
					if (!$isActivePage){
						if ($errCode == 1){			// ページIDが不正な場合
							$toAdminType = 4;
						} else {
							$toAdminType = 3;		// 有効なアクセスポイント、ページでない場合は存在しないページとする
						}
					}
				}
			}
			
			// ################### ユーザアクセス制御 ######################
			// クッキーがないため権限を識別できない場合でも、管理者として処理する場合があるので、サブクラスの_checkAccess()メソッドは必ず通るようにする
			if ($canAccess){		// アクセス可能な場合はユーザをチェック
				if (method_exists($this, '_checkAccess')){
					// 管理画面へのアクセスを制御
					$canAccess = $this->_checkAccess($request);		// サブクラスメソッドの呼び出し
					
					// フロント画面から直接管理画面が呼ばれた場合は一旦ログインへ遷移
					if (!$canAccess && 
						($cmd == M3_REQUEST_CMD_CONFIG_WIDGET ||						// ウィジェットの設定
						$cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET)){			// 表示位置を表示するとき(ウィジェット付き)
						// 管理画面で処理
						$toAdminType = 1;		// ログイン画面へ
					}
				} else {			// _checkAccess()がないときは、標準のアクセス制御
					// フロント画面へのアクセスを制御
					$canAccess = $this->_accessSite($request);		// サイト公開制御
					if ($canAccess){
						if ($cmd == M3_REQUEST_CMD_LOGIN ||					// ログイン画面を表示のとき
							$cmd == M3_REQUEST_CMD_LOGOUT){				// ログアウトのとき

							// 管理画面で処理
							$canAccess = false;
							$toAdminType = 1;		// ログイン画面へ
						} else if ($cmd != '' &&								// コマンドなし
							$cmd != M3_REQUEST_CMD_CHANGE_TEMPLATE &&			// テンプレート変更
							$cmd != M3_REQUEST_CMD_SHOW_POSITION &&				// 表示位置を表示するとき
							$cmd != M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET &&	// 表示位置を表示するとき(ウィジェット付き)
							$cmd != M3_REQUEST_CMD_FIND_WIDGET &&				// ウィジェットを検索
							$cmd != M3_REQUEST_CMD_DO_WIDGET &&					// ウィジェット単体実行
							$cmd != M3_REQUEST_CMD_PREVIEW &&					// サイトのプレビューを表示
							$cmd != M3_REQUEST_CMD_RSS &&						// RSS配信
							$cmd != M3_REQUEST_CMD_CSS){						// CSS生成
							
							// 標準のアクセスでは、上記コマンド以外は受け付けない
							$canAccess = false;
							$isErrorAccess = true;		// 不正アクセス
							$errMessage = '不正なコマンドの実行。';
							$messageDetail = 'アクセスポイント状態=公開';
						}
					} else {		// サイトアクセスできない場合は、管理画面でメッセージを表示
						if ($cmd == M3_REQUEST_CMD_LOGIN ||					// ログイン画面を表示のとき
							$cmd == M3_REQUEST_CMD_LOGOUT ||				// ログアウトのとき
							$cmd == M3_REQUEST_CMD_PREVIEW){					// サイトのプレビューを表示
							$toAdminType = 1;		// ログイン画面へ
						} else {
							$toAdminType = 2;		// サイト非公開画面へ
						}
						
						// 不正なコマンドはログを残す
						if ($cmd != '' &&								// コマンドなし
							$cmd != M3_REQUEST_CMD_LOGIN &&				// ログイン画面を表示のとき
							$cmd != M3_REQUEST_CMD_LOGOUT &&					// ログアウトのとき
							$cmd != M3_REQUEST_CMD_CHANGE_TEMPLATE &&			// テンプレート変更
							$cmd != M3_REQUEST_CMD_SHOW_POSITION &&				// 表示位置を表示するとき
							$cmd != M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET &&	// 表示位置を表示するとき(ウィジェット付き)
							$cmd != M3_REQUEST_CMD_FIND_WIDGET &&				// ウィジェットを検索
							$cmd != M3_REQUEST_CMD_DO_WIDGET &&					// ウィジェット単体実行
							$cmd != M3_REQUEST_CMD_PREVIEW &&					// サイトのプレビューを表示
							$cmd != M3_REQUEST_CMD_RSS &&						// RSS配信
							$cmd != M3_REQUEST_CMD_CSS){						// CSS生成
							
							$isErrorAccess = true;		// 不正アクセス
							$errMessage = '不正なコマンドの実行。';
							$messageDetail = 'アクセスポイント状態=非公開';
						}
					}
				}
				// システム運用可能ユーザはアクセス可。
				// ログアウトのときはすでに管理ユーザの可能性があるので、ログアウト時は変更しない
				//if ($isSystemManageUser && $cmd != M3_REQUEST_CMD_LOGOUT) $canAccess = true;
				if ($isSystemAdmin && $cmd != M3_REQUEST_CMD_LOGOUT) $canAccess = true;			// 2011/8/31 システム管理者のみに変更
			}
			// #################### アクセスログ記録 #######################
			// DBが使用可能であれば、ログイン処理終了後、アクセスログを残す
			if ($this->gEnv->canUseDb()) $this->gAccess->accessLog();

			// アクセス不可のときはここで終了
			if (!$canAccess){
				switch ($toAdminType){
					case 1:			// ログイン画面へ
						// システム制御モードに変更
						$this->gPage->setSystemHandleMode(1/*管理画面*/);
						break;
					case 2:			// サイト非公開画面へ
						$this->gPage->setSystemHandleMode(10/*サイト非公開中*/);
						break;
					case 3:			// 存在しないページ画面へ(システム運用可能ユーザ以外)
						// サイトが非公開の場合は、メンテナンス中画面のみ表示
						if ($this->_accessSite($request)){		// サイト公開中の場合
							$messageDetail = 'アクセスポイント状態=公開';
							$this->gPage->setSystemHandleMode(12/*存在しないページ*/);
						} else {
							$messageDetail = 'アクセスポイント状態=非公開';
							$this->gPage->setSystemHandleMode(10/*サイト非公開中*/);
						}
						break;
					case 4:		// 不正なページIDの指定
						$messageDetail = '不正なページIDの指定';
						$this->gPage->setSystemHandleMode(12/*存在しないページ*/);
						break;
					default:		// アクセス不可画面へ
						// システム制御モードに変更
						$this->gPage->setSystemHandleMode(11/*アクセス不可*/);
						break;
				}
				// システム制御画面を表示
				$this->_showSystemPage($request, $toAdminType);
						
				// 不正アクセスの場合は、アクセスエラーログを残す
				if ($isErrorAccess) $this->gOpeLog->writeUserAccess(__METHOD__, '不正なアクセスを検出しました。' . $errMessage, 2201, 'アクセスをブロックしました。URL: ' . $this->gEnv->getCurrentRequestUri() . ', ' . $messageDetail);
				return;
			}
			// #################### URLの遷移 #######################
			//if ($this->gSystem->getSystemConfig(self::CF_MOBILE_AUTO_REDIRECT)){		// 携帯自動遷移を行う場合
			if ($this->gSystem->mobileAutoRedirect()){		// 携帯自動遷移を行う場合
				// 携帯のときは携帯用URLへ遷移
				if ($this->gEnv->isMobile() && !$this->gEnv->getIsMobileSite()){
					$this->gPage->redirect($this->gEnv->getDefaultMobileUrl(true/*携帯用パラメータ付加*/), true/*遷移時のダイアログ表示を抑止*/);
					return;
				}
			}
			if ($this->gSystem->smartphoneAutoRedirect()){		// スマートフォン自動遷移を行う場合
				// スマートフォンのときはスマートフォンURLへ遷移
				if ($this->gEnv->isSmartphone() && !$this->gEnv->getIsSmartphoneSite()){
					$this->gPage->redirect($this->gEnv->getDefaultSmartphoneUrl());
					return;
				}
			}
		}

		// ################## 実行コマンドから処理を確定 ##################
		$openBy = $request->trimValueOf(M3_REQUEST_PARAM_OPEN_BY);		// ウィンドウオープンタイプ
		
		// 画面作成モードか、ウィジェット単体処理モードかを決定
		$createPage = true;		// 画面作成モード
		if ($cmd == M3_REQUEST_CMD_INIT_DB){	// DB初期化オペレーションのとき
		} else if ($cmd == M3_REQUEST_CMD_SHOW_POSITION){		// 表示位置を表示するとき
			// 管理者権限がある場合のみ実行可能
			//if ($this->gEnv->isSystemAdmin()){
			if ($isSystemAdmin){
				// ポジションの表示画面のアクセスは、すべて管理機能URLで受け付ける
				// ページIDを再設定
/*				$pageId = $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_ID);
				if (empty($pageId)) $pageId = $this->gEnv->getDefaultPageId();		// 値がないときはデフォルトのページIDを設定
				$this->gEnv->setCurrentPageId($pageId);
				$pageSubId = $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_SUB_ID);
				if (!empty($pageSubId)) $this->gEnv->setCurrentPageSubId($pageSubId);
			*/
				$this->gPage->showPosition(1);			// ポジションを表示
			} else {
				return;
			}
		} else if ($cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){		// 表示位置を表示するとき(ウィジェット付き)
			// 管理者権限がある場合のみ実行可能
			//if ($this->gEnv->isSystemAdmin()){
			if ($isSystemAdmin){
				// ポジションの表示画面のアクセスは、すべて管理機能URLで受け付ける
				// ページIDを再設定
/*				$pageId = $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_ID);
				if (empty($pageId)) $pageId = $this->gEnv->getDefaultPageId();		// 値がないときはデフォルトのページIDを設定
				$this->gEnv->setCurrentPageId($pageId);
				$pageSubId = $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_SUB_ID);
				if (!empty($pageSubId)) $this->gEnv->setCurrentPageSubId($pageSubId);
				*/
				$this->gPage->showPosition(2);			// ウィジェット付きポジションを表示
			} else {
				return;
			}
		} else if ($cmd == M3_REQUEST_CMD_GET_WIDGET_INFO){		// ウィジェット各種情報取得(AJAX用)
			// ウィジェット情報取得
			$this->gPage->getWidgetInfoByAjax($request);
			return;
		} else if ($cmd == M3_REQUEST_CMD_SHOW_PHPINFO){	// phpinfoの表示
			// phpinfo画面を表示
			$this->_showPhpinfoPage($request);
			return;
		} else if ($cmd == M3_REQUEST_CMD_FIND_WIDGET){		// ウィジェットを検索し、前面表示
			// 目的のウィジェットのあるページサブIDへ遷移
			$this->gPage->redirectToUpdatedPageSubId($request);
			return;
		} else if ($cmd == M3_REQUEST_CMD_SHOW_WIDGET){		// ウィジェットの単体表示
			$createPage = false;		// ウィジェット単体処理モードに設定
			$this->gPage->showWidget();	// ウィジェット表示
		} else if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){		// ウィジェットの設定管理
			$createPage = false;		// ウィジェット単体処理モードに設定
			$this->gPage->showWidget();	// ウィジェット表示
		} else if ($cmd == M3_REQUEST_CMD_DO_WIDGET){		// ウィジェット単体オペレーション
			$createPage = false;		// ウィジェット単体処理モードに設定
			
			// ウィンドウオープンタイプ指定のときは、テンプレートを表示する
			if (!empty($openBy)) $this->gPage->showWidget();	// ウィジェット表示
		} else if ($cmd == M3_REQUEST_CMD_RSS){		// RSS配信
			$createPage = false;		// ウィジェット単体処理モードに設定
		} else if ($cmd == M3_REQUEST_CMD_CSS){		// CSS生成
		
		} else if ($this->gEnv->isServerConnector()){		// サーバ接続の場合
			$createPage = false;		// ウィジェット単体処理モードに設定
		}

		// ################### クライアントへの出力方法の制御 ######################
		// ウィジェットIDの取得
		$widgetId = $request->trimValueOf(M3_REQUEST_PARAM_WIDGET_ID);
		if ($createPage){				// 通常の画面作成の場合
			// 画面のキャッシュデータを取得
			$this->gCache->initCache($request);		// キャッシュ機能初期化
			$cacheData = $this->gCache->getPageCache($request);
			
			if (empty($cacheData)){		// キャッシュデータがないときは画面を作成
				// カレントのテンプレートを決定
				$curTemplateId = $this->_defineTemplate($request, $subTemplateId);

				// 画面を作成
				$pageData = $this->_createPage($request, $curTemplateId, $subTemplateId);
				
				// ##### 非共通ウィジェットがページ上になくてもエラーとしない #####
				$this->gCache->setPageCache($request, $pageData);		// キャッシュデータを設定
				echo $pageData;
					
/*				// 使用した非共通ウィジェットの数をチェック
				$nonSharedWidgetCount = $this->gPage->getNonSharedWidgetCount();
				if ($nonSharedWidgetCount == -1){		// カウントなしの場合
					$this->gCache->setPageCache($request, $pageData);		// キャッシュデータを設定
					echo $pageData;
				} else {
					// ***** WordPressテンプレートの場合は非共通のウィジェットが使用されていなくても表示可とする *****
					if ($this->gEnv->getCurrentTemplateType() == 100) $nonSharedWidgetCount = 1;
					
					if ($isSystemAdmin || $nonSharedWidgetCount > 0){
						$this->gCache->setPageCache($request, $pageData);		// キャッシュデータを設定
						echo $pageData;
					} else {		// 管理者以外で、非共通のウィジェットが使用されていないページはアクセス不可とする
						$errMessage = 'ユーザに公開されていないページへのアクセス。';
						$messageDetail = 'アクセスポイント状態=公開, 要因: グローバルウィジェットのみのページへのアクセスはできません。ページには1つ以上のローカルウィジェットが必要です。';
						$this->gOpeLog->writeUserAccess(__METHOD__, '不正なアクセスを検出しました。' . $errMessage, 2202, 'アクセスをブロックしました。URL: ' . $this->gEnv->getCurrentRequestUri() . ', ' . $messageDetail);

						// アクセス不可ページへ遷移
						$this->gPage->redirect('?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=_accessdeny');
					}
				}*/
			} else {
				echo $cacheData;
			}
			
			if ($cmd != M3_REQUEST_CMD_CSS){		// 画面出力(CSS生成以外)のとき
				// オプション出力(時間計測等)追加
				echo $this->gPage->getOptionContents($request);
			}
		} else {		// ウィジェット単体実行モードのとき
			// ###################ウィジェット指定で出力の場合####################
			// ウィジェット単体を直接実行するインターフェイスで、HTTPヘッダは送信しない。
			// 以下のパターンで使用する。
			// ・Ajaxを使って、データをやり取りしたい場合
			// ・ウィジェット単体での実行(ウィジェットが生成したタグのみ)
			// ・ウィジェット単体での実行(HTMLやJavascriptの追加あり)
			// ・ウィジェット個別の設定(セキュリティの必要あり)

			// ################# アクセスチェック ################
			// ウィジェット単体オペレーションのときは、ウィジェット情報の単体実行許可があるかどうか判断(管理権限にかかわらず同じ動作)
			if ($cmd == M3_REQUEST_CMD_DO_WIDGET ||		// ウィジェット単体実行
				$cmd == M3_REQUEST_CMD_RSS){		// RSS配信
				if (empty($widgetId)){
					$this->gOpeLog->writeUserAccess(__METHOD__, 'ウィジェットIDが設定されていません。', 2200,
						'実行処理はキャンセルされました。');
					return;
				} else if ($this->_db->getWidgetInfo($widgetId, $row)){			// ウィジェット情報取得
					if ($cmd == M3_REQUEST_CMD_DO_WIDGET && !$row['wd_enable_operation']){	// ウィジェット単体実行
						// アクセスエラーのログを残す
						$this->_db->writeWidgetLog($widgetId, 1/*単体実行*/, $cmd, self::ERR_MESSAGE_ACCESS_DENY);
						
						$this->gOpeLog->writeUserAccess(__METHOD__, 'このウィジェットは単体起動できません。(ウィジェットID: ' . $widgetId . ')', 2200,
						'実行処理はキャンセルされました。このウィジェットは単体起動できないウィジェットです。単体起動を許可するにはウィジェット情報(_widgets)の単体起動フラグ(wd_enable_operation)がtrueになっている必要があります。');
						return;
					} else if ($cmd == M3_REQUEST_CMD_RSS && !$row['wd_has_rss']){				// RSS配信
						// アクセスエラーのログを残す
						$this->_db->writeWidgetLog($widgetId, 1/*単体実行*/, $cmd, self::ERR_MESSAGE_ACCESS_DENY);
						
						$this->gOpeLog->writeUserAccess(__METHOD__, 'このウィジェットはRSS配信できません。(ウィジェットID: ' . $widgetId . ')', 2200,
						'実行処理はキャンセルされました。このウィジェットはRSS配信できないウィジェットです。RSS配信を許可するにはウィジェット情報(_widgets)のRSS配信フラグ(wd_has_rss)がtrueになっている必要があります。');
						return;
					}
				} else {
					$this->gOpeLog->writeUserAccess(__METHOD__, 'このウィジェットは実行許可がありません。(ウィジェットID: ' . $widgetId . ')', 2200,
						'実行処理はキャンセルされました。ウィジェット情報(_widgets)が見つかりません。');
					return;
				}
			}
			
			// 管理権限がない場合は、ウィジェットのページへの配置状況からアクセス権限をチェックする
			if (!$isSystemManageUser && !$this->gAccess->isValidAdminKey() && !$this->_db->canAccessWidget($widgetId)){
				// アクセスエラーのログを残す
				$this->_db->writeWidgetLog($widgetId, 1/*単体実行*/, $cmd, self::ERR_MESSAGE_ACCESS_DENY);
				
				$this->gOpeLog->writeUserAccess(__METHOD__, 'ウィジェットへの不正なアクセスを検出しました。(ウィジェットID: ' . $widgetId . ')', 2200,
						'実行処理はキャンセルされました。このウィジェットは一般ユーザに公開されているページ上に存在しないため単体実行できません。');
				return;
			}
			
			// ################# パラメータチェック ################
			if (!$isSystemManageUser && !$this->gAccess->isValidAdminKey() && $this->gEnv->isServerConnector()){		// サーバ接続の場合
				// クエリーパラメータはウィジェットIDのみ正常とする
				$params = $request->getQueryArray();
				$paramCount = count($params);
				if (!($paramCount == 1 && !empty($params[M3_REQUEST_PARAM_WIDGET_ID]))){
					// アクセスエラーのログを残す
					$this->_db->writeWidgetLog($widgetId, 1/*単体実行*/, $cmd, self::ERR_MESSAGE_ACCESS_DENY);
				
					$this->gOpeLog->writeUserAccess(__METHOD__, 'サーバ接続アクセスポイントへの不正なアクセスを検出しました。', 2200,
							'実行処理はキャンセルされました。URLのクエリー部が不正です。URL=' . $this->gEnv->getCurrentRequestUri());
					return;
				}
			}

			// 画面表示する場合はテンプレートを設定。画面に表示しない場合はテンプレートが必要ない。
			if ($this->gPage->getShowWidget()){
				// 管理用テンプレートに固定
				//$curTemplate = $this->_defineTemplate($request);
				$curTemplate = $this->gSystem->defaultAdminTemplateId();

				// カレントのテンプレートIDを設定
				$this->gEnv->setCurrentTemplateId($curTemplate);
			}
			
			// ################### バッファリング開始 ######################
			// ob_end_flush()までの出力をバッファリングする
			ob_start();
			
			// サブクラスの前処理を実行
			if (method_exists($this, '_preBuffer')) $this->_preBuffer($request);
		
			// 作業中のウィジェットIDを設定
			$this->gEnv->setCurrentWidgetId($widgetId);
			
			if ($this->gEnv->isServerConnector()){		// サーバ接続の場合
				// ウィジェット用のHTMLヘッダを出力
				$this->gPage->startWidgetXml($cmd);

				// 指定のウィジェットを実行
				$widgetIndexFile = $this->gEnv->getWidgetsPath() . '/' . $widgetId . '/' . M3_FILENAME_INDEX;

				if (file_exists($widgetIndexFile)){
					// 実行のログを残す
					$this->_db->writeWidgetLog($widgetId, 1/*単体実行*/, $cmd);

					require_once($widgetIndexFile);
				} else {
					echo 'file not found: ' . $widgetIndexFile;
				}
			
				// ウィジェット用のタグを閉じる
				$this->gPage->endWidgetXml($cmd);
			} else if ($cmd == M3_REQUEST_CMD_RSS){		// RSS配信のとき
				ob_start();// バッファ作成
				
				// ウィジェット用のHTMLヘッダを出力
				$this->gPage->startWidgetRss($cmd);
				
				// 指定のウィジェットを実行
				$widgetIndexFile = $this->gEnv->getWidgetsPath() . '/' . $widgetId . '/' . M3_FILENAME_INDEX;

				if (file_exists($widgetIndexFile)){
					// ウィジェット定義ID、ページ定義のシリアル番号を取得
					$configId = 0;		// 定義ID
					$serial = 0;		// シリアル番号
					if ($this->_db->getPageDefOnPageByWidgetId($this->gEnv->getCurrentPageId(), $this->gEnv->getCurrentPageSubId(), $widgetId, $row)){
						$configId = $row['pd_config_id'];		// 定義ID
						$serial = $row['pd_serial'];		// シリアル番号
					}

					// ウィジェット定義IDを設定
					$this->gEnv->setCurrentWidgetConfigId($configId);
			
					// ページ定義のシリアル番号を設定
					$this->gEnv->setCurrentPageDefSerial($serial);
				
					// 実行のログを残す
					$this->_db->writeWidgetLog($widgetId, 1/*単体実行*/, $cmd);

					require_once($widgetIndexFile);
					
					// ウィジェット定義IDを解除
					$this->gEnv->setCurrentWidgetConfigId('');
				
					// ページ定義のシリアル番号を解除
					$this->gEnv->setCurrentPageDefSerial(0);
				} else {
					echo 'file not found: ' . $widgetIndexFile;
				}
			
				// 現在のバッファ内容を取得し、バッファを破棄
				$content = ob_get_contents();
				ob_end_clean();
				
				// ウィジェット用のタグを閉じる
				$this->gPage->endWidgetRss($cmd, $content);
			} else {		// RSS配信以外のとき
				ob_start();// バッファ作成
							
				// ウィジェット用のHTMLヘッダを出力
				$this->gPage->startWidget($cmd);
				
				// 指定のウィジェットを実行
				if ($cmd == M3_REQUEST_CMD_CONFIG_WIDGET){		// ウィジェット設定のとき
					$widgetIndexFile = $this->gEnv->getWidgetsPath() . '/' . $widgetId . '/admin/' . M3_FILENAME_INDEX;		// 管理用画面
				} else {
					$widgetIndexFile = $this->gEnv->getWidgetsPath() . '/' . $widgetId . '/' . M3_FILENAME_INDEX;
				}
				if (file_exists($widgetIndexFile)){
					// ウィジェット定義ID、ページ定義のシリアル番号を取得
					$configId = 0;		// 定義ID
					$serial = 0;		// シリアル番号
					if ($this->_db->getPageDefOnPageByWidgetId($this->gEnv->getCurrentPageId(), $this->gEnv->getCurrentPageSubId(), $widgetId, $row)){
						$configId = $row['pd_config_id'];		// 定義ID
						$serial = $row['pd_serial'];		// シリアル番号
					}

					// ウィジェット定義IDを設定
					$this->gEnv->setCurrentWidgetConfigId($configId);
			
					// ページ定義のシリアル番号を設定
					$this->gEnv->setCurrentPageDefSerial($serial);
					
					// 実行のログを残す
					$this->_db->writeWidgetLog($widgetId, 1/*単体実行*/, $cmd);

					require_once($widgetIndexFile);
					
					// ウィジェット定義IDを解除
					$this->gEnv->setCurrentWidgetConfigId('');
				
					// ページ定義のシリアル番号を解除
					$this->gEnv->setCurrentPageDefSerial(0);
				} else {
					echo 'file not found: ' . $widgetIndexFile;
				}
			
				// 現在のバッファ内容を取得し、バッファを破棄
				$content = ob_get_contents();
				ob_end_clean();
				
				// ウィジェット用のタグを閉じる
				$this->gPage->endWidget($cmd, $content);
			}
		
			// 作業中のウィジェットIDを解除
			$this->gEnv->setCurrentWidgetId('');
			
			// サブクラスの後処理の呼び出し
			if (method_exists($this, '_postBuffer')) $this->_postBuffer($request);
			
			if ($cmd == M3_REQUEST_CMD_SHOW_WIDGET ||		// ウィジェットの単体表示
				$cmd == M3_REQUEST_CMD_CONFIG_WIDGET ||		// ウィジェット設定のとき
				($cmd == M3_REQUEST_CMD_DO_WIDGET && !empty($openBy))){		// ウィンドウオープンタイプ指定でウィジェット単体実行のとき
				
				// 現在の出力内容を取得し、一旦内容をクリア
				$srcContents = ob_get_contents();
				ob_clean();
				
				// 追加変換処理。HTMLヘッダ出力する。
				$destContents = $this->gPage->lateLaunchWidget($request, $srcContents);
				
				echo $destContents;
			}
			
			// ページ作成終了処理(HTTPヘッダ出力)
			$this->gPage->endPage($request);

			if ($cmd != M3_REQUEST_CMD_RSS){		// 画面出力(RSS配信以外)のとき
				// オプション出力(時間計測等)追加
				echo $this->gPage->getOptionContents($request);
			}

			// バッファ内容を送信(クライアントへの送信完了)
			ob_end_flush();
		}
		if (!defined('M3_STATE_IN_INSTALL')){		// インストールモード以外のとき
			// #################### アクセスログ記録 #######################
			// DBが使用可能であれば、アクセスログのユーザを登録
			if ($this->gEnv->canUseDb()) $this->gAccess->accessLogUser();
		}

		// 終了ログ出力
		//$this->gLog->info(__METHOD__, 'フレーム作成終了');
	}
	/**
	 * 画面を作成
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param string $curTemplate			テンプレートID
	 * @param string $subTemplateId			サブページID
	 * @return string						画面出力
	 */
	function _createPage($request, $curTemplate, $subTemplateId = '')
	{
		$defaultIndexFile = M3_FILENAME_INDEX;			// テンプレートの起動ファイル
		
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);		// 実行コマンドを取得
		
		// カレントのテンプレートIDを設定
		$this->gEnv->setCurrentTemplateId($curTemplate, $subTemplateId);

		// テンプレート情報を取得
		$convType = 0;		// 変換処理タイプ(0=デフォルト(Joomla!v1.0)、-1=携帯用、1=Joomla!v1.5、2=Joomla!v2.5)
		if ($this->gEnv->getIsMobileSite()){
			$convType = -1;		// 携帯サイト用変換
		} else {
			// テンプレートタイプを取得(0=デフォルト(Joomla!v1.0),1=Joomla!v1.5,2=Joomla!v2.5)
			$convType = $this->gEnv->getCurrentTemplateType();
		}

		// バッファリングの準備
		if (method_exists($this, '_prepareBuffer')) $this->_prepareBuffer($request);
	
		// ################### バッファリング開始 ######################
		// ob_end_flush()までの出力をバッファリングする
		if ($convType == -1){// 携帯用サイトの場合は出力エンコーディングを変更
			$mobileEncoding = $this->gEnv->getMobileEncoding();		// 携帯用エンコーディングを取得
			mb_http_output($mobileEncoding);
			ob_start("mb_output_handler"); // 出力のバッファリング開始
		} else {
			ob_start();
		}

		// サブクラスの前処理を実行
		if (method_exists($this, '_preBuffer')) $this->_preBuffer($request);
	
		if ($convType == 100){		// WordPressテンプレートのとき
			// WordPress用ベース定義値
			define('WP_DEBUG', true);			// ##### エラーメッセージ表示制御(true時noticeを表示) #####
			define('WPINC', 'wp-includes');
			define('ABSPATH', $this->gEnv->getWordpressRootPath() . '/' );
			define('TEMPLATEPATH', $this->gEnv->getTemplatesPath() . '/' . $curTemplate);
			define('STYLESHEETPATH', $this->gEnv->getTemplatesPath() . '/' . $curTemplate);		// 子テンプレートを使用している場合は子テンプレートを示す。デフォルトはテンプレートを示す。
			define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
			define('WP_LANG_DIR', WP_CONTENT_DIR . '/languages');
			define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
			define('WP_CONTENT_URL', '/wp-content');				// 定義自体は必要であるが使用しないのでダミー値で定義
			define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');	// 定義自体は必要であるが使用しないのでダミー値で定義
			
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/load.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/default-constants.php');		// デフォルト値取得
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/plugin.php');

			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/functions.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/default-filters.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/l10n.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-error.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-locale.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-user.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-walker.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-query.php');				// コンテンツデータ取得
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-comment-query.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-term.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-walker-page.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-theme.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-widget.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-widget-factory.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-list-util.php');
//			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-walker-nav-menu.php');
//			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-dependency.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-post.php');			// コンテンツAPIマネージャーからWP_Post型でデータを取得
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-post-type.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-embed.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-http.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-http-requests-hooks.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/class-wp-http-proxy.php');

			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/query.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/pluggable.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/post.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/user.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/widgets.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/comment.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/http.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/kses.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/script-loader.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/theme.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/template.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/link-template.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/category.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/category-template.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/post-template.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/post-thumbnail-template.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/comment-template.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/author-template.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/nav-menu-template.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/nav-menu.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/general-template.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/cache.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/shortcodes.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/formatting.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/post-formats.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/taxonomy.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/media.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/embed.php');
//			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/option.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/pomo/translations.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/pomo/mo.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/capabilities.php');
			require_once($this->gEnv->getWordpressRootPath() . '/wp-includes/meta.php');
		
			// Magic3用インターフェイス
			require_once($this->gEnv->getWordpressRootPath() . '/wpInit.php');		// 初期値取得
			require_once($this->gEnv->getWordpressRootPath() . '/contentApi.php');	// コンテンツ取得API
			require_once($this->gEnv->getWordpressRootPath() . '/menuApi.php');		// メニュー情報取得API
			require_once($this->gEnv->getWordpressRootPath() . '/WPRender.php');		// ウィジェット描画クラス

			// ##### データ初期化 #####
			wp_initial_constants();			// WordPressその他定義値設定
			wp_cookie_constants();			// クッキー用定義
			create_initial_post_types();	// WP_Post型データ型登録
			register_shutdown_function('shutdown_action_hook');		// 終了時イベント登録
			
			// プラグイン初期化
			$GLOBALS['wp_plugin_paths'] = array();			// $wp_plugin_pathsは未使用?
			foreach (wp_get_active_and_valid_plugins() as $plugin) {// プラグインロード
				wp_register_plugin_realpath($plugin);
				include_once($plugin);
			}
			unset($plugin);

			// WordPressメインオブジェクト作成
			$GLOBALS['wp_version'] = '4.7.0';			// 下位互換性チェックで引っかかるのでv4.7.0に定める
			$GLOBALS['locale'] = $this->gEnv->getCurrentLanguage();		// 表示言語を設定
			$GLOBALS['wp_embed'] = new WP_Embed();
			$GLOBALS['wp_the_query'] = new WP_Query();				// $wp_the_queryは変更不可変数で$wp_queryは変更可変数
			$GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];
			$GLOBALS['wp'] = new WP();
			$GLOBALS['wp_widget_factory'] = new WP_Widget_Factory();
			$GLOBALS['gContentApi'] = new contentApi();			// Magic3コンテンツAPIオブジェクト
			$GLOBALS['gMenuApi'] = new menuApi();			// Magic3メニュー情報APIオブジェクト
			$GLOBALS['m3WpOptions'] = array();				// 定義値初期化
			// テンプレートから参照可能にする
			global $wp_query;

			// ページに配置されているウィジェットの状況からWordPress以外の主コンテンツ用のプラグイン(WooCommerce等)をロード
			// setup_themeイベント処理を設定
			$GLOBALS['gContentApi']->loadPlugin();
			
			// テンプレート初期処理
			do_action('setup_theme');
			load_default_textdomain();			// 言語リソースを読み込む
			m3WpInit();							// 言語リソース読み込み後にMagic3用インターフェイス初期化。$GLOBALS['m3WpOptions']を初期化し、get_option()はここから使用可能にする。
			$GLOBALS['wp_locale'] = new WP_Locale();		// 言語リソース読み込み後に生成

			// functions.phpを読み込む。ファイル内で定義されている変数はグローバル変数に変換する。
			$this->_loadFileAsGlobal(TEMPLATEPATH . '/functions.php');

			do_action('after_setup_theme');		// wp-multibyte-patchプラグイン読み込み
			do_action('init');					// テンプレート側からの初期処理(ウィジェットのCSSの初期化等)
			do_action('wp_loaded');
			
			// ##### 起動PHPファイル取得。データ取得用パラメータ設定。#####
			// URLパラメータからコンテンツ形式を取得し、ページを選択
			$params = $request->getQueryArray();
			$paramCount = count($params);
			reset($params);
			$firstKey = key($params);
//			$firstValue = $params[$firstKey];
			
			// コンテンツタイプに合わせて起動PHPファイルを決める。デフォルトはindex.phpで一覧形式で表示。
			$pageTypeDefined = false;		// ページタイプ確定したかどうか
			$wpIndexFile = get_index_template();		// WordPress用テンプレート起動ファイル
			$contentType = $GLOBALS['gContentApi']->getContentType();
			switch ($contentType){
			case M3_VIEW_TYPE_CONTENT:		// 汎用コンテンツ
				if ($firstKey == M3_REQUEST_PARAM_CONTENT_ID || $firstKey == M3_REQUEST_PARAM_CONTENT_ID_SHORT){	// コンテンツIDのとき
					// ページタイプを設定
					$GLOBALS['gContentApi']->setPageType('page');
					
					// フルパスで返るので相対パスに修正
//					$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, get_page_template());		// 固定ページテンプレート
					$wpIndexFile = get_page_template();		// 固定ページテンプレート
					
					// コンテンツID設定
					$firstValue = $request->trimValueOf($firstKey);
					$GLOBALS['gContentApi']->setContentId($firstValue);
					
					$pageTypeDefined = true;		// ページタイプ確定
				}
				break;
			case M3_VIEW_TYPE_PRODUCT:	// 製品
				if ($firstKey == M3_REQUEST_PARAM_PRODUCT_ID || $firstKey == M3_REQUEST_PARAM_PRODUCT_ID_SHORT){		// 製品IDのとき
					// ページタイプを設定
					$GLOBALS['gContentApi']->setPageType('single');
					
					// フルパスで返るので相対パスに修正
					//$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, get_single_template());		// 記事詳細テンプレート
					$wpIndexFile = get_single_template();		// 記事詳細テンプレート
					
					// コンテンツID設定
					$firstValue = $request->trimValueOf($firstKey);
					$GLOBALS['gContentApi']->setContentId($firstValue);
					
					$pageTypeDefined = true;		// ページタイプ確定
				}
				break;
			case M3_VIEW_TYPE_BBS:	// BBS
				break;
			case M3_VIEW_TYPE_BLOG:	// ブログ
				if ($firstKey == M3_REQUEST_PARAM_BLOG_ENTRY_ID || $firstKey == M3_REQUEST_PARAM_BLOG_ENTRY_ID_SHORT){		// ブログ記事IDのとき
					// ページタイプを設定
					$GLOBALS['gContentApi']->setPageType('single');
					
					// フルパスで返るので相対パスに修正
					//$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, get_single_template());		// 記事詳細テンプレート
					$wpIndexFile = get_single_template();		// 記事詳細テンプレート
					
					// コンテンツID設定
					$firstValue = $request->trimValueOf($firstKey);
					$GLOBALS['gContentApi']->setContentId($firstValue);
					
					$pageTypeDefined = true;		// ページタイプ確定
				} else {
					// カテゴリーが設定されている場合はカテゴリー画面を表示
					$value = $request->trimValueOf(M3_REQUEST_PARAM_CATEGORY_ID);
					if (!empty($value)){
						// ページタイプを設定
						$GLOBALS['gContentApi']->setPageType('category');			// カテゴリー表示
				
						// カテゴリー用テンプレート取得
						$template = get_category_template();
						if (empty($template)) $template = get_archive_template();		// カテゴリー用のテンプレートが取得できない場合はアーカイブ用テンプレートを取得
						if (!empty($template)) $wpIndexFile = $template;

						// フルパスで返るので相対パスに修正
						//$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, $template);		// カテゴリーテンプレート
						
						$pageTypeDefined = true;		// ページタイプ確定
					}
					if (!$pageTypeDefined){
						$year = $request->trimValueOf(M3_REQUEST_PARAM_YEAR);		// 年指定
						$month = $request->trimValueOf(M3_REQUEST_PARAM_MONTH);		// 月指定
						$day = $request->trimValueOf(M3_REQUEST_PARAM_DAY);		// 日指定

						if (!empty($year)){			// 年月日指定のとき
							// ページタイプを設定
							$GLOBALS['gContentApi']->setPageType('date');			// 年月日表示
				
							// 年月日用テンプレート取得
							$template = get_date_template();
							if (empty($template)) $template = get_archive_template();		// 年月日用のテンプレートが取得できない場合はアーカイブ用テンプレートを取得
							if (!empty($template)) $wpIndexFile = $template;
						
							// フルパスで返るので相対パスに修正
							//$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, $template);

							$pageTypeDefined = true;		// ページタイプ確定
						}
					}
					// 検索条件が設定されている場合は検索画面を表示
					if (!$pageTypeDefined){
						$value = $request->trimValueOf('s');
						if (!empty($value)){
							// ページタイプを設定
							$GLOBALS['gContentApi']->setPageType('search');			// 検索結果表示
					
							// フルパスで返るので相対パスに修正
							//$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, get_search_template());		// 検索結果テンプレート
							$template = get_search_template();		// 検索結果テンプレート
							if (!empty($template)) $wpIndexFile = $template;
							
							$pageTypeDefined = true;		// ページタイプ確定
						}
					}
				}
				break;
			case M3_VIEW_TYPE_WIKI:	// Wiki
				break;
			case M3_VIEW_TYPE_USER:	// ユーザ作成コンテンツ
				break;
			case M3_VIEW_TYPE_EVENT:	// イベント
				break;
			case M3_VIEW_TYPE_PHOTO:	// フォトギャラリー
				break;
			default:
				// 検索キーワードが設定されている場合は検索結果画面を表示
				$value = $request->trimValueOf('s');
				if (!empty($value)){
					// ページ指定されていない場合(フロント画面等)、検索結果表示用ページがある場合はリダイレクト
					$subId = $request->trimValueOf(M3_REQUEST_PARAM_PAGE_SUB_ID);
					if (empty($subId)){
						$subId = $this->_db->getSubPageIdWithContent(M3_VIEW_TYPE_SEARCH, $this->gEnv->getCurrentPageId());// ページサブIDを取得
						if (!empty($subId)){
							// リダイレクト用URLを作成
							$redirectUrl = $this->gEnv->createPageUrl();
							$redirectUrl .= '?' . M3_REQUEST_PARAM_PAGE_SUB_ID . '=' . $subId;
							//$redirectUrl .= '&s=' . urlencode($value);							// 検索キーワード
							$redirectUrl .= '&' . M3_REQUEST_PARAM_OPERATION_TASK . '=search&' . M3_REQUEST_PARAM_KEYWORD . '=' . urlencode($value);		// 検索キーワード
							
							$this->gPage->redirect($redirectUrl);
							return;				// ここで終了
						}
					}
//				} else {
//					// 検索キーワードが設定されている場合は画面タイトルを設定
//					$value = $request->trimValueOf(M3_REQUEST_PARAM_KEYWORD);
//					if (!empty($value)){
//					}
				}
				
				// ##### デフォルトのページタイトルを設定 #####
				$pageInfo = $this->gPage->getCurrentPageInfo();
				if (!empty($pageInfo)){
					$pageTitle = $pageInfo['pg_name'];
					if (!empty($pageTitle)) $GLOBALS['gContentApi']->setPostTitle($pageTitle);
				}
				
				// コンテンツタイプが設定されていないページ(お問合わせページ等)に場合は、固定ページ用のテンプレートを使用
				// ページタイプを設定
				$GLOBALS['gContentApi']->setPageType('page');
				
				// フルパスで返るので相対パスに修正
				//$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, get_page_template());		// 固定ページテンプレート
				$wpIndexFile = get_page_template();		// 固定ページテンプレート
			
				$pageTypeDefined = true;		// ページタイプ確定
				break;
			}
			
			// コンテンツタイプが設定されているページでページタイプが設定されていないページの場合はデフォルトテンプレート(index.php)の代わりにホーム用テンプレートを取得
			if (!empty($contentType) && !$pageTypeDefined){
				// フルパスで返るので相対パスに修正
				//$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, get_home_template());		// ホーム用テンプレート
				$wpIndexFile = get_home_template();		// ホーム用テンプレート
			}

			// サイトのトップページを表示する場合(コンテンツタイプが設定されていないページをデフォルトで表示する場合)は優先してフロント用テンプレートを表示
			if (empty($contentType)){
			//if (!$GLOBALS['gContentApi']->isHomeUrl()){
//			if ($defaultIndexFile == M3_FILENAME_INDEX){		// テンプレートの起動ファイル
				$pageSubId = $request->trimValueOf(M3_REQUEST_PARAM_PAGE_SUB_ID);
				if ($this->gEnv->getCurrentPageSubId() == $this->gEnv->getDefaultPageSubId() && empty($pageSubId)){		// デフォルトページを表示し「sub」なしに限定
					$frontPageTemplate = get_front_page_template();
					if (!empty($frontPageTemplate)){
						//$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, $frontPageTemplate);	// フロントページテンプレート
						$wpIndexFile = $frontPageTemplate;		// フロントページテンプレート
					}
				}
			}

			// プラグインからの起動ファイルパス変換
			$wpIndexFile = apply_filters('template_include', $wpIndexFile);

			// Magic3用のテンプレート起動ファイルパスに変換
//			$defaultIndexFile = $this->_getRelativeTemplateIndexPath($curTemplate, $wpIndexFile);
			$defaultIndexFile = $wpIndexFile;
			
			// ##### テンプレート前処理(起動ファイル決定後に実行) #####
			do_action('template_redirect');
						
			// WordPressオブジェクト作成。wpイベント実行。
			wp();
		} else if ($convType >= 1){		// Joomla!v1.5,v2.5テンプレートのとき
			// Joomla!テンプレート共通の設定
			define('_JEXEC', 1);
			define('JPATH_PLATFORM', $this->gEnv->getJoomlaRootPath());
			define('JPATH_BASE', dirname(__FILE__));
			define('JPATH_SITE', $this->gEnv->getSystemRootPath());
			define('JPATH_PLUGINS', $this->gEnv->getJoomlaRootPath() . '/class/plugins');			// プラグインパス
//			define('JPATH_THEMES', $this->gEnv->getTemplatesPath());								// テンプレートパス		## テンプレート内でエラーが発生するのでここでは定義しない(2015/10/13)
			define('DS', DIRECTORY_SEPARATOR);
			
			global $mainframe;
			require_once($this->gEnv->getJoomlaRootPath() . '/mosDef.php');// Joomla定義読み込み
			require_once($this->gEnv->getJoomlaRootPath() . '/JParameter.php');
			require_once($this->gEnv->getJoomlaRootPath() . '/JRender.php');
						
			// 設定ファイルの読み込み
			//$params = array();
			$paramFile = $this->gEnv->getTemplatesPath() . '/' . $curTemplate . '/params.ini';
			if (is_readable($paramFile)){
				$content = file_get_contents($paramFile);
				$params = new JParameter($content);
			} else {
				$params = new JParameter();
			}
			// テンプレートヘッダ画像上のテキスト設定(Joomla!テンプレート2.5以降)
			$params->set('siteTitle',		$this->gEnv->getSiteName());		// サイト名
			$params->set('siteSlogan',		$this->gSystem->getSiteDef(M3_TB_FIELD_SITE_SLOGAN));		// サイトスローガン

			// Joomla!v1.5用の設定
			$this->language = $this->gEnv->getCurrentLanguage();
			$this->template = $curTemplate;
			//$this->baseurl  = $this->gEnv->getRootUrl();
			$this->baseurl		= $this->gEnv->getRootUrlByCurrentPage();
			$this->direction = 'ltr';
			$this->params   = $params;
			
			// サブテンプレート用の設定
			if ($this->gEnv->getCurrentTemplateGenerator() == self::TEMPLATE_GENERATOR_THEMLER){		// Themlerテンプレートの場合はサブテンプレート用のパラメータを設定
				// JRequest経由でレンダー側にサブテンプレートIDを渡す
				if (!empty($subTemplateId)) JRequest::injectSetVar('file_template_name', $subTemplateId);

				// サブテンプレートIDの渡し方は以下の方法もある(Themlerテンプレート1.39以降はこちらが有効)
				// サブテンプレートIDを埋め込む
				if (!empty($subTemplateId)) $this->setBuffer('<!--TEMPLATE ' . $subTemplateId . ' /-->', 'component');
			}
			
			// 現在のJoomla!ドキュメントを設定
			$this->gEnv->setJoomlaDocument($this);
		} else {			// デフォルト(Joomla!v1.0テンプレート)テンプレートのとき(PC用および携帯用)
			// Joomla!テンプレート共通の設定
			define('_JEXEC', 1);
			
			// Joomlaテンプレート用定義
			global $mosConfig_absolute_path;
			global $mosConfig_live_site;
			global $mosConfig_sitename;
			global $mosConfig_favicon;
			global $mosConfig_sef;
			global $cur_template;
			global $mainframe;
			require_once($this->gEnv->getJoomlaRootPath() . '/mosDef.php');// Joomla定義読み込み
			require_once($this->gEnv->getJoomlaRootPath() . '/mosFunc.php');
			require_once($this->gEnv->getJoomlaRootPath() . '/includes/sef.php');
		}

		// ################### テンプレート読み込み ###################
		// フルパスに変換
		if (strStartsWith($defaultIndexFile, '/')){				// フルパス起動(WordPressテンプレート)のとき
			$templateIndexFile = $defaultIndexFile;
		} else {
			$templateIndexFile = $this->gEnv->getTemplatesPath() . '/' . $curTemplate . '/' . $defaultIndexFile;
		}
		
		if (file_exists($templateIndexFile)){
			require_once($templateIndexFile);
		} else {		// テンプレートが存在しないとき
			if ($this->gEnv->isSystemManageUser()){		// システム管理ユーザのとき
				echo 'template not found error: ' . $curTemplate . ', path=' . $templateIndexFile;
			} else {
				// 一般向けにはメンテナンス画面を表示
				$this->gPage->setSystemHandleMode(10/*サイト非公開中*/);
				$this->_showSystemPage($request, 2/*サイト非公開画面*/);// システム制御画面を表示
					
				// 運用ログに記録(一度だけ出力したい)
				//$this->gOpeLog->writeFatal(__METHOD__, 'テンプレートが存在しません。メンテナンス画面を表示します。(テンプレートID=' . $curTemplate . ')', 1100);
				return;
			}
		}

		// サブクラスの後処理の呼び出し
		if (method_exists($this, '_postBuffer')) $this->_postBuffer($request);

		// ##### WordPressテンプレートの場合は終了イベントを実行。WordPressのエラーメッセージは画面に出力させる。 #####
		if ($convType == 100){		// WordPressテンプレートのとき
			do_action('shutdown');
		}
		
		// 現在の出力内容を取得し、一旦内容をクリア
		$srcContents = ob_get_contents();
		ob_clean();

		// Joomla!タグの変換処理(ウィジェット実行)
		if ($convType >= 1 && $convType < 100){		// Joomla!v1.5,v2.5テンプレートのとき
			$srcContents = $this->gPage->launchWidgetByJoomlaTag($srcContents, $convType);		// launchWidgetByJoomlaTag()は携帯変換(-1)は実行されない
		}
	
		// 遅延実行ウィジェットの出力を埋め込む。HTMLヘッダ出力する。
		$destContents = $this->gPage->lateLaunchWidget($request, $srcContents);

		// 携帯インターフェイスのときのときは、手動変換後、バイナリコード(絵文字等)を埋め込む
		if ($convType == -1){			// 携帯アクセスポイントの場合
			// 出力するコードに変換
			$destContents = mb_convert_encoding($destContents, $mobileEncoding, M3_ENCODING);
	
			// コンテンツ変換メソッドがある場合は実行
			if (method_exists($this, '_convContents')){
				$destContents = $this->_convContents($destContents);// 絵文字埋め込み処理等
			}
		}
		
		// ##### CSS生成の場合は、すべてのウィジェット実行後出力を削除する #####
		if ($cmd == M3_REQUEST_CMD_CSS) $destContents = '';		// CSS生成のとき

		// ページ作成終了処理(HTTPヘッダ出力)
		$destContents .= $this->gPage->endPage($request, true/*出力を取得*/);		// 最終HTMLを追加
		if ($this->gPage->isRedirect()) return '';// リダイレクトの場合ob_end_clean()を実行すると、ログインできないことがあるのでここで終了(2011/11/11)
		
		// バッファを破棄
		//ob_end_flush();
		ob_end_clean();
		
		// 送信データを返す
		return $destContents;
	}
	/**
	 * テンプレートを決定
	 *
	 * @param RequestManager $request	HTTPリクエスト処理クラス
	 * @param string $subTemplateId		テンプレートIDが取得できるときはサブページIDが返る
	 * @return string					テンプレート名
	 */
	function _defineTemplate($request, &$subTemplateId)
	{
		// ########### テンプレートID(ディレクトリ名)を設定 ############
		// テンプレートIDの指定の方法は2パターン
		// 　1.サブクラスで固定に指定
		// 　2.コンテンツからの指定
		// 　3.セッションに保持
		// テンプレートIDの優先順位
		// 　1.サブクラスの_setTemplate()で固定設定にしている場合の固定値
		// 　2.セッションに持っている値
		// 　3.DBのデフォルト値
		$curTemplate = '';
		$subTemplateId = '';
		$isSystemManageUser = $this->gEnv->isSystemManageUser();		// システム運用可能かどうか
		$useSubClassDefine = true;			// サブクラスでの定義を使用するかどうか
		
		// テンプレート変更のときは、セッションのテンプレートIDを変更
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);		// 実行コマンドを取得
		if ($cmd == M3_REQUEST_CMD_CHANGE_TEMPLATE){
			// テンプレートIDをセッションに残す場合
			if ($this->gSystem->useTemplateIdInSession()){		// セッションに保存する場合
				$request->setSessionValue(M3_SESSION_CURRENT_TEMPLATE, $request->trimValueOf(M3_SYSTEM_TAG_CHANGE_TEMPLATE));
			}
		}

		// サブクラスでテンプレートIDを指定している場合はそちらを使用
		$templateDefined = false;		// テンプレート固定かどうか
		if ($useSubClassDefine){
			$tmplStr = trim($this->_setTemplate($request));
			if (strlen($tmplStr) > 0){
				$curTemplate = $tmplStr;
				$templateDefined = true;		// テンプレート固定かどうか
			}
		}

		// セッションにあるときは、セッションの値を使用(携帯でないとき)
		$pageId = $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_ID);
		if (empty($curTemplate)){
			if ($cmd == M3_REQUEST_CMD_SHOW_POSITION ||				// 表示位置を表示するとき
				$cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){	// 表示位置を表示するとき(ウィジェット付き)
				// URLの引数として、ページIDとページサブIDが指定されてくる
				// URLの引数のテンプレートを優先し、引数で指定されていなければ、ページ用個別のテンプレートを取得する
				
				// URLの引数でテンプレートIDが指定されている場合は設定
				$templateId = $request->trimValueOf(M3_REQUEST_PARAM_TEMPLATE_ID);		// テンプレートIDを取得
				if (!empty($templateId)) $curTemplate = $templateId;
					
				// ページ用個別に設定されたテンプレートがある場合は取得
				if (empty($curTemplate)){
					$pageSubId = $request->trimValueOf(M3_REQUEST_PARAM_DEF_PAGE_SUB_ID);
					$line = $this->gPage->getPageInfo($pageId, $pageSubId);
					if (!empty($line)){
						$pageTemplateId = $line['pn_template_id'];
						$subTemplateId = $line['pn_sub_template_id'];		// サブテンプレートID
					}
					if (!empty($pageTemplateId)) $curTemplate = $pageTemplateId;
				}
				
				// 取得できなければデフォルトを取得
				if (empty($curTemplate)){
					if ($pageId == $this->gEnv->getDefaultPageId()){		// 通常サイトのとき
						$curTemplate = $this->gSystem->defaultTemplateId();
						$subTemplateId = $this->gSystem->defaultSubTemplateId();
					} else if ($pageId == $this->gEnv->getDefaultMobilePageId()){		// 携帯サイトのとき
						$curTemplate = $this->gSystem->defaultMobileTemplateId();		// 携帯用デフォルトテンプレート
					} else if ($pageId == $this->gEnv->getDefaultSmartphonePageId()){		// スマートフォン用サイトのとき
						$curTemplate = $this->gSystem->defaultSmartphoneTemplateId();		// スマートフォン用デフォルトテンプレート
					} else if ($pageId == $this->gEnv->getDefaultAdminPageId() ||		// 管理サイトのとき
								$pageId == $this->gEnv->getDefaultRegistPageId()){		// 登録サイトのとき
						$curTemplate = $this->gSystem->defaultAdminTemplateId();
					} else if (empty($pageId)){			// ページIDが指定されていないときは、ウィジェットを表示しないテンプレートのみの表示
						// URLの引数でテンプレートIDが指定されている場合は設定
	//					$templateId = $request->trimValueOf(M3_REQUEST_PARAM_TEMPLATE_ID);		// テンプレートIDを取得
	//					if (!empty($templateId)) $curTemplate = $templateId;
					}
				}
			} else {
				// ページ用のテンプレートがあるときは優先
				$pageTemplateId = $this->gPage->getTemplateIdFromCurrentPageInfo($subTemplateId);
				if (!empty($pageTemplateId)) $curTemplate = $pageTemplateId;

				// テンプレートIDをセッションから取得
				if (empty($curTemplate) && !$isSystemManageUser){			// システム運用者はセッション値を使用できない
					if ($this->gSystem->useTemplateIdInSession()){		// セッションに保存する場合
						if (!$this->gEnv->getIsMobileSite() && !$this->gEnv->getIsSmartphoneSite()){
							$curTemplate = $request->getSessionValue(M3_SESSION_CURRENT_TEMPLATE);// 携帯サイト、スマートフォンサイトでないときはセッション値を取得
						}
					}
				}
				
				// オプションのテンプレートがある場合はオプションを優先
				list($optionTemplate, $optionSubTemplate) = $this->gPage->getOptionTemplateId();
				if (!empty($optionTemplate)){
					$curTemplate = $optionTemplate;
					$subTemplateId = $optionSubTemplate;
					$templateDefined = true;		// テンプレート固定かどうか
				}
				
				// セッションにないときはデフォルトを取得
				if (empty($curTemplate)){
					if ($this->gEnv->getIsMobileSite()){// 携帯用サイトの場合
						$curTemplate = $this->gSystem->defaultMobileTemplateId();		// 携帯用デフォルトテンプレート
					} else if ($this->gEnv->getIsSmartphoneSite()){// スマートフォン用サイトの場合
						$curTemplate = $this->gSystem->defaultSmartphoneTemplateId();		// スマートフォン用デフォルトテンプレート
					} else {
						$curTemplate = $this->gSystem->defaultTemplateId();
						$subTemplateId = $this->gSystem->defaultSubTemplateId();
					}
				}
			}
		}

		if (empty($curTemplate)){
			// テンプレートが１つもみつからないときは、管理用テンプレートを使用
			$curTemplate = $this->gSystem->defaultAdminTemplateId();
			echo 'template not found. viewing by administration template. [' . $curTemplate . ']';
		} else {	// セッションにテンプレートIDを保存
			// テンプレートIDをセッションに残す場合
/*			if ($this->gSystem->useTemplateIdInSession()){		// セッションに保存する場合
				if ($cmd == M3_REQUEST_CMD_SHOW_POSITION ||				// 表示位置を表示するとき
					$cmd == M3_REQUEST_CMD_SHOW_POSITION_WITH_WIDGET){	// 表示位置を表示するとき(ウィジェット付き)
				} else {
					if (!$this->gEnv->getIsMobileSite() && !$this->gEnv->getIsSmartphoneSite() && !$templateDefined){		// PC用画面でサブクラス固定でないとき場合は保存
						$request->setSessionValue(M3_SESSION_CURRENT_TEMPLATE, $curTemplate);
					}
				}
			}*/
		}
		return $curTemplate;
	}
	/**
	 * サイト公開制御
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return bool							サイトにアクセスできるかどうか
	 */
	function _accessSite($request)
	{
		// サイトの公開状況を取得
		$isOpen = $this->gSystem->siteInPublic();
		if ($isOpen){
			// PC用サイト、携帯用サイト、スマートフォン用サイトの公開状況をチェック
			if ($this->gEnv->getIsPcSite()){
				if ($this->gSystem->sitePcInPublic()) return true;
			} else if ($this->gEnv->getIsMobileSite()){
				if ($this->gSystem->siteMobileInPublic()) return true;
			} else if ($this->gEnv->getIsSmartphoneSite()){
				if ($this->gSystem->siteSmartphoneInPublic()) return true;
			}
			return false;
		} else {
			// 例外とするIPアドレスをチェック
			$ip = $this->gSystem->getSystemConfig(self::SITE_ACCESS_EXCEPTION_IP);
			if (!empty($ip) && $ip = $request->trimServerValueOf('REMOTE_ADDR')){
				return true;
			} else {
				return false;
			}
		}
	}
	/**
	 * システム制御画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param int $type						画面タイプ(0=アクセス不可、1=ログイン画面、2=サイト非公開画面)
	 * @return なし
	 */
	function _showSystemPage($request, $type)
	{
		// ページIDを設定
		$pageId = 'admin_index';		// 管理画面を表示
		$this->gEnv->setCurrentPageId($pageId);								// ここでデフォルトページサブIDが再設定される
		$this->gEnv->setCurrentPageSubId($this->gEnv->getDefaultPageSubId());// デフォルトページサブIDをカレントにする
		
		// テンプレートの設定
		// DBで設定されている値を取得し、なければ管理用デフォルトテンプレートを使用
		if ($this->gEnv->getIsMobileSite()){		// 携帯用サイトのアクセスの場合
			$curTemplateId = self::M_ADMIN_TEMPLATE;	// 携帯管理画面用テンプレート
		} else {			// 携帯以外のサイトへのアクセスの場合
			if ($type == 1){			// ログインはデフォルトの管理画面テンプレートに固定
				$curTemplateId = $this->gSystem->defaultAdminTemplateId();
			} else {
				$curTemplateId = $this->gSystem->getSystemConfig(self::CONFIG_KEY_MSG_TEMPLATE);
				if (empty($curTemplateId)){
					$curTemplateId = self::SYSTEM_TEMPLATE;// システム画面用テンプレート
				} else {
					// テンプレートの存在チェック
					$templateIndexFile = $this->gEnv->getTemplatesPath() . '/' . $curTemplateId . '/' . M3_FILENAME_INDEX;
					if (!file_exists($templateIndexFile)) $curTemplateId = self::SYSTEM_TEMPLATE;// システム画面用テンプレート
				}
			}
		}

		// 画面を作成
		$pageData = $this->_createPage($request, $curTemplateId);
		echo $pageData;
	}
	/**
	 * phpinfo画面表示
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @return なし
	 */
	function _showPhpinfoPage($request)
	{
		// ################### バッファリング開始 ######################
		// ob_end_flush()までの出力をバッファリングする
		ob_start();
		
		phpinfo();
		
		// バッファ内容を送信(クライアントへの送信完了)
		ob_end_flush();
	}
	/**
	 * WordPressテンプレートの起動ファイルパスを相対パスに変換
	 *
	 * @param string $templateId	テンプレートID
	 * @param string $path			テンプレートの起動ファイル絶対パス
	 * @return string				テンプレート内での相対パス。エラー発生の場合はデフォルト(index.php)を返す。
	 */
	function _getRelativeTemplateIndexPath($templateId, $path)
	{
		$savedPath = $path;
		$templatePath = $this->gEnv->getTemplatesPath() . '/' . $templateId . '/';
		
		// テンプレートまでのパスを削除
		$path = str_replace($templatePath, '', $path);
		if ($path == $savedPath) $path = M3_FILENAME_INDEX;
		return $path;
	}
	/**
	 * 旧システムディレクトリが存在するかどうかを取得
	 *
	 * @return bool				true=存在する、false=存在しない
	 */
	function _isExistsOldSystemDir()
	{
		// 旧システムディレクトリは同ディレクト階層に存在し、ディレクトリ名の先頭に「_」が付加されているディレクトリ
		$currentDir = $this->gEnv->getSystemRootPath();
		$parentDir = dirname($currentDir);
		$dirName = basename($currentDir);
		
		// ##### open_basedir等のアクセス制限が掛かっていてディレクトリが見えない場合はis_dir()はfalseを返す #####
		// 親ディレクトリへのアクセス権をチェック
		if (@is_dir($parentDir)){
			if (@is_dir($parentDir . '/_' . $dirName)){
				return true;
			} else {
				return false;
			}
		} else {		// 親ディレクトリへのアクセス権がない場合は旧システムが存在すると判断する
			return true;
		}
	}
	/**
	 * PHPファイルを読み込み、定義値をグローバル値に変換する
	 *
	 * @param string $path		ファイルパス
	 * @return bool				true=ファイル読み込み完了、false=ファイル読み込み失敗
	 */
	function _loadFileAsGlobal($path)
	{
		if (file_exists($path)){
			include($path);
			
			// グローバル変数に変換
			$vars = get_defined_vars();
			foreach($vars as $varName => $varValue){
				if (!isset($GLOBALS[$varName])) $GLOBALS[$varName] = $varValue;
			}
			return true;
		} else {
			return false;
		}
	}
	/***********************************************************************************
	 * 以下、Joomla!v1.5テンプレート専用
	 ***********************************************************************************/
	/**
	 * ウィジェット数を取得
	 *
	 * @param string $pos		ポジション
	 * @return int				ウィジェット数
	 */
	function countModules($pos)
	{
		$count = $this->gPage->getWidgetsCount($pos);
		return $count;
	}
	function getBuffer($type = null, $name = null, $attribs = array())
	{
		if (isset($this->joomlaBufArray[$type])){
			return $this->joomlaBufArray[$type];
		} else {
			return '';
		}
	}
	function setBuffer($contents, $type, $name = null)
	{
		$this->joomlaBufArray[$type] = $contents;
		return;
	}
	/**
	 * 出力タイプ取得
	 *
	 * @return string				出力タイプ
	 */
	function getType()
	{
		return 'html';
	}
	/**
	 * HTMLヘッダ情報取得
	 *
	 * @return array				ヘッダ情報
	 */
	function getHeadData()
	{
		$data = array();
		/*$data['title']		= $this->title;
		$data['description']= $this->description;
		$data['link']		= $this->link;
		$data['metaTags']	= $this->_metaTags;
		$data['links']		= $this->_links;
		$data['styleSheets']= $this->_styleSheets;
		$data['style']		= $this->_style;
		$data['scripts']	= $this->_scripts;
		$data['script']		= $this->_script;
		$data['custom']		= $this->_custom;*/
		return $data;
	}
	/**
	 * BASEタグ設定用
	 *
	 * @return string				ベースパス
	 */
	function getBase()
	{
		return '';
	}
	 /**
	 * Adds a linked script to the page
	 *
	 * @param	string  $url		URL to the linked script
	 * @param	string  $type		Type of script. Defaults to 'text/javascript'
	 * @access   public
	 */
	function addScript($url, $type="text/javascript") {
		$this->_scripts[$url] = $type;
	}
	/**
	 * Adds a script to the page
	 *
	 * @access   public
	 * @param	string  $content   Script
	 * @param	string  $type	Scripting mime (defaults to 'text/javascript')
	 * @return   void
	 */
	function addScriptDeclaration($content, $type = 'text/javascript')
	{
		if (!isset($this->_script[strtolower($type)])) {
			$this->_script[strtolower($type)] = $content;
		} else {
			$this->_script[strtolower($type)] .= chr(13).$content;
		}
	}
	/**
	 * Adds a linked stylesheet to the page
	 *
	 * @param   string  $url      URL to the linked style sheet
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9')
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'stylesheet', 'data-test' => 1)
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 * @deprecated 4.0  The (url, mime, media, attribs) method signature is deprecated, use (url, options, attributes) instead.
	 */
	public function addStyleSheet($url, $options = array(), $attribs = array())
	{
		// B/C before 3.7.0
		if (is_string($options))
		{
			JLog::add('The addStyleSheet method signature used has changed, use (url, options, attributes) instead.', JLog::WARNING, 'deprecated');

			$argList = func_get_args();
			$options = array();
			$attribs = array();

			// Old mime type parameter.
			if (!empty($argList[1]))
			{
				$attribs['type'] = $argList[1];
			}

			// Old media parameter.
			if (isset($argList[2]) && $argList[2])
			{
				$attribs['media'] = $argList[2];
			}

			// Old attribs parameter.
			if (isset($argList[3]) && $argList[3])
			{
				$attribs = array_replace($attribs, $argList[3]);
			}
		}

		// Default value for type.
		if (!isset($attribs['type']) && !isset($attribs['mime']))
		{
			$attribs['type'] = 'text/css';
		}

		$this->_styleSheets[$url] = isset($this->_styleSheets[$url]) ? array_replace($this->_styleSheets[$url], $attribs) : $attribs;

		if (isset($this->_styleSheets[$url]['options']))
		{
			$this->_styleSheets[$url]['options'] = array_replace($this->_styleSheets[$url]['options'], $options);
		}
		else
		{
			$this->_styleSheets[$url]['options'] = $options;
		}

		return $this;
	}
}
?>
