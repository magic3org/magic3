<?php
/**
 * index.php用コンテナクラス
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    マイクロブログ
 * @author     株式会社 毎日メディアサービス
 * @copyright  Copyright 2010 株式会社 毎日メディアサービス.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: m_chachaTopWidgetContainer.php 3296 2010-06-26 06:59:12Z fishbone $
 * @link       http://www.m-media.co.jp
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_chachaBaseWidgetContainer.php');

class m_chachaTopWidgetContainer extends m_chachaBaseWidgetContainer
{
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
		return 'top.tmpl.html';
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
		// トップコンテンツ
		$topContents = $this->_configArray[self::CF_TOP_CONTENTS];
		$topContents = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->getUrl($this->gEnv->getRootUrl()), $topContents);	// Magic3ルートURLの変換
		$this->gInstance->getTextConvManager()->convFromEmojiTag($topContents, $topContents);// Magic3内部タグから絵文字画像タグに変換
		$this->tmpl->addVar("_widget", "top_contents", $topContents);
		
		// URL
		$this->tmpl->addVar('_widget', 'read_url', $this->gEnv->createCurrentPageUrlForMobile('task=' . self::TASK_READ));
		$registLink = $this->convertUrlToHtmlEntity($this->getUrl($this->gEnv->createCurrentPageUrlForMobile('task=' . self::TASK_MYPAGE)));
		$registName = '投稿する';
		$this->tmpl->addVar("_widget", "regist_url", $registLink);
		$this->tmpl->addVar("_widget", "regist_name", $registName);
	}
}
?>
