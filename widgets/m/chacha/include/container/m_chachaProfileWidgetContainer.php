<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_chachaProfileWidgetContainer.php 3306 2010-06-28 07:07:39Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_chachaBaseWidgetContainer.php');

class m_chachaProfileWidgetContainer extends m_chachaBaseWidgetContainer
{
	const DEFAULT_PAGE_TITLE = 'プロフィール';
	const MEMBER_ID_LENGTH = 5;		// 会員IDの桁数
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
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
		return 'profile.tmpl.html';
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
		$act = $request->trimValueOf('act');
		$postTicket = $request->trimValueOf('ticket');		// POST確認用
		$memberId = $request->trimValueOf(self::URL_PARAM_MEMBER_ID);	// 会員ID

		// ##### ユーザの識別 #####
		// ユーザIDでユーザのプロフィールを参照。ユーザIDがない場合は自分自身のプロフィールを表示。
		// クライアントIDを取得
		$canEdit = false;			// データ編集可能かどうか
		$isNew = false;				// 新規登録かどうか
		
		// クライアントIDを取得
		$clientId = $this->_mobileId;

		// 会員情報が登録されている場合は更新。登録されていない場合は新規登録。
		$clientMemberId = '';			// 現在の端末の会員ID
		$ret = $this->_db->getMemberInfoByDeviceId($clientId, $row);
		if ($ret) $clientMemberId = $row['mb_id'];
		if (empty($memberId)){
			if (empty($clientMemberId)){
				$isNew = true;					// 新規登録処理
			} else {
				$memberId = $clientMemberId;
			}
			$canEdit = true;			// データ編集可能かどうか
		} else if ($memberId == $clientMemberId){		// 自分自身のデータのとき
			$canEdit = true;			// データ編集可能かどうか
		}

		$name = $request->mobileTrimValueOf('name');					// ユーザ名
		$email = $request->mobileTrimValueOf('email');				// Eメール
		$url = $request->mobileTrimValueOf('url');					// URL
		$avatar = '';			// アバターファイル名
		$showEmail = ($request->trimValueOf('show_email') == 'on') ? 1 : 0;		// Eメールアドレスを公開するかどうか
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'add'){		// 新規追加のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 入力チェック
				$this->checkInput($name, 'ﾆｯｸﾈｰﾑ');
				$this->checkMailAddress($email, 'Eﾒｰﾙ', true/*空OK*/);
				$this->checkInput($clientId, '端末ID');
			
				if ($this->_db->isExistsMemberName($name)) $this->setUserErrorMsg('このﾆｯｸﾈｰﾑはすでに存在しています');
				
				// エラーなしの場合は、データを登録
				if ($this->getMsgCount() == 0){
					// 会員ID作成
					$memberId = $this->createMemberId();
					if (empty($memberId)){
						$this->setAppErrorMsg('IDが作成できません');
					} else {
						// 一般ユーザの場合はユーザIDも登録
						$userId = 0;
						$userInfo = $this->gEnv->getCurrentUserInfo();
						if (!is_null($userInfo) && $userInfo->userType == UserInfo::USER_TYPE_NORMAL) $userId = $this->gEnv->getCurrentUserId();
						
						$ret = $this->_db->addMember($clientId, $memberId, $userId, $name, $email, $url, ''/*アバターファイル名*/, $showEmail);
						if ($ret){
							$this->setGuidanceMsg('登録完了しました');
							$reloadData = true;		// データの再読み込み
							
							$isNew = false;					// 更新処理画面を表示
						}
					}
				}
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		} else if ($act == 'update'){		// 設定更新のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 更新権限のチェック
				if (!$canEdit) $this->setUserErrorMsg('更新権限がありません');
				
				// 入力チェック
				$this->checkInput($name, 'ﾆｯｸﾈｰﾑ');
				$this->checkMailAddress($email, 'Eﾒｰﾙ', true/*空OK*/);
				$this->checkInput($clientId, '端末ID');
			
				$ret = $this->_db->getMemberInfoByDeviceId($clientId, $row);
				if ($ret){
					if ($name != $row['mb_name'] && $this->_db->isExistsMemberName($name)) $this->setUserErrorMsg('このﾆｯｸﾈｰﾑはすでに存在しています');
				}
								
				// エラーなしの場合は、データを更新
				if ($this->getMsgCount() == 0){
					$ret = $this->_db->updateMember($clientId, $name, $email, $url, $avatar, $showEmail, $newSerial);
					if ($ret){
						$this->setGuidanceMsg('更新完了しました');
						$reloadData = true;		// データの再読み込み
					}
				}
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
		} else {
			$reloadData = true;		// データの再読み込み
		}
		
		// データの再取得
		if ($reloadData){
			$ret = $this->_db->getMemberInfoById($memberId, $row);
			if ($ret){
				$name = $row['mb_name'];		// ユーザ名
				$email = $row['mb_email'];	// Eメール
				$url = $row['mb_url'];		// URL
				$showEmail = $row['mb_show_email'];		// Eメールを公開するかどうか
			} else if (!empty($memberId)){
				$this->setUserErrorMsg('登録されていないﾕｰｻﾞです');
				$memberId = '';		// 会員ID初期化
				$canEdit = false;			// データ編集不可
			}
		}
		// リンク作成
		$mypageUrl = '';
		//if (!empty($memberId)) $mypageUrl = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId, true));
		if (!empty($memberId)) $mypageUrl = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile(self::URL_PARAM_MEMBER_ID . '=' . $memberId)));
					
		// 編集状態の設定
		if ($canEdit){		// 編集可の場合
			// 各部の表示制御
			if ($isNew){		// 新規登録のとき
				// メッセージ
				if ($this->getMsgCount() == 0) $this->setGuidanceMsg('ﾕｰｻﾞ登録して下さい');
				
				$this->tmpl->setAttribute('add_area', 'visibility', 'visible');// 新規登録ボタン表示
				$this->tmpl->addVar('_widget', 'act', 'add');		// 新規登録
			} else {		// 更新のとき
				$this->tmpl->setAttribute('update_area', 'visibility', 'visible');// 更新ボタン表示
				$this->tmpl->addVar('_widget', 'act', 'update');		// 更新
			}
			// 各入力部表示
			$this->tmpl->setAttribute('name_input_area', 'visibility', 'visible');	// 名前編集
			$this->tmpl->setAttribute('name_required_area', 'visibility', 'visible');		// 「必須」メッセージ
			$this->tmpl->setAttribute('email_area', 'visibility', 'visible');				// Eメール入力
			$this->tmpl->setAttribute('url_area', 'visibility', 'visible');				// URL入力
			$this->tmpl->setAttribute('show_email_area', 'visibility', 'visible');			// Eメール公開
			
			// リンクを表示
			$mypageName = '';// マイページURL
			$mypageLink = '';
			if (!empty($mypageUrl)){
				$mypageName = '投稿[3]';
				$mypageLink = '<a href="' . $mypageUrl . '" accesskey="3">投稿[3]</a><br />';
			}
			$this->tmpl->addVar("_widget", "mypage_name", $mypageName);
			$this->tmpl->addVar("_widget", "mypage_link", $mypageLink);
			//$this->tmpl->setAttribute('top_link_area', 'visibility', 'visible');
			//$this->tmpl->addVar("_widget", "mypage_url", $mypageUrl);			// マイページURL
			
			// 値の埋め込み
			$this->tmpl->addVar("name_input_area", "name", $name);		// ユーザ名
			$this->tmpl->addVar("show_email_area", "email", $email);	// Eメール
			$this->tmpl->addVar("url_area", "url", $url);		// URL
			if ($showEmail) $this->tmpl->addVar("show_email_area", "show_email", 'checked');		// Eメールを公開するかどうか
			$this->tmpl->addVar('_widget', 'current_url', $this->gEnv->createCurrentPageUrlForMobile('task=' . self::TASK_PROFILE));
			
			// ハッシュキー作成
			$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
			$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
		} else {
			if (!empty($memberId)){
				// 各部の表示制御
				$this->tmpl->setAttribute('name_area', 'visibility', 'visible');	// 名前表示
				if ($showEmail) $this->tmpl->setAttribute('email_area', 'visibility', 'visible');
				
				// 値の埋め込み
				$this->tmpl->addVar("name_area", "name", $name);		// ユーザ名
				$this->tmpl->addVar("name_area", "mypage_url", $mypageUrl);			// マイページURL
				$this->tmpl->addVar("email_area", "email", $email);			// Eメール
				$urlStr = '';
				if (!empty($url)) $urlStr = '<a href="' . $this->convertUrlToHtmlEntity($url) . '">' . $this->convertToDispString($url) . '</a>';
				$this->tmpl->addVar("_widget", "url", $urlStr);		// URL
			}
		}
		
		$this->tmpl->addVar("_widget", "page_title", self::DEFAULT_PAGE_TITLE);		// ページタイトル
		
		$memberIdStr = '';
		if (!empty($mypageUrl)) $memberIdStr = '<a href="' . $mypageUrl . '">' . $this->convertToDispString($memberId) . '</a>';
		$this->tmpl->addVar("_widget", "id", $memberIdStr);		// マイブログページへのリンク
		
		// アバター画像
		$avatarImageUrl = $this->getAvatarUrl($memberId);// アバター画像URL
		$imageTag = '<img src="' . $this->getUrl($avatarImageUrl) . '" width="' . self::AVATAR_SIZE . '" height="' . self::AVATAR_SIZE .'" />';
		$this->tmpl->addVar("_widget", "avatar_img", $imageTag);		// 画像
	}
	/**
	 * 会員IDを作成
	 *
	 * @return string				会員ID
	 */
	function createMemberId()
	{
		$memberId = '';
		
		for ($i = 0; $i < self::CREATE_CODE_RETRY_COUNT; $i++){
			// 「0」除くランダム文字列を作成
			$memberId = $this->_createRandString('123456789', self::MEMBER_ID_LENGTH);
		
			// すでに登録済みかどうかチェック
			$ret = $this->_db->isExistsMemberId($memberId);
			if (!$ret) break;
		}
		return $memberId;
	}
	/**
	 * 画像の種別を取得
	 *
	 * @param string $mime	MIMEコンテンツタイプ
	 * @return string		画像の種別
	 */
	function getImageType($mime)
	{
		if ($mime != ''){
			if ($mime == 'image/gif')	return 'gif';
			if ($mime == 'image/jpeg')	return 'jpeg';
			if ($mime == 'image/jpg')	return 'jpeg';
			if ($mime == 'image/pjpeg')	return 'jpeg';
			if ($mime == 'image/png')	return 'png';
		}
		return '';
	}		
	/**
	 * サムネールを作成
	 *
	 * @param string $type	MIMEコンテンツタイプ
	 * @param string $path	拡張子
	 * @param int $size		サムネールの縦横サイズ
	 * @return object		画像オブジェクト
	 */
	function createThumb($type, $path, $size)
	{
		// 画像作成
		switch ($type){
			case "jpeg":
				$img = @imagecreatefromjpeg($path);
				break;
			case "gif":
				$img = @imagecreatefromgif($path);
				break;
			case "png":
				$img = @imagecreatefrompng($path);
				break;
			default:
				return false;
		}
		
		// size for thumbnail
		$width = imagesx($img);
		$height = imagesy($img);
		
		if ($width > $height){
			$n_height = $height * ($size / $width);
			$n_width = $size;
		} else {
			$n_width = $width * ($size / $height);
			$n_height = $size;
		}
		
		$x = 0;
		$y = 0;
		if ($n_width < $size) $x = round(($size - $n_width) / 2);
		if ($n_height < $size) $y = round(($size - $n_height) / 2);
		
		// imagecreatetruecolor
		$thumb = imagecreatetruecolor($size, $size);
		
		$bgcolor = imagecolorallocate($thumb, 255, 255, 255);
		imagefill($thumb, 0, 0, $bgcolor);
		
		// imagecopyresized (imagecopyresampled)
		if (function_exists("imagecopyresampled")){
			if (!imagecopyresampled($thumb, $img, $x, $y, 0, 0, $n_width, $n_height, $width, $height)){
				if (!imagecopyresized($thumb, $img, $x, $y, 0, 0, $n_width, $n_height, $width, $height)) return false;
			}
		} else {
			if (!imagecopyresized($thumb, $img, $x, $y, 0, 0, $n_width, $n_height, $width, $height)) return false;
		}
		return $thumb;
	}
	/**
	 * サムネールを出力
	 *
	 * @param string $type	MIMEコンテンツタイプ
	 * @param object $image	画像オブジェクト
	 * @param string $path	ファイル保存の場合のパス
	 * @return bool			true=成功、false=失敗
	 */
	function outputThumb($type, &$image, $path = null)
	{
		$ret = false;
		if (is_null($path)){
			switch ($type){
				case "jpeg":
					$ret = imagejpeg($image);
					break;
				case "gif":
					$ret = imagegif($image);
					break;
				case "png":
					$ret = imagepng($image);
					break;
			}
		} else {
			switch ($type){
				case "jpeg":
					$ret = imagejpeg($image, $path);
					break;
				case "gif":
					$ret = imagegif($image, $path);
					break;
				case "png":
					$ret = imagepng($image, $path);
					break;
			}
		}
		// イメージを破棄
		imagedestroy($image);
		
		return $ret;
	}
}
?>
