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
 * @copyright  Copyright 2006-2013 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: banner3WidgetContainer.php 5868 2013-03-28 04:08:49Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
//require_once($gEnvManager->getCurrentWidgetDbPath() . '/banner3Db.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');
require_once($gEnvManager->getWidgetContainerPath('banner3') . '/default_bannerCommonDef.php');
require_once($gEnvManager->getWidgetDbPath('banner3') . '/banner3Db.php');

class banner3WidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $record;		// 定義取得用
	private $headCss;			// ヘッダ出力用CSS
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'バナー';		// デフォルトのウィジェットタイトル名
	const MSG_NOT_FOUND_IMG_TAG = 'item tag not found';		// 画像置換用のタグが見つからないときのメッセージ
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// 代替処理用のウィジェットIDを設定
		$this->setDefaultWidgetId(default_bannerCommonDef::BANNER_WIDGET_ID);
		
		// DBオブジェクト作成
		$this->db = new banner3Db();
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;

		// バナー定義を取得
		if (!empty($configId) && ValueCheck::isNumeric($configId)){
			$ret = $this->db->getBanner($configId, $row);
			if ($ret){
				$this->record = $row;
			} else {
				$this->record = array();
			}
		}
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
		if (empty($this->record['bd_disp_direction'])){	// 縦方向に並べる場合
			return 'main_v.tmpl.html';
		} else {
			return 'main_h.tmpl.html';
		}
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
		// ウィジェット単体実行の場合
		$cmd = $request->trimValueOf(M3_REQUEST_PARAM_OPERATION_COMMAND);
		if ($cmd == M3_REQUEST_CMD_DO_WIDGET){	// ウィジェット単体実行
			$stamp = $request->trimValueOf(M3_REQUEST_PARAM_STAMP);	// 公開ID
			$url = $request->trimValueOf(M3_REQUEST_PARAM_URL);		// リダイレクトURL
			
			// バナーをクリックしたログを残す
			$this->db->clickBannerItemLog($stamp, $url, $this->gAccess->getAccessLogSerialNo());
			
			// リンク先を表示
			if (!empty($url)) $this->gPage->redirect($url);
			return;
		}
		// バナー定義が取得できたときは、バナーを画面に出力
		if (empty($this->record)){
			$this->cancelParse();		// 出力しない
		} else {
			$itemArray = explode(',', $this->record['bd_item_id']);
			if (count($itemArray) > 0){
				// 表示するバナー項目を決定する
				$itemCount = $this->record['bd_disp_item_count'];
				$dispItemArray = array();
				switch ($this->record['bd_disp_type']){
					case 0:// サイクリック
						$firstIndex = $this->record['bd_first_item_index'];
						if ($firstIndex < 0 || $firstIndex >= count($itemArray)) $firstIndex = 0;
						if ($itemCount > count($itemArray)) $itemCount = count($itemArray);
						$count = count($itemArray);
						for ($i = $firstIndex; $i < $count; $i++)
						{
							$dispItemArray[] = $itemArray[$i];
							if (count($dispItemArray) >= $itemCount){
								$nextIndex = $i + 1;
								break;
							}
						}
						$count = $itemCount - count($dispItemArray);
						for ($i = 0; $i < $count; $i++)
						{
							$dispItemArray[] = $itemArray[$i];
						}
						if ($count > 0) $nextIndex = $count;
						if ($nextIndex >= count($itemArray)) $nextIndex = 0;
						// 読み込みインデックスを保存
						if (!$this->gEnv->isSystemAdmin()){		// システム管理者の場合はカウントしない
							$ret = $this->db->updateBannerItemIndex($this->record['bd_id'], $nextIndex);
						}
						break;
					case 1:// ランダム
						while (true){
							$index = mt_rand(0, count($itemArray) -1);
							$dispItemArray[] = $itemArray[$index];
							array_splice($itemArray, $index, 1);	// 取得した項目を削除
							if (count($itemArray) == 0 || // 元のデータからすべて取得した
								count($dispItemArray) >= $itemCount) break;		// 取得最大個数に達した
						}
						break;
				}
			}
			// バナー出力
			for ($i = 0; $i < count($dispItemArray); $i++)
			{
				$ret = $this->db->getImageById($dispItemArray[$i], $row);
				if ($ret && $row['bi_visible']){	// 表示可能なときは表示
					// DBにバナー表示のログを残す
					$key = '';
					if (!$this->gEnv->isSystemAdmin()){		// システム管理者の場合はカウントしない
						$key = $this->db->viewBannerItemLog($row['bi_serial'], $this->gAccess->getAccessLogSerialNo());
					}
		
					$itemType = $row['bi_type'];		// 画像タイプ
					$imageUrl = $row['bi_image_url'];
					if (!empty($imageUrl)) $imageUrl = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $imageUrl);
					$imageWidth = $row['bi_image_width'];
					$imageHeight = $row['bi_image_height'];
					$imageAlt = $row['bi_image_alt'];		// 代替テキスト
					$srcHtml = $row['bi_html'];		// HTML
					$attr = $row['bi_attr'];		// その他の属性
					
					// その他の属性を設定
					$targetType = '';			// リンクターゲット
					if (!empty($attr)){
						$attrArray = explode(';', $attr);
						for ($j = 0; $j < count($attrArray); $j++){
							list($key, $value) = explode('=', $attrArray[$j]);
							$key = trim($key);
							$value = trim($value);
							switch ($key){
								case 'target':
									$targetType = $value;			// リンクターゲットの種類
									break;
							}
						}
					}

					// バナー表示イメージの作成
					$destImg = '';
					if (!empty($imageUrl)){
						if ($itemType == 0){		// 画像ファイルの場合
							$destImg = '<img class="banner_image" src="' . $this->getUrl($imageUrl) . '"';
							if (!empty($imageWidth) && $imageWidth > 0) $destImg .= ' width="' . $imageWidth . '"';
							if (!empty($imageHeight) && $imageHeight > 0) $destImg .= ' height="' . $imageHeight. '"';
							if (!empty($imageAlt)) $destImg .= ' alt="' . $this->convertToDispString($imageAlt) . '"';		// 代替テキスト
							$destImg .= ' />';
						} else if ($itemType == 1){		// Flashファイルの場合
							$destImg = '<object data="' . $this->getUrl($imageUrl) . '" type="application/x-shockwave-flash"';
							if (!empty($imageWidth) && $imageWidth > 0) $destImg .= ' width="' . $imageWidth . '"';
							if (!empty($imageHeight) && $imageHeight > 0) $destImg .= ' height="' . $imageHeight . '"';
							$destImg .= '><param name="movie" value="' . $this->getUrl($imageUrl) . '" /><param name="wmode" value="transparent" /></object>';
						}
					}
					
					// リンク作成
					if (empty($row['bi_link_url'])){
						$link = $destImg;
					} else {			// リンク先が設定されているとき
						// リンク先URLを取得
						$redirectUrl = default_bannerCommonDef::getLinkUrlByDevice($row['bi_link_url']);
						
						$linkUrl  = $this->gEnv->getDefaultUrl() . '?' . M3_REQUEST_PARAM_OPERATION_COMMAND . '=' . M3_REQUEST_CMD_DO_WIDGET;
						$linkUrl .= '&' . M3_REQUEST_PARAM_WIDGET_ID . '=' . $this->gEnv->getCurrentWidgetId();
						if (!empty($key)) $linkUrl .= '&stamp=' . $key;
						//$linkUrl .= '&url=' . urlencode($row['bi_link_url']);		// URLはエンコードする
						$linkUrl .= '&url=' . urlencode($redirectUrl);		// URLはエンコードする
						$link = '<a style="margin:0;padding:0;" href="' . $this->convertUrlToHtmlEntity($this->getUrl($linkUrl)) . '"';
						if (!empty($targetType)) $link .= ' target="' . $targetType . '"';		// リンクターゲット
						$link .= '>';
						$link .= $destImg;
						$link .= '</a>';
					}
					
					// テンプレートに埋め込む
					$keyTag = M3_TAG_START . M3_TAG_MACRO_ITEM . M3_TAG_END;
					$srcHtml = str_replace($keyTag, $link, $srcHtml, $count);
				//	if ($count <= 0) $srcHtml .= self::MSG_NOT_FOUND_IMG_TAG;		// 置換用のタグが見つからないときはエラーメッセージを設定
					
					$lineOutput = array(
						'item_data' => $srcHtml
					);
					$this->tmpl->addVars('itemlist', $lineOutput);
					$this->tmpl->parseTemplate('itemlist', 'a');
				}
			}
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
		return $this->headCss;
	}
}
?>
