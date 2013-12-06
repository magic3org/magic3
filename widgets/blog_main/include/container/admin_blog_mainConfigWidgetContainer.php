<?php
/**
 * コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() . '/admin_blog_mainBaseWidgetContainer.php');

class admin_blog_mainConfigWidgetContainer extends admin_blog_mainBaseWidgetContainer
{
	const DEFAULT_VIEW_COUNT	= 10;				// デフォルトの表示記事数
		
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
		return 'admin_config.tmpl.html';
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
		$defaultLang	= $this->gEnv->getDefaultLanguage();
				
		$act = $request->trimValueOf('act');
		$entryViewCount = $request->trimValueOf('entry_view_count');		// 記事表示数
		$entryViewOrder = $request->trimValueOf('entry_view_order');		// 記事表示順
		$categoryCount = $request->trimValueOf('category_count');		// カテゴリ数
		$receiveComment = ($request->trimValueOf('receive_comment') == 'on') ? 1 : 0;		// コメントを受け付けるかどうか
		$useMultiBlog = ($request->trimValueOf('use_multi_blog') == 'on') ? 1 : 0;		// マルチブログ機能を使用するかどうか
		$topContent = $request->valueOf('top_content');	// トップコンテンツ
		$maxCommentLength = $request->valueOf('max_comment_length');	// コメント最大文字数
		$layoutEntrySingle = $request->valueOf('item_layout_entry_single');					// コンテンツレイアウト(記事詳細)
		$layoutEntryList = $request->valueOf('item_layout_entry_list');					// コンテンツレイアウト(記事一覧)
		$layoutCommentList = $request->valueOf('item_layout_comment_list');					// コンテンツレイアウト(コメント一覧)
		$outputHead	= ($request->trimValueOf('item_output_head') == 'on') ? 1 : 0;		// ヘッダ出力するかどうか
		$headViewDetail = $request->valueOf('item_head_view_detail');					// ヘッダ出力(詳細表示)
		$commentUserLimited = ($request->trimValueOf('comment_user_limited') == 'on') ? 1 : 0;		// コメントのユーザ制限
		if (strlen($topContent) <= 10){ // FCKEditorのバグの対応(空の場合でもBRタグが送信される)
			$topContent = '';
		}
		$titleSearchList	= $request->trimValueOf('item_title_search_list');		// 検索結果タイトル
		$titleNoEntry		= $request->trimValueOf('item_title_no_entry');		// 記事なし時タイトル
		$messageNoEntry		= $request->trimValueOf('item_message_no_entry');		// 記事が登録されていないメッセージ
		$messageFindNoEntry = $request->trimValueOf('item_message_find_no_entry');		// 記事が見つからないメッセージ
		
		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($entryViewCount, '記事表示順');
			$this->checkNumeric($maxCommentLength, 'コメント最大文字数');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				// 空の場合はデフォルト値を設定
				if (empty($titleSearchList)) $titleSearchList = blog_mainCommonDef::DEFAULT_TITLE_SEARCH_LIST;// 検索結果タイトル
				if (empty($titleNoEntry)) $titleNoEntry = blog_mainCommonDef::DEFAULT_TITLE_NO_ENTRY;	// 記事なし時タイトル
				if (empty($messageNoEntry)) $messageNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_NO_ENTRY;// 記事が登録されていないメッセージ
				if (empty($messageFindNoEntry)) $messageFindNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_FIND_NO_ENTRY;	// 記事が見つからないメッセージ
			
				$ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_ENTRY_VIEW_COUNT, $entryViewCount);				// 記事表示数
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_ENTRY_VIEW_ORDER, $entryViewOrder);	// 記事表示順
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_CATEGORY_COUNT, $categoryCount);		// カテゴリ数
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_RECEIVE_COMMENT, $receiveComment);		// コメントを受け付けるかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_MAX_COMMENT_LENGTH, $maxCommentLength);// コメント最大文字数
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_COMMENT_USER_LIMITED, $commentUserLimited);// コメントのユーザ制限
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_USE_MULTI_BLOG, $useMultiBlog);		// マルチブログ機能を使用するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_MULTI_BLOG_TOP_CONTENT, $topContent);	// マルチブログ時のトップコンテンツ
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE, $layoutEntrySingle);		// コンテンツレイアウト(記事詳細)
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_LAYOUT_ENTRY_LIST, $layoutEntryList);		// コンテンツレイアウト(記事一覧)
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_LAYOUT_COMMENT_LIST, $layoutCommentList);		// コンテンツレイアウト(コメント一覧)
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_OUTPUT_HEAD, $outputHead);		// ヘッダ出力するかどうか
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_HEAD_VIEW_DETAIL, $headViewDetail);	// ヘッダ出力(詳細表示)
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_TITLE_SEARCH_LIST, $titleSearchList);		// 検索結果タイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_TITLE_NO_ENTRY, $titleNoEntry);		// 記事なし時タイトル
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_MESSAGE_NO_ENTRY, $messageNoEntry);		// 記事が登録されていないメッセージ
				if ($ret) $ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_MESSAGE_FIND_NO_ENTRY, $messageFindNoEntry);		// 記事が見つからないメッセージ
																										
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else if ($act == 'upload'){		// 画像アップロードのとき
			// アップロードされたファイルか？セキュリティチェックする
			if (is_uploaded_file($_FILES['upfile']['tmp_name'])){
				// テンポラリディレクトリの書き込み権限をチェック
				if (!is_writable($this->gEnv->getWorkDirPath())){
					$msg = sprintf('一時ディレクトリに書き込み権限がありません。(ディレクトリ：%s)', $this->gEnv->getWorkDirPath());
					$this->setAppErrorMsg($msg);
				}
				
				if ($this->getMsgCount() == 0){		// エラーが発生していないとき
					// ファイルを保存するサーバディレクトリを指定
					$tmpFile = tempnam($this->gEnv->getWorkDirPath(), M3_SYSTEM_WORK_UPLOAD_FILENAME_HEAD);
		
					// アップされたテンポラリファイルを保存ディレクトリにコピー
					$ret = move_uploaded_file($_FILES['upfile']['tmp_name'], $tmpFile);
					if ($ret){
						// サムネール作成
						$ret = $this->gInstance->getImageManager()->createSystemDefaultThumb(M3_VIEW_TYPE_BLOG, blog_mainCommonDef::$_deviceType, 0/*記事ID*/, $tmpFile, $destFilename);
						if ($ret){
							$entryDefaultImages = implode(';', $destFilename);
							$ret = self::$_mainDb->updateConfig(blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE, $entryDefaultImages);
						}
						if ($ret){
							$msg = '画像を変更しました';
							$this->setGuidanceMsg($msg);
						} else {
							$msg = '画像の作成に失敗しました';
							$this->setAppErrorMsg($msg);
						}
					} else {
						$msg = 'ファイルのアップロードに失敗しました';
						$this->setAppErrorMsg($msg);
					}
					// テンポラリファイル削除
					unlink($tmpFile);
				}
			} else {
				$msg = sprintf('アップロードファイルが見つかりません(要因：アップロード可能なファイルのMaxサイズを超えている可能性があります。%sバイト)', $this->gSystem->getMaxFileSizeForUpload());
				$this->setAppErrorMsg($msg);
			}
		} else {		// 初期表示の場合
			$entryViewCount	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_ENTRY_VIEW_COUNT);// 記事表示数
			if (empty($entryViewCount)) $entryViewCount = self::DEFAULT_VIEW_COUNT;
			$entryViewOrder	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_ENTRY_VIEW_ORDER);// 記事表示順
			$categoryCount	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_CATEGORY_COUNT);// カテゴリ数
			if (empty($categoryCount)) $categoryCount = self::DEFAULT_CATEGORY_COUNT;
			$receiveComment	= self::$_mainDb->getConfig(blog_mainCommonDef::CF_RECEIVE_COMMENT);
			$maxCommentLength = self::$_mainDb->getConfig(blog_mainCommonDef::CF_MAX_COMMENT_LENGTH);	// コメント最大文字数
			if ($maxCommentLength == '') $maxCommentLength = self::DEFAULT_COMMENT_LENGTH;
			$commentUserLimited = self::$_mainDb->getConfig(blog_mainCommonDef::CF_COMMENT_USER_LIMITED);	// コメントのユーザ制限
			if (!isset($commentUserLimited)) $commentUserLimited = '0';
			$useMultiBlog = self::$_mainDb->getConfig(blog_mainCommonDef::CF_USE_MULTI_BLOG);// マルチブログ機能を使用するかどうか
			if (!isset($useMultiBlog)) $useMultiBlog = '0';
			$topContent = self::$_mainDb->getConfig(blog_mainCommonDef::CF_MULTI_BLOG_TOP_CONTENT);// マルチブログ時のトップコンテンツ
			$layoutEntrySingle = self::$_mainDb->getConfig(blog_mainCommonDef::CF_LAYOUT_ENTRY_SINGLE);		// コンテンツレイアウト(記事詳細)
			if (empty($layoutEntrySingle)) $layoutEntrySingle = blog_mainCommonDef::DEFAULT_LAYOUT_ENTRY_SINGLE;
			$layoutEntryList = self::$_mainDb->getConfig(blog_mainCommonDef::CF_LAYOUT_ENTRY_LIST);		// コンテンツレイアウト(記事一覧)
			if (empty($layoutEntryList)) $layoutEntryList = blog_mainCommonDef::DEFAULT_LAYOUT_ENTRY_LIST;
			$layoutCommentList = self::$_mainDb->getConfig(blog_mainCommonDef::CF_LAYOUT_COMMENT_LIST);		// コンテンツレイアウト(コメント一覧)
			if (empty($layoutCommentList)) $layoutCommentList = blog_mainCommonDef::DEFAULT_LAYOUT_COMMENT_LIST;
			$outputHead = self::$_mainDb->getConfig(blog_mainCommonDef::CF_OUTPUT_HEAD);		// ヘッダ出力するかどうか
			$headViewDetail = self::$_mainDb->getConfig(blog_mainCommonDef::CF_HEAD_VIEW_DETAIL);		// ヘッダ出力(詳細表示)
			$titleSearchList = self::$_mainDb->getConfig(blog_mainCommonDef::CF_TITLE_SEARCH_LIST);		// 検索結果タイトル
			if (empty($titleSearchList)) $titleSearchList = blog_mainCommonDef::DEFAULT_TITLE_SEARCH_LIST;
			$titleNoEntry = self::$_mainDb->getConfig(blog_mainCommonDef::CF_TITLE_NO_ENTRY);		// 記事なし時タイトル
			if (empty($titleNoEntry)) $titleNoEntry = blog_mainCommonDef::DEFAULT_TITLE_NO_ENTRY;
			$messageNoEntry = self::$_mainDb->getConfig(blog_mainCommonDef::CF_MESSAGE_NO_ENTRY);		// 記事が登録されていないメッセージ
			if (empty($messageNoEntry)) $messageNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_NO_ENTRY;
			$messageFindNoEntry = self::$_mainDb->getConfig(blog_mainCommonDef::CF_MESSAGE_FIND_NO_ENTRY);		// 記事が見つからないメッセージ
			if (empty($messageFindNoEntry)) $messageFindNoEntry = blog_mainCommonDef::DEFAULT_MESSAGE_FIND_NO_ENTRY;
		}
		// 画面に書き戻す
		$this->tmpl->addVar("_widget", "view_count", $entryViewCount);// 記事表示数
		if (empty($entryViewOrder)){	// 順方向
			$this->tmpl->addVar("_widget", "view_order_inc_selected", 'selected');// 記事表示順
		} else {
			$this->tmpl->addVar("_widget", "view_order_dec_selected", 'selected');// 記事表示順
		}
		
		// 記事デフォルト画像
		$entryDefaultImageUrl = '';
		$value = self::$_mainDb->getConfig(blog_mainCommonDef::CF_ENTRY_DEFAULT_IMAGE);
		if (!empty($value)){
			$entryDefaultImages = explode(';', $value);
			$entryDefaultImageUrl = $this->gInstance->getImageManager()->getSystemThumbUrl(M3_VIEW_TYPE_BLOG, blog_mainCommonDef::$_deviceType, $entryDefaultImages[count($entryDefaultImages) -1]);
			$entryDefaultImage = '<img src="' . $this->convertUrlToHtmlEntity($this->getUrl($entryDefaultImageUrl . '?' . date('YmdHis'))) . '" />';
			$this->tmpl->addVar("_widget", "entry_default_image", $entryDefaultImage);
		}
		
		$this->tmpl->addVar("_widget", "category_count", $categoryCount);// カテゴリ数
		$checked = '';
		if ($receiveComment) $checked = 'checked';
		$this->tmpl->addVar("_widget", "receive_comment", $checked);// コメントを受け付けるかどうか
		$this->tmpl->addVar("_widget", "max_comment_length", $maxCommentLength);// コメント最大文字数
		$checked = '';
		if ($commentUserLimited) $checked = 'checked';
		$this->tmpl->addVar("_widget", "comment_user_limited", $checked);// コメントのユーザ制限
		$checked = '';
		if ($useMultiBlog) $checked = 'checked';
		$this->tmpl->addVar("_widget", "use_multi_blog", $checked);// マルチブログ機能を使用するかどうか
		$this->tmpl->addVar("_widget", "top_content", $topContent);		// マルチブログ時のトップコンテンツ
		$this->tmpl->addVar("_widget", "layout_entry_single", $layoutEntrySingle);		// コンテンツレイアウト(記事詳細)
		$this->tmpl->addVar("_widget", "layout_entry_list", $layoutEntryList);		// コンテンツレイアウト(記事一覧)
		$this->tmpl->addVar("_widget", "layout_comment_list", $layoutCommentList);		// コンテンツレイアウト(コメント一覧)
		$checked = '';
		if (!empty($outputHead)) $checked = 'checked';
		$this->tmpl->addVar("_widget", "output_head_checked", $checked);		// ヘッダ出力するかどうか
		$this->tmpl->addVar("_widget", "head_view_detail", $headViewDetail);		// ヘッダ出力(詳細表示)
		$this->tmpl->addVar("_widget", "title_search_list", $titleSearchList);		// 検索結果タイトル
		$this->tmpl->addVar("_widget", "title_no_entry", $titleNoEntry);		// 記事なし時タイトル
		$this->tmpl->addVar("_widget", "message_no_entry", $messageNoEntry);		// 記事が登録されていないメッセージ
		$this->tmpl->addVar("_widget", "message_find_no_entry", $messageFindNoEntry);		// 記事が見つからないメッセージ
	}
}
?>
