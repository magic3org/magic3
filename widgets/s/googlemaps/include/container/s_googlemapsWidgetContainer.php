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
 * @version    SVN: $Id: s_googlemapsWidgetContainer.php 4768 2012-03-19 10:40:37Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class s_googlemapsWidgetContainer extends BaseWidgetContainer
{
	private $langId;		// 現在の言語
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'Googleマップ';			// デフォルトのウィジェットタイトル
	
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
	 * @return								なし
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
			$name = $targetObj->name;// 定義名
			$width	= $targetObj->width;		// 幅
			$height	= $targetObj->height;		// 高さ
			$lat	= $targetObj->lat;		// 緯度
			$lng	= $targetObj->lng;		// 経度
			$markerLat	= $targetObj->markerLat;		// マーカー緯度
			$markerLng	= $targetObj->markerLng;		// マーカー経度
			$infoLat	= $targetObj->infoLat;		// 吹き出し緯度
			$infoLng	= $targetObj->infoLng;		// 吹き出し経度
			$zoom	= $targetObj->zoom;		// ズームレベル
			$infoContent	= $targetObj->infoContent;		// 吹き出し内容
			$showMarker = $targetObj->showMarker;		// マーカーを表示するかどうか
			$showPosControl = $targetObj->showPosControl;		// 位置コントローラを表示するかどうか
			$showTypeControl = $targetObj->showTypeControl;		// 地図タイプコントローラを表示するかどうか
			$showInfo		= $targetObj->showInfo;			// 吹き出しを表示するかどうか
					
			// 表示データ埋め込み
			$this->tmpl->addVar("_widget", "tag_id",	$this->gEnv->getCurrentWidgetId() . '_' . $configId);	// タグのID
			$this->tmpl->addVar("_widget", "width",	$width);
			$this->tmpl->addVar("_widget", "height",	$height);
			$this->tmpl->addVar("_widget", "lat",	$lat);		// 緯度
			$this->tmpl->addVar("_widget", "lng",	$lng);		// 経度
			$this->tmpl->addVar("_widget", "zoom",	$zoom);		// ズームレベル
			
			// マーカー表示
			if ($showMarker){
				$this->tmpl->setAttribute('show_marker', 'visibility', 'visible');
				$this->tmpl->addVar("show_marker", "marker_lat",	$markerLat);		// マーカー緯度
				$this->tmpl->addVar("show_marker", "marker_lng",	$markerLng);		// マーカー経度
			}
			// 吹き出し表示
			if ($showInfo){
				$this->tmpl->setAttribute('show_info', 'visibility', 'visible');// 吹き出しを表示
				$this->tmpl->addVar("show_info", "info_lat",	$infoLat);		// 吹き出し緯度
				$this->tmpl->addVar("show_info", "info_lng",	$infoLng);		// 吹き出し経度
				$this->tmpl->addVar("show_info", "info_content",	addslashes($infoContent));		// 吹き出し内容
			}
			
			// コントローラ表示
			if ($showPosControl) $this->tmpl->setAttribute('show_pos_control', 'visibility', 'hidden');// 位置コントローラを表示
			if ($showTypeControl) $this->tmpl->setAttribute('show_type_control', 'visibility', 'hidden');// 地図タイプコントローラを表示
		} else {
			$this->cancelParse();		// 出力しない
		}
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
	 * JavascriptファイルをHTMLヘッダ部に設定
	 *
	 * JavascriptファイルをHTMLのheadタグ内に追加出力する。
	 * _assign()よりも後に実行される。
	 *
	 * @param RequestManager $request		HTTPリクエスト処理クラス
	 * @param object         $param			任意使用パラメータ。
	 * @return string 						Javascriptファイル。出力しない場合は空文字列を設定。
	 */
	function _addScriptFileToHead($request, &$param)
	{
		$scriptUrl = $this->getUrl('http://maps.google.com/maps/api/js?sensor=true');
		return $scriptUrl;
	}
}
?>
