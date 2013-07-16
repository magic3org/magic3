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
 * @copyright  Copyright 2006-2009 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: image2WidgetContainer.php 2268 2009-08-31 03:29:18Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/image2Db.php');

class image2WidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $langId;		// 現在の言語
	private $paramObj;		// 定義取得用
	private $headCss;			// ヘッダ出力用CSS
	const DEFAULT_CONFIG_ID = 0;
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new image2Db();
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$menuId		= $targetObj->menuId;	// メニューID
			$name		= $targetObj->name;// 定義名
			$imageUrl 	= $targetObj->imageUrl;							// 画像へのパス
			$linkUrl	= $targetObj->linkUrl;			// リンク先
			$align		= $targetObj->align;			// 表示位置
			$bgcolor 	= $targetObj->bgcolor;		// 画像バックグランドカラー
			$width		= $targetObj->width;		// 画像の幅
			$height		= $targetObj->height;		// 画像の高さ
			$margin		= $targetObj->margin;		// 画像マージン
			$widthType	= $targetObj->widthType;		// 画像の幅単位
			$heightType	= $targetObj->heightType;		// 画像の高さ単位
			$posx		= $targetObj->posx;		// x座標
			$posy		= $targetObj->posy;		// y座標
			$posxType	= $targetObj->posxType;		// x座標単位
			$posyType	= $targetObj->posyType;		// y座標単位
			$posType	= $targetObj->posType;		// 座標指定方法(相対座標)
			$usePos		= $targetObj->usePos;			// 座標指定を可能とするかどうか
			$useLink	= $targetObj->useLink;			// 画像にリンクを付けるかどうか
				
			// 画像のパスを修正
			if (!empty($imageUrl)){
				$imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
			}
			if (!empty($imageUrl)){
				// 外側のdivの設定
				$divStyle = '';
				if (!empty($align)){
					$align = 'align="' . $align . '"';
					$this->tmpl->addVar("_widget", "align",	$align);
				}
				if (!empty($bgcolor)) $divStyle .= 'background:' . $bgcolor . ';';
				$divStyle .= 'margin:0;';
				if (!empty($divStyle)){
					$divStyle = 'style="' . $divStyle . '"';
					$this->tmpl->addVar("_widget", "div_style",	$divStyle);
				}
			
				// 画像
				$imgStyle = 'border:0;';
				$destImg = '<img src="' . $this->getUrl($imageUrl) . '"';

				if ($width > 0){
					$destImg .= ' width="' . $width;
					if ($widthType == 0){
						$destImg .= '"';
					} else {
						$destImg .= '%"';
					}
				}
				if ($height > 0){
					$destImg .= ' height="' . $height;
					if ($heightType == 0){
						$destImg .= '"';
					} else {
						$destImg .= '%"';
					}
				}
				// マージン
				if (!empty($margin)) $imgStyle .= 'margin:' . $margin . 'px;';
				
				// 座標
				if ($usePos){
					if ($posType == 'absolute'){
						$imgStyle .= 'position:absolute;';
					} else {
						$imgStyle .= 'position:relative;';
					}
					$imgStyle .= 'left:' . $posx;
					if ($posxType == 0){
						$imgStyle .= 'px;';
					} else {
						$imgStyle .= '%;';
					}
					$imgStyle .= 'top:' . $posy;
					if ($posyType == 0){
						$imgStyle .= 'px;';
					} else {
						$imgStyle .= '%;';
					}
				}
			
				if (!empty($imgStyle)){
					$destImg .= ' style="'. $imgStyle . '"';
				}
				$destImg .= ' />';
			
				if ($useLink){		// リンクありのとき
					$destImg = '<a href="' . $this->convertUrlToHtmlEntity($linkUrl) . '">' . $destImg . '</a>';
				}
				$this->tmpl->addVar("_widget", "image",	$destImg);
			}
		}
	}
}
?>
