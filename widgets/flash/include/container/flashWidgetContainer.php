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
 * @version    SVN: $Id: flashWidgetContainer.php 2266 2009-08-28 08:25:59Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class flashWidgetContainer extends BaseWidgetContainer
{
	private $langId;		// 現在の言語
	private $paramObj;		// 定義取得用
	const DEFAULT_CONFIG_ID = 0;
	
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
		//return 'index.tmpl.html';
		return 'index_simple.tmpl.html';
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
		$this->paramObj = $this->getWidgetParamObjectWithId();

		// 指定定義IDのデータを取得
		$name = '';
		for ($i = 0; $i < count($this->paramObj); $i++){
			$id			= $this->paramObj[$i]->id;// 定義ID
			$targetObj	= $this->paramObj[$i]->object;
			if ($id == $configId){
				$name		= $targetObj->name;// 定義名
				$width		= $targetObj->width;		// 幅
				$height		= $targetObj->height;		// 高さ
				$url		= $targetObj->url;		// FlashファイルのURL
				$showTitle	= $targetObj->showTitle;// タイトルを表示するかどうか
				break;
			}
		}
		if ($i < count($this->paramObj)){		// 該当する定義IDのデータが取得できたとき
			// FlashファイルのURLを修正
			$url = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $url);
			
			$this->tmpl->setAttribute('show_html', 'visibility', 'visible');
						
			// 表示データ埋め込み
			$this->tmpl->addVar("show_html", "width",	$width);
			$this->tmpl->addVar("show_html", "height",	$height);
			$this->tmpl->addVar("show_html", "url",	$this->getUrl($url));
		}
	}
}
?>
