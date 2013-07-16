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
 * @version    SVN: $Id: s_bbs_2chSubjectWidgetContainer.php 4878 2012-04-22 14:22:38Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/s_bbs_2chBaseWidgetContainer.php');

class s_bbs_2chSubjectWidgetContainer extends s_bbs_2chBaseWidgetContainer
{
	private $isExistsThread;	// スレッドが存在するかどうか
		
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
		return 'subject.tmpl.html';
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
		// 検索キーワードを取得
		$keyword = $request->trimValueOf(M3_REQUEST_PARAM_KEYWORD);
			
		// スレッドメニュー作成
		if (empty($keyword)){
			$this->_db->getThread(array($this, 'itemsLoop'), $this->_boardId, -1/*すべて取得*/);
		} else {
			$this->_db->getThreadByKeyword(array($this, 'itemsLoop'), $this->_boardId, -1/*すべて取得*/, $keyword);
		}
		
		// スレッドが存在しないときはタグを非表示にする
		if (!$this->isExistsThread) $this->tmpl->setAttribute('itemlist', 'visibility', 'hidden');
		
		// 遷移先
		$this->tmpl->addVar("_widget", "url_newthread",	$this->getUrl($this->_currentPageUrl . '&task=' . self::TASK_NEW_THREAD, true));	// 新規スレッド作成
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
		// トップ画面に表示するスレッド最大数
		$threadId = $fetchedRow['th_id'];
		$no = $index + 1;
		$subject = $fetchedRow['th_subject'] . ' (' . $fetchedRow['th_message_count'] . ')';
		$url = $this->_currentPageUrl_short . M3_REQUEST_PARAM_BBS_THREAD_ID . '=' . $threadId;
		
		$row = array(
			'no' => $no,											// インデックス番号
			'url' => $this->convertUrlToHtmlEntity($this->getUrl($url, true)),											// スレッド画面へのリンク
			'subject' => $this->convertToDispString($subject)		// スレッド件名
		);
		$this->tmpl->addVars('itemlist', $row);
		$this->tmpl->parseTemplate('itemlist', 'a');
		
		$this->isExistsThread = true;	// スレッドが存在するかどうか
		return true;
	}
}
?>
