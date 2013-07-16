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
 * @version    SVN: $Id: m_pc_contentWidgetContainer.php 3728 2010-10-24 09:16:35Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getContainerPath() . '/baseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() . '/pc_contentDb.php');
require_once($gEnvManager->getCommonPath() . '/valueCheck.php');

class m_pc_contentWidgetContainer extends BaseWidgetContainer
{
	private $db;			// DB接続オブジェクト
	private $_contentCreated;	// コンテンツが取得できたかどうか
	private $currentDay;		// 現在日
	private $currentHour;		// 現在時間
	private $headTitle;		// HTMLヘッダタイトル
	private $currentRootUrl;		// 現在のページのルートURL
	const CONTENT_TYPE = 'ct';		// 参照数カウント用
		
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new pc_contentDb();
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
		return 'main.tmpl.html';
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
		// 現在日時を取得
		$this->currentDay = date("Y/m/d");		// 日
		$this->currentHour = (int)date("H");		// 時間

		// 現在のページのルートURL
		$this->currentRootUrl = $this->gEnv->getRootUrlByCurrentPage();
		
		// ログインユーザでないときは、ユーザ制限のない項目だけ表示
		$all = false;
		if ($this->gEnv->isCurrentUserLogined()) $all = true;
		
		$contentid = $request->trimValueOf('contentid');
		if (empty($contentid)){	// コンテンツIDがないときはデフォルトデータを取得
			$this->db->getContentItems(array($this, 'itemsLoop'), null, $this->gEnv->getCurrentLanguage(), $all);
			if (!$this->_contentCreated){		// コンテンツが取得できなかったときはデフォルト言語で取得
				$this->db->getContentItems(array($this, 'itemsLoop'), null, $this->gEnv->getDefaultLanguage(), $all);
			}
		} else {
			// データエラーチェック
			$contentIdArray = explode(',', $contentid);
			if (ValueCheck::isNumeric($contentIdArray)){		// すべて数値であるかチェック
				$this->db->getContentItems(array($this, 'itemsLoop'), $contentIdArray, $this->gEnv->getCurrentLanguage(), $all);
				if (!$this->_contentCreated){		// コンテンツが取得できなかったときはデフォルト言語で取得
					$this->db->getContentItems(array($this, 'itemsLoop'), $contentIdArray, $this->gEnv->getDefaultLanguage(), $all);
				}
			} else {
				$this->setAppErrorMsg('IDにエラー値があります');
			}
		}
		// HTMLサブタイトルを設定
		if (!empty($this->headTitle)) $this->gPage->setHeadSubTitle($this->headTitle);
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
		// ビューカウントを更新
		if (!$this->gEnv->isSystemManageUser()){		// システム運用者以上の場合はカウントしない
			$this->gInstance->getAnalyzeManager()->updateContentViewCount(self::CONTENT_TYPE, $fetchedRow['cn_serial'], $this->currentDay, $this->currentHour);
		}

		// タイトルを設定
		$title = $fetchedRow['cn_name'];
		if (empty($this->headTitle)) $this->headTitle = $title;
		
		// HTMLを出力
		// 出力内容は特にエラーチェックしない
		$contentText = $fetchedRow['cn_html'];
		$contentText = str_replace(M3_TAG_START . M3_TAG_MACRO_ROOT_URL . M3_TAG_END, $this->gEnv->getRootUrl(), $contentText);// アプリケーションルートを変換
		
		// 登録したキーワードを変換
		$this->gInstance->getTextConvManager()->convByKeyValue($contentText, $contentText, true/*改行コーをbrタグに変換*/);
		
		// 携帯用コンテンツに変換
		$contentText = $this->gInstance->getTextConvManager()->autoConvPcContentToMobile($contentText, $this->currentRootUrl/*現在のページのルートURL*/, 
																				M3_VIEW_TYPE_CONTENT/*汎用コンテンツ*/, $fetchedRow['cn_create_dt']/*コンテンツ作成日時*/);
		
		$row = array(
			'title' => $title,
			'content' => $contentText	// コンテンツ
		);
		$this->tmpl->addVars('contentlist', $row);
		$this->tmpl->parseTemplate('contentlist', 'a');
		
		// コンテンツが取得できた
		$this->_contentCreated = true;
		return true;
	}
}
?>
