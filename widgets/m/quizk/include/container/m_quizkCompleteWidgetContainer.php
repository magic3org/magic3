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
 * @version    SVN: $Id: m_quizkCompleteWidgetContainer.php 1933 2009-05-28 10:54:45Z fishbone $
 * @link       http://www.magic3.org
 */
require_once($gEnvManager->getCurrentWidgetContainerPath() .	'/m_quizkBaseWidgetContainer.php');
require_once($gEnvManager->getCurrentWidgetDbPath() .	'/quizkDb.php');

class m_quizkCompleteWidgetContainer extends m_quizkBaseWidgetContainer
{
	private $db;	// DB接続オブジェクト
	private $setId;				// 定義セットID
	const CFG_DEFAULT_SET_ID_KEY = 'current_set_id';		// 現在の選択中のセットID取得用キー
	
	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		// 親クラスを呼び出す
		parent::__construct();
		
		// DBオブジェクト作成
		$this->db = new quizkDb();
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
		return 'complete.tmpl.html';
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
		$this->setId = $this->db->getConfig(self::CFG_DEFAULT_SET_ID_KEY);		// パターンセットID
		$ret = $this->db->getAnswerResult($this->setId, $this->mobileId, $rows);
		if ($ret){
			$totalCount = count($rows);
			$rightCount = 0;
			for ($i = 0; $i < $totalCount; $i++){
				if ($rows[$i]['qp_result']) $rightCount++;
			}
			$resutlStr = $totalCount . '問中&nbsp;' . $rightCount . '問正解';
			$resutlRatioStr = '正解率&nbsp;0%';
			if ($totalCount > 0) $resutlRatioStr = '正解率&nbsp;' . sprintf("%01.1f", round($rightCount / $totalCount * 100, 1)) . '%';
			$this->tmpl->addVar('_widget', 'result', $resutlStr);
			$this->tmpl->addVar('_widget', 'result_ratio', $resutlRatioStr);
		}
		$this->tmpl->addVar('_widget', 'top_url', $this->gEnv->createCurrentPageUrlForMobile(''));
	}
}
?>
