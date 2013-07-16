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
 * @version    SVN: $Id: chacha_mainProfileWidgetContainer.php 3352 2010-07-08 02:59:39Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/chacha_mainBaseWidgetContainer.php');

class chacha_mainProfileWidgetContainer extends chacha_mainBaseWidgetContainer
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
		$clientId = '';
		if ($this->gEnv->canUseCookie()){		// クッキー使用可能なとき
			$clientId = $this->gAccess->getClientId();
		} else {
			$this->setUserErrorMsg('クッキーを使用可にして下さい');
		}
		
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

		$name = $request->trimValueOf('name');					// ユーザ名
		$email = $request->trimValueOf('email');				// Eメール
		$url = $request->trimValueOf('url');					// URL
		$avatar = '';			// アバターファイル名
		$showEmail = ($request->trimValueOf('show_email') == 'on') ? 1 : 0;		// Eメールアドレスを公開するかどうか
		
		$reloadData = false;		// データの再読み込み
		if ($act == 'add'){		// 新規追加のとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 入力チェック
				$this->checkInput($name, 'ニックネーム');
				$this->checkMailAddress($email, 'Eメール', true/*空OK*/);
				$this->checkInput($clientId, '端末ID');
			
				if ($this->_db->isExistsMemberName($name)) $this->setUserErrorMsg('このニックネームはすでに存在しています');
				
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
				$this->checkInput($name, 'ニックネーム');
				$this->checkMailAddress($email, 'Eメール', true/*空OK*/);
				$this->checkInput($clientId, '端末ID');
			
				$ret = $this->_db->getMemberInfoByDeviceId($clientId, $row);
				if ($ret){
					if ($name != $row['mb_name'] && $this->_db->isExistsMemberName($name)) $this->setUserErrorMsg('このニックネームはすでに存在しています');
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
		} else if ($act == 'upload_avatar'){		// アバターファイルアップロードのとき
			if (!empty($postTicket) && $postTicket == $request->getSessionValue(M3_SESSION_POST_TICKET)){		// 正常なPOST値のとき
				// 更新権限のチェック
				if (!empty($clientMemberId) && $memberId == $clientMemberId){
					// アップロードされたファイルか？セキュリティチェックする
					if (isset($_FILES["upfile"]) && is_uploaded_file($_FILES['upfile']['tmp_name'])){
						$uploadFilename = $_FILES['upfile']['name'];		// アップロードされたファイルのファイル名取得

						// テンポラリディレクトリの書き込み権限をチェック
						if (!is_writable($this->gEnv->getWorkDirPath())){
							$msg = 'アップロードに失敗しました';
							$this->setAppErrorMsg($msg);
							
							// 運用ログに記録
							$this->writeError(__METHOD__, 'アップロードに失敗しました。一時ディレクトリの書き込み権限がありません。', 1100, 'ディレクトリ=' . $this->gEnv->getWorkDirPath());
						}
						if ($this->getMsgCount() == 0){		// エラーが発生していないとき
							$errMsg = '';		// エラーメッセージ
							$isErr = false;		// エラーが発生したかどうか
							
							// ファイルを保存するサーバディレクトリを指定
							$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);

							// アップされたテンポラリファイルを保存ディレクトリにコピー
							$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
							if ($ret){
								// 画像のファイルタイプをチェック
								$resizeImage = true;		// 画像をリサイズするかどうか
								$imageSize = getimagesize($tmpFile);
								$imageType = $this->getImageType($imageSize['mime']);
								
								// ファイル拡張子のチェック
								if (empty($imageType)){
									$errMsg = 'ファイル形式が不明です';
									$isErr = true;		// エラー発生
								}
								
								// デフォルトの画像と同じ規格の場合はアップロードされた画像をそのまま使用
								if ($imageSize[0] == self::AVATAR_SIZE && $imageSize[1] == self::AVATAR_SIZE && $imageType == self::DEFAULT_AVATAR_FILE_EXT) $resizeImage = false;

								// アバター小のオブジェクトを作成
								$imageObj2 = $this->createThumb($imageType, $tmpFile, self::SMALL_AVATAR_SIZE);
								
								// 画像をリサイズしファイルに保存
								if ($resizeImage){
									if (!$isErr){
										$imageObj = $this->createThumb($imageType, $tmpFile, self::AVATAR_SIZE);
										if ($imageObj !== false){
											$ret = $this->outputThumb(self::DEFAULT_AVATAR_FILE_EXT, $imageObj, $tmpFile);
											if (!$ret){
												$errMsg = '画像ファイル作成に失敗しました';
												$isErr = true;		// エラー発生
												
												// 運用ログに記録
												$this->writeError(__METHOD__, '画像ファイル作成に失敗しました。', 1100, 'ファイル名=' . $tmpFile);
											}
										} else {
											$errMsg = '画像ファイル作成に失敗しました';
											$isErr = true;		// エラー発生
										}
									}
								}
												
								// アバターファイルのディレクトリに移動
								if (!$isErr){
									$imageFilename = $memberId . '.' . self::DEFAULT_AVATAR_FILE_EXT;
									$imagePath = $this->gEnv->getResourcePath() . self::AVATAR_DIR . '/' . $imageFilename;
									$tmpImagePath = '';
									
									// ファイルが存在している場合は退避
									if (file_exists($imagePath)){
										$tmpImagePath = $this->gEnv->getResourcePath() . self::AVATAR_DIR . '/_' . $imageFilename;
										mvFile($imagePath, $tmpImagePath);
									}
									$ret = mvFile($tmpFile, $imagePath);
									if ($ret){
										if (!empty($tmpImagePath)) unlink($tmpImagePath);// 退避ファイル削除
										
										// ##### アバターアップロードに成功したときはアバター小を作成 #####
										// 一時ファイル作成
										$tmpFile2 = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
										
										// アバター小作成
										if ($imageObj2 !== false){
											$ret = $this->outputThumb(self::DEFAULT_SMALL_AVATAR_FILE_EXT, $imageObj2, $tmpFile2);
										
											// ファイルの移動
											if ($ret){
												$imageFilename = $memberId . '.' . self::DEFAULT_SMALL_AVATAR_FILE_EXT;
												$imagePath = $this->gEnv->getResourcePath() . self::AVATAR32_DIR . '/' . $imageFilename;
												mvFile($tmpFile2, $imagePath);
											}
										}
									} else {
										$errMsg = 'ファイルが移動できません';		// エラーメッセージ
										$isErr = true;		// エラー発生
										
										// 退避ファイルを戻す
										if (!empty($tmpImagePath)) mvFile($tmpImagePath, $imagePath);
									}
								}
								// アバターファイル名を更新
							} else {
								$errMsg = 'ファイルが移動できません';		// エラーメッセージ
								$isErr = true;		// エラー発生
							}
							if ($isErr){
								$this->setUserErrorMsg($errMsg);
							
								// テンポラリファイル削除
								unlink($tmpFile);
							} else {
								$this->setGuidanceMsg('アバターを更新しました');
							}
						}
					} else {
						$msg = 'アップロードに失敗しました';
						$this->setUserErrorMsg($msg);
					}
				} else {		// 更新権限がないとき
					$this->setUserErrorMsg('更新権限がありません');
				}
			}
			$request->unsetSessionValue(M3_SESSION_POST_TICKET);		// セッション値をクリア
			$reloadData = true;		// データの再読み込み
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
				$this->setUserErrorMsg('登録されていないユーザです');
				$memberId = '';		// 会員ID初期化
				$canEdit = false;			// データ編集不可
			}
		}
		// リンク作成
		$mypageUrl = '';
		if (!empty($memberId)) $mypageUrl = $this->convertUrlToHtmlEntity($this->getUrl($this->_currentPageUrl . '&' . self::URL_PARAM_MEMBER_ID . '=' . $memberId, true));
		
		// 編集状態の設定
		if ($canEdit){		// 編集可の場合
			// 各部の表示制御
			if ($isNew){		// 新規登録のとき
				$this->tmpl->setAttribute('add_area', 'visibility', 'visible');// 新規登録ボタン表示
			} else {		// 更新のとき
				$this->tmpl->setAttribute('update_area', 'visibility', 'visible');// 更新ボタン表示
				
				// アバター画像のアップロード部表示
				$this->tmpl->setAttribute('upload_area', 'visibility', 'visible');
			}
			// 各入力部表示
			$this->tmpl->setAttribute('name_required_area', 'visibility', 'visible');		// 「必須」メッセージ
			$this->tmpl->setAttribute('email_area', 'visibility', 'visible');				// Eメール入力
			$this->tmpl->setAttribute('url_area', 'visibility', 'visible');				// URL入力
			$this->tmpl->setAttribute('show_email_area', 'visibility', 'visible');			// Eメール公開
			
			// マイページへのリンクを表示
			$this->tmpl->setAttribute('top_link_area', 'visibility', 'visible');
			$this->tmpl->addVar("top_link_area", "mypage_url", $mypageUrl);			// マイページURL
			
			// 値の埋め込み
			$this->tmpl->addVar("show_email_area", "email", $email);	// Eメール
			$this->tmpl->addVar("url_area", "url", $url);		// URL
			if ($showEmail) $this->tmpl->addVar("show_email_area", "show_email", 'checked');		// Eメールを公開するかどうか
			
			// ハッシュキー作成
			$postTicket = md5(time() . $this->gAccess->getAccessLogSerialNo());
			$request->setSessionValue(M3_SESSION_POST_TICKET, $postTicket);		// セッションに保存
			$this->tmpl->addVar("_widget", "ticket", $postTicket);				// 画面に書き出し
			$this->tmpl->addVar("upload_area", "ticket", $postTicket);				// 画面に書き出し
		} else {
			$this->tmpl->addVar("_widget", "name_disabled", "disabled");		// ユーザ名
						
			if (!empty($memberId)){
				// 各部の表示制御
				if ($showEmail) $this->tmpl->setAttribute('email_area', 'visibility', 'visible');
				
				// 値の埋め込み
				$this->tmpl->addVar("email_area", "email", $email);			// Eメール
				$urlStr = '';
				if (!empty($url)) $urlStr = '<a href="' . $this->convertUrlToHtmlEntity($url) . '">' . $this->convertToDispString($url) . '</a>';
				$this->tmpl->addVar("_widget", "url", $urlStr);		// URL
			}
		}
		
		// 表示設定
		$makeThreadStyle = self::CSS_BLOG_INNER_STYLE;
		$makeThreadColor = $this->_configArray[self::CF_PROFILE_COLOR];		// プロフィール背景色
		if (empty($makeThreadColor)) $makeThreadColor = $this->_configArray[self::CF_INNER_BG_COLOR];		// デフォルトの内枠背景色
		if (!empty($makeThreadColor)) $makeThreadStyle .= 'background-color:' . $makeThreadColor . ';';
		$this->tmpl->addVar("_widget", "make_thread_style", $makeThreadStyle);
		
		$this->tmpl->addVar("_widget", "page_title", self::DEFAULT_PAGE_TITLE);		// ページタイトル
		$this->tmpl->addVar("_widget", "name", $name);		// ユーザ名
		$this->tmpl->addVar("_widget", "avatar_id", self::AVATAR_TAG_ID);		// アバターのタグID
		
		$memberIdStr = '';
		if (!empty($mypageUrl)) $memberIdStr = '<a href="' . $mypageUrl . '">' . $this->convertToDispString($memberId) . '</a>';
		$this->tmpl->addVar("_widget", "id", $memberIdStr);		// マイブログページへのリンク
		
		// アバター画像
		$avatarImageUrl = $this->getAvatarUrl($memberId);// アバター画像URL
		$imageTag = '<img id="' . self::AVATAR_TAG_ID . '" src="' . $this->getUrl($avatarImageUrl) . '" width="' . self::AVATAR_SIZE . '" height="' . self::AVATAR_SIZE .'" />';
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
