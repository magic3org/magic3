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
 * @copyright  Copyright 2006-2017 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id$
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseAdminWidgetContainer.php');

class admin_blog_new_boxWidgetContainer extends BaseAdminWidgetContainer
{
	private $imageType;		// 選択中の画像タイプ
	const DEFAULT_ITEM_COUNT = 10;		// デフォルトの表示項目数
	const DEFAULT_IMAGE_TYPE = '80c.jpg';		// デフォルトの画像タイプ
	
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
		return 'admin.tmpl.html';
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
	function _postAssign($request, &$param)
	{
		// メニューバー、パンくずリスト作成(簡易版)
		$this->createBasicConfigMenubar($request);
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
		// 入力値を取得
		$act			= $request->trimValueOf('act');
		$itemCount		= $request->trimValueOf('item_count');			// 表示項目数
		$useRss			= $request->trimCheckedValueOf('item_use_rss');		// RSS配信を行うかどうか
		$showDate		= $request->trimCheckedValueOf('item_show_date');		// 日付を表示するかどうか
		$optionPassage	= $request->trimCheckedValueOf('item_option_passage');		// 表示オプション(経過日時)
		$showImage		= $request->trimCheckedValueOf('item_show_image');		// 画像を表示するかどうか
		$this->imageType	= $request->trimValueOf('item_image_type');				// 画像タイプ
		$imageWidth		= $request->trimIntValueOf('item_image_width', '0');			// 画像幅(空文字列をOKとする)
		$imageHeight	= $request->trimIntValueOf('item_image_height', '0');			// 画像高さ(空文字列をOKとする)

		if ($act == 'update'){		// 設定更新のとき
			// 入力値のエラーチェック
			$this->checkNumeric($itemCount, '項目数');
			$this->checkNumeric($imageWidth, '画像幅');
			$this->checkNumeric($imageHeight, '画像高さ');
			
			if ($this->getMsgCount() == 0){			// エラーのないとき
				$paramObj = new stdClass;
				$paramObj->itemCount		= $itemCount;
				$paramObj->useRss			= $useRss;
				$paramObj->showDate			= $showDate;		// 日付を表示するかどうか
				$paramObj->optionPassage	= $optionPassage;		// 表示オプション(経過日時)
				$paramObj->showImage		= $showImage;		// 画像を表示するかどうか
				$paramObj->imageType		= $this->imageType;				// 画像タイプ
				$paramObj->imageWidth		= intval($imageWidth);			// 画像幅
				$paramObj->imageHeight		= intval($imageHeight);			// 画像高さ
		
				$ret = $this->updateWidgetParamObj($paramObj);
				if ($ret){
					$this->setMsg(self::MSG_GUIDANCE, 'データを更新しました');
				} else {
					$this->setMsg(self::MSG_APP_ERR, 'データ更新に失敗しました');
				}
				$this->gPage->updateParentWindow();// 親ウィンドウを更新
			}
		} else {		// 初期表示の場合
			// デフォルト値設定
			$itemCount = self::DEFAULT_ITEM_COUNT;	// 表示項目数
			$useRss = 1;							// RSS配信を行うかどうか
			$showDate = 0;							// 日付を表示するかどうか
			$optionPassage = 0;						// 表示オプション(経過日時)
			$showImage		= 0;		// 画像を表示するかどうか
			$this->imageType	= self::DEFAULT_IMAGE_TYPE;				// 画像タイプ
			$imageWidth		= 0;			// 画像幅
			$imageHeight	= 0;			// 画像高さ
			
			$paramObj = $this->getWidgetParamObj();
			if (!empty($paramObj)){
				$itemCount	= $paramObj->itemCount;
				$useRss		= $paramObj->useRss;// RSS配信を行うかどうか
				if (!isset($useRss)) $useRss = 1;
				$showDate			= $paramObj->showDate;		// 日付を表示するかどうか
				if (!isset($showDate)) $showDate = 0;
				$optionPassage		= $paramObj->optionPassage;		// 表示オプション(経過日時)
				if (!isset($optionPassage)) $optionPassage = 0;
				$showImage			= $paramObj->showImage;		// 画像を表示するかどうか
				$this->imageType	= $paramObj->imageType;				// 画像タイプ
				$imageWidth			= intval($paramObj->imageWidth);			// 画像幅
				$imageHeight		= intval($paramObj->imageHeight);			// 画像高さ
			}
		}
		// 画像タイプ選択メニュー作成
		$this->createpImageTypeList();
		
		// 画面にデータを埋め込む
		$this->tmpl->addVar("_widget", "item_count",	$itemCount);
		$this->tmpl->addVar("_widget", "use_rss",	$this->convertToCheckedString($useRss));// RSS配信を行うかどうか
		$this->tmpl->addVar("_widget", "show_date_checked",	$this->convertToCheckedString($showDate));// 日付を表示するかどうか
		$this->tmpl->addVar("_widget", "option_passage_checked",	$this->convertToCheckedString($optionPassage));// 表示オプション(経過日時)
		$this->tmpl->addVar("_widget", "show_image_checked",	$this->convertToCheckedString($showImage));// 画像を表示するかどうか
		$imageWidth = empty($imageWidth) ? '' : $imageWidth;
		$imageHeight = empty($imageHeight) ? '' : $imageHeight;
		$this->tmpl->addVar("_widget", "image_width",	$this->convertToDispString($imageWidth));// 画像幅
		$this->tmpl->addVar("_widget", "image_height",	$this->convertToDispString($imageHeight));// 画像高さ
	}
	/**
	 * 画像タイプ選択メニュー作成
	 *
	 * @return なし
	 */
	function createpImageTypeList()
	{
		$formats = $this->gInstance->getImageManager()->getSystemThumbFormat(1/*クロップ画像のみ*/);
		
		for ($i = 0; $i < count($formats); $i++){
			$id = $formats[$i];
			$name = $id;
			
			$selected = '';
			if ($id == $this->imageType) $selected = 'selected';

			$row = array(
				'value'			=> $this->convertToDispString($id),				// 値
				'name'			=> $this->convertToDispString($name),			// 名前
				'selected'		=> $selected			// 選択中かどうか
			);
			$this->tmpl->addVars('image_type_list', $row);
			$this->tmpl->parseTemplate('image_type_list', 'a');
		}
	}
}
?>
