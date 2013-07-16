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
 * @version    SVN: $Id: effect_nicejformsWidgetContainer.php 2266 2009-08-28 08:25:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class effect_nicejformsWidgetContainer extends BaseWidgetContainer
{
	private $langId;		// 現在の言語
	private $paramObj;		// 定義取得用
	private $headCss;			// ヘッダ出力用CSS
	private $cssFilePath;		// CSSファイルのパス
	private $colorTypeDef;		// カラータイプ選択用メニュー定義
	const DEFAULT_CONFIG_ID = 0;
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// カラータイプ選択用メニュー定義
		$this->colorTypeDef = array(	array(	'name' => '青',		'value' => '0',		'filename' => 'niceforms-default.css',	'image_dir' => 'default'),
										array(	'name' => '緑',		'value' => '1',		'filename' => 'niceforms-green.css',	'image_dir' => 'greentheme'),
										array(	'name' => '赤',		'value' => '2',		'filename' => 'niceforms-red.css',		'image_dir' => 'redtheme'));
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
			$colorType	= $targetObj->colorType;	// カラータイプ
			
			$this->cssFilePath = '';		// CSSファイルのパス
			for ($i = 0; $i < count($this->colorTypeDef); $i++){
				$value = $this->colorTypeDef[$i]['value'];
				if ($value == $colorType){
					$this->cssFilePath = $this->gEnv->getCurrentWidgetCssUrl() . '/' . $this->colorTypeDef[$i]['filename'];		// CSSファイル名
					$imagePath = $this->gEnv->getCurrentWidgetImagesUrl() . '/' . $this->colorTypeDef[$i]['image_dir'] . '/';	// 画像ディレクトリ
					
					$this->tmpl->setAttribute('show_html', 'visibility', 'visible');
					$this->tmpl->addVar('show_html', 'image_path', $this->getUrl($imagePath));
					break;
				}
			}
		}
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
		return $this->cssFilePath;
	}
}
?>
