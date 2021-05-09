<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2021 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/admin_loginuserDb.php');

class admin_loginuserWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 言語
	const DEFAULT_TITLE = 'ログインユーザ';		// デフォルトのウィジェットタイトル名
	const DEFAULT_CSS_FILE = '/default.css';		// CSSファイル
	const TAG_ID_EDIT_USER = 'edituser';			// ユーザ編集ボタンタグID
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new admin_loginuserDb();
	}
	/**
	 * テンプレートファイルを設定
	 *
	 * _assign()でデータを埋め込むテンプレートファイルのファイル名を返す。
	 * 読み込むディレクトリは、「自ウィジェットディレクトリ/include/template」に固定。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						テンプレートファイル名。テンプレートライブラリを使用しない場合は空文字列「''」を返す。
	 */
	function _setTemplate($request, &$param)
	{
		return 'index.tmpl.html';
	}
	/**
	 * テンプレートにデータ埋め込む
	 *
	 * _setTemplate()で指定したテンプレートファイルにデータを埋め込む。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。_setTemplate()と共有。
	 * @param								なし
	 */
	function _assign($request, &$param)
	{
		$userId = $this->gEnv->getCurrentUserId();
//		$avatarFormat = $this->gInstance->getImageManager()->getDefaultAvatarFormat();		// 画像フォーマット取得
		
		// ユーザ情報取得
		$ret = $this->db->getLoginUserInfo($userId, $row);
		if ($ret){
			$name		= $row['lu_name'];	// ユーザ名
			$avatar		= $row['lu_avatar'];		// アバター
			$loginCount = $row['ll_login_count'];	// ログイン回数
			$loginDt	= $row['ll_pre_login_dt'];	// 前回ログイン日時
			
			$userDetailUrl	= '?task=userlist_detail&' . M3_REQUEST_PARAM_USER_ID . '=' . $row['lu_id'];		// ユーザ詳細画面URL
	//		$loginStatusUrl = '?task=loginstatus_history&account=' . $row['lu_account'];// ログイン状況画面URL
			$loginStatusUrl = '?task=loginhistory&userid=' . $row['lu_id'];// ログイン履歴画面URL
		}
		
		// ##### アバター画像取得 #####
		// 画像サイズ取得
		$sizeIdArray = $this->gInstance->getImageManager()->getAllAvatarSizeId();
		if (!empty($sizeIdArray)){
			$sizeId = $sizeIdArray[count($sizeIdArray) -1];
			$this->gInstance->getImageManager()->getAvatarFormatInfo($sizeId, $imageType, $imageAttr, $imageSize);
			$avatarUrl = $this->gInstance->getImageManager()->getAvatarUrl($avatar, $sizeId);
		}
//		$this->gInstance->getImageManager()->parseImageFormat($avatarFormat, $imageType, $imageAttr, $imageSize);		// 画像情報取得
		
		$iconTitle = 'アバター画像';
		$iconTag = '<img src="' . $this->getUrl($avatarUrl) . '" width="' . $imageSize . '" height="' . $imageSize . '" border="0" alt="' . $iconTitle . '" title="' . $iconTitle . '" />';
		
		// 画面に埋め込む
		$this->tmpl->addVar("_widget", "name", $this->convertToDispString($name));
		$this->tmpl->addVar("_widget", "login_count", $loginCount);
		$this->tmpl->addVar("_widget", "avatar_image", $iconTag);
		// 前回ログイン日時。年が同じ場合は省略。
		if (intval(date('Y', strtotime($loginDt))) == intval(date('Y'))){
			$this->tmpl->addVar("_widget", "login_dt", $this->convertToDispDateTime($loginDt, 11/*年省略,0なし年月*/, 10/*時分表示*/));
		} else {
			$this->tmpl->addVar("_widget", "login_dt", $this->convertToDispDateTime($loginDt, 0, 10/*時分表示*/));
		}
		
		// ユーザ情報へのリンク
		$buttonTag = $this->gDesign->createEditButton($userDetailUrl, 'ユーザ情報を編集', self::TAG_ID_EDIT_USER);
		$this->tmpl->addVar("_widget", "user_detail_button", $buttonTag);
			
		$this->tmpl->addVar("_widget", "login_status_url", $this->convertUrlToHtmlEntity($loginStatusUrl));	// ログイン状況画面URL
	}
	/**
	 * ウィジェットのタイトルを設定
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。そのまま_assign()に渡る
	 * @return string 						ウィジェットのタイトル名
	 */
	function _setTitle($request, &$param)
	{
		return self::DEFAULT_TITLE;
	}
	/**
	 * CSSファイルをHTMLヘッダ部に設定
	 *
	 * CSSファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssFileToHead($request, &$param)
	{
		$cssFilePath = $this->getUrl($this->gEnv->getCurrentWidgetCssUrl() . self::DEFAULT_CSS_FILE);		// CSSファイル
		return $cssFilePath;
	}
}
?>
