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
 * @copyright  Copyright 2006-2012 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_googlemapsWidgetContainer.php 4770 2012-03-19 12:15:19Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');

class m_googlemapsWidgetContainer extends BaseWidgetContainer
{
	private $langId;		// 現在の言語
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'Googleマップ';			// デフォルトのウィジェットタイトル
	const MIN_ZOOM_LEVEL = 1;			// ズーム値最小
	const MAX_ZOOM_LEVEL = 21;			// ズーム値最大
	const MOVE_RATIO = 0.4;			// 地図を移動する場合の移動比率
	
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
		if (empty($targetObj)){ 		// 定義データが取得できないとき
			$this->cancelParse();// 出力しない
			return;
		}
		
		// 初期値取得
		$name = $targetObj->name;// 定義名
		$width	= $targetObj->width;		// 幅
		$height	= $targetObj->height;		// 高さ
		$lat	= $targetObj->lat;		// 緯度
		$lng	= $targetObj->lng;		// 経度
		$markerLat	= $targetObj->markerLat;		// マーカー緯度
		$markerLng	= $targetObj->markerLng;		// マーカー経度
		$zoom	= intval($targetObj->zoom);		// ズームレベル
		$showMarker = $targetObj->showMarker;		// マーカーを表示するかどうか
		$alt	= $targetObj->alt;		// 代替テキスト
		$pixelX = 0;		// 経度移動ピクセル値
		$pixelY = 0;		// 緯度移動ピクセル値
		
		// URLからパラメータを取得
		$value	= $request->trimValueOf('center');		// 中心点
		if (!empty($value)){
			list($lat, $lng) = explode(',', $value, 2);
			$lat = floatval(trim($lat));
			$lng = floatval(trim($lng));
		}
		$value	= $request->trimValueOf('zoom');		// ズームレベル
		if (!empty($value)) $zoom = intval($value);
		$value	= $request->trimValueOf('move');		// 地図移動方向
		if (!empty($value)){
			switch ($value){
				case 'left':
					$pixelX = $width * self::MOVE_RATIO * (-1);
					break;
				case 'right':
					$pixelX = $width * self::MOVE_RATIO;
					break;
				case 'up':
					$pixelY = $height * self::MOVE_RATIO * (-1);
					break;
				case 'down':
					$pixelY = $height * self::MOVE_RATIO;
					break;
			}
		}
		
		// 値の修正
		if ($zoom < self::MIN_ZOOM_LEVEL) $zoom = self::MIN_ZOOM_LEVEL;
		if ($zoom > self::MAX_ZOOM_LEVEL) $zoom = self::MAX_ZOOM_LEVEL;
		
		// 中心点の移動
		if ($pixelX != 0 || $pixelY != 0) list($lng, $lat) = $this->moveByPixel($lng, $lat, $pixelX, $pixelY, $zoom);

		// 画像URL作成
//		$url = 'http://maps.google.com/maps/api/staticmap?center=' . $lat . ',' . $lng . '&zoom=' . $zoom . '&size=' . $width . 'x' . $height . '&format=gif&mobile=true&sensor=true';	// docomo携帯では「format」が必須
		$url = 'http://maps.googleapis.com/maps/api/staticmap?center=' . $lat . ',' . $lng . '&zoom=' . $zoom . '&size=' . $width . 'x' . $height . '&format=gif&mobile=true';	// docomo携帯では「format」が必須		// 2016/9/19更新
		if ($showMarker){// マーカー表示
			$url .= '&markers=' . $markerLat . ',' . $markerLng;
		}
		$option = 'width="' . $width . '" height="' . $height . '" ';
		if (!empty($alt)) $option .= 'alt="' . $this->convertToDispString($alt) . '" ';
		
		// 地図操作用URL作成
		$paramBase = 'center=' . $lat . ',' . $lng;
		$zoomSmall = $zoom -1;
		if ($zoomSmall < self::MIN_ZOOM_LEVEL) $zoomSmall = self::MIN_ZOOM_LEVEL;
		$param = $paramBase . '&zoom=' . $zoomSmall;
		$smallUrlLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile($param)));
		$zoomLarge = $zoom +1;
		if ($zoomLarge > self::MAX_ZOOM_LEVEL) $zoomLarge = self::MAX_ZOOM_LEVEL;
		$param = $paramBase . '&zoom=' . $zoomLarge;
		$largeUrlLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile($param)));
		$param = $paramBase . '&zoom=' . $zoom . '&move=left';
		$leftUrlLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile($param)));
		$param = $paramBase . '&zoom=' . $zoom . '&move=right';
		$rightUrlLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile($param)));
		$param = $paramBase . '&zoom=' . $zoom . '&move=up';
		$upUrlLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile($param)));
		$param = $paramBase . '&zoom=' . $zoom . '&move=down';
		$downUrlLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile($param)));

		// 表示データ埋め込み
		$this->tmpl->addVar("_widget", "url",		$this->getUrl($url));
		$this->tmpl->addVar("_widget", "option",	$option);
		$this->tmpl->addVar("_widget", "url_small",	$smallUrlLink);
		$this->tmpl->addVar("_widget", "url_large",	$largeUrlLink);
		$this->tmpl->addVar("_widget", "url_left",	$leftUrlLink);
		$this->tmpl->addVar("_widget", "url_right",	$rightUrlLink);
		$this->tmpl->addVar("_widget", "url_up",	$upUrlLink);
		$this->tmpl->addVar("_widget", "url_down",	$downUrlLink);
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
	 * Googleマップの中心点をピクセル値で移動
	 *
	 * @param float $x		中心点経度
	 * @param float $y		中心点緯度
	 * @param int $pixelX	移動ピクセル値経度
	 * @param int $pixelY	移動ピクセル値緯度
	 * @param int $zoom		ズームレベル
	 * @return array 		x,yの配列で新規の中心点の緯度経度が返る
	 */
	function moveByPixel($x, $y, $pixelX, $pixelY, $zoom)
	{
		$offset = 268435456;
		$radius = $offset / pi();
		$newX = ((round(round($offset + $radius * $x * pi() / 180) + ($pixelX << (21 - $zoom))) - $offset) / $radius) * 180 / pi();
		$newY = (pi() / 2 - 2 * atan(exp((round(round($offset - $radius * log((1 + sin($y * pi() / 180)) / (1 - sin($y * pi() / 180))) / 2) + ($pixelY << (21 - $zoom))) - $offset) / $radius))) * 180 / pi();
		return array($newX, $newY);
	}
}
?>
