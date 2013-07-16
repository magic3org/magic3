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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: admin_custom_headerWidgetContainer.php 5166 2012-09-06 01:21:31Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_custom_headerWidgetContainer extends BaseAdminWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $titleAlign;			// ヘッダタイトル表示位置
	private $descAlign;			// ヘッダ説明表示位置
	private $urlAlign;	// ヘッダURL表示位置
	const IMAGE_DIR = 'image';				// 画像ディレクトリ名
	const DEFAULT_IMAGE = 'header9.png';		// デフォルトのヘッダ画像
	const DEFAULT_BG_COLOR = '#FFCC00';			// バックグランドデフォルトカラー
	private $itemAlignArray;	// 表示位置
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 表示位置
		$this->itemAlignArray = array(	array(	'name' => '指定なし',	'value' => ''),
										array(	'name' => '左寄せ',	'value' => 'left'),
										array(	'name' => '中央',	'value' => 'center'),
										array(	'name' => '右寄せ',	'value' => 'right'));
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
		return 'admin.tmpl.html';
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
		$filename = $request->trimValueOf('item_image');		// 画像ファイル名
		$act = $request->trimValueOf('act');
		if ($act == 'update'){		// 設定更新のとき
			// 入力値を取得
			$image_mtop = $request->trimValueOf('item_image_mtop');			// ヘッダ画像マージンtop
			$image_mleft = $request->trimValueOf('item_image_mleft');			// ヘッダ画像マージンleft
			$image_mright = $request->trimValueOf('item_image_mright');			// ヘッダ画像マージンright
			$image_mbottom = $request->trimValueOf('item_image_mbottom');			// ヘッダ画像マージンbottom
		//	$this->imageAlign	= $request->trimValueOf('item_image_align');	// ヘッダ画像表示位置
			$title	= $request->valueOf('item_title');			// ヘッダタイトル(HTMLを許可)
			$title_mtop = $request->trimValueOf('item_title_mtop');			// ヘッダタイトルマージンtop
			$title_mleft = $request->trimValueOf('item_title_mleft');			// ヘッダタイトルマージンleft
			$title_mright = $request->trimValueOf('item_title_mright');			// ヘッダタイトルマージンright
			$title_mbottom = $request->trimValueOf('item_title_mbottom');			// ヘッダタイトルマージンbottom
			$this->titleAlign	= $request->trimValueOf('item_title_align');	// ヘッダタイトル表示位置
			$desc	= $request->valueOf('item_desc');	// ヘッダ説明(HTMLを許可)
			$desc_mtop = $request->trimValueOf('item_desc_mtop');			// ヘッダ説明マージンtop
			$desc_mleft = $request->trimValueOf('item_desc_mleft');			// ヘッダ説明マージンleft
			$desc_mright = $request->trimValueOf('item_desc_mright');			// ヘッダ説明マージンright
			$desc_mbottom = $request->trimValueOf('item_desc_mbottom');			// ヘッダ説明マージンbottom
			$this->descAlign	= $request->trimValueOf('item_desc_align');	// ヘッダ説明表示位置
			$url	= $request->valueOf('item_url');	// ヘッダURL(HTMLを許可)
			$url_mtop = $request->trimValueOf('item_url_mtop');			// ヘッダURLマージンtop
			$url_mleft = $request->trimValueOf('item_url_mleft');			// ヘッダURLマージンleft
			$url_mright = $request->trimValueOf('item_url_mright');			// ヘッダURLマージンright
			$url_mbottom = $request->trimValueOf('item_url_mbottom');			// ヘッダURLマージンbottom
			$this->urlAlign	= $request->trimValueOf('item_url_align');	// ヘッダURL表示位置
			$titleColor = $request->trimValueOf('item_title_color');		// ヘッダタイトルカラー
			$descColor = $request->trimValueOf('item_desc_color');		// ヘッダ説明カラー
			$urlColor = $request->trimValueOf('item_url_color');		// ヘッダURLカラー
			$bgcolor = $request->trimValueOf('item_bgcolor');		// ヘッダバックグランドカラー
			$width	= $request->trimValueOf('item_width');		// ヘッダの幅
			$height	= $request->trimValueOf('item_height');		// ヘッダの高さ
			$widthType	= $request->trimValueOf('item_widthtype');		// ヘッダの幅単位
			$heightType	= $request->trimValueOf('item_heighttype');		// ヘッダの高さ単位
			$titleFontsize	= $request->trimValueOf('item_title_fontsize');// タイトルのフォントサイズ
			$descFontsize	= $request->trimValueOf('item_desc_fontsize');// 説明のフォントサイズ
			$urlFontsize	= $request->trimValueOf('item_url_fontsize');	// URLのフォントサイズ
			$linkUrl		= $request->trimValueOf('item_link_url');		// リンク先
			
			// Pタグを除去
			$title = $this->gInstance->getTextConvManager()->deleteTag($title, 'p');
			$desc = $this->gInstance->getTextConvManager()->deleteTag($desc, 'p');
			$url = $this->gInstance->getTextConvManager()->deleteTag($url, 'p');
			
			// 画像の種類を取得
			$useOriginalImage	= $request->trimValueOf('item_sel_image');
			$imageUrl = '';
			if ($useOriginalImage == 1){		// メニューから画像を選択の場合
				$imageUrl = $this->gEnv->getCurrentWidgetRootUrl() . '/' . self::IMAGE_DIR . '/' . $filename;
			} else if ($useOriginalImage == 2){
				$imageUrl = $request->trimValueOf('item_image_url');
				if (!empty($imageUrl)){
					if (strncmp($imageUrl, '/', 1) == 0){		// 相対パス表記のとき
						$imageUrl = $this->gEnv->getRootUrl() . $this->gEnv->getRelativePathToSystemRootUrl($this->gEnv->getDocumentRootUrl() . $imageUrl);
					}
				}
			}
			// 入力値のエラーチェック
			$this->checkNumeric($width, 'ヘッダの幅');
			$this->checkNumeric($height, 'ヘッダの高さ');
			$this->checkNumeric($titleFontsize, 'タイトルのフォントサイズ');
			$this->checkNumeric($descFontsize, '説明のフォントサイズ');
			$this->checkNumeric($urlFontsize, 'URLのフォントサイズ');
			$this->checkNumber($image_mtop, '画像マージンtop', true);
			$this->checkNumber($image_mleft, '画像マージンleft', true);
			$this->checkNumber($image_mright, '画像マージンright', true);
			$this->checkNumber($image_mbottom, '画像マージンbottom', true);
			$this->checkNumber($title_mtop, 'タイトルマージンtop', true);
			$this->checkNumber($title_mleft, 'タイトルマージンleft', true);
			$this->checkNumber($title_mright, 'タイトルマージンright', true);
			$this->checkNumber($title_mbottom, 'タイトルマージンbottom', true);
			$this->checkNumber($desc_mtop, '説明マージンtop', true);
			$this->checkNumber($desc_mleft, '説明マージンleft', true);
			$this->checkNumber($desc_mright, '説明マージンright', true);
			$this->checkNumber($desc_mbottom, '説明マージンbottom', true);
			$this->checkNumber($url_mtop, 'URLマージンtop', true);
			$this->checkNumber($url_mleft, 'URLマージンleft', true);
			$this->checkNumber($url_mright, 'URLマージンright', true);
			$this->checkNumber($url_mbottom, 'URLマージンbottom', true);
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->title	= $title;			// ヘッダタイトル
				$paramObj->desc		= $desc;		// ヘッダ説明
				$paramObj->url		= $url;	// ヘッダURL
				$paramObj->image_mtop = $image_mtop;			// ヘッダ画像マージンtop
				$paramObj->image_mleft = $image_mleft;			// ヘッダ画像マージンleft
				$paramObj->image_mright = $image_mright;		// ヘッダ画像マージンright
				$paramObj->image_mbottom = $image_mbottom;		// ヘッダ画像マージンbottom
		//		$paramObj->imageAlign = $this->imageAlign;		// ヘッダ画像表示位置
				$paramObj->title_mtop = $title_mtop;			// ヘッダタイトルマージンtop
				$paramObj->title_mleft = $title_mleft;			// ヘッダタイトルマージンleft
				$paramObj->title_mright = $title_mright;			// ヘッダタイトルマージンright
				$paramObj->title_mbottom = $title_mbottom;			// ヘッダタイトルマージンbottom
				$paramObj->titleAlign = $this->titleAlign;	// ヘッダタイトル表示位置
				$paramObj->desc_mtop = $desc_mtop;			// ヘッダ説明マージンtop
				$paramObj->desc_mleft = $desc_mleft;			// ヘッダ説明マージンleft
				$paramObj->desc_mright = $desc_mright;			// ヘッダ説明マージンright
				$paramObj->desc_mbottom = $desc_mbottom;			// ヘッダ説明マージンbottom
				$paramObj->descAlign = $this->descAlign;	// ヘッダ説明表示位置
				$paramObj->url_mtop = $url_mtop;			// ヘッダURLマージンtop
				$paramObj->url_mleft = $url_mleft;			// ヘッダURLマージンleft
				$paramObj->url_mright = $url_mright;			// ヘッダURLマージンright
				$paramObj->url_mbottom = $url_mbottom;			// ヘッダURLマージンbottom
				$paramObj->urlAlign = $this->urlAlign;			// ヘッダURL表示位置
				$paramObj->titleColor = $titleColor;		// ヘッダタイトルカラー
				$paramObj->descColor = $descColor;		// ヘッダ説明カラー
				$paramObj->urlColor = $urlColor;		// ヘッダURLカラー
				$paramObj->bgcolor	= $bgcolor;	// ヘッダバックグランドカラー
				$paramObj->width	= $width;		// ヘッダの幅
				$paramObj->height	= $height;		// ヘッダの高さ
				$paramObj->widthType	= $widthType;		// ヘッダの幅単位
				$paramObj->heightType	= $heightType;		// ヘッダの高さ単位
				$paramObj->titleFontsize	= $titleFontsize;// タイトルのフォントサイズ
				$paramObj->descFontsize	= $descFontsize;// 説明のフォントサイズ
				$paramObj->urlFontsize	= $urlFontsize;	// URLのフォントサイズ
				$paramObj->useOriginalImage = $useOriginalImage;			// オリジナル画像を使用するかどうか
				$paramObj->imageUrl = $imageUrl;							// 画像へのパス
				$paramObj->linkUrl = $linkUrl;								// リンク先URL
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->clearCache();			// キャッシュをクリア
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値設定
			$title = 'title';	// ヘッダタイトル
			$desc = 'description';	// ヘッダ説明
			$url = 'http://www.sample.com';	// ヘッダURL
			$image_mtop = 0;			// ヘッダ画像マージンtop
			$image_mleft = 0;			// ヘッダ画像マージンleft
			$image_mright = 0;		// ヘッダ画像マージンright
			$image_mbottom = 0;		// ヘッダ画像マージンbottom
	//		$this->imageAlign = '';		// ヘッダ画像表示位置
			$title_mtop = 10;			// ヘッダタイトルマージンtop
			$title_mleft = 20;			// ヘッダタイトルマージンleft
			$title_mright = 0;			// ヘッダタイトルマージンright
			$title_mbottom = 0;			// ヘッダタイトルマージンbottom
			$this->titleAlign = '';	// ヘッダタイトル表示位置
			$desc_mtop = 10;			// ヘッダ説明マージンtop
			$desc_mleft = 30;			// ヘッダ説明マージンleft
			$desc_mright = 0;			// ヘッダ説明マージンright
			$desc_mbottom = 0;			// ヘッダ説明マージンbottom
			$this->descAlign = '';	// ヘッダ説明表示位置
			$url_mtop = 10;			// ヘッダURLマージンtop
			$url_mleft = 0;			// ヘッダURLマージンleft
			$url_mright = 20;			// ヘッダURLマージンright
			$url_mbottom = 0;			// ヘッダURLマージンbottom
			$this->urlAlign = 'right';	// ヘッダ説明表示位置
			$titleColor = '#FFFFFF';		// ヘッダタイトルカラー
			$descColor = '#FFFFFF';		// ヘッダ説明カラー
			$urlColor = '#FFFFFF';		// ヘッダURLカラー
			$bgcolor = '';					// ヘッダバックグランドカラー
			$width	= 100;		// ヘッダの幅
			$height	= 100;		// ヘッダの高さ
			$widthType = 0;		// ヘッダの幅単位
			$heightType = 1;		// ヘッダの高さ単位
			$titleFontsize	= 30;// タイトルのフォントサイズ
			$descFontsize	= 14;// 説明のフォントサイズ
			$urlFontsize	= 18;	// URLのフォントサイズ
			$useOriginalImage = 0;			// オリジナル画像を使用するかどうか
			//$imageUrl = '';							// 画像へのパス
			$imageUrl = $this->gEnv->getCurrentWidgetRootUrl() . '/' . self::IMAGE_DIR . '/' . self::DEFAULT_IMAGE;// 画像へのパス
					
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$title	= $paramObj->title;			// ヘッダタイトル
				$desc	= $paramObj->desc;	// ヘッダ説明
				$url	= $paramObj->url;	// ヘッダURL
				$image_mtop = $paramObj->image_mtop;			// ヘッダ画像マージンtop
				$image_mleft = $paramObj->image_mleft;			// ヘッダ画像マージンleft
				$image_mright = $paramObj->image_mright;		// ヘッダ画像マージンright
				$image_mbottom = $paramObj->image_mbottom;		// ヘッダ画像マージンbottom
		//		$this->imageAlign = $paramObj->imageAlign;		// ヘッダ画像表示位置
				$title_mtop = $paramObj->title_mtop;			// ヘッダタイトルマージンtop
				$title_mleft = $paramObj->title_mleft;			// ヘッダタイトルマージンleft
				$title_mright = $paramObj->title_mright;			// ヘッダタイトルマージンright
				$title_mbottom = $paramObj->title_mbottom;			// ヘッダタイトルマージンbottom
				$this->titleAlign = $paramObj->titleAlign;	// ヘッダタイトル表示位置
				$desc_mtop = $paramObj->desc_mtop;			// ヘッダ説明マージンtop
				$desc_mleft = $paramObj->desc_mleft;			// ヘッダ説明マージンleft
				$desc_mright = $paramObj->desc_mright;			// ヘッダ説明マージンright
				$desc_mbottom = $paramObj->desc_mbottom;			// ヘッダ説明マージンbottom
				$this->descAlign = $paramObj->descAlign;	// ヘッダ説明表示位置
				$url_mtop = $paramObj->url_mtop;			// ヘッダURLマージンtop
				$url_mleft = $paramObj->url_mleft;			// ヘッダURLマージンleft
				$url_mright = $paramObj->url_mright;			// ヘッダURLマージンright
				$url_mbottom = $paramObj->url_mbottom;			// ヘッダURLマージンbottom
				$this->urlAlign = $paramObj->urlAlign;	// ヘッダURL表示位置
				$titleColor = $paramObj->titleColor;		// ヘッダタイトルカラー
				$descColor = $paramObj->descColor;		// ヘッダ説明カラー
				$urlColor = $paramObj->urlColor;		// ヘッダURLカラー
				$bgcolor = $paramObj->bgcolor;		// ヘッダバックグランドカラー
				$width	= $paramObj->width;		// ヘッダの幅
				$height	= $paramObj->height;		// ヘッダの高さ
				$widthType = $paramObj->widthType;		// ヘッダの幅単位
				$heightType = $paramObj->heightType;		// ヘッダの高さ単位
				$titleFontsize	= $paramObj->titleFontsize;// タイトルのフォントサイズ
				$descFontsize	= $paramObj->descFontsize;// 説明のフォントサイズ
				$urlFontsize	= $paramObj->urlFontsize;	// URLのフォントサイズ
				$useOriginalImage = $paramObj->useOriginalImage;			// オリジナル画像を使用するかどうか
				$imageUrl	= $paramObj->imageUrl;							// 画像へのパス
				$linkUrl	= $paramObj->linkUrl;								// リンク先URL
			}
		}
		
		// 表示位置選択メニュー作成
		$this->createTitleAlignMenu();
		$this->createDescAlignMenu();
		$this->createUrlAlignMenu();
		//$this->createImageAlignMenu();
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "title",	$title);
		$this->tmpl->addVar("_widget", "desc",	$desc);
		$this->tmpl->addVar("_widget", "url",	$url);
		$this->tmpl->addVar("_widget", "image_mtop",	$image_mtop);
		$this->tmpl->addVar("_widget", "image_mleft",	$image_mleft);
		$this->tmpl->addVar("_widget", "image_mright",	$image_mright);
		$this->tmpl->addVar("_widget", "image_mbottom",	$image_mbottom);
		$this->tmpl->addVar("_widget", "title_mtop",	$title_mtop);
		$this->tmpl->addVar("_widget", "title_mleft",	$title_mleft);
		$this->tmpl->addVar("_widget", "title_mright",	$title_mright);
		$this->tmpl->addVar("_widget", "title_mbottom",	$title_mbottom);
		$this->tmpl->addVar("_widget", "desc_mtop",	$desc_mtop);
		$this->tmpl->addVar("_widget", "desc_mleft",	$desc_mleft);
		$this->tmpl->addVar("_widget", "desc_mright",	$desc_mright);
		$this->tmpl->addVar("_widget", "desc_mbottom",	$desc_mbottom);
		$this->tmpl->addVar("_widget", "url_mtop",	$url_mtop);
		$this->tmpl->addVar("_widget", "url_mleft",	$url_mleft);
		$this->tmpl->addVar("_widget", "url_mright",	$url_mright);
		$this->tmpl->addVar("_widget", "url_mbottom",	$url_mbottom);
		$this->tmpl->addVar("_widget", "title_color", $titleColor);
		$this->tmpl->addVar("_widget", "desc_color", $descColor);
		$this->tmpl->addVar("_widget", "url_color", $urlColor);
		$this->tmpl->addVar("_widget", "bgcolor", $bgcolor);
		$this->tmpl->addVar("_widget", "width",	$width);
		$this->tmpl->addVar("_widget", "height",	$height);
		$this->tmpl->addVar("_widget", "title_fontsize",	$titleFontsize);
		$this->tmpl->addVar("_widget", "desc_fontsize",	$descFontsize);
		$this->tmpl->addVar("_widget", "url_fontsize",	$urlFontsize);

		// 高さ、幅の単位
		if (empty($widthType)){		// ヘッダの幅単位
			$this->tmpl->addVar("_widget", "width0_selected",	'selected');
		} else {
			$this->tmpl->addVar("_widget", "width1_selected",	'selected');
		}
		if (empty($heightType)){		// ヘッダの高さ単位
			$this->tmpl->addVar("_widget", "height0_selected",	'selected');
		} else {
			$this->tmpl->addVar("_widget", "height1_selected",	'selected');
		}
				
		// ヘッダ画像の種類の選択状況
		if ($useOriginalImage == 0){		// 画像を使用しない
			$this->tmpl->addVar("_widget", "no_img_checked",	'checked');
		} else if ($useOriginalImage == 1){		// メニューから選択
			$this->tmpl->addVar("_widget", "menu_img_checked",	'checked');
			$filename = basename($imageUrl);
		} else if ($useOriginalImage == 2){		// オリジナル画像
			$this->tmpl->addVar("_widget", "original_img_checked",	'checked');
			$this->tmpl->addVar("_widget", "image_url",	$this->getUrl($imageUrl));
		}

		// メニュー画像メニューを作成
		// 画像ディレクトリチェック
		$searchPath = $this->gEnv->getCurrentWidgetRootPath() . '/' . self::IMAGE_DIR;
		if (is_dir($searchPath)){
			$dir = dir($searchPath);
			while (($file = $dir->read()) !== false){
				$filePath = $searchPath . '/' . $file;
				// ファイルかどうかチェック
				if (strncmp($file, '.', 1) != 0 && $file != '..' && is_file($filePath)
					&& strncmp($file, '_', 1) != 0){		// 「_」で始まる名前のファイルは読み込まない
					
					$selected = '';
					if ($file == $filename){
						$selected = 'selected';
					}
					$row = array(
						'value'    => $file,			// ファイル名
						'name'     => $file,			// ファイル名
						'selected' => $selected														// 選択中かどうか
					);
					$this->tmpl->addVars('image_file_list', $row);
					$this->tmpl->parseTemplate('image_file_list', 'a');
				}
			}
			$dir->close();
		}
		// プレビューヘッダの作成
		// 高さ、幅を取得
		if (empty($width)){		// 0または空のときは設定しない
			$width = '';
		} else {
			if (empty($widthType)){		// ヘッダの幅単位
				$width = 'width: ' . $width . '%;';
			} else {
				$width = 'width: ' . $width . 'px;';
			}
		}
		if (empty($height)){		// 0または空のときは設定しない
			$height = '';
		} else {
			if (empty($heightType)){		// ヘッダの高さ単位
				$height = 'height: ' . $height . '%;';
			} else {
				$height = 'height: ' . $height . 'px;';
			}
		}
		/*if (empty($imageUrl)){		// 画像を設定しないとき
			$width = 'width: ' . $width . ';';
			$height = 'height: ' . $height . ';';			
		} else {
			$image = '<img src="' . $this->getUrl($imageUrl) . '" width="' . $width . '" height="' . $height . '" style="z-index:0" />';
			$this->tmpl->addVar("_widget", "head_image", $image);		// 画像
			
			// CSSの高さ、幅をクリア
			$width = '';
			$height = '';
		}*/
		if (!empty($imageUrl)){		// 画像を設定
			$image = '<img src="' . $this->getUrl($imageUrl) . '" />';
			$this->tmpl->addVar("_widget", "head_image", $image);		// 画像
		}
		// フォントカラー
		if (!empty($titleColor)) $titleColor = "color: $titleColor;";		// ヘッダタイトルカラー
		if (!empty($descColor)) $descColor = "color: $descColor;";			// ヘッダ説明カラー
		if (!empty($urlColor)) $urlColor = "color: $urlColor;";				// ヘッダURLカラー
				
		// 背景色
		if (!empty($bgcolor)) $bgcolor = "background: $bgcolor;";
		
		// 表示文字列の位置
		if (empty($image_mtop)){
			$image_mtop = '';
		} else {
			$image_mtop = 'margin-top: ' . $image_mtop . 'px;';
		}
		if (empty($image_mleft)){
			$image_mleft = '';
		} else {
			$image_mleft = 'margin-left: ' . $image_mleft . 'px;';
		}
		if (empty($image_mright)){
			$image_mright = '';
		} else {
			$image_mright = 'margin-right: ' . $image_mright . 'px;';
		}
		if (empty($image_mbottom)){
			$image_mbottom = '';
		} else {
			$image_mbottom = 'margin-bottom: ' . $image_mbottom . 'px;';
		}
		if (empty($title_mtop)){
			$title_mtop = '';
		} else {
			$title_mtop = 'margin-top: ' . $title_mtop . 'px;';
		}
		if (empty($title_mleft)){
			$title_mleft = '';
		} else {
			$title_mleft = 'margin-left: ' . $title_mleft . 'px;';
		}
		if (empty($title_mright)){
			$title_mright = '';
		} else {
			$title_mright = 'margin-right: ' . $title_mright . 'px;';
		}
		if (empty($title_mbottom)){
			$title_mbottom = '';
		} else {
			$title_mbottom = 'margin-bottom: ' . $title_mbottom . 'px;';
		}
		if (empty($desc_mtop)){
			$desc_mtop = '';
		} else {
			$desc_mtop = 'margin-top: ' . $desc_mtop . 'px;';
		}
		if (empty($desc_mleft)){
			$desc_mleft = '';
		} else {
			$desc_mleft = 'margin-left: ' . $desc_mleft . 'px;';
		}
		if (empty($desc_mright)){
			$desc_mright = '';
		} else {
			$desc_mright = 'margin-right: ' . $desc_mright . 'px;';
		}
		if (empty($desc_mbottom)){
			$desc_mbottom = '';
		} else {
			$desc_mbottom = 'margin-bottom: ' . $desc_mbottom . 'px;';
		}
		if (empty($url_mtop)){
			$url_mtop = '';
		} else {
			$url_mtop = 'margin-top: ' . $url_mtop . 'px;';
		}
		if (empty($url_mleft)){
			$url_mleft = '';
		} else {
			$url_mleft = 'margin-left: ' . $url_mleft . 'px;';
		}
		if (empty($url_mright)){
			$url_mright = '';
		} else {
			$url_mright = 'margin-right: ' . $url_mright . 'px;';
		}
		if (empty($url_mbottom)){
			$url_mbottom = '';
		} else {
			$url_mbottom = 'margin-bottom: ' . $url_mbottom . 'px;';
		}
		
		// 表示位置
	//	$image_align = '';
		$title_align = '';
		$desc_align = '';
		$url_align = '';
	//	if (!empty($this->imageAlign)) $image_align = 'text-align: ' . $this->imageAlign . ';';
		if (!empty($this->titleAlign)) $title_align = 'text-align: ' . $this->titleAlign . ';';
		if (!empty($this->descAlign)) $desc_align = 'text-align: ' . $this->descAlign . ';';
		if (!empty($this->urlAlign)) $url_align = 'text-align: ' . $this->urlAlign . ';';
		
		$this->tmpl->addVar("_widget", "head_title",	$title);
		$this->tmpl->addVar("_widget", "head_desc",	$desc);
		$this->tmpl->addVar("_widget", "head_url",	$url);
		$this->tmpl->addVar("_widget", "head_image_mtop",	$image_mtop);
		$this->tmpl->addVar("_widget", "head_image_mleft",	$image_mleft);
		$this->tmpl->addVar("_widget", "head_image_mright",	$image_mright);
		$this->tmpl->addVar("_widget", "head_image_mbottom",	$image_mbottom);
		$this->tmpl->addVar("_widget", "head_title_mtop",	$title_mtop);
		$this->tmpl->addVar("_widget", "head_title_mleft",	$title_mleft);
		$this->tmpl->addVar("_widget", "head_title_mright",	$title_mright);
		$this->tmpl->addVar("_widget", "head_title_mbottom",	$title_mbottom);
		$this->tmpl->addVar("_widget", "head_desc_mtop",	$desc_mtop);
		$this->tmpl->addVar("_widget", "head_desc_mleft",	$desc_mleft);
		$this->tmpl->addVar("_widget", "head_desc_mright",	$desc_mright);
		$this->tmpl->addVar("_widget", "head_desc_mbottom",	$desc_mbottom);
		$this->tmpl->addVar("_widget", "head_url_mtop",	$url_mtop);
		$this->tmpl->addVar("_widget", "head_url_mleft",	$url_mleft);
		$this->tmpl->addVar("_widget", "head_url_mright",	$url_mright);
		$this->tmpl->addVar("_widget", "head_url_mbottom",	$url_mbottom);
	//	$this->tmpl->addVar("_widget", "head_image_align",	$image_align);
		$this->tmpl->addVar("_widget", "head_title_align",	$title_align);
		$this->tmpl->addVar("_widget", "head_desc_align",	$desc_align);
		$this->tmpl->addVar("_widget", "head_url_align",	$url_align);
		$this->tmpl->addVar("_widget", "head_title_color", $titleColor);
		$this->tmpl->addVar("_widget", "head_desc_color", $descColor);
		$this->tmpl->addVar("_widget", "head_url_color", $urlColor);
		$this->tmpl->addVar("_widget", "head_bgcolor", $bgcolor);
		$this->tmpl->addVar("_widget", "head_width",	$width);
		$this->tmpl->addVar("_widget", "head_height",	$height);
		$this->tmpl->addVar("_widget", "head_title_fontsize",	$titleFontsize);
		$this->tmpl->addVar("_widget", "head_desc_fontsize",	$descFontsize);
		$this->tmpl->addVar("_widget", "head_url_fontsize",	$urlFontsize);
		$this->tmpl->addVar("_widget", "link_url",	$this->getUrl($linkUrl));		// リンク先URL
	}
	/**
	 * 表示位置選択メニュー作成
	 *
	 * @return なし
	 */
	function createTitleAlignMenu()
	{
		for ($i = 0; $i < count($this->itemAlignArray); $i++){
			$value = $this->itemAlignArray[$i]['value'];
			$name = $this->itemAlignArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->titleAlign) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_title_align_list', $row);
			$this->tmpl->parseTemplate('item_title_align_list', 'a');
		}
	}
	/**
	 * 表示位置選択メニュー作成
	 *
	 * @return なし
	 */
	function createDescAlignMenu()
	{
		for ($i = 0; $i < count($this->itemAlignArray); $i++){
			$value = $this->itemAlignArray[$i]['value'];
			$name = $this->itemAlignArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->descAlign) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_desc_align_list', $row);
			$this->tmpl->parseTemplate('item_desc_align_list', 'a');
		}
	}
	/**
	 * 表示位置選択メニュー作成
	 *
	 * @return なし
	 */
	function createUrlAlignMenu()
	{
		for ($i = 0; $i < count($this->itemAlignArray); $i++){
			$value = $this->itemAlignArray[$i]['value'];
			$name = $this->itemAlignArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->urlAlign) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_url_align_list', $row);
			$this->tmpl->parseTemplate('item_url_align_list', 'a');
		}
	}
	/**
	 * 表示位置選択メニュー作成
	 *
	 * @return なし
	 */
	function createImageAlignMenu()
	{
		for ($i = 0; $i < count($this->itemAlignArray); $i++){
			$value = $this->itemAlignArray[$i]['value'];
			$name = $this->itemAlignArray[$i]['name'];
			
			$selected = '';
			if ($value == $this->imageAlign) $selected = 'selected';
			
			$row = array(
				'value'    => $value,			// 値
				'name'     => $name,			// 名前
				'selected' => $selected														// 選択中かどうか
			);
			$this->tmpl->addVars('item_image_align_list', $row);
			$this->tmpl->parseTemplate('item_image_align_list', 'a');
		}
	}
}
?>
