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
 * @copyright  Copyright 2006-2011 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: picasaWidgetContainer.php 4233 2011-07-25 09:39:04Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class picasaWidgetContainer extends BaseWidgetContainer
{
	private $langId;		// 現在の言語
	const DEFAULT_CONFIG_ID = 0;
	const DEFAULT_TITLE = 'Picasaアルバム';			// デフォルトのウィジェットタイトル
	const CONTENT_TYPE = 'smartphone';			// コンテンツタイプ
	const DEFAULT_GOOGLE_LANG = 'ja';			// Googleの表示言語 en_US
	const DEFAULT_DISPLAY_TYPE = 'title_image';		// デフォルトのPicasa表示タイプ
	
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
		$this->langId = $this->gEnv->getCurrentLanguage();
		
		// 定義ID取得
		$configId = $this->gEnv->getCurrentWidgetConfigId();
		if (empty($configId)) $configId = self::DEFAULT_CONFIG_ID;
		
		// パラメータオブジェクトを取得
		$targetObj = $this->getWidgetParamObjByConfigId($configId);
		if (!empty($targetObj)){		// 定義データが取得できたとき
			$name		= $targetObj->name;// 定義名
			$title		= $targetObj->title;			// リストタイトル
			$picasaId	= $targetObj->picasaId;		// PicasaユーザID
			$displayType = $targetObj->displayType;			// Picasa表示タイプ
			if (empty($displayType)) $displayType = self::DEFAULT_DISPLAY_TYPE;
			$imageWidth = $targetObj->imageWidth;			// 画像幅
//			$imageHeight = $targetObj->imageHeight;			// 画像幅
			$imageStyle	= $targetObj->imageStyle;			// 画像スタイル
			$showTitle	= $targetObj->showTitle;			// アルバムタイトルを表示するかどうか
			$albumCount	= $targetObj->albumCount;			// アルバム数
			$imageCount	= $targetObj->imageCount;			// 画像数
			$colCount	= $targetObj->colCount;			// カラム数
			$albumId = $targetObj->albumId;			// アルバムID
			
			// RSSを取得
			$commonParam = '&hl=' . self::DEFAULT_GOOGLE_LANG . '&access=public';
//			$rssUrl = 'http://picasaweb.google.com/data/feed/base/user/' . $picasaId . '?kind=album&alt=rss&hl=' . self::DEFAULT_GOOGLE_LANG . '&access=public&max-results=' . $maxres;
			if ($displayType == 'select_album' && !empty($albumId)){	// 選択アルバム
				$photoUrl = 'https://picasaweb.google.com/data/feed/api/user/' . $picasaId . '/albumid/' . $albumId . '?alt=rss' . $commonParam . '&max-results=' . $imageCount;	// RSS 2.0でアルバムを取得
				$photoXml = simplexml_load_file($photoUrl);
				if ($photoXml !== false){	// 正常終了のとき
					$this->tmpl->addVar('display_type', 'type', 'images');// ランダムアルバム
					
					// アルバムタイトル、URL取得
					$photoCount = count($photoXml->channel->item);
					$albumTitle = $photoXml->channel->title;
					$albumUrl = str_replace('https://', 'http://', $photoXml->channel->link);		// HTTPに統一
					
					// アルバムタイトルの表示
					if (!empty($showTitle)){
						$titleTag = '<h2><a href="' . $this->convertUrlToHtmlEntity($albumUrl) . '" target="_blank">' . $this->convertToDispString($albumTitle) . '</a></h2>';
						$this->tmpl->addVar("display_type", "title",		$titleTag);
					}
				
					// 画像の表示
					for ($j = 0; $j < $photoCount; $j++){
						$photo = $photoXml->channel->item[$j];
						$photoTitle = $photo->title;
						$photoLink = str_replace('https://', 'http://', $photo->link);		// HTTPに統一
					
						$media = $photo->children('http://search.yahoo.com/mrss/');
						$groupContent = $media->group->content;
						$attrs = $groupContent->attributes();
						$photoUrl = str_replace('https://', 'http://', $attrs['url']);		// HTTPに統一
						$photoUrl = $this->getUrl($photoUrl);			// ページに合わせてSSLを使用
					
						$groupThumbnail = $media->group->thumbnail[0];			// S72
						//$groupThumbnail = $media->group->thumbnail[1];		// S144
						//$groupThumbnail = $media->group->thumbnail[2];		// S288 最大画像
						$attrs = $groupThumbnail->attributes();
						$thumbnailUrl = $attrs['url'];
						$thumbnailWidth = $attrs['width'];
						$thumnailUrl = str_replace('https://', 'http://', $thumbnailUrl);		// HTTPに統一
						$thumnailUrl = str_replace('s72-', 's' . $imageWidth . '-', $thumnailUrl);		// 画像サイズを合わせる
						$thumnailUrl = $this->getUrl($thumnailUrl);			// ページに合わせてSSLを使用
					
						$dispPhotoTitle = $this->convertToDispString($photoTitle);
						$imageTag = '<a href="' . $this->convertUrlToHtmlEntity($photoLink) . '" target="_blank"><img src="' . $this->convertUrlToHtmlEntity($thumnailUrl) . '" alt="' . $dispPhotoTitle . '" title="' . $dispPhotoTitle . '" style="width:' . $imageWidth . 'px;height:' . $imageWidth . 'px;' . $imageStyle . '" /></a>';
						if (($j + 1) % $colCount == 0) $imageTag .= '<br />';
						$row = array(
							'image' => $imageTag			// アルバムのサムネール画像
						);
						$this->tmpl->addVars('imagelist', $row);
						$this->tmpl->parseTemplate('imagelist', 'a');
					}
				}
			} else {
				$rssUrl = 'https://picasaweb.google.com/data/feed/api/user/' . $picasaId . '?alt=rss&kind=album' . $commonParam . '&max-results=' . $albumCount;	// RSS 2.0でアルバムを取得
				
				$xml = simplexml_load_file($rssUrl);
				if ($xml !== false){	// 正常終了のとき
					$i = 0;
					$albumCount = count($xml->channel->item);		// アルバム数

					// 出力用テンプレート設定
					if ($displayType == 'random_album'){	// ランダムアルバム
						$i = rand(0, $albumCount - 1);// 出力アルバム固定
						$albumCount = $i + 1;
						$this->tmpl->addVar('display_type', 'type', 'images');// ランダムアルバム
					}
				
					for (; $i < $albumCount; $i++){
						$album = $xml->channel->item[$i];
						$albumTitle = $album->title;
						$albumUrl = str_replace('https://', 'http://', $album->link);		// HTTPに統一
						$photoCount = (int)$album->children('http://schemas.google.com/photos/2007')->numphotos;// 画像数

						$media = $album->children('http://search.yahoo.com/mrss/');
						$attrs = $media->group->thumbnail[0]->attributes();
						$thumnailUrl = $attrs['url'];
						$thumnailUrl = str_replace('https://', 'http://', $thumnailUrl);		// HTTPに統一
						$thumnailUrl = str_replace('s160-', 's' . $imageWidth . '-', $thumnailUrl);		// 画像サイズを合わせる
						$thumnailUrl = $this->getUrl($thumnailUrl);			// ページに合わせてSSLを使用
					
						// アルバムの画像情報取得用
						$photoUrl = str_replace('entry', 'feed', $album->guid) . '&kind=photo' . $commonParam . '&max-results=' . $imageCount;

						// 表示タイプごとの処理
						switch ($displayType){
							case 'title':
							default:
								$row = array(
									'name' => $this->convertToDispString($albumTitle),
									'url' => $this->convertUrlToHtmlEntity($albumUrl)	// アルバムへのリンク
								);
								$this->tmpl->addVars('itemlist', $row);
								$this->tmpl->parseTemplate('itemlist', 'a');
								break;
							case 'title_image':
								$dispAlbumTitle = $this->convertToDispString($albumTitle);
								$imageTag = '<br /><a href="' . $this->convertUrlToHtmlEntity($albumUrl) . '" target="_blank"><img src="' . $this->convertUrlToHtmlEntity($thumnailUrl) . '" alt="' . $dispAlbumTitle . '" title="' . $dispAlbumTitle . '" style="width:' . $imageWidth . 'px;height:' . $imageWidth . 'px;' . $imageStyle . '" /></a>';
								$row = array(
									'name' => $dispAlbumTitle,
									'url' => $this->convertUrlToHtmlEntity($albumUrl),	// アルバムへのリンク
									'image' => $imageTag			// アルバムのサムネール画像
								);
								$this->tmpl->addVars('itemlist', $row);
								$this->tmpl->parseTemplate('itemlist', 'a');
								break;
							case 'random_album':	// ランダムアルバム
								$photoXml = simplexml_load_file($photoUrl);
								if ($photoXml !== false){	// 正常終了のとき
									$photoCount = count($photoXml->channel->item);

									// アルバムタイトルの表示
									if (!empty($showTitle)){
										$titleTag = '<h2><a href="' . $this->convertUrlToHtmlEntity($albumUrl) . '" target="_blank">' . $this->convertToDispString($albumTitle) . '</a></h2>';
										$this->tmpl->addVar("display_type", "title",		$titleTag);
									}
								
									// 画像の表示
									for ($j = 0; $j < $photoCount; $j++){
										$photo = $photoXml->channel->item[$j];
										$photoTitle = $photo->title;
										$photoLink = str_replace('https://', 'http://', $photo->link);		// HTTPに統一
									
										$media = $photo->children('http://search.yahoo.com/mrss/');
										$groupContent = $media->group->content;
										$attrs = $groupContent->attributes();
										$photoUrl = str_replace('https://', 'http://', $attrs['url']);		// HTTPに統一
										$photoUrl = $this->getUrl($photoUrl);			// ページに合わせてSSLを使用
									
										$groupThumbnail = $media->group->thumbnail[0];			// S72
										//$groupThumbnail = $media->group->thumbnail[1];		// S144
										//$groupThumbnail = $media->group->thumbnail[2];		// S288 最大画像
										$attrs = $groupThumbnail->attributes();
										$thumbnailUrl = $attrs['url'];
										$thumbnailWidth = $attrs['width'];
										$thumnailUrl = str_replace('https://', 'http://', $thumbnailUrl);		// HTTPに統一
										$thumnailUrl = str_replace('s72-', 's' . $imageWidth . '-', $thumnailUrl);		// 画像サイズを合わせる
										$thumnailUrl = $this->getUrl($thumnailUrl);			// ページに合わせてSSLを使用
									
										$dispPhotoTitle = $this->convertToDispString($photoTitle);
										$imageTag = '<a href="' . $this->convertUrlToHtmlEntity($photoLink) . '" target="_blank"><img src="' . $this->convertUrlToHtmlEntity($thumnailUrl) . '" alt="' . $dispPhotoTitle . '" title="' . $dispPhotoTitle . '" style="width:' . $imageWidth . 'px;height:' . $imageWidth . 'px;' . $imageStyle . '" /></a>';
										if (($j + 1) % $colCount == 0) $imageTag .= '<br />';
										$row = array(
											'image' => $imageTag			// アルバムのサムネール画像
										);
										$this->tmpl->addVars('imagelist', $row);
										$this->tmpl->parseTemplate('imagelist', 'a');
									}
								}
								break;
						}
					}
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
	 * 取得したコンテンツ項目をテンプレートに設定する
	 *
	 * @param int		$index			行番号
	 * @param array		$fetchedRow		取得行
	 * @param object	$param			任意使用パラメータ
	 * @return bool						trueを返すとループ続行。falseを返すとその時点で終了。
	 */
	function itemsLoop($index, $fetchedRow)
	{
		// コンテンツへのリンクを生成
		$linkUrl = $this->getUrl($this->gEnv->getDefaultSmartphoneUrl() . '?' . M3_REQUEST_PARAM_CONTENT_ID . '=' . $fetchedRow['cn_id']);
		
		$row = array(
			'name' => $this->convertToDispString($fetchedRow['cn_name']),
			'url' => $this->convertUrlToHtmlEntity($linkUrl)	// コンテンツへのリンク
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		return true;
	}
}
?>
