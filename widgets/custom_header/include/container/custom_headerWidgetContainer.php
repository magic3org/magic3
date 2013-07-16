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
 * @copyright  Copyright 2006-2010 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: custom_headerWidgetContainer.php 3105 2010-05-08 08:51:30Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class custom_headerWidgetContainer extends BaseWidgetContainer
{
	private $image_mtop;			// ヘッダ画像マージンtop
	private $image_mleft;			// ヘッダ画像マージンleft
	private $image_mright;			// ヘッダ画像マージンright
	private $image_mbottom;			// ヘッダ画像マージンbottom
	private $title_mtop;			// ヘッダタイトルマージンtop
	private $title_mleft;			// ヘッダタイトルマージンleft
	private $title_mright;			// ヘッダタイトルマージンright
	private $title_mbottom;			// ヘッダタイトルマージンbottom
	private $desc_mtop;			// ヘッダ説明マージンtop
	private $desc_mleft;			// ヘッダ説明マージンleft
	private $desc_mright;			// ヘッダ説明マージンright
	private $desc_mbottom;			// ヘッダ説明マージンbottom
	private $url_mtop;			// ヘッダURLマージンtop
	private $url_mleft;			// ヘッダURLマージンleft
	private $url_mright;			// ヘッダURLマージンright
	private $url_mbottom;			// ヘッダURLマージンbottom
	private $titleColor;		// ヘッダタイトルカラー
	private $descColor;		// ヘッダ説明カラー
	private $urlColor;		// ヘッダURLカラー
	private $bgcolor;
	private $width;		// ヘッダの幅
	private $height;		// ヘッダの高さ
	private $titleFontsize;// タイトルのフォントサイズ
	private $descFontsize;// 説明のフォントサイズ
	private $urlFontsize;	// URLのフォントサイズ
	const IMAGE_DIR = 'image';				// 画像ディレクトリ名
	const DEFAULT_IMAGE = 'header9.png';		// デフォルトのヘッダ画像
	
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
		$title = 'title';	// ヘッダタイトル
		$desc = 'description';	// ヘッダ説明
		$url = 'http://www.sample.com';	// ヘッダURL
		
		$this->image_mtop = 0;			// ヘッダ画像マージンtop
		$this->image_mleft = 0;			// ヘッダ画像マージンleft
		$this->image_mright = 0;			// ヘッダ画像マージンright
		$this->image_mbottom = 0;			// ヘッダ画像マージンbottom
		$this->title_mtop = 10;			// ヘッダタイトルマージンtop
		$this->title_mleft = 20;			// ヘッダタイトルマージンleft
		$this->title_mright = 0;			// ヘッダタイトルマージンright
		$this->title_mbottom = 0;			// ヘッダタイトルマージンbottom
		$this->desc_mtop = 10;			// ヘッダ説明マージンtop
		$this->desc_mleft = 30;			// ヘッダ説明マージンleft
		$this->desc_mright = 0;			// ヘッダ説明マージンright
		$this->desc_mbottom = 0;			// ヘッダ説明マージンbottom
		$this->url_mtop = 10;			// ヘッダURLマージンtop
		$this->url_mleft = 0;			// ヘッダURLマージンleft
		$this->url_mright = 20;			// ヘッダURLマージンright
		$this->url_mbottom = 0;			// ヘッダURLマージンbottom
		$this->titleColor = '#FFFFFF';		// ヘッダタイトルカラー
		$this->descColor = '#FFFFFF';		// ヘッダ説明カラー
		$this->urlColor = '#FFFFFF';		// ヘッダURLカラー
		$this->bgcolor = '';
		$this->width	= 100;		// ヘッダの幅
		$this->height	= 100;		// ヘッダの高さ
		$widthType = 0;		// ヘッダの幅単位
		$heightType = 1;		// ヘッダの高さ単位
		$this->titleFontsize	= 30;// タイトルのフォントサイズ
		$this->descFontsize	= 14;// 説明のフォントサイズ
		$this->urlFontsize	= 18;	// URLのフォントサイズ
		$imageUrl = $this->gEnv->getCurrentWidgetRootUrl() . '/' . self::IMAGE_DIR . '/' . self::DEFAULT_IMAGE;// 画像へのパス
		$linkUrl = '';		// リンク先
		//$imageAlign = '';// ヘッダ画像表示位置
		$titleAlign = '';// ヘッダタイトル表示位置
		$descAlign = '';		// ヘッダ説明表示位置
		$urlAlign = 'right';			// ヘッダURL表示位置
			
		$paramObj = $this->getWidgetParamObj();
		if (!empty($paramObj)){
			$title	= $paramObj->title;			// ヘッダタイトル
			$desc	= $paramObj->desc;	// ヘッダ説明
			$url	= $paramObj->url;	// ヘッダURL
			$linkUrl	= $paramObj->linkUrl;	// リンク先URL
			
			$this->image_mtop = $paramObj->image_mtop;			// ヘッダ画像マージンtop
			$this->image_mleft = $paramObj->image_mleft;			// ヘッダ画像マージンleft
			$this->image_mright = $paramObj->image_mright;			// ヘッダ画像マージンright
			$this->image_mbottom = $paramObj->image_mbottom;			// ヘッダ画像マージンbottom
			$this->title_mtop = $paramObj->title_mtop;			// ヘッダタイトルマージンtop
			$this->title_mleft = $paramObj->title_mleft;			// ヘッダタイトルマージンleft
			$this->title_mright = $paramObj->title_mright;			// ヘッダタイトルマージンright
			$this->title_mbottom = $paramObj->title_mbottom;			// ヘッダタイトルマージンbottom
			$this->desc_mtop = $paramObj->desc_mtop;			// ヘッダ説明マージンtop
			$this->desc_mleft = $paramObj->desc_mleft;			// ヘッダ説明マージンleft
			$this->desc_mright = $paramObj->desc_mright;			// ヘッダ説明マージンright
			$this->desc_mbottom = $paramObj->desc_mbottom;			// ヘッダ説明マージンbottom
			$this->url_mtop = $paramObj->url_mtop;			// ヘッダURLマージンtop
			$this->url_mleft = $paramObj->url_mleft;			// ヘッダURLマージンleft
			$this->url_mright = $paramObj->url_mright;			// ヘッダURLマージンright
			$this->url_mbottom = $paramObj->url_mbottom;			// ヘッダURLマージンbottom
			$this->titleColor = $paramObj->titleColor;		// ヘッダタイトルカラー
			$this->descColor = $paramObj->descColor;		// ヘッダ説明カラー
			$this->urlColor = $paramObj->urlColor;		// ヘッダURLカラー
			$this->bgcolor = $paramObj->bgcolor;		// ヘッダバックグランドカラー
			$this->width	= $paramObj->width;		// ヘッダの幅
			$this->height	= $paramObj->height;		// ヘッダの高さ
			$widthType = $paramObj->widthType;		// ヘッダの幅単位
			$heightType = $paramObj->heightType;		// ヘッダの高さ単位
			$this->titleFontsize	= $paramObj->titleFontsize;// タイトルのフォントサイズ
			$this->descFontsize	= $paramObj->descFontsize;// 説明のフォントサイズ
			$this->urlFontsize	= $paramObj->urlFontsize;	// URLのフォントサイズ
			$imageUrl	= $paramObj->imageUrl;							// 画像へのパス
			//$imageAlign = $paramObj->imageAlign;// ヘッダ画像表示位置
			$titleAlign = $paramObj->titleAlign;// ヘッダタイトル表示位置
			$descAlign = $paramObj->descAlign;		// ヘッダ説明表示位置
			$urlAlign = $paramObj->urlAlign;			// ヘッダURL表示位置
		}

		// 表示データ埋め込み
		// 高さ、幅を取得
		if (empty($this->width)){		// 0または空のときは設定しない
			$this->width = '';
		} else {
			if (empty($widthType)){		// ヘッダの幅単位
				$this->width = 'width: ' . $this->width . '%;';
			} else {
				$this->width = 'width: ' . $this->width . 'px;';
			}
		}
		if (empty($this->height)){		// 0または空のときは設定しない
			$this->height = '';
		} else {
			if (empty($heightType)){		// ヘッダの高さ単位
				$this->height = 'height: ' . $this->height . '%;';
			} else {
				$this->height = 'height: ' . $this->height . 'px;';
			}
		}
		if (!empty($imageUrl)){		// 画像を設定
			$image = '<img src="' . $this->getUrl($imageUrl) . '" />';
			$this->tmpl->addVar("_widget", "image", $image);		// 画像
		}
		
		// フォントカラー
		if (!empty($this->titleColor)) $this->titleColor = "color: $this->titleColor;";		// ヘッダタイトルカラー
		if (!empty($this->descColor)) $this->descColor = "color: $this->descColor;";			// ヘッダ説明カラー
		if (!empty($this->urlColor)) $this->urlColor = "color: $this->urlColor;";				// ヘッダURLカラー
		
		// 背景色
		if (!empty($this->bgcolor)) $this->bgcolor = "background: $this->bgcolor;";
		
		// 表示文字列の位置
		if (empty($this->image_mtop)){
			$this->image_mtop = '';
		} else {
			$this->image_mtop = 'margin-top: ' . $this->image_mtop . 'px;';
		}
		if (empty($this->image_mleft)){
			$this->image_mleft = '';
		} else {
			$this->image_mleft = 'margin-left: ' . $this->image_mleft . 'px;';
		}
		if (empty($this->image_mright)){
			$this->image_mright = '';
		} else {
			$this->image_mright = 'margin-right: ' . $this->image_mright . 'px;';
		}
		if (empty($this->image_mbottom)){
			$this->image_mbottom = '';
		} else {
			$this->image_mbottom = 'margin-bottom: ' . $this->image_mbottom . 'px;';
		}
		if (empty($this->title_mtop)){
			$this->title_mtop = '';
		} else {
			$this->title_mtop = 'margin-top: ' . $this->title_mtop . 'px;';
		}
		if (empty($this->title_mleft)){
			$this->title_mleft = '';
		} else {
			$this->title_mleft = 'margin-left: ' . $this->title_mleft . 'px;';
		}
		if (empty($this->title_mright)){
			$this->title_mright = '';
		} else {
			$this->title_mright = 'margin-right: ' . $this->title_mright . 'px;';
		}
		if (empty($this->title_mbottom)){
			$this->title_mbottom = '';
		} else {
			$this->title_mbottom = 'margin-bottom: ' . $this->title_mbottom . 'px;';
		}
		if (empty($this->desc_mtop)){
			$this->desc_mtop = '';
		} else {
			$this->desc_mtop = 'margin-top: ' . $this->desc_mtop . 'px;';
		}
		if (empty($this->desc_mleft)){
			$this->desc_mleft = '';
		} else {
			$this->desc_mleft = 'margin-left: ' . $this->desc_mleft . 'px;';
		}
		if (empty($this->desc_mright)){
			$this->desc_mright = '';
		} else {
			$this->desc_mright = 'margin-right: ' . $this->desc_mright . 'px;';
		}
		if (empty($this->desc_mbottom)){
			$this->desc_mbottom = '';
		} else {
			$this->desc_mbottom = 'margin-bottom: ' . $this->desc_mbottom . 'px;';
		}
		if (empty($this->url_mtop)){
			$this->url_mtop = '';
		} else {
			$this->url_mtop = 'margin-top: ' . $this->url_mtop . 'px;';
		}
		if (empty($this->url_mleft)){
			$this->url_mleft = '';
		} else {
			$this->url_mleft = 'margin-left: ' . $this->url_mleft . 'px;';
		}
		if (empty($this->url_mright)){
			$this->url_mright = '';
		} else {
			$this->url_mright = 'margin-right: ' . $this->url_mright . 'px;';
		}
		if (empty($this->url_mbottom)){
			$this->url_mbottom = '';
		} else {
			$this->url_mbottom = 'margin-bottom: ' . $this->url_mbottom . 'px;';
		}
		
		// 表示位置
		//$this->image_align = '';
		$this->title_align = '';
		$this->desc_align = '';
		$this->url_align = '';
		//if (!empty($imageAlign)) $this->image_align = 'text-align: ' . $imageAlign . ';';
		if (!empty($titleAlign)) $this->title_align = 'text-align: ' . $titleAlign . ';';
		if (!empty($descAlign)) $this->desc_align = 'text-align: ' . $descAlign . ';';
		if (!empty($urlAlign)) $this->url_align = 'text-align: ' . $urlAlign . ';';
		
		$this->tmpl->addVar("_widget", "title",	$title);
		$this->tmpl->addVar("_widget", "desc",	$desc);
		$this->tmpl->addVar("_widget", "url",	$url);
		
		// リンク先URL
		if (!empty($linkUrl)){
			$this->tmpl->addVar("_widget", "link_start", '<a href="' . $linkUrl . '">');
			$this->tmpl->addVar("_widget", "link_end",	'</a>');
		}
	}
	/**
	 * CSSデータをHTMLヘッダ部に設定
	 *
	 * CSSデータをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						CSS文字列。出力しない場合は空文字列を設定。
	 */
	function _addCssToHead($request, &$param)
	{
		// テンプレートからCSSを作成
		$css = $this->getParsedTemplateData('index.tmpl.css', array($this, 'makeCss'));
		return $css;
	}
	/**
	 * CSSデータ作成処理コールバック
	 *
	 * @param object         $tmpl			テンプレートオブジェクト
	 * @param								なし
	 */
	function makeCss($tmpl)
	{
		$tmpl->addVar("_tmpl", "image_mtop",	$this->image_mtop);
		$tmpl->addVar("_tmpl", "image_mleft",	$this->image_mleft);
		$tmpl->addVar("_tmpl", "image_mright",	$this->image_mright);
		$tmpl->addVar("_tmpl", "image_mbottom",	$this->image_mbottom);
		$tmpl->addVar("_tmpl", "title_mtop",	$this->title_mtop);
		$tmpl->addVar("_tmpl", "title_mleft",	$this->title_mleft);
		$tmpl->addVar("_tmpl", "title_mright",	$this->title_mright);
		$tmpl->addVar("_tmpl", "title_mbottom",	$this->title_mbottom);
		$tmpl->addVar("_tmpl", "desc_mtop",		$this->desc_mtop);
		$tmpl->addVar("_tmpl", "desc_mleft",	$this->desc_mleft);
		$tmpl->addVar("_tmpl", "desc_mright",	$this->desc_mright);
		$tmpl->addVar("_tmpl", "desc_mbottom",	$this->desc_mbottom);
		$tmpl->addVar("_tmpl", "url_mtop",		$this->url_mtop);
		$tmpl->addVar("_tmpl", "url_mleft",		$this->url_mleft);
		$tmpl->addVar("_tmpl", "url_mright",	$this->url_mright);
		$tmpl->addVar("_tmpl", "url_mbottom",	$this->url_mbottom);
		$tmpl->addVar("_tmpl", "title_color", 	$this->titleColor);
		$tmpl->addVar("_tmpl", "desc_color", 	$this->descColor);
		$tmpl->addVar("_tmpl", "url_color", 	$this->urlColor);
		$tmpl->addVar("_tmpl", "bgcolor",		$this->bgcolor);
		$tmpl->addVar("_tmpl", "width",			$this->width);
		$tmpl->addVar("_tmpl", "height",		$this->height);
		$tmpl->addVar("_tmpl", "title_fontsize",	$this->titleFontsize);
		$tmpl->addVar("_tmpl", "desc_fontsize",	$this->descFontsize);
		$tmpl->addVar("_tmpl", "url_fontsize",	$this->urlFontsize);
		//$tmpl->addVar("_tmpl", "image_align",	$this->image_align);
		$tmpl->addVar("_tmpl", "title_align",	$this->title_align);
		$tmpl->addVar("_tmpl", "desc_align",	$this->desc_align);
		$tmpl->addVar("_tmpl", "url_align",		$this->url_align);
	}
}
?>
